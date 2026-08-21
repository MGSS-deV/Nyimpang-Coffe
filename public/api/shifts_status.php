<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../includes/auth.php';
requireAuthApi();
$user = currentUser();

$open = $pdo->prepare("SELECT clock_in FROM shifts WHERE staff_username = :u AND clock_out IS NULL ORDER BY clock_in DESC LIMIT 1");
$open->execute(['u' => $user['username']]);
$shift = $open->fetch();

jsonResponse(['success' => true, 'isClockedIn' => (bool) $shift, 'clockInAt' => $shift ? $shift['clock_in'] : null]);
