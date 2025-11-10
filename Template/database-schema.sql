-- ============================================
-- Database Schema untuk Web Profile Lab SE
-- Berdasarkan analisis index.html dan file terkait
-- ============================================

-- ============================================
-- 1. Tabel Users (Admin)
-- ============================================
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(255),
    role VARCHAR(50) DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- 2. Tabel Dosen Pengampu
-- ============================================
CREATE TABLE dosen (
    id SERIAL PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    gelar VARCHAR(100),
    posisi VARCHAR(100) NOT NULL, -- Ketua Lab, Wakil Ketua, Dosen Pengampu
    email VARCHAR(255),
    linkedin_url VARCHAR(500),
    foto VARCHAR(500), -- path ke file foto
    bio TEXT,
    urutan INTEGER DEFAULT 0, -- untuk sorting
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- 3. Tabel Pendaftaran (Anggota)
-- ============================================
CREATE TABLE pendaftaran (
    id SERIAL PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    nim VARCHAR(50) NOT NULL,
    jurusan VARCHAR(100) NOT NULL,
    email VARCHAR(255),
    minat TEXT, -- bidang minat penelitian
    status VARCHAR(50) DEFAULT 'pending', -- pending, diterima, ditolak
    tanggal_daftar TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    tanggal_review TIMESTAMP,
    catatan_admin TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- 4. Tabel Personil Lab
-- ============================================
CREATE TABLE personil (
    id SERIAL PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    nim VARCHAR(50),
    posisi VARCHAR(100) NOT NULL, -- Asisten Lab, Koordinator, dll
    jurusan VARCHAR(100),
    email VARCHAR(255),
    foto VARCHAR(500),
    bio TEXT,
    tahun_aktif INTEGER,
    is_active BOOLEAN DEFAULT TRUE,
    urutan INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- 5. Tabel Contact Messages
-- ============================================
CREATE TABLE contact_messages (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    number VARCHAR(50),
    subject VARCHAR(255),
    message TEXT NOT NULL,
    status VARCHAR(50) DEFAULT 'unread', -- unread, read, replied
    replied_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- 6. Tabel Blog/Artikel
-- ============================================
CREATE TABLE blog (
    id SERIAL PRIMARY KEY,
    title VARCHAR(500) NOT NULL,
    slug VARCHAR(500) UNIQUE,
    content TEXT NOT NULL,
    excerpt TEXT, -- ringkasan artikel
    featured_image VARCHAR(500),
    author_id INTEGER REFERENCES users(id),
    category VARCHAR(100),
    tags TEXT[], -- array of tags
    views INTEGER DEFAULT 0,
    is_published BOOLEAN DEFAULT FALSE,
    published_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- 7. Tabel Roadmap Laboratorium
-- ============================================
CREATE TABLE roadmap (
    id SERIAL PRIMARY KEY,
    tahun VARCHAR(10) NOT NULL,
    quarter VARCHAR(10), -- Q1, Q2, Q3, Q4, atau null untuk seluruh tahun
    fase VARCHAR(255), -- Fase Pendirian, Fase Pengembangan, dll
    deskripsi TEXT,
    activities JSONB, -- array of activities/goals dalam format JSON
    status VARCHAR(50) DEFAULT 'planned', -- planned, in_progress, completed
    urutan INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- 8. Tabel Visi & Misi
-- ============================================
CREATE TABLE visi_misi (
    id SERIAL PRIMARY KEY,
    visi TEXT NOT NULL,
    misi JSONB NOT NULL, -- array of misi dalam format JSON
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- 9. Tabel Focus & Scope
-- ============================================
CREATE TABLE focus_scope (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    icon VARCHAR(100), -- nama icon class
    urutan INTEGER DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- 10. Tabel Tentang Kami
-- ============================================
CREATE TABLE tentang_kami (
    id SERIAL PRIMARY KEY,
    deskripsi TEXT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- 11. Tabel Struktur Organisasi
-- ============================================
CREATE TABLE struktur_organisasi (
    id SERIAL PRIMARY KEY,
    nama_jabatan VARCHAR(255) NOT NULL,
    nama_pemegang VARCHAR(255),
    level INTEGER, -- untuk hierarki (1 = tertinggi)
    parent_id INTEGER REFERENCES struktur_organisasi(id), -- untuk struktur hierarkis
    urutan INTEGER DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- INDEXES untuk Optimasi Query
-- ============================================
CREATE INDEX idx_pendaftaran_status ON pendaftaran(status);
CREATE INDEX idx_pendaftaran_tanggal ON pendaftaran(tanggal_daftar);
CREATE INDEX idx_blog_published ON blog(is_published, published_at);
CREATE INDEX idx_blog_slug ON blog(slug);
CREATE INDEX idx_contact_status ON contact_messages(status);
CREATE INDEX idx_dosen_active ON dosen(is_active);
CREATE INDEX idx_personil_active ON personil(is_active);
CREATE INDEX idx_roadmap_tahun ON roadmap(tahun);

-- ============================================
-- INSERT DATA SAMPLE (Optional)
-- ============================================

-- Sample Admin User (password: admin123 - harus di-hash di aplikasi)
INSERT INTO users (username, password, email, full_name, role) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@labse.ac.id', 'Administrator', 'admin');

-- Sample Visi & Misi
INSERT INTO visi_misi (visi, misi) VALUES (
    'Menjadi pusat penelitian software engineering yang relevan dan berdaya guna bagi pengembangan teknologi informasi di Indonesia.',
    '["Melakukan penelitian berkualitas di bidang perangkat lunak yang dapat memberikan kontribusi nyata bagi industri dan akademisi", "Mendorong kolaborasi akademik dan industri untuk menghasilkan inovasi teknologi yang applicable", "Membina mahasiswa melalui proyek penelitian yang berkualitas dan membentuk karakter peneliti yang handal"]'::jsonb
);

-- Sample Tentang Kami
INSERT INTO tentang_kami (deskripsi) VALUES (
    'Lab SE adalah laboratorium penelitian bagi mahasiswa dan dosen yang fokus pada rekayasa perangkat lunak, quality assurance, dan HCI.'
);

-- Sample Focus & Scope
INSERT INTO focus_scope (title, description, icon, urutan) VALUES
    ('Quality Assurance (QA)', 'Pengujian dan jaminan kualitas perangkat lunak menggunakan metodologi terkini', 'lni lni-vector', 1),
    ('Human-Computer Interaction (HCI)', 'Desain interaksi dan pengalaman pengguna untuk aplikasi yang user-friendly', 'lni lni-layers', 2),
    ('Machine Learning untuk Software Engineering', 'Penerapan ML dalam optimasi proses pengembangan perangkat lunak', 'lni lni-code-alt', 3);

-- Sample Roadmap
INSERT INTO roadmap (tahun, quarter, fase, deskripsi, activities, status) VALUES
    ('2024', 'Q1-Q4', 'Fase Pendirian & Setup', 'Fase awal pendirian laboratorium', 
     '["Pembentukan struktur organisasi laboratorium", "Pengadaan infrastruktur dan peralatan dasar", "Rekrutmen anggota dan asisten laboratorium", "Peluncuran website dan sistem informasi lab"]'::jsonb, 
     'completed'),
    ('2025', 'Q1-Q4', 'Fase Pengembangan & Riset', 'Fase pengembangan dan penelitian', 
     '["Pengembangan proyek penelitian", "Kerjasama dengan industri untuk implementasi teknologi"]'::jsonb, 
     'in_progress');

-- Sample Dosen (berdasarkan data di index.html)
INSERT INTO dosen (nama, gelar, posisi, email, linkedin_url, foto, urutan) VALUES
    ('Andi Pratama', 'Dr.', 'Ketua Lab', 'andi.pratama@universitas.ac.id', '#', 'assets/images/dosen1.jpg', 1),
    ('Siti Nurhaliza', 'Dr.', 'Wakil Ketua', 'siti.nurhaliza@universitas.ac.id', '#', 'assets/images/dosen2.jpg', 2),
    ('Budi Santoso', 'M.Kom', 'Dosen Pengampu', 'budi.santoso@universitas.ac.id', '#', 'assets/images/dosen3.jpg', 3),
    ('Lia Zahra', 'M.T.', 'Dosen Pengampu', 'lia.zahra@universitas.ac.id', '#', 'assets/images/dosen4.jpg', 4),
    ('Dani Rahman', 'Ir., M.Eng', 'Dosen Pengampu', 'dani.rahman@universitas.ac.id', '#', 'assets/images/dosen5.jpg', 5),
    ('Rina Mayang', 'M.Cs', 'Dosen Pengampu', 'rina.mayang@universitas.ac.id', '#', 'assets/images/dosen6.jpg', 6);

