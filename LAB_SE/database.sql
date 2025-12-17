-- ============================================
-- DATABASE LAB SE - COMPLETE SCHEMA
-- ============================================
-- File ini berisi semua struktur database lengkap
-- Jalankan file ini untuk membuat database dari awal
-- ============================================

-- Create Database
CREATE DATABASE lab_se
    WITH 
    OWNER = postgres
    ENCODING = 'UTF8'
    TABLESPACE = pg_default
    CONNECTION LIMIT = -1;

-- ============================================
-- TABEL USERS
-- ============================================
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- TABEL JABATAN
-- ============================================
CREATE TABLE IF NOT EXISTS jabatan (
    id SERIAL PRIMARY KEY,
    nama_jabatan VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- TABEL ANGGOTA
-- ============================================
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

-- ============================================
-- TABEL PENDAFTARAN
-- ============================================
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
DO $$
BEGIN
    IF EXISTS (
        SELECT 1 
        FROM information_schema.columns 
        WHERE table_name = 'pendaftaran' 
        AND column_name = 'jurusan'
    ) THEN
        ALTER TABLE pendaftaran ALTER COLUMN jurusan DROP NOT NULL;
    END IF;
END $$;

-- ============================================
-- TABEL BLOG
-- ============================================
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

-- ============================================
-- TABEL RIWAYAT PENDAFTARAN
-- ============================================
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

-- ============================================
-- TABEL PENELITIAN (LENGKAP DENGAN KOLOM TAMBAHAN)
-- ============================================
CREATE TABLE IF NOT EXISTS penelitian (
    id SERIAL PRIMARY KEY,
    judul VARCHAR(500) NOT NULL,
    deskripsi TEXT,
    tahun INTEGER NOT NULL,
    status VARCHAR(50) DEFAULT 'ongoing', -- ongoing, completed
    jumlah_tim INTEGER DEFAULT 0,
    -- Kolom tambahan
    peneliti_utama VARCHAR(255),
    anggota_tim TEXT,
    dosen_pembimbing VARCHAR(255),
    kategori VARCHAR(100),
    metodologi TEXT,
    tanggal_mulai DATE,
    tanggal_selesai DATE,
    progress INTEGER DEFAULT 0 CHECK (progress >= 0 AND progress <= 100),
    keywords TEXT,
    link_dokumen VARCHAR(500),
    output_hasil TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- TABEL PUBLIKASI (LENGKAP DENGAN KOLOM TAMBAHAN)
-- ============================================
CREATE TABLE IF NOT EXISTS publikasi (
    id SERIAL PRIMARY KEY,
    judul TEXT NOT NULL,
    tipe VARCHAR(50) NOT NULL, -- journal, conference
    tahun INTEGER NOT NULL,
    -- Kolom tambahan
    penulis_utama VARCHAR(255),
    daftar_penulis TEXT,
    nama_journal_conference VARCHAR(500),
    volume VARCHAR(50),
    issue VARCHAR(50),
    halaman VARCHAR(50),
    doi VARCHAR(255),
    abstract TEXT,
    keywords TEXT,
    link_url VARCHAR(500),
    status VARCHAR(50) DEFAULT 'published', -- published, accepted, submitted
    scopus_indexed BOOLEAN DEFAULT FALSE,
    quartile VARCHAR(10), -- Q1, Q2, Q3, Q4
    tanggal_publikasi DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- TABEL KONTEN PAGE (PROFIL)
-- ============================================
CREATE TABLE IF NOT EXISTS konten_page (
    id SERIAL PRIMARY KEY,
    page_key VARCHAR(100) NOT NULL UNIQUE,
    title VARCHAR(255) NULL,
    content TEXT NOT NULL,
    last_updated TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- TABEL ROADMAP
-- ============================================
CREATE TABLE IF NOT EXISTS roadmap_periode (
    id SERIAL PRIMARY KEY,
    tahun_periode VARCHAR(100) NOT NULL,
    judul_fase VARCHAR(255) NOT NULL,
    urutan INTEGER NOT NULL DEFAULT 1,
    created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS roadmap_item (
    id SERIAL PRIMARY KEY,
    id_periode INTEGER NOT NULL REFERENCES roadmap_periode(id) ON DELETE CASCADE,
    deskripsi_tugas TEXT NOT NULL,
    urutan INTEGER NOT NULL DEFAULT 1,
    created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- INDEXES UNTUK PERFORMA
-- ============================================
CREATE INDEX IF NOT EXISTS idx_pendaftaran_status ON pendaftaran(status);
CREATE INDEX IF NOT EXISTS idx_pendaftaran_tanggal ON pendaftaran(tanggal_daftar);
CREATE INDEX IF NOT EXISTS idx_blog_published ON blog(is_published, published_at);
CREATE INDEX IF NOT EXISTS idx_blog_slug ON blog(slug);
CREATE INDEX IF NOT EXISTS idx_users_username ON users(username);
CREATE INDEX IF NOT EXISTS idx_anggota_jabatan ON anggota(jabatan);
CREATE INDEX IF NOT EXISTS idx_riwayat_pendaftaran_id ON riwayat_pendaftaran(pendaftaran_id);
CREATE INDEX IF NOT EXISTS idx_riwayat_pendaftaran_changed_at ON riwayat_pendaftaran(changed_at);

-- Index untuk penelitian
CREATE INDEX IF NOT EXISTS idx_penelitian_tahun ON penelitian(tahun);
CREATE INDEX IF NOT EXISTS idx_penelitian_status ON penelitian(status);
CREATE INDEX IF NOT EXISTS idx_penelitian_kategori ON penelitian(kategori);
CREATE INDEX IF NOT EXISTS idx_penelitian_peneliti_utama ON penelitian(peneliti_utama);
CREATE INDEX IF NOT EXISTS idx_penelitian_tanggal_mulai ON penelitian(tanggal_mulai);

-- Index untuk publikasi
CREATE INDEX IF NOT EXISTS idx_publikasi_tahun ON publikasi(tahun);
CREATE INDEX IF NOT EXISTS idx_publikasi_tipe ON publikasi(tipe);
CREATE INDEX IF NOT EXISTS idx_publikasi_status ON publikasi(status);
CREATE INDEX IF NOT EXISTS idx_publikasi_penulis_utama ON publikasi(penulis_utama);
CREATE INDEX IF NOT EXISTS idx_publikasi_scopus ON publikasi(scopus_indexed);
CREATE INDEX IF NOT EXISTS idx_publikasi_quartile ON publikasi(quartile);

-- ============================================
-- FUNCTIONS & TRIGGERS
-- ============================================

-- Function untuk update updated_at otomatis
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ language 'plpgsql';

-- Trigger untuk penelitian
DROP TRIGGER IF EXISTS update_penelitian_updated_at ON penelitian;
CREATE TRIGGER update_penelitian_updated_at
    BEFORE UPDATE ON penelitian
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- Trigger untuk publikasi
DROP TRIGGER IF EXISTS update_publikasi_updated_at ON publikasi;
CREATE TRIGGER update_publikasi_updated_at
    BEFORE UPDATE ON publikasi
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- Function untuk update last_updated konten_page
CREATE OR REPLACE FUNCTION update_last_updated_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.last_updated = now();
    RETURN NEW;
END;
$$ language 'plpgsql';

-- Trigger untuk konten_page
DROP TRIGGER IF EXISTS last_updated_trg ON konten_page;
CREATE TRIGGER last_updated_trg
BEFORE UPDATE ON konten_page
FOR EACH ROW
EXECUTE PROCEDURE update_last_updated_column();

-- ============================================
-- COMMENTS UNTUK DOKUMENTASI
-- ============================================

-- Comments untuk penelitian
COMMENT ON COLUMN penelitian.peneliti_utama IS 'Nama peneliti utama/ketua penelitian';
COMMENT ON COLUMN penelitian.anggota_tim IS 'Daftar anggota tim peneliti (dipisah koma)';
COMMENT ON COLUMN penelitian.dosen_pembimbing IS 'Nama dosen pembimbing (jika penelitian mahasiswa)';
COMMENT ON COLUMN penelitian.kategori IS 'Kategori/bidang penelitian';
COMMENT ON COLUMN penelitian.metodologi IS 'Metode yang digunakan dalam penelitian';
COMMENT ON COLUMN penelitian.tanggal_mulai IS 'Tanggal mulai penelitian';
COMMENT ON COLUMN penelitian.tanggal_selesai IS 'Tanggal selesai penelitian';
COMMENT ON COLUMN penelitian.progress IS 'Progress penelitian dalam persen (0-100)';
COMMENT ON COLUMN penelitian.keywords IS 'Kata kunci penelitian';
COMMENT ON COLUMN penelitian.link_dokumen IS 'Link ke repository, dokumentasi, atau file penelitian';
COMMENT ON COLUMN penelitian.output_hasil IS 'Output atau hasil dari penelitian';

-- Comments untuk publikasi
COMMENT ON COLUMN publikasi.penulis_utama IS 'Nama penulis utama publikasi';
COMMENT ON COLUMN publikasi.daftar_penulis IS 'Daftar semua penulis (dipisah koma)';
COMMENT ON COLUMN publikasi.nama_journal_conference IS 'Nama journal atau conference';
COMMENT ON COLUMN publikasi.volume IS 'Volume jurnal';
COMMENT ON COLUMN publikasi.issue IS 'Issue/Number jurnal';
COMMENT ON COLUMN publikasi.halaman IS 'Halaman publikasi (contoh: 123-145)';
COMMENT ON COLUMN publikasi.doi IS 'Digital Object Identifier';
COMMENT ON COLUMN publikasi.abstract IS 'Abstract publikasi';
COMMENT ON COLUMN publikasi.keywords IS 'Kata kunci publikasi';
COMMENT ON COLUMN publikasi.link_url IS 'Link ke publikasi online';
COMMENT ON COLUMN publikasi.status IS 'Status publikasi (published, accepted, submitted)';
COMMENT ON COLUMN publikasi.scopus_indexed IS 'Apakah terindeks di Scopus';
COMMENT ON COLUMN publikasi.quartile IS 'Quartile jurnal (Q1, Q2, Q3, Q4)';
COMMENT ON COLUMN publikasi.tanggal_publikasi IS 'Tanggal publikasi';

-- ============================================
-- DATA DEFAULT
-- ============================================

-- Insert default admin user
INSERT INTO users (username, password, role) 
VALUES ('admin', MD5('admin123'), 'admin')
ON CONFLICT (username) DO NOTHING;

-- Truncate dan insert data profil default
TRUNCATE TABLE konten_page;

INSERT INTO konten_page (page_key, title, content) VALUES 
('roadmap_judul', 'Roadmap Laboratorium SE', 'Perjalanan dan Rencana Pengembangan Laboratorium Software Engineering.'),
('visi_deskripsi', 'Visi Kami', 'Menjadi pusat penelitian software engineering yang relevan dan berdaya guna bagi pengembangan teknologi informasi di Indonesia.'),
('misi_item_1', 'Misi Utama', 'Melakukan penelitian berkualitas di bidang perangkat lunak yang memberikan kontribusi nyata bagi industri dan akademisi.'),
('tentang_kami', 'Tentang Lab SE', 'Laboratorium Software Engineering merupakan wadah kolaborasi mahasiswa dan dosen untuk penelitian dan pengembangan.'),
('tentang_kami_feature_rekayasa', 'Rekayasa Perangkat Lunak', 'Fokus pada pengembangan perangkat lunak berkualitas tinggi dengan metodologi terkini'),
('tentang_kami_feature_publikasi', 'Publikasi Ilmiah', 'Mendorong penelitian dan publikasi hasil riset di bidang software engineering'),
('tentang_kami_feature_kemitraan', 'Kemitraan Industri', 'Membangun kerjasama dengan industri untuk implementasi teknologi nyata'),
('tentang_kami_feature_pembinaan', 'Pembinaan Kompetensi', 'Membina mahasiswa melalui proyek nyata dan pengembangan skill praktis');

-- Insert data roadmap default
INSERT INTO roadmap_periode (tahun_periode, judul_fase, urutan)
VALUES 
('2024 Q1 - Q4', 'Fase Pendirian & Setup', 1),
('2025 Q1 - Q4', 'Fase Pengembangan & Riset', 2)
ON CONFLICT DO NOTHING;

-- Insert roadmap items untuk 2024
WITH id_2024 AS (
    SELECT id FROM roadmap_periode WHERE tahun_periode = '2024 Q1 - Q4' LIMIT 1
)
INSERT INTO roadmap_item (id_periode, deskripsi_tugas, urutan)
SELECT 
    (SELECT id FROM id_2024), 
    deskripsi_tugas, 
    urutan
FROM (VALUES
    ('Pembentukan struktur organisasi laboratorium', 1),
    ('Pengadaan infrastruktur dan peralatan dasar', 2),
    ('Rekrutmen anggota dan asisten laboratorium', 3),
    ('Peluncuran website dan sistem informasi lab', 4)
) AS v(deskripsi_tugas, urutan)
WHERE NOT EXISTS (
    SELECT 1 FROM roadmap_item 
    WHERE id_periode = (SELECT id FROM id_2024) 
    AND deskripsi_tugas = v.deskripsi_tugas
);

-- Insert roadmap items untuk 2025
WITH id_2025 AS (
    SELECT id FROM roadmap_periode WHERE tahun_periode = '2025 Q1 - Q4' LIMIT 1
)
INSERT INTO roadmap_item (id_periode, deskripsi_tugas, urutan)
SELECT 
    (SELECT id FROM id_2025), 
    deskripsi_tugas, 
    urutan
FROM (VALUES
    ('Pengembangan proyek penelitian', 1),
    ('Kerjasama dengan industri untuk implementasi teknologi', 2)
) AS v(deskripsi_tugas, urutan)
WHERE NOT EXISTS (
    SELECT 1 FROM roadmap_item 
    WHERE id_periode = (SELECT id FROM id_2025) 
    AND deskripsi_tugas = v.deskripsi_tugas
);

-- ============================================
-- SELESAI
-- ============================================
-- PENTING: Jika pakai DBeaver/pgAdmin, jangan lupa klik tombol COMMIT 
-- (ikon Disket/Centang Hijau) setelah menjalankan script ini!
