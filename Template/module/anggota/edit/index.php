<?php
require_once __DIR__ . '/../../../config/koneksi.php';
require_once __DIR__ . '/../../../config/anti_injection.php';
require_once __DIR__ . '/../../../fungsi/pesan_kilat.php';

session_start();

$id = isset($_GET['id']) ? clean_input($_GET['id']) : '';

// Ambil data anggota berdasarkan ID
$query = "SELECT * FROM anggota WHERE id = '$id'";
$result = pg_query($koneksi, $query);
if (!$result) {
    error_log("Error query anggota edit: " . pg_last_error($koneksi));
    header('Location: ../index.php');
    exit;
}
$data = pg_fetch_assoc($result);

if (!$data) {
    header('Location: ../index.php');
    exit;
}

// Proses update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = clean_input($_POST['nama']);
    $email = clean_input($_POST['email']);
    
    require_once __DIR__ . '/../../../fungsi/edit.php';
    if (edit_data('anggota', ['nama' => $nama, 'email' => $email], $id)) {
        pesan_kilat('Data anggota berhasil diupdate', 'success');
        header('Location: ../index.php');
        exit;
    } else {
        pesan_kilat('Gagal mengupdate data', 'error');
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Anggota</title>
    <link rel="stylesheet" href="../../../assets/css/bootstrap-5.0.0-alpha-2.min.css" />
</head>
<body>
    <div class="container mt-5">
        <h2>Edit Anggota</h2>
        <?php tampilkan_pesan(); ?>
        
        <form method="POST">
            <div class="mb-3">
                <label for="nama" class="form-label">Nama</label>
                <input type="text" class="form-control" id="nama" name="nama" value="<?php echo htmlspecialchars($data['nama']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($data['email']); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="../index.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</body>
</html>

