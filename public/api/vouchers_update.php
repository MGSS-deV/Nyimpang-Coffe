<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../includes/auth.php';

requireRoleApi(['Admin']);

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$id = (int) ($body['id'] ?? 0);

if ($id <= 0) {
    jsonResponse(['success' => false, 'message' => 'ID voucher tidak valid'], 400);
}

if (isset($body['isActive'])) {
    $pdo->prepare("UPDATE vouchers SET is_active = :active WHERE id = :id")
        ->execute(['active' => $body['isActive'] ? 1 : 0, 'id' => $id]);
}

jsonResponse(['success' => true, 'message' => 'Voucher diperbarui']);
