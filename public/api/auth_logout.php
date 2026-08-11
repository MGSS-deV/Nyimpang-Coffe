<?php
require __DIR__ . '/../../includes/auth.php';

$_SESSION = [];
session_destroy();

jsonResponse(['success' => true, 'message' => 'Logout berhasil']);
