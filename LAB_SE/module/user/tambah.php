<?php
$page_title = "Tambah User - Admin";
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../auth/cek_login.php';
require_once __DIR__ . '/../../config/anti_injection.php';
require_once __DIR__ . '/../../fungsi/pesan_kilat.php';

// Set active menu
$current_page = 'user';
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
        'active' => false
    ],
    'user' => [
        'title' => 'Kelola User',
        'icon' => 'lni lni-user',
        'link' => '../../module/user/index.php',
        'active' => true
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
    ],
    'penelitian' => [
        'title' => 'Kelola Penelitian',
        'icon' => 'lni lni-microscope',
        'link' => '../../module/penelitian/index.php',
        'active' => false
    ],
    'publikasi' => [
        'title' => 'Kelola Publikasi',
        'icon' => 'lni lni-book',
        'link' => '../../module/publikasi/index.php',
        'active' => false
    ]
];

// Cek apakah user adalah admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    pesan_kilat('Akses ditolak. Halaman ini hanya dapat diakses oleh ADMIN.', 'error');
    header('Location: index.php');
    exit;
}

// Proses form (Create)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = 'admin'; // Hanya admin saja

    // Validasi
    if ($username === '') {
        pesan_kilat('Username wajib diisi.', 'error');
    } elseif (strlen($username) < 3) {
        pesan_kilat('Username minimal 3 karakter.', 'error');
    } elseif ($password === '') {
        pesan_kilat('Password wajib diisi.', 'error');
    } elseif (strlen($password) < 5) {
        pesan_kilat('Password minimal 5 karakter.', 'error');
    } else {
        $username_e = pg_escape_string($koneksi, $username);
        
        // Cek apakah username sudah ada
        $check = pg_query($koneksi, "SELECT id FROM users WHERE username = '$username_e' LIMIT 1");
        if ($check && pg_num_rows($check) > 0) {
            pesan_kilat('Username sudah digunakan.', 'error');
        } else {
            $password_md5 = md5($password);
            $role_e = pg_escape_string($koneksi, $role);
            
            $query = "INSERT INTO users (username, password, role, created_at, updated_at) VALUES ('$username_e', '$password_md5', '$role_e', NOW(), NOW())";
            $result = pg_query($koneksi, $query);
            
            if ($result) {
                pesan_kilat('Admin berhasil ditambahkan!', 'success');
                header('Location: index.php');
                exit;
            } else {
                pesan_kilat('Gagal menambahkan admin. Error: ' . pg_last_error($koneksi), 'error');
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
                    <h2 class="mb-15 wow fadeInUp" data-wow-delay=".2s">Tambah Admin</h2>
                    <p class="wow fadeInUp" data-wow-delay=".4s">Tambahkan akun admin baru ke dalam sistem</p>
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
                            <i class="lni lni-user text-primary"></i> Form Tambah Admin
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="" id="formTambahUser">
                            <div class="mb-4">
                                <label for="username" class="form-label form-label-custom">
                                    <i class="lni lni-user"></i> Username <span class="text-danger">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    class="form-control form-control-lg form-input-custom" 
                                    id="username" 
                                    name="username" 
                                    placeholder="Contoh: admin, admin_lab, ketua_lab" 
                                    required
                                    minlength="3"
                                    maxlength="50"
                                    autofocus>
                                <div class="form-text">
                                    <i class="lni lni-information"></i> Username untuk login ke sistem (minimal 3 karakter, maksimal 50 karakter)
                                </div>
                                <div class="invalid-feedback" id="username_error"></div>
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label form-label-custom">
                                    <i class="lni lni-lock"></i> Password <span class="text-danger">*</span>
                                </label>
                                <input 
                                    type="password" 
                                    class="form-control form-control-lg form-input-custom" 
                                    id="password" 
                                    name="password" 
                                    placeholder="Masukkan password"
                                    required 
                                    minlength="5">
                                <div class="form-text">
                                    <i class="lni lni-information"></i> Password untuk login (minimal 5 karakter)
                                </div>
                                <div class="invalid-feedback" id="password_error"></div>
                            </div>

                            <div class="mb-4">
                                <label for="password_confirm" class="form-label form-label-custom">
                                    <i class="lni lni-lock-alt"></i> Konfirmasi Password <span class="text-danger">*</span>
                                </label>
                                <input 
                                    type="password" 
                                    class="form-control form-control-lg form-input-custom" 
                                    id="password_confirm" 
                                    name="password_confirm" 
                                    placeholder="Masukkan ulang password"
                                    required>
                                <div class="form-text">
                                    <i class="lni lni-information"></i> Masukkan password yang sama untuk konfirmasi
                                </div>
                                <div class="invalid-feedback" id="password_confirm_error"></div>
                            </div>

                            <div class="alert alert-info d-flex align-items-center" role="alert">
                                <i class="lni lni-bulb me-2 fs-5"></i>
                                <div>
                                    <strong>Tips:</strong> Pastikan username unik dan password kuat. Simpan informasi login dengan aman.
                                </div>
                            </div>

                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-success btn-lg px-4" id="btnSimpan">
                                    <i class="lni lni-save me-2"></i> Simpan Admin
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
                                <small>Username harus unik</small>
                            </li>
                            <li class="mb-2">
                                <i class="lni lni-checkmark-circle text-success me-2"></i>
                                <small>Minimal 3 karakter</small>
                            </li>
                            <li class="mb-2">
                                <i class="lni lni-checkmark-circle text-success me-2"></i>
                                <small>Maksimal 50 karakter</small>
                            </li>
                            <li class="mb-2">
                                <i class="lni lni-checkmark-circle text-success me-2"></i>
                                <small>Password minimal 5 karakter</small>
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
                            <i class="lni lni-lightbulb me-2"></i> Contoh Username
                        </h6>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge bg-light text-dark">admin</span>
                            <span class="badge bg-light text-dark">admin_lab</span>
                            <span class="badge bg-light text-dark">ketua_lab</span>
                            <span class="badge bg-light text-dark">koordinator</span>
                            <span class="badge bg-light text-dark">sekretaris</span>
                            <span class="badge bg-light text-dark">bendahara</span>
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
                            Halaman ini hanya dapat diakses oleh ADMIN. Semua akun yang dibuat akan memiliki role admin dan dapat mengakses semua fitur sistem.
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
    const form = document.getElementById('formTambahUser');
    const inputUsername = document.getElementById('username');
    const inputPassword = document.getElementById('password');
    const inputPasswordConfirm = document.getElementById('password_confirm');
    const btnSimpan = document.getElementById('btnSimpan');
    
    // Real-time validation for username
    inputUsername.addEventListener('input', function() {
        const value = this.value.trim();
        const minLength = 3;
        const maxLength = 50;
        
        // Remove previous validation classes
        this.classList.remove('is-invalid', 'is-valid');
        
        if (value.length === 0) {
            // Empty, no validation yet
            return;
        } else if (value.length < minLength) {
            this.classList.add('is-invalid');
            document.getElementById('username_error').textContent = 'Username minimal ' + minLength + ' karakter';
        } else if (value.length > maxLength) {
            this.classList.add('is-invalid');
            document.getElementById('username_error').textContent = 'Username maksimal ' + maxLength + ' karakter';
        } else {
            this.classList.add('is-valid');
        }
    });
    
    // Real-time validation for password
    inputPassword.addEventListener('input', function() {
        const value = this.value;
        const minLength = 5;
        
        // Remove previous validation classes
        this.classList.remove('is-invalid', 'is-valid');
        
        if (value.length === 0) {
            return;
        } else if (value.length < minLength) {
            this.classList.add('is-invalid');
            document.getElementById('password_error').textContent = 'Password minimal ' + minLength + ' karakter';
        } else {
            this.classList.add('is-valid');
        }
        
        // Check password confirm if it has value
        if (inputPasswordConfirm.value) {
            inputPasswordConfirm.dispatchEvent(new Event('input'));
        }
    });
    
    // Real-time validation for password confirm
    inputPasswordConfirm.addEventListener('input', function() {
        const value = this.value;
        
        // Remove previous validation classes
        this.classList.remove('is-invalid', 'is-valid');
        
        if (value.length === 0) {
            return;
        } else if (value !== inputPassword.value) {
            this.classList.add('is-invalid');
            document.getElementById('password_confirm_error').textContent = 'Password tidak cocok';
        } else {
            this.classList.add('is-valid');
        }
    });
    
    // Form submission
    form.addEventListener('submit', function(e) {
        const usernameValue = inputUsername.value.trim();
        const passwordValue = inputPassword.value;
        const passwordConfirmValue = inputPasswordConfirm.value;
        
        let isValid = true;
        
        if (usernameValue.length < 3) {
            e.preventDefault();
            inputUsername.classList.add('is-invalid');
            document.getElementById('username_error').textContent = 'Username minimal 3 karakter';
            inputUsername.focus();
            isValid = false;
        }
        
        if (passwordValue.length < 5) {
            e.preventDefault();
            inputPassword.classList.add('is-invalid');
            document.getElementById('password_error').textContent = 'Password minimal 5 karakter';
            if (isValid) {
                inputPassword.focus();
            }
            isValid = false;
        }
        
        if (passwordValue !== passwordConfirmValue) {
            e.preventDefault();
            inputPasswordConfirm.classList.add('is-invalid');
            document.getElementById('password_confirm_error').textContent = 'Password tidak cocok';
            if (isValid) {
                inputPasswordConfirm.focus();
            }
            isValid = false;
        }
        
        if (!isValid) {
            if (window.toast) {
                window.toast.error('Mohon perbaiki kesalahan pada form', 'Validasi Gagal');
            }
            return false;
        }
        
        // Show loading
        if (window.loading) {
            window.loading.show('Menyimpan admin...');
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

