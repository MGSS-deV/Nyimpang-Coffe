<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../includes/auth.php';
require __DIR__ . '/../../includes/orders_helper.php';

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$rawItems = $body['items'] ?? [];

if (empty($rawItems) || !is_array($rawItems)) {
    jsonResponse(['success' => false, 'message' => 'Keranjang kosong!'], 400);
}

// ---------- VALIDASI & AMBIL HARGA ASLI DARI DATABASE ----------
// Client cuma boleh kirim product id + qty. Nama & harga SELALU diambil
// dari tabel products di server, supaya nggak bisa dimanipulasi lewat
// request langsung (mis. ubah harga jadi Rp 1 lewat DevTools/Postman).
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
        jsonResponse(['success' => false, 'message' => "Produk tidak ditemukan atau sudah tidak tersedia"], 400);
    }

    $verifiedItems[] = [
        'id' => (int) $product['id'],
        'name' => $product['name'],
        'price' => (int) $product['price'],
        'qty' => $qty
    ];
    $totalAmount += $product['price'] * $qty;
}

// ---------- SIMPAN PESANAN (dengan retry kalau ID kebetulan bentrok) ----------
$stmt = $pdo->prepare(
    "INSERT INTO orders (id, customer_name, order_type, table_no, payment_method, items, total_amount, status)
     VALUES (:id, :customer_name, :order_type, :table_no, :payment_method, :items, :total_amount, 'Masuk')"
);

$maxAttempts = 3;
$id = null;

for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
    $id = generateOrderId();
    try {
        $stmt->execute([
            'id' => $id,
            'customer_name' => trim($body['customerName'] ?? '') ?: 'Pelanggan',
            'order_type' => $body['orderType'] ?? 'Dine In',
            'table_no' => $body['tableNo'] ?? '-',
            'payment_method' => $body['paymentMethod'] ?? 'QRIS',
            'items' => json_encode($verifiedItems),
            'total_amount' => $totalAmount
        ]);
        break; // sukses, keluar dari loop retry
    } catch (PDOException $e) {
        $isDuplicateId = $e->getCode() === '23000';
        if (!$isDuplicateId || $attempt === $maxAttempts - 1) {
            jsonResponse(['success' => false, 'message' => 'Gagal menyimpan pesanan, coba lagi'], 500);
        }
        // ID kebetulan bentrok (sangat jarang) -> coba lagi dengan ID baru
    }
}

$row = $pdo->prepare("SELECT * FROM orders WHERE id = :id");
$row->execute(['id' => $id]);
$order = formatOrderRow($row->fetch());

jsonResponse(['success' => true, 'message' => 'Pembayaran berhasil, pesanan dikirim ke Barista', 'order' => $order], 201);
