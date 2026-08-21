<?php
require __DIR__ . "/../includes/auth.php";
requireRolePage(['Admin']);
$activePage = 'pelanggan';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nyimpang Coffee — Riwayat Pelanggan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="pb-20">
    <header class="bg-[var(--surface)] border-b border-[var(--border)] sticky top-0 z-30">
        <div class="max-w-5xl mx-auto px-6 py-5 flex flex-col gap-4">
            <div class="flex justify-between items-center flex-wrap gap-3">
                <div>
                    <h1 class="font-display text-xl tracking-tight" style="color: var(--text)">Nyimpang Coffee</h1>
                    <p class="text-[11px] text-[var(--text-muted)] mt-0.5">Riwayat Pelanggan</p>
                </div>
                <div class="flex items-center gap-2">
                    <span id="staff-badge" class="text-xs text-[var(--text-muted)] px-2"></span>
                    <button onclick="logout()" class="btn-text-muted text-xs px-2 py-2">Keluar</button>
                </div>
            </div>
            <?php require __DIR__ . '/../includes/nav.php'; ?>
        </div>
    </header>

    <main class="max-w-5xl mx-auto px-6 py-8 space-y-6">
        <p class="text-xs" style="color: var(--text-muted)">
            Cuma pelanggan yang ngisi nomor WhatsApp pas checkout yang muncul di sini. Diurutkan dari yang paling sering order.
        </p>
        <div class="surface-card overflow-hidden">
            <table class="w-full text-xs">
                <thead>
                    <tr class="hairline-divider text-left" style="background: var(--bg)">
                        <th class="px-4 py-3 font-semibold" style="color: var(--text-muted)">Nama</th>
                        <th class="px-4 py-3 font-semibold" style="color: var(--text-muted)">No. WhatsApp</th>
                        <th class="px-4 py-3 font-semibold text-right" style="color: var(--text-muted)">Jumlah Order</th>
                        <th class="px-4 py-3 font-semibold text-right" style="color: var(--text-muted)">Total Belanja</th>
                        <th class="px-4 py-3 font-semibold text-right" style="color: var(--text-muted)">Poin</th>
                        <th class="px-4 py-3 font-semibold" style="color: var(--text-muted)">Order Terakhir</th>
                    </tr>
                </thead>
                <tbody id="customer-tbody"></tbody>
            </table>
            <p id="customer-empty" class="hidden text-center text-xs py-10" style="color: var(--text-faint)">Belum ada data pelanggan dengan nomor WhatsApp.</p>
        </div>
    </main>

    <script src="pelanggan.js"></script>
</body>
</html>
