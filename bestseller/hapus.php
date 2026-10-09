<?php
// Hapus (nonaktifkan) data best seller. Urutan pengecekan: login -> metode POST -> role admin -> CSRF.
// Data TIDAK dihapus dari database: hanya kolom "aktif" yang diubah.
require_once __DIR__ . '/../includes/auth.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: list.php');
    exit;
}

require_admin('list.php');
csrf_verify('list.php');

$id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
if ($id === false || $id < 1) {
    $_SESSION['flash'] = ['type' => 'error', 'pesan' => 'ID data best seller tidak valid.'];
    header('Location: list.php');
    exit;
}

try {
    $stmt = $pdo->prepare('UPDATE bestseller SET aktif = FALSE WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $pesan = $stmt->rowCount() > 0
        ? ['type' => 'success', 'pesan' => 'Data best seller dihapus dari katalog. Data tetap tersimpan dan bisa ditampilkan lagi.']
        : ['type' => 'error', 'pesan' => 'Data best seller tidak ditemukan.'];
} catch (PDOException $e) {
    error_log('SimpleTop hapus bestseller error: ' . $e->getMessage());
    $pesan = ['type' => 'error', 'pesan' => 'Aksi gagal diproses. Pastikan migrasi sql/05_soft_delete.sql sudah dijalankan.'];
}

$_SESSION['flash'] = $pesan;
header('Location: list.php');
exit;
