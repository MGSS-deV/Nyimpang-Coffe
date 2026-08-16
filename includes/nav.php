<?php
// Include file ini SETELAH set $activePage = 'dashboard' | 'bar' | 'riwayat' | 'menu' | 'keuangan' | 'stok' | 'pelanggan'
// dari halaman yang manggil. Menyorot menu yang lagi aktif.
// Link yang butuh role Admin otomatis kesembunyi dari staff biasa.

$currentRole = currentUser()['role'] ?? '';

$navLinks = [
    'dashboard' => ['label' => 'Dashboard', 'href' => 'dashboard.php', 'adminOnly' => false],
    'bar' => ['label' => 'Barista', 'href' => 'bar.php', 'adminOnly' => false],
    'riwayat' => ['label' => 'Riwayat', 'href' => 'riwayat.php', 'adminOnly' => false],
    'menu' => ['label' => 'Menu', 'href' => 'menu-admin.php', 'adminOnly' => true],
    'stok' => ['label' => 'Stok', 'href' => 'stok.php', 'adminOnly' => true],
    'pelanggan' => ['label' => 'Pelanggan', 'href' => 'pelanggan.php', 'adminOnly' => true],
    'keuangan' => ['label' => 'Keuangan', 'href' => 'keuangan.php', 'adminOnly' => true],
];
?>
<nav class="flex items-center gap-1 overflow-x-auto">
    <?php foreach ($navLinks as $key => $link): ?>
        <?php if ($link['adminOnly'] && $currentRole !== 'Admin') continue; ?>
        <a href="<?= htmlspecialchars($link['href']) ?>"
           class="<?= ($activePage ?? '') === $key ? 'btn-primary' : 'btn-ghost' ?> px-3 py-2 text-xs whitespace-nowrap">
            <?= htmlspecialchars($link['label']) ?>
        </a>
    <?php endforeach; ?>
</nav>
