<?php
require_once __DIR__ . '/../../config/koneksi.php';

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 9;
$offset = ($page - 1) * $per_page;

// Ambil blog dari materialized view (lebih cepat!)
// Fallback ke query biasa jika materialized view belum dibuat
$total_blogs = 0;
$total_pages = 0;
$blogs = [];

if (file_exists(__DIR__ . '/../../fungsi/materialized_views_complete.php')) {
    require_once __DIR__ . '/../../fungsi/materialized_views_complete.php';
    
    // Ambil total blog dari materialized view
    $stats_mv = get_stats_complete();
    if ($stats_mv) {
        $total_blogs = (int)($stats_mv['total_blog_published'] ?? 0);
        $total_pages = ceil($total_blogs / $per_page);
    }
    
    // Ambil blog yang published dari materialized view dengan pagination
    if (function_exists('get_blog_published_complete')) {
        $blogs_all = get_blog_published_complete(1000); // Ambil banyak, lalu paginate manual
        $count = 0;
        foreach ($blogs_all as $blog) {
            if ($count >= $offset && $count < ($offset + $per_page)) {
                // Materialized view sudah include semua data termasuk featured_image dan views
                $blogs[] = [
                    'id' => (int)($blog['id'] ?? 0),
                    'title' => $blog['title'] ?? '',
                    'excerpt' => $blog['excerpt'] ?? '',
                    'featured_image' => $blog['featured_image'] ?? '',
                    'category' => $blog['category'] ?? '',
                    'published_at' => $blog['published_at'] ?? null,
                    'created_at' => $blog['created_at'] ?? null,
                    'slug' => $blog['slug'] ?? '',
                    'views' => (int)($blog['views'] ?? 0)
                ];
            }
            $count++;
        }
    }
}

// Fallback: Query langsung jika materialized view tidak tersedia
if ($total_blogs === 0 && $koneksi) {
    $count_query = "SELECT COUNT(*) as total FROM blog WHERE is_published = true";
    $count_result = pg_query($koneksi, $count_query);
    if ($count_result) {
        $count_row = pg_fetch_assoc($count_result);
        $total_blogs = (int)$count_row['total'];
        $total_pages = ceil($total_blogs / $per_page);
    }
}

if (empty($blogs) && $koneksi) {
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
}
?>
<?php include __DIR__ . '/../../template/header.php'; ?>

<link rel="stylesheet" href="../../assets/css/blog-list.css?<?php echo time(); ?>" />

<section class="pt-150 pb-50">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="section-title text-center mb-30">
                    <h2 class="mb-15 wow fadeInUp" data-wow-delay=".2s">Blog & <span>Artikel</span></h2>
                    <p class="wow fadeInUp" data-wow-delay=".4s">Artikel dan berita terkini dari Laboratorium Software Engineering</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="blog-list" class="pt-100 pb-70">
    <div class="container">

        <div class="row">
            <?php if (!empty($blogs)): ?>
                <?php foreach ($blogs as $blog): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="blog-card wow fadeInUp" data-wow-delay=".2s">
                            <div class="blog-image">
                                <?php if (!empty($blog['featured_image'])): ?>
                                    <img src="<?php echo htmlspecialchars($blog['featured_image']); ?>" alt="<?php echo htmlspecialchars($blog['title']); ?>">
                                <?php else: ?>
                                    <img src="../../assets/img/blog/blog-1-1.jpg" alt="Blog Image">
                                <?php endif; ?>
                                <div class="blog-category">
                                    <?php echo htmlspecialchars(ucfirst($blog['category'] ?? 'Uncategorized')); ?>
                                </div>
                            </div>
                            <div class="blog-content">
                                <div class="blog-meta">
                                    <span><i class="lni lni-calendar"></i> <?php echo $blog['published_at'] ? date('d M Y', strtotime($blog['published_at'])) : date('d M Y', strtotime($blog['created_at'])); ?></span>
                                    <span><i class="lni lni-eye"></i> <?php echo number_format($blog['views']); ?> views</span>
                                </div>
                                <h3 class="blog-title">
                                    <a href="<?php echo $labseHomeBase; ?>/pages/blog/blog-detail.php?slug=<?php echo urlencode($blog['slug']); ?>">
                                        <?php echo htmlspecialchars($blog['title']); ?>
                                    </a>
                                </h3>
                                <p class="blog-excerpt">
                                    <?php echo htmlspecialchars($blog['excerpt'] ?? substr(strip_tags($blog['content'] ?? ''), 0, 150) . '...'); ?>
                                </p>
                                <a href="<?php echo $labseHomeBase; ?>/pages/blog/blog-detail.php?slug=<?php echo urlencode($blog['slug']); ?>" class="read-more">
                                    Baca Selengkapnya <i class="lni lni-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <p class="text-muted">Belum ada artikel yang dipublikasi.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="row">
                <div class="col-lg-12">
                    <div class="pagination-wrapper text-center mt-50">
                        <ul class="pagination">
                            <?php if ($page > 1): ?>
                                <li><a href="?page=<?php echo $page - 1; ?>"><i class="lni lni-chevron-left"></i></a></li>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <li class="<?php echo $i === $page ? 'active' : ''; ?>">
                                    <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <li><a href="?page=<?php echo $page + 1; ?>"><i class="lni lni-chevron-right"></i></a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/../../template/footer.php'; ?>
