

CREATE DATABASE lab_se
    WITH 
    OWNER = postgres
    ENCODING = 'UTF8'
    TABLESPACE = pg_default
    CONNECTION LIMIT = -1;

CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE IF NOT EXISTS jabatan (
    id SERIAL PRIMARY KEY,
    nama_jabatan VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS anggota (
    id SERIAL PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    jabatan VARCHAR(100),
    foto VARCHAR(500),
	instagram VARCHAR(255),
	linkedin VARCHAR(255),
    deskripsi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS pendaftaran (
    id SERIAL PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    nim VARCHAR(50) NOT NULL,
	email VARCHAR(255),
	foto VARCHAR(500),
    minat TEXT,
    status VARCHAR(50) DEFAULT 'pending',
    tanggal_daftar TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    tanggal_review TIMESTAMP,
    catatan_admin TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Fix: Hapus NOT NULL constraint dari kolom jurusan jika ada
-- Jika kolom jurusan tidak ada, query ini akan diabaikan
DO $$
BEGIN
    -- Cek apakah kolom jurusan ada
    IF EXISTS (
        SELECT 1 
        FROM information_schema.columns 
        WHERE table_name = 'pendaftaran' 
        AND column_name = 'jurusan'
    ) THEN
        -- Hapus NOT NULL constraint jika ada
        ALTER TABLE pendaftaran ALTER COLUMN jurusan DROP NOT NULL;
    END IF;
END $$;

CREATE TABLE IF NOT EXISTS blog (
    id SERIAL PRIMARY KEY,
    title VARCHAR(500) NOT NULL,
    slug VARCHAR(500) UNIQUE,
    content TEXT NOT NULL,
    excerpt TEXT,
    featured_image VARCHAR(500),
    author_id INTEGER REFERENCES users(id),
    category VARCHAR(100),
    tags TEXT[],
    views INTEGER DEFAULT 0,
    is_published BOOLEAN DEFAULT FALSE,
    published_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_pendaftaran_status ON pendaftaran(status);
CREATE INDEX IF NOT EXISTS idx_pendaftaran_tanggal ON pendaftaran(tanggal_daftar);
CREATE INDEX IF NOT EXISTS idx_blog_published ON blog(is_published, published_at);
CREATE INDEX IF NOT EXISTS idx_blog_slug ON blog(slug);
CREATE INDEX IF NOT EXISTS idx_users_username ON users(username);
CREATE INDEX IF NOT EXISTS idx_anggota_jabatan ON anggota(jabatan);

INSERT INTO users (username, password, full_name, role) 
VALUES ('admin', MD5('admin123'), 'Administrator', 'admin')
ON CONFLICT (username) DO NOTHING;

CREATE TABLE IF NOT EXISTS riwayat_pendaftaran (
    id SERIAL PRIMARY KEY,
    pendaftaran_id INTEGER NOT NULL REFERENCES pendaftaran(id) ON DELETE CASCADE,
    status_lama VARCHAR(50),
    status_baru VARCHAR(50) NOT NULL,
    catatan TEXT,
    changed_by INTEGER REFERENCES users(id),
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT riwayat_status_check CHECK (status_baru IN ('pending','accepted','rejected'))
);

CREATE INDEX IF NOT EXISTS idx_riwayat_pendaftaran_id ON riwayat_pendaftaran(pendaftaran_id);
CREATE INDEX IF NOT EXISTS idx_riwayat_pendaftaran_changed_at ON riwayat_pendaftaran(changed_at);

-- database profile (belum fix)
CREATE TABLE konten_page (
    id SERIAL PRIMARY KEY,
    page_key VARCHAR(100) NOT NULL UNIQUE,
    title VARCHAR(255) NULL,
    content TEXT NOT NULL,
    last_updated TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE OR REPLACE FUNCTION update_last_updated_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.last_updated = now();
    RETURN NEW;
END;
$$ language 'plpgsql';

CREATE TRIGGER last_updated_trg
BEFORE UPDATE ON konten_page
FOR EACH ROW
EXECUTE PROCEDURE update_last_updated_column();


TRUNCATE TABLE konten_page;

-- Insert data profil
INSERT INTO konten_page (page_key, title, content) VALUES 
('roadmap_judul', 'Roadmap Laboratorium SE', 'Perjalanan dan Rencana Pengembangan Laboratorium Software Engineering.'),
('visi_deskripsi', 'Visi Kami', 'Menjadi pusat penelitian software engineering yang relevan dan berdaya guna bagi pengembangan teknologi informasi di Indonesia.'),
('misi_item_1', 'Misi Utama', 'Melakukan penelitian berkualitas di bidang perangkat lunak yang memberikan kontribusi nyata bagi industri dan akademisi.'),
('tentang_kami', 'Tentang Lab SE', 'Laboratorium Software Engineering merupakan wadah kolaborasi mahasiswa dan dosen untuk penelitian dan pengembangan.');

-- PENTING: Jika pakai DBeaver/pgAdmin, jangan lupa klik tombol COMMIT (ikon Disket/Centang Hijau) setelah menjalankan ini!
-- Data ini masih dummy


-- Database roadmap

CREATE TABLE roadmap_periode (
    id SERIAL PRIMARY KEY,
    tahun_periode VARCHAR(50) NOT NULL,    -- Contoh: '2024 Q1 - Q4'
    judul_fase VARCHAR(150) NOT NULL,      -- Contoh: 'Fase Pendirian & Setup'
    urutan INTEGER NOT NULL DEFAULT 0       -- Untuk sorting
);

CREATE TABLE roadmap_item (
    id SERIAL PRIMARY KEY,
    id_periode INTEGER REFERENCES roadmap_periode(id) ON DELETE CASCADE, -- Foreign Key
    deskripsi_tugas TEXT NOT NULL,         -- Contoh: 'Pembentukan struktur organisasi laboratorium'
    urutan INTEGER NOT NULL DEFAULT 0       -- Untuk sorting item di dalam fase
);


-- Memasukkan Periode 2024 (Fase Pendirian & Setup)
INSERT INTO roadmap_periode (tahun_periode, judul_fase, urutan)
VALUES ('2024 Q1 - Q4', 'Fase Pendirian & Setup', 1);

-- Memasukkan Periode 2025 (Fase Pengembangan & Riset)
INSERT INTO roadmap_periode (tahun_periode, judul_fase, urutan)
VALUES ('2025 Q1 - Q4', 'Fase Pengembangan & Riset', 2);

-- Ambil ID Periode untuk '2024 Q1 - Q4'
WITH id_2024 AS (
    SELECT id FROM roadmap_periode WHERE tahun_periode = '2024 Q1 - Q4'
)
-- Masukkan item-item (urutan 1 sampai 4)
INSERT INTO roadmap_item (id_periode, deskripsi_tugas, urutan)
VALUES 
(
    (SELECT id FROM id_2024), 
    'Pembentukan struktur organisasi laboratorium', 
    1
),
(
    (SELECT id FROM id_2024), 
    'Pengadaan infrastruktur dan peralatan dasar', 
    2
),
(
    (SELECT id FROM id_2024), 
    'Rekrutmen anggota dan asisten laboratorium', 
    3
),
(
    (SELECT id FROM id_2024), 
    'Peluncuran website dan sistem informasi lab', 
    4
);

-- Ambil ID Periode untuk '2025 Q1 - Q4'
WITH id_2025 AS (
    SELECT id FROM roadmap_periode WHERE tahun_periode = '2025 Q1 - Q4'
)
-- Masukkan item-item (urutan 1 sampai 2)
INSERT INTO roadmap_item (id_periode, deskripsi_tugas, urutan)
VALUES 
(
    (SELECT id FROM id_2025), 
    'Pengembangan proyek penelitian', 
    1
),
(
    (SELECT id FROM id_2025), 
    'Kerjasama dengan industri untuk implementasi teknologi', 
    2
);

