<?php
/**
 * GET /api/reservations/list.php
 * ?status=ceka,potvrzena  ?from=YYYY-MM-DD  ?limit=50
 * Personál (obsluha+) → vše  |  Zákazník → vlastní
 */
require_once __DIR__ . '/../config.php';
$sess  = require_auth();
$roles = $sess['roles'] ?? [];
$staff = (bool)array_intersect($roles, ['super','vedouci','obsluha','kuchyn']);

$where = ['1=1']; $params = [];

if ($staff) {
    if (!empty($_GET['status'])) {
        $st = array_map('trim', explode(',', $_GET['status']));
        $ph = implode(',', array_fill(0, count($st), '?'));
        $where[] = "status IN ($ph)"; $params = array_merge($params, $st);
    }
    if (!empty($_GET['from'])) { $where[] = 'res_date >= ?'; $params[] = $_GET['from']; }
} else {
    $where[] = 'user_id = ?'; $params[] = $sess['user_id'];
}

$limit = min((int)($_GET['limit'] ?? 50), 200);
$stmt = db()->prepare('SELECT * FROM restaurant_reservations WHERE '
    . implode(' AND ', $where) . ' ORDER BY res_date ASC, time_from ASC LIMIT ' . $limit);
$stmt->execute($params);
json_response($stmt->fetchAll());
