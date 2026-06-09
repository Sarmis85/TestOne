<?php
/**
 * JEDNORÁZOVÝ SETUP — nastavení hesel testovacích uživatelů
 * ─────────────────────────────────────────────────────────
 * 1. Nahrajte tento soubor do kořene webu (vedle index.html)
 * 2. Otevřete v prohlížeči: https://od.prorozvojholcovic.cz/setup.php
 * 3. Po úspěšném spuštění soubor OKAMŽITĚ SMAŽTE (nebo přejmenujte)
 *
 * ⚠️  Nikdy nenechávejte tento soubor na serveru déle než nutno!
 */

// Jednoduchá ochrana — změňte na vlastní tajný klíč
define('SETUP_KEY', 'holcovice2025setup');

if (($_GET['key'] ?? '') !== SETUP_KEY) {
    http_response_code(403);
    die('<h2>403 Přístup zakázán</h2><p>Přidejte ?key=holcovice2025setup do URL.</p>');
}

require_once __DIR__ . '/api/config.php';

// ── Testovací uživatelé: [id, username, heslo] ──────────────
// Hesla změňte před spuštěním na skutečná provozní hesla!
$users = [
    [1,  'admin',                'ZmenteHeslo!2025'],
    [2,  'vedouci',              'Vedouci2025!'],
    [3,  'obsluha1',             'Obsluha1heslo!'],
    [4,  'obsluha2',             'Obsluha2heslo!'],
    [5,  'kuchyn1',              'Kuchyn1heslo!'],
    [6,  'kuchyn2',              'Kuchyn2heslo!'],
    [7,  'rozvoz1',              'Rozvoz1heslo!'],
    [8,  'sprava.obce',          'Obec2025heslo!'],
    [9,  'jan.novak@example.cz', 'Zakaznik1!'],
    [10, 'marie.free@seznam.cz', 'Zakaznik2!'],
];

$results = [];
$errors  = [];

try {
    $pdo  = db();
    $stmt = $pdo->prepare('UPDATE portal_users SET password_hash = ? WHERE id = ?');

    foreach ($users as [$id, $username, $password]) {
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt->execute([$hash, $id]);
        $results[] = ['id' => $id, 'username' => $username, 'rows' => $stmt->rowCount()];
    }
} catch (PDOException $e) {
    $errors[] = 'DB chyba: ' . $e->getMessage();
}

// ── Výstup ───────────────────────────────────────────────────
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="cs">
<head>
  <meta charset="UTF-8">
  <title>Setup — Obecní dům Holčovice</title>
  <style>
    body { font-family: system-ui, sans-serif; max-width: 700px; margin: 3rem auto; padding: 0 1rem; }
    h1 { color: #1A3129; }
    table { width: 100%; border-collapse: collapse; margin: 1.5rem 0; }
    th, td { padding: .5rem .75rem; border: 1px solid #ddd; text-align: left; }
    th { background: #f5f5f2; }
    .ok  { color: #1e6e42; font-weight: 600; }
    .err { color: #c0392b; font-weight: 600; }
    .warn { background: #fff3cd; border: 1px solid #f0a500; padding: 1rem; border-radius: 6px; margin-top: 1.5rem; }
  </style>
</head>
<body>
<h1>🔧 Setup — nastavení hesel</h1>

<?php if ($errors): ?>
  <?php foreach ($errors as $e): ?>
    <p class="err">✗ <?= htmlspecialchars($e) ?></p>
  <?php endforeach; ?>
<?php else: ?>
  <p class="ok">✓ Všechna hesla byla úspěšně nastavena (bcrypt cost=12)</p>

  <table>
    <tr><th>ID</th><th>Username</th><th>Výsledek</th></tr>
    <?php foreach ($results as $r): ?>
      <tr>
        <td><?= $r['id'] ?></td>
        <td><?= htmlspecialchars($r['username']) ?></td>
        <td class="<?= $r['rows'] ? 'ok' : 'err' ?>">
          <?= $r['rows'] ? '✓ Uloženo' : '✗ Řádek nenalezen' ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>

  <div class="warn">
    <strong>⚠️ Důležité:</strong> Tento soubor nyní smažte přes FTP nebo správce souborů na WEDOS.<br>
    Cesta: <code>/www/OD/setup.php</code>
  </div>
<?php endif; ?>

<p style="margin-top:2rem;color:#888;font-size:.875rem">
  Spuštěno: <?= date('d.m.Y H:i:s') ?> · PHP <?= PHP_VERSION ?> · <?= PHP_OS ?>
</p>
</body>
</html>
