<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../includes/auth.php';

requireRoleApi(['Admin']);

$productId = (int) ($_GET['product_id'] ?? 0);

$stmt = $pdo->prepare(
    "SELECT pi.ingredient_id, pi.qty_per_serving, i.name, i.unit
     FROM product_ingredients pi
     JOIN ingredients i ON i.id = pi.ingredient_id
     WHERE pi.product_id = :product_id"
);
$stmt->execute(['product_id' => $productId]);
$rows = $stmt->fetchAll();

$recipe = array_map(fn($r) => [
    'ingredientId' => (int) $r['ingredient_id'],
    'name' => $r['name'],
    'unit' => $r['unit'],
    'qtyPerServing' => (float) $r['qty_per_serving']
], $rows);

jsonResponse(['success' => true, 'recipe' => $recipe]);
