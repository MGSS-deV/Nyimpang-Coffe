<?php
// ==========================================
// LAPORAN HARIAN OTOMATIS KE WA — dipicu dari LUAR (bukan Railway sendiri)
// ==========================================
// PHP di shared/VPS biasa nggak bisa "nyalain sendiri" jam 23:00 setiap
// hari, jadi butuh trigger eksternal yang nge-hit URL ini tiap hari.
//
// CARA SETUP (gratis, 2 menit):
// 1. Daftar di https://cron-job.org (gratis, nggak perlu kartu kredit)
// 2. Buat cron job baru, URL:
//    https://domain-kamu.up.railway.app/cron-daily-report.php?secret=XXXX
//    (XXXX = isi sama dengan CRON_SECRET di .env kamu)
// 3. Atur jadwal: setiap hari jam 23:00 WIB (=16:00 UTC)
// 4. Selesai — tiap malam otomatis kekirim laporan ke WA_NOTIFY_NUMBER

require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/whatsapp.php';

$secret = $_GET['secret'] ?? '';
$expectedSecret = getenv('CRON_SECRET');

// Endpoint ini publik (dipanggil dari luar, bukan browser staff), jadi
// WAJIB dilindungi pakai secret key. Tanpa secret yang cocok, ditolak.
if (!$expectedSecret || $secret !== $expectedSecret) {
    jsonResponse(['success' => false, 'message' => 'Secret tidak valid'], 403);
}

$today = date('Y-m-d');

$orderStats = $pdo->prepare(
    "SELECT COUNT(*) AS order_count, COALESCE(SUM(total_amount), 0) AS revenue
     FROM orders WHERE status = 'Selesai' AND DATE(created_at) = :today"
);
$orderStats->execute(['today' => $today]);
$orderRow = $orderStats->fetch();

$expenseStats = $pdo->prepare(
    "SELECT COALESCE(SUM(amount), 0) AS total FROM expenses WHERE DATE(created_at) = :today"
);
$expenseStats->execute(['today' => $today]);
$expenseRow = $expenseStats->fetch();

// Menu terlaris hari ini
$itemRows = $pdo->prepare(
    "SELECT items FROM orders WHERE status = 'Selesai' AND DATE(created_at) = :today"
);
$itemRows->execute(['today' => $today]);

$tally = [];
foreach ($itemRows->fetchAll() as $row) {
    $items = json_decode($row['items'], true) ?: [];
    foreach ($items as $item) {
        $name = $item['name'] ?? '-';
        $tally[$name] = ($tally[$name] ?? 0) + ($item['qty'] ?? 0);
    }
}
arsort($tally);
$topProductName = array_key_first($tally);
$topProduct = $topProductName ? ['name' => $topProductName, 'qty' => $tally[$topProductName]] : null;

$stats = [
    'orderCount' => (int) $orderRow['order_count'],
    'revenue' => (int) $orderRow['revenue'],
    'expense' => (int) $expenseRow['total'],
    'topProduct' => $topProduct
];

sendWhatsAppNotification(formatDailyReportWhatsAppMessage($stats));

jsonResponse(['success' => true, 'message' => 'Laporan harian terkirim', 'stats' => $stats]);
