<?php
declare(strict_types=1);

function get_auth_header(): string {
    // Method 1: Standard
    if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
        return $_SERVER['HTTP_AUTHORIZATION'];
    }
    // Method 2: After mod_rewrite redirect
    if (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        return $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    }
    // Method 3: Apache with getallheaders()
    if (function_exists('getallheaders')) {
        $headers = getallheaders();
        foreach ($headers as $key => $value) {
            if (strtolower($key) === 'authorization') {
                return $value;
            }
        }
    }
    // Method 4: Read directly from apache_request_headers
    if (function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
        foreach ($headers as $key => $value) {
            if (strtolower($key) === 'authorization') {
                return $value;
            }
        }
    }
    return '';
}

function auth_guard(): array {
    $header = get_auth_header();
    if (!str_starts_with($header, 'Bearer ')) {
        json_error('Unauthorized — no token found', 401);
    }
    $token   = substr($header, 7);
    $payload = jwt_decode($token);
    if (!$payload) json_error('Invalid or expired token', 401);
    return $payload;
}

function admin_guard(): array {
    $payload = auth_guard();
    if (($payload['role'] ?? '') !== 'admin') {
        json_error('Forbidden: admin only', 403);
    }
    return $payload;
}