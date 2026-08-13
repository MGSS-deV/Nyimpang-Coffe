-- ==========================================
-- MIGRASI v2 — jalankan ini kalau database kamu sudah ada isinya (dibuat
-- dari database.sql versi lama) dan kamu cuma mau nambahin perubahan baru,
-- BUKAN install dari nol.
--
--   mysql -u root -p nyimpang_coffee < migration_v2.sql
--
-- Kalau baru install dari nol, LANGSUNG pakai database.sql saja, file ini
-- tidak perlu dijalankan (kolomnya sudah otomatis ada di sana).
-- ==========================================

-- Kolom status aktif/nonaktif menu (fitur Manajemen Menu).
-- Kalau kolom ini sudah pernah ditambahkan, baris ini akan gagal dengan
-- error "Duplicate column name" — itu artinya migrasi sudah pernah jalan,
-- aman untuk diabaikan.
ALTER TABLE products ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1;
