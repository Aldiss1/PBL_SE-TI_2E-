<?php $labseHomeBase = defined('LABSE_BASE_PATH') ? LABSE_BASE_PATH : ''; 
if (!function_exists('ambil_konten')) {
    function ambil_konten($koneksi, $key) {
        if (!$koneksi) {
            return ['title' => 'Error', 'content' => 'Koneksi database gagal.'];
        }
        
        // Menggunakan pg_query_params untuk keamanan
        $query = "SELECT title, content FROM konten_page WHERE page_key = $1";
        $result = pg_query_params($koneksi, $query, array($key));
        
        if ($result && pg_num_rows($result) > 0) {
            return pg_fetch_assoc($result);
        }
        return ['title' => '', 'content' => ''];
    }
}

// Fungsi untuk mengambil periode roadmap
if (!function_exists('ambil_periode_roadmap')) {
    function ambil_periode_roadmap($koneksi) {
        if (!$koneksi) {
            return [];
        }
        $query = "SELECT id, tahun_periode, judul_fase FROM roadmap_periode ORDER BY urutan ASC";
        $result = pg_query($koneksi, $query);
        
        $periode = [];
        if ($result) {
            while ($row = pg_fetch_assoc($result)) {
                $periode[] = $row;
            }
        }
        return $periode;
    }
}

// Fungsi untuk mengambil item roadmap berdasarkan ID periode
if (!function_exists('ambil_item_roadmap')) {
    function ambil_item_roadmap($koneksi, $id_periode) {
        if (!$koneksi) {
            return [];
        }
        $query = "SELECT deskripsi_tugas FROM roadmap_item WHERE id_periode = $1 ORDER BY urutan ASC LIMIT 3";
        $result = pg_query_params($koneksi, $query, array($id_periode));
        
        $items = [];
        if ($result !== false) {
            while ($row = pg_fetch_assoc($result)) {
                $items[] = $row;
            }
        }
        return $items;
    }
}

// Ambil data konten 'Tentang Kami' dari database
$tentang_kami_data = ambil_konten($koneksi, 'tentang_kami');
$tentang_kami_judul = htmlspecialchars($tentang_kami_data['title'] ?? 'Tentang Kami');
// Gunakan substr untuk preview singkat
$tentang_kami_preview = htmlspecialchars(substr($tentang_kami_data['content'] ?? '', 0, 15000)) . '...';

// Ambil data roadmap dari database (maksimal 2 periode terbaru untuk preview)
$semua_periode = ambil_periode_roadmap($koneksi);
$periode_preview = array_slice($semua_periode, 0, 2); // Ambil 2 periode pertama

// Fungsi untuk mengambil item misi
if (!function_exists('ambil_misi_items')) {
    function ambil_misi_items($koneksi, $limit = 3) {
        if (!$koneksi) {
            return [];
        }
        $query = "SELECT content FROM konten_page WHERE page_key LIKE 'misi_item_%' ORDER BY page_key ASC LIMIT $1";
        $result = pg_query_params($koneksi, $query, array($limit));
        
        $items = [];
        if ($result !== false) {
            while ($row = pg_fetch_assoc($result)) {
                $items[] = $row;
            }
        }
        return $items;
    }
}

// Ambil data Visi
$visi_data = ambil_konten($koneksi, 'visi_deskripsi');
$visi_content = htmlspecialchars($visi_data['content'] ?? 'Menjadi pusat penelitian software engineering yang relevan dan berdaya guna bagi pengembangan teknologi informasi di Indonesia.');

// Ambil data Misi (maksimal 3 untuk preview)
$misi_items = ambil_misi_items($koneksi, 3);

// Fungsi untuk mengambil data Focus & Scope
if (!function_exists('ambil_focus_scope')) {
    function ambil_focus_scope($koneksi) {
        if (!$koneksi) {
            return [];
        }
        $focus_keys = ['focus_qa', 'focus_hci', 'focus_ml'];
        $focus_data = [];
        
        foreach ($focus_keys as $key) {
            $query = "SELECT page_key, title, content FROM konten_page WHERE page_key = $1";
            $result = pg_query_params($koneksi, $query, array($key));
            if ($result && pg_num_rows($result) > 0) {
                $row = pg_fetch_assoc($result);
                $parsed = json_decode($row['content'], true);
                if ($parsed) {
                    $focus_data[$key] = [
                        'title' => $parsed['title'] ?? $row['title'],
                        'content' => $parsed['content'] ?? '',
                        'icon' => $parsed['icon'] ?? 'lni-vector'
                    ];
                } else {
                    // Fallback jika bukan JSON
                    $focus_data[$key] = [
                        'title' => $row['title'],
                        'content' => $row['content'],
                        'icon' => 'lni-vector'
                    ];
                }
            }
        }
        return $focus_data;
    }
}

// Ambil data Focus & Scope
$focus_scope = ambil_focus_scope($koneksi);

// Default values jika belum ada di database
$focus_defaults = [
    'focus_qa' => [
        'title' => 'Quality Assurance (QA)',
        'content' => 'Pengujian dan jaminan kualitas perangkat lunak menggunakan metodologi terkini',
        'icon' => 'lni-vector'
    ],
    'focus_hci' => [
        'title' => 'Human-Computer Interaction (HCI)',
        'content' => 'Desain interaksi dan pengalaman pengguna untuk aplikasi yang user-friendly',
        'icon' => 'lni-layers'
    ],
    'focus_ml' => [
        'title' => 'Machine Learning untuk Software Engineering',
        'content' => 'Penerapan ML dalam optimasi proses pengembangan perangkat lunak',
        'icon' => 'lni-code-alt'
    ]
];

// Merge dengan default jika belum ada
foreach ($focus_defaults as $key => $default) {
    if (!isset($focus_scope[$key])) {
        $focus_scope[$key] = $default;
    }
}
?>
      <!-- ========================= hero-2 start ========================= -->
        <!-- Start of Selection -->
        <div class="row align-items-center justify-content-center">
          <div class="col-12 col-lg-10">
            <div class="row align-items-center">
              <div class="col-lg-6 d-flex align-items-center">
                <div class="text-center w-100">
                  <h4 style="color: #2F80ED !important;">Selamat Datang</h4>
                  <h2 style="background: linear-gradient(135deg, #2F80ED 0%, #1E5FCC 100%) !important; -webkit-background-clip: text !important; -webkit-text-fill-color: transparent !important; background-clip: text !important; color: transparent !important;">
                    Web Profile Laboratorium<br>Software Engineering
                  </h2>
                </div>
              </div>
              <div class="col-lg-6 d-flex align-items-center">
                <div class="hero-mascot wow fadeInRight w-100" data-wow-delay=".4s">
                  <div class="mascot-container">
                    <div class="mascot-bg-circle"></div>
                    <img src="assets/img/LAB SE_Outline.png" class="mascot-illustration" alt="Lab SE Mascot">
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- ========================= hero-2 end ========================= -->


<!-- ========================= feature style-2 start ========================= -->
<?php $labseHomeBase = defined('LABSE_BASE_PATH') ? LABSE_BASE_PATH : ''; ?>
<section id="services" class="feature-section feature-style-2 pt-100 pb-70">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="row justify-content-center">
          <div class="col-xl-10 col-lg-12 col-md-12">
            <div class="section-surface mb-60 text-center">
              <div class="section-title mb-0">
                                <h3 class="mb-15 wow fadeInUp gradient-text" data-wow-delay=".2s">
                    <?php echo $tentang_kami_judul; ?>
                </h3>
                
                                <p class="wow fadeInUp" data-wow-delay=".4s">
                    <?php echo $tentang_kami_preview; ?>
                </p>

                <div class="mt-4 wow fadeInUp" data-wow-delay=".5s">
                    <a href="<?php echo $labseHomeBase; ?>/pages/profil/tentang-kami.php" 
                       class="btn btn-primary btn-sm radius-10 btn-primary-custom">
                        Selengkapnya <i class="lni lni-arrow-right"></i>
                    </a>
                </div>
                
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>


<!-- ========================= about style-3 end ========================= -->

<!-- ========================= Focus & Scope Section ========================= -->
<section id="focus" class="feature-section feature-style-2 pt-100 pb-70">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="section-title text-center mb-60">
          <h3 class="mb-15 wow fadeInUp gradient-text" data-wow-delay=".2s">Focus & Scope</h3>
          <p class="wow fadeInUp" data-wow-delay=".4s">Fokus penelitian dan pengembangan di Laboratorium Software Engineering</p>
        </div>
      </div>
    </div>
    <div class="row">
      <?php 
      $focus_items = [
        ['key' => 'focus_qa', 'delay' => 0.2],
        ['key' => 'focus_hci', 'delay' => 0.3],
        ['key' => 'focus_ml', 'delay' => 0.4]
      ];
      foreach ($focus_items as $item): 
        $focus = $focus_scope[$item['key']] ?? $focus_defaults[$item['key']];
      ?>
      <div class="col-lg-4 col-md-6">
        <div class="single-feature wow fadeInUp" data-wow-delay="<?php echo $item['delay']; ?>s">
          <div class="icon">
            <i class="lni <?php echo htmlspecialchars($focus['icon']); ?>"></i>
          </div>
          <div class="content">
            <h5><?php echo htmlspecialchars($focus['title']); ?></h5>
            <p><?php echo htmlspecialchars($focus['content']); ?></p>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<!-- ========================= Focus & Scope End ========================= -->

<!-- ========================= Visi & Misi Preview Section ========================= -->
<section id="visi" class="feature-section feature-style-2 pt-100 pb-70">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div class="section-title text-center mb-60">
          <h3 class="mb-15 wow fadeInUp gradient-text" data-wow-delay=".2s">Visi & Misi</h3>
        </div>
        <div class="row">
          <div class="col-lg-6 mb-4">
            <div class="single-feature wow fadeInUp" data-wow-delay=".2s">
              <h5>Visi</h5>
              <p><?php echo nl2br($visi_content); ?></p>
            </div>
          </div>
          <div class="col-lg-6 mb-4">
            <div class="single-feature wow fadeInUp" data-wow-delay=".3s">
              <h5>Misi</h5>
              <?php if (!empty($misi_items)): ?>
                <ul>
                  <?php foreach ($misi_items as $misi): ?>
                  <li>
                    <i class="lni lni-checkmark-circle"></i>
                    <?php echo htmlspecialchars($misi['content']); ?>
                  </li>
                  <?php endforeach; ?>
                </ul>
              <?php else: ?>
                <p class="empty-state">Konten misi belum diatur di Admin Panel.</p>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <div class="text-center mt-4">
          <a href="<?php echo $labseHomeBase; ?>/pages/profil/visi-misi.php" class="button button-sm radius-10">
            <?php if (!empty($misi_items) && count($misi_items) >= 3): ?>
              Lihat Selengkapnya
            <?php else: ?>
              Lihat Selengkapnya
            <?php endif; ?>
          </a>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- ========================= Visi & Misi Preview End ========================= -->

<!-- ========================= Roadmap Preview Section ========================= -->
<section id="roadmap" class="feature-section feature-style-2 pt-100 pb-70">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div class="section-title text-center mb-60">
          <h3 class="mb-15 wow fadeInUp gradient-text" data-wow-delay=".2s">Roadmap</h3>
          <p class="wow fadeInUp" data-wow-delay=".4s">Perjalanan dan Rencana Pengembangan Laboratorium</p>
        </div>
        <div class="row">
          <?php if (!empty($periode_preview)): ?>
            <?php 
            $delay = 0.2;
            foreach ($periode_preview as $index => $periode): 
              $items_tugas = ambil_item_roadmap($koneksi, $periode['id']);
            ?>
            <div class="col-lg-6 mb-4">
              <div class="single-feature wow fadeInUp" data-wow-delay="<?php echo $delay; ?>s">
                <h5><?php echo htmlspecialchars($periode['judul_fase']); ?></h5>
                <p><?php echo htmlspecialchars($periode['tahun_periode']); ?></p>
                <?php if (!empty($items_tugas)): ?>
                <ul>
                  <?php foreach ($items_tugas as $tugas): ?>
                  <li>
                    <i class="lni lni-checkmark-circle"></i>
                    <?php echo htmlspecialchars($tugas['deskripsi_tugas']); ?>
                  </li>
                  <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <p class="empty-state">Belum ada tugas yang ditambahkan untuk periode ini.</p>
                <?php endif; ?>
              </div>
            </div>
            <?php 
              $delay += 0.1;
            endforeach; ?>
          <?php else: ?>
            <div class="col-12">
              <div class="text-center empty-state-box">
                <p class="empty-state-message">
                  Roadmap belum diatur di Admin Panel.
                  <br><small>Silakan tambahkan roadmap melalui <a href="<?php echo $labseHomeBase; ?>/module/auth/login.php">admin panel</a>.</small>
                </p>
              </div>
            </div>
          <?php endif; ?>
        </div>
        <?php if (!empty($semua_periode) && count($semua_periode) > 2): ?>
        <div class="text-center mt-4">
          <a href="<?php echo $labseHomeBase; ?>/pages/profil/roadmap.php" class="button button-sm radius-10">Lihat Selengkapnya (<?php echo count($semua_periode); ?> Periode)</a>
        </div>
        <?php elseif (!empty($semua_periode)): ?>
        <div class="text-center mt-4">
          <a href="<?php echo $labseHomeBase; ?>/pages/profil/roadmap.php" class="button button-sm radius-10">Lihat Selengkapnya</a>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
<!-- ========================= Roadmap Preview End ========================= -->

<!-- ========================= Galeri Dosen Section ========================= -->
<section id="galeri-dosen" class="team-style-1 pt-100 pb-70">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="section-title text-center mb-60">
          <h3 class="mb-15 wow fadeInUp gradient-text" data-wow-delay=".2s">Dosen Pengampu</h3>
          <p class="wow fadeInUp" data-wow-delay=".4s">Tim dosen yang membimbing dan mengembangkan Laboratorium Software Engineering</p>
        </div>
      </div>
    </div>
    <div class="row justify-content-center">
      <?php
      // Cek jumlah data dari materialized view (lebih cepat!)
      $total_data = 0;
      if (file_exists(__DIR__ . '/../fungsi/materialized_views_complete.php')) {
        require_once __DIR__ . '/../fungsi/materialized_views_complete.php';
        $stats_mv = get_stats_complete();
        if ($stats_mv) {
          $total_data = (int)($stats_mv['total_anggota'] ?? 0);
        }
      }
      
      // Fallback: Query langsung jika materialized view tidak tersedia
      if ($total_data === 0 && $koneksi) {
        $count_query = "SELECT COUNT(*) as total FROM anggota WHERE jabatan IS NOT NULL";
        $count_result = pg_query($koneksi, $count_query);
        if ($count_result) {
          $count_row = pg_fetch_assoc($count_result);
          $total_data = (int)$count_row['total'];
        }
      }
      
      // Query yang lebih inklusif untuk mengambil semua anggota (dosen, ketua, dll)
      $query_dosen = "SELECT * FROM anggota 
        WHERE jabatan IS NOT NULL 
        AND (jabatan LIKE '%Dosen%' OR jabatan LIKE '%Ketua%' OR jabatan LIKE '%Koordinator%' OR jabatan LIKE '%Pembimbing%')
        ORDER BY 
        CASE jabatan
          WHEN 'Ketua Lab' THEN 1
          WHEN 'Wakil Ketua Lab' THEN 2
          WHEN 'Koordinator Penelitian' THEN 3
          WHEN 'Dosen Pembimbing' THEN 4
          WHEN 'Dosen Pengampu' THEN 5
          ELSE 6
        END, id ASC 
        LIMIT 6";
      $result_dosen = pg_query($koneksi, $query_dosen);
      
      if ($result_dosen && pg_num_rows($result_dosen) > 0) {
        while ($dosen = pg_fetch_assoc($result_dosen)) {
      ?>
      <div class="col-xl-4 col-lg-4 col-md-6">
        <div class="single-team wow fadeInUp" data-wow-delay=".2s">
          <div class="image">
            <img src="<?php echo isset($dosen['foto']) && $dosen['foto'] ? htmlspecialchars($dosen['foto']) : 'https://via.placeholder.com/300'; ?>" alt="<?php echo htmlspecialchars($dosen['nama']); ?>">
          </div>
          <div class="info">
            <h6><?php echo htmlspecialchars($dosen['nama']); ?></h6>
            <p><?php echo htmlspecialchars($dosen['jabatan'] ?? 'Dosen'); ?></p>
            <a href="<?php echo $labseHomeBase; ?>/pages/profil/detail-personil.php?id=<?php echo $dosen['id']; ?>" class="button button-sm radius-10 border-button mt-20">Lihat Profil</a>
          </div>
        </div>
      </div>
      <?php
        }
      } else {
      ?>
      <div class="col-12 text-center">
        <p class="empty-state-message">
          <?php if (isset($total_data) && $total_data > 0): ?>
            Data ada di database (<?php echo $total_data; ?> anggota), tapi tidak sesuai filter. 
            <br><small>Silakan cek data di <a href="<?php echo $labseHomeBase; ?>/pages/profil/personil.php">halaman personil</a> atau tambahkan data melalui admin.</small>
          <?php else: ?>
            Belum ada data anggota di database. 
            <br><small>Silakan jalankan file <code>database.sql</code> bagian INSERT anggota, atau tambahkan data melalui admin.</small>
          <?php endif; ?>
        </p>
        <a href="<?php echo $labseHomeBase; ?>/pages/profil/personil.php" class="button button-sm radius-10 mt-3">Lihat Semua Personil</a>
      </div>
      <?php
      }
      ?>
    </div>
  </div>
</section>
<!-- ========================= Galeri Dosen End ========================= -->

<!-- ========================= Blog Section ========================= -->
<section id="blog" class="feature-section feature-style-2 pt-100 pb-70">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="section-title text-center mb-60">
          <h3 class="mb-15 wow fadeInUp gradient-text" data-wow-delay=".2s">Blog & Artikel</h3>
          <p class="wow fadeInUp" data-wow-delay=".4s">Artikel dan berita terkini dari Laboratorium Software Engineering</p>
        </div>
      </div>
    </div>
    <div class="row">
      <?php
      // Hitung total blog yang published dari materialized view (lebih cepat!)
      $total_blog_count = 0;
      if (file_exists(__DIR__ . '/../fungsi/materialized_views_complete.php')) {
        require_once __DIR__ . '/../fungsi/materialized_views_complete.php';
        $stats_mv = get_stats_complete();
        if ($stats_mv) {
          $total_blog_count = (int)($stats_mv['total_blog_published'] ?? 0);
        }
      }
      
      // Fallback: Query langsung jika materialized view tidak tersedia
      if ($total_blog_count === 0 && $koneksi) {
        $count_query = "SELECT COUNT(*) as total FROM blog WHERE is_published = true";
        $count_result = pg_query($koneksi, $count_query);
        if ($count_result) {
          $count_row = pg_fetch_assoc($count_result);
          $total_blog_count = (int)$count_row['total'];
        }
      }
      
      // Ambil blog yang sudah published (maksimal 6)
      $query_blog = "SELECT id, title, excerpt, featured_image, category, published_at, created_at, slug, views 
                     FROM blog 
                     WHERE is_published = true 
                     ORDER BY published_at DESC, created_at DESC 
                     LIMIT 6";
      $result_blog = pg_query($koneksi, $query_blog);
      
      if ($result_blog && pg_num_rows($result_blog) > 0) {
        while ($blog_item = pg_fetch_assoc($result_blog)) {
          // Gunakan slug jika ada, fallback ke id
          // Path relatif dari template/home.php ke pages/blog/blog-detail.php
          if (!empty($blog_item['slug'])) {
            $blog_link = ($labseHomeBase ? rtrim($labseHomeBase, '/') . '/' : '') . 'pages/blog/blog-detail.php?slug=' . urlencode($blog_item['slug']);
          } else {
            $blog_link = ($labseHomeBase ? rtrim($labseHomeBase, '/') . '/' : '') . 'pages/blog/blog-detail.php?id=' . (int)$blog_item['id'];
          }
          // Handle path gambar
          $featured_image_raw = $blog_item['featured_image'] ?? '';
          $blog_image = '';
          if (!empty($featured_image_raw)) {
              $is_abs = preg_match('/^https?:\/\//', $featured_image_raw) || (strlen($featured_image_raw) > 0 && $featured_image_raw[0] === '/');
              if ($is_abs) {
                  $blog_image = $featured_image_raw;
              } else {
                  // Jika path dimulai dengan ../../, hapus karena kita di root
                  if (strpos($featured_image_raw, '../../') === 0) {
                      $blog_image = ($labseHomeBase ? rtrim($labseHomeBase, '/') . '/' : '') . substr($featured_image_raw, 6); // Hapus '../../'
                  } else {
                      // Path relatif dari root
                      $blog_image = ($labseHomeBase ? rtrim($labseHomeBase, '/') . '/' : '') . ltrim($featured_image_raw, '/');
                  }
              }
          } else {
              $blog_image = ($labseHomeBase ? rtrim($labseHomeBase, '/') . '/' : '') . 'assets/img/blog/blog-1-1.jpg';
          }
          $blog_excerpt = $blog_item['excerpt'] ? htmlspecialchars(substr($blog_item['excerpt'], 0, 100)) . '...' : '';
          $blog_date = $blog_item['published_at'] ? date('d F Y', strtotime($blog_item['published_at'])) : date('d F Y', strtotime($blog_item['created_at']));
      ?>
      <div class="col-lg-4 col-md-6 mb-4">
        <div class="single-feature blog-card-item wow fadeInUp" data-wow-delay=".2s">
          <div class="image">
            <img src="<?php echo htmlspecialchars($blog_image); ?>" 
                 alt="<?php echo htmlspecialchars($blog_item['title']); ?>"
                 onerror="this.src='<?php echo ($labseHomeBase ? rtrim($labseHomeBase, '/') . '/' : '') . 'assets/img/blog/blog-1-1.jpg'; ?>';">
          </div>
          <div class="content">
            <?php if ($blog_item['category']): ?>
            <span class="category-badge"><?php echo htmlspecialchars($blog_item['category']); ?></span>
            <?php endif; ?>
            <h5>
              <a href="<?php echo $blog_link; ?>">
                <?php echo htmlspecialchars($blog_item['title']); ?>
              </a>
            </h5>
            <?php if ($blog_excerpt): ?>
            <p>
              <?php echo $blog_excerpt; ?>
            </p>
            <?php endif; ?>
            <div class="blog-meta">
              <span>
                <i class="lni lni-calendar"></i> <?php echo $blog_date; ?>
              </span>
              <a href="<?php echo $blog_link; ?>" class="button button-sm radius-10">Baca</a>
            </div>
          </div>
        </div>
      </div>
      <?php
        }
      } else {
      ?>
      <div class="col-12 text-center">
        <p class="empty-state-message">
          Belum ada artikel blog yang dipublikasikan.
          <br><small>Silakan tambahkan blog melalui admin panel dan pastikan statusnya "Published".</small>
        </p>
      </div>
      <?php
      }
      ?>
    </div>
    <?php if ($total_blog_count > 6): ?>
    <div class="row mt-4">
      <div class="col-12 text-center">
        <a href="<?php echo $labseHomeBase; ?>/pages/blog/blog-list.php" class="button radius-10">Lihat Semua Artikel (<?php echo $total_blog_count; ?>)</a>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>
<!-- ========================= Blog Section End ========================= -->

<!-- ========================= Contact Section ========================= -->
<section id="contact" class="contact-section contact-style-6 pt-150 pb-100">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="section-title text-center mb-60">
          <h2 class="mb-25 wow fadeInUp" data-wow-delay=".2s">Bergabung dengan Kami</h2>
          <p class="wow fadeInUp" data-wow-delay=".4s">Daftarkan diri Anda untuk menjadi bagian dari Laboratorium Software Engineering</p>
        </div>
        <div class="contact-form-wrapper wow fadeInUp" data-wow-delay=".6s">
          <a href="<?php echo $labseHomeBase; ?>/pages/pendaftaran/daftar.php" class="button radius-10 w-100 text-center" style="display: block; padding: 15px; font-size: 1.1rem;">Formulir Pendaftaran</a>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- ========================= Contact End ========================= -->

