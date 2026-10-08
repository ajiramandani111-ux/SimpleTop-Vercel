<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin('list.php'); // hanya admin yang boleh menambah produk
$page_title = "Tambah Best Seller";
include __DIR__ . '/../includes/header.php';
?>
<section>
    <h2>Tambah Data Best Seller</h2>
    <form action="proses_tambah.php" method="POST">
        <?php echo csrf_field(); ?>
        <div class="form-group">
            <label for="merk">Merk</label>
            <input type="text" id="merk" name="merk" placeholder="Contoh: Apple" required>
        </div>

        <div class="form-group">
            <label for="seri">Seri / Model</label>
            <input type="text" id="seri" name="seri" placeholder="Contoh: MacBook Air M3" required>
        </div>

        <div class="form-group">
            <label for="total_penjualan">Total Penjualan (unit)</label>
            <input type="number" id="total_penjualan" name="total_penjualan" min="0" required>
        </div>

        <div class="form-group">
            <label for="rating">Rating (1 - 5)</label>
            <input type="number" id="rating" name="rating" min="1" max="5" step="0.1" required>
        </div>

        <div class="form-actions">
            <button type="submit">Simpan</button>
            <a href="list.php" class="btn-batal">Batal</a>
        </div>
    </form>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
