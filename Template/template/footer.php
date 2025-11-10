		<!-- ========================= footer style-1 start ========================= -->
    <footer class="footer footer-style-1">
      <div class="container">
        <div class="widget-wrapper">
          <div class="row">
            <div class="col-xl-3 col-lg-4 col-md-6">
              <div class="footer-widget wow fadeInUp" data-wow-delay=".2s">
                <div class="logo">
                  <a href="#0"> <img src="assets/img/logo/logo.svg" alt=""> </a>
                </div>
                <p class="desc">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Facilisis nulla placerat amet amet congue.</p>
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
                <h6>Quick Link</h6>
                <ul class="links">
                  <li> <a href="#0">Home</a> </li>
                  <li> <a href="#0">About</a> </li>
                  <li> <a href="#0">Service</a> </li>
                  <li> <a href="#0">Contact</a> </li>
                </ul>
              </div>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6">
              <div class="footer-widget wow fadeInUp" data-wow-delay=".4s">
                <h6>Services</h6>
                <ul class="links">
                  <li> <a href="#0">Web Design</a> </li>
                  <li> <a href="#0">Web Development</a> </li>
                  <li> <a href="#0">Seo Optimization</a> </li>
                  <li> <a href="#0">Blog Writing</a> </li>
                </ul>
              </div>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-6">
              <div class="footer-widget wow fadeInUp" data-wow-delay=".5s">
                <h6>Help & Support</h6>
                <ul class="links">
                  <li> <a href="#0">Support Center</a> </li>
                  <li> <a href="#0">Live Chat</a> </li>
                  <li> <a href="#0">FAQ</a> </li>
                  <li> <a href="#0">Terms & Conditions</a> </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
        <div class="copyright-wrapper wow fadeInUp" data-wow-delay=".2s">
          <p>Design and Developed by <a href="https://uideck.com" rel="nofollow" target="_blank">UIdeck</a> Built-with <a href="#">Lindy UI Kit</a>. Distibuted by <a href="https://themewagon.com" target="_blank">ThemeWagon</a></p>
        </div>
      </div>
    </footer>
    <!-- ========================= footer style-1 end ========================= -->

    <!-- ========================= scroll-top start ========================= -->
    <a href="#" class="scroll-top"> <i class="lni lni-chevron-up"></i> </a>
    <!-- ========================= scroll-top end ========================= -->
		

    <!-- ========================= JS here ========================= -->
    <script src="assets/js/bootstrap.5.0.0.alpha-2-min.js"></script>
    <script src="assets/js/count-up.min.js"></script>
    <script src="assets/js/wow.min.js"></script>
    <script src="assets/js/main.js"></script>
    
    <!-- Navbar Sticky Fix - Memastikan navbar tidak hilang saat scroll -->
    <script>
      (function() {
        let headerNavbar = null;
        
        function initNavbar() {
          headerNavbar = document.querySelector(".hero-section-wrapper-2 .header") || 
                         document.querySelector(".header.header-2") || 
                         document.querySelector("header.header");
          
          if (!headerNavbar) {
            setTimeout(initNavbar, 100);
            return;
          }
          
          // Set initial style untuk memastikan navbar selalu visible
          function ensureNavbarVisible() {
            if (!headerNavbar) return;
            
            // Force navbar selalu fixed dan visible
            headerNavbar.style.setProperty('position', 'fixed', 'important');
            headerNavbar.style.setProperty('top', '0', 'important');
            headerNavbar.style.setProperty('left', '0', 'important');
            headerNavbar.style.setProperty('right', '0', 'important');
            headerNavbar.style.setProperty('width', '100%', 'important');
            headerNavbar.style.setProperty('z-index', '99999', 'important');
            headerNavbar.style.setProperty('background', '#fff', 'important');
            headerNavbar.style.setProperty('display', 'block', 'important');
            headerNavbar.style.setProperty('visibility', 'visible', 'important');
            headerNavbar.style.setProperty('opacity', '1', 'important');
            
            // Tambahkan class sticky
            if (!headerNavbar.classList.contains("sticky")) {
              headerNavbar.classList.add("sticky");
            }
          }
          
          // Function untuk handle scroll
          function handleScroll() {
            if (!headerNavbar) return;
            
            const scrollY = window.pageYOffset || window.scrollY || document.documentElement.scrollTop;
            
            // Selalu pastikan navbar visible dan fixed
            ensureNavbarVisible();
            
            // Tambahkan shadow saat scroll
            if (scrollY > 50) {
              headerNavbar.style.setProperty('box-shadow', '0 2px 10px rgba(0,0,0,0.1)', 'important');
            } else {
              headerNavbar.style.setProperty('box-shadow', 'none', 'important');
            }
          }
          
          // Override window.onscroll
          const originalOnScroll = window.onscroll;
          window.onscroll = function() {
            if (originalOnScroll) {
              try {
                originalOnScroll();
              } catch(e) {}
            }
            handleScroll();
          };
          
          // Juga listen dengan addEventListener sebagai backup
          window.addEventListener('scroll', handleScroll, { passive: true });
          
          // Initial setup
          ensureNavbarVisible();
          handleScroll();
          
          // Periodic check setiap 100ms untuk memastikan navbar selalu visible
          setInterval(function() {
            ensureNavbarVisible();
            const scrollY = window.pageYOffset || window.scrollY || document.documentElement.scrollTop;
            if (scrollY > 50) {
              headerNavbar.style.setProperty('box-shadow', '0 2px 10px rgba(0,0,0,0.1)', 'important');
            }
          }, 100);
        }
        
        // Init setelah DOM ready
        if (document.readyState === 'loading') {
          document.addEventListener('DOMContentLoaded', initNavbar);
        } else {
          initNavbar();
        }
        
        // Backup init
        setTimeout(initNavbar, 500);
        window.addEventListener('load', initNavbar);
      })();
    </script>
    
    <!-- Smooth scroll for anchor links -->
    <script>
      document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
          const href = this.getAttribute('href');
          if (href !== '#' && href !== '#0') {
            e.preventDefault();
            const target = document.querySelector(href);
            if (target) {
              target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
              });
            }
          }
        });
      });
      
      // Handle avatar image errors - show initial if image fails to load
      document.querySelectorAll('.dosen-avatar-img img').forEach(img => {
        img.addEventListener('error', function() {
          this.style.display = 'none';
          const parent = this.parentElement;
          if (parent && parent.classList.contains('dosen-avatar-img')) {
            parent.style.background = 'linear-gradient(135deg, #2F80ED 0%, #6C63FF 100%)';
            // Ensure initial is visible
            const initial = parent.getAttribute('data-initial');
            if (initial && !parent.querySelector('::before')) {
              parent.innerHTML = '<span style="position: relative; z-index: 1; line-height: 1;">' + initial + '</span>';
            }
          }
        });
        
        // Check if image loaded successfully
        if (!img.complete || img.naturalHeight === 0) {
          img.dispatchEvent(new Event('error'));
        }
      });
      
      // Fix: Pastikan avatar tidak mengikuti navbar saat scroll
      window.addEventListener('scroll', function() {
        const galeriDosen = document.querySelector('#galeri-dosen');
        if (galeriDosen) {
          const avatars = galeriDosen.querySelectorAll('.dosen-avatar-border, .dosen-avatar-img');
          avatars.forEach(avatar => {
            avatar.style.position = 'relative';
            avatar.style.top = 'auto';
            avatar.style.left = 'auto';
            avatar.style.transform = 'none';
            avatar.style.zIndex = '1';
          });
        }
      }, { passive: true });
    </script>
  </body>
</html>

