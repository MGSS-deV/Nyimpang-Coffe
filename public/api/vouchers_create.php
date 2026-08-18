<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../includes/auth.php';

requireRoleApi(['Admin']);

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$code = strtoupper(trim($body['code'] ?? ''));
$discountType = ($body['discountType'] ?? '') === 'fixed' ? 'fixed' : 'percent';
$discountValue = (int) ($body['discountValue'] ?? 0);
$minPurchase = (int) ($body['minPurchase'] ?? 0);
$maxUses = isset($body['maxUses']) && $body['maxUses'] !== '' ? (int) $body['maxUses'] : null;
$expiresAt = $body['expiresAt'] ?? null;

if ($code === '' || $discountValue <= 0) {
    jsonResponse(['success' => false, 'message' => 'Kode voucher & nilai diskon wajib diisi'], 400);
}
if ($discountType === 'percent' && $discountValue > 100) {
    jsonResponse(['success' => false, 'message' => 'Diskon persen maksimal 100'], 400);
}

try {
    $stmt = $pdo->prepare(
        "INSERT INTO vouchers (code, discount_type, discount_value, min_purchase, max_uses, expires_at)
         VALUES (:code, :type, :value, :min, :max, :expires)"
    );
    $stmt->execute([
        'code' => $code,
        'type' => $discountType,
        'value' => $discountValue,
        'min' => $minPurchase,
        'max' => $maxUses,
        'expires' => $expiresAt ?: null
    ]);
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        jsonResponse(['success' => false, 'message' => 'Kode voucher itu sudah dipakai'], 409);
    }
    jsonResponse(['success' => false, 'message' => 'Gagal membuat voucher'], 500);
}

jsonResponse(['success' => true, 'message' => 'Voucher dibuat'], 201);
