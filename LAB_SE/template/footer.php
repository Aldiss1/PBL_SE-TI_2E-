<?php $labseFooterBase = defined('LABSE_BASE_PATH') ? LABSE_BASE_PATH : ''; ?>
		<!-- ========================= footer style-1 start ========================= -->
    <footer class="footer footer-style-1">
      <div class="container">
        <div class="widget-wrapper">
          <div class="row">
            <div class="col-xl-3 col-lg-4 col-md-6">
              <div class="footer-widget wow fadeInUp" data-wow-delay=".2s">
                <div class="logo">
                  <a href="<?php echo $labseFooterBase; ?>/index.php"> <img src="<?php echo $labseFooterBase; ?>/assets/img/logo/logo.png" alt="Logo Lab SE"> </a>
                </div>
                <p class="desc">Laboratorium Software Engineering Politeknik Negeri Malang yang berfokus pada pengembangan perangkat lunak berkualitas tinggi, penelitian, dan pembinaan kompetensi mahasiswa di bidang rekayasa perangkat lunak.</p>
                <ul class="socials">
                  <li> <a href="#0"> <i class="lni lni-facebook-filled"></i> </a> </li>
                  <li> <a href="#0"> <i class="lni lni-twitter-filled"></i> </a> </li>
                  <li> <a href="#0"> <i class="lni lni-instagram-filled"></i> </a> </li>
                  <li> <a href="#0"> <i class="lni lni-linkedin-original"></i> </a> </li>
                </ul>
              </div>
            </div>
            <div class="col-xl-2 offset-xl-1 col-lg-2 col-md-6 col-sm-6">
              <div class="footer-widget wow fadeInUp" data-wow-delay=".3s">
                <h6>Tautan Cepat</h6>
                <ul class="links">
                  <li> <a href="<?php echo $labseFooterBase; ?>/index.php#home">Beranda</a> </li>
                  <li> <a href="<?php echo $labseFooterBase; ?>/pages/profil/tentang-kami.php">Tentang Kami</a> </li>
                  <li> <a href="<?php echo $labseFooterBase; ?>/pages/profil/personil.php">Personil</a> </li>
                </ul>
              </div>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6">
              <div class="footer-widget wow fadeInUp" data-wow-delay=".4s">
                <h6>Layanan</h6>
                <ul class="links">
                  <li> <a href="<?php echo $labseFooterBase; ?>/pages/profil/visi-misi.php">Visi & Misi</a> </li>
                  <li> <a href="<?php echo $labseFooterBase; ?>/pages/profil/roadmap.php">Roadmap</a> </li>
                  <li> <a href="<?php echo $labseFooterBase; ?>/pages/blog/blog-list.php">Blog</a> </li>
                  <li> <a href="<?php echo $labseFooterBase; ?>/index.php#galeri-dosen">Dosen Pengampu</a> </li>
                </ul>
              </div>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-6">
              <div class="footer-widget wow fadeInUp" data-wow-delay=".5s">
                <h6>Informasi</h6>
                <ul class="links">
                  <li> <a href="<?php echo $labseFooterBase; ?>/pages/pendaftaran/daftar.php" style="background: transparent !important; border: none !important; padding: 0 !important; border-radius: 0 !important; box-shadow: none !important; color: #6c757d !important; font-weight: 400 !important; font-size: 14px !important;">Pendaftaran</a> </li>
                  <li> <a href="<?php echo $labseFooterBase; ?>/pages/profil/personil.php">Anggota Lab</a> </li>
                  <li> <a href="<?php echo $labseFooterBase; ?>/module/auth/login.php">Admin</a> </li>
                  <li> <a href="<?php echo $labseFooterBase; ?>/index.php#contact">Kontak</a> </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
        <div class="copyright-wrapper wow fadeInUp" data-wow-delay=".2s">
          <p>&copy; <?php echo date('Y'); ?> Laboratorium Software Engineering - Jurusan Teknologi Informasi, Politeknik Negeri Malang. All rights reserved.</p>
        </div>
      </div>
    </footer>
    <!-- ========================= footer style-1 end ========================= -->

    <!-- ========================= scroll-top start ========================= -->
    <a href="#" class="scroll-top"> <i class="lni lni-chevron-up"></i> </a>
    <!-- ========================= scroll-top end ========================= -->
		

    <!-- ========================= JS here ========================= -->
    <script src="<?php echo $labseFooterBase; ?>/assets/js/bootstrap.5.0.0.alpha-2-min.js"></script>
    <script src="<?php echo $labseFooterBase; ?>/assets/js/count-up.min.js"></script>
    <script src="<?php echo $labseFooterBase; ?>/assets/js/wow.min.js"></script>
    <script src="<?php echo $labseFooterBase; ?>/assets/js/main.js"></script>
    
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

