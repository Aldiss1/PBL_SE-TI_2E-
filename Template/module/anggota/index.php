<?php
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../config/anti_injection.php';
require_once __DIR__ . '/../../fungsi/pesan_kilat.php';

session_start();

// Ambil data anggota
$query = "SELECT * FROM anggota ORDER BY id DESC";
$result = pg_query($koneksi, $query);
if (!$result) {
    error_log("Error query anggota: " . pg_last_error($koneksi));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Anggota</title>
    <link rel="stylesheet" href="../../assets/css/bootstrap-5.0.0-alpha-2.min.css" />
</head>
<body>
    <div class="container mt-5">
        <h2>Kelola Anggota</h2>
        <?php tampilkan_pesan(); ?>
        
        <a href="../../fungsi/tambah.php?module=anggota" class="btn btn-primary mb-3">Tambah Anggota</a>
        
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>Email</th>
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
                    <td><?php echo htmlspecialchars($row['nama']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td>
                        <a href="edit/index.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="../../fungsi/hapus.php?module=anggota&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
                    </td>
                </tr>
                <?php 
                    endwhile;
                } else {
                ?>
                <tr>
                    <td colspan="4" class="text-center">Belum ada data anggota.</td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>
</html>

