-- ==========================================
-- MIGRASI V5 — jalankan satu-satu di Railway
-- Margin profit, shift/absen staff, poin loyalitas pelanggan
-- ==========================================

-- Query 1
ALTER TABLE expenses ADD COLUMN restock_qty DECIMAL(10,2) NULL;

-- Query 2
CREATE TABLE IF NOT EXISTS shifts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    staff_username VARCHAR(50) NOT NULL,
    clock_in DATETIME NOT NULL,
    clock_out DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Query 3
CREATE TABLE IF NOT EXISTS customer_points (
    phone VARCHAR(20) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    points INT NOT NULL DEFAULT 0,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
