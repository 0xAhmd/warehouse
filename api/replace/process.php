<?php
declare(strict_types=1);
require_once __DIR__ . '/../../core/bootstrap.php';

// POST /api/replace/process
// Search: GET ?q=keyword
// Process: POST body: { movement_id, quantity, return_type: "working"|"faulty" }
$auth = admin_guard();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $q = trim($_GET['q'] ?? '');
    if ($q === '') json_error('Provide search keyword ?q=');

    $rows = db_all(
        'SELECT m.id, p.name AS product, m.quantity, m.note, m.created_at
           FROM stock_movements m
           JOIN products p ON m.product_id = p.id
          WHERE m.type = "out" AND (p.name LIKE ? OR m.note LIKE ?)
          ORDER BY m.created_at DESC LIMIT 50',
        ["%$q%", "%$q%"]
    );
    json_out(['data' => $rows]);
}

// POST
$b           = require_fields(['movement_id', 'quantity', 'return_type']);
$movement_id = (int)$b['movement_id'];
$qty         = (int)$b['quantity'];
$return_type = $b['return_type'];

if ($qty <= 0) json_error('Quantity must be > 0');
if (!in_array($return_type, ['working', 'faulty'], true)) json_error('Invalid return_type');

$movement = db_one(
    'SELECT m.*, p.quantity AS w_qty, p.faulty_quantity FROM stock_movements m
       JOIN products p ON m.product_id = p.id WHERE m.id = ? AND m.type = "out"',
    [$movement_id]
);
if (!$movement) json_error('Movement not found', 404);
if ($qty > (int)$movement['quantity']) {
    json_error("Cannot return more than {$movement['quantity']} units");
}

$col = $return_type === 'faulty' ? 'faulty_quantity' : 'quantity';
db_run("UPDATE products SET $col = $col + ? WHERE id = ?", [$qty, $movement['product_id']]);
db_run('UPDATE stock_movements SET quantity = quantity - ? WHERE id = ?', [$qty, $movement_id]);
db_run(
    'INSERT INTO stock_movements (product_id, type, quantity, note, created_by, created_at)
     VALUES (?, "return", ?, ?, ?, ?)',
    [$movement['product_id'], $qty, "Returned ($return_type) from movement #$movement_id", $auth['sub'], now()]
);

json_success("$qty unit(s) returned to $return_type stock");
