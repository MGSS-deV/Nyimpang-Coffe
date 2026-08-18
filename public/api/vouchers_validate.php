<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../includes/auth.php';
require __DIR__ . '/../../includes/voucher_helper.php';

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$code = $body['code'] ?? '';
$subtotal = (int) ($body['subtotal'] ?? 0);

$result = validateVoucher($pdo, $code, $subtotal);

if (!$result['valid']) {
    jsonResponse(['success' => false, 'message' => $result['message']], 400);
}

jsonResponse([
    'success' => true,
    'discountAmount' => $result['discountAmount'],
    'discountType' => $result['voucher']['discount_type'],
    'discountValue' => (int) $result['voucher']['discount_value']
]);
