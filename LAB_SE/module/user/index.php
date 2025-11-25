<?php
$page_title = "Kelola User - Admin";
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
    ]
];

include __DIR__ . '/../../template/admin_header.php';

// Ambil data users
$users = [];
$query = "SELECT id, username, role, created_at, updated_at FROM users ORDER BY id DESC";
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
                    <p class="wow fadeInUp" data-wow-delay=".4s">Kelola data user (admin dan mahasiswa)</p>
                </div>
            </div>
        </div>

        <?php tampilkan_pesan(); ?>

        <div class="row mb-30">
            <div class="col-lg-12">
                <a href="tambah.php" class="button button-sm radius-10 wow fadeInUp" data-wow-delay=".2s">
                    <i class="lni lni-plus"></i> Tambah User
                </a>
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
                                <th>Role</th>
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
                                    <td>
                                        <?php 
                                        $role_badge = '';
                                        $role_text = htmlspecialchars($user['role']);
                                        if ($role_text === 'admin') {
                                            $role_badge = '<span class="role-badge role-badge-admin">Admin</span>';
                                        } elseif ($role_text === 'mahasiswa') {
                                            $role_badge = '<span class="role-badge role-badge-mahasiswa">Mahasiswa</span>';
                                        } else {
                                            $role_badge = '<span class="role-badge role-badge-default">' . $role_text . '</span>';
                                        }
                                        echo $role_badge;
                                        ?>
                                    </td>
                                    <td><?php echo $user['created_at'] ? date('d/m/Y H:i', strtotime($user['created_at'])) : '-'; ?></td>
                                    <td><?php echo $user['updated_at'] ? date('d/m/Y H:i', strtotime($user['updated_at'])) : '-'; ?></td>
                                    <td style="text-align: center;">
                                        <a href="edit.php?id=<?php echo $user['id']; ?>" class="button button-sm radius-10 btn-edit">
                                            <i class="lni lni-pencil"></i> Edit
                                        </a>
                                        <?php 
                                        $current_user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
                                        if ($user['id'] != $current_user_id): 
                                        ?>
                                        <a href="hapus.php?id=<?php echo $user['id']; ?>" class="button button-sm radius-10 btn-delete" data-delete-url="hapus.php?id=<?php echo $user['id']; ?>">
                                            <i class="lni lni-trash"></i> Hapus
                                        </a>
                                        <?php else: ?>
                                        <span class="text-muted-small">(User aktif)</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center admin-table-empty">
                                        <i class="lni lni-inbox admin-table-empty-icon"></i>
                                        <p class="mt-3 text-muted">Belum ada data user. <a href="tambah.php">Tambah user</a></p>
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

<!-- Custom Confirm Modal -->
<div id="confirmDeleteOverlay">
    <div class="confirm-modal">
        <div class="confirm-modal-header">
            <i class="lni lni-trash"></i>
            <h4>Hapus User?</h4>
        </div>
        <div class="confirm-modal-body">
            Apakah Anda yakin ingin menghapus user ini? Tindakan ini tidak dapat dibatalkan.
        </div>
        <div class="confirm-modal-footer">
            <button id="btnCancelDelete" class="button radius-10 border-button">Batal</button>
            <button id="btnConfirmDelete" class="button radius-10 btn-delete">Hapus</button>
        </div>
    </div>
</div>

<script src="../../assets/js/classes/ConfirmModal.js"></script>
<script>
// Initialize ConfirmModal for user deletion
if (typeof ConfirmModal !== 'undefined') {
    window.userConfirmModal = new ConfirmModal({
        overlayId: 'confirmDeleteOverlay',
        confirmButtonId: 'btnConfirmDelete',
        cancelButtonId: 'btnCancelDelete',
        deleteButtonSelector: '.btn-delete'
    });
}
</script>

<?php include __DIR__ . '/../../template/admin_footer.php'; ?>

