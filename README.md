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
│   ├── auth.php               # Guard clause login (+ role, CSRF, Ingat Saya, rate limit di file pendukung)
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
├── auth/
│   ├── login.php, proses_login.php
│   ├── register.php, proses_register.php
│   └── logout.php
├── docs/wireframe.md
├── README.md
└── Dokumentasi/
    └── perbedaan-simpus.md
```
