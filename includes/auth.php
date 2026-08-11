<?php
// ==========================================
// HELPER AUTENTIKASI (session PHP native)
// ==========================================

if (session_status() === PHP_SESSION_NONE) {
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

function jsonResponse($data, $statusCode = 200)
{
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
