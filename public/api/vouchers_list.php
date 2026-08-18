<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../includes/auth.php';

requireRoleApi(['Admin']);

$vouchers = $pdo->query("SELECT * FROM vouchers ORDER BY created_at DESC")->fetchAll();

foreach ($vouchers as &$v) {
    $v['discountValue'] = (int) $v['discount_value'];
    $v['minPurchase'] = (int) $v['min_purchase'];
    $v['maxUses'] = $v['max_uses'] !== null ? (int) $v['max_uses'] : null;
    $v['usedCount'] = (int) $v['used_count'];
    $v['isActive'] = (bool) $v['is_active'];
    $v['expiresAt'] = $v['expires_at'];
    unset($v['discount_value'], $v['min_purchase'], $v['max_uses'], $v['used_count'], $v['is_active'], $v['expires_at']);
}

jsonResponse(['success' => true, 'vouchers' => $vouchers]);
