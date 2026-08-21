<?php
// ==========================================
// NOTIFIKASI WHATSAPP (via Fonnte)
// ==========================================
// Daftar dulu di https://fonnte.com, scan QR buat connect nomor WA pengirim,
// copy token dari dashboard Fonnte, isi ke .env:
//   FONNTE_TOKEN=xxxxxxxxxxxx
//   WA_NOTIFY_NUMBER=6281234567890   (nomor tujuan notif pesanan baru & stok rendah)
//
// Kalau FONNTE_TOKEN belum diisi, semua fungsi di sini otomatis nggak ngapa-ngapain.

// Fungsi dasar: kirim WA ke NOMOR MANAPUN yang di-spesifikasiin (dipakai buat notif ke pelanggan)
function sendWhatsAppNotificationTo($target, $message)
{
    $token = getenv('FONNTE_TOKEN');

    if (!$token || !$target) {
        return;
    }

    $ch = curl_init('https://api.fonnte.com/send');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, [
        'target' => $target,
        'message' => $message
    ]);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: {$token}"]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);

    try {
        curl_exec($ch);
        curl_close($ch);
    } catch (Exception $e) {
        error_log('[WhatsApp] Gagal kirim notifikasi: ' . $e->getMessage());
    }
}

// Shortcut: kirim ke nomor barista/admin (WA_NOTIFY_NUMBER di .env)
function sendWhatsAppNotification($message)
{
    sendWhatsAppNotificationTo(getenv('WA_NOTIFY_NUMBER'), $message);
}

function formatOrderWhatsAppMessage($order)
{
    $itemsText = implode("\n", array_map(
        fn($i) => "- {$i['name']} x{$i['qty']}",
        $order['items']
    ));

    return "🔔 *Pesanan Baru Masuk!*\n\n"
        . "ID: {$order['id']}\n"
        . "Nama: {$order['customerName']}\n"
        . "Tipe: {$order['orderType']} (Meja {$order['tableNo']})\n\n"
        . "{$itemsText}\n\n"
        . "Total: Rp " . number_format($order['totalAmount'], 0, ',', '.') . "\n"
        . "Bayar: {$order['paymentMethod']}";
}

// Notif ke PELANGGAN pas pesanan siap diambil (kalau dia isi no HP pas checkout)
function formatPickupWhatsAppMessage($order)
{
    return "☕ *Pesanan Kamu Siap Diambil!*\n\n"
        . "Halo {$order['customerName']}, pesanan {$order['id']} udah siap ya, silakan diambil di kasir 😊\n\n"
        . "Terima kasih sudah pesan di Nyimpang Coffee!";
}

// Alert ke admin/barista kalau ada bahan baku yang stoknya tembus batas rendah
function formatLowStockWhatsAppMessage($ingredients)
{
    $list = implode("\n", array_map(
        fn($i) => "- {$i['name']}: sisa {$i['stock_qty']} {$i['unit']} (batas: {$i['low_stock_threshold']} {$i['unit']})",
        $ingredients
    ));

    return "⚠️ *Peringatan Stok Rendah!*\n\n{$list}\n\nSegera restock ya sebelum kehabisan.";
}

// Format laporan harian, dipakai cron-daily-report.php
function formatDailyReportWhatsAppMessage($stats)
{
    $topLine = $stats['topProduct']
        ? "{$stats['topProduct']['name']} ({$stats['topProduct']['qty']} porsi)"
        : '-';

    return "📊 *Laporan Harian Nyimpang Coffee*\n"
        . date('d/m/Y') . "\n\n"
        . "🧾 Pesanan Selesai: {$stats['orderCount']}\n"
        . "💰 Omzet: Rp " . number_format($stats['revenue'], 0, ',', '.') . "\n"
        . "💸 Pengeluaran: Rp " . number_format($stats['expense'], 0, ',', '.') . "\n"
        . "📈 Laba Bersih: Rp " . number_format($stats['revenue'] - $stats['expense'], 0, ',', '.') . "\n"
        . "🏆 Menu Terlaris: {$topLine}\n\n"
        . "Semangat terus ya! ☕";
}
