<?php
require_once __DIR__ . '/includes/session.php';

$nama = trim($_POST['nama'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$passwordConfirm = $_POST['password_confirm'] ?? '';

$errors = [];
if ($nama === '') {
    $errors[] = "Nama lengkap wajib diisi.";
}
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Email tidak valid.";
}
if (strlen($password) < 8) {
    $errors[] = "Kata sandi minimal 8 karakter.";
}
if ($password !== $passwordConfirm) {
    $errors[] = "Konfirmasi kata sandi tidak cocok.";
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
