<!DOCTYPE html>
<html class="no-js" lang="id">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
    <title>Web Profile Laboratorium Software Engineering</title>
    <meta name="description" content="" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- ========================= CSS here ========================= -->
    <link rel="stylesheet" href="assets/css/bootstrap-5.0.0-alpha-2.min.css" />
    <link rel="stylesheet" href="assets/css/LineIcons.2.0.css"/>
    <link rel="stylesheet" href="assets/css/animate.css"/>
    <link rel="stylesheet" href="assets/css/lindy-uikit.css"/>
    <style>
      /* Homepage hero overrides */
      .hero-section-wrapper-2 .hero-style-2::after { display: none !important; }
      .hero-section-wrapper-2 .hero-style-2 .hero-content-wrapper { padding: 120px 0 60px !important; }
      /* Remove big white card around hero */
      .hero-section-wrapper-2 .hero-style-2 { background: transparent !important; border: 0 !important; box-shadow: none !important; }

      /* Subtle page background */
      body {
        background: linear-gradient(180deg, #f8fbff 0%, #ffffff 50%, #f8fbff 100%);
        overflow-x: hidden;
        overflow-y: auto;
        height: auto;
        min-height: 100vh;
      }
      
      html {
        overflow-x: hidden;
        overflow-y: auto;
        height: auto;
      }
      
      /* Fix hero section untuk tidak membatasi scroll */
      .hero-section-wrapper-2 {
        min-height: auto !important;
        height: auto !important;
        overflow: visible !important;
        position: relative !important;
      }
      
      .hero-section-wrapper-2 .hero-style-2 {
        min-height: auto !important;
        height: auto !important;
        overflow: visible !important;
        position: relative !important;
      }
      
      /* Pastikan semua section bisa di-scroll dan tidak menyembunyikan navbar */
      section {
        overflow: visible !important;
        position: relative;
        z-index: 1;
      }
      
      /* Pastikan navbar selalu di atas semua section */
      section,
      #services,
      #focus,
      #visi,
      #roadmap,
      #galeri-dosen,
      #contact {
        z-index: 1 !important;
        position: relative !important;
      }
      
      /* Navbar HARUS selalu di atas - SANGAT TINGGI */
      .header,
      .header.header-2,
      .hero-section-wrapper-2 .header {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        width: 100% !important;
        z-index: 99999 !important;
        background: #fff !important;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1) !important;
      }
      
      .header.sticky,
      .header.header-2.sticky,
      .hero-section-wrapper-2 .header.sticky {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        width: 100% !important;
        z-index: 99999 !important;
        background: #fff !important;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1) !important;
      }
      
      /* Navbar area juga harus fixed */
      .header .navbar-area,
      .header.header-2 .navbar-area,
      .hero-section-wrapper-2 .header .navbar-area {
        position: relative !important;
        z-index: 1 !important;
      }
      
      /* Pastikan navbar tidak pernah tersembunyi */
      .navbar,
      .navbar-area,
      .header .navbar,
      .header .navbar-area {
        position: relative !important;
        z-index: 1 !important;
      }
      
      /* Pastikan galeri dosen tidak konflik dengan navbar */
      #galeri-dosen {
        position: relative;
        z-index: 1;
      }
      
      /* Override semua kemungkinan yang menyembunyikan navbar */
      .header[style*="display: none"],
      .header[style*="visibility: hidden"],
      .header[style*="opacity: 0"] {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
      }

      /* ========================= Mascot Styling ========================= */
      .hero-mascot {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
        margin-top: 100px;
      }

      .mascot-container {
        position: relative;
        width: 100%;
        max-width: 500px;
        height: auto;
        display: flex;
        align-items: center;
        justify-content: center;
      }

      .mascot-bg-circle {
        position: absolute;
        width: 100%;
        max-width: 450px;
        height: 450px;
        background: linear-gradient(135deg, #2F80ED 0%, #6C63FF 50%, #2F80ED 100%);
        border-radius: 50%;
        opacity: 0.1;
        animation: pulse 3s ease-in-out infinite;
        z-index: 1;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
      }

      .mascot-bg-circle::before {
        content: '';
        position: absolute;
        width: 120%;
        height: 120%;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: linear-gradient(135deg, rgba(47, 128, 237, 0.15) 0%, rgba(108, 99, 255, 0.15) 100%);
        border-radius: 50%;
        animation: pulse 3s ease-in-out infinite 0.5s;
      }

      .mascot-bg-circle::after {
        content: '';
        position: absolute;
        width: 140%;
        height: 140%;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: linear-gradient(135deg, rgba(47, 128, 237, 0.08) 0%, rgba(108, 99, 255, 0.08) 100%);
        border-radius: 50%;
        animation: pulse 3s ease-in-out infinite 1s;
      }

      @keyframes pulse {
        0%, 100% {
          transform: translate(-50%, -50%) scale(1);
          opacity: 0.1;
        }
        50% {
          transform: translate(-50%, -50%) scale(1.05);
          opacity: 0.15;
        }
      }

      .mascot-illustration {
        position: relative;
        z-index: 3;
        width: 100%;
        max-width: 400px;
        height: auto;
        filter: drop-shadow(0 10px 30px rgba(47, 128, 237, 0.2));
        animation: float 3s ease-in-out infinite;
        transition: all 0.3s ease;
      }

      .mascot-illustration:hover {
        transform: translateY(-10px) scale(1.05);
        filter: drop-shadow(0 15px 40px rgba(47, 128, 237, 0.3));
      }

      @keyframes float {
        0%, 100% {
          transform: translateY(0px);
        }
        50% {
          transform: translateY(-15px);
        }
      }

      /* Decorative elements */
      .mascot-container::before {
        content: '';
        position: absolute;
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #2F80ED, #6C63FF);
        border-radius: 50%;
        top: 10%;
        right: 10%;
        opacity: 0.2;
        animation: float 4s ease-in-out infinite 1s;
        z-index: 2;
      }

      .mascot-container::after {
        content: '';
        position: absolute;
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #6C63FF, #2F80ED);
        border-radius: 50%;
        bottom: 15%;
        left: 5%;
        opacity: 0.15;
        animation: float 3.5s ease-in-out infinite 0.5s;
        z-index: 2;
      }

      /* Responsive adjustments */
      @media (max-width: 991px) {
        .hero-mascot {
          margin-top: 40px;
          padding: 20px;
        }

        .mascot-bg-circle {
          max-width: 350px;
          height: 350px;
        }

        .mascot-illustration {
          max-width: 300px;
        }
      }

      @media (max-width: 767px) {
        .hero-mascot {
          margin-top: 20px;
        }

        .mascot-bg-circle {
          max-width: 280px;
          height: 280px;
        }

        .mascot-illustration {
          max-width: 250px;
        }

        .mascot-container::before,
        .mascot-container::after {
          display: none;
        }
      }

    </style>
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
                  <a class="navbar-brand" href="index.php">
                    <img src="assets/img/logo/logo.png" alt="Logo" />
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
                    
                    // Cek dan hitung anggota
                    $table_check_anggota = "SELECT EXISTS (
                      SELECT FROM information_schema.tables 
                      WHERE table_schema = 'public' 
                      AND table_name = 'anggota'
                    )";
                    $check_anggota = pg_query($koneksi, $table_check_anggota);
                    if ($check_anggota) {
                      $row_check = pg_fetch_assoc($check_anggota);
                      if ($row_check['exists'] === 't' || $row_check['exists'] === true) {
                        $query_anggota = "SELECT COUNT(*) as total FROM anggota";
                        $result_anggota = pg_query($koneksi, $query_anggota);
                        if ($result_anggota) {
                          $row = pg_fetch_assoc($result_anggota);
                          $stats['anggota'] = (int)$row['total'];
                        }
                      }
                    }
                    
                    // Cek dan hitung pendaftar
                    $table_check_pendaftar = "SELECT EXISTS (
                      SELECT FROM information_schema.tables 
                      WHERE table_schema = 'public' 
                      AND table_name = 'pendaftaran'
                    )";
                    $check_pendaftar = pg_query($koneksi, $table_check_pendaftar);
                    if ($check_pendaftar) {
                      $row_check = pg_fetch_assoc($check_pendaftar);
                      if ($row_check['exists'] === 't' || $row_check['exists'] === true) {
                        $query_pendaftar = "SELECT COUNT(*) as total FROM pendaftaran";
                        $result_pendaftar = pg_query($koneksi, $query_pendaftar);
                        if ($result_pendaftar) {
                          $row = pg_fetch_assoc($result_pendaftar);
                          $stats['pendaftar'] = (int)$row['total'];
                        }
                      }
                    }
                    
                    // Cek dan hitung blog
                    $table_check_blog = "SELECT EXISTS (
                      SELECT FROM information_schema.tables 
                      WHERE table_schema = 'public' 
                      AND table_name = 'blog'
                    )";
                    $check_blog = pg_query($koneksi, $table_check_blog);
                    if ($check_blog) {
                      $row_check = pg_fetch_assoc($check_blog);
                      if ($row_check['exists'] === 't' || $row_check['exists'] === true) {
                        $query_blog = "SELECT COUNT(*) as total FROM blog WHERE is_published = true";
                        $result_blog = pg_query($koneksi, $query_blog);
                        if ($result_blog) {
                          $row = pg_fetch_assoc($result_blog);
                          $stats['blog'] = (int)$row['total'];
                        }
                      }
                    }
                    ?>
                    <?php include __DIR__ . '/menu.php'; ?>
                    <a href="login.php" class="button button-sm radius-10 d-none d-lg-flex">Admin</a>
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

