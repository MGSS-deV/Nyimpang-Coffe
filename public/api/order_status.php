<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../includes/auth.php';

$id = $_GET['id'] ?? '';

if ($id === '') {
    jsonResponse(['success' => false, 'message' => 'ID pesanan wajib diisi'], 400);
}

// Sengaja cuma balikin id + status (bukan data lengkap) supaya pelanggan
// nggak bisa intip data pesanan orang lain lewat endpoint publik ini.
$stmt = $pdo->prepare("SELECT id, status FROM orders WHERE id = :id");
$stmt->execute(['id' => $id]);
$order = $stmt->fetch();

if (!$order) {
    jsonResponse(['success' => false, 'message' => 'Pesanan tidak ditemukan'], 404);
}

jsonResponse(['success' => true, 'order' => $order]);
