<?php
declare(strict_types=1);
require_once __DIR__ . '/../../core/bootstrap.php';

// POST /api/stock/in
$auth = admin_guard();

$b = require_fields(['product_id', 'quantity']);

$pid  = (int)$b['product_id'];
$qty  = (int)$b['quantity'];
$note = trim($b['note'] ?? '');

if ($qty <= 0) json_error('Quantity must be > 0');

$product = db_one('SELECT id, name, quantity FROM products WHERE id = ?', [$pid]);
if (!$product) json_error('Product not found', 404);

db_run('UPDATE products SET quantity = quantity + ? WHERE id = ?', [$qty, $pid]);
db_run(
    'INSERT INTO stock_movements (product_id, type, quantity, note, created_by, created_at)
     VALUES (?, "in", ?, ?, ?, ?)',
    [$pid, $qty, $note ?: null, $auth['sub'], now()]
);

json_success("Added $qty units to {$product['name']}");
