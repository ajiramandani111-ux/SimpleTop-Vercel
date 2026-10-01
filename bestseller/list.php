<?php
$page_title = "Produk Terlaris";
include __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/koneksi.php';

$daftarBestSeller = $pdo->query("SELECT * FROM bestseller ORDER BY total_penjualan DESC")->fetchAll();
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
            <?php foreach ($daftarBestSeller as $i => $item):
                $cari = strtolower($item['merk'] . ' ' . $item['seri']);
                $rank = $i + 1;
            ?>
            <article class="ranking-item searchable-item" data-search="<?php echo htmlspecialchars($cari); ?>">
                <div class="rank-badge rank-<?php echo $rank <= 3 ? $rank : 'lain'; ?>">#<?php echo $rank; ?></div>
                <div class="ranking-info">
                    <h3 class="item-name"><?php echo htmlspecialchars($item['merk'] . ' ' . $item['seri']); ?></h3>
                    <p class="ranking-meta">
                        <?php echo (int) $item['total_penjualan']; ?> unit terjual
                        &bull; <span class="rating-star">&#9733;</span> <?php echo htmlspecialchars($item['rating']); ?>
                    </p>
                </div>
                <?php if ($user): ?>
                <div class="ranking-actions">
                    <button type="button">Edit</button>
                    <?php if (is_admin()): ?>
                    <form action="hapus.php" method="POST" class="form-hapus">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="id" value="<?php echo (int) $item['id']; ?>">
                        <button type="submit" class="btn-hapus">Hapus</button>
                    </form>
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
