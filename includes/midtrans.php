<?php
// ==========================================
// PEMBAYARAN MIDTRANS (Snap)
// ==========================================
// Daftar di https://midtrans.com (mode Sandbox dulu buat testing gratis),
// ambil Server Key & Client Key dari dashboard, isi ke .env:
//   MIDTRANS_SERVER_KEY=SB-Mid-server-xxxxxxxx
//   MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxxxxxx
//   MIDTRANS_IS_PRODUCTION=false
//
// Kalau belum diisi, opsi bayar Midtrans otomatis nggak muncul di halaman
// pelanggan (checkout tetap bisa lewat QRIS manual / Cash seperti biasa).

function isMidtransConfigured()
{
    return !empty(getenv('MIDTRANS_SERVER_KEY')) && !empty(getenv('MIDTRANS_CLIENT_KEY'));
}

function createMidtransSnapToken($orderId, $grossAmount, $customerName)
{
    $serverKey = getenv('MIDTRANS_SERVER_KEY');
    $isProduction = getenv('MIDTRANS_IS_PRODUCTION') === 'true';
    $url = $isProduction
        ? 'https://app.midtrans.com/snap/v1/transactions'
        : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

    $payload = [
        'transaction_details' => [
            'order_id' => $orderId,
            'gross_amount' => (int) $grossAmount
        ],
        'customer_details' => [
            'first_name' => $customerName ?: 'Pelanggan'
        ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Basic ' . base64_encode($serverKey . ':')
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode($response, true);

    if ($httpCode !== 201 || empty($data['token'])) {
        return ['success' => false, 'message' => $data['error_messages'][0] ?? 'Gagal membuat transaksi Midtrans'];
    }

    return ['success' => true, 'token' => $data['token']];
}

// Verifikasi signature notifikasi webhook dari Midtrans, biar nggak bisa
// dipalsuin orang lain yang nembak endpoint webhook kita langsung.
function verifyMidtransSignature($orderId, $statusCode, $grossAmount, $signatureKey)
{
    $serverKey = getenv('MIDTRANS_SERVER_KEY');
    $expected = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
    return hash_equals($expected, $signatureKey);
}

// Cek status transaksi LANGSUNG ke server Midtrans (bukan percaya callback
// client), terus kalau beneran lunas, baru dibikin order aslinya.
// Dipakai bareng oleh midtrans_confirm.php (dipanggil dari browser) dan
// midtrans_notification.php (webhook dari Midtrans).
function finalizePendingMidtransOrder($pdo, $pendingId)
{
    $check = $pdo->prepare("SELECT * FROM midtrans_pending WHERE id = :id");
    $check->execute(['id' => $pendingId]);
    $pending = $check->fetch();

    if (!$pending) {
        // Udah nggak ada = kemungkinan udah pernah diproses sebelumnya
        // (misal double call dari confirm.php DAN webhook). Aman, bukan error.
        return ['success' => false, 'message' => 'Transaksi sudah diproses atau tidak ditemukan', 'alreadyProcessed' => true];
    }

    $status = getMidtransTransactionStatus($pendingId);
    if (!$status['success']) {
        return ['success' => false, 'message' => $status['message']];
    }

    $paidStatuses = ['capture', 'settlement'];
    if (!in_array($status['transactionStatus'], $paidStatuses, true)) {
        return ['success' => false, 'message' => 'Pembayaran belum diterima', 'transactionStatus' => $status['transactionStatus']];
    }

    require_once __DIR__ . '/orders_helper.php';

    $payload = json_decode($pending['payload'], true);
    $result = persistOrder($pdo, $payload['items'], $payload['totalAmount'], $payload);

    // Hapus record pending-nya (berhasil atau gagal simpan order, sama-sama
    // nggak perlu disimpan lagi biar nggak diproses dobel).
    $pdo->prepare("DELETE FROM midtrans_pending WHERE id = :id")->execute(['id' => $pendingId]);

    return $result;
}

function getMidtransTransactionStatus($orderId)
{
    $serverKey = getenv('MIDTRANS_SERVER_KEY');
    $isProduction = getenv('MIDTRANS_IS_PRODUCTION') === 'true';
    $url = ($isProduction ? 'https://api.midtrans.com' : 'https://api.sandbox.midtrans.com')
        . "/v2/{$orderId}/status";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Authorization: Basic ' . base64_encode($serverKey . ':')
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    if (!$data || !isset($data['transaction_status'])) {
        return ['success' => false, 'message' => 'Gagal cek status transaksi ke Midtrans'];
    }

    return ['success' => true, 'transactionStatus' => $data['transaction_status']];
}
