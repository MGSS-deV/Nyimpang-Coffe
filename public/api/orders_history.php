<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../includes/auth.php';
require __DIR__ . '/../../includes/orders_helper.php';

requireAuthApi();

$status = $_GET['status'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

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

// Total baris (buat pagination), pakai filter yang sama
$countStmt = $pdo->prepare("SELECT COUNT(*) AS total FROM orders {$whereSql}");
$countStmt->execute($params);
$totalRows = (int) $countStmt->fetch()['total'];

// Data halaman ini
$stmt = $pdo->prepare(
    "SELECT * FROM orders {$whereSql} ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}"
);
$stmt->execute($params);
$orders = array_map('formatOrderRowFull', $stmt->fetchAll());

jsonResponse([
    'success' => true,
    'orders' => $orders,
    'pagination' => [
        'page' => $page,
        'perPage' => $perPage,
        'totalRows' => $totalRows,
        'totalPages' => max(1, (int) ceil($totalRows / $perPage))
    ]
]);
