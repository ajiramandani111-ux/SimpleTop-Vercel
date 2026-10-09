<?php
// Urutan pengecekan: login -> metode POST -> role admin -> CSRF -> validasi -> database.
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/koneksi.php';
require_once __DIR__ . '/../includes/validasi.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: tambah.php');
    exit;
}

require_admin('list.php');
csrf_verify('tambah.php');

['data' => $d, 'errors' => $errors] = validasi_bestseller($_POST);

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
        ':merk' => $d['merk'],
        ':seri' => $d['seri'],
        ':total_penjualan' => $d['total_penjualan'],
        ':rating' => $d['rating'],
    ]);
    $_SESSION['flash'] = ['type' => 'success', 'pesan' => 'Data best seller berhasil ditambahkan.'];
} catch (PDOException $e) {
    error_log('SimpleTop tambah bestseller error: ' . $e->getMessage());
    $_SESSION['flash'] = ['type' => 'error', 'pesan' => 'Gagal menyimpan data. Coba lagi nanti.'];
}

header('Location: list.php');
exit;
