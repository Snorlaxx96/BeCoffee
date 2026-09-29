<?php
/**
 * Suite: Admin Menu CMS Buttons
 * Tests Create, Update, Toggle Availability, and Delete actions on menu items, plus role barriers.
 */

TestFramework::registerSuite('Menu CMS Buttons & Catalog Management', function(SuiteReporter $t) {
    require_once dirname(__DIR__) . '/api/db.php';
    $db = Database::getConnection();

    // 1. Role Barriers: Guest & Staff blocked from CMS
    $guest = new TestClient();
    $res = $guest->get('api/admin_menu.php');
    $t->assert('Guest blocked from admin_menu.php (401/403)', in_array($res['status'], [401, 403], true));

    $staff = new TestClient();
    $staff->loginAs('staff');
    $res = $staff->get('api/admin_menu.php');
    $t->assert('Staff blocked from admin_menu.php (403 Forbidden)', $res['status'] === 403);

    // 2. Admin Authentication
    $admin = new TestClient();
    $t->assert('Admin signs in', $admin->loginAs('admin'));

    // 3. Fetch Catalog Button
    $catRes = $admin->get('api/admin_menu.php');
    $t->assertEquals('Admin can fetch full CMS catalog (200 OK)', 200, $catRes['status']);
    $t->assert('Categories returned in catalog response', !empty($catRes['json']['categories']));

    // 4. "Add Product" Button (Create New Item)
    $createPayload = [
        'name' => 'AutoTest Matcha Supreme',
        'category_id' => 1,
        'price' => 195.00,
        'price_iced_m' => 195.00,
        'price_iced_l' => 225.00,
        'price_hot' => 185.00,
        'description' => 'Automated test ceremonial matcha latte.',
        'is_available' => true,
        'is_bestseller' => false
    ];
    $createRes = $admin->post('api/admin_menu.php?action=save_item', $createPayload);
    $t->assertEquals('Create Item button returns 200 OK', 200, $createRes['status']);
    $t->assert('Product created message returned', !empty($createRes['json']['success']));
    $createdId = $createRes['json']['id'] ?? null;

    if ($createdId) {
        // 5. "Edit Price" Button (Update Item)
        $updatePayload = array_merge($createPayload, [
            'id' => $createdId,
            'name' => 'AutoTest Matcha Supreme (Updated)',
            'price' => 205.00
        ]);
        $updateRes = $admin->post('api/admin_menu.php?action=save_item', $updatePayload);
        $t->assertEquals('Update Item button returns 200 OK', 200, $updateRes['status']);

        // Verify update in DB
        $chk = $db->prepare("SELECT price FROM menu_items WHERE id = ?");
        $chk->execute([$createdId]);
        $price = (float)$chk->fetchColumn();
        $t->assertEquals('Updated price saved as 205.00', 205.00, $price);

        // 6. "Toggle Availability" Button (Mark Sold Out)
        $toggleRes = $admin->post('api/admin_menu.php?action=toggle_availability', [
            'id' => $createdId,
            'is_available' => false
        ]);
        $t->assertEquals('Toggle Availability button returns 200 OK', 200, $toggleRes['status']);
        $t->assertEquals('Item status marked as unavailable', false, $toggleRes['json']['is_available'] ?? null);

        // 7. "Delete Product" Button
        $deleteRes = $admin->post('api/admin_menu.php?action=delete_item', [
            'id' => $createdId
        ]);
        $t->assertEquals('Delete Product button returns 200 OK', 200, $deleteRes['status']);

        // Verify deleted from DB
        $verifyDel = $db->prepare("SELECT COUNT(*) FROM menu_items WHERE id = ?");
        $verifyDel->execute([$createdId]);
        $t->assertEquals('Product permanently removed from database', 0, (int)$verifyDel->fetchColumn());
    }
});
