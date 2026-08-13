<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../includes/auth.php';

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$username = $body['username'] ?? '';
$password = $body['password'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM staff WHERE username = :username LIMIT 1");
$stmt->execute(['username' => $username]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    jsonResponse(['success' => false, 'message' => 'Username atau password salah'], 401);
}

$_SESSION['user'] = ['username' => $user['username'], 'role' => $user['role']];

jsonResponse(['success' => true, 'message' => 'Login berhasil', 'user' => $_SESSION['user']]);
