<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../includes/auth.php';
require __DIR__ . '/../../includes/orders_helper.php';

requireAuthApi();

// FIX BUG (performa): sebelumnya endpoint ini narik SEMUA baris di tabel
// orders, dari awal restoran buka sampai sekarang, setiap kali di-poll
// (tiap 2.5 detik oleh bar.js). Makin lama restoran jalan, makin berat query
// ini padahal Papan Barista cuma butuh pesanan yang masih aktif hari ini.
// Sekarang dibatasi: pesanan yang STATUSNYA masih aktif (kapan pun
// dibuatnya) ATAU dibuat hari ini. Riwayat pesanan lama dipindah ke
// endpoint terpisah (orders_history.php) yang mendukung filter & pagination.
$rows = $pdo->query(
    "SELECT * FROM orders
     WHERE status NOT IN ('Selesai', 'Dibatalkan')
        OR created_at >= CURDATE()
     ORDER BY created_at DESC"
)->fetchAll();

$orders = array_map('formatOrderRow', $rows);

jsonResponse([
    'success' => true,
    'orders' => $orders,
    'stats' => computeOrderStats($pdo)
]);
