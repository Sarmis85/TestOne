<?php
// ── Načti nastavení ────────────────────────────────────────
$content     = @json_decode(@file_get_contents(__DIR__ . '/content.json'), true) ?? [];
$maintenance = $content['maintenance'] ?? true;

// ── Správa preview cookie ───────────────────────────────────
// /?preview=1  → nastaví cookie a přesměruje na plný web
// /?preview=0  → smaže cookie a vrátí na homepage
if (isset($_GET['preview'])) {
    if ($_GET['preview'] === '0') {
        setcookie('prh_preview', '', time() - 3600, '/');
        header('Location: /');
    } else {
        setcookie('prh_preview', '1', time() + 28800, '/'); // 8 hodin
        header('Location: /index_backup.html');
    }
    exit;
}

$preview = !empty($_COOKIE['prh_preview']);

// ── Routing ─────────────────────────────────────────────────
if (!$maintenance || $preview) {
    // Plný web
    header('Location: /index_backup.html');
    exit;
}

// Stránka v údržbě — zobraz placeholder, URL zůstane /
include __DIR__ . '/index.html';
