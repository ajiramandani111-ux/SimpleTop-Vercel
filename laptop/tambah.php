<?php
require_once __DIR__ . '/../includes/auth.php';
<<<<<<< Updated upstream
=======
require_admin('list.php'); // hanya admin yang boleh menambah produk
>>>>>>> Stashed changes
$page_title = "Tambah Laptop";
include __DIR__ . '/../includes/header.php';
?>
<section>
    <h2>Tambah Laptop</h2>
    <form action="proses_tambah.php" method="POST">
        <?php echo csrf_field(); ?>
        <div class="form-group">
            <label for="merk">Merk</label>
            <input type="text" id="merk" name="merk" placeholder="Contoh: ASUS" required>
        </div>

        <div class="form-group">
            <label for="seri">Seri / Model</label>
            <input type="text" id="seri" name="seri" placeholder="Contoh: ROG Strix G16" required>
        </div>

        <div class="form-group">
            <label for="tahun">Tahun Keluaran</label>
            <input type="number" id="tahun" name="tahun" min="2000" max="2026" required>
        </div>

        <div class="form-group">
            <label for="harga">Harga (Rp)</label>
            <input type="number" id="harga" name="harga" min="0" placeholder="Contoh: 15000000" required>
        </div>

        <div class="form-group">
            <label for="stok">Stok</label>
            <input type="number" id="stok" name="stok" min="0" required>
        </div>

        <div class="form-group">
            <label for="kategori">Kategori</label>
            <select id="kategori" name="kategori" required>
                <option value="" disabled selected>-- Pilih Kategori --</option>
                <option value="gaming">Gaming</option>
                <option value="ultrabook">Ultrabook</option>
                <option value="bisnis">Bisnis</option>
                <option value="2in1">2-in-1</option>
            </select>
        </div>

        <div class="form-actions">
            <button type="submit">Simpan</button>
            <a href="list.php" class="btn-batal">Batal</a>
        </div>
    </form>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
