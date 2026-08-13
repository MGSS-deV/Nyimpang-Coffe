<?php
// FITUR BARU: Manajemen Menu — tambah menu baru. Khusus role Admin, supaya
// barista biasa nggak bisa asal ubah harga/menu dari browser.
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../includes/auth.php';

requireRoleApi('Admin');

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$name = trim($body['name'] ?? '');
$description = trim($body['description'] ?? '');
$price = (int) ($body['price'] ?? 0);
$category = trim($body['category'] ?? '') ?: 'Lainnya';
$icon = trim($body['icon'] ?? '') ?: '☕';

if ($name === '' || $price <= 0) {
    jsonResponse(['success' => false, 'message' => 'Nama menu dan harga wajib diisi dengan benar'], 400);
}

$stmt = $pdo->prepare(
    "INSERT INTO products (name, description, price, category, icon, is_active)
     VALUES (:name, :description, :price, :category, :icon, 1)"
);
$stmt->execute([
    'name' => $name,
    'description' => $description,
    'price' => $price,
    'category' => $category,
    'icon' => $icon
]);

jsonResponse(['success' => true, 'message' => 'Menu ditambahkan', 'id' => (int) $pdo->lastInsertId()], 201);
