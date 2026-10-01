<?php
/**
 * Suite: Walk-in Registrar Order (No Table / No Phone Required)
 * Tests walk-in orders with null table and empty phone number, database persistence, and KDS queue display.
 */

TestFramework::registerSuite('Walk-in Registrar Order (No Table / No Phone)', function(SuiteReporter $t) {
    require_once dirname(__DIR__) . '/api/db.php';
    $db = Database::getConnection();

    // 1. Fetch an active menu item
    $stmt = $db->query("SELECT id, name, price FROM menu_items WHERE is_available = 1 LIMIT 1");
    $item = $stmt->fetch();
    $t->assert('Active menu item available for test', !empty($item));

    $client = new TestClient();

    // 2. Submit Walk-in Registrar Order without table number and without phone number
    $payload = [
        'customer_name' => 'John Walkin',
        'customer_phone' => '', // blank phone
        'order_type' => 'dine_in',
        'table_number' => null, // null table
        'order_source' => 'registrar',
        'payment_method' => 'cash',
        'items' => [
            [
                'id' => (string)$item['id'],
                'name' => $item['name'],
                'quantity' => 1,
                'unit_price' => (float)$item['price'],
                'temperature' => 'Hot',
                'milk_option' => 'Regular Milk',
                'sweetness_level' => 'Normal (100%)',
                'custom_notes' => 'Registrar counter walk-in test'
            ]
        ]
    ];

    $res = $client->post('api/orders.php', $payload);
    $t->assertEquals('Registrar walk-in order created without table & phone (201)', 201, $res['status']);
    $t->assert('Response success is true', !empty($res['json']['success']));
    $t->assert('Order reference generated', !empty($res['json']['order_reference']));
    $orderRef = $res['json']['order_reference'] ?? null;

    // 3. Verify in database
    $stmt = $db->prepare("SELECT id, queue_number, order_source, table_number, customer_name, customer_phone, order_type FROM orders WHERE order_reference = ?");
    $stmt->execute([$orderRef]);
    $dbOrder = $stmt->fetch();
    $t->assert('Order found in database', !empty($dbOrder));
    $t->assertEquals('Order source is registrar', 'registrar', $dbOrder['order_source'] ?? null);
    $t->assert('table_number in DB is NULL for registrar walk-in', is_null($dbOrder['table_number']));
    $t->assertEquals('customer_phone in DB is empty string for registrar walk-in', '', $dbOrder['customer_phone'] ?? null);
    $t->assertEquals('Customer name is John Walkin', 'John Walkin', $dbOrder['customer_name'] ?? null);

    // 4. Staff logs in and fetches KDS queue
    $staff = new TestClient();
    $t->assert('Staff signs in successfully', $staff->loginAs('staff'));

    $kdsRes = $staff->get('api/kds.php');
    $t->assertEquals('Staff can fetch KDS queue (200 OK)', 200, $kdsRes['status']);
    $found = false;
    if (!empty($kdsRes['json']['pending'])) {
        foreach ($kdsRes['json']['pending'] as $p) {
            if ($p['order_reference'] === $orderRef) {
                $found = true;
                $t->assert('KDS pending order table_number is null', $p['table_number'] === null);
                $t->assertEquals('KDS pending order source is registrar', 'registrar', $p['order_source']);
                $t->assertEquals('KDS pending customer name is John Walkin', 'John Walkin', $p['customer_name']);
                break;
            }
        }
    }
    $t->assert('Registrar order appears in KDS pending queue', $found);

    // 5. Cleanup test order
    if (!empty($dbOrder['id'])) {
        $orderId = (int)$dbOrder['id'];
        $db->prepare("DELETE FROM order_items WHERE order_id = ?")->execute([$orderId]);
        $db->prepare("DELETE FROM orders WHERE id = ?")->execute([$orderId]);
    }
});
