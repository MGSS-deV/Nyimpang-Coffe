<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../includes/auth.php';

$stmt = $pdo->query("SELECT id, name, description, price, category, icon FROM products WHERE is_active = 1 ORDER BY id ASC");
$products = $stmt->fetchAll();

// Ambil semua resep sekaligus (lebih efisien daripada query per-produk)
$recipeRows = $pdo->query(
    "SELECT pi.product_id, pi.qty_per_serving, i.stock_qty
     FROM product_ingredients pi
     JOIN ingredients i ON i.id = pi.ingredient_id"
)->fetchAll();

$recipeByProduct = [];
foreach ($recipeRows as $r) {
    $recipeByProduct[$r['product_id']][] = [
        'qtyPerServing' => (float) $r['qty_per_serving'],
        'stockQty' => (float) $r['stock_qty']
    ];
}

foreach ($products as &$p) {
    $p['price'] = (int) $p['price'];

    $requirements = $recipeByProduct[$p['id']] ?? [];
    $p['inStock'] = true;
    foreach ($requirements as $req) {
        if ($req['stockQty'] < $req['qtyPerServing']) {
            $p['inStock'] = false;
            break;
        }
    }
}

jsonResponse(['success' => true, 'products' => $products]);
