<?php
declare(strict_types=1);
require_once __DIR__ . '/../../core/bootstrap.php';

// POST /api/products
admin_guard();

$b = require_fields(['name', 'price', 'quantity']);

$name  = trim($b['name']);
$sku   = trim($b['sku'] ?? '') ?: null;
$price = (float)$b['price'];
$qty   = (int)$b['quantity'];

if ($price < 0) json_error('Price must be non-negative');
if ($qty < 0)   json_error('Quantity must be non-negative');

// Duplicate check
if ($sku && db_one('SELECT id FROM products WHERE sku = ?', [$sku])) {
    json_error('SKU already exists');
}
if (db_one('SELECT id FROM products WHERE name = ?', [$name])) {
    json_error('Product name already exists');
}

$id = db_insert(
    'INSERT INTO products (sku, name, price, quantity, faulty_quantity, created_at)
     VALUES (?, ?, ?, ?, 0, ?)',
    [$sku, $name, $price, $qty, now()]
);

json_out(['id' => (int)$id, 'message' => 'Product created'], 201);
