<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../includes/auth.php';

requireRoleApi(['Admin']);

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$productId = (int) ($body['productId'] ?? 0);
$recipe = $body['recipe'] ?? [];

if ($productId <= 0) {
    jsonResponse(['success' => false, 'message' => 'Produk tidak valid'], 400);
}

$pdo->beginTransaction();
try {
    $pdo->prepare("DELETE FROM product_ingredients WHERE product_id = :product_id")
        ->execute(['product_id' => $productId]);

    $insert = $pdo->prepare(
        "INSERT INTO product_ingredients (product_id, ingredient_id, qty_per_serving) VALUES (:product_id, :ingredient_id, :qty)"
    );

    foreach ($recipe as $item) {
        $ingredientId = (int) ($item['ingredientId'] ?? 0);
        $qty = (float) ($item['qtyPerServing'] ?? 0);
        if ($ingredientId <= 0 || $qty <= 0) continue;

        $insert->execute([
            'product_id' => $productId,
            'ingredient_id' => $ingredientId,
            'qty' => $qty
        ]);
    }

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    jsonResponse(['success' => false, 'message' => 'Gagal menyimpan resep'], 500);
}

jsonResponse(['success' => true, 'message' => 'Resep berhasil disimpan']);
