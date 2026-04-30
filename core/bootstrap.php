<?php
declare(strict_types=1);

// CORS — adjust origin for production
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204); exit;
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/jwt.php';
require_once __DIR__ . '/guard.php';
