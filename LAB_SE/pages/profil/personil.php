<?php
require_once __DIR__ . '/../../config/koneksi.php';
?>
<?php include __DIR__ . '/../../template/header.php'; ?>

<section class="pt-150 pb-50">
    <div class="container">
      <div class="row">
        <div class="col-lg-12">
          <div class="section-title text-center mb-30">
            <h2 class="mb-15 wow fadeInUp" data-wow-delay=".2s">Anggota <span>Laboratorium</span></h2>
            <p class="wow fadeInUp" data-wow-delay=".4s">Dosen, Asisten, dan Peneliti di Lab Software Engineering</p>
          </div>
        </div>
      </div>
    </div>
</section>

<section id="team" class="team-style-1 pt-100 pb-70">
    <div class="container">
        <div class="row">
            <?php
            $query = "SELECT * FROM anggota ORDER BY id DESC";
            $result = pg_query($koneksi, $query);
            
            if ($result && pg_num_rows($result) > 0) {
                $anggota_map = [];
                while ($row = pg_fetch_assoc($result)) {
            ?>
            <div class="col-xl-4 col-lg-4 col-md-6">
                <div class="single-team wow fadeInUp" data-wow-delay=".2s">
                    <div class="image">
                        <?php 
                        $foto_raw = $row['foto'] ?? '';
                        $is_abs = preg_match('/^https?:\/\//', $foto_raw) || (strlen($foto_raw) > 0 && $foto_raw[0] === '/');
                        $foto_src = $is_abs ? $foto_raw : ($foto_raw ? '../../' . $foto_raw : 'https://via.placeholder.com/300');
                        ?>
                        <img src="<?php echo htmlspecialchars($foto_src); ?>" alt="<?php echo htmlspecialchars($row['nama']); ?>" loading="lazy" width="300" height="300" onerror="this.src='https://via.placeholder.com/300?text=<?php echo urlencode(substr($row['nama'], 0, 1)); ?>'">
                    </div>
                    <div class="info">
                        <h6><?php echo htmlspecialchars($row['nama']); ?></h6>
                        <p><?php echo htmlspecialchars($row['jabatan'] ?? 'Anggota'); ?></p>
                        <ul class="socials">
                            <?php if (!empty($row['instagram'])): ?>
                            <li> <a href="<?php echo htmlspecialchars($row['instagram']); ?>" target="_blank" rel="noopener"> <i class="lni lni-instagram-filled"></i> </a> </li>
                            <?php endif; ?>
                            <?php if (!empty($row['linkedin'])): ?>
                            <li> <a href="<?php echo htmlspecialchars($row['linkedin']); ?>" target="_blank" rel="noopener"> <i class="lni lni-linkedin-original"></i> </a> </li>
                            <?php endif; ?>
                        </ul>
                        <a href="javascript:void(0)" onclick="openPersonilDetail(<?php echo (int)$row['id']; ?>)" class="button button-sm radius-10 border-button mt-20">Lihat Profil</a>
                    </div>
                </div>
            </div>
            <?php
                // Siapkan data untuk modal detail
                $anggota_map[(string)$row['id']] = [
                    'id' => (int)$row['id'],
                    'nama' => $row['nama'] ?? '',
                    'jabatan' => $row['jabatan'] ?? '',
                    'foto' => $row['foto'] ?? '',
                    'deskripsi' => $row['deskripsi'] ?? '',
                    'created_at' => $row['created_at'] ?? '',
                    'updated_at' => $row['updated_at'] ?? ''
                ];
                }
            } else {
            ?>
            <div class="col-12">
                <p class="text-center" style="color: #717791;">
                    Belum ada data anggota.
                </p>
            </div>
            <?php
            }
            ?>
        </div>
    </div>
</section>

<!-- Styling modal sudah dipindah ke personil.css -->

<?php if (!empty($anggota_map)): ?>
<script>
window.personilData = <?php echo json_encode($anggota_map); ?>;
</script>
<script src="../../assets/js/classes/PersonilModal.js"></script>

<!-- Modal Detail Personil -->
<div id="personilOverlay" onclick="if(event.target===this){ closePersonilModal(); }">
	<div class="personil-modal">
		<div class="personil-header">
			<h4><i class="lni lni-user" style="color:#2F80ED;"></i> Detail Personil</h4>
			<button class="personil-close" title="Tutup" onclick="closePersonilModal()">&times;</button>
		</div>
		<div class="personil-body">
			<div class="personil-grid">
				<img id="personilFoto" class="personil-photo" src="https://via.placeholder.com/300" alt="Foto Personil" loading="lazy" width="300" height="300">
				<div>
					<h5 id="personilNama" class="personil-name">-</h5>
					<div id="personilJabatan" class="personil-role">Anggota</div>
					<div id="personilDeskripsi" class="personil-desc">-</div>
					<div id="personilMeta" class="personil-meta"></div>
				</div>
			</div>
		</div>
		<div class="personil-footer">
			<button class="button radius-10 border-button" onclick="closePersonilModal()">Tutup</button>
		</div>
	</div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../../template/footer.php'; ?>

