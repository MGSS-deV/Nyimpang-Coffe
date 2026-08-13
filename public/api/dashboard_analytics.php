<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../includes/auth.php';

requireAuthApi();

// ---------- PENJUALAN 7 HARI TERAKHIR ----------
$dailyRows = $pdo->query(
    "SELECT DATE(created_at) AS day, COUNT(*) AS order_count, SUM(total_amount) AS revenue
     FROM orders
     WHERE status = 'Selesai' AND created_at >= (CURDATE() - INTERVAL 6 DAY)
     GROUP BY DATE(created_at)"
)->fetchAll();

$dailyByDate = [];
foreach ($dailyRows as $row) {
    $dailyByDate[$row['day']] = [
        'orderCount' => (int) $row['order_count'],
        'revenue' => (int) $row['revenue']
    ];
}

$dailySales = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-{$i} days"));
    $entry = $dailyByDate[$date] ?? ['orderCount' => 0, 'revenue' => 0];
    $dailySales[] = [
        'date' => $date,
        'label' => date('d M', strtotime($date)),
        'orderCount' => $entry['orderCount'],
        'revenue' => $entry['revenue']
    ];
}

// ---------- PENJUALAN 8 MINGGU TERAKHIR ----------
$weeklyRows = $pdo->query(
    "SELECT YEARWEEK(created_at, 3) AS ykey, MIN(DATE(created_at)) AS week_start,
            SUM(total_amount) AS revenue, COUNT(*) AS order_count
     FROM orders
     WHERE status = 'Selesai' AND created_at >= (CURDATE() - INTERVAL 8 WEEK)
     GROUP BY YEARWEEK(created_at, 3)
     ORDER BY ykey ASC"
)->fetchAll();

$weeklySales = array_map(function ($row) {
    return [
        'weekStart' => $row['week_start'],
        'label' => 'Mgg ' . date('d M', strtotime($row['week_start'])),
        'orderCount' => (int) $row['order_count'],
        'revenue' => (int) $row['revenue']
    ];
}, $weeklyRows);

// ---------- MENU TERLARIS (all-time, dari pesanan Selesai) ----------
// items disimpan sebagai JSON per baris, jadi digabung & dihitung di PHP.
$itemRows = $pdo->query("SELECT items FROM orders WHERE status = 'Selesai'")->fetchAll();

$productTally = []; // name => ['qty' => x, 'revenue' => y]
foreach ($itemRows as $row) {
    $items = json_decode($row['items'], true) ?: [];
    foreach ($items as $item) {
        $name = $item['name'] ?? 'Tidak diketahui';
        if (!isset($productTally[$name])) {
            $productTally[$name] = ['qty' => 0, 'revenue' => 0];
        }
        $productTally[$name]['qty'] += (int) ($item['qty'] ?? 0);
        $productTally[$name]['revenue'] += (int) ($item['qty'] ?? 0) * (int) ($item['price'] ?? 0);
    }
}

uasort($productTally, fn($a, $b) => $b['qty'] <=> $a['qty']);

$topProducts = [];
$rank = 0;
foreach ($productTally as $name => $data) {
    if ($rank >= 5) break;
    $topProducts[] = ['name' => $name, 'qty' => $data['qty'], 'revenue' => $data['revenue']];
    $rank++;
}

// ---------- JAM PALING RAMAI (30 hari terakhir, semua status -> pola kedatangan pesanan) ----------
$hourRows = $pdo->query(
    "SELECT HOUR(created_at) AS hr, COUNT(*) AS total
     FROM orders
     WHERE created_at >= (NOW() - INTERVAL 30 DAY)
     GROUP BY HOUR(created_at)"
)->fetchAll();

$hourByValue = [];
foreach ($hourRows as $row) {
    $hourByValue[(int) $row['hr']] = (int) $row['total'];
}

$busiestHours = [];
for ($h = 0; $h < 24; $h++) {
    $busiestHours[] = [
        'hour' => $h,
        'label' => sprintf('%02d:00', $h),
        'total' => $hourByValue[$h] ?? 0
    ];
}

jsonResponse([
    'success' => true,
    'dailySales' => $dailySales,
    'weeklySales' => $weeklySales,
    'topProducts' => $topProducts,
    'busiestHours' => $busiestHours
]);
