<?php
require __DIR__ . "/../includes/auth.php";
requireRolePage(['Admin']);
$activePage = 'voucher';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nyimpang Coffee — Voucher/Promo</title>
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
                    <p class="text-[11px] text-[var(--text-muted)] mt-0.5">Voucher / Promo</p>
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
            <h2 class="font-display text-lg mb-4">Buat Voucher Baru</h2>
            <form id="voucher-form" class="grid grid-cols-1 sm:grid-cols-6 gap-3 items-end">
                <div>
                    <label class="block text-xs font-medium text-[var(--text-muted)] mb-1">Kode</label>
                    <input type="text" id="v-code" required placeholder="DISKON20" style="text-transform: uppercase"
                        class="w-full bg-[var(--bg)] border border-[var(--border)] rounded-[var(--radius-sm)] px-3 py-2 text-xs focus:outline-none focus:border-[var(--accent)]">
                </div>
                <div>
                    <label class="block text-xs font-medium text-[var(--text-muted)] mb-1">Tipe</label>
                    <select id="v-type" class="w-full bg-[var(--bg)] border border-[var(--border)] rounded-[var(--radius-sm)] px-3 py-2 text-xs focus:outline-none focus:border-[var(--accent)]">
                        <option value="percent">Persen (%)</option>
                        <option value="fixed">Nominal (Rp)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-[var(--text-muted)] mb-1">Nilai</label>
                    <input type="number" id="v-value" required min="1" placeholder="20"
                        class="w-full bg-[var(--bg)] border border-[var(--border)] rounded-[var(--radius-sm)] px-3 py-2 text-xs focus:outline-none focus:border-[var(--accent)]">
                </div>
                <div>
                    <label class="block text-xs font-medium text-[var(--text-muted)] mb-1">Min. Belanja</label>
                    <input type="number" id="v-min" min="0" placeholder="0"
                        class="w-full bg-[var(--bg)] border border-[var(--border)] rounded-[var(--radius-sm)] px-3 py-2 text-xs focus:outline-none focus:border-[var(--accent)]">
                </div>
                <div>
                    <label class="block text-xs font-medium text-[var(--text-muted)] mb-1">Maks. Pemakaian</label>
                    <input type="number" id="v-max" min="1" placeholder="Tanpa batas"
                        class="w-full bg-[var(--bg)] border border-[var(--border)] rounded-[var(--radius-sm)] px-3 py-2 text-xs focus:outline-none focus:border-[var(--accent)]">
                </div>
                <div>
                    <label class="block text-xs font-medium text-[var(--text-muted)] mb-1">Berlaku Sampai</label>
                    <input type="date" id="v-expires"
                        class="w-full bg-[var(--bg)] border border-[var(--border)] rounded-[var(--radius-sm)] px-3 py-2 text-xs focus:outline-none focus:border-[var(--accent)]">
                </div>
                <button type="submit" class="btn-primary sm:col-span-6 py-2.5 text-xs">Buat Voucher</button>
            </form>
        </div>

        <div id="voucher-list" class="space-y-3"></div>
    </main>

    <script src="voucher-admin.js"></script>
</body>
</html>
