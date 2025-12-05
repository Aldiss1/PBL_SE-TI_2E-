<?php
if (!defined('LABSE_BASE_PATH')) {
  $documentRoot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : '';
  $appRoot = realpath(__DIR__ . '/..');

  if ($documentRoot) {
    $documentRoot = str_replace('\\', '/', $documentRoot);
  }
  if ($appRoot) {
    $appRoot = str_replace('\\', '/', $appRoot);
  }

  $basePath = '';
  if ($documentRoot && $appRoot && strpos($appRoot, $documentRoot) === 0) {
    $basePath = substr($appRoot, strlen($documentRoot));
    $basePath = '/' . trim($basePath, '/');
    if ($basePath === '/') {
      $basePath = '';
    }
  }

  define('LABSE_BASE_PATH', $basePath);
}

// Pastikan koneksi database tersedia untuk statistik navbar
if (!isset($koneksi)) {
    $koneksi_file = __DIR__ . '/../config/koneksi.php';
    if (file_exists($koneksi_file)) {
        require_once $koneksi_file;
    }
}
?>
<!DOCTYPE html>
<html class="no-js" lang="id">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
    <title>Web Profile Laboratorium Software Engineering</title>
    <meta name="description" content="" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <?php
    // Ambil statistik untuk navbar (sebelum CSS dimuat)
    $stats = ['anggota' => 0, 'pendaftar' => 0, 'blog' => 0];
    
    if (isset($koneksi) && $koneksi) {
        if (file_exists(__DIR__ . '/../fungsi/materialized_views_complete.php')) {
            require_once __DIR__ . '/../fungsi/materialized_views_complete.php';
            $stats_mv = get_stats_complete();
            if ($stats_mv) {
                $stats['anggota'] = (int)($stats_mv['total_anggota'] ?? 0);
                $stats['pendaftar'] = (int)($stats_mv['total_pendaftar'] ?? 0);
                $stats['blog'] = (int)($stats_mv['total_blog_published'] ?? 0);
            }
        }
        
        // Fallback: Query langsung jika materialized view tidak tersedia
        if ($stats['anggota'] === 0) {
            $query_anggota = "SELECT COUNT(*) as total FROM anggota";
            $result_anggota = pg_query($koneksi, $query_anggota);
            if ($result_anggota) {
                $row = pg_fetch_assoc($result_anggota);
                $stats['anggota'] = (int)$row['total'];
            }
            
            $query_pendaftar = "SELECT COUNT(*) as total FROM pendaftaran";
            $result_pendaftar = pg_query($koneksi, $query_pendaftar);
            if ($result_pendaftar) {
                $row = pg_fetch_assoc($result_pendaftar);
                $stats['pendaftar'] = (int)$row['total'];
            }
            
            $query_blog = "SELECT COUNT(*) as total FROM blog WHERE is_published = true";
            $result_blog = pg_query($koneksi, $query_blog);
            if ($result_blog) {
                $row = pg_fetch_assoc($result_blog);
                $stats['blog'] = (int)$row['total'];
            }
        }
    }
    ?>

    <!-- ========================= CSS here ========================= -->
    <link rel="stylesheet" href="<?php echo LABSE_BASE_PATH; ?>/assets/css/bootstrap-5.0.0-alpha-2.min.css" />
    <link rel="stylesheet" href="<?php echo LABSE_BASE_PATH; ?>/assets/css/LineIcons.2.0.css"/>
    <link rel="stylesheet" href="<?php echo LABSE_BASE_PATH; ?>/assets/css/animate.css"/>
    <link rel="stylesheet" href="<?php echo LABSE_BASE_PATH; ?>/assets/css/lindy-uikit.css"/>
    <link rel="stylesheet" href="<?php echo LABSE_BASE_PATH; ?>/assets/css/home.css"/>
    <link rel="stylesheet" href="<?php echo LABSE_BASE_PATH; ?>/assets/css/fixed-navbar.css"/>
    <link rel="stylesheet" href="<?php echo LABSE_BASE_PATH; ?>/assets/css/dosen-pengampu.css"/>
    <link rel="stylesheet" href="<?php echo LABSE_BASE_PATH; ?>/assets/css/landing-page.css"/>
    <link rel="stylesheet" href="<?php echo LABSE_BASE_PATH; ?>/assets/css/footer-modern.css"/>
    
    <?php 
    // Deteksi halaman dan inject CSS styling
    $current_page = $_SERVER['REQUEST_URI'] ?? '';
    
    // Deteksi halaman tentang-kami
    if (strpos($current_page, 'tentang-kami') !== false || (isset($is_tentang_kami_page) && $is_tentang_kami_page)):
    ?>
    <!-- Load tentang-kami.css SETELAH semua CSS lain untuk override -->
    <link rel="stylesheet" href="<?php echo LABSE_BASE_PATH; ?>/assets/css/tentang-kami.css?<?php echo time(); ?>" />
    <?php endif; ?>
    
    <?php 
    // Deteksi halaman visi-misi
    if (strpos($current_page, 'visi-misi') !== false):
    ?>
    <!-- Load visi-misi.css SETELAH semua CSS lain untuk override -->
    <link rel="stylesheet" href="<?php echo LABSE_BASE_PATH; ?>/assets/css/visi-misi.css?<?php echo time(); ?>" />
    <?php endif; ?>
    
    <?php 
    // Deteksi halaman roadmap
    if (strpos($current_page, 'roadmap') !== false):
    ?>
    <!-- Load roadmap.css SETELAH semua CSS lain untuk override -->
    <link rel="stylesheet" href="<?php echo LABSE_BASE_PATH; ?>/assets/css/roadmap.css?<?php echo time(); ?>" />
    <?php endif; ?>
    
    <?php 
    // Deteksi halaman personil dan inject CSS styling
    if (strpos($current_page, 'personil') !== false && strpos($current_page, 'detail-personil') === false):
    ?>
    <!-- Load personil.css SETELAH semua CSS lain untuk override -->
    <link rel="stylesheet" href="<?php echo LABSE_BASE_PATH; ?>/assets/css/personil.css?<?php echo time(); ?>" />
    <?php endif; ?>
    
    <?php 
    // Deteksi halaman detail-personil
    if (strpos($current_page, 'detail-personil') !== false):
    ?>
    <!-- Load detail-personil.css SETELAH semua CSS lain untuk override -->
    <link rel="stylesheet" href="<?php echo LABSE_BASE_PATH; ?>/assets/css/detail-personil.css?<?php echo time(); ?>" />
    <?php endif; ?>
    
    <?php 
    // Deteksi halaman penelitian-dan-publikasi
    if (strpos($current_page, 'penelitian-dan-publikasi') !== false || (isset($is_penelitian_publikasi_page) && $is_penelitian_publikasi_page)):
    ?>
    <!-- Load penelitian-publikasi.css SETELAH semua CSS lain untuk override -->
    <link rel="stylesheet" href="<?php echo LABSE_BASE_PATH; ?>/assets/css/penelitian-publikasi.css?<?php echo time(); ?>" />
    <?php endif; ?>
    
  </head>
  <body>

    <!-- ========================= NAVBAR BARU - FIXED DI ATAS SEMUA ========================= -->
    <nav id="fixed-navbar" class="fixed-navbar-new">
      <div class="navbar-container-new">
        <div class="container">
          <div class="navbar-content-new">
            <a class="navbar-brand-new" href="<?php echo LABSE_BASE_PATH; ?>/index.php">
              <img src="<?php echo LABSE_BASE_PATH; ?>/assets/img/logo/logo.png" alt="Logo" />
            </a>
            <button class="navbar-toggler-new" type="button" id="navbarToggleNew">
              <span></span>
              <span></span>
              <span></span>
            </button>
            <div class="navbar-menu-new" id="navbarMenuNew">
              <?php include __DIR__ . '/menu.php'; ?>
              <a href="<?php echo LABSE_BASE_PATH; ?>/module/auth/login.php" class="btn-admin-new">Admin</a>
            </div>
          </div>
        </div>
      </div>
    </nav>
    <!-- ========================= NAVBAR BARU END ========================= -->

    <!-- ========================= preloader start ========================= -->
    <div class="preloader">
      <div class="loader">
        <div class="spinner">
          <div class="spinner-container">
            <div class="spinner-rotator">
              <div class="spinner-left">
                <div class="spinner-circle"></div>
              </div>
              <div class="spinner-right">
                <div class="spinner-circle"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- ========================= preloader end ========================= -->

    <!-- ========================= hero-section-wrapper-2 start ========================= -->
    <section id="home" class="hero-section-wrapper-2">

