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
        'customerPhone' => $row['customer_phone'] ?? null,
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

// Sama seperti formatOrderRow, tapi createdAt/updatedAt pakai tanggal LENGKAP
// (bukan cuma jam) — dipakai halaman Riwayat yang nampilin data lintas hari.
function formatOrderRowFull($row)
{
    return [
        'id' => $row['id'],
        'customerName' => $row['customer_name'],
        'customerPhone' => $row['customer_phone'] ?? null,
        'orderType' => $row['order_type'],
        'tableNo' => $row['table_no'],
        'paymentMethod' => $row['payment_method'],
        'items' => json_decode($row['items'], true),
        'totalAmount' => (int) $row['total_amount'],
        'status' => $row['status'],
        'createdAt' => date('d/m/Y H.i', strtotime($row['created_at'])),
        'updatedAt' => $row['updated_at'] ? date('d/m/Y H.i', strtotime($row['updated_at'])) : null
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
    // bin2hex(random_bytes(4)) = 8 karakter hex acak, praktis nggak mungkin
    // tabrakan meskipun banyak pesanan masuk di detik yang sama.
    return 'ORD-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
}
