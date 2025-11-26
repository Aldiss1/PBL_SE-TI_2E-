# 📚 Cara Pakai Materialized Views Lengkap untuk Pemula

## 🎯 Apa itu Materialized Views?

**Materialized View** = Menyimpan hasil query di database agar query lebih cepat.

**Analoginya:**
- **Tanpa Materialized View** = Setiap kali minta data, harus hitung ulang (lambat) 🔴
- **Dengan Materialized View** = Data sudah dihitung dan disimpan, tinggal ambil (cepat) 🟢

## 🚀 Langkah 1: Install Materialized Views

### Cara 1: Via phpPgAdmin atau DBeaver
1. Buka **phpPgAdmin** atau **DBeaver**
2. Pilih database **lab_se**
3. Buka file `materialized_views_complete.sql`
4. Copy semua query
5. Paste dan jalankan (Execute)

### Cara 2: Via Command Line
```bash
psql -U postgres -d lab_se -f materialized_views_complete.sql
```

## 📊 Materialized Views yang Tersedia

### 1. `mv_stats_complete` - Statistik Dashboard Lengkap
Menyimpan semua statistik penting untuk dashboard.

**Cara pakai di SQL:**
```sql
SELECT * FROM mv_stats_complete;
```

**Cara pakai di PHP:**
```php
require_once 'fungsi/materialized_views_complete.php';

$stats = get_stats_complete();
echo "Total Anggota: " . $stats['total_anggota'];
echo "Total Pendaftar: " . $stats['total_pendaftar'];
echo "Total Blog Views: " . $stats['total_blog_views'];
```

### 2. `mv_pendaftar_terbaru_complete` - 20 Pendaftar Terbaru
Menyimpan 20 pendaftar terbaru dengan informasi lengkap.

**Cara pakai di PHP:**
```php
$pendaftar = get_pendaftar_terbaru_complete(10); // Ambil 10 terbaru
foreach ($pendaftar as $p) {
    echo $p['nama'] . " - " . $p['status'];
}
```

### 3. `mv_blog_published_complete` - Blog Published Lengkap
Menyimpan blog yang sudah published dengan informasi lengkap.

**Cara pakai di PHP:**
```php
// Ambil 10 blog terbaru
$blogs = get_blog_published_complete(10);

// Ambil blog event
$events = get_blog_published_complete(5, 'event');

// Ambil blog populer (urutkan berdasarkan views)
$popular = get_blog_published_complete(5, null, 'views');
```

### 4. `mv_pendaftar_per_status` - Statistik per Status
Menghitung jumlah pendaftar per status.

**Cara pakai di PHP:**
```php
$stats = get_pendaftar_per_status();
echo "Pending: " . $stats['pending'];
echo "Accepted: " . $stats['accepted'];
echo "Rejected: " . $stats['rejected'];
```

### 5. `mv_anggota_per_jabatan` - Anggota per Jabatan
Menghitung jumlah anggota per jabatan.

**Cara pakai di PHP:**
```php
$stats = get_anggota_per_jabatan();
foreach ($stats as $jabatan => $jumlah) {
    echo "$jabatan: $jumlah anggota";
}
```

### 6. `mv_blog_per_kategori` - Blog per Kategori
Menghitung jumlah blog per kategori.

**Cara pakai di PHP:**
```php
$stats = get_blog_per_kategori();
foreach ($stats as $item) {
    echo $item['category'] . ": " . $item['jumlah'] . " blog";
}
```

### 7. `mv_pendaftar_per_bulan` - Pendaftar per Bulan
Menghitung jumlah pendaftar per bulan (6 bulan terakhir).

**Cara pakai di PHP:**
```php
$stats = get_pendaftar_per_bulan();
foreach ($stats as $item) {
    echo $item['label'] . ": " . $item['jumlah'] . " pendaftar";
}
```

### 8. `mv_blog_populer` - Blog Populer (Top 10)
Menyimpan 10 blog terpopuler berdasarkan views.

**Cara pakai di PHP:**
```php
$popular = get_blog_populer(5); // Ambil 5 terpopuler
foreach ($popular as $blog) {
    echo $blog['title'] . " - " . $blog['views'] . " views";
}
```

### 9. `mv_riwayat_status_terbaru` - Riwayat Perubahan Status
Menyimpan riwayat perubahan status pendaftar terbaru.

**Cara pakai di PHP:**
```php
$riwayat = get_riwayat_status_terbaru(10);
foreach ($riwayat as $r) {
    echo $r['nama'] . ": " . $r['status_lama'] . " → " . $r['status_baru'];
}
```

### 10. `mv_anggota_lengkap` - Anggota Lengkap
Menyimpan anggota dengan informasi jabatan lengkap.

**Cara pakai di PHP:**
```php
// Ambil semua anggota
$anggota = get_anggota_lengkap();

// Ambil anggota dengan jabatan tertentu
$ketua = get_anggota_lengkap(null, 'Ketua');
```

### 11. `mv_blog_per_bulan` - Blog per Bulan
Menghitung jumlah blog yang dipublish per bulan.

**Cara pakai di PHP:**
```php
$stats = get_blog_per_bulan();
foreach ($stats as $item) {
    echo $item['label'] . ": " . $item['jumlah'] . " blog";
}
```

### 12. `mv_pendaftar_pending` - Pendaftar Pending
Menyimpan pendaftar yang masih pending dan perlu review.

**Cara pakai di PHP:**
```php
$pending = get_pendaftar_pending();
echo "Ada " . count($pending) . " pendaftar yang perlu review";
```

## 🔄 Refresh Materialized Views

**PENTING:** Materialized view perlu di-refresh agar datanya update!

### Refresh Manual (Via SQL)
```sql
REFRESH MATERIALIZED VIEW mv_stats_complete;
REFRESH MATERIALIZED VIEW mv_pendaftar_terbaru_complete;
-- ... dst
```

### Refresh via PHP
```php
require_once 'fungsi/materialized_views_complete.php';

// Refresh semua
refresh_views_complete();
```

### Refresh Otomatis (Cron Job)
Buat file `refresh_complete.php` sudah tersedia.

Jalankan via cron setiap 15 menit:
```bash
*/15 * * * * php /path/to/LAB_SE/refresh_complete.php
```

## ⚠️ Kapan Harus Refresh?

Refresh materialized view setelah:
- ✅ Menambah data baru (INSERT)
- ✅ Mengubah data (UPDATE)
- ✅ Menghapus data (DELETE)

**Contoh:**
```php
// Setelah insert pendaftar baru
$query = "INSERT INTO pendaftaran ...";
pg_query($koneksi, $query);

// Refresh materialized view
refresh_views_complete();
```

## 🎯 Tips untuk Pemula

1. **Mulai dengan yang sederhana**
   - Pakai `mv_stats_complete` dulu
   - Setelah paham, baru pakai yang lain

2. **Refresh secara berkala**
   - Setiap 15 menit untuk data yang sering berubah
   - Setiap 1 jam untuk data yang jarang berubah

3. **Jangan lupa refresh!**
   - Data di materialized view tidak otomatis update
   - Harus refresh manual atau via cron job

4. **Cek apakah sudah ter-update**
   ```sql
   SELECT * FROM mv_stats_complete;
   -- Bandingkan dengan query langsung
   SELECT COUNT(*) FROM pendaftaran;
   ```

## 📝 Contoh Penggunaan Lengkap

### Contoh 1: Dashboard Admin

```php
require_once 'fungsi/materialized_views_complete.php';

// Ambil semua statistik
$stats = get_stats_complete();

// Ambil pendaftar terbaru
$pendaftar_terbaru = get_pendaftar_terbaru_complete(5);

// Ambil blog populer
$blog_populer = get_blog_populer(5);

// Ambil pendaftar pending
$pending = get_pendaftar_pending();
$jumlah_pending = count($pending);
```

### Contoh 2: Chart/Grafik

```php
// Data untuk chart pendaftar per bulan
$pendaftar_bulan = get_pendaftar_per_bulan();
$labels = [];
$data = [];
foreach ($pendaftar_bulan as $item) {
    $labels[] = $item['label'];
    $data[] = $item['jumlah'];
}

// Data untuk chart blog per kategori
$blog_kategori = get_blog_per_kategori();
```

## ❓ FAQ

**Q: Kapan harus pakai Materialized View?**
A: Ketika query lambat dan sering digunakan (misal: dashboard, laporan)

**Q: Apakah data selalu update?**
A: Tidak, harus di-refresh. Tapi bisa dijadwalkan otomatis via cron.

**Q: Apakah aman pakai Materialized View?**
A: Ya, aman. Hanya menyimpan hasil query, tidak mengubah data asli.

**Q: Berapa lama refresh?**
A: Biasanya 1-5 detik, tergantung jumlah data.

**Q: Bisa pakai bersamaan dengan query biasa?**
A: Ya, bisa! Materialized view hanya untuk optimasi, query biasa tetap bisa dipakai.

---

**Selamat belajar! 🎉**

