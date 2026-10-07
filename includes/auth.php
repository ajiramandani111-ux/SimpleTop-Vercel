<?php
// Guard clause: WAJIB di-include di baris paling atas halaman yang butuh login
// (sebelum header.php mengeluarkan output apa pun) agar header('Location: ...')
// masih bisa dipanggil.
//
// Urutan sengaja: cek login DULU, baru pastikan database tersedia. Karena
// SIMPLETOP_DB_OPTIONAL didefinisikan sebelum session.php dimuat, koneksi yang
// gagal tidak langsung die(); sesi kosong => belum login => redirect ke Login.
// Jadi saat database mati, pengunjung tanpa login tetap diarahkan ke Login,
// bukan melihat error koneksi.

if (!defined('SIMPLETOP_DB_OPTIONAL')) {
    define('SIMPLETOP_DB_OPTIONAL', true);
}
require_once __DIR__ . '/session.php';

if (!is_logged_in()) {
    // Ingat halaman tujuan (hanya untuk GET) agar setelah login kembali ke sana.
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
        $_SESSION['next'] = app_current_path();
    }
    $_SESSION['flash'] = ['type' => 'error', 'pesan' => 'Silakan masuk terlebih dahulu untuk mengakses halaman itu.'];
    header('Location: ' . app_base() . 'auth/login.php');
    exit;
}

// Sudah login, tetapi halaman ini butuh database.
if (!($pdo instanceof PDO)) {
    http_response_code(503);
    die('Layanan database sedang tidak tersedia. Coba lagi beberapa saat.');
}
