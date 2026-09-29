<?php
/**
 * Suite: User Roles & Page Routing Guards
 * Tests access rights, 302 server redirects, and view targets for all 4 roles.
 */

TestFramework::registerSuite('User Roles & Routing Guards', function(SuiteReporter $t) {
    // 1. Unauthenticated Guest
    $guest = new TestClient();
    $res = $guest->get('index.php');
    $t->assert('Guest can access index.php', $res['status'] === 200);

    $res = $guest->get('admin.php');
    $t->assert('Guest accessing admin.php receives 302 redirect', $res['status'] === 302);
    $t->assert('Guest redirected from admin.php to index.php?error=unauthorized', strpos($res['location'], 'index.php?error=unauthorized') !== false);

    $res = $guest->get('kds.php');
    $t->assert('Guest accessing kds.php receives 302 redirect', $res['status'] === 302);
    $t->assert('Guest redirected from kds.php to index.php?error=unauthorized', strpos($res['location'], 'index.php?error=unauthorized') !== false);

    // 2. Staff Member
    $staff = new TestClient();
    $login = $staff->loginAs('staff');
    $t->assert('Staff login authentication succeeds', $login);

    $res = $staff->get('kds.php');
    $t->assert('Staff can access Kitchen Display System (kds.php)', $res['status'] === 200);

    $res = $staff->get('admin.php');
    $t->assert('Staff accessing admin.php receives 302 redirect', $res['status'] === 302);
    $t->assert('Staff redirected to kds.php?notice=staff_restricted', strpos($res['location'], 'kds.php?notice=staff_restricted') !== false);

    $res = $staff->get('index.php?mode=staff');
    $t->assert('Staff can access Counter POS (index.php?mode=staff)', $res['status'] === 200);

    // 3. Administrator
    $admin = new TestClient();
    $login = $admin->loginAs('admin');
    $t->assert('Admin login authentication succeeds', $login);

    $res = $admin->get('admin.php');
    $t->assert('Admin can access Admin Studio (admin.php)', $res['status'] === 200);

    $res = $admin->get('kds.php');
    $t->assert('Admin can access Kitchen Display System (kds.php)', $res['status'] === 200);

    // 4. SuperAdmin (Developer)
    $super = new TestClient();
    $login = $super->loginAs('superadmin');
    $t->assert('SuperAdmin login authentication succeeds', $login);

    $res = $super->get('admin.php?view=developer');
    $t->assert('SuperAdmin can access Developer Studio (admin.php?view=developer)', $res['status'] === 200);

    // 5. Customer / Registered User
    $cust = new TestClient();
    // Test customer login via API
    $res = $cust->post('api/auth.php?action=login', [
        'email' => 'customer@becoffee.ph',
        'password' => 'customer123'
    ]);
    if ($res['status'] !== 200) {
        // If customer account not yet created, register test customer
        $cust->post('api/auth.php?action=register', [
            'name' => 'Test Customer',
            'email' => 'customer@becoffee.ph',
            'phone' => '+63 912 345 6789',
            'password' => 'customer123'
        ]);
        $res = $cust->post('api/auth.php?action=login', [
            'email' => 'customer@becoffee.ph',
            'password' => 'customer123'
        ]);
    }
    $t->assert('Customer login succeeds', $res['status'] === 200);
    $t->assertEquals('Customer target view is index.php', 'index.php', $res['json']['target_view'] ?? '');

    $res = $cust->get('admin.php');
    $t->assert('Customer accessing admin.php receives 302 redirect', $res['status'] === 302);
    $t->assert('Customer redirected to index.php?notice=customer_restricted', strpos($res['location'], 'index.php?notice=customer_restricted') !== false);

    $res = $cust->get('kds.php');
    $t->assert('Customer accessing kds.php receives 302 redirect', $res['status'] === 302);
    $t->assert('Customer redirected from kds.php to index.php?notice=staff_only', strpos($res['location'], 'index.php?notice=staff_only') !== false);
});
