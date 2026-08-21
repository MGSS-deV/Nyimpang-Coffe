<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../includes/auth.php';

$phone = $_GET['phone'] ?? '';
if ($phone === '') {
    jsonResponse(['success' => false, 'message' => 'Nomor HP wajib diisi'], 400);
}

$stmt = $pdo->prepare("SELECT points FROM customer_points WHERE phone = :phone");
$stmt->execute(['phone' => $phone]);
$row = $stmt->fetch();

jsonResponse(['success' => true, 'points' => $row ? (int) $row['points'] : 0]);
