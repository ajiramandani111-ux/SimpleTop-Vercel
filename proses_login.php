<?php
require_once __DIR__ . '/includes/session.php';

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

$errors = [];
if ($email === '') {
    $errors[] = "Email wajib diisi.";
}
if ($password === '') {
    $errors[] = "Kata sandi wajib diisi.";
}

if (!empty($errors)) {
    $_SESSION['flash'] = ['type' => 'error', 'pesan' => implode(' ', $errors)];
    header('Location: login.php');
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT nama, email, password_hash FROM users WHERE LOWER(email) = LOWER(:email)');
    $stmt->execute([':email' => $email]);
    $userDitemukan = $stmt->fetch();
} catch (PDOException $e) {
    error_log('SimpleTop login error: ' . $e->getMessage());
    $_SESSION['flash'] = ['type' => 'error', 'pesan' => 'Terjadi kesalahan pada server. Coba lagi nanti.'];
    header('Location: login.php');
    exit;
}

if (!$userDitemukan || !password_verify($password, $userDitemukan['password_hash'])) {
    $_SESSION['flash'] = ['type' => 'error', 'pesan' => 'Email atau kata sandi salah, atau akun belum terdaftar.'];
    header('Location: login.php');
    exit;
}

session_regenerate_id(true);
$_SESSION['user'] = ['nama' => $userDitemukan['nama'], 'email' => $userDitemukan['email']];
$_SESSION['flash'] = ['type' => 'success', 'pesan' => 'Login berhasil. Selamat datang, ' . $userDitemukan['nama'] . '!'];
header('Location: index.php');
exit;
