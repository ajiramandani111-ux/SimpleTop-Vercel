<?php
// "Ingat Saya": cookie berumur panjang berisi selector:validator.
// - selector  : pengenal acak (disimpan polos, untuk mencari baris).
// - validator : rahasia acak 256-bit; di database hanya disimpan hash sha256-nya.
// Cookie HttpOnly + SameSite=Lax (+ Secure di HTTPS) supaya tidak bisa dibaca JavaScript.
// Semua fungsi "fail-safe": jika tabel remember_tokens belum dibuat, fitur ini
// diam-diam nonaktif dan login biasa tetap berjalan.

const REMEMBER_COOKIE = 'remember_me';
const REMEMBER_DAYS = 30;

function remember_cookie_options(int $expires): array
{
    return [
        'expires'  => $expires,
        'path'     => '/',
        'secure'   => is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ];
}

function remember_clear_cookie(): void
{
    if (!headers_sent()) {
        setcookie(REMEMBER_COOKIE, '', remember_cookie_options(time() - 3600));
    }
    unset($_COOKIE[REMEMBER_COOKIE]);
}

// Buat token baru untuk user dan kirim cookie-nya.
function remember_issue(PDO $pdo, int $userId): void
{
    try {
        $selector  = bin2hex(random_bytes(9));   // 18 karakter hex
        $validator = bin2hex(random_bytes(32));  // 64 karakter hex
        $days = (int) REMEMBER_DAYS;

        // Bersihkan token kedaluwarsa sekalian.
        $pdo->exec('DELETE FROM remember_tokens WHERE expires_at < NOW()');

        $stmt = $pdo->prepare(
            "INSERT INTO remember_tokens (user_id, selector, token_hash, expires_at)
             VALUES (:uid, :sel, :hash, NOW() + INTERVAL '{$days} days')"
        );
        $stmt->execute([
            ':uid'  => $userId,
            ':sel'  => $selector,
            ':hash' => hash('sha256', $validator),
        ]);

        if (!headers_sent()) {
            setcookie(
                REMEMBER_COOKIE,
                $selector . ':' . $validator,
                remember_cookie_options(time() + $days * 86400)
            );
        }
    } catch (PDOException $e) {
        error_log('SimpleTop remember_issue error: ' . $e->getMessage());
    }
}

// Dipanggil saat sesi belum login tetapi ada cookie. Return true bila berhasil login otomatis.
function remember_autologin(PDO $pdo): bool
{
    $raw = $_COOKIE[REMEMBER_COOKIE] ?? null;
    if (!is_string($raw) || $raw === '') {
        return false;
    }

    $bagian = explode(':', $raw);
    if (count($bagian) !== 2
        || !preg_match('/^[a-f0-9]{18}$/', $bagian[0])
        || !preg_match('/^[a-f0-9]{64}$/', $bagian[1])) {
        remember_clear_cookie();
        return false;
    }
    [$selector, $validator] = $bagian;

    try {
        $stmt = $pdo->prepare(
            'SELECT t.token_hash, u.*
               FROM remember_tokens t
               JOIN users u ON u.id = t.user_id
              WHERE t.selector = :sel AND t.expires_at > NOW()'
        );
        $stmt->execute([':sel' => $selector]);
        $row = $stmt->fetch();

        if (!$row) {
            remember_clear_cookie();
            return false;
        }

        if (!hash_equals(trim($row['token_hash']), hash('sha256', $validator))) {
            // Selector valid tapi validator salah = cookie dimanipulasi -> cabut token.
            $pdo->prepare('DELETE FROM remember_tokens WHERE selector = :sel')->execute([':sel' => $selector]);
            remember_clear_cookie();
            return false;
        }

        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'    => (int) $row['id'],
            'nama'  => $row['nama'],
            'email' => $row['email'],
            'role'  => $row['role'] ?? 'customer',
        ];
        return true;
    } catch (PDOException $e) {
        error_log('SimpleTop remember_autologin error: ' . $e->getMessage());
        return false;
    }
}

// Dipanggil saat logout: hapus token milik cookie ini dan hapus cookie-nya.
function remember_forget(?PDO $pdo): void
{
    $raw = $_COOKIE[REMEMBER_COOKIE] ?? null;
    if ($pdo instanceof PDO && is_string($raw) && strpos($raw, ':') !== false) {
        $selector = explode(':', $raw, 2)[0];
        try {
            $pdo->prepare('DELETE FROM remember_tokens WHERE selector = :sel')->execute([':sel' => $selector]);
        } catch (PDOException $e) {
            error_log('SimpleTop remember_forget error: ' . $e->getMessage());
        }
    }
    remember_clear_cookie();
}
