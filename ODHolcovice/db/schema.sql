-- ============================================================
--  Obecní dům Holčovice — databázové schéma
--  MariaDB 10.4+  |  charset: utf8mb4
--  Spustit: mysql -u <user> -p <db_name> < schema.sql
-- ============================================================

SET NAMES utf8mb4;
SET time_zone = '+01:00';
SET foreign_key_checks = 0;

-- ============================================================
--  PORTAL — sdílená vrstva (users, roles, events, articles)
-- ============================================================

-- ------------------------------------------------------------
--  Uživatelé
--  username: krátký login pro personál (kuchyn1, obsluha, ...)
--  email:    nullable — povinný pouze pro zákazníky
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS portal_users (
  id            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  username      VARCHAR(64)     NOT NULL UNIQUE,          -- primární přihlašovací jméno
  email         VARCHAR(255)    NULL     UNIQUE,          -- nullable pro interní účty
  password_hash VARCHAR(255)    NOT NULL,                 -- bcrypt / password_hash()
  first_name    VARCHAR(100)    NOT NULL DEFAULT '',
  last_name     VARCHAR(100)    NOT NULL DEFAULT '',
  phone         VARCHAR(30)     NULL,
  is_active     TINYINT(1)      NOT NULL DEFAULT 1,
  created_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  last_login    DATETIME        NULL,
  PRIMARY KEY (id),
  KEY idx_email    (email),
  KEY idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- ------------------------------------------------------------
--  Role  (uživatel může mít více rolí)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS portal_user_roles (
  user_id   INT UNSIGNED NOT NULL,
  role      ENUM('super','vedouci','obsluha','kuchyn','rozvoz','obec','zakaznik')
            NOT NULL,
  granted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, role),
  CONSTRAINT fk_role_user FOREIGN KEY (user_id)
    REFERENCES portal_users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
--  Akce & události
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS portal_events (
  id               INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  title            VARCHAR(255)  NOT NULL,
  slug             VARCHAR(255)  NOT NULL UNIQUE,
  body             TEXT          NULL,
  date_start       DATETIME      NOT NULL,
  date_end         DATETIME      NULL,
  location         VARCHAR(255)  NULL,
  image_url        VARCHAR(500)  NULL,
  promote_homepage TINYINT(1)    NOT NULL DEFAULT 0,   -- checkbox v adminu
  is_published     TINYINT(1)    NOT NULL DEFAULT 0,
  author_id        INT UNSIGNED  NULL,
  created_at       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
                   ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_date_start      (date_start),
  KEY idx_promote         (promote_homepage, is_published),
  CONSTRAINT fk_event_author FOREIGN KEY (author_id)
    REFERENCES portal_users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- ------------------------------------------------------------
--  Články
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS portal_articles (
  id           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  title        VARCHAR(255)  NOT NULL,
  slug         VARCHAR(255)  NOT NULL UNIQUE,
  perex        VARCHAR(500)  NULL,
  body         TEXT          NULL,
  image_url    VARCHAR(500)  NULL,
  is_published TINYINT(1)    NOT NULL DEFAULT 0,
  published_at DATETIME      NULL,
  author_id    INT UNSIGNED  NULL,
  created_at   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
               ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_published (is_published, published_at),
  CONSTRAINT fk_article_author FOREIGN KEY (author_id)
    REFERENCES portal_users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;


-- ============================================================
--  RESTAURANT — provozní vrstva OD Holčovice
-- ============================================================

-- ------------------------------------------------------------
--  Databáze jídel
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS restaurant_menu_items (
  id          INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  name        VARCHAR(255)   NOT NULL,
  category    ENUM('Polévka','Předkrm','Hlavní','Vegetariánské','Dezert','Nápoj','Jiné')
              NOT NULL DEFAULT 'Hlavní',
  price_kc    DECIMAL(8,2)   NOT NULL,
  allergens   VARCHAR(100)   NULL,        -- "1, 3, 7"
  weight_g    VARCHAR(30)    NULL,        -- "350 g" / "300 ml"
  is_vege     TINYINT(1)     NOT NULL DEFAULT 0,
  is_active   TINYINT(1)     NOT NULL DEFAULT 1,
  created_at  DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_category (category),
  KEY idx_active   (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- ------------------------------------------------------------
--  Denní menu (přiřazení jídel na konkrétní datum)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS restaurant_daily_menu (
  id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  menu_date   DATE          NOT NULL UNIQUE,
  soup_id     INT UNSIGNED  NULL,
  main1_id    INT UNSIGNED  NULL,
  main2_id    INT UNSIGNED  NULL,
  vege_id     INT UNSIGNED  NULL,
  is_weekend  TINYINT(1)    NOT NULL DEFAULT 0,
  note        VARCHAR(500)  NULL,
  PRIMARY KEY (id),
  KEY idx_date (menu_date),
  CONSTRAINT fk_dm_soup  FOREIGN KEY (soup_id)  REFERENCES restaurant_menu_items (id) ON DELETE SET NULL,
  CONSTRAINT fk_dm_main1 FOREIGN KEY (main1_id) REFERENCES restaurant_menu_items (id) ON DELETE SET NULL,
  CONSTRAINT fk_dm_main2 FOREIGN KEY (main2_id) REFERENCES restaurant_menu_items (id) ON DELETE SET NULL,
  CONSTRAINT fk_dm_vege  FOREIGN KEY (vege_id)  REFERENCES restaurant_menu_items (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
--  Objednávky (rozvoz obědů)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS restaurant_orders (
  id               INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  user_id          INT UNSIGNED  NULL,     -- NULL = hostující objednávka
  delivery_date    DATE          NOT NULL,
  delivery_address TEXT          NULL,
  contact_name     VARCHAR(200)  NULL,
  contact_phone    VARCHAR(30)   NULL,
  status           ENUM('nova','prijata','pripravuje','vyrazi','dorucena','zrusena')
                   NOT NULL DEFAULT 'nova',
  total_kc         DECIMAL(8,2)  NOT NULL DEFAULT 0,
  note             TEXT          NULL,
  created_at       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
                   ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_date   (delivery_date),
  KEY idx_status (status),
  KEY idx_user   (user_id),
  CONSTRAINT fk_order_user FOREIGN KEY (user_id)
    REFERENCES portal_users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- ------------------------------------------------------------
--  Položky objednávky
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS restaurant_order_items (
  id            INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  order_id      INT UNSIGNED   NOT NULL,
  menu_item_id  INT UNSIGNED   NOT NULL,
  quantity      TINYINT        NOT NULL DEFAULT 1,
  price_at_time DECIMAL(8,2)   NOT NULL,   -- cena v momentě objednávky
  PRIMARY KEY (id),
  CONSTRAINT fk_oi_order FOREIGN KEY (order_id)
    REFERENCES restaurant_orders (id) ON DELETE CASCADE,
  CONSTRAINT fk_oi_item FOREIGN KEY (menu_item_id)
    REFERENCES restaurant_menu_items (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
--  Hodnocení pokrmů zákazníky
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS restaurant_menu_ratings (
  id            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  menu_item_id  INT UNSIGNED  NOT NULL,
  user_id       INT UNSIGNED  NOT NULL,
  order_item_id INT UNSIGNED  NULL,        -- vazba na konkrétní objednávku
  rating        TINYINT       NOT NULL CHECK (rating BETWEEN 1 AND 5),
  created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_user_item_order (user_id, menu_item_id, order_item_id),
  CONSTRAINT fk_rating_item  FOREIGN KEY (menu_item_id)  REFERENCES restaurant_menu_items (id) ON DELETE CASCADE,
  CONSTRAINT fk_rating_user  FOREIGN KEY (user_id)       REFERENCES portal_users (id) ON DELETE CASCADE,
  CONSTRAINT fk_rating_oitem FOREIGN KEY (order_item_id) REFERENCES restaurant_order_items (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
--  Rezervace stolů
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS restaurant_reservations (
  id            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  user_id       INT UNSIGNED  NULL,        -- přihlášený zákazník (volitelné)
  name          VARCHAR(200)  NOT NULL,
  phone         VARCHAR(30)   NOT NULL,
  email         VARCHAR(255)  NULL,
  res_date      DATE          NOT NULL,
  time_from     TIME          NOT NULL,
  time_to       TIME          NULL,
  guests_range  VARCHAR(20)   NOT NULL,    -- "3-4", "5-8", ...
  note          TEXT          NULL,
  status        ENUM('ceka','potvrzena','zrusena')
                NOT NULL DEFAULT 'ceka',
  created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_date   (res_date),
  KEY idx_status (status),
  CONSTRAINT fk_res_user FOREIGN KEY (user_id)
    REFERENCES portal_users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- ------------------------------------------------------------
--  Prostory (sál, salónek, catering)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS restaurant_venues (
  id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  name        VARCHAR(100)  NOT NULL,
  capacity    SMALLINT      NULL,
  description TEXT          NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- ------------------------------------------------------------
--  Poptávky pronájmu / cateringu
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS restaurant_catering_requests (
  id            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  venue_id      INT UNSIGNED  NULL,
  contact_name  VARCHAR(200)  NOT NULL,
  phone         VARCHAR(30)   NOT NULL,
  email         VARCHAR(255)  NULL,
  event_date    DATE          NULL,
  guests        SMALLINT      NULL,
  note          TEXT          NULL,
  status        ENUM('nova','reseno','uzavrena','zrusena')
                NOT NULL DEFAULT 'nova',
  created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  CONSTRAINT fk_cr_venue FOREIGN KEY (venue_id)
    REFERENCES restaurant_venues (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;


-- ============================================================
--  TESTOVACÍ DATA
-- ============================================================

-- ------------------------------------------------------------
--  Uživatelé — hesla jsou 'heslo123' (bcrypt placeholder)
--  V produkci vygenerovat přes PHP: password_hash('heslo123', PASSWORD_BCRYPT)
--
--  ROLE PŘEHLED:
--    super       → plný přístup ke všemu
--    vedouci     → menu, jídla, objednávky, rezervace, akce
--    obsluha     → objednávky + rezervace (čtení + stav)
--    kuchyn      → fronta objednávek (čtení + stav přípravy)
--    rozvoz      → doručovací objednávky (čtení + stav doručení)
--    obec        → akce, články
--    zakaznik    → vlastní objednávky, rezervace, hodnocení
--
--  USERNAME KONVENCE:
--    personál    → krátký alias bez @ (admin, vedouci, kuchyn1…)
--    zákazníci   → email jako username (jan.novak@example.cz)
-- ------------------------------------------------------------

-- Hesla jsou nastavena jako 'SETUP_NEEDED' — po importu spusťte setup.php
-- který přepíše password_hash skutečnými bcrypt hashy.
INSERT INTO portal_users
  (username,               email,                          password_hash,   first_name,  last_name,   phone)
VALUES
  ('admin',                NULL,                           'SETUP_NEEDED',  'Admin',     'Systém',    NULL),
  ('vedouci',              'vedouci@obecnidumholcovice.cz','SETUP_NEEDED',  'Jana',      'Veselá',    '+420 601 111 001'),
  ('obsluha1',             NULL,                           'SETUP_NEEDED',  'Petra',     'Horáková',  '+420 601 111 002'),
  ('obsluha2',             NULL,                           'SETUP_NEEDED',  'Tomáš',     'Blažek',    NULL),
  ('kuchyn1',              NULL,                           'SETUP_NEEDED',  'Karel',     'Novotný',   NULL),
  ('kuchyn2',              NULL,                           'SETUP_NEEDED',  'Marie',     'Součková',  NULL),
  ('rozvoz1',              NULL,                           'SETUP_NEEDED',  'Pavel',     'Horák',     '+420 601 111 005'),
  ('sprava.obce',          'obec@holcovice.cz',            'SETUP_NEEDED',  'Eva',       'Fišerová',  '+420 601 111 006'),
  ('jan.novak@example.cz', 'jan.novak@example.cz',        'SETUP_NEEDED',  'Jan',       'Novák',     '+420 601 222 001'),
  ('marie.free@seznam.cz', 'marie.free@seznam.cz',        'SETUP_NEEDED',  'Marie',     'Svobodová', '+420 601 222 002');

-- Role přiřazení
INSERT INTO portal_user_roles (user_id, role) VALUES
  (1, 'super'),
  (2, 'vedouci'),
  (3, 'obsluha'),
  (4, 'obsluha'),
  (5, 'kuchyn'),
  (6, 'kuchyn'),
  (7, 'rozvoz'),
  (8, 'obec'),
  (9, 'zakaznik'),
  (10,'zakaznik');

-- Prostory
INSERT INTO restaurant_venues (name, capacity, description) VALUES
  ('Velký sál',  80,  'Hlavní sál s plným cateringem, vlastní bar, projekce'),
  ('Salónek',    20,  'Uzavřený prostor pro firemní akce a rodinné oslavy'),
  ('Catering',   NULL,'Výjezdní catering — vaříme u vás');

-- Ukázková jídla
INSERT INTO restaurant_menu_items (name, category, price_kc, allergens, weight_g, is_vege, is_active) VALUES
  ('Hovězí vývar s nudlemi',            'Polévka',       65.00, '1, 3',      '300 ml', 0, 1),
  ('Česneková polévka se sýrem',         'Polévka',       79.00, '1, 7',      '300 ml', 0, 1),
  ('Vepřová pečeně, knedlík, zelí',      'Hlavní',       159.00, '1, 3, 7',  '350 g',  0, 1),
  ('Svíčková na smetaně, knedlík',       'Hlavní',       259.00, '1, 3, 7',  '350 g',  0, 1),
  ('Kuřecí řízek, bramborový salát',     'Hlavní',       169.00, '1, 3, 7, 10','320 g',0, 1),
  ('Gulášek s houskovým knedlíkem',      'Hlavní',       155.00, '1, 3',      '380 g',  0, 1),
  ('Zapečené brambory se sýrem',         'Vegetariánské',129.00, '7',         '300 g',  1, 1),
  ('Čočkový dhal s rýží',               'Vegetariánské',165.00, NULL,        '350 g',  1, 0),
  -- Víkendové menu
  ('Svíčková na smetaně — víkend',      'Hlavní',       289.00, '1, 3, 7',  '400 g',  0, 1),
  ('Pstruh na másle, vařené brambory',   'Hlavní',       249.00, '4, 7',      '320 g',  0, 1),
  ('Houbové rizoto s parmazánem',        'Vegetariánské',189.00, '1, 7',      '350 g',  1, 1);

-- Dnešní denní menu (demo)
INSERT INTO restaurant_daily_menu (menu_date, soup_id, main1_id, main2_id, vege_id, is_weekend) VALUES
  (CURDATE(), 1, 3, 5, 7, IF(DAYOFWEEK(CURDATE()) IN (1,6,7), 1, 0));

-- Ukázková rezervace
INSERT INTO restaurant_reservations (user_id, name, phone, email, res_date, time_from, time_to, guests_range, status) VALUES
  (9, 'Jan Novák',       '+420 601 222 001', 'jan.novak@example.cz', DATE_ADD(CURDATE(), INTERVAL 3 DAY), '18:00', '20:00', '3-4',  'potvrzena'),
  (NULL,'Petra Malá',    '+420 777 888 999', NULL,                   DATE_ADD(CURDATE(), INTERVAL 5 DAY), '12:00', NULL,   '1-2',  'ceka'),
  (NULL,'Obecní úřad',   '+420 554 000 100', 'obec@holcovice.cz',   DATE_ADD(CURDATE(), INTERVAL 14 DAY),'10:00', '14:00','9-12', 'ceka');

-- Ukázkové objednávky
INSERT INTO restaurant_orders (user_id, delivery_date, delivery_address, contact_name, contact_phone, status, total_kc) VALUES
  (9,    CURDATE(), 'Holčovice 42, 793 71', 'Jan Novák',    '+420 601 222 001', 'dorucena', 224.00),
  (10,   CURDATE(), 'Holčovice 88, 793 71', 'Marie Svobodová','+420 601 222 002','pripravuje',159.00),
  (NULL, CURDATE(), 'Heřmanovice 15',       'Pavel Horák',  '+420 554 100 200', 'nova',     328.00);

INSERT INTO restaurant_order_items (order_id, menu_item_id, quantity, price_at_time) VALUES
  (1, 1, 1,  65.00),
  (1, 4, 1, 259.00),   -- svíčková → sleva polévky: 30 Kč (platí PHP logika)
  (2, 3, 1, 159.00),
  (3, 1, 1,  65.00),
  (3, 3, 1, 159.00),
  (3, 5, 1, 169.00);

-- Ukázková hodnocení
INSERT INTO restaurant_menu_ratings (menu_item_id, user_id, order_item_id, rating) VALUES
  (1, 9, 1, 4),   -- hovězí vývar → 4★
  (4, 9, 2, 5);   -- svíčková → 5★

-- Ukázková akce s promote_homepage
INSERT INTO portal_events (title, slug, body, date_start, date_end, promote_homepage, is_published, author_id) VALUES
  ('Zasedání zastupitelstva obce',
   'zasedani-zastupitelstva-2025-06',
   'Veřejné zasedání zastupitelstva obce Holčovice. Program: rozpočtové změny, územní plán, různé.',
   DATE_ADD(CURDATE(), INTERVAL 7 DAY),
   NULL, 1, 1, 8),
  ('Letní tábor pro děti — přihlášky',
   'letni-tabor-2025',
   'Přihlášky na letní příměstský tábor jsou otevřeny. Kapacita omezena na 20 dětí.',
   DATE_ADD(CURDATE(), INTERVAL 21 DAY),
   NULL, 0, 1, 8);

SET foreign_key_checks = 1;

-- ============================================================
--  POMOCNÉ POHLEDY (VIEW) — pro admin a API
-- ============================================================

-- Průměrné hodnocení pokrmů
CREATE OR REPLACE VIEW v_menu_item_ratings AS
SELECT
  mi.id,
  mi.name,
  mi.category,
  mi.price_kc,
  mi.is_active,
  COUNT(r.id)          AS rating_count,
  ROUND(AVG(r.rating), 1) AS rating_avg
FROM restaurant_menu_items mi
LEFT JOIN restaurant_menu_ratings r ON r.menu_item_id = mi.id
GROUP BY mi.id;

-- Dnešní menu s detaily jídel
CREATE OR REPLACE VIEW v_today_menu AS
SELECT
  dm.menu_date,
  dm.is_weekend,
  s.id AS soup_id,  s.name AS soup_name,  s.price_kc AS soup_price,  s.allergens AS soup_allergens,
  m1.id AS main1_id, m1.name AS main1_name, m1.price_kc AS main1_price, m1.allergens AS main1_allergens,
  m2.id AS main2_id, m2.name AS main2_name, m2.price_kc AS main2_price, m2.allergens AS main2_allergens,
  v.id AS vege_id,  v.name AS vege_name,  v.price_kc AS vege_price,  v.allergens AS vege_allergens
FROM restaurant_daily_menu dm
LEFT JOIN restaurant_menu_items s  ON s.id  = dm.soup_id
LEFT JOIN restaurant_menu_items m1 ON m1.id = dm.main1_id
LEFT JOIN restaurant_menu_items m2 ON m2.id = dm.main2_id
LEFT JOIN restaurant_menu_items v  ON v.id  = dm.vege_id
WHERE dm.menu_date = CURDATE();

-- Otevřené objednávky (kuchyně + rozvoz)
CREATE OR REPLACE VIEW v_active_orders AS
SELECT
  o.id, o.delivery_date, o.delivery_address,
  o.contact_name, o.contact_phone, o.status, o.total_kc,
  o.created_at,
  GROUP_CONCAT(
    CONCAT(oi.quantity, '× ', mi.name)
    ORDER BY mi.category SEPARATOR ' | '
  ) AS items_summary
FROM restaurant_orders o
JOIN restaurant_order_items oi ON oi.order_id = o.id
JOIN restaurant_menu_items  mi ON mi.id = oi.menu_item_id
WHERE o.status NOT IN ('dorucena','zrusena')
  AND o.delivery_date = CURDATE()
GROUP BY o.id;
