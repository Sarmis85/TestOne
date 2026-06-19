<?php
/**
 * StoryOus OAuth 2.0 autentizace s cachováním tokenu
 */
require_once __DIR__ . '/config.php';

function storyous_get_token(): string {
    if (file_exists(STORYOUS_TOKEN_FILE)) {
        $cached = json_decode(file_get_contents(STORYOUS_TOKEN_FILE), true);
        if ($cached && time() < ($cached['expires_at'] - 60)) {
            return $cached['access_token'];
        }
    }

    if (empty(STORYOUS_CLIENT_ID) || empty(STORYOUS_CLIENT_SECRET)) {
        throw new RuntimeException('StoryOus API klíče nejsou nakonfigurovány');
    }

    $ch = curl_init(STORYOUS_LOGIN_URL);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'client_id'     => STORYOUS_CLIENT_ID,
            'client_secret' => STORYOUS_CLIENT_SECRET,
            'grant_type'    => 'client_credentials',
        ]),
        CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        throw new RuntimeException("StoryOus auth selhala: HTTP $httpCode — $response");
    }

    $data = json_decode($response, true);
    if (empty($data['access_token'])) {
        throw new RuntimeException('StoryOus auth: chybí access_token');
    }

    file_put_contents(STORYOUS_TOKEN_FILE, json_encode($data));
    return $data['access_token'];
}

function storyous_api(string $path, array $query = []): array {
    $token = storyous_get_token();
    $url = STORYOUS_API_URL . $path;
    if ($query) {
        $url .= '?' . http_build_query($query);
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $token,
            'Accept: application/json',
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        throw new RuntimeException("StoryOus API $path: HTTP $httpCode — $response");
    }

    return json_decode($response, true) ?? [];
}
