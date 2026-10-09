<?php
// Database boleh gagal tersambung: ditangani dengan pesan ramah di bawah.
define('SIMPLETOP_DB_OPTIONAL', true);
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/rate_limit.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: login.php');
    exit;
}

csrf_verify('login.php'); // sebelum menyentuh database

$email = trim((string) ($_POST['email'] ?? ''));
$password = (string) ($_POST['password'] ?? '');
$ingat = !empty($_POST['ingat']);

$errors = [];
if ($email === '') {
    $errors[] = "Email wajib diisi.";
}
if ($password === '') {
    $errors[] = "Kata sandi wajib diisi.";
}

if (strlen($email) > 255 || strlen($password) > 1024) {
    $errors[] = "Email atau kata sandi terlalu panjang.";
}

if (!empty($errors)) {
    $_SESSION['flash'] = ['type' => 'error', 'pesan' => implode(' ', $errors)];
    header('Location: login.php');
    exit;
}

if (!($pdo instanceof PDO)) {
    $_SESSION['flash'] = ['type' => 'error', 'pesan' => 'Layanan sedang tidak tersedia. Coba lagi beberapa saat.'];
    header('Location: login.php');
    exit;
}

$identifier = login_identifier($email);

// 1) Akun sedang dikunci? Tolak SEBELUM memeriksa password
//    (password benar pun tidak diterima selama masa kunci).
$status = login_lock_status($pdo, $identifier);
if ($status['locked']) {
    $_SESSION['flash'] = [
        'type' => 'error',
        'pesan' => 'Terlalu banyak percobaan login gagal. Coba lagi dalam '
            . login_format_sisa_waktu($status['sisa_detik']) . '.',
    ];
    header('Location: login.php');
    exit;
}

// 2) Cari akun
try {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE LOWER(email) = LOWER(:email)');
    $stmt->execute([':email' => $email]);
    $userDitemukan = $stmt->fetch();
} catch (PDOException $e) {
    error_log('SimpleTop login error: ' . $e->getMessage());
    $_SESSION['flash'] = ['type' => 'error', 'pesan' => 'Terjadi kesalahan pada server. Coba lagi nanti.'];
    header('Location: login.php');
    exit;
}

// 3) Gagal: catat percobaan, beri peringatan bila mendekati batas
if (!$userDitemukan || !password_verify($password, $userDitemukan['password_hash'])) {
    $gagal = login_record_failure($pdo, $identifier);

    if ($gagal['locked']) {
        $pesan = 'Terlalu banyak percobaan login gagal. Akun dikunci sementara selama '
            . LOGIN_LOCK_MINUTES . ' menit.';
    } elseif ($gagal['attempts'] >= LOGIN_WARN_AFTER) {
        $pesan = 'Email atau kata sandi salah. Peringatan: sisa ' . $gagal['sisa']
            . ' percobaan sebelum akun dikunci sementara.';
    } else {
        $pesan = 'Email atau kata sandi salah, atau akun belum terdaftar.';
    }

    $_SESSION['flash'] = ['type' => 'error', 'pesan' => $pesan];
    header('Location: login.php');
    exit;
}

// 4) Berhasil
login_clear($pdo, $identifier);

$next = safe_next_path($_SESSION['next'] ?? null);

// Cegah session fixation: ID sesi diganti SETELAH login berhasil, dan token
// CSRF lama dibuang (akan dibuat baru untuk sesi baru).
session_regenerate_id(true);
csrf_reset();
$_SESSION['user'] = [
    'id'    => (int) $userDitemukan['id'],
    'nama'  => $userDitemukan['nama'],
    'email' => $userDitemukan['email'],
    'role'  => $userDitemukan['role'] ?? 'customer',
];
unset($_SESSION['next']);

if ($ingat) {
    remember_issue($pdo, (int) $userDitemukan['id']);
}

$_SESSION['flash'] = ['type' => 'success', 'pesan' => 'Login berhasil. Selamat datang, ' . $userDitemukan['nama'] . '!'];
header('Location: ../' . $next);
exit;
