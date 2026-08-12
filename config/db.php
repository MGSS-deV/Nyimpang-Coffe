<?php
// ==========================================
// KONEKSI DATABASE (PDO / MySQL)
// ==========================================

// Baca file .env sederhana (tanpa library tambahan)
function loadEnv($path)
{
    if (!file_exists($path)) return;
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
        putenv(trim($key) . '=' . trim($value));
    }
}

loadEnv(__DIR__ . '/../.env');

// Railway (dan beberapa PaaS lain) nyuntik variabel MySQL otomatis lewat
// plugin database, tapi nama variabelnya beda dari .env kustom kita
// (MYSQLHOST bukan DB_HOST). Di sini kita coba DB_* dulu, baru fallback
// ke punya Railway kalau DB_* nggak diisi manual.
$host = getenv('DB_HOST') ?: (getenv('MYSQLHOST') ?: 'localhost');
$user = getenv('DB_USER') ?: (getenv('MYSQLUSER') ?: 'root');
$pass = getenv('DB_PASSWORD') ?: (getenv('MYSQLPASSWORD') ?: '');
$name = getenv('DB_NAME') ?: (getenv('MYSQLDATABASE') ?: 'nyimpang_coffee');
$port = getenv('DB_PORT') ?: (getenv('MYSQLPORT') ?: '3306');

try {
    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Gagal konek ke database. Cek variabel DB_HOST/DB_USER/dll di Railway atau .env kamu.',
        'error' => $e->getMessage()
    ]);
    exit;
}
