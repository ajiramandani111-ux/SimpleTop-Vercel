<?php
// Fungsi bantu umum. Dimuat otomatis oleh includes/session.php (dan header.php).

// Escape output ke HTML (pencegah XSS). WAJIB dipakai untuk setiap data dari
// database, $_GET, $_POST, atau $_SESSION yang dicetak ke halaman, termasuk
// di dalam atribut HTML (value="...", title="...").
//   ENT_QUOTES      : tanda ' dan " ikut di-escape (aman di atribut)
//   ENT_SUBSTITUTE  : byte UTF-8 rusak diganti, bukan membuat hasil kosong
if (!function_exists('e')) {
    function e($nilai): string
    {
        return htmlspecialchars((string) ($nilai ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

// Apakah baris produk berstatus aktif? Kolom boolean PostgreSQL bisa terbaca
// sebagai true/false, 't'/'f', atau 1/0 tergantung versi driver, jadi dicek longgar.
// Jika kolom belum ada (migrasi belum dijalankan) dianggap aktif.
if (!function_exists('produk_aktif')) {
    function produk_aktif(array $baris): bool
    {
        if (!array_key_exists('aktif', $baris)) {
            return true;
        }
        $v = $baris['aktif'];
        return $v === true || $v === 1 || $v === '1' || $v === 't' || $v === 'true';
    }
}
