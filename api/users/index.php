<?php
declare(strict_types=1);
require_once __DIR__ . '/../../core/bootstrap.php';

// GET /api/users
admin_guard();

$rows = db_all(
    'SELECT id, name, email, role, is_active, created_at FROM users ORDER BY created_at DESC'
);

json_out(['data' => $rows]);
