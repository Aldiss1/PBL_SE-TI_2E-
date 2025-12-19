
-- ============================================
-- SETUP: PASTIKAN KOLOM YANG DIPERLUKAN ADA
-- ============================================
-- Script ini memastikan semua kolom yang diperlukan untuk materialized views sudah ada
-- PENTING: Jika error "column already exists", abaikan saja - itu berarti kolom sudah ada!

-- Pastikan kolom email ada di tabel pendaftaran
-- Jika error "column already exists", abaikan - kolom sudah ada
ALTER TABLE pendaftaran ADD COLUMN email VARCHAR(255);

-- Pastikan kolom instagram ada di tabel anggota  
-- Jika error "column already exists", abaikan - kolom sudah ada
ALTER TABLE anggota ADD COLUMN instagram VARCHAR(255);

-- Pastikan kolom linkedin ada di tabel anggota
-- Jika error "column already exists", abaikan - kolom sudah ada
ALTER TABLE anggota ADD COLUMN linkedin VARCHAR(255);

-- ============================================
-- 1. STATISTIK DASHBOARD LENGKAP
-- ============================================
-- View ini menyimpan semua statistik penting untuk dashboard
-- Lebih cepat daripada menghitung COUNT(*) berkali-kali

DROP MATERIALIZED VIEW IF EXISTS mv_stats_complete;
CREATE MATERIALIZED VIEW mv_stats_complete AS
SELECT 
    -- Statistik Anggota
    (SELECT COUNT(*) FROM anggota) AS total_anggota,
    -- Statistik Pendaftar
    (SELECT COUNT(*) FROM pendaftaran) AS total_pendaftar,
    (SELECT COUNT(*) FROM pendaftaran WHERE status = 'pending') AS pendaftar_pending,
    (SELECT COUNT(*) FROM pendaftaran WHERE status = 'accepted') AS pendaftar_accepted,
    (SELECT COUNT(*) FROM pendaftaran WHERE status = 'rejected') AS pendaftar_rejected,
    -- Statistik Jabatan
    (SELECT COUNT(*) FROM jabatan) AS total_jabatan,
    -- Statistik Blog
    (SELECT COUNT(*) FROM blog WHERE is_published = TRUE) AS total_blog_published,
    (SELECT COUNT(*) FROM blog WHERE is_published = FALSE) AS total_blog_draft,
    COALESCE((SELECT SUM(views) FROM blog WHERE is_published = TRUE), 0) AS total_blog_views,
    -- Statistik User
    (SELECT COUNT(*) FROM users) AS total_user,
    (SELECT COUNT(*) FROM users WHERE role = 'admin') AS total_admin,
    (SELECT COUNT(*) FROM users WHERE role = 'mahasiswa') AS total_mahasiswa,
    -- Waktu terakhir di-refresh
    NOW() AS last_refreshed;


-- ============================================
-- 2. PENDAFTAR TERBARU (20 Terbaru)
-- ============================================
-- View ini menyimpan 20 pendaftar terbaru dengan informasi lengkap
-- Digunakan untuk menampilkan di dashboard dan halaman pendaftar

DROP MATERIALIZED VIEW IF EXISTS mv_pendaftar_terbaru_complete;
CREATE MATERIALIZED VIEW mv_pendaftar_terbaru_complete AS
SELECT 
    id,
    nama,
    nim,
    COALESCE(email, '') AS email,
    status,
    tanggal_daftar,
    tanggal_review,
    created_at
FROM pendaftaran
ORDER BY tanggal_daftar DESC
LIMIT 20;



-- ============================================
-- 3. BLOG YANG SUDAH PUBLISHED (LENGKAP)
-- ============================================
-- View ini menyimpan blog yang sudah published dengan informasi lengkap
-- Termasuk views untuk sorting blog populer

DROP MATERIALIZED VIEW IF EXISTS mv_blog_published_complete;
CREATE MATERIALIZED VIEW mv_blog_published_complete AS
SELECT 
    id,
    title,
    slug,
    excerpt,
    featured_image,
    category,
    views,
    published_at,
    created_at,
    author_id
FROM blog
WHERE is_published = TRUE
ORDER BY COALESCE(published_at, created_at) DESC;



-- ============================================
-- 4. STATISTIK PENDAFTAR PER STATUS
-- ============================================
-- View ini menghitung jumlah pendaftar per status
-- Lebih cepat daripada GROUP BY setiap kali query

DROP MATERIALIZED VIEW IF EXISTS mv_pendaftar_per_status;
CREATE MATERIALIZED VIEW mv_pendaftar_per_status AS
SELECT 
    status,
    COUNT(*) as jumlah
FROM pendaftaran
GROUP BY status;

-- Cara pakai:
-- SELECT * FROM mv_pendaftar_per_status;

-- ============================================
-- 5. ANGGOTA PER JABATAN
-- ============================================
-- View ini menghitung jumlah anggota per jabatan
-- Berguna untuk menampilkan distribusi anggota

DROP MATERIALIZED VIEW IF EXISTS mv_anggota_per_jabatan;
CREATE MATERIALIZED VIEW mv_anggota_per_jabatan AS
SELECT 
    jabatan,
    COUNT(*) as jumlah
FROM anggota
WHERE jabatan IS NOT NULL
GROUP BY jabatan
ORDER BY jumlah DESC;

-- Cara pakai:
-- SELECT * FROM mv_anggota_per_jabatan;

-- ============================================
-- 6. BLOG PER KATEGORI
-- ============================================
-- View ini menghitung jumlah blog per kategori
-- Berguna untuk menampilkan statistik blog

DROP MATERIALIZED VIEW IF EXISTS mv_blog_per_kategori;
CREATE MATERIALIZED VIEW mv_blog_per_kategori AS
SELECT 
    category,
    COUNT(*) as jumlah,
    SUM(views) as total_views
FROM blog
WHERE is_published = TRUE AND category IS NOT NULL
GROUP BY category
ORDER BY jumlah DESC;

-- Cara pakai:
-- SELECT * FROM mv_blog_per_kategori;

-- ============================================
-- 7. PENDAFTAR PER BULAN (6 BULAN TERAKHIR)
-- ============================================
-- View ini menghitung jumlah pendaftar per bulan
-- Berguna untuk chart/graph di dashboard

DROP MATERIALIZED VIEW IF EXISTS mv_pendaftar_per_bulan;
CREATE MATERIALIZED VIEW mv_pendaftar_per_bulan AS
SELECT 
    DATE_TRUNC('month', tanggal_daftar) as bulan,
    TO_CHAR(tanggal_daftar, 'YYYY-MM') as bulan_format,
    TO_CHAR(tanggal_daftar, 'Mon YYYY') as bulan_label,
    COUNT(*) as jumlah
FROM pendaftaran
WHERE tanggal_daftar >= DATE_TRUNC('month', CURRENT_DATE - INTERVAL '6 months')
GROUP BY DATE_TRUNC('month', tanggal_daftar), TO_CHAR(tanggal_daftar, 'YYYY-MM'), TO_CHAR(tanggal_daftar, 'Mon YYYY')
ORDER BY bulan DESC;

-- Cara pakai:
-- SELECT * FROM mv_pendaftar_per_bulan;

-- ============================================
-- 8. BLOG POPULER (TOP 10)
-- ============================================
-- View ini menyimpan 10 blog terpopuler berdasarkan views
-- Berguna untuk menampilkan blog yang banyak dibaca

DROP MATERIALIZED VIEW IF EXISTS mv_blog_populer;
CREATE MATERIALIZED VIEW mv_blog_populer AS
SELECT 
    id,
    title,
    slug,
    excerpt,
    featured_image,
    category,
    views,
    published_at
FROM blog
WHERE is_published = TRUE
ORDER BY views DESC
LIMIT 10;

-- Cara pakai:
-- SELECT * FROM mv_blog_populer;

-- ============================================
-- 9. RIWAYAT PERUBAHAN STATUS TERBARU
-- ============================================
-- View ini menyimpan riwayat perubahan status pendaftar terbaru
-- Berguna untuk tracking perubahan status

DROP MATERIALIZED VIEW IF EXISTS mv_riwayat_status_terbaru;
CREATE MATERIALIZED VIEW mv_riwayat_status_terbaru AS
SELECT 
    r.id,
    r.pendaftaran_id,
    p.nama,
    p.nim,
    r.status_lama,
    r.status_baru,
    r.catatan,
    r.changed_at,
    u.username as changed_by_username
FROM riwayat_pendaftaran r
LEFT JOIN pendaftaran p ON r.pendaftaran_id = p.id
LEFT JOIN users u ON r.changed_by = u.id
ORDER BY r.changed_at DESC
LIMIT 50;

-- Cara pakai:
-- SELECT * FROM mv_riwayat_status_terbaru;

-- ============================================
-- 10. ANGGOTA DENGAN JABATAN (LENGKAP)
-- ============================================
-- View ini menyimpan anggota dengan informasi jabatan
-- Berguna untuk menampilkan daftar anggota

DROP MATERIALIZED VIEW IF EXISTS mv_anggota_lengkap;
CREATE MATERIALIZED VIEW mv_anggota_lengkap AS
SELECT 
    a.id,
    a.nama,
    a.jabatan,
    a.foto,
    a.instagram,
    a.linkedin,
    a.deskripsi,
    j.nama_jabatan,
    a.created_at
FROM anggota a
LEFT JOIN jabatan j ON a.jabatan = j.nama_jabatan
ORDER BY a.created_at DESC;

-- Cara pakai:
-- SELECT * FROM mv_anggota_lengkap;
-- SELECT * FROM mv_anggota_lengkap WHERE jabatan = 'Ketua';

-- ============================================
-- 11. STATISTIK BLOG PER BULAN
-- ============================================
-- View ini menghitung jumlah blog yang dipublish per bulan
-- Berguna untuk chart/graph blog

DROP MATERIALIZED VIEW IF EXISTS mv_blog_per_bulan;
CREATE MATERIALIZED VIEW mv_blog_per_bulan AS
SELECT 
    DATE_TRUNC('month', COALESCE(published_at, created_at)) as bulan,
    TO_CHAR(COALESCE(published_at, created_at), 'YYYY-MM') as bulan_format,
    TO_CHAR(COALESCE(published_at, created_at), 'Mon YYYY') as bulan_label,
    COUNT(*) as jumlah
FROM blog
WHERE is_published = TRUE
  AND COALESCE(published_at, created_at) >= DATE_TRUNC('month', CURRENT_DATE - INTERVAL '6 months')
GROUP BY DATE_TRUNC('month', COALESCE(published_at, created_at)), 
         TO_CHAR(COALESCE(published_at, created_at), 'YYYY-MM'),
         TO_CHAR(COALESCE(published_at, created_at), 'Mon YYYY')
ORDER BY bulan DESC;

-- Cara pakai:
-- SELECT * FROM mv_blog_per_bulan;

-- ============================================
-- 12. PENDAFTAR YANG PERLU REVIEW
-- ============================================
-- View ini menyimpan pendaftar yang masih pending dan belum di-review
-- Berguna untuk notifikasi admin

DROP MATERIALIZED VIEW IF EXISTS mv_pendaftar_pending;
CREATE MATERIALIZED VIEW mv_pendaftar_pending AS
SELECT 
    id,
    nama,
    nim,
    COALESCE(email, '') AS email,
    tanggal_daftar,
    created_at
FROM pendaftaran
WHERE status = 'pending'
ORDER BY tanggal_daftar ASC;

-- Cara pakai:
-- SELECT * FROM mv_pendaftar_pending;
-- SELECT COUNT(*) FROM mv_pendaftar_pending;

-- ============================================
-- CARA REFRESH MATERIALIZED VIEWS
-- ============================================
-- Materialized view perlu di-refresh agar datanya update
-- Refresh dilakukan setelah ada perubahan data (INSERT/UPDATE/DELETE)

-- Refresh satu view:
-- REFRESH MATERIALIZED VIEW mv_stats_complete;
-- REFRESH MATERIALIZED VIEW mv_pendaftar_terbaru_complete;
-- REFRESH MATERIALIZED VIEW mv_blog_published_complete;
-- REFRESH MATERIALIZED VIEW mv_pendaftar_per_status;
-- REFRESH MATERIALIZED VIEW mv_anggota_per_jabatan;
-- REFRESH MATERIALIZED VIEW mv_blog_per_kategori;
-- REFRESH MATERIALIZED VIEW mv_pendaftar_per_bulan;
-- REFRESH MATERIALIZED VIEW mv_blog_populer;
-- REFRESH MATERIALIZED VIEW mv_riwayat_status_terbaru;
-- REFRESH MATERIALIZED VIEW mv_anggota_lengkap;
-- REFRESH MATERIALIZED VIEW mv_blog_per_bulan;
-- REFRESH MATERIALIZED VIEW mv_pendaftar_pending;

-- Refresh semua sekaligus (setelah dibuat):
REFRESH MATERIALIZED VIEW mv_stats_complete;
REFRESH MATERIALIZED VIEW mv_pendaftar_terbaru_complete;
REFRESH MATERIALIZED VIEW mv_blog_published_complete;
REFRESH MATERIALIZED VIEW mv_pendaftar_per_status;
REFRESH MATERIALIZED VIEW mv_anggota_per_jabatan;
REFRESH MATERIALIZED VIEW mv_blog_per_kategori;
REFRESH MATERIALIZED VIEW mv_pendaftar_per_bulan;
REFRESH MATERIALIZED VIEW mv_blog_populer;
REFRESH MATERIALIZED VIEW mv_riwayat_status_terbaru;
REFRESH MATERIALIZED VIEW mv_anggota_lengkap;
REFRESH MATERIALIZED VIEW mv_blog_per_bulan;
REFRESH MATERIALIZED VIEW mv_pendaftar_pending;

-- ============================================
-- CATATAN PENTING UNTUK PEMULA
-- ============================================
-- 1. Materialized view = Menyimpan hasil query di database
-- 2. Data di materialized view TIDAK otomatis update
-- 3. Harus di-refresh manual setelah ada perubahan data
-- 4. Refresh bisa dilakukan via SQL atau PHP script
-- 5. Materialized view membuat query lebih cepat
-- 6. Cocok untuk data yang sering dibaca tapi jarang berubah
--
-- KAPAN HARUS REFRESH?
-- - Setelah INSERT data baru
-- - Setelah UPDATE data
-- - Setelah DELETE data
-- - Secara berkala (setiap 15 menit atau 1 jam)
--
-- CONTOH REFRESH OTOMATIS (Cron Job):
-- */15 * * * * psql -d lab_se -c "REFRESH MATERIALIZED VIEW mv_stats_complete;"
--
-- ============================================

