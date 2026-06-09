<?php
/**
 * GET /api/admin/stats.php
 * Dashboard KPI pro adminy — tržby, objednávky, rezervace, hodnocení
 */
require_once __DIR__ . '/../config.php';
require_role('vedouci','super','obsluha');

$db = db();

// Dnešní objednávky
$today = $db->query("SELECT COUNT(*) AS cnt, COALESCE(SUM(total_kc),0) AS revenue
    FROM restaurant_orders WHERE delivery_date = CURDATE() AND status != 'zrusena'")->fetch();

// Čekající rezervace
$pending_res = $db->query("SELECT COUNT(*) AS cnt FROM restaurant_reservations
    WHERE status = 'ceka' AND res_date >= CURDATE()")->fetch()['cnt'];

// Objednávky čekající na přípravu
$pending_orders = $db->query("SELECT COUNT(*) AS cnt FROM restaurant_orders
    WHERE status IN ('nova','prijata') AND delivery_date = CURDATE()")->fetch()['cnt'];

// Průměrné hodnocení (celkové)
$rating = $db->query("SELECT ROUND(AVG(rating),1) AS avg, COUNT(*) AS cnt
    FROM restaurant_menu_ratings")->fetch();

// Tržby za posledních 7 dní (pro sparkline)
$weekly = $db->query("SELECT delivery_date AS date, COALESCE(SUM(total_kc),0) AS revenue,
        COUNT(*) AS orders
    FROM restaurant_orders
    WHERE delivery_date >= DATE_SUB(CURDATE(),INTERVAL 6 DAY)
      AND status != 'zrusena'
    GROUP BY delivery_date ORDER BY delivery_date ASC")->fetchAll();

// Top jídla dle hodnocení
$top_foods = $db->query("SELECT name, category, rating_avg, rating_count
    FROM v_menu_item_ratings
    WHERE rating_count > 0 ORDER BY rating_avg DESC, rating_count DESC LIMIT 5")->fetchAll();

// Nadcházející akce s promo
$promo_events = $db->query("SELECT id, title, date_start, promote_homepage
    FROM portal_events WHERE is_published=1 AND date_start >= CURDATE()
    ORDER BY date_start LIMIT 3")->fetchAll();

json_response([
    'today' => [
        'orders'  => (int)$today['cnt'],
        'revenue' => (float)$today['revenue'],
    ],
    'pending_reservations' => (int)$pending_res,
    'pending_orders'       => (int)$pending_orders,
    'rating' => [
        'avg'   => (float)$rating['avg'],
        'count' => (int)$rating['cnt'],
    ],
    'weekly'      => $weekly,
    'top_foods'   => $top_foods,
    'promo_events'=> $promo_events,
]);
