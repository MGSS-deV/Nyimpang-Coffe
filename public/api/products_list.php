<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../includes/auth.php';

$stmt = $pdo->query("SELECT id, name, description, price, category, icon FROM products WHERE is_active = 1 ORDER BY id ASC");
$products = $stmt->fetchAll();

// price disimpan sebagai INT di DB, pastikan dikirim sebagai number (bukan string)
foreach ($products as &$p) {
    $p['price'] = (int) $p['price'];
}

jsonResponse(['success' => true, 'products' => $products]);
