<?php
require __DIR__ . "/../includes/auth.php";
requireAuthPage();
$activePage = 'bar';
$isAdmin = currentUser()['role'] === 'Admin';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nyimpang Coffee — Shift/Absen</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="pb-20">
    <header class="bg-[var(--surface)] border-b border-[var(--border)] sticky top-0 z-30">
        <div class="max-w-3xl mx-auto px-6 py-5 flex flex-col gap-4">
            <div class="flex justify-between items-center flex-wrap gap-3">
                <div>
                    <h1 class="font-display text-xl tracking-tight" style="color: var(--text)">Nyimpang Coffee</h1>
                    <p class="text-[11px] text-[var(--text-muted)] mt-0.5">Shift / Absen</p>
                </div>
                <div class="flex items-center gap-2">
                    <span id="staff-badge" class="text-xs text-[var(--text-muted)] px-2"></span>
                    <a href="bar.php" class="btn-ghost px-4 py-2 text-xs">← Kembali</a>
                    <button onclick="logout()" class="btn-text-muted text-xs px-2 py-2">Keluar</button>
                </div>
            </div>
        </div>
    </header>

    <main class="max-w-3xl mx-auto px-6 py-8 space-y-6">
        <div class="surface-card p-6 text-center">
            <p class="text-xs mb-2" style="color: var(--text-muted)">Status Kamu Sekarang</p>
            <h2 id="shift-status" class="font-display text-2xl mb-1">Memuat...</h2>
            <p id="shift-since" class="text-xs" style="color: var(--text-faint)"></p>
            <div class="flex justify-center gap-3 mt-5">
                <button id="btn-clockin" onclick="clockIn()" class="btn-primary px-6 py-3 text-sm">Clock In</button>
                <button id="btn-clockout" onclick="clockOut()" class="btn-ghost px-6 py-3 text-sm hidden">Clock Out</button>
            </div>
        </div>

        <?php if ($isAdmin): ?>
        <div>
            <p class="text-[11px] tracking-wide text-[var(--text-muted)] uppercase mb-3">Riwayat Shift Semua Staff</p>
            <div class="surface-card overflow-hidden">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="hairline-divider text-left" style="background: var(--bg)">
                            <th class="px-4 py-3 font-semibold" style="color: var(--text-muted)">Staff</th>
                            <th class="px-4 py-3 font-semibold" style="color: var(--text-muted)">Clock In</th>
                            <th class="px-4 py-3 font-semibold" style="color: var(--text-muted)">Clock Out</th>
                            <th class="px-4 py-3 font-semibold text-right" style="color: var(--text-muted)">Durasi</th>
                        </tr>
                    </thead>
                    <tbody id="shift-tbody"></tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </main>

    <script src="shift.js"></script>
</body>
</html>
