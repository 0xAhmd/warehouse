<?php
declare(strict_types=1);
require_once __DIR__ . '/../../core/bootstrap.php';

// GET /api/products
auth_guard();

$search = trim($_GET['q'] ?? '');
if ($search !== '') {
    $rows = db_all(
        'SELECT id, sku, name, price, quantity, faulty_quantity, created_at
           FROM products WHERE name LIKE ? OR sku LIKE ? ORDER BY name',
        ["%$search%", "%$search%"]
    );
} else {
    $rows = db_all(
        'SELECT id, sku, name, price, quantity, faulty_quantity, created_at
           FROM products ORDER BY name'
    );
}

json_out(['data' => $rows]);
