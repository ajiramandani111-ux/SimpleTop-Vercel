<?php
define('SIMPLETOP_DB_OPTIONAL', true);
require_once __DIR__ . '/../includes/session.php';
<<<<<<< Updated upstream

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: register.php');
    exit;
}
=======
>>>>>>> Stashed changes

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: register.php');
    exit;
}

csrf_verify('register.php'); // sebelum menyentuh database

$nama = trim((string) ($_POST['nama'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$password = (string) ($_POST['password'] ?? '');
$passwordConfirm = (string) ($_POST['password_confirm'] ?? '');

$errors = [];
if ($nama === '') {
    $errors[] = "Nama lengkap wajib diisi.";
}
if (strlen($nama) > 150) {
    $errors[] = "Nama maksimal 150 karakter.";
}
if (strlen($email) > 255) {
    $errors[] = "Email maksimal 255 karakter.";
}
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Email tidak valid.";
}
if (strlen($password) < 8) {
    $errors[] = "Kata sandi minimal 8 karakter.";
}
// bcrypt hanya membaca 72 byte pertama; batasi agar tidak menyesatkan & mencegah input raksasa.
if (strlen($password) > 72) {
    $errors[] = "Kata sandi maksimal 72 karakter.";
}
if ($password !== $passwordConfirm) {
    $errors[] = "Konfirmasi kata sandi tidak cocok.";
}

if (empty($errors) && !($pdo instanceof PDO)) {
    $errors[] = "Layanan sedang tidak tersedia. Coba lagi beberapa saat.";
}

if (empty($errors)) {
    try {
        $cek = $pdo->prepare('SELECT 1 FROM users WHERE LOWER(email) = LOWER(:email)');
        $cek->execute([':email' => $email]);
        if ($cek->fetch()) {
            $errors[] = "Email sudah terdaftar. Silakan masuk.";
        }
    } catch (PDOException $e) {
        error_log('SimpleTop register check error: ' . $e->getMessage());
        $errors[] = "Terjadi kesalahan pada server. Coba lagi nanti.";
    }
}

if (!empty($errors)) {
    $_SESSION['flash'] = ['type' => 'error', 'pesan' => implode(' ', $errors)];
    header('Location: register.php');
    exit;
}

try {
    $stmt = $pdo->prepare('INSERT INTO users (nama, email, password_hash) VALUES (:nama, :email, :hash)');
    $stmt->execute([
        ':nama'  => $nama,
        ':email' => $email,
        ':hash'  => password_hash($password, PASSWORD_DEFAULT),
    ]);
} catch (PDOException $e) {
    error_log('SimpleTop register insert error: ' . $e->getMessage());
    // 23505 = unique_violation (email didaftarkan bersamaan)
    $pesan = $e->getCode() === '23505'
        ? 'Email sudah terdaftar. Silakan masuk.'
        : 'Terjadi kesalahan pada server. Coba lagi nanti.';
    $_SESSION['flash'] = ['type' => 'error', 'pesan' => $pesan];
    header('Location: register.php');
    exit;
}

$_SESSION['flash'] = ['type' => 'success', 'pesan' => 'Registrasi berhasil! Silakan masuk.'];
header('Location: login.php');
exit;
