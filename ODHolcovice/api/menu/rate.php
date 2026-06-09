<?php
/**
 * POST /api/menu/rate.php
 * Body: { menu_item_id, rating (1-5), order_item_id? }
 */
require_once __DIR__ . '/../config.php';
$sess = require_auth();

$b = json_decode(file_get_contents('php://input'), true) ?? [];
$item_id = (int)($b['menu_item_id'] ?? 0);
$rating  = (int)($b['rating'] ?? 0);
$oi_id   = !empty($b['order_item_id']) ? (int)$b['order_item_id'] : null;

if (!$item_id || $rating < 1 || $rating > 5) {
    json_response(['error' => 'Neplatné hodnocení'], 422);
}

// Ověřit že jídlo zákazník skutečně objednal
$check = db()->prepare('SELECT oi.id FROM restaurant_order_items oi
    JOIN restaurant_orders o ON o.id = oi.order_id
    WHERE oi.menu_item_id = ? AND o.user_id = ? AND o.status = "dorucena" LIMIT 1');
$check->execute([$item_id, $sess['user_id']]);
if (!$check->fetch()) {
    json_response(['error' => 'Toto jídlo nemůžete hodnotit'], 403);
}

$stmt = db()->prepare('INSERT INTO restaurant_menu_ratings
    (menu_item_id, user_id, order_item_id, rating)
    VALUES (?,?,?,?)
    ON DUPLICATE KEY UPDATE rating = VALUES(rating)');
$stmt->execute([$item_id, $sess['user_id'], $oi_id, $rating]);

json_response(['ok' => true]);
