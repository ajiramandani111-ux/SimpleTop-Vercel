












-- jangan diubah




-- Jobsheet 8 (SimpleTop): skema awal database simpletop (PostgreSQL)
-- Jalankan setelah membuat database, misal:
--   createdb simpletop
--   psql -d simpletop -f sql/01_laptop_bestseller.sql

CREATE TABLE IF NOT EXISTS laptop (
    id SERIAL PRIMARY KEY,
    merk VARCHAR(100) NOT NULL,
    seri VARCHAR(255) NOT NULL,
    tahun INTEGER NOT NULL,
    harga BIGINT NOT NULL DEFAULT 0,
    stok INTEGER NOT NULL DEFAULT 0,
    kategori VARCHAR(50)
);

CREATE TABLE IF NOT EXISTS bestseller (
    id SERIAL PRIMARY KEY,
    merk VARCHAR(100) NOT NULL,
    seri VARCHAR(255) NOT NULL,
    total_penjualan INTEGER NOT NULL DEFAULT 0,
    rating NUMERIC(2,1) NOT NULL DEFAULT 0
);

-- Data awal, sama seperti data seed sebelumnya (versi $_SESSION)
INSERT INTO laptop (merk, seri, tahun, harga, stok, kategori) VALUES
    ('ASUS',   'ROG Strix G16',      2025, 15000000, 7,  'gaming'),
    ('Lenovo', 'ThinkPad X1 Carbon', 2024, 12000000, 5,  'bisnis'),
    ('Apple',  'MacBook Air M3',     2024, 18000000, 10, 'ultrabook'),
    ('Dell',   'XPS 13',             2023, 14000000, 4,  'ultrabook'),
    ('HP',     'Pavilion Aero 13',   2023, 11000000, 6,  'ultrabook'),
    ('Acer',   'Swift Go 14',        2025, 13000000, 8,  'ultrabook'),
    ('MSI',    'Katana 15',          2024, 16000000, 3,  'gaming'),
    ('ASUS',   'Zenbook 14 OLED',    2025, 17000000, 9,  'ultrabook');

INSERT INTO bestseller (merk, seri, total_penjualan, rating) VALUES
    ('Apple',  'MacBook Air M3',     152, 4.9),
    ('ASUS',   'ROG Strix G16',      121, 4.7),
    ('Lenovo', 'ThinkPad X1 Carbon', 98,  4.8),
    ('Acer',   'Swift Go 14',        87,  4.6),
    ('Dell',   'XPS 13',             76,  4.5);
