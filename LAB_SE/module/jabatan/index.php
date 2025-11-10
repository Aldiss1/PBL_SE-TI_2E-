<?php
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../config/anti_injection.php';
require_once __DIR__ . '/../../fungsi/pesan_kilat.php';

session_start();

// Ambil data jabatan
$query = "SELECT * FROM jabatan ORDER BY id DESC";
$result = pg_query($koneksi, $query);
if (!$result) {
    error_log("Error query jabatan: " . pg_last_error($koneksi));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Jabatan</title>
    <link rel="stylesheet" href="../../assets/css/bootstrap-5.0.0-alpha-2.min.css" />
</head>
<body>
    <div class="container mt-5">
        <h2>Kelola Jabatan</h2>
        <?php tampilkan_pesan(); ?>
        
        <a href="../../fungsi/tambah.php?module=jabatan" class="btn btn-primary mb-3">Tambah Jabatan</a>
        
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Jabatan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if ($result && pg_num_rows($result) > 0) {
                    while ($row = pg_fetch_assoc($result)): 
                ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['nama_jabatan']); ?></td>
                    <td>
                        <a href="edit/index.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="../../fungsi/hapus.php?module=jabatan&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
                    </td>
                </tr>
                <?php 
                    endwhile;
                } else {
                ?>
                <tr>
                    <td colspan="3" class="text-center">Belum ada data jabatan.</td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>
</html>

