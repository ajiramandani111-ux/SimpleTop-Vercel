<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/koneksi.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: tambah.php');
    exit;
}

$merk = trim($_POST['merk'] ?? '');
$seri = trim($_POST['seri'] ?? '');
$tahun = $_POST['tahun'] ?? '';
$harga = $_POST['harga'] ?? '';
$stok = $_POST['stok'] ?? '';
$kategori = trim($_POST['kategori'] ?? '');

$errors = [];
if ($merk === '') {
    $errors[] = "Merk wajib diisi.";
}
if ($seri === '') {
    $errors[] = "Seri/model wajib diisi.";
}
if (!is_numeric($tahun) || $tahun < 2000 || $tahun > 2026) {
    $errors[] = "Tahun keluaran harus di antara 2000-2026.";
}
if (!is_numeric($harga) || $harga < 0) {
    $errors[] = "Harga tidak boleh negatif.";
}
if (!is_numeric($stok) || $stok < 0) {
    $errors[] = "Stok tidak boleh negatif.";
}

if (!empty($errors)) {
    $_SESSION['flash'] = ['type' => 'error', 'pesan' => implode(' ', $errors)];
    header('Location: tambah.php');
    exit;
}

$stmt = $pdo->prepare(
    "INSERT INTO laptop (merk, seri, tahun, harga, stok, kategori)
     VALUES (:merk, :seri, :tahun, :harga, :stok, :kategori)"
);
$stmt->execute([
    ':merk' => $merk,
    ':seri' => $seri,
    ':tahun' => (int) $tahun,
    ':harga' => (int) $harga,
    ':stok' => (int) $stok,
    ':kategori' => $kategori,
]);

$_SESSION['flash'] = ['type' => 'success', 'pesan' => 'Laptop berhasil ditambahkan.'];
header('Location: list.php');
exit;
