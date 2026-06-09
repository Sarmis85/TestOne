<?php
/**
 * GET  /api/menu/items.php[?category=Hlavní&active=1]  — seznam jídel s hodnoceními
 * POST /api/menu/items.php  — přidat jídlo (vedoucí+)
 * PUT  /api/menu/items.php  — upravit jídlo (vedoucí+)  body: {id, field, value}
 */
require_once __DIR__ . '/../config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $where = ['1=1'];
    $params = [];
    if (!empty($_GET['category'])) { $where[] = 'category = ?'; $params[] = $_GET['category']; }
    if (isset($_GET['active']))    { $where[] = 'is_active = ?'; $params[] = (int)$_GET['active']; }

    $sql = 'SELECT id, name, category, price_kc, allergens, weight_g, is_vege, is_active,
                   COALESCE(rating_count,0) AS rating_count,
                   COALESCE(rating_avg,0)   AS rating_avg
            FROM v_menu_item_ratings
            WHERE ' . implode(' AND ', $where) . '
            ORDER BY FIELD(category,"Polévka","Předkrm","Hlavní","Vegetariánské","Dezert","Nápoj"), name';

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    json_response($stmt->fetchAll());
}

if ($method === 'POST') {
    require_role('vedouci', 'super');
    $b = json_decode(file_get_contents('php://input'), true) ?? [];
    $stmt = db()->prepare('INSERT INTO restaurant_menu_items
        (name,category,price_kc,allergens,weight_g,is_vege,is_active)
        VALUES (?,?,?,?,?,?,1)');
    $stmt->execute([$b['name'],$b['category'],(float)$b['price_kc'],
                    $b['allergens']??null,$b['weight_g']??null,(int)($b['is_vege']??0)]);
    json_response(['ok' => true, 'id' => db()->lastInsertId()], 201);
}

if ($method === 'PUT') {
    require_role('vedouci', 'super');
    $b = json_decode(file_get_contents('php://input'), true) ?? [];
    $allowed = ['name','category','price_kc','allergens','weight_g','is_vege','is_active'];
    if (!in_array($b['field'] ?? '', $allowed, true)) {
        json_response(['error' => 'Nepovolené pole'], 422);
    }
    db()->prepare("UPDATE restaurant_menu_items SET {$b['field']} = ? WHERE id = ?")
        ->execute([$b['value'], $b['id']]);
    json_response(['ok' => true]);
}

json_response(['error' => 'Method not allowed'], 405);
