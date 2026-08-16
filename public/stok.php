<?php
require __DIR__ . "/../includes/auth.php";
requireRolePage(['Admin']);
$activePage = 'stok';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nyimpang Coffee — Stok Bahan Baku</title>
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
                    <p class="text-[11px] text-[var(--text-muted)] mt-0.5">Stok Bahan Baku</p>
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
        <div class="surface-card p-6">
            <h2 class="font-display text-lg mb-4">Tambah Bahan Baku</h2>
            <form id="ingredient-form" class="grid grid-cols-1 sm:grid-cols-5 gap-3 items-end">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-[var(--text-muted)] mb-1">Nama Bahan</label>
                    <input type="text" id="ing-name" required placeholder="Cth: Susu UHT"
                        class="w-full bg-[var(--bg)] border border-[var(--border)] rounded-[var(--radius-sm)] px-3 py-2 text-xs focus:outline-none focus:border-[var(--accent)]">
                </div>
                <div>
                    <label class="block text-xs font-medium text-[var(--text-muted)] mb-1">Satuan</label>
                    <input type="text" id="ing-unit" placeholder="ml / gram / pcs"
                        class="w-full bg-[var(--bg)] border border-[var(--border)] rounded-[var(--radius-sm)] px-3 py-2 text-xs focus:outline-none focus:border-[var(--accent)]">
                </div>
                <div>
                    <label class="block text-xs font-medium text-[var(--text-muted)] mb-1">Stok Awal</label>
                    <input type="number" id="ing-stock" step="0.01" min="0" placeholder="0"
                        class="w-full bg-[var(--bg)] border border-[var(--border)] rounded-[var(--radius-sm)] px-3 py-2 text-xs focus:outline-none focus:border-[var(--accent)]">
                </div>
                <div>
                    <label class="block text-xs font-medium text-[var(--text-muted)] mb-1">Batas Rendah</label>
                    <input type="number" id="ing-threshold" step="0.01" min="0" placeholder="0"
                        class="w-full bg-[var(--bg)] border border-[var(--border)] rounded-[var(--radius-sm)] px-3 py-2 text-xs focus:outline-none focus:border-[var(--accent)]">
                </div>
                <button type="submit" class="btn-primary sm:col-span-5 py-2.5 text-xs">Tambah Bahan Baku</button>
            </form>
        </div>

        <div id="ingredient-list" class="space-y-3"></div>
    </main>

    <div id="restock-modal" class="fixed inset-0 bg-black/40 z-50 hidden flex items-center justify-center p-4">
        <div class="surface-card w-full max-w-sm p-6 space-y-4">
            <h3 class="font-display text-lg">Restock: <span id="restock-ing-name"></span></h3>
            <form id="restock-form" class="space-y-3">
                <input type="hidden" id="restock-ing-id">
                <div>
                    <label class="block text-xs font-medium text-[var(--text-muted)] mb-1">Jumlah Ditambahkan</label>
                    <input type="number" id="restock-qty" step="0.01" min="0.01" required
                        class="w-full bg-[var(--bg)] border border-[var(--border)] rounded-[var(--radius-sm)] px-3 py-2 text-xs focus:outline-none focus:border-[var(--accent)]">
                </div>
                <div>
                    <label class="block text-xs font-medium text-[var(--text-muted)] mb-1">Biaya (opsional, auto-catat ke Keuangan)</label>
                    <input type="number" id="restock-cost" min="0" placeholder="0"
                        class="w-full bg-[var(--bg)] border border-[var(--border)] rounded-[var(--radius-sm)] px-3 py-2 text-xs focus:outline-none focus:border-[var(--accent)]">
                </div>
                <div class="flex gap-2 pt-1">
                    <button type="button" onclick="closeRestockModal()" class="btn-ghost flex-1 py-2.5 text-xs">Batal</button>
                    <button type="submit" class="btn-primary flex-1 py-2.5 text-xs">Simpan Restock</button>
                </div>
            </form>
        </div>
    </div>

    <script src="stok.js"></script>
</body>
</html>
