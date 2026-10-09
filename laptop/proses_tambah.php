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

['data' => $d, 'errors' => $errors] = validasi_laptop($_POST);

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
        ':merk' => $d['merk'],
        ':seri' => $d['seri'],
        ':tahun' => $d['tahun'],
        ':harga' => $d['harga'],
        ':stok' => $d['stok'],
        ':kategori' => $d['kategori'],
    ]);
    $_SESSION['flash'] = ['type' => 'success', 'pesan' => 'Laptop berhasil ditambahkan.'];
} catch (PDOException $e) {
    error_log('SimpleTop tambah laptop error: ' . $e->getMessage());
    $_SESSION['flash'] = ['type' => 'error', 'pesan' => 'Gagal menyimpan data. Coba lagi nanti.'];
}

header('Location: list.php');
exit;
