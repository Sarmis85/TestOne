<?php
/**
 * POST /api/reservations/create.php
 * Body JSON: { name, phone, email?, date, time_from, time_to?, guests_range, note? }
 * Veřejný endpoint — nevyžaduje přihlášení
 */
require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Pouze POST'], 405);
}

$b = json_decode(file_get_contents('php://input'), true) ?? [];

// Validace povinných polí
$required = ['name','phone','date','time_from','guests_range'];
foreach ($required as $field) {
    if (empty(trim($b[$field] ?? ''))) {
        json_response(['error' => "Pole '$field' je povinné"], 422);
    }
}

// Datum nesmí být v minulosti
if (strtotime($b['date']) < strtotime('today')) {
    json_response(['error' => 'Datum rezervace musí být v budoucnosti'], 422);
}

// Přihlášený uživatel (volitelné)
session_start();
$user_id = $_SESSION['user_id'] ?? null;

$stmt = db()->prepare('
    INSERT INTO restaurant_reservations
      (user_id, name, phone, email, res_date, time_from, time_to, guests_range, note, status)
    VALUES
      (:user_id, :name, :phone, :email, :date, :time_from, :time_to, :guests_range, :note, "ceka")
');

$stmt->execute([
    ':user_id'     => $user_id,
    ':name'        => trim($b['name']),
    ':phone'       => trim($b['phone']),
    ':email'       => trim($b['email'] ?? '') ?: null,
    ':date'        => $b['date'],
    ':time_from'   => $b['time_from'],
    ':time_to'     => $b['time_to'] ?? null,
    ':guests_range'=> $b['guests_range'],
    ':note'        => trim($b['note'] ?? '') ?: null,
]);

$id = db()->lastInsertId();

// TODO: odeslat notifikační email obsluze

json_response([
    'ok'             => true,
    'reservation_id' => $id,
    'message'        => 'Rezervace přijata. Potvrdíme do 24 hodin.',
]);
