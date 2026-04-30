<?php
declare(strict_types=1);
require_once __DIR__ . '/../../core/bootstrap.php';

// PUT /api/products/{id}  — id passed as ?id= or from URL segment
admin_guard();

$id = (int)($_GET['id'] ?? 0);
if (!$id) json_error('Missing product id');

$product = db_one('SELECT * FROM products WHERE id = ?', [$id]);
if (!$product) json_error('Product not found', 404);

$b = body();

$name  = trim($b['name']  ?? $product['name']);
$sku   = trim($b['sku']   ?? $product['sku'] ?? '') ?: null;
$price = isset($b['price'])    ? (float)$b['price']    : (float)$product['price'];
$qty   = isset($b['quantity']) ? (int)$b['quantity']   : (int)$product['quantity'];

if ($name === '') json_error('Name is required');
if ($price < 0)  json_error('Price must be non-negative');
if ($qty < 0)    json_error('Quantity must be non-negative');

db_run(
    'UPDATE products SET sku = ?, name = ?, price = ?, quantity = ? WHERE id = ?',
    [$sku, $name, $price, $qty, $id]
);

json_success('Product updated');
