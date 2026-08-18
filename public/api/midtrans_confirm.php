<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../includes/auth.php';
require __DIR__ . '/../../includes/midtrans.php';

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$pendingId = $body['pendingId'] ?? '';

if ($pendingId === '') {
    jsonResponse(['success' => false, 'message' => 'pendingId wajib diisi'], 400);
}

$result = finalizePendingMidtransOrder($pdo, $pendingId);

if (!$result['success']) {
    jsonResponse($result, 200); // 200 tetep, biar frontend gampang baca "belum lunas" vs error jaringan
    exit;
}

jsonResponse(['success' => true, 'order' => $result['order']]);
