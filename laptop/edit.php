<?php
// Semua pengecekan & redirect dilakukan SEBELUM header.php (belum ada output HTML),
// supaya header('Location: ...') tetap bisa dipanggil.
require_once __DIR__ . '/../includes/auth.php';
require_admin('list.php'); // hanya admin yang boleh mengedit

$id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT);
if ($id === false || $id < 1) {
    header('Location: list.php');
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT * FROM laptop WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $laptop = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('SimpleTop edit laptop error: ' . $e->getMessage());
    $laptop = false;
}

if (!$laptop) {
    $_SESSION['flash'] = ['type' => 'error', 'pesan' => 'Laptop tidak ditemukan.'];
    header('Location: list.php');
    exit;
}

$kategoriPilihan = ['gaming' => 'Gaming', 'ultrabook' => 'Ultrabook', 'bisnis' => 'Bisnis', '2in1' => '2-in-1'];
$kategoriSaatIni = (string) ($laptop['kategori'] ?? '');

$page_title = "Edit Laptop";
include __DIR__ . '/../includes/header.php';
?>
<section>
    <h2>Edit Laptop</h2>
    <form action="proses_edit.php" method="POST">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="id" value="<?php echo (int) $laptop['id']; ?>">

        <div class="form-group">
            <label for="merk">Merk</label>
            <input type="text" id="merk" name="merk" value="<?php echo e($laptop['merk']); ?>" maxlength="100" required>
        </div>

        <div class="form-group">
            <label for="seri">Seri / Model</label>
            <input type="text" id="seri" name="seri" value="<?php echo e($laptop['seri']); ?>" maxlength="255" required>
        </div>

        <div class="form-group">
            <label for="tahun">Tahun Keluaran</label>
            <input type="number" id="tahun" name="tahun" min="2000" max="<?php echo (int) date('Y') + 1; ?>" value="<?php echo e($laptop['tahun']); ?>" required>
        </div>

        <div class="form-group">
            <label for="harga">Harga (Rp)</label>
            <input type="number" id="harga" name="harga" min="0" value="<?php echo e($laptop['harga']); ?>" required>
        </div>

        <div class="form-group">
            <label for="stok">Stok</label>
            <input type="number" id="stok" name="stok" min="0" value="<?php echo e($laptop['stok']); ?>" required>
        </div>

        <div class="form-group">
            <label for="kategori">Kategori</label>
            <select id="kategori" name="kategori" required>
                <?php foreach ($kategoriPilihan as $nilai => $label): ?>
                <option value="<?php echo e($nilai); ?>"<?php echo $kategoriSaatIni === $nilai ? ' selected' : ''; ?>><?php echo e($label); ?></option>
                <?php endforeach; ?>
                <?php if ($kategoriSaatIni !== '' && !isset($kategoriPilihan[$kategoriSaatIni])): ?>
                <option value="<?php echo e($kategoriSaatIni); ?>" selected><?php echo e($kategoriSaatIni); ?></option>
                <?php endif; ?>
            </select>
        </div>

        <div class="form-actions">
            <button type="submit">Simpan Perubahan</button>
            <a href="list.php" class="btn-batal">Batal</a>
        </div>
    </form>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
