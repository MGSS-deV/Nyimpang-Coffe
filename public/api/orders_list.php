<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../includes/auth.php';
require __DIR__ . '/../../includes/orders_helper.php';

requireAuthApi();

// Dashboard barista cuma perlu pesanan yang MASIH AKTIF (belum Selesai/Dibatalkan).
// Riwayat lengkap (termasuk yang udah selesai) ada di endpoint terpisah:
// orders_history.php — biar query ini tetap ringan meskipun pesanan udah
// numpuk ribuan dari bulan-bulan sebelumnya.
$rows = $pdo->query(
    "SELECT * FROM orders
     WHERE status NOT IN ('Selesai', 'Dibatalkan')
     ORDER BY created_at DESC
     LIMIT 200"
)->fetchAll();

$orders = array_map('formatOrderRow', $rows);

jsonResponse([
    'success' => true,
    'orders' => $orders,
    'stats' => computeOrderStats($pdo)
]);
