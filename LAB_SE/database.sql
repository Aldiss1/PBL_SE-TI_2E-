

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



x
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

