<?php
// Urutan pengecekan: login -> metode POST -> role admin -> CSRF -> validasi -> database.
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/koneksi.php';
require_once __DIR__ . '/../includes/validasi.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: list.php');
    exit;
}

require_admin('list.php');

$id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
$halamanEdit = 'edit.php?id=' . (int) ($id === false ? 0 : $id);
csrf_verify($halamanEdit);

if ($id === false || $id < 1) {
    $_SESSION['flash'] = ['type' => 'error', 'pesan' => 'ID laptop tidak valid.'];
    header('Location: list.php');
    exit;
}

['data' => $d, 'errors' => $errors] = validasi_laptop($_POST);

if (!empty($errors)) {
    $_SESSION['flash'] = ['type' => 'error', 'pesan' => implode(' ', $errors)];
    header('Location: ' . $halamanEdit);
    exit;
}

try {
    $stmt = $pdo->prepare(
        "UPDATE laptop
            SET merk = :merk, seri = :seri, tahun = :tahun,
                harga = :harga, stok = :stok, kategori = :kategori
          WHERE id = :id"
    );
    $stmt->execute([
        ':merk' => $d['merk'],
        ':seri' => $d['seri'],
        ':tahun' => $d['tahun'],
        ':harga' => $d['harga'],
        ':stok' => $d['stok'],
        ':kategori' => $d['kategori'],
        ':id' => $id,
    ]);
    $_SESSION['flash'] = $stmt->rowCount() > 0
        ? ['type' => 'success', 'pesan' => 'Laptop berhasil diperbarui.']
        : ['type' => 'error', 'pesan' => 'Laptop tidak ditemukan.'];
} catch (PDOException $e) {
    error_log('SimpleTop edit laptop error: ' . $e->getMessage());
    $_SESSION['flash'] = ['type' => 'error', 'pesan' => 'Gagal menyimpan perubahan. Coba lagi nanti.'];
}

header('Location: list.php');
exit;
