<?php
/**
 * GET /api/events/list.php
 * ?promote=1     → jen promo akce na homepage
 * ?upcoming=1    → jen budoucí
 * ?admin=1       → vše bez filtru (pro admin panel)
 * ?limit=10
 */
require_once __DIR__ . '/../config.php';

$isAdmin = !empty($_GET['admin']);
$where = []; $params = [];

if (!$isAdmin) {
    $where[] = 'is_published = 1';
    $where[] = 'date_start >= CURDATE()';
}
if (!empty($_GET['promote'])) { $where[] = 'promote_homepage = 1'; }

$whereStr = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
$limit = min((int)($_GET['limit'] ?? 20), 200);

$stmt = db()->prepare('SELECT id, title, slug, body, date_start, date_end,
        location, image_url, promote_homepage, is_published
    FROM portal_events ' . $whereStr . ' ORDER BY date_start ASC LIMIT ' . $limit);
$stmt->execute($params);
json_response($stmt->fetchAll());
