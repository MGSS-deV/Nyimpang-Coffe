<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../includes/auth.php';

requireRoleApi(['Admin']);

$products = $pdo->query(
    "SELECT id, name, description, price, category, icon, is_active FROM products ORDER BY id ASC"
)->fetchAll();

foreach ($products as &$p) {
    $p['price'] = (int) $p['price'];
    $p['isActive'] = (bool) $p['is_active'];
    unset($p['is_active']);
}

jsonResponse(['success' => true, 'products' => $products]);
