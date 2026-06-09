<?php
/**
 * GET /api/seed/events.php — nasadí ukázkové akce s budoucími daty (2026)
 */
require_once __DIR__ . '/../config.php';

$pdo = db();

// Zkontrolujeme tabulku
try {
    $pdo->query('SELECT 1 FROM portal_events LIMIT 1');
} catch (PDOException $e) {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS portal_events (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            slug VARCHAR(255) UNIQUE NOT NULL,
            body TEXT,
            date_start DATETIME NOT NULL,
            date_end DATETIME,
            location VARCHAR(255),
            image_url VARCHAR(500),
            promote_homepage TINYINT(1) DEFAULT 0,
            is_published TINYINT(1) DEFAULT 0,
            author_id INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
}

$events = [
    ['Letní hudební večer',          'letni-hudebni-vecer-2026',    'Živá kapela na terase Obecního domu. Vstup zdarma, bohatý program od 18:00. Občerstvení k dispozici.', '2026-06-14 18:00:00', '2026-06-14 23:00:00', 'Terasa OD', 1, 1],
    ['Zasedání zastupitelstva',      'zasedani-zastupitelstva-6-2026','Pravidelné zasedání obecního zastupitelstva. Veřejnost vítána.', '2026-06-21 17:00:00', '2026-06-21 19:00:00', 'Velký sál', 0, 1],
    ['Komentovaný výlet — Pradědova stezka', 'pravedova-stezka-2026', 'Turistický výlet s průvodcem. Start u Obecního domu v 7:30. S sebou: svačinu, pláštěnku, dobrou obuv. Cena 250 Kč/os.', '2026-07-05 07:30:00', '2026-07-05 15:00:00', 'Start u OD', 1, 1],
    ['Farmářský trh',               'farmarsky-trh-cerven-2026',   'Regionální produkty od místních farmářů. Med, sýry, zelenina, pečivo, domácí marmelády.', '2026-06-28 08:00:00', '2026-06-28 13:00:00', 'Náměstí u OD', 1, 1],
    ['Dětský den v Holčovicích',     'detsky-den-2026',            'Soutěže, skákací hrad, malování na obličej, cukrová vata. Program pro celou rodinu od 10:00.', '2026-06-20 10:00:00', '2026-06-20 16:00:00', 'Hřiště u školy', 1, 1],
    ['Degustace vín',                'degustace-vin-2026',         'Ochutnávka moravských vín s výkladem sommeliera. Limitovaný počet míst — rezervace nutná.', '2026-07-12 18:00:00', '2026-07-12 21:00:00', 'Restaurace OD', 0, 0],
    ['Letní kino — Pelíšky',        'letni-kino-pelicky-2026',    'Promítání kultovní české komedie pod hvězdami. Začátek po setmění (cca 21:00). Vstupné dobrovolné.', '2026-07-19 21:00:00', '2026-07-19 23:30:00', 'Terasa OD', 1, 1],
    ['Workshop: Vaříme s šéfkuchařem','workshop-vareni-2026',      'Naučte se připravit 3 pokrmy pod vedením šéfkuchaře Obecního domu. Vhodné pro začátečníky i pokročilé.', '2026-08-02 14:00:00', '2026-08-02 17:00:00', 'Kuchyně OD', 0, 0],
];

$inserted = 0;
$skipped = 0;

foreach ($events as [$title, $slug, $body, $dateStart, $dateEnd, $location, $promote, $published]) {
    $check = $pdo->prepare('SELECT id FROM portal_events WHERE slug = ? LIMIT 1');
    $check->execute([$slug]);
    if ($check->fetch()) {
        $skipped++;
        continue;
    }
    $pdo->prepare('INSERT INTO portal_events (title, slug, body, date_start, date_end, location, promote_homepage, is_published) VALUES (?,?,?,?,?,?,?,?)')
        ->execute([$title, $slug, $body, $dateStart, $dateEnd, $location, $promote, $published]);
    $inserted++;
}

json_response([
    'ok'       => true,
    'inserted' => $inserted,
    'skipped'  => $skipped,
    'message'  => "Vloženo $inserted akcí, přeskočeno $skipped duplicit.",
]);
