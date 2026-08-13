<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../includes/auth.php';
require __DIR__ . '/../../includes/orders_helper.php';

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$items = $body['items'] ?? [];

if (empty($items)) {
    jsonResponse(['success' => false, 'message' => 'Keranjang kosong!'], 400);
}

// FIX BUG KEAMANAN: sebelumnya `price` per item diambil mentah-mentah dari
// body request (dikirim oleh browser pelanggan). Itu artinya siapapun bisa
// buka DevTools -> tab Network -> ubah nilai `price` sebelum request
// dikirim, dan checkout dengan harga berapa pun yang dia mau (termasuk 0
// atau minus). Sekarang harga SELALU diambil ulang dari tabel `products` di
// server berdasarkan nama menu — bukan dari input client sama sekali.
$productStmt = $pdo->query("SELECT name, price FROM products WHERE is_active = 1");
$priceMap = [];
foreach ($productStmt->fetchAll() as $p) {
    $priceMap[$p['name']] = (int) $p['price'];
}

$safeItems = [];
$totalAmount = 0;

foreach ($items as $item) {
    $name = $item['name'] ?? '';
    $qty = max(1, (int) ($item['qty'] ?? 0));

    if (!isset($priceMap[$name])) {
        jsonResponse([
            'success' => false,
            'message' => "Menu \"{$name}\" tidak ditemukan atau sedang tidak tersedia. Coba refresh halaman."
        ], 400);
    }

    $price = $priceMap[$name];
    $safeItems[] = ['name' => $name, 'price' => $price, 'qty' => $qty];
    $totalAmount += $price * $qty;
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
    'items' => json_encode($safeItems),
    'total_amount' => $totalAmount
]);

$row = $pdo->prepare("SELECT * FROM orders WHERE id = :id");
$row->execute(['id' => $id]);
$order = formatOrderRow($row->fetch());

jsonResponse(['success' => true, 'message' => 'Pembayaran berhasil, pesanan dikirim ke Barista', 'order' => $order], 201);
