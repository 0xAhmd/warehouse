<?php
declare(strict_types=1);
require_once __DIR__ . '/../../core/bootstrap.php';

// DELETE /api/users?id=N
$auth = admin_guard();

$id = (int)($_GET['id'] ?? 0);
if (!$id) json_error('Missing user id');

if ($id === (int)$auth['sub']) json_error('You cannot delete your own account');

if (!db_one('SELECT id FROM users WHERE id = ?', [$id])) {
    json_error('User not found', 404);
}

db_run('DELETE FROM users WHERE id = ?', [$id]);
json_success('User deleted successfully');
