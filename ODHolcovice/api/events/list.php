<?php
/**
 * GET /api/events/list.php
 * ?promote=1     → jen promo akce na homepage
 * ?upcoming=1    → jen budoucí
 * ?limit=10
 */
require_once __DIR__ . '/../config.php';

$where = ['is_published = 1', 'date_start >= CURDATE()']; $params = [];
if (!empty($_GET['promote'])) { $where[] = 'promote_homepage = 1'; }
$limit = min((int)($_GET['limit'] ?? 20), 100);

$stmt = db()->prepare('SELECT id, title, slug, body, date_start, date_end,
        location, image_url, promote_homepage, is_published
    FROM portal_events WHERE ' . implode(' AND ', $where)
    . ' ORDER BY date_start ASC LIMIT ' . $limit);
$stmt->execute($params);
json_response($stmt->fetchAll());
