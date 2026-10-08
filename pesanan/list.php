<?php
require_once __DIR__ . '/../includes/auth.php';
$page_title = is_admin() ? "Semua Pesanan" : "Pesanan Saya";
include __DIR__ . '/../includes/header.php';

$daftar = [];
$galat = false;
try {
    if (is_admin()) {
        $stmt = $pdo->query(
            'SELECT o.*, u.nama AS nama_pemesan
               FROM orders o JOIN users u ON u.id = o.user_id
              ORDER BY o.created_at DESC, o.id DESC LIMIT 200'
        );
    } else {
        $stmt = $pdo->prepare('SELECT * FROM orders WHERE user_id = :uid ORDER BY created_at DESC, id DESC LIMIT 200');
        $stmt->execute([':uid' => (int) $_SESSION['user']['id']]);
    }
    $daftar = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('SimpleTop pesanan error: ' . $e->getMessage());
    $galat = true;
}
?>
<section>
    <h2><?php echo e($page_title); ?></h2>

    <?php if ($galat): ?>
        <p class="empty-state">Data pesanan belum bisa ditampilkan. Pastikan migrasi <code>sql/04_customer_orders.sql</code> sudah dijalankan.</p>
    <?php elseif (empty($daftar)): ?>
        <p class="empty-state">Belum ada pesanan. Lihat <a href="../laptop/list.php">Katalog Laptop</a> untuk mulai memesan.</p>
    <?php else: ?>
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>No. Pesanan</th>
                    <th>Tanggal</th>
                    <?php if (is_admin()): ?><th>Pemesan</th><?php endif; ?>
                    <th>Produk</th>
                    <th>Jumlah</th>
                    <th>Total</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($daftar as $o): ?>
                <tr>
                    <td>#<?php echo (int) $o['id']; ?></td>
                    <td><?php echo e(date('d M Y H:i', strtotime($o['created_at']))); ?></td>
                    <?php if (is_admin()): ?><td><?php echo e($o['nama_pemesan']); ?></td><?php endif; ?>
                    <td><?php echo e($o['merk'] . ' ' . $o['seri']); ?></td>
                    <td><?php echo (int) $o['jumlah']; ?></td>
                    <td>Rp<?php echo number_format((float) $o['total'], 0, ',', '.'); ?></td>
                    <td><?php echo e(ucfirst($o['status'])); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
