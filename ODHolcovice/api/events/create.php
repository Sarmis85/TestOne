<?php
/**
 * POST /api/events/create.php
 * Body JSON: { title, body?, date_start, date_end?, location?, image_url?,
 *              promote_homepage?, is_published? }
 * Vyžaduje roli: obec | vedouci | super
 */
require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Pouze POST'], 405);
}

require_role('obec', 'vedouci', 'super');
$sess = $_SESSION;

$b = json_decode(file_get_contents('php://input'), true) ?? [];

if (empty(trim($b['title'] ?? ''))) {
    json_response(['error' => 'Název akce je povinný'], 422);
}
if (empty($b['date_start'])) {
    json_response(['error' => 'Datum začátku je povinné'], 422);
}

// Slug z názvu (unikátní)
function slugify(string $text): string {
    $text = mb_strtolower($text, 'UTF-8');
    $map  = ['á'=>'a','č'=>'c','ď'=>'d','é'=>'e','ě'=>'e','í'=>'i','ň'=>'n',
             'ó'=>'o','ř'=>'r','š'=>'s','ť'=>'t','ú'=>'u','ů'=>'u','ý'=>'y','ž'=>'z'];
    $text = strtr($text, $map);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

$baseSlug = slugify($b['title']);
$slug     = $baseSlug;
$i = 2;
while (true) {
    $check = db()->prepare('SELECT id FROM portal_events WHERE slug = ?');
    $check->execute([$slug]);
    if (!$check->fetch()) break;
    $slug = $baseSlug . '-' . $i++;
}

$stmt = db()->prepare('
    INSERT INTO portal_events
      (title, slug, body, date_start, date_end, location, image_url,
       promote_homepage, is_published, author_id)
    VALUES
      (:title, :slug, :body, :date_start, :date_end, :location, :image_url,
       :promote, :published, :author)
');

$stmt->execute([
    ':title'     => trim($b['title']),
    ':slug'      => $slug,
    ':body'      => trim($b['body'] ?? '') ?: null,
    ':date_start'=> $b['date_start'],
    ':date_end'  => !empty($b['date_end']) ? $b['date_end'] : null,
    ':location'  => trim($b['location'] ?? '') ?: null,
    ':image_url' => trim($b['image_url'] ?? '') ?: null,
    ':promote'   => (int)($b['promote_homepage'] ?? 0),
    ':published' => (int)($b['is_published'] ?? 0),
    ':author'    => $sess['user_id'] ?? null,
]);

json_response([
    'ok'    => true,
    'id'    => (int)db()->lastInsertId(),
    'slug'  => $slug,
], 201);
