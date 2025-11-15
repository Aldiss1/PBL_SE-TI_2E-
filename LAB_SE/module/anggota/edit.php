<?php
$page_title = "Edit Anggota - Admin";
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../cek_login.php';
require_once __DIR__ . '/../../config/anti_injection.php';
require_once __DIR__ . '/../../fungsi/pesan_kilat.php';

// Set active menu
$current_page = 'anggota';
$menu_items = [
    'dashboard.php' => [
        'title' => 'Dashboard',
        'icon' => 'lni lni-home',
        'link' => '../../module/admin/dashboard.php',
        'active' => false
    ],
    'anggota' => [
        'title' => 'Kelola Anggota',
        'icon' => 'lni lni-users',
        'link' => '../../module/anggota/index.php',
        'active' => true
    ],
    'jabatan' => [
        'title' => 'Kelola Jabatan',
        'icon' => 'lni lni-briefcase',
        'link' => '../../module/jabatan/index.php',
        'active' => false
    ],
    'pendaftar' => [
        'title' => 'Kelola Pendaftar',
        'icon' => 'lni lni-list',
        'link' => '../../list-pendaftar.php',
        'active' => false
    ],
    'blog' => [
        'title' => 'Kelola Blog',
        'icon' => 'lni lni-text-format',
        'link' => '../../module/blog/index.php',
        'active' => false
    ]
];

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$data = null;
if ($id > 0) {
    $q = pg_query($koneksi, "SELECT * FROM anggota WHERE id = $id");
    if ($q && pg_num_rows($q) > 0) {
        $data = pg_fetch_assoc($q);
    }
}
if (!$data) {
    pesan_kilat('Anggota tidak ditemukan.', 'error');
    header('Location: index.php');
    exit;
}

// Proses form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = clean_input($_POST['nama'] ?? '');
    $jabatan = clean_input($_POST['jabatan'] ?? '');
    $foto = clean_input($_POST['foto'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    if ($nama === '') {
        pesan_kilat('Nama wajib diisi.', 'error');
    } else {
        $nama_e = pg_escape_string($koneksi, $nama);
        $jabatan_e = pg_escape_string($koneksi, $jabatan);
        $foto_e = pg_escape_string($koneksi, $foto);
        $deskripsi_e = pg_escape_string($koneksi, $deskripsi);

        $query = "UPDATE anggota SET 
                    nama = '$nama_e',
                    jabatan = NULLIF('$jabatan_e',''),
                    foto = NULLIF('$foto_e',''),
                    deskripsi = NULLIF('$deskripsi_e',''),
                    updated_at = NOW()
                  WHERE id = $id";
        $result = pg_query($koneksi, $query);
        if ($result) {
            pesan_kilat('Anggota berhasil diperbarui!', 'success');
            header('Location: index.php');
            exit;
        } else {
            pesan_kilat('Gagal memperbarui anggota. Error: ' . pg_last_error($koneksi), 'error');
        }
    }
}

include __DIR__ . '/../../template/admin_header.php';
?>

<!-- Sidebar -->
<?php include __DIR__ . '/../../template/admin_sidebar.php'; ?>

<!-- Main Content -->
<main class="admin-main-content">
<section class="pt-50 pb-50" style="background: #f8f9fa; padding-top: 30px !important;">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="section-title mb-50">
                    <h2 class="mb-15 wow fadeInUp" data-wow-delay=".2s">Edit Anggota</h2>
                    <p class="wow fadeInUp" data-wow-delay=".4s">Perbarui data anggota/personil</p>
                </div>
            </div>
        </div>

        <?php tampilkan_pesan(); ?>

        <div class="row">
            <div class="col-lg-12">
                <div class="card wow fadeInUp" data-wow-delay=".3s" style="background: white; border-radius: 15px; padding: 30px; box-shadow: 0 5px 20px rgba(0,0,0,0.08);">
                    <form method="POST" action="">
                        <div class="row mb-3">
                            <div class="col-lg-6">
                                <label for="nama" class="form-label" style="font-weight: 600; color: #2c3e50;">Nama *</label>
                                <input type="text" class="form-control" id="nama" name="nama" value="<?php echo htmlspecialchars($data['nama']); ?>" required style="padding: 12px; border-radius: 5px; border: 1px solid #dee2e6;">
                            </div>
                            <div class="col-lg-6">
                                <label for="jabatan" class="form-label" style="font-weight: 600; color: #2c3e50;">Jabatan</label>
                                <input type="text" class="form-control" id="jabatan" name="jabatan" value="<?php echo htmlspecialchars($data['jabatan']); ?>" placeholder="Contoh: Dosen Pengampu, Ketua Lab" style="padding: 12px; border-radius: 5px; border: 1px solid #dee2e6;">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-lg-12">
                                <label for="foto" class="form-label" style="font-weight: 600; color: #2c3e50;">URL Foto</label>
                                <input type="text" class="form-control" id="foto" name="foto" value="<?php echo htmlspecialchars($data['foto']); ?>" placeholder="Contoh: assets/img/team/team-1/team-1.png" style="padding: 12px; border-radius: 5px; border: 1px solid #dee2e6;">
                                <small class="form-text text-muted">Masukkan path/URL gambar foto profil.</small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-lg-12">
                                <label for="deskripsi" class="form-label" style="font-weight: 600; color: #2c3e50;">Deskripsi</label>
                                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="6" style="padding: 12px; border-radius: 5px; border: 1px solid #dee2e6; font-family: inherit;"><?php echo htmlspecialchars($data['deskripsi']); ?></textarea>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-12">
                                <button type="submit" class="button radius-10" style="background: #2F80ED; color: white; padding: 12px 30px;">
                                    <i class="lni lni-save"></i> Update
                                </button>
                                <a href="index.php" class="button radius-10 border-button" style="padding: 12px 30px; margin-left: 10px;">
                                    <i class="lni lni-arrow-left"></i> Batal
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
</main>

<?php include __DIR__ . '/../../template/admin_footer.php'; ?>


