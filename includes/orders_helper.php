<?php
// ==========================================
// HELPER PESANAN — dipakai bareng oleh orders_*.php & finance_summary.php
// ==========================================

// Ubah 1 baris hasil query jadi bentuk yang dipakai frontend (camelCase, items di-decode)
function formatOrderRow($row)
{
    return [
        'id' => $row['id'],
        'customerName' => $row['customer_name'],
        'orderType' => $row['order_type'],
        'tableNo' => $row['table_no'],
        'paymentMethod' => $row['payment_method'],
        'items' => json_decode($row['items'], true),
        'totalAmount' => (int) $row['total_amount'],
        'status' => $row['status'],
        'createdAt' => date('H.i.s', strtotime($row['created_at'])),
        'updatedAt' => $row['updated_at'] ? date('H.i.s', strtotime($row['updated_at'])) : null
    ];
}

function computeOrderStats($pdo)
{
    $totalRevenue = (int) $pdo->query(
        "SELECT COALESCE(SUM(total_amount), 0) AS total FROM orders WHERE status = 'Selesai'"
    )->fetch()['total'];

    $pendingOrders = (int) $pdo->query(
        "SELECT COUNT(*) AS total FROM orders WHERE status NOT IN ('Selesai', 'Dibatalkan')"
    )->fetch()['total'];

    $completedOrders = (int) $pdo->query(
        "SELECT COUNT(*) AS total FROM orders WHERE status = 'Selesai'"
    )->fetch()['total'];

    $totalOrders = (int) $pdo->query("SELECT COUNT(*) AS total FROM orders")->fetch()['total'];

    return [
        'totalRevenue' => $totalRevenue,
        'pendingOrders' => $pendingOrders,
        'completedOrders' => $completedOrders,
        'totalOrders' => $totalOrders
    ];
}

function generateOrderId()
{
    return 'ORD-' . time() . random_int(100, 999);
}
