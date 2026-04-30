<?php
declare(strict_types=1);
require_once __DIR__ . '/../../core/bootstrap.php';

// DELETE /api/products/{id}
admin_guard();

$id = (int)($_GET['id'] ?? 0);
if (!$id) json_error('Missing product id');

if (!db_one('SELECT id FROM products WHERE id = ?', [$id])) {
    json_error('Product not found', 404);
}

db_run('DELETE FROM products WHERE id = ?', [$id]);
json_success('Product deleted');
