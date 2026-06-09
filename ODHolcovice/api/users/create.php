<?php
/**
 * POST /api/users/create.php
 * Body: { first_name, last_name, email, phone?, password, role }
 */
require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Pouze POST'], 405);
}

// Volitelné přihlášení
if (session_status() === PHP_SESSION_NONE) session_start();

$b = json_decode(file_get_contents('php://input'), true) ?? [];

$firstName = trim($b['first_name'] ?? '');
$lastName  = trim($b['last_name'] ?? '');
$email     = trim($b['email'] ?? '');
$phone     = trim($b['phone'] ?? '');
$password  = $b['password'] ?? '';
$role      = $b['role'] ?? '';

if (!$firstName || !$lastName) {
    json_response(['error' => 'Jméno a příjmení jsou povinné'], 422);
}
if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response(['error' => 'Neplatný e-mail'], 422);
}
if (strlen($password) < 4) {
    json_response(['error' => 'Heslo musí mít alespoň 4 znaky'], 422);
}

$allowedRoles = ['super','vedouci','obsluha','kuchyn','rozvoz','obec','zakaznik'];
if (!in_array($role, $allowedRoles, true)) {
    json_response(['error' => 'Neplatná role'], 422);
}

$pdo = db();

// Kontrola duplicity
$check = $pdo->prepare('SELECT id FROM portal_users WHERE email = ? LIMIT 1');
$check->execute([$email]);
if ($check->fetch()) {
    json_response(['error' => 'Uživatel s tímto e-mailem již existuje'], 409);
}

// Generujeme username z emailu
$username = explode('@', $email)[0];
$checkU = $pdo->prepare('SELECT id FROM portal_users WHERE username = ? LIMIT 1');
$checkU->execute([$username]);
if ($checkU->fetch()) {
    $username .= rand(10, 99);
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare('
        INSERT INTO portal_users (username, email, password_hash, first_name, last_name, phone, is_active)
        VALUES (?, ?, ?, ?, ?, ?, 1)
    ');
    $stmt->execute([
        $username, $email, password_hash($password, PASSWORD_DEFAULT),
        $firstName, $lastName, $phone ?: null
    ]);
    $userId = (int)$pdo->lastInsertId();

    // Přidáme roli
    $pdo->prepare('INSERT INTO portal_user_roles (user_id, role) VALUES (?, ?)')
        ->execute([$userId, $role]);

    $pdo->commit();

    json_response([
        'ok'       => true,
        'id'       => $userId,
        'username' => $username,
        'message'  => 'Uživatel vytvořen',
    ], 201);
} catch (PDOException $e) {
    $pdo->rollBack();
    json_response(['error' => 'Chyba databáze: ' . $e->getMessage()], 500);
}
