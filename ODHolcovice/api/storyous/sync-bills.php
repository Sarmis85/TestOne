<?php
/**
 * GET /api/storyous/sync-bills.php[?from=2024-12-01&till=2025-06-15]
 *
 * Stáhne účtenky ze StoryOus a uloží do restaurant_sales + restaurant_sale_items.
 * Stránkuje automaticky (limit 100, lastBillId).
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';

if (empty(STORYOUS_MERCHANT_ID) || empty(STORYOUS_PLACE_ID)) {
    json_response(['error' => 'STORYOUS_MERCHANT_ID nebo PLACE_ID není nastaven'], 500);
}

$sourceId = STORYOUS_MERCHANT_ID . '-' . STORYOUS_PLACE_ID;

$from = $_GET['from'] ?? date('Y-m-d', strtotime('-7 days'));
$till = $_GET['till'] ?? date('Y-m-d');

$pdo = db();

$insertBill = $pdo->prepare('
    INSERT INTO restaurant_sales
        (storyous_bill_id, bill_number, paid_at, session_created_at,
         total_amount, discount_amount, tips_amount, rounding_amount,
         currency, payment_method, person_count,
         created_by_name, paid_by_name, is_refunded, is_deleted, raw_json)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
        total_amount = VALUES(total_amount),
        discount_amount = VALUES(discount_amount),
        tips_amount = VALUES(tips_amount),
        payment_method = VALUES(payment_method),
        is_refunded = VALUES(is_refunded),
        is_deleted = VALUES(is_deleted)
');

$insertItem = $pdo->prepare('
    INSERT INTO restaurant_sale_items
        (sale_id, storyous_item_id, name, amount, measure,
         unit_price_with_vat, total_price_with_vat, vat_rate, discount)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
        name = VALUES(name),
        amount = VALUES(amount),
        total_price_with_vat = VALUES(total_price_with_vat)
');

$synced = 0;
$pages = 0;
$lastBillId = null;

do {
    $query = [
        'from'  => $from . 'T00:00:00Z',
        'till'  => $till . 'T23:59:59Z',
        'limit' => '100',
    ];
    if ($lastBillId) {
        $query['lastBillId'] = $lastBillId;
    }

    try {
        $result = storyous_api("/bills/$sourceId", $query);
    } catch (RuntimeException $e) {
        json_response(['error' => $e->getMessage(), 'synced_before_error' => $synced], 500);
    }

    $bills = $result['data'] ?? [];
    $pages++;

    foreach ($bills as $bill) {
        $insertBill->execute([
            $bill['billId'],
            $bill['billNumber'] ?? $bill['billId'],
            $bill['paidAt'] ?? $bill['createdAt'],
            $bill['sessionCreated'] ?? null,
            (float)($bill['finalPrice'] ?? $bill['totalAmount'] ?? 0),
            (float)($bill['discount'] ?? $bill['discountAmount'] ?? 0),
            (float)($bill['tips'] ?? $bill['tipsAmount'] ?? 0),
            (float)($bill['rounding'] ?? $bill['roundingAmount'] ?? 0),
            $bill['currencyCode'] ?? $bill['currency'] ?? 'CZK',
            $bill['paymentMethod'] ?? 'unknown',
            (int)($bill['personCount'] ?? 1),
            $bill['createdBy']['fullName'] ?? null,
            $bill['paidBy']['fullName'] ?? null,
            (int)($bill['refunded'] ?? false),
            (int)($bill['deleted'] ?? false),
            json_encode($bill, JSON_UNESCAPED_UNICODE),
        ]);

        $saleId = $pdo->lastInsertId();
        if (!$saleId) {
            $stmt = $pdo->prepare('SELECT id FROM restaurant_sales WHERE storyous_bill_id = ?');
            $stmt->execute([$bill['billId']]);
            $saleId = $stmt->fetchColumn();
        }

        foreach ($bill['items'] ?? [] as $item) {
            $insertItem->execute([
                $saleId,
                $item['itemId'] ?? $item['productId'] ?? null,
                $item['name'],
                (float)($item['amount'] ?? 1),
                $item['measure'] ?? 'ks',
                (float)($item['unitPriceWithVat'] ?? $item['price'] ?? 0),
                (float)($item['totalPriceWithVat'] ?? ($item['price'] ?? 0) * ($item['amount'] ?? 1)),
                (float)($item['vatRate'] ?? 0),
                (float)($item['unitDiscount'] ?? $item['totalDiscount'] ?? 0),
            ]);
        }

        $synced++;
        $lastBillId = $bill['billId'] ?? $bill['billNumber'] ?? null;
    }

    $hasMore = !empty($result['nextPage']) && !empty($bills);
} while ($hasMore && $pages < 50);

json_response([
    'ok'     => true,
    'synced' => $synced,
    'pages'  => $pages,
    'from'   => $from,
    'till'   => $till,
]);
