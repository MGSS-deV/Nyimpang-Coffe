<?php
// FITUR BARU: Dashboard Ringkasan/Analitik — penjualan harian, menu terlaris,
// dan jam paling ramai. Cuma menghitung pesanan berstatus 'Selesai' supaya
// angka penjualan sesuai duit yang benar-benar masuk.
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../includes/auth.php';

requireAuthApi();

$days = (int) ($_GET['days'] ?? 7);
if (!in_array($days, [7, 14, 30], true)) {
    $days = 7;
}

$startDate = date('Y-m-d', strtotime("-" . ($days - 1) . " days"));

// ---------- PENJUALAN HARIAN ----------
$stmt = $pdo->prepare(
    "SELECT DATE(created_at) AS day, COALESCE(SUM(total_amount), 0) AS total, COUNT(*) AS orders_count
     FROM orders
     WHERE status = 'Selesai' AND created_at >= :start
     GROUP BY DATE(created_at)
     ORDER BY day ASC"
);
$stmt->execute(['start' => $startDate . ' 00:00:00']);
$rows = $stmt->fetchAll();

$byDate = [];
foreach ($rows as $r) {
    $byDate[$r['day']] = ['total' => (int) $r['total'], 'count' => (int) $r['orders_count']];
}

// Isi tanggal yang kosong (nggak ada penjualan) dengan 0, biar grafik nggak bolong
$dailySales = [];
for ($i = 0; $i < $days; $i++) {
    $date = date('Y-m-d', strtotime($startDate . " +{$i} days"));
    $dailySales[] = [
        'date' => $date,
        'label' => date('d/m', strtotime($date)),
        'total' => $byDate[$date]['total'] ?? 0,
        'orders' => $byDate[$date]['count'] ?? 0
    ];
}

// ---------- MENU TERLARIS & JAM PALING RAMAI ----------
// items disimpan sebagai JSON per baris, jadi diagregasi di PHP (portable,
// nggak bergantung fitur JSON_TABLE yang cuma ada di MySQL 8+).
$detailStmt = $pdo->prepare(
    "SELECT items, created_at FROM orders WHERE status = 'Selesai' AND created_at >= :start"
);
$detailStmt->execute(['start' => $startDate . ' 00:00:00']);
$detailRows = $detailStmt->fetchAll();

$itemCounts = [];
$hourCounts = array_fill(0, 24, 0);

foreach ($detailRows as $row) {
    $hour = (int) date('G', strtotime($row['created_at']));
    $hourCounts[$hour]++;

    $items = json_decode($row['items'], true) ?: [];
    foreach ($items as $item) {
        $name = $item['name'] ?? 'Lainnya';
        $qty = (int) ($item['qty'] ?? 0);
        if (!isset($itemCounts[$name])) {
            $itemCounts[$name] = 0;
        }
        $itemCounts[$name] += $qty;
    }
}

arsort($itemCounts);
$topItems = [];
$i = 0;
foreach ($itemCounts as $name => $qty) {
    if ($i >= 5) break;
    $topItems[] = ['name' => $name, 'qty' => $qty];
    $i++;
}

$busiestHours = [];
foreach ($hourCounts as $hour => $count) {
    $busiestHours[] = ['hour' => sprintf('%02d.00', $hour), 'orders' => $count];
}

$totalRevenueRange = array_sum(array_column($dailySales, 'total'));
$totalOrdersRange = array_sum(array_column($dailySales, 'orders'));
$avgPerOrder = $totalOrdersRange > 0 ? (int) round($totalRevenueRange / $totalOrdersRange) : 0;

jsonResponse([
    'success' => true,
    'rangeDays' => $days,
    'dailySales' => $dailySales,
    'topItems' => $topItems,
    'busiestHours' => $busiestHours,
    'summary' => [
        'totalRevenue' => $totalRevenueRange,
        'totalOrders' => $totalOrdersRange,
        'avgPerOrder' => $avgPerOrder
    ]
]);
