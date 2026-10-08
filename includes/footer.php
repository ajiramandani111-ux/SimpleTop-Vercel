</main>

<footer>
    <div class="footer-brand">SimpleTop</div>
    <p>Toko laptop terpercaya &mdash; baru, bergaransi, harga bersahabat.</p>
    <p>&copy; 2026 SimpleTop. Semua hak dilindungi.</p>
</footer>

<script src="<?php echo $base; ?>assets/js/app.js"></script>

<?php if (!empty($extra_scripts)): ?>
    <?php foreach ($extra_scripts as $src): ?>
        <script src="<?php echo e($src); ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

</body>
</html>
