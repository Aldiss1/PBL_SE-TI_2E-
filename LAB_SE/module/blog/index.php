<?php
$page_title = "Kelola Blog - Admin";
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../cek_login.php';
require_once __DIR__ . '/../../config/anti_injection.php';
require_once __DIR__ . '/../../fungsi/pesan_kilat.php';

// Set active menu
$current_page = 'blog';
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
        'active' => true
    ]
];

include __DIR__ . '/../../template/admin_header.php';

// Ambil data blog
$blogs = [];
$query = "SELECT b.*, u.username as author_name 
          FROM blog b 
          LEFT JOIN users u ON b.author_id = u.id 
          ORDER BY b.created_at DESC";
$result = pg_query($koneksi, $query);
if ($result && pg_num_rows($result) > 0) {
    while ($row = pg_fetch_assoc($result)) {
        $blogs[] = $row;
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
                    <h2 class="mb-15 wow fadeInUp" data-wow-delay=".2s">Kelola Blog</h2>
                    <p class="wow fadeInUp" data-wow-delay=".4s">Kelola artikel blog laboratorium</p>
                </div>
            </div>
        </div>

        <?php tampilkan_pesan(); ?>

        <div class="row mb-30">
            <div class="col-lg-12">
                <a href="tambah.php" class="button button-sm radius-10 wow fadeInUp" data-wow-delay=".2s">
                    <i class="lni lni-plus"></i> Tambah Blog Baru
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="table-responsive wow fadeInUp" data-wow-delay=".3s">
                    <table class="table table-striped table-hover" style="background: white; border-radius: 10px; overflow: hidden;">
                        <thead style="background: #2c3e50; color: white;">
                            <tr>
                                <th style="padding: 15px;">ID</th>
                                <th style="padding: 15px;">Judul</th>
                                <th style="padding: 15px;">Kategori</th>
                                <th style="padding: 15px;">Status</th>
                                <th style="padding: 15px;">Tanggal</th>
                                <th style="padding: 15px;">Views</th>
                                <th style="padding: 15px; text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($blogs) > 0): ?>
                                <?php foreach ($blogs as $blog): ?>
                                <tr>
                                    <td style="padding: 15px;"><?php echo $blog['id']; ?></td>
                                    <td style="padding: 15px;">
                                        <strong><?php echo htmlspecialchars($blog['title']); ?></strong>
                                        <?php if ($blog['excerpt']): ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars(substr($blog['excerpt'], 0, 50)); ?>...</small>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 15px;">
                                        <?php echo $blog['category'] ? htmlspecialchars($blog['category']) : '-'; ?>
                                    </td>
                                    <td style="padding: 15px;">
                                        <?php if ($blog['is_published']): ?>
                                            <span class="badge" style="background: #27AE60; color: white; padding: 5px 10px; border-radius: 5px;">Published</span>
                                        <?php else: ?>
                                            <span class="badge" style="background: #F2994A; color: white; padding: 5px 10px; border-radius: 5px;">Draft</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 15px;">
                                        <?php echo date('d/m/Y', strtotime($blog['created_at'])); ?>
                                    </td>
                                    <td style="padding: 15px;">
                                        <?php echo (int)$blog['views']; ?>
                                    </td>
                                    <td style="padding: 15px; text-align: center;">
                                        <a href="edit.php?id=<?php echo $blog['id']; ?>" class="button button-sm radius-10" style="background: #2F80ED; color: white; padding: 5px 15px; margin-right: 5px;">
                                            <i class="lni lni-pencil"></i> Edit
                                        </a>
                                        <a href="hapus.php?id=<?php echo $blog['id']; ?>" class="button button-sm radius-10" style="background: #e74c3c; color: white; padding: 5px 15px;" onclick="return confirm('Yakin ingin menghapus blog ini?')">
                                            <i class="lni lni-trash"></i> Hapus
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center" style="padding: 40px;">
                                        <i class="lni lni-inbox" style="font-size: 48px; color: #dee2e6;"></i>
                                        <p class="mt-3 text-muted">Belum ada data blog. <a href="tambah.php">Tambah blog baru</a></p>
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

<?php include __DIR__ . '/../../template/admin_footer.php'; ?>

