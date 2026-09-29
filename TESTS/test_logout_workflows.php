<?php
/**
 * Suite: User & Staff Authentication Logout Workflows
 * Tests session destruction, cookie clearance, GET redirect logout, and post-logout access barriers for all roles.
 */

TestFramework::registerSuite('Authentication & Logout Workflows', function(SuiteReporter $t) {
    // 1. Staff Sign Out Workflow
    $staff = new TestClient();
    $login = $staff->loginAs('staff');
    $t->assert('Staff signs in successfully', $login);

    $meRes = $staff->get('api/auth.php?action=me');
    $t->assertEquals('Staff session is authenticated', true, $meRes['json']['authenticated'] ?? false);
    $t->assertEquals('Staff role is active', 'staff', $meRes['json']['user']['role'] ?? '');

    // Staff can access KDS while logged in
    $kdsRes = $staff->get('kds.php');
    $t->assertEquals('Staff can access kds.php with active session', 200, $kdsRes['status']);

    // Staff triggers POST logout action
    $logoutRes = $staff->post('api/auth.php?action=logout');
    $t->assertEquals('Staff logout returns 200 OK', 200, $logoutRes['status']);
    $t->assert('Logout confirmation message received', !empty($logoutRes['json']['success']));

    // Verify session is completely dead
    $meAfter = $staff->get('api/auth.php?action=me');
    $t->assertEquals('Session after logout is unauthenticated', false, $meAfter['json']['authenticated'] ?? true);

    // Verify staff is immediately locked out of kds.php
    $kdsAfter = $staff->get('kds.php');
    $t->assertEquals('Accessing kds.php after logout returns 302 redirect', 302, $kdsAfter['status']);
    $t->assert('Redirected to index.php?error=unauthorized', strpos($kdsAfter['location'], 'index.php?error=unauthorized') !== false);

    // 2. Browser Direct GET Logout Redirect
    $browserClient = new TestClient();
    $browserClient->loginAs('staff');
    $getLogout = $browserClient->get('api/auth.php?action=logout');
    $t->assertEquals('GET api/auth.php?action=logout returns 302 redirect', 302, $getLogout['status']);
    $t->assert('Location header points to ../index.php', strpos($getLogout['location'], 'index.php') !== false);

    $meBrowser = $browserClient->get('api/auth.php?action=me');
    $t->assertEquals('GET logout successfully destroyed session', false, $meBrowser['json']['authenticated'] ?? true);

    // 3. Admin Sign Out Workflow
    $admin = new TestClient();
    $admin->loginAs('admin');
    $t->assertEquals('Admin accesses admin.php before logout', 200, $admin->get('admin.php')['status']);

    $admin->logout();
    $adminAfter = $admin->get('admin.php');
    $t->assertEquals('Admin locked out of admin.php after logout (302)', 302, $adminAfter['status']);

    // 4. SuperAdmin Sign Out Workflow
    $super = new TestClient();
    $super->loginAs('superadmin');
    $t->assertEquals('SuperAdmin accesses dev view before logout', 200, $super->get('admin.php?view=developer')['status']);

    $super->logout();
    $superAfter = $super->get('admin.php?view=developer');
    $t->assertEquals('SuperAdmin locked out of dev view after logout (302)', 302, $superAfter['status']);
    $t->assert('Blocked from api/system.php after logout (401/403)', in_array($super->get('api/system.php?action=diagnostics')['status'], [401, 403], true));
});
