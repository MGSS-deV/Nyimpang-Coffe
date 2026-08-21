<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../includes/auth.php';
require __DIR__ . '/../../includes/orders_helper.php';
require __DIR__ . '/../../includes/whatsapp.php';

requireAuthApi();

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$id = $body['id'] ?? '';
$status = $body['status'] ?? '';

$validStatuses = ['Masuk', 'Dibuat', 'Siap Diambil', 'Selesai', 'Dibatalkan'];
if (!in_array($status, $validStatuses, true)) {
    jsonResponse(['success' => false, 'message' => 'Status tidak valid'], 400);
}

$check = $pdo->prepare("SELECT id FROM orders WHERE id = :id");
$check->execute(['id' => $id]);
if (!$check->fetch()) {
    jsonResponse(['success' => false, 'message' => 'Pesanan tidak ditemukan'], 404);
}

$update = $pdo->prepare("UPDATE orders SET status = :status, updated_at = NOW() WHERE id = :id");
$update->execute(['status' => $status, 'id' => $id]);

$row = $pdo->prepare("SELECT * FROM orders WHERE id = :id");
$row->execute(['id' => $id]);
$order = formatOrderRow($row->fetch());

// Notif WA ke pelanggan (kalau dia isi no HP) pas pesanannya siap diambil
if ($status === 'Siap Diambil' && !empty($order['customerPhone'])) {
    sendWhatsAppNotificationTo($order['customerPhone'], formatPickupWhatsAppMessage($order));
}

// Poin loyalitas: 1 poin per Rp 10.000 belanja, dikasih pas pesanan Selesai
// (bukan pas Masuk, biar nggak dikasih poin buat pesanan yang dibatalkan)
if ($status === 'Selesai' && !empty($order['customerPhone'])) {
    $pointsEarned = intdiv($order['totalAmount'], 10000);
    if ($pointsEarned > 0) {
        $pdo->prepare(
            "INSERT INTO customer_points (phone, name, points) VALUES (:phone, :name, :points)
             ON DUPLICATE KEY UPDATE points = points + :points, name = :name"
        )->execute([
            'phone' => $order['customerPhone'],
            'name' => $order['customerName'],
            'points' => $pointsEarned
        ]);
    }
}

jsonResponse([
    'success' => true,
    'message' => 'Status pesanan diperbarui',
    'order' => $order,
    'stats' => computeOrderStats($pdo)
]);
