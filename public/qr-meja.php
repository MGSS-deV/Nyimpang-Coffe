<?php
require __DIR__ . "/../includes/auth.php";
requireRolePage(['Admin']);
$activePage = 'qr-meja';

// Base URL otomatis dari domain yang lagi dipake, biar QR-nya bener
// baik pas dites di localhost maupun udah di Railway.
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') ? 'https' : 'http';
$baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nyimpang Coffee — QR Code Meja</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="pb-20">
    <header class="bg-[var(--surface)] border-b border-[var(--border)] sticky top-0 z-30 no-print">
        <div class="max-w-5xl mx-auto px-6 py-5 flex flex-col gap-4">
            <div class="flex justify-between items-center flex-wrap gap-3">
                <div>
                    <h1 class="font-display text-xl tracking-tight" style="color: var(--text)">Nyimpang Coffee</h1>
                    <p class="text-[11px] text-[var(--text-muted)] mt-0.5">QR Code Meja</p>
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
        <div class="surface-card p-6 no-print">
            <h2 class="font-display text-lg mb-4">Generate QR Meja</h2>
            <form id="generate-form" class="flex items-end gap-3">
                <div>
                    <label class="block text-xs font-medium text-[var(--text-muted)] mb-1">Jumlah Meja</label>
                    <input type="number" id="table-count" min="1" max="100" value="10"
                        class="w-32 bg-[var(--bg)] border border-[var(--border)] rounded-[var(--radius-sm)] px-3 py-2 text-xs focus:outline-none focus:border-[var(--accent)]">
                </div>
                <button type="submit" class="btn-primary py-2.5 px-4 text-xs">Generate</button>
                <button type="button" onclick="window.print()" class="btn-ghost py-2.5 px-4 text-xs">🖨️ Cetak Semua</button>
            </form>
            <p class="text-[11px] mt-3" style="color: var(--text-muted)">
                Tiap QR nunjuk ke halaman pesan dengan nomor meja udah keisi otomatis. Print, gunting, tempel di tiap meja.
            </p>
        </div>

        <div id="qr-grid" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4"></div>
    </main>

    <script>
        const BASE_URL = <?= json_encode($baseUrl) ?>;

        function renderQrGrid(count) {
            const grid = document.getElementById('qr-grid');
            let html = '';
            for (let i = 1; i <= count; i++) {
                const url = `${BASE_URL}/index.html?meja=${i}`;
                const qrImg = `https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=${encodeURIComponent(url)}`;
                html += `
                    <div class="surface-card p-4 text-center break-inside-avoid">
                        <img src="${qrImg}" alt="QR Meja ${i}" class="w-full rounded-[var(--radius-sm)]">
                        <p class="font-display text-lg mt-2">Meja ${i}</p>
                        <p class="text-[10px]" style="color: var(--text-faint)">Scan untuk pesan</p>
                    </div>
                `;
            }
            grid.innerHTML = html;
        }

        document.getElementById('generate-form').addEventListener('submit', (e) => {
            e.preventDefault();
            const count = Number(document.getElementById('table-count').value) || 10;
            renderQrGrid(count);
        });

        async function loadStaffInfo() {
            try {
                const response = await fetch('/api/auth_me.php');
                const data = await response.json();
                const badge = document.getElementById('staff-badge');
                if (data.success && badge) badge.innerText = `👤 ${data.user.username} (${data.user.role})`;
            } catch (error) { console.error(error); }
        }

        async function logout() {
            if (!confirm('Yakin mau keluar?')) return;
            await fetch('/api/auth_logout.php', { method: 'POST' });
            window.location.href = 'login.html';
        }

        document.addEventListener('DOMContentLoaded', () => {
            loadStaffInfo();
            renderQrGrid(10);
        });
    </script>
</body>
</html>
