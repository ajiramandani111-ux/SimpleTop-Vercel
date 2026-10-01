<?php
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/seed_data.php';

$__root = dirname(__DIR__);
$__scriptDir = dirname($_SERVER['SCRIPT_FILENAME']);
$__rel = ltrim(str_replace('\\', '/', substr($__scriptDir, strlen($__root))), '/');
$base = $__rel === '' ? '' : str_repeat('../', substr_count($__rel, '/') + 1);

$user = $_SESSION['user'] ?? null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SimpleTop<?php echo isset($page_title) ? ' | ' . $page_title : ' | Toko Laptop'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo $base; ?>assets/css/style.css">
</head>
<body class="<?php echo isset($body_class) ? $body_class : ''; ?>">
<div class="topbar">
    <span><i class="bi bi-truck"></i> Gratis ongkir se-Indonesia</span>
    <span><i class="bi bi-shield-check"></i> Garansi resmi 1 tahun</span>
    <span><i class="bi bi-credit-card"></i> Cicilan 0%</span>
</div>
<header>
    <h1><a href="<?php echo $base; ?>index.php">SimpleTop</a></h1>

    <div class="header-right">
        <?php if ($user): ?>
            <span class="nav-user">Halo, <?php echo htmlspecialchars($user['nama']); ?></span>
            <span class="role-badge role-<?php echo htmlspecialchars($user['role'] ?? 'petugas'); ?>"><?php echo htmlspecialchars($user['role'] ?? 'petugas'); ?></span>
            <a href="<?php echo $base; ?>auth/logout.php" class="nav-login-btn">Keluar</a>
        <?php else: ?>
            <a href="<?php echo $base; ?>auth/login.php" class="nav-login-btn">
                <i class="bi bi-person-circle"></i> Masuk
            </a>
            <a href="<?php echo $base; ?>auth/register.php" class="nav-login-btn nav-register-btn">Daftar</a>
        <?php endif; ?>
        <button type="button" id="nav-toggle-btn" class="nav-toggle-label" aria-label="Menu">&#9776;</button>
    </div>

    <nav>
        <ul>
            <li><a href="<?php echo $base; ?>index.php">Beranda</a></li>
            <li><a href="<?php echo $base; ?>laptop/list.php">Katalog Laptop</a></li>
            <?php if ($user): ?>
            <li><a href="<?php echo $base; ?>laptop/tambah.php">Tambah Laptop</a></li>
            <?php endif; ?>
            <li><a href="<?php echo $base; ?>bestseller/list.php">Terlaris</a></li>
            <?php if ($user): ?>
            <li><a href="<?php echo $base; ?>bestseller/tambah.php">Tambah Terlaris</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
<main>
<?php
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
if ($flash):
?>
    <p class="flash flash-<?php echo $flash['type']; ?>"><?php echo htmlspecialchars($flash['pesan']); ?></p>
<?php endif; ?>
