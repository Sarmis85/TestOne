<?php
/**
 * GET /api/seed/users.php — vytvoří výchozí uživatele pro testování
 * Heslo pro všechny: test1234
 */
require_once __DIR__ . '/../config.php';

$pdo = db();

// Zkontrolujeme / vytvoříme tabulky
try {
    $pdo->query('SELECT 1 FROM portal_users LIMIT 1');
} catch (PDOException $e) {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS portal_users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            first_name VARCHAR(100),
            last_name VARCHAR(100),
            phone VARCHAR(20),
            is_active TINYINT(1) DEFAULT 1,
            last_login DATETIME,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
}

try {
    $pdo->query('SELECT 1 FROM portal_user_roles LIMIT 1');
} catch (PDOException $e) {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS portal_user_roles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            role VARCHAR(30) NOT NULL,
            UNIQUE KEY (user_id, role),
            FOREIGN KEY (user_id) REFERENCES portal_users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
}

$hash = password_hash('test1234', PASSWORD_DEFAULT);

$users = [
    ['admin',    'admin@obecnidumholcovice.cz',    'Super',  'Admin',    null,               ['super']],
    ['vesela',   'vesela@obecnidumholcovice.cz',   'Jana',   'Veselá',   '+420 604 111 222', ['vedouci']],
    ['novotny',  'novotny@obecnidumholcovice.cz',  'Tomáš',  'Novotný',  '+420 604 333 444', ['obsluha']],
    ['kral',     'kral@obecnidumholcovice.cz',     'Pavel',  'Král',     '+420 775 555 666', ['kuchyn']],
    ['horak',    'horak@obecnidumholcovice.cz',    'Martin', 'Horák',    '+420 720 777 888', ['rozvoz']],
    ['fiserova', 'fiserova@obecnidumholcovice.cz', 'Eva',    'Fišerová', '+420 608 999 000', ['obec']],
    ['zakaznik', 'zakaznik@test.cz',               'Jan',    'Testový',  '+420 777 000 111', ['zakaznik']],
];

$inserted = 0;
$skipped = 0;

foreach ($users as [$username, $email, $first, $last, $phone, $roles]) {
    $check = $pdo->prepare('SELECT id FROM portal_users WHERE username = ? OR email = ? LIMIT 1');
    $check->execute([$username, $email]);
    $existing = $check->fetch();

    if ($existing) {
        $skipped++;
        continue;
    }

    $pdo->prepare('INSERT INTO portal_users (username, email, password_hash, first_name, last_name, phone, is_active) VALUES (?,?,?,?,?,?,1)')
        ->execute([$username, $email, $hash, $first, $last, $phone]);
    $userId = (int)$pdo->lastInsertId();

    foreach ($roles as $role) {
        $pdo->prepare('INSERT INTO portal_user_roles (user_id, role) VALUES (?,?)')
            ->execute([$userId, $role]);
    }
    $inserted++;
}

json_response([
    'ok'       => true,
    'inserted' => $inserted,
    'skipped'  => $skipped,
    'password' => 'test1234 (pro všechny)',
    'message'  => "Vloženo $inserted uživatelů, přeskočeno $skipped duplicit.",
]);
