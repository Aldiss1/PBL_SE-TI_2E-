<?php $labseMenuBase = defined('LABSE_BASE_PATH') ? LABSE_BASE_PATH : ''; ?>
<ul id="nav2" class="navbar-nav">
  <li class="nav-item">
    <a class="page-scroll" href="<?php echo $labseMenuBase; ?>/index.php#home">Home</a>
  </li>
  <li class="nav-item dropdown">
    <a class="page-scroll dropdown-toggle" href="#" id="navbarDropdownProfile">Profil Kami</a>
    <div class="dropdown-menu">
      <a class="dropdown-item" href="<?php echo $labseMenuBase; ?>/pages/profil/tentang-kami.php">Tentang Kami</a>
      <a class="dropdown-item" href="<?php echo $labseMenuBase; ?>/pages/profil/visi-misi.php">Visi &amp; Misi</a>
      <a class="dropdown-item" href="<?php echo $labseMenuBase; ?>/pages/profil/roadmap.php">Roadmap</a>
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
    <a class="page-scroll dropdown-toggle" href="#" id="navbarDropdownStats">Statistik</a>
    <div class="dropdown-menu">
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
      <a class="dropdown-item" href="<?php echo $labseMenuBase; ?>/pages/profil/personil.php">Anggota: <strong><?php echo $stats['anggota']; ?></strong></a>
      <a class="dropdown-item" href="<?php echo $labseMenuBase; ?>/pages/pendaftaran/pendaftar-list.php">Pendaftar: <strong><?php echo $stats['pendaftar']; ?></strong></a>
      <a class="dropdown-item" href="<?php echo $labseMenuBase; ?>/pages/blog/blog-list.php">Blog: <strong><?php echo $stats['blog']; ?></strong></a>
    </div>
  </li>
</ul>

