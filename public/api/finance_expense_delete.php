<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../includes/auth.php';

requireAuthApi();

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$id = $body['id'] ?? '';

$stmt = $pdo->prepare("DELETE FROM expenses WHERE id = :id");
$stmt->execute(['id' => $id]);

if ($stmt->rowCount() === 0) {
    jsonResponse(['success' => false, 'message' => 'Data pengeluaran tidak ditemukan'], 404);
}

jsonResponse(['success' => true, 'message' => 'Pengeluaran dihapus']);
