<?php
/**
 * BeCoffee — Menu API Endpoint
 * Full CRUD for menu items and dynamic catalog retrieval
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

$db = Database::getConnection();
$method = $_SERVER['REQUEST_METHOD'];

// Handle Item Update & Creation
if ($method === 'POST' || $method === 'PUT') {
    requireRole(['admin', 'superadmin']);
    $input = getJsonInput();
    $id = trim($input['id'] ?? '');
    $name = trim($input['name'] ?? '');
    $price = (float)($input['price'] ?? 0);
    $desc = trim($input['description'] ?? '');
    $catSlug = trim($input['category'] ?? 'house-coffee');
    $isAvailable = isset($input['is_available']) ? ((bool)$input['is_available'] ? 1 : 0) : 1;
    $imageUrl = trim($input['image'] ?? $input['image_url'] ?? '');

    if (empty($name)) {
        jsonResponse(['success' => false, 'error' => 'Drink name is required.'], 400);
    }
    if ($price <= 0) {
        jsonResponse(['success' => false, 'error' => 'Price must be greater than 0.'], 400);
    }

    // Lookup category id
    $catStmt = $db->prepare("SELECT id FROM categories WHERE slug = ?");
    $catStmt->execute([$catSlug]);
    $catId = $catStmt->fetchColumn() ?: 1;

    // Check if item exists
    if (!empty($id)) {
        $checkStmt = $db->prepare("SELECT id FROM menu_items WHERE id = ?");
        $checkStmt->execute([$id]);
        $exists = $checkStmt->fetch();
        if ($exists) {
            $upd = $db->prepare("
                UPDATE menu_items 
                SET name = ?, price = ?, price_iced_m = ?, description = ?, is_available = ?, category_id = ?,
                    image_url = CASE WHEN ? != '' THEN ? ELSE image_url END
                WHERE id = ?
            ");
            $upd->execute([$name, $price, $price, $desc, $isAvailable, $catId, $imageUrl, $imageUrl, $id]);
            jsonResponse(['success' => true, 'message' => "Item '{$name}' updated successfully.", 'item_id' => $id]);
        }
    }

    // Insert new item
    if (empty($id)) {
        $id = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name)) . '-' . substr(uniqid(), -4);
    }
    $finalImg = !empty($imageUrl) ? $imageUrl : 'images/menu/hc-spanish.webp';
    $ins = $db->prepare("
        INSERT INTO menu_items (id, category_id, name, price, price_iced_m, description, image_url, is_available)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $ins->execute([$id, $catId, $name, $price, $price, $desc, $finalImg, $isAvailable]);
    jsonResponse(['success' => true, 'message' => "Item '{$name}' created successfully.", 'item_id' => $id], 201);
}

// Handle Item Deletion
if ($method === 'DELETE') {
    requireRole(['admin', 'superadmin']);
    $id = trim($_GET['id'] ?? getJsonInput()['id'] ?? '');
    if (empty($id)) {
        jsonResponse(['success' => false, 'error' => 'Item ID is required for deletion.'], 400);
    }
    $db->prepare("DELETE FROM item_flavors WHERE item_id = ?")->execute([$id]);
    $db->prepare("DELETE FROM menu_items WHERE id = ?")->execute([$id]);
    jsonResponse(['success' => true, 'message' => "Item '{$id}' deleted."]);
}

// GET: Query Menu Items
$categoryFilter = $_GET['category'] ?? 'all';
$flavorFilter   = $_GET['flavor'] ?? null;

$query = "
    SELECT 
        m.id,
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
        m.is_available AS isAvailable
    FROM menu_items m
    JOIN categories c ON m.category_id = c.id
";

$conditions = [];
$params = [];
if ($categoryFilter !== 'all') {
    $conditions[] = "c.slug = ?";
    $params[] = $categoryFilter;
}

if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

$query .= " ORDER BY c.sort_order ASC, m.price DESC, m.name ASC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$items = $stmt->fetchAll();

// Fetch flavors for items
$flavorStmt = $db->query("SELECT item_id, flavor_slug, flavor_label FROM item_flavors");
$allFlavors = $flavorStmt->fetchAll();

$flavorMap = [];
$flavorLabelMap = [];
foreach ($allFlavors as $row) {
    $flavorMap[$row['item_id']][] = $row['flavor_slug'];
    $flavorLabelMap[$row['item_id']][] = $row['flavor_label'];
}

// Assemble final payload
$results = [];
foreach ($items as $item) {
    $itemId = $item['id'];
    $flavors = $flavorMap[$itemId] ?? [];
    $flavorLabels = $flavorLabelMap[$itemId] ?? [];

    if ($flavorFilter && !in_array($flavorFilter, $flavors)) {
        continue;
    }

    $tags = [];
    if ($item['isBestseller']) {
        $tags[] = 'Bestseller';
    }
    $tags[] = $item['category_name'];

    $results[] = [
        'id'           => $item['id'],
        'category'     => $item['category'],
        'name'         => $item['name'],
        'origin'       => $item['origin'],
        'elevation'    => $item['elevation'],
        'price'        => (float) $item['price'],
        'priceIcedM'   => (float) $item['priceIcedM'],
        'priceIcedL'   => $item['priceIcedL'] !== null ? (float) $item['priceIcedL'] : null,
        'priceHot'     => $item['priceHot'] !== null ? (float) $item['priceHot'] : null,
        'description'  => $item['description'],
        'image'        => $item['image'],
        'isAvailable'  => (bool) $item['isAvailable'],
        'tags'         => $tags,
        'flavors'      => $flavors,
        'flavorLabels' => $flavorLabels
    ];
}

jsonResponse([
    'success' => true,
    'count'   => count($results),
    'items'   => $results
]);
