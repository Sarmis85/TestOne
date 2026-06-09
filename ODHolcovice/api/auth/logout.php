<?php
// POST /api/auth/logout.php
require_once __DIR__ . '/../config.php';
session_start(); session_destroy();
json_response(['ok' => true]);
