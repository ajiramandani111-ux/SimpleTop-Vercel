<?php
$page_title = "Beranda";
include __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/koneksi.php';

$totalLaptop = (int) $pdo->query("SELECT COUNT(*) AS jumlah FROM laptop WHERE aktif = TRUE")->fetch()['jumlah'];
$totalBestSeller = (int) $pdo->query("SELECT COUNT(*) AS jumlah FROM bestseller WHERE aktif = TRUE")->fetch()['jumlah'];
$totalStok = (int) $pdo->query("SELECT COALESCE(SUM(stok), 0) AS jumlah FROM laptop WHERE aktif = TRUE")->fetch()['jumlah'];

$produkPilihan = $pdo->query("SELECT * FROM laptop WHERE aktif = TRUE ORDER BY id LIMIT 3")->fetchAll();
?>
<section class="hero">
    <h2>Laptop Terbaik, Harga Bersahabat</h2>
    <p>SimpleTop menyediakan laptop original dengan garansi resmi untuk kerja, kuliah, dan gaming.</p>
    <div class="hero-actions">
        <a href="laptop/list.php" class="btn-hero btn-hero-primary">
            <i class="bi bi-laptop"></i> Jelajahi Katalog
        </a>
        <a href="bestseller/list.php" class="btn-hero btn-hero-outline">
            <i class="bi bi-trophy-fill"></i> Produk Terlaris
        </a>
    </div>
</section>

<section class="ringkasan-strip">
    <article class="ringkasan-card">
        <span class="ringkasan-angka"><?php echo (int) $totalLaptop; ?></span>
        <span class="ringkasan-label">Model Laptop</span>
    </article>
    <article class="ringkasan-card">
        <span class="ringkasan-angka"><?php echo (int) $totalStok; ?></span>
        <span class="ringkasan-label">Unit Siap Kirim</span>
    </article>
    <article class="ringkasan-card">
        <span class="ringkasan-angka"><?php echo (int) $totalBestSeller; ?></span>
        <span class="ringkasan-label">Produk Terlaris</span>
    </article>
</section>

<section class="fitur-strip">
    <div class="fitur-item">
        <i class="bi bi-cpu"></i>
        <h3>Unit Original</h3>
        <p>Langsung dari distributor resmi, bukan rekondisi.</p>
    </div>
    <div class="fitur-item">
        <i class="bi bi-headset"></i>
        <h3>Konsultasi Gratis</h3>
        <p>Bingung pilih spek? Tim kami bantu rekomendasi.</p>
    </div>
    <div class="fitur-item">
        <i class="bi bi-arrow-repeat"></i>
        <h3>Tukar Tambah</h3>
        <p>Laptop lama bisa ditukar dengan yang baru.</p>
    </div>
</section>

<section>
    <h2>Produk Pilihan</h2>
    <div class="produk-grid">
        <?php foreach ($produkPilihan as $laptop): ?>
        <article class="produk-card">
            <i class="bi bi-laptop produk-icon"></i>
            <h3><?php echo e($laptop['merk'] . ' ' . $laptop['seri']); ?></h3>
            <p class="produk-harga">Rp<?php echo number_format((float) $laptop['harga'], 0, ',', '.'); ?></p>
            <p class="produk-stok">Stok: <?php echo (int) $laptop['stok']; ?></p>
        </article>
        <?php endforeach; ?>
    </div>
    <div class="text-center-link">
        <a href="laptop/list.php">Lihat Semua Produk &rarr;</a>
    </div>
</section>
<?php
include __DIR__ . '/includes/footer.php';
?>
