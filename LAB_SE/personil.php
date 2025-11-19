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
            $query = "SELECT * FROM anggota ORDER BY id DESC";
            $result = pg_query($koneksi, $query);
            
            if ($result && pg_num_rows($result) > 0) {
                $anggota_map = [];
                while ($row = pg_fetch_assoc($result)) {
            ?>
            <div class="col-xl-4 col-lg-4 col-md-6">
                <div class="single-team wow fadeInUp" data-wow-delay=".2s">
                    <div class="image">
                        <img src="<?php echo isset($row['foto']) && $row['foto'] ? $row['foto'] : 'https://via.placeholder.com/300'; ?>" alt="<?php echo htmlspecialchars($row['nama']); ?>" loading="lazy" width="300" height="300">
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

<style>
#personilOverlay { display:none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1100; align-items: center; justify-content: center; padding: 20px; }
.personil-modal { background: #fff; border-radius: 12px; max-width: 760px; width: 100%; box-shadow: 0 10px 30px rgba(0,0,0,0.2); overflow: hidden; }
.personil-header { padding: 16px 20px; border-bottom: 1px solid #f1f1f1; display: flex; align-items: center; justify-content: space-between; gap: 10px; }
.personil-header h4 { margin: 0; color: #2c3e50; font-weight: 700; font-size: 18px; display: flex; align-items: center; gap: 10px; }
.personil-close { background: transparent; border: none; font-size: 20px; cursor: pointer; color: #6c757d; }
.personil-body { padding: 18px 20px; }
.personil-grid { display: grid; grid-template-columns: 180px 1fr; gap: 16px 20px; align-items: start; }
.personil-photo { width: 180px; height: 180px; border-radius: 12px; object-fit: cover; border: 1px solid #eee; background: #fafafa; }
.personil-name { margin: 0 0 6px 0; font-size: 20px; font-weight: 800; color: #2c3e50; }
.personil-role { margin: 0 0 12px 0; font-size: 14px; color: #6c757d; font-weight: 700; }
.personil-desc { font-size: 14px; color: #2c3e50; line-height: 1.6; white-space: pre-wrap; }
.personil-meta { margin-top: 12px; font-size: 12px; color: #8b8f97; }
.personil-footer { padding: 12px 20px; background: #fafafa; border-top: 1px solid #f1f1f1; display: flex; justify-content: flex-end; gap: 8px; }
</style>

<?php if (!empty($anggota_map)): ?>
<script>
window.personilData = <?php echo json_encode($anggota_map); ?>;
function openPersonilDetail(id) {
	var data = (window.personilData && window.personilData[id]) ? window.personilData[id] : null;
	if (!data) { alert('Data personil tidak ditemukan.'); return; }

	var overlay = document.getElementById('personilOverlay');
	var img = document.getElementById('personilFoto');
	var nama = document.getElementById('personilNama');
	var jabatan = document.getElementById('personilJabatan');
	var deskripsi = document.getElementById('personilDeskripsi');
	var meta = document.getElementById('personilMeta');

	img.src = data.foto && data.foto.length ? data.foto : 'https://via.placeholder.com/300';
	img.alt = data.nama || 'Foto Personil';
	nama.textContent = data.nama || '-';
	jabatan.textContent = data.jabatan || 'Anggota';
	deskripsi.textContent = data.deskripsi && data.deskripsi.length ? data.deskripsi : '-';

	var metaText = [];
	if (data.created_at) metaText.push('Dibuat: ' + data.created_at);
	if (data.updated_at) metaText.push('Diubah: ' + data.updated_at);
	meta.textContent = metaText.join(' • ');

	overlay.style.display = 'flex';
}
function closePersonilModal(){ document.getElementById('personilOverlay').style.display='none'; }
</script>

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

<?php include __DIR__ . '/template/footer.php'; ?>

