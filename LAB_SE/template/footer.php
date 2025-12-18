<?php 
// Pastikan base path memiliki trailing slash
$labseFooterBase = defined('LABSE_BASE_PATH') ? rtrim(LABSE_BASE_PATH, '/') . '/' : '';

// Fungsi untuk mengambil konten footer dari database
function ambil_footer_konten($koneksi, $key) {
    if (!$koneksi) {
        return null;
    }
    $query = "SELECT content FROM konten_page WHERE page_key = $1";
    $result = pg_query_params($koneksi, $query, array($key));
    if ($result && pg_num_rows($result) > 0) {
        $row = pg_fetch_assoc($result);
        return $row['content'];
    }
    return null;
}

// Ambil data footer dari database
$footer_deskripsi = ambil_footer_konten($koneksi, 'footer_deskripsi');
if (!$footer_deskripsi) {
    $footer_deskripsi = 'Laboratorium Software Engineering Politeknik Negeri Malang yang berfokus pada pengembangan perangkat lunak berkualitas tinggi, penelitian, dan pembinaan kompetensi mahasiswa di bidang rekayasa perangkat lunak.';
}

$footer_copyright = ambil_footer_konten($koneksi, 'footer_copyright');
if (!$footer_copyright) {
    $footer_copyright = '© ' . date('Y') . ' Laboratorium Software Engineering - Jurusan Teknologi Informasi, Politeknik Negeri Malang. All rights reserved.';
} else {
    // Replace {YEAR} dengan tahun saat ini
    $footer_copyright = str_replace('{YEAR}', date('Y'), $footer_copyright);
}

// Ambil social links
$social_links_json = ambil_footer_konten($koneksi, 'footer_social_links');
$social_links = [];
if ($social_links_json) {
    $social_links = json_decode($social_links_json, true) ?: [];
}
// Fallback jika tidak ada data
if (empty($social_links)) {
    $social_links = [
        ['icon' => 'lni-facebook-filled', 'url' => '#0', 'label' => 'Facebook'],
        ['icon' => 'lni-twitter-filled', 'url' => '#0', 'label' => 'Twitter'],
        ['icon' => 'lni-instagram-filled', 'url' => '#0', 'label' => 'Instagram'],
        ['icon' => 'lni-linkedin-original', 'url' => '#0', 'label' => 'LinkedIn']
    ];
}

// Ambil tautan cepat
$tautan_cepat_json = ambil_footer_konten($koneksi, 'footer_tautan_cepat');
$tautan_cepat = [];
if ($tautan_cepat_json) {
    $tautan_cepat = json_decode($tautan_cepat_json, true) ?: [];
}
// Fallback jika tidak ada data
if (empty($tautan_cepat)) {
    $tautan_cepat = [
        ['label' => 'Beranda', 'url' => '/index.php#home'],
        ['label' => 'Tentang Kami', 'url' => '/pages/profil/tentang-kami.php'],
        ['label' => 'Personil', 'url' => '/pages/profil/personil.php']
    ];
}

// Ambil layanan
$layanan_json = ambil_footer_konten($koneksi, 'footer_layanan');
$layanan = [];
if ($layanan_json) {
    $layanan = json_decode($layanan_json, true) ?: [];
}
// Fallback jika tidak ada data
if (empty($layanan)) {
    $layanan = [
        ['label' => 'Visi & Misi', 'url' => '/pages/profil/visi-misi.php'],
        ['label' => 'Roadmap', 'url' => '/pages/profil/roadmap.php'],
        ['label' => 'Blog', 'url' => '/pages/blog/blog-list.php'],
        ['label' => 'Dosen Pengampu', 'url' => '/index.php#galeri-dosen']
    ];
}

// Ambil informasi
$informasi_json = ambil_footer_konten($koneksi, 'footer_informasi');
$informasi = [];
if ($informasi_json) {
    $informasi = json_decode($informasi_json, true) ?: [];
}
// Fallback jika tidak ada data
if (empty($informasi)) {
    $informasi = [
        ['label' => 'Pendaftaran', 'url' => '/pages/pendaftaran/daftar.php'],
        ['label' => 'Anggota Lab', 'url' => '/pages/profil/personil.php'],
        ['label' => 'Admin', 'url' => '/module/auth/login.php'],
        ['label' => 'Kontak', 'url' => '/index.php#contact']
    ];
}

// Helper function untuk format URL
function format_footer_url($url, $base) {
    if (empty($url)) return '#';
    if (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0) {
        return $url;
    }
    if ($url[0] === '/') {
        return $base . $url;
    }
    return $base . '/' . $url;
}
?>
		<!-- ========================= footer style-1 start ========================= -->
    <footer class="footer footer-style-1">
      <div class="container">
        <div class="widget-wrapper">
          <div class="row">
            <div class="col-xl-3 col-lg-4 col-md-6">
              <div class="footer-widget wow fadeInUp" data-wow-delay=".2s">
                <div class="logo">
                  <a href="<?php echo $labseFooterBase; ?>index.php"> <img src="<?php echo $labseFooterBase; ?>assets/img/logo/logo.png" alt="Logo Lab SE"> </a>
                </div>
                <p class="desc"><?php echo htmlspecialchars($footer_deskripsi); ?></p>
                <ul class="socials">
                  <?php foreach ($social_links as $social): ?>
                  <li> 
                    <a href="<?php echo htmlspecialchars(format_footer_url($social['url'] ?? '#0', $labseFooterBase)); ?>" target="_blank" rel="noopener" title="<?php echo htmlspecialchars($social['label'] ?? ''); ?>"> 
                      <i class="lni <?php echo htmlspecialchars($social['icon'] ?? 'lni-link'); ?>"></i> 
                    </a> 
                  </li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </div>
            <div class="col-xl-2 offset-xl-1 col-lg-2 col-md-6 col-sm-6">
              <div class="footer-widget wow fadeInUp" data-wow-delay=".3s">
                <h6>Tautan Cepat</h6>
                <ul class="links">
                  <?php foreach ($tautan_cepat as $link): ?>
                  <li> 
                    <a href="<?php echo htmlspecialchars(format_footer_url($link['url'] ?? '#', $labseFooterBase)); ?>">
                      <?php echo htmlspecialchars($link['label'] ?? ''); ?>
                    </a> 
                  </li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6">
              <div class="footer-widget wow fadeInUp" data-wow-delay=".4s">
                <h6>Layanan</h6>
                <ul class="links">
                  <?php foreach ($layanan as $link): ?>
                  <li> 
                    <a href="<?php echo htmlspecialchars(format_footer_url($link['url'] ?? '#', $labseFooterBase)); ?>">
                      <?php echo htmlspecialchars($link['label'] ?? ''); ?>
                    </a> 
                  </li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-6">
              <div class="footer-widget wow fadeInUp" data-wow-delay=".5s">
                <h6>Informasi</h6>
                <ul class="links">
                  <?php foreach ($informasi as $link): ?>
                  <li> 
                    <a href="<?php echo htmlspecialchars(format_footer_url($link['url'] ?? '#', $labseFooterBase)); ?>" style="background: transparent !important; border: none !important; padding: 0 !important; border-radius: 0 !important; box-shadow: none !important; color: #6c757d !important; font-weight: 400 !important; font-size: 14px !important;">
                      <?php echo htmlspecialchars($link['label'] ?? ''); ?>
                    </a> 
                  </li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </div>
          </div>
        </div>
        <div class="copyright-wrapper wow fadeInUp" data-wow-delay=".2s">
          <p><?php echo htmlspecialchars($footer_copyright); ?></p>
        </div>
      </div>
    </footer>
    <!-- ========================= footer style-1 end ========================= -->

    <!-- ========================= scroll-top start ========================= -->
    <a href="#" class="scroll-top"> <i class="lni lni-chevron-up"></i> </a>
    <!-- ========================= scroll-top end ========================= -->
		

    <!-- ========================= JS here ========================= -->
    <script src="<?php echo $labseFooterBase; ?>assets/js/bootstrap.5.0.0.alpha-2-min.js"></script>
    <script src="<?php echo $labseFooterBase; ?>assets/js/count-up.min.js"></script>
    <script src="<?php echo $labseFooterBase; ?>assets/js/wow.min.js"></script>
    <script src="<?php echo $labseFooterBase; ?>assets/js/main.js"></script>
    
    <!-- Force Responsive Script - Load First -->
    <script>
    (function() {
        'use strict';
        
        // Force responsive on page load and resize
        function forceResponsive() {
            var width = window.innerWidth || document.documentElement.clientWidth;
            
            if (width <= 767) {
                // Mobile: Force all columns to full width
                var cols = document.querySelectorAll('[class*="col-"]');
                cols.forEach(function(col) {
                    if (!col.classList.contains('col-12')) {
                        col.style.flex = '0 0 100%';
                        col.style.maxWidth = '100%';
                        col.style.width = '100%';
                    }
                });
                
                // Force container max-width
                var containers = document.querySelectorAll('.container, .container-fluid');
                containers.forEach(function(container) {
                    container.style.maxWidth = '100%';
                    container.style.width = '100%';
                    container.style.paddingLeft = '15px';
                    container.style.paddingRight = '15px';
                });
                
                // Force images responsive
                var images = document.querySelectorAll('img');
                images.forEach(function(img) {
                    if (img.style.maxWidth !== '100%') {
                        img.style.maxWidth = '100%';
                        img.style.height = 'auto';
                    }
                });
                
                // Prevent horizontal scroll
                document.documentElement.style.overflowX = 'hidden';
                document.body.style.overflowX = 'hidden';
                document.documentElement.style.maxWidth = '100vw';
                document.body.style.maxWidth = '100vw';
            }
        }
        
        // Run on load
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', forceResponsive);
        } else {
            forceResponsive();
        }
        
        // Run on resize
        window.addEventListener('resize', forceResponsive);
        
        // Run after a short delay to catch dynamic content
        setTimeout(forceResponsive, 100);
        setTimeout(forceResponsive, 500);
    })();
    </script>
    
    <!-- Navbar Toggle Script & Effects -->
    <script>
    (function() {
        var toggleBtn = document.getElementById('navbarToggleNew');
        var navbarMenu = document.getElementById('navbarMenuNew');
        
        // Toggle mobile menu
        if (toggleBtn && navbarMenu) {
            toggleBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                navbarMenu.classList.toggle('active');
                toggleBtn.classList.toggle('active');
            });
            
            // Close menu when clicking outside
            document.addEventListener('click', function(e) {
                if (!toggleBtn.contains(e.target) && !navbarMenu.contains(e.target)) {
                    navbarMenu.classList.remove('active');
                    toggleBtn.classList.remove('active');
                }
            });
            
            // Handle dropdown toggle on mobile - FIXED VERSION
            function setupMobileDropdowns() {
                var dropdownToggles = navbarMenu.querySelectorAll('.dropdown-toggle');
                dropdownToggles.forEach(function(toggle) {
                    // Remove old listener by cloning
                    var newToggle = toggle.cloneNode(true);
                    toggle.parentNode.replaceChild(newToggle, toggle);
                    
                    newToggle.addEventListener('click', function(e) {
                        if (window.innerWidth <= 991) {
                            e.preventDefault();
                            e.stopPropagation();
                            
                            var dropdown = this.closest('.nav-item.dropdown');
                            if (!dropdown) dropdown = this.parentElement;
                            
                            var dropdownMenu = dropdown.querySelector('.dropdown-menu');
                            if (!dropdownMenu) return;
                            
                            // Style fix
                            this.style.background = 'transparent';
                            this.style.backgroundColor = 'transparent';
                            this.style.border = 'none';
                            this.style.borderRadius = '0';
                            this.style.boxShadow = 'none';
                            
                            // Close other dropdowns
                            dropdownToggles.forEach(function(otherToggle) {
                                if (otherToggle !== newToggle) {
                                    var otherDropdown = otherToggle.closest('.nav-item.dropdown') || otherToggle.parentElement;
                                    if (otherDropdown) {
                                        var otherMenu = otherDropdown.querySelector('.dropdown-menu');
                                        if (otherMenu) {
                                            otherMenu.style.display = 'none';
                                        }
                                        otherDropdown.classList.remove('active', 'show');
                                    }
                                }
                            });
                            
                            // Toggle current
                            var isActive = dropdown.classList.contains('active');
                            if (isActive) {
                                dropdownMenu.style.display = 'none';
                                dropdown.classList.remove('active', 'show');
                            } else {
                                dropdownMenu.style.display = 'block';
                                dropdown.classList.add('active', 'show');
                            }
                        }
                    });
                });
            }
            
            setupMobileDropdowns();
            
            // Re-setup when menu toggles
            if (toggleBtn) {
                var originalToggle = toggleBtn.onclick;
                toggleBtn.addEventListener('click', function() {
                    setTimeout(setupMobileDropdowns, 200);
                });
            }
                
                // Pastikan dropdown toggle tidak memiliki style button saat hover
                toggle.addEventListener('mouseenter', function() {
                    this.style.background = 'transparent';
                    this.style.backgroundColor = 'transparent';
                    this.style.border = 'none';
                    this.style.borderRadius = '0';
                    this.style.boxShadow = 'none';
                });
            });
            
            // Pastikan semua dropdown toggle tidak memiliki style button
            var allDropdownToggles = document.querySelectorAll('#fixed-navbar .dropdown-toggle');
            allDropdownToggles.forEach(function(toggle) {
                toggle.style.background = 'transparent';
                toggle.style.backgroundColor = 'transparent';
                toggle.style.border = 'none';
                toggle.style.borderRadius = '0';
                toggle.style.boxShadow = 'none';
                toggle.style.padding = '10px 0px';
            });
            
            // Close menu when clicking on a link
            var menuLinks = navbarMenu.querySelectorAll('a');
            menuLinks.forEach(function(link) {
                link.addEventListener('click', function() {
                    if (window.innerWidth <= 991) {
                        navbarMenu.classList.remove('active');
                        toggleBtn.classList.remove('active');
                    }
                });
            });
        }
        
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (href !== '#' && href !== '#0') {
                    e.preventDefault();
                    const target = document.querySelector(href);
                    if (target) {
                        var offset = 80;
                        if (window.innerWidth <= 991) {
                            offset = 70;
                        }
                        if (window.innerWidth <= 575) {
                            offset = 65;
                        }
                        const targetPosition = target.offsetTop - offset;
                        window.scrollTo({
                            top: targetPosition,
                            behavior: 'smooth'
                        });
                        
                        // Close mobile menu if open
                        if (navbarMenu && navbarMenu.classList.contains('active')) {
                            navbarMenu.classList.remove('active');
                            if (toggleBtn) toggleBtn.classList.remove('active');
                        }
                    }
                }
            });
        });
    })();
    </script>
  </body>
</html>

