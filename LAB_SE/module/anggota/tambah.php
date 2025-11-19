<?php
$page_title = "Tambah Anggota - Admin";
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
    ],
    'profil' => [ 
        'title' => 'Kelola Profil', 
        'icon'  => 'lni lni-cog', 
        'link'  => '../../module/profil/index.php', 
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

    // Pastikan kolom baru tersedia
    @pg_query($koneksi, "ALTER TABLE anggota ADD COLUMN IF NOT EXISTS instagram VARCHAR(255)");
    @pg_query($koneksi, "ALTER TABLE anggota ADD COLUMN IF NOT EXISTS linkedin VARCHAR(255)");

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
<section class="pt-50 pb-50" style="background: #f8f9fa; padding-top: 30px !important;">
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
                <div class="card wow fadeInUp" data-wow-delay=".3s" style="background: white; border-radius: 15px; padding: 30px; box-shadow: 0 5px 20px rgba(0,0,0,0.08);">
                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="row mb-3">
                            <div class="col-lg-6">
                                <label for="nama" class="form-label" style="font-weight: 600; color: #2c3e50;">Nama *</label>
                                <input type="text" class="form-control" id="nama" name="nama" required style="padding: 12px; border-radius: 5px; border: 1px solid #dee2e6;">
                            </div>
                            <div class="col-lg-6">
                                <label for="jabatan" class="form-label" style="font-weight: 600; color: #2c3e50;">Jabatan</label>
                                <select class="form-select" id="jabatan" name="jabatan" style="padding: 12px; border-radius: 5px; border: 1px solid #dee2e6;">
                                    <option value="">Pilih Jabatan</option>
                                    <?php foreach ($list_jabatan as $j): ?>
                                    <option value="<?php echo htmlspecialchars($j); ?>"><?php echo htmlspecialchars($j); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-lg-6">
                                <label for="instagram" class="form-label" style="font-weight: 600; color: #2c3e50;">Link Instagram</label>
                                <input type="url" class="form-control" id="instagram" name="instagram" placeholder="https://instagram.com/username" style="padding: 12px; border-radius: 5px; border: 1px solid #dee2e6;">
                            </div>
                            <div class="col-lg-6">
                                <label for="linkedin" class="form-label" style="font-weight: 600; color: #2c3e50;">Link LinkedIn</label>
                                <input type="url" class="form-control" id="linkedin" name="linkedin" placeholder="https://www.linkedin.com/in/username" style="padding: 12px; border-radius: 5px; border: 1px solid #dee2e6;">
                            </div>
                        </div>

        <div class="row mb-3">
            <div class="col-lg-12">
                <label for="foto" class="form-label" style="font-weight: 600; color: #2c3e50;">Foto (opsional)</label>
                <input type="file" class="form-control" id="foto" name="foto" accept=".jpg,.jpeg,.png,.webp" style="padding: 10px; border-radius: 5px; border: 1px solid #dee2e6; background: #fff;">
                <small class="text-muted">Maks 2MB. Format: JPG, JPEG, PNG, WEBP.</small>
                <div id="previewFoto" style="margin-top:10px; display:none;">
                    <img id="previewImg" src="#" alt="Preview" style="height: 100px; border-radius: 8px; object-fit: cover; border: 1px solid #eee;">
                </div>
            </div>
        </div>

                        <div class="row">
                            <div class="col-lg-12">
                                <button type="submit" class="button radius-10" style="background: #27AE60; color: white; padding: 12px 30px;">
                                    <i class="lni lni-save"></i> Simpan
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

<script>
(function(){
    var input = document.getElementById('foto');
    if(!input) return;
    input.addEventListener('change', function(){
        var file = this.files && this.files[0];
        if(!file){ document.getElementById('previewFoto').style.display='none'; return; }
        var allowed = ['image/jpeg','image/png','image/webp'];
        if(allowed.indexOf(file.type) === -1){ document.getElementById('previewFoto').style.display='none'; return; }
        var reader = new FileReader();
        reader.onload = function(e){
            var img = document.getElementById('previewImg');
            img.src = e.target.result;
            document.getElementById('previewFoto').style.display='block';
        };
        reader.readAsDataURL(file);
    });
})();
</script>

