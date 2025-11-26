-- ============================================
-- SETUP KOLOM YANG DIPERLUKAN
-- ============================================
-- Script ini HARUS dijalankan terlebih dahulu sebelum menjalankan materialized_views_complete.sql
-- Script ini memastikan semua kolom yang diperlukan sudah ada di database

-- Pastikan kolom email ada di tabel pendaftaran
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_schema = 'public' 
        AND table_name = 'pendaftaran' 
        AND column_name = 'email'
    ) THEN
        ALTER TABLE pendaftaran ADD COLUMN email VARCHAR(255);
        RAISE NOTICE 'Kolom email berhasil ditambahkan ke tabel pendaftaran';
    ELSE
        RAISE NOTICE 'Kolom email sudah ada di tabel pendaftaran';
    END IF;
EXCEPTION WHEN OTHERS THEN
    RAISE NOTICE 'Error saat menambahkan kolom email: %', SQLERRM;
END $$;

-- Pastikan kolom instagram ada di tabel anggota
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_schema = 'public' 
        AND table_name = 'anggota' 
        AND column_name = 'instagram'
    ) THEN
        ALTER TABLE anggota ADD COLUMN instagram VARCHAR(255);
        RAISE NOTICE 'Kolom instagram berhasil ditambahkan ke tabel anggota';
    ELSE
        RAISE NOTICE 'Kolom instagram sudah ada di tabel anggota';
    END IF;
EXCEPTION WHEN OTHERS THEN
    RAISE NOTICE 'Error saat menambahkan kolom instagram: %', SQLERRM;
END $$;

-- Pastikan kolom linkedin ada di tabel anggota
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_schema = 'public' 
        AND table_name = 'anggota' 
        AND column_name = 'linkedin'
    ) THEN
        ALTER TABLE anggota ADD COLUMN linkedin VARCHAR(255);
        RAISE NOTICE 'Kolom linkedin berhasil ditambahkan ke tabel anggota';
    ELSE
        RAISE NOTICE 'Kolom linkedin sudah ada di tabel anggota';
    END IF;
EXCEPTION WHEN OTHERS THEN
    RAISE NOTICE 'Error saat menambahkan kolom linkedin: %', SQLERRM;
END $$;

-- Selesai
SELECT 'Setup kolom selesai! Sekarang Anda bisa menjalankan materialized_views_complete.sql' AS status;

