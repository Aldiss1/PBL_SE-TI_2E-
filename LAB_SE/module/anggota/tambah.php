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
    $deskripsi = clean_input($_POST['deskripsi'] ?? '');
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
        $deskripsi_e = pg_escape_string($koneksi, $deskripsi);
        $instagram_e = pg_escape_string($koneksi, $instagram);
        $linkedin_e = pg_escape_string($koneksi, $linkedin);
        $foto_e = pg_escape_string($koneksi, $foto_path);

        $columns = "nama, jabatan, created_at, updated_at";
        $values  = "'$nama_e', NULLIF('$jabatan_e',''), NOW(), NOW()";
        if ($foto_path !== '') { $columns .= ", foto"; $values .= ", NULLIF('$foto_e','')"; }
        if ($deskripsi !== '') { $columns .= ", deskripsi"; $values .= ", NULLIF('$deskripsi_e','')"; }
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
            <!-- Form Section -->
            <div class="col-lg-8">
                <div class="card wow fadeInUp form-card" data-wow-delay=".3s">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="card-title mb-0">
                            <i class="lni lni-user text-primary"></i> Form Tambah Anggota
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="" enctype="multipart/form-data" id="formTambahAnggota">
                            <div class="row mb-3">
                                <div class="col-lg-6">
                                    <label for="nama" class="form-label form-label-custom">
                                        <i class="lni lni-user"></i> Nama <span class="text-danger">*</span>
                                    </label>
                                    <input 
                                        type="text" 
                                        class="form-control form-control-lg form-input-custom" 
                                        id="nama" 
                                        name="nama" 
                                        placeholder="Masukkan nama lengkap"
                                        required
                                        minlength="3"
                                        maxlength="100"
                                        autofocus>
                                    <div class="form-text">
                                        <i class="lni lni-information"></i> Masukkan nama lengkap anggota (minimal 3 karakter)
                                    </div>
                                    <div class="invalid-feedback" id="nama_error"></div>
                                </div>
                                <div class="col-lg-6">
                                    <label for="jabatan" class="form-label form-label-custom">
                                        <i class="lni lni-briefcase"></i> Jabatan
                                    </label>
                                    <select class="form-select form-select-lg form-select-custom" id="jabatan" name="jabatan">
                                        <option value="">Pilih Jabatan (Opsional)</option>
                                        <?php foreach ($list_jabatan as $j): ?>
                                        <option value="<?php echo htmlspecialchars($j); ?>"><?php echo htmlspecialchars($j); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text">
                                        <i class="lni lni-information"></i> Pilih jabatan anggota di laboratorium
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="deskripsi" class="form-label form-label-custom">
                                    <i class="lni lni-text-format"></i> Deskripsi
                                </label>
                                <textarea 
                                    class="form-control form-input-custom" 
                                    id="deskripsi" 
                                    name="deskripsi" 
                                    rows="4"
                                    placeholder="Masukkan deskripsi atau biografi singkat anggota (opsional)"
                                    maxlength="500"></textarea>
                                <div class="form-text">
                                    <i class="lni lni-information"></i> Deskripsi singkat tentang anggota (maksimal 500 karakter)
                                </div>
                                <small class="text-muted float-end">
                                    <span id="deskripsi_count">0</span>/500 karakter
                                </small>
                            </div>

                            <div class="row mb-3">
                                <div class="col-lg-6">
                                    <label for="instagram" class="form-label form-label-custom">
                                        <i class="lni lni-instagram"></i> Link Instagram
                                    </label>
                                    <input 
                                        type="url" 
                                        class="form-control form-control-lg form-input-custom" 
                                        id="instagram" 
                                        name="instagram" 
                                        placeholder="https://instagram.com/username">
                                    <div class="form-text">
                                        <i class="lni lni-information"></i> Link profil Instagram (opsional)
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <label for="linkedin" class="form-label form-label-custom">
                                        <i class="lni lni-linkedin"></i> Link LinkedIn
                                    </label>
                                    <input 
                                        type="url" 
                                        class="form-control form-control-lg form-input-custom" 
                                        id="linkedin" 
                                        name="linkedin" 
                                        placeholder="https://www.linkedin.com/in/username">
                                    <div class="form-text">
                                        <i class="lni lni-information"></i> Link profil LinkedIn (opsional)
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="foto" class="form-label form-label-custom">
                                    <i class="lni lni-image"></i> Foto (Opsional)
                                </label>
                                <input 
                                    type="file" 
                                    class="form-control form-control-lg form-file-custom" 
                                    id="foto" 
                                    name="foto" 
                                    accept=".jpg,.jpeg,.png,.webp">
                                <div class="form-text">
                                    <i class="lni lni-information"></i> Maks 2MB. Format: JPG, JPEG, PNG, WEBP
                                </div>
                                <div id="previewFoto" class="preview-container mt-3" style="display: none;">
                                    <img id="previewImg" src="#" alt="Preview" class="preview-image img-thumbnail" style="max-width: 200px; max-height: 200px;">
                                </div>
                            </div>

                            <div class="alert alert-info d-flex align-items-center" role="alert">
                                <i class="lni lni-bulb me-2 fs-5"></i>
                                <div>
                                    <strong>Tips:</strong> Pastikan foto memiliki kualitas yang baik dan ukuran yang sesuai. Deskripsi akan membantu pengunjung mengenal anggota lebih baik.
                                </div>
                            </div>

                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-success btn-lg px-4" id="btnSimpan">
                                    <i class="lni lni-save me-2"></i> Simpan Anggota
                                </button>
                                <a href="index.php" class="btn btn-outline-secondary btn-lg px-4">
                                    <i class="lni lni-arrow-left me-2"></i> Batal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Info Section -->
            <div class="col-lg-4">
                <!-- Quick Info Card -->
                <div class="card wow fadeInUp mb-3" data-wow-delay=".4s" style="border-left: 4px solid #667eea;">
                    <div class="card-body">
                        <h6 class="card-title text-primary mb-3">
                            <i class="lni lni-information me-2"></i> Informasi
                        </h6>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="lni lni-checkmark-circle text-success me-2"></i>
                                <small>Nama wajib diisi (min 3 karakter)</small>
                            </li>
                            <li class="mb-2">
                                <i class="lni lni-checkmark-circle text-success me-2"></i>
                                <small>Jabatan dapat dipilih atau dikosongkan</small>
                            </li>
                            <li class="mb-2">
                                <i class="lni lni-checkmark-circle text-success me-2"></i>
                                <small>Foto maksimal 2MB</small>
                            </li>
                            <li class="mb-2">
                                <i class="lni lni-checkmark-circle text-success me-2"></i>
                                <small>Deskripsi maksimal 500 karakter</small>
                            </li>
                            <li>
                                <i class="lni lni-checkmark-circle text-success me-2"></i>
                                <small>Semua data dapat diedit nanti</small>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Examples Card -->
                <div class="card wow fadeInUp mb-3" data-wow-delay=".5s" style="border-left: 4px solid #28a745;">
                    <div class="card-body">
                        <h6 class="card-title text-success mb-3">
                            <i class="lni lni-lightbulb me-2"></i> Contoh Deskripsi
                        </h6>
                        <div class="small text-muted">
                            <p class="mb-2"><strong>Contoh 1:</strong></p>
                            <p class="mb-3">"Dosen dengan spesialisasi di bidang Software Engineering. Memiliki pengalaman lebih dari 10 tahun dalam pengembangan aplikasi web dan mobile."</p>
                            
                            <p class="mb-2"><strong>Contoh 2:</strong></p>
                            <p class="mb-0">"Ketua Laboratorium dengan fokus pada penelitian AI dan Machine Learning. Aktif dalam publikasi jurnal internasional."</p>
                        </div>
                    </div>
                </div>

                <!-- Help Card -->
                <div class="card wow fadeInUp" data-wow-delay=".6s" style="border-left: 4px solid #ffc107;">
                    <div class="card-body">
                        <h6 class="card-title text-warning mb-3">
                            <i class="lni lni-question-circle me-2"></i> Butuh Bantuan?
                        </h6>
                        <p class="card-text small mb-2">
                            Jika Anda bingung mengisi form, pastikan minimal mengisi nama. Field lainnya bersifat opsional dan dapat ditambahkan nanti.
                        </p>
                        <a href="index.php" class="btn btn-sm btn-outline-primary">
                            <i class="lni lni-arrow-left me-1"></i> Kembali ke Daftar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
</main>

<!-- Load UI/UX Components -->
<link rel="stylesheet" href="../../assets/css/anggota-ui.css">
<script src="../../assets/js/classes/ToastNotification.js"></script>
<script src="../../assets/js/classes/LoadingState.js"></script>

<?php include __DIR__ . '/../../template/admin_footer.php'; ?>

<script src="../../assets/js/classes/ImagePreview.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formTambahAnggota');
    const inputNama = document.getElementById('nama');
    const textareaDeskripsi = document.getElementById('deskripsi');
    const deskripsiCount = document.getElementById('deskripsi_count');
    const btnSimpan = document.getElementById('btnSimpan');
    
    // Initialize ImagePreview
    if (typeof ImagePreview !== 'undefined') {
        window.imagePreview = new ImagePreview({
            inputId: 'foto',
            previewContainerId: 'previewFoto',
            previewImgId: 'previewImg'
        });
    }
    
    // Deskripsi character counter
    if (textareaDeskripsi && deskripsiCount) {
        textareaDeskripsi.addEventListener('input', function() {
            const length = this.value.length;
            deskripsiCount.textContent = length;
            
            if (length > 500) {
                deskripsiCount.classList.add('text-danger');
                this.classList.add('is-invalid');
            } else {
                deskripsiCount.classList.remove('text-danger');
                this.classList.remove('is-invalid');
            }
        });
    }
    
    // Real-time validation for nama
    if (inputNama) {
        inputNama.addEventListener('input', function() {
            const value = this.value.trim();
            const minLength = 3;
            const maxLength = 100;
            
            this.classList.remove('is-invalid', 'is-valid');
            
            if (value.length === 0) {
                return;
            } else if (value.length < minLength) {
                this.classList.add('is-invalid');
                document.getElementById('nama_error').textContent = 'Nama minimal ' + minLength + ' karakter';
            } else if (value.length > maxLength) {
                this.classList.add('is-invalid');
                document.getElementById('nama_error').textContent = 'Nama maksimal ' + maxLength + ' karakter';
            } else {
                this.classList.add('is-valid');
            }
        });
    }
    
    // Form submission
    if (form) {
        form.addEventListener('submit', function(e) {
            const namaValue = inputNama ? inputNama.value.trim() : '';
            const deskripsiValue = textareaDeskripsi ? textareaDeskripsi.value.trim() : '';
            
            if (namaValue.length < 3) {
                e.preventDefault();
                if (inputNama) {
                    inputNama.classList.add('is-invalid');
                    document.getElementById('nama_error').textContent = 'Nama minimal 3 karakter';
                    inputNama.focus();
                }
                
                if (window.toast) {
                    window.toast.error('Nama minimal 3 karakter', 'Validasi Gagal');
                }
                return false;
            }
            
            if (deskripsiValue.length > 500) {
                e.preventDefault();
                if (window.toast) {
                    window.toast.error('Deskripsi maksimal 500 karakter', 'Validasi Gagal');
                }
                if (textareaDeskripsi) {
                    textareaDeskripsi.focus();
                }
                return false;
            }
            
            // Show loading
            if (window.loading) {
                window.loading.show('Menyimpan anggota...');
            }
            if (btnSimpan) {
                btnSimpan.disabled = true;
                btnSimpan.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
            }
        });
    }
    
    // Show toast from PHP flash messages
    const alertMessages = document.querySelectorAll('.alert:not(.alert-info)');
    alertMessages.forEach(function(alert) {
        const message = alert.textContent.trim();
        const type = alert.classList.contains('alert-success') ? 'success' :
                     alert.classList.contains('alert-danger') ? 'error' :
                     alert.classList.contains('alert-warning') ? 'warning' : 'info';
        
        if (window.toast && message) {
            window.toast.show(message, type);
        }
        
        // Hide original alert after showing toast
        setTimeout(() => {
            alert.style.display = 'none';
        }, 100);
    });
});
</script>

