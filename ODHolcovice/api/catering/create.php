<?php
/**
 * POST /api/catering/create.php
 * Body JSON: { contact_name, phone, email?, event_type?, event_date?, guests?, venue?, note? }
 * Veřejný endpoint — nevyžaduje přihlášení
 */
require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Pouze POST'], 405);
}

$b = json_decode(file_get_contents('php://input'), true) ?? [];

$required = ['contact_name', 'phone'];
foreach ($required as $f) {
    if (empty(trim($b[$f] ?? ''))) {
        json_response(['error' => "Pole '$f' je povinné"], 422);
    }
}

// Sestavit note z více polí
$note_parts = [];
if (!empty($b['event_type'])) $note_parts[] = 'Typ akce: ' . $b['event_type'];
if (!empty($b['venue']))      $note_parts[] = 'Prostor: '  . $b['venue'];
if (!empty($b['note']))       $note_parts[] = $b['note'];
$full_note = implode("\n", $note_parts) ?: null;

$stmt = db()->prepare('
    INSERT INTO restaurant_catering_requests
      (contact_name, phone, email, event_date, guests, note, status)
    VALUES
      (:name, :phone, :email, :event_date, :guests, :note, "nova")
');

$stmt->execute([
    ':name'       => trim($b['contact_name']),
    ':phone'      => trim($b['phone']),
    ':email'      => trim($b['email'] ?? '') ?: null,
    ':event_date' => !empty($b['event_date']) ? $b['event_date'] : null,
    ':guests'     => !empty($b['guests']) ? (int)preg_replace('/[^0-9]/', '', $b['guests']) : null,
    ':note'       => $full_note,
]);

json_response([
    'ok'      => true,
    'id'      => db()->lastInsertId(),
    'message' => 'Poptávka přijata. Ozveme se vám do 48 hodin.',
]);
