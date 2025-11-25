/**
 * PersonilModal - Kelas untuk mengelola modal detail personil
 */
class PersonilModal {
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
        this.overlay = document.getElementById('personilOverlay');
        
        // Expose global function for backward compatibility
        if (typeof window !== 'undefined') {
            window.openPersonilDetail = (id) => this.openDetail(id);
            window.closePersonilModal = () => this.close();
        }
    }

    setData(data) {
        this.data = data;
    }

    openDetail(id) {
        const data = (this.data && this.data[id]) ? this.data[id] : null;
        if (!data) {
            alert('Data personil tidak ditemukan.');
            return;
        }

        if (!this.overlay) {
            this.overlay = document.getElementById('personilOverlay');
        }

        const img = document.getElementById('personilFoto');
        const nama = document.getElementById('personilNama');
        const jabatan = document.getElementById('personilJabatan');
        const deskripsi = document.getElementById('personilDeskripsi');
        const meta = document.getElementById('personilMeta');

        if (img) {
            img.src = data.foto && data.foto.length ? data.foto : 'https://via.placeholder.com/300';
            img.alt = data.nama || 'Foto Personil';
        }
        
        if (nama) nama.textContent = data.nama || '-';
        if (jabatan) jabatan.textContent = data.jabatan || 'Anggota';
        if (deskripsi) deskripsi.textContent = data.deskripsi && data.deskripsi.length ? data.deskripsi : '-';

        if (meta) {
            const metaText = [];
            if (data.created_at) metaText.push('Dibuat: ' + data.created_at);
            if (data.updated_at) metaText.push('Diubah: ' + data.updated_at);
            meta.textContent = metaText.join(' • ');
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

// Initialize PersonilModal if data is available
if (typeof window !== 'undefined' && window.personilData) {
    window.personilModal = new PersonilModal(window.personilData);
}

