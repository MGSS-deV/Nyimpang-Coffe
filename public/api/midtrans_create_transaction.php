<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../includes/auth.php';
require __DIR__ . '/../../includes/orders_helper.php';
require __DIR__ . '/../../includes/voucher_helper.php';
require __DIR__ . '/../../includes/midtrans.php';

if (!isMidtransConfigured()) {
    jsonResponse(['success' => false, 'message' => 'Midtrans belum dikonfigurasi di server'], 400);
}

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

if ($totalAmount <= 0) {
    jsonResponse(['success' => false, 'message' => 'Total pembayaran tidak valid'], 400);
}

$pendingId = 'MTP-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(5)));

$payload = [
    'customerName' => trim($body['customerName'] ?? ''),
    'customerPhone' => trim($body['customerPhone'] ?? ''),
    'orderType' => $body['orderType'] ?? 'Dine In',
    'tableNo' => $body['tableNo'] ?? '-',
    'paymentMethod' => 'Midtrans',
    'items' => $priced['items'],
    'totalAmount' => $totalAmount,
    'voucherCode' => $voucherCode,
    'discountAmount' => $discountAmount
];

$pdo->prepare("INSERT INTO midtrans_pending (id, payload) VALUES (:id, :payload)")
    ->execute(['id' => $pendingId, 'payload' => json_encode($payload)]);

$snap = createMidtransSnapToken($pendingId, $totalAmount, $payload['customerName']);

if (!$snap['success']) {
    $pdo->prepare("DELETE FROM midtrans_pending WHERE id = :id")->execute(['id' => $pendingId]);
    jsonResponse(['success' => false, 'message' => $snap['message']], 500);
}

jsonResponse(['success' => true, 'snapToken' => $snap['token'], 'pendingId' => $pendingId]);
