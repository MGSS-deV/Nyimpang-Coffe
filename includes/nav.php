<?php
// Include file ini SETELAH set $activePage = 'dashboard' | 'bar' | 'riwayat' | 'menu' | 'keuangan'
// dari halaman yang manggil. Menyorot menu yang lagi aktif.

$navLinks = [
    'dashboard' => ['label' => 'Dashboard', 'href' => 'dashboard.php'],
    'bar' => ['label' => 'Barista', 'href' => 'bar.php'],
    'riwayat' => ['label' => 'Riwayat', 'href' => 'riwayat.php'],
    'menu' => ['label' => 'Menu', 'href' => 'menu-admin.php'],
    'keuangan' => ['label' => 'Keuangan', 'href' => 'keuangan.php'],
];
?>
<nav class="flex items-center gap-1 overflow-x-auto">
    <?php foreach ($navLinks as $key => $link): ?>
        <a href="<?= htmlspecialchars($link['href']) ?>"
           class="<?= ($activePage ?? '') === $key ? 'btn-primary' : 'btn-ghost' ?> px-3 py-2 text-xs whitespace-nowrap">
            <?= htmlspecialchars($link['label']) ?>
        </a>
    <?php endforeach; ?>
</nav>
