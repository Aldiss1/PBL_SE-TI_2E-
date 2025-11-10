# Konfigurasi PostgreSQL untuk Web Profile Lab SE

## Setup Database

1. Buat database PostgreSQL:
```sql
CREATE DATABASE lab_se;
```

2. Import schema database:
```bash
psql -U postgres -d lab_se -f database-schema-postgresql.sql
```

## Konfigurasi Koneksi

Edit file `config/koneksi.php` untuk menyesuaikan kredensial database:

```php
$host = "localhost";
$port = "5432";
$username = "postgres";  // Ganti dengan username PostgreSQL Anda
$password = "";          // Ganti dengan password PostgreSQL Anda
$database = "lab_se";
```

## Perubahan dari MySQL ke PostgreSQL

### Fungsi yang Diubah:
- `mysqli_connect()` → `pg_connect()`
- `mysqli_query()` → `pg_query()`
- `mysqli_num_rows()` → `pg_num_rows()`
- `mysqli_fetch_assoc()` → `pg_fetch_assoc()`
- `mysqli_real_escape_string()` → `pg_escape_string()`
- `mysqli_set_charset()` → `pg_set_client_encoding()`

### Tabel yang Diubah:
- `admin` → `users` (sesuai schema PostgreSQL)
- `pendaftar` → `pendaftaran` (sesuai schema PostgreSQL)

### Nama Kolom Blog:
- `judul` → `title`
- `isi` → `content`
- `gambar` → `featured_image`
- `tanggal_publish` → `published_at`

## Catatan Penting

1. Pastikan extension `pgsql` sudah diaktifkan di PHP:
   - Edit `php.ini` dan pastikan `extension=pgsql` tidak di-comment
   - Restart web server

2. Untuk Laragon, pastikan PostgreSQL service sudah berjalan

3. Default user PostgreSQL biasanya `postgres` dengan password kosong (sesuaikan dengan setup Anda)

