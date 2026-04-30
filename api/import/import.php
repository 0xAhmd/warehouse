<?php
declare(strict_types=1);
require_once __DIR__ . '/../../core/bootstrap.php';

// POST /api/import  (multipart/form-data, field: file)
admin_guard();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_error('Method not allowed', 405);
if (empty($_FILES['file'])) json_error('No file uploaded');

$file = $_FILES['file'];
if ($file['error'] !== UPLOAD_ERR_OK) json_error('Upload error');

$ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if ($ext === 'csv') {
    $rows = parse_csv($file['tmp_name']);
} elseif (in_array($ext, ['xlsx', 'xls'], true)) {
    $rows = parse_xlsx($file['tmp_name']);
} else {
    json_error('Only CSV or XLSX files supported');
}

$result = import_products($rows);
json_out($result, 200);

// ── helpers ──────────────────────────────────────────────────────────────────

function import_products(array $rows): array {
    $inserted = $updated = $skipped = 0;
    foreach ($rows as $row) {
        $name  = trim($row['name']     ?? '');
        $sku   = trim($row['sku']      ?? '') ?: null;
        $price = isset($row['price'])    ? (float)$row['price']  : 0.0;
        $qty   = isset($row['quantity']) ? (int)$row['quantity'] : 0;

        if ($name === '' || $qty <= 0) { $skipped++; continue; }

        $existing = ($sku ? db_one('SELECT id FROM products WHERE sku = ?', [$sku]) : null)
                 ?? db_one('SELECT id FROM products WHERE name = ?', [$name]);

        if ($existing) {
            db_run('UPDATE products SET quantity = quantity + ? WHERE id = ?', [$qty, $existing['id']]);
            $updated++;
        } else {
            db_insert(
                'INSERT INTO products (sku, name, price, quantity, faulty_quantity, created_at)
                 VALUES (?, ?, ?, ?, 0, ?)',
                [$sku, $name, $price, $qty, now()]
            );
            $inserted++;
        }
    }
    return compact('inserted', 'updated', 'skipped');
}

function parse_xlsx(string $filepath): array {
    $rows = []; $zip = new ZipArchive();
    if ($zip->open($filepath) !== true) return $rows;

    $strings = [];
    if ($ssXml = $zip->getFromName('xl/sharedStrings.xml')) {
        $ss = simplexml_load_string($ssXml);
        foreach ($ss->si as $si) $strings[] = (string)$si->t;
    }

    $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
    $zip->close();
    if (!$sheetXml) return $rows;

    $sheet = simplexml_load_string($sheetXml);
    $headers = [];

    foreach ($sheet->sheetData->row as $i => $row) {
        $cells = [];
        foreach ($row->c as $cell) {
            $v = (string)$cell->v;
            $cells[] = (string)$cell['t'] === 's' ? ($strings[(int)$v] ?? '') : $v;
        }
        if ($i === 0) {
            $headers = array_map(fn($h) => strtolower(trim($h)), $cells);
        } else {
            if (count($cells) < count($headers)) $cells = array_pad($cells, count($headers), '');
            $rows[] = array_combine($headers, array_slice($cells, 0, count($headers)));
        }
        if ($i > 500) break;
    }
    return $rows;
}
