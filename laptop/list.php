<?php
$page_title = "Katalog Laptop";
include __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/koneksi.php';

$daftarLaptop = $pdo->query("SELECT * FROM laptop ORDER BY id")->fetchAll();

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
            ?>
            <article class="catalog-card searchable-item" data-search="<?php echo htmlspecialchars($cari); ?>">
                <div class="catalog-card-top">
                    <i class="bi bi-laptop catalog-icon"></i>
                    <span class="badge-stok <?php echo $kelasStok; ?>"><?php echo $labelStok; ?></span>
                </div>
                <h3 class="item-name"><?php echo htmlspecialchars($laptop['merk'] . ' ' . $laptop['seri']); ?></h3>
                <p class="catalog-meta">
                    Tahun <?php echo htmlspecialchars($laptop['tahun']); ?>
                    <?php if (!empty($laptop['kategori'])): ?>
                        &bull; <?php echo htmlspecialchars(ucfirst($laptop['kategori'])); ?>
                    <?php endif; ?>
                </p>
                <p class="catalog-harga">Rp<?php echo number_format((float) $laptop['harga'], 0, ',', '.'); ?></p>
                <p class="catalog-stok">Stok: <?php echo (int) $laptop['stok']; ?> unit</p>
                <?php if ($user): ?>
                <div class="catalog-actions">
                    <button type="button">Edit</button>
                    <?php if (is_admin()): ?>
                    <form action="hapus.php" method="POST" class="form-hapus">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="id" value="<?php echo (int) $laptop['id']; ?>">
                        <button type="submit" class="btn-hapus">Hapus</button>
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
