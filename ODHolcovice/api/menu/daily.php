<?php
/**
 * GET /api/menu/daily.php[?date=YYYY-MM-DD]  — denní menu pro datum (default: dnes)
 * POST /api/menu/daily.php  — uložit denní menu  (vedoucí+)
 */
require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Range mode: ?from=YYYY-MM-DD&to=YYYY-MM-DD  — returns array of days
    if (!empty($_GET['from']) && !empty($_GET['to'])) {
        $stmt = db()->prepare('SELECT * FROM v_today_menu WHERE menu_date BETWEEN ? AND ? ORDER BY menu_date');
        $stmt->execute([$_GET['from'], $_GET['to']]);
        $rows = $stmt->fetchAll();
        $days = array_map(function($m) {
            return [
                'menu_date' => $m['menu_date'],
                'soup_id'   => $m['soup_id'],   'soup_name'  => $m['soup_name'],
                'main1_id'  => $m['main1_id'],  'main1_name' => $m['main1_name'],
                'main2_id'  => $m['main2_id'],  'main2_name' => $m['main2_name'],
                'vege_id'   => $m['vege_id'],   'vege_name'  => $m['vege_name'],
            ];
        }, $rows);
        json_response(['days' => $days]);
    }

    // Single day mode: ?date=YYYY-MM-DD
    $date = $_GET['date'] ?? date('Y-m-d');
    $stmt = db()->prepare('SELECT * FROM v_today_menu WHERE menu_date = ?');
    $stmt->execute([$date]);
    $menu = $stmt->fetch();
    if (!$menu) { json_response(['available' => false]); }

    $soup_full = (float)($menu['soup_price'] ?? 0);
    $soup_disc = floor($soup_full * 0.5 / 5) * 5; // 50% sleva, zaokrouhlení na 5

    json_response([
        'available'  => true,
        'date'       => $menu['menu_date'],
        'is_weekend' => (bool)$menu['is_weekend'],
        'soup'  => $menu['soup_id']  ? ['id'=>$menu['soup_id'],  'name'=>$menu['soup_name'],
                      'price'=>$soup_full, 'price_with_main'=>$soup_disc,
                      'allergens'=>$menu['soup_allergens']] : null,
        'main1' => $menu['main1_id'] ? ['id'=>$menu['main1_id'], 'name'=>$menu['main1_name'],
                      'price'=>(float)$menu['main1_price'], 'allergens'=>$menu['main1_allergens'], 'is_vege'=>false] : null,
        'main2' => $menu['main2_id'] ? ['id'=>$menu['main2_id'], 'name'=>$menu['main2_name'],
                      'price'=>(float)$menu['main2_price'], 'allergens'=>$menu['main2_allergens'], 'is_vege'=>false] : null,
        'vege'  => $menu['vege_id']  ? ['id'=>$menu['vege_id'],  'name'=>$menu['vege_name'],
                      'price'=>(float)$menu['vege_price'],  'allergens'=>$menu['vege_allergens'],  'is_vege'=>true] : null,
    ]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Volitelné přihlášení — admin panel funguje i bez PHP session
    if (session_status() === PHP_SESSION_NONE) session_start();
    $b = json_decode(file_get_contents('php://input'), true) ?? [];
    $date = $b['menu_date'] ?? $b['date'] ?? date('Y-m-d');
    $stmt = db()->prepare('REPLACE INTO restaurant_daily_menu
        (menu_date, soup_id, main1_id, main2_id, vege_id, is_weekend, note)
        VALUES (?,?,?,?,?,?,?)');
    $stmt->execute([$date, $b['soup_id']??null, $b['main1_id']??null,
                    $b['main2_id']??null, $b['vege_id']??null,
                    (int)($b['is_weekend']??0), $b['note']??null]);
    json_response(['ok' => true]);
}

json_response(['error' => 'Method not allowed'], 405);
