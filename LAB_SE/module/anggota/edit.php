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
    ],
    'profil' => [ 
        'title' => 'Kelola Profil', 
        'icon'  => 'lni lni-cog', 
        'link'  => '../../module/profil/index.php', 
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

// Ambil daftar jabatan untuk dropdown
$list_jabatan = [];
$q_jabatan = pg_query($koneksi, "SELECT nama_jabatan FROM jabatan ORDER BY nama_jabatan ASC");
if ($q_jabatan && pg_num_rows($q_jabatan) > 0) {
	while ($row = pg_fetch_assoc($q_jabatan)) {
		$list_jabatan[] = $row['nama_jabatan'];
	}
}

// Proses form (Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = clean_input($_POST['nama'] ?? '');
    $jabatan = clean_input($_POST['jabatan'] ?? '');
    $instagram = clean_input($_POST['instagram'] ?? '');
    $linkedin = clean_input($_POST['linkedin'] ?? '');

    // Pastikan kolom baru tersedia
    @pg_query($koneksi, "ALTER TABLE anggota ADD COLUMN IF NOT EXISTS instagram VARCHAR(255)");
    @pg_query($koneksi, "ALTER TABLE anggota ADD COLUMN IF NOT EXISTS linkedin VARCHAR(255)");

    // Upload foto (opsional - replace jika ada)
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

        $query = "UPDATE anggota SET 
                    nama = '$nama_e',
                    jabatan = NULLIF('$jabatan_e',''),
                    instagram = NULLIF('$instagram_e',''),
                    linkedin = NULLIF('$linkedin_e',''),
                    updated_at = NOW()
                  WHERE id = $id";
        if ($foto_path !== '') {
            $query = "UPDATE anggota SET 
                        nama = '$nama_e',
                        jabatan = NULLIF('$jabatan_e',''),
                        instagram = NULLIF('$instagram_e',''),
                        linkedin = NULLIF('$linkedin_e',''),
                        foto = NULLIF('$foto_e',''),
                        updated_at = NOW()
                      WHERE id = $id";
        }
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
                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="row mb-3">
                            <div class="col-lg-6">
                                <label for="nama" class="form-label" style="font-weight: 600; color: #2c3e50;">Nama *</label>
                                <input type="text" class="form-control" id="nama" name="nama" value="<?php echo htmlspecialchars($data['nama']); ?>" required style="padding: 12px; border-radius: 5px; border: 1px solid #dee2e6;">
                            </div>
                            <div class="col-lg-6">
                                <label for="jabatan" class="form-label" style="font-weight: 600; color: #2c3e50;">Jabatan</label>
                                <select class="form-select" id="jabatan" name="jabatan" style="padding: 12px; border-radius: 5px; border: 1px solid #dee2e6;">
                                    <option value="">Pilih Jabatan</option>
                                    <?php foreach ($list_jabatan as $j): ?>
                                    <option value="<?php echo htmlspecialchars($j); ?>" <?php echo ($data['jabatan'] === $j) ? 'selected' : ''; ?>><?php echo htmlspecialchars($j); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-lg-6">
                                <label for="instagram" class="form-label" style="font-weight: 600; color: #2c3e50;">Link Instagram</label>
                                <input type="url" class="form-control" id="instagram" name="instagram" value="<?php echo htmlspecialchars($data['instagram'] ?? ''); ?>" placeholder="https://instagram.com/username" style="padding: 12px; border-radius: 5px; border: 1px solid #dee2e6;">
                            </div>
                            <div class="col-lg-6">
                                <label for="linkedin" class="form-label" style="font-weight: 600; color: #2c3e50;">Link LinkedIn</label>
                                <input type="url" class="form-control" id="linkedin" name="linkedin" value="<?php echo htmlspecialchars($data['linkedin'] ?? ''); ?>" placeholder="https://www.linkedin.com/in/username" style="padding: 12px; border-radius: 5px; border: 1px solid #dee2e6;">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-lg-12">
                                <label for="foto" class="form-label" style="font-weight: 600; color: #2c3e50;">Foto (opsional)</label>
                                <?php 
                                $foto_raw = $data['foto'] ?? '';
                                $is_abs = preg_match('/^https?:\\/\\//', $foto_raw) || (strlen($foto_raw) > 0 && $foto_raw[0] === '/');
                                $foto_resolved = $is_abs ? $foto_raw : ($foto_raw ? '../../' . $foto_raw : '');
                                if (!empty($foto_resolved)): ?>
                                    <div class="mb-2">
                                        <img src="<?php echo htmlspecialchars($foto_resolved); ?>" alt="Foto" style="height: 60px; border-radius: 6px; object-fit: cover; border: 1px solid #eee;">
                                    </div>
                                <?php endif; ?>
                                <input type="file" class="form-control" id="foto" name="foto" accept=".jpg,.jpeg,.png,.webp" style="padding: 10px; border-radius: 5px; border: 1px solid #dee2e6; background: #fff;">
                                <small class="text-muted">Maks 2MB. Format: JPG, JPEG, PNG, WEBP.</small>
                                <div id="previewFoto" style="margin-top:10px; display:none;">
                                    <img id="previewImg" src="#" alt="Preview" style="height: 100px; border-radius: 8px; object-fit: cover; border: 1px solid #eee;">
                                </div>
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

