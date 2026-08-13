<?php
// FITUR BARU: Riwayat Pesanan + Filter tanggal/status/pencarian.
require __DIR__ . "/../includes/auth.php";
requireAuthPage();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nyimpang Coffee — Riwayat Pesanan</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
                <p class="text-[11px] text-[var(--text-muted)] mt-0.5">Riwayat Pesanan</p>
            </div>
            <nav class="flex items-center gap-1 text-xs flex-wrap">
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="bar.php" class="nav-link">Papan Pesanan</a>
                <a href="riwayat.php" class="nav-link nav-link-active">Riwayat</a>
                <a href="menu.php" id="nav-menu-link" class="nav-link hidden">Menu</a>
                <a href="keuangan.php" class="nav-link">Laporan Keuangan</a>
            </nav>
            <div class="flex items-center gap-2">
                <span id="staff-badge" class="text-xs text-[var(--text-muted)] px-2"></span>
                <button onclick="logout()" class="btn-text-muted text-xs px-2 py-2">Keluar</button>
            </div>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-6 py-8 space-y-6">

        <div>
            <p class="text-[11px] tracking-wide text-[var(--text-muted)] uppercase mb-1">Histori</p>
            <h2 class="font-display text-xl">Semua Pesanan</h2>
        </div>

        <!-- FILTER -->
        <form id="filter-form" class="surface-card p-5 grid grid-cols-1 sm:grid-cols-5 gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-[var(--text-muted)] mb-1">Dari Tanggal</label>
                <input type="date" id="filter-date-from" class="w-full bg-[var(--bg)] border border-[var(--border)] rounded-[var(--radius-sm)] px-3 py-2 text-xs focus:outline-none focus:border-[var(--accent)]">
            </div>
            <div>
                <label class="block text-xs font-medium text-[var(--text-muted)] mb-1">Sampai Tanggal</label>
                <input type="date" id="filter-date-to" class="w-full bg-[var(--bg)] border border-[var(--border)] rounded-[var(--radius-sm)] px-3 py-2 text-xs focus:outline-none focus:border-[var(--accent)]">
            </div>
            <div>
                <label class="block text-xs font-medium text-[var(--text-muted)] mb-1">Status</label>
                <select id="filter-status" class="w-full bg-[var(--bg)] border border-[var(--border)] rounded-[var(--radius-sm)] px-3 py-2 text-xs focus:outline-none focus:border-[var(--accent)]">
                    <option value="">Semua</option>
                    <option value="Masuk">Masuk</option>
                    <option value="Dibuat">Dibuat</option>
                    <option value="Siap Diambil">Siap Diambil</option>
                    <option value="Selesai">Selesai</option>
                    <option value="Dibatalkan">Dibatalkan</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-[var(--text-muted)] mb-1">Cari (nama/ID)</label>
                <input type="text" id="filter-search" placeholder="Cth: Budi / ORD-..." class="w-full bg-[var(--bg)] border border-[var(--border)] rounded-[var(--radius-sm)] px-3 py-2 text-xs focus:outline-none focus:border-[var(--accent)]">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary flex-1 py-2.5 text-xs">Terapkan</button>
                <button type="button" id="btn-reset" class="btn-ghost flex-1 py-2.5 text-xs">Reset</button>
            </div>
        </form>

        <!-- TABEL RIWAYAT -->
        <div class="surface-card overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="hairline-divider text-[11px] uppercase tracking-wide" style="color: var(--text-faint)">
                        <th class="py-3 px-3">ID</th>
                        <th class="py-3 px-3">Pelanggan</th>
                        <th class="py-3 px-3">Tipe / Meja</th>
                        <th class="py-3 px-3">Waktu</th>
                        <th class="py-3 px-3">Total</th>
                        <th class="py-3 px-3">Status</th>
                    </tr>
                </thead>
                <tbody id="history-table-body"></tbody>
            </table>
        </div>

        <!-- PAGINATION -->
        <div class="flex justify-between items-center flex-wrap gap-3">
            <p id="pagination-info" class="text-xs" style="color: var(--text-muted)">Memuat...</p>
            <div class="flex gap-2">
                <button id="btn-prev" class="btn-ghost px-4 py-2 text-xs">← Sebelumnya</button>
                <button id="btn-next" class="btn-ghost px-4 py-2 text-xs">Berikutnya →</button>
            </div>
        </div>

    </main>

    <script src="riwayat.js"></script>
</body>
</html>
