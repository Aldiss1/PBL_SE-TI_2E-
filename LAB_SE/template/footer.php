<?php $labseFooterBase = defined('LABSE_BASE_PATH') ? LABSE_BASE_PATH : ''; ?>
		<!-- ========================= footer style-1 start ========================= -->
    <footer class="footer footer-style-1">
      <div class="container">
        <div class="widget-wrapper">
          <div class="row">
            <div class="col-xl-3 col-lg-4 col-md-6">
              <div class="footer-widget wow fadeInUp" data-wow-delay=".2s">
                <div class="logo">
                  <a href="#0"> <img src="<?php echo $labseFooterBase; ?>/assets/img/logo/logo.svg" alt=""> </a>
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
            toggleBtn.addEventListener('click', function() {
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
        }
        
        // Pastikan navbar selalu visible
        var fixedNavbar = document.getElementById('fixed-navbar');
        if (fixedNavbar) {
            function ensureVisible() {
                fixedNavbar.style.setProperty('position', 'fixed', 'important');
                fixedNavbar.style.setProperty('top', '0', 'important');
                fixedNavbar.style.setProperty('z-index', '999999', 'important');
                fixedNavbar.style.setProperty('display', 'block', 'important');
                fixedNavbar.style.setProperty('visibility', 'visible', 'important');
                fixedNavbar.style.setProperty('opacity', '1', 'important');
            }
            
            ensureVisible();
            setInterval(ensureVisible, 100);
            window.addEventListener('scroll', ensureVisible, { passive: true });
            
        }
        
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (href !== '#' && href !== '#0') {
                    e.preventDefault();
                    const target = document.querySelector(href);
                    if (target) {
                        const offset = 80; // Navbar height
                        const targetPosition = target.offsetTop - offset;
                        window.scrollTo({
                            top: targetPosition,
                            behavior: 'smooth'
                        });
                    }
                }
            });
        });
    })();
    </script>
  </body>
</html>

