<?php
/**
 * GET /api/test-db.php — test připojení k databázi
 * PO OVĚŘENÍ SMAZAT!
 */
require_once __DIR__ . '/config.php';

try {
    $pdo = db();
    $version = $pdo->query('SELECT VERSION()')->fetchColumn();

    // Zjistíme existující tabulky
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

    json_response([
        'ok'      => true,
        'version' => $version,
        'tables'  => $tables,
        'count'   => count($tables),
        'message' => 'Připojení k databázi funguje!',
    ]);
} catch (PDOException $e) {
    json_response([
        'ok'    => false,
        'error' => $e->getMessage(),
        'hint'  => 'Zkontrolujte DB credentials v api/config.php a že DB existuje v WEDOS admin.',
    ], 500);
}
