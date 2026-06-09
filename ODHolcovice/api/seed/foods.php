<?php
/**
 * GET /api/seed/foods.php — nasadí 7 jídel do každé kategorie
 * Bezpečnostní pojistka: nezakládá duplikáty (kontroluje existenci dle názvu)
 */
require_once __DIR__ . '/../config.php';

$foods = [
    // ── Polévky ───────────────────────────────────────────
    ['Hovězí vývar s nudlemi',        'Polévka',       65, '1,3,9',   '300 ml', 0],
    ['Česneková polévka se sýrem',    'Polévka',       75, '1,7',     '300 ml', 0],
    ['Bramborová polévka s houbami',  'Polévka',       69, '1,7,9',   '300 ml', 0],
    ['Kulajda',                       'Polévka',       72, '1,3,7',   '300 ml', 0],
    ['Rajská polévka s těstovinami',  'Polévka',       65, '1,3,7',   '300 ml', 0],
    ['Dýňový krém se zázvorem',       'Polévka',       79, '7',       '300 ml', 1],
    ['Zelňačka se smetanou',          'Polévka',       69, '1,7',     '300 ml', 0],

    // ── Předkrmy & Saláty ─────────────────────────────────
    ['Tatarský biftek s topinkami',    'Předkrm',      189, '1,3,10',  '200 g', 0],
    ['Carpaccio z hovězí svíčkové',   'Předkrm',      169, '7',       '150 g', 0],
    ['Caesar salát s kuřecím masem',  'Předkrm',      149, '1,3,4,7', '300 g', 0],
    ['Bruschetta s rajčaty a bazalkou','Předkrm',      99, '1,7',     '180 g', 1],
    ['Domácí paštika s brusinkami',   'Předkrm',      129, '1,3,7',   '150 g', 0],
    ['Zeleninový salát s balkánským sýrem','Předkrm', 119, '7',       '250 g', 1],
    ['Krevety na česneku s chlebem',  'Předkrm',      199, '1,2,7',   '180 g', 0],

    // ── Hlavní jídla ──────────────────────────────────────
    ['Svíčková na smetaně, houskový knedlík','Hlavní',259, '1,3,7',  '350 g', 0],
    ['Vepřová pečeně, knedlík, zelí', 'Hlavní',      159, '1,3,7',   '350 g', 0],
    ['Kuřecí řízek, bramborový salát', 'Hlavní',      169, '1,3,7,10','350 g', 0],
    ['Pečený pstruh na másle',         'Hlavní',      229, '4,7',     '300 g', 0],
    ['Hovězí guláš s houskovým knedlíkem','Hlavní',   179, '1,3,7',  '350 g', 0],
    ['Grilovaný steak z vepřové krkovice','Hlavní',   199, '1,10',   '300 g', 0],
    ['Smažený sýr, hranolky, tatarská omáčka','Hlavní',149,'1,3,7,10','300 g', 1],

    // ── Vegetariánské ─────────────────────────────────────
    ['Zapečené brambory se sýrem',     'Vegetariánské',129, '7',      '300 g', 1],
    ['Čočkový dhal s rýží',           'Vegetariánské', 165, '',        '350 g', 1],
    ['Špenátové rizoto s parmazánem',  'Vegetariánské',155, '7',      '300 g', 1],
    ['Zeleninové curry s kokosovým mlékem','Vegetariánské',159,'',    '350 g', 1],
    ['Bramboráky se zakysanou smetanou','Vegetariánské',129,'1,3,7',  '300 g', 1],
    ['Gnocchi s rajčatovou omáčkou',   'Vegetariánské',149, '1,3,7',  '300 g', 1],
    ['Houbový kuba s česnekem',        'Vegetariánské',139, '1',      '300 g', 1],

    // ── Dezerty ───────────────────────────────────────────
    ['Domácí jablečný štrúdl',         'Dezert',       89, '1,3,7,8', '180 g', 1],
    ['Palačinky s marmeládou a šlehačkou','Dezert',    99, '1,3,7',   '200 g', 1],
    ['Čokoládový fondant s vanilkovou zmrzlinou','Dezert',129,'1,3,7,8','200 g',1],
    ['Domácí tiramisu',                'Dezert',      109, '1,3,7',   '150 g', 1],
    ['Horká čokoláda s marshmallows',  'Dezert',       79, '7',       '250 ml',1],
    ['Tvarohový koláč s ovocem',       'Dezert',       99, '1,3,7',   '180 g', 1],
    ['Zmrzlinový pohár s oříšky',      'Dezert',      119, '1,7,8',   '250 g', 1],

    // ── Nápoje ────────────────────────────────────────────
    ['Domácí limonáda citron-máta',    'Nápoj',        55, '',        '400 ml', 1],
    ['Čerstvě vymačkaný pomerančový džus','Nápoj',     69, '',       '300 ml', 1],
    ['Espresso',                       'Nápoj',        49, '',        '30 ml',  1],
    ['Cappuccino',                     'Nápoj',        65, '7',       '200 ml', 1],
    ['Čaj dle výběru',                 'Nápoj',        45, '',        '300 ml', 1],
    ['Moravské bílé víno 0,2l',        'Nápoj',        79, '12',      '200 ml', 0],
    ['Pilsner Urquell 0,5l',          'Nápoj',         55, '1',       '500 ml', 0],
];

$pdo = db();
$inserted = 0;
$skipped = 0;

// Zkontrolujeme, zda tabulka existuje
try {
    $pdo->query('SELECT 1 FROM restaurant_menu_items LIMIT 1');
} catch (PDOException $e) {
    // Tabulka neexistuje – vytvoříme ji
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS restaurant_menu_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            category VARCHAR(50) NOT NULL DEFAULT 'Hlavní',
            price_kc DECIMAL(8,2) NOT NULL DEFAULT 0,
            allergens VARCHAR(100) DEFAULT NULL,
            weight_g VARCHAR(50) DEFAULT NULL,
            description TEXT DEFAULT NULL,
            is_vege TINYINT(1) NOT NULL DEFAULT 0,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
}

// Zkontrolujeme view
try {
    $pdo->query('SELECT 1 FROM v_menu_item_ratings LIMIT 1');
} catch (PDOException $e) {
    $pdo->exec("
        CREATE OR REPLACE VIEW v_menu_item_ratings AS
        SELECT mi.*,
               COALESCE(COUNT(r.id), 0) AS rating_count,
               COALESCE(AVG(r.rating), NULL) AS rating_avg
        FROM restaurant_menu_items mi
        LEFT JOIN restaurant_menu_ratings r ON r.menu_item_id = mi.id
        GROUP BY mi.id
    ");
}

$checkStmt = $pdo->prepare('SELECT id FROM restaurant_menu_items WHERE name = ? LIMIT 1');
$insertStmt = $pdo->prepare('INSERT INTO restaurant_menu_items (name, category, price_kc, allergens, weight_g, is_vege, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)');

foreach ($foods as [$name, $category, $price, $allergens, $weight, $is_vege]) {
    $checkStmt->execute([$name]);
    if ($checkStmt->fetch()) {
        $skipped++;
        continue;
    }
    $insertStmt->execute([$name, $category, $price, $allergens ?: null, $weight ?: null, $is_vege]);
    $inserted++;
}

json_response([
    'ok'       => true,
    'inserted' => $inserted,
    'skipped'  => $skipped,
    'total'    => count($foods),
    'message'  => "Vloženo $inserted jídel, přeskočeno $skipped duplicit.",
]);
