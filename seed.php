<?php
// ==========================================
// SEED DATA AWAL
// Jalankan sekali: php seed.php
// ==========================================

require __DIR__ . '/config/db.php';

echo "Seeding data awal...\n";

// ---------- STAFF ----------
$staffAccounts = [
    ['username' => 'barista', 'password' => 'barista123', 'role' => 'Barista'],
    ['username' => 'admin', 'password' => 'admin123', 'role' => 'Admin'],
];

$stmt = $pdo->prepare(
    "INSERT INTO staff (username, password_hash, role) VALUES (:username, :password_hash, :role)
     ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash), role = VALUES(role)"
);

foreach ($staffAccounts as $s) {
    $stmt->execute([
        'username' => $s['username'],
        'password_hash' => password_hash($s['password'], PASSWORD_BCRYPT),
        'role' => $s['role']
    ]);
    echo "  ✓ Staff: {$s['username']} / {$s['password']}\n";
}

// ---------- PRODUK ----------
// Catatan: kolom is_active punya DEFAULT 1 di database.sql, jadi seluruh
// menu di bawah ini otomatis aktif tanpa perlu ditulis eksplisit di sini.
$products = [
    ['name' => 'Kopi Susu Gula Aren', 'description' => 'Espresso, susu segar, gula aren', 'price' => 18000, 'category' => 'Kopi', 'icon' => '☕'],
    ['name' => 'Americano Hot/Ice', 'description' => 'Double shot, dingin atau panas', 'price' => 15000, 'category' => 'Kopi', 'icon' => '🧊'],
    ['name' => 'Matcha Latte', 'description' => 'Green tea premium, susu lembut', 'price' => 22000, 'category' => 'Non-Kopi', 'icon' => '🥛'],
    ['name' => 'Dark Chocolate', 'description' => 'Cokelat pekat, rasa rich', 'price' => 20000, 'category' => 'Non-Kopi', 'icon' => '🍫'],
];

$countStmt = $pdo->query("SELECT COUNT(*) AS total FROM products");
$hasProducts = $countStmt->fetch()['total'] > 0;

if (!$hasProducts) {
    $stmt = $pdo->prepare(
        "INSERT INTO products (name, description, price, category, icon) VALUES (:name, :description, :price, :category, :icon)"
    );
    foreach ($products as $p) {
        $stmt->execute($p);
        echo "  ✓ Produk: {$p['name']}\n";
    }
} else {
    echo "  – Produk sudah ada, dilewati (hapus manual dulu kalau mau re-seed).\n";
}

echo "\nSelesai! ⚠️  Ganti password default ini sebelum dipakai beneran.\n";
