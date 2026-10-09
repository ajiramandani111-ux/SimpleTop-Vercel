# Autentikasi Lanjutan SimpleTop

## 1. Kontrol akses berbasis role

<<<<<<< Updated upstream
| Aksi | Tamu | Petugas | Admin |
|---|---|---|---|
| Lihat Beranda, Katalog, Terlaris | ✅ | ✅ | ✅ |
| Tambah laptop / terlaris | ❌ → Login | ✅ | ✅ |
| Hapus laptop / terlaris (`hapus.php`) | ❌ → Login | ❌ (ditolak + pesan) | ✅ |

- `includes/auth.php` = guard login (di-include di **baris pertama** halaman).
- `require_admin()` (di `includes/auth_functions.php`) = pengecekan role setelah guard.
- Tombol Hapus hanya dirender untuk admin, **tetapi** server tetap memeriksa role + token CSRF
  (menyembunyikan tombol bukan pengamanan).
- Jadikan akun admin lewat SQL (lihat `sql/03_roles_remember_ratelimit.sql`).
=======
| Aksi | Tamu (belum login) | Customer (otomatis saat daftar) | Admin |
|---|---|---|---|
| Lihat Beranda, Katalog, Terlaris | ✅ | ✅ | ✅ |
| Pesan laptop (`laptop/pesan.php`) | ❌ → Login | ✅ | ✅ |
| Lihat pesanan | ❌ → Login | ✅ (milik sendiri) | ✅ (semua) |
| Tambah laptop / terlaris | ❌ → Login | ❌ (ditolak + pesan) | ✅ |
| Edit laptop / terlaris | ❌ → Login | ❌ (ditolak + pesan) | ✅ |
| Hapus (nonaktifkan) / Tampilkan kembali | ❌ → Login | ❌ (ditolak + pesan) | ✅ |

- Setiap akun yang mendaftar otomatis `customer`. Admin hanya bisa dibuat lewat SQL.
- `includes/auth.php` = guard login (di-include di **baris pertama** halaman).
- `require_admin()` (di `includes/auth_functions.php`) = pengecekan role setelah guard.
- Tombol disembunyikan sesuai role, **tetapi** server tetap memeriksa role + token CSRF
  (menyembunyikan tombol bukan pengamanan).
- Pemesanan memakai transaksi + `SELECT ... FOR UPDATE`, sehingga stok tidak bisa menjadi minus
  walau dua pembeli memesan bersamaan.
>>>>>>> Stashed changes

## 2. Ingat Saya — cara kerja & risiko

Cookie `remember_me` = `selector:validator` (30 hari, HttpOnly, SameSite=Lax, Secure di HTTPS).
Database (`remember_tokens`) hanya menyimpan **hash sha256 dari validator**, sehingga kebocoran
database tidak membocorkan cookie yang bisa langsung dipakai. Logout menghapus token dan cookie.

Dibanding `$_SESSION` biasa (cookie hilang saat browser ditutup):

- **Jendela serangan jauh lebih panjang.** Cookie yang dicuri (komputer bersama, malware, XSS pada
  situs lain di perangkat yang sama) berlaku 30 hari, bukan sampai browser ditutup.
- **Perangkat bersama berbahaya.** Jangan centang di warnet/komputer umum.
- **Token tidak otomatis batal bila password diubah** kecuali kita menghapus token user saat ganti
  password (fitur ganti password belum ada di SimpleTop; saat dibuat, hapus semua token user).
- **Tanpa rotasi token**, pencurian cookie tidak terdeteksi. Perbaikan lanjutan: ganti token setiap
  dipakai dan cabut semua token user jika selector yang sudah dipakai muncul lagi.
- **Aksi sensitif** (ganti password, hapus akun) sebaiknya tetap meminta password ulang walau
  user login lewat Ingat Saya.
- HTTPS wajib: tanpa flag `Secure`, cookie bisa tersadap di jaringan.

## 3. Pembatasan percobaan login

- Dihitung **per email** di tabel `login_attempts` (bukan `$_SESSION`: penyerang cukup membuang
  cookie session untuk mereset hitungan di session).
- Gagal ke-3 dan ke-4: muncul peringatan sisa percobaan. Gagal ke-5: akun dikunci 15 menit.
  Selama dikunci, password benar pun ditolak. Berhasil login = hitungan direset.
- Konstanta ada di `includes/rate_limit.php` (`LOGIN_MAX_ATTEMPTS`, dst).
- Kelemahan yang perlu disadari: kunci per-email bisa dipakai orang iseng untuk mengunci akun
  orang lain (DoS akun). Solusi produksi: kombinasikan dengan batas per-IP dan CAPTCHA.

## 4. Uji: database mati, `/laptop/tambah.php` tanpa login

Guard memakai `SIMPLETOP_DB_OPTIONAL` sehingga koneksi gagal tidak menghentikan halaman; tanpa
sesi login, pengunjung langsung di-redirect ke `auth/login.php`, dan halaman login tetap tampil.

Neon tidak bisa "distop" seperti PostgreSQL lokal, jadi simulasikan DB mati:

**Lokal:** jalankan server dengan DATABASE_URL yang menunjuk ke port mati:

```
DATABASE_URL="postgresql://x:x@127.0.0.1:1/x?sslmode=disable" php -S localhost:8000
```

**Di Vercel (preview):** ubah sementara `DATABASE_URL` menjadi host yang salah, redeploy,
lalu kembalikan setelah uji.

Hasil yang diharapkan:

| Buka | Hasil |
|---|---|
| `/laptop/tambah.php` (tanpa login) | Redirect ke `/auth/login.php` + pesan "Silakan masuk…", **bukan** error koneksi |
| `/auth/login.php` | Form tampil normal |
| Kirim form login | Pesan "Layanan sedang tidak tersedia…" |
| `/index.php`, `/laptop/list.php` | Tetap menampilkan "Koneksi database gagal…" (halaman ini memang butuh data) |
