-- ==========================================
-- MIGRASI V4 — jalankan di query editor Railway
-- Fitur pembayaran Midtrans beneran
-- ==========================================
CREATE TABLE IF NOT EXISTS midtrans_pending (
    id VARCHAR(50) PRIMARY KEY,
    payload TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
