-- ============================================================
--  SEED DATA — testovací záznamy pro všechny tabulky
--  Spustit PO schema.sql v phpMyAdmin → Import
-- ============================================================
SET NAMES utf8mb4;
SET foreign_key_checks = 0;

-- Vyčistit existující testovací data (bezpečné opakované spuštění)
DELETE FROM restaurant_menu_ratings;
DELETE FROM restaurant_order_items;
DELETE FROM restaurant_orders;
DELETE FROM restaurant_reservations;
DELETE FROM restaurant_catering_requests;
DELETE FROM restaurant_daily_menu;
DELETE FROM portal_articles;
DELETE FROM portal_events;

-- ============================================================
--  PORTAL EVENTS — akce a události
-- ============================================================
INSERT INTO portal_events (id, title, slug, body, date_start, date_end, location, promote_homepage, is_published, author_id) VALUES
(1, 'Zasedání zastupitelstva obce',
   'zasedani-zastupitelstva-2025-06',
   'Veřejné zasedání zastupitelstva obce Holčovice. Program jednání: schválení rozpočtových změn č. 2/2025, projednání územního plánu – aktualizace č. 3, různé podněty občanů. Zasedání je veřejné, všichni občané jsou srdečně zváni.',
   DATE_ADD(CURDATE(), INTERVAL 7 DAY), NULL,
   'Obecní dům Holčovice – velký sál', 1, 1, 8),

(2, 'Dětský den — Holčovice pro děti',
   'detsky-den-2025',
   'Přijďte s dětmi oslavit jejich svátek! Připravujeme skákací hrad, malování na obličej, soutěže o ceny a grilování. Pro nejmenší pohádkový koutek. Vstup volný.',
   DATE_ADD(CURDATE(), INTERVAL 14 DAY), DATE_ADD(CURDATE(), INTERVAL 14 DAY),
   'Areál Obecního domu Holčovice', 1, 1, 8),

(3, 'Letní tábor pro děti — přihlášky otevřeny',
   'letni-tabor-2025',
   'Přihlášky na letní příměstský tábor jsou otevřeny. Program: turistika v Jeseníkách, keramika, vaření, sport. Kapacita omezena na 20 dětí ve věku 7–14 let. Přihlášky na recepci OD nebo emailem.',
   DATE_ADD(CURDATE(), INTERVAL 21 DAY), DATE_ADD(CURDATE(), INTERVAL 28 DAY),
   'Sraz u Obecního domu Holčovice', 0, 1, 8),

(4, 'Ochutnávka místních vín a sýrů',
   'ochutnávka-vin-2025',
   'Zveme vás na příjemný večer plný vůní a chutí. Místní producenti představí svá vína, domácí sýry a uzeniny. Hudební doprovod zajistí místní kapela. Vstupné 150 Kč (zahrnuje degustační sklenici).',
   DATE_ADD(CURDATE(), INTERVAL 35 DAY), NULL,
   'Obecní dům Holčovice – salónek', 0, 1, 2),

(5, 'Výstava fotografií — Jeseníky v proměnách ročních dob',
   'vystava-fotografie-jeseniky-2025',
   'Výstava amatérských fotografů z Holčovicka zachycující krásy Jeseníků v průběhu celého roku. Vernisáž v pátek v 18:00, výstava potrvá celý červenec.',
   DATE_ADD(CURDATE(), INTERVAL 3 DAY), DATE_ADD(CURDATE(), INTERVAL 33 DAY),
   'Foyer Obecního domu Holčovice', 0, 1, 8);

-- ============================================================
--  PORTAL ARTICLES — články
-- ============================================================
INSERT INTO portal_articles (id, title, slug, perex, body, is_published, published_at, author_id) VALUES
(1, 'Rekonstrukce sálu dokončena',
   'rekonstrukce-salu-dokoncena',
   'Velký sál Obecního domu prošel kompletní rekonstrukcí. Nová podlaha, osvětlení a ozvučení.',
   'Po třech měsících prací je velký sál Obecního domu Holčovice opět připraven přivítat hosty. Rekonstrukce zahrnovala výměnu parketové podlahy, instalaci moderního LED osvětlení s možností nastavení barevné teploty a nový ozvučovací systém. Kapacita sálu zůstává 80 míst k sezení.',
   1, DATE_SUB(CURDATE(), INTERVAL 5 DAY), 2),

(2, 'Nové víkendové menu — Jesenická kuchyně',
   'vikendove-menu-jesenicka-kuchyne',
   'Od tohoto víkendu rozšiřujeme naši nabídku o speciální víkendové menu inspirované tradiční jesenickou kuchyní.',
   'Každý víkend od pátku 14:00 nabízíme rozšířené menu s pokrmy tradiční jesenické a slezské kuchyně. Svíčková na smetaně, pstruh na másle, bramborové placky se zelím — to vše připravováno z místních surovin. Víkendové menu je k dispozici i v rámci rozvozu obědů.',
   1, DATE_SUB(CURDATE(), INTERVAL 12 DAY), 2),

(3, 'Otevírací doba o prázdninách',
   'oteviraci-doba-prazdniny-2025',
   'Upozorňujeme na změnu otevírací doby v období hlavní turistické sezóny.',
   'V době letních prázdnin (1. 7. – 31. 8. 2025) rozšiřujeme provozní dobu kuchyně. Teplá jídla budeme vydávat v době 11:00–15:00 a 17:00–21:00. Pivnice bude otevřena denně od 10:00 do 23:00.',
   1, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 2);

-- ============================================================
--  RESTAURANT DAILY MENU — denní menu (7 dní)
-- ============================================================
INSERT INTO restaurant_daily_menu (menu_date, soup_id, main1_id, main2_id, vege_id, is_weekend) VALUES
(DATE_SUB(CURDATE(), INTERVAL 4 DAY), 2, 6, 5, 7, 0),
(DATE_SUB(CURDATE(), INTERVAL 3 DAY), 1, 3, 6, 7, 0),
(DATE_SUB(CURDATE(), INTERVAL 2 DAY), 2, 4, 5, 7, 0),
(DATE_SUB(CURDATE(), INTERVAL 1 DAY), 1, 3, 4, 7, 0),
(CURDATE(),                           1, 3, 5, 7, IF(DAYOFWEEK(CURDATE()) IN (1,6,7), 1, 0)),
(DATE_ADD(CURDATE(), INTERVAL 1 DAY), 2, 4, 6, 7, IF(DAYOFWEEK(DATE_ADD(CURDATE(),INTERVAL 1 DAY)) IN (1,6,7),1,0)),
(DATE_ADD(CURDATE(), INTERVAL 2 DAY), 1, 3, 5, 7, IF(DAYOFWEEK(DATE_ADD(CURDATE(),INTERVAL 2 DAY)) IN (1,6,7),1,0));

-- ============================================================
--  RESTAURANT ORDERS — objednávky (různé stavy + dny)
-- ============================================================
INSERT INTO restaurant_orders (id, user_id, delivery_date, delivery_address, contact_name, contact_phone, status, total_kc, note, created_at) VALUES
-- Dnes — aktivní objednávky
(1,  9,    CURDATE(), 'Holčovice 42, 793 71',    'Jan Novák',        '+420 601 222 001', 'dorucena',   224.00, NULL,                     NOW()),
(2,  10,   CURDATE(), 'Holčovice 88, 793 71',    'Marie Svobodová',  '+420 601 222 002', 'pripravuje', 159.00, NULL,                     NOW()),
(3,  NULL, CURDATE(), 'Heřmanovice 15, 793 74',  'Pavel Horák',      '+420 554 100 200', 'nova',       323.00, 'Bez cibule prosím',      NOW()),
(4,  NULL, CURDATE(), 'Holčovice 7, 793 71',     'Alena Krejčí',     '+420 777 321 654', 'prijata',    259.00, NULL,                     NOW()),
(5,  9,    CURDATE(), 'Holčovice 42, 793 71',    'Jan Novák',        '+420 601 222 001', 'vyrazi',     224.00, NULL,                     NOW()),
-- Včera — doručeno / zrušeno
(6,  10,   DATE_SUB(CURDATE(),INTERVAL 1 DAY), 'Holčovice 88, 793 71', 'Marie Svobodová', '+420 601 222 002', 'dorucena', 318.00, NULL, DATE_SUB(NOW(),INTERVAL 1 DAY)),
(7,  NULL, DATE_SUB(CURDATE(),INTERVAL 1 DAY), 'Heřmanovice 22',       'Tomáš Rada',      '+420 605 111 222', 'zrusena',  159.00, 'Stornováno zákazníkem', DATE_SUB(NOW(),INTERVAL 1 DAY)),
-- 2 dny zpět
(8,  9,    DATE_SUB(CURDATE(),INTERVAL 2 DAY), 'Holčovice 42, 793 71', 'Jan Novák',      '+420 601 222 001', 'dorucena', 159.00, NULL, DATE_SUB(NOW(),INTERVAL 2 DAY)),
(9,  10,   DATE_SUB(CURDATE(),INTERVAL 2 DAY), 'Holčovice 88, 793 71', 'Marie Svobodová','+420 601 222 002', 'dorucena', 418.00, NULL, DATE_SUB(NOW(),INTERVAL 2 DAY)),
-- Starší
(10, 9,    DATE_SUB(CURDATE(),INTERVAL 5 DAY), 'Holčovice 42, 793 71', 'Jan Novák',      '+420 601 222 001', 'dorucena', 259.00, NULL, DATE_SUB(NOW(),INTERVAL 5 DAY)),
(11, NULL, DATE_SUB(CURDATE(),INTERVAL 5 DAY), 'Holčovice 12, 793 71', 'Eva Malá',       '+420 602 333 444', 'dorucena', 159.00, NULL, DATE_SUB(NOW(),INTERVAL 5 DAY));

-- Položky objednávek
INSERT INTO restaurant_order_items (order_id, menu_item_id, quantity, price_at_time) VALUES
(1,  1, 1, 65.00), (1,  4, 1, 159.00),
(2,  3, 1, 159.00),
(3,  2, 1, 79.00),  (3,  3, 1, 159.00), (3, 5, 1, 169.00),  -- polévka plná cena (bez hlavního stejného dne)
(4,  4, 1, 259.00),
(5,  1, 1, 65.00),  (5,  3, 1, 159.00),
(6,  1, 1, 65.00),  (6,  4, 1, 259.00),
(7,  3, 1, 159.00),
(8,  3, 1, 159.00),
(9,  2, 1, 79.00),  (9,  4, 1, 259.00), (9, 7, 1, 129.00),  -- víkend: 3 jídla
(10, 4, 1, 259.00),
(11, 3, 1, 159.00);

-- ============================================================
--  RESTAURANT RESERVATIONS — rezervace stolů
-- ============================================================
INSERT INTO restaurant_reservations (id, user_id, name, phone, email, res_date, time_from, time_to, guests_range, note, status, created_at) VALUES
(1, 9,    'Jan Novák',        '+420 601 222 001', 'jan.novak@example.cz', DATE_ADD(CURDATE(),INTERVAL 3 DAY),  '18:00', '20:00', '3-4',  'Narozeniny — prosím vyhradit rohový stůl', 'potvrzena', DATE_SUB(NOW(),INTERVAL 2 DAY)),
(2, NULL, 'Petra Malá',       '+420 777 888 999', NULL,                   DATE_ADD(CURDATE(),INTERVAL 5 DAY),  '12:00', NULL,    '1-2',  NULL,                                       'ceka',      DATE_SUB(NOW(),INTERVAL 1 DAY)),
(3, NULL, 'Obecní úřad Holčovice','+420 554 000 100','obec@holcovice.cz',DATE_ADD(CURDATE(),INTERVAL 14 DAY), '10:00', '14:00', '9-12', 'Výroční schůze zastupitelů + oběd',        'ceka',      NOW()),
(4, 10,   'Marie Svobodová',  '+420 601 222 002', 'marie.free@seznam.cz', DATE_ADD(CURDATE(),INTERVAL 7 DAY),  '19:00', '21:30', '3-4',  'Vegetariánské menu pro 2 osoby',           'potvrzena', DATE_SUB(NOW(),INTERVAL 3 DAY)),
(5, NULL, 'Firemní oběd — Stavebniny Rada','+420 605 111 222',NULL,       DATE_ADD(CURDATE(),INTERVAL 10 DAY), '11:30', '13:30', '5-8',  'Faktura na firmu, IČ: 12345678',           'ceka',      NOW()),
(6, NULL, 'Zdeněk Procházka', '+420 737 100 200', NULL,                   DATE_SUB(CURDATE(),INTERVAL 2 DAY),  '18:00', '20:00', '1-2',  NULL,                                       'potvrzena', DATE_SUB(NOW(),INTERVAL 5 DAY)),
(7, NULL, 'Oslava — rodina Blažkova','+420 721 555 777',NULL,             DATE_SUB(CURDATE(),INTERVAL 7 DAY),  '17:00', '22:00', '12+',  'Raut 20 os., prosíme o dort z cukrárny',   'potvrzena', DATE_SUB(NOW(),INTERVAL 10 DAY));

-- ============================================================
--  RESTAURANT CATERING REQUESTS — poptávky pronájmu
-- ============================================================
INSERT INTO restaurant_catering_requests (venue_id, contact_name, phone, email, event_date, guests, note, status) VALUES
(1, 'Lenka Horáková',    '+420 732 100 200', 'lenka.horakova@email.cz', DATE_ADD(CURDATE(),INTERVAL 30 DAY), 60, 'Svatební recepce, catering + bar, DJ vlastní', 'reseno'),
(2, 'TJ Sokol Holčovice','+420 554 100 300', 'sokol@holcovice.cz',      DATE_ADD(CURDATE(),INTERVAL 45 DAY), 18, 'Výroční schůze, oběd', 'nova'),
(3, 'Věra Nováková',     '+420 606 700 800', NULL,                       DATE_ADD(CURDATE(),INTERVAL 20 DAY), 25, 'Výjezdní catering — zahrada, gril', 'nova'),
(1, 'ZŠ Holčovice',      '+420 554 200 100', 'reditel@zsholcovice.cz',  DATE_ADD(CURDATE(),INTERVAL 60 DAY), 80, 'Školní akademie + raut pro rodiče', 'uzavrena');

-- ============================================================
--  RESTAURANT MENU RATINGS — hodnocení pokrmů
-- ============================================================
INSERT INTO restaurant_menu_ratings (menu_item_id, user_id, order_item_id, rating) VALUES
-- Jan Novák hodnotí
(1, 9,  1,  4),   -- hovězí vývar
(4, 9,  2,  5),   -- svíčková
(3, 9,  10, 4),   -- vepřová — starší objednávka
(3, 9,  8,  5),   -- vepřová — ještě starší
-- Marie Svobodová hodnotí
(1, 10, 6,  5),   -- hovězí vývar
(4, 10, 7,  4),   -- svíčková
(7, 10, 9,  3),   -- zapečené brambory
-- Další hodnocení (bez vazby na order_item — anonymní legacy)
(3, 9,  NULL, 5),
(6, 10, NULL, 4),
(5, 9,  NULL, 4),
(2, 10, NULL, 5);

SET foreign_key_checks = 1;
