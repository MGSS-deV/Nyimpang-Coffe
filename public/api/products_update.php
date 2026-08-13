<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../includes/auth.php';

requireAuthApi();

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$id = (int) ($body['id'] ?? 0);

if ($id <= 0) {
    jsonResponse(['success' => false, 'message' => 'ID produk tidak valid'], 400);
}

$check = $pdo->prepare("SELECT id FROM products WHERE id = :id");
$check->execute(['id' => $id]);
if (!$check->fetch()) {
    jsonResponse(['success' => false, 'message' => 'Produk tidak ditemukan'], 404);
}

// Update fleksibel: kalau field nggak dikirim, nilai lama dipakai (COALESCE-style di PHP)
$fields = [];
$params = ['id' => $id];

if (isset($body['name'])) {
    $name = trim($body['name']);
    if ($name === '') {
        jsonResponse(['success' => false, 'message' => 'Nama menu tidak boleh kosong'], 400);
    }
    $fields[] = 'name = :name';
    $params['name'] = $name;
}
if (isset($body['description'])) {
    $fields[] = 'description = :description';
    $params['description'] = trim($body['description']);
}
if (isset($body['price'])) {
    $price = (int) $body['price'];
    if ($price <= 0) {
        jsonResponse(['success' => false, 'message' => 'Harga harus lebih dari 0'], 400);
    }
    $fields[] = 'price = :price';
    $params['price'] = $price;
}
if (isset($body['category'])) {
    $fields[] = 'category = :category';
    $params['category'] = trim($body['category']) ?: 'Lainnya';
}
if (isset($body['icon'])) {
    $fields[] = 'icon = :icon';
    $params['icon'] = trim($body['icon']) ?: '☕';
}
if (isset($body['isActive'])) {
    $fields[] = 'is_active = :is_active';
    $params['is_active'] = $body['isActive'] ? 1 : 0;
}

if (empty($fields)) {
    jsonResponse(['success' => false, 'message' => 'Nggak ada data yang diubah'], 400);
}

$sql = "UPDATE products SET " . implode(', ', $fields) . " WHERE id = :id";
$pdo->prepare($sql)->execute($params);

jsonResponse(['success' => true, 'message' => 'Menu berhasil diperbarui']);
