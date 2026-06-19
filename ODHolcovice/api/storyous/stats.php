<?php
/**
 * GET /api/storyous/stats.php[?from=2024-12-01&till=2025-06-15&period=daily|weekly|monthly]
 *
 * Analytika tržeb ze StoryOus dat — pro admin dashboard.
 */
require_once __DIR__ . '/../config.php';

$pdo = db();

$from = $_GET['from'] ?? date('Y-m-d', strtotime('-30 days'));
$till = $_GET['till'] ?? date('Y-m-d');
$period = $_GET['period'] ?? 'daily';

$groupBy = match($period) {
    'weekly'  => "DATE_FORMAT(paid_at, '%x-W%v')",
    'monthly' => "DATE_FORMAT(paid_at, '%Y-%m')",
    default   => 'DATE(paid_at)',
};

$baseWhere = 'is_deleted = 0 AND is_refunded = 0 AND paid_at BETWEEN ? AND ?';
$params = [$from . ' 00:00:00', $till . ' 23:59:59'];

$timeline = $pdo->prepare("
    SELECT
        $groupBy AS period,
        COUNT(*) AS bill_count,
        ROUND(SUM(total_amount), 2) AS revenue,
        ROUND(SUM(tips_amount), 2) AS tips,
        ROUND(AVG(total_amount), 2) AS avg_bill,
        SUM(person_count) AS guests
    FROM restaurant_sales
    WHERE $baseWhere
    GROUP BY $groupBy
    ORDER BY period
");
$timeline->execute($params);

$totals = $pdo->prepare("
    SELECT
        COUNT(*) AS bill_count,
        ROUND(SUM(total_amount), 2) AS revenue,
        ROUND(SUM(tips_amount), 2) AS tips,
        ROUND(SUM(discount_amount), 2) AS discounts,
        ROUND(AVG(total_amount), 2) AS avg_bill,
        SUM(person_count) AS guests
    FROM restaurant_sales
    WHERE $baseWhere
");
$totals->execute($params);

$topItems = $pdo->prepare("
    SELECT
        si.name,
        ROUND(SUM(si.amount), 0) AS total_sold,
        ROUND(SUM(si.total_price_with_vat), 2) AS total_revenue,
        COUNT(DISTINCT si.sale_id) AS in_bills
    FROM restaurant_sale_items si
    JOIN restaurant_sales s ON si.sale_id = s.id
    WHERE s.is_deleted = 0 AND s.is_refunded = 0
        AND s.paid_at BETWEEN ? AND ?
    GROUP BY si.name
    ORDER BY total_sold DESC
    LIMIT 15
");
$topItems->execute($params);

$paymentMethods = $pdo->prepare("
    SELECT
        payment_method,
        COUNT(*) AS count,
        ROUND(SUM(total_amount), 2) AS total
    FROM restaurant_sales
    WHERE $baseWhere
    GROUP BY payment_method
    ORDER BY total DESC
");
$paymentMethods->execute($params);

$lastSync = $pdo->query("
    SELECT started_at, details FROM storyous_sync_log
    ORDER BY id DESC LIMIT 1
")->fetch();

json_response([
    'from'     => $from,
    'till'     => $till,
    'period'   => $period,
    'totals'   => $totals->fetch(),
    'timeline' => $timeline->fetchAll(),
    'top_items'       => $topItems->fetchAll(),
    'payment_methods' => $paymentMethods->fetchAll(),
    'last_sync'       => $lastSync ?: null,
]);
