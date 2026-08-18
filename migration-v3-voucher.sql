-- ==========================================
-- MIGRASI V3 — jalankan SATU-SATU di query editor Railway
-- Ini buat fitur voucher/promo diskon
-- ==========================================

-- Query 1
ALTER TABLE orders ADD COLUMN voucher_code VARCHAR(30) NULL;

-- Query 2
ALTER TABLE orders ADD COLUMN discount_amount INT NOT NULL DEFAULT 0;

-- Query 3
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
