<?php
require_once __DIR__ . '/config/koneksi.php';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 9;
$offset = ($page - 1) * $per_page;

// Ambil total blog yang published
$count_query = "SELECT COUNT(*) as total FROM blog WHERE is_published = true";
$count_result = pg_query($koneksi, $count_query);
$total_blogs = 0;
if ($count_result) {
    $count_row = pg_fetch_assoc($count_result);
    $total_blogs = (int)$count_row['total'];
}
$total_pages = ceil($total_blogs / $per_page);

// Ambil blog yang published
$blogs = [];
$query = "SELECT id, title, excerpt, featured_image, category, published_at, created_at, slug, views 
          FROM blog 
          WHERE is_published = true 
          ORDER BY published_at DESC, created_at DESC 
          LIMIT $per_page OFFSET $offset";
$result = pg_query($koneksi, $query);
if ($result && pg_num_rows($result) > 0) {
    while ($row = pg_fetch_assoc($result)) {
        $blogs[] = $row;
    }
}
?>
<?php include __DIR__ . '/template/header.php'; ?>

<link rel="stylesheet" href="assets/css/blog-list.css" />

<section class="pt-150 pb-100" style="margin-top: 80px; background: #f8f9fa;">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="section-title text-center mb-60">
                    <h2 class="mb-15 wow fadeInUp" data-wow-delay=".2s">Blog & Artikel</h2>
                    <p class="wow fadeInUp" data-wow-delay=".4s">Artikel dan berita terkini dari Laboratorium Software Engineering</p>
                </div>
            </div>
        </div>

        <div class="row">
            <?php if (count($blogs) > 0): ?>
                <?php foreach ($blogs as $blog_item): ?>
                <?php
                    $blog_link = "blog-detail.php?id=" . $blog_item['id'];
                    $blog_image = $blog_item['featured_image'] ? $blog_item['featured_image'] : 'assets/img/blog/blog-1-1.jpg';
                    $blog_excerpt = $blog_item['excerpt'] ? htmlspecialchars(substr($blog_item['excerpt'], 0, 150)) . '...' : (strlen($blog_item['title']) > 100 ? htmlspecialchars(substr($blog_item['title'], 0, 100)) . '...' : htmlspecialchars($blog_item['title']));
                    $blog_date = $blog_item['published_at'] ? date('d F Y', strtotime($blog_item['published_at'])) : date('d F Y', strtotime($blog_item['created_at']));
                ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="blog-card wow fadeInUp" data-wow-delay=".2s" style="background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05); transition: all 0.3s ease; height: 100%;">
                        <div class="blog-image" style="height: 200px; overflow: hidden;">
                            <img src="<?php echo htmlspecialchars($blog_image); ?>" alt="<?php echo htmlspecialchars($blog_item['title']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        <div class="blog-content" style="padding: 20px;">
                            <?php if ($blog_item['category']): ?>
                            <span style="color: #2F80ED; font-size: 12px; font-weight: 600; text-transform: uppercase;"><?php echo htmlspecialchars($blog_item['category']); ?></span>
                            <?php endif; ?>
                            <h5 style="margin-top: 10px; margin-bottom: 10px; color: #2c3e50; min-height: 60px;">
                                <a href="<?php echo $blog_link; ?>" style="color: #2c3e50; text-decoration: none; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                    <?php echo htmlspecialchars($blog_item['title']); ?>
                                </a>
                            </h5>
                            <?php if ($blog_excerpt): ?>
                            <p style="color: #6c757d; font-size: 14px; margin-bottom: 15px; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; min-height: 60px;">
                                <?php echo $blog_excerpt; ?>
                            </p>
                            <?php endif; ?>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px; padding-top: 15px; border-top: 1px solid #f0f0f0;">
                                <span style="color: #999; font-size: 12px;">
                                    <i class="lni lni-calendar"></i> <?php echo $blog_date; ?>
                                    <?php if ($blog_item['views'] > 0): ?>
                                    <span style="margin-left: 10px;">
                                        <i class="lni lni-eye"></i> <?php echo (int)$blog_item['views']; ?>
                                    </span>
                                    <?php endif; ?>
                                </span>
                                <a href="<?php echo $blog_link; ?>" class="button button-sm radius-10" style="padding: 5px 15px; font-size: 12px;">Baca</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <i class="lni lni-inbox" style="font-size: 48px; color: #dee2e6;"></i>
                    <p style="color: #717791; margin-top: 20px;">Belum ada artikel blog yang dipublikasikan.</p>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($total_pages > 1): ?>
        <div class="row mt-50">
            <div class="col-lg-12">
                <div class="pagination-wrapper text-center">
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center" style="list-style: none; padding: 0; margin: 0;">
                            <?php if ($page > 1): ?>
                            <li class="page-item" style="display: inline-block; margin: 0 5px;">
                                <a class="page-link" href="?page=<?php echo ($page - 1); ?>" style="padding: 8px 15px; border: 1px solid #dee2e6; border-radius: 5px; text-decoration: none; color: #2F80ED;">← Prev</a>
                            </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <?php if ($i == $page): ?>
                                <li class="page-item active" style="display: inline-block; margin: 0 5px;">
                                    <span class="page-link" style="padding: 8px 15px; border: 1px solid #2F80ED; border-radius: 5px; background: #2F80ED; color: white;"><?php echo $i; ?></span>
                                </li>
                                <?php else: ?>
                                <li class="page-item" style="display: inline-block; margin: 0 5px;">
                                    <a class="page-link" href="?page=<?php echo $i; ?>" style="padding: 8px 15px; border: 1px solid #dee2e6; border-radius: 5px; text-decoration: none; color: #2c3e50;"><?php echo $i; ?></a>
                                </li>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                            <li class="page-item" style="display: inline-block; margin: 0 5px;">
                                <a class="page-link" href="?page=<?php echo ($page + 1); ?>" style="padding: 8px 15px; border: 1px solid #dee2e6; border-radius: 5px; text-decoration: none; color: #2F80ED;">Next →</a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/template/footer.php'; ?>

