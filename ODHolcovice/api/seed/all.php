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

    "CREATE TABLE IF NOT EXISTS restaurant_sales (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        storyous_bill_id VARCHAR(50) UNIQUE NOT NULL,
        bill_number VARCHAR(50),
        paid_at DATETIME,
        session_created_at DATETIME,
        total_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
        discount_amount DECIMAL(10,2) DEFAULT 0,
        tips_amount DECIMAL(10,2) DEFAULT 0,
        rounding_amount DECIMAL(10,2) DEFAULT 0,
        currency VARCHAR(5) DEFAULT 'CZK',
        payment_method VARCHAR(30),
        person_count INT DEFAULT 1,
        created_by_name VARCHAR(255),
        paid_by_name VARCHAR(255),
        is_refunded TINYINT(1) DEFAULT 0,
        is_deleted TINYINT(1) DEFAULT 0,
        raw_json LONGTEXT,
        synced_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_paid_at (paid_at),
        INDEX idx_bill_number (bill_number)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS restaurant_sale_items (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        sale_id BIGINT NOT NULL,
        storyous_item_id VARCHAR(50),
        name VARCHAR(255) NOT NULL,
        amount DECIMAL(8,2) DEFAULT 1,
        measure VARCHAR(20) DEFAULT 'ks',
        unit_price_with_vat DECIMAL(10,2) DEFAULT 0,
        total_price_with_vat DECIMAL(10,2) DEFAULT 0,
        vat_rate DECIMAL(4,2) DEFAULT 0,
        discount DECIMAL(10,2) DEFAULT 0,
        FOREIGN KEY (sale_id) REFERENCES restaurant_sales(id) ON DELETE CASCADE,
        INDEX idx_sale (sale_id),
        INDEX idx_item_name (name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS storyous_sync_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sync_type VARCHAR(30) NOT NULL,
        status VARCHAR(20) NOT NULL,
        details TEXT,
        started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
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
        CREATE OR REPLACE VIEW v_daily_sales AS
        SELECT
            DATE(paid_at) AS sale_date,
            COUNT(*) AS bill_count,
            ROUND(SUM(total_amount), 2) AS revenue,
            ROUND(SUM(tips_amount), 2) AS tips,
            ROUND(SUM(discount_amount), 2) AS discounts,
            ROUND(AVG(total_amount), 2) AS avg_bill,
            SUM(person_count) AS total_guests
        FROM restaurant_sales
        WHERE is_deleted = 0 AND is_refunded = 0
        GROUP BY DATE(paid_at)
        ORDER BY sale_date DESC
    ");
} catch (PDOException $e) {}

try {
    $pdo->exec("
        CREATE OR REPLACE VIEW v_top_sold_items AS
        SELECT
            si.name,
            SUM(si.amount) AS total_sold,
            SUM(si.total_price_with_vat) AS total_revenue,
            COUNT(DISTINCT si.sale_id) AS in_bills
        FROM restaurant_sale_items si
        JOIN restaurant_sales s ON si.sale_id = s.id
        WHERE s.is_deleted = 0 AND s.is_refunded = 0
        GROUP BY si.name
        ORDER BY total_sold DESC
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
