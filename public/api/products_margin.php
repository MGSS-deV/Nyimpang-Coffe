<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../includes/auth.php';

requireRoleApi(['Admin']);

// Harga rata-rata per satuan bahan baku, dihitung dari histori restock
// yang ada biayanya: total Rp dibagi total qty yang pernah direstock.
$avgCostRows = $pdo->query(
    "SELECT ingredient_id, SUM(amount) AS total_cost, SUM(restock_qty) AS total_qty
     FROM expenses
     WHERE ingredient_id IS NOT NULL AND restock_qty IS NOT NULL AND restock_qty > 0
     GROUP BY ingredient_id"
)->fetchAll();

$avgCostByIngredient = [];
foreach ($avgCostRows as $row) {
    $avgCostByIngredient[$row['ingredient_id']] = $row['total_cost'] / $row['total_qty'];
}

$products = $pdo->query("SELECT id, name, price FROM products WHERE is_active = 1")->fetchAll();

$recipeRows = $pdo->query(
    "SELECT product_id, ingredient_id, qty_per_serving FROM product_ingredients"
)->fetchAll();

$recipeByProduct = [];
foreach ($recipeRows as $r) {
    $recipeByProduct[$r['product_id']][] = $r;
}

$result = [];
foreach ($products as $p) {
    $recipe = $recipeByProduct[$p['id']] ?? [];
    $hasFullCostData = !empty($recipe);
    $estimatedCost = 0;

    foreach ($recipe as $r) {
        $avgCost = $avgCostByIngredient[$r['ingredient_id']] ?? null;
        if ($avgCost === null) {
            // Ada bahan yang belum pernah direstock dengan biaya -> nggak bisa dihitung akurat
            $hasFullCostData = false;
            continue;
        }
        $estimatedCost += $avgCost * (float) $r['qty_per_serving'];
    }

    $price = (int) $p['price'];
    $margin = $hasFullCostData ? $price - round($estimatedCost) : null;
    $marginPercent = ($hasFullCostData && $price > 0) ? round(($margin / $price) * 100, 1) : null;

    $result[] = [
        'id' => (int) $p['id'],
        'name' => $p['name'],
        'price' => $price,
        'estimatedCost' => $hasFullCostData ? (int) round($estimatedCost) : null,
        'margin' => $margin,
        'marginPercent' => $marginPercent,
        'hasFullCostData' => $hasFullCostData
    ];
}

jsonResponse(['success' => true, 'products' => $result]);
