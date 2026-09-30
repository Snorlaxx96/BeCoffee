<?php
/**
 * BeCoffee — Kitchen Display System (KDS) Controller
 * Serves the Staff Tablet board, manages order acknowledgments, and enforces the 3-point QA Lockout
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

$method = $_SERVER['REQUEST_METHOD'];
requireRole(['staff', 'admin', 'superadmin']);

$db = Database::getConnection();

// --- 1. GET: Live Kitchen Queue (Pending & In Progress) ---
if ($method === 'GET') {
    $stmt = $db->prepare("
        SELECT id, queue_number, order_reference, order_type, table_number, payment_method, payment_status,
               customer_name, customer_phone, customer_notes, subtotal, eco_fee, grand_total, status,
               qa_payment_verified, qa_customizations_followed, qa_packaging_secured,
               created_at, completed_at,
               TIMESTAMPDIFF(SECOND, created_at, NOW()) AS elapsed_seconds
        FROM orders
        WHERE status IN ('pending', 'in_progress')
        ORDER BY created_at ASC
    ");
    $stmt->execute();
    $rawOrders = $stmt->fetchAll();

    // Fetch line items for each active ticket
    $itemStmt = $db->prepare("
        SELECT id, order_id, item_id, item_name, temperature, milk_option, sweetness_level, custom_notes, quantity, unit_price, subtotal
        FROM order_items
        WHERE order_id = ?
    ");

    $pending = [];
    $inProgress = [];

    foreach ($rawOrders as $ord) {
        $itemStmt->execute([$ord['id']]);
        $ord['items'] = $itemStmt->fetchAll();

        // Package QA checklist booleans
        $ord['qa_checks'] = [
            'payment_verified'        => (bool) $ord['qa_payment_verified'],
            'customizations_followed' => (bool) $ord['qa_customizations_followed'],
            'packaging_secured'       => (bool) $ord['qa_packaging_secured']
        ];

        if ($ord['status'] === 'pending') {
            $pending[] = $ord;
        } else {
            $inProgress[] = $ord;
        }
    }

    jsonResponse([
        'success'     => true,
        'server_time' => time(),
        'counts'      => [
            'pending'     => count($pending),
            'in_progress' => count($inProgress),
            'total'       => count($rawOrders)
        ],
        'pending'     => $pending,
        'in_progress' => $inProgress
    ]);
}

// --- 2. PATCH: State Transitions & QA Lockout Validation ---
if ($method === 'PATCH' || $method === 'POST') {
    $action = $_GET['action'] ?? '';
    $input = getJsonInput();
    $orderId = (int) ($input['order_id'] ?? 0);
    $orderRef = trim($input['order_reference'] ?? '');

    if ($orderId <= 0 && empty($orderRef)) {
        jsonResponse(['success' => false, 'error' => 'Order ID or order reference required.'], 400);
    }

    // Locate target order
    if ($orderId > 0) {
        $checkStmt = $db->prepare("SELECT id, queue_number, status, payment_method, order_type FROM orders WHERE id = ?");
        $checkStmt->execute([$orderId]);
    } else {
        $checkStmt = $db->prepare("SELECT id, queue_number, status, payment_method, order_type FROM orders WHERE order_reference = ?");
        $checkStmt->execute([$orderRef]);
    }
    $target = $checkStmt->fetch();

    if (!$target) {
        jsonResponse(['success' => false, 'error' => 'Target order not found.'], 404);
    }

    $targetId = (int) $target['id'];
    $queueNum = $target['queue_number'];

    // Action A: Acknowledge (Move from Pending to In Progress)
    if ($action === 'acknowledge') {
        $updateStmt = $db->prepare("UPDATE orders SET status = 'in_progress' WHERE id = ? AND status = 'pending'");
        $updateStmt->execute([$targetId]);

        jsonResponse([
            'success'      => true,
            'message'      => "Ticket #$queueNum acknowledged and moved to In Progress.",
            'queue_number' => $queueNum,
            'status'       => 'in_progress'
        ]);
    }

    // Action B: Complete & Clear (Enforcing the 3-Point QA Lockout)
    if ($action === 'complete') {
        $payVerified    = !empty($input['qa_payment_verified']);
        $customFollowed = !empty($input['qa_customizations_followed']);
        $packSecured    = !empty($input['qa_packaging_secured']);

        // Strict QA Lockout: Reject completion if ANY checkbox is unchecked
        if (!$payVerified || !$customFollowed || !$packSecured) {
            jsonResponse([
                'success' => false,
                'error'   => 'QA Lockout: You must verify payment, drink customizations, and packaging before completing this order.',
                'qa_status' => [
                    'payment_verified'        => $payVerified,
                    'customizations_followed' => $customFollowed,
                    'packaging_secured'       => $packSecured
                ]
            ], 422);
        }

        // Commit completion & audit stamps
        $updateStmt = $db->prepare("
            UPDATE orders
            SET status = 'completed',
                payment_status = 'verified',
                qa_payment_verified = 1,
                qa_customizations_followed = 1,
                qa_packaging_secured = 1,
                completed_at = NOW()
            WHERE id = ?
        ");
        $updateStmt->execute([$targetId]);

        jsonResponse([
            'success'      => true,
            'message'      => "Ticket #$queueNum completed and cleared from active queue.",
            'queue_number' => $queueNum,
            'status'       => 'completed',
            'completed_at' => date('Y-m-d H:i:s')
        ]);
    }

    jsonResponse(['success' => false, 'error' => 'Invalid action. Supported: acknowledge, complete.'], 400);
}

jsonResponse(['error' => 'Method not allowed.'], 405);
