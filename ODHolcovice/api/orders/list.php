<?php
/**
 * GET /api/orders/list.php
 * Zákazník → vlastní objednávky
 * Obsluha/kuchyně/rozvoz/vedoucí → všechny objednávky (s filtry)
 *   ?date=YYYY-MM-DD  ?status=nova,prijata,...  ?limit=20
 */
require_once __DIR__ . '/../config.php';
$sess = require_auth();

$roles     = $sess['roles'] ?? [];
$isStaff   = array_intersect($roles, ['super','vedouci','obsluha','kuchyn','rozvoz']);
$userId    = $sess['user_id'];

$where  = ['1=1']; $params = [];

if ($isStaff) {
    // Personál vidí vše — filtry z query stringu
    if (!empty($_GET['date']))   { $where[] = 'o.delivery_date = ?'; $params[] = $_GET['date']; }
    if (!empty($_GET['status'])) {
        $statuses = array_map('trim', explode(',', $_GET['status']));
        $placeholders = implode(',', array_fill(0, count($statuses), '?'));
        $where[] = "o.status IN ($placeholders)";
        $params  = array_merge($params, $statuses);
    }
    // Rozvoz vidí pouze dnešní doručovací objednávky
    if (in_array('rozvoz', $roles) && !in_array('super',$roles) && !in_array('vedouci',$roles)) {
        $where[] = 'o.delivery_date = CURDATE()';
        $where[] = "o.status NOT IN ('zrusena')";
    }
    // Kuchyně vidí pouze dnešní nepřipravené
    if (in_array('kuchyn', $roles) && !in_array('super',$roles) && !in_array('vedouci',$roles)) {
        $where[] = 'o.delivery_date = CURDATE()';
        $where[] = "o.status IN ('nova','prijata','pripravuje')";
    }
} else {
    // Zákazník vidí pouze své
    $where[] = 'o.user_id = ?'; $params[] = $userId;
}

$limit = min((int)($_GET['limit'] ?? 50), 200);
$sql = "SELECT o.id, o.delivery_date, o.delivery_address, o.contact_name, o.contact_phone,
               o.status, o.total_kc, o.note, o.created_at,
               GROUP_CONCAT(CONCAT(oi.quantity,'× ',mi.name) ORDER BY mi.category SEPARATOR ' | ') AS items_summary,
               GROUP_CONCAT(CONCAT(oi.id,':',mi.id,':',mi.name,':',oi.price_at_time) SEPARATOR '||') AS items_raw
        FROM restaurant_orders o
        JOIN restaurant_order_items oi ON oi.order_id = o.id
        JOIN restaurant_menu_items  mi ON mi.id = oi.menu_item_id
        WHERE " . implode(' AND ', $where) . "
        GROUP BY o.id
        ORDER BY o.created_at DESC
        LIMIT $limit";

$stmt = db()->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

// Rozparsovat items_raw do strukturovaných položek
foreach ($rows as &$row) {
    $row['items'] = [];
    foreach (explode('||', $row['items_raw'] ?? '') as $raw) {
        [$oi_id, $mi_id, $name, $price] = explode(':', $raw, 4);
        $row['items'][] = ['order_item_id'=>(int)$oi_id,'menu_item_id'=>(int)$mi_id,
                           'name'=>$name,'price'=>(float)$price];
    }
    unset($row['items_raw']);
}

json_response($rows);
