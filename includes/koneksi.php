<?php
// Koneksi PDO PostgreSQL.
// Production (Vercel): set DATABASE_URL or PGHOST/PG* in Project Settings > Environment Variables.
// Local Laragon: values below are only fallbacks for local development.

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
        die('DATABASE_URL tidak valid. Periksa Environment Variables Vercel.');
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
    die('Koneksi database gagal. Periksa DATABASE_URL/PG* di Environment Variables dan pastikan database dapat diakses.');
}
