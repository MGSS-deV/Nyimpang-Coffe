<?php
// FITUR BARU: Manajemen Menu (CRUD produk) — khusus role Admin, supaya
// barista biasa nggak bisa asal ubah harga/nonaktifkan menu dari browser.
require __DIR__ . "/../includes/auth.php";
requireRolePage('Admin');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nyimpang Coffee — Manajemen Menu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="pb-20">

    <!-- HEADER -->
    <header class="bg-[var(--surface)] border-b border-[var(--border)] sticky top-0 z-30">
        <div class="max-w-5xl mx-auto px-6 py-5 flex flex-wrap justify-between items-center gap-3">
            <div>
                <h1 class="font-display text-xl tracking-tight" style="color: var(--text)">Nyimpang Coffee</h1>
                <p class="text-[11px] text-[var(--text-muted)] mt-0.5">Manajemen Menu</p>
            </div>
            <nav class="flex items-center gap-1 text-xs flex-wrap">
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="bar.php" class="nav-link">Papan Pesanan</a>
                <a href="riwayat.php" class="nav-link">Riwayat</a>
                <a href="menu.php" class="nav-link nav-link-active">Menu</a>
                <a href="keuangan.php" class="nav-link">Laporan Keuangan</a>
            </nav>
            <div class="flex items-center gap-2">
                <span id="staff-badge" class="text-xs text-[var(--text-muted)] px-2"></span>
                <button onclick="logout()" class="btn-text-muted text-xs px-2 py-2">Keluar</button>
            </div>
        </div>
    </header>

    <main class="max-w-5xl mx-auto px-6 py-8 space-y-6">

        <div class="flex justify-between items-center flex-wrap gap-3">
            <div>
                <p class="text-[11px] tracking-wide text-[var(--text-muted)] uppercase mb-1">Menu</p>
                <h2 class="font-display text-xl">Daftar Menu</h2>
            </div>
            <button onclick="openAddModal()" class="btn-primary px-4 py-2.5 text-xs">+ Tambah Menu</button>
        </div>

        <p class="text-xs" style="color: var(--text-faint)">
            Menu yang dinonaktifkan otomatis hilang dari halaman pemesanan pelanggan (index.html),
            tapi datanya tetap ada di sini kalau mau diaktifkan lagi.
        </p>

        <div class="surface-card overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="hairline-divider text-[11px] uppercase tracking-wide" style="color: var(--text-faint)">
                        <th class="py-3 px-3">Menu</th>
                        <th class="py-3 px-3">Kategori</th>
                        <th class="py-3 px-3">Harga</th>
                        <th class="py-3 px-3">Status</th>
                        <th class="py-3 px-3">Aksi</th>
                    </tr>
                </thead>
                <tbody id="menu-table-body"></tbody>
            </table>
        </div>

    </main>

    <!-- MODAL TAMBAH/EDIT MENU -->
    <div id="product-modal" class="fixed inset-0 bg-black/40 z-50 hidden flex items-center justify-center p-4">
        <div class="surface-card w-full max-w-sm p-6">
            <h3 id="modal-title" class="font-display text-lg mb-4">Tambah Menu</h3>
            <form id="product-form" class="space-y-3">
                <div>
                    <label class="block text-xs font-medium text-[var(--text-muted)] mb-1">Nama Menu</label>
                    <input type="text" id="product-name" required placeholder="Cth: Kopi Susu Gula Aren"
                        class="w-full bg-[var(--bg)] border border-[var(--border)] rounded-[var(--radius-sm)] px-3 py-2 text-xs focus:outline-none focus:border-[var(--accent)]">
                </div>
                <div>
                    <label class="block text-xs font-medium text-[var(--text-muted)] mb-1">Deskripsi</label>
                    <input type="text" id="product-description" placeholder="Cth: Espresso, susu segar, gula aren"
                        class="w-full bg-[var(--bg)] border border-[var(--border)] rounded-[var(--radius-sm)] px-3 py-2 text-xs focus:outline-none focus:border-[var(--accent)]">
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-medium text-[var(--text-muted)] mb-1">Harga</label>
                        <input type="number" id="product-price" required min="1" placeholder="18000"
                            class="w-full bg-[var(--bg)] border border-[var(--border)] rounded-[var(--radius-sm)] px-3 py-2 text-xs focus:outline-none focus:border-[var(--accent)]">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-[var(--text-muted)] mb-1">Icon (emoji)</label>
                        <input type="text" id="product-icon" placeholder="☕"
                            class="w-full bg-[var(--bg)] border border-[var(--border)] rounded-[var(--radius-sm)] px-3 py-2 text-xs focus:outline-none focus:border-[var(--accent)]">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-[var(--text-muted)] mb-1">Kategori</label>
                    <input type="text" id="product-category" placeholder="Kopi / Non-Kopi / dst"
                        class="w-full bg-[var(--bg)] border border-[var(--border)] rounded-[var(--radius-sm)] px-3 py-2 text-xs focus:outline-none focus:border-[var(--accent)]">
                </div>
                <label class="flex items-center gap-2 text-xs" style="color: var(--text-muted)">
                    <input type="checkbox" id="product-active" checked> Menu aktif (tampil di halaman pelanggan)
                </label>

                <div class="flex gap-2 pt-2">
                    <button type="button" onclick="closeModal()" class="btn-ghost flex-1 py-2.5 text-xs">Batal</button>
                    <button type="submit" class="btn-primary flex-1 py-2.5 text-xs">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script src="menu.js"></script>
</body>
</html>
