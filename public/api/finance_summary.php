<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../includes/auth.php';

requireAuthApi();

$incomeRows = $pdo->query(
    "SELECT id, customer_name, items, total_amount, created_at
     FROM orders WHERE status = 'Selesai' ORDER BY created_at DESC"
)->fetchAll();

$incomeEntries = array_map(function ($row) {
    $items = json_decode($row['items'], true);
    $itemNames = implode(', ', array_map(fn($i) => $i['name'], $items));
    return [
        'id' => $row['id'],
        'description' => "Pesanan {$row['customer_name']} — {$itemNames}",
        'amount' => (int) $row['total_amount'],
        'createdAt' => date('d/m/Y H.i', strtotime($row['created_at']))
    ];
}, $incomeRows);

$expenseRows = $pdo->query("SELECT * FROM expenses ORDER BY created_at DESC")->fetchAll();
$expenseEntries = array_map(function ($row) {
    return [
        'id' => $row['id'],
        'description' => $row['description'],
        'amount' => (int) $row['amount'],
        'category' => $row['category'],
        'createdAt' => date('d/m/Y H.i', strtotime($row['created_at']))
    ];
}, $expenseRows);

$totalIncome = array_sum(array_column($incomeEntries, 'amount'));
$totalExpense = array_sum(array_column($expenseEntries, 'amount'));

jsonResponse([
    'success' => true,
    'totalIncome' => $totalIncome,
    'totalExpense' => $totalExpense,
    'netProfit' => $totalIncome - $totalExpense,
    'incomeEntries' => $incomeEntries,
    'expenseEntries' => $expenseEntries
]);
