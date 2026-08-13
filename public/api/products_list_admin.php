<?php
// FITUR BARU: dipakai halaman Manajemen Menu (menu.php). Beda dari
// products_list.php yang publik, ini butuh login dan menampilkan SEMUA
// menu termasuk yang sedang dinonaktifkan.
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../includes/auth.php';

requireAuthApi();

$stmt = $pdo->query(
    "SELECT id, name, description, price, category, icon, is_active
     FROM products ORDER BY id ASC"
);
$products = $stmt->fetchAll();

foreach ($products as &$p) {
    $p['price'] = (int) $p['price'];
    $p['isActive'] = (bool) $p['is_active'];
    unset($p['is_active']);
}

jsonResponse(['success' => true, 'products' => $products]);
