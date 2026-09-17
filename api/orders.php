<?php
/**
 * BeCoffee — Orders Controller (REST API)
 * Processes checkout transactions with database-verified pricing and optional member link
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

$method = $_SERVER['REQUEST_METHOD'];
$db = Database::getConnection();

// --- 1. Get Member Orders (GET) ---
if ($method === 'GET') {
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        jsonResponse(['success' => false, 'error' => 'Authentication required to view order history.'], 401);
    }

    $stmt = $db->prepare("
        SELECT id, order_reference, customer_name, customer_phone, subtotal, eco_fee, grand_total, status, created_at
        FROM orders
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT 20
    ");
    $stmt->execute([$userId]);
    $orders = $stmt->fetchAll();

    // Fetch line items for each order
    $itemStmt = $db->prepare("SELECT order_id, item_id, item_name, quantity, unit_price, subtotal FROM order_items WHERE order_id = ?");
    foreach ($orders as &$ord) {
        $itemStmt->execute([$ord['id']]);
        $ord['items'] = $itemStmt->fetchAll();
    }

    jsonResponse([
        'success' => true,
        'orders'  => $orders
    ]);
}

// --- 2. Create Order (POST) ---
if ($method === 'POST') {
    $input = getJsonInput();

    $items = $input['items'] ?? [];
    if (empty($items) || !is_array($items)) {
        jsonResponse(['success' => false, 'error' => 'Your order cart is empty.'], 422);
    }

    $customerName  = trim($input['customer_name'] ?? '');
    $customerPhone = trim($input['customer_phone'] ?? '');
    $customerNotes = trim($input['customer_notes'] ?? '');

    // Authentication required: guest ordering is disabled
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        jsonResponse([
            'success' => false,
            'error'   => 'Account required. Please sign in or create an account to complete your order.'
        ], 401);
    }

    // Fetch user details from database
    $userStmt = $db->prepare("SELECT name, phone FROM users WHERE id = ?");
    $userStmt->execute([$userId]);
    $user = $userStmt->fetch();
    if (!$user) {
        jsonResponse([
            'success' => false,
            'error'   => 'User account not found. Please sign in again.'
        ], 401);
    }

    if (empty($customerName)) {
        $customerName = $user['name'];
    }
    if (empty($customerPhone)) {
        $customerPhone = !empty($user['phone']) ? $user['phone'] : '+63 900 000 0000';
    }

    // Begin Database Transaction for Atomic Order Placement
    $db->beginTransaction();

    try {
        // Anti-Tampering: Query real price of each item from database
        $itemIds = array_map(fn($it) => $it['id'] ?? '', $items);
        $placeholders = str_repeat('?,', count($itemIds) - 1) . '?';

        $priceStmt = $db->prepare("SELECT id, name, price, price_iced_m, price_iced_l, price_hot FROM menu_items WHERE id IN ($placeholders) AND is_available = TRUE");
        $priceStmt->execute($itemIds);
        $dbItems = $priceStmt->fetchAll();

        $dbMap = [];
        foreach ($dbItems as $dbi) {
            $dbMap[$dbi['id']] = $dbi;
        }

        $subtotal = 0.0;
        $validatedLineItems = [];

        foreach ($items as $it) {
            $itemId = $it['id'] ?? '';
            $qty = max(1, (int) ($it['quantity'] ?? 1));
            $temp = ($it['temperature'] ?? '') === 'Hot' ? 'Hot' : 'Iced';
            $size = ($it['size'] ?? '') === 'Medium' ? 'Medium' : 'Small';

            if (!isset($dbMap[$itemId])) {
                throw new Exception("One or more items in your cart are no longer available.");
            }

            $dbItem = $dbMap[$itemId];
            $unitPrice = ($size === 'Medium')
                ? ((float)($dbItem['price_iced_l'] ?? ($dbItem['price'] + 20)))
                : ((float)($dbItem['price_iced_m'] ?? $dbItem['price']));

            $lineSubtotal = $unitPrice * $qty;
            $subtotal += $lineSubtotal;

            $displayName = $dbItem['name'] . " ($temp · $size)";

            $validatedLineItems[] = [
                'item_id'    => $itemId,
                'item_name'  => $displayName,
                'quantity'   => $qty,
                'unit_price' => $unitPrice,
                'subtotal'   => $lineSubtotal
            ];
        }

        $ecoFee = 25.00;
        $grandTotal = $subtotal + $ecoFee;

        // Generate clean unique order reference
        $orderRef = 'BC-' . date('ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));

        // Insert Order Record
        $orderStmt = $db->prepare("
            INSERT INTO orders (order_reference, user_id, customer_name, customer_phone, customer_notes, subtotal, eco_fee, grand_total, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'confirmed')
        ");
        $orderStmt->execute([
            $orderRef,
            $userId,
            $customerName,
            $customerPhone,
            $customerNotes ?: null,
            $subtotal,
            $ecoFee,
            $grandTotal
        ]);

        $orderId = (int) $db->lastInsertId();

        // Insert Line Items
        $lineStmt = $db->prepare("
            INSERT INTO order_items (order_id, item_id, item_name, quantity, unit_price, subtotal)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        foreach ($validatedLineItems as $line) {
            $lineStmt->execute([
                $orderId,
                $line['item_id'],
                $line['item_name'],
                $line['quantity'],
                $line['unit_price'],
                $line['subtotal']
            ]);
        }

        // Commit transaction
        $db->commit();

        jsonResponse([
            'success'         => true,
            'message'         => 'Order successfully placed!',
            'order_reference' => $orderRef,
            'subtotal'        => $subtotal,
            'eco_fee'         => $ecoFee,
            'grand_total'     => $grandTotal,
            'status'          => 'confirmed'
        ], 201);

    } catch (Exception $e) {
        $db->rollBack();
        jsonResponse([
            'success' => false,
            'error'   => $e->getMessage() ?: 'An error occurred while creating your order.'
        ], 400);
    }
}

jsonResponse(['error' => 'Method not allowed.'], 405);
