-- ==========================================
-- NYIMPANG COFFEE - DATABASE SCHEMA (MySQL)
-- ==========================================
-- Import ini sekali di awal lewat phpMyAdmin / mysql CLI:
--   mysql -u root -p nyimpang_coffee < database.sql

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
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS orders (
    id VARCHAR(30) PRIMARY KEY,
    customer_name VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(20) NULL,
    order_type VARCHAR(20) NOT NULL DEFAULT 'Dine In',
    table_no VARCHAR(20) DEFAULT '-',
    payment_method VARCHAR(50) DEFAULT 'QRIS',
    items JSON NOT NULL,
    total_amount INT NOT NULL,
    voucher_code VARCHAR(30) NULL,
    discount_amount INT NOT NULL DEFAULT 0,
    status VARCHAR(20) NOT NULL DEFAULT 'Masuk',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS expenses (
    id VARCHAR(30) PRIMARY KEY,
    description VARCHAR(255) NOT NULL,
    amount INT NOT NULL,
    category VARCHAR(50) NOT NULL DEFAULT 'Lainnya',
    ingredient_id INT NULL,
    restock_qty DECIMAL(10,2) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bahan baku (stok)
CREATE TABLE IF NOT EXISTS ingredients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    unit VARCHAR(20) NOT NULL DEFAULT 'pcs',
    stock_qty DECIMAL(10,2) NOT NULL DEFAULT 0,
    low_stock_threshold DECIMAL(10,2) NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Resep: bahan baku apa aja & berapa banyak dipakai per 1 porsi menu
CREATE TABLE IF NOT EXISTS product_ingredients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    ingredient_id INT NOT NULL,
    qty_per_serving DECIMAL(10,2) NOT NULL DEFAULT 1,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (ingredient_id) REFERENCES ingredients(id) ON DELETE CASCADE,
    UNIQUE KEY unique_product_ingredient (product_id, ingredient_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- SELESAI ----------
-- Data awal (akun staff & menu) di-generate lewat seed.php, BUKAN di file ini,
-- supaya password staff ter-hash dengan benar pakai fungsi PHP password_hash().
--
-- Setelah import file ini, jalankan:
--   php seed.php


-- Voucher/promo diskon
CREATE TABLE IF NOT EXISTS vouchers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(30) UNIQUE NOT NULL,
    discount_type ENUM('percent','fixed') NOT NULL DEFAULT 'percent',
    discount_value INT NOT NULL,
    min_purchase INT NOT NULL DEFAULT 0,
    max_uses INT NULL,
    used_count INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    expires_at DATE NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Transaksi Midtrans yang belum lunas (dihapus otomatis pas berhasil dikonfirmasi)
CREATE TABLE IF NOT EXISTS midtrans_pending (
    id VARCHAR(50) PRIMARY KEY,
    payload TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Shift/absen staff
CREATE TABLE IF NOT EXISTS shifts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    staff_username VARCHAR(50) NOT NULL,
    clock_in DATETIME NOT NULL,
    clock_out DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Poin loyalitas pelanggan
CREATE TABLE IF NOT EXISTS customer_points (
    phone VARCHAR(20) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    points INT NOT NULL DEFAULT 0,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
