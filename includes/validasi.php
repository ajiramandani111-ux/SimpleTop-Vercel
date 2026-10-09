<?php
// Validasi input produk. Dipakai bersama oleh proses_tambah.php dan proses_edit.php
// agar aturan keduanya selalu sama. Mengembalikan ['data' => [...], 'errors' => [...]].

function validasi_laptop(array $in): array
{
    $merk = trim((string) ($in['merk'] ?? ''));
    $seri = trim((string) ($in['seri'] ?? ''));
    $kategori = trim((string) ($in['kategori'] ?? ''));

    // FILTER_VALIDATE_INT + rentang (is_numeric menerima bentuk aneh seperti "1e3").
    $batasTahun = (int) date('Y') + 1;
    $tahun = filter_var(trim((string) ($in['tahun'] ?? '')), FILTER_VALIDATE_INT, ['options' => ['min_range' => 2000, 'max_range' => $batasTahun]]);
    $harga = filter_var(trim((string) ($in['harga'] ?? '')), FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 100000000000]]);
    $stok  = filter_var(trim((string) ($in['stok'] ?? '')), FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 100000]]);

    $errors = [];
    if ($merk === '' || strlen($merk) > 100) {
        $errors[] = "Merk wajib diisi (maksimal 100 karakter).";
    }
    if ($seri === '' || strlen($seri) > 255) {
        $errors[] = "Seri/model wajib diisi (maksimal 255 karakter).";
    }
    if (strlen($kategori) > 50) {
        $errors[] = "Kategori maksimal 50 karakter.";
    }
    if ($tahun === false) {
        $errors[] = "Tahun keluaran harus bilangan bulat antara 2000-{$batasTahun}.";
    }
    if ($harga === false) {
        $errors[] = "Harga harus bilangan bulat, tidak boleh negatif.";
    }
    if ($stok === false) {
        $errors[] = "Stok harus bilangan bulat antara 0-100000.";
    }

    return [
        'data' => [
            'merk' => $merk,
            'seri' => $seri,
            'tahun' => $tahun,
            'harga' => $harga,
            'stok' => $stok,
            'kategori' => $kategori === '' ? null : $kategori,
        ],
        'errors' => $errors,
    ];
}

function validasi_bestseller(array $in): array
{
    $merk = trim((string) ($in['merk'] ?? ''));
    $seri = trim((string) ($in['seri'] ?? ''));
    $totalPenjualan = filter_var(trim((string) ($in['total_penjualan'] ?? '')), FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 1000000000]]);
    $rating = filter_var(trim((string) ($in['rating'] ?? '')), FILTER_VALIDATE_FLOAT);

    $errors = [];
    if ($merk === '' || strlen($merk) > 100) {
        $errors[] = "Merk wajib diisi (maksimal 100 karakter).";
    }
    if ($seri === '' || strlen($seri) > 255) {
        $errors[] = "Seri/model wajib diisi (maksimal 255 karakter).";
    }
    if ($totalPenjualan === false) {
        $errors[] = "Total penjualan harus bilangan bulat, tidak boleh negatif.";
    }
    if ($rating === false || $rating < 1 || $rating > 5) {
        $errors[] = "Rating harus angka di antara 1-5.";
    }

    return [
        'data' => [
            'merk' => $merk,
            'seri' => $seri,
            'total_penjualan' => $totalPenjualan,
            'rating' => $rating === false ? false : round($rating, 1),
        ],
        'errors' => $errors,
    ];
}
