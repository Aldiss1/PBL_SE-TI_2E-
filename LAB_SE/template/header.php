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
?>
<!DOCTYPE html>
<html class="no-js" lang="id">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
    <title>Web Profile Laboratorium Software Engineering</title>
    <meta name="description" content="" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- ========================= CSS here ========================= -->
    <link rel="stylesheet" href="<?php echo LABSE_BASE_PATH; ?>/assets/css/bootstrap-5.0.0-alpha-2.min.css" />
    <link rel="stylesheet" href="<?php echo LABSE_BASE_PATH; ?>/assets/css/LineIcons.2.0.css"/>
    <link rel="stylesheet" href="<?php echo LABSE_BASE_PATH; ?>/assets/css/animate.css"/>
    <link rel="stylesheet" href="<?php echo LABSE_BASE_PATH; ?>/assets/css/lindy-uikit.css"/>
    <link rel="stylesheet" href="<?php echo LABSE_BASE_PATH; ?>/assets/css/home.css"/>
  </head>
  <body>

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

      <!-- ========================= header-2 start ========================= -->
      <header class="header header-2">
        <div class="navbar-area">
          <div class="container">
            <div class="row align-items-center">
              <div class="col-lg-12">
                <nav class="navbar navbar-expand-lg">
                  <a class="navbar-brand" href="<?php echo LABSE_BASE_PATH; ?>/index.php">
                    <img src="<?php echo LABSE_BASE_PATH; ?>/assets/img/logo/logo.png" alt="Logo" />
                  </a>
                  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent2" aria-controls="navbarSupportedContent2" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="toggler-icon"></span>
                    <span class="toggler-icon"></span>
                    <span class="toggler-icon"></span>
                  </button>

                  <div class="collapse navbar-collapse sub-menu-bar" id="navbarSupportedContent2">
                    <?php
                    // Ambil statistik dari database untuk menu
                    $stats = [
                      'anggota' => 0,
                      'pendaftar' => 0,
                      'blog' => 0
                    ];
                    
                    // Hitung anggota
                    $query_anggota = "SELECT COUNT(*) as total FROM anggota";
                    $result_anggota = pg_query($koneksi, $query_anggota);
                    if ($result_anggota) {
                      $row = pg_fetch_assoc($result_anggota);
                      $stats['anggota'] = (int)$row['total'];
                    }
                    
                    // Hitung pendaftar
                    $query_pendaftar = "SELECT COUNT(*) as total FROM pendaftaran";
                    $result_pendaftar = pg_query($koneksi, $query_pendaftar);
                    if ($result_pendaftar) {
                      $row = pg_fetch_assoc($result_pendaftar);
                      $stats['pendaftar'] = (int)$row['total'];
                    }
                    
                    // Hitung blog
                    $query_blog = "SELECT COUNT(*) as total FROM blog WHERE is_published = true";
                    $result_blog = pg_query($koneksi, $query_blog);
                    if ($result_blog) {
                      $row = pg_fetch_assoc($result_blog);
                      $stats['blog'] = (int)$row['total'];
                    }
                    ?>
                    <?php include __DIR__ . '/menu.php'; ?>
                    <a href="<?php echo LABSE_BASE_PATH; ?>/module/auth/login.php" class="button button-sm radius-10 d-none d-lg-flex">Admin</a>
                  </div>
                  <!-- navbar collapse -->
                </nav>
                <!-- navbar -->
              </div>
            </div>
            <!-- row -->
          </div>
          <!-- container -->
        </div>  
        <!-- navbar area -->
      </header>
      <!-- ========================= header-2 end ========================= -->

