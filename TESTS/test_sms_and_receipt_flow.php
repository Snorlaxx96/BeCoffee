<?php
/**
 * Suite: SMS Notification & Digital Receipt Workflow
 * Tests SmsService dispatching, phone number normalization, KDS completion trigger, and outbox logging.
 */

TestFramework::registerSuite('SMS Alerts & Digital Receipt Workflow', function(SuiteReporter $t) {
    require_once dirname(__DIR__) . '/api/db.php';
    require_once dirname(__DIR__) . '/api/sms.php';
    $db = Database::getConnection();

    // 1. Direct SmsService Unit Test
    $testPhone = '09171234567';
    $testMsg = 'BeCoffee Test Alert: Your artisanal order is ready!';
    $smsRes = SmsService::send($testPhone, $testMsg);
    $t->assert('SmsService::send returns success', !empty($smsRes['success']));
    $t->assert('SmsService logged or sent', !empty($smsRes['mode']));

    $logFile = dirname(__DIR__) . '/scratch/sms_outbox.log';
    $t->assert('scratch/sms_outbox.log file exists', file_exists($logFile));
    $logContent = file_get_contents($logFile);
    $t->assert('Log contains normalized phone (+639171234567)', strpos($logContent, '639171234567') !== false);

    // 2. Create Online Take-out Order with Phone Number
    $stmt = $db->query("SELECT id, name, price FROM menu_items WHERE is_available = 1 LIMIT 1");
    $item = $stmt->fetch();
    $t->assert('Active menu item available', !empty($item));

    $client = new TestClient();
    $onlinePhone = '09988776655';
    $onlinePayload = [
        'customer_name' => 'Maria Takeout',
        'customer_phone' => $onlinePhone,
        'order_type' => 'take_out',
        'table_number' => null,
        'order_source' => 'online',
        'payment_method' => 'gcash',
        'items' => [
            [
                'id' => (string)$item['id'],
                'name' => $item['name'],
                'quantity' => 1,
                'unit_price' => (float)$item['price'],
                'temperature' => 'Iced',
                'milk_option' => 'Oat Milk',
                'sweetness_level' => 'Normal (100%)',
                'custom_notes' => 'Extra ice please'
            ]
        ]
    ];

    $res = $client->post('api/orders.php', $onlinePayload);
    $t->assertEquals('Online takeout order created (201)', 201, $res['status']);
    $orderRef = $res['json']['order_reference'] ?? null;
    $queueNum = $res['json']['queue_number'] ?? null;
    $t->assert('Order reference generated', !empty($orderRef));

    // Get order ID from database
    $stmt = $db->prepare("SELECT id FROM orders WHERE order_reference = ?");
    $stmt->execute([$orderRef]);
    $dbOrd = $stmt->fetch();
    $orderId = (int)($dbOrd['id'] ?? 0);
    $t->assert('Order ID found in DB', $orderId > 0);

    // 3. Staff logs in and fulfills order in KDS
    $staff = new TestClient();
    $t->assert('Staff signs in', $staff->loginAs('staff'));

    // Move to in_progress
    $ackRes = $staff->post('api/kds.php?action=acknowledge', ['order_id' => $orderId]);
    $t->assertEquals('Order acknowledged by barista (200)', 200, $ackRes['status']);

    // Complete order with QA checkpoints
    $completeRes = $staff->post('api/kds.php?action=complete', [
        'order_id' => $orderId,
        'qa_payment_verified' => true,
        'qa_customizations_followed' => true,
        'qa_packaging_secured' => true
    ]);
    $t->assertEquals('Order completed in KDS (200)', 200, $completeRes['status']);

    // 4. Verify SMS Outbox has message for Maria Takeout
    clearstatcache();
    $freshLog = file_get_contents($logFile);
    $t->assert('Outbox contains Maria Takeout alert', strpos($freshLog, '639988776655') !== false);
    $t->assert('Outbox contains queue number in alert', strpos($freshLog, '#' . $queueNum) !== false);
    $t->assert('Outbox contains barista counter instructions', strpos($freshLog, 'barista counter') !== false);

    // 5. Cleanup test order
    $stmt = $db->prepare("SELECT id FROM orders WHERE order_reference = ?");
    $stmt->execute([$orderRef]);
    $dbOrd = $stmt->fetch();
    if (!empty($dbOrd['id'])) {
        $oid = (int)$dbOrd['id'];
        $db->prepare("DELETE FROM order_items WHERE order_id = ?")->execute([$oid]);
        $db->prepare("DELETE FROM orders WHERE id = ?")->execute([$oid]);
    }
});
