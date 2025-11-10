<?php
require_once __DIR__ . '/config/koneksi.php';
?>
<?php include __DIR__ . '/template/header.php'; ?>

<section class="pt-150 pb-50" style="background: #f3f3f3; margin-top: 80px;">
    <div class="container">
      <div class="row">
        <div class="col-lg-12">
          <div class="section-title text-center mb-30">
            <h2 class="mb-15 wow fadeInUp" data-wow-delay=".2s">Anggota Laboratorium</h2>
            <p class="wow fadeInUp" data-wow-delay=".4s">Dosen, Asisten, dan Peneliti di Lab Software Engineering</p>
          </div>
        </div>
      </div>
    </div>
</section>

<section id="team" class="team-style-1 pt-100 pb-70" style="background: #ffffff;">
    <div class="container">
        <div class="row">
            <?php
            // Cek apakah tabel anggota ada
            $table_check = "SELECT EXISTS (
              SELECT FROM information_schema.tables 
              WHERE table_schema = 'public' 
              AND table_name = 'anggota'
            )";
            $table_exists = pg_query($koneksi, $table_check);
            $has_table = false;
            if ($table_exists) {
              $row_check = pg_fetch_assoc($table_exists);
              $has_table = $row_check['exists'] === 't' || $row_check['exists'] === true;
            }
            
            $result = false;
            if ($has_table) {
              $query = "SELECT * FROM anggota ORDER BY id DESC";
              $result = pg_query($koneksi, $query);
              if (!$result) {
                error_log("Error query personil: " . pg_last_error($koneksi));
              }
            }
            
            if ($result && pg_num_rows($result) > 0) {
                while ($row = pg_fetch_assoc($result)) {
            ?>
            <div class="col-xl-4 col-lg-4 col-md-6">
                <div class="single-team wow fadeInUp" data-wow-delay=".2s">
                    <div class="image">
                        <img src="<?php echo isset($row['foto']) && $row['foto'] ? $row['foto'] : 'https://via.placeholder.com/300'; ?>" alt="<?php echo htmlspecialchars($row['nama']); ?>">
                    </div>
                    <div class="info">
                        <h6><?php echo htmlspecialchars($row['nama']); ?></h6>
                        <p><?php echo htmlspecialchars($row['jabatan'] ?? 'Anggota'); ?></p>
                        <ul class="socials">
                            <li> <a href="#0"> <i class="lni lni-facebook-filled"></i> </a> </li>
                            <li> <a href="#0"> <i class="lni lni-twitter-filled"></i> </a> </li>
                            <li> <a href="#0"> <i class="lni lni-linkedin-original"></i> </a> </li>
                        </ul>
                        <a href="detail-personil.php?id=<?php echo $row['id']; ?>" class="button button-sm radius-10 border-button mt-20">Lihat Profil</a>
                    </div>
                </div>
            </div>
            <?php
                }
            } else {
            ?>
            <div class="col-12">
                <p class="text-center" style="color: #717791;">
                  <?php if (!$has_table): ?>
                    Tabel database belum dibuat. Silakan import file <code>setup-database.sql</code> terlebih dahulu.
                  <?php else: ?>
                    Belum ada data anggota.
                  <?php endif; ?>
                </p>
            </div>
            <?php
            }
            ?>
        </div>
    </div>
</section>

<?php include __DIR__ . '/template/footer.php'; ?>

