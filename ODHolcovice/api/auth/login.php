<?php
/**
 * POST /api/auth/login.php
 * Body: { "login": "admin|jan.novak@example.cz", "password": "heslo123" }
 * Přijímá username i email — funguje pro personál i zákazníky
 */
require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Pouze POST'], 405);
}

$body = json_decode(file_get_contents('php://input'), true);
$login    = trim($body['login']    ?? '');
$password = trim($body['password'] ?? '');

if (!$login || !$password) {
    json_response(['error' => 'Vyplňte přihlašovací jméno a heslo'], 422);
}

// Hledáme podle username NEBO emailu
$stmt = db()->prepare('
    SELECT u.id, u.username, u.email, u.password_hash,
           u.first_name, u.last_name, u.is_active
    FROM portal_users u
    WHERE u.username = :login OR u.email = :login
    LIMIT 1
');
$stmt->execute([':login' => $login]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    json_response(['error' => 'Nesprávné přihlašovací údaje'], 401);
}

if (!$user['is_active']) {
    json_response(['error' => 'Účet je deaktivován'], 403);
}

// Načteme role
$roleStmt = db()->prepare('
    SELECT role FROM portal_user_roles WHERE user_id = ?
');
$roleStmt->execute([$user['id']]);
$roles = $roleStmt->fetchAll(PDO::FETCH_COLUMN);

// Aktualizujeme last_login
db()->prepare('UPDATE portal_users SET last_login = NOW() WHERE id = ?')
    ->execute([$user['id']]);

// Uložíme session
session_start();
session_regenerate_id(true);
$_SESSION['user_id']    = $user['id'];
$_SESSION['username']   = $user['username'];
$_SESSION['first_name'] = $user['first_name'];
$_SESSION['last_name']  = $user['last_name'];
$_SESSION['roles']      = $roles;

json_response([
    'ok'         => true,
    'user_id'    => $user['id'],
    'username'   => $user['username'],
    'first_name' => $user['first_name'],
    'last_name'  => $user['last_name'],
    'roles'      => $roles,
    // Redirect hint pro frontend
    'redirect'   => in_array('super', $roles) || in_array('vedouci', $roles) || in_array('obsluha', $roles)
                    || in_array('kuchyn', $roles) || in_array('rozvoz', $roles) || in_array('obec', $roles)
                    ? '../admin/index.html'
                    : '../account/profile.html',
]);
