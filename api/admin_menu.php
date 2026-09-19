<?php
/**
 * BeCoffee — Admin Menu CMS Controller
 * Protected endpoint for catalog management (prices, availability, item creation/editing)
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

// Verify Admin Session
if (empty($_SESSION['user_id'])) {
    jsonResponse(['success' => false, 'error' => 'Authentication required.'], 401);
}

$db = Database::getConnection();
$adminStmt = $db->prepare("SELECT id, name, email, role FROM users WHERE id = ?");
$adminStmt->execute([$_SESSION['user_id']]);
$admin = $adminStmt->fetch();

if (!$admin || $admin['role'] !== 'admin') {
    jsonResponse(['success' => false, 'error' => 'Forbidden. Administrator privileges required.'], 403);
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// --- 1. GET: Fetch Full Catalog for CMS ---
if ($method === 'GET') {
    // 1. Fetch Categories
    $catStmt = $db->query("SELECT id, slug, name, sort_order FROM categories ORDER BY sort_order ASC");
    $categories = $catStmt->fetchAll();

    // 2. Fetch All Menu Items
    $query = "
        SELECT 
            m.id,
            m.category_id,
            c.slug AS category,
            c.name AS category_name,
            m.name,
            m.origin_notes AS origin,
            m.elevation_info AS elevation,
            CAST(m.price AS DECIMAL(10,2)) AS price,
            CAST(m.price_iced_m AS DECIMAL(10,2)) AS priceIcedM,
            IF(m.price_iced_l IS NULL, NULL, CAST(m.price_iced_l AS DECIMAL(10,2))) AS priceIcedL,
            IF(m.price_hot IS NULL, NULL, CAST(m.price_hot AS DECIMAL(10,2))) AS priceHot,
            m.description,
            m.image_url AS image,
            m.is_bestseller AS isBestseller,
            m.is_available AS isAvailable,
            m.created_at
        FROM menu_items m
        JOIN categories c ON m.category_id = c.id
        ORDER BY c.sort_order ASC, m.name ASC
    ";
    $items = $db->query($query)->fetchAll();

    // 3. Fetch Flavors
    $flavorStmt = $db->query("SELECT item_id, flavor_slug, flavor_label FROM item_flavors");
    $allFlavors = $flavorStmt->fetchAll();
    $flavorMap = [];
    $flavorLabelMap = [];
    foreach ($allFlavors as $row) {
        $flavorMap[$row['item_id']][] = $row['flavor_slug'];
        $flavorLabelMap[$row['item_id']][] = $row['flavor_label'];
    }

    $formattedItems = [];
    $totalCount = count($items);
    $activeCount = 0;
    $unavailableCount = 0;

    foreach ($items as $item) {
        $itemId = $item['id'];
        $isAvail = (bool) $item['isAvailable'];
        if ($isAvail) {
            $activeCount++;
        } else {
            $unavailableCount++;
        }

        $formattedItems[] = [
            'id'           => $item['id'],
            'categoryId'   => (int) $item['category_id'],
            'category'     => $item['category'],
            'categoryName' => $item['category_name'],
            'name'         => $item['name'],
            'origin'       => $item['origin'] ?? '',
            'elevation'    => $item['elevation'] ?? '',
            'price'        => (float) $item['price'],
            'priceIcedM'   => (float) $item['priceIcedM'],
            'priceIcedL'   => $item['priceIcedL'] !== null ? (float) $item['priceIcedL'] : null,
            'priceHot'     => $item['priceHot'] !== null ? (float) $item['priceHot'] : null,
            'description'  => $item['description'] ?? '',
            'image'        => $item['image'],
            'isBestseller' => (bool) $item['isBestseller'],
            'isAvailable'  => $isAvail,
            'flavors'      => $flavorMap[$itemId] ?? [],
            'flavorLabels' => $flavorLabelMap[$itemId] ?? []
        ];
    }

    jsonResponse([
        'success'    => true,
        'stats'      => [
            'total'       => $totalCount,
            'active'      => $activeCount,
            'unavailable' => $unavailableCount
        ],
        'categories' => $categories,
        'items'      => $formattedItems
    ]);
}

// --- 2. POST: Toggle Item Availability ---
if ($method === 'POST' && $action === 'toggle_availability') {
    $input = getJsonInput();
    $id = trim($input['id'] ?? '');

    if (empty($id)) {
        jsonResponse(['success' => false, 'error' => 'Product ID is required.'], 422);
    }

    if (isset($input['is_available'])) {
        $newStatus = (bool) $input['is_available'] ? 1 : 0;
        $stmt = $db->prepare("UPDATE menu_items SET is_available = ? WHERE id = ?");
        $stmt->execute([$newStatus, $id]);
    } else {
        $stmt = $db->prepare("UPDATE menu_items SET is_available = NOT is_available WHERE id = ?");
        $stmt->execute([$id]);
    }

    // Retrieve new status
    $check = $db->prepare("SELECT is_available, name FROM menu_items WHERE id = ?");
    $check->execute([$id]);
    $row = $check->fetch();

    if (!$row) {
        jsonResponse(['success' => false, 'error' => 'Product not found.'], 404);
    }

    $isAvail = (bool) $row['is_available'];
    jsonResponse([
        'success'      => true,
        'id'           => $id,
        'name'         => $row['name'],
        'is_available' => $isAvail,
        'message'      => $row['name'] . ($isAvail ? ' is now Available.' : ' is marked as Unavailable / Sold Out.')
    ]);
}

// --- 3. POST: Save Product (Create or Update) ---
if ($method === 'POST' && $action === 'save_item') {
    $input = getJsonInput();

    $name         = trim($input['name'] ?? '');
    $categoryId   = (int) ($input['category_id'] ?? 1);
    $price        = (float) ($input['price'] ?? 0);
    $priceIcedM   = (float) ($input['price_iced_m'] ?? $price);
    $priceIcedL   = !empty($input['price_iced_l']) ? (float) $input['price_iced_l'] : null;
    $priceHot     = !empty($input['price_hot']) ? (float) $input['price_hot'] : null;
    $origin       = trim($input['origin'] ?? 'House Roast Blend');
    $elevation    = trim($input['elevation'] ?? ($priceHot ? 'Hot / Iced' : 'Iced Only'));
    $description  = trim($input['description'] ?? '');
    $image        = trim($input['image'] ?? 'images/menu/hc-classic.jpg');
    $isBestseller = !empty($input['is_bestseller']) ? 1 : 0;
    $isAvailable  = isset($input['is_available']) ? ((bool) $input['is_available'] ? 1 : 0) : 1;

    if (mb_strlen($name) < 2) {
        jsonResponse(['success' => false, 'error' => 'Product name must be at least 2 characters.'], 422);
    }
    if ($price <= 0) {
        jsonResponse(['success' => false, 'error' => 'Valid base price is required.'], 422);
    }

    // Verify category exists
    $catCheck = $db->prepare("SELECT id, slug FROM categories WHERE id = ?");
    $catCheck->execute([$categoryId]);
    $catRow = $catCheck->fetch();
    if (!$catRow) {
        jsonResponse(['success' => false, 'error' => 'Selected category is invalid.'], 422);
    }

    // Slug / ID generation
    $id = trim($input['id'] ?? '');
    if (empty($id)) {
        $slugPrefix = [
            'house-coffee'   => 'hc',
            'matcha'         => 'mat',
            'house-specials' => 'hs',
            'yogurt-soda'    => 'ys'
        ][$catRow['slug']] ?? 'item';

        $cleanName = strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', $name));
        $id = $slugPrefix . '-' . trim($cleanName, '-');
    }

    $upsertQuery = "
        INSERT INTO menu_items (
            id, category_id, name, origin_notes, elevation_info,
            price, price_iced_m, price_iced_l, price_hot,
            description, image_url, is_bestseller, is_available
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            category_id   = VALUES(category_id),
            name          = VALUES(name),
            origin_notes  = VALUES(origin_notes),
            elevation_info= VALUES(elevation_info),
            price         = VALUES(price),
            price_iced_m  = VALUES(price_iced_m),
            price_iced_l  = VALUES(price_iced_l),
            price_hot     = VALUES(price_hot),
            description   = VALUES(description),
            image_url     = VALUES(image_url),
            is_bestseller = VALUES(is_bestseller),
            is_available  = VALUES(is_available)
    ";

    $stmt = $db->prepare($upsertQuery);
    $stmt->execute([
        $id, $categoryId, $name, $origin, $elevation,
        $price, $priceIcedM, $priceIcedL, $priceHot,
        $description, $image, $isBestseller, $isAvailable
    ]);

    // Handle Flavors
    if (isset($input['flavors']) && is_array($input['flavors'])) {
        $delFlavor = $db->prepare("DELETE FROM item_flavors WHERE item_id = ?");
        $delFlavor->execute([$id]);

        $insFlavor = $db->prepare("INSERT INTO item_flavors (item_id, flavor_slug, flavor_label) VALUES (?, ?, ?)");
        $knownLabels = [
            'bold-coffee'     => 'Bold & Classic Coffee',
            'sweet-caramel'   => 'Sweet & Caramel',
            'chocolate-malt'  => 'Chocolate & Malt',
            'matcha'          => 'Ceremonial Matcha',
            'fruity-berry'    => 'Fruity & Refreshing'
        ];

        foreach ($input['flavors'] as $fSlug) {
            $fSlug = trim($fSlug);
            if ($fSlug) {
                $fLabel = $knownLabels[$fSlug] ?? ucwords(str_replace('-', ' ', $fSlug));
                $insFlavor->execute([$id, $fSlug, $fLabel]);
            }
        }
    }

    jsonResponse([
        'success' => true,
        'message' => "Product '{$name}' saved successfully.",
        'id'      => $id
    ]);
}

// --- 4. POST: Delete Item ---
if ($method === 'POST' && $action === 'delete_item') {
    $input = getJsonInput();
    $id = trim($input['id'] ?? '');

    if (empty($id)) {
        jsonResponse(['success' => false, 'error' => 'Product ID is required.'], 422);
    }

    try {
        // Delete flavors first
        $delFlavors = $db->prepare("DELETE FROM item_flavors WHERE item_id = ?");
        $delFlavors->execute([$id]);

        // Delete menu item
        $delItem = $db->prepare("DELETE FROM menu_items WHERE id = ?");
        $delItem->execute([$id]);

        jsonResponse([
            'success' => true,
            'message' => "Product '{$id}' removed successfully."
        ]);
    } catch (PDOException $e) {
        // If tied to foreign key (orders), soft-delete instead
        $softDel = $db->prepare("UPDATE menu_items SET is_available = FALSE WHERE id = ?");
        $softDel->execute([$id]);

        jsonResponse([
            'success' => true,
            'message' => "Item has historical orders; it was set to Inactive/Unavailable."
        ]);
    }
}

jsonResponse(['success' => false, 'error' => 'Invalid action.'], 400);
