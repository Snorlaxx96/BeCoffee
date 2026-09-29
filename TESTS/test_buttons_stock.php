<?php
/**
 * Suite: Stock & Add-on Management Buttons
 * Tests 86'ing items/options, creating new add-ons, and permission guards for staff and customers.
 */

TestFramework::registerSuite('Stock Management & Add-on Buttons', function(SuiteReporter $t) {
    require_once dirname(__DIR__) . '/api/db.php';
    $db = Database::getConnection();

    // 1. Customer cannot modify stock
    $guest = new TestClient();
    $res = $guest->post('api/stock.php', ['option_key' => 'oat']);
    $t->assert('Guest blocked from mutating stock (401/403)', in_array($res['status'], [401, 403], true));

    // 2. Staff can toggle stock (86 item/option)
    $staff = new TestClient();
    $staff->loginAs('staff');

    // Ensure a test option exists in option_availability
    $db->exec("
        INSERT INTO option_availability (category_type, option_key, option_label, surcharge, is_available)
        VALUES ('milk', 'TEST_SOY', 'Soy Milk (+₱20)', 20.00, 1)
        ON DUPLICATE KEY UPDATE is_available = 1
    ");

    $res = $staff->post('api/stock.php', [
        'option_key' => 'TEST_SOY',
        'is_available' => false
    ]);
    $t->assertEquals('Staff toggle stock button returns 200 OK', 200, $res['status']);
    $t->assertEquals('Option marked unavailable', false, $res['json']['is_available'] ?? null);

    // Verify persisted in DB
    $chk = $db->query("SELECT is_available FROM option_availability WHERE option_key = 'TEST_SOY'")->fetchColumn();
    $t->assertEquals('Stock state 0 saved in database', 0, (int)$chk);

    // 3. Staff cannot create new add-on options (Admin only)
    $staffAdd = $staff->post('api/stock.php', [
        'action' => 'add_option',
        'option_key' => 'TEST_VANILLA_BEANS',
        'surcharge' => 30.00
    ]);
    $t->assertEquals('Staff blocked from adding options (403 Forbidden)', 403, $staffAdd['status']);

    // 4. Admin can create new add-on options
    $admin = new TestClient();
    $admin->loginAs('admin');
    $adminAdd = $admin->post('api/stock.php', [
        'action' => 'add_option',
        'option_key' => 'TEST_VANILLA_BEANS',
        'surcharge' => 30.00,
        'category_type' => 'addon'
    ]);
    $t->assertEquals('Admin add option button returns 200 OK', 200, $adminAdd['status']);
    $t->assert('Add-on created successfully', !empty($adminAdd['json']['success']));

    // 5. Admin can delete option
    $adminDel = $admin->post('api/stock.php', [
        'action' => 'delete_option',
        'option_key' => 'TEST_VANILLA_BEANS'
    ]);
    $t->assertEquals('Admin delete option button returns 200 OK', 200, $adminDel['status']);

    // Cleanup test option
    $db->exec("DELETE FROM option_availability WHERE option_key IN ('TEST_SOY', 'TEST_VANILLA_BEANS')");
    $t->pass('Cleaned up test stock options');
});
