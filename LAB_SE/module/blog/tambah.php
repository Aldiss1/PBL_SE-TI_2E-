<?php
$page_title = "Tambah Blog - Admin";
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

// Fungsi untuk generate slug
function generateSlug($text) {
    // Convert to lowercase
    $text = strtolower($text);
    // Replace spaces with hyphens
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    // Remove leading/trailing hyphens
    $text = trim($text, '-');
    return $text;
}

// Proses form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = clean_input($_POST['title']);
    $content = $_POST['content']; // Jangan clean untuk content karena bisa mengandung format
    $excerpt = clean_input($_POST['excerpt']);
    $category = clean_input($_POST['category']);
    $tags = clean_input($_POST['tags']);
    $featured_image = clean_input($_POST['featured_image']);
    $is_published = isset($_POST['is_published']) ? 'true' : 'false';
    $published_at = $is_published === 'true' ? date('Y-m-d H:i:s') : null;
    
    // Escape untuk PostgreSQL
    $title_escaped = pg_escape_string($koneksi, $title);
    $content_escaped = pg_escape_string($koneksi, $content);
    $excerpt_escaped = pg_escape_string($koneksi, $excerpt);
    $category_escaped = pg_escape_string($koneksi, $category);
    $featured_image_escaped = pg_escape_string($koneksi, $featured_image);
    
    // Generate slug
    $slug = generateSlug($title);
    $slug_escaped = pg_escape_string($koneksi, $slug);
    
    // Cek apakah slug sudah ada
    $check_slug = "SELECT COUNT(*) as count FROM blog WHERE slug = '$slug_escaped'";
    $result_check = pg_query($koneksi, $check_slug);
    if ($result_check) {
        $row_check = pg_fetch_assoc($result_check);
        if ($row_check['count'] > 0) {
            $slug = $slug . '-' . time();
            $slug_escaped = pg_escape_string($koneksi, $slug);
        }
    }
    
    // Get author_id from session
    $author_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 1;
    
    // Convert tags to array format for PostgreSQL
    $tags_array = [];
    if (!empty($tags)) {
        $tags_list = explode(',', $tags);
        foreach ($tags_list as $tag) {
            $tags_array[] = trim($tag);
        }
    }
    $tags_json = json_encode($tags_array);
    $tags_json_escaped = pg_escape_string($koneksi, $tags_json);
    
    // Insert blog
    $published_at_str = $published_at ? "'$published_at'" : "NULL";
    $query = "INSERT INTO blog (title, slug, content, excerpt, featured_image, author_id, category, tags, is_published, published_at, created_at, updated_at) 
              VALUES ('$title_escaped', '$slug_escaped', '$content_escaped', '$excerpt_escaped', '$featured_image_escaped', $author_id, '$category_escaped', '$tags_json_escaped'::jsonb, $is_published, $published_at_str, NOW(), NOW())";
    
    $result = pg_query($koneksi, $query);
    
    if ($result) {
        pesan_kilat('Blog berhasil ditambahkan!', 'success');
        header('Location: index.php');
        exit;
    } else {
        pesan_kilat('Gagal menambahkan blog. Error: ' . pg_last_error($koneksi), 'error');
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
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-12">
                                <button type="submit" class="button radius-10" style="background: #2F80ED; color: white; padding: 12px 30px;">
                                    <i class="lni lni-save"></i> Simpan Blog
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

