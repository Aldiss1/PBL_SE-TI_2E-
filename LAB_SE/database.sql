

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
    full_name VARCHAR(255),
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
    deskripsi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS pendaftaran (
    id SERIAL PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    nim VARCHAR(50) NOT NULL,
    jurusan VARCHAR(100) NOT NULL,
    minat TEXT,
    status VARCHAR(50) DEFAULT 'pending',
    tanggal_daftar TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    tanggal_review TIMESTAMP,
    catatan_admin TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

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

INSERT INTO jabatan (nama_jabatan) VALUES
    ('Ketua Lab'),
    ('Wakil Ketua Lab'),
    ('Dosen Pengampu'),
    ('Koordinator Penelitian'),
    ('Dosen Pembimbing')
ON CONFLICT DO NOTHING;

INSERT INTO anggota (nama, jabatan, foto, deskripsi) VALUES
    ('Dr. Andi Pratama, S.Kom., M.Kom.', 'Ketua Lab', 'assets/img/team/team-1/team-1.png', 'Ketua Laboratorium Software Engineering dengan fokus penelitian di bidang Quality Assurance dan Software Testing.'),
    ('Dr. Siti Nurhaliza, S.T., M.T.', 'Wakil Ketua Lab', 'assets/img/team/team-1/team-2.png', 'Wakil Ketua Laboratorium dengan spesialisasi di bidang Human-Computer Interaction (HCI) dan User Experience Design.'),
    ('Budi Santoso, M.Kom.', 'Dosen Pengampu', 'assets/img/team/team-1/team-3.png', 'Dosen pengampu dengan keahlian di bidang Software Engineering dan Agile Development.'),
    ('Lia Zahra, M.T.', 'Dosen Pengampu', 'assets/img/team/team-1/team-4.png', 'Dosen pengampu yang fokus pada penelitian Machine Learning untuk Software Engineering.'),
    ('Ir. Dani Rahman, M.Eng.', 'Dosen Pengampu', 'assets/img/team/team-1/team-1.png', 'Dosen pengampu dengan latar belakang teknik informatika dan spesialisasi di bidang Software Architecture.'),
    ('Rina Mayang, M.Cs.', 'Dosen Pengampu', 'assets/img/team/team-1/team-2.png', 'Dosen pengampu dengan keahlian di bidang Database Systems dan Data Engineering.'),
    ('Ahmad Fauzi, S.Kom., M.T.I.', 'Dosen Pengampu', 'assets/img/team/team-1/team-3.png', 'Dosen pengampu yang fokus pada penelitian Cloud Computing dan DevOps.'),
    ('Sari Dewi, M.Kom.', 'Dosen Pengampu', 'assets/img/team/team-1/team-4.png', 'Dosen pengampu dengan spesialisasi di bidang Mobile Application Development.'),
    ('Prof. Dr. H. Muhammad Rizki, S.T., M.T.', 'Koordinator Penelitian', 'assets/img/team/team-1/team-1.png', 'Koordinator penelitian dengan fokus pada Software Engineering Research dan Academic Publication.'),
    ('Dr. Fitri Handayani, S.Kom., M.Sc.', 'Dosen Pembimbing', 'assets/img/team/team-1/team-2.png', 'Dosen pembimbing yang fokus pada penelitian Software Quality Assurance dan Testing Automation.')
ON CONFLICT DO NOTHING;

