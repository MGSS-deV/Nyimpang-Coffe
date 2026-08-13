<?php
require __DIR__ . "/../includes/auth.php";
requireAuthPage();
$activePage = 'riwayat';
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
        <div class="max-w-6xl mx-auto px-6 py-5 flex flex-col gap-4">
            <div class="flex justify-between items-center flex-wrap gap-3">
                <div>
                    <h1 class="font-display text-xl tracking-tight" style="color: var(--text)">Nyimpang Coffee</h1>
                    <p class="text-[11px] text-[var(--text-muted)] mt-0.5">Riwayat Pesanan</p>
                </div>
                <div class="flex items-center gap-2">
                    <span id="staff-badge" class="text-xs text-[var(--text-muted)] px-2"></span>
                    <button onclick="logout()" class="btn-text-muted text-xs px-2 py-2">Keluar</button>
                </div>
            </div>
            <?php require __DIR__ . '/../includes/nav.php'; ?>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-6 py-8 space-y-6">

        <!-- FILTER -->
        <div class="surface-card p-5">
            <form id="filter-form" class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
                <div>
                    <label class="block text-xs font-medium text-[var(--text-muted)] mb-1">Status</label>
                    <select id="filter-status" class="w-full bg-[var(--bg)] border border-[var(--border)] rounded-[var(--radius-sm)] px-3 py-2 text-xs focus:outline-none focus:border-[var(--accent)]">
                        <option value="">Semua Status</option>
                        <option value="Masuk">Masuk</option>
                        <option value="Dibuat">Dibuat</option>
                        <option value="Siap Diambil">Siap Diambil</option>
                        <option value="Selesai">Selesai</option>
                        <option value="Dibatalkan">Dibatalkan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-[var(--text-muted)] mb-1">Dari Tanggal</label>
                    <input type="date" id="filter-date-from" class="w-full bg-[var(--bg)] border border-[var(--border)] rounded-[var(--radius-sm)] px-3 py-2 text-xs focus:outline-none focus:border-[var(--accent)]">
                </div>
                <div>
                    <label class="block text-xs font-medium text-[var(--text-muted)] mb-1">Sampai Tanggal</label>
                    <input type="date" id="filter-date-to" class="w-full bg-[var(--bg)] border border-[var(--border)] rounded-[var(--radius-sm)] px-3 py-2 text-xs focus:outline-none focus:border-[var(--accent)]">
                </div>
                <button type="submit" class="btn-primary py-2.5 text-xs">Terapkan Filter</button>
            </form>
        </div>

        <!-- TABEL RIWAYAT -->
        <div class="surface-card overflow-hidden">
            <table class="w-full text-xs">
                <thead>
                    <tr class="hairline-divider text-left" style="background: var(--bg)">
                        <th class="px-4 py-3 font-semibold" style="color: var(--text-muted)">Waktu</th>
                        <th class="px-4 py-3 font-semibold" style="color: var(--text-muted)">ID</th>
                        <th class="px-4 py-3 font-semibold" style="color: var(--text-muted)">Pelanggan</th>
                        <th class="px-4 py-3 font-semibold" style="color: var(--text-muted)">Item</th>
                        <th class="px-4 py-3 font-semibold text-right" style="color: var(--text-muted)">Total</th>
                        <th class="px-4 py-3 font-semibold" style="color: var(--text-muted)">Status</th>
                    </tr>
                </thead>
                <tbody id="history-tbody"></tbody>
            </table>
            <p id="history-empty" class="hidden text-center text-xs py-10" style="color: var(--text-faint)">Nggak ada pesanan yang cocok dengan filter ini.</p>
        </div>

        <!-- PAGINATION -->
        <div id="pagination-controls" class="flex items-center justify-between text-xs" style="color: var(--text-muted)">
            <span id="pagination-info"></span>
            <div class="flex items-center gap-2">
                <button id="btn-prev-page" onclick="changePage(-1)" class="btn-ghost px-3 py-1.5 text-xs">← Sebelumnya</button>
                <button id="btn-next-page" onclick="changePage(1)" class="btn-ghost px-3 py-1.5 text-xs">Berikutnya →</button>
            </div>
        </div>

    </main>

    <script src="riwayat.js"></script>
</body>
</html>
