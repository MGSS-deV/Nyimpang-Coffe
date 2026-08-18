<?php
require __DIR__ . '/../config/db.php';

// Halaman ini sengaja publik (nggak wajib login) supaya pelanggan bisa
// buka struk pesanannya sendiri buat di-print. ID pesanan itu random hex
// yang praktis nggak bisa ditebak, jadi cukup aman dipakai sebagai "kunci".
$id = $_GET['id'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = :id");
$stmt->execute(['id' => $id]);
$order = $stmt->fetch();

if (!$order) {
    http_response_code(404);
    echo "Struk tidak ditemukan.";
    exit;
}

$items = json_decode($order['items'], true) ?: [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk <?= htmlspecialchars($order['id']) ?> — Nyimpang Coffee</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            max-width: 320px;
            margin: 24px auto;
            padding: 20px;
            color: #2B241D;
            font-size: 12px;
        }
        h1 { font-family: 'Fraunces', serif; font-size: 18px; text-align: center; margin: 0 0 2px; }
        .sub { text-align: center; color: #8C8175; margin-bottom: 16px; }
        .divider { border-top: 1px dashed #ccc; margin: 12px 0; }
        .row { display: flex; justify-content: space-between; margin-bottom: 4px; }
        .item-row { display: flex; justify-content: space-between; margin-bottom: 6px; }
        .total-row { display: flex; justify-content: space-between; font-weight: 700; font-size: 14px; margin-top: 8px; }
        .btn-print {
            display: block; width: 100%; margin-top: 20px; padding: 10px;
            background: #A8613A; color: white; border: none; border-radius: 8px;
            font-size: 13px; font-weight: 600; cursor: pointer;
        }
        @media print { .btn-print { display: none; } body { margin: 0; } }
    </style>
</head>
<body>
    <h1>Nyimpang Coffee</h1>
    <p class="sub">Struk Pesanan</p>

    <div class="row"><span>No. Pesanan</span><strong><?= htmlspecialchars($order['id']) ?></strong></div>
    <div class="row"><span>Tanggal</span><span><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></span></div>
    <div class="row"><span>Pelanggan</span><span><?= htmlspecialchars($order['customer_name']) ?></span></div>
    <div class="row"><span>Tipe</span><span><?= htmlspecialchars($order['order_type']) ?> (Meja <?= htmlspecialchars($order['table_no']) ?>)</span></div>

    <div class="divider"></div>

    <?php foreach ($items as $item): ?>
        <div class="item-row">
            <span><?= htmlspecialchars($item['name']) ?> x<?= (int) $item['qty'] ?></span>
            <span>Rp <?= number_format($item['price'] * $item['qty'], 0, ',', '.') ?></span>
        </div>
    <?php endforeach; ?>

    <div class="divider"></div>

    <div class="total-row"><span>TOTAL</span><span>Rp <?= number_format($order['total_amount'], 0, ',', '.') ?></span></div>
    <div class="row" style="margin-top:6px"><span>Metode Bayar</span><span><?= htmlspecialchars($order['payment_method']) ?></span></div>
    <div class="row"><span>Status</span><span><?= htmlspecialchars($order['status']) ?></span></div>

    <div class="divider"></div>
    <p class="sub" style="margin-top:16px">Terima kasih sudah mampir ☕</p>

    <button class="btn-print" onclick="window.print()">🖨️ Cetak Struk</button>
</body>
</html>
