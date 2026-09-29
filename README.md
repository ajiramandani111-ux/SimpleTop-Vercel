# SimpleTop — Toko Jual Beli Laptop

Website toko laptop sederhana berbasis PHP native, dikembangkan dari struktur jobsheet SIMPUS-Mini namun dengan tema dan tata letak yang dibuat berbeda agar terasa seperti toko online yang sesungguhnya.

## Struktur Folder

```
SimpleTop-php/
├── index.php                  # Beranda (hero, kartu statistik dari COUNT(*), produk pilihan)
├── includes/
│   ├── header.php             # Topbar promo + navbar
│   ├── footer.php             # Footer
│   ├── koneksi.php            # Koneksi PDO ke PostgreSQL
│   └── seed_data.php          # (kosong; akun kini di database)
├── sql/
│   └── 01_laptop_bestseller.sql   # Skema + data awal tabel laptop & bestseller
├── assets/
│   ├── css/style.css          # Tema gradien putih-silver-biru muda
│   └── js/app.js              # Hamburger menu, filter pencarian, konfirmasi hapus, toggle password
├── laptop/
│   ├── list.php                # Katalog laptop (kartu produk) — SELECT * FROM laptop
│   ├── tambah.php              # Form tambah laptop
│   └── proses_tambah.php       # Validasi & INSERT via prepared statement
├── bestseller/
│   ├── list.php                # Produk terlaris (ranking list) — SELECT * FROM bestseller
│   ├── tambah.php              # Form tambah data terlaris
│   └── proses_tambah.php       # Validasi & INSERT via prepared statement
├── login.php                   # Halaman masuk
├── register.php                # Halaman daftar akun
├── proses_login.php
├── proses_register.php
├── logout.php
├── docs/wireframe.md
├── README.md
└── Dokumentasi/
    └── perbedaan-simpus.md
```

## Cara Menjalankan

1. **Buat database PostgreSQL** lalu jalankan skema:
   ```
   createdb simpletop
   psql -d simpletop -f sql/01_laptop_bestseller.sql
   ```
2. **Untuk dev lokal**, `includes/koneksi.php` otomatis jatuh ke nilai default (`localhost`, `5432`, `simpletop`, user `postgres`/`postgres`) kalau tidak ada environment variable Railway — edit langsung di file itu kalau kredensial lokalmu beda. **Untuk deploy ke Railway**, tidak perlu edit apa pun, lihat bagian "Deploy ke Railway" di bawah.
3. Salin folder `SimpleTop-railway` ke direktori web server (htdocs XAMPP/Laragon, atau folder proyek PHP built-in server) untuk uji lokal. Pastikan ekstensi PHP `pdo_pgsql` sudah aktif.
4. Jalankan dengan PHP built-in server untuk uji cepat:
   ```
   php -S localhost:8000
   ```
5. Buka `http://localhost:8000/index.php` di browser.

## Fitur Utama

- **Autentikasi sederhana** — registrasi & login dengan akun di tabel `users` (password di-hash) dan session di tabel `sessions`, navbar berubah otomatis menampilkan "Halo, {nama}" saat sudah login.
- **Katalog Laptop & Produk Terlaris** — data disimpan permanen di **PostgreSQL** (tabel `laptop` & `bestseller`), diambil dengan `SELECT * FROM ...` dan ditambah lewat form dengan validasi server + `INSERT` via prepared statement (`proses_tambah.php`).
- **Kartu statistik di beranda** — total model laptop, total unit stok, dan total produk terlaris dihitung langsung dari database memakai `SELECT COUNT(*)` / `SUM()`.
- **Pencarian** — kolom cari di setiap halaman katalog memfilter kartu/list secara langsung (client-side).

## Deploy ke Vercel

Project ini dapat dijalankan di Vercel menggunakan container. Vercel mendeteksi `Dockerfile.vercel` di root project dan menjalankan server HTTP PHP pada port yang diberikan melalui `PORT`.

1. Push seluruh isi project ke GitHub.
2. Di Vercel pilih **Add New → Project**, lalu import repository GitHub.
3. Biarkan Root Directory di folder project ini dan gunakan konfigurasi default. `Dockerfile.vercel` akan digunakan untuk build container.
4. Tambahkan environment variable `DATABASE_URL` di **Project Settings → Environment Variables**. Gunakan connection string PostgreSQL dari provider database kamu. Untuk database hosted seperti Neon, gunakan connection string yang mendukung koneksi dari serverless/container dan biasanya menyertakan `sslmode=require`.
5. Deploy ulang setelah environment variable disimpan.
6. Jalankan **dua** file SQL berurutan pada database PostgreSQL yang digunakan: `sql/01_laptop_bestseller.sql` lalu `sql/02_users_sessions.sql`. Jika tabel `laptop` & `bestseller` sudah ada dan terisi, jalankan hanya `02_users_sessions.sql`.
7. Pastikan `Dockerfile.vercel` berada di root repository (bukan di dalam sub-folder), atau atur **Root Directory** di Vercel. Tidak perlu `vercel.json`.

Untuk lokal Laragon, aplikasi tetap memakai fallback PostgreSQL `localhost:5432`, database `simpletop`, user `postgres`, password `postgres` jika tidak ada environment variable.

**Catatan deployment:** container Vercel stateless dan dimatikan setelah tidak ada trafik, sehingga akun (tabel `users`) dan session (tabel `sessions`, lihat `includes/session.php`) disimpan di PostgreSQL. Request pertama setelah idle bisa lebih lambat.

## Catatan

- Data **laptop & bestseller** tersimpan permanen di PostgreSQL (tidak hilang saat service di-restart, selama volume database Railway tidak dihapus).
- Data **akun login** disimpan di tabel `users` (password memakai `password_hash`), jadi tidak hilang saat service restart.
- Tombol "Hapus" di tampilan katalog/ranking masih bersifat tampilan (menghapus dari DOM lewat JavaScript), belum terhubung ke `DELETE` di database.
