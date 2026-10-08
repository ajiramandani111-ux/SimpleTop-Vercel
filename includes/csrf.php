<?php
// Perlindungan CSRF: token acak per sesi, disisipkan ke setiap form POST dan
// diverifikasi di server SEBELUM menyentuh database. Bergantung pada session
// yang sudah berjalan (dimuat oleh includes/session.php).

const CSRF_FIELD = 'csrf_token';

function csrf_token(): string
{
    if (empty($_SESSION[CSRF_FIELD]) || !is_string($_SESSION[CSRF_FIELD])) {
        $_SESSION[CSRF_FIELD] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_FIELD];
}

// Letakkan di dalam setiap <form method="POST">.
function csrf_field(): string
{
    return '<input type="hidden" name="' . CSRF_FIELD . '" value="' . e(csrf_token()) . '">';
}

// Hapus token (dipanggil saat login/logout agar token lama tidak dipakai ulang).
function csrf_reset(): void
{
    unset($_SESSION[CSRF_FIELD]);
}

// Verifikasi token pada request POST. Jika tidak valid: simpan pesan, redirect ke
// $redirectTo, dan hentikan eksekusi. hash_equals() = perbandingan aman dari timing attack.
function csrf_verify(string $redirectTo): void
{
    $kirim = $_POST[CSRF_FIELD] ?? '';
    $asli  = $_SESSION[CSRF_FIELD] ?? '';

    if (is_string($kirim) && is_string($asli) && $asli !== '' && hash_equals($asli, $kirim)) {
        return;
    }

    $_SESSION['flash'] = [
        'type'  => 'error',
        'pesan' => 'Formulir kedaluwarsa atau tidak valid. Muat ulang halaman lalu coba lagi.',
    ];
    header('Location: ' . $redirectTo);
    exit;
}
