-- ============================================
-- STORED PROCEDURES LAB SE
-- ============================================
-- File ini berisi stored procedures untuk operasi database
-- Jalankan file ini setelah database.sql selesai dijalankan
-- ============================================

-- ============================================
-- 1. STORED PROCEDURE: INSERT PENELITIAN
-- ============================================
CREATE OR REPLACE FUNCTION sp_insert_penelitian(
    p_judul VARCHAR(500),
    p_tahun INTEGER,
    p_deskripsi TEXT DEFAULT NULL,
    p_status VARCHAR(50) DEFAULT 'ongoing',
    p_jumlah_tim INTEGER DEFAULT 0,
    p_peneliti_utama VARCHAR(255) DEFAULT NULL,
    p_anggota_tim TEXT DEFAULT NULL,
    p_dosen_pembimbing VARCHAR(255) DEFAULT NULL,
    p_kategori VARCHAR(100) DEFAULT NULL,
    p_metodologi TEXT DEFAULT NULL,
    p_tanggal_mulai DATE DEFAULT NULL,
    p_tanggal_selesai DATE DEFAULT NULL,
    p_progress INTEGER DEFAULT 0,
    p_keywords TEXT DEFAULT NULL,
    p_link_dokumen VARCHAR(500) DEFAULT NULL,
    p_output_hasil TEXT DEFAULT NULL
)
RETURNS INTEGER AS $$
DECLARE
    v_id INTEGER;
BEGIN
    INSERT INTO penelitian (
        judul, deskripsi, tahun, status, jumlah_tim,
        peneliti_utama, anggota_tim, dosen_pembimbing, kategori, metodologi,
        tanggal_mulai, tanggal_selesai, progress, keywords, link_dokumen, output_hasil
    ) VALUES (
        p_judul, p_deskripsi, p_tahun, p_status, p_jumlah_tim,
        p_peneliti_utama, p_anggota_tim, p_dosen_pembimbing, p_kategori, p_metodologi,
        p_tanggal_mulai, p_tanggal_selesai, p_progress, p_keywords, p_link_dokumen, p_output_hasil
    ) RETURNING id INTO v_id;
    
    RETURN v_id;
END;
$$ LANGUAGE plpgsql;

-- ============================================
-- 2. STORED PROCEDURE: UPDATE PENELITIAN
-- ============================================
CREATE OR REPLACE FUNCTION sp_update_penelitian(
    p_id INTEGER,
    p_judul VARCHAR(500),
    p_tahun INTEGER,
    p_deskripsi TEXT DEFAULT NULL,
    p_status VARCHAR(50) DEFAULT 'ongoing',
    p_jumlah_tim INTEGER DEFAULT 0,
    p_peneliti_utama VARCHAR(255) DEFAULT NULL,
    p_anggota_tim TEXT DEFAULT NULL,
    p_dosen_pembimbing VARCHAR(255) DEFAULT NULL,
    p_kategori VARCHAR(100) DEFAULT NULL,
    p_metodologi TEXT DEFAULT NULL,
    p_tanggal_mulai DATE DEFAULT NULL,
    p_tanggal_selesai DATE DEFAULT NULL,
    p_progress INTEGER DEFAULT 0,
    p_keywords TEXT DEFAULT NULL,
    p_link_dokumen VARCHAR(500) DEFAULT NULL,
    p_output_hasil TEXT DEFAULT NULL
)
RETURNS BOOLEAN AS $$
BEGIN
    UPDATE penelitian SET
        judul = p_judul,
        deskripsi = p_deskripsi,
        tahun = p_tahun,
        status = p_status,
        jumlah_tim = p_jumlah_tim,
        peneliti_utama = p_peneliti_utama,
        anggota_tim = p_anggota_tim,
        dosen_pembimbing = p_dosen_pembimbing,
        kategori = p_kategori,
        metodologi = p_metodologi,
        tanggal_mulai = p_tanggal_mulai,
        tanggal_selesai = p_tanggal_selesai,
        progress = p_progress,
        keywords = p_keywords,
        link_dokumen = p_link_dokumen,
        output_hasil = p_output_hasil,
        updated_at = CURRENT_TIMESTAMP
    WHERE id = p_id;
    
    RETURN FOUND;
END;
$$ LANGUAGE plpgsql;

-- ============================================
-- 3. STORED PROCEDURE: DELETE PENELITIAN
-- ============================================
CREATE OR REPLACE FUNCTION sp_delete_penelitian(p_id INTEGER)
RETURNS BOOLEAN AS $$
BEGIN
    DELETE FROM penelitian WHERE id = p_id;
    RETURN FOUND;
END;
$$ LANGUAGE plpgsql;

-- ============================================
-- 4. STORED PROCEDURE: INSERT PUBLIKASI
-- ============================================
CREATE OR REPLACE FUNCTION sp_insert_publikasi(
    p_judul TEXT,
    p_tipe VARCHAR(50),
    p_tahun INTEGER,
    p_penulis_utama VARCHAR(255) DEFAULT NULL,
    p_daftar_penulis TEXT DEFAULT NULL,
    p_nama_journal_conference VARCHAR(500) DEFAULT NULL,
    p_volume VARCHAR(50) DEFAULT NULL,
    p_issue VARCHAR(50) DEFAULT NULL,
    p_halaman VARCHAR(50) DEFAULT NULL,
    p_doi VARCHAR(255) DEFAULT NULL,
    p_abstract TEXT DEFAULT NULL,
    p_keywords TEXT DEFAULT NULL,
    p_link_url VARCHAR(500) DEFAULT NULL,
    p_status VARCHAR(50) DEFAULT 'published',
    p_scopus_indexed BOOLEAN DEFAULT FALSE,
    p_quartile VARCHAR(10) DEFAULT NULL,
    p_tanggal_publikasi DATE DEFAULT NULL
)
RETURNS INTEGER AS $$
DECLARE
    v_id INTEGER;
BEGIN
    INSERT INTO publikasi (
        judul, tipe, tahun, penulis_utama, daftar_penulis,
        nama_journal_conference, volume, issue, halaman, doi,
        abstract, keywords, link_url, status, scopus_indexed, quartile, tanggal_publikasi
    ) VALUES (
        p_judul, p_tipe, p_tahun, p_penulis_utama, p_daftar_penulis,
        p_nama_journal_conference, p_volume, p_issue, p_halaman, p_doi,
        p_abstract, p_keywords, p_link_url, p_status, p_scopus_indexed, p_quartile, p_tanggal_publikasi
    ) RETURNING id INTO v_id;
    
    RETURN v_id;
END;
$$ LANGUAGE plpgsql;

-- ============================================
-- 5. STORED PROCEDURE: UPDATE PUBLIKASI
-- ============================================
CREATE OR REPLACE FUNCTION sp_update_publikasi(
    p_id INTEGER,
    p_judul TEXT,
    p_tipe VARCHAR(50),
    p_tahun INTEGER,
    p_penulis_utama VARCHAR(255) DEFAULT NULL,
    p_daftar_penulis TEXT DEFAULT NULL,
    p_nama_journal_conference VARCHAR(500) DEFAULT NULL,
    p_volume VARCHAR(50) DEFAULT NULL,
    p_issue VARCHAR(50) DEFAULT NULL,
    p_halaman VARCHAR(50) DEFAULT NULL,
    p_doi VARCHAR(255) DEFAULT NULL,
    p_abstract TEXT DEFAULT NULL,
    p_keywords TEXT DEFAULT NULL,
    p_link_url VARCHAR(500) DEFAULT NULL,
    p_status VARCHAR(50) DEFAULT 'published',
    p_scopus_indexed BOOLEAN DEFAULT FALSE,
    p_quartile VARCHAR(10) DEFAULT NULL,
    p_tanggal_publikasi DATE DEFAULT NULL
)
RETURNS BOOLEAN AS $$
BEGIN
    UPDATE publikasi SET
        judul = p_judul,
        tipe = p_tipe,
        tahun = p_tahun,
        penulis_utama = p_penulis_utama,
        daftar_penulis = p_daftar_penulis,
        nama_journal_conference = p_nama_journal_conference,
        volume = p_volume,
        issue = p_issue,
        halaman = p_halaman,
        doi = p_doi,
        abstract = p_abstract,
        keywords = p_keywords,
        link_url = p_link_url,
        status = p_status,
        scopus_indexed = p_scopus_indexed,
        quartile = p_quartile,
        tanggal_publikasi = p_tanggal_publikasi,
        updated_at = CURRENT_TIMESTAMP
    WHERE id = p_id;
    
    RETURN FOUND;
END;
$$ LANGUAGE plpgsql;

-- ============================================
-- 6. STORED PROCEDURE: DELETE PUBLIKASI
-- ============================================
CREATE OR REPLACE FUNCTION sp_delete_publikasi(p_id INTEGER)
RETURNS BOOLEAN AS $$
BEGIN
    DELETE FROM publikasi WHERE id = p_id;
    RETURN FOUND;
END;
$$ LANGUAGE plpgsql;

-- ============================================
-- 7. STORED PROCEDURE: GET STATISTIK DASHBOARD
-- ============================================
-- Note: Setup script akan otomatis drop function ini sebelum create
-- Jika masih error, jalankan manual: DROP FUNCTION sp_get_statistik_dashboard() CASCADE;
CREATE OR REPLACE FUNCTION sp_get_statistik_dashboard()
RETURNS TABLE (
    total_anggota BIGINT,
    total_pendaftar BIGINT,
    pendaftar_pending BIGINT,
    pendaftar_accepted BIGINT,
    pendaftar_rejected BIGINT,
    total_penelitian BIGINT,
    penelitian_ongoing BIGINT,
    penelitian_completed BIGINT,
    total_publikasi BIGINT,
    publikasi_journal BIGINT,
    publikasi_conference BIGINT,
    publikasi_scopus BIGINT,
    total_blog BIGINT,
    blog_published BIGINT,
    blog_draft BIGINT,
    total_user BIGINT,
    total_jabatan BIGINT
) AS $$
BEGIN
    RETURN QUERY
    SELECT
        (SELECT COUNT(*) FROM anggota)::BIGINT AS total_anggota,
        (SELECT COUNT(*) FROM pendaftaran)::BIGINT AS total_pendaftar,
        (SELECT COUNT(*) FROM pendaftaran WHERE status = 'pending')::BIGINT AS pendaftar_pending,
        (SELECT COUNT(*) FROM pendaftaran WHERE status = 'accepted')::BIGINT AS pendaftar_accepted,
        (SELECT COUNT(*) FROM pendaftaran WHERE status = 'rejected')::BIGINT AS pendaftar_rejected,
        (SELECT COUNT(*) FROM penelitian)::BIGINT AS total_penelitian,
        (SELECT COUNT(*) FROM penelitian WHERE status = 'ongoing')::BIGINT AS penelitian_ongoing,
        (SELECT COUNT(*) FROM penelitian WHERE status = 'completed')::BIGINT AS penelitian_completed,
        (SELECT COUNT(*) FROM publikasi)::BIGINT AS total_publikasi,
        (SELECT COUNT(*) FROM publikasi WHERE tipe = 'journal')::BIGINT AS publikasi_journal,
        (SELECT COUNT(*) FROM publikasi WHERE tipe = 'conference')::BIGINT AS publikasi_conference,
        (SELECT COUNT(*) FROM publikasi WHERE scopus_indexed = TRUE)::BIGINT AS publikasi_scopus,
        (SELECT COUNT(*) FROM blog)::BIGINT AS total_blog,
        (SELECT COUNT(*) FROM blog WHERE is_published = TRUE)::BIGINT AS blog_published,
        (SELECT COUNT(*) FROM blog WHERE is_published = FALSE)::BIGINT AS blog_draft,
        (SELECT COUNT(*) FROM users)::BIGINT AS total_user,
        (SELECT COUNT(*) FROM jabatan)::BIGINT AS total_jabatan;
END;
$$ LANGUAGE plpgsql;

-- ============================================
-- 8. STORED PROCEDURE: GET PENELITIAN BY STATUS
-- ============================================
CREATE OR REPLACE FUNCTION sp_get_penelitian_by_status(p_status VARCHAR(50))
RETURNS TABLE (
    id INTEGER,
    judul VARCHAR(500),
    deskripsi TEXT,
    tahun INTEGER,
    status VARCHAR(50),
    jumlah_tim INTEGER,
    peneliti_utama VARCHAR(255),
    kategori VARCHAR(100),
    progress INTEGER,
    created_at TIMESTAMP
) AS $$
BEGIN
    RETURN QUERY
    SELECT
        p.id,
        p.judul,
        p.deskripsi,
        p.tahun,
        p.status,
        p.jumlah_tim,
        p.peneliti_utama,
        p.kategori,
        p.progress,
        p.created_at
    FROM penelitian p
    WHERE p.status = p_status
    ORDER BY p.tahun DESC, p.id DESC;
END;
$$ LANGUAGE plpgsql;

-- ============================================
-- 9. STORED PROCEDURE: GET PUBLIKASI BY TIPE
-- ============================================
CREATE OR REPLACE FUNCTION sp_get_publikasi_by_tipe(p_tipe VARCHAR(50))
RETURNS TABLE (
    id INTEGER,
    judul TEXT,
    tipe VARCHAR(50),
    tahun INTEGER,
    penulis_utama VARCHAR(255),
    nama_journal_conference VARCHAR(500),
    volume VARCHAR(50),
    issue VARCHAR(50),
    doi VARCHAR(255),
    status VARCHAR(50),
    scopus_indexed BOOLEAN,
    quartile VARCHAR(10),
    created_at TIMESTAMP
) AS $$
BEGIN
    RETURN QUERY
    SELECT
        pub.id,
        pub.judul,
        pub.tipe,
        pub.tahun,
        pub.penulis_utama,
        pub.nama_journal_conference,
        pub.volume,
        pub.issue,
        pub.doi,
        pub.status,
        pub.scopus_indexed,
        pub.quartile,
        pub.created_at
    FROM publikasi pub
    WHERE pub.tipe = p_tipe AND pub.status = 'published'
    ORDER BY pub.tahun DESC, pub.id DESC;
END;
$$ LANGUAGE plpgsql;

-- ============================================
-- 10. STORED PROCEDURE: SEARCH PENELITIAN
-- ============================================
CREATE OR REPLACE FUNCTION sp_search_penelitian(p_search_term TEXT)
RETURNS TABLE (
    id INTEGER,
    judul VARCHAR(500),
    deskripsi TEXT,
    tahun INTEGER,
    status VARCHAR(50),
    peneliti_utama VARCHAR(255),
    kategori VARCHAR(100)
) AS $$
BEGIN
    RETURN QUERY
    SELECT
        p.id,
        p.judul,
        p.deskripsi,
        p.tahun,
        p.status,
        p.peneliti_utama,
        p.kategori
    FROM penelitian p
    WHERE 
        p.judul ILIKE '%' || p_search_term || '%'
        OR p.deskripsi ILIKE '%' || p_search_term || '%'
        OR p.peneliti_utama ILIKE '%' || p_search_term || '%'
        OR p.kategori ILIKE '%' || p_search_term || '%'
        OR p.keywords ILIKE '%' || p_search_term || '%'
    ORDER BY p.tahun DESC, p.id DESC;
END;
$$ LANGUAGE plpgsql;

-- ============================================
-- 11. STORED PROCEDURE: SEARCH PUBLIKASI
-- ============================================
CREATE OR REPLACE FUNCTION sp_search_publikasi(p_search_term TEXT)
RETURNS TABLE (
    id INTEGER,
    judul TEXT,
    tipe VARCHAR(50),
    tahun INTEGER,
    penulis_utama VARCHAR(255),
    nama_journal_conference VARCHAR(500),
    status VARCHAR(50)
) AS $$
BEGIN
    RETURN QUERY
    SELECT
        pub.id,
        pub.judul,
        pub.tipe,
        pub.tahun,
        pub.penulis_utama,
        pub.nama_journal_conference,
        pub.status
    FROM publikasi pub
    WHERE 
        pub.judul ILIKE '%' || p_search_term || '%'
        OR pub.penulis_utama ILIKE '%' || p_search_term || '%'
        OR pub.daftar_penulis ILIKE '%' || p_search_term || '%'
        OR pub.nama_journal_conference ILIKE '%' || p_search_term || '%'
        OR pub.keywords ILIKE '%' || p_search_term || '%'
        OR pub.abstract ILIKE '%' || p_search_term || '%'
    ORDER BY pub.tahun DESC, pub.id DESC;
END;
$$ LANGUAGE plpgsql;

-- ============================================
-- 12. STORED PROCEDURE: UPDATE STATUS PENDAFTARAN
-- ============================================
CREATE OR REPLACE FUNCTION sp_update_status_pendaftaran(
    p_id INTEGER,
    p_status_baru VARCHAR(50),
    p_catatan TEXT DEFAULT NULL,
    p_user_id INTEGER DEFAULT NULL
)
RETURNS BOOLEAN AS $$
DECLARE
    v_status_lama VARCHAR(50);
BEGIN
    -- Ambil status lama
    SELECT status INTO v_status_lama FROM pendaftaran WHERE id = p_id;
    
    IF NOT FOUND THEN
        RETURN FALSE;
    END IF;
    
    -- Update status
    UPDATE pendaftaran SET
        status = p_status_baru,
        tanggal_review = CURRENT_TIMESTAMP,
        updated_at = CURRENT_TIMESTAMP
    WHERE id = p_id;
    
    -- Insert ke riwayat
    INSERT INTO riwayat_pendaftaran (
        pendaftaran_id, status_lama, status_baru, catatan, changed_by
    ) VALUES (
        p_id, v_status_lama, p_status_baru, p_catatan, p_user_id
    );
    
    RETURN TRUE;
END;
$$ LANGUAGE plpgsql;

-- ============================================
-- 13. STORED PROCEDURE: GET PENELITIAN BY TAHUN
-- ============================================
CREATE OR REPLACE FUNCTION sp_get_penelitian_by_tahun(p_tahun INTEGER)
RETURNS TABLE (
    id INTEGER,
    judul VARCHAR(500),
    tahun INTEGER,
    status VARCHAR(50),
    peneliti_utama VARCHAR(255),
    kategori VARCHAR(100)
) AS $$
BEGIN
    RETURN QUERY
    SELECT
        p.id,
        p.judul,
        p.tahun,
        p.status,
        p.peneliti_utama,
        p.kategori
    FROM penelitian p
    WHERE p.tahun = p_tahun
    ORDER BY p.id DESC;
END;
$$ LANGUAGE plpgsql;

-- ============================================
-- 14. STORED PROCEDURE: GET PUBLIKASI BY TAHUN
-- ============================================
CREATE OR REPLACE FUNCTION sp_get_publikasi_by_tahun(p_tahun INTEGER)
RETURNS TABLE (
    id INTEGER,
    judul TEXT,
    tipe VARCHAR(50),
    tahun INTEGER,
    penulis_utama VARCHAR(255),
    nama_journal_conference VARCHAR(500),
    scopus_indexed BOOLEAN
) AS $$
BEGIN
    RETURN QUERY
    SELECT
        pub.id,
        pub.judul,
        pub.tipe,
        pub.tahun,
        pub.penulis_utama,
        pub.nama_journal_conference,
        pub.scopus_indexed
    FROM publikasi pub
    WHERE pub.tahun = p_tahun AND pub.status = 'published'
    ORDER BY pub.id DESC;
END;
$$ LANGUAGE plpgsql;

-- ============================================
-- 15. STORED PROCEDURE: GET PUBLIKASI BY QUARTILE
-- ============================================
CREATE OR REPLACE FUNCTION sp_get_publikasi_by_quartile(p_quartile VARCHAR(10))
RETURNS TABLE (
    id INTEGER,
    judul TEXT,
    tipe VARCHAR(50),
    tahun INTEGER,
    penulis_utama VARCHAR(255),
    nama_journal_conference VARCHAR(500),
    quartile VARCHAR(10),
    scopus_indexed BOOLEAN
) AS $$
BEGIN
    RETURN QUERY
    SELECT
        pub.id,
        pub.judul,
        pub.tipe,
        pub.tahun,
        pub.penulis_utama,
        pub.nama_journal_conference,
        pub.quartile,
        pub.scopus_indexed
    FROM publikasi pub
    WHERE pub.quartile = p_quartile AND pub.status = 'published'
    ORDER BY pub.tahun DESC, pub.id DESC;
END;
$$ LANGUAGE plpgsql;

-- ============================================
-- 16. STORED PROCEDURE: GET PENELITIAN BY KATEGORI
-- ============================================
CREATE OR REPLACE FUNCTION sp_get_penelitian_by_kategori(p_kategori VARCHAR(100))
RETURNS TABLE (
    id INTEGER,
    judul VARCHAR(500),
    tahun INTEGER,
    status VARCHAR(50),
    peneliti_utama VARCHAR(255),
    kategori VARCHAR(100),
    progress INTEGER
) AS $$
BEGIN
    RETURN QUERY
    SELECT
        p.id,
        p.judul,
        p.tahun,
        p.status,
        p.peneliti_utama,
        p.kategori,
        p.progress
    FROM penelitian p
    WHERE p.kategori = p_kategori
    ORDER BY p.tahun DESC, p.id DESC;
END;
$$ LANGUAGE plpgsql;

-- ============================================
-- 17. STORED PROCEDURE: GET STATISTIK PENELITIAN
-- ============================================
CREATE OR REPLACE FUNCTION sp_get_statistik_penelitian()
RETURNS TABLE (
    total BIGINT,
    ongoing BIGINT,
    completed BIGINT,
    by_kategori JSONB,
    by_tahun JSONB
) AS $$
BEGIN
    RETURN QUERY
    SELECT
        (SELECT COUNT(*) FROM penelitian)::BIGINT AS total,
        (SELECT COUNT(*) FROM penelitian WHERE status = 'ongoing')::BIGINT AS ongoing,
        (SELECT COUNT(*) FROM penelitian WHERE status = 'completed')::BIGINT AS completed,
        (SELECT jsonb_object_agg(kategori, count) 
         FROM (SELECT kategori, COUNT(*) as count FROM penelitian WHERE kategori IS NOT NULL GROUP BY kategori) sub) AS by_kategori,
        (SELECT jsonb_object_agg(tahun::text, count) 
         FROM (SELECT tahun, COUNT(*) as count FROM penelitian GROUP BY tahun ORDER BY tahun DESC) sub) AS by_tahun;
END;
$$ LANGUAGE plpgsql;

-- ============================================
-- 18. STORED PROCEDURE: GET STATISTIK PUBLIKASI
-- ============================================
CREATE OR REPLACE FUNCTION sp_get_statistik_publikasi()
RETURNS TABLE (
    total BIGINT,
    journal BIGINT,
    conference BIGINT,
    scopus BIGINT,
    by_quartile JSONB,
    by_tahun JSONB
) AS $$
BEGIN
    RETURN QUERY
    SELECT
        (SELECT COUNT(*) FROM publikasi WHERE status = 'published')::BIGINT AS total,
        (SELECT COUNT(*) FROM publikasi WHERE tipe = 'journal' AND status = 'published')::BIGINT AS journal,
        (SELECT COUNT(*) FROM publikasi WHERE tipe = 'conference' AND status = 'published')::BIGINT AS conference,
        (SELECT COUNT(*) FROM publikasi WHERE scopus_indexed = TRUE AND status = 'published')::BIGINT AS scopus,
        (SELECT jsonb_object_agg(quartile, count) 
         FROM (SELECT quartile, COUNT(*) as count FROM publikasi WHERE quartile IS NOT NULL AND status = 'published' GROUP BY quartile) sub) AS by_quartile,
        (SELECT jsonb_object_agg(tahun::text, count) 
         FROM (SELECT tahun, COUNT(*) as count FROM publikasi WHERE status = 'published' GROUP BY tahun ORDER BY tahun DESC) sub) AS by_tahun;
END;
$$ LANGUAGE plpgsql;

-- ============================================
-- 19. STORED PROCEDURE: VALIDATE PENELITIAN
-- ============================================
CREATE OR REPLACE FUNCTION sp_validate_penelitian(
    p_judul VARCHAR(500),
    p_tahun INTEGER
)
RETURNS TABLE (
    is_valid BOOLEAN,
    error_message TEXT
) AS $$
DECLARE
    v_error TEXT;
BEGIN
    -- Validasi judul
    IF p_judul IS NULL OR LENGTH(TRIM(p_judul)) = 0 THEN
        RETURN QUERY SELECT FALSE, 'Judul penelitian tidak boleh kosong'::TEXT;
        RETURN;
    END IF;
    
    -- Validasi tahun
    IF p_tahun IS NULL OR p_tahun < 2000 OR p_tahun > EXTRACT(YEAR FROM CURRENT_DATE) + 1 THEN
        RETURN QUERY SELECT FALSE, 'Tahun tidak valid'::TEXT;
        RETURN;
    END IF;
    
    -- Cek duplikat
    IF EXISTS (SELECT 1 FROM penelitian WHERE judul = p_judul AND tahun = p_tahun) THEN
        RETURN QUERY SELECT FALSE, 'Penelitian dengan judul dan tahun yang sama sudah ada'::TEXT;
        RETURN;
    END IF;
    
    RETURN QUERY SELECT TRUE, NULL::TEXT;
END;
$$ LANGUAGE plpgsql;

-- ============================================
-- 20. STORED PROCEDURE: VALIDATE PUBLIKASI
-- ============================================
CREATE OR REPLACE FUNCTION sp_validate_publikasi(
    p_judul TEXT,
    p_tipe VARCHAR(50),
    p_tahun INTEGER
)
RETURNS TABLE (
    is_valid BOOLEAN,
    error_message TEXT
) AS $$
DECLARE
    v_error TEXT;
BEGIN
    -- Validasi judul
    IF p_judul IS NULL OR LENGTH(TRIM(p_judul)) = 0 THEN
        RETURN QUERY SELECT FALSE, 'Judul publikasi tidak boleh kosong'::TEXT;
        RETURN;
    END IF;
    
    -- Validasi tipe
    IF p_tipe NOT IN ('journal', 'conference') THEN
        RETURN QUERY SELECT FALSE, 'Tipe publikasi harus journal atau conference'::TEXT;
        RETURN;
    END IF;
    
    -- Validasi tahun
    IF p_tahun IS NULL OR p_tahun < 2000 OR p_tahun > EXTRACT(YEAR FROM CURRENT_DATE) + 1 THEN
        RETURN QUERY SELECT FALSE, 'Tahun tidak valid'::TEXT;
        RETURN;
    END IF;
    
    RETURN QUERY SELECT TRUE, NULL::TEXT;
END;
$$ LANGUAGE plpgsql;

-- ============================================
-- COMMENTS UNTUK DOKUMENTASI
-- ============================================
COMMENT ON FUNCTION sp_insert_penelitian IS 'Insert data penelitian baru, return ID';
COMMENT ON FUNCTION sp_update_penelitian IS 'Update data penelitian berdasarkan ID';
COMMENT ON FUNCTION sp_delete_penelitian IS 'Hapus data penelitian berdasarkan ID';
COMMENT ON FUNCTION sp_insert_publikasi IS 'Insert data publikasi baru, return ID';
COMMENT ON FUNCTION sp_update_publikasi IS 'Update data publikasi berdasarkan ID';
COMMENT ON FUNCTION sp_delete_publikasi IS 'Hapus data publikasi berdasarkan ID';
COMMENT ON FUNCTION sp_get_statistik_dashboard IS 'Ambil semua statistik untuk dashboard';
COMMENT ON FUNCTION sp_get_penelitian_by_status IS 'Ambil penelitian berdasarkan status (ongoing/completed)';
COMMENT ON FUNCTION sp_get_publikasi_by_tipe IS 'Ambil publikasi berdasarkan tipe (journal/conference)';
COMMENT ON FUNCTION sp_search_penelitian IS 'Cari penelitian berdasarkan keyword';
COMMENT ON FUNCTION sp_search_publikasi IS 'Cari publikasi berdasarkan keyword';
COMMENT ON FUNCTION sp_update_status_pendaftaran IS 'Update status pendaftaran dan simpan ke riwayat';
COMMENT ON FUNCTION sp_get_penelitian_by_tahun IS 'Ambil penelitian berdasarkan tahun';
COMMENT ON FUNCTION sp_get_publikasi_by_tahun IS 'Ambil publikasi berdasarkan tahun';
COMMENT ON FUNCTION sp_get_publikasi_by_quartile IS 'Ambil publikasi berdasarkan quartile (Q1-Q4)';
COMMENT ON FUNCTION sp_get_penelitian_by_kategori IS 'Ambil penelitian berdasarkan kategori';
COMMENT ON FUNCTION sp_get_statistik_penelitian IS 'Ambil statistik penelitian (total, by kategori, by tahun)';
COMMENT ON FUNCTION sp_get_statistik_publikasi IS 'Ambil statistik publikasi (total, by quartile, by tahun)';
COMMENT ON FUNCTION sp_validate_penelitian IS 'Validasi data penelitian sebelum insert/update';
COMMENT ON FUNCTION sp_validate_publikasi IS 'Validasi data publikasi sebelum insert/update';

-- ============================================
-- SELESAI
-- ============================================

