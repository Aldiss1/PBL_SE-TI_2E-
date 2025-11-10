<?php
require_once __DIR__ . '/../../../config/koneksi.php';
require_once __DIR__ . '/../../../config/anti_injection.php';
require_once __DIR__ . '/../../../fungsi/pesan_kilat.php';

session_start();

$id = isset($_GET['id']) ? clean_input($_GET['id']) : '';

// Ambil data jabatan berdasarkan ID
$query = "SELECT * FROM jabatan WHERE id = '$id'";
$result = pg_query($koneksi, $query);
if (!$result) {
    error_log("Error query jabatan edit: " . pg_last_error($koneksi));
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
    $nama_jabatan = clean_input($_POST['nama_jabatan']);
    
    require_once __DIR__ . '/../../../fungsi/edit.php';
    if (edit_data('jabatan', ['nama_jabatan' => $nama_jabatan], $id)) {
        pesan_kilat('Data jabatan berhasil diupdate', 'success');
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
    <title>Edit Jabatan</title>
    <link rel="stylesheet" href="../../../assets/css/bootstrap-5.0.0-alpha-2.min.css" />
</head>
<body>
    <div class="container mt-5">
        <h2>Edit Jabatan</h2>
        <?php tampilkan_pesan(); ?>
        
        <form method="POST">
            <div class="mb-3">
                <label for="nama_jabatan" class="form-label">Nama Jabatan</label>
                <input type="text" class="form-control" id="nama_jabatan" name="nama_jabatan" value="<?php echo htmlspecialchars($data['nama_jabatan']); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="../index.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</body>
</html>

