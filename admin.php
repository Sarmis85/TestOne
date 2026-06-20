<?php
// ─── Konfigurace ──────────────────────────────────────────
define('ADMIN_PASS', 'holcovice2026');   // ZMĚŇTE před nasazením!
define('CONTENT_FILE', __DIR__ . '/content.json');
define('SESSION_NAME', 'prh_admin');

session_name(SESSION_NAME);
session_start();

$action = $_GET['action'] ?? '';

// ─── AJAX: uložit content.json ─────────────────────────────
if ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_SESSION['auth'])) { http_response_code(403); echo '{"ok":false}'; exit; }
    $tok = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!hash_equals($_SESSION['csrf'] ?? '', $tok)) { http_response_code(403); echo '{"ok":false,"error":"CSRF"}'; exit; }
    $body = file_get_contents('php://input');
    $data = json_decode($body, true);
    if (json_last_error() !== JSON_ERROR_NONE) { http_response_code(400); echo '{"ok":false,"error":"JSON invalid"}'; exit; }
    file_put_contents(CONTENT_FILE, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    echo '{"ok":true}';
    exit;
}

// ─── Přihlášení ────────────────────────────────────────────
if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['pass'] ?? '') === ADMIN_PASS) {
        $_SESSION['auth'] = true;
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    header('Location: admin.php');
    exit;
}

// ─── Odhlášení ─────────────────────────────────────────────
if ($action === 'logout') {
    session_destroy();
    header('Location: admin.php');
    exit;
}

// ─── Načtení obsahu ────────────────────────────────────────
$content = [];
if (file_exists(CONTENT_FILE)) {
    $content = json_decode(file_get_contents(CONTENT_FILE), true) ?? [];
}
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));

// ─── Helper ────────────────────────────────────────────────
function v($data, ...$keys) {
    foreach ($keys as $k) {
        if (!is_array($data) || !array_key_exists($k, $data)) return '';
        $data = $data[$k];
    }
    return htmlspecialchars((string)($data ?? ''), ENT_QUOTES, 'UTF-8');
}

$c = $content;
$csrf = $_SESSION['csrf'] ?? '';
?>
<!DOCTYPE html>
<html lang="cs">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin — Pro rozvoj Holčovic</title>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;font-size:14px;background:#f0f0f0;color:#1a1a1a;line-height:1.5}
a{color:inherit;text-decoration:none}

/* ── Přihlašovací formulář ─── */
.login-wrap{min-height:100vh;display:flex;align-items:center;justify-content:center}
.login-box{background:#fff;border:1px solid #ddd;border-radius:8px;padding:40px;width:340px;box-shadow:0 4px 20px rgba(0,0,0,.08)}
.login-box h1{font-size:1.1rem;font-weight:600;margin-bottom:6px}
.login-box p{font-size:.82rem;color:#666;margin-bottom:24px}
.login-box input{width:100%;border:1px solid #ccc;border-radius:4px;padding:10px 12px;font-size:.9rem;outline:none;transition:border-color .2s}
.login-box input:focus{border-color:#1C3354}
.login-box button{margin-top:12px;width:100%;background:#1C3354;color:#fff;border:none;border-radius:4px;padding:11px;font-size:.88rem;font-weight:600;cursor:pointer}
.login-box button:hover{background:#264878}

/* ── Admin layout ─── */
.admin-top{background:#1C3354;color:#fff;padding:0 24px;display:flex;align-items:center;justify-content:space-between;height:52px;position:sticky;top:0;z-index:100;box-shadow:0 2px 8px rgba(0,0,0,.2)}
.admin-brand{font-weight:600;font-size:.95rem;letter-spacing:.02em}
.admin-brand span{opacity:.55;font-weight:400;margin-left:8px;font-size:.8rem}
.admin-actions{display:flex;align-items:center;gap:12px}
.btn-save{background:#B8873A;color:#fff;border:none;border-radius:4px;padding:7px 18px;font-size:.82rem;font-weight:600;cursor:pointer;transition:background .2s}
.btn-save:hover{background:#D4A85C}
.btn-save:disabled{background:#aaa;cursor:default}
.save-msg{font-size:.78rem;opacity:.75}
.btn-logout{font-size:.75rem;opacity:.5;cursor:pointer;background:none;border:none;color:#fff;padding:4px 8px;border-radius:3px}
.btn-logout:hover{opacity:.9;background:rgba(255,255,255,.1)}

/* ── Maintenance bar ── */
.maint-bar{display:flex;align-items:center;gap:16px;padding:10px 24px;border-bottom:1px solid #e0e0e0;background:#fff}
.maint-bar.on{background:#fff8f0;border-bottom-color:#f5c98a}
.maint-status{font-size:.8rem;font-weight:600;display:flex;align-items:center;gap:8px}
.maint-dot{width:9px;height:9px;border-radius:50%;flex-shrink:0}
.maint-bar.on  .maint-dot{background:#e67e22}
.maint-bar.off .maint-dot{background:#27ae60}
.maint-bar.on  .maint-text{color:#c0610a}
.maint-bar.off .maint-text{color:#1e8449}
.toggle-wrap{display:flex;align-items:center;gap:8px;cursor:pointer}
.toggle-input{display:none}
.toggle-track{width:40px;height:22px;background:#ccc;border-radius:11px;position:relative;transition:background .2s;flex-shrink:0}
.toggle-track::after{content:'';position:absolute;top:3px;left:3px;width:16px;height:16px;border-radius:50%;background:#fff;transition:transform .2s;box-shadow:0 1px 3px rgba(0,0,0,.25)}
.toggle-input:checked+.toggle-track{background:#e67e22}
.toggle-input:checked+.toggle-track::after{transform:translateX(18px)}
.toggle-label{font-size:.8rem;color:#555;user-select:none}
.btn-preview{display:inline-flex;align-items:center;gap:5px;font-size:.77rem;color:#1C3354;border:1px solid #c5d2e0;border-radius:4px;padding:5px 12px;font-weight:500;cursor:pointer;text-decoration:none;background:#f4f7fb;transition:background .15s}
.btn-preview:hover{background:#e8edf4}
.maint-hint{font-size:.72rem;color:#999;margin-left:auto}
.maint-saving{font-size:.72rem;color:#B8873A;margin-left:4px}

/* ── Tabs ─── */
.tabs{background:#fff;border-bottom:1px solid #ddd;padding:0 24px;display:flex;gap:0}
.tab-btn{padding:14px 20px;font-size:.84rem;font-weight:500;color:#666;cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-1px;background:none;border-top:none;border-left:none;border-right:none;transition:color .2s,border-color .2s}
.tab-btn.active{color:#1C3354;border-bottom-color:#B8873A}
.tab-btn:hover:not(.active){color:#1C3354}

/* ── Obsah ─── */
.admin-body{max-width:960px;margin:0 auto;padding:28px 24px 80px}
.tab-panel{display:none}.tab-panel.active{display:block}

/* ── Sekce ─── */
.section{background:#fff;border:1px solid #e0e0e0;border-radius:6px;margin-bottom:20px;overflow:hidden}
.section-head{background:#f7f7f7;border-bottom:1px solid #e8e8e8;padding:12px 20px;display:flex;align-items:center;gap:10px}
.section-head h2{font-size:.88rem;font-weight:600;color:#333}
.section-head .badge{font-size:.68rem;background:#e8edf4;color:#1C3354;border-radius:3px;padding:2px 7px;font-weight:500}
.section-body{padding:20px}
.fields{display:grid;gap:14px}

/* ── Pole ─── */
.field{display:flex;flex-direction:column;gap:5px}
.field label{font-size:.72rem;font-weight:600;color:#555;letter-spacing:.04em;text-transform:uppercase}
.field input,.field textarea{border:1px solid #d0d0d0;border-radius:4px;padding:8px 11px;font-size:.88rem;font-family:inherit;color:#1a1a1a;outline:none;transition:border-color .2s;width:100%;resize:vertical}
.field input:focus,.field textarea:focus{border-color:#1C3354;box-shadow:0 0 0 2px rgba(28,51,84,.08)}
.field textarea{min-height:88px;line-height:1.6}
.field textarea.tall{min-height:130px}
.field textarea.xtall{min-height:180px}
.field .hint{font-size:.68rem;color:#999;margin-top:2px}

/* ── Skupiny kandidátů ─── */
.cand-group{border:1px solid #e8e8e8;border-radius:5px;padding:16px;margin-bottom:12px}
.cand-group:last-child{margin-bottom:0}
.cand-label{font-size:.72rem;font-weight:700;color:#B8873A;letter-spacing:.06em;text-transform:uppercase;margin-bottom:12px}
.grid-2{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.grid-3{display:grid;grid-template-columns:120px 1fr 1fr;gap:14px}

@media(max-width:600px){.grid-2,.grid-3{grid-template-columns:1fr}}
</style>
</head>
<body>

<?php if (empty($_SESSION['auth'])): ?>
<!-- ══════════════════════════════════════
     PŘIHLÁŠENÍ
     ══════════════════════════════════════ -->
<div class="login-wrap">
  <div class="login-box">
    <h1>Pro rozvoj Holčovic</h1>
    <p>Administrace obsahu webu</p>
    <form method="post" action="admin.php?action=login">
      <input type="password" name="pass" placeholder="Heslo" autofocus required>
      <button type="submit">Přihlásit se</button>
    </form>
  </div>
</div>

<?php else: ?>
<!-- ══════════════════════════════════════
     ADMIN PANEL
     ══════════════════════════════════════ -->
<div class="admin-top">
  <div class="admin-brand">Admin <span>Pro rozvoj Holčovic</span></div>
  <div class="admin-actions">
    <span class="save-msg" id="saveMsg"></span>
    <button class="btn-save" id="btnSave" onclick="saveAll()">Uložit změny</button>
    <a href="admin.php?action=logout"><button class="btn-logout">Odhlásit</button></a>
  </div>
</div>

<!-- MAINTENANCE BAR -->
<?php $maint = $content['maintenance'] ?? true; ?>
<div class="maint-bar <?= $maint ? 'on' : 'off' ?>" id="maintBar">
  <div class="maint-status">
    <div class="maint-dot"></div>
    <span class="maint-text" id="maintText">Režim údržby: <?= $maint ? 'ZAPNUTO' : 'VYPNUTO' ?></span>
  </div>
  <label class="toggle-wrap">
    <input type="checkbox" class="toggle-input" id="maintToggle" onchange="saveMaint()" <?= $maint ? 'checked' : '' ?>>
    <div class="toggle-track"></div>
    <span class="toggle-label" id="maintToggleLabel"><?= $maint ? 'Web je v údržbě' : 'Web je živý' ?></span>
  </label>
  <a href="/?preview=1" target="_blank" class="btn-preview">
    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
    Náhled plného webu
  </a>
  <span class="maint-saving" id="maintSaving"></span>
  <span class="maint-hint">Náhled funguje 8 hodin po kliknutí · <a href="/?preview=0" target="_blank" style="color:#B8873A">Zrušit náhled</a></span>
</div>

<div class="tabs">
  <button class="tab-btn active" onclick="showTab('hlavni',this)">Hlavní stránka</button>
  <button class="tab-btn" onclick="showTab('program',this)">Program</button>
</div>

<div class="admin-body">

<!-- ══════════════════════════
     TAB 1 — HLAVNÍ STRÁNKA
     ══════════════════════════ -->
<div class="tab-panel active" id="tab-hlavni">

  <!-- HERO -->
  <div class="section">
    <div class="section-head"><h2>Hero</h2><span class="badge">Úvodní sekce</span></div>
    <div class="section-body">
      <div class="fields">
        <div class="field">
          <label>Tagline (tučný perex)</label>
          <input type="text" data-path="hero.tagline" value="<?= v($c,'hero','tagline') ?>">
        </div>
        <div class="field">
          <label>Text pod tagline</label>
          <textarea class="tall" data-path="hero.text"><?= v($c,'hero','text') ?></textarea>
        </div>
      </div>
    </div>
  </div>

  <!-- PROGRAM KARTY -->
  <div class="section">
    <div class="section-head"><h2>Program</h2><span class="badge">5 prioritních karet</span></div>
    <div class="section-body">
      <?php
      $cards = [
        ['01','Obnova po povodních'],
        ['02','Základní služby'],
        ['03','Místo pro život'],
        ['04','Turistika a příroda'],
        ['05','Otevřený úřad'],
      ];
      foreach ($cards as $i => [$num, $label]):
      ?>
      <div class="cand-group">
        <div class="cand-label">Karta <?= $num ?> — <?= $label ?></div>
        <div class="fields">
          <div class="field">
            <label>Nadpis</label>
            <input type="text" data-path="program.cards.<?= $i ?>.title" value="<?= v($c,'program','cards',$i,'title') ?>">
          </div>
          <div class="field">
            <label>Text</label>
            <textarea class="tall" data-path="program.cards.<?= $i ?>.text"><?= v($c,'program','cards',$i,'text') ?></textarea>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- VÝSLEDKY -->
  <div class="section">
    <div class="section-head"><h2>Výsledky</h2><span class="badge">Průběžné + datované</span></div>
    <div class="section-body">

      <?php
      $ongoing = [
        ['Každoroční podpora spolků','ongoing',0],
        ['Kulturní a sportovní program','ongoing',1],
        ['Transparentní hospodaření','ongoing',2],
      ];
      ?>
      <div class="cand-label" style="margin-bottom:10px">Průběžná péče o obec (levý sloupec)</div>
      <?php foreach ($ongoing as [$label, $col, $i]): ?>
      <div class="cand-group">
        <div class="cand-label"><?= $label ?></div>
        <div class="fields">
          <div class="field">
            <label>Nadpis</label>
            <input type="text" data-path="vysledky.<?= $col ?>.<?= $i ?>.title" value="<?= v($c,'vysledky',$col,$i,'title') ?>">
          </div>
          <div class="field">
            <label>Text</label>
            <textarea data-path="vysledky.<?= $col ?>.<?= $i ?>.text"><?= v($c,'vysledky',$col,$i,'text') ?></textarea>
          </div>
        </div>
      </div>
      <?php endforeach; ?>

      <?php
      $dated = [
        ['Povodeň 2024','dated',0],
        ['Sanace + rekonstrukce','dated',1],
        ['Knihovna + komunikace','dated',2],
      ];
      ?>
      <div class="cand-label" style="margin:16px 0 10px">Konkrétní kroky s datem (pravý sloupec)</div>
      <?php foreach ($dated as [$label, $col, $i]): ?>
      <div class="cand-group">
        <div class="cand-label"><?= $label ?></div>
        <div class="grid-3">
          <div class="field">
            <label>Datum</label>
            <input type="text" data-path="vysledky.<?= $col ?>.<?= $i ?>.date" value="<?= v($c,'vysledky',$col,$i,'date') ?>">
          </div>
          <div class="field">
            <label>Nadpis</label>
            <input type="text" data-path="vysledky.<?= $col ?>.<?= $i ?>.title" value="<?= v($c,'vysledky',$col,$i,'title') ?>">
          </div>
          <div class="field">
            <label>Text</label>
            <input type="text" data-path="vysledky.<?= $col ?>.<?= $i ?>.text" value="<?= v($c,'vysledky',$col,$i,'text') ?>">
          </div>
        </div>
      </div>
      <?php endforeach; ?>

    </div>
  </div>

  <!-- KANDIDÁTI -->
  <div class="section">
    <div class="section-head"><h2>Kandidáti</h2><span class="badge">č. 1–10</span></div>
    <div class="section-body">

      <!-- Kandidát 1 -->
      <div class="cand-group">
        <div class="cand-label">01 — Lídr (featured karta)</div>
        <div class="fields">
          <div class="grid-2">
            <div class="field">
              <label>Badge (nad jménem)</label>
              <input type="text" data-path="kandidati.c1.badge" value="<?= v($c,'kandidati','c1','badge') ?>">
            </div>
            <div class="field">
              <label>Jméno</label>
              <input type="text" data-path="kandidati.c1.name" value="<?= v($c,'kandidati','c1','name') ?>">
            </div>
          </div>
          <div class="field">
            <label>Role</label>
            <input type="text" data-path="kandidati.c1.role" value="<?= v($c,'kandidati','c1','role') ?>">
          </div>
          <div class="field">
            <label>Citát (kurzívou)</label>
            <textarea data-path="kandidati.c1.quote"><?= v($c,'kandidati','c1','quote') ?></textarea>
          </div>
          <div class="field">
            <label>Bio — odstavec 1</label>
            <textarea class="xtall" data-path="kandidati.c1.bio1"><?= v($c,'kandidati','c1','bio1') ?></textarea>
          </div>
          <div class="field">
            <label>Bio — odstavec 2 (může obsahovat HTML, např. &lt;a href=...&gt;)</label>
            <textarea class="xtall" data-path="kandidati.c1.bio2"><?= v($c,'kandidati','c1','bio2') ?></textarea>
          </div>
        </div>
      </div>

      <!-- Kandidáti 2–10 -->
      <?php
      $cands = [
        ['02','c2','Místostarosta'],
        ['03','c3','Zastupitel'],
        ['04','c4','Zastupitel'],
        ['05','c5','Zastupitel'],
        ['06','c6','Zastupitelka'],
        ['07','c7','Kandidátka'],
        ['08','c8','Kandidát'],
        ['09','c9','Bývalý zastupitel'],
        ['10','c10','Náhradnice'],
      ];
      foreach ($cands as [$num, $key, $hint]):
      ?>
      <div class="cand-group">
        <div class="cand-label">Kandidát <?= $num ?> <span style="font-weight:400;color:#999">(<?= $hint ?>)</span></div>
        <div class="fields">
          <div class="grid-2">
            <div class="field">
              <label>Jméno</label>
              <input type="text" data-path="kandidati.<?= $key ?>.name" value="<?= v($c,'kandidati',$key,'name') ?>">
            </div>
            <div class="field">
              <label>Role</label>
              <input type="text" data-path="kandidati.<?= $key ?>.role" value="<?= v($c,'kandidati',$key,'role') ?>">
            </div>
          </div>
          <div class="field">
            <label>Bio</label>
            <textarea class="tall" data-path="kandidati.<?= $key ?>.bio"><?= v($c,'kandidati',$key,'bio') ?></textarea>
          </div>
        </div>
      </div>
      <?php endforeach; ?>

    </div>
  </div>

  <!-- VOLBY -->
  <div class="section">
    <div class="section-head"><h2>Výzva k volbám</h2><span class="badge">Sekce na konci stránky</span></div>
    <div class="section-body">
      <div class="fields">
        <div class="field">
          <label>Text (perex)</label>
          <textarea data-path="volby.sub"><?= v($c,'volby','sub') ?></textarea>
        </div>
        <div class="field">
          <label>Badge text</label>
          <input type="text" data-path="volby.badge" value="<?= v($c,'volby','badge') ?>">
        </div>
      </div>
    </div>
  </div>

</div><!-- /tab-hlavni -->

<!-- ══════════════════════════
     TAB 2 — PROGRAM
     ══════════════════════════ -->
<div class="tab-panel" id="tab-program">

  <!-- Hero programu -->
  <div class="section">
    <div class="section-head"><h2>Hero programu</h2><span class="badge">program.html — úvod</span></div>
    <div class="section-body">
      <div class="field">
        <label>Podtitulek pod H1</label>
        <textarea data-path="program_page.hero_sub"><?= v($c,'program_page','hero_sub') ?></textarea>
      </div>
    </div>
  </div>

  <!-- 5 sekcí programu — úvodní texty -->
  <div class="section">
    <div class="section-head"><h2>Úvodní texty sekcí</h2><span class="badge">Perex pod názvem tématu</span></div>
    <div class="section-body">
      <?php
      $sections = [
        ['01','povodni','Obnova po povodních'],
        ['02','sluzby','Základní služby'],
        ['03','zivot','Místo pro život'],
        ['04','turistika','Turistika a příroda'],
        ['05','urad','Otevřený úřad'],
      ];
      foreach ($sections as [$num, $key, $label]):
      ?>
      <div class="cand-group">
        <div class="cand-label">Téma <?= $num ?> — <?= $label ?></div>
        <div class="field">
          <label>Perex (prog-lead)</label>
          <textarea class="tall" data-path="program_page.<?= $key ?>.lead"><?= v($c,'program_page',$key,'lead') ?></textarea>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

</div><!-- /tab-program -->

</div><!-- /admin-body -->

<script>
var CSRF = <?= json_encode($csrf) ?>;

function showTab(name, btn) {
  document.querySelectorAll('.tab-panel').forEach(function(p){ p.classList.remove('active'); });
  document.querySelectorAll('.tab-btn').forEach(function(b){ b.classList.remove('active'); });
  document.getElementById('tab-' + name).classList.add('active');
  btn.classList.add('active');
}

function setPath(obj, path, value) {
  var parts = path.split('.');
  var cur = obj;
  for (var i = 0; i < parts.length - 1; i++) {
    var k = parts[i];
    var next = parts[i + 1];
    var isArr = /^\d+$/.test(next);
    if (cur[k] === undefined || cur[k] === null) cur[k] = isArr ? [] : {};
    cur = cur[k];
  }
  var last = parts[parts.length - 1];
  if (/^\d+$/.test(last)) {
    cur[parseInt(last)] = value;
  } else {
    cur[last] = value;
  }
}

function buildJson() {
  var data = {};
  data.maintenance = document.getElementById('maintToggle').checked;
  document.querySelectorAll('[data-path]').forEach(function(el) {
    var path = el.getAttribute('data-path');
    var val = el.value;
    setPath(data, path, val);
  });
  return data;
}

/* ── Okamžité uložení maintenance přepínače ── */
function saveMaint() {
  var on    = document.getElementById('maintToggle').checked;
  var bar   = document.getElementById('maintBar');
  var text  = document.getElementById('maintText');
  var label = document.getElementById('maintToggleLabel');
  var hint  = document.getElementById('maintSaving');
  bar.className    = 'maint-bar ' + (on ? 'on' : 'off');
  text.textContent  = 'Režim údržby: ' + (on ? 'ZAPNUTO' : 'VYPNUTO');
  label.textContent = on ? 'Web je v údržbě' : 'Web je živý';
  hint.textContent  = 'Ukládám…';

  /* Načti aktuální JSON, uprav maintenance, ulož */
  fetch('content.json?t=' + Date.now())
    .then(function(r){ return r.json(); })
    .then(function(data){
      data.maintenance = on;
      return fetch('admin.php?action=save', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Csrf-Token': CSRF },
        body: JSON.stringify(data)
      });
    })
    .then(function(r){ return r.json(); })
    .then(function(d){
      hint.textContent = d.ok ? '✓ Uloženo' : '✗ Chyba';
      setTimeout(function(){ hint.textContent = ''; }, 3000);
    })
    .catch(function(){
      hint.textContent = '✗ Chyba připojení';
      setTimeout(function(){ hint.textContent = ''; }, 3000);
    });
}

function saveAll() {
  var btn = document.getElementById('btnSave');
  var msg = document.getElementById('saveMsg');
  btn.disabled = true;
  btn.textContent = 'Ukládám…';
  msg.textContent = '';

  fetch('admin.php?action=save', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-Csrf-Token': CSRF
    },
    body: JSON.stringify(buildJson())
  })
  .then(function(r){ return r.json(); })
  .then(function(d){
    if (d.ok) {
      msg.textContent = '✓ Uloženo';
      msg.style.color = '#5cb85c';
    } else {
      msg.textContent = '✗ Chyba: ' + (d.error || 'neznámá');
      msg.style.color = '#d9534f';
    }
  })
  .catch(function(){
    msg.textContent = '✗ Chyba připojení';
    msg.style.color = '#d9534f';
  })
  .finally(function(){
    btn.disabled = false;
    btn.textContent = 'Uložit změny';
    setTimeout(function(){ msg.textContent = ''; }, 4000);
  });
}

/* Ctrl+S = uložit */
document.addEventListener('keydown', function(e) {
  if ((e.ctrlKey || e.metaKey) && e.key === 's') {
    e.preventDefault();
    saveAll();
  }
});
</script>

<?php endif; ?>
</body>
</html>
