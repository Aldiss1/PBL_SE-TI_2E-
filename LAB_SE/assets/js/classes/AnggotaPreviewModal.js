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
            alert('Data tidak ditemukan');
            return;
        }

        if (!this.overlay) {
            this.overlay = document.getElementById('anggotaPreviewOverlay');
        }

        const nama = document.getElementById('apNama');
        const jabatan = document.getElementById('apJabatan');
        const img = document.getElementById('apFoto');
        const soc = document.getElementById('apSosmed');

        if (nama) nama.textContent = data.nama || '-';
        if (jabatan) jabatan.textContent = data.jabatan || '-';
        
        if (img) {
            img.src = data.foto_resolved && data.foto_resolved.length ? data.foto_resolved : 'https://via.placeholder.com/300';
            img.alt = data.nama || 'Foto';
        }

        if (soc) {
            soc.innerHTML = '';
            if (data.instagram) {
                const a = document.createElement('a');
                a.href = data.instagram;
                a.target = '_blank';
                a.rel = 'noopener';
                a.innerHTML = '<i class="lni lni-instagram-filled"></i> Instagram';
                soc.appendChild(a);
            }
            if (data.linkedin) {
                const a2 = document.createElement('a');
                a2.href = data.linkedin;
                a2.target = '_blank';
                a2.rel = 'noopener';
                a2.innerHTML = '<i class="lni lni-linkedin-original"></i> LinkedIn';
                soc.appendChild(a2);
            }
        }

        if (this.overlay) {
            this.overlay.style.display = 'flex';
        }
    }

    close() {
        if (this.overlay) {
            this.overlay.style.display = 'none';
        }
    }
}

// Initialize AnggotaPreviewModal if data is available
if (typeof window !== 'undefined' && window.anggotaData) {
    window.anggotaPreviewModal = new AnggotaPreviewModal(window.anggotaData);
}

