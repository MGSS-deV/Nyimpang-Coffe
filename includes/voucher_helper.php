<?php
// ==========================================
// HELPER VOUCHER — dipakai di vouchers_validate.php & orders_create.php
// biar logic validasi SAMA PERSIS di kedua tempat (nggak bisa diakalin
// klien kirim discount sendiri).
// ==========================================

function validateVoucher($pdo, $code, $subtotal)
{
    $code = strtoupper(trim($code));
    if ($code === '') {
        return ['valid' => false, 'message' => 'Kode voucher kosong'];
    }

    $stmt = $pdo->prepare("SELECT * FROM vouchers WHERE code = :code");
    $stmt->execute(['code' => $code]);
    $voucher = $stmt->fetch();

    if (!$voucher) {
        return ['valid' => false, 'message' => 'Kode voucher tidak ditemukan'];
    }
    if (!$voucher['is_active']) {
        return ['valid' => false, 'message' => 'Voucher ini sudah tidak aktif'];
    }
    if ($voucher['expires_at'] && strtotime($voucher['expires_at']) < strtotime(date('Y-m-d'))) {
        return ['valid' => false, 'message' => 'Voucher ini sudah kedaluwarsa'];
    }
    if ($voucher['max_uses'] !== null && $voucher['used_count'] >= $voucher['max_uses']) {
        return ['valid' => false, 'message' => 'Voucher ini sudah mencapai batas pemakaian'];
    }
    if ($subtotal < $voucher['min_purchase']) {
        $minFormatted = number_format($voucher['min_purchase'], 0, ',', '.');
        return ['valid' => false, 'message' => "Minimal belanja Rp {$minFormatted} untuk pakai voucher ini"];
    }

    $discountAmount = $voucher['discount_type'] === 'percent'
        ? (int) round($subtotal * $voucher['discount_value'] / 100)
        : (int) $voucher['discount_value'];

    // Diskon nggak boleh lebih besar dari subtotal (total minimal Rp 0)
    $discountAmount = min($discountAmount, $subtotal);

    return [
        'valid' => true,
        'voucher' => $voucher,
        'discountAmount' => $discountAmount
    ];
}
