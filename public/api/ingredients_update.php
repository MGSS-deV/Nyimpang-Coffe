<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../includes/auth.php';

requireRoleApi(['Admin']);

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$id = (int) ($body['id'] ?? 0);
$name = trim($body['name'] ?? '');
$unit = trim($body['unit'] ?? '') ?: 'pcs';
$lowStockThreshold = (float) ($body['lowStockThreshold'] ?? 0);

if ($id <= 0 || $name === '') {
    jsonResponse(['success' => false, 'message' => 'Data tidak valid'], 400);
}

$check = $pdo->prepare("SELECT id FROM ingredients WHERE id = :id");
$check->execute(['id' => $id]);
if (!$check->fetch()) {
    jsonResponse(['success' => false, 'message' => 'Bahan baku tidak ditemukan'], 404);
}

$stmt = $pdo->prepare(
    "UPDATE ingredients SET name = :name, unit = :unit, low_stock_threshold = :threshold WHERE id = :id"
);
$stmt->execute(['id' => $id, 'name' => $name, 'unit' => $unit, 'threshold' => $lowStockThreshold]);

jsonResponse(['success' => true, 'message' => 'Bahan baku diperbarui']);
