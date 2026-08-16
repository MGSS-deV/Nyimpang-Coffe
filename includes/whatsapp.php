<?php
// ==========================================
// NOTIFIKASI WHATSAPP KE BARISTA (via Fonnte)
// ==========================================
// Daftar dulu di https://fonnte.com, scan QR buat connect nomor WA barista,
// copy token dari dashboard Fonnte, isi ke .env:
//   FONNTE_TOKEN=xxxxxxxxxxxx
//   WA_NOTIFY_NUMBER=6281234567890
//
// Kalau FONNTE_TOKEN belum diisi, fungsi ini otomatis nggak ngapa-ngapain.

function sendWhatsAppNotification($message)
{
    $token = getenv('FONNTE_TOKEN');
    $target = getenv('WA_NOTIFY_NUMBER');

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
