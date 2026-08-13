<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../includes/auth.php';

requireAuthApi();

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$description = trim($body['description'] ?? '');
$amount = (int) ($body['amount'] ?? 0);
$category = $body['category'] ?? 'Lainnya';

$validCategories = ['Bahan Baku', 'Operasional', 'Gaji', 'Lainnya'];
if (!in_array($category, $validCategories, true)) {
    $category = 'Lainnya';
}

if ($description === '' || $amount <= 0) {
    jsonResponse(['success' => false, 'message' => 'Deskripsi dan nominal wajib diisi dengan benar'], 400);
}

$id = 'EXP-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));

$stmt = $pdo->prepare(
    "INSERT INTO expenses (id, description, amount, category) VALUES (:id, :description, :amount, :category)"
);
$stmt->execute([
    'id' => $id,
    'description' => $description,
    'amount' => $amount,
    'category' => $category
]);

jsonResponse(['success' => true, 'message' => 'Pengeluaran dicatat'], 201);
