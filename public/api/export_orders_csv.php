<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../includes/auth.php';

requireAuthApi();

$status = $_GET['status'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';
$validStatuses = ['Masuk', 'Dibuat', 'Siap Diambil', 'Selesai', 'Dibatalkan'];

$where = [];
$params = [];
if ($status !== '' && in_array($status, $validStatuses, true)) {
    $where[] = "status = :status";
    $params['status'] = $status;
}
if ($dateFrom !== '') {
    $where[] = "created_at >= :date_from";
    $params['date_from'] = $dateFrom . ' 00:00:00';
}
if ($dateTo !== '') {
    $where[] = "created_at <= :date_to";
    $params['date_to'] = $dateTo . ' 23:59:59';
}
$whereSql = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = $pdo->prepare("SELECT * FROM orders {$whereSql} ORDER BY created_at DESC");
$stmt->execute($params);
$rows = $stmt->fetchAll();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="riwayat-pesanan-nyimpang-coffee-' . date('Y-m-d') . '.csv"');

$out = fopen('php://output', 'w');
fputs($out, "\xEF\xBB\xBF");
fputcsv($out, ['Waktu', 'ID Pesanan', 'Pelanggan', 'No. HP', 'Tipe', 'Meja', 'Item', 'Total (Rp)', 'Status', 'Metode Bayar']);
foreach ($rows as $row) {
    $items = json_decode($row['items'], true) ?: [];
    $itemText = implode(', ', array_map(fn($i) => "{$i['name']} x{$i['qty']}", $items));
    fputcsv($out, [
        date('d/m/Y H:i', strtotime($row['created_at'])), $row['id'], $row['customer_name'],
        $row['customer_phone'] ?? '-', $row['order_type'], $row['table_no'],
        $itemText, $row['total_amount'], $row['status'], $row['payment_method']
    ]);
}
fclose($out);
exit;
