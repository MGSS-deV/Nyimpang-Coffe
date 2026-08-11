# Nyimpang Coffee — Versi PHP

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
├── .env                  <- kredensial DB (JANGAN di-commit / upload publik)
├── database.sql          <- skema tabel, import sekali di awal
├── seed.php              <- isi data awal (staff + menu)
├── config/
│   └── db.php             <- koneksi PDO
├── includes/
│   ├── auth.php           <- helper session & proteksi login
│   └── orders_helper.php  <- helper format & statistik pesanan
└── public/                <- INI yang jadi document root web server
    ├── index.html          <- halaman pelanggan (self order)
    ├── login.html          <- login staff
    ├── bar.php             <- dashboard barista (wajib login)
    ├── keuangan.php        <- laporan keuangan (wajib login)
    ├── app.js / bar.js / keuangan.js / login.js
    ├── style.css
    ├── assets/             <- taruh qris-sample.png di sini
    └── api/                <- seluruh endpoint backend (dipanggil via fetch)
```

## Nambah/ubah menu
Edit langsung tabel `products` di database (lewat phpMyAdmin, Adminer, atau
`mysql` CLI) — nggak perlu ubah kode.

## Catatan keamanan
- Ganti `barista123` / `admin123` sebelum dipakai beneran.
- Jangan expose folder `config/` atau `includes/` ke web root.
- Aktifkan HTTPS di VPS (nginx + certbot / Let's Encrypt) sebelum dipakai
  publik, supaya password login nggak lewat plain HTTP.
