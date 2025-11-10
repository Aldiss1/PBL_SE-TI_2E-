<?php
$page_title = "List Pendaftar - Web Profile Laboratorium SE";
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/cek_login.php';
?>
<?php include __DIR__ . '/template/admin_header.php'; ?>

<section id="list-pendaftar" class="contact-style-6 pt-150 pb-100" style="margin-top: 80px;">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-10">
            
            <div class="section-title text-center mb-60">
                <h2 class="mb-25 wow fadeInUp" data-wow-delay=".2s">Anggota Mahasiswa (SE Geeks)</h2>
                <p class="wow fadeInUp" data-wow-delay=".4s">Daftar mahasiswa yang telah diterima dan aktif di Laboratorium.</p>
            </div>

            <div class="table-responsive wow fadeInUp" data-wow-delay=".6s">
                <table class="table table-striped table-hover">
                    <thead>
                      <tr>
                        <th scope="col">No.</th>
                        <th scope="col">Nama Lengkap</th>
                        <th scope="col">NIM</th>
                        <th scope="col">Jurusan</th>
                        <th scope="col">Tanggal Daftar</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      $query = "SELECT * FROM pendaftaran ORDER BY tanggal_daftar DESC";
                      $result = pg_query($koneksi, $query);
                      if (!$result) {
                          error_log("Error query list pendaftar: " . pg_last_error($koneksi));
                      }
                      $no = 1;
                      if ($result && pg_num_rows($result) > 0) {
                          while ($row = pg_fetch_assoc($result)) {
                      ?>
                      <tr>
                        <th scope="row"><?php echo $no++; ?></th>
                        <td><?php echo htmlspecialchars($row['nama']); ?></td>
                        <td><?php echo htmlspecialchars($row['nim']); ?></td>
                        <td><?php echo htmlspecialchars($row['jurusan']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($row['tanggal_daftar'])); ?></td>
                      </tr>
                      <?php
                          }
                      } else {
                      ?>
                      <tr>
                        <td colspan="5" class="text-center">Belum ada data pendaftar.</td>
                      </tr>
                      <?php
                      }
                      ?>
                    </tbody>
                </table>
            </div>

        </div>
      </div>
    </div>
</section>

<?php include __DIR__ . '/template/admin_footer.php'; ?>

