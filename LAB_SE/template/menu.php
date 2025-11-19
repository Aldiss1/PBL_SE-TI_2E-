<?php $labseMenuBase = defined('LABSE_BASE_PATH') ? LABSE_BASE_PATH : ''; ?>
<ul id="nav2" class="navbar-nav ml-auto">
  <li class="nav-item">
    <a class="page-scroll active" href="<?php echo $labseMenuBase; ?>/index.php#home">Home</a>
  </li>
  <li class="nav-item dropdown">
    <a class="page-scroll dropdown-toggle" href="#" id="navbarDropdownProfile" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
      <i class="fas fa-user-circle mr-1"></i> Profil Kami
      <span class="ml-1"><i class="fas fa-chevron-down"></i></span>
    </a>
    <div class="dropdown-menu animated fadeIn" aria-labelledby="navbarDropdownProfile">
      <a class="dropdown-item" href="<?php echo $labseMenuBase; ?>/pages/profil/tentang-kami.php">
        <i class="fas fa-info-circle mr-2 text-primary"></i> Tentang Kami
      </a>
      <a class="dropdown-item" href="<?php echo $labseMenuBase; ?>/pages/profil/visi-misi.php">
        <i class="fas fa-bullseye mr-2 text-success"></i> Visi &amp; Misi
      </a>
      <a class="dropdown-item" href="<?php echo $labseMenuBase; ?>/pages/profil/roadmap.php">
        <i class="fas fa-sitemap mr-2 text-warning"></i> Roadmap
      </a>
    </div>
  </li>
  <li class="nav-item">
    <a class="page-scroll" href="<?php echo $labseMenuBase; ?>/pages/profil/personil.php">Personil</a>
  </li>
  <li class="nav-item">
    <a class="page-scroll" href="<?php echo $labseMenuBase; ?>/pages/pendaftaran/daftar.php">Daftar</a>
  </li>
  <li class="nav-item">
    <a class="page-scroll" href="<?php echo $labseMenuBase; ?>/index.php#blog">Blog</a>
  </li>
  <li class="nav-item dropdown">
    <a class="page-scroll dropdown-toggle" href="#" id="navbarDropdownStats" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
      <i class="lni lni-stats-up mr-1"></i> Statistik
      <span class="ml-1"><i class="fas fa-chevron-down"></i></span>
    </a>
    <div class="dropdown-menu animated fadeIn" aria-labelledby="navbarDropdownStats">
      <?php
      // Statistik sudah diambil di header.php, gunakan variable $stats yang sudah ada
      if (!isset($stats)) {
        $stats = [
          'anggota' => 0,
          'pendaftar' => 0,
          'blog' => 0
        ];
      }
      ?>
      <a class="dropdown-item" href="<?php echo $labseMenuBase; ?>/pages/profil/personil.php">
        <i class="lni lni-user mr-2" style="color: #2F80ED;"></i> Anggota: <strong><?php echo $stats['anggota']; ?></strong>
      </a>
      <a class="dropdown-item" href="<?php echo $labseMenuBase; ?>/module/pendaftar/index.php">
        <i class="lni lni-graduation mr-2" style="color: #27AE60;"></i> Pendaftar: <strong><?php echo $stats['pendaftar']; ?></strong>
      </a>
      <a class="dropdown-item" href="<?php echo $labseMenuBase; ?>/pages/blog/blog-list.php">
        <i class="lni lni-book mr-2" style="color: #6C63FF;"></i> Blog: <strong><?php echo $stats['blog']; ?></strong>
      </a>
    </div>
  </li>
</ul>

