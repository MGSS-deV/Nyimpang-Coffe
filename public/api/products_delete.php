<?php
// FITUR BARU: Manajemen Menu — hapus menu permanen. Khusus role Admin.
// Catatan: pesanan lama tetap aman karena `items` di tabel orders disimpan
// sebagai snapshot JSON (nama+harga saat itu), bukan foreign key ke produk.
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../includes/auth.php';

requireRoleApi('Admin');

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$id = (int) ($body['id'] ?? 0);

$stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
$stmt->execute(['id' => $id]);

if ($stmt->rowCount() === 0) {
    jsonResponse(['success' => false, 'message' => 'Menu tidak ditemukan'], 404);
}

jsonResponse(['success' => true, 'message' => 'Menu dihapus']);
