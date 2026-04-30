<?php
declare(strict_types=1);

function json_out(array $data, int $code = 200): never {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function json_error(string $message, int $code = 400): never {
    json_out(['error' => $message], $code);
}

function json_success(string $message, array $extra = []): never {
    json_out(array_merge(['success' => true, 'message' => $message], $extra));
}

function body(): array {
    static $parsed = null;
    if ($parsed === null) {
        $raw    = file_get_contents('php://input');
        $parsed = json_decode($raw ?: '{}', true) ?? [];
    }
    return $parsed;
}

function require_fields(array $fields): array {
    $body = body();
    foreach ($fields as $f) {
        if (!isset($body[$f]) || (is_string($body[$f]) && trim($body[$f]) === '')) {
            json_error("Field '$f' is required");
        }
    }
    return $body;
}

function now(): string  { return date('Y-m-d H:i:s'); }
function today(): string { return date('Y-m-d'); }

function parse_csv(string $filepath): array {
    $rows = [];
    if (($fh = fopen($filepath, 'r')) === false) return $rows;
    $headers = fgetcsv($fh);
    if (!$headers) { fclose($fh); return $rows; }
    $headers = array_map(fn($h) => strtolower(trim($h)), $headers);
    while (($row = fgetcsv($fh)) !== false) {
        $rows[] = array_combine($headers, array_map('trim', $row));
    }
    fclose($fh);
    return $rows;
}
