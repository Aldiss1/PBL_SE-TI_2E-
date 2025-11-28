<?php
$page_title = "Kelola User - Admin";
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../auth/cek_login.php';
require_once __DIR__ . '/../../config/anti_injection.php';
require_once __DIR__ . '/../../fungsi/pesan_kilat.php';

// Cek apakah user adalah admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    pesan_kilat('Akses ditolak. Halaman ini hanya dapat diakses oleh ADMIN.', 'error');
    header('Location: ../../module/admin/dashboard.php');
    exit;
}

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
    ]
];

include __DIR__ . '/../../template/admin_header.php';

// Pencarian
$search_query = isset($_GET['search']) ? trim(clean_input($_GET['search'])) : '';

// Hitung total user (hanya admin)
$count_query = "SELECT COUNT(*) as total FROM users WHERE role = 'admin'";
if (!empty($search_query)) {
    $search_escaped = pg_escape_string($koneksi, $search_query);
    $count_query = "SELECT COUNT(*) as total FROM users WHERE role = 'admin' AND (username ILIKE '%$search_escaped%' OR role ILIKE '%$search_escaped%')";
}
$count_result = pg_query($koneksi, $count_query);
$total_user = 0;
if ($count_result && pg_num_rows($count_result) > 0) {
    $count_row = pg_fetch_assoc($count_result);
    $total_user = (int)$count_row['total'];
}

// Hitung user dengan role admin
$admin_query = "SELECT COUNT(*) as total FROM users WHERE role = 'admin'";
$admin_result = pg_query($koneksi, $admin_query);
$total_admin = 0;
if ($admin_result && pg_num_rows($admin_result) > 0) {
    $admin_row = pg_fetch_assoc($admin_result);
    $total_admin = (int)$admin_row['total'];
}

// Ambil data users (hanya admin)
$users = [];
$query = "SELECT id, username, role, created_at, updated_at FROM users WHERE role = 'admin'";
if (!empty($search_query)) {
    $search_escaped = pg_escape_string($koneksi, $search_query);
    $query .= " AND (username ILIKE '%$search_escaped%' OR role ILIKE '%$search_escaped%')";
}
$query .= " ORDER BY id DESC";
$result = pg_query($koneksi, $query);
if ($result && pg_num_rows($result) > 0) {
    while ($row = pg_fetch_assoc($result)) {
        $users[] = $row;
    }
}
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
                    <h2 class="mb-15 wow fadeInUp" data-wow-delay=".2s">Kelola User</h2>
                    <p class="wow fadeInUp" data-wow-delay=".4s">Kelola data akun admin</p>
                </div>
            </div>
        </div>

        <?php tampilkan_pesan(); ?>

        <!-- Stat Cards -->
        <div class="row mb-30">
            <div class="col-lg-6 col-md-6 mb-20">
                <div class="stat-card wow fadeInUp" data-wow-delay=".2s">
                    <div class="stat-card-icon stat-card-icon-primary">
                        <i class="lni lni-user"></i>
                    </div>
                    <div class="stat-card-content">
                        <h3 class="stat-card-number"><?php echo $total_user; ?></h3>
                        <p class="stat-card-label">Total Admin</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-6 mb-20">
                <div class="stat-card wow fadeInUp" data-wow-delay=".3s">
                    <div class="stat-card-icon stat-card-icon-danger">
                        <i class="lni lni-shield"></i>
                    </div>
                    <div class="stat-card-content">
                        <h3 class="stat-card-number"><?php echo $total_admin; ?></h3>
                        <p class="stat-card-label">Admin Aktif</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="row mb-20">
            <div class="col-lg-12">
                <div class="jabatan-search-box wow fadeInUp" data-wow-delay=".2s">
                    <i class="lni lni-search"></i>
                    <input type="text" id="searchUser" class="jabatan-search-input" placeholder="Cari admin berdasarkan username..." value="<?php echo htmlspecialchars($search_query); ?>">
                    <?php if (!empty($search_query)): ?>
                        <a href="index.php" class="jabatan-search-clear" title="Hapus pencarian">
                            <i class="lni lni-close"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Action Bar -->
        <div class="row mb-30">
            <div class="col-lg-12">
                <a href="tambah.php" class="btn btn-primary btn-lg wow fadeInUp" data-wow-delay=".3s">
                    <i class="lni lni-plus me-2"></i> Tambah User
                </a>
            </div>
        </div>

        <!-- Info Box -->
        <div class="row mb-30">
            <div class="col-lg-12">
                <div class="info-box wow fadeInUp" data-wow-delay=".3s">
                    <div class="info-box-icon">
                        <i class="lni lni-information"></i>
                    </div>
                    <div class="info-box-content">
                        <h4 class="info-box-title">Tips Mengelola User</h4>
                        <p class="info-box-text">
                            Halaman ini hanya dapat diakses oleh ADMIN. Anda dapat mengelola semua akun admin di sini.
                            <?php if ($total_user > 0): ?>
                                <br><strong>Total: <?php echo $total_user; ?> admin</strong>
                            <?php endif; ?>
                            <br><small class="text-muted">Anda tidak dapat menghapus user yang sedang aktif (user yang sedang login). Pastikan untuk logout terlebih dahulu jika ingin menghapus akun sendiri.</small>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="table-responsive wow fadeInUp" data-wow-delay=".3s">
                    <table class="table table-striped table-hover admin-table">
                        <thead>
                            <tr>
                                <th>NO</th>
                                <th>Username</th>
                                <th>Tanggal Dibuat</th>
                                <th>Terakhir Diupdate</th>
                                <th style="text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($users) > 0): ?>
                                <?php $no = 1; foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                    </td>
                                    <td><?php echo $user['created_at'] ? date('d/m/Y H:i', strtotime($user['created_at'])) : '-'; ?></td>
                                    <td><?php echo $user['updated_at'] ? date('d/m/Y H:i', strtotime($user['updated_at'])) : '-'; ?></td>
                                    <td style="text-align: center;">
                                        <div class="btn-group" role="group">
                                            <a href="edit.php?id=<?php echo $user['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="lni lni-pencil me-1"></i> Edit
                                            </a>
                                            <?php 
                                            $current_user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
                                            if ($user['id'] != $current_user_id): 
                                            ?>
                                            <button type="button" class="btn btn-danger btn-sm" onclick="confirmDeleteUser(<?php echo (int)$user['id']; ?>, '<?php echo htmlspecialchars(addslashes($user['username'])); ?>')">
                                                <i class="lni lni-trash me-1"></i> Hapus
                                            </button>
                                            <?php else: ?>
                                            <span class="badge bg-secondary">User Aktif</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">
                                        <div class="empty-state wow fadeInUp" data-wow-delay=".3s">
                                            <i class="lni lni-user empty-state-icon"></i>
                                            <h3 class="empty-state-title">Belum Ada User</h3>
                                            <p class="empty-state-message">Mulai dengan menambahkan user pertama ke dalam sistem.</p>
                                            <a href="tambah.php" class="empty-state-action">
                                                <i class="lni lni-plus"></i> Tambah User Pertama
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
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
<script src="../../assets/js/classes/AnggotaConfirmModal.js"></script>

<script>
// Search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchUser');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            searchTimeout = setTimeout(function() {
                if (query.length > 0 || window.location.search.includes('search=')) {
                    window.location.href = 'index.php' + (query.length > 0 ? '?search=' + encodeURIComponent(query) : '');
                }
            }, 500);
        });
    }
    
    // Initialize toast notifications
    if (typeof ToastNotification !== 'undefined') {
        window.toast = new ToastNotification();
    }
    
    // Initialize loading state
    if (typeof LoadingState !== 'undefined') {
        window.loading = new LoadingState();
    }
    
    // Initialize confirm modal
    if (typeof AnggotaConfirmModal !== 'undefined') {
        window.userConfirmModal = new AnggotaConfirmModal();
    }
    
    // Convert alert messages to toast
    const alertMessages = document.querySelectorAll('.alert');
    alertMessages.forEach(function(alert) {
        const message = alert.textContent.trim();
        const type = alert.classList.contains('alert-success') ? 'success' :
                     alert.classList.contains('alert-danger') ? 'error' :
                     alert.classList.contains('alert-warning') ? 'warning' : 'info';
        
        if (window.toast && message) {
            window.toast.show(message, type);
        }
        
        // Hide original alert
        setTimeout(() => {
            alert.style.display = 'none';
        }, 100);
    });
});

// Function to confirm delete with modal
function confirmDeleteUser(id, username) {
    if (window.userConfirmModal) {
        window.userConfirmModal.confirmDelete('User "' + username + '"', function() {
            // Show loading
            if (window.loading) {
                window.loading.show('Menghapus user...');
            }
            
            // Delete via fetch
            fetch('hapus.php?id=' + id, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (window.loading) {
                    window.loading.hide();
                }
                
                if (data.success) {
                    // Show success toast
                    if (window.toast) {
                        window.toast.success(data.message || 'User berhasil dihapus!', 'Berhasil');
                    }
                    
                    // Reload page after short delay
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    if (window.toast) {
                        window.toast.error(data.message || 'Gagal menghapus user. Silakan coba lagi.', 'Error');
                    }
                }
            })
            .catch(error => {
                if (window.loading) {
                    window.loading.hide();
                }
                if (window.toast) {
                    window.toast.error('Terjadi kesalahan. Silakan coba lagi.', 'Error');
                }
                console.error('Error:', error);
            });
        });
    } else {
        // Fallback to default confirm
        if (confirm('Apakah Anda yakin ingin menghapus user "' + username + '"? Tindakan ini tidak dapat dibatalkan.')) {
            window.location.href = 'hapus.php?id=' + id;
        }
    }
}
</script>

<?php include __DIR__ . '/../../template/admin_footer.php'; ?>

