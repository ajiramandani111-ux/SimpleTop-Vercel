<?php
define('SIMPLETOP_DB_OPTIONAL', true);
require_once __DIR__ . '/../includes/session.php';

// Logout hanya lewat POST + token CSRF: link/gambar di situs lain tidak bisa
// memaksa pengguna keluar.
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: ../index.php');
    exit;
}
csrf_verify('../index.php');

// Cabut token "Ingat Saya" (database + cookie) supaya tidak login otomatis lagi.
remember_forget($pdo instanceof PDO ? $pdo : null);

unset($_SESSION['user'], $_SESSION['next']);
csrf_reset();
session_regenerate_id(true);

$_SESSION['flash'] = ['type' => 'success', 'pesan' => 'Anda telah keluar.'];
header('Location: ../index.php');
exit;
