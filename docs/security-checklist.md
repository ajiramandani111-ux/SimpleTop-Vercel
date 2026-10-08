# Security Checklist SimpleTop

Audit keamanan menyeluruh (Jobsheet 11) terhadap seluruh kode SimpleTop.
File pusat: `includes/helpers.php` (fungsi `e()`) dan `includes/csrf.php`
(`csrf_token()`, `csrf_field()`, `csrf_verify()`, `csrf_reset()`). Keduanya dimuat
otomatis oleh `includes/session.php` (jadi tersedia juga di file `proses_*.php`
yang tidak memuat `header.php`) dan juga di-require oleh `header.php`.

## Ringkasan

| # | Kerentanan | Status | Tempat utama |
|---|---|---|---|
| 1 | SQL Injection | ✅ Aman (tidak ada perubahan berarti) | Seluruh query memakai prepared statement |
| 2 | XSS | ✅ Diperbaiki | Semua output dibungkus `e()` |
| 3 | CSRF | ✅ Diperbaiki | Semua form POST + `csrf_verify()` |
| 4 | Validasi & sanitasi input | ✅ Diperketat | `laptop/` & `bestseller/proses_tambah.php`, `auth/proses_*.php` |
| 5 | Session fixation | ✅ Diperbaiki | `session_regenerate_id(true)` setelah login |

## 1. SQL Injection

- Semua query yang memuat input pengguna memakai `prepare()` + `execute([...])` dengan placeholder bernama.
  Nilai tidak pernah digabung ke string SQL.
- Satu-satunya SQL yang menyisipkan nilai lewat interpolasi adalah interval waktu di
  `includes/rate_limit.php` dan `includes/remember.php`, dan nilainya konstanta `int` buatan sendiri
  (`(int) LOGIN_LOCK_MINUTES`), bukan input pengguna.
- Query tanpa input (`SELECT * FROM laptop ORDER BY id`) memakai `query()` dan aman.
- Koneksi PDO memakai `ERRMODE_EXCEPTION`; detail error ditulis ke `error_log`, pengunjung hanya
  melihat pesan umum.

## 2. XSS

`e($nilai)` = `htmlspecialchars((string)$nilai, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')`.

Dipakai pada: nama/merk/seri/kategori produk, nama pengguna & role di navbar, pesan flash (yang bisa
memuat nama produk), `$page_title`, `$body_class`, nama pemesan dan produk di halaman pesanan, serta
nilai token CSRF. Angka yang dicetak dipaksa `(int)`.

Uji: daftar akun dengan nama `<script>alert(1)</script>` lalu login: nama harus tampil sebagai teks,
tidak ada popup. Lakukan hal sama untuk merk produk (sebagai admin).

Aturan: setiap `echo` data dari database/`$_GET`/`$_POST`/`$_SESSION` **wajib** lewat `e()` atau `(int)`.

## 3. CSRF

Form yang dilindungi (`csrf_field()` di form, `csrf_verify()` di server sebelum menyentuh database):

| Form | Penerima |
|---|---|
| Login | `auth/proses_login.php` |
| Register | `auth/proses_register.php` |
| Logout (kini POST, bukan link) | `auth/logout.php` |
| Tambah laptop / terlaris | `laptop/` & `bestseller/proses_tambah.php` |
| Hapus laptop / terlaris | `laptop/` & `bestseller/hapus.php` |
| Pesan laptop | `laptop/pesan.php` |

- Token 256-bit acak (`random_bytes`), disimpan di session, dibandingkan dengan `hash_equals()`.
- Token tidak valid → pesan "Formulir kedaluwarsa…" + redirect; database tidak disentuh.
- Token dibuang saat login dan logout (`csrf_reset()`).
- Lapisan tambahan: cookie session memakai `SameSite=Lax` dan `HttpOnly`.

Uji: buka DevTools, ubah nilai input `csrf_token` lalu kirim form. Harus ditolak.

## 4. Validasi & sanitasi input

- Angka divalidasi dengan `filter_var(..., FILTER_VALIDATE_INT, min/max)`. Sebelumnya `is_numeric()`
  menerima bentuk seperti `1e3` dan `" 5"`.
- Batas panjang sesuai kolom database (merk 100, seri 255, kategori 50, nama 150, email 255).
  Sebelumnya input kepanjangan menyebabkan error 500 dari PostgreSQL.
- Password 8-72 karakter (bcrypt hanya membaca 72 byte pertama); login menolak input > 1024 byte.
- Rating dibulatkan 1 desimal (kolom `NUMERIC(2,1)`).
- Insert dibungkus `try/catch`; gagal = pesan umum, bukan error 500.
- Urutan pada aksi sensitif: **login → metode POST → role → CSRF → validasi → database**.
  (Sebelumnya `proses_tambah.php` memvalidasi sebelum memeriksa role.)
- Otorisasi tetap dicek di server, bukan hanya dengan menyembunyikan tombol.

## 5. Session fixation

- `session_regenerate_id(true)` dipanggil setelah login berhasil (`auth/proses_login.php`),
  setelah login otomatis "Ingat Saya" (`includes/remember.php`), dan saat logout.
- Cookie session: `HttpOnly`, `SameSite=Lax`, `Secure` di HTTPS.
- `session.use_strict_mode` sengaja belum diaktifkan: SimpleTop memakai session handler database
  buatan sendiri, dan mode itu memerlukan `validate_sid` pada handler. Dicatat sebagai pekerjaan lanjutan.

## Temuan tambahan saat audit

| Temuan | Perbaikan |
|---|---|
| Logout lewat link GET (bisa dipicu situs lain) | Jadi form POST + CSRF |
| `is_numeric()` menerima format aneh | `FILTER_VALIDATE_INT` + rentang |
| Tidak ada batas panjang input | Batas sesuai kolom database |
| Insert tanpa `try/catch` | Ditangani, pesan umum |
| Pembatasan login & Remember Me | Lihat `docs/auth-lanjutan.md` |

## Belum dicakup (saran lanjutan)

- Header keamanan: `Content-Security-Policy`, `X-Content-Type-Options`, `Referrer-Policy`, HSTS.
- Pembatasan pendaftaran akun (anti spam) dan CAPTCHA.
- `session.use_strict_mode` (lihat bagian 5).
- Rotasi token Ingat Saya dan penghapusan token saat ganti password.
- Pembatasan per-IP selain per-email pada login.
