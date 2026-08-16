<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../includes/auth.php';

requireRoleApi(['Admin']);

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$id = (int) ($body['id'] ?? 0);

if ($id <= 0) {
    jsonResponse(['success' => false, 'message' => 'ID produk tidak valid'], 400);
}

// Aman dihapus permanen: pesanan lama nyimpen snapshot nama+harga sendiri
// di kolom items (JSON), jadi nggak bergantung ke baris produk ini lagi.
$stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
$stmt->execute(['id' => $id]);

if ($stmt->rowCount() === 0) {
    jsonResponse(['success' => false, 'message' => 'Produk tidak ditemukan'], 404);
}

jsonResponse(['success' => true, 'message' => 'Menu berhasil dihapus']);
