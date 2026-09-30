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

        // 8. "Recall" Button (Completed -> In Progress)
        $recallRes = $staff->post('api/kds.php?action=recall', [
            'order_id' => $orderId
        ]);
        $t->assertEquals('Recall button returns 200 OK', 200, $recallRes['status']);
        $t->assertEquals('Recalled ticket status is in_progress', 'in_progress', $recallRes['json']['status'] ?? '');

        // Verify ticket is back in active kitchen queue
        $queueRecalled = $staff->get('api/kds.php');
        $inProgIdsRecalled = array_column($queueRecalled['json']['in_progress'] ?? [], 'id');
        $t->assert('Recalled ticket is back in active in_progress queue', in_array($orderId, $inProgIdsRecalled));

        // 9. Single-Tap "Fast Bump" (Rush Mode: auto-certifies QA and completes in 1 tap)
        $fastBumpRes = $staff->post('api/kds.php?action=complete', [
            'order_id' => $orderId,
            'fast_bump' => true
        ]);
        $t->assertEquals('Fast bump completes ticket without manual QA (200 OK)', 200, $fastBumpRes['status']);
        $t->assertEquals('Fast bumped ticket status is completed', 'completed', $fastBumpRes['json']['status'] ?? '');

        // 10. Single Order Cancellation ("Void Ticket") with Reason
        // Recall first, then void with reason
        $staff->post('api/kds.php?action=recall', ['order_id' => $orderId]);
        $voidRes = $staff->post('api/kds.php?action=cancel', [
            'order_id' => $orderId,
            'reason' => 'Duplicate Cashier Ring'
        ]);
        $t->assertEquals('Void ticket returns 200 OK', 200, $voidRes['status']);
        $t->assertEquals('Voided ticket status is cancelled', 'cancelled', $voidRes['json']['status'] ?? '');

        // Verify void reason stored in database
        $checkStmt = $db->prepare("SELECT customer_notes FROM orders WHERE id = ?");
        $checkStmt->execute([$orderId]);
        $notes = $checkStmt->fetchColumn();
        $t->assert('Void reason stored in customer_notes', strpos($notes, '[VOID: Duplicate Cashier Ring]') !== false);

        // 11. "History" View returns completed and voided tickets
        $historyRes = $staff->get('api/kds.php?view=history');
        $t->assertEquals('History view returns 200 OK', 200, $historyRes['status']);
        $t->assert('History contains completed list', isset($historyRes['json']['completed']));
        $t->assert('History contains cancelled list', isset($historyRes['json']['cancelled']));

        // 12. "Clear All / Shift Reset" protected by Manager PIN
        $wrongPinRes = $staff->post('api/kds.php?action=clear_all', [
            'pin' => '9999' // invalid
        ]);
        $t->assertEquals('Invalid supervisor PIN rejected with 403', 403, $wrongPinRes['status']);

        // Save existing active orders outside of this test to preserve them
        $savedOrders = $db->query("SELECT id, status, customer_notes FROM orders WHERE id != {$orderId} AND status IN ('pending', 'in_progress')")->fetchAll(PDO::FETCH_ASSOC);

        $correctPinRes = $staff->post('api/kds.php?action=clear_all', [
            'pin' => '1234' // valid supervisor PIN
        ]);
        $t->assertEquals('Valid supervisor PIN clears queue with 200 OK', 200, $correctPinRes['status']);

    } finally {
        // Restore pre-existing active orders that were in queue before clear_all was tested
        if (!empty($savedOrders)) {
            $restoreStmt = $db->prepare("UPDATE orders SET status = ?, customer_notes = ?, completed_at = NULL WHERE id = ?");
            foreach ($savedOrders as $saved) {
                $restoreStmt->execute([$saved['status'], $saved['customer_notes'], $saved['id']]);
            }
        }

        // Automatic cleanup of mock ticket
        $db->prepare("DELETE FROM order_items WHERE order_id = ?")->execute([$orderId]);
        $db->prepare("DELETE FROM orders WHERE id = ?")->execute([$orderId]);
        $t->pass('Cleaned up mock KDS test ticket');
    }
});
