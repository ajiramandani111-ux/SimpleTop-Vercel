<?php
// Session disimpan di PostgreSQL (tabel "sessions"), bukan di file server.
// Alasannya: di Vercel, container bersifat stateless dan bisa dimatikan/diganti
// kapan saja, sehingga session berbasis file akan hilang.

require_once __DIR__ . '/koneksi.php';

if (session_status() === PHP_SESSION_NONE) {

    class DbSessionHandler implements SessionHandlerInterface
    {
        private PDO $pdo;

        public function __construct(PDO $pdo)
        {
            $this->pdo = $pdo;
        }

        public function open(string $path, string $name): bool
        {
            return true;
        }

        public function close(): bool
        {
            return true;
        }

        public function read(string $id): string|false
        {
            try {
                $stmt = $this->pdo->prepare('SELECT data FROM sessions WHERE id = :id');
                $stmt->execute([':id' => $id]);
                $row = $stmt->fetch();
                if (!$row) {
                    return '';
                }
                $decoded = base64_decode($row['data'], true);
                return $decoded === false ? '' : $decoded;
            } catch (PDOException $e) {
                error_log('SimpleTop session read error: ' . $e->getMessage());
                return '';
            }
        }

        public function write(string $id, string $data): bool
        {
            try {
                $stmt = $this->pdo->prepare(
                    'INSERT INTO sessions (id, data, updated_at) VALUES (:id, :data, NOW())
                     ON CONFLICT (id) DO UPDATE SET data = EXCLUDED.data, updated_at = NOW()'
                );
                return $stmt->execute([':id' => $id, ':data' => base64_encode($data)]);
            } catch (PDOException $e) {
                error_log('SimpleTop session write error: ' . $e->getMessage());
                return false;
            }
        }

        public function destroy(string $id): bool
        {
            try {
                $stmt = $this->pdo->prepare('DELETE FROM sessions WHERE id = :id');
                return $stmt->execute([':id' => $id]);
            } catch (PDOException $e) {
                error_log('SimpleTop session destroy error: ' . $e->getMessage());
                return false;
            }
        }

        public function gc(int $max_lifetime): int|false
        {
            try {
                $stmt = $this->pdo->prepare(
                    "DELETE FROM sessions WHERE updated_at < NOW() - (:secs * INTERVAL '1 second')"
                );
                $stmt->execute([':secs' => $max_lifetime]);
                return $stmt->rowCount();
            } catch (PDOException $e) {
                error_log('SimpleTop session gc error: ' . $e->getMessage());
                return false;
            }
        }
    }

    session_set_save_handler(new DbSessionHandler($pdo), true);

    $__https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $__https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}
