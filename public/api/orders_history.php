<?php
// FITUR BARU: Riwayat Pesanan + Filter. Berbeda dari orders_list.php (yang
// sekarang dibatasi cuma pesanan aktif/hari ini demi performa), endpoint
// ini menarik SELURUH histori tapi dengan filter tanggal/status/pencarian
// dan pagination, jadi tetap ringan walau data sudah menumpuk.
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../includes/auth.php';
require __DIR__ . '/../../includes/orders_helper.php';

requireAuthApi();

$status = $_GET['status'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';
$search = trim($_GET['q'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 20;

$where = [];
$params = [];

$validStatuses = ['Masuk', 'Dibuat', 'Siap Diambil', 'Selesai', 'Dibatalkan'];
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
if ($search !== '') {
    $where[] = "(customer_name LIKE :search OR id LIKE :search)";
    $params['search'] = "%{$search}%";
}

$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$countStmt = $pdo->prepare("SELECT COUNT(*) AS total FROM orders {$whereSql}");
$countStmt->execute($params);
$total = (int) $countStmt->fetch()['total'];
$totalPages = max(1, (int) ceil($total / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

// LIMIT/OFFSET nggak bisa di-bind sebagai parameter string di sebagian
// driver, jadi di-cast ke int dulu lalu ditempel langsung ke query (aman,
// karena sudah dipaksa jadi integer, bukan input mentah).
$stmt = $pdo->prepare("SELECT * FROM orders {$whereSql} ORDER BY created_at DESC LIMIT " . (int) $perPage . " OFFSET " . (int) $offset);
$stmt->execute($params);
$rows = $stmt->fetchAll();

jsonResponse([
    'success' => true,
    'orders' => array_map('formatOrderRow', $rows),
    'pagination' => [
        'page' => $page,
        'perPage' => $perPage,
        'total' => $total,
        'totalPages' => $totalPages
    ]
]);
