<?php
require_once __DIR__ . '/../../config/koneksi.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$slug = isset($_GET['slug']) ? $_GET['slug'] : '';
$blog = null;

// Ambil blog berdasarkan ID atau slug
if ($id > 0) {
    $id_escaped = pg_escape_string($koneksi, $id);
    $query = "SELECT * FROM blog WHERE id = $id_escaped AND is_published = true";
    $result = pg_query($koneksi, $query);
    if ($result && pg_num_rows($result) > 0) {
        $blog = pg_fetch_assoc($result);
        
        // Update views counter
        $update_views = "UPDATE blog SET views = views + 1 WHERE id = $id_escaped";
        pg_query($koneksi, $update_views);
    }
} elseif (!empty($slug)) {
    $slug_escaped = pg_escape_string($koneksi, $slug);
    $query = "SELECT * FROM blog WHERE slug = '$slug_escaped' AND is_published = true";
    $result = pg_query($koneksi, $query);
    if ($result && pg_num_rows($result) > 0) {
        $blog = pg_fetch_assoc($result);
        
        // Update views counter
        $blog_id = $blog['id'];
        $update_views = "UPDATE blog SET views = views + 1 WHERE id = $blog_id";
        pg_query($koneksi, $update_views);
    }
}
?>
<?php include __DIR__ . '/../../template/header.php'; ?>

<link rel="stylesheet" href="../../assets/css/blog-detail.css" />

<section class="pt-150 pb-100" style="margin-top: 80px; background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-9" id="blog-detail-container">
          <?php if ($blog): ?>
            <div class="section-title mb-40">
                <h2 class="mb-25 wow fadeInUp" data-wow-delay=".2s"><?php echo htmlspecialchars($blog['title']); ?></h2>
                <p class="wow fadeInUp" data-wow-delay=".4s">Dipublikasikan pada: <?php echo $blog['published_at'] ? date('d F Y', strtotime($blog['published_at'])) : date('d F Y', strtotime($blog['created_at'])); ?></p>
            </div>

            <!-- Meta Information -->
            <div class="blog-meta-info wow fadeInUp" data-wow-delay=".3s">
                <div class="meta-item">
                    <i class="lni lni-calendar"></i>
                    <span><?php echo $blog['published_at'] ? date('d F Y', strtotime($blog['published_at'])) : date('d F Y', strtotime($blog['created_at'])); ?></span>
                </div>
                <?php if (!empty($blog['views'])): ?>
                <div class="meta-item">
                    <i class="lni lni-eye"></i>
                    <span><?php echo number_format($blog['views']); ?> views</span>
                </div>
                <?php endif; ?>
                <?php if (!empty($blog['category'])): ?>
                <div class="meta-item">
                    <i class="lni lni-tag"></i>
                    <span><?php echo htmlspecialchars(ucfirst($blog['category'])); ?></span>
                </div>
                <?php endif; ?>
            </div>
        
            <?php 
            $featured_image_raw = $blog['featured_image'] ?? '';
            $featured_image_src = '';
            if (!empty($featured_image_raw)) {
                $is_abs = preg_match('/^https?:\/\//', $featured_image_raw) || (strlen($featured_image_raw) > 0 && $featured_image_raw[0] === '/');
                if ($is_abs) {
                    $featured_image_src = $featured_image_raw;
                } else {
                    // Jika path sudah dimulai dengan ../../, gunakan langsung
                    if (strpos($featured_image_raw, '../../') === 0) {
                        $featured_image_src = $featured_image_raw;
                    } else {
                        // Path relatif dari root, tambahkan ../../ untuk pages/blog/
                        $featured_image_src = '../../' . ltrim($featured_image_raw, '/');
                    }
                }
            } else {
                $featured_image_src = '../../assets/img/blog/blog-1-1.jpg';
            }
            ?>
            <div class="wow fadeInUp" data-wow-delay=".6s">
                <img src="<?php echo htmlspecialchars($featured_image_src); ?>" 
                     alt="<?php echo htmlspecialchars($blog['title']); ?>" 
                     class="img-fluid mb-40 blog-featured-image"
                     onerror="this.src='../../assets/img/blog/blog-1-1.jpg';">
            </div>
        
            <div class="article-content wow fadeInUp" data-wow-delay=".8s">
                <?php 
                // Render content dari Quill editor (sudah dalam format HTML)
                $content = $blog['content'] ?? '';
                
                // Konten dari Quill sudah HTML, jadi langsung render tanpa escape
                // Quill editor hanya menghasilkan tag HTML yang aman (p, strong, em, ul, ol, li, a, img, blockquote, code, pre, h1-h6)
                // Untuk keamanan tambahan, kita bisa strip script tags jika ada
                $content = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $content);
                
                echo $content;
                ?>
            </div>
            
            <?php if (!empty($blog['tags'])): 
                $tags_array = json_decode($blog['tags'], true);
                if (is_array($tags_array) && count($tags_array) > 0):
            ?>
            <div class="blog-tags wow fadeInUp" data-wow-delay=".9s">
                <strong>Tags:</strong>
                <?php foreach ($tags_array as $tag): ?>
                <span><?php echo htmlspecialchars(trim($tag)); ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; endif; ?>
          <?php else: ?>
            <div class="section-title mb-40">
                <h2 class="mb-25 wow fadeInUp" data-wow-delay=".2s">Artikel Tidak Ditemukan</h2>
                <p class="wow fadeInUp" data-wow-delay=".4s" style="color: #585978;">Artikel yang Anda cari tidak ditemukan atau belum dipublikasikan.</p>
            </div>
          <?php endif; ?>
          <hr class="my-5">
          <a href="../../index.php#blog" class="button radius-10 border-button mt-20 wow fadeInUp" data-wow-delay="1s">← Kembali ke Daftar Blog</a>
        </div>
      </div>
    </div>
</section>

<?php include __DIR__ . '/../../template/footer.php'; ?>

