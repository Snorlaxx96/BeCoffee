<?php
/**
 * Suite: Customer Online (Takeout) & In-Store (Walk-in / QR) Flow
 * Tests customer@example.com login, takeout.php landing, and in-store walk-in QR routing.
 */

TestFramework::registerSuite('Customer Online Takeout & Walk-in QR Flow', function(SuiteReporter $t) {
    require_once dirname(__DIR__) . '/api/db.php';
    $db = Database::getConnection();

    // 1. Online Customer Authentication
    $onlineCustomer = new TestClient();
    $loginRes = $onlineCustomer->post('api/auth.php?action=login', [
        'email' => 'customer@example.com',
        'password' => '123123123'
    ]);
    $t->assertEquals('customer@example.com login returns 200 OK', 200, $loginRes['status']);
    $t->assertEquals('Online customer target view is takeout.php', 'takeout.php', $loginRes['json']['target_view'] ?? '');
    $t->assertEquals('Customer role is customer', 'customer', $loginRes['json']['user']['role'] ?? '');

    // 2. Online Customer Lands on Dedicated Takeout Page
    $takeoutRes = $onlineCustomer->get('takeout.php');
    $t->assertEquals('Customer can access takeout.php (200 OK)', 200, $takeoutRes['status']);
    $t->assert('Takeout page title contains Online Take Out Order', strpos($takeoutRes['body'], 'Online Take Out Order') !== false);
    $t->assert('Takeout page contains packaging fee breakdown', strpos($takeoutRes['body'], 'Eco-Packaging Fee') !== false);
    $t->assert('Our Story removed from takeout.php header', strpos($takeoutRes['body'], 'Our Story') === false);
    $t->assert('Log Out button present on takeout.php header', strpos($takeoutRes['body'], 'Log Out') !== false);
    $t->assert('Takeout page prep countdown clock removed for online logged in customers', strpos($takeoutRes['body'], 'prepCountdownClock') === false);

    // 3. Online Customer Submits Take-out Order
    $itemStmt = $db->query("SELECT id, name, price FROM menu_items WHERE is_available = 1 LIMIT 1");
    $item = $itemStmt->fetch();
    $orderRes = $onlineCustomer->post('api/orders.php', [
        'customer_name' => 'Online Customer',
        'customer_phone' => '09171112233',
        'order_type' => 'take_out',
        'payment_method' => 'cash',
        'items' => [
            [
                'id' => $item['id'],
                'name' => $item['name'],
                'quantity' => 1,
                'unit_price' => (float)$item['price'],
                'temperature' => 'Iced',
                'milk_option' => 'Regular Milk',
                'sweetness_level' => 'Normal (100%)',
                'custom_notes' => 'ONLINE_TAKEOUT_TEST'
            ]
        ]
    ]);
    $t->assertEquals('Online takeout order returns 201 Created', 201, $orderRes['status']);
    $t->assertEquals('Order type is take_out', 'take_out', $orderRes['json']['order_type'] ?? '');
    $ref = $orderRes['json']['order_reference'] ?? null;

    if ($ref) {
        $db->prepare("DELETE FROM order_items WHERE order_id = (SELECT id FROM orders WHERE order_reference = ?)")->execute([$ref]);
        $db->prepare("DELETE FROM orders WHERE order_reference = ?")->execute([$ref]);
        $t->pass('Cleaned up online takeout test order');
    }

    // 4. In-Store / Table QR Access
    $walkin = new TestClient();
    $walkinGeneral = $walkin->get('index.php');
    $t->assertEquals('General in-store link loads index.php (200 OK)', 200, $walkinGeneral['status']);
    $t->assert('Walk-in modal is removed from index.php', strpos($walkinGeneral['body'], 'walkinDiningModal') === false);
    $t->assert('Index page contains leaveWebEntirely logic', strpos($walkinGeneral['body'], 'leaveWebEntirely') !== false);
    $t->assert('Index page includes prep countdown clock', strpos($walkinGeneral['body'], 'prepCountdownClock') !== false);
    $t->assert('Index page includes Time Left label on countdown', strpos($walkinGeneral['body'], 'Time Left:') !== false);
    $t->assert('Index page includes tableExpiredModal for auto-leave', strpos($walkinGeneral['body'], 'tableExpiredModal') !== false);
    $t->assert('Index page includes cat-pill-group container', strpos($walkinGeneral['body'], 'cat-pill-group') !== false);
    $t->assert('Index page includes Our Story link in header', strpos($walkinGeneral['body'], 'Our Story') !== false);
    $t->assert('Index page includes Leave button after Our Story', strpos($walkinGeneral['body'], 'btnLeaveSession') !== false);

    // 5. Table QR Scan (e.g. ?table=5)
    $tableQr = $walkin->get('index.php', ['table' => '5']);
    $t->assertEquals('Table QR scan loads index.php (200 OK)', 200, $tableQr['status']);
    $t->assert('Table QR scan page includes prep countdown clock', strpos($tableQr['body'], 'prepCountdownClock') !== false);
    $t->assert('Table QR scan page includes Leave button after Our Story', strpos($tableQr['body'], 'btnLeaveSession') !== false);
    $t->assert('Table QR scan page includes tableExpiredModal for auto-leave', strpos($tableQr['body'], 'tableExpiredModal') !== false);
    $t->assert('Table QR scan page includes tableLockedBadge for auto-locking table', strpos($tableQr['body'], 'tableLockedBadge') !== false);
    $t->assert('Table QR scan page includes tableOrderingPausedBanner', strpos($tableQr['body'], 'tableOrderingPausedBanner') !== false);

    // 6. Online Customer Logout Reroutes Properly to home.php (Never index.php)
    $logoutWithRedirect = $onlineCustomer->get('api/auth.php?action=logout&redirect=home.php');
    $t->assertEquals('Customer logout with redirect returns 302 redirect', 302, $logoutWithRedirect['status']);
    $t->assert('Customer logout redirects to home.php', strpos($logoutWithRedirect['location'], 'home.php') !== false);
    $t->assert('Customer logout does NOT redirect to index.php', strpos($logoutWithRedirect['location'], 'index.php') === false);

    // Test fallback role-based redirect when no redirect query param provided
    $customerFallback = new TestClient();
    $customerFallback->loginAs('customer');
    $defaultLogout = $customerFallback->get('api/auth.php?action=logout');
    $t->assertEquals('Customer default logout returns 302 redirect', 302, $defaultLogout['status']);
    $t->assert('Customer default logout redirects to home.php', strpos($defaultLogout['location'], 'home.php') !== false);
    $t->assert('Customer default logout does NOT redirect to in-store index.php', strpos($defaultLogout['location'], 'index.php') === false);

    // 7. Physical Table QR Stickers Access Control (qr_stickers.php)
    $adminClient = new TestClient();
    $adminClient->loginAs('admin');
    $qrStickersRes = $adminClient->get('qr_stickers.php');
    $t->assertEquals('Admin can access qr_stickers.php (200 OK)', 200, $qrStickersRes['status']);
    $t->assert('Stickers page contains generator title', strpos($qrStickersRes['body'], 'Table QR Stickers Generator') !== false);
    $t->assert('Stickers page contains sticker cards', strpos($qrStickersRes['body'], 'sticker-card') !== false);

    $staffClient = new TestClient();
    $staffClient->loginAs('staff');
    $staffStickersRes = $staffClient->get('qr_stickers.php');
    $t->assertEquals('Kitchen staff is blocked from qr_stickers.php (403 Forbidden)', 403, $staffStickersRes['status']);

    // Admin Quick Links & Operational Previews
    $adminPageRes = $adminClient->get('admin.php');
    $t->assertEquals('Admin can access admin.php (200 OK)', 200, $adminPageRes['status']);
    $t->assert('Admin Quick Links contain previewDeviceStrip', strpos($adminPageRes['body'], 'previewDeviceStrip') !== false);
    $t->assert('Admin Quick Links contain Kitchen Screen link', strpos($adminPageRes['body'], 'id="quickLinkKds"') !== false);
    $t->assert('Dine-In/Takeout customer quick link is removed', strpos($adminPageRes['body'], 'id="quickLinkPos"') === false);
    $t->assert('Online order customer quick link is removed', strpos($adminPageRes['body'], 'id="quickLinkStorefront"') === false);
    $t->assert('Kitchen Screen preview iframe targets kds.php', strpos($adminPageRes['body'], 'data-src="kds.php"') !== false);
    $t->assert('Table QR Stickers preview iframe targets qr_stickers.php', strpos($adminPageRes['body'], 'data-src="qr_stickers.php"') !== false);

    // 8. Management Operational Settings RBAC (api/settings.php)
    $staffToggleRes = $staffClient->post('api/settings.php?action=toggle_qr', ['enabled' => false]);
    $t->assertEquals('Kitchen staff is blocked from toggling Table QR ordering (403 Forbidden)', 403, $staffToggleRes['status']);

    // 9. Admin Can Pause Table QR Ordering
    $adminPauseRes = $adminClient->post('api/settings.php?action=toggle_qr', ['enabled' => false]);
    $t->assertEquals('Admin can toggle Table QR ordering to PAUSED (200 OK)', 200, $adminPauseRes['status']);
    $t->assertEquals('Setting table_qr_ordering_enabled is now false', false, $adminPauseRes['json']['table_qr_ordering_enabled'] ?? null);

    // 10. Operational Guard: Customer Dine-In is Blocked (422) When Paused
    $blockedOrderRes = $walkin->post('api/orders.php', [
        'customer_name' => 'Walkin Guest',
        'customer_phone' => '09170000000',
        'order_type' => 'dine_in',
        'table_number' => '3',
        'payment_method' => 'cash',
        'items' => [
            [
                'id' => $item['id'],
                'name' => $item['name'],
                'quantity' => 1,
                'unit_price' => (float)$item['price']
            ]
        ]
    ]);
    $t->assertEquals('Customer dine-in order is rejected when paused (422 Unprocessable)', 422, $blockedOrderRes['status']);
    $t->assert('Rejection error explains table ordering is paused', strpos($blockedOrderRes['json']['error'] ?? '', 'temporarily paused') !== false);

    // 11. Admin Re-enables Table QR Ordering & Customer Can Order Again
    $adminResumeRes = $adminClient->post('api/settings.php?action=toggle_qr', ['enabled' => true]);
    $t->assertEquals('Admin can re-enable Table QR ordering (200 OK)', 200, $adminResumeRes['status']);
    $t->assertEquals('Setting table_qr_ordering_enabled is now true', true, $adminResumeRes['json']['table_qr_ordering_enabled'] ?? null);

    $allowedOrderRes = $walkin->post('api/orders.php', [
        'customer_name' => 'Walkin Guest',
        'customer_phone' => '09170000000',
        'order_type' => 'dine_in',
        'table_number' => '3',
        'payment_method' => 'cash',
        'items' => [
            [
                'id' => $item['id'],
                'name' => $item['name'],
                'quantity' => 1,
                'unit_price' => (float)$item['price']
            ]
        ]
    ]);
    $t->assertEquals('Customer dine-in order succeeds after re-enabling (201 Created)', 201, $allowedOrderRes['status']);
    $allowedRef = $allowedOrderRes['json']['order_reference'] ?? null;
    if ($allowedRef) {
        $db->prepare("DELETE FROM order_items WHERE order_id = (SELECT id FROM orders WHERE order_reference = ?)")->execute([$allowedRef]);
        $db->prepare("DELETE FROM orders WHERE order_reference = ?")->execute([$allowedRef]);
        $t->pass('Cleaned up test dine-in order');
    }
});
