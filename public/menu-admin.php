<?php
require __DIR__ . "/../includes/auth.php";
requireRolePage(['Admin']);
$activePage = 'menu';
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
        <div class="max-w-5xl mx-auto px-6 py-5 flex flex-col gap-4">
            <div class="flex justify-between items-center flex-wrap gap-3">
                <div>
                    <h1 class="font-display text-xl tracking-tight" style="color: var(--text)">Nyimpang Coffee</h1>
                    <p class="text-[11px] text-[var(--text-muted)] mt-0.5">Manajemen Menu</p>
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

        <!-- FORM TAMBAH/EDIT MENU -->
        <div class="surface-card p-6">
            <h2 id="form-title" class="font-display text-lg mb-4">Tambah Menu Baru</h2>
            <form id="product-form" class="grid grid-cols-1 sm:grid-cols-6 gap-3 items-end">
                <input type="hidden" id="product-id" value="">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-[var(--text-muted)] mb-1">Nama Menu</label>
                    <input type="text" id="product-name" required placeholder="Cth: Kopi Susu Gula Aren"
                        class="w-full bg-[var(--bg)] border border-[var(--border)] rounded-[var(--radius-sm)] px-3 py-2 text-xs focus:outline-none focus:border-[var(--accent)]">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-[var(--text-muted)] mb-1">Deskripsi</label>
                    <input type="text" id="product-description" placeholder="Cth: Espresso, susu segar"
                        class="w-full bg-[var(--bg)] border border-[var(--border)] rounded-[var(--radius-sm)] px-3 py-2 text-xs focus:outline-none focus:border-[var(--accent)]">
                </div>
                <div>
                    <label class="block text-xs font-medium text-[var(--text-muted)] mb-1">Harga</label>
                    <input type="number" id="product-price" required min="1" placeholder="18000"
                        class="w-full bg-[var(--bg)] border border-[var(--border)] rounded-[var(--radius-sm)] px-3 py-2 text-xs focus:outline-none focus:border-[var(--accent)]">
                </div>
                <div>
                    <label class="block text-xs font-medium text-[var(--text-muted)] mb-1">Ikon (emoji)</label>
                    <input type="text" id="product-icon" placeholder="☕" maxlength="4"
                        class="w-full bg-[var(--bg)] border border-[var(--border)] rounded-[var(--radius-sm)] px-3 py-2 text-xs focus:outline-none focus:border-[var(--accent)]">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-[var(--text-muted)] mb-1">Kategori</label>
                    <input type="text" id="product-category" placeholder="Kopi / Non-Kopi"
                        class="w-full bg-[var(--bg)] border border-[var(--border)] rounded-[var(--radius-sm)] px-3 py-2 text-xs focus:outline-none focus:border-[var(--accent)]">
                </div>
                <div class="sm:col-span-2 flex items-center gap-2">
                    <button type="submit" id="submit-btn" class="btn-primary flex-1 py-2.5 text-xs">Simpan Menu</button>
                    <button type="button" id="cancel-edit-btn" onclick="cancelEdit()" class="btn-ghost py-2.5 px-4 text-xs hidden">Batal</button>
                </div>
            </form>
        </div>

        <!-- DAFTAR MENU -->
        <div class="flex items-center justify-between">
            <h2 class="font-display text-lg">Daftar Menu</h2>
            <button onclick="toggleMarginView()" id="margin-toggle-btn" class="btn-ghost text-xs px-3 py-2">📊 Tampilkan Margin Profit</button>
        </div>
        <div id="product-list" class="space-y-3"></div>

    </main>

    <!-- MODAL ATUR RESEP -->
    <div id="recipe-modal" class="fixed inset-0 bg-black/40 z-50 hidden flex items-center justify-center p-4">
        <div class="surface-card w-full max-w-md p-6 space-y-4 max-h-[85vh] overflow-y-auto">
            <div>
                <h3 class="font-display text-lg">Resep: <span id="recipe-product-name"></span></h3>
                <p class="text-[11px] mt-1" style="color: var(--text-muted)">Centang bahan baku yang dipakai & isi jumlahnya per 1 porsi. Kosongkan centang kalau nggak mau dilacak stoknya.</p>
            </div>
            <div id="recipe-ingredient-list" class="space-y-2"></div>
            <div class="flex gap-2 pt-2">
                <button type="button" onclick="closeRecipeModal()" class="btn-ghost flex-1 py-2.5 text-xs">Batal</button>
                <button type="button" onclick="saveRecipe()" class="btn-primary flex-1 py-2.5 text-xs">Simpan Resep</button>
            </div>
        </div>
    </div>

    <script src="menu-admin.js"></script>
</body>
</html>
