<?php
declare(strict_types=1);
require_once __DIR__ . '/../../core/bootstrap.php';

// PUT /api/users?id=N
$auth = admin_guard();

$id = (int)($_GET['id'] ?? 0);
if (!$id) json_error('Missing user id');

$user = db_one('SELECT * FROM users WHERE id = ?', [$id]);
if (!$user) json_error('User not found', 404);

// Prevent admin from demoting themselves
if ($id === (int)$auth['sub']) json_error('You cannot edit your own account here');

$b = body();

$name      = trim($b['name']      ?? $user['name']);
$email     = trim($b['email']     ?? $user['email']);
$role      = $b['role']            ?? $user['role'];
$is_active = isset($b['is_active']) ? (int)(bool)$b['is_active'] : (int)$user['is_active'];

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) json_error('Invalid email address');
if (!in_array($role, ['admin', 'viewer'], true)) json_error('Role must be admin or viewer');

// Check email uniqueness (excluding current user)
$existing = db_one('SELECT id FROM users WHERE email = ? AND id != ?', [$email, $id]);
if ($existing) json_error('Email already in use');

// Update password only if provided
if (!empty($b['password'])) {
    if (strlen($b['password']) < 6) json_error('Password must be at least 6 characters');
    $hash = password_hash($b['password'], PASSWORD_BCRYPT);
    db_run(
        'UPDATE users SET name = ?, email = ?, password = ?, role = ?, is_active = ? WHERE id = ?',
        [$name, $email, $hash, $role, $is_active, $id]
    );
} else {
    db_run(
        'UPDATE users SET name = ?, email = ?, role = ?, is_active = ? WHERE id = ?',
        [$name, $email, $role, $is_active, $id]
    );
}

json_success('User updated successfully');
