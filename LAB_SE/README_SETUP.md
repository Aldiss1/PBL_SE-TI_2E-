# 🗄️ Setup Database Lab SE

Panduan lengkap untuk setup database PostgreSQL untuk aplikasi Lab SE.

## 📋 Prasyarat

1. **PostgreSQL** sudah terinstall dan berjalan
   - Download: https://www.postgresql.org/download/
   - Untuk Windows: Install menggunakan installer resmi
   - Untuk Linux: `sudo apt-get install postgresql` (Ubuntu/Debian)

2. **PHP Extension pgsql** sudah aktif
   - Buka file `php.ini`
   - Pastikan baris `extension=pgsql` tidak di-comment (tidak ada `;` di depannya)
   - Restart web server (Apache/Nginx) atau Laragon

3. **Kredensial PostgreSQL**
   - Username: `postgres` (default)
   - Password: Sesuai yang Anda set saat install
   - Port: `5432` (default)

## 🚀 Cara Setup

### Metode 1: Menggunakan Setup Script (Recommended)

1. Buka browser dan akses:
   ```
   http://localhost/LAB_SE/setup_database.php
   ```

2. Periksa konfigurasi database yang ditampilkan

3. Klik tombol **"Setup Database Sekarang"**

4. Tunggu hingga proses selesai

5. Jika berhasil, Anda akan melihat pesan sukses dan link ke admin panel

### Metode 2: Manual Setup via psql

1. Buka terminal/command prompt

2. Login ke PostgreSQL:
   ```bash
   psql -U postgres
   ```

3. Buat database:
   ```sql
   CREATE DATABASE lab_se WITH ENCODING = 'UTF8';
   ```

4. Keluar dari psql:
   ```sql
   \q
   ```

5. Import schema:
   ```bash
   psql -U postgres -d lab_se -f database.sql
   ```

## ⚙️ Konfigurasi Database

Edit file `config/koneksi.php` untuk mengubah konfigurasi:

```php
$host = "localhost";
$port = "5432";
$username = "postgres";
$password = "123456";  // Ganti dengan password PostgreSQL Anda
$database = "lab_se";
```

## 🔐 Login Default

Setelah setup selesai, Anda dapat login ke admin panel:

- **URL**: `http://localhost/LAB_SE/module/auth/login.php`
- **Username**: `admin`
- **Password**: `admin`

**⚠️ PENTING**: Segera ubah password default setelah login pertama kali!

## 📊 Struktur Database

Database akan berisi tabel-tabel berikut:

- `users` - Data user/admin
- `jabatan` - Data jabatan anggota
- `anggota` - Data anggota laboratorium
- `pendaftar` - Data pendaftar baru
- `blog` - Data artikel blog
- `penelitian` - Data penelitian
- `publikasi` - Data publikasi
- `konten_page` - Konten dinamis halaman
- `roadmap_periode` - Periode roadmap
- `roadmap_item` - Item tugas roadmap

## 🛠️ Troubleshooting

### Error: "Extension PostgreSQL tidak ditemukan"

**Solusi:**
1. Buka file `php.ini`
2. Cari baris `;extension=pgsql`
3. Hapus tanda `;` di depan
4. Simpan dan restart web server

### Error: "Koneksi ke database gagal"

**Periksa:**
1. PostgreSQL service sudah berjalan?
   - Windows: Services → PostgreSQL
   - Linux: `sudo systemctl status postgresql`
2. Username dan password benar?
3. Port sesuai? (default: 5432)
4. Database sudah dibuat?

### Error: "Database sudah ada"

Ini bukan error. Script akan menggunakan database yang sudah ada dan hanya membuat tabel yang belum ada.

## 📝 Catatan

- File `database.sql` berisi schema lengkap database
- Setup script akan otomatis membuat database dan tabel
- Data default akan di-insert otomatis
- Pastikan backup database sebelum melakukan perubahan besar

## 🔗 Link Penting

- Admin Panel: `module/auth/login.php`
- Landing Page: `index.php`
- Setup Database: `setup_database.php`

## 📞 Support

Jika mengalami masalah, periksa:
1. Log error PHP
2. Log PostgreSQL
3. Konfigurasi di `config/koneksi.php`
4. Extension pgsql sudah aktif

---

**Selamat! Database sudah siap digunakan.** 🎉


