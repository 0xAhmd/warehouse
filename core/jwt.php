<?php
declare(strict_types=1);

define('JWT_SECRET', getenv('JWT_SECRET') ?: 'change_this_secret_in_production');
define('JWT_TTL',    60 * 60 * 8); // 8 hours

function jwt_encode(array $payload): string {
    $header  = base64url_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    $payload['exp'] = time() + JWT_TTL;
    $payload['iat'] = time();
    $body    = base64url_encode(json_encode($payload));
    $sig     = base64url_encode(hash_hmac('sha256', "$header.$body", JWT_SECRET, true));
    return "$header.$body.$sig";
}

function jwt_decode(string $token): array|false {
    $parts = explode('.', $token);
    if (count($parts) !== 3) return false;

    [$header, $body, $sig] = $parts;
    $expected = base64url_encode(hash_hmac('sha256', "$header.$body", JWT_SECRET, true));
    if (!hash_equals($expected, $sig)) return false;

    $payload = json_decode(base64url_decode($body), true);
    if (!$payload || ($payload['exp'] ?? 0) < time()) return false;

    return $payload;
}

function base64url_encode(string $data): string {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode(string $data): string {
    return base64_decode(strtr($data, '-_', '+/'));
}
