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
    if (!isset($b['id'])) json_response(['error' => 'Chybí id'], 422);

    // Accept either {id, field, value} or {id, fieldname: value}
    $allowed = ['name','category','price_kc','allergens','weight_g','is_vege','is_active',
                'price','portion','is_vegetarian']; // frontend aliases
    $fieldMap = ['price'=>'price_kc','portion'=>'weight_g','is_vegetarian'=>'is_vege'];

    if (isset($b['field'])) {
        // Legacy format: {id, field, value}
        $field = $fieldMap[$b['field']] ?? $b['field'];
        if (!in_array($field, ['name','category','price_kc','allergens','weight_g','is_vege','is_active'])) {
            json_response(['error' => 'Nepovolené pole'], 422);
        }
        db()->prepare("UPDATE restaurant_menu_items SET {$field} = ? WHERE id = ?")
            ->execute([$b['value'], $b['id']]);
    } else {
        // New format: {id, fieldname: value, ...}
        $updates = [];
        $vals = [];
        foreach ($b as $k => $v) {
            if ($k === 'id') continue;
            $col = $fieldMap[$k] ?? $k;
            if (in_array($col, ['name','category','price_kc','allergens','weight_g','is_vege','is_active'])) {
                $updates[] = "$col = ?";
                $vals[] = $v;
            }
        }
        if (empty($updates)) json_response(['error' => 'Žádná pole k aktualizaci'], 422);
        $vals[] = $b['id'];
        db()->prepare("UPDATE restaurant_menu_items SET " . implode(', ', $updates) . " WHERE id = ?")
            ->execute($vals);
    }
    json_response(['ok' => true]);
}

if ($method === 'DELETE') {
    require_role('vedouci', 'super');
    $b = json_decode(file_get_contents('php://input'), true) ?? [];
    if (!isset($b['id'])) json_response(['error' => 'Chybí id'], 422);
    db()->prepare("DELETE FROM restaurant_menu_items WHERE id = ?")->execute([$b['id']]);
    json_response(['ok' => true]);
}

json_response(['error' => 'Method not allowed'], 405);
