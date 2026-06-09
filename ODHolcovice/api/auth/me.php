<?php
// GET /api/auth/me.php — vrátí aktuálního přihlášeného uživatele nebo 401
require_once __DIR__ . '/../config.php';
session_start();
if (empty($_SESSION['user_id'])) { json_response(['logged_in' => false], 401); }
json_response(['logged_in' => true, 'user_id' => $_SESSION['user_id'],
  'username' => $_SESSION['username'], 'first_name' => $_SESSION['first_name'],
  'last_name' => $_SESSION['last_name'] ?? '', 'roles' => $_SESSION['roles'] ?? []]);
