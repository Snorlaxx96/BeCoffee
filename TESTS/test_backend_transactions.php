<?php
/**
 * Suite: Backend Persistence, Transactions & Security Defense
 * Verifies PDO health check, foreign key integrity, atomic transaction rollback, and SQL injection safety per backend.md.
 */

TestFramework::registerSuite('Backend Transactions & Persistence Integrity', function(SuiteReporter $t) {
    require_once dirname(__DIR__) . '/api/db.php';
    $db = Database::getConnection();

    // 1. Database Connection & Health Check (Stage 4)
    $pingStmt = $db->query("SELECT 1 AS alive");
    $alive = $pingStmt->fetchColumn();
    $t->assertEquals('Database health check (SELECT 1) succeeds', 1, (int)$alive);

    // 2. Foreign Key Constraint Discipline (Stage 2)
    $fkFailed = false;
    try {
        // Attempting to insert an order_item referencing an invalid order_id (99999999)
        $invalidStmt = $db->prepare("
            INSERT INTO order_items (order_id, item_name, quantity, unit_price, subtotal)
            VALUES (99999999, 'Phantom Item', 1, 150.00, 150.00)
        ");
        $invalidStmt->execute();
    } catch (PDOException $e) {
        $fkFailed = true;
    }
    $t->assert('Foreign key constraint restricts orphaned order_items', $fkFailed);

    // 3. Atomic Multi-Step Transaction & Rollback (Stage 3)
    $initialOrderCount = (int)$db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $initialItemCount = (int)$db->query("SELECT COUNT(*) FROM order_items")->fetchColumn();

    try {
        $db->beginTransaction();

        $testRef = 'TEST_ROLLBACK_' . uniqid();
        $insOrder = $db->prepare("
            INSERT INTO orders (order_reference, queue_number, customer_name, order_type, payment_method, status, grand_total)
            VALUES (?, 'TX-01', 'Rollback Tester', 'take_out', 'cash', 'pending', 300.00)
        ");
        $insOrder->execute([$testRef]);
        $txOrderId = (int)$db->lastInsertId();

        $insItem = $db->prepare("
            INSERT INTO order_items (order_id, item_name, quantity, unit_price, subtotal)
            VALUES (?, 'Item 1', 1, 150.00, 150.00)
        ");
        $insItem->execute([$txOrderId]);

        // Deliberate simulated failure midway
        throw new RuntimeException("Simulated mid-transaction failure");

        $db->commit();
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
    }

    $finalOrderCount = (int)$db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $finalItemCount = (int)$db->query("SELECT COUNT(*) FROM order_items")->fetchColumn();

    $t->assertEquals('Orders table rolled back with zero leakage', $initialOrderCount, $finalOrderCount);
    $t->assertEquals('Order items table rolled back with zero orphaned rows', $initialItemCount, $finalItemCount);

    // 4. SQL Injection Sanitization & Parameterization Defense
    $client = new TestClient();
    $sqlPayload = "' OR 1=1 -- \"; DROP TABLE users; --";
    $searchRes = $client->get('api/orders.php', ['reference' => $sqlPayload]);
    $t->assertEquals('SQL injection string returns safe 404 (not 500 or SQL syntax error)', 404, $searchRes['status']);
    $t->assert('Error message does not leak database paths or SQL stack traces', strpos($searchRes['body'], 'SQLSTATE') === false);

    // Verify users table remains intact
    $usersCount = (int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $t->assert('Users table safe and intact after injection payload', $usersCount > 0);
});
