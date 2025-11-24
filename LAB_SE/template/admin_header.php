<!DOCTYPE html>
<html class="no-js" lang="id">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
    <title><?php echo isset($page_title) ? $page_title : 'Admin - Web Profile Laboratorium SE'; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    
    <?php
    // Path relatif default dari template ke root (untuk module/* gunakan ../../)
    if (!isset($base_path)) {
        $base_path = '../../';
    }
    ?>
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/bootstrap-5.0.0-alpha-2.min.css" />
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/LineIcons.2.0.css"/>
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/animate.css"/>
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/lindy-uikit.css"/>
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/admin-layout.css"/>
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/admin-dashboard.css"/>
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/admin-modules.css"/>
</head>
<body>
    <?php
    require_once __DIR__ . '/../module/auth/cek_login.php';
    ?>
    
    <div class="preloader">
      <div class="loader">
        <div class="spinner">
          <div class="spinner-container">
            <div class="spinner-rotator">
              <div class="spinner-left"><div class="spinner-circle"></div></div>
              <div class="spinner-right"><div class="spinner-circle"></div></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <header class="header header-2">
        <div class="navbar-area">
          <div class="container">
            <div class="row align-items-center">
              <div class="col-lg-12">
                <nav class="navbar navbar-expand-lg">
                    <a class="navbar-brand" href="<?php echo $base_path; ?>index.php">
                      <img src="<?php echo $base_path; ?>assets/img/logo/logo.png" alt="Logo" />
                    </a>
                    <div class="collapse navbar-collapse sub-menu-bar" id="navbarSupportedContent2">
                        <ul id="nav2" class="navbar-nav ml-auto">
                            <li class="nav-item">
                              <a class="page-scroll active" href="<?php echo $base_path; ?>module/admin/dashboard.php">Dashboard</a>
                            </li>
                        </ul>
                      <a href="<?php echo $base_path; ?>logout.php" class="button button-sm radius-10 border-button d-none d-lg-flex">Logout</a>
                    </div>
                  </nav>
              </div>
            </div>
          </div>
        </div> 
    </header>

