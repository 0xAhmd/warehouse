<?php
declare(strict_types=1);

$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Strip /warehouse/api prefix and trailing slash
$path = preg_replace('#^/warehouse/api/?#', '', $uri);
$path = rtrim($path, '/');

$parts = explode('/', $path);
$base  = $parts[0] ?? '';
$sub   = $parts[1] ?? '';

if (isset($parts[1]) && ctype_digit($parts[1])) {
    $_GET['id'] = $parts[1];
    $sub = $parts[2] ?? '';
}

$map = [
    'auth/login' => __DIR__ . '/../api/auth/login.php',

    'products' => match($method) {
        'GET'    => __DIR__ . '/../api/products/index.php',
        'POST'   => __DIR__ . '/../api/products/create.php',
        'PUT'    => __DIR__ . '/../api/products/update.php',
        'DELETE' => __DIR__ . '/../api/products/delete.php',
        default  => null,
    },

    'stock/in'  => __DIR__ . '/../api/stock/in.php',
    'stock/out' => __DIR__ . '/../api/stock/out.php',

    'faulty' => match($method) {
        'GET'  => __DIR__ . '/../api/faulty/index.php',
        'POST' => __DIR__ . '/../api/faulty/create.php',
        default => null,
    },

    'replace' => __DIR__ . '/../api/replace/process.php',
    'import'  => __DIR__ . '/../api/import/import.php',
    'reports' => __DIR__ . '/../api/reports/report.php',

    'users' => match($method) {
        'GET'    => __DIR__ . '/../api/users/index.php',
        'POST'   => __DIR__ . '/../api/users/create.php',
        'PUT'    => __DIR__ . '/../api/users/update.php',
        'DELETE' => __DIR__ . '/../api/users/delete.php',
        default  => null,
    },
];

$key = $base;
if ($sub) $key .= "/$sub";

$file = $map[$key] ?? null;

if (!$file || !file_exists($file)) {
    header('Content-Type: application/json');
    http_response_code(404);
    echo json_encode(['error' => "Route not found: $method /$path"]);
    exit;
}

require $file;
