<?php
declare(strict_types=1);
require_once __DIR__ . '/../../core/bootstrap.php';

// GET /api/faulty
auth_guard();

$rows = db_all(
    'SELECT id, name, quantity, faulty_quantity
       FROM products WHERE faulty_quantity > 0 ORDER BY name'
);

json_out(['data' => $rows]);
