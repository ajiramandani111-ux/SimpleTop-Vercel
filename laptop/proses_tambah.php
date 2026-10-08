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
$kategori = trim((string) ($_POST['kategori'] ?? ''));

// Angka divalidasi sebagai INTEGER murni (is_numeric menerima "1e3", " 5", "0x1A"
// bentuk aneh; FILTER_VALIDATE_INT + rentang jauh lebih ketat).
$batasTahun = (int) date('Y') + 1;
$tahun = filter_var(trim((string) ($_POST['tahun'] ?? '')), FILTER_VALIDATE_INT, ['options' => ['min_range' => 2000, 'max_range' => $batasTahun]]);
$harga = filter_var(trim((string) ($_POST['harga'] ?? '')), FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 100000000000]]);
$stok  = filter_var(trim((string) ($_POST['stok'] ?? '')), FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 100000]]);

$errors = [];
if ($merk === '' || strlen($merk) > 100) {
    $errors[] = "Merk wajib diisi (maksimal 100 karakter).";
}
if ($seri === '' || strlen($seri) > 255) {
    $errors[] = "Seri/model wajib diisi (maksimal 255 karakter).";
}
if (strlen($kategori) > 50) {
    $errors[] = "Kategori maksimal 50 karakter.";
}
if ($tahun === false) {
    $errors[] = "Tahun keluaran harus bilangan bulat antara 2000-{$batasTahun}.";
}
if ($harga === false) {
    $errors[] = "Harga harus bilangan bulat, tidak boleh negatif.";
}
if ($stok === false) {
    $errors[] = "Stok harus bilangan bulat antara 0-100000.";
}

if (!empty($errors)) {
    $_SESSION['flash'] = ['type' => 'error', 'pesan' => implode(' ', $errors)];
    header('Location: tambah.php');
    exit;
}

try {
    $stmt = $pdo->prepare(
        "INSERT INTO laptop (merk, seri, tahun, harga, stok, kategori)
         VALUES (:merk, :seri, :tahun, :harga, :stok, :kategori)"
    );
    $stmt->execute([
        ':merk' => $merk,
        ':seri' => $seri,
        ':tahun' => $tahun,
        ':harga' => $harga,
        ':stok' => $stok,
        ':kategori' => $kategori === '' ? null : $kategori,
    ]);
    $_SESSION['flash'] = ['type' => 'success', 'pesan' => 'Laptop berhasil ditambahkan.'];
} catch (PDOException $e) {
    error_log('SimpleTop tambah laptop error: ' . $e->getMessage());
    $_SESSION['flash'] = ['type' => 'error', 'pesan' => 'Gagal menyimpan data. Coba lagi nanti.'];
}

header('Location: list.php');
exit;
