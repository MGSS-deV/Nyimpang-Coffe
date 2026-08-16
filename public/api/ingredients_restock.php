<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../includes/auth.php';

requireRoleApi(['Admin']);

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$id = (int) ($body['id'] ?? 0);
$addQty = (float) ($body['addQty'] ?? 0);
$cost = (int) ($body['cost'] ?? 0);

if ($id <= 0 || $addQty <= 0) {
    jsonResponse(['success' => false, 'message' => 'Jumlah restock harus lebih dari 0'], 400);
}

$check = $pdo->prepare("SELECT * FROM ingredients WHERE id = :id");
$check->execute(['id' => $id]);
$ingredient = $check->fetch();
if (!$ingredient) {
    jsonResponse(['success' => false, 'message' => 'Bahan baku tidak ditemukan'], 404);
}

$pdo->beginTransaction();
try {
    $update = $pdo->prepare("UPDATE ingredients SET stock_qty = stock_qty + :qty WHERE id = :id");
    $update->execute(['qty' => $addQty, 'id' => $id]);

    if ($cost > 0) {
        $expenseId = 'EXP-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
        $expenseStmt = $pdo->prepare(
            "INSERT INTO expenses (id, description, amount, category, ingredient_id)
             VALUES (:id, :description, :amount, 'Bahan Baku', :ingredient_id)"
        );
        $expenseStmt->execute([
            'id' => $expenseId,
            'description' => "Restock {$ingredient['name']} ({$addQty} {$ingredient['unit']})",
            'amount' => $cost,
            'ingredient_id' => $id
        ]);
    }

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    jsonResponse(['success' => false, 'message' => 'Gagal restock, coba lagi'], 500);
}

jsonResponse(['success' => true, 'message' => 'Stok berhasil ditambahkan']);
