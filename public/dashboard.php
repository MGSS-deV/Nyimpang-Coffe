<?php
// FITUR BARU: Dashboard Ringkasan/Analitik — halaman awal setelah login.
require __DIR__ . "/../includes/auth.php";
requireAuthPage();
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
        <div class="max-w-6xl mx-auto px-6 py-5 flex flex-wrap justify-between items-center gap-3">
            <div>
                <h1 class="font-display text-xl tracking-tight" style="color: var(--text)">Nyimpang Coffee</h1>
                <p class="text-[11px] text-[var(--text-muted)] mt-0.5">Dashboard Ringkasan</p>
            </div>
            <nav class="flex items-center gap-1 text-xs flex-wrap">
                <a href="dashboard.php" class="nav-link nav-link-active">Dashboard</a>
                <a href="bar.php" class="nav-link">Papan Pesanan</a>
                <a href="riwayat.php" class="nav-link">Riwayat</a>
                <a href="menu.php" id="nav-menu-link" class="nav-link hidden">Menu</a>
                <a href="keuangan.php" class="nav-link">Laporan Keuangan</a>
            </nav>
            <div class="flex items-center gap-2">
                <span id="staff-badge" class="text-xs text-[var(--text-muted)] px-2"></span>
                <button onclick="logout()" class="btn-text-muted text-xs px-2 py-2">Keluar</button>
            </div>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-6 py-8 space-y-8">

        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <p class="text-[11px] tracking-wide text-[var(--text-muted)] uppercase mb-1">Analitik</p>
                <h2 class="font-display text-xl">Ringkasan Penjualan</h2>
            </div>
            <div class="flex gap-2">
                <button data-days="7" class="range-btn range-btn-active">7 Hari</button>
                <button data-days="14" class="range-btn">14 Hari</button>
                <button data-days="30" class="range-btn">30 Hari</button>
            </div>
        </div>

        <!-- RINGKASAN ANGKA -->
        <div class="surface-card px-6 py-5 flex flex-wrap gap-y-4">
            <div class="flex-1 min-w-[160px] px-2">
                <p class="text-[11px] text-[var(--text-muted)] uppercase tracking-wide">Total Penjualan</p>
                <h3 id="stat-range-revenue" class="font-display text-lg mt-1">Rp 0</h3>
            </div>
            <div class="flex-1 min-w-[160px] px-2 border-l border-[var(--border-soft)]">
                <p class="text-[11px] text-[var(--text-muted)] uppercase tracking-wide">Pesanan Selesai</p>
                <h3 id="stat-range-orders" class="font-display text-lg mt-1">0</h3>
            </div>
            <div class="flex-1 min-w-[160px] px-2 border-l border-[var(--border-soft)]">
                <p class="text-[11px] text-[var(--text-muted)] uppercase tracking-wide">Rata-rata / Pesanan</p>
                <h3 id="stat-range-avg" class="font-display text-lg mt-1">Rp 0</h3>
            </div>
        </div>

        <!-- GRAFIK PENJUALAN HARIAN + MENU TERLARIS -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="surface-card p-6 lg:col-span-2">
                <h3 class="font-display text-base mb-4">Penjualan Harian</h3>
                <canvas id="sales-chart" height="130"></canvas>
            </div>
            <div class="surface-card p-6">
                <h3 class="font-display text-base mb-4">Menu Terlaris</h3>
                <div id="top-items-list"></div>
            </div>
        </div>

        <!-- JAM PALING RAMAI -->
        <div class="surface-card p-6">
            <h3 class="font-display text-base mb-1">Jam Paling Ramai</h3>
            <p class="text-[11px] mb-4" style="color: var(--text-faint)">Jumlah pesanan Selesai berdasarkan jam pemesanan (WIB)</p>
            <canvas id="hours-chart" height="90"></canvas>
        </div>

    </main>

    <script src="dashboard.js"></script>
</body>
</html>
