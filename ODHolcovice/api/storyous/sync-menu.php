<?php
/**
 * GET /api/storyous/sync-menu.php — stáhne menu ze StoryOus a uloží do DB
 *
 * Mapování: StoryOus produkty → restaurant_menu_items
 * Kategorie StoryOus → naše kategorie (Polévka, Hlavní, Vegetariánské...)
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';

if (empty(STORYOUS_MERCHANT_ID)) {
    json_response(['error' => 'STORYOUS_MERCHANT_ID není nastaven'], 500);
}

$query = ['depth' => '-1'];
if (!empty(STORYOUS_PLACE_ID)) {
    $query['placeId'] = STORYOUS_PLACE_ID;
}

try {
    $menu = storyous_api('/menu/' . STORYOUS_MERCHANT_ID, $query);
} catch (RuntimeException $e) {
    json_response(['error' => $e->getMessage()], 500);
}

$pdo = db();
$synced = 0;
$skipped = 0;

$upsert = $pdo->prepare('
    INSERT INTO restaurant_menu_items
        (storyous_product_id, name, category, price_kc, is_vege, is_active)
    VALUES (?, ?, ?, ?, ?, 1)
    ON DUPLICATE KEY UPDATE
        name = VALUES(name),
        category = VALUES(category),
        price_kc = VALUES(price_kc),
        is_vege = VALUES(is_vege)
');

$categoryMap = [];

function extractItems(array $items, string $parentCategory, array &$categoryMap): array {
    $products = [];
    foreach ($items as $item) {
        if (isset($item['categoryId'])) {
            $catName = $item['name'] ?? $parentCategory;
            $categoryMap[$item['categoryId']] = $catName;
            if (!empty($item['items'])) {
                $products = array_merge($products, extractItems($item['items'], $catName, $categoryMap));
            }
        } elseif (isset($item['productId'])) {
            $item['_category'] = $parentCategory;
            $products[] = $item;
        }
    }
    return $products;
}

$products = extractItems($menu['items'] ?? [], 'Hlavní', $categoryMap);

$storyousCategoryToLocal = [
    'polévka' => 'Polévka', 'polévky' => 'Polévka', 'soup' => 'Polévka', 'soups' => 'Polévka',
    'předkrm' => 'Předkrm', 'předkrmy' => 'Předkrm', 'starter' => 'Předkrm',
    'hlavní' => 'Hlavní', 'hlavní jídla' => 'Hlavní', 'main' => 'Hlavní',
    'vegetariánské' => 'Vegetariánské', 'vege' => 'Vegetariánské', 'vegetarian' => 'Vegetariánské',
    'dezert' => 'Dezert', 'dezerty' => 'Dezert', 'dessert' => 'Dezert',
    'nápoj' => 'Nápoj', 'nápoje' => 'Nápoj', 'drink' => 'Nápoj', 'drinks' => 'Nápoj',
    'pivo' => 'Nápoj', 'víno' => 'Nápoj',
];

foreach ($products as $product) {
    $rawCat = mb_strtolower($product['_category']);
    $localCat = $storyousCategoryToLocal[$rawCat] ?? 'Hlavní';

    $price = 0;
    $pv = $product['placeValues'] ?? $product['placesValues'][STORYOUS_PLACE_ID] ?? [];
    if (isset($pv['priceLevels']['default']['price'])) {
        $price = $pv['priceLevels']['default']['price'];
    } elseif (isset($pv['priceLevels'])) {
        $first = reset($pv['priceLevels']);
        $price = $first['price'] ?? 0;
    }

    $labels = $product['labels'] ?? [];
    $isVege = in_array('vegetarian', $labels) || in_array('vegan', $labels) || $localCat === 'Vegetariánské';

    try {
        $upsert->execute([
            $product['productId'],
            $product['name'],
            $localCat,
            $price,
            (int)$isVege,
        ]);
        $synced++;
    } catch (PDOException $e) {
        $skipped++;
    }
}

json_response([
    'ok'      => true,
    'synced'  => $synced,
    'skipped' => $skipped,
    'total_from_storyous' => count($products),
    'categories_found'    => array_values(array_unique($categoryMap)),
]);
