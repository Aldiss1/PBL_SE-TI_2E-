<?php
$page_title = "Tambah Anggota - Admin";
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../auth/cek_login.php';
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
    'user' => [
        'title' => 'Kelola User',
        'icon' => 'lni lni-user',
        'link' => '../../module/user/index.php',
        'active' => false
    ],
    'pendaftar' => [
        'title' => 'Kelola Pendaftar',
        'icon' => 'lni lni-list',
        'link' => '../../module/pendaftar/index.php',
        'active' => false
    ],
    'blog' => [
        'title' => 'Kelola Blog',
        'icon' => 'lni lni-text-format',
        'link' => '../../module/blog/index.php',
        'active' => false
    ],
    'profil' => [ 
        'title' => 'Kelola Profil', 
        'icon'  => 'lni lni-cog', 
        'link'  => '../../module/profil/index.php',
        'active' => false
    ]
];

// Ambil daftar jabatan untuk dropdown
$list_jabatan = [];
$q_jabatan = pg_query($koneksi, "SELECT nama_jabatan FROM jabatan ORDER BY nama_jabatan ASC");
if ($q_jabatan && pg_num_rows($q_jabatan) > 0) {
	while ($row = pg_fetch_assoc($q_jabatan)) {
		$list_jabatan[] = $row['nama_jabatan'];
	}
}

// Proses form (Create)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = clean_input($_POST['nama'] ?? '');
    $jabatan = clean_input($_POST['jabatan'] ?? '');
    $instagram = clean_input($_POST['instagram'] ?? '');
    $linkedin = clean_input($_POST['linkedin'] ?? '');

    // Handle upload foto (opsional)
    $foto_path = '';
    if (isset($_FILES['foto']) && is_uploaded_file($_FILES['foto']['tmp_name'])) {
        $allowed_ext = ['jpg','jpeg','png','webp'];
        $max_size = 2 * 1024 * 1024;
        $original_name = $_FILES['foto']['name'];
        $tmp_path = $_FILES['foto']['tmp_name'];
        $size = (int)$_FILES['foto']['size'];
        $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        if (in_array($ext, $allowed_ext) && $size <= $max_size) {
            $uploadDir = __DIR__ . '/../../uploads/anggota/';
            if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0775, true); }
            $safeBase = preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower(pathinfo($original_name, PATHINFO_FILENAME)));
            $newName = $safeBase . '_' . time() . '_' . mt_rand(1000,9999) . '.' . $ext;
            $destPath = $uploadDir . $newName;
            if (@move_uploaded_file($tmp_path, $destPath)) {
                $foto_path = 'uploads/anggota/' . $newName;
            }
        } else {
            pesan_kilat('Foto tidak valid (format harus JPG/PNG/WEBP, maks 2MB).', 'error');
        }
    }

    if ($nama === '') {
        pesan_kilat('Nama wajib diisi.', 'error');
    } else {
        $nama_e = pg_escape_string($koneksi, $nama);
        $jabatan_e = pg_escape_string($koneksi, $jabatan);
        $instagram_e = pg_escape_string($koneksi, $instagram);
        $linkedin_e = pg_escape_string($koneksi, $linkedin);
        $foto_e = pg_escape_string($koneksi, $foto_path);

        $columns = "nama, jabatan, created_at, updated_at";
        $values  = "'$nama_e', NULLIF('$jabatan_e',''), NOW(), NOW()";
        if ($foto_path !== '') { $columns .= ", foto"; $values .= ", NULLIF('$foto_e','')"; }
        if ($instagram !== '') { $columns .= ", instagram"; $values .= ", NULLIF('$instagram_e','')"; }
        if ($linkedin !== '') { $columns .= ", linkedin"; $values .= ", NULLIF('$linkedin_e','')"; }

        $query = "INSERT INTO anggota ($columns) VALUES ($values)";
        $result = pg_query($koneksi, $query);
        if ($result) {
            pesan_kilat('Anggota berhasil ditambahkan!', 'success');
            header('Location: index.php');
            exit;
        } else {
            pesan_kilat('Gagal menambahkan anggota. Error: ' . pg_last_error($koneksi), 'error');
        }
    }
}

include __DIR__ . '/../../template/admin_header.php';
?>

<!-- Sidebar -->
<?php include __DIR__ . '/../../template/admin_sidebar.php'; ?>

<!-- Main Content -->
<main class="admin-main-content">
<section class="pt-50 pb-50 admin-section-bg">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="section-title mb-50">
                    <h2 class="mb-15 wow fadeInUp" data-wow-delay=".2s">Tambah Anggota</h2>
                    <p class="wow fadeInUp" data-wow-delay=".4s">Tambahkan data anggota/personil</p>
                </div>
            </div>
        </div>

        <?php tampilkan_pesan(); ?>

        <div class="row">
            <div class="col-lg-12">
                <div class="card wow fadeInUp form-card" data-wow-delay=".3s">
                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="row mb-3">
                            <div class="col-lg-6">
                                <label for="nama" class="form-label form-label-custom">Nama *</label>
                                <input type="text" class="form-control form-input-custom" id="nama" name="nama" required>
                            </div>
                            <div class="col-lg-6">
                                <label for="jabatan" class="form-label form-label-custom">Jabatan</label>
                                <select class="form-select form-select-custom" id="jabatan" name="jabatan">
                                    <option value="">Pilih Jabatan</option>
                                    <?php foreach ($list_jabatan as $j): ?>
                                    <option value="<?php echo htmlspecialchars($j); ?>"><?php echo htmlspecialchars($j); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-lg-6">
                                <label for="instagram" class="form-label form-label-custom">Link Instagram</label>
                                <input type="url" class="form-control form-input-custom" id="instagram" name="instagram" placeholder="https://instagram.com/username">
                            </div>
                            <div class="col-lg-6">
                                <label for="linkedin" class="form-label form-label-custom">Link LinkedIn</label>
                                <input type="url" class="form-control form-input-custom" id="linkedin" name="linkedin" placeholder="https://www.linkedin.com/in/username">
                            </div>
                        </div>

        <div class="row mb-3">
            <div class="col-lg-12">
                <label for="foto" class="form-label form-label-custom">Foto (opsional)</label>
                <input type="file" class="form-control form-file-custom" id="foto" name="foto" accept=".jpg,.jpeg,.png,.webp">
                <small class="text-muted">Maks 2MB. Format: JPG, JPEG, PNG, WEBP.</small>
                <div id="previewFoto" class="preview-container">
                    <img id="previewImg" src="#" alt="Preview" class="preview-image">
                </div>
            </div>
        </div>

                        <div class="row">
                            <div class="col-lg-12">
                                <button type="submit" class="button radius-10 btn-save">
                                    <i class="lni lni-save"></i> Simpan
                                </button>
                                <a href="index.php" class="button radius-10 border-button btn-cancel">
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

<script src="../../assets/js/classes/ImagePreview.js"></script>
<script>
// Initialize ImagePreview
if (typeof ImagePreview !== 'undefined') {
    window.imagePreview = new ImagePreview({
        inputId: 'foto',
        previewContainerId: 'previewFoto',
        previewImgId: 'previewImg'
    });
}
</script>

