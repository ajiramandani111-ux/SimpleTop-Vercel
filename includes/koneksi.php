<?php
// Koneksi PDO PostgreSQL.
// Production (Vercel): set DATABASE_URL or PGHOST/PG* in Project Settings > Environment Variables.
// Local Laragon: values below are only fallbacks for local development.

// Halaman yang mendefinisikan SIMPLETOP_DB_OPTIONAL (guard auth.php, login,
// register, logout) tidak boleh menampilkan error koneksi ke pengunjung saat
// database mati: $pdo diisi null dan halaman memutuskan sendiri apa yang
// ditampilkan (mis. redirect ke login). Halaman lain tetap berhenti dengan pesan.
if (!function_exists('simpletop_db_gagal')) {
    function simpletop_db_gagal(string $pesanPengunjung): void
    {
        if (defined('SIMPLETOP_DB_OPTIONAL')) {
            return;
        }
        die($pesanPengunjung);
    }
}

$pdo = null;
$databaseUrl = getenv('DATABASE_URL');
$db_host = null;
$db_port = 5432;
$db_name = null;
$db_user = null;
$db_pass = null;
$sslmode = getenv('PGSSLMODE') ?: null;

if ($databaseUrl) {
    $parts = parse_url($databaseUrl);

    if ($parts === false || empty($parts['host']) || empty($parts['path'])) {
        error_log('SimpleTop: DATABASE_URL tidak valid.');
        simpletop_db_gagal('DATABASE_URL tidak valid. Periksa Environment Variables Vercel.');
        return;
    }

    $db_host = $parts['host'];
    $db_port = $parts['port'] ?? 5432;
    $db_name = ltrim($parts['path'], '/');
    $db_user = isset($parts['user']) ? rawurldecode($parts['user']) : '';
    $db_pass = isset($parts['pass']) ? rawurldecode($parts['pass']) : '';

    // Preserve sslmode from the URL when supplied; otherwise use SSL for hosted PostgreSQL.
    if (!empty($parts['query'])) {
        parse_str($parts['query'], $query);
        $sslmode = $query['sslmode'] ?? $sslmode;
    }
    // Jaringan privat Railway & localhost tidak memakai SSL; host publik (Neon, dll.) wajib.
    $hostTanpaSsl = ($db_host === 'localhost' || $db_host === '127.0.0.1'
        || substr($db_host, -17) === '.railway.internal');
    if (!$sslmode && !$hostTanpaSsl) {
        $sslmode = 'require';
    }
} elseif (getenv('PGHOST')) {
    $db_host = getenv('PGHOST');
    $db_port = getenv('PGPORT') ?: 5432;
    $db_name = getenv('PGDATABASE');
    $db_user = getenv('PGUSER');
    $db_pass = getenv('PGPASSWORD');
} else {
    // Fallback khusus untuk Laragon/local development.
    $db_host = 'localhost';
    $db_port = '5432';
    $db_name = 'simpletop';
    $db_user = 'postgres';
    $db_pass = 'postgres';
}

try {
    // connect_timeout: saat database mati, gagal cepat (bukan menggantung lama).
    $dsn = "pgsql:host={$db_host};port={$db_port};dbname={$db_name}";
    if ($sslmode) {
        $dsn .= ";sslmode={$sslmode}";
    }

    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    // Jangan tampilkan password/connection string ke pengunjung.
    error_log('SimpleTop database error: ' . $e->getMessage());
    $pdo = null;
    simpletop_db_gagal('Koneksi database gagal. Periksa DATABASE_URL/PG* di Environment Variables dan pastikan database dapat diakses.');
}
