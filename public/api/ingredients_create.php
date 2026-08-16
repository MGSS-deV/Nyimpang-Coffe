<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../includes/auth.php';

requireRoleApi(['Admin']);

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$name = trim($body['name'] ?? '');
$unit = trim($body['unit'] ?? '') ?: 'pcs';
$stockQty = (float) ($body['stockQty'] ?? 0);
$lowStockThreshold = (float) ($body['lowStockThreshold'] ?? 0);

if ($name === '') {
    jsonResponse(['success' => false, 'message' => 'Nama bahan baku wajib diisi'], 400);
}

$stmt = $pdo->prepare(
    "INSERT INTO ingredients (name, unit, stock_qty, low_stock_threshold) VALUES (:name, :unit, :stock_qty, :low_stock_threshold)"
);
$stmt->execute([
    'name' => $name,
    'unit' => $unit,
    'stock_qty' => $stockQty,
    'low_stock_threshold' => $lowStockThreshold
]);

jsonResponse(['success' => true, 'message' => 'Bahan baku ditambahkan', 'id' => (int) $pdo->lastInsertId()], 201);
