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

function currentRole()
{
    $user = currentUser();
    return $user['role'] ?? null;
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

// FITUR BARU: pembatasan berdasarkan role, dipakai untuk fitur yang cuma
// boleh diakses Admin (misalnya Manajemen Menu). $roles bisa string atau array.
function requireRoleApi($roles)
{
    requireAuthApi();
    if (!in_array(currentRole(), (array) $roles, true)) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Kamu tidak punya akses ke fitur ini (khusus ' . implode('/', (array) $roles) . ')'
        ]);
        exit;
    }
}

function requireRolePage($roles)
{
    requireAuthPage();
    if (!in_array(currentRole(), (array) $roles, true)) {
        http_response_code(403);
        echo '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>Akses Ditolak</title></head>'
            . '<body style="font-family: sans-serif; padding: 40px; text-align: center; color: #2B241D;">'
            . '<h2>Akses ditolak</h2>'
            . '<p>Halaman ini khusus untuk role: ' . htmlspecialchars(implode('/', (array) $roles)) . '.</p>'
            . '<p><a href="/bar.php">← Kembali ke Papan Pesanan</a></p>'
            . '</body></html>';
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
