<?php
/**
 * GET /api/seed/all.php — spustí všechny seed skripty najednou
 * Vytvoří tabulky (pokud neexistují), naplní demo daty
 */
require_once __DIR__ . '/../config.php';

$pdo = db();

// Vytvoříme tabulky, které mohou chybět
$tables = [
    "CREATE TABLE IF NOT EXISTS portal_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        first_name VARCHAR(100),
        last_name VARCHAR(100),
        phone VARCHAR(20),
        is_active TINYINT(1) DEFAULT 1,
        last_login DATETIME,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS portal_user_roles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        role VARCHAR(30) NOT NULL,
        UNIQUE KEY (user_id, role),
        FOREIGN KEY (user_id) REFERENCES portal_users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS portal_events (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS restaurant_menu_items (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS restaurant_menu_ratings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        menu_item_id INT NOT NULL,
        user_id INT,
        rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
        comment TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (menu_item_id) REFERENCES restaurant_menu_items(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS restaurant_daily_menu (
        id INT AUTO_INCREMENT PRIMARY KEY,
        menu_date DATE NOT NULL UNIQUE,
        soup_id INT,
        main1_id INT,
        main2_id INT,
        vege_id INT,
        is_weekend TINYINT(1) DEFAULT 0,
        note TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS restaurant_reservations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_name VARCHAR(255) NOT NULL,
        email VARCHAR(255),
        phone VARCHAR(30),
        date DATE NOT NULL,
        time TIME NOT NULL,
        guests INT NOT NULL DEFAULT 2,
        note TEXT,
        status ENUM('pending','confirmed','cancelled','completed') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS restaurant_orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_name VARCHAR(255),
        address TEXT,
        phone VARCHAR(30),
        items TEXT,
        total_price DECIMAL(10,2),
        status VARCHAR(30) DEFAULT 'nova',
        delivery_type ENUM('delivery','pickup') DEFAULT 'delivery',
        note TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
];

$created = 0;
foreach ($tables as $sql) {
    try {
        $pdo->exec($sql);
        $created++;
    } catch (PDOException $e) {
        // Ignorujeme chyby (tabulka může existovat s jinou strukturou)
    }
}

// Vytvoříme views
try {
    $pdo->exec("
        CREATE OR REPLACE VIEW v_menu_item_ratings AS
        SELECT mi.*,
               COUNT(r.id) AS rating_count,
               AVG(r.rating) AS rating_avg
        FROM restaurant_menu_items mi
        LEFT JOIN restaurant_menu_ratings r ON r.menu_item_id = mi.id
        GROUP BY mi.id
    ");
} catch (PDOException $e) {}

try {
    $pdo->exec("
        CREATE OR REPLACE VIEW v_today_menu AS
        SELECT dm.*,
               s.name AS soup_name, s.price_kc AS soup_price, s.allergens AS soup_allergens,
               m1.name AS main1_name, m1.price_kc AS main1_price, m1.allergens AS main1_allergens,
               m2.name AS main2_name, m2.price_kc AS main2_price, m2.allergens AS main2_allergens,
               v.name AS vege_name, v.price_kc AS vege_price, v.allergens AS vege_allergens
        FROM restaurant_daily_menu dm
        LEFT JOIN restaurant_menu_items s ON dm.soup_id = s.id
        LEFT JOIN restaurant_menu_items m1 ON dm.main1_id = m1.id
        LEFT JOIN restaurant_menu_items m2 ON dm.main2_id = m2.id
        LEFT JOIN restaurant_menu_items v ON dm.vege_id = v.id
    ");
} catch (PDOException $e) {}

// Spustíme jednotlivé seed skripty
$results = [];

ob_start();
include __DIR__ . '/users.php';
$results['users'] = json_decode(ob_get_clean(), true);

ob_start();
include __DIR__ . '/foods.php';
$results['foods'] = json_decode(ob_get_clean(), true);

ob_start();
include __DIR__ . '/events.php';
$results['events'] = json_decode(ob_get_clean(), true);

json_response([
    'ok'      => true,
    'tables'  => $created,
    'seeds'   => $results,
    'message' => 'Všechny seed skripty dokončeny.',
]);
