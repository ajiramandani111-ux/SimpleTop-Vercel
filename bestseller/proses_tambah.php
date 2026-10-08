<?php
// Urutan pengecekan: login -> metode POST -> role admin -> CSRF -> validasi -> database.
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/koneksi.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: tambah.php');
    exit;
}

require_admin('list.php');
csrf_verify('tambah.php');

$merk = trim((string) ($_POST['merk'] ?? ''));
$seri = trim((string) ($_POST['seri'] ?? ''));
$totalPenjualan = filter_var(trim((string) ($_POST['total_penjualan'] ?? '')), FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 1000000000]]);
$rating = filter_var(trim((string) ($_POST['rating'] ?? '')), FILTER_VALIDATE_FLOAT);

$errors = [];
if ($merk === '' || strlen($merk) > 100) {
    $errors[] = "Merk wajib diisi (maksimal 100 karakter).";
}
if ($seri === '' || strlen($seri) > 255) {
    $errors[] = "Seri/model wajib diisi (maksimal 255 karakter).";
}
if ($totalPenjualan === false) {
    $errors[] = "Total penjualan harus bilangan bulat, tidak boleh negatif.";
}
if ($rating === false || $rating < 1 || $rating > 5) {
    $errors[] = "Rating harus angka di antara 1-5.";
}

if (!empty($errors)) {
    $_SESSION['flash'] = ['type' => 'error', 'pesan' => implode(' ', $errors)];
    header('Location: tambah.php');
    exit;
}

try {
    $stmt = $pdo->prepare(
        "INSERT INTO bestseller (merk, seri, total_penjualan, rating)
         VALUES (:merk, :seri, :total_penjualan, :rating)"
    );
    $stmt->execute([
        ':merk' => $merk,
        ':seri' => $seri,
        ':total_penjualan' => $totalPenjualan,
        ':rating' => round($rating, 1),
    ]);
    $_SESSION['flash'] = ['type' => 'success', 'pesan' => 'Data best seller berhasil ditambahkan.'];
} catch (PDOException $e) {
    error_log('SimpleTop tambah bestseller error: ' . $e->getMessage());
    $_SESSION['flash'] = ['type' => 'error', 'pesan' => 'Gagal menyimpan data. Coba lagi nanti.'];
}

header('Location: list.php');
exit;
