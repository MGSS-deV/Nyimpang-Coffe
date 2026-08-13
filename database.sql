-- ==========================================
-- NYIMPANG COFFEE - DATABASE SCHEMA (MySQL)
-- ==========================================
-- Import ini sekali di awal lewat phpMyAdmin / mysql CLI:
--   mysql -u root -p nyimpang_coffee < database.sql
--
-- Kalau kamu UPDATE dari versi lama (database sudah pernah dibuat
-- sebelumnya), JANGAN jalankan file ini lagi — pakai migration_v2.sql saja
-- supaya data lama nggak tertimpa. Lihat README bagian "Update dari versi
-- lama".

CREATE TABLE IF NOT EXISTS staff (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'Barista'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description VARCHAR(255) DEFAULT '',
    price INT NOT NULL,
    category VARCHAR(50) DEFAULT 'Lainnya',
    icon VARCHAR(10) DEFAULT '☕',
    -- BARU (v2): dipakai fitur Manajemen Menu, buat nonaktifkan menu tanpa
    -- hapus permanen. Menu nonaktif otomatis hilang dari halaman pelanggan.
    is_active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS orders (
    id VARCHAR(30) PRIMARY KEY,
    customer_name VARCHAR(100) NOT NULL,
    order_type VARCHAR(20) NOT NULL DEFAULT 'Dine In',
    table_no VARCHAR(20) DEFAULT '-',
    payment_method VARCHAR(50) DEFAULT 'QRIS',
    items JSON NOT NULL,
    total_amount INT NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'Masuk',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS expenses (
    id VARCHAR(30) PRIMARY KEY,
    description VARCHAR(255) NOT NULL,
    amount INT NOT NULL,
    category VARCHAR(50) NOT NULL DEFAULT 'Lainnya',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- SELESAI ----------
-- Data awal (akun staff & menu) di-generate lewat seed.php, BUKAN di file ini,
-- supaya password staff ter-hash dengan benar pakai fungsi PHP password_hash().
--
-- Setelah import file ini, jalankan:
--   php seed.php
