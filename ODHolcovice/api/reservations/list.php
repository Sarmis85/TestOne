<?php
/**
 * GET /api/reservations/list.php
 * ?status=ceka,potvrzena  ?from=YYYY-MM-DD  ?limit=50
 * Personál (obsluha+) → vše  |  Zákazník → vlastní  |  Nepřihlášen → nadcházející (admin fallback)
 */
require_once __DIR__ . '/../config.php';

// Volitelné přihlášení — admin stránka funguje i bez PHP session
if (session_status() === PHP_SESSION_NONE) session_start();
$sess  = $_SESSION ?? [];
$roles = $sess['roles'] ?? [];
$staff = !empty($sess['user_id']) && (bool)array_intersect($roles, ['super','vedouci','obsluha','kuchyn','rozvoz']);
$isCustomer = !empty($sess['user_id']) && !$staff;
$noAuth     = empty($sess['user_id']);

$where = ['1=1']; $params = [];

if ($noAuth || $staff) {
    // Admin bez session nebo přihlášený personál → všechny rezervace
    if (!empty($_GET['status'])) {
        $st = array_map('trim', explode(',', $_GET['status']));
        $ph = implode(',', array_fill(0, count($st), '?'));
        $where[] = "status IN ($ph)"; $params = array_merge($params, $st);
    }
    if (!empty($_GET['from'])) { $where[] = 'res_date >= ?'; $params[] = $_GET['from']; }
} elseif ($isCustomer) {
    // Přihlášený zákazník → jen vlastní
    $where[] = 'user_id = ?'; $params[] = $sess['user_id'];
}

$limit = min((int)($_GET['limit'] ?? 50), 200);
$stmt = db()->prepare('SELECT * FROM restaurant_reservations WHERE '
    . implode(' AND ', $where) . ' ORDER BY res_date ASC, time_from ASC LIMIT ' . $limit);
$stmt->execute($params);
json_response($stmt->fetchAll());
