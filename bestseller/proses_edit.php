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
    $_SESSION['flash'] = ['type' => 'error', 'pesan' => 'ID data tidak valid.'];
    header('Location: list.php');
    exit;
}

['data' => $d, 'errors' => $errors] = validasi_bestseller($_POST);

if (!empty($errors)) {
    $_SESSION['flash'] = ['type' => 'error', 'pesan' => implode(' ', $errors)];
    header('Location: ' . $halamanEdit);
    exit;
}

try {
    $stmt = $pdo->prepare(
        "UPDATE bestseller
            SET merk = :merk, seri = :seri,
                total_penjualan = :total_penjualan, rating = :rating
          WHERE id = :id"
    );
    $stmt->execute([
        ':merk' => $d['merk'],
        ':seri' => $d['seri'],
        ':total_penjualan' => $d['total_penjualan'],
        ':rating' => $d['rating'],
        ':id' => $id,
    ]);
    $_SESSION['flash'] = $stmt->rowCount() > 0
        ? ['type' => 'success', 'pesan' => 'Data best seller berhasil diperbarui.']
        : ['type' => 'error', 'pesan' => 'Data best seller tidak ditemukan.'];
} catch (PDOException $e) {
    error_log('SimpleTop edit bestseller error: ' . $e->getMessage());
    $_SESSION['flash'] = ['type' => 'error', 'pesan' => 'Gagal menyimpan perubahan. Coba lagi nanti.'];
}

header('Location: list.php');
exit;
