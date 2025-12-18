/**
 * PendaftarModals - Kelas untuk mengelola modal pendaftar (detail, status)
 */
class PendaftarModals {
    constructor(data = {}) {
        this.data = data;
        this.pendingStatus = { id: null, status: null };
        this.eventListenersSetup = false; // Flag to prevent duplicate event listeners
        this.isSubmitting = false; // Flag to prevent multiple form submissions
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
        this.setupDetailModal();
        this.setupStatusModal();
    }

    setData(data) {
        this.data = data;
    }

    // Detail Modal
    setupDetailModal() {
        // Expose global function
        if (typeof window !== 'undefined') {
            window.viewDetail = (id) => this.viewDetail(id);
        }
    }

    viewDetail(id) {
        const data = (this.data && this.data[id]) ? this.data[id] : null;
        if (!data) {
            alert('Data pendaftar tidak ditemukan.');
            return;
        }

        const overlay = document.getElementById('detailOverlay');
        if (!overlay) return;

        // Populate
        const detailNama = document.getElementById('detailNama');
        const detailNim = document.getElementById('detailNim');
        const detailEmail = document.getElementById('detailEmail');
        const detailTanggalDaftar = document.getElementById('detailTanggalDaftar');
        const detailTanggalReview = document.getElementById('detailTanggalReview');
        const detailCatatan = document.getElementById('detailCatatan');
        const detailFoto = document.getElementById('detailFoto');
        const detailStatus = document.getElementById('detailStatus');

        if (detailNama) detailNama.textContent = data.nama || '-';
        if (detailNim) detailNim.textContent = data.nim || '-';
        
        if (detailEmail) {
            detailEmail.textContent = data.email || '-';
            detailEmail.href = data.email ? ('mailto:' + data.email) : 'javascript:void(0)';
        }
        
        if (detailTanggalDaftar) detailTanggalDaftar.textContent = data.tanggal_daftar || '-';
        if (detailTanggalReview) detailTanggalReview.textContent = data.tanggal_review ? data.tanggal_review : '-';
        if (detailCatatan) detailCatatan.textContent = data.catatan_admin ? data.catatan_admin : '-';

        // Foto
        if (detailFoto) {
            if (data.foto) {
                let fotoPath = data.foto;
                // Cek apakah path sudah absolut (http/https atau mulai dengan /)
                const isAbs = /^https?:\/\//.test(fotoPath) || fotoPath.charAt(0) === '/';
                if (!isAbs) {
                    // Path relatif, tambahkan base path
                    // Dari assets/js/classes/ ke root, jadi ../../../ atau gunakan base path jika tersedia
                    const basePath = window.LABSE_BASE_PATH || '';
                    if (basePath) {
                        fotoPath = basePath.replace(/\/$/, '') + '/' + fotoPath.replace(/^\//, '');
                    } else {
                        // Fallback: path relatif dari assets/js/classes/ ke root
                        fotoPath = '../../../' + fotoPath.replace(/^\//, '');
                    }
                }
                detailFoto.innerHTML = '<img src="'+ fotoPath +'" alt="Foto '+ (data.nama || "") +'" class="detail-image" onerror="this.parentElement.textContent=\'-\';">';
            } else {
                detailFoto.textContent = '-';
            }
        }

        // Status badge
        if (detailStatus) {
            let statusText = 'Pending';
            let statusClass = 'status-pending';
            let statusIcon = 'lni-hourglass';
            
            if (data.status === 'accepted') {
                statusText = 'Diterima';
                statusClass = 'status-accepted';
                statusIcon = 'lni-checkmark-circle';
            } else if (data.status === 'rejected') {
                statusText = 'Ditolak';
                statusClass = 'status-rejected';
                statusIcon = 'lni-close';
            }
            
            detailStatus.className = 'detail-status-badge ' + statusClass;
            detailStatus.innerHTML = '<i class="lni ' + statusIcon + '"></i> ' + statusText;
        }

        overlay.style.display = 'flex';
    }

    // Status Modal
    setupStatusModal() {
        const overlay = document.getElementById('confirmStatusOverlay');
        const title = document.getElementById('confirmStatusTitle');
        const text = document.getElementById('confirmStatusText');
        const icon = document.getElementById('confirmStatusIcon');
        const btnConfirm = document.getElementById('btnConfirmStatus');
        const btnCancel = document.getElementById('btnCancelStatus');

        if (!overlay || !btnConfirm || !btnCancel) return;

        btnCancel.addEventListener('click', () => this.closeStatusModal());
        btnConfirm.addEventListener('click', () => this.handleStatusConfirm());
        
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                this.closeStatusModal();
            }
        });

        // Expose global function
        if (typeof window !== 'undefined') {
            window.updateStatus = (id, status) => this.openStatusModal(id, status);
        }
    }

    openStatusModal(id, status) {
        this.pendingStatus.id = id;
        this.pendingStatus.status = status;

        const overlay = document.getElementById('confirmStatusOverlay');
        const title = document.getElementById('confirmStatusTitle');
        const text = document.getElementById('confirmStatusText');
        const icon = document.getElementById('confirmStatusIcon');
        const btnConfirm = document.getElementById('btnConfirmStatus');

        if (!overlay) return;

        btnConfirm.classList.remove('accept', 'reject');
        
        if (status === 'accepted') {
            if (title) title.textContent = 'Terima Pendaftar?';
            if (text) text.textContent = 'Apakah Anda yakin ingin menerima pendaftar ini?';
            if (icon) {
                icon.className = 'lni lni-checkmark-circle';
                icon.style.color = '#27AE60';
            }
            if (btnConfirm) {
                btnConfirm.textContent = 'Terima';
                btnConfirm.classList.add('accept');
            }
        } else if (status === 'rejected') {
            if (title) title.textContent = 'Tolak Pendaftar?';
            if (text) text.textContent = 'Apakah Anda yakin ingin menolak pendaftar ini?';
            if (icon) {
                icon.className = 'lni lni-close';
                icon.style.color = '#e74c3c';
            }
            if (btnConfirm) {
                btnConfirm.textContent = 'Tolak';
                btnConfirm.classList.add('reject');
            }
        } else {
            if (title) title.textContent = 'Konfirmasi';
            if (text) text.textContent = 'Apakah Anda yakin?';
            if (icon) {
                icon.className = 'lni lni-hourglass';
                icon.style.color = '#2F80ED';
            }
            if (btnConfirm) btnConfirm.textContent = 'Konfirmasi';
        }

        overlay.style.display = 'flex';
    }

    closeStatusModal() {
        this.pendingStatus.id = null;
        this.pendingStatus.status = null;
        const overlay = document.getElementById('confirmStatusOverlay');
        if (overlay) {
            overlay.style.display = 'none';
        }
    }

    handleStatusConfirm() {
        if (!this.pendingStatus.id || !this.pendingStatus.status) return;
        
        // Submit hidden form
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '';
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'update_status';
        form.appendChild(actionInput);
        
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'id';
        idInput.value = this.pendingStatus.id;
        form.appendChild(idInput);
        
        const statusInput = document.createElement('input');
        statusInput.type = 'hidden';
        statusInput.name = 'status';
        statusInput.value = this.pendingStatus.status;
        form.appendChild(statusInput);
        
        const urlParams = new URLSearchParams(window.location.search);
        const currentFilter = urlParams.get('status') || 'all';
        const filterInput = document.createElement('input');
        filterInput.type = 'hidden';
        filterInput.name = 'current_filter';
        filterInput.value = currentFilter;
        form.appendChild(filterInput);
        
        document.body.appendChild(form);
        form.submit();
    }

}

// Initialize PendaftarModals if data is available (only once)
if (typeof window !== 'undefined' && window.pendaftarData && !window.pendaftarModals) {
    window.pendaftarModals = new PendaftarModals(window.pendaftarData);
}

