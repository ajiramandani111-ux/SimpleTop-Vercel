<?php
// Pembatasan percobaan login gagal per email (anti brute-force).
// Disimpan di tabel login_attempts (bukan $_SESSION) karena penyerang cukup
// membuang cookie session untuk mereset hitungan yang disimpan di session.
// Fail-open: bila tabel belum ada, login tetap berjalan tanpa pembatasan.

const LOGIN_MAX_ATTEMPTS  = 5;   // gagal ke-5 -> terkunci
const LOGIN_WARN_AFTER    = 3;   // mulai tampilkan peringatan sisa percobaan
const LOGIN_LOCK_MINUTES  = 15;  // lama kunci
const LOGIN_WINDOW_MINUTES = 15; // hitungan direset jika tidak ada percobaan selama ini

function login_identifier(string $email): string
{
    return strtolower(substr(trim($email), 0, 255));
}

// Return ['locked' => bool, 'sisa_detik' => int]
function login_lock_status(PDO $pdo, string $identifier): array
{
    try {
        $stmt = $pdo->prepare(
            'SELECT CASE WHEN locked_until IS NOT NULL AND locked_until > NOW()
                         THEN CEIL(EXTRACT(EPOCH FROM (locked_until - NOW())))::int
                         ELSE 0 END AS sisa_detik
               FROM login_attempts WHERE identifier = :id'
        );
        $stmt->execute([':id' => $identifier]);
        $row = $stmt->fetch();
        $sisa = $row ? (int) $row['sisa_detik'] : 0;
        return ['locked' => $sisa > 0, 'sisa_detik' => $sisa];
    } catch (PDOException $e) {
        error_log('SimpleTop login_lock_status error: ' . $e->getMessage());
        return ['locked' => false, 'sisa_detik' => 0];
    }
}

// Catat 1 kegagalan. Return ['attempts' => int, 'sisa' => int, 'locked' => bool]
function login_record_failure(PDO $pdo, string $identifier): array
{
    $max    = (int) LOGIN_MAX_ATTEMPTS;
    $lock   = (int) LOGIN_LOCK_MINUTES;
    $window = (int) LOGIN_WINDOW_MINUTES;

    try {
        $stmt = $pdo->prepare(
            "INSERT INTO login_attempts (identifier, attempts, last_attempt, locked_until)
             VALUES (:id, 1, NOW(), NULL)
             ON CONFLICT (identifier) DO UPDATE SET
                 attempts = CASE
                     WHEN login_attempts.last_attempt < NOW() - INTERVAL '{$window} minutes'
                       OR (login_attempts.locked_until IS NOT NULL AND login_attempts.locked_until <= NOW())
                     THEN 1
                     ELSE login_attempts.attempts + 1
                 END,
                 last_attempt = NOW(),
                 locked_until = NULL
             RETURNING attempts"
        );
        $stmt->execute([':id' => $identifier]);
        $attempts = (int) $stmt->fetchColumn();

        $locked = $attempts >= $max;
        if ($locked) {
            $pdo->prepare("UPDATE login_attempts SET locked_until = NOW() + INTERVAL '{$lock} minutes' WHERE identifier = :id")
                ->execute([':id' => $identifier]);
        }

        // Rapikan baris lama (termasuk email asal-asalan yang pernah dicoba).
        $pdo->exec("DELETE FROM login_attempts WHERE last_attempt < NOW() - INTERVAL '1 day'");

        return ['attempts' => $attempts, 'sisa' => max(0, $max - $attempts), 'locked' => $locked];
    } catch (PDOException $e) {
        error_log('SimpleTop login_record_failure error: ' . $e->getMessage());
        return ['attempts' => 0, 'sisa' => $max, 'locked' => false];
    }
}

function login_clear(PDO $pdo, string $identifier): void
{
    try {
        $pdo->prepare('DELETE FROM login_attempts WHERE identifier = :id')->execute([':id' => $identifier]);
    } catch (PDOException $e) {
        error_log('SimpleTop login_clear error: ' . $e->getMessage());
    }
}

function login_format_sisa_waktu(int $detik): string
{
    $menit = (int) ceil($detik / 60);
    return $menit <= 1 ? '1 menit' : $menit . ' menit';
}
