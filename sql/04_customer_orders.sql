-- Role baru: akun yang mendaftar otomatis menjadi 'customer' (boleh melihat & memesan).
-- 'admin' tetap mengelola produk. Aman dijalankan berulang kali.

-- 1) Ganti constraint role: admin | customer
ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check;
UPDATE users SET role = 'customer' WHERE role NOT IN ('admin', 'customer');
ALTER TABLE users ALTER COLUMN role SET DEFAULT 'customer';
ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('admin', 'customer'));

-- 2) Tabel pesanan. merk/seri/harga disalin (snapshot) supaya riwayat pesanan
--    tetap benar walau produknya kemudian diubah atau dihapus admin.
CREATE TABLE IF NOT EXISTS orders (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    laptop_id INTEGER REFERENCES laptop(id) ON DELETE SET NULL,
    merk VARCHAR(100) NOT NULL,
    seri VARCHAR(255) NOT NULL,
    jumlah INTEGER NOT NULL CHECK (jumlah > 0),
    harga_satuan BIGINT NOT NULL,
    total BIGINT NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'diproses',
    created_at TIMESTAMP NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS orders_user_idx ON orders (user_id, created_at DESC);

INSERT INTO schema_migrations (id)
VALUES ('04_customer_orders.sql')
ON CONFLICT (id) DO NOTHING;
