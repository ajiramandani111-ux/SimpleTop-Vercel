<?php
<<<<<<< Updated upstream
// Hapus laptop. Urutan pengecekan: login -> metode POST -> role admin -> CSRF.
=======
// Hapus (nonaktifkan) laptop. Urutan pengecekan: login -> metode POST -> role admin -> CSRF.
// Data TIDAK dihapus dari database: hanya kolom "aktif" yang diubah.
>>>>>>> Stashed changes
require_once __DIR__ . '/../includes/auth.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: list.php');
    exit;
}

<<<<<<< Updated upstream
// Kontrol akses berbasis role: petugas biasa hanya boleh melihat & menambah.
require_admin('list.php');

if (!csrf_valid()) {
    $_SESSION['flash'] = ['type' => 'error', 'pesan' => 'Permintaan tidak valid. Muat ulang halaman lalu coba lagi.'];
    header('Location: list.php');
    exit;
}
=======
require_admin('list.php');
csrf_verify('list.php');
>>>>>>> Stashed changes

$id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
if ($id === false || $id < 1) {
    $_SESSION['flash'] = ['type' => 'error', 'pesan' => 'ID laptop tidak valid.'];
    header('Location: list.php');
    exit;
}

try {
<<<<<<< Updated upstream
    $stmt = $pdo->prepare('DELETE FROM laptop WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $pesan = $stmt->rowCount() > 0
        ? ['type' => 'success', 'pesan' => 'Laptop berhasil dihapus.']
        : ['type' => 'error', 'pesan' => 'Laptop tidak ditemukan.'];
} catch (PDOException $e) {
    error_log('SimpleTop hapus laptop error: ' . $e->getMessage());
    $pesan = ['type' => 'error', 'pesan' => 'Gagal menghapus data. Coba lagi nanti.'];
=======
    $stmt = $pdo->prepare('UPDATE laptop SET aktif = FALSE WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $pesan = $stmt->rowCount() > 0
        ? ['type' => 'success', 'pesan' => 'Laptop dihapus dari katalog. Data tetap tersimpan dan bisa ditampilkan lagi.']
        : ['type' => 'error', 'pesan' => 'Laptop tidak ditemukan.'];
} catch (PDOException $e) {
    error_log('SimpleTop hapus laptop error: ' . $e->getMessage());
    $pesan = ['type' => 'error', 'pesan' => 'Aksi gagal diproses. Pastikan migrasi sql/05_soft_delete.sql sudah dijalankan.'];
>>>>>>> Stashed changes
}

$_SESSION['flash'] = $pesan;
header('Location: list.php');
exit;
