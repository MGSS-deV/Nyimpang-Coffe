# Nyimpang Coffee — Versi PHP

Versi ini dikonversi dari versi Node.js/Socket.io. Karena PHP klasik nggak
bisa pertahanin koneksi WebSocket, dashboard barista & pelacak status
pelanggan di sini pakai **polling** (auto-refresh tiap 2.5–3 detik), bukan
realtime instan. Kalau pesanan baru masuk atau status berubah, paling lambat
kelihatan 2-3 detik kemudian — bukan langsung 0 detik kayak versi Socket.io.

## 🆕 Update v2 — Bug fix & fitur baru

**Bug/celah yang diperbaiki:**
- 🔴 **Keamanan: harga bisa dimanipulasi pelanggan.** `orders_create.php`
  dulu percaya begitu saja `price` yang dikirim dari browser — pelanggan
  bisa ubah request lewat DevTools dan bayar semaunya. Sekarang harga
  SELALU diambil ulang dari tabel `products` di server.
- 🔴 **Zona waktu server tidak diset.** Host seperti Railway biasanya jalan
  di UTC, beda 7 jam dari WIB. Sekarang `config/db.php` memaksa
  `SET time_zone = '+07:00'` di sesi MySQL + `date_default_timezone_set('Asia/Jakarta')`
  di PHP, supaya jam pesanan dan data analitik konsisten dengan WIB.
- 🟠 **Session fixation saat login** — sekarang session ID diregenerasi
  (`session_regenerate_id`) begitu login berhasil.
- 🟠 **`orders_list.php` menarik seluruh riwayat pesanan tanpa batas**
  setiap 2.5 detik. Sekarang dibatasi ke pesanan aktif + hari ini; riwayat
  lengkap dipindah ke `orders_history.php` yang mendukung filter & pagination.
- 🟡 **Menu tidak punya status aktif/nonaktif** — ditambahkan kolom
  `products.is_active`.

**Fitur baru:**
1. **Dashboard Ringkasan/Analitik** (`dashboard.php`) — halaman awal setelah
   login. Grafik penjualan harian (7/14/30 hari), menu terlaris, dan jam
   paling ramai.
2. **Manajemen Menu** (`menu.php`, khusus role **Admin**) — tambah, edit,
   nonaktifkan, atau hapus menu langsung dari browser, nggak perlu buka
   phpMyAdmin lagi.
3. **Riwayat Pesanan + Filter** (`riwayat.php`) — cari pesanan lama
   berdasarkan rentang tanggal, status, atau nama/ID, dengan pagination.

Kalau kamu upgrade dari versi lama (database sudah ada isinya), jalankan
`migration_v2.sql` — lihat bagian "Update dari versi lama" di bawah.

## Kebutuhan
- PHP 8.x (dengan extension `pdo_mysql`)
- MySQL / MariaDB
- Web server: Apache/Nginx, atau cukup `php -S` buat coba-coba lokal

## Instalasi (baru)

1. **Buat database & tabel**
   ```
   mysql -u root -p -e "CREATE DATABASE nyimpang_coffee"
   mysql -u root -p nyimpang_coffee < database.sql
   ```

2. **Isi `.env`** — copy `.env.example` jadi `.env`, sesuaikan kredensial DB kamu:
   ```
   cp .env.example .env
   ```

3. **Seed data awal** (akun staff + menu):
   ```
   php seed.php
   ```
   Ini bikin akun `barista/barista123` (role Barista) dan `admin/admin123`
   (role **Admin** — satu-satunya role yang bisa buka halaman Manajemen
   Menu) — **ganti passwordnya** sebelum dipakai beneran (edit `seed.php`
   lalu jalankan ulang, atau langsung UPDATE tabel `staff` di database).

4. **Jalankan**
   - Coba lokal cepat: `php -S localhost:8000 -t public`
   - Produksi: arahkan **document root** web server (Apache/Nginx) ke folder
     `public/`. Folder `config/` dan `includes/` sengaja ada di luar
     `public/` supaya kredensial database nggak bisa diakses langsung lewat
     browser.

## Update dari versi lama

Kalau database kamu sudah pernah dibuat dari `database.sql` versi
sebelumnya (sebelum v2) dan sudah ada datanya, **jangan** re-import
`database.sql` (bisa menimpa data). Cukup jalankan migrasinya saja:

```
mysql -u root -p nyimpang_coffee < migration_v2.sql
```

Ini cuma menambahkan kolom `products.is_active` (default aktif untuk semua
menu lama). Setelah itu, pull/salin file kode terbaru (`config/`,
`includes/`, `public/`) ke server kamu seperti biasa.

## Struktur folder

```
nyimpang-coffee-php/
├── .env                  <- kredensial DB (JANGAN di-commit / upload publik)
├── database.sql          <- skema tabel, import sekali di awal (install baru)
├── migration_v2.sql      <- migrasi buat yang upgrade dari versi lama
├── seed.php              <- isi data awal (staff + menu)
├── config/
│   └── db.php             <- koneksi PDO + timezone WIB
├── includes/
│   ├── auth.php           <- helper session, proteksi login & role (Admin)
│   └── orders_helper.php  <- helper format & statistik pesanan
└── public/                <- INI yang jadi document root web server
    ├── index.html          <- halaman pelanggan (self order)
    ├── login.html          <- login staff
    ├── dashboard.php       <- 🆕 Dashboard Ringkasan/Analitik (halaman awal setelah login)
    ├── bar.php             <- dashboard barista / papan pesanan (wajib login)
    ├── riwayat.php         <- 🆕 Riwayat Pesanan + Filter (wajib login)
    ├── menu.php            <- 🆕 Manajemen Menu / CRUD produk (khusus Admin)
    ├── keuangan.php        <- laporan keuangan (wajib login)
    ├── app.js / bar.js / keuangan.js / login.js
    ├── dashboard.js / riwayat.js / menu.js   <- 🆕
    ├── style.css
    ├── assets/             <- taruh qris-sample.png di sini
    └── api/                <- seluruh endpoint backend (dipanggil via fetch)
        ├── auth_login.php / auth_logout.php / auth_me.php
        ├── orders_create.php / orders_list.php / orders_update.php
        ├── orders_history.php        <- 🆕 riwayat + filter + pagination
        ├── order_status.php
        ├── products_list.php         <- publik, cuma menu aktif
        ├── products_list_admin.php   <- 🆕 admin, semua menu
        ├── products_create.php       <- 🆕
        ├── products_update.php       <- 🆕
        ├── products_delete.php       <- 🆕
        ├── analytics_summary.php     <- 🆕 data buat Dashboard
        ├── finance_summary.php / finance_expense_create.php / finance_expense_delete.php
```

## Deploy ke Railway

Railway butuh 2 file tambahan yang sudah disiapkan di project ini:
- **`Procfile`** — perintah start (`php -S 0.0.0.0:$PORT -t public`). Railway kasih port secara dinamis lewat variabel `$PORT`, jadi jangan hardcode port sendiri.
- **`composer.json`** — biar Railway/Nixpacks otomatis ngenalin ini sebagai project PHP.

Langkah-langkah:

1. **Push project ini** (folder `nyimpang-coffee-php`, termasuk `Procfile` &
   `composer.json` di root) ke GitHub, lalu deploy repo itu di Railway.

2. **Tambah database MySQL**: di project Railway kamu, klik **New →
   Database → Add MySQL**. Railway otomatis bikin service MySQL terpisah.

3. **Hubungkan variabel database**: buka service PHP kamu → tab
   **Variables** → tambahkan variabel `DB_HOST`, `DB_USER`, `DB_PASSWORD`,
   `DB_NAME` yang **reference** ke service MySQL (klik "Add Reference Variable"
   atau ketik `${{MySQL.MYSQLHOST}}`, `${{MySQL.MYSQLUSER}}`, dst).
   Kalau males, sebenarnya nggak perlu diisi manual — `config/db.php` sudah
   otomatis fallback ke variabel bawaan Railway (`MYSQLHOST`, `MYSQLUSER`,
   `MYSQLPASSWORD`, `MYSQLDATABASE`, `MYSQLPORT`) kalau `DB_*` kosong.

4. **Import skema & seed data** — Railway nggak jalanin `database.sql`/`seed.php`
   otomatis. Pakai Railway CLI:
   ```
   railway run mysql -h $MYSQLHOST -u $MYSQLUSER -p$MYSQLPASSWORD $MYSQLDATABASE < database.sql
   railway run php seed.php
   ```
   (atau connect manual pakai kredensial dari tab Variables service MySQL,
   lewat TablePlus/Adminer/DBeaver dari komputer kamu.)

   Kalau ini update dari deployment lama, ganti baris pertama jadi
   `< migration_v2.sql` (bukan `database.sql`).

5. Redeploy kalau perlu, lalu buka domain Railway-nya.

**Kalau masih "Application failed to respond"**: buka tab **Deployments →
Deploy Logs** di Railway, itu bakal nunjukin persis errornya di mana
(biasanya gagal konek DB kalau env var belum diisi bener).

## Nambah/ubah menu

Sekarang ada dua cara:
1. **Lewat browser (disarankan)** — login sebagai Admin, buka menu **Menu**
   di navigasi atas, lalu tambah/edit/nonaktifkan/hapus menu dari sana.
2. **Manual lewat database** — masih bisa, edit langsung tabel `products`
   lewat phpMyAdmin/Adminer/`mysql` CLI kalau perlu.

## Role staff

- **Barista** — akses Dashboard, Papan Pesanan, Riwayat, Laporan Keuangan.
- **Admin** — semua akses Barista, plus Manajemen Menu.

Role diatur lewat kolom `role` di tabel `staff` (`'Barista'` atau `'Admin'`,
case-sensitive).

## Catatan keamanan

- Ganti `barista123` / `admin123` sebelum dipakai beneran.
- Jangan expose folder `config/` atau `includes/` ke web root.
- Aktifkan HTTPS di VPS (nginx + certbot / Let's Encrypt) sebelum dipakai
  publik, supaya password login nggak lewat plain HTTP.
- **Keterbatasan yang belum ditangani (untuk pengembangan lanjutan):**
  belum ada proteksi CSRF di form-form staff (mengandalkan session cookie
  saja), dan ID pesanan (`ORD-<timestamp><random 3 digit>`) cukup mudah
  ditebak sehingga endpoint publik `order_status.php` sebaiknya jangan
  dianggap rahasia penuh. Untuk skala kecil/menengah risiko ini rendah,
  tapi kalau mau dipakai skala lebih besar, ini kandidat perbaikan
  berikutnya.
