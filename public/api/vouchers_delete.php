<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../includes/auth.php';

requireRoleApi(['Admin']);

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$id = (int) ($body['id'] ?? 0);

$stmt = $pdo->prepare("DELETE FROM vouchers WHERE id = :id");
$stmt->execute(['id' => $id]);

if ($stmt->rowCount() === 0) {
    jsonResponse(['success' => false, 'message' => 'Voucher tidak ditemukan'], 404);
}

jsonResponse(['success' => true, 'message' => 'Voucher dihapus']);
