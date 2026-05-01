<?php
declare(strict_types=1);
require_once __DIR__ . '/../../core/bootstrap.php';

// POST /api/users
admin_guard();

$b = require_fields(['name', 'email', 'password', 'role']);

$name  = trim($b['name']);
$email = trim($b['email']);
$pass  = $b['password'];
$role  = $b['role'];

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) json_error('Invalid email address');
if (!in_array($role, ['admin', 'viewer'], true)) json_error('Role must be admin or viewer');
if (strlen($pass) < 6) json_error('Password must be at least 6 characters');

if (db_one('SELECT id FROM users WHERE email = ?', [$email])) {
    json_error('Email already exists');
}

$hash = password_hash($pass, PASSWORD_BCRYPT);

$id = db_insert(
    'INSERT INTO users (name, email, password, role, is_active, created_at) VALUES (?, ?, ?, ?, 1, ?)',
    [$name, $email, $hash, $role, now()]
);

json_out(['id' => (int)$id, 'message' => 'User created successfully'], 201);
