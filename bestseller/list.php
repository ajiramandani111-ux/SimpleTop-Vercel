<?php
$page_title = "Produk Terlaris";
include __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/koneksi.php';

// Admin melihat semua (termasuk yang nonaktif); pengunjung & customer hanya yang aktif.
$sqlBest = is_admin()
    ? "SELECT * FROM bestseller ORDER BY total_penjualan DESC, id"
    : "SELECT * FROM bestseller WHERE aktif = TRUE ORDER BY total_penjualan DESC, id";
$daftarBestSeller = $pdo->query($sqlBest)->fetchAll();
$urutan = 0; // peringkat hanya dihitung untuk data aktif
?>
<section>
    <h2>Produk Terlaris</h2>

    <div class="search-box">
        <label for="search-input">Cari Laptop</label>
        <input type="text" id="search-input" placeholder="Cari merk atau seri laptop...">
    </div>

    <div class="ranking-list" id="catalog-container">
        <?php if (empty($daftarBestSeller)): ?>
            <p class="empty-state">Belum ada data produk terlaris.</p>
        <?php else: ?>
            <?php foreach ($daftarBestSeller as $item):
                $cari = strtolower($item['merk'] . ' ' . $item['seri']);
                $aktif = produk_aktif($item);
                if ($aktif) {
                    $urutan++;
                }
            ?>
            <article class="ranking-item searchable-item<?php echo $aktif ? '' : ' item-nonaktif'; ?>" data-search="<?php echo e($cari); ?>">
                <?php if ($aktif): ?>
                <div class="rank-badge rank-<?php echo $urutan <= 3 ? (int) $urutan : 'lain'; ?>">#<?php echo (int) $urutan; ?></div>
                <?php else: ?>
                <div class="rank-badge rank-lain" title="Dihapus dari peringkat">&ndash;</div>
                <?php endif; ?>
                <div class="ranking-info">
                    <h3 class="item-name"><?php echo e($item['merk'] . ' ' . $item['seri']); ?><?php if (!$aktif): ?> <span class="badge-stok badge-nonaktif">Dihapus</span><?php endif; ?></h3>
                    <p class="ranking-meta">
                        <?php echo (int) $item['total_penjualan']; ?> unit terjual
                        &bull; <span class="rating-star">&#9733;</span> <?php echo e($item['rating']); ?>
                    </p>
                </div>
<<<<<<< Updated upstream
                <?php if ($user): ?>
                <div class="ranking-actions">
                    <button type="button">Edit</button>
                    <?php if (is_admin()): ?>
=======
                <?php if (is_admin()): ?>
                <div class="ranking-actions">
                    <a href="edit.php?id=<?php echo (int) $item['id']; ?>" class="btn-edit">Edit</a>
                    <?php if ($aktif): ?>
>>>>>>> Stashed changes
                    <form action="hapus.php" method="POST" class="form-hapus">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="id" value="<?php echo (int) $item['id']; ?>">
                        <button type="submit" class="btn-hapus">Hapus</button>
                    </form>
<<<<<<< Updated upstream
=======
                    <?php else: ?>
                    <form action="aktifkan.php" method="POST" class="form-aktifkan">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="id" value="<?php echo (int) $item['id']; ?>">
                        <button type="submit" class="btn-tampilkan">Tampilkan</button>
                    </form>
>>>>>>> Stashed changes
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <p class="empty-state" id="no-result-msg" style="display:none;">Tidak ada produk yang cocok dengan pencarian.</p>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
