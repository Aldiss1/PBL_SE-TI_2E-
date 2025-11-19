<?php
$page_title = "Kelola Blog - Admin";
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../auth/cek_login.php';
require_once __DIR__ . '/../../config/anti_injection.php';
require_once __DIR__ . '/../../fungsi/pesan_kilat.php';
require_once __DIR__ . '/../../fungsi/blog.php';

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
        'active' => true
    ],
    'profil' => [ 
        'title' => 'Kelola Profil', 
        'icon'  => 'lni lni-cog', 
        'link'  => '../../module/profil/index.php',
        'active' => false
    ]
];

// Ambil action dari URL
$action = isset($_GET['action']) ? clean_input($_GET['action']) : 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Proses form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $post_action = isset($_POST['action']) ? clean_input($_POST['action']) : '';
    
    if ($post_action === 'tambah') {
        $data = [
            'title' => clean_input($_POST['title']),
            'content' => $_POST['content'], // Jangan clean untuk content
            'excerpt' => clean_input($_POST['excerpt']),
            'category' => clean_input($_POST['category']),
            'tags' => clean_input($_POST['tags']),
            'featured_image' => clean_input($_POST['featured_image']),
            'is_published' => isset($_POST['is_published']) ? '1' : '0'
        ];
        
        $result = tambahBlog($data);
        if ($result['success']) {
            pesan_kilat($result['message'], 'success');
            header('Location: index.php');
            exit;
        } else {
            pesan_kilat($result['message'], 'error');
        }
    } elseif ($post_action === 'edit') {
        $post_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $data = [
            'title' => clean_input($_POST['title']),
            'content' => $_POST['content'], // Jangan clean untuk content
            'excerpt' => clean_input($_POST['excerpt']),
            'category' => clean_input($_POST['category']),
            'tags' => clean_input($_POST['tags']),
            'featured_image' => clean_input($_POST['featured_image']),
            'is_published' => isset($_POST['is_published']) ? '1' : '0'
        ];
        
        $result = updateBlog($post_id, $data);
        if ($result['success']) {
            pesan_kilat($result['message'], 'success');
            header('Location: index.php');
            exit;
        } else {
            pesan_kilat($result['message'], 'error');
        }
    }
}

// Proses hapus
if ($action === 'hapus' && $id > 0) {
    $result = hapusBlog($id);
    if ($result['success']) {
        pesan_kilat($result['message'], 'success');
    } else {
        pesan_kilat($result['message'], 'error');
    }
    header('Location: index.php');
    exit;
}

// Ambil data blog untuk edit
$blog = null;
if ($action === 'edit' && $id > 0) {
    $blog = getBlogById($id);
    if (!$blog) {
        pesan_kilat('Blog tidak ditemukan!', 'error');
        header('Location: index.php');
        exit;
    }
}

// Ambil semua blog untuk list
$blogs = getAllBlogs();

include __DIR__ . '/../../template/admin_header.php';
?>

<!-- Sidebar -->
<?php include __DIR__ . '/../../template/admin_sidebar.php'; ?>

<!-- Main Content -->
<main class="admin-main-content">
<section class="pt-50 pb-50" style="background: #f8f9fa; padding-top: 30px !important;">
    <div class="container">
        <?php if ($action === 'list'): ?>
            <!-- List Blog -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="section-title mb-50">
                        <h2 class="mb-15 wow fadeInUp" data-wow-delay=".2s">Kelola Blog</h2>
                        <p class="wow fadeInUp" data-wow-delay=".4s">Kelola artikel blog laboratorium. Blog dengan status <strong>Published</strong> akan tampil di landing page (index.php), sedangkan blog dengan status <strong>Draft</strong> hanya dapat dilihat melalui admin panel.</p>
                    </div>
                </div>
            </div>

            <?php tampilkan_pesan(); ?>

            <div class="row mb-30">
                <div class="col-lg-12">
                    <a href="?action=tambah" class="button button-sm radius-10 wow fadeInUp" data-wow-delay=".2s">
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
                                    <th style="padding: 15px;">NO</th>
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
                                    <?php $no = 1; foreach ($blogs as $blog_item): ?>
                                    <tr>
                                        <td style="padding: 15px;"><?php echo $no++; ?></td>
                                        <td style="padding: 15px;">
                                            <strong><?php echo htmlspecialchars($blog_item['title']); ?></strong>
                                            <?php if ($blog_item['excerpt']): ?>
                                                <br><small class="text-muted"><?php echo htmlspecialchars(substr($blog_item['excerpt'], 0, 50)); ?>...</small>
                                            <?php endif; ?>
                                        </td>
                                        <td style="padding: 15px;">
                                            <?php echo $blog_item['category'] ? htmlspecialchars($blog_item['category']) : '-'; ?>
                                        </td>
                                        <td style="padding: 15px;">
                                            <?php if ($blog_item['is_published']): ?>
                                                <span class="badge" style="background: #27AE60; color: white; padding: 5px 10px; border-radius: 5px;">Published</span>
                                            <?php else: ?>
                                                <span class="badge" style="background: #F2994A; color: white; padding: 5px 10px; border-radius: 5px;">Draft</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="padding: 15px;">
                                            <?php echo date('d/m/Y', strtotime($blog_item['created_at'])); ?>
                                        </td>
                                        <td style="padding: 15px;">
                                            <?php echo (int)$blog_item['views']; ?>
                                        </td>
                                        <td style="padding: 15px; text-align: center;">
                                            <?php if ($blog_item['is_published']): ?>
                                            <a href="../../pages/blog/blog-detail.php?id=<?php echo $blog_item['id']; ?>" target="_blank" class="button button-sm radius-10" style="background: #27AE60; color: white; padding: 5px 15px; margin-right: 5px;" title="Lihat di Website">
                                                <i class="lni lni-eye"></i> Preview
                                            </a>
                                            <?php endif; ?>
                                            <a href="?action=edit&id=<?php echo $blog_item['id']; ?>" class="button button-sm radius-10" style="background: #2F80ED; color: white; padding: 5px 15px; margin-right: 5px;">
                                                <i class="lni lni-pencil"></i> Edit
                                            </a>
                                            <a href="?action=hapus&id=<?php echo $blog_item['id']; ?>" class="button button-sm radius-10" style="background: #e74c3c; color: white; padding: 5px 15px;" onclick="return confirm('Yakin ingin menghapus blog ini?')">
                                                <i class="lni lni-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center" style="padding: 40px;">
                                            <i class="lni lni-inbox" style="font-size: 48px; color: #dee2e6;"></i>
                                            <p class="mt-3 text-muted">Belum ada data blog. <a href="?action=tambah">Tambah blog baru</a></p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <?php elseif ($action === 'tambah'): ?>
            <!-- Form Tambah Blog -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="section-title mb-50">
                        <h2 class="mb-15 wow fadeInUp" data-wow-delay=".2s">Tambah Blog Baru</h2>
                        <p class="wow fadeInUp" data-wow-delay=".4s">Buat artikel blog baru untuk laboratorium</p>
                    </div>
                </div>
            </div>

            <?php tampilkan_pesan(); ?>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card wow fadeInUp" data-wow-delay=".3s" style="background: white; border-radius: 15px; padding: 30px; box-shadow: 0 5px 20px rgba(0,0,0,0.08);">
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="tambah">
                            <div class="row mb-3">
                                <div class="col-lg-12">
                                    <label for="title" class="form-label" style="font-weight: 600; color: #2c3e50;">Judul Blog *</label>
                                    <input type="text" class="form-control" id="title" name="title" required style="padding: 12px; border-radius: 5px; border: 1px solid #dee2e6;">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-lg-6">
                                    <label for="category" class="form-label" style="font-weight: 600; color: #2c3e50;">Kategori</label>
                                    <input type="text" class="form-control" id="category" name="category" placeholder="Contoh: Teknologi, Research, dll" style="padding: 12px; border-radius: 5px; border: 1px solid #dee2e6;">
                                </div>
                                <div class="col-lg-6">
                                    <label for="tags" class="form-label" style="font-weight: 600; color: #2c3e50;">Tags</label>
                                    <input type="text" class="form-control" id="tags" name="tags" placeholder="Pisahkan dengan koma (contoh: php, javascript, web)" style="padding: 12px; border-radius: 5px; border: 1px solid #dee2e6;">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-lg-12">
                                    <label for="featured_image" class="form-label" style="font-weight: 600; color: #2c3e50;">URL Gambar Featured</label>
                                    <input type="text" class="form-control" id="featured_image" name="featured_image" placeholder="Contoh: assets/img/blog/blog-1.jpg" style="padding: 12px; border-radius: 5px; border: 1px solid #dee2e6;">
                                    <small class="form-text text-muted">Masukkan path/URL gambar featured untuk blog ini</small>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-lg-12">
                                    <label for="excerpt" class="form-label" style="font-weight: 600; color: #2c3e50;">Excerpt / Ringkasan</label>
                                    <textarea class="form-control" id="excerpt" name="excerpt" rows="3" placeholder="Ringkasan singkat tentang artikel ini..." style="padding: 12px; border-radius: 5px; border: 1px solid #dee2e6;"></textarea>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-lg-12">
                                    <label for="content" class="form-label" style="font-weight: 600; color: #2c3e50;">Konten Blog *</label>
                                    <textarea class="form-control" id="content" name="content" rows="15" required style="padding: 12px; border-radius: 5px; border: 1px solid #dee2e6; font-family: inherit;"></textarea>
                                    <small class="form-text text-muted">Gunakan baris baru untuk paragraf baru</small>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-lg-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_published" name="is_published" value="1">
                                        <label class="form-check-label" for="is_published" style="font-weight: 600; color: #2c3e50;">
                                            Publikasikan sekarang
                                        </label>
                                        <small class="form-text text-muted d-block" style="margin-top: 5px;">
                                            <i class="lni lni-info"></i> Blog yang dipublikasikan akan tampil di landing page (index.php). Blog dengan status Draft hanya dapat dilihat melalui admin panel.
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-12">
                                    <button type="submit" class="button radius-10" style="background: #2F80ED; color: white; padding: 12px 30px;">
                                        <i class="lni lni-save"></i> Simpan Blog
                                    </button>
                                    <a href="index.php" class="button radius-10 border-button" style="padding: 12px 30px; margin-left: 10px;">
                                        <i class="lni lni-arrow-left"></i> Kembali
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        <?php elseif ($action === 'edit' && $blog): ?>
            <!-- Form Edit Blog -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="section-title mb-50">
                        <h2 class="mb-15 wow fadeInUp" data-wow-delay=".2s">Edit Blog</h2>
                        <p class="wow fadeInUp" data-wow-delay=".4s">Edit artikel blog</p>
                    </div>
                </div>
            </div>

            <?php tampilkan_pesan(); ?>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card wow fadeInUp" data-wow-delay=".3s" style="background: white; border-radius: 15px; padding: 30px; box-shadow: 0 5px 20px rgba(0,0,0,0.08);">
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="id" value="<?php echo $blog['id']; ?>">
                            <div class="row mb-3">
                                <div class="col-lg-12">
                                    <label for="title" class="form-label" style="font-weight: 600; color: #2c3e50;">Judul Blog *</label>
                                    <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($blog['title']); ?>" required style="padding: 12px; border-radius: 5px; border: 1px solid #dee2e6;">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-lg-6">
                                    <label for="category" class="form-label" style="font-weight: 600; color: #2c3e50;">Kategori</label>
                                    <input type="text" class="form-control" id="category" name="category" value="<?php echo htmlspecialchars($blog['category']); ?>" placeholder="Contoh: Teknologi, Research, dll" style="padding: 12px; border-radius: 5px; border: 1px solid #dee2e6;">
                                </div>
                                <div class="col-lg-6">
                                    <label for="tags" class="form-label" style="font-weight: 600; color: #2c3e50;">Tags</label>
                                    <input type="text" class="form-control" id="tags" name="tags" value="<?php echo htmlspecialchars($blog['tags_string']); ?>" placeholder="Pisahkan dengan koma (contoh: php, javascript, web)" style="padding: 12px; border-radius: 5px; border: 1px solid #dee2e6;">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-lg-12">
                                    <label for="featured_image" class="form-label" style="font-weight: 600; color: #2c3e50;">URL Gambar Featured</label>
                                    <input type="text" class="form-control" id="featured_image" name="featured_image" value="<?php echo htmlspecialchars($blog['featured_image']); ?>" placeholder="Contoh: assets/img/blog/blog-1.jpg" style="padding: 12px; border-radius: 5px; border: 1px solid #dee2e6;">
                                    <small class="form-text text-muted">Masukkan path/URL gambar featured untuk blog ini</small>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-lg-12">
                                    <label for="excerpt" class="form-label" style="font-weight: 600; color: #2c3e50;">Excerpt / Ringkasan</label>
                                    <textarea class="form-control" id="excerpt" name="excerpt" rows="3" placeholder="Ringkasan singkat tentang artikel ini..." style="padding: 12px; border-radius: 5px; border: 1px solid #dee2e6;"><?php echo htmlspecialchars($blog['excerpt']); ?></textarea>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-lg-12">
                                    <label for="content" class="form-label" style="font-weight: 600; color: #2c3e50;">Konten Blog *</label>
                                    <textarea class="form-control" id="content" name="content" rows="15" required style="padding: 12px; border-radius: 5px; border: 1px solid #dee2e6; font-family: inherit;"><?php echo htmlspecialchars($blog['content']); ?></textarea>
                                    <small class="form-text text-muted">Gunakan baris baru untuk paragraf baru</small>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-lg-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_published" name="is_published" value="1" <?php echo $blog['is_published'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="is_published" style="font-weight: 600; color: #2c3e50;">
                                            Publikasikan sekarang
                                        </label>
                                        <small class="form-text text-muted d-block" style="margin-top: 5px;">
                                            <i class="lni lni-info"></i> Blog yang dipublikasikan akan tampil di landing page (index.php). Blog dengan status Draft hanya dapat dilihat melalui admin panel.
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-12">
                                    <button type="submit" class="button radius-10" style="background: #2F80ED; color: white; padding: 12px 30px;">
                                        <i class="lni lni-save"></i> Update Blog
                                    </button>
                                    <a href="index.php" class="button radius-10 border-button" style="padding: 12px 30px; margin-left: 10px;">
                                        <i class="lni lni-arrow-left"></i> Kembali
                                    </a>
                                    <?php if ($blog['is_published']): ?>
                                    <a href="../../pages/blog/blog-detail.php?id=<?php echo $blog['id']; ?>" target="_blank" class="button radius-10" style="background: #27AE60; color: white; padding: 12px 30px; margin-left: 10px;">
                                        <i class="lni lni-eye"></i> Preview
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
</main>

<?php include __DIR__ . '/../../template/admin_footer.php'; ?>
