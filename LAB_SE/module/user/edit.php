<?php
$page_title = "Edit User - Admin";
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

// Proses form (Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = clean_input($_POST['role'] ?? 'admin');

    // Validasi
    if ($username === '') {
        pesan_kilat('Username wajib diisi.', 'error');
    } elseif (strlen($username) < 3) {
        pesan_kilat('Username minimal 3 karakter.', 'error');
    } elseif (!in_array($role, ['admin', 'mahasiswa'])) {
        pesan_kilat('Role tidak valid.', 'error');
    } else {
        $username_e = pg_escape_string($koneksi, $username);
        
        // Cek apakah username sudah digunakan oleh user lain
        $check = pg_query($koneksi, "SELECT id FROM users WHERE username = '$username_e' AND id != $id LIMIT 1");
        if ($check && pg_num_rows($check) > 0) {
            pesan_kilat('Username sudah digunakan.', 'error');
        } else {
            $role_e = pg_escape_string($koneksi, $role);
            
            // Update username dan role
            if ($password !== '') {
                // Jika password diisi, update password juga
                if (strlen($password) < 5) {
                    pesan_kilat('Password minimal 5 karakter.', 'error');
                } else {
                    $password_md5 = md5($password);
                    $query = "UPDATE users SET username = '$username_e', password = '$password_md5', role = '$role_e', updated_at = NOW() WHERE id = $id";
                }
            } else {
                // Jika password kosong, tidak update password
                $query = "UPDATE users SET username = '$username_e', role = '$role_e', updated_at = NOW() WHERE id = $id";
            }
            
            $result = pg_query($koneksi, $query);
            if ($result) {
                pesan_kilat('User berhasil diperbarui!', 'success');
                header('Location: index.php');
                exit;
            } else {
                pesan_kilat('Gagal memperbarui user. Error: ' . pg_last_error($koneksi), 'error');
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
                    <h2 class="mb-15 wow fadeInUp" data-wow-delay=".2s">Edit User</h2>
                    <p class="wow fadeInUp" data-wow-delay=".4s">Perbarui data user</p>
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
                                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($data['username']); ?>" required style="padding: 12px; border-radius: 5px; border: 1px solid #dee2e6;" minlength="3">
                                <small class="text-muted">Minimal 3 karakter</small>
                            </div>
                            <div class="col-lg-6">
                                <label for="password" class="form-label" style="font-weight: 600; color: #2c3e50;">Password Baru (Opsional)</label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Kosongkan jika tidak ingin mengubah password" style="padding: 12px; border-radius: 5px; border: 1px solid #dee2e6;" minlength="5">
                                <small class="text-muted">Minimal 5 karakter. Kosongkan jika tidak ingin mengubah password</small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-lg-6">
                                <label for="role" class="form-label" style="font-weight: 600; color: #2c3e50;">Role *</label>
                                <select class="form-select" id="role" name="role" required style="padding: 12px; border-radius: 5px; border: 1px solid #dee2e6;">
                                    <option value="admin" <?php echo $data['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                    <option value="mahasiswa" <?php echo $data['role'] === 'mahasiswa' ? 'selected' : ''; ?>>Mahasiswa</option>
                                </select>
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

