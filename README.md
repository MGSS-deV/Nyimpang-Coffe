# Nyimpang Coffee — Versi PHP

## 🆕🆕🆕🆕 Update TERBARU: margin profit, laporan WA harian, shift, poin loyalitas

- **Margin Profit per Menu** — di `menu-admin.php`, klik "Tampilkan Margin
  Profit" buat lihat estimasi modal & untung tiap menu (dihitung otomatis
  dari harga rata-rata bahan baku yang pernah di-restock + resep menu).
  Kalau menu belum ada resep atau bahannya belum pernah direstock pakai
  biaya, tampil "belum ada data" (bukan angka ngasal).
- **Laporan Harian Otomatis ke WA jam 23:00** — perlu setup pemicu dari
  luar (Railway sendiri nggak bisa "nyalain jadwal"). Caranya ada di komentar
  paling atas file `public/cron-daily-report.php` — intinya daftar gratis
  di [cron-job.org](https://cron-job.org), suruh dia hit URL
  `.../cron-daily-report.php?secret=XXXX` tiap jam 23:00 WIB. Isi
  `CRON_SECRET` di `.env` dulu (bebas, kayak password).
- **Shift/Absen Staff** (`shift.php`, semua role) — clock-in/clock-out
  sendiri, Admin bisa lihat riwayat shift semua staff.
- **Poin Loyalitas** — pelanggan yang isi WA otomatis dapat 1 poin tiap
  kelipatan Rp 10.000 belanja (dihitung pas pesanan Selesai). Poin bisa
  dipakai lagi pas checkout berikutnya (1 poin = Rp 100 diskon), otomatis
  muncul kalau pelanggan punya poin & isi nomor WA yang sama. Kelihatan
  juga di halaman Pelanggan.

**Kalau database kamu sudah ada isinya**, jalankan `migration-v5-margin-shift-loyalty.sql`.

---

## Update sebelumnya (3): WA pelanggan, alert stok, struk, QR meja, voucher, Midtrans

- **Notif WA ke Pelanggan** — begitu status pesanan jadi "Siap Diambil",
  otomatis kirim WA ke pelanggan (kalau dia isi nomor pas checkout).
- **Alert Stok Rendah Otomatis** — begitu ada bahan baku tembus batas
  rendah pas ada pesanan masuk, langsung WA ke nomor admin.
- **Cetak Struk** (`struk.php?id=...`) — halaman struk yang bisa diprint,
  linknya muncul otomatis di tracker pelanggan & kartu kanban barista.
- **QR Code per Meja** (`qr-meja.php`, khusus Admin) — generate & cetak QR
  buat tiap meja, pelanggan scan → nomor meja otomatis keisi.
- **Voucher/Promo** (`voucher-admin.php`, khusus Admin) — bikin kode diskon
  (persen/nominal, minimal belanja, batas pemakaian, kedaluwarsa). Pelanggan
  masukin kode pas checkout, diskon diverifikasi ulang di server (nggak
  bisa dimanipulasi lewat DevTools).
- **Pembayaran Midtrans Beneran** — opsi bayar kartu/e-wallet asli muncul
  otomatis di checkout **kalau** kredensial Midtrans udah diisi di `.env`.
  **Perlu setup manual**: daftar di [midtrans.com](https://midtrans.com)
  (mode Sandbox dulu buat testing gratis), isi `MIDTRANS_SERVER_KEY`,
  `MIDTRANS_CLIENT_KEY`, `MIDTRANS_IS_PRODUCTION` di `.env`. Status
  pembayaran dicek LANGSUNG ke server Midtrans (bukan percaya popup di
  browser doang), jadi aman dari orang yang coba akalin "pura-pura bayar".

**Kalau database kamu sudah ada isinya**, jalankan `migration-v3-voucher.sql`
dan `migration-v4-midtrans.sql` (satu-satu, sama kayak migrasi sebelumnya).

---

## Update sebelumnya (2): stok bahan baku, WA ke barista, pelanggan, export, role

**Fitur baru session ini:**
- **Manajemen Stok Bahan Baku** (`stok.php`, khusus Admin) — catat bahan baku
  (nama, satuan, stok, batas rendah), restock kapan aja (bisa auto-catat ke
  Keuangan sekalian). Hubungkan bahan baku ke menu lewat tombol **Resep** di
  halaman Menu. Begitu stok bahan kurang, menu otomatis muncul badge
  **"Stok Habis"** ke pelanggan dan nggak bisa dipesan — nggak perlu
  nonaktifin manual. Sistemnya juga aman dari race condition (2 pesanan
  masuk bersamaan pas stok tinggal sedikit).
- **Notifikasi WhatsApp ke Barista** (via Fonnte) — begitu ada pesanan baru,
  otomatis kirim WA ke nomor barista. **Perlu setup manual**: daftar di
  [fonnte.com](https://fonnte.com), scan QR buat connect WA barista, copy
  token, isi `FONNTE_TOKEN` & `WA_NOTIFY_NUMBER` di `.env`. Kalau belum
  diisi, fitur ini otomatis nggak aktif (nggak bikin checkout gagal).
- **Riwayat Pelanggan** (`pelanggan.php`, khusus Admin) — pelanggan yang isi
  nomor WhatsApp pas checkout otomatis kerekap di sini, diurutkan dari yang
  paling sering order (loyal customer).
- **Export Laporan** — tombol "Export CSV/Excel" di halaman Keuangan &
  Riwayat (kebuka langsung di Excel), plus tombol "Cetak/Simpan PDF" (pakai
  fitur print bawaan browser, nggak butuh library tambahan).
- **Role Permission Beneran** — sekarang `Barista` dan `Admin` beda akses.
  Barista cuma bisa Dashboard, Barista (kanban), Riwayat. Menu, Stok,
  Pelanggan, dan Keuangan **khusus Admin** — otomatis kesembunyi dari nav
  kalau login sebagai Barista, dan API-nya juga ditolak (403) kalau dicoba
  akses langsung.

**Kalau database kamu sudah ada isinya** (misal di Railway), jalankan
`migration-v2-stok-crm.sql` dulu (satu-satu, sama kayak migrasi sebelumnya).

---

## Update sebelumnya: perbaikan bug + 3 fitur (dashboard, menu, riwayat)

**Bug yang diperbaiki:**
- `orders_list.php` dulu narik SEMUA baris pesanan tanpa batas (bisa lambat
  kalau data numpuk) → sekarang cuma narik pesanan aktif, riwayat lengkap
  pindah ke endpoint terpisah dengan pagination.
- Harga pesanan dulu dipercaya mentah-mentah dari request (bisa dimanipulasi
  lewat DevTools/Postman) → sekarang harga & nama produk SELALU diverifikasi
  ulang dari database di server.
- ID pesanan/pengeluaran dulu berisiko kecil tabrakan → diganti pakai
  random bytes yang jauh lebih aman.
- Jam yang tersimpan di database dulu ikut UTC server, bukan WIB → sekarang
  dipaksa WIB (`Asia/Jakarta`) baik di PHP maupun di koneksi MySQL-nya.
  **Penting untuk akurasi fitur "jam paling ramai" di dashboard baru.**
- Cookie sesi login diperketat (httponly, samesite, secure kalau HTTPS).
- Ada `<div>` yang nggak ketutup di `keuangan.php` (bug HTML kecil), sudah dibenerin.

**Fitur baru:**
- **`dashboard.php`** — halaman awal setelah login. Grafik pemasukan
  harian/mingguan, menu terlaris, dan jam paling ramai (pakai Chart.js).
- **`menu-admin.php`** — tambah/edit/nonaktifkan/hapus menu langsung dari
  browser, nggak perlu lagi buka phpMyAdmin manual.
- **`riwayat.php`** — cari pesanan lama dengan filter status & rentang
  tanggal, ada pagination.

**Kalau kamu sudah punya database yang jalan** (misal di Railway), jangan
lupa jalankan `migration-add-menu-management.sql` dulu — itu nambah kolom
yang dibutuhkan fitur menu management ke tabel `products` yang udah ada.
Kalau baru install dari nol, nggak perlu, `database.sql` udah termasuk
kolom itu.

---

Versi ini dikonversi dari versi Node.js/Socket.io. Karena PHP klasik nggak
bisa pertahanin koneksi WebSocket, dashboard barista & pelacak status
pelanggan di sini pakai **polling** (auto-refresh tiap 2.5–3 detik), bukan
realtime instan. Kalau pesanan baru masuk atau status berubah, paling lambat
kelihatan 2-3 detik kemudian — bukan langsung 0 detik kayak versi Socket.io.

## Kebutuhan
- PHP 8.x (dengan extension `pdo_mysql`)
- MySQL / MariaDB
- Web server: Apache/Nginx, atau cukup `php -S` buat coba-coba lokal

## Instalasi

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
   Ini bikin akun `barista/barista123` dan `admin/admin123` — **ganti
   passwordnya** lewat `controllers` (edit `seed.php` lalu jalankan ulang,
   atau langsung UPDATE tabel `staff` di database).

4. **Jalankan**
   - Coba lokal cepat: `php -S localhost:8000 -t public`
   - Produksi: arahkan **document root** web server (Apache/Nginx) ke folder
     `public/`. Folder `config/` dan `includes/` sengaja ada di luar
     `public/` supaya kredensial database nggak bisa diakses langsung lewat
     browser.

## Struktur folder

```
nyimpang-coffee-php/
├── .env                  <- kredensial DB + FONNTE_TOKEN (JANGAN di-commit)
├── database.sql          <- skema tabel, import sekali di awal (instalasi baru)
├── migration-add-menu-management.sql  <- migrasi lama (is_active menu)
├── migration-v2-stok-crm.sql          <- migrasi baru (stok, no HP pelanggan)
├── seed.php              <- isi data awal (staff + menu)
├── config/
│   └── db.php             <- koneksi PDO (+ set timezone WIB)
├── includes/
│   ├── auth.php           <- helper session, proteksi login & role
│   ├── nav.php             <- navigasi bersama, otomatis role-aware
│   ├── orders_helper.php  <- helper format & statistik pesanan
│   └── whatsapp.php        <- helper kirim notifikasi via Fonnte
└── public/                <- INI yang jadi document root web server
    ├── index.html          <- halaman pelanggan (self order)
    ├── login.html          <- login staff
    ├── dashboard.php       <- dashboard analitik (semua role)
    ├── bar.php             <- dashboard barista/kanban (semua role)
    ├── riwayat.php         <- riwayat pesanan + filter (semua role)
    ├── menu-admin.php      <- kelola menu + resep (khusus Admin)
    ├── stok.php             <- kelola bahan baku (khusus Admin)
    ├── pelanggan.php        <- riwayat pelanggan (khusus Admin)
    ├── keuangan.php        <- laporan keuangan (khusus Admin)
    ├── *.js                <- logic tiap halaman (nama sama kayak .php-nya)
    ├── style.css
    ├── assets/             <- taruh qris-sample.png di sini
    └── api/                <- seluruh endpoint backend (dipanggil via fetch)
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

5. Redeploy kalau perlu, lalu buka domain Railway-nya.

**Kalau masih "Application failed to respond"**: buka tab **Deployments →
Deploy Logs** di Railway, itu bakal nunjukin persis errornya di mana
(biasanya gagal konek DB kalau env var belum diisi bener).


Edit langsung tabel `products` di database (lewat phpMyAdmin, Adminer, atau
`mysql` CLI) — nggak perlu ubah kode.

## Nambah/ubah menu
Edit langsung tabel `products` di database (lewat phpMyAdmin, Adminer, atau
`mysql` CLI) — nggak perlu ubah kode.

## Catatan keamanan
- Ganti `barista123` / `admin123` sebelum dipakai beneran.
- Jangan expose folder `config/` atau `includes/` ke web root.
- Aktifkan HTTPS di VPS (nginx + certbot / Let's Encrypt) sebelum dipakai
  publik, supaya password login nggak lewat plain HTTP.
