<?php
// Halaman Penelitian dan Publikasi

// --- 1. SETUP & KONEKSI ---
require_once __DIR__ . '/../../config/koneksi.php'; 

// --- 2. AMBIL DATA PENELITIAN DARI DATABASE ---
$penelitian_list = [];
if ($koneksi) {
    $query_penelitian = "SELECT id, judul, deskripsi, tahun, status, jumlah_tim, peneliti_utama, kategori, progress 
                        FROM penelitian 
                        ORDER BY tahun DESC, id DESC";
    $result_penelitian = pg_query($koneksi, $query_penelitian);
    if ($result_penelitian && pg_num_rows($result_penelitian) > 0) {
        while ($row = pg_fetch_assoc($result_penelitian)) {
            $penelitian_list[] = $row;
        }
    }
}

// --- 3. AMBIL DATA PUBLIKASI DARI DATABASE ---
$publikasi_list = [];
if ($koneksi) {
    $query_publikasi = "SELECT id, judul, tipe, tahun, penulis_utama, nama_journal_conference, volume, issue, halaman, doi, status, quartile, scopus_indexed 
                        FROM publikasi 
                        WHERE status = 'published' 
                        ORDER BY tahun DESC, id DESC";
    $result_publikasi = pg_query($koneksi, $query_publikasi);
    if ($result_publikasi && pg_num_rows($result_publikasi) > 0) {
        while ($row = pg_fetch_assoc($result_publikasi)) {
            $publikasi_list[] = $row;
        }
    }
}

?>

<?php 
// Deteksi halaman penelitian-dan-publikasi untuk inject CSS khusus di head
$is_penelitian_publikasi_page = true;
?>
<?php include __DIR__ . '/../../template/header.php'; ?>

<section id="penelitian-publikasi-content" class="pb-70">
  <div class="container">
    <div class="row">
      <div class="col-lg-12">
        
        <!-- Tab Navigation - Clean Design -->
        <div class="research-tabs-container wow fadeInUp" data-wow-delay=".2s">
          <div class="tab-buttons" id="researchTabs" role="tablist">
            <button class="tab-btn" id="penelitian-tab" data-bs-toggle="tab" data-bs-target="#penelitian" type="button" role="tab" aria-controls="penelitian" aria-selected="false">
              <i class="lni lni-microscope"></i>
              <span>Penelitian</span>
            </button>
            <button class="tab-btn active" id="publikasi-tab" data-bs-toggle="tab" data-bs-target="#publikasi" type="button" role="tab" aria-controls="publikasi" aria-selected="true">
              <i class="lni lni-book"></i>
              <span>Publikasi</span>
            </button>
          </div>
        </div>

        <!-- Tab Content -->
        <div class="tab-content-container wow fadeInUp" data-wow-delay=".3s">
          <div class="tab-content" id="researchTabsContent">
            
            <!-- Penelitian Tab -->
            <div class="tab-pane fade" id="penelitian" role="tabpanel" aria-labelledby="penelitian-tab">
              <div class="tab-content-inner">
                <h2 class="tab-title">Penelitian</h2>
                <p class="tab-intro">Daftar penelitian yang sedang dan telah dilakukan oleh Laboratorium Software Engineering:</p>
                
                <?php if (count($penelitian_list) > 0): ?>
                <div class="research-list">
                  <?php foreach ($penelitian_list as $index => $p): ?>
                  <div class="research-item-card">
                    <div class="research-header">
                      <span class="research-year-badge"><?php echo htmlspecialchars($p['tahun']); ?></span>
                      <span class="research-status-badge <?php echo $p['status'] == 'ongoing' ? 'status-ongoing' : 'status-completed'; ?>">
                        <?php echo $p['status'] == 'ongoing' ? 'Sedang Berlangsung' : 'Selesai'; ?>
                      </span>
                    </div>
                    <h3 class="research-title-main"><?php echo htmlspecialchars($p['judul']); ?></h3>
                    <?php if (!empty($p['deskripsi'])): ?>
                      <p class="research-description-text"><?php echo nl2br(htmlspecialchars($p['deskripsi'])); ?></p>
                    <?php endif; ?>
                    <div class="research-details-grid">
                      <?php if (!empty($p['peneliti_utama'])): ?>
                        <div class="research-detail-item">
                          <i class="lni lni-user"></i>
                          <span><?php echo htmlspecialchars($p['peneliti_utama']); ?></span>
                        </div>
                      <?php endif; ?>
                      <?php if (!empty($p['kategori'])): ?>
                        <div class="research-detail-item">
                          <i class="lni lni-folder"></i>
                          <span><?php echo htmlspecialchars($p['kategori']); ?></span>
                        </div>
                      <?php endif; ?>
                      <?php if (!empty($p['jumlah_tim']) && $p['jumlah_tim'] > 0): ?>
                        <div class="research-detail-item">
                          <i class="lni lni-users"></i>
                          <span><?php echo (int)$p['jumlah_tim']; ?> Peneliti</span>
                        </div>
                      <?php endif; ?>
                      <?php if (!empty($p['progress'])): ?>
                        <div class="research-detail-item">
                          <i class="lni lni-bar-chart"></i>
                          <span>Progress: <?php echo (int)$p['progress']; ?>%</span>
                        </div>
                      <?php endif; ?>
                    </div>
                  </div>
                  <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="empty-state" style="text-align: center; padding: 60px 20px;">
                  <i class="lni lni-microscope" style="font-size: 64px; color: #dee2e6; margin-bottom: 20px;"></i>
                  <h3 style="color: #6c757d; margin-bottom: 10px;">Belum Ada Data Penelitian</h3>
                  <p style="color: #adb5bd;">Data penelitian akan ditampilkan di sini setelah ditambahkan melalui admin panel.</p>
                </div>
                <?php endif; ?>
              </div>
            </div>

            <!-- Publikasi Tab (Active) -->
            <div class="tab-pane fade show active" id="publikasi" role="tabpanel" aria-labelledby="publikasi-tab">
              <div class="tab-content-inner">
                <h2 class="tab-title">Publikasi</h2>
                <p class="tab-intro">Berikut ini adalah daftar publikasi di laboratorium Rekayasa Perangkat Lunak:</p>
                
                <?php if (count($publikasi_list) > 0): ?>
                <div class="publications-grid">
                  <?php foreach ($publikasi_list as $index => $pub): ?>
                  <div class="pub-item-card">
                    <div class="pub-number-badge"><?php echo str_pad($index + 1, 2, '0', STR_PAD_LEFT); ?></div>
                    <div class="pub-content">
                      <h3 class="pub-title-main"><?php echo htmlspecialchars($pub['judul']); ?></h3>
                      <?php if (!empty($pub['nama_journal_conference'])): ?>
                        <p class="pub-journal">
                          <i class="lni lni-book"></i>
                          <?php echo htmlspecialchars($pub['nama_journal_conference']); ?>
                          <?php if (!empty($pub['volume']) || !empty($pub['issue'])): ?>
                            <?php if (!empty($pub['volume'])): ?><span class="pub-volume">Vol. <?php echo htmlspecialchars($pub['volume']); ?></span><?php endif; ?>
                            <?php if (!empty($pub['issue'])): ?><span class="pub-issue">No. <?php echo htmlspecialchars($pub['issue']); ?></span><?php endif; ?>
                          <?php endif; ?>
                          <?php if (!empty($pub['halaman'])): ?>
                            <span class="pub-pages">pp. <?php echo htmlspecialchars($pub['halaman']); ?></span>
                          <?php endif; ?>
                        </p>
                      <?php endif; ?>
                      <?php if (!empty($pub['penulis_utama'])): ?>
                        <div class="pub-author">
                          <i class="lni lni-user"></i>
                          <span><?php echo htmlspecialchars($pub['penulis_utama']); ?></span>
                        </div>
                      <?php endif; ?>
                      <div class="pub-meta-tags">
                        <span class="pub-type-badge type-<?php echo $pub['tipe'] == 'journal' ? 'journal' : 'conference'; ?>">
                          <i class="lni <?php echo $pub['tipe'] == 'journal' ? 'lni-book' : 'lni-presentation'; ?>"></i>
                          <?php echo ucfirst($pub['tipe']); ?>
                        </span>
                        <?php if (!empty($pub['quartile'])): ?>
                          <span class="pub-quartile-badge">
                            <?php echo htmlspecialchars($pub['quartile']); ?>
                          </span>
                        <?php endif; ?>
                        <?php if (!empty($pub['scopus_indexed']) && $pub['scopus_indexed']): ?>
                          <span class="pub-scopus-badge">
                            <i class="lni lni-checkmark-circle"></i> Scopus
                          </span>
                        <?php endif; ?>
                        <span class="pub-year-badge">
                          <i class="lni lni-calendar"></i>
                          <?php echo htmlspecialchars($pub['tahun']); ?>
                        </span>
                      </div>
                      <?php if (!empty($pub['doi'])): ?>
                        <div class="pub-doi-link">
                          <a href="https://doi.org/<?php echo htmlspecialchars($pub['doi']); ?>" target="_blank" class="doi-link">
                            <i class="lni lni-link"></i>
                            <span>DOI: <?php echo htmlspecialchars($pub['doi']); ?></span>
                            <i class="lni lni-arrow-right"></i>
                          </a>
                        </div>
                      <?php endif; ?>
                    </div>
                  </div>
                  <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="empty-state" style="text-align: center; padding: 60px 20px;">
                  <i class="lni lni-book" style="font-size: 64px; color: #dee2e6; margin-bottom: 20px;"></i>
                  <h3 style="color: #6c757d; margin-bottom: 10px;">Belum Ada Data Publikasi</h3>
                  <p style="color: #adb5bd;">Data publikasi akan ditampilkan di sini setelah ditambahkan melalui admin panel.</p>
                </div>
                <?php endif; ?>
              </div>
            </div>

          </div>
        </div>

      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/../../template/footer.php'; ?>

<script>
// Enhanced tab functionality
document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    // Remove active class from all buttons and panes
    function resetTabs() {
        tabButtons.forEach(function(btn) {
            btn.classList.remove('active');
            btn.setAttribute('aria-selected', 'false');
        });
        tabPanes.forEach(function(pane) {
            pane.classList.remove('show', 'active');
        });
    }
    
    // Activate tab
    function activateTab(button) {
        const targetId = button.getAttribute('data-bs-target');
        const targetPane = document.querySelector(targetId);
        
        resetTabs();
        
        button.classList.add('active');
        button.setAttribute('aria-selected', 'true');
        
        if (targetPane) {
            setTimeout(function() {
                targetPane.classList.add('show', 'active');
            }, 50);
        }
    }
    
    // Add click handlers
    tabButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            activateTab(this);
        });
    });
    
    // Initialize active tab
    const activeTab = document.querySelector('.tab-btn.active');
    if (activeTab) {
        activateTab(activeTab);
    }
    
    // Keyboard navigation
    tabButtons.forEach(function(button, index) {
        button.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') {
                e.preventDefault();
                const buttons = Array.from(tabButtons);
                const currentIndex = buttons.indexOf(this);
                let nextIndex;
                
                if (e.key === 'ArrowRight') {
                    nextIndex = (currentIndex + 1) % buttons.length;
                } else {
                    nextIndex = (currentIndex - 1 + buttons.length) % buttons.length;
                }
                
                buttons[nextIndex].focus();
                activateTab(buttons[nextIndex]);
            }
        });
    });
});
</script>

