<?php
/**
 * PUT /api/orders/update.php
 * Body: { id, status }
 * Kuchyně: nova→prijata→pripravuje
 * Rozvoz:  pripravuje→vyrazi→dorucena
 * Vedoucí/super: libovolný přechod
 */
require_once __DIR__ . '/../config.php';
$sess = require_role('obsluha','kuchyn','rozvoz','vedouci','super');

$b      = json_decode(file_get_contents('php://input'), true) ?? [];
$id     = (int)($b['id'] ?? 0);
$status = $b['status'] ?? '';

$allowed = ['nova','prijata','pripravuje','vyrazi','dorucena','zrusena'];
if (!$id || !in_array($status, $allowed, true)) {
    json_response(['error' => 'Neplatná data'], 422);
}

// Role restrictions
$roles = $sess['roles'];
if (in_array('kuchyn', $roles) && !array_intersect($roles, ['super','vedouci'])) {
    if (!in_array($status, ['prijata','pripravuje'], true)) {
        json_response(['error' => 'Nedostatečné oprávnění pro tento stav'], 403);
    }
}
if (in_array('rozvoz', $roles) && !array_intersect($roles, ['super','vedouci'])) {
    if (!in_array($status, ['vyrazi','dorucena'], true)) {
        json_response(['error' => 'Nedostatečné oprávnění pro tento stav'], 403);
    }
}

db()->prepare('UPDATE restaurant_orders SET status = ? WHERE id = ?')
    ->execute([$status, $id]);

json_response(['ok' => true]);
