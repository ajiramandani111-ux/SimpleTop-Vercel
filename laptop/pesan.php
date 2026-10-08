<?php
// Pesan laptop. Semua pengguna yang login (customer maupun admin) boleh memesan.
require_once __DIR__ . '/../includes/auth.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: list.php');
    exit;
}

csrf_verify('list.php');

$id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
$jumlah = filter_var($_POST['jumlah'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 100]]);

if ($id === false || $id < 1 || $jumlah === false) {
    $_SESSION['flash'] = ['type' => 'error', 'pesan' => 'Data pesanan tidak valid (jumlah 1-100).'];
    header('Location: list.php');
    exit;
}

$userId = (int) $_SESSION['user']['id'];

try {
    // Transaksi + FOR UPDATE: dua pembeli yang memesan bersamaan tidak bisa
    // sama-sama mengambil stok terakhir (mencegah stok menjadi minus).
    $pdo->beginTransaction();

    $stmt = $pdo->prepare('SELECT id, merk, seri, harga, stok FROM laptop WHERE id = :id FOR UPDATE');
    $stmt->execute([':id' => $id]);
    $laptop = $stmt->fetch();

    if (!$laptop) {
        $pdo->rollBack();
        $_SESSION['flash'] = ['type' => 'error', 'pesan' => 'Laptop tidak ditemukan.'];
        header('Location: list.php');
        exit;
    }

    if ((int) $laptop['stok'] < $jumlah) {
        $pdo->rollBack();
        $_SESSION['flash'] = ['type' => 'error', 'pesan' => 'Stok tidak mencukupi. Tersisa ' . (int) $laptop['stok'] . ' unit.'];
        header('Location: list.php');
        exit;
    }

    $pdo->prepare('UPDATE laptop SET stok = stok - :j WHERE id = :id')
        ->execute([':j' => $jumlah, ':id' => $id]);

    $harga = (int) $laptop['harga'];
    $pdo->prepare(
        'INSERT INTO orders (user_id, laptop_id, merk, seri, jumlah, harga_satuan, total)
         VALUES (:uid, :lid, :merk, :seri, :jumlah, :harga, :total)'
    )->execute([
        ':uid'    => $userId,
        ':lid'    => $id,
        ':merk'   => $laptop['merk'],
        ':seri'   => $laptop['seri'],
        ':jumlah' => $jumlah,
        ':harga'  => $harga,
        ':total'  => $harga * $jumlah,
    ]);

    $pdo->commit();

    $_SESSION['flash'] = ['type' => 'success', 'pesan' => 'Pesanan berhasil: ' . $jumlah . ' x ' . $laptop['merk'] . ' ' . $laptop['seri'] . '.'];
    header('Location: ../pesanan/list.php');
    exit;
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('SimpleTop pesan error: ' . $e->getMessage());
    $_SESSION['flash'] = ['type' => 'error', 'pesan' => 'Pesanan gagal diproses. Coba lagi nanti.'];
    header('Location: list.php');
    exit;
}
