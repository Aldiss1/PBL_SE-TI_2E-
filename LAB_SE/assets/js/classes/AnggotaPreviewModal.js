/**
 * AnggotaPreviewModal - Kelas untuk mengelola modal preview anggota
 */
class AnggotaPreviewModal {
    constructor(data = {}) {
        this.data = data;
        this.overlay = null;
        this.init();
    }

    init() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setupElements());
        } else {
            this.setupElements();
        }
    }

    setupElements() {
        this.overlay = document.getElementById('anggotaPreviewOverlay');
        
        // Expose global functions for backward compatibility
        if (typeof window !== 'undefined') {
            window.openPreviewAnggota = (id) => this.openPreview(id);
            window.closePreviewAnggota = () => this.close();
        }
    }

    setData(data) {
        this.data = data;
    }

    openPreview(id) {
        const data = (this.data && this.data[id]) ? this.data[id] : null;
        if (!data) {
            if (window.toast) {
                window.toast.error('Data anggota tidak ditemukan', 'Error');
            } else {
                alert('Data tidak ditemukan');
            }
            return;
        }

        const nama = document.getElementById('apNama');
        const jabatan = document.getElementById('apJabatan');
        const deskripsi = document.getElementById('apDeskripsi');
        const img = document.getElementById('apFoto');
        const initial = document.getElementById('apInitial');
        const soc = document.getElementById('apSosmed');

        // Set nama
        if (nama) {
            nama.textContent = data.nama || '-';
        }

        // Set jabatan
        if (jabatan) {
            if (data.jabatan && data.jabatan.trim() !== '') {
                jabatan.innerHTML = `<span class="badge bg-primary fs-6">${data.jabatan}</span>`;
            } else {
                jabatan.innerHTML = '<span class="badge bg-secondary fs-6">Belum ada jabatan</span>';
            }
        }

        // Set deskripsi
        if (deskripsi) {
            if (data.deskripsi && data.deskripsi.trim() !== '') {
                deskripsi.innerHTML = `<p class="text-muted mb-0" style="line-height: 1.6;">${data.deskripsi}</p>`;
            } else {
                deskripsi.innerHTML = '<p class="text-muted mb-0 fst-italic">Tidak ada deskripsi</p>';
            }
        }
        
        // Set foto atau initial - CARA PALING SEDERHANA DAN PASTI JALAN
        if (!img || !initial) {
            console.error('Elemen img atau initial tidak ditemukan!');
            return;
        }
        
        // Get foto URL
        let fotoUrl = data.foto_resolved || data.foto || '';
        if (fotoUrl) fotoUrl = fotoUrl.trim();
        
        // Get initial character
        const initialChar = data.nama ? data.nama.charAt(0).toUpperCase() : '?';
        initial.textContent = initialChar;
        
        // Check if foto exists
        const hasFoto = fotoUrl && 
                       fotoUrl !== '' && 
                       fotoUrl !== '../../' &&
                       fotoUrl !== '../../uploads/anggota/' &&
                       !fotoUrl.endsWith('/');
        
        // Function to show initial
        const showInitial = function() {
            if (initial) {
                initial.style.display = 'flex';
                initial.style.opacity = '1';
                initial.style.visibility = 'visible';
            }
            if (img) {
                img.style.display = 'none';
                img.style.opacity = '0';
                img.style.visibility = 'hidden';
            }
        };
        
        // Function to show foto
        const showFoto = function() {
            if (img) {
                img.style.display = 'block';
                img.style.opacity = '1';
                img.style.visibility = 'visible';
            }
            if (initial) {
                initial.style.display = 'none';
                initial.style.opacity = '0';
                initial.style.visibility = 'hidden';
            }
        };
        
        // Always show initial first
        showInitial();
        
        if (hasFoto) {
            // Remove old handlers
            img.onerror = null;
            img.onload = null;
            
            // Set handlers
            img.onerror = function() {
                showInitial();
            };
            
            img.onload = function() {
                showFoto();
            };
            
            // Set source - INI PENTING!
            img.src = fotoUrl;
            img.alt = data.nama || 'Foto';
            
            // Check cached immediately
            if (img.complete && img.naturalHeight > 0 && img.naturalWidth > 0) {
                showFoto();
            } else {
                // Check again after a moment
                setTimeout(function() {
                    if (img.complete) {
                        if (img.naturalHeight > 0 && img.naturalWidth > 0) {
                            showFoto();
                        } else {
                            showInitial();
                        }
                    }
                }, 100);
            }
        }

        // Set social media
        if (soc) {
            soc.innerHTML = '';
            if (data.instagram) {
                const a = document.createElement('a');
                a.href = data.instagram;
                a.target = '_blank';
                a.rel = 'noopener noreferrer';
                a.className = 'btn btn-outline-danger btn-sm';
                a.innerHTML = '<i class="lni lni-instagram-filled me-1"></i> Instagram';
                soc.appendChild(a);
            }
            if (data.linkedin) {
                const a2 = document.createElement('a');
                a2.href = data.linkedin;
                a2.target = '_blank';
                a2.rel = 'noopener noreferrer';
                a2.className = 'btn btn-outline-primary btn-sm';
                a2.innerHTML = '<i class="lni lni-linkedin-original me-1"></i> LinkedIn';
                soc.appendChild(a2);
            }
            if (!data.instagram && !data.linkedin) {
                soc.innerHTML = '<small class="text-muted">Tidak ada media sosial</small>';
            }
        }

        // Show modal
        if (this.overlay) {
            this.overlay.style.display = 'flex';
            this.overlay.classList.add('show');
            document.body.style.overflow = 'hidden'; // Prevent background scrolling
        }
    }

    close() {
        if (this.overlay) {
            this.overlay.style.display = 'none';
            this.overlay.classList.remove('show');
            document.body.style.overflow = ''; // Restore scrolling
        }
    }
}

// Initialize AnggotaPreviewModal if data is available
if (typeof window !== 'undefined' && window.anggotaData) {
    window.anggotaPreviewModal = new AnggotaPreviewModal(window.anggotaData);
}

