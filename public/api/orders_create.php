<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../includes/auth.php';
require __DIR__ . '/../../includes/orders_helper.php';
require __DIR__ . '/../../includes/voucher_helper.php';

$body = json_decode(file_get_contents('php://input'), true) ?? [];

$priced = verifyAndPriceItems($pdo, $body['items'] ?? []);
if (!$priced['success']) {
    jsonResponse($priced, 400);
}

$subtotalAmount = $priced['totalAmount'];
$totalAmount = $subtotalAmount;
$discountAmount = 0;
$voucherCode = strtoupper(trim($body['voucherCode'] ?? ''));

if ($voucherCode !== '') {
    $voucherResult = validateVoucher($pdo, $voucherCode, $subtotalAmount);
    if (!$voucherResult['valid']) {
        jsonResponse(['success' => false, 'message' => $voucherResult['message']], 400);
    }
    $discountAmount = $voucherResult['discountAmount'];
    $totalAmount = $subtotalAmount - $discountAmount;
}

// ---------- PEMAKAIAN POIN LOYALITAS (opsional, 1 poin = Rp 100) ----------
// Divalidasi ulang dari DB, bukan dari angka yang dikirim client.
$customerPhone = trim($body['customerPhone'] ?? '');
$requestedPoints = (int) ($body['usePoints'] ?? 0);
$pointsUsed = 0;

if ($requestedPoints > 0 && $customerPhone !== '') {
    $pointsRow = $pdo->prepare("SELECT points FROM customer_points WHERE phone = :phone");
    $pointsRow->execute(['phone' => $customerPhone]);
    $availablePoints = (int) ($pointsRow->fetch()['points'] ?? 0);

    $pointsUsed = min($requestedPoints, $availablePoints);
    $pointsDiscount = min($pointsUsed * 100, $totalAmount);
    // Sesuaikan pointsUsed kalau ke-cap sama totalAmount (biar konsisten Rp-nya)
    $pointsUsed = intdiv($pointsDiscount, 100);

    $discountAmount += $pointsDiscount;
    $totalAmount -= $pointsDiscount;
}

$result = persistOrder($pdo, $priced['items'], $totalAmount, [
    'customerName' => trim($body['customerName'] ?? ''),
    'customerPhone' => $customerPhone,
    'orderType' => $body['orderType'] ?? 'Dine In',
    'tableNo' => $body['tableNo'] ?? '-',
    'paymentMethod' => $body['paymentMethod'] ?? 'QRIS',
    'voucherCode' => $voucherCode,
    'discountAmount' => $discountAmount
]);

if (!$result['success']) {
    $statusCode = str_contains($result['message'], 'stok') ? 409 : 500;
    jsonResponse($result, $statusCode);
}

// Potong poin yang kepake SETELAH order sukses tersimpan
if ($pointsUsed > 0) {
    $pdo->prepare("UPDATE customer_points SET points = points - :used WHERE phone = :phone")
        ->execute(['used' => $pointsUsed, 'phone' => $customerPhone]);
}

jsonResponse(['success' => true, 'message' => 'Pembayaran berhasil, pesanan dikirim ke Barista', 'order' => $result['order']], 201);
