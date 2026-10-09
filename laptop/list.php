<?php
$page_title = "Katalog Laptop";
include __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/koneksi.php';

// Admin melihat semua (termasuk yang nonaktif); pengunjung & customer hanya yang aktif.
$sqlLaptop = is_admin()
    ? "SELECT * FROM laptop ORDER BY id"
    : "SELECT * FROM laptop WHERE aktif = TRUE ORDER BY id";
$daftarLaptop = $pdo->query($sqlLaptop)->fetchAll();

function statusStok($stok) {
    if ($stok <= 0) return ['Habis', 'badge-habis'];
    if ($stok <= 4) return ['Stok Terbatas', 'badge-terbatas'];
    return ['Tersedia', 'badge-tersedia'];
}
?>
<section>
    <h2>Katalog Laptop</h2>

    <div class="search-box">
        <label for="search-input">Cari Laptop</label>
        <input type="text" id="search-input" placeholder="Cari merk atau seri laptop...">
    </div>

    <div class="catalog-grid" id="catalog-container">
        <?php if (empty($daftarLaptop)): ?>
            <p class="empty-state">Belum ada data laptop. Silakan tambah lewat menu "Tambah Laptop".</p>
        <?php else: ?>
            <?php foreach ($daftarLaptop as $laptop):
                [$labelStok, $kelasStok] = statusStok((int) $laptop['stok']);
                $cari = strtolower($laptop['merk'] . ' ' . $laptop['seri'] . ' ' . ($laptop['kategori'] ?? ''));
                $aktif = produk_aktif($laptop);
            ?>
            <article class="catalog-card searchable-item<?php echo $aktif ? '' : ' item-nonaktif'; ?>" data-search="<?php echo e($cari); ?>">
                <div class="catalog-card-top">
                    <i class="bi bi-laptop catalog-icon"></i>
                    <span>
                        <?php if (!$aktif): ?><span class="badge-stok badge-nonaktif">Dihapus</span><?php endif; ?>
                        <span class="badge-stok <?php echo $kelasStok; ?>"><?php echo $labelStok; ?></span>
                    </span>
                </div>
                <h3 class="item-name"><?php echo e($laptop['merk'] . ' ' . $laptop['seri']); ?></h3>
                <p class="catalog-meta">
                    Tahun <?php echo e($laptop['tahun']); ?>
                    <?php if (!empty($laptop['kategori'])): ?>
                        &bull; <?php echo e(ucfirst($laptop['kategori'])); ?>
                    <?php endif; ?>
                </p>
                <p class="catalog-harga">Rp<?php echo number_format((float) $laptop['harga'], 0, ',', '.'); ?></p>
                <p class="catalog-stok">Stok: <?php echo (int) $laptop['stok']; ?> unit</p>
                <?php if (!$aktif): ?>
                <p class="catalog-login-hint">Tidak tampil di katalog publik.</p>
                <?php elseif (!$user): ?>
                <a href="<?php echo $base; ?>auth/login.php" class="catalog-login-hint">Masuk untuk memesan</a>
                <?php elseif ((int) $laptop['stok'] > 0): ?>
                <form action="pesan.php" method="POST" class="form-pesan">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="id" value="<?php echo (int) $laptop['id']; ?>">
                    <label class="sr-only" for="jumlah-<?php echo (int) $laptop['id']; ?>">Jumlah</label>
                    <input type="number" id="jumlah-<?php echo (int) $laptop['id']; ?>" name="jumlah" value="1" min="1" max="<?php echo (int) $laptop['stok']; ?>" required>
                    <button type="submit" class="btn-pesan"><i class="bi bi-bag-plus"></i> Pesan</button>
                </form>
                <?php else: ?>
                <p class="catalog-login-hint">Stok habis</p>
                <?php endif; ?>
                <?php if (is_admin()): ?>
                <div class="catalog-actions">
                    <a href="edit.php?id=<?php echo (int) $laptop['id']; ?>" class="btn-edit">Edit</a>
                    <?php if ($aktif): ?>
                    <form action="hapus.php" method="POST" class="form-hapus">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="id" value="<?php echo (int) $laptop['id']; ?>">
                        <button type="submit" class="btn-hapus">Hapus</button>
                    </form>
                    <?php else: ?>
                    <form action="aktifkan.php" method="POST" class="form-aktifkan">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="id" value="<?php echo (int) $laptop['id']; ?>">
                        <button type="submit" class="btn-tampilkan">Tampilkan</button>
                    </form>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <p class="empty-state" id="no-result-msg" style="display:none;">Tidak ada laptop yang cocok dengan pencarian.</p>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
