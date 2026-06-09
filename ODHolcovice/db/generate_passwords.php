<?php
/**
 * Spustit JEDNORÁZOVĚ v příkazové řádce na serveru nebo lokálně:
 *   php generate_passwords.php
 *
 * Vygeneruje UPDATE příkazy se skutečnými bcrypt hashy.
 * Výstup zkopírujte do phpMyAdmin → SQL záložka.
 */

$users = [
    1  => ['admin',      'ZmenteHeslo!2025'],   // ← nastavte silné heslo
    2  => ['vedouci',    'Vedouci2025!'],
    3  => ['obsluha1',   'Obsluha1!'],
    4  => ['obsluha2',   'Obsluha2!'],
    5  => ['kuchyn1',    'Kuchyn1!'],
    6  => ['kuchyn2',    'Kuchyn2!'],
    7  => ['rozvoz1',    'Rozvoz1!'],
    8  => ['sprava.obce','Obec2025!'],
    9  => ['jan.novak',  'Zakaznik1!'],
    10 => ['marie.free', 'Zakaznik2!'],
];

echo "-- Spusťte v phpMyAdmin → SQL:\n\n";
foreach ($users as $id => [$username, $password]) {
    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    echo "UPDATE portal_users SET password_hash = '$hash' WHERE id = $id;  -- $username\n";
}
echo "\n-- Hotovo. Tento soubor po použití SMAŽTE.\n";
