<?php
/**
 * Pro rozvoj Holčovic — PHP mailer
 * Umístit do root složky webu (vedle index.html)
 *
 * NASTAVENÍ:
 *   $to      — e-mail, kam přijdou zprávy
 *   $subject — předmět zprávy
 */

// ── Konfigurace ────────────────────────────────────
$to      = 'info@prorozvojholcovic.cz';   // ← sem přijdou zprávy
$subject = 'Zpráva z webu Pro rozvoj Holčovic';
// ──────────────────────────────────────────────────

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// Pouze POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Metoda není povolena.']);
    exit;
}

// Honeypot — robotem vyplněné pole → zahodit potichu
if (!empty($_POST['website'])) {
    http_response_code(200);
    echo json_encode(['ok' => true]);
    exit;
}

// Načtení a sanitizace polí
$name    = trim(strip_tags($_POST['name']    ?? ''));
$email   = trim(strip_tags($_POST['email']   ?? ''));
$message = trim(strip_tags($_POST['message'] ?? ''));

// Validace
$errors = [];
if ($name === '')                        $errors[] = 'Vyplňte prosím jméno.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Zadejte platnou e-mailovou adresu.';
if (strlen($message) < 10)              $errors[] = 'Zpráva je příliš krátká.';

if ($errors) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => implode(' ', $errors)]);
    exit;
}

// Sestavení zprávy
$body  = "Jméno:   {$name}\n";
$body .= "E-mail:  {$email}\n";
$body .= "Zpráva:\n{$message}\n";

// Hlavičky mailu
$headers  = "From: web@prorozvojholcovic.cz\r\n";
$headers .= "Reply-To: {$email}\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

// Odeslání
$sent = mail($to, $subject, $body, $headers);

if ($sent) {
    echo json_encode(['ok' => true]);
} else {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Zprávu se nepodařilo odeslat. Zkuste to prosím znovu.']);
}
