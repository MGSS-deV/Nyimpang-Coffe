<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../includes/auth.php';
require __DIR__ . '/../../includes/orders_helper.php';

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$items = $body['items'] ?? [];

if (empty($items)) {
    jsonResponse(['success' => false, 'message' => 'Keranjang kosong!'], 400);
}

$totalAmount = 0;
foreach ($items as $item) {
    $totalAmount += ($item['price'] ?? 0) * ($item['qty'] ?? 0);
}

$id = generateOrderId();

$stmt = $pdo->prepare(
    "INSERT INTO orders (id, customer_name, order_type, table_no, payment_method, items, total_amount, status)
     VALUES (:id, :customer_name, :order_type, :table_no, :payment_method, :items, :total_amount, 'Masuk')"
);

$stmt->execute([
    'id' => $id,
    'customer_name' => $body['customerName'] ?? 'Pelanggan',
    'order_type' => $body['orderType'] ?? 'Dine In',
    'table_no' => $body['tableNo'] ?? '-',
    'payment_method' => $body['paymentMethod'] ?? 'QRIS',
    'items' => json_encode($items),
    'total_amount' => $totalAmount
]);

$row = $pdo->prepare("SELECT * FROM orders WHERE id = :id");
$row->execute(['id' => $id]);
$order = formatOrderRow($row->fetch());

jsonResponse(['success' => true, 'message' => 'Pembayaran berhasil, pesanan dikirim ke Barista', 'order' => $order], 201);
