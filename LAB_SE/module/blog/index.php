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

// Ambil action dari URL
$action = isset($_GET['action']) ? clean_input($_GET['action']) : 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Proses form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $post_action = isset($_POST['action']) ? clean_input($_POST['action']) : '';
    
    // Handle file upload untuk featured_image
    $featured_image = '';
    
    // Jika ada file yang diupload
    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['featured_image'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if (in_array($ext, $allowed) && $file['size'] <= $max_size) {
            $upload_dir = __DIR__ . '/../../assets/img/blog/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $nama_file_baru = uniqid('blog_', true) . '.' . $ext;
            $server_file = $upload_dir . $nama_file_baru;
            
            if (move_uploaded_file($file['tmp_name'], $server_file)) {
                // Simpan path relatif dari root website (bukan relatif dari module/blog/)
                $featured_image = 'assets/img/blog/' . $nama_file_baru;
            }
        }
    } 
    // Jika tidak ada file baru dan ada featured_image_old (untuk edit yang tidak ganti gambar)
    elseif (isset($_POST['featured_image_old']) && !empty($_POST['featured_image_old'])) {
        $featured_image = clean_input($_POST['featured_image_old']);
    }
    // Jika ada input featured_image langsung (untuk tambah atau fallback)
    elseif (isset($_POST['featured_image']) && !empty($_POST['featured_image'])) {
        $featured_image = clean_input($_POST['featured_image']);
    }
    
    if ($post_action === 'tambah') {
        $data = [
            'title' => clean_input($_POST['title']),
            'content' => $_POST['content'], // Jangan clean untuk content
            'excerpt' => clean_input($_POST['excerpt']),
            'category' => clean_input($_POST['category']),
            'tags' => clean_input($_POST['tags']),
            'featured_image' => $featured_image,
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
            'featured_image' => $featured_image,
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

// Hitung statistik blog
$total_blog_query = "SELECT COUNT(*) as total FROM blog";
$total_blog_result = pg_query($koneksi, $total_blog_query);
$total_blog = 0;
if ($total_blog_result && pg_num_rows($total_blog_result) > 0) {
    $total_blog_row = pg_fetch_assoc($total_blog_result);
    $total_blog = (int)$total_blog_row['total'];
}

// Hitung blog published
$blog_published_query = "SELECT COUNT(*) as total FROM blog WHERE is_published = true";
$blog_published_result = pg_query($koneksi, $blog_published_query);
$blog_published = 0;
if ($blog_published_result && pg_num_rows($blog_published_result) > 0) {
    $blog_published_row = pg_fetch_assoc($blog_published_result);
    $blog_published = (int)$blog_published_row['total'];
}

// Hitung blog draft
$blog_draft = $total_blog - $blog_published;
if ($blog_draft < 0) $blog_draft = 0;

// Pencarian
$search_query = isset($_GET['search']) ? trim(clean_input($_GET['search'])) : '';

// Ambil semua blog untuk list
$blogs = getAllBlogs();

// Filter berdasarkan search jika ada
if (!empty($search_query)) {
    $search_escaped = pg_escape_string($koneksi, $search_query);
    $blogs_filtered = [];
    foreach ($blogs as $blog_item) {
        if (stripos($blog_item['title'], $search_query) !== false || 
            stripos($blog_item['category'] ?? '', $search_query) !== false ||
            stripos($blog_item['excerpt'] ?? '', $search_query) !== false) {
            $blogs_filtered[] = $blog_item;
        }
    }
    $blogs = $blogs_filtered;
}

include __DIR__ . '/../../template/admin_header.php';
?>

<!-- Sidebar -->
<?php include __DIR__ . '/../../template/admin_sidebar.php'; ?>

<!-- Main Content -->
<main class="admin-main-content">
<section class="pt-50 pb-50 admin-section-bg">
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

            <!-- Stat Cards -->
            <div class="row mb-30">
                <div class="col-lg-4 col-md-6 mb-20">
                    <div class="stat-card wow fadeInUp" data-wow-delay=".2s">
                        <div class="stat-card-icon stat-card-icon-primary">
                            <i class="lni lni-text-format"></i>
                        </div>
                        <div class="stat-card-content">
                            <h3 class="stat-card-number"><?php echo $total_blog; ?></h3>
                            <p class="stat-card-label">Total Blog</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-20">
                    <div class="stat-card wow fadeInUp" data-wow-delay=".3s">
                        <div class="stat-card-icon stat-card-icon-success">
                            <i class="lni lni-checkmark-circle"></i>
                        </div>
                        <div class="stat-card-content">
                            <h3 class="stat-card-number"><?php echo $blog_published; ?></h3>
                            <p class="stat-card-label">Blog Published</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-20">
                    <div class="stat-card wow fadeInUp" data-wow-delay=".4s">
                        <div class="stat-card-icon stat-card-icon-info">
                            <i class="lni lni-file"></i>
                        </div>
                        <div class="stat-card-content">
                            <h3 class="stat-card-number"><?php echo $blog_draft; ?></h3>
                            <p class="stat-card-label">Blog Draft</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="row mb-20">
                <div class="col-lg-12">
                    <div class="jabatan-search-box wow fadeInUp" data-wow-delay=".2s">
                        <i class="lni lni-search"></i>
                        <input type="text" id="searchBlog" class="jabatan-search-input" placeholder="Cari blog berdasarkan judul, kategori, atau excerpt..." value="<?php echo htmlspecialchars($search_query); ?>">
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
                    <a href="?action=tambah" class="btn btn-primary btn-lg wow fadeInUp" data-wow-delay=".3s">
                        <i class="lni lni-plus me-2"></i> Tambah Blog
                    </a>
                </div>
            </div>

            <!-- Info Box -->
            <?php if ($total_blog > 0): ?>
            <div class="row mb-30">
                <div class="col-lg-12">
                    <div class="info-box wow fadeInUp" data-wow-delay=".3s">
                        <div class="info-box-icon">
                            <i class="lni lni-information"></i>
                        </div>
                        <div class="info-box-content">
                            <h4 class="info-box-title">Tips Mengelola Blog</h4>
                            <p class="info-box-text">
                                Blog dengan status <strong>Published</strong> akan tampil di landing page (index.php), sedangkan blog dengan status <strong>Draft</strong> hanya dapat dilihat melalui admin panel.
                                <?php if ($blog_draft > 0): ?>
                                    <br><strong>Anda memiliki <?php echo $blog_draft; ?> blog yang masih dalam status Draft.</strong>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-lg-12">
                    <?php if (count($blogs) > 0): ?>
                        <!-- Table View -->
                        <div class="table-responsive wow fadeInUp" data-wow-delay=".3s">
                            <table class="table table-striped table-hover admin-table">
                                <thead>
                                    <tr>
                                        <th>NO</th>
                                        <th>Judul</th>
                                        <th>Kategori</th>
                                        <th>Status</th>
                                        <th>Tanggal</th>
                                        <th>Views</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1; foreach ($blogs as $blog_item): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($blog_item['title']); ?></strong>
                                            <?php if ($blog_item['excerpt']): ?>
                                                <br><small class="text-muted"><?php echo htmlspecialchars(substr($blog_item['excerpt'], 0, 50)); ?>...</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo $blog_item['category'] ? htmlspecialchars($blog_item['category']) : '-'; ?>
                                        </td>
                                        <td>
                                            <?php if ($blog_item['is_published']): ?>
                                                <span class="status-badge status-badge-published">Published</span>
                                            <?php else: ?>
                                                <span class="status-badge status-badge-draft">Draft</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo date('d/m/Y', strtotime($blog_item['created_at'])); ?>
                                        </td>
                                        <td>
                                            <?php echo (int)$blog_item['views']; ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <?php if ($blog_item['is_published']): ?>
                                                <button type="button" class="btn btn-info btn-sm" onclick="window.open('../../pages/blog/blog-detail.php?id=<?php echo $blog_item['id']; ?>', '_blank')" title="Lihat di Website">
                                                    <i class="lni lni-eye me-1"></i> Preview
                                                </button>
                                                <?php endif; ?>
                                                <a href="?action=edit&id=<?php echo $blog_item['id']; ?>" class="btn btn-primary btn-sm">
                                                    <i class="lni lni-pencil me-1"></i> Edit
                                                </a>
                                                <button type="button" class="btn btn-danger btn-sm" onclick="confirmDeleteBlog(<?php echo (int)$blog_item['id']; ?>, '<?php echo htmlspecialchars(addslashes($blog_item['title'])); ?>')">
                                                    <i class="lni lni-trash me-1"></i> Hapus
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <!-- Empty State -->
                        <div class="empty-state wow fadeInUp" data-wow-delay=".3s">
                            <i class="lni lni-text-format empty-state-icon"></i>
                            <h3 class="empty-state-title">Belum Ada Blog</h3>
                            <p class="empty-state-message">Mulai dengan menambahkan blog pertama ke dalam sistem.</p>
                            <a href="?action=tambah" class="empty-state-action">
                                <i class="lni lni-plus"></i> Tambah Blog Pertama
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        <?php elseif ($action === 'tambah'): ?>
            <!-- Form Tambah Blog -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="section-title mb-50">
                        <h2 class="mb-15 wow fadeInUp" data-wow-delay=".2s">Tambah Blog</h2>
                        <p class="wow fadeInUp" data-wow-delay=".4s">Buat artikel blog baru untuk laboratorium</p>
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
                                <i class="lni lni-text-format text-primary"></i> Form Tambah Blog
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="" id="formTambahBlog" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="tambah">
                                
                                <div class="mb-4">
                                    <label for="title" class="form-label form-label-custom">
                                        <i class="lni lni-headphone-alt"></i> Judul Blog <span class="text-danger">*</span>
                                    </label>
                                    <input 
                                        type="text" 
                                        class="form-control form-control-lg form-input-custom" 
                                        id="title" 
                                        name="title" 
                                        placeholder="Masukkan judul artikel blog" 
                                        required
                                        minlength="5"
                                        maxlength="200"
                                        autofocus>
                                    <div class="form-text">
                                        <i class="lni lni-information"></i> Judul yang menarik akan meningkatkan minat pembaca (minimal 5 karakter)
                                    </div>
                                    <div class="invalid-feedback" id="title_error"></div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-lg-6">
                                        <label for="category" class="form-label form-label-custom">
                                            <i class="lni lni-folder"></i> Kategori
                                        </label>
                                        <input 
                                            type="text" 
                                            class="form-control form-control-lg form-input-custom" 
                                            id="category" 
                                            name="category" 
                                            placeholder="Contoh: Teknologi, Research, dll"
                                            maxlength="50">
                                        <div class="form-text">
                                            <i class="lni lni-information"></i> Kategori membantu mengelompokkan artikel
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <label for="tags" class="form-label form-label-custom">
                                            <i class="lni lni-tag"></i> Tags
                                        </label>
                                        <input 
                                            type="text" 
                                            class="form-control form-control-lg form-input-custom" 
                                            id="tags" 
                                            name="tags" 
                                            placeholder="Pisahkan dengan koma (contoh: php, javascript, web)"
                                            maxlength="200">
                                        <div class="form-text">
                                            <i class="lni lni-information"></i> Tags membantu pencarian artikel
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="featured_image" class="form-label form-label-custom">
                                        <i class="lni lni-image"></i> Gambar Featured
                                    </label>
                                    <input 
                                        type="file" 
                                        class="form-control form-control-lg form-input-custom" 
                                        id="featured_image" 
                                        name="featured_image" 
                                        accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                                    <div class="form-text">
                                        <i class="lni lni-information"></i> Upload gambar featured untuk blog ini (JPG, PNG, GIF, WEBP - Maksimal 5MB)
                                    </div>
                                    <div id="imagePreview" class="mt-3" style="display: none;">
                                        <img id="previewImg" src="" alt="Preview" style="max-width: 300px; max-height: 200px; border-radius: 8px; border: 2px solid #e0e0e0;">
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="excerpt" class="form-label form-label-custom">
                                        <i class="lni lni-text-format"></i> Excerpt / Ringkasan
                                    </label>
                                    <textarea 
                                        class="form-control form-input-custom" 
                                        id="excerpt" 
                                        name="excerpt" 
                                        rows="3" 
                                        placeholder="Ringkasan singkat tentang artikel ini..."
                                        maxlength="300"></textarea>
                                    <div class="form-text">
                                        <i class="lni lni-information"></i> Ringkasan akan ditampilkan di halaman blog list
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="content" class="form-label form-label-custom">
                                        <i class="lni lni-file"></i> Konten Blog <span class="text-danger">*</span>
                                    </label>
                                    <div id="content-editor" style="min-height: 400px; margin-bottom: 10px; background: #fff;"></div>
                                    <textarea 
                                        class="form-control form-input-custom" 
                                        id="content" 
                                        name="content" 
                                        rows="15" 
                                        placeholder="Tulis konten artikel blog di sini..."
                                        style="display: none;"></textarea>
                                    <div class="form-text">
                                        <i class="lni lni-information"></i> Gunakan toolbar di atas untuk memformat teks (bold, italic, dll)
                                    </div>
                                    <div class="invalid-feedback" id="content_error"></div>
                                </div>

                                <div class="mb-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_published" name="is_published" value="1">
                                        <label class="form-check-label form-label-custom" for="is_published">
                                            <i class="lni lni-checkmark-circle"></i> Publikasikan sekarang
                                        </label>
                                    </div>
                                    <div class="alert alert-info d-flex align-items-center mt-3" role="alert">
                                        <i class="lni lni-bulb me-2 fs-5"></i>
                                        <div>
                                            <strong>Tips:</strong> Blog yang dipublikasikan akan tampil di landing page (index.php). Blog dengan status Draft hanya dapat dilihat melalui admin panel.
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex gap-2 mt-4">
                                    <button type="submit" class="btn btn-success btn-lg px-4" id="btnSimpan">
                                        <i class="lni lni-save me-2"></i> Simpan Blog
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
                                    <small>Judul minimal 5 karakter</small>
                                </li>
                                <li class="mb-2">
                                    <i class="lni lni-checkmark-circle text-success me-2"></i>
                                    <small>Konten minimal 50 karakter</small>
                                </li>
                                <li class="mb-2">
                                    <i class="lni lni-checkmark-circle text-success me-2"></i>
                                    <small>Dapat diedit nanti</small>
                                </li>
                                <li>
                                    <i class="lni lni-checkmark-circle text-success me-2"></i>
                                    <small>Draft tidak tampil di website</small>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Examples Card -->
                    <div class="card wow fadeInUp mb-3" data-wow-delay=".5s" style="border-left: 4px solid #28a745;">
                        <div class="card-body">
                            <h6 class="card-title text-success mb-3">
                                <i class="lni lni-lightbulb me-2"></i> Contoh Kategori
                            </h6>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="badge bg-light text-dark">Teknologi</span>
                                <span class="badge bg-light text-dark">Research</span>
                                <span class="badge bg-light text-dark">Tutorial</span>
                                <span class="badge bg-light text-dark">Berita</span>
                                <span class="badge bg-light text-dark">Event</span>
                                <span class="badge bg-light text-dark">Update</span>
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
                                Pastikan judul menarik dan konten informatif. Gunakan excerpt untuk ringkasan singkat yang menarik perhatian pembaca.
                            </p>
                            <a href="index.php" class="btn btn-sm btn-outline-primary">
                                <i class="lni lni-arrow-left me-1"></i> Kembali ke Daftar
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif ($action === 'edit' && $blog): ?>
            <!-- Form Edit Blog -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="section-title mb-50">
                        <h2 class="mb-15 wow fadeInUp" data-wow-delay=".2s">Edit Blog</h2>
                        <p class="wow fadeInUp" data-wow-delay=".4s">Edit artikel blog yang sudah ada</p>
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
                                <i class="lni lni-text-format text-primary"></i> Form Edit Blog
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="" id="formEditBlog" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="id" value="<?php echo $blog['id']; ?>">
                                <input type="hidden" name="featured_image" id="featured_image_old" value="<?php echo htmlspecialchars($blog['featured_image'] ?? ''); ?>">
                                
                                <div class="mb-4">
                                    <label for="title" class="form-label form-label-custom">
                                        <i class="lni lni-headphone-alt"></i> Judul Blog <span class="text-danger">*</span>
                                    </label>
                                    <input 
                                        type="text" 
                                        class="form-control form-control-lg form-input-custom" 
                                        id="title" 
                                        name="title" 
                                        value="<?php echo htmlspecialchars($blog['title']); ?>"
                                        placeholder="Masukkan judul artikel blog" 
                                        required
                                        minlength="5"
                                        maxlength="200"
                                        autofocus>
                                    <div class="form-text">
                                        <i class="lni lni-information"></i> Judul yang menarik akan meningkatkan minat pembaca (minimal 5 karakter)
                                    </div>
                                    <div class="invalid-feedback" id="title_error"></div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-lg-6">
                                        <label for="category" class="form-label form-label-custom">
                                            <i class="lni lni-folder"></i> Kategori
                                        </label>
                                        <input 
                                            type="text" 
                                            class="form-control form-control-lg form-input-custom" 
                                            id="category" 
                                            name="category" 
                                            value="<?php echo htmlspecialchars($blog['category'] ?? ''); ?>"
                                            placeholder="Contoh: Teknologi, Research, dll"
                                            maxlength="50">
                                        <div class="form-text">
                                            <i class="lni lni-information"></i> Kategori membantu mengelompokkan artikel
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <label for="tags" class="form-label form-label-custom">
                                            <i class="lni lni-tag"></i> Tags
                                        </label>
                                        <input 
                                            type="text" 
                                            class="form-control form-control-lg form-input-custom" 
                                            id="tags" 
                                            name="tags" 
                                            value="<?php echo htmlspecialchars($blog['tags_string'] ?? ''); ?>"
                                            placeholder="Pisahkan dengan koma (contoh: php, javascript, web)"
                                            maxlength="200">
                                        <div class="form-text">
                                            <i class="lni lni-information"></i> Tags membantu pencarian artikel
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="featured_image" class="form-label form-label-custom">
                                        <i class="lni lni-image"></i> Gambar Featured
                                    </label>
                                    <?php if (!empty($blog['featured_image'])): 
                                        // Handle path gambar untuk preview di admin
                                        $preview_image_raw = $blog['featured_image'];
                                        $preview_image_src = '';
                                        $is_abs_preview = preg_match('/^https?:\/\//', $preview_image_raw) || (strlen($preview_image_raw) > 0 && $preview_image_raw[0] === '/');
                                        if ($is_abs_preview) {
                                            $preview_image_src = $preview_image_raw;
                                        } else {
                                            // Jika path sudah dimulai dengan ../../, gunakan langsung
                                            if (strpos($preview_image_raw, '../../') === 0) {
                                                $preview_image_src = $preview_image_raw;
                                            } else {
                                                // Path relatif dari root, tambahkan ../../ untuk module/blog/
                                                $preview_image_src = '../../' . ltrim($preview_image_raw, '/');
                                            }
                                        }
                                    ?>
                                    <div class="mb-2">
                                        <small class="text-muted">Gambar saat ini:</small><br>
                                        <img src="<?php echo htmlspecialchars($preview_image_src); ?>" 
                                             alt="Current" 
                                             style="max-width: 200px; max-height: 150px; border-radius: 8px; border: 2px solid #e0e0e0; margin-top: 8px;"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                        <small class="text-danger" style="display: none;">Gambar tidak dapat dimuat</small>
                                    </div>
                                    <?php endif; ?>
                                    <input 
                                        type="file" 
                                        class="form-control form-control-lg form-input-custom" 
                                        id="featured_image_edit" 
                                        name="featured_image" 
                                        accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                                    <div class="form-text">
                                        <i class="lni lni-information"></i> Upload gambar baru untuk mengganti gambar saat ini (JPG, PNG, GIF, WEBP - Maksimal 5MB). Kosongkan jika tidak ingin mengganti.
                                    </div>
                                    <div id="imagePreviewEdit" class="mt-3" style="display: none;">
                                        <small class="text-muted">Preview gambar baru:</small><br>
                                        <img id="previewImgEdit" src="" alt="Preview" style="max-width: 300px; max-height: 200px; border-radius: 8px; border: 2px solid #e0e0e0; margin-top: 8px;">
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="excerpt" class="form-label form-label-custom">
                                        <i class="lni lni-text-format"></i> Excerpt / Ringkasan
                                    </label>
                                    <textarea 
                                        class="form-control form-input-custom" 
                                        id="excerpt" 
                                        name="excerpt" 
                                        rows="3" 
                                        placeholder="Ringkasan singkat tentang artikel ini..."
                                        maxlength="300"><?php echo htmlspecialchars($blog['excerpt'] ?? ''); ?></textarea>
                                    <div class="form-text">
                                        <i class="lni lni-information"></i> Ringkasan akan ditampilkan di halaman blog list
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="content" class="form-label form-label-custom">
                                        <i class="lni lni-file"></i> Konten Blog <span class="text-danger">*</span>
                                    </label>
                                    <div id="content-editor-edit" style="min-height: 400px; margin-bottom: 10px; background: #fff;"></div>
                                    <textarea 
                                        class="form-control form-input-custom" 
                                        id="content-edit" 
                                        name="content" 
                                        rows="15" 
                                        placeholder="Tulis konten artikel blog di sini..."
                                        style="display: none;"><?php echo htmlspecialchars($blog['content']); ?></textarea>
                                    <div class="form-text">
                                        <i class="lni lni-information"></i> Gunakan toolbar di atas untuk memformat teks (bold, italic, dll)
                                    </div>
                                    <div class="invalid-feedback" id="content_error"></div>
                                </div>

                                <div class="mb-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_published" name="is_published" value="1" <?php echo $blog['is_published'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label form-label-custom" for="is_published">
                                            <i class="lni lni-checkmark-circle"></i> Publikasikan sekarang
                                        </label>
                                    </div>
                                    <div class="alert alert-info d-flex align-items-center mt-3" role="alert">
                                        <i class="lni lni-bulb me-2 fs-5"></i>
                                        <div>
                                            <strong>Tips:</strong> Blog yang dipublikasikan akan tampil di landing page (index.php). Blog dengan status Draft hanya dapat dilihat melalui admin panel.
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex gap-2 mt-4">
                                    <button type="submit" class="btn btn-success btn-lg px-4" id="btnUpdate">
                                        <i class="lni lni-save me-2"></i> Update Blog
                                    </button>
                                    <a href="index.php" class="btn btn-outline-secondary btn-lg px-4">
                                        <i class="lni lni-arrow-left me-2"></i> Batal
                                    </a>
                                    <?php if ($blog['is_published']): ?>
                                    <a href="../../pages/blog/blog-detail.php?id=<?php echo $blog['id']; ?>" target="_blank" class="btn btn-info btn-lg px-4">
                                        <i class="lni lni-eye me-2"></i> Preview
                                    </a>
                                    <?php endif; ?>
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
                                    <small>Judul minimal 5 karakter</small>
                                </li>
                                <li class="mb-2">
                                    <i class="lni lni-checkmark-circle text-success me-2"></i>
                                    <small>Konten minimal 50 karakter</small>
                                </li>
                                <li class="mb-2">
                                    <i class="lni lni-checkmark-circle text-success me-2"></i>
                                    <small>Perubahan akan tersimpan</small>
                                </li>
                                <li>
                                    <i class="lni lni-checkmark-circle text-success me-2"></i>
                                    <small>Draft tidak tampil di website</small>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Blog Status Card -->
                    <div class="card wow fadeInUp mb-3" data-wow-delay=".5s" style="border-left: 4px solid <?php echo $blog['is_published'] ? '#28a745' : '#ffc107'; ?>;">
                        <div class="card-body">
                            <h6 class="card-title mb-3" style="color: <?php echo $blog['is_published'] ? '#28a745' : '#ffc107'; ?>;">
                                <i class="lni <?php echo $blog['is_published'] ? 'lni-checkmark-circle' : 'lni-file'; ?> me-2"></i> Status Blog
                            </h6>
                            <div class="mb-2">
                                <?php if ($blog['is_published']): ?>
                                    <span class="badge bg-success">Published</span>
                                    <p class="small text-muted mt-2 mb-0">Blog ini tampil di landing page</p>
                                <?php else: ?>
                                    <span class="badge bg-warning">Draft</span>
                                    <p class="small text-muted mt-2 mb-0">Blog ini hanya terlihat di admin panel</p>
                                <?php endif; ?>
                            </div>
                            <?php if ($blog['is_published'] && isset($blog['published_at'])): ?>
                                <p class="small text-muted mb-0">
                                    <i class="lni lni-calendar"></i> Dipublikasikan: <?php echo date('d/m/Y H:i', strtotime($blog['published_at'])); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Help Card -->
                    <div class="card wow fadeInUp" data-wow-delay=".6s" style="border-left: 4px solid #ffc107;">
                        <div class="card-body">
                            <h6 class="card-title text-warning mb-3">
                                <i class="lni lni-question-circle me-2"></i> Butuh Bantuan?
                            </h6>
                            <p class="card-text small mb-2">
                                Pastikan judul menarik dan konten informatif. Gunakan excerpt untuk ringkasan singkat yang menarik perhatian pembaca.
                            </p>
                            <a href="index.php" class="btn btn-sm btn-outline-primary">
                                <i class="lni lni-arrow-left me-1"></i> Kembali ke Daftar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
</main>

<!-- Load UI/UX Components -->
<link rel="stylesheet" href="../../assets/css/anggota-ui.css">

<!-- Quill Rich Text Editor - Load First -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<style>
.ql-container {
    font-size: 16px;
    min-height: 400px;
}
.ql-editor {
    min-height: 400px;
}
/* Error message styling */
#content_error {
    display: none;
    width: 100%;
    margin-top: 0.25rem;
    font-size: 0.875rem;
    color: #dc3545;
}
#content_error.show {
    display: block !important;
}
.is-invalid ~ #content_error {
    display: block !important;
}
</style>

<script src="../../assets/js/classes/ToastNotification.js"></script>
<script src="../../assets/js/classes/LoadingState.js"></script>
<script src="../../assets/js/classes/AnggotaConfirmModal.js"></script>

<script>
// Search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchBlog');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query.length >= 2 || query.length === 0) {
                searchTimeout = setTimeout(function() {
                    if (query.length > 0) {
                        window.location.href = 'index.php?search=' + encodeURIComponent(query);
                    } else {
                        window.location.href = 'index.php';
                    }
                }, 500);
            }
        });
        
        // Enter key to search immediately
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(searchTimeout);
                const query = this.value.trim();
                if (query.length > 0) {
                    window.location.href = 'index.php?search=' + encodeURIComponent(query);
                } else {
                    window.location.href = 'index.php';
                }
            }
        });
    }
    
    // Show toast from PHP flash messages
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
function confirmDeleteBlog(id, title) {
    if (window.anggotaConfirmModal) {
        window.anggotaConfirmModal.confirmDelete(title, function() {
            // Show loading
            if (window.loading) {
                window.loading.show('Menghapus blog...');
            }
            
            // Delete via fetch
            fetch('index.php?action=hapus&id=' + id, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (window.loading) {
                    window.loading.hide();
                }
                // Reload page
                window.location.reload();
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
        if (confirm('Yakin ingin menghapus blog "' + title + '"?')) {
            window.location.href = 'index.php?action=hapus&id=' + id;
        }
    }
}

// Form validation untuk tambah blog
document.addEventListener('DOMContentLoaded', function() {
    const formTambah = document.getElementById('formTambahBlog');
    const formEdit = document.getElementById('formEditBlog');
    const form = formTambah || formEdit;
    
    if (form) {
        const inputTitle = document.getElementById('title');
        const inputContent = document.getElementById('content');
        const btnSimpan = document.getElementById('btnSimpan');
        const btnUpdate = document.getElementById('btnUpdate');
        const btnSubmit = btnSimpan || btnUpdate;
        
        // Real-time validation untuk title
        if (inputTitle) {
            inputTitle.addEventListener('input', function() {
                const value = this.value.trim();
                const minLength = 5;
                const maxLength = 200;
                
                // Remove previous validation classes
                this.classList.remove('is-invalid', 'is-valid');
                
                if (value.length === 0) {
                    // Empty, no validation yet
                    return;
                } else if (value.length < minLength) {
                    this.classList.add('is-invalid');
                    const errorEl = document.getElementById('title_error');
                    if (errorEl) {
                        errorEl.textContent = 'Judul blog minimal ' + minLength + ' karakter';
                    }
                } else if (value.length > maxLength) {
                    this.classList.add('is-invalid');
                    const errorEl = document.getElementById('title_error');
                    if (errorEl) {
                        errorEl.textContent = 'Judul blog maksimal ' + maxLength + ' karakter';
                    }
                } else {
                    this.classList.add('is-valid');
                }
            });
        }
        
        // Real-time validation untuk content
        if (inputContent) {
            inputContent.addEventListener('input', function() {
                const value = this.value.trim();
                const minLength = 50;
                
                // Remove previous validation classes
                this.classList.remove('is-invalid', 'is-valid');
                
                if (value.length === 0) {
                    // Empty, no validation yet
                    return;
                } else if (value.length < minLength) {
                    this.classList.add('is-invalid');
                    const errorEl = document.getElementById('content_error');
                    if (errorEl) {
                        errorEl.textContent = 'Konten blog minimal ' + minLength + ' karakter';
                    }
                } else {
                    this.classList.add('is-valid');
                }
            });
        }
        
        // Form submission
        if (form && btnSubmit) {
            form.addEventListener('submit', function(e) {
                // Copy konten dari Quill ke textarea SEBELUM validasi
                const contentEditorDiv = document.getElementById('content-editor');
                const contentEditorEditDiv = document.getElementById('content-editor-edit');
                let quillInstance = null;
                
                if (contentEditorDiv && contentEditorDiv.__quill) {
                    quillInstance = contentEditorDiv.__quill;
                } else if (contentEditorEditDiv && contentEditorEditDiv.__quill) {
                    quillInstance = contentEditorEditDiv.__quill;
                }
                
                // Coba ambil Quill instance dari textarea juga
                if (!quillInstance && inputContent && inputContent.__quill) {
                    quillInstance = inputContent.__quill;
                }
                
                if (quillInstance && inputContent) {
                    // Copy HTML content dari Quill ke textarea
                    inputContent.value = quillInstance.root.innerHTML;
                    console.log('Content copied from Quill to textarea, length:', inputContent.value.length);
                }
                
                const titleValue = inputTitle ? inputTitle.value.trim() : '';
                const contentValue = inputContent ? inputContent.value.trim() : '';
                
                // Validasi konten (gunakan text only untuk panjang)
                let contentTextLength = 0;
                if (quillInstance) {
                    contentTextLength = quillInstance.getText().trim().length;
                    console.log('Content text length from Quill:', contentTextLength);
                } else if (contentValue) {
                    // Fallback: strip HTML tags untuk menghitung panjang teks
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = contentValue;
                    contentTextLength = tempDiv.textContent.trim().length;
                    console.log('Content text length from textarea (stripped HTML):', contentTextLength);
                }
                
                console.log('Form validation - Title:', titleValue.length, 'Content:', contentTextLength);
                
                if (titleValue.length < 5) {
                    e.preventDefault();
                    if (inputTitle) {
                        inputTitle.classList.add('is-invalid');
                        const errorEl = document.getElementById('title_error');
                        if (errorEl) {
                            errorEl.textContent = 'Judul blog minimal 5 karakter';
                        }
                        inputTitle.focus();
                    }
                    
                    if (window.toast) {
                        window.toast.error('Judul blog minimal 5 karakter', 'Validasi Gagal');
                    }
                    return false;
                }
                
                if (contentTextLength < 50) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.error('Validation failed: Content too short', contentTextLength);
                    
                    if (inputContent) {
                        inputContent.classList.add('is-invalid');
                        const errorEl = document.getElementById('content_error');
                        if (errorEl) {
                            errorEl.textContent = 'Konten blog minimal 50 karakter (tanpa HTML tags)';
                            errorEl.style.cssText = 'display: block !important; color: #dc3545 !important; font-size: 0.875rem !important; margin-top: 0.25rem !important; width: 100% !important;';
                        }
                        // Focus ke editor jika ada
                        if (contentEditorDiv) {
                            contentEditorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        } else if (contentEditorEditDiv) {
                            contentEditorEditDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    }
                    
                    // Tampilkan alert sebagai fallback
                    alert('Konten blog minimal 50 karakter!');
                    
                    if (window.toast) {
                        window.toast.error('Konten blog minimal 50 karakter', 'Validasi Gagal');
                    }
                    return false;
                }
                
                // Pastikan textarea memiliki value sebelum submit
                // Jika contentTextLength sudah >= 50, berarti konten valid
                if (contentTextLength >= 50) {
                    // Konten sudah valid, pastikan ter-copy ke textarea
                    if (quillInstance && inputContent) {
                        const quillContent = quillInstance.root.innerHTML;
                        inputContent.value = quillContent;
                        console.log('Content copied to textarea after validation passed, length:', inputContent.value.length);
                        
                        // Pastikan textarea tidak kosong setelah copy
                        if (!inputContent.value || inputContent.value.trim() === '' || inputContent.value.trim() === '<p><br></p>') {
                            console.error('ERROR: Textarea still empty after copy!');
                            e.preventDefault();
                            e.stopPropagation();
                            alert('Error: Konten tidak ter-copy ke textarea. Silakan coba lagi.');
                            return false;
                        }
                    } else if (!quillInstance && inputContent) {
                        // Jika tidak ada Quill instance, cek textarea langsung
                        const textareaValue = inputContent.value ? inputContent.value.trim() : '';
                        if (!textareaValue || textareaValue === '<p><br></p>' || textareaValue === '<p></p>') {
                            console.error('ERROR: No Quill instance and textarea is empty!');
                            e.preventDefault();
                            e.stopPropagation();
                            inputContent.classList.add('is-invalid');
                            const errorEl = document.getElementById('content_error');
                            if (errorEl) {
                                errorEl.textContent = 'Konten blog tidak boleh kosong';
                                errorEl.classList.add('show');
                                errorEl.style.cssText = 'display: block !important; color: #dc3545 !important; font-size: 0.875rem !important; margin-top: 0.25rem !important; width: 100% !important;';
                            }
                            alert('Konten blog tidak boleh kosong!');
                            return false;
                        }
                    }
                }
                
                // Clear error jika validasi berhasil
                if (inputContent) {
                    inputContent.classList.remove('is-invalid');
                    const errorEl = document.getElementById('content_error');
                    if (errorEl) {
                        errorEl.classList.remove('show');
                        errorEl.style.display = 'none';
                    }
                }
                
                console.log('Validation passed, submitting form...');
                console.log('Final textarea value length:', inputContent ? inputContent.value.length : 0);
                
                // Show loading
                if (window.loading) {
                    const actionText = formTambah ? 'Menyimpan' : 'Mengupdate';
                    window.loading.show(actionText + ' blog...');
                }
                btnSubmit.disabled = true;
                const buttonText = formTambah ? 'Menyimpan...' : 'Mengupdate...';
                btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>' + buttonText;
            });
        }
    }
    
    // Preview gambar untuk form tambah
    const featuredImageInput = document.getElementById('featured_image');
    const imagePreview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    
    if (featuredImageInput && imagePreview && previewImg) {
        featuredImageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    imagePreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                imagePreview.style.display = 'none';
            }
        });
    }
    
    // Preview gambar untuk form edit
    const featuredImageInputEdit = document.getElementById('featured_image_edit');
    const imagePreviewEdit = document.getElementById('imagePreviewEdit');
    const previewImgEdit = document.getElementById('previewImgEdit');
    
    if (featuredImageInputEdit && imagePreviewEdit && previewImgEdit) {
        featuredImageInputEdit.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImgEdit.src = e.target.result;
                    imagePreviewEdit.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                imagePreviewEdit.style.display = 'none';
            }
        });
    }
    
    // Initialize Quill Rich Text Editor
    function initQuillEditor(editorId, textareaId, formId, existingContent) {
        try {
            const editorDiv = document.getElementById(editorId);
            const textarea = document.getElementById(textareaId);
            const form = document.getElementById(formId);
            
            if (!editorDiv) {
                console.error('Editor div not found:', editorId);
                return null;
            }
            
            if (!textarea) {
                console.error('Textarea not found:', textareaId);
                return null;
            }
            
            if (!form) {
                console.error('Form not found:', formId);
                return null;
            }
            
            // Check if Quill is loaded
            if (typeof Quill === 'undefined') {
                console.error('Quill library is not loaded');
                return null;
            }
            
            // Check if Quill already initialized on this element
            if (editorDiv.__quill) {
                console.log('Quill already initialized on', editorId, '- returning existing instance');
                return editorDiv.__quill;
            }
            
            // Check if element already has Quill toolbar (prevent duplicate)
            if (editorDiv.querySelector('.ql-toolbar')) {
                console.log('Quill toolbar already exists on', editorId, '- removing and reinitializing');
                // Remove existing Quill instance
                editorDiv.innerHTML = '';
            }
            
            // Create Quill editor
            const quill = new Quill('#' + editorId, {
                theme: 'snow',
                modules: {
                    toolbar: [
                        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ 'color': [] }, { 'background': [] }],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        [{ 'align': [] }],
                        ['link', 'image'],
                        ['blockquote', 'code-block'],
                        ['clean']
                    ]
                },
                placeholder: 'Tulis konten artikel blog di sini...',
                bounds: document.body
            });
            
            // Set existing content jika ada
            if (existingContent && existingContent.trim() !== '') {
                // Decode HTML entities jika ada
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = existingContent;
                const decodedContent = tempDiv.textContent || tempDiv.innerText || '';
                
                if (existingContent.includes('<') && existingContent !== decodedContent) {
                    // Jika sudah HTML, langsung set
                    quill.root.innerHTML = existingContent;
                } else {
                    // Jika plain text, convert ke HTML
                    quill.root.innerHTML = existingContent.replace(/\n/g, '<br>');
                }
            }
            
            // Simpan instance Quill ke element dan textarea untuk akses nanti
            editorDiv.__quill = quill;
            textarea.__quill = quill;
            
            // Update textarea saat form submit (sebelum validasi)
            // Event listener ini akan dijalankan SEBELUM event listener validasi utama
            form.addEventListener('submit', function(e) {
                // Copy konten dari Quill ke textarea
                const quillContent = quill.root.innerHTML;
                textarea.value = quillContent;
                console.log('Quill content copied to textarea:', quillContent.substring(0, 100));
                // Jangan preventDefault di sini, biarkan event listener utama yang handle validasi
            }, true); // Use capture phase untuk dijalankan lebih dulu
            
            return quill;
        } catch (error) {
            console.error('Error initializing Quill editor:', error);
            return null;
        }
    }
    
    // Function to initialize editors after Quill is loaded
    function initializeEditors() {
        console.log('Initializing Quill editors...');
        
        // Initialize untuk form tambah
        const contentEditorDiv = document.getElementById('content-editor');
        const contentTextarea = document.getElementById('content');
        const formTambah = document.getElementById('formTambahBlog');
        
        if (contentEditorDiv && contentTextarea && formTambah) {
            // Cek apakah sudah diinisialisasi
            if (!contentEditorDiv.__quill) {
                console.log('Found form tambah, initializing editor...');
                initQuillEditor('content-editor', 'content', 'formTambahBlog', '');
            } else {
                console.log('Form tambah editor already initialized');
            }
        }
        
        // Initialize untuk form edit
        const contentEditorEditDiv = document.getElementById('content-editor-edit');
        const contentTextareaEdit = document.getElementById('content-edit');
        const formEdit = document.getElementById('formEditBlog');
        
        if (contentEditorEditDiv && contentTextareaEdit && formEdit) {
            // Cek apakah sudah diinisialisasi
            if (!contentEditorEditDiv.__quill) {
                console.log('Found form edit, initializing editor...');
                const existingContent = contentTextareaEdit.value || '';
                console.log('Existing content length:', existingContent.length);
                const quill = initQuillEditor('content-editor-edit', 'content-edit', 'formEditBlog', existingContent);
                if (quill) {
                    console.log('Quill editor initialized successfully for form edit');
                } else {
                    console.error('Failed to initialize Quill editor for form edit');
                }
            } else {
                console.log('Form edit editor already initialized');
            }
        } else {
            console.warn('Form edit elements not found. Editor div:', !!contentEditorEditDiv, 'Textarea:', !!contentTextareaEdit, 'Form:', !!formEdit);
        }
    }
    
    // Wait for Quill to be loaded and DOM to be ready
    function waitAndInitialize() {
        // Check if Quill is available
        if (typeof Quill === 'undefined') {
            console.log('Waiting for Quill library to load...');
            let quillCheckCount = 0;
            const quillCheckInterval = setInterval(function() {
                quillCheckCount++;
                if (typeof Quill !== 'undefined') {
                    clearInterval(quillCheckInterval);
                    console.log('Quill library loaded, initializing editors...');
                    // Wait a bit more to ensure DOM is ready
                    setTimeout(initializeEditors, 200);
                } else if (quillCheckCount > 100) {
                    // Timeout after 10 seconds
                    clearInterval(quillCheckInterval);
                    console.error('Quill library failed to load after 10 seconds');
                    // Try to initialize anyway (maybe Quill will load later)
                    setTimeout(initializeEditors, 500);
                }
            }, 100);
        } else {
            console.log('Quill library already loaded, initializing editors...');
            // Wait a bit to ensure DOM is fully ready
            setTimeout(initializeEditors, 200);
        }
    }
    
    // Start initialization when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', waitAndInitialize);
    } else {
        // DOM already ready
        waitAndInitialize();
    }
    
    // Also try on window load as backup
    window.addEventListener('load', function() {
        setTimeout(function() {
            // Check if editors were initialized
            const editorDiv = document.getElementById('content-editor-edit');
            if (editorDiv && !editorDiv.querySelector('.ql-toolbar')) {
                console.log('Editor not initialized, trying again...');
                initializeEditors();
            }
        }, 500);
    });
});
</script>

<?php include __DIR__ . '/../../template/admin_footer.php'; ?>
