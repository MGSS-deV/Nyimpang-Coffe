<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../includes/auth.php';

requireRoleApi(['Admin']);

$rows = $pdo->query(
    "SELECT
        customer_phone,
        MAX(customer_name) AS latest_name,
        COUNT(*) AS order_count,
        SUM(CASE WHEN status = 'Selesai' THEN total_amount ELSE 0 END) AS total_spent,
        MAX(created_at) AS last_order_at
     FROM orders
     WHERE customer_phone IS NOT NULL AND customer_phone != ''
     GROUP BY customer_phone
     ORDER BY order_count DESC, total_spent DESC"
)->fetchAll();

$customers = array_map(function ($row) use ($pdo) {
    $pointsRow = $pdo->prepare("SELECT points FROM customer_points WHERE phone = :phone");
    $pointsRow->execute(['phone' => $row['customer_phone']]);
    $points = (int) ($pointsRow->fetch()['points'] ?? 0);

    return [
        'phone' => $row['customer_phone'],
        'name' => $row['latest_name'],
        'orderCount' => (int) $row['order_count'],
        'totalSpent' => (int) $row['total_spent'],
        'lastOrderAt' => date('d/m/Y H.i', strtotime($row['last_order_at'])),
        'points' => $points
    ];
}, $rows);

jsonResponse(['success' => true, 'customers' => $customers]);
