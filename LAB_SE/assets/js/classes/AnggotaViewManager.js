/**
 * AnggotaViewManager - Kelas untuk mengelola toggle antara table view dan card view
 */
class AnggotaViewManager {
    constructor() {
        this.currentView = 'table'; // 'table' or 'card'
        this.init();
    }

    init() {
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setupViewToggle());
        } else {
            this.setupViewToggle();
        }
    }

    setupViewToggle() {
        // Create view toggle buttons if they don't exist
        if (!document.getElementById('anggotaViewToggle')) {
            const toggleContainer = document.createElement('div');
            toggleContainer.id = 'anggotaViewToggle';
            toggleContainer.className = 'anggota-view-toggle';
            toggleContainer.innerHTML = `
                <button class="view-toggle-btn active" data-view="table" onclick="window.anggotaViewManager.setView('table')">
                    <i class="lni lni-list"></i> Tabel
                </button>
                <button class="view-toggle-btn" data-view="card" onclick="window.anggotaViewManager.setView('card')">
                    <i class="lni lni-grid-alt"></i> Card
                </button>
            `;
            
            // Insert before the table/card container
            const container = document.querySelector('.row .col-lg-12');
            if (container) {
                const firstChild = container.querySelector('.table-responsive, .anggota-card-view, .empty-state');
                if (firstChild) {
                    container.insertBefore(toggleContainer, firstChild);
                } else {
                    container.appendChild(toggleContainer);
                }
            }
        }
        
        // Load saved preference
        const savedView = localStorage.getItem('anggotaView');
        if (savedView && (savedView === 'table' || savedView === 'card')) {
            this.setView(savedView, false);
        } else {
            // Default to table view
            this.setView('table', false);
        }
    }

    setView(view, savePreference = true) {
        if (view !== 'table' && view !== 'card') return;
        
        this.currentView = view;
        
        // Update toggle buttons
        document.querySelectorAll('.view-toggle-btn').forEach(btn => {
            if (btn.dataset.view === view) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
        
        // Show/hide views
        const tableView = document.querySelector('.table-responsive');
        const cardView = document.querySelector('.anggota-card-view');
        
        if (view === 'table') {
            if (tableView) tableView.style.display = 'block';
            if (cardView) cardView.classList.remove('active');
        } else {
            if (tableView) tableView.style.display = 'none';
            if (cardView) cardView.classList.add('active');
            
            // Generate card view if not exists
            if (cardView && !cardView.querySelector('.anggota-card-grid')) {
                this.generateCardView();
            }
        }
        
        // Save preference
        if (savePreference) {
            localStorage.setItem('anggotaView', view);
        }
    }

    generateCardView() {
        const cardView = document.querySelector('.anggota-card-view');
        if (!cardView) return;
        
        const anggotaData = window.anggotaData || {};
        const grid = document.createElement('div');
        grid.className = 'anggota-card-grid';
        
        Object.values(anggotaData).forEach(anggota => {
            const card = this.createCard(anggota);
            grid.appendChild(card);
        });
        
        cardView.innerHTML = '';
        cardView.appendChild(grid);
    }

    createCard(anggota) {
        const card = document.createElement('div');
        card.className = 'anggota-card';
        
        const foto = anggota.foto_resolved || '';
        const initial = anggota.nama ? anggota.nama.charAt(0).toUpperCase() : '?';
        const nama = anggota.nama || '-';
        const jabatan = anggota.jabatan || '-';
        
        let photoHtml = '';
        if (foto) {
            photoHtml = `<img src="${foto}" alt="${nama}" class="anggota-card-photo">`;
        } else {
            photoHtml = `<div class="anggota-card-initial">${initial}</div>`;
        }
        
        let socialHtml = '';
        if (anggota.instagram || anggota.linkedin) {
            socialHtml = '<div class="anggota-card-social">';
            if (anggota.instagram) {
                socialHtml += `<a href="${anggota.instagram}" target="_blank" title="Instagram"><i class="lni lni-instagram"></i></a>`;
            }
            if (anggota.linkedin) {
                socialHtml += `<a href="${anggota.linkedin}" target="_blank" title="LinkedIn"><i class="lni lni-linkedin"></i></a>`;
            }
            socialHtml += '</div>';
        }
        
        card.innerHTML = `
            <div class="anggota-card-header">
                ${photoHtml}
                <h4 class="anggota-card-name">${nama}</h4>
            </div>
            <div class="anggota-card-body">
                <div class="anggota-card-jabatan">
                    <i class="lni lni-briefcase"></i> ${jabatan}
                </div>
                ${socialHtml}
                <div class="anggota-card-actions">
                    <div class="btn-group w-100" role="group">
                        <button type="button" class="btn btn-info btn-sm" onclick="openPreviewAnggota(${anggota.id})">
                            <i class="lni lni-eye me-1"></i> Preview
                        </button>
                        <a href="edit.php?id=${anggota.id}" class="btn btn-primary btn-sm">
                            <i class="lni lni-pencil me-1"></i> Edit
                        </a>
                        <button type="button" class="btn btn-danger btn-sm" onclick="confirmDeleteAnggota(${anggota.id}, '${nama.replace(/'/g, "\\'")}')">
                            <i class="lni lni-trash me-1"></i> Hapus
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        return card;
    }
}

// Initialize global view manager instance
if (typeof window !== 'undefined') {
    window.anggotaViewManager = new AnggotaViewManager();
}

