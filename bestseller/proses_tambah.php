<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/koneksi.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: tambah.php');
    exit;
}

$merk = trim($_POST['merk'] ?? '');
$seri = trim($_POST['seri'] ?? '');
$totalPenjualan = $_POST['total_penjualan'] ?? '';
$rating = $_POST['rating'] ?? '';

$errors = [];
if ($merk === '') {
    $errors[] = "Merk wajib diisi.";
}
if ($seri === '') {
    $errors[] = "Seri/model wajib diisi.";
}
if (!is_numeric($totalPenjualan) || $totalPenjualan < 0) {
    $errors[] = "Total penjualan tidak boleh negatif.";
}
if (!is_numeric($rating) || $rating < 1 || $rating > 5) {
    $errors[] = "Rating harus di antara 1-5.";
}

if (!empty($errors)) {
    $_SESSION['flash'] = ['type' => 'error', 'pesan' => implode(' ', $errors)];
    header('Location: tambah.php');
    exit;
}

$stmt = $pdo->prepare(
    "INSERT INTO bestseller (merk, seri, total_penjualan, rating)
     VALUES (:merk, :seri, :total_penjualan, :rating)"
);
$stmt->execute([
    ':merk' => $merk,
    ':seri' => $seri,
    ':total_penjualan' => (int) $totalPenjualan,
    ':rating' => (float) $rating,
]);

$_SESSION['flash'] = ['type' => 'success', 'pesan' => 'Data best seller berhasil ditambahkan.'];
header('Location: list.php');
exit;
