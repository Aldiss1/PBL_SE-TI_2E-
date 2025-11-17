    <footer class="footer footer-style-1">
        <div class="container">
          <div class="copyright-wrapper wow fadeInUp" data-wow-delay=".2s">
            <p>Admin Panel - Laboratorium Software Engineering</p>
          </div>
        </div>
    </footer>

    <?php
    if (!isset($base_path)) {
        $base_path = '../../';
    }
    ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="<?php echo $base_path; ?>assets/js/bootstrap.5.0.0.alpha-2-min.js"></script>
    <script src="<?php echo $base_path; ?>assets/js/count-up.min.js"></script>
    <script src="<?php echo $base_path; ?>assets/js/wow.min.js"></script>
    <script src="<?php echo $base_path; ?>assets/js/main.js"></script>
  </body>
</html>

