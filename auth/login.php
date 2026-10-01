<?php
// Halaman ini harus tetap bisa tampil walau database sedang mati.
define('SIMPLETOP_DB_OPTIONAL', true);
require_once __DIR__ . '/../includes/session.php';

// Sudah login? Redirect sebelum ada output HTML.
if (is_logged_in()) {
    header('Location: ../index.php');
    exit;
}

$page_title = "Masuk";
$body_class = "auth-page";
include __DIR__ . '/../includes/header.php';
?>
<div class="auth-card">
    <div class="auth-icon"><i class="bi bi-person-circle"></i></div>
    <h2>Masuk</h2>
    <p class="auth-subtitle">Silakan masuk untuk berbelanja di SimpleTop</p>

    <form action="proses_login.php" method="POST" novalidate>
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
                <input type="password" id="password" name="password" placeholder="Masukkan kata sandi" required>
                <button type="button" class="toggle-password" data-target="password" aria-label="Tampilkan kata sandi">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
        </div>

        <div class="auth-options">
            <label for="ingat"><input type="checkbox" id="ingat" name="ingat" value="1"> Ingat saya (30 hari di perangkat ini)</label>
        </div>

        <button type="submit" class="btn-auth-submit">Masuk</button>
    </form>

    <p class="auth-footer-note">Belum punya akun? <a href="register.php">Daftar di sini</a></p>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
