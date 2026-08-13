<?php
require __DIR__ . "/../includes/auth.php";
requireAuthPage();
$activePage = 'dashboard';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nyimpang Coffee — Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="pb-20">

    <!-- HEADER -->
    <header class="bg-[var(--surface)] border-b border-[var(--border)] sticky top-0 z-30">
        <div class="max-w-6xl mx-auto px-6 py-5 flex flex-col gap-4">
            <div class="flex justify-between items-center flex-wrap gap-3">
                <div>
                    <h1 class="font-display text-xl tracking-tight" style="color: var(--text)">Nyimpang Coffee</h1>
                    <p class="text-[11px] text-[var(--text-muted)] mt-0.5">Dashboard Ringkasan</p>
                </div>
                <div class="flex items-center gap-2">
                    <span id="staff-badge" class="text-xs text-[var(--text-muted)] px-2"></span>
                    <button onclick="logout()" class="btn-text-muted text-xs px-2 py-2">Keluar</button>
                </div>
            </div>
            <?php require __DIR__ . '/../includes/nav.php'; ?>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-6 py-8 space-y-8">

        <!-- TOGGLE HARIAN / MINGGUAN -->
        <div>
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-[11px] tracking-wide text-[var(--text-muted)] uppercase mb-1">Penjualan</p>
                    <h2 class="font-display text-xl">Grafik Pemasukan</h2>
                </div>
                <div class="flex items-center gap-1">
                    <button id="btn-daily" onclick="switchSalesView('daily')" class="btn-primary px-3 py-2 text-xs">Harian</button>
                    <button id="btn-weekly" onclick="switchSalesView('weekly')" class="btn-ghost px-3 py-2 text-xs">Mingguan</button>
                </div>
            </div>
            <div class="surface-card p-6">
                <canvas id="sales-chart" height="90"></canvas>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- MENU TERLARIS -->
            <div>
                <p class="text-[11px] tracking-wide text-[var(--text-muted)] uppercase mb-1">Menu Terlaris</p>
                <h2 class="font-display text-xl mb-4">Top 5 Produk</h2>
                <div class="surface-card p-6">
                    <canvas id="top-products-chart" height="200"></canvas>
                    <p id="top-products-empty" class="hidden text-center text-xs py-8" style="color: var(--text-faint)">Belum ada pesanan Selesai untuk dianalisis.</p>
                </div>
            </div>

            <!-- JAM PALING RAMAI -->
            <div>
                <p class="text-[11px] tracking-wide text-[var(--text-muted)] uppercase mb-1">Pola Kedatangan (30 hari)</p>
                <h2 class="font-display text-xl mb-4">Jam Paling Ramai</h2>
                <div class="surface-card p-6">
                    <canvas id="busy-hours-chart" height="200"></canvas>
                </div>
            </div>

        </div>

    </main>

    <script src="dashboard.js"></script>
</body>
</html>
