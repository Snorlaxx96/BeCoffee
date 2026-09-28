<?php
/**
 * Escobar Cafe / BeCoffee — Option & Item Stock Availability Controller (REST API)
 * Allows staff/admin to 86 / cancel out customization options and add new add-ons in real-time
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

$method = $_SERVER['REQUEST_METHOD'];
$db = Database::getConnection();

// Ensure option_availability table exists and has surcharge
$db->exec("
    CREATE TABLE IF NOT EXISTS option_availability (
        id INT AUTO_INCREMENT PRIMARY KEY,
        category_type VARCHAR(50) NOT NULL,
        option_key VARCHAR(100) NOT NULL UNIQUE,
        option_label VARCHAR(100) NOT NULL,
        surcharge DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        is_available TINYINT(1) NOT NULL DEFAULT 1,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

if ($method === 'GET') {
    $stmt = $db->query("SELECT id, category_type, option_key, option_label, surcharge, is_available FROM option_availability ORDER BY id ASC");
    $options = $stmt->fetchAll();

    $availabilityMap = [];
    $surchargeMap = [];
    foreach ($options as $opt) {
        $availabilityMap[$opt['option_key']] = (int)$opt['is_available'] === 1;
        $surchargeMap[$opt['option_key']] = (float)$opt['surcharge'];
    }

    // Also fetch menu item availability
    $itemStmt = $db->query("SELECT id, name, is_available FROM menu_items ORDER BY id ASC");
    $items = $itemStmt->fetchAll();
    $itemAvailabilityMap = [];
    foreach ($items as $it) {
        $itemAvailabilityMap[$it['id']] = (int)$it['is_available'] === 1;
    }

    echo json_encode([
        'success' => true,
        'options' => $options,
        'availability' => $availabilityMap,
        'surcharges' => $surchargeMap,
        'items' => $items,
        'item_availability' => $itemAvailabilityMap
    ]);
    exit;
}

if ($method === 'POST' || $method === 'PATCH') {
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    // Action A: Add New Option / Add-on
    if (!empty($input['action']) && $input['action'] === 'add_option') {
        $catType = trim($input['category_type'] ?? 'addon');
        $name = trim($input['option_key'] ?? '');
        $surcharge = max(0, (float)($input['surcharge'] ?? 0));

        if (empty($name)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Option / Add-on name is required.']);
            exit;
        }

        $label = $surcharge > 0 ? "{$name} (+₱" . number_format($surcharge, 0) . ")" : "{$name} (+₱0)";

        $ins = $db->prepare("
            INSERT INTO option_availability (category_type, option_key, option_label, surcharge, is_available)
            VALUES (?, ?, ?, ?, 1)
            ON DUPLICATE KEY UPDATE option_label = VALUES(option_label), surcharge = VALUES(surcharge), is_available = 1
        ");
        $ins->execute([$catType, $name, $label, $surcharge]);

        echo json_encode([
            'success' => true,
            'message' => "Add-on '{$name}' created successfully.",
            'option_key' => $name,
            'option_label' => $label,
            'surcharge' => $surcharge,
            'category_type' => $catType
        ]);
        exit;
    }

    // Action B: Delete Option
    if (!empty($input['action']) && $input['action'] === 'delete_option') {
        $name = trim($input['option_key'] ?? '');
        if (empty($name)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Option name is required.']);
            exit;
        }

        $del = $db->prepare("DELETE FROM option_availability WHERE option_key = ?");
        $del->execute([$name]);

        echo json_encode(['success' => true, 'message' => "Option '{$name}' deleted."]);
        exit;
    }

    // Action C: Toggle Customization Option Stock
    if (!empty($input['option_key'])) {
        $optionKey = trim($input['option_key']);

        $stmt = $db->prepare("SELECT id, is_available FROM option_availability WHERE option_key = ?");
        $stmt->execute([$optionKey]);
        $row = $stmt->fetch();

        if (!$row) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => "Option '{$optionKey}' not found in stock registry."]);
            exit;
        }

        $newVal = isset($input['is_available']) 
            ? ((bool)$input['is_available'] ? 1 : 0) 
            : ($row['is_available'] ? 0 : 1);

        $upd = $db->prepare("UPDATE option_availability SET is_available = ? WHERE option_key = ?");
        $upd->execute([$newVal, $optionKey]);

        echo json_encode([
            'success' => true,
            'message' => "Option '{$optionKey}' updated.",
            'option_key' => $optionKey,
            'is_available' => (bool)$newVal
        ]);
        exit;
    }

    // Action D: Toggle Entire Drink Item
    if (!empty($input['item_id'])) {
        $itemId = trim($input['item_id']);

        $stmt = $db->prepare("SELECT id, is_available FROM menu_items WHERE id = ?");
        $stmt->execute([$itemId]);
        $row = $stmt->fetch();

        if (!$row) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => "Item '{$itemId}' not found in menu."]);
            exit;
        }

        $newVal = isset($input['is_available']) 
            ? ((bool)$input['is_available'] ? 1 : 0) 
            : ($row['is_available'] ? 0 : 1);

        $upd = $db->prepare("UPDATE menu_items SET is_available = ? WHERE id = ?");
        $upd->execute([$newVal, $itemId]);

        echo json_encode([
            'success' => true,
            'message' => "Item '{$itemId}' updated.",
            'item_id' => $itemId,
            'is_available' => (bool)$newVal
        ]);
        exit;
    }

    // Action E: Reset all to in-stock
    if (!empty($input['reset_all'])) {
        $db->exec("UPDATE option_availability SET is_available = 1");
        $db->exec("UPDATE menu_items SET is_available = 1");
        echo json_encode(['success' => true, 'message' => 'All options and items set to In Stock.']);
        exit;
    }

    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid action or missing parameters.']);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
