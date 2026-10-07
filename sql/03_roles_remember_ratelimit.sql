-- Jobsheet 10 (lanjutan): kontrol akses berbasis role, "Ingat Saya",
-- dan pembatasan percobaan login. Aman dijalankan berulang kali.
-- Jalankan di Neon SQL Editor SEBELUM / SETELAH deploy (aplikasi tetap
-- berjalan walau belum dijalankan, tetapi fitur barunya belum aktif).

-- 1) Role pada tabel users: 'admin' atau 'petugas' (default petugas).
ALTER TABLE users ADD COLUMN IF NOT EXISTS role VARCHAR(20) NOT NULL DEFAULT 'petugas';

DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'users_role_check') THEN
        ALTER TABLE users
            ADD CONSTRAINT users_role_check CHECK (role IN ('admin', 'petugas'));
    END IF;
END $$;

-- 2) Token "Ingat Saya". Yang disimpan hanya HASH validator (sha256),
--    bukan token aslinya, sehingga kebocoran database tidak langsung
--    memberi akses login.
CREATE TABLE IF NOT EXISTS remember_tokens (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    selector VARCHAR(32) NOT NULL UNIQUE,
    token_hash CHAR(64) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS remember_tokens_user_idx ON remember_tokens (user_id);
CREATE INDEX IF NOT EXISTS remember_tokens_expires_idx ON remember_tokens (expires_at);

-- 3) Penghitung percobaan login gagal per email.
CREATE TABLE IF NOT EXISTS login_attempts (
    identifier VARCHAR(255) PRIMARY KEY,
    attempts INTEGER NOT NULL DEFAULT 0,
    last_attempt TIMESTAMP NOT NULL DEFAULT NOW(),
    locked_until TIMESTAMP
);

INSERT INTO schema_migrations (id)
VALUES ('03_roles_remember_ratelimit.sql')
ON CONFLICT (id) DO NOTHING;

-- 4) JADIKAN AKUN ANDA ADMIN (ganti emailnya, lalu jalankan baris ini):
-- UPDATE users SET role = 'admin' WHERE LOWER(email) = LOWER('emailanda@contoh.com');
