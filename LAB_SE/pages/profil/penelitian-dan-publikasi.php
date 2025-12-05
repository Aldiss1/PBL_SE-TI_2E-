<?php
// Halaman Penelitian dan Publikasi

// --- 1. SETUP & KONEKSI ---
require_once __DIR__ . '/../../config/koneksi.php'; 

?>

<?php 
// Deteksi halaman penelitian-dan-publikasi untuk inject CSS khusus di head
$is_penelitian_publikasi_page = true;
?>
<?php include __DIR__ . '/../../template/header.php'; ?>

<section id="penelitian-publikasi-content" class="pt-150 pb-70">
  <div class="container">
    <div class="row">
      <div class="col-lg-12">
        
        <!-- Breadcrumbs - Simplified -->
        <nav aria-label="breadcrumb" class="breadcrumb-nav wow fadeInUp" data-wow-delay=".1s">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo LABSE_BASE_PATH; ?>/index.php">HOME</a></li>
            <li class="breadcrumb-item"><a href="#">LAB-RPL</a></li>
            <li class="breadcrumb-item active">PENELITIAN DAN PUBLIKASI LAB RPL</li>
          </ol>
        </nav>

        <!-- Tab Navigation - Clean Design -->
        <div class="research-tabs-container wow fadeInUp" data-wow-delay=".2s">
          <div class="tab-buttons" id="researchTabs" role="tablist">
            <button class="tab-btn" id="research-areas-tab" data-bs-toggle="tab" data-bs-target="#research-areas" type="button" role="tab" aria-controls="research-areas" aria-selected="false">
              <i class="lni lni-target"></i>
              <span>Research Areas</span>
            </button>
            <button class="tab-btn" id="penelitian-tab" data-bs-toggle="tab" data-bs-target="#penelitian" type="button" role="tab" aria-controls="penelitian" aria-selected="false">
              <i class="lni lni-microscope"></i>
              <span>Penelitian</span>
            </button>
            <button class="tab-btn" id="pengabdian-tab" data-bs-toggle="tab" data-bs-target="#pengabdian" type="button" role="tab" aria-controls="pengabdian" aria-selected="false">
              <i class="lni lni-handshake"></i>
              <span>Pengabdian</span>
            </button>
            <button class="tab-btn active" id="publikasi-tab" data-bs-toggle="tab" data-bs-target="#publikasi" type="button" role="tab" aria-controls="publikasi" aria-selected="true">
              <i class="lni lni-book"></i>
              <span>Publikasi</span>
            </button>
            <button class="tab-btn" id="produk-inovasi-tab" data-bs-toggle="tab" data-bs-target="#produk-inovasi" type="button" role="tab" aria-controls="produk-inovasi" aria-selected="false">
              <i class="lni lni-bulb"></i>
              <span>Produk Inovasi</span>
            </button>
          </div>
        </div>

        <!-- Tab Content -->
        <div class="tab-content-container wow fadeInUp" data-wow-delay=".3s">
          <div class="tab-content" id="researchTabsContent">
            
            <!-- Research Areas Tab -->
            <div class="tab-pane fade" id="research-areas" role="tabpanel" aria-labelledby="research-areas-tab">
              <div class="tab-content-inner">
                <h2 class="tab-title">Research Areas</h2>
                <p class="tab-intro">Laboratorium Software Engineering fokus pada beberapa area penelitian utama:</p>
                
                <div class="research-areas-grid">
                  <div class="area-card">
                    <div class="area-icon">
                      <i class="lni lni-code-alt"></i>
                    </div>
                    <h3 class="area-title">Software Testing & Quality Assurance</h3>
                    <p class="area-desc">Penelitian tentang metode pengujian perangkat lunak, automated testing, dan jaminan kualitas software.</p>
                  </div>
                  
                  <div class="area-card">
                    <div class="area-icon">
                      <i class="lni lni-search"></i>
                    </div>
                    <h3 class="area-title">Code Analysis & Retrieval</h3>
                    <p class="area-desc">Penelitian tentang analisis kode sumber, code search, dan retrieval system untuk software engineering.</p>
                  </div>
                  
                  <div class="area-card">
                    <div class="area-icon">
                      <i class="lni lni-network"></i>
                    </div>
                    <h3 class="area-title">Semantic Web & Ontology</h3>
                    <p class="area-desc">Pengembangan sistem berbasis semantic web, ontology engineering, dan knowledge representation.</p>
                  </div>
                  
                  <div class="area-card">
                    <div class="area-icon">
                      <i class="lni lni-cog"></i>
                    </div>
                    <h3 class="area-title">Software Maintenance</h3>
                    <p class="area-desc">Penelitian tentang maintenance software, feature location, dan refactoring techniques.</p>
                  </div>
                  
                  <div class="area-card">
                    <div class="area-icon">
                      <i class="lni lni-layers"></i>
                    </div>
                    <h3 class="area-title">Software Architecture</h3>
                    <p class="area-desc">Desain dan evaluasi arsitektur perangkat lunak, pattern-based development, dan architectural patterns.</p>
                  </div>
                  
                  <div class="area-card">
                    <div class="area-icon">
                      <i class="lni lni-cloud"></i>
                    </div>
                    <h3 class="area-title">Cloud Computing & Distributed Systems</h3>
                    <p class="area-desc">Penelitian tentang cloud computing, distributed systems, dan scalable software architectures.</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Penelitian Tab -->
            <div class="tab-pane fade" id="penelitian" role="tabpanel" aria-labelledby="penelitian-tab">
              <div class="tab-content-inner">
                <h2 class="tab-title">Penelitian</h2>
                <p class="tab-intro">Daftar penelitian yang sedang dan telah dilakukan oleh Laboratorium Software Engineering:</p>
                
                <div class="research-list">
                  <div class="research-card">
                    <div class="research-year">2024</div>
                    <div class="research-body">
                      <h3 class="research-title">Optimasi Algoritma Genetika untuk Test Path Generation</h3>
                      <p class="research-desc">Penelitian tentang penggunaan algoritma genetika dan decision tree (J48) untuk menghasilkan basis test paths yang optimal dalam pengujian perangkat lunak.</p>
                      <div class="research-meta">
                        <span class="research-status ongoing">Sedang Berlangsung</span>
                        <span class="research-team">Tim: 5 Peneliti</span>
                      </div>
                    </div>
                  </div>
                  
                  <div class="research-card">
                    <div class="research-year">2023</div>
                    <div class="research-body">
                      <h3 class="research-title">Sistem Retrieval Kode Sumber Menggunakan LDA</h3>
                      <p class="research-desc">Pengembangan sistem untuk mencari dan mengambil kode sumber dari platform seperti StackOverflow menggunakan teknik Latent Dirichlet Allocation (LDA).</p>
                      <div class="research-meta">
                        <span class="research-status completed">Selesai</span>
                        <span class="research-team">Tim: 4 Peneliti</span>
                      </div>
                    </div>
                  </div>
                  
                  <div class="research-card">
                    <div class="research-year">2023</div>
                    <div class="research-body">
                      <h3 class="research-title">Ontology-Based Food Recommendation System</h3>
                      <p class="research-desc">Pengembangan sistem rekomendasi makanan untuk penderita diabetes menggunakan semantic matching dan ontology engineering.</p>
                      <div class="research-meta">
                        <span class="research-status completed">Selesai</span>
                        <span class="research-team">Tim: 6 Peneliti</span>
                      </div>
                    </div>
                  </div>
                  
                  <div class="research-card">
                    <div class="research-year">2022</div>
                    <div class="research-body">
                      <h3 class="research-title">Feature Location dalam Web-Based Applications</h3>
                      <p class="research-desc">Penelitian tentang teknik feature location untuk memudahkan maintenance aplikasi berbasis web dengan pendekatan static dan dynamic analysis.</p>
                      <div class="research-meta">
                        <span class="research-status completed">Selesai</span>
                        <span class="research-team">Tim: 4 Peneliti</span>
                      </div>
                    </div>
                  </div>
                  
                  <div class="research-card">
                    <div class="research-year">2024</div>
                    <div class="research-body">
                      <h3 class="research-title">Microservices Architecture Pattern Analysis</h3>
                      <p class="research-desc">Analisis pola arsitektur microservices untuk aplikasi enterprise dengan fokus pada scalability, maintainability, dan performance optimization.</p>
                      <div class="research-meta">
                        <span class="research-status ongoing">Sedang Berlangsung</span>
                        <span class="research-team">Tim: 7 Peneliti</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Pengabdian Tab -->
            <div class="tab-pane fade" id="pengabdian" role="tabpanel" aria-labelledby="pengabdian-tab">
              <div class="tab-content-inner">
                <h2 class="tab-title">Pengabdian Masyarakat</h2>
                <p class="tab-intro">Kegiatan pengabdian masyarakat yang telah dilakukan oleh Laboratorium Software Engineering:</p>
                
                <div class="pengabdian-list">
                  <div class="pengabdian-card">
                    <div class="pengabdian-date">
                      <span class="date-day">15</span>
                      <span class="date-month">Mar</span>
                      <span class="date-year">2024</span>
                    </div>
                    <div class="pengabdian-body">
                      <h3 class="pengabdian-title">Pelatihan Pemrograman Web untuk UMKM</h3>
                      <p class="pengabdian-desc">Pelatihan pembuatan website sederhana untuk usaha mikro, kecil, dan menengah di wilayah Malang. Peserta diajarkan dasar-dasar HTML, CSS, dan penggunaan CMS.</p>
                      <div class="pengabdian-meta">
                        <span class="pengabdian-location"><i class="lni lni-map-marker"></i> Malang, Jawa Timur</span>
                        <span class="pengabdian-participants"><i class="lni lni-users"></i> 25 Peserta</span>
                      </div>
                    </div>
                  </div>
                  
                  <div class="pengabdian-card">
                    <div class="pengabdian-date">
                      <span class="date-day">22</span>
                      <span class="date-month">Feb</span>
                      <span class="date-year">2024</span>
                    </div>
                    <div class="pengabdian-body">
                      <h3 class="pengabdian-title">Workshop Software Testing untuk Industri</h3>
                      <p class="pengabdian-desc">Workshop tentang teknik pengujian perangkat lunak untuk perusahaan teknologi di Malang. Materi meliputi unit testing, integration testing, dan automated testing.</p>
                      <div class="pengabdian-meta">
                        <span class="pengabdian-location"><i class="lni lni-map-marker"></i> Malang, Jawa Timur</span>
                        <span class="pengabdian-participants"><i class="lni lni-users"></i> 30 Peserta</span>
                      </div>
                    </div>
                  </div>
                  
                  <div class="pengabdian-card">
                    <div class="pengabdian-date">
                      <span class="date-day">10</span>
                      <span class="date-month">Des</span>
                      <span class="date-year">2023</span>
                    </div>
                    <div class="pengabdian-body">
                      <h3 class="pengabdian-title">Sosialisasi Keamanan Cyber untuk Sekolah</h3>
                      <p class="pengabdian-desc">Program sosialisasi tentang pentingnya keamanan cyber dan cara melindungi data pribadi di internet untuk siswa SMA/SMK di Malang Raya.</p>
                      <div class="pengabdian-meta">
                        <span class="pengabdian-location"><i class="lni lni-map-marker"></i> Malang, Jawa Timur</span>
                        <span class="pengabdian-participants"><i class="lni lni-users"></i> 150 Peserta</span>
                      </div>
                    </div>
                  </div>
                  
                  <div class="pengabdian-card">
                    <div class="pengabdian-date">
                      <span class="date-day">05</span>
                      <span class="date-month">Nov</span>
                      <span class="date-year">2023</span>
                    </div>
                    <div class="pengabdian-body">
                      <h3 class="pengabdian-title">Pengembangan Sistem Informasi Desa</h3>
                      <p class="pengabdian-desc">Pengembangan sistem informasi desa untuk mengelola data kependudukan, keuangan, dan pelayanan publik di Desa X, Kabupaten Malang.</p>
                      <div class="pengabdian-meta">
                        <span class="pengabdian-location"><i class="lni lni-map-marker"></i> Kabupaten Malang</span>
                        <span class="pengabdian-participants"><i class="lni lni-users"></i> 1 Desa</span>
                      </div>
                    </div>
                  </div>
                  
                  <div class="pengabdian-card">
                    <div class="pengabdian-date">
                      <span class="date-day">18</span>
                      <span class="date-month">Agu</span>
                      <span class="date-year">2023</span>
                    </div>
                    <div class="pengabdian-body">
                      <h3 class="pengabdian-title">Pelatihan Git & Version Control</h3>
                      <p class="pengabdian-desc">Pelatihan penggunaan Git dan GitHub untuk developer lokal dan mahasiswa. Materi mencakup dasar-dasar version control, branching, dan collaboration.</p>
                      <div class="pengabdian-meta">
                        <span class="pengabdian-location"><i class="lni lni-map-marker"></i> Malang, Jawa Timur</span>
                        <span class="pengabdian-participants"><i class="lni lni-users"></i> 40 Peserta</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Publikasi Tab (Active) -->
            <div class="tab-pane fade show active" id="publikasi" role="tabpanel" aria-labelledby="publikasi-tab">
              <div class="tab-content-inner">
                <h2 class="tab-title">Publikasi</h2>
                <p class="tab-intro">Berikut ini adalah daftar publikasi di laboratorium Rekayasa Perangkat Lunak:</p>
                
                <div class="publications-grid">
                  <div class="pub-card">
                    <div class="pub-number">01</div>
                    <div class="pub-body">
                      <p class="pub-text">Determining basis test paths using genetic algorithm and J48. Journal International Journal of Electrical and Computer Engineering, Vol. 14, No. 3, 2024</p>
                      <div class="pub-meta">
                        <span class="pub-type">Journal</span>
                        <span class="pub-year">2024</span>
                      </div>
                    </div>
                  </div>
                  
                  <div class="pub-card">
                    <div class="pub-number">02</div>
                    <div class="pub-body">
                      <p class="pub-text">Source code retrieval on StackOverflow using LDA. International Conference on Information and Communication Technology. ICOICT 2015</p>
                      <div class="pub-meta">
                        <span class="pub-type">Conference</span>
                        <span class="pub-year">2015</span>
                      </div>
                    </div>
                  </div>
                  
                  <div class="pub-card">
                    <div class="pub-number">03</div>
                    <div class="pub-body">
                      <p class="pub-text">Ontology and semantic matching for diabetic food recommendations. 2013 International Conference on Information Technology and Electrical Engineering</p>
                      <div class="pub-meta">
                        <span class="pub-type">Conference</span>
                        <span class="pub-year">2013</span>
                      </div>
                    </div>
                  </div>
                  
                  <div class="pub-card">
                    <div class="pub-number">04</div>
                    <div class="pub-body">
                      <p class="pub-text">Maintenance Web Based Applications Using Feature Location. JITECS (Journal of Information Technology and Computer Science), Vol. 8, No. 2, 2022</p>
                      <div class="pub-meta">
                        <span class="pub-type">Journal</span>
                        <span class="pub-year">2022</span>
                      </div>
                    </div>
                  </div>
                  
                  <div class="pub-card">
                    <div class="pub-number">05</div>
                    <div class="pub-body">
                      <p class="pub-text">Automated Test Case Generation Using Machine Learning Approaches. IEEE Transactions on Software Engineering, 2023</p>
                      <div class="pub-meta">
                        <span class="pub-type">Journal</span>
                        <span class="pub-year">2023</span>
                      </div>
                    </div>
                  </div>
                  
                  <div class="pub-card">
                    <div class="pub-number">06</div>
                    <div class="pub-body">
                      <p class="pub-text">Microservices Architecture Patterns: A Comparative Study. International Conference on Software Engineering, ICSE 2024</p>
                      <div class="pub-meta">
                        <span class="pub-type">Conference</span>
                        <span class="pub-year">2024</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Produk Inovasi Tab -->
            <div class="tab-pane fade" id="produk-inovasi" role="tabpanel" aria-labelledby="produk-inovasi-tab">
              <div class="tab-content-inner">
                <h2 class="tab-title">Produk Inovasi</h2>
                <p class="tab-intro">Produk inovasi yang telah dikembangkan oleh Laboratorium Software Engineering:</p>
                
                <div class="produk-grid">
                  <div class="produk-card">
                    <div class="produk-icon">
                      <i class="lni lni-code"></i>
                    </div>
                    <h3 class="produk-title">TestPath Generator</h3>
                    <p class="produk-desc">Tool untuk generate test paths secara otomatis menggunakan algoritma genetika dan decision tree. Memudahkan proses testing dengan coverage yang optimal.</p>
                    <div class="produk-tech">
                      <span class="tech-tag">Python</span>
                      <span class="tech-tag">Genetic Algorithm</span>
                      <span class="tech-tag">J48</span>
                    </div>
                    <div class="produk-status">
                      <span class="status-badge released">Released</span>
                    </div>
                  </div>
                  
                  <div class="produk-card">
                    <div class="produk-icon">
                      <i class="lni lni-search"></i>
                    </div>
                    <h3 class="produk-title">CodeSearch Engine</h3>
                    <p class="produk-desc">Mesin pencari kode sumber berbasis LDA untuk mencari dan mengambil kode dari berbagai repository. Mendukung semantic search dan code similarity.</p>
                    <div class="produk-tech">
                      <span class="tech-tag">Java</span>
                      <span class="tech-tag">LDA</span>
                      <span class="tech-tag">Elasticsearch</span>
                    </div>
                    <div class="produk-status">
                      <span class="status-badge released">Released</span>
                    </div>
                  </div>
                  
                  <div class="produk-card">
                    <div class="produk-icon">
                      <i class="lni lni-heart"></i>
                    </div>
                    <h3 class="produk-title">DiabFood Recommender</h3>
                    <p class="produk-desc">Sistem rekomendasi makanan untuk penderita diabetes berbasis ontology dan semantic matching. Menyediakan rekomendasi makanan sehat sesuai kondisi kesehatan.</p>
                    <div class="produk-tech">
                      <span class="tech-tag">Python</span>
                      <span class="tech-tag">Ontology</span>
                      <span class="tech-tag">Flask</span>
                    </div>
                    <div class="produk-status">
                      <span class="status-badge released">Released</span>
                    </div>
                  </div>
                  
                  <div class="produk-card">
                    <div class="produk-icon">
                      <i class="lni lni-cog"></i>
                    </div>
                    <h3 class="produk-title">FeatureLocator Tool</h3>
                    <p class="produk-desc">Tool untuk menemukan lokasi fitur dalam aplikasi web. Memudahkan proses maintenance dengan analisis static dan dynamic code analysis.</p>
                    <div class="produk-tech">
                      <span class="tech-tag">JavaScript</span>
                      <span class="tech-tag">AST Analysis</span>
                      <span class="tech-tag">Node.js</span>
                    </div>
                    <div class="produk-status">
                      <span class="status-badge released">Released</span>
                    </div>
                  </div>
                  
                  <div class="produk-card">
                    <div class="produk-icon">
                      <i class="lni lni-layers"></i>
                    </div>
                    <h3 class="produk-title">MicroArch Analyzer</h3>
                    <p class="produk-desc">Tool untuk menganalisis dan memvisualisasikan arsitektur microservices. Membantu dalam evaluasi dan optimasi arsitektur aplikasi enterprise.</p>
                    <div class="produk-tech">
                      <span class="tech-tag">Python</span>
                      <span class="tech-tag">Graph Analysis</span>
                      <span class="tech-tag">D3.js</span>
                    </div>
                    <div class="produk-status">
                      <span class="status-badge development">In Development</span>
                    </div>
                  </div>
                  
                  <div class="produk-card">
                    <div class="produk-icon">
                      <i class="lni lni-bug"></i>
                    </div>
                    <h3 class="produk-title">AutoTest Framework</h3>
                    <p class="produk-desc">Framework automated testing yang terintegrasi dengan machine learning untuk generate test cases secara otomatis. Mendukung multiple testing strategies.</p>
                    <div class="produk-tech">
                      <span class="tech-tag">Python</span>
                      <span class="tech-tag">ML</span>
                      <span class="tech-tag">Selenium</span>
                    </div>
                    <div class="produk-status">
                      <span class="status-badge development">In Development</span>
                    </div>
                  </div>
                </div>
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

