<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../includes/auth.php';

requireRoleApi(['Admin']);

$incomeRows = $pdo->query(
    "SELECT customer_name, items, total_amount, created_at FROM orders WHERE status = 'Selesai' ORDER BY created_at DESC"
)->fetchAll();
$expenseRows = $pdo->query(
    "SELECT description, amount, category, created_at FROM expenses ORDER BY created_at DESC"
)->fetchAll();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="laporan-keuangan-nyimpang-coffee-' . date('Y-m-d') . '.csv"');

$out = fopen('php://output', 'w');
fputs($out, "\xEF\xBB\xBF");

fputcsv($out, ['LAPORAN KEUANGAN NYIMPANG COFFEE']);
fputcsv($out, ['Diekspor pada', date('d/m/Y H:i')]);
fputcsv($out, []);
fputcsv($out, ['-- PEMASUKAN --']);
fputcsv($out, ['Tanggal', 'Pelanggan', 'Item', 'Jumlah (Rp)']);
$totalIncome = 0;
foreach ($incomeRows as $row) {
    $items = json_decode($row['items'], true) ?: [];
    $itemText = implode(', ', array_map(fn($i) => "{$i['name']} x{$i['qty']}", $items));
    fputcsv($out, [date('d/m/Y H:i', strtotime($row['created_at'])), $row['customer_name'], $itemText, $row['total_amount']]);
    $totalIncome += $row['total_amount'];
}
fputcsv($out, ['', '', 'TOTAL PEMASUKAN', $totalIncome]);
fputcsv($out, []);
fputcsv($out, ['-- PENGELUARAN --']);
fputcsv($out, ['Tanggal', 'Kategori', 'Deskripsi', 'Jumlah (Rp)']);
$totalExpense = 0;
foreach ($expenseRows as $row) {
    fputcsv($out, [date('d/m/Y H:i', strtotime($row['created_at'])), $row['category'], $row['description'], $row['amount']]);
    $totalExpense += $row['amount'];
}
fputcsv($out, ['', '', 'TOTAL PENGELUARAN', $totalExpense]);
fputcsv($out, []);
fputcsv($out, ['', '', 'LABA BERSIH', $totalIncome - $totalExpense]);
fclose($out);
exit;
