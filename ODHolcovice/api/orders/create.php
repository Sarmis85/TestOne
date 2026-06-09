<?php
/**
 * POST /api/orders/create.php
 * Body: { delivery_date, items:[{menu_item_id,quantity,price}], total_kc, delivery_address?, note? }
 */
require_once __DIR__ . '/../config.php';

$b = json_decode(file_get_contents('php://input'), true) ?? [];

if (empty($b['items']) || !is_array($b['items'])) {
    json_response(['error' => 'Košík je prázdný'], 422);
}

// Ověřit čas objednávky (do 10:30 na dnešek)
$date = $b['delivery_date'] ?? date('Y-m-d');
if ($date === date('Y-m-d') && date('H:i') > '10:30') {
    json_response(['error' => 'Objednávky pro dnešek přijímáme do 10:30'], 422);
}

session_start();
$user_id = $_SESSION['user_id'] ?? null;

// Ověřit ceny z DB (ochrana proti manipulaci)
$ids = array_map(fn($i) => (int)$i['menu_item_id'], $b['items']);
$ph  = implode(',', array_fill(0, count($ids), '?'));
$prices = db()->prepare("SELECT id, price_kc FROM restaurant_menu_items WHERE id IN ($ph) AND is_active = 1");
$prices->execute($ids);
$priceMap = array_column($prices->fetchAll(), 'price_kc', 'id');

// Spočítat total z DB cen
$total = 0;
$hasSoup = false; $hasMain = false;
foreach ($b['items'] as $item) {
    $id = (int)$item['menu_item_id'];
    if (!isset($priceMap[$id])) { json_response(['error' => "Jídlo #$id není dostupné"], 422); }
    // Jednoduché zjištění kategorie pro slevu na polévku
    $cat = db()->prepare('SELECT category FROM restaurant_menu_items WHERE id=?');
    $cat->execute([$id]); $c = $cat->fetchColumn();
    if ($c === 'Polévka') $hasSoup = true;
    if (in_array($c, ['Hlavní','Vegetariánské'])) $hasMain = true;
}
foreach ($b['items'] as $item) {
    $id  = (int)$item['menu_item_id'];
    $qty = max(1, (int)($item['quantity'] ?? 1));
    $cat = db()->prepare('SELECT category FROM restaurant_menu_items WHERE id=?');
    $cat->execute([$id]); $c = $cat->fetchColumn();
    $price = (float)$priceMap[$id];
    if ($c === 'Polévka' && $hasSoup && $hasMain) {
        $price = floor($price * 0.5 / 5) * 5; // 50% sleva na polévku
    }
    $total += $price * $qty;
}

// Uložit objednávku
$db = db();
$db->beginTransaction();
try {
    $stmt = $db->prepare('INSERT INTO restaurant_orders
        (user_id, delivery_date, delivery_address, contact_name, contact_phone, status, total_kc, note)
        VALUES (?,?,?,?,?,?,?,?)');
    $stmt->execute([
        $user_id, $date,
        $b['delivery_address'] ?? ($_SESSION['default_address'] ?? null),
        $b['contact_name'] ?? ($_SESSION['first_name'].' '.($_SESSION['last_name']??'')),
        $b['contact_phone'] ?? null,
        'nova', $total, $b['note'] ?? null,
    ]);
    $order_id = $db->lastInsertId();

    $itemStmt = $db->prepare('INSERT INTO restaurant_order_items
        (order_id, menu_item_id, quantity, price_at_time) VALUES (?,?,?,?)');
    foreach ($b['items'] as $item) {
        $id  = (int)$item['menu_item_id'];
        $qty = max(1,(int)($item['quantity']??1));
        $cat = db()->prepare('SELECT category FROM restaurant_menu_items WHERE id=?');
        $cat->execute([$id]); $c = $cat->fetchColumn();
        $price = (float)$priceMap[$id];
        if ($c === 'Polévka' && $hasSoup && $hasMain) $price = floor($price*0.5/5)*5;
        $itemStmt->execute([$order_id, $id, $qty, $price]);
    }
    $db->commit();
} catch (Exception $e) {
    $db->rollBack();
    json_response(['error' => 'Chyba při ukládání objednávky'], 500);
}

json_response(['ok' => true, 'order_id' => $order_id, 'total_kc' => $total], 201);
