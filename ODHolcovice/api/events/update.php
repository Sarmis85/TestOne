<?php
// PUT /api/events/update.php  body: { id, field, value }
// Povolená pole: promote_homepage, is_published, title, body, date_start, date_end, location
require_once __DIR__ . '/../config.php';
require_role('obec','vedouci','super');
$b = json_decode(file_get_contents('php://input'), true) ?? [];
$allowed = ['promote_homepage','is_published','title','body','date_start','date_end','location','image_url'];
if (!in_array($b['field']??'', $allowed, true) || empty($b['id'])) {
    json_response(['error' => 'Neplatná data'], 422);
}
db()->prepare("UPDATE portal_events SET {$b['field']} = ? WHERE id = ?")
    ->execute([$b['value'], (int)$b['id']]);
json_response(['ok' => true]);
