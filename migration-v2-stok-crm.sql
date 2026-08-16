-- ==========================================
-- MIGRASI V2 — jalankan SATU-SATU di query editor Railway (Data tab, MySQL service)
-- Ini buat nambahin: stok bahan baku, riwayat pelanggan, link pengeluaran ke bahan baku
-- ==========================================

-- Query 1
ALTER TABLE orders ADD COLUMN customer_phone VARCHAR(20) NULL AFTER customer_name;

-- Query 2
ALTER TABLE expenses ADD COLUMN ingredient_id INT NULL;

-- Query 3
CREATE TABLE IF NOT EXISTS ingredients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    unit VARCHAR(20) NOT NULL DEFAULT 'pcs',
    stock_qty DECIMAL(10,2) NOT NULL DEFAULT 0,
    low_stock_threshold DECIMAL(10,2) NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Query 4
CREATE TABLE IF NOT EXISTS product_ingredients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    ingredient_id INT NOT NULL,
    qty_per_serving DECIMAL(10,2) NOT NULL DEFAULT 1,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (ingredient_id) REFERENCES ingredients(id) ON DELETE CASCADE,
    UNIQUE KEY unique_product_ingredient (product_id, ingredient_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
