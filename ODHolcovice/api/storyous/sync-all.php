<?php
/**
 * GET /api/storyous/sync-all.php[?from=2024-12-01]
 *
 * Master sync: vytvoří tabulky (pokud chybí), spustí sync menu + bills.
 * Volat z WEDOS CRONu: wget -q https://od.prorozvojholcovic.cz/api/storyous/sync-all.php
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/config.php';

$pdo = db();

$tables = [
    "ALTER TABLE restaurant_menu_items
        ADD COLUMN IF NOT EXISTS storyous_product_id VARCHAR(50) DEFAULT NULL,
        ADD UNIQUE INDEX IF NOT EXISTS idx_storyous_product (storyous_product_id)",

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

    "CREATE OR REPLACE VIEW v_daily_sales AS
        SELECT
            DATE(paid_at) AS sale_date,
            COUNT(*) AS bill_count,
            SUM(total_amount) AS revenue,
            SUM(tips_amount) AS tips,
            SUM(discount_amount) AS discounts,
            AVG(total_amount) AS avg_bill,
            SUM(person_count) AS total_guests
        FROM restaurant_sales
        WHERE is_deleted = 0 AND is_refunded = 0
        GROUP BY DATE(paid_at)
        ORDER BY sale_date DESC",

    "CREATE OR REPLACE VIEW v_top_sold_items AS
        SELECT
            si.name,
            SUM(si.amount) AS total_sold,
            SUM(si.total_price_with_vat) AS total_revenue,
            COUNT(DISTINCT si.sale_id) AS in_bills
        FROM restaurant_sale_items si
        JOIN restaurant_sales s ON si.sale_id = s.id
        WHERE s.is_deleted = 0 AND s.is_refunded = 0
        GROUP BY si.name
        ORDER BY total_sold DESC",

    "CREATE TABLE IF NOT EXISTS storyous_sync_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sync_type VARCHAR(30) NOT NULL,
        status VARCHAR(20) NOT NULL,
        details TEXT,
        started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
];

$errors = [];
foreach ($tables as $sql) {
    try {
        $pdo->exec($sql);
    } catch (PDOException $e) {
        $errors[] = $e->getMessage();
    }
}

if (empty(STORYOUS_CLIENT_ID)) {
    json_response([
        'ok'      => true,
        'message' => 'Tabulky vytvořeny. StoryOus API klíče nejsou nastaveny — sync přeskočen.',
        'tables_errors' => $errors,
    ]);
}

$results = [];

ob_start();
$_GET['from'] = $_GET['from'] ?? date('Y-m-d', strtotime('-7 days'));
include __DIR__ . '/sync-menu.php';
$results['menu'] = json_decode(ob_get_clean(), true);

ob_start();
include __DIR__ . '/sync-bills.php';
$results['bills'] = json_decode(ob_get_clean(), true);

$pdo->prepare('INSERT INTO storyous_sync_log (sync_type, status, details) VALUES (?, ?, ?)')
    ->execute(['full', 'ok', json_encode($results, JSON_UNESCAPED_UNICODE)]);

json_response([
    'ok'      => true,
    'results' => $results,
    'tables_errors' => $errors,
]);
