<?php
/**
 * Suite: Storefront & POS Ordering Buttons
 * Tests order placement button, dining mode switch button, cart validation, and order polling.
 */

TestFramework::registerSuite('Ordering Buttons & Dining Modes', function(SuiteReporter $t) {
    require_once dirname(__DIR__) . '/api/db.php';
    $db = Database::getConnection();

    // Fetch an active menu item to use for the order cart
    $stmt = $db->query("SELECT id, name, price FROM menu_items WHERE is_available = 1 LIMIT 1");
    $item = $stmt->fetch();
    $t->assert('Active menu item available for cart test', !empty($item));

    $client = new TestClient();

    // 1. Empty Cart Button Click
    $res = $client->post('api/orders.php', [
        'customer_name' => 'Test User',
        'customer_phone' => '09123456789',
        'order_type' => 'dine_in',
        'items' => []
    ]);
    $t->assertEquals('Empty cart rejected with 422', 422, $res['status']);
    $t->assert('Cart empty error message returned', !empty($res['json']['error']));

    // 2. Submit Dine-In Order Button
    $dineInPayload = [
        'customer_name' => 'AutoTest DineIn Customer',
        'customer_phone' => '09171234567',
        'order_type' => 'dine_in',
        'table_number' => '4',
        'payment_method' => 'cash',
        'items' => [
            [
                'id' => $item['id'],
                'name' => $item['name'],
                'quantity' => 2,
                'unit_price' => (float)$item['price'],
                'temperature' => 'Iced',
                'milk_option' => 'Regular Milk',
                'sweetness_level' => 'Normal (100%)',
                'custom_notes' => 'TEST_AUTOMATION'
            ]
        ]
    ];

    $res = $client->post('api/orders.php', $dineInPayload);
    $t->assertEquals('Dine-In order placement returns 201 Created', 201, $res['status']);
    $t->assert('Order reference returned', !empty($res['json']['order_reference']));
    $t->assert('Queue number returned', !empty($res['json']['queue_number']));
    $createdRef = $res['json']['order_reference'] ?? null;
    $createdId = null;
    if ($createdRef) {
        $idStmt = $db->prepare("SELECT id FROM orders WHERE order_reference = ?");
        $idStmt->execute([$createdRef]);
        $createdId = (int)$idStmt->fetchColumn();
    }

    // 3. Customer Polls Live Ticket Button / Refresh
    if ($createdRef) {
        $pollRes = $client->get('api/orders.php', ['reference' => $createdRef]);
        $t->assertEquals('Live ticket polling returns 200 OK', 200, $pollRes['status']);
        $t->assertEquals('Order type is dine_in', 'dine_in', $pollRes['json']['order']['order_type'] ?? '');
        $t->assertEquals('Table number matches Table 4', '4', (string)($pollRes['json']['order']['table_number'] ?? ''));

        // 4. Switch Dining Mode Button (Customer accidentally selected dine-in, switches to take-out)
        $switchRes = $client->post('api/orders.php?action=update_dining', [
            'reference' => $createdRef,
            'order_type' => 'take_out'
        ]);
        $t->assertEquals('Switch Dining Mode button returns 200 OK', 200, $switchRes['status']);
        $t->assert('Dining mode update confirmation returned', !empty($switchRes['json']['success']));

        // Verify state change persisted
        $pollRes2 = $client->get('api/orders.php', ['reference' => $createdRef]);
        $t->assertEquals('Updated ticket reflects take_out', 'take_out', $pollRes2['json']['order']['order_type'] ?? '');

        // 5. Automatic Cleanup of Test Order
        if ($createdId) {
            $db->prepare("DELETE FROM order_items WHERE order_id = ?")->execute([$createdId]);
            $db->prepare("DELETE FROM orders WHERE id = ?")->execute([$createdId]);
            $t->pass('Cleaned up test order record from database');
        }
    }
});
