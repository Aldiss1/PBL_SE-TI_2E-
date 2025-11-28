<?php
$page_title = "Tambah Jabatan - Admin";
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../auth/cek_login.php';
require_once __DIR__ . '/../../config/anti_injection.php';
require_once __DIR__ . '/../../fungsi/pesan_kilat.php';

// Active menu
$current_page = 'jabatan';
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
        'active' => false
    ],
    'jabatan' => [
        'title' => 'Kelola Jabatan',
        'icon' => 'lni lni-briefcase',
        'link' => '../../module/jabatan/index.php',
        'active' => true
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama_jabatan'] ?? '');
    $nama = clean_input($nama);

    if ($nama === '') {
        pesan_kilat('Nama jabatan wajib diisi.', 'error');
    } else {
        $nama_e = pg_escape_string($koneksi, $nama);
        // Optional: prevent duplicates (case-insensitive)
        $dup = pg_query($koneksi, "SELECT 1 FROM jabatan WHERE LOWER(nama_jabatan) = LOWER('$nama_e') LIMIT 1");
        if ($dup && pg_num_rows($dup) > 0) {
            pesan_kilat('Jabatan sudah ada.', 'error');
        } else {
            $q = pg_query($koneksi, "INSERT INTO jabatan (nama_jabatan, created_at, updated_at) VALUES ('$nama_e', NOW(), NOW())");
            if ($q) {
                pesan_kilat('Jabatan berhasil ditambahkan!', 'success');
                header('Location: index.php');
                exit;
            } else {
                pesan_kilat('Gagal menambahkan jabatan. Error: ' . pg_last_error($koneksi), 'error');
            }
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
                    <h2 class="mb-15 wow fadeInUp" data-wow-delay=".2s">Tambah Jabatan</h2>
                    <p class="wow fadeInUp" data-wow-delay=".4s">Masukkan nama jabatan/posisi</p>
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
                            <i class="lni lni-briefcase text-primary"></i> Form Tambah Jabatan
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="" id="formTambahJabatan">
                            <div class="mb-4">
                                <label for="nama_jabatan" class="form-label form-label-custom">
                                    <i class="lni lni-tag"></i> Nama Jabatan <span class="text-danger">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    class="form-control form-control-lg form-input-custom" 
                                    id="nama_jabatan" 
                                    name="nama_jabatan" 
                                    placeholder="Contoh: Ketua Lab, Wakil Ketua, Dosen Pengampu" 
                                    required
                                    minlength="3"
                                    maxlength="100"
                                    autofocus>
                                <div class="form-text">
                                    <i class="lni lni-information"></i> Masukkan nama jabatan atau posisi di laboratorium (minimal 3 karakter)
                                </div>
                                <div class="invalid-feedback" id="nama_jabatan_error"></div>
                            </div>

                            <div class="alert alert-info d-flex align-items-center" role="alert">
                                <i class="lni lni-bulb me-2 fs-5"></i>
                                <div>
                                    <strong>Tips:</strong> Pastikan nama jabatan unik dan jelas. Jabatan ini akan digunakan untuk mengelompokkan anggota laboratorium.
                                </div>
                            </div>

                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-success btn-lg px-4" id="btnSimpan">
                                    <i class="lni lni-save me-2"></i> Simpan Jabatan
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
                                <small>Nama jabatan harus unik</small>
                            </li>
                            <li class="mb-2">
                                <i class="lni lni-checkmark-circle text-success me-2"></i>
                                <small>Minimal 3 karakter</small>
                            </li>
                            <li class="mb-2">
                                <i class="lni lni-checkmark-circle text-success me-2"></i>
                                <small>Maksimal 100 karakter</small>
                            </li>
                            <li>
                                <i class="lni lni-checkmark-circle text-success me-2"></i>
                                <small>Dapat diedit nanti</small>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Examples Card -->
                <div class="card wow fadeInUp mb-3" data-wow-delay=".5s" style="border-left: 4px solid #28a745;">
                    <div class="card-body">
                        <h6 class="card-title text-success mb-3">
                            <i class="lni lni-lightbulb me-2"></i> Contoh Jabatan
                        </h6>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge bg-light text-dark">Ketua Lab</span>
                            <span class="badge bg-light text-dark">Wakil Ketua</span>
                            <span class="badge bg-light text-dark">Dosen Pengampu</span>
                            <span class="badge bg-light text-dark">Koordinator</span>
                            <span class="badge bg-light text-dark">Sekretaris</span>
                            <span class="badge bg-light text-dark">Bendahara</span>
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
                            Jika Anda bingung menentukan nama jabatan, lihat contoh di atas atau hubungi administrator.
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formTambahJabatan');
    const inputNama = document.getElementById('nama_jabatan');
    const btnSimpan = document.getElementById('btnSimpan');
    
    // Real-time validation
    inputNama.addEventListener('input', function() {
        const value = this.value.trim();
        const minLength = 3;
        const maxLength = 100;
        
        // Remove previous validation classes
        this.classList.remove('is-invalid', 'is-valid');
        
        if (value.length === 0) {
            // Empty, no validation yet
            return;
        } else if (value.length < minLength) {
            this.classList.add('is-invalid');
            document.getElementById('nama_jabatan_error').textContent = 'Nama jabatan minimal ' + minLength + ' karakter';
        } else if (value.length > maxLength) {
            this.classList.add('is-invalid');
            document.getElementById('nama_jabatan_error').textContent = 'Nama jabatan maksimal ' + maxLength + ' karakter';
        } else {
            this.classList.add('is-valid');
        }
    });
    
    // Form submission
    form.addEventListener('submit', function(e) {
        const value = inputNama.value.trim();
        
        if (value.length < 3) {
            e.preventDefault();
            inputNama.classList.add('is-invalid');
            document.getElementById('nama_jabatan_error').textContent = 'Nama jabatan minimal 3 karakter';
            inputNama.focus();
            
            if (window.toast) {
                window.toast.error('Nama jabatan minimal 3 karakter', 'Validasi Gagal');
            }
            return false;
        }
        
        // Show loading
        if (window.loading) {
            window.loading.show('Menyimpan jabatan...');
        }
        btnSimpan.disabled = true;
        btnSimpan.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
    });
    
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

<?php include __DIR__ . '/../../template/admin_footer.php'; ?>
