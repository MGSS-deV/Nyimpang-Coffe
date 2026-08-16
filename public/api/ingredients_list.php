<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../includes/auth.php';

requireRoleApi(['Admin']);

$ingredients = $pdo->query(
    "SELECT id, name, unit, stock_qty, low_stock_threshold FROM ingredients ORDER BY name ASC"
)->fetchAll();

foreach ($ingredients as &$i) {
    $i['stockQty'] = (float) $i['stock_qty'];
    $i['lowStockThreshold'] = (float) $i['low_stock_threshold'];
    $i['isLow'] = $i['stockQty'] <= $i['lowStockThreshold'];
    unset($i['stock_qty'], $i['low_stock_threshold']);
}

jsonResponse(['success' => true, 'ingredients' => $ingredients]);
