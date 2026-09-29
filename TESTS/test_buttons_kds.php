<?php
/**
 * Suite: KDS Kitchen Buttons & QA Lockout Verification
 * Tests Kitchen queue polling, Acknowledge button, Complete button with strict 3-point QA Lockout.
 */

TestFramework::registerSuite('KDS Kitchen Workflow & QA Lockout', function(SuiteReporter $t) {
    require_once dirname(__DIR__) . '/api/db.php';
    $db = Database::getConnection();

    // 1. Unauthenticated or Customer blocked from KDS API
    $guest = new TestClient();
    $res = $guest->get('api/kds.php');
    $t->assert('Guest blocked from api/kds.php (401 or 403)', in_array($res['status'], [401, 403], true));

    // 2. Staff Authentication
    $staff = new TestClient();
    $t->assert('Staff signs in', $staff->loginAs('staff'));

    // Create a temporary mock order in pending status
    $ref = 'TEST_KDS_' . uniqid();
    $ins = $db->prepare("
        INSERT INTO orders (order_reference, queue_number, customer_name, order_type, payment_method, payment_status, subtotal, eco_fee, grand_total, status)
        VALUES (?, 'K-99', 'Auto KDS Tester', 'dine_in', 'cash', 'pending', 180.00, 5.00, 185.00, 'pending')
    ");
    $ins->execute([$ref]);
    $orderId = (int)$db->lastInsertId();

    try {
        // 3. Live Kitchen Queue Fetch
        $queueRes = $staff->get('api/kds.php');
        $t->assertEquals('Staff fetches live queue with 200 OK', 200, $queueRes['status']);
        $t->assert('Live queue has pending list', isset($queueRes['json']['pending']));

        // 4. "Acknowledge" Button (Pending -> In Progress)
        $ackRes = $staff->post('api/kds.php?action=acknowledge', [
            'order_id' => $orderId
        ]);
        $t->assertEquals('Acknowledge button moves ticket to in_progress', 200, $ackRes['status']);
        $t->assertEquals('Ticket status is now in_progress', 'in_progress', $ackRes['json']['status'] ?? '');

        // 5. "Complete" Button without QA Checklist (Strict 3-point QA Lockout enforcement)
        $incompleteRes = $staff->post('api/kds.php?action=complete', [
            'order_id' => $orderId,
            'qa_payment_verified' => true,
            'qa_customizations_followed' => false, // missing!
            'qa_packaging_secured' => true
        ]);
        $t->assertEquals('QA Lockout rejects completion with 422', 422, $incompleteRes['status']);
        $t->assert('QA Lockout warning returned', strpos($incompleteRes['json']['error'] ?? '', 'QA Lockout') !== false);

        // 6. "Complete" Button with ALL 3 QA Checkpoints Checked
        $completeRes = $staff->post('api/kds.php?action=complete', [
            'order_id' => $orderId,
            'qa_payment_verified' => true,
            'qa_customizations_followed' => true,
            'qa_packaging_secured' => true
        ]);
        $t->assertEquals('Complete button with full QA clears ticket (200 OK)', 200, $completeRes['status']);
        $t->assertEquals('Ticket status is completed', 'completed', $completeRes['json']['status'] ?? '');

        // 7. Verify cleared from active pending/in_progress queue
        $queueAfter = $staff->get('api/kds.php');
        $inProgIds = array_column($queueAfter['json']['in_progress'] ?? [], 'id');
        $t->assert('Completed ticket no longer in active kitchen queue', !in_array($orderId, $inProgIds));

    } finally {
        // Automatic cleanup
        $db->prepare("DELETE FROM order_items WHERE order_id = ?")->execute([$orderId]);
        $db->prepare("DELETE FROM orders WHERE id = ?")->execute([$orderId]);
        $t->pass('Cleaned up mock KDS test ticket');
    }
});
