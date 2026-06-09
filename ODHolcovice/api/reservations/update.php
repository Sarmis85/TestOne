<?php
// PUT /api/reservations/update.php  body: { id, status }
require_once __DIR__ . '/../config.php';
require_role('obsluha','vedouci','super');
$b = json_decode(file_get_contents('php://input'), true) ?? [];
if (empty($b['id']) || !in_array($b['status']??'', ['ceka','potvrzena','zrusena'], true)) {
    json_response(['error' => 'Neplatná data'], 422);
}
db()->prepare('UPDATE restaurant_reservations SET status = ? WHERE id = ?')
    ->execute([$b['status'], (int)$b['id']]);
json_response(['ok' => true]);
