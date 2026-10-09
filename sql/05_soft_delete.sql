-- "Hapus" kini berarti nonaktifkan (soft delete): baris tetap ada di database,
-- hanya disembunyikan dari pengunjung & customer. Admin bisa menampilkannya lagi.
-- Aman dijalankan berulang kali; data yang sudah ada otomatis berstatus aktif.
-- JALANKAN FILE INI SEBELUM deploy kode baru.

ALTER TABLE laptop     ADD COLUMN IF NOT EXISTS aktif BOOLEAN NOT NULL DEFAULT TRUE;
ALTER TABLE bestseller ADD COLUMN IF NOT EXISTS aktif BOOLEAN NOT NULL DEFAULT TRUE;

CREATE INDEX IF NOT EXISTS laptop_aktif_idx     ON laptop (aktif);
CREATE INDEX IF NOT EXISTS bestseller_aktif_idx ON bestseller (aktif);

INSERT INTO schema_migrations (id)
VALUES ('05_soft_delete.sql')
ON CONFLICT (id) DO NOTHING;
