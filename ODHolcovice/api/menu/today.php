<?php
/**
 * GET /api/menu/today.php
 * Vrátí dnešní menu jako JSON — používá view v_today_menu
 * Veřejný endpoint (bez autentizace)
 */
require_once __DIR__ . '/../config.php';

$stmt = db()->query('SELECT * FROM v_today_menu LIMIT 1');
$menu = $stmt->fetch();

if (!$menu) {
    json_response(['available' => false, 'message' => 'Dnešní menu zatím není sestaveno']);
}

// Sleva na polévku: 50% zaokrouhleno dolů na násobek 5
$soup_price_full      = (float)($menu['soup_price'] ?? 0);
$soup_price_discounted = floor(($soup_price_full * 0.5) / 5) * 5;

json_response([
    'available'   => true,
    'date'        => $menu['menu_date'],
    'is_weekend'  => (bool)$menu['is_weekend'],
    'soup' => [
        'id'               => $menu['soup_id'],
        'name'             => $menu['soup_name'],
        'price'            => $soup_price_full,
        'price_with_main'  => $soup_price_discounted,   // slevněná cena při objednání s hlavním
        'allergens'        => $menu['soup_allergens'],
    ],
    'mains' => array_filter([
        $menu['main1_id'] ? [
            'id'        => $menu['main1_id'],
            'name'      => $menu['main1_name'],
            'price'     => (float)$menu['main1_price'],
            'allergens' => $menu['main1_allergens'],
            'is_vege'   => false,
        ] : null,
        $menu['main2_id'] ? [
            'id'        => $menu['main2_id'],
            'name'      => $menu['main2_name'],
            'price'     => (float)$menu['main2_price'],
            'allergens' => $menu['main2_allergens'],
            'is_vege'   => false,
        ] : null,
        $menu['vege_id'] ? [
            'id'        => $menu['vege_id'],
            'name'      => $menu['vege_name'],
            'price'     => (float)$menu['vege_price'],
            'allergens' => $menu['vege_allergens'],
            'is_vege'   => true,
        ] : null,
    ]),
]);
