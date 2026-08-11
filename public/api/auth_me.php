<?php
require __DIR__ . '/../../includes/auth.php';

if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Belum login'], 401);
}

jsonResponse(['success' => true, 'user' => currentUser()]);
