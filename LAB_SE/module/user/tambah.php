<?php
$page_title = "Tambah User - Admin";
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../cek_login.php';
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

// Proses form (Create)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = clean_input($_POST['role'] ?? 'admin');

    // Validasi
    if ($username === '') {
        pesan_kilat('Username wajib diisi.', 'error');
    } elseif (strlen($username) < 3) {
        pesan_kilat('Username minimal 3 karakter.', 'error');
    } elseif ($password === '') {
        pesan_kilat('Password wajib diisi.', 'error');
    } elseif (strlen($password) < 5) {
        pesan_kilat('Password minimal 5 karakter.', 'error');
    } elseif (!in_array($role, ['admin', 'mahasiswa'])) {
        pesan_kilat('Role tidak valid.', 'error');
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
                pesan_kilat('User berhasil ditambahkan!', 'success');
                header('Location: index.php');
                exit;
            } else {
                pesan_kilat('Gagal menambahkan user. Error: ' . pg_last_error($koneksi), 'error');
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
<section class="pt-50 pb-50" style="background: #f8f9fa; padding-top: 30px !important;">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="section-title mb-50">
                    <h2 class="mb-15 wow fadeInUp" data-wow-delay=".2s">Tambah User</h2>
                    <p class="wow fadeInUp" data-wow-delay=".4s">Tambahkan user baru (admin atau mahasiswa)</p>
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
                                <label for="username" class="form-label" style="font-weight: 600; color: #2c3e50;">Username *</label>
                                <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan username" required style="padding: 12px; border-radius: 5px; border: 1px solid #dee2e6;" minlength="3">
                                <small class="text-muted">Minimal 3 karakter</small>
                            </div>
                            <div class="col-lg-6">
                                <label for="password" class="form-label" style="font-weight: 600; color: #2c3e50;">Password *</label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password" required style="padding: 12px; border-radius: 5px; border: 1px solid #dee2e6;" minlength="5">
                                <small class="text-muted">Minimal 5 karakter</small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-lg-6">
                                <label for="role" class="form-label" style="font-weight: 600; color: #2c3e50;">Role *</label>
                                <select class="form-select" id="role" name="role" required style="padding: 12px; border-radius: 5px; border: 1px solid #dee2e6;">
                                    <option value="admin" selected>Admin</option>
                                    <option value="mahasiswa">Mahasiswa</option>
                                </select>
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

