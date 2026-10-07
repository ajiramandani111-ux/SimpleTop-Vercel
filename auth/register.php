<?php
// Halaman ini harus tetap bisa tampil walau database sedang mati.
define('SIMPLETOP_DB_OPTIONAL', true);
require_once __DIR__ . '/../includes/session.php';

// Sudah login? Redirect sebelum ada output HTML.
if (is_logged_in()) {
    header('Location: ../index.php');
    exit;
}

$page_title = "Daftar";
$body_class = "auth-page";
include __DIR__ . '/../includes/header.php';
?>
<div class="auth-card">
    <div class="auth-icon"><i class="bi bi-person-plus-fill"></i></div>
    <h2>Buat Akun</h2>
    <p class="auth-subtitle">Daftar untuk mulai belanja laptop di SimpleTop</p>

    <form action="proses_register.php" method="POST" novalidate>
        <div class="form-group">
            <label for="nama">Nama Lengkap</label>
            <div class="input-icon-wrap">
                <i class="bi bi-person bi-icon-left"></i>
                <input type="text" id="nama" name="nama" placeholder="Nama lengkap Anda" required>
            </div>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <div class="input-icon-wrap">
                <i class="bi bi-envelope bi-icon-left"></i>
                <input type="email" id="email" name="email" placeholder="nama@email.com" required>
            </div>
        </div>

        <div class="form-group">
            <label for="password">Kata Sandi</label>
            <div class="input-icon-wrap">
                <i class="bi bi-lock bi-icon-left"></i>
                <input type="password" id="password" name="password" placeholder="Minimal 8 karakter" required>
                <button type="button" class="toggle-password" data-target="password" aria-label="Tampilkan kata sandi">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
        </div>

        <div class="form-group">
            <label for="password_confirm">Konfirmasi Kata Sandi</label>
            <div class="input-icon-wrap">
                <i class="bi bi-lock-fill bi-icon-left"></i>
                <input type="password" id="password_confirm" name="password_confirm" placeholder="Ulangi kata sandi" required>
                <button type="button" class="toggle-password" data-target="password_confirm" aria-label="Tampilkan kata sandi">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
        </div>

        <button type="submit" class="btn-auth-submit">Daftar</button>
    </form>

    <p class="auth-footer-note">Sudah punya akun? <a href="login.php">Masuk di sini</a></p>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
