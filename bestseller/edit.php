<?php
// Semua pengecekan & redirect dilakukan SEBELUM header.php (belum ada output HTML).
require_once __DIR__ . '/../includes/auth.php';
require_admin('list.php'); // hanya admin yang boleh mengedit

$id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT);
if ($id === false || $id < 1) {
    header('Location: list.php');
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT * FROM bestseller WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('SimpleTop edit bestseller error: ' . $e->getMessage());
    $item = false;
}

if (!$item) {
    $_SESSION['flash'] = ['type' => 'error', 'pesan' => 'Data best seller tidak ditemukan.'];
    header('Location: list.php');
    exit;
}

$page_title = "Edit Best Seller";
include __DIR__ . '/../includes/header.php';
?>
<section>
    <h2>Edit Data Best Seller</h2>
    <form action="proses_edit.php" method="POST">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="id" value="<?php echo (int) $item['id']; ?>">

        <div class="form-group">
            <label for="merk">Merk</label>
            <input type="text" id="merk" name="merk" value="<?php echo e($item['merk']); ?>" maxlength="100" required>
        </div>

        <div class="form-group">
            <label for="seri">Seri / Model</label>
            <input type="text" id="seri" name="seri" value="<?php echo e($item['seri']); ?>" maxlength="255" required>
        </div>

        <div class="form-group">
            <label for="total_penjualan">Total Penjualan (unit)</label>
            <input type="number" id="total_penjualan" name="total_penjualan" min="0" value="<?php echo e($item['total_penjualan']); ?>" required>
        </div>

        <div class="form-group">
            <label for="rating">Rating (1 - 5)</label>
            <input type="number" id="rating" name="rating" min="1" max="5" step="0.1" value="<?php echo e($item['rating']); ?>" required>
        </div>

        <div class="form-actions">
            <button type="submit">Simpan Perubahan</button>
            <a href="list.php" class="btn-batal">Batal</a>
        </div>
    </form>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
