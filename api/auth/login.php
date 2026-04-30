<?php
declare(strict_types=1);
require_once __DIR__ . '/../../core/bootstrap.php';

// POST /api/auth/login
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_error('Method not allowed', 405);

$b = require_fields(['email', 'password']);

$user = db_one(
    'SELECT id, name, email, password, role FROM users WHERE email = ? AND is_active = 1',
    [trim($b['email'])]
);

if (!$user || !password_verify($b['password'], $user['password'])) {
    json_error('Invalid credentials', 401);
}

$token = jwt_encode([
    'sub'   => $user['id'],
    'name'  => $user['name'],
    'email' => $user['email'],
    'role'  => $user['role'],
]);

json_out([
    'token' => $token,
    'user'  => ['id' => $user['id'], 'name' => $user['name'], 'role' => $user['role']],
]);
