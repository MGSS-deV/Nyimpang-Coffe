<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../includes/auth.php';
requireAuthApi();
$user = currentUser();

$open = $pdo->prepare("SELECT id FROM shifts WHERE staff_username = :u AND clock_out IS NULL");
$open->execute(['u' => $user['username']]);
if ($open->fetch()) {
    jsonResponse(['success' => false, 'message' => 'Kamu masih dalam shift aktif, clock-out dulu'], 400);
}

$pdo->prepare("INSERT INTO shifts (staff_username, clock_in) VALUES (:u, NOW())")->execute(['u' => $user['username']]);
jsonResponse(['success' => true, 'message' => 'Clock-in berhasil']);
