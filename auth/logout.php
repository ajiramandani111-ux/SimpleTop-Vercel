<?php
define('SIMPLETOP_DB_OPTIONAL', true);
require_once __DIR__ . '/../includes/session.php';

// Cabut token "Ingat Saya" (database + cookie) supaya tidak login otomatis lagi.
remember_forget($pdo instanceof PDO ? $pdo : null);

unset($_SESSION['user'], $_SESSION['next'], $_SESSION['csrf']);
session_regenerate_id(true);

$_SESSION['flash'] = ['type' => 'success', 'pesan' => 'Anda telah keluar.'];
header('Location: ../index.php');
exit;
