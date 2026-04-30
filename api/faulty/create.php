<?php
declare(strict_types=1);
require_once __DIR__ . '/../../core/bootstrap.php';

// POST /api/faulty
// body: { product_id, quantity, direction: "to_faulty"|"to_working" }
admin_guard();

$b = require_fields(['product_id', 'quantity', 'direction']);

$pid       = (int)$b['product_id'];
$qty       = (int)$b['quantity'];
$direction = $b['direction'];

if ($qty <= 0) json_error('Quantity must be > 0');
if (!in_array($direction, ['to_faulty', 'to_working'], true)) json_error('Invalid direction');

$product = db_one('SELECT * FROM products WHERE id = ?', [$pid]);
if (!$product) json_error('Product not found', 404);

if ($direction === 'to_faulty') {
    if ($qty > (int)$product['quantity']) {
        json_error("Only {$product['quantity']} working units available");
    }
    db_run(
        'UPDATE products SET quantity = quantity - ?, faulty_quantity = faulty_quantity + ? WHERE id = ?',
        [$qty, $qty, $pid]
    );
    json_success("$qty unit(s) marked as faulty");
} else {
    if ($qty > (int)$product['faulty_quantity']) {
        json_error("Only {$product['faulty_quantity']} faulty units available");
    }
    db_run(
        'UPDATE products SET faulty_quantity = faulty_quantity - ?, quantity = quantity + ? WHERE id = ?',
        [$qty, $qty, $pid]
    );
    json_success("$qty unit(s) restored to working stock");
}
