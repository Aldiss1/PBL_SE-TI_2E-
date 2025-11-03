/* js/script.js
   Versi 4.1: Perbaikan Layout Home
   - Logika header sticky on-scroll DIHAPUS.
   - Navbar CSL sekarang ada di semua halaman, termasuk index.html.
   - Sistem Anggota/Pendaftar tetap ada.
*/

/* ---------- Storage keys ---------- */
const STORAGE_KEY = 'labse_anggota'; // Pendaftar (Calon)
const PERSONIL_KEY = 'labse_personil'; // Staf/Dosen
const BLOG_KEY = 'labse_blog'; // Artikel
const MEMBER_KEY = 'labse_member'; // Anggota Mahasiswa (Diterima)

/* ---------- Dummy data Defaults ---------- */
const DUMMY_DEFAULTS = {
  about: "Lab SE adalah laboratorium penelitian bagi mahasiswa dan dosen yang fokus pada rekayasa perangkat lunak, quality assurance, dan HCI.",
  vision: {
    visi: "Menjadi pusat penelitian software engineering yang relevan dan berdaya guna.",
    misi: [
      "Melakukan penelitian berkualitas di bidang perangkat lunak.",
      "Mendorong kolaborasi akademik dan industri.",
      "Membina mahasiswa melalui proyek penelitian."
    ]
  },
  roadmap: [
    { year: "2025", goals: "Membangun infrastruktur penelitian & CI/CD." },
    { year: "2026", goals: "Pelaksanaan penelitian kolaboratif & workshop." },
    { year: "2027", goals: "Publikasi dan penguatan kemitraan industri." }
  ],
  focus: [
    "Automated testing",
    "Microservices & Integrasi",
    "Usability & HCI",
    "ML for Software Engineering"
  ],
  personil: [
    { id:1, nama:"Dr. Andi Prasetyo", posisi:"Ketua Lab", foto:"assets/person1.jpg", bio:"Dosen di bidang Software Engineering." },
    { id:2, nama:"Dra. Santi Wulandari", posisi:"Sekretaris", foto:"assets/person2.jpg", bio:"Peneliti HCI dan UX." },
    { id:3, nama:"Budi Santoso", posisi:"Peneliti", foto:"assets/person3.jpg", bio:"Developer dan QA engineer." }
  ],
  blog: [
    { id:101, title:"Membangun Pipeline CI/CD", date:"2025-03-20", excerpt:"Panduan implementasi CI/CD sederhana.", cover:"assets/blog1.jpg", content:"<p>Isi artikel contoh. Ganti dengan konten asli.</p>" },
    { id:102, title:"Dasar Testing Otomatis", date:"2025-04-15", excerpt:"Kenali dasar testing otomatis.", cover:"assets/blog2.jpg", content:"<p>Isi artikel contoh. Ganti dengan konten asli.</p>" }
  ]
};

/* ---------- Data Initialization ---------- */
let DUMMY; 

/* --- Storage Getters / Setters --- */
function getPersonilFromStorage() { try { const d = localStorage.getItem(PERSONIL_KEY); return d ? JSON.parse(d) : DUMMY_DEFAULTS.personil; } catch { return DUMMY_DEFAULTS.personil; } }
function savePersonilToStorage(data) { localStorage.setItem(PERSONIL_KEY, JSON.stringify(data)); }

function getBlogFromStorage() { try { const d = localStorage.getItem(BLOG_KEY); return d ? JSON.parse(d) : DUMMY_DEFAULTS.blog; } catch { return DUMMY_DEFAULTS.blog; } }
function saveBlogToStorage(data) { localStorage.setItem(BLOG_KEY, JSON.stringify(data)); }

function getAnggotaFromStorage(){ try { return JSON.parse(localStorage.getItem(STORAGE_KEY)) || []; } catch { return []; } }
function saveAnggotaToStorage(data) { localStorage.setItem(STORAGE_KEY, JSON.stringify(data)); }

function getMemberFromStorage(){ try { return JSON.parse(localStorage.getItem(MEMBER_KEY)) || []; } catch { return []; } }
function saveMemberToStorage(data) { localStorage.setItem(MEMBER_KEY, JSON.stringify(data)); }


function initData() {
  DUMMY = {
    ...DUMMY_DEFAULTS,
    personil: getPersonilFromStorage(),
    blog: getBlogFromStorage()
  };
  
  if (!localStorage.getItem(PERSONIL_KEY)) { savePersonilToStorage(DUMMY.personil); }
  if (!localStorage.getItem(BLOG_KEY)) { saveBlogToStorage(DUMMY.blog); }
  if (!localStorage.getItem(MEMBER_KEY)) { saveMemberToStorage([]); }
}
initData();

/* ---------- Utilities ---------- */
function qParam(name){
  const url = new URL(window.location.href);
  return url.searchParams.get(name);
}

/* ---------- PUBLIC RENDERS ---------- */
function renderPersonilPreview(limit = 3){
  const container = document.getElementById('personil-preview');
  if(!container) return;
  container.innerHTML = "";
  DUMMY.personil.slice(0, limit).forEach(p => {
    const col = document.createElement('div');
    col.className = 'col-md-4';
    col.innerHTML = `
      <div class="card h-100">
        <img src="${p.foto}" class="personil-img" alt="${p.nama}" onerror="this.style.display='none'">
        <div class="card-body">
          <h6 class="card-title mb-1">${p.nama}</h6>
          <p class="text-muted small mb-2">${p.posisi}</p>
          <a href="personil-detail.html?id=${p.id}" class="link-primary stretched-link">Lihat profil →</a>
        </div>
      </div>
    `;
    container.appendChild(col);
  });
}

function renderBlogPreview(limit = 2){
  const container = document.getElementById('blog-preview');
  if(!container) return;
  container.innerHTML = "";
  DUMMY.blog.slice(0, limit).forEach(b => {
    const col = document.createElement('div');
    col.className = 'col-md-6';
    col.innerHTML = `
      <div class="card h-100">
        <img src="${b.cover}" class="card-img-top" style="height:140px; object-fit:cover;" onerror="this.style.display='none'">
        <div class="card-body">
          <h6 class="card-title">${b.title}</h6>
          <p class="small text-muted mb-2">${b.date}</p>
          <p class="card-text">${b.excerpt}</p>
          <a href="blog-article.html?id=${b.id}" class="link-primary stretched-link">Baca →</a>
        </div>
      </div>
    `;
    container.appendChild(col);
  });
}

function renderProfileContent(){
  const aboutEl = document.getElementById('about-text');
  const visionEl = document.getElementById('vision-text');
  const roadmapEl = document.getElementById('roadmap-list');
  const focusEl = document.getElementById('focus-list');
  if(aboutEl) aboutEl.textContent = DUMMY.about;
  if(visionEl) {
    visionEl.innerHTML = `<p><strong>Visi:</strong> ${DUMMY.vision.visi}</p>
      <p><strong>Misi:</strong></p><ul>${DUMMY.vision.misi.map(i=>`<li>${i}</li>`).join('')}</ul>`;
  }
  if(roadmapEl) roadmapEl.innerHTML = `<ul>${DUMMY.roadmap.map(r=>`<li><strong>${r.year}:</strong> ${r.goals}</li>`).join('')}</ul>`;
  if(focusEl) focusEl.innerHTML = DUMMY.focus.map(f=>`<li>${f}</li>`).join('');
}

function renderPersonilList(filter=''){
  const container = document.getElementById('personil-list');
  if(!container) return;
  container.innerHTML = '';
  const normalized = filter.toLowerCase();
  const items = DUMMY.personil.filter(p => {
    if(!filter) return true;
    return p.nama.toLowerCase().includes(normalized) || p.posisi.toLowerCase().includes(normalized);
  });
  if(items.length===0){ container.innerHTML = `<div class="col-12"><div class="alert alert-warning">Tidak ada personil staf/dosen.</div></div>`; return; }
  items.forEach(p => {
    const col = document.createElement('div');
    col.className = 'col-md-4';
    col.innerHTML = `
      <div class="card h-100">
        <img src="${p.foto}" class="personil-img" onerror="this.style.display='none'">
        <div class="card-body">
          <h6>${p.nama}</h6>
          <p class="small text-muted">${p.posisi}</p>
          <p class="small text-muted">${p.bio}</p>
          <a href="personil-detail.html?id=${p.id}" class="btn btn-outline-primary btn-sm mt-2">Lihat</a>
        </div>
      </div>
    `;
    container.appendChild(col);
  });
}

function renderPublicMembers(){
  const container = document.getElementById('member-list');
  if(!container) return;
  const list = getMemberFromStorage();
  if(list.length===0){ container.innerHTML = `<div class="col-12"><p class="text-muted">Belum ada anggota mahasiswa yang bergabung.</p></div>`; return; }
  
  container.innerHTML = '';
  list.forEach(a => {
    const col = document.createElement('div');
    col.className = 'col-md-4';
    col.innerHTML = `
      <div class="card h-100">
        <div class="card-body">
          <h6 class="card-title mb-1">${a.nama}</h6>
          <p class="text-muted small mb-0">Minat: ${a.minat}</p>
        </div>
      </div>
    `;
    container.appendChild(col);
  });
}

function renderPersonilDetail(){
  const id = parseInt(qParam('id')||'0',10);
  const target = DUMMY.personil.find(p=>p.id===id);
  const container = document.getElementById('personil-detail');
  if(!container) return;
  if(!target){ container.innerHTML = `<div class="p-4"><h5>Personil tidak ditemukan</h5></div>`; return; }
  container.innerHTML = `
    <div class="row g-0">
      <div class="col-md-4">
        <img src="${target.foto}" class="img-fluid rounded-start" onerror="this.style.display='none'">
      </div>
      <div class="col-md-8">
        <div class="card-body">
          <h3 class="card-title">${target.nama}</h3>
          <p class="small text-muted">${target.posisi}</p>
          <p>${target.bio}</p>
          <p class="small text-muted">Email: personil${target.id}@example.edu</p>
        </div>
      </div>
    </div>
  `;
}

/* ---------- DAFTAR (Formulir) ---------- */
function initRekrutForm(){
  const form = document.getElementById('form-rekrut');
  if(!form) return;
  form.addEventListener('submit', function(e){
    e.preventDefault();
    const nama = document.getElementById('r-nama').value.trim();
    const nim = document.getElementById('r-nim').value.trim();
    const email = document.getElementById('r-email').value.trim();
    const minat = document.getElementById('r-minat').value;
    if(!nama||!nim||!email||!minat) return alert('Lengkapi semua field.');
    
    const daftar = getAnggotaFromStorage();
    daftar.push({ id: Date.now(), nama, nim, email, minat, joined: new Date().toISOString() });
    saveAnggotaToStorage(daftar);
    
    form.reset();
    alert('Pendaftaran berhasil (demo). Data Anda akan ditinjau oleh Admin.');
  });
}

/* ---------- BLOG ---------- */
function renderBlogList(){
  const container = document.getElementById('blog-list');
  if(!container) return;
  container.innerHTML = '';
  DUMMY.blog.forEach(b=>{
    const col = document.createElement('div');
    col.className = 'col-md-6';
    col.innerHTML = `
      <div class="card h-100">
        ${b.cover ? `<img src="${b.cover}" class="card-img-top" style="height:200px; object-fit:cover;" onerror="this.style.display='none'">` : ''}
        <div class="card-body">
          <h6 class="card-title">${b.title}</h6>
          <p class="small text-muted mb-2">${b.date}</p>
          <p class="card-text">${b.excerpt}</p>
          <a href="blog-article.html?id=${b.id}" class="link-primary stretched-link">Baca →</a>
        </div>
      </div>
    `;
    container.appendChild(col);
  });
}
function renderArticleDetail(){
  const id = parseInt(qParam('id')||'0',10);
  const article = DUMMY.blog.find(b=>b.id===id);
  const container = document.getElementById('article-detail');
  if(!container) return;
  if(!article){ container.innerHTML = `<div class="p-4"><h5>Artikel tidak ditemukan</h5></div>`; return; }
  container.innerHTML = `
    <h2>${article.title}</h2>
    <p class="small text-muted">${article.date}</p>
    ${article.cover ? `<img src="${article.cover}" class="img-fluid mb-4" style="max-height:360px; object-fit:cover; width:100%;" onerror="this.style.display='none'">` : ''}
    <div>${article.content}</div>
  `;
}

/* ---------- ADMIN: Auth & Protection (demo) ---------- */
function initAdminLogin(){
  const form = document.getElementById('admin-login-form');
  if(!form) return;
  form.addEventListener('submit', function(e){
    e.preventDefault();
    const u = document.getElementById('admin-username').value.trim();
    const p = document.getElementById('admin-password').value;
    if(u === 'admin' && p === 'admin123'){
      localStorage.setItem('labse_admin', 'true');
      window.location.href = 'admin/dashboard.html';
    } else {
      alert('Login gagal (demo). Gunakan admin / admin123');
    }
  });
}

function protectAdmin(){
  const ok = localStorage.getItem('labse_admin') === 'true';
  if(!ok){
    const path = window.location.pathname;
    window.location.href = (path.includes('/admin/') ? '../login.html' : 'login.html');
  }
}

function adminLogout(){
  localStorage.removeItem('labse_admin');
  window.location.href = '../login.html';
}

/* ---------- ADMIN: Stats ---------- */
function renderAdminStats(){
  const statP = document.getElementById('stat-personil');
  const statA = document.getElementById('stat-article');
  const statAng = document.getElementById('stat-anggota');
  const statMem = document.getElementById('stat-member');

  if(statP) statP.textContent = DUMMY.personil.length;
  if(statA) statA.textContent = DUMMY.blog.length;
  if(statAng) statAng.textContent = getAnggotaFromStorage().length; 
  if(statMem) statMem.textContent = getMemberFromStorage().length;
  
  const container = document.getElementById('admin-applicants');
  if(!container) return;
  const recent = getAnggotaFromStorage().slice(-5).reverse();
  if(recent.length===0) container.innerHTML = `<div class="small text-muted p-3">Belum ada pendaftar baru.</div>`;
  else container.innerHTML = recent.map(a=>`<div class="list-group-item"><div class="fw-semibold">${a.nama}</div><div class="small text-muted">${a.nim} • ${a.minat}</div></div>`).join('');
}

/* ---------- ADMIN: Personil (Staf) CRUD ---------- */
function renderAdminPersonil(){
  const container = document.getElementById('admin-personil-list');
  if(!container) return;
  container.innerHTML = '';
  DUMMY.personil.forEach(p=>{
    const col = document.createElement('div');
    col.className = 'col-md-4';
    col.innerHTML = `
      <div class="card p-3">
        <div class="d-flex align-items-start">
          <img src="../${p.foto}" style="width:72px; height:72px; object-fit:cover; border-radius:8px; margin-right:12px;" onerror="this.style.display='none'">
          <div class="flex-grow-1">
            <div class="fw-semibold">${p.nama}</div>
            <div class="small text-muted">${p.posisi}</div>
            <div class="mt-2">
              <button class="btn btn-sm btn-outline-primary" onclick="adminEditPersonil(${p.id})">Edit</button>
              <button class="btn btn-sm btn-danger" onclick="adminDeletePersonil(${p.id})">Hapus</button>
            </div>
          </div>
        </div>
      </div>
    `;
    container.appendChild(col);
  });
}

function adminEditPersonil(id){
  const p = DUMMY.personil.find(x=>x.id===id);
  if(!p) return alert('Personil tidak ditemukan');
  document.getElementById('p-id').value = p.id;
  document.getElementById('p-nama').value = p.nama;
  document.getElementById('p-posisi').value = p.posisi;
  document.getElementById('p-bio').value = p.bio || '';
  document.getElementById('p-foto').value = p.foto || '';
  const modal = new bootstrap.Modal(document.getElementById('modalPersonil'));
  modal.show();
}

function adminSavePersonil(){
  const id = parseInt(document.getElementById('p-id').value||'0',10);
  const nama = document.getElementById('p-nama').value.trim();
  const posisi = document.getElementById('p-posisi').value.trim();
  const bio = document.getElementById('p-bio').value.trim();
  const foto = document.getElementById('p-foto').value.trim() || 'assets/person-placeholder.jpg';
  
  if(!nama||!posisi) return alert('Nama & posisi wajib.');
  
  if(id){
    const idx = DUMMY.personil.findIndex(x=>x.id===id);
    if(idx>-1) DUMMY.personil[idx] = { id, nama, posisi, bio, foto };
  } else {
    const newId = Date.now();
    DUMMY.personil.unshift({ id: newId, nama, posisi, bio, foto });
  }
  savePersonilToStorage(DUMMY.personil);
  renderAdminPersonil();
  renderAdminStats();
}

function adminDeletePersonil(id){
  if(!confirm('Hapus personil ini?')) return;
  const pIdx = DUMMY.personil.findIndex(p=>p.id===id);
  if (pIdx > -1) {
    DUMMY.personil.splice(pIdx, 1);
    savePersonilToStorage(DUMMY.personil);
  }
  renderAdminPersonil();
  renderAdminStats();
}

/* ---------- ADMIN: Blog CRUD ---------- */
function renderAdminBlog(){
  const container = document.getElementById('admin-blog-list');
  if(!container) return;
  container.innerHTML = '';
  DUMMY.blog.forEach(b=>{
    const col = document.createElement('div');
    col.className = 'col-md-6';
    col.innerHTML = `
      <div class="card p-3">
        <div class="d-flex">
          ${b.cover ? `<img src="../${b.cover}" style="width:120px; height:80px; object-fit:cover; border-radius:8px; margin-right:12px;" onerror="this.style.display='none'">` : ''}
          <div class="flex-grow-1">
            <div class="fw-semibold">${b.title}</div>
            <div class="small text-muted mb-2">${b.date}</div>
            <div>
              <button class="btn btn-sm btn-outline-primary" onclick="adminEditBlog(${b.id})">Edit</button>
              <button class="btn btn-sm btn-danger" onclick="adminDeleteBlog(${b.id})">Hapus</button>
            </div>
          </div>
        </div>
      </div>
    `;
    container.appendChild(col);
  });
}

function adminEditBlog(id){
  const b = DUMMY.blog.find(x=>x.id===id);
  if(!b) return alert('Artikel tidak ditemukan');
  document.getElementById('b-id').value = b.id;
  document.getElementById('b-title').value = b.title;
  document.getElementById('b-date').value = b.date;
  document.getElementById('b-excerpt').value = b.excerpt;
  document.getElementById('b-cover').value = b.cover || '';
  document.getElementById('b-content').value = b.content || '';
  const modal = new bootstrap.Modal(document.getElementById('modalBlog'));
  modal.show();
}

function adminSaveBlog(){
  const id = parseInt(document.getElementById('b-id').value||'0',10);
  const title = document.getElementById('b-title').value.trim();
  const date = document.getElementById('b-date').value || new Date().toISOString().slice(0,10);
  const excerpt = document.getElementById('b-excerpt').value.trim();
  const cover = document.getElementById('b-cover').value.trim();
  const content = document.getElementById('b-content').value || '';
  if(!title) return alert('Judul wajib.');
  
  if(id){
    const idx = DUMMY.blog.findIndex(x=>x.id===id);
    if(idx>-1) DUMMY.blog[idx] = { id, title, date, excerpt, cover, content };
  } else {
    const newId = Date.now();
    DUMMY.blog.unshift({ id: newId, title, date, excerpt, cover, content });
  }
  
  saveBlogToStorage(DUMMY.blog);
  renderAdminBlog();
  renderAdminStats();
}

function adminDeleteBlog(id){
  if(!confirm('Hapus artikel?')) return;
  const bIdx = DUMMY.blog.findIndex(b=>b.id===id);
  if (bIdx > -1) {
    DUMMY.blog.splice(bIdx, 1);
    saveBlogToStorage(DUMMY.blog);
  }
  renderAdminBlog();
  renderAdminStats();
}


/* ---------- ADMIN: Pendaftar (Applicants) ---------- */
function renderAdminApplicants(){
  const container = document.getElementById('admin-applicants-list');
  if(!container) return;
  const list = getAnggotaFromStorage();
  if(list.length===0){ container.innerHTML = `<div class="small text-muted p-3">Belum ada pendaftar baru.</div>`; return; }
  container.innerHTML = list.slice().reverse().map(a=>`
    <div class="list-group-item d-flex justify-content-between align-items-center">
      <div>
        <div class="fw-semibold">${a.nama}</div>
        <div class="small text-muted">${a.nim} • ${a.minat} • ${a.email}</div>
      </div>
      <div class="d-flex gap-2">
        <button class="btn btn-sm btn-success" onclick="adminAcceptApplicant(${a.id})">Terima</button>
        <button class="btn btn-sm btn-danger" onclick="adminDeleteApplicant(${a.id})">Hapus</button>
      </div>
    </div>
  `).join('');
}

function adminAcceptApplicant(id) {
  if (!confirm('Terima pendaftar ini menjadi anggota? Mereka akan dihapus dari daftar pendaftar.')) return;
  
  let applicants = getAnggotaFromStorage();
  let members = getMemberFromStorage();
  
  const applicantIndex = applicants.findIndex(a => a.id === id);
  if (applicantIndex === -1) return alert('Pendaftar tidak ditemukan.');

  const applicant = applicants[applicantIndex];
  members.push({
    id: applicant.id,
    nama: applicant.nama,
    nim: applicant.nim,
    minat: applicant.minat,
    joined: new Date().toISOString()
  });
  
  applicants.splice(applicantIndex, 1);
  
  saveAnggotaToStorage(applicants);
  saveMemberToStorage(members);
  
  renderAdminApplicants();
  renderAdminStats();
}

function adminDeleteApplicant(id){
  if(!confirm('Hapus pendaftar ini?')) return;
  let list = getAnggotaFromStorage();
  list = list.filter(x=>x.id!==id);
  saveAnggotaToStorage(list);
  renderAdminApplicants();
  renderAdminStats();
}

function adminDeleteAllApplicants() {
  if (confirm('Apakah Anda yakin ingin menghapus SEMUA riwayat pendaftar?')) {
    saveAnggotaToStorage([]);
    renderAdminApplicants();
    renderAdminStats();
  }
}

/* ---------- ADMIN: Anggota (Members) ---------- */
function renderAdminMembers() {
  const container = document.getElementById('admin-member-list');
  if (!container) return;
  const list = getMemberFromStorage();
  if (list.length === 0) { container.innerHTML = `<div class="small text-muted p-3">Belum ada anggota yang diterima.</div>`; return; }
  
  container.innerHTML = list.slice().reverse().map(a => `
    <div class="list-group-item d-flex justify-content-between align-items-center">
      <div>
        <div class="fw-semibold">${a.nama}</div>
        <div class="small text-muted">${a.nim} • ${a.minat}</div>
      </div>
      <div class="d-flex gap-2">
        <button class="btn btn-sm btn-danger" onclick="adminRemoveMember(${a.id})">Keluarkan</button>
      </div>
    </div>
  `).join('');
}

function adminRemoveMember(id) {
  if (!confirm('Keluarkan anggota ini dari lab?')) return;
  let members = getMemberFromStorage();
  members = members.filter(m => m.id !== id);
  saveMemberToStorage(members);
  
  renderAdminMembers();
  renderAdminStats();
}


/**
 * =================================================================
 * APPLICATION INIT (ROUTER)
 * =================================================================
 */
document.addEventListener('DOMContentLoaded', () => {
  const pageId = document.body.id;

  // --- LOGIKA HEADER SCROLL DIHAPUS ---

  const yearSpan = document.getElementById('footer-year');
  if (yearSpan) {
    yearSpan.textContent = new Date().getFullYear();
  }

  // Router sederhana berdasarkan ID body
  switch (pageId) {
    // --- Halaman Publik ---
    case 'page-home':
      renderPersonilPreview();
      renderBlogPreview();
      break;
    case 'page-profile':
      renderProfileContent();
      break;
    case 'page-personil-list':
      renderPersonilList();
      renderPublicMembers();
      const searchBox = document.getElementById('search-personil');
      if (searchBox) {
        searchBox.addEventListener('input', (e) => renderPersonilList(e.target.value));
      }
      break;
    case 'page-personil-detail':
      renderPersonilDetail();
      break;
    case 'page-daftar':
      initRekrutForm();
      break;
    case 'page-blog-list':
      renderBlogList();
      break;
    case 'page-blog-detail':
      renderArticleDetail();
      break;
    case 'page-login':
      initAdminLogin();
      break;
    
    // --- Halaman Admin ---
    case 'page-admin-dashboard':
      protectAdmin();
      renderAdminStats();
      break;
    case 'page-admin-personil':
      protectAdmin();
      renderAdminPersonil();
      const personilForm = document.getElementById('form-personil-admin');
      if (personilForm) {
        personilForm.addEventListener('submit', function(e){
          e.preventDefault();
          adminSavePersonil();
          const modalEl = document.getElementById('modalPersonil');
          const modal = bootstrap.Modal.getInstance(modalEl);
          if(modal) modal.hide();
          personilForm.reset();
          document.getElementById('p-id').value = '';
        });
        const modalEl = document.getElementById('modalPersonil');
        modalEl.addEventListener('hidden.bs.modal', () => {
            personilForm.reset();
            document.getElementById('p-id').value = '';
        });
      }
      break;
    case 'page-admin-blog':
      protectAdmin();
      renderAdminBlog();
      const blogForm = document.getElementById('form-blog-admin');
      if (blogForm) {
        blogForm.addEventListener('submit', function(e){
          e.preventDefault();
          adminSaveBlog();
          const modalEl = document.getElementById('modalBlog');
          const modal = bootstrap.Modal.getInstance(modalEl);
          if(modal) modal.hide();
          blogForm.reset();
          document.getElementById('b-id').value = '';
        });
        const modalEl = document.getElementById('modalBlog');
        modalEl.addEventListener('hidden.bs.modal', () => {
            blogForm.reset();
            document.getElementById('b-id').value = '';
        });
      }
      break;
    case 'page-admin-pendaftar':
      protectAdmin();
      renderAdminApplicants();
      const hapusSemuaBtn = document.getElementById('hapus-semua-btn');
      if (hapusSemuaBtn) {
        hapusSemuaBtn.addEventListener('click', adminDeleteAllApplicants);
      }
      break;
    
    case 'page-admin-anggota':
      protectAdmin();
      renderAdminMembers();
      break;
  }

  // Handle admin logout button
  const logoutBtn = document.getElementById('admin-logout-btn');
  if (logoutBtn) {
    logoutBtn.addEventListener('click', (e) => {
      e.preventDefault();
      adminLogout();
    });
  }
});

/**
 * =================================================================
 * GLOBAL FUNCTIONS (untuk onclick="" di HTML)
 * =================================================================
 */
window.adminEditPersonil = adminEditPersonil;
window.adminDeletePersonil = adminDeletePersonil;
window.adminEditBlog = adminEditBlog;
window.adminDeleteBlog = adminDeleteBlog;
window.adminDeleteApplicant = adminDeleteApplicant;
window.adminAcceptApplicant = adminAcceptApplicant;
window.adminRemoveMember = adminRemoveMember;