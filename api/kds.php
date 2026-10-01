<?php
/**
 * BeCoffee — Kitchen Display System (KDS) Controller
 * Serves the Staff Tablet board, manages station routing, batch counts,
 * fast 1-tap bumps, order acknowledgments, QA lockouts, ticket recalls, and voiding.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

$method = $_SERVER['REQUEST_METHOD'];
requireRole(['staff', 'admin', 'superadmin']);

$db = Database::getConnection();

function classifyItemStation(string $categorySlug, string $itemName): string {
    $cat = strtolower($categorySlug);
    $name = strtolower($itemName);
    $isFood = in_array($cat, ['food', 'pastries', 'bakery', 'sandwiches', 'pastry']) ||
              preg_match('/(panini|sandwich|croissant|pastry|toast|cookie|muffin|bread|cake|waffle|bagel)/i', $name);
    return $isFood ? 'kitchen' : 'barista';
}

function classifyOrderStation(array $items): string {
    $hasBarista = false;
    $hasKitchen = false;
    foreach ($items as $item) {
        if (($item['station'] ?? '') === 'kitchen') {
            $hasKitchen = true;
        } else {
            $hasBarista = true;
        }
    }
    if ($hasBarista && $hasKitchen) return 'both';
    if ($hasKitchen) return 'kitchen';
    return 'barista';
}

function resolveOrderOrigin(?string $source, ?string $orderType, ?string $tableNumber): array {
    $src = strtolower(trim((string)$source));
    if ($src === 'online') {
        return [
            'source' => 'online',
            'label'  => 'ONLINE',
            'badge'  => 'ONLINE'
        ];
    }
    if ($src === 'registrar') {
        $detail = !empty($tableNumber) ? "REGISTRAR · T#{$tableNumber}" : "REGISTRAR";
        return [
            'source' => 'registrar',
            'label'  => 'REGISTRAR',
            'badge'  => $detail
        ];
    }
    if ($src === 'qr_link') {
        $detail = !empty($tableNumber) ? "QR / LINK · T#{$tableNumber}" : "QR / LINK";
        return [
            'source' => 'qr_link',
            'label'  => 'QR / LINK',
            'badge'  => $detail
        ];
    }
    if ($orderType === 'take_out') {
        return [
            'source' => 'online',
            'label'  => 'ONLINE',
            'badge'  => 'ONLINE'
        ];
    }
    $detail = !empty($tableNumber) ? "QR / LINK · T#{$tableNumber}" : "QR / LINK";
    return [
        'source' => 'qr_link',
        'label'  => 'QR / LINK',
        'badge'  => $detail
    ];
}

// --- 1. GET: Kitchen Queue & Recent History ---
if ($method === 'GET') {
    $view = $_GET['view'] ?? 'active';

    if ($view === 'history') {
        $histStmt = $db->prepare("
            SELECT id, queue_number, order_reference, order_type, table_number, order_source, payment_method, payment_status,
                   customer_name, customer_phone, customer_notes, subtotal, eco_fee, grand_total, status,
                   qa_payment_verified, qa_customizations_followed, qa_packaging_secured,
                   created_at, completed_at,
                   TIMESTAMPDIFF(SECOND, created_at, NOW()) AS elapsed_seconds
            FROM orders
            WHERE status IN ('completed', 'cancelled')
            ORDER BY completed_at DESC, id DESC
            LIMIT 30
        ");
        $histStmt->execute();
        $historyOrders = $histStmt->fetchAll();

        $itemStmt = $db->prepare("
            SELECT oi.id, oi.order_id, oi.item_id, oi.item_name, oi.temperature, oi.milk_option, oi.sweetness_level,
                   oi.custom_notes, oi.quantity, oi.unit_price, oi.subtotal,
                   COALESCE(c.slug, 'house-coffee') AS category_slug,
                   COALESCE(c.name, 'House Coffee') AS category_name
            FROM order_items oi
            LEFT JOIN menu_items m ON oi.item_id = m.id
            LEFT JOIN categories c ON m.category_id = c.id
            WHERE oi.order_id = ?
        ");

        $completed = [];
        $cancelled = [];

        foreach ($historyOrders as $ord) {
            $itemStmt->execute([$ord['id']]);
            $rawItems = $itemStmt->fetchAll();
            foreach ($rawItems as &$it) {
                $it['station'] = classifyItemStation($it['category_slug'] ?? '', $it['item_name'] ?? '');
            }
            $ord['items'] = $rawItems;
            $ord['station'] = classifyOrderStation($rawItems);

            $origin = resolveOrderOrigin($ord['order_source'] ?? null, $ord['order_type'] ?? null, $ord['table_number'] ?? null);
            $ord['order_source'] = $origin['source'];
            $ord['order_source_label'] = $origin['label'];
            $ord['order_source_badge'] = $origin['badge'];

            if ($ord['status'] === 'completed') {
                $completed[] = $ord;
            } else {
                $cancelled[] = $ord;
            }
        }

        jsonResponse([
            'success'     => true,
            'completed'   => $completed,
            'cancelled'   => $cancelled,
            'server_time' => time()
        ]);
    }

    // Default: Active Queue (Pending & In Progress)
    $stmt = $db->prepare("
        SELECT id, queue_number, order_reference, order_type, table_number, order_source, payment_method, payment_status,
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

    // Fetch line items for each active ticket with category classification
    $itemStmt = $db->prepare("
        SELECT oi.id, oi.order_id, oi.item_id, oi.item_name, oi.temperature, oi.milk_option, oi.sweetness_level,
               oi.custom_notes, oi.quantity, oi.unit_price, oi.subtotal,
               COALESCE(c.slug, 'house-coffee') AS category_slug,
               COALESCE(c.name, 'House Coffee') AS category_name
        FROM order_items oi
        LEFT JOIN menu_items m ON oi.item_id = m.id
        LEFT JOIN categories c ON m.category_id = c.id
        WHERE oi.order_id = ?
    ");

    $pending = [];
    $inProgress = [];

    foreach ($rawOrders as $ord) {
        $itemStmt->execute([$ord['id']]);
        $rawItems = $itemStmt->fetchAll();

        foreach ($rawItems as &$it) {
            $it['station'] = classifyItemStation($it['category_slug'] ?? '', $it['item_name'] ?? '');
        }
        $ord['items'] = $rawItems;
        $ord['station'] = classifyOrderStation($rawItems);

        $origin = resolveOrderOrigin($ord['order_source'] ?? null, $ord['order_type'] ?? null, $ord['table_number'] ?? null);
        $ord['order_source'] = $origin['source'];
        $ord['order_source_label'] = $origin['label'];
        $ord['order_source_badge'] = $origin['badge'];

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

    // Also fetch last 10 completed/cancelled for instant Recall ribbon
    $recentStmt = $db->prepare("
        SELECT id, queue_number, order_reference, customer_name, status, completed_at, customer_notes,
               TIMESTAMPDIFF(SECOND, completed_at, NOW()) AS completed_ago_seconds
        FROM orders
        WHERE status IN ('completed', 'cancelled')
        ORDER BY completed_at DESC, id DESC
        LIMIT 10
    ");
    $recentStmt->execute();
    $recentHistory = $recentStmt->fetchAll();

    jsonResponse([
        'success'          => true,
        'server_time'      => time(),
        'counts'           => [
            'pending'     => count($pending),
            'in_progress' => count($inProgress),
            'total'       => count($rawOrders)
        ],
        'pending'          => $pending,
        'in_progress'      => $inProgress,
        'recent_history'   => $recentHistory
    ]);
}

// --- 2. PATCH / POST: State Transitions & Actions ---
if ($method === 'PATCH' || $method === 'POST') {
    $action = $_GET['action'] ?? '';
    $input = getJsonInput();

    // Action 0: Bulk Queue Wipe (Manager PIN Protected)
    if ($action === 'clear_all') {
        $pin = trim((string)($input['pin'] ?? ''));
        $user = getAuthenticatedUser();
        $isAdmin = $user && in_array($user['role'], ['admin', 'superadmin'], true);

        if ($pin !== '1234' && !$isAdmin) {
            jsonResponse(['success' => false, 'error' => 'Invalid Manager PIN. Supervisor authorization required to clear active queue.'], 403);
        }

        $clearStmt = $db->prepare("
            UPDATE orders
            SET status = 'cancelled',
                customer_notes = CASE 
                    WHEN customer_notes IS NULL OR customer_notes = '' THEN '[VOID: Bulk Manager Reset]'
                    ELSE CONCAT(customer_notes, ' | [VOID: Bulk Manager Reset]')
                END,
                completed_at = NOW()
            WHERE status IN ('pending', 'in_progress')
        ");
        $clearStmt->execute();
        $wipedCount = $clearStmt->rowCount();

        jsonResponse([
            'success'     => true,
            'message'     => "Active queue cleared ($wipedCount orders voided via Supervisor PIN).",
            'wiped_count' => $wipedCount
        ]);
    }

    $orderId = (int) ($input['order_id'] ?? ($_GET['order_id'] ?? ($_POST['order_id'] ?? 0)));
    $orderRef = trim($input['order_reference'] ?? ($_GET['order_reference'] ?? ($_POST['order_reference'] ?? '')));

    if ($orderId <= 0 && empty($orderRef)) {
        jsonResponse(['success' => false, 'error' => 'Order ID or order reference required.'], 400);
    }

    // Locate target order
    if ($orderId > 0) {
        $checkStmt = $db->prepare("SELECT id, queue_number, status, payment_method, order_type, order_source, customer_name, customer_phone, customer_notes FROM orders WHERE id = ?");
        $checkStmt->execute([$orderId]);
    } else {
        $checkStmt = $db->prepare("SELECT id, queue_number, status, payment_method, order_type, order_source, customer_name, customer_phone, customer_notes FROM orders WHERE order_reference = ?");
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

    // Action B: Complete & Clear (Enforcing QA Lockout or Fast Bump)
    if ($action === 'complete') {
        $isFastBump = !empty($input['fast_bump']) || ($input['bump_mode'] ?? '') === 'fast';
        $payVerified    = !empty($input['qa_payment_verified']) || $isFastBump;
        $customFollowed = !empty($input['qa_customizations_followed']) || $isFastBump;
        $packSecured    = !empty($input['qa_packaging_secured']) || $isFastBump;

        // Strict QA Lockout: Reject completion if ANY checkbox is unchecked and not fast bump
        if (!$isFastBump && (!$payVerified || !$customFollowed || !$packSecured)) {
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

        // Dispatch SMS notification for online takeout orders with valid phone numbers
        require_once __DIR__ . '/sms.php';
        $smsResult = null;
        if (!empty($target['customer_phone']) && ($target['order_source'] === 'online' || $target['order_type'] === 'take_out')) {
            $smsResult = SmsService::notifyOrderReady($target);
        }

        jsonResponse([
            'success'      => true,
            'message'      => "Ticket #$queueNum completed and cleared from active queue.",
            'queue_number' => $queueNum,
            'status'       => 'completed',
            'completed_at' => date('Y-m-d H:i:s'),
            'fast_bump'    => $isFastBump,
            'sms_status'   => $smsResult
        ]);
    }

    // Action C: Recall Ticket (Revert from Completed or Cancelled back to In Progress)
    if ($action === 'recall') {
        $updateStmt = $db->prepare("
            UPDATE orders
            SET status = 'in_progress',
                completed_at = NULL
            WHERE id = ?
        ");
        $updateStmt->execute([$targetId]);

        jsonResponse([
            'success'      => true,
            'message'      => "Ticket #$queueNum recalled back to In Progress queue.",
            'queue_number' => $queueNum,
            'status'       => 'in_progress'
        ]);
    }

    // Action D: Cancel / Void Single Ticket with Reason Prompt
    if ($action === 'cancel' || $action === 'void') {
        $reason = trim($input['reason'] ?? 'Staff Cancelled');
        if (empty($reason)) $reason = 'Staff Cancelled';

        $voidTag = "[VOID: $reason]";
        $updateStmt = $db->prepare("
            UPDATE orders
            SET status = 'cancelled',
                customer_notes = CASE 
                    WHEN customer_notes IS NULL OR customer_notes = '' THEN ?
                    ELSE CONCAT(customer_notes, ' | ', ?)
                END,
                completed_at = NOW()
            WHERE id = ?
        ");
        $updateStmt->execute([$voidTag, $voidTag, $targetId]);

        jsonResponse([
            'success'      => true,
            'message'      => "Ticket #$queueNum voided: $reason.",
            'queue_number' => $queueNum,
            'status'       => 'cancelled',
            'reason'       => $reason,
            'completed_at' => date('Y-m-d H:i:s')
        ]);
    }

    jsonResponse(['success' => false, 'error' => 'Invalid action. Supported: acknowledge, complete, recall, cancel, void, clear_all.'], 400);
}

jsonResponse(['error' => 'Method not allowed.'], 405);
