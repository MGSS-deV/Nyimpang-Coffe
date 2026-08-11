<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../includes/auth.php';
require __DIR__ . '/../../includes/orders_helper.php';

requireAuthApi();

$rows = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC")->fetchAll();
$orders = array_map('formatOrderRow', $rows);

jsonResponse([
    'success' => true,
    'orders' => $orders,
    'stats' => computeOrderStats($pdo)
]);
