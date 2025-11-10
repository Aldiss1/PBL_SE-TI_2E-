<?php
require_once __DIR__ . '/config/koneksi.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$blog = null;

if ($id > 0) {
    $query = "SELECT * FROM blog WHERE id = $id";
    $result = pg_query($koneksi, $query);
    if (!$result) {
        error_log("Error query blog detail: " . pg_last_error($koneksi));
    } elseif (pg_num_rows($result) > 0) {
        $blog = pg_fetch_assoc($result);
    }
}
?>
<?php include __DIR__ . '/template/header.php'; ?>

<style>
  .article-content p {
    margin-bottom: 1.5rem;
    line-height: 1.8;
    color: #585978;
  }
  .article-content h3 {
    margin-top: 2.5rem;
    margin-bottom: 1rem;
  }
  .article-content ul, .article-content ol {
    margin-bottom: 1.5rem;
    padding-left: 2rem;
    color: #585978;
  }
  .article-content li {
    margin-bottom: 0.5rem;
  }
  .article-content img {
    border-radius: 10px;
  }
</style>

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
        
            <div class="article-content wow fadeInUp" data-wow-delay=".8s">
                <?php echo nl2br(htmlspecialchars($blog['content'])); ?>
            </div>
          <?php else: ?>
            <div class="section-title mb-40">
                <h2 class="mb-25 wow fadeInUp" data-wow-delay=".2s">Ini Adalah Contoh Judul Artikel</h2>
                <p class="wow fadeInUp" data-wow-delay=".4s" style="color: #585978;">Dipublikasikan pada: 20 Oktober 2025</p>
            </div>
        
            <div class="wow fadeInUp" data-wow-delay=".6s">
                <img src="assets/img/blog/blog-1-1.jpg" alt="Blog 1" class="img-fluid mb-40" style="width: 100%; max-height: 450px; object-fit: cover; border-radius: 10px;">
            </div>
        
            <div class="article-content wow fadeInUp" data-wow-delay=".8s">
                <p>Ini adalah paragraf pembuka untuk contoh artikel.</p>
                <h3>Contoh Subjudul (Heading 3)</h3>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer nec odio. Praesent libero. Sed cursus ante dapibus diam. Sed nisi. Nulla quis sem at nibh elementum imperdiet. Duis sagittis ipsum. Praesent mauris. Fusce nec tellus sed augue semper porta. Mauris massa.</p>
                <h3>Contoh Daftar Poin</h3>
                <p>Vestibulum lacinia arcu eget nulla. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Berikut adalah contoh daftar:</p>
                <ul>
                   <li>Ini adalah contoh poin daftar (list item).</li>
                   <li>Item ini menunjukkan bagaimana daftar akan terlihat.</li>
                   <li>Poin ketiga untuk melengkapi contoh.</li>
                </ul>
            </div>
          <?php endif; ?>
          <hr class="my-5">
          <a href="index.php#blog" class="button radius-10 border-button mt-20 wow fadeInUp" data-wow-delay="1s">← Kembali ke Daftar Blog</a>
        </div>
      </div>
    </div>
</section>

<?php include __DIR__ . '/template/footer.php'; ?>

