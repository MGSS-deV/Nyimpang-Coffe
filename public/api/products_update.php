<?php
// FITUR BARU: Manajemen Menu — edit menu / ubah status aktif-nonaktif.
// Khusus role Admin.
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../includes/auth.php';

requireRoleApi('Admin');

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$id = (int) ($body['id'] ?? 0);
$name = trim($body['name'] ?? '');
$description = trim($body['description'] ?? '');
$price = (int) ($body['price'] ?? 0);
$category = trim($body['category'] ?? '') ?: 'Lainnya';
$icon = trim($body['icon'] ?? '') ?: '☕';
$isActive = isset($body['isActive']) ? (int) (bool) $body['isActive'] : 1;

if ($id <= 0 || $name === '' || $price <= 0) {
    jsonResponse(['success' => false, 'message' => 'Data menu tidak valid'], 400);
}

$check = $pdo->prepare("SELECT id FROM products WHERE id = :id");
$check->execute(['id' => $id]);
if (!$check->fetch()) {
    jsonResponse(['success' => false, 'message' => 'Menu tidak ditemukan'], 404);
}

$stmt = $pdo->prepare(
    "UPDATE products SET name = :name, description = :description, price = :price,
     category = :category, icon = :icon, is_active = :is_active WHERE id = :id"
);
$stmt->execute([
    'name' => $name,
    'description' => $description,
    'price' => $price,
    'category' => $category,
    'icon' => $icon,
    'is_active' => $isActive,
    'id' => $id
]);

jsonResponse(['success' => true, 'message' => 'Menu diperbarui']);
