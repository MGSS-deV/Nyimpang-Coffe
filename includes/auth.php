<?php
// ==========================================
// HELPER AUTENTIKASI (session PHP native)
// ==========================================

if (session_status() === PHP_SESSION_NONE) {
    // Cookie session diperketat: nggak bisa dibaca lewat JS (httponly),
    // nggak dikirim ke situs lain (samesite lax), dan wajib HTTPS kalau
    // memang diakses lewat HTTPS (Railway/production).
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

    session_set_cookie_params([
        'lifetime' => 8 * 60 * 60, // 8 jam, cukup untuk satu shift
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => $isHttps
    ]);
    session_start();
}

function currentUser()
{
    return $_SESSION['user'] ?? null;
}

function isLoggedIn()
{
    return currentUser() !== null;
}

// Panggil di awal file API yang wajib login. Kalau belum login,
// langsung balas JSON 401 dan hentikan eksekusi (exit).
function requireAuthApi()
{
    if (!isLoggedIn()) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Sesi habis, silakan login lagi']);
        exit;
    }
}

// Panggil di awal file halaman (bar.php, keuangan.php) yang wajib login.
// Kalau belum login, redirect ke login.html sambil bawa tujuan awal.
function requireAuthPage()
{
    if (!isLoggedIn()) {
        $redirect = urlencode($_SERVER['REQUEST_URI']);
        header("Location: /login.html?redirect={$redirect}");
        exit;
    }
}

// Panggil di awal file API yang cuma boleh diakses role tertentu, mis. Admin.
function requireRoleApi($allowedRoles)
{
    requireAuthApi();
    $user = currentUser();
    if (!in_array($user['role'], $allowedRoles, true)) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Kamu nggak punya akses ke fitur ini']);
        exit;
    }
}

// Panggil di awal file halaman yang cuma boleh diakses role tertentu.
function requireRolePage($allowedRoles)
{
    requireAuthPage();
    $user = currentUser();
    if (!in_array($user['role'], $allowedRoles, true)) {
        header('Location: /dashboard.php?denied=1');
        exit;
    }
}

function jsonResponse($data, $statusCode = 200)
{
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
