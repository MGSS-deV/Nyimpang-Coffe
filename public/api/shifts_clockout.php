<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../includes/auth.php';
requireAuthApi();
$user = currentUser();

$open = $pdo->prepare("SELECT id FROM shifts WHERE staff_username = :u AND clock_out IS NULL ORDER BY clock_in DESC LIMIT 1");
$open->execute(['u' => $user['username']]);
$shift = $open->fetch();
if (!$shift) {
    jsonResponse(['success' => false, 'message' => 'Kamu belum clock-in'], 400);
}

$pdo->prepare("UPDATE shifts SET clock_out = NOW() WHERE id = :id")->execute(['id' => $shift['id']]);
jsonResponse(['success' => true, 'message' => 'Clock-out berhasil']);
