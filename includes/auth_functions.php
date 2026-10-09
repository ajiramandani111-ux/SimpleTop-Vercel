<?php
// Fungsi bantu autentikasi/otorisasi. Tidak mengakses database, jadi aman
// dipakai walau koneksi database sedang mati.

function is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
}

function is_logged_in(): bool
{
    return !empty($_SESSION['user']);
}

// Role akun yang sedang login. Session lama (sebelum kolom role ada)
<<<<<<< Updated upstream
// dianggap 'petugas' = hak paling rendah.
function user_role(): string
{
    return (string) ($_SESSION['user']['role'] ?? 'petugas');
=======
// dianggap 'customer' = hak paling rendah. (Role lama 'petugas' juga
// diperlakukan sebagai customer: hanya 'admin' yang punya hak kelola.)
function user_role(): string
{
    return (string) ($_SESSION['user']['role'] ?? 'customer');
>>>>>>> Stashed changes
}

function is_admin(): bool
{
    return is_logged_in() && user_role() === 'admin';
}

// Path halaman saat ini relatif terhadap root project, mis. "laptop/tambah.php".
function app_current_path(): string
{
    $root = dirname(__DIR__);
    $file = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_FILENAME'] ?? ''));
    $root = str_replace('\\', '/', $root);
    return ltrim(substr($file, strlen($root)), '/');
}

// Prefix relatif ke root project ("", "../", dst) sesuai kedalaman halaman.
function app_base(): string
{
    $dir = dirname(app_current_path());
    return $dir === '.' ? '' : str_repeat('../', substr_count($dir, '/') + 1);
}

// Hanya izinkan path internal sederhana (cegah open redirect).
function safe_next_path(?string $path): string
{
    if ($path !== null && preg_match('#^[A-Za-z0-9_\-]+(/[A-Za-z0-9_\-]+)*\.php$#', $path)) {
        return $path;
    }
    return 'index.php';
}

// Hanya admin yang lolos; selain itu diarahkan kembali dengan pesan.
function require_admin(string $redirectTo): void
{
    if (!is_admin()) {
        $_SESSION['flash'] = ['type' => 'error', 'pesan' => 'Akses ditolak: hanya admin yang boleh melakukan aksi ini.'];
        header('Location: ' . $redirectTo);
        exit;
    }
}
<<<<<<< Updated upstream

// ---- CSRF (dipakai untuk aksi hapus) ----
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . htmlspecialchars(csrf_token()) . '">';
}

function csrf_valid(): bool
{
    $kirim = $_POST['csrf'] ?? '';
    return is_string($kirim) && !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $kirim);
}
=======
>>>>>>> Stashed changes
