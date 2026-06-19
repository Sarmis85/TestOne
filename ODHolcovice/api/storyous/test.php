<?php
/**
 * GET /api/storyous/test.php — test StoryOus připojení
 *
 * 1. Ověří autentizaci
 * 2. Vrátí merchantId, placeId a základní info
 * 3. PO OVĚŘENÍ SMAZAT nebo uzamknout!
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';

if (empty(STORYOUS_CLIENT_ID)) {
    json_response([
        'ok'    => false,
        'error' => 'Vyplňte STORYOUS_CLIENT_ID a STORYOUS_CLIENT_SECRET v api/storyous/config.php',
        'hint'  => 'Požádejte StoryOus podporu o API přístupové údaje (client_id + client_secret).',
    ]);
}

try {
    $token = storyous_get_token();
} catch (RuntimeException $e) {
    json_response(['ok' => false, 'error' => 'Auth selhala: ' . $e->getMessage()], 500);
}

$ch = curl_init(STORYOUS_API_URL . '/merchants');
curl_setopt_array($ch, [
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $token,
        'Accept: application/json',
    ],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 15,
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$data = json_decode($response, true);

json_response([
    'ok'        => $httpCode === 200,
    'http_code' => $httpCode,
    'message'   => $httpCode === 200
        ? 'StoryOus připojení funguje! Níže najdete merchantId a placeId — vložte do config.php.'
        : 'Chyba při získávání merchantů',
    'merchants' => $data,
    'hint'      => 'Zkopírujte merchantId a placeId do api/storyous/config.php',
]);
