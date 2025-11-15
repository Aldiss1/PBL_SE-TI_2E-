<?php
$page_title = "Edit Blog - Admin";
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
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim($text, '-');
    return $text;
}

// Ambil data blog
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$blog = null;

if ($id > 0) {
    $query = "SELECT * FROM blog WHERE id = $id";
    $result = pg_query($koneksi, $query);
    if ($result && pg_num_rows($result) > 0) {
        $blog = pg_fetch_assoc($result);
        
        // Parse tags from JSON
        if (!empty($blog['tags'])) {
            $tags_array = json_decode($blog['tags'], true);
            if (is_array($tags_array)) {
                $blog['tags_string'] = implode(', ', $tags_array);
            } else {
                $blog['tags_string'] = '';
            }
        } else {
            $blog['tags_string'] = '';
        }
    }
}

if (!$blog) {
    pesan_kilat('Blog tidak ditemukan!', 'error');
    header('Location: index.php');
    exit;
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
    
    // Escape untuk PostgreSQL
    $title_escaped = pg_escape_string($koneksi, $title);
    $content_escaped = pg_escape_string($koneksi, $content);
    $excerpt_escaped = pg_escape_string($koneksi, $excerpt);
    $category_escaped = pg_escape_string($koneksi, $category);
    $featured_image_escaped = pg_escape_string($koneksi, $featured_image);
    
    // Generate slug baru jika title berubah
    $new_slug = generateSlug($title);
    $slug_update = '';
    if ($new_slug !== $blog['slug']) {
        $new_slug_escaped = pg_escape_string($koneksi, $new_slug);
        // Cek apakah slug baru sudah ada
        $check_slug = "SELECT COUNT(*) as count FROM blog WHERE slug = '$new_slug_escaped' AND id != $id";
        $result_check = pg_query($koneksi, $check_slug);
        if ($result_check) {
            $row_check = pg_fetch_assoc($result_check);
            if ($row_check['count'] > 0) {
                $new_slug = $new_slug . '-' . time();
                $new_slug_escaped = pg_escape_string($koneksi, $new_slug);
            }
        }
        $slug_update = ", slug = '$new_slug_escaped'";
    }
    
    // Set published_at jika baru dipublish
    $published_at_update = '';
    if ($is_published === 'true' && !$blog['is_published']) {
        $published_at = date('Y-m-d H:i:s');
        $published_at_update = ", published_at = '$published_at'";
    }
    
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
    
    // Update blog
    $query = "UPDATE blog SET 
              title = '$title_escaped',
              content = '$content_escaped',
              excerpt = '$excerpt_escaped',
              featured_image = '$featured_image_escaped',
              category = '$category_escaped',
              tags = '$tags_json_escaped'::jsonb,
              is_published = $is_published
              $slug_update
              $published_at_update,
              updated_at = NOW()
              WHERE id = $id";
    
    $result = pg_query($koneksi, $query);
    
    if ($result) {
        pesan_kilat('Blog berhasil diperbarui!', 'success');
        header('Location: index.php');
        exit;
    } else {
        pesan_kilat('Gagal memperbarui blog. Error: ' . pg_last_error($koneksi), 'error');
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
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-12">
                                <button type="submit" class="button radius-10" style="background: #2F80ED; color: white; padding: 12px 30px;">
                                    <i class="lni lni-save"></i> Update Blog
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

