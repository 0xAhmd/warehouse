<?php
declare(strict_types=1);

/**
 * Decode JWT from Authorization header.
 * Returns payload array or sends 401 and exits.
 */
function auth_guard(): array {
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!str_starts_with($header, 'Bearer ')) {
        json_error('Unauthorized', 401);
    }
    $token   = substr($header, 7);
    $payload = jwt_decode($token);
    if (!$payload) json_error('Invalid or expired token', 401);
    return $payload;
}

/**
 * Require admin role. Returns payload or sends 403.
 */
function admin_guard(): array {
    $payload = auth_guard();
    if (($payload['role'] ?? '') !== 'admin') {
        json_error('Forbidden: admin only', 403);
    }
    return $payload;
}
