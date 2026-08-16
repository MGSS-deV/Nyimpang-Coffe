<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../includes/auth.php';
require __DIR__ . '/../../includes/orders_helper.php';
require __DIR__ . '/../../includes/whatsapp.php';

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$rawItems = $body['items'] ?? [];

if (empty($rawItems) || !is_array($rawItems)) {
    jsonResponse(['success' => false, 'message' => 'Keranjang kosong!'], 400);
}

$productStmt = $pdo->prepare("SELECT id, name, price FROM products WHERE id = :id AND is_active = 1");

$verifiedItems = [];
$totalAmount = 0;

foreach ($rawItems as $rawItem) {
    $productId = (int) ($rawItem['id'] ?? 0);
    $qty = (int) ($rawItem['qty'] ?? 0);

    if ($productId <= 0 || $qty <= 0 || $qty > 50) {
        jsonResponse(['success' => false, 'message' => 'Item pesanan tidak valid'], 400);
    }

    $productStmt->execute(['id' => $productId]);
    $product = $productStmt->fetch();

    if (!$product) {
        jsonResponse(['success' => false, 'message' => 'Produk tidak ditemukan atau sudah tidak tersedia'], 400);
    }

    $verifiedItems[] = [
        'id' => (int) $product['id'],
        'name' => $product['name'],
        'price' => (int) $product['price'],
        'qty' => $qty
    ];
    $totalAmount += $product['price'] * $qty;
}

$pdo->beginTransaction();

try {
    $neededPerIngredient = [];
    $recipeStmt = $pdo->prepare(
        "SELECT ingredient_id, qty_per_serving FROM product_ingredients WHERE product_id = :product_id"
    );

    foreach ($verifiedItems as $item) {
        $recipeStmt->execute(['product_id' => $item['id']]);
        foreach ($recipeStmt->fetchAll() as $req) {
            $ingId = (int) $req['ingredient_id'];
            $needed = (float) $req['qty_per_serving'] * $item['qty'];
            $neededPerIngredient[$ingId] = ($neededPerIngredient[$ingId] ?? 0) + $needed;
        }
    }

    if (!empty($neededPerIngredient)) {
        $lockStmt = $pdo->prepare("SELECT id, name, unit, stock_qty FROM ingredients WHERE id = :id FOR UPDATE");
        foreach ($neededPerIngredient as $ingId => $neededQty) {
            $lockStmt->execute(['id' => $ingId]);
            $ingredient = $lockStmt->fetch();

            if (!$ingredient || (float) $ingredient['stock_qty'] < $neededQty) {
                $pdo->rollBack();
                $name = $ingredient['name'] ?? 'bahan baku';
                jsonResponse(['success' => false, 'message' => "Maaf, stok {$name} nggak cukup. Coba pesan menu lain ya."], 409);
            }
        }

        $deductStmt = $pdo->prepare("UPDATE ingredients SET stock_qty = stock_qty - :qty WHERE id = :id");
        foreach ($neededPerIngredient as $ingId => $neededQty) {
            $deductStmt->execute(['qty' => $neededQty, 'id' => $ingId]);
        }
    }

    $insertStmt = $pdo->prepare(
        "INSERT INTO orders (id, customer_name, customer_phone, order_type, table_no, payment_method, items, total_amount, status)
         VALUES (:id, :customer_name, :customer_phone, :order_type, :table_no, :payment_method, :items, :total_amount, 'Masuk')"
    );

    $id = null;
    $maxAttempts = 3;
    for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
        $id = generateOrderId();
        try {
            $insertStmt->execute([
                'id' => $id,
                'customer_name' => trim($body['customerName'] ?? '') ?: 'Pelanggan',
                'customer_phone' => trim($body['customerPhone'] ?? '') ?: null,
                'order_type' => $body['orderType'] ?? 'Dine In',
                'table_no' => $body['tableNo'] ?? '-',
                'payment_method' => $body['paymentMethod'] ?? 'QRIS',
                'items' => json_encode($verifiedItems),
                'total_amount' => $totalAmount
            ]);
            break;
        } catch (PDOException $e) {
            if ($e->getCode() !== '23000' || $attempt === $maxAttempts - 1) {
                throw $e;
            }
        }
    }

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    jsonResponse(['success' => false, 'message' => 'Gagal menyimpan pesanan, coba lagi'], 500);
}

$row = $pdo->prepare("SELECT * FROM orders WHERE id = :id");
$row->execute(['id' => $id]);
$order = formatOrderRow($row->fetch());

sendWhatsAppNotification(formatOrderWhatsAppMessage($order));

jsonResponse(['success' => true, 'message' => 'Pembayaran berhasil, pesanan dikirim ke Barista', 'order' => $order], 201);
