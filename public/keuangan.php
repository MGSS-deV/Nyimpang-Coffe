<?php
require __DIR__ . "/../includes/auth.php";
requireAuthPage();
$activePage = 'keuangan';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nyimpang Coffee — Laporan Keuangan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="pb-20">

    <!-- HEADER -->
    <header class="bg-[var(--surface)] border-b border-[var(--border)] sticky top-0 z-30">
        <div class="max-w-5xl mx-auto px-6 py-5 flex flex-col gap-4">
            <div class="flex justify-between items-center flex-wrap gap-3">
                <div>
                    <h1 class="font-display text-xl tracking-tight" style="color: var(--text)">Nyimpang Coffee</h1>
                    <p class="text-[11px] text-[var(--text-muted)] mt-0.5">Laporan Keuangan</p>
                </div>
                <div class="flex items-center gap-2">
                    <span id="staff-badge" class="text-xs text-[var(--text-muted)] px-2"></span>
                    <button onclick="logout()" class="btn-text-muted text-xs px-2 py-2">Keluar</button>
                </div>
            </div>
            <?php require __DIR__ . '/../includes/nav.php'; ?>
        </div>
    </header>

    <main class="max-w-5xl mx-auto px-6 py-8 space-y-8">

        <!-- RINGKASAN -->
        <div class="surface-card px-6 py-5 flex flex-wrap gap-y-4">
            <div class="flex-1 min-w-[160px] px-2">
                <p class="text-[11px] text-[var(--text-muted)] uppercase tracking-wide">Total Pemasukan</p>
                <h3 id="stat-income" class="font-display text-lg mt-1">Rp 0</h3>
            </div>
            <div class="flex-1 min-w-[160px] px-2 border-l border-[var(--border-soft)]">
                <p class="text-[11px] text-[var(--text-muted)] uppercase tracking-wide">Total Pengeluaran</p>
                <h3 id="stat-expense" class="font-display text-lg mt-1">Rp 0</h3>
            </div>
            <div class="flex-1 min-w-[160px] px-2 border-l border-[var(--border-soft)]">
                <p class="text-[11px] text-[var(--text-muted)] uppercase tracking-wide">Laba Bersih</p>
                <h3 id="stat-net" class="font-display text-lg mt-1" style="color: var(--accent-dark)">Rp 0</h3>
            </div>
        </div>

        <!-- FORM CATAT PENGELUARAN -->
        <div class="surface-card p-6">
            <h2 class="font-display text-lg mb-4">Catat Pengeluaran</h2>
            <form id="expense-form" class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-[var(--text-muted)] mb-1">Deskripsi</label>
                    <input type="text" id="expense-description" required placeholder="Cth: Beli susu UHT"
                        class="w-full bg-[var(--bg)] border border-[var(--border)] rounded-[var(--radius-sm)] px-3 py-2 text-xs focus:outline-none focus:border-[var(--accent)]">
                </div>
                <div>
                    <label class="block text-xs font-medium text-[var(--text-muted)] mb-1">Nominal</label>
                    <input type="number" id="expense-amount" required min="1" placeholder="0"
                        class="w-full bg-[var(--bg)] border border-[var(--border)] rounded-[var(--radius-sm)] px-3 py-2 text-xs focus:outline-none focus:border-[var(--accent)]">
                </div>
                <div>
                    <label class="block text-xs font-medium text-[var(--text-muted)] mb-1">Kategori</label>
                    <select id="expense-category"
                        class="w-full bg-[var(--bg)] border border-[var(--border)] rounded-[var(--radius-sm)] px-3 py-2 text-xs focus:outline-none focus:border-[var(--accent)]">
                        <option value="Bahan Baku">Bahan Baku</option>
                        <option value="Operasional">Operasional</option>
                        <option value="Gaji">Gaji</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
                <button type="submit" class="btn-primary sm:col-span-4 py-2.5 text-xs">Simpan Pengeluaran</button>
            </form>
        </div>

        <!-- DUA KOLOM: PEMASUKAN & PENGELUARAN -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <div>
                <p class="text-[11px] tracking-wide text-[var(--text-muted)] uppercase mb-3">Pemasukan (dari pesanan Selesai)</p>
                <div id="income-list" class="space-y-2"></div>
            </div>

            <div>
                <p class="text-[11px] tracking-wide text-[var(--text-muted)] uppercase mb-3">Pengeluaran</p>
                <div id="expense-list" class="space-y-2"></div>
            </div>

        </div>

    </main>

    <script src="keuangan.js"></script>
</body>
</html>
