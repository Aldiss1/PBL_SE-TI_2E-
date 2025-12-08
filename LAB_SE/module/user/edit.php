<?php
$page_title = "Edit User - Admin";
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

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$data = null;
if ($id > 0) {
    $q = pg_query($koneksi, "SELECT * FROM users WHERE id = $id");
    if ($q && pg_num_rows($q) > 0) {
        $data = pg_fetch_assoc($q);
    }
}
if (!$data) {
    pesan_kilat('User tidak ditemukan.', 'error');
    header('Location: index.php');
    exit;
}

// Cek apakah user adalah admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    pesan_kilat('Akses ditolak. Halaman ini hanya dapat diakses oleh ADMIN.', 'error');
    header('Location: index.php');
    exit;
}

// Proses form (Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $role = 'admin'; // Hanya admin saja

    // Validasi
    if ($username === '') {
        pesan_kilat('Username wajib diisi.', 'error');
    } elseif (strlen($username) < 3) {
        pesan_kilat('Username minimal 3 karakter.', 'error');
    } elseif (strlen($username) > 50) {
        pesan_kilat('Username maksimal 50 karakter.', 'error');
    } else {
        $username_e = pg_escape_string($koneksi, $username);
        
        // Cek apakah username sudah digunakan oleh user lain
        $check = pg_query($koneksi, "SELECT id FROM users WHERE username = '$username_e' AND id != $id LIMIT 1");
        if ($check && pg_num_rows($check) > 0) {
            pesan_kilat('Username sudah digunakan.', 'error');
        } else {
            // Validasi password jika diisi
            $query = '';
            if ($password !== '') {
                if (strlen($password) < 5) {
                    pesan_kilat('Password minimal 5 karakter.', 'error');
                } elseif ($password !== $password_confirm) {
                    pesan_kilat('Password dan konfirmasi password tidak cocok.', 'error');
                } else {
                    $password_md5 = md5($password);
                    $query = "UPDATE users SET username = '$username_e', password = '$password_md5', role = 'admin', updated_at = NOW() WHERE id = $id";
                }
            } else {
                // Jika password kosong, tidak update password
                $query = "UPDATE users SET username = '$username_e', role = 'admin', updated_at = NOW() WHERE id = $id";
            }
            
            if ($query !== '') {
                $result = pg_query($koneksi, $query);
                if ($result) {
                    pesan_kilat('Admin berhasil diperbarui!', 'success');
                    header('Location: index.php');
                    exit;
                } else {
                    pesan_kilat('Gagal memperbarui admin. Error: ' . pg_last_error($koneksi), 'error');
                }
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
                    <h2 class="mb-15 wow fadeInUp" data-wow-delay=".2s">Edit Admin</h2>
                    <p class="wow fadeInUp" data-wow-delay=".4s">Perbarui data akun admin</p>
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
                            <i class="lni lni-pencil text-primary"></i> Form Edit Admin
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="" id="formEditUser">
                            <div class="mb-4">
                                <label for="username" class="form-label form-label-custom">
                                    <i class="lni lni-user"></i> Username <span class="text-danger">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    class="form-control form-control-lg form-input-custom" 
                                    id="username" 
                                    name="username" 
                                    value="<?php echo htmlspecialchars($data['username']); ?>" 
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
                                    <i class="lni lni-lock"></i> Password Baru (Opsional)
                                </label>
                                <input 
                                    type="password" 
                                    class="form-control form-control-lg form-input-custom" 
                                    id="password" 
                                    name="password" 
                                    placeholder="Kosongkan jika tidak ingin mengubah password"
                                    minlength="5">
                                <div class="form-text">
                                    <i class="lni lni-information"></i> Password untuk login (minimal 5 karakter). Kosongkan jika tidak ingin mengubah password
                                </div>
                                <div class="invalid-feedback" id="password_error"></div>
                            </div>

                            <div class="mb-4">
                                <label for="password_confirm" class="form-label form-label-custom">
                                    <i class="lni lni-lock-alt"></i> Konfirmasi Password Baru
                                </label>
                                <input 
                                    type="password" 
                                    class="form-control form-control-lg form-input-custom" 
                                    id="password_confirm" 
                                    name="password_confirm" 
                                    placeholder="Masukkan ulang password baru">
                                <div class="form-text">
                                    <i class="lni lni-information"></i> Masukkan password yang sama untuk konfirmasi (jika mengubah password)
                                </div>
                                <div class="invalid-feedback" id="password_confirm_error"></div>
                            </div>

                            <div class="alert alert-info d-flex align-items-center" role="alert">
                                <i class="lni lni-bulb me-2 fs-5"></i>
                                <div>
                                    <strong>Tips:</strong> Kosongkan field password jika tidak ingin mengubah password. Jika mengubah password, pastikan password dan konfirmasi password cocok.
                                </div>
                            </div>

                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-primary btn-lg px-4" id="btnUpdate">
                                    <i class="lni lni-save me-2"></i> Update Admin
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
                <!-- Data Saat Ini Card -->
                <div class="card wow fadeInUp mb-3" data-wow-delay=".4s" style="border-left: 4px solid #667eea;">
                    <div class="card-body">
                        <h6 class="card-title text-primary mb-3">
                            <i class="lni lni-database me-2"></i> Data Saat Ini
                        </h6>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <strong>Username:</strong><br>
                                <span class="text-muted"><?php echo htmlspecialchars($data['username']); ?></span>
                            </li>
                            <li class="mb-2">
                                <strong>Role:</strong><br>
                                <span class="badge bg-danger">Admin</span>
                            </li>
                            <li class="mb-2">
                                <strong>Tanggal Dibuat:</strong><br>
                                <span class="text-muted"><?php echo $data['created_at'] ? date('d/m/Y H:i', strtotime($data['created_at'])) : '-'; ?></span>
                            </li>
                            <li>
                                <strong>Terakhir Diupdate:</strong><br>
                                <span class="text-muted"><?php echo $data['updated_at'] ? date('d/m/Y H:i', strtotime($data['updated_at'])) : '-'; ?></span>
                            </li>
                        </ul>
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
                                <small>Password opsional</small>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Help Card -->
                <div class="card wow fadeInUp" data-wow-delay=".6s" style="border-left: 4px solid #ffc107;">
                    <div class="card-body">
                        <h6 class="card-title text-warning mb-3">
                            <i class="lni lni-question-circle me-2"></i> Butuh Bantuan?
                        </h6>
                        <p class="card-text small mb-2">
                            Jika Anda mengubah password, pastikan untuk mengingat password baru. Jika lupa, hubungi administrator.
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
    const form = document.getElementById('formEditUser');
    const inputUsername = document.getElementById('username');
    const inputPassword = document.getElementById('password');
    const inputPasswordConfirm = document.getElementById('password_confirm');
    const btnUpdate = document.getElementById('btnUpdate');
    
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
    
    // Real-time validation for password (only if filled)
    inputPassword.addEventListener('input', function() {
        const value = this.value;
        const minLength = 5;
        
        // Remove previous validation classes
        this.classList.remove('is-invalid', 'is-valid');
        
        if (value.length === 0) {
            // Empty is OK for optional field
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
    
    // Real-time validation for password confirm (only if password is filled)
    inputPasswordConfirm.addEventListener('input', function() {
        const value = this.value;
        const passwordValue = inputPassword.value;
        
        // Remove previous validation classes
        this.classList.remove('is-invalid', 'is-valid');
        
        if (passwordValue.length === 0) {
            // Password is empty, so confirm is not needed
            return;
        }
        
        if (value.length === 0) {
            // Empty when password is filled
            this.classList.add('is-invalid');
            document.getElementById('password_confirm_error').textContent = 'Konfirmasi password wajib diisi jika mengubah password';
        } else if (value !== passwordValue) {
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
        
        // If password is filled, validate it
        if (passwordValue.length > 0) {
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
        }
        
        if (!isValid) {
            if (window.toast) {
                window.toast.error('Mohon perbaiki kesalahan pada form', 'Validasi Gagal');
            }
            return false;
        }
        
        // Show loading
        if (window.loading) {
            window.loading.show('Memperbarui admin...');
        }
        btnUpdate.disabled = true;
        btnUpdate.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memperbarui...';
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

