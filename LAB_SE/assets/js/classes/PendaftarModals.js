/**
 * PendaftarModals - Kelas untuk mengelola modal pendaftar (detail, status, create account)
 */
class PendaftarModals {
    constructor(data = {}) {
        this.data = data;
        this.pendingStatus = { id: null, status: null };
        this.pendingAccount = { id: null, nim: null };
        this.pendingDeactivate = { id: null, nim: null };
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
        this.setupCreateAccountModal();
        this.setupDeactivateAccountModal();
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
        const detailMinat = document.getElementById('detailMinat');
        const detailEmail = document.getElementById('detailEmail');
        const detailTanggalDaftar = document.getElementById('detailTanggalDaftar');
        const detailTanggalReview = document.getElementById('detailTanggalReview');
        const detailCatatan = document.getElementById('detailCatatan');
        const detailFoto = document.getElementById('detailFoto');
        const detailStatus = document.getElementById('detailStatus');

        if (detailNama) detailNama.textContent = data.nama || '-';
        if (detailNim) detailNim.textContent = data.nim || '-';
        if (detailMinat) detailMinat.textContent = data.minat ? data.minat : '-';
        
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
                const fotoPath = data.foto.startsWith('http') ? data.foto : '../../' + data.foto;
                detailFoto.innerHTML = '<img src="'+ fotoPath +'" alt="Foto '+ (data.nama || "") +'" class="detail-image">';
            } else {
                detailFoto.textContent = '-';
            }
        }

        // Detail Akun
        const detailAccountSection = document.getElementById('detailAccountSection');
        if (detailAccountSection) {
            if (data.has_account === true && data.account_info && typeof data.account_info === 'object') {
                const accountInfo = data.account_info;
                let accountHtml = '<div class="detail-account-info">';
                
                // Username
                accountHtml += '<div class="detail-account-item">';
                accountHtml += '<span class="detail-account-label">Username:</span> ';
                accountHtml += '<span class="detail-account-value">' + (accountInfo.username || '-') + '</span>';
                accountHtml += '</div>';
                
                // Role
                accountHtml += '<div class="detail-account-item">';
                accountHtml += '<span class="detail-account-label">Role:</span> ';
                const roleText = accountInfo.role === 'mahasiswa' ? 'Mahasiswa' : (accountInfo.role || '-');
                accountHtml += '<span class="detail-account-value">' + roleText + '</span>';
                accountHtml += '</div>';
                
                // Tanggal Dibuat
                if (accountInfo.created_at && accountInfo.created_at !== '-') {
                    accountHtml += '<div class="detail-account-item">';
                    accountHtml += '<span class="detail-account-label">Tanggal Dibuat:</span> ';
                    accountHtml += '<span class="detail-account-value">' + accountInfo.created_at + '</span>';
                    accountHtml += '</div>';
                }
                
                // Status Badge
                accountHtml += '<div class="detail-account-status">';
                accountHtml += '<span class="account-status-badge-active"><i class="lni lni-checkmark-circle"></i> Akun Aktif</span>';
                accountHtml += '</div>';
                accountHtml += '</div>';
                
                detailAccountSection.innerHTML = accountHtml;
            } else {
                detailAccountSection.innerHTML = '<span class="text-muted-small">Belum ada akun. Akun dapat dibuat melalui menu Aksi.</span>';
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

    // Create Account Modal
    setupCreateAccountModal() {
        // Prevent duplicate event listeners
        if (this.createAccountSetupDone) return;
        
        const overlay = document.getElementById('createAccountOverlay');
        const btnConfirm = document.getElementById('btnConfirmCreateAccount');
        const btnCancel = document.getElementById('btnCancelCreateAccount');

        if (!overlay || !btnConfirm || !btnCancel) return;

        // Store original handlers
        const handleConfirm = (e) => {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            console.log('handleConfirm called');
            const currentBtn = document.getElementById('btnConfirmCreateAccount');
            if (currentBtn) {
                currentBtn.disabled = false;
                currentBtn.style.pointerEvents = 'auto';
                currentBtn.style.cursor = 'pointer';
                if (!currentBtn.disabled) {
                    this.handleCreateAccountConfirm();
                }
            }
        };
        
        const handleCancel = (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.closeCreateAccountModal();
        };

        // Remove any existing listeners by cloning
        const newBtnCancel = btnCancel.cloneNode(true);
        const newBtnConfirm = btnConfirm.cloneNode(true);
        btnCancel.parentNode.replaceChild(newBtnCancel, btnCancel);
        btnConfirm.parentNode.replaceChild(newBtnConfirm, btnConfirm);

        // Add event listeners once
        newBtnCancel.addEventListener('click', handleCancel);
        newBtnConfirm.addEventListener('click', handleConfirm);
        
        // Overlay click handler
        const handleOverlayClick = (e) => {
            if (e.target === overlay) {
                this.closeCreateAccountModal();
            }
        };
        overlay.addEventListener('click', handleOverlayClick);

        // Expose global function (only once)
        if (typeof window !== 'undefined' && !window.showCreateAccountModal) {
            window.showCreateAccountModal = (id, nim) => this.openCreateAccountModal(id, nim);
        }
        
        this.createAccountSetupDone = true;
    }

    openCreateAccountModal(id, nim) {
        this.pendingAccount.id = id;
        this.pendingAccount.nim = nim;
        this.isSubmitting = false; // Reset submission flag
        
        // Simpan ID ke global variable juga untuk backup
        if (typeof window !== 'undefined') {
            window.pendingCreateAccountId = id;
        }

        const overlay = document.getElementById('createAccountOverlay');
        const usernameEl = document.getElementById('createAccountUsername');
        const passwordEl = document.getElementById('createAccountPassword');
        const btnConfirm = document.getElementById('btnConfirmCreateAccount');

        if (!overlay) return;

        if (usernameEl) usernameEl.textContent = nim;
        if (passwordEl) passwordEl.textContent = nim;
        
        // Reset button state - pastikan bisa diklik
        if (btnConfirm) {
            btnConfirm.disabled = false;
            btnConfirm.textContent = 'OK';
            btnConfirm.style.pointerEvents = 'auto';
            btnConfirm.style.cursor = 'pointer';
            btnConfirm.style.position = 'relative';
            btnConfirm.style.zIndex = '10001';
        }

        overlay.style.display = 'flex';
        overlay.style.zIndex = '10000';
    }

    closeCreateAccountModal() {
        this.pendingAccount.id = null;
        this.pendingAccount.nim = null;
        const overlay = document.getElementById('createAccountOverlay');
        if (overlay) {
            overlay.style.display = 'none';
        }
    }

    handleCreateAccountConfirm() {
        if (!this.pendingAccount.id) return;
        
        // Prevent double submission
        if (this.isSubmitting) {
            return; // Already submitting
        }
        
        const btnConfirm = document.getElementById('btnConfirmCreateAccount');
        if (btnConfirm && btnConfirm.disabled) {
            return; // Already submitting
        }
        
        // Set flag to prevent multiple submissions
        this.isSubmitting = true;
        
        // Disable button to prevent double click
        if (btnConfirm) {
            btnConfirm.disabled = true;
            btnConfirm.textContent = 'Memproses...';
        }
        
        // Submit form
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '';
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'create_account';
        form.appendChild(actionInput);
        
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'id';
        idInput.value = this.pendingAccount.id;
        form.appendChild(idInput);
        
        const urlParams = new URLSearchParams(window.location.search);
        const currentFilter = urlParams.get('status') || 'all';
        const filterInput = document.createElement('input');
        filterInput.type = 'hidden';
        filterInput.name = 'current_filter';
        filterInput.value = currentFilter;
        form.appendChild(filterInput);
        
        document.body.appendChild(form);
        form.submit();
        
        // Note: isSubmitting will be reset when page reloads
    }

    // Deactivate Account Modal
    setupDeactivateAccountModal() {
        // Prevent duplicate event listeners
        if (this.deactivateAccountSetupDone) return;
        
        const overlay = document.getElementById('deactivateAccountOverlay');
        const btnConfirm = document.getElementById('btnConfirmDeactivateAccount');
        const btnCancel = document.getElementById('btnCancelDeactivateAccount');

        if (!overlay || !btnConfirm || !btnCancel) return;

        // Store original handlers
        const handleConfirm = (e) => {
            e.preventDefault();
            e.stopPropagation();
            const currentBtn = document.getElementById('btnConfirmDeactivateAccount');
            if (currentBtn && !currentBtn.disabled) {
                this.handleDeactivateAccountConfirm();
            }
        };
        
        const handleCancel = (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.closeDeactivateAccountModal();
        };

        // Remove any existing listeners by cloning
        const newBtnCancel = btnCancel.cloneNode(true);
        const newBtnConfirm = btnConfirm.cloneNode(true);
        btnCancel.parentNode.replaceChild(newBtnCancel, btnCancel);
        btnConfirm.parentNode.replaceChild(newBtnConfirm, btnConfirm);

        // Add event listeners once
        newBtnCancel.addEventListener('click', handleCancel);
        newBtnConfirm.addEventListener('click', handleConfirm);

        // Overlay click handler
        const handleOverlayClick = (e) => {
            if (e.target === overlay) {
                this.closeDeactivateAccountModal();
            }
        };
        overlay.addEventListener('click', handleOverlayClick);

        // Expose global function (only once)
        if (typeof window !== 'undefined' && !window.showDeactivateAccountModal) {
            window.showDeactivateAccountModal = (id, nim) => this.openDeactivateAccountModal(id, nim);
        }
        
        this.deactivateAccountSetupDone = true;
    }

    openDeactivateAccountModal(id, nim) {
        this.pendingDeactivate.id = id;
        this.pendingDeactivate.nim = nim;
        this.isSubmitting = false; // Reset submission flag

        const overlay = document.getElementById('deactivateAccountOverlay');
        const nimEl = document.getElementById('deactivateAccountNim');
        const btnConfirm = document.getElementById('btnConfirmDeactivateAccount');

        if (nimEl) nimEl.textContent = nim || '-';
        
        // Reset button state
        if (btnConfirm) {
            btnConfirm.disabled = false;
            btnConfirm.textContent = 'Nonaktifkan';
        }
        
        if (overlay) overlay.style.display = 'flex';
    }

    closeDeactivateAccountModal() {
        this.pendingDeactivate.id = null;
        this.pendingDeactivate.nim = null;
        const overlay = document.getElementById('deactivateAccountOverlay');
        if (overlay) overlay.style.display = 'none';
    }

    handleDeactivateAccountConfirm() {
        if (!this.pendingDeactivate.id) return;

        // Prevent double submission
        if (this.isSubmitting) {
            return; // Already submitting
        }
        
        const btnConfirm = document.getElementById('btnConfirmDeactivateAccount');
        if (btnConfirm && btnConfirm.disabled) {
            return; // Already submitting
        }
        
        // Set flag to prevent multiple submissions
        this.isSubmitting = true;
        
        // Disable button to prevent double click
        if (btnConfirm) {
            btnConfirm.disabled = true;
            btnConfirm.textContent = 'Memproses...';
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '';

        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'deactivate_account';
        form.appendChild(actionInput);

        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'id';
        idInput.value = this.pendingDeactivate.id;
        form.appendChild(idInput);

        const urlParams = new URLSearchParams(window.location.search);
        const currentFilter = urlParams.get('status') || 'all';
        const filterInput = document.createElement('input');
        filterInput.type = 'hidden';
        filterInput.name = 'current_filter';
        filterInput.value = currentFilter;
        form.appendChild(filterInput);

        document.body.appendChild(form);
        form.submit();
        
        // Note: isSubmitting will be reset when page reloads
    }
}

// Initialize PendaftarModals if data is available (only once)
if (typeof window !== 'undefined' && window.pendaftarData && !window.pendaftarModals) {
    window.pendaftarModals = new PendaftarModals(window.pendaftarData);
}

