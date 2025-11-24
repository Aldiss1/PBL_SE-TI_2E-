<!DOCTYPE html>
<html class="no-js" lang="id">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
    <title><?php echo isset($page_title) ? $page_title : 'Mahasiswa - Lab SE'; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <?php
    if (!isset($base_path)) {
        $base_path = '../../';
    }
    ?>
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/bootstrap-5.0.0-alpha-2.min.css" />
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/LineIcons.2.0.css"/>
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/animate.css"/>
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/lindy-uikit.css"/>
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/admin-layout.css"/>
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/admin-modules.css"/>
</head>
<body>
    <?php require_once __DIR__ . '/../module/auth/cek_login_mahasiswa.php'; ?>

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

    <!-- Navbar removed: using sidebar layout only -->


