<?php
$page_title = "Edit Anggota - Admin";
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

$id = (int)($_GET['id'] ?? 0);
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
    $deskripsi = clean_input($_POST['deskripsi'] ?? '');
    $instagram = clean_input($_POST['instagram'] ?? '');
    $linkedin = clean_input($_POST['linkedin'] ?? '');

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
        $deskripsi_e = pg_escape_string($koneksi, $deskripsi);
        $instagram_e = pg_escape_string($koneksi, $instagram);
        $linkedin_e = pg_escape_string($koneksi, $linkedin);
        $foto_e = pg_escape_string($koneksi, $foto_path);

        $query = "UPDATE anggota SET 
                    nama = '$nama_e',
                    jabatan = NULLIF('$jabatan_e',''),
                    deskripsi = NULLIF('$deskripsi_e',''),
                    instagram = NULLIF('$instagram_e',''),
                    linkedin = NULLIF('$linkedin_e',''),
                    updated_at = NOW()
                  WHERE id = $id";
        if ($foto_path !== '') {
            $query = "UPDATE anggota SET 
                        nama = '$nama_e',
                        jabatan = NULLIF('$jabatan_e',''),
                        deskripsi = NULLIF('$deskripsi_e',''),
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
<section class="pt-50 pb-50 admin-section-bg">
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
            <!-- Form Section -->
            <div class="col-lg-8">
                <div class="card wow fadeInUp form-card" data-wow-delay=".3s">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="card-title mb-0">
                            <i class="lni lni-pencil text-primary"></i> Form Edit Anggota
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="" enctype="multipart/form-data" id="formEditAnggota">
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
                                        value="<?php echo htmlspecialchars($data['nama']); ?>"
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
                                        <option value="<?php echo htmlspecialchars($j); ?>" <?php echo ($data['jabatan'] === $j) ? 'selected' : ''; ?>><?php echo htmlspecialchars($j); ?></option>
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
                                    maxlength="500"><?php echo htmlspecialchars($data['deskripsi'] ?? ''); ?></textarea>
                                <div class="form-text">
                                    <i class="lni lni-information"></i> Deskripsi singkat tentang anggota (maksimal 500 karakter)
                                </div>
                                <small class="text-muted float-end">
                                    <span id="deskripsi_count"><?php echo strlen($data['deskripsi'] ?? ''); ?></span>/500 karakter
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
                                        value="<?php echo htmlspecialchars($data['instagram'] ?? ''); ?>"
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
                                        value="<?php echo htmlspecialchars($data['linkedin'] ?? ''); ?>"
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
                                <?php 
                                $foto_raw = $data['foto'] ?? '';
                                $is_abs = preg_match('/^https?:\\/\\//', $foto_raw) || (strlen($foto_raw) > 0 && $foto_raw[0] === '/');
                                $foto_resolved = $is_abs ? $foto_raw : ($foto_raw ? '../../' . $foto_raw : '');
                                if (!empty($foto_resolved)): ?>
                                    <div class="mb-3">
                                        <p class="small text-muted mb-2">Foto Saat Ini:</p>
                                        <img src="<?php echo htmlspecialchars($foto_resolved); ?>" alt="Foto" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                                    </div>
                                <?php endif; ?>
                                <input 
                                    type="file" 
                                    class="form-control form-control-lg form-file-custom" 
                                    id="foto" 
                                    name="foto" 
                                    accept=".jpg,.jpeg,.png,.webp">
                                <div class="form-text">
                                    <i class="lni lni-information"></i> Maks 2MB. Format: JPG, JPEG, PNG, WEBP. Kosongkan jika tidak ingin mengubah foto.
                                </div>
                                <div id="previewFoto" class="preview-container mt-3" style="display: none;">
                                    <p class="small text-muted mb-2">Preview Foto Baru:</p>
                                    <img id="previewImg" src="#" alt="Preview" class="preview-image img-thumbnail" style="max-width: 200px; max-height: 200px;">
                                </div>
                            </div>

                            <div class="alert alert-info d-flex align-items-center" role="alert">
                                <i class="lni lni-bulb me-2 fs-5"></i>
                                <div>
                                    <strong>Tips:</strong> Pastikan semua informasi sudah benar sebelum menyimpan. Perubahan akan langsung tersimpan ke database.
                                </div>
                            </div>

                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-primary btn-lg px-4" id="btnUpdate">
                                    <i class="lni lni-save me-2"></i> Update Anggota
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
                <!-- Current Data Card -->
                <div class="card wow fadeInUp mb-3" data-wow-delay=".4s" style="border-left: 4px solid #667eea;">
                    <div class="card-body">
                        <h6 class="card-title text-primary mb-3">
                            <i class="lni lni-file me-2"></i> Data Saat Ini
                        </h6>
                        <div class="mb-2">
                            <small class="text-muted d-block">Nama</small>
                            <strong><?php echo htmlspecialchars($data['nama']); ?></strong>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted d-block">Jabatan</small>
                            <span><?php echo !empty($data['jabatan']) ? htmlspecialchars($data['jabatan']) : '<em class="text-muted">Belum ada</em>'; ?></span>
                        </div>
                        <?php if (isset($data['created_at']) && !empty($data['created_at'])): ?>
                        <div class="mb-2">
                            <small class="text-muted d-block">
                                <i class="lni lni-calendar me-1"></i> Dibuat
                            </small>
                            <small><?php echo date('d M Y', strtotime($data['created_at'])); ?></small>
                        </div>
                        <?php endif; ?>
                        <?php if (isset($data['updated_at']) && !empty($data['updated_at']) && $data['updated_at'] != $data['created_at']): ?>
                        <div class="mb-2">
                            <small class="text-muted d-block">
                                <i class="lni lni-reload me-1"></i> Terakhir Diupdate
                            </small>
                            <small><?php echo date('d M Y', strtotime($data['updated_at'])); ?></small>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Info Card -->
                <div class="card wow fadeInUp mb-3" data-wow-delay=".5s" style="border-left: 4px solid #28a745;">
                    <div class="card-body">
                        <h6 class="card-title text-success mb-3">
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
                                <small>Perubahan akan langsung tersimpan</small>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Help Card -->
                <div class="card wow fadeInUp" data-wow-delay=".6s" style="border-left: 4px solid #ffc107;">
                    <div class="card-body">
                        <h6 class="card-title text-warning mb-3">
                            <i class="lni lni-question-circle me-2"></i> Tips
                        </h6>
                        <p class="card-text small mb-2">
                            Pastikan semua informasi sudah benar sebelum menyimpan. Foto yang diupload akan menggantikan foto lama jika ada.
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
    const form = document.getElementById('formEditAnggota');
    const inputNama = document.getElementById('nama');
    const textareaDeskripsi = document.getElementById('deskripsi');
    const deskripsiCount = document.getElementById('deskripsi_count');
    const btnUpdate = document.getElementById('btnUpdate');
    const originalNama = inputNama ? inputNama.value.trim() : '';
    const originalDeskripsi = textareaDeskripsi ? textareaDeskripsi.value.trim() : '';
    
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
            } else if (value === originalNama) {
                this.classList.remove('is-valid', 'is-invalid');
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
            
            // Check if there are changes
            const jabatanSelect = document.getElementById('jabatan');
            const instagramInput = document.getElementById('instagram');
            const linkedinInput = document.getElementById('linkedin');
            const fotoInput = document.getElementById('foto');
            
            const originalJabatan = jabatanSelect ? jabatanSelect.value : '';
            const originalInstagram = instagramInput ? instagramInput.value.trim() : '';
            const originalLinkedin = linkedinInput ? linkedinInput.value.trim() : '';
            
            const currentJabatan = jabatanSelect ? jabatanSelect.value : '';
            const currentInstagram = instagramInput ? instagramInput.value.trim() : '';
            const currentLinkedin = linkedinInput ? linkedinInput.value.trim() : '';
            
            const hasChanges = namaValue !== originalNama || 
                              deskripsiValue !== originalDeskripsi ||
                              currentJabatan !== originalJabatan ||
                              currentInstagram !== originalInstagram ||
                              currentLinkedin !== originalLinkedin ||
                              (fotoInput && fotoInput.files.length > 0);
            
            if (!hasChanges) {
                e.preventDefault();
                if (window.toast) {
                    window.toast.info('Tidak ada perubahan yang dilakukan', 'Tidak Ada Perubahan');
                }
                return false;
            }
            
            // Show loading
            if (window.loading) {
                window.loading.show('Mengupdate anggota...');
            }
            if (btnUpdate) {
                btnUpdate.disabled = true;
                btnUpdate.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengupdate...';
            }
        });
    }
    
    // Show toast from PHP flash messages (only if toast is available)
    if (window.toast) {
        const alertMessages = document.querySelectorAll('.alert:not(.alert-info)');
        alertMessages.forEach(function(alert) {
            // Get message text (exclude close button text)
            const closeBtn = alert.querySelector('.btn-close');
            let message = alert.textContent.trim();
            if (closeBtn) {
                message = message.replace(closeBtn.textContent.trim(), '').trim();
            }
            
            if (message) {
                const type = alert.classList.contains('alert-success') ? 'success' :
                             alert.classList.contains('alert-danger') ? 'error' :
                             alert.classList.contains('alert-warning') ? 'warning' : 'info';
                
                window.toast.show(message, type);
                
                // Hide original alert after showing toast
                setTimeout(() => {
                    alert.style.display = 'none';
                }, 100);
            }
        });
    }
});
</script>

