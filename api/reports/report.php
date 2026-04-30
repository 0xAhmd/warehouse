<?php
declare(strict_types=1);
require_once __DIR__ . '/../../core/bootstrap.php';

// GET /api/reports?type=inventory|movements|invoice
auth_guard();

$type = $_GET['type'] ?? 'inventory';

if ($type === 'inventory') {
    $rows = db_all(
        'SELECT id, sku, name, price, quantity, faulty_quantity,
                (quantity + faulty_quantity) AS total
           FROM products ORDER BY name'
    );
    json_out(['type' => 'inventory', 'data' => $rows]);

} elseif ($type === 'movements') {
    $limit  = min((int)($_GET['limit'] ?? 30), 200);
    $mov_type = $_GET['mov_type'] ?? null; // in|out|return

    $sql  = 'SELECT m.id, p.name AS product, m.type, m.quantity, m.note,
                    u.name AS user, m.created_at
               FROM stock_movements m
               JOIN products p ON m.product_id = p.id
               JOIN users    u ON m.created_by  = u.id';
    $params = [];

    if ($mov_type) {
        $sql   .= ' WHERE m.type = ?';
        $params[] = $mov_type;
    }
    $sql .= ' ORDER BY m.created_at DESC LIMIT ' . $limit;

    json_out(['type' => 'movements', 'data' => db_all($sql, $params)]);

} elseif ($type === 'invoice') {
    $raw = $_GET['ids'] ?? '';
    $ids = array_filter(array_map('intval', explode(',', $raw)));
    if (empty($ids)) json_error('Provide ?ids=1,2,3');

    $ph   = implode(',', array_fill(0, count($ids), '?'));
    $rows = db_all(
        "SELECT m.id, p.name AS product, m.quantity, m.note, m.created_at, u.name AS user
           FROM stock_movements m
           JOIN products p ON m.product_id = p.id
           JOIN users    u ON m.created_by  = u.id
          WHERE m.id IN ($ph)",
        $ids
    );
    json_out(['type' => 'invoice', 'date' => today(), 'data' => $rows]);

} else {
    json_error('Unknown report type');
}
