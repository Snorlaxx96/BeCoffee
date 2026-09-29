<?php
/**
 * Escobar Cafe / BeCoffee — Orders Controller (REST API)
 * Handles customer order placement, customization specs, daily queue generation, and live ticket polling
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

$method = $_SERVER['REQUEST_METHOD'];
$db = Database::getConnection();

// --- 1. GET: Poll Single Order by Reference OR Fetch Member History ---
if ($method === 'GET') {
    $ref = trim($_GET['reference'] ?? '');

    // Case A: Customer Live Order Ticket Polling (by Order Reference)
    if (!empty($ref)) {
        $stmt = $db->prepare("
            SELECT id, queue_number, order_reference, order_type, table_number, payment_method, 
                   payment_status, customer_name, customer_phone, customer_notes,
                   subtotal, eco_fee, grand_total, status, created_at, completed_at,
                   TIMESTAMPDIFF(SECOND, created_at, NOW()) AS elapsed_seconds
            FROM orders
            WHERE order_reference = ?
            LIMIT 1
        ");
        $stmt->execute([$ref]);
        $order = $stmt->fetch();

        if (!$order) {
            jsonResponse(['success' => false, 'error' => 'Order reference not found.'], 404);
        }

        // Fetch line items with customization specs
        $itemStmt = $db->prepare("
            SELECT id, item_id, item_name, temperature, milk_option, sweetness_level, custom_notes, quantity, unit_price, subtotal
            FROM order_items
            WHERE order_id = ?
        ");
        $itemStmt->execute([$order['id']]);
        $order['items'] = $itemStmt->fetchAll();

        // Calculate queue position: how many orders are ahead in pending/in_progress
        $aheadStmt = $db->prepare("
            SELECT COUNT(*) AS orders_ahead
            FROM orders
            WHERE status IN ('pending', 'in_progress')
              AND id < ?
              AND DATE(created_at) = DATE(?)
        ");
        $aheadStmt->execute([$order['id'], $order['created_at']]);
        $aheadRow = $aheadStmt->fetch();
        $order['orders_ahead'] = (int) ($aheadRow['orders_ahead'] ?? 0);

        jsonResponse([
            'success' => true,
            'order'   => $order
        ]);
    }

    // Case B: Authenticated Member Orders History
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        jsonResponse(['success' => false, 'error' => 'Authentication or order reference required.'], 401);
    }

    $stmt = $db->prepare("
        SELECT id, queue_number, order_reference, order_type, table_number, payment_method, payment_status,
               customer_name, customer_phone, subtotal, eco_fee, grand_total, status, created_at, completed_at
        FROM orders
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT 20
    ");
    $stmt->execute([$userId]);
    $orders = $stmt->fetchAll();

    $itemStmt = $db->prepare("SELECT id, item_id, item_name, temperature, milk_option, sweetness_level, custom_notes, quantity, unit_price, subtotal FROM order_items WHERE order_id = ?");
    foreach ($orders as &$ord) {
        $itemStmt->execute([$ord['id']]);
        $ord['items'] = $itemStmt->fetchAll();
    }

    jsonResponse([
        'success' => true,
        'orders'  => $orders
    ]);
}

// --- 2. POST: Create New Cafe Order OR Update Dining Mode ---
if ($method === 'POST') {
    $input = getJsonInput();
    $action = $_GET['action'] ?? '';

    // Sub-action: Update dining mode for an active ticket (e.g. customer accidentally chose take-out)
    if ($action === 'update_dining') {
        $ref = trim($input['reference'] ?? '');
        $orderType = in_array($input['order_type'] ?? '', ['dine_in', 'take_out'], true) ? $input['order_type'] : 'dine_in';
        $tableNumber = ($orderType === 'dine_in') ? trim($input['table_number'] ?? '1') : null;

        if (empty($ref)) {
            jsonResponse(['success' => false, 'error' => 'Order reference is required.'], 400);
        }

        $chk = $db->prepare("SELECT id, status FROM orders WHERE order_reference = ?");
        $chk->execute([$ref]);
        $existing = $chk->fetch();
        if (!$existing) {
            jsonResponse(['success' => false, 'error' => 'Order not found.'], 404);
        }
        if ($existing['status'] === 'completed' || $existing['status'] === 'cancelled') {
            jsonResponse(['success' => false, 'error' => 'Order is already ' . $existing['status'] . ' and cannot be changed.'], 422);
        }

        $upd = $db->prepare("UPDATE orders SET order_type = ?, table_number = ? WHERE id = ?");
        $upd->execute([$orderType, $tableNumber, $existing['id']]);

        jsonResponse([
            'success'      => true,
            'message'      => 'Dining preference updated to ' . ($orderType === 'dine_in' ? 'Dine-in (Table #' . ($tableNumber ?: '1') . ')' : 'Take-out') . '.',
            'order_type'   => $orderType,
            'table_number' => $tableNumber
        ]);
    }

    $items = $input['items'] ?? [];
    if (empty($items) || !is_array($items)) {
        jsonResponse(['success' => false, 'error' => 'Your order cart is empty.'], 422);
    }

    $customerName  = trim($input['customer_name'] ?? '');
    $customerPhone = trim($input['customer_phone'] ?? '');
    $customerNotes = trim($input['customer_notes'] ?? '');
    $orderType     = in_array($input['order_type'] ?? '', ['dine_in', 'take_out'], true) ? $input['order_type'] : 'dine_in';
    $tableNumber   = ($orderType === 'dine_in') ? trim($input['table_number'] ?? '1') : null;
    $paymentMethod = in_array($input['payment_method'] ?? '', ['cash', 'gcash'], true) ? $input['payment_method'] : 'cash';

    // Guest Checkout or Authenticated User Association
    $userId = $_SESSION['user_id'] ?? null;
    if ($userId) {
        $userStmt = $db->prepare("SELECT name, phone FROM users WHERE id = ?");
        $userStmt->execute([$userId]);
        $user = $userStmt->fetch();
        if ($user) {
            if (empty($customerName)) {
                $customerName = $user['name'];
            }
            if (empty($customerPhone) && !empty($user['phone'])) {
                $customerPhone = $user['phone'];
            }
        }
    }

    if (empty($customerName)) {
        $customerName = 'Guest Customer';
    }
    if (empty($customerPhone)) {
        $customerPhone = '+63 900 000 0000';
    }

    $db->beginTransaction();

    try {
        // Anti-Tampering: Fetch base prices from database
        $itemIds = array_unique(array_map(fn($it) => $it['id'] ?? '', $items));
        $placeholders = str_repeat('?,', count($itemIds) - 1) . '?';

        $priceStmt = $db->prepare("SELECT id, name, price, price_iced_m, price_iced_l, price_hot FROM menu_items WHERE id IN ($placeholders) AND is_available = TRUE");
        $priceStmt->execute(array_values($itemIds));
        $dbItems = $priceStmt->fetchAll();

        $dbMap = [];
        foreach ($dbItems as $dbi) {
            $dbMap[$dbi['id']] = $dbi;
        }

        $subtotal = 0.0;
        $validatedLineItems = [];

        // Check against disabled / sold-out options
        $optCheckStmt = $db->query("SELECT option_key FROM option_availability WHERE is_available = 0");
        $disabledOptions = $optCheckStmt ? $optCheckStmt->fetchAll(PDO::FETCH_COLUMN) : [];
        $disabledSet = array_flip($disabledOptions);

        // Fetch dynamic surcharges from option_availability
        $surchargeStmt = $db->query("SELECT option_key, surcharge FROM option_availability WHERE surcharge > 0");
        $dbSurcharges = $surchargeStmt ? $surchargeStmt->fetchAll(PDO::FETCH_KEY_PAIR) : [];

        foreach ($items as $it) {
            $itemId = $it['id'] ?? '';
            $qty = max(1, (int) ($it['quantity'] ?? 1));
            $rawSize = $it['size'] ?? 'Medium';
            $size = ($rawSize === 'Large') ? 'Large' : 'Medium';
            $temp = in_array($it['temperature'] ?? '', ['Hot', 'Iced'], true) ? $it['temperature'] : 'Iced';
            $milk = trim($it['milk_option'] ?? 'Regular Milk');
            $sweetness = trim($it['sweetness_level'] ?? 'Normal (100%)');
            $notes = trim($it['custom_notes'] ?? '');

            if (isset($disabledSet[$temp])) {
                throw new Exception("Temperature option '{$temp}' is currently sold out.");
            }
            if (isset($disabledSet[$milk])) {
                throw new Exception("Option / Add-on '{$milk}' is currently sold out.");
            }
            if (isset($disabledSet[$sweetness])) {
                throw new Exception("Sweetness option '{$sweetness}' is currently sold out.");
            }

            if (!isset($dbMap[$itemId])) {
                throw new Exception("One or more items in your cart are currently unavailable.");
            }

            $dbItem = $dbMap[$itemId];

            // Base price calculation by size and temperature
            if ($temp === 'Hot' && !empty($dbItem['price_hot'])) {
                $baseUnit = (float) $dbItem['price_hot'];
            } else {
                $baseUnit = ($size === 'Large')
                    ? ((float) ($dbItem['price_iced_l'] ?? ($dbItem['price'] + 20)))
                    : ((float) ($dbItem['price_iced_m'] ?? $dbItem['price']));
            }

            // Dynamic Add-on / Milk surcharge lookup
            $addonSurcharge = (float)($dbSurcharges[$milk] ?? 0.00);
            $unitPrice = $baseUnit + $addonSurcharge;

            $lineSubtotal = $unitPrice * $qty;
            $subtotal += $lineSubtotal;

            $displayName = $dbItem['name'] . " ($temp · $size)";

            $validatedLineItems[] = [
                'item_id'         => $itemId,
                'item_name'       => $displayName,
                'temperature'     => $temp,
                'milk_option'     => $milk,
                'sweetness_level' => $sweetness,
                'custom_notes'    => $notes,
                'quantity'        => $qty,
                'unit_price'      => $unitPrice,
                'subtotal'        => $lineSubtotal
            ];
        }

        // Eco packaging fee only applies to take-out orders
        $ecoFee = ($orderType === 'take_out') ? 15.00 : 0.00;
        $grandTotal = $subtotal + $ecoFee;

        // Generate Daily Sequential Queue Number (#101, #102...)
        $queueStmt = $db->query("SELECT COALESCE(MAX(queue_number), 100) + 1 AS next_queue FROM orders WHERE DATE(created_at) = CURDATE()");
        $queueRow = $queueStmt->fetch();
        $queueNumber = (int) ($queueRow['next_queue'] ?? 101);

        // Unique order reference (e.g., BC-260929-A1B2C)
        $orderRef = 'BC-' . date('ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));

        // Insert Order Record
        $orderStmt = $db->prepare("
            INSERT INTO orders (
                queue_number, order_reference, order_type, table_number, payment_method, payment_status,
                user_id, customer_name, customer_phone, customer_notes,
                subtotal, eco_fee, grand_total, status, created_at
            ) VALUES (?, ?, ?, ?, ?, 'unpaid', ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
        ");
        $orderStmt->execute([
            $queueNumber,
            $orderRef,
            $orderType,
            $tableNumber,
            $paymentMethod,
            $userId,
            $customerName,
            $customerPhone,
            $customerNotes ?: null,
            $subtotal,
            $ecoFee,
            $grandTotal
        ]);

        $orderId = (int) $db->lastInsertId();

        // Insert Line Items with Drink Customizations
        $lineStmt = $db->prepare("
            INSERT INTO order_items (
                order_id, item_id, item_name, temperature, milk_option, sweetness_level, custom_notes,
                quantity, unit_price, subtotal
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        foreach ($validatedLineItems as $line) {
            $lineStmt->execute([
                $orderId,
                $line['item_id'],
                $line['item_name'],
                $line['temperature'],
                $line['milk_option'],
                $line['sweetness_level'],
                $line['custom_notes'] ?: null,
                $line['quantity'],
                $line['unit_price'],
                $line['subtotal']
            ]);
        }

        $db->commit();

        jsonResponse([
            'success'         => true,
            'message'         => 'Order successfully placed!',
            'order_reference' => $orderRef,
            'queue_number'    => $queueNumber,
            'order_type'      => $orderType,
            'table_number'    => $tableNumber,
            'payment_method'  => $paymentMethod,
            'subtotal'        => $subtotal,
            'eco_fee'         => $ecoFee,
            'grand_total'     => $grandTotal,
            'status'          => 'pending',
            'created_at'      => date('Y-m-d H:i:s')
        ], 201);

    } catch (Exception $e) {
        $db->rollBack();
        jsonResponse([
            'success' => false,
            'error'   => $e->getMessage() ?: 'An error occurred while placing your order.'
        ], 400);
    }
}

jsonResponse(['error' => 'Method not allowed.'], 405);
