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
?>
      <!-- ========================= hero-2 start ========================= -->
        <!-- Start of Selection -->
        <div class="row align-items-center justify-content-center">
          <div class="col-12 col-lg-10">
            <div class="row align-items-center">
              <div class="col-lg-6">
                <div class="text-center text-lg-left" style="padding: 60px 24px; margin-top: 150px; margin-bottom: 32px; padding-top: 150px;">
                  <h4 style="color: #2F80ED; font-weight: 700; margin-bottom: 22px; font-size: 2rem;">Selamat Datang</h4>
                  <h2 style="font-size: 2.8rem; font-weight: 800; color: #22243e; margin-bottom: 22px; line-height: 1.2;">
                    Web Profile Laboratorium<br>Software Engineering
                  </h2>
                  <p style="font-size: 1.15rem; color: #717791; margin-bottom: 40px;">
                    Untuk mahasiswa yang ingin mengakses Laboratorium Software Engineering<br>silahkan klik tombol di bawah
                  </p>
                  <a href="<?php echo $labseHomeBase; ?>/pages/pendaftaran/daftar.php" 
                     style="display: inline-block; font-weight: 600; color: #fff; background: #2F80ED; border-radius: 8px; padding: 12px 38px; font-size: 1.09rem; box-shadow: 0 2px 8px rgba(47,128,237,.07); text-decoration: none; transition: all 0.3s;">
                    Daftar
                  </a>
                </div>
              </div>
              <div class="col-lg-6">
                <div class="hero-mascot wow fadeInRight" data-wow-delay=".4s">
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

    </section>
    <!-- ========================= hero-section-wrapper-2 end ========================= -->

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
                       class="btn btn-primary btn-sm radius-10" 
                       style="background: #2F80ED; border-color: #2F80ED; padding: 10px 25px; font-weight: 600;">
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
<section id="focus" class="feature-section feature-style-2 pt-100 pb-70" style="background: #f8f9fa;">
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
      <div class="col-lg-4 col-md-6">
        <div class="single-feature wow fadeInUp" data-wow-delay=".2s">
          <div class="icon">
            <i class="lni lni-vector"></i>
          </div>
          <div class="content">
            <h5>Quality Assurance (QA)</h5>
            <p>Pengujian dan jaminan kualitas perangkat lunak menggunakan metodologi terkini</p>
          </div>
        </div>
      </div>
      <div class="col-lg-4 col-md-6">
        <div class="single-feature wow fadeInUp" data-wow-delay=".3s">
          <div class="icon">
            <i class="lni lni-layers"></i>
          </div>
          <div class="content">
            <h5>Human-Computer Interaction (HCI)</h5>
            <p>Desain interaksi dan pengalaman pengguna untuk aplikasi yang user-friendly</p>
          </div>
        </div>
      </div>
      <div class="col-lg-4 col-md-6">
        <div class="single-feature wow fadeInUp" data-wow-delay=".4s">
          <div class="icon">
            <i class="lni lni-code-alt"></i>
          </div>
          <div class="content">
            <h5>Machine Learning untuk Software Engineering</h5>
            <p>Penerapan ML dalam optimasi proses pengembangan perangkat lunak</p>
          </div>
        </div>
      </div>
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
            <div class="single-feature wow fadeInUp" data-wow-delay=".2s" style="background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
              <h5 style="color: #2F80ED; margin-bottom: 15px;">Visi</h5>
              <p>Menjadi pusat penelitian software engineering yang relevan dan berdaya guna bagi pengembangan teknologi informasi di Indonesia.</p>
            </div>
          </div>
          <div class="col-lg-6 mb-4">
            <div class="single-feature wow fadeInUp" data-wow-delay=".3s" style="background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
              <h5 style="color: #2F80ED; margin-bottom: 15px;">Misi</h5>
              <ul style="list-style: none; padding: 0;">
                <li style="margin-bottom: 10px;"><i class="lni lni-checkmark-circle" style="color: #27AE60; margin-right: 8px;"></i>Melakukan penelitian berkualitas di bidang perangkat lunak</li>
                <li style="margin-bottom: 10px;"><i class="lni lni-checkmark-circle" style="color: #27AE60; margin-right: 8px;"></i>Mendorong kolaborasi akademik dan industri</li>
                <li><i class="lni lni-checkmark-circle" style="color: #27AE60; margin-right: 8px;"></i>Membina mahasiswa melalui proyek penelitian</li>
              </ul>
            </div>
          </div>
        </div>
        <div class="text-center mt-4">
          <a href="<?php echo $labseHomeBase; ?>/pages/profil/visi-misi.php" class="button button-sm radius-10">Lihat Selengkapnya</a>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- ========================= Visi & Misi Preview End ========================= -->

<!-- ========================= Roadmap Preview Section ========================= -->
<section id="roadmap" class="feature-section feature-style-2 pt-100 pb-70" style="background: #f8f9fa;">
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
              <div class="single-feature wow fadeInUp" data-wow-delay="<?php echo $delay; ?>s" style="background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); height: 100%;">
                <h5 style="color: #2F80ED; margin-bottom: 10px;"><?php echo htmlspecialchars($periode['judul_fase']); ?></h5>
                <p style="color: #999; font-size: 0.9rem; margin-bottom: 15px;"><?php echo htmlspecialchars($periode['tahun_periode']); ?></p>
                <?php if (!empty($items_tugas)): ?>
                <ul style="list-style: none; padding: 0;">
                  <?php foreach ($items_tugas as $tugas): ?>
                  <li style="margin-bottom: 8px;">
                    <i class="lni lni-checkmark-circle" style="color: #27AE60; margin-right: 8px;"></i>
                    <?php echo htmlspecialchars($tugas['deskripsi_tugas']); ?>
                  </li>
                  <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <p style="color: #999; font-size: 0.9rem; font-style: italic;">Belum ada tugas yang ditambahkan untuk periode ini.</p>
                <?php endif; ?>
              </div>
            </div>
            <?php 
              $delay += 0.1;
            endforeach; ?>
          <?php else: ?>
            <div class="col-12">
              <div class="text-center" style="background: #fff; padding: 40px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                <p style="color: #717791; margin-bottom: 20px;">
                  Roadmap belum diatur di Admin Panel.
                  <br><small>Silakan tambahkan roadmap melalui <a href="<?php echo $labseHomeBase; ?>/module/auth/login.php" style="color: #2F80ED;">admin panel</a>.</small>
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
    <div class="row">
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
        <p style="color: #717791; margin-bottom: 20px;">
          <?php if (isset($total_data) && $total_data > 0): ?>
            Data ada di database (<?php echo $total_data; ?> anggota), tapi tidak sesuai filter. 
            <br><small>Silakan cek data di <a href="<?php echo $labseHomeBase; ?>/pages/profil/personil.php" style="color: #2F80ED;">halaman personil</a> atau tambahkan data melalui admin.</small>
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
<section id="blog" class="feature-section feature-style-2 pt-100 pb-70" style="background: #f8f9fa;">
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
          $blog_link = $labseHomeBase . "/pages/blog/blog-detail.php?id=" . $blog_item['id'];
          $blog_image = $blog_item['featured_image'] ? $blog_item['featured_image'] : 'assets/img/blog/blog-1-1.jpg';
          $blog_excerpt = $blog_item['excerpt'] ? htmlspecialchars(substr($blog_item['excerpt'], 0, 100)) . '...' : '';
          $blog_date = $blog_item['published_at'] ? date('d F Y', strtotime($blog_item['published_at'])) : date('d F Y', strtotime($blog_item['created_at']));
      ?>
      <div class="col-lg-4 col-md-6 mb-4">
        <div class="single-feature blog-card-item wow fadeInUp" data-wow-delay=".2s" style="background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05); transition: all 0.3s ease; height: 100%;">
          <div class="image" style="height: 200px; overflow: hidden; position: relative;">
            <img src="<?php echo htmlspecialchars($blog_image); ?>" alt="<?php echo htmlspecialchars($blog_item['title']); ?>" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
          </div>
          <div class="content" style="padding: 20px;">
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
              </span>
              <a href="<?php echo $blog_link; ?>" class="button button-sm radius-10" style="padding: 5px 15px; font-size: 12px;">Baca</a>
            </div>
          </div>
        </div>
      </div>
      <?php
        }
      } else {
      ?>
      <div class="col-12 text-center">
        <p style="color: #717791; margin-bottom: 20px;">
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

