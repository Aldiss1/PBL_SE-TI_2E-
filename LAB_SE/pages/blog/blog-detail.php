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

<section class="pt-150 pb-100" style="margin-top: 80px;">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-10" id="blog-detail-container">
          <?php if ($blog): ?>
            <div class="section-title mb-40">
                <h2 class="mb-25 wow fadeInUp" data-wow-delay=".2s"><?php echo htmlspecialchars($blog['title']); ?></h2>
                <p class="wow fadeInUp" data-wow-delay=".4s" style="color: #585978;">Dipublikasikan pada: <?php echo $blog['published_at'] ? date('d F Y', strtotime($blog['published_at'])) : date('d F Y', strtotime($blog['created_at'])); ?></p>
            </div>
        
            <?php if ($blog['featured_image']): ?>
            <div class="wow fadeInUp" data-wow-delay=".6s">
                <img src="<?php echo htmlspecialchars($blog['featured_image']); ?>" alt="<?php echo htmlspecialchars($blog['title']); ?>" class="img-fluid mb-40" style="width: 100%; max-height: 450px; object-fit: cover; border-radius: 10px;">
            </div>
            <?php endif; ?>
        
            <div class="article-content wow fadeInUp" data-wow-delay=".8s" style="line-height: 1.8; color: #2c3e50;">
                <?php 
                // Render content dengan formatting
                $content = $blog['content'];
                // Convert line breaks to HTML
                $content = nl2br(htmlspecialchars($content));
                // Simple formatting untuk paragraf
                echo $content;
                ?>
            </div>
            
            <?php if (!empty($blog['tags'])): 
                $tags_array = json_decode($blog['tags'], true);
                if (is_array($tags_array) && count($tags_array) > 0):
            ?>
            <div class="blog-tags mt-40 wow fadeInUp" data-wow-delay=".9s">
                <strong style="color: #2c3e50; margin-right: 10px;">Tags:</strong>
                <?php foreach ($tags_array as $tag): ?>
                <span style="display: inline-block; background: #f8f9fa; color: #2F80ED; padding: 5px 12px; border-radius: 20px; margin-right: 5px; margin-bottom: 5px; font-size: 12px;">
                    <?php echo htmlspecialchars(trim($tag)); ?>
                </span>
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

