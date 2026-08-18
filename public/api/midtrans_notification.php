<?php
// Webhook yang dipanggil MIDTRANS SENDIRI (bukan browser pelanggan), jadi
// nggak pakai session/login. Set URL ini di dashboard Midtrans:
// Settings > Configuration > Payment Notification URL.
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../includes/auth.php';
require __DIR__ . '/../../includes/midtrans.php';

$body = json_decode(file_get_contents('php://input'), true) ?? [];

$orderId = $body['order_id'] ?? '';
$statusCode = $body['status_code'] ?? '';
$grossAmount = $body['gross_amount'] ?? '';
$signatureKey = $body['signature_key'] ?? '';

if (!verifyMidtransSignature($orderId, $statusCode, $grossAmount, $signatureKey)) {
    jsonResponse(['success' => false, 'message' => 'Signature tidak valid'], 403);
}

finalizePendingMidtransOrder($pdo, $orderId);

jsonResponse(['success' => true]);
