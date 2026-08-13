-- ==========================================
-- MIGRASI UPDATE — jalankan ini di database Railway kamu yang SUDAH ADA
-- (bukan install baru). Paste satu-satu ke query editor Railway kalau
-- nggak bisa multi-statement sekaligus.
-- ==========================================

-- 1. Tambah kolom buat fitur nonaktifkan menu
ALTER TABLE products ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER icon;

-- 2. Tambah kolom tanggal dibuat (opsional, buat rapi-rapi aja)
ALTER TABLE products ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP;
