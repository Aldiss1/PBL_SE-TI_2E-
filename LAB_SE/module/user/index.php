<?php
$page_title = "Kelola User - Admin";
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
<section class="pt-50 pb-50" style="background: #f8f9fa; padding-top: 30px !important;">
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
                    <table class="table table-striped table-hover" style="background: white; border-radius: 10px; overflow: hidden;">
                        <thead style="background: #2c3e50; color: white;">
                            <tr>
                                <th style="padding: 15px;">NO</th>
                                <th style="padding: 15px;">Username</th>
                                <th style="padding: 15px;">Role</th>
                                <th style="padding: 15px;">Tanggal Dibuat</th>
                                <th style="padding: 15px;">Terakhir Diupdate</th>
                                <th style="padding: 15px; text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($users) > 0): ?>
                                <?php $no = 1; foreach ($users as $user): ?>
                                <tr>
                                    <td style="padding: 15px;"><?php echo $no++; ?></td>
                                    <td style="padding: 15px;">
                                        <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                    </td>
                                    <td style="padding: 15px;">
                                        <?php 
                                        $role_badge = '';
                                        $role_text = htmlspecialchars($user['role']);
                                        if ($role_text === 'admin') {
                                            $role_badge = '<span style="background: #e74c3c; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">Admin</span>';
                                        } elseif ($role_text === 'mahasiswa') {
                                            $role_badge = '<span style="background: #2F80ED; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">Mahasiswa</span>';
                                        } else {
                                            $role_badge = '<span style="background: #95a5a6; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">' . $role_text . '</span>';
                                        }
                                        echo $role_badge;
                                        ?>
                                    </td>
                                    <td style="padding: 15px;"><?php echo $user['created_at'] ? date('d/m/Y H:i', strtotime($user['created_at'])) : '-'; ?></td>
                                    <td style="padding: 15px;"><?php echo $user['updated_at'] ? date('d/m/Y H:i', strtotime($user['updated_at'])) : '-'; ?></td>
                                    <td style="padding: 15px; text-align: center;">
                                        <a href="edit.php?id=<?php echo $user['id']; ?>" class="button button-sm radius-10" style="background: #2F80ED; color: white; padding: 5px 15px; margin-right: 5px;">
                                            <i class="lni lni-pencil"></i> Edit
                                        </a>
                                        <?php 
                                        $current_user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
                                        if ($user['id'] != $current_user_id): 
                                        ?>
                                        <a href="hapus.php?id=<?php echo $user['id']; ?>" class="button button-sm radius-10 btn-delete" data-delete-url="hapus.php?id=<?php echo $user['id']; ?>" style="background: #e74c3c; color: white; padding: 5px 15px;">
                                            <i class="lni lni-trash"></i> Hapus
                                        </a>
                                        <?php else: ?>
                                        <span style="color: #95a5a6; font-size: 12px;">(User aktif)</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center" style="padding: 40px;">
                                        <i class="lni lni-inbox" style="font-size: 48px; color: #dee2e6;"></i>
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
<div id="confirmDeleteOverlay" style="display:none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1100; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #fff; border-radius: 12px; max-width: 420px; width: 100%; box-shadow: 0 10px 30px rgba(0,0,0,0.2); overflow: hidden;">
        <div style="padding: 20px 24px; border-bottom: 1px solid #f1f1f1; display: flex; align-items: center; gap: 10px;">
            <i class="lni lni-trash" style="color: #e74c3c; font-size: 22px;"></i>
            <h4 style="margin: 0; color: #2c3e50; font-weight: 600;">Hapus User?</h4>
        </div>
        <div style="padding: 20px 24px; color: #6c757d;">
            Apakah Anda yakin ingin menghapus user ini? Tindakan ini tidak dapat dibatalkan.
        </div>
        <div style="padding: 16px 24px; display: flex; justify-content: flex-end; gap: 10px; background: #fafafa;">
            <button id="btnCancelDelete" class="button radius-10 border-button" style="padding: 10px 18px;">Batal</button>
            <button id="btnConfirmDelete" class="button radius-10" style="background: #e74c3c; color: #fff; padding: 10px 18px;">Hapus</button>
        </div>
    </div>
</div>

<script>
(function() {
    let pendingDeleteUrl = null;
    const overlay = document.getElementById('confirmDeleteOverlay');
    const btnConfirm = document.getElementById('btnConfirmDelete');
    const btnCancel = document.getElementById('btnCancelDelete');

    function openConfirm(url) {
        pendingDeleteUrl = url;
        overlay.style.display = 'flex';
    }
    function closeConfirm() {
        pendingDeleteUrl = null;
        overlay.style.display = 'none';
    }

    document.querySelectorAll('.btn-delete').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.getAttribute('data-delete-url') || this.getAttribute('href');
            openConfirm(url);
        });
    });

    btnCancel.addEventListener('click', function() { closeConfirm(); });
    btnConfirm.addEventListener('click', function() {
        if (pendingDeleteUrl) {
            window.location.href = pendingDeleteUrl;
        }
    });

    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) { closeConfirm(); }
    });
})();
</script>

<?php include __DIR__ . '/../../template/admin_footer.php'; ?>

