<?php
/**
 * Konfigurace DB připojení — Obecní dům Holčovice
 * WEDOS MariaDB: server je ve tvaru wmX.wedos.net nebo mdX.wedos.net
 *
 * ⚠️  Tento soubor NESMÍ být přístupný z webu.
 *     Ideálně přesuňte mimo web root nebo ochraňte .htaccess (viz níže).
 */

define('DB_HOST', 'md431.wedos.net');
define('DB_NAME', 'd304841_prh');
define('DB_USER', 'a304841_prh');
define('DB_PASS', 'NE5rwHv7');
define('DB_CHARSET', 'utf8mb4');

/**
 * Vrátí PDO připojení (singleton)
 */
function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST, DB_NAME, DB_CHARSET
        );
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}

/**
 * Odešle JSON odpověď a ukončí skript
 */
function json_response(mixed $data, int $status = 200): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');   // upravit na konkrétní doménu v produkci
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Vyžaduje přihlášení — vrátí data session nebo 401
 */
function require_auth(): array {
    session_start();
    if (empty($_SESSION['user_id'])) {
        json_response(['error' => 'Nejste přihlášeni'], 401);
    }
    return $_SESSION;
}

/**
 * Vyžaduje konkrétní roli
 */
function require_role(string ...$roles): array {
    $session = require_auth();
    $user_roles = $session['roles'] ?? [];
    foreach ($roles as $role) {
        if (in_array($role, $user_roles, true) || in_array('super', $user_roles, true)) {
            return $session;
        }
    }
    json_response(['error' => 'Nemáte oprávnění'], 403);
}
