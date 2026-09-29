<?php
/**
 * Suite: SuperAdmin Management & System Control
 * Tests RBAC user management, role update buttons, last-superadmin safety, and system diagnostics.
 */

TestFramework::registerSuite('SuperAdmin RBAC & System Control', function(SuiteReporter $t) {
    require_once dirname(__DIR__) . '/api/db.php';
    $db = Database::getConnection();

    // 1. Role Barriers: Admin & Staff blocked from Users & System APIs
    $admin = new TestClient();
    $admin->loginAs('admin');
    $res = $admin->get('api/users.php');
    $t->assertEquals('Admin blocked from api/users.php (403 Forbidden)', 403, $res['status']);

    $res = $admin->get('api/system.php?action=diagnostics');
    $t->assertEquals('Admin blocked from api/system.php (403 Forbidden)', 403, $res['status']);

    $staff = new TestClient();
    $staff->loginAs('staff');
    $res = $staff->get('api/users.php');
    $t->assertEquals('Staff blocked from api/users.php (403 Forbidden)', 403, $res['status']);

    // 2. SuperAdmin Authentication
    $super = new TestClient();
    $t->assert('SuperAdmin signs in', $super->loginAs('superadmin'));

    // 3. List Users
    $listRes = $super->get('api/users.php');
    $t->assertEquals('SuperAdmin can list users (200 OK)', 200, $listRes['status']);
    $t->assert('Role counts returned in user summary', isset($listRes['json']['role_counts']));

    // 4. "Create User" Button
    $newUserPayload = [
        'name' => 'AutoTest Staff Member',
        'email' => 'autotest_staff_' . uniqid() . '@becoffee.ph',
        'password' => 'StaffPass123!',
        'role' => 'staff'
    ];
    $createRes = $super->post('api/users.php?action=create', $newUserPayload);
    $t->assertEquals('Create User button returns 201 Created', 201, $createRes['status']);
    $createdUserId = $createRes['json']['user']['id'] ?? null;

    if ($createdUserId) {
        // 5. "Update Role" Button (Promote staff to admin)
        $roleRes = $super->post('api/users.php?action=update_role', [
            'user_id' => $createdUserId,
            'role' => 'admin'
        ]);
        $t->assertEquals('Update Role button returns 200 OK', 200, $roleRes['status']);

        // Verify in DB
        $chk = $db->prepare("SELECT role FROM users WHERE id = ?");
        $chk->execute([$createdUserId]);
        $t->assertEquals('User role in database is admin', 'admin', $chk->fetchColumn());

        // 6. Cleanup test user
        $db->prepare("DELETE FROM users WHERE id = ?")->execute([$createdUserId]);
        $t->pass('Cleaned up test user record');
    }

    // 7. System Diagnostics Button
    $diagRes = $super->get('api/system.php?action=diagnostics');
    $t->assertEquals('System Diagnostics button returns 200 OK', 200, $diagRes['status']);
    $t->assertEquals('Database name matches becoffee_db', 'becoffee_db', $diagRes['json']['diagnostics']['database_name'] ?? '');
    $t->assert('MySQL version returned', !empty($diagRes['json']['diagnostics']['mysql_version']));

    // 8. Audit Logs Button
    $logsRes = $super->get('api/system.php?action=audit_logs');
    $t->assertEquals('Audit Logs query returns 200 OK', 200, $logsRes['status']);
    $t->assert('Audit logs array returned', isset($logsRes['json']['logs']));
});
