<?php
/**
 * POST /api/storyous/webhook.php — DataSync receiver
 *
 * StoryOus posílá změněná data automaticky (bills, menu, stocks, persons).
 * Nastavení: v StoryOus admin zadejte URL tohoto endpointu + auth secret.
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'POST only'], 405);
}

$body = json_decode(file_get_contents('php://input'), true);
if (!$body) {
    json_response(['error' => 'Invalid JSON'], 400);
}

$domain = $body['domain'] ?? 'unknown';
$entities = $body['data'] ?? [];

$pdo = db();

$pdo->prepare('INSERT INTO storyous_sync_log (sync_type, status, details) VALUES (?, ?, ?)')
    ->execute(["webhook:$domain", 'received', json_encode([
        'domain'   => $domain,
        'count'    => count($entities),
        'received' => date('c'),
    ])]);

$processed = 0;

switch ($domain) {
    case 'bills':
        $insert = $pdo->prepare('
            INSERT INTO restaurant_sales
                (storyous_bill_id, bill_number, paid_at, total_amount, discount_amount,
                 tips_amount, currency, payment_method, person_count, is_refunded, is_deleted, raw_json)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                total_amount = VALUES(total_amount),
                is_refunded = VALUES(is_refunded),
                is_deleted = VALUES(is_deleted)
        ');

        foreach ($entities as $bill) {
            $insert->execute([
                $bill['id'] ?? $bill['billNumber'],
                $bill['billNumber'] ?? '',
                $bill['createdAt'] ?? null,
                (float)($bill['totalAmount'] ?? 0),
                (float)($bill['discountAmount'] ?? 0),
                (float)($bill['tipsAmount'] ?? 0),
                $bill['currency'] ?? 'CZK',
                $bill['payments'][0]['paymentTypeId'] ?? 'unknown',
                1,
                (int)(($bill['status'] ?? '') === 'refunded'),
                (int)(($bill['status'] ?? '') === 'deleted'),
                json_encode($bill, JSON_UNESCAPED_UNICODE),
            ]);
            $processed++;
        }
        break;

    case 'items':
    case 'placeitems':
        foreach ($entities as $item) {
            $pdo->prepare('
                UPDATE restaurant_menu_items SET name = ?, is_active = 1
                WHERE storyous_product_id = ?
            ')->execute([$item['name'] ?? '', $item['itemId'] ?? '']);
            $processed++;
        }
        break;

    default:
        break;
}

http_response_code(204);
