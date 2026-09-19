<?php
/**
 * BeCoffee — Menu API Endpoint
 * Provides dynamic menu data with category & flavor mappings
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

$db = Database::getConnection();

$categoryFilter = $_GET['category'] ?? 'all';
$flavorFilter   = $_GET['flavor'] ?? null;

// Query menu items
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

    // If flavor filter is specified, check inclusion
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
