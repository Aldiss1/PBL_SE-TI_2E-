
-- 1. STATISTIK DASHBOARD

-- View ini menyimpan jumlah total dari setiap tabel
-- Digunakan untuk menampilkan statistik di dashboard admin

DROP MATERIALIZED VIEW IF EXISTS mv_stats_simple;
CREATE MATERIALIZED VIEW mv_stats_simple AS
SELECT 
    (SELECT COUNT(*) FROM anggota) as total_anggota,
    (SELECT COUNT(*) FROM pendaftaran) as total_pendaftar,
    (SELECT COUNT(*) FROM pendaftaran WHERE status = 'pending') as pendaftar_pending,
    (SELECT COUNT(*) FROM pendaftaran WHERE status = 'accepted') as pendaftar_accepted,
    (SELECT COUNT(*) FROM pendaftaran WHERE status = 'rejected') as pendaftar_rejected,
    (SELECT COUNT(*) FROM jabatan) as total_jabatan,
    (SELECT COUNT(*) FROM blog WHERE is_published = TRUE) as total_blog;

-- Cara pakai:
-- SELECT * FROM mv_stats_simple;

-- ============================================
-- 2. PENDAFTAR TERBARU (10 Terbaru)
-- ============================================
-- View ini menyimpan 10 pendaftar terbaru
-- Digunakan untuk menampilkan di dashboard

DROP MATERIALIZED VIEW IF EXISTS mv_pendaftar_terbaru;
CREATE MATERIALIZED VIEW mv_pendaftar_terbaru AS
SELECT 
    id,
    nama,
    nim,
    email,
    status,
    tanggal_daftar
FROM pendaftaran
ORDER BY tanggal_daftar DESC
LIMIT 10;

-- Cara pakai:
-- SELECT * FROM mv_pendaftar_terbaru;

-- ============================================
-- 3. BLOG YANG SUDAH PUBLISHED
-- ============================================
-- View ini menyimpan hanya blog yang sudah published
-- Lebih cepat daripada query dengan WHERE is_published = TRUE

DROP MATERIALIZED VIEW IF EXISTS mv_blog_published_simple;
CREATE MATERIALIZED VIEW mv_blog_published_simple AS
SELECT 
    id,
    title,
    slug,
    excerpt,
    category,
    published_at,
    created_at
FROM blog
WHERE is_published = TRUE
ORDER BY COALESCE(published_at, created_at) DESC;

-- Cara pakai:
-- SELECT * FROM mv_blog_published_simple LIMIT 10;
-- SELECT * FROM mv_blog_published_simple WHERE category = 'event';

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
-- Hasil: 
--   status   | jumlah
--   ---------|-------
--   pending  |   25
--   accepted |   15
--   rejected |   10

-- ============================================
-- CARA REFRESH MATERIALIZED VIEW
-- ============================================
-- Materialized view perlu di-refresh agar datanya update
-- Refresh dilakukan setelah ada perubahan data

-- Refresh satu view:
-- REFRESH MATERIALIZED VIEW mv_stats_simple;
-- REFRESH MATERIALIZED VIEW mv_pendaftar_terbaru;
-- REFRESH MATERIALIZED VIEW mv_blog_published_simple;
-- REFRESH MATERIALIZED VIEW mv_pendaftar_per_status;

-- Refresh semua sekaligus (setelah dibuat):
REFRESH MATERIALIZED VIEW mv_stats_simple;
REFRESH MATERIALIZED VIEW mv_pendaftar_terbaru;
REFRESH MATERIALIZED VIEW mv_blog_published_simple;
REFRESH MATERIALIZED VIEW mv_pendaftar_per_status;

-- ============================================
-- CONTOH PENGGUNAAN DI PHP
-- ============================================
-- 
-- SEBELUM (Tanpa Materialized View):
-- $query = "SELECT COUNT(*) FROM pendaftaran WHERE status = 'pending'";
-- $result = pg_query($koneksi, $query);
-- 
-- SESUDAH (Dengan Materialized View):
-- $query = "SELECT pendaftar_pending FROM mv_stats_simple";
-- $result = pg_query($koneksi, $query);
-- 
-- ============================================
-- CATATAN PENTING:
-- ============================================
-- 1. Materialized view menyimpan data, jadi perlu di-refresh
-- 2. Refresh dilakukan setelah INSERT/UPDATE/DELETE data
-- 3. Untuk data yang sering berubah, refresh setiap 5-15 menit
-- 4. Untuk data yang jarang berubah, refresh setiap 1 jam atau lebih
-- 
-- Contoh refresh otomatis (cron job):
-- */15 * * * * psql -d lab_se -c "REFRESH MATERIALIZED VIEW mv_stats_simple;"
-- 
-- ============================================

