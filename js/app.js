/**
 * SIPADES — Sistem Informasi Pelayanan Administrasi Desa/Kelurahan
 * Main Application Controller
 * 
 * Handles routing, authentication, and all page rendering for:
 * - Masyarakat (Public/Citizen)
 * - Petugas (Officer)
 * - Kepala Desa/Lurah (Village Head)
 * - Admin (Administrator)
 */

// ============================================================
//  GLOBAL STATE
// ============================================================
const state = {
    currentUser: null,
    currentPage: 'landing',
    sidebarOpen: false,
    uploadedFiles: []
};

const DESA_NAME = 'Desa Suka Maju';
const KECAMATAN = 'Kecamatan Bukit Raya';
const KABUPATEN = 'Kabupaten Kampar';
const PROVINSI = 'Provinsi Riau';

// ============================================================
//  UTILITY FUNCTIONS
// ============================================================
function $(sel) { return document.querySelector(sel); }
function $$(sel) { return document.querySelectorAll(sel); }

function formatDate(isoStr) {
    if (!isoStr) return '-';
    const d = new Date(isoStr);
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
}

function formatDateTime(isoStr) {
    if (!isoStr) return '-';
    const d = new Date(isoStr);
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function statusBadge(status) {
    const map = {
        'DIAJUKAN': 'diajukan',
        'DIPERIKSA': 'diperiksa',
        'REVISI': 'revisi',
        'DIVERIFIKASI': 'diverifikasi',
        'MENUNGGU_PERSETUJUAN': 'menunggu',
        'DISETUJUI': 'disetujui',
        'DITOLAK': 'ditolak',
        'DIPROSES': 'diproses',
        'SELESAI': 'selesai'
    };
    const label = status.replace(/_/g, ' ');
    return `<span class="badge badge-${map[status] || 'diajukan'}">${label}</span>`;
}

function roleLabel(role) {
    const map = { masyarakat: 'Masyarakat', petugas: 'Petugas', kepala: 'Kepala Desa/Lurah', admin: 'Administrator' };
    return map[role] || role;
}

function getInitials(name) {
    return name.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase();
}

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str || '';
    return div.innerHTML;
}

function debounce(fn, delay = 300) {
    let timer;
    return (...args) => { clearTimeout(timer); timer = setTimeout(() => fn(...args), delay); };
}

function getPasswordStrength(pwd) {
    if (!pwd) return { level: 0, label: '', cls: '' };
    let score = 0;
    if (pwd.length >= 6) score++;
    if (pwd.length >= 10) score++;
    if (/[A-Z]/.test(pwd)) score++;
    if (/[0-9]/.test(pwd)) score++;
    if (/[^A-Za-z0-9]/.test(pwd)) score++;
    if (score <= 2) return { level: 1, label: 'Lemah', cls: 'weak' };
    if (score <= 3) return { level: 2, label: 'Sedang', cls: 'medium' };
    return { level: 3, label: 'Kuat', cls: 'strong' };
}

// ============================================================
//  TOAST NOTIFICATIONS
// ============================================================
function showToast(message, type = 'info') {
    const container = document.getElementById('toast-container');
    const icons = { success: 'fa-circle-check', error: 'fa-circle-xmark', warning: 'fa-triangle-exclamation', info: 'fa-circle-info' };
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <i class="fas ${icons[type]} toast-icon"></i>
        <span class="toast-message">${message}</span>
        <button class="toast-close" onclick="this.parentElement.remove()"><i class="fas fa-xmark"></i></button>
    `;
    container.appendChild(toast);
    setTimeout(() => toast.remove(), 4000);
}

// ============================================================
//  MODAL SYSTEM
// ============================================================
function showModal(title, content, footerHtml = '', large = false) {
    const existing = document.querySelector('.modal-overlay');
    if (existing) existing.remove();
    const overlay = document.createElement('div');
    overlay.className = 'modal-overlay';
    overlay.innerHTML = `
        <div class="modal ${large ? 'modal-lg' : ''}">
            <div class="modal-header">
                <h3>${title}</h3>
                <button class="modal-close" onclick="closeModal()"><i class="fas fa-xmark"></i></button>
            </div>
            <div class="modal-body">${content}</div>
            ${footerHtml ? `<div class="modal-footer">${footerHtml}</div>` : ''}
        </div>
    `;
    overlay.addEventListener('click', (e) => { if (e.target === overlay) closeModal(); });
    document.body.appendChild(overlay);
}

function closeModal() {
    const overlay = document.querySelector('.modal-overlay');
    if (overlay) overlay.remove();
}

// ============================================================
//  AUTHENTICATION
// ============================================================
function login(email, password) {
    const user = db.findUserByEmail(email);
    if (!user) return { success: false, message: 'Email tidak ditemukan.' };
    if (!db.verifyPassword(password, user.password)) return { success: false, message: 'Password salah.' };
    state.currentUser = user;
    sessionStorage.setItem('sipades_user', JSON.stringify(user));
    return { success: true, user };
}

function logout() {
    state.currentUser = null;
    sessionStorage.removeItem('sipades_user');
    navigate('landing');
}

function checkSession() {
    const saved = sessionStorage.getItem('sipades_user');
    if (saved) {
        state.currentUser = JSON.parse(saved);
        return true;
    }
    return false;
}

function requireAuth(allowedRoles = []) {
    if (!state.currentUser) { navigate('login'); return false; }
    if (allowedRoles.length > 0 && !allowedRoles.includes(state.currentUser.role)) {
        showToast('Anda tidak memiliki akses ke halaman ini.', 'error');
        navigate(getDashboardRoute(state.currentUser.role));
        return false;
    }
    return true;
}

function getDashboardRoute(role) {
    const map = { masyarakat: 'masyarakat-dashboard', petugas: 'petugas-dashboard', kepala: 'kepala-dashboard', admin: 'admin-dashboard' };
    return map[role] || 'landing';
}

// ============================================================
//  ROUTER
// ============================================================
function navigate(page, params = {}) {
    state.currentPage = page;
    state.pageParams = params;
    render();
    window.scrollTo(0, 0);
}

function render() {
    const app = document.getElementById('app');
    const page = state.currentPage;

    // Public pages
    if (page === 'landing') return renderLanding(app);
    if (page === 'login') return renderLogin(app);
    if (page === 'register') return renderRegister(app);

    // Auth check
    if (!state.currentUser) return navigate('login');

    // Dashboard routing by role
    const role = state.currentUser.role;
    const renderers = {
        // Masyarakat
        'masyarakat-dashboard': () => renderMasyarakatDashboard(app),
        'masyarakat-pelayanan': () => renderPelayananList(app),
        'masyarakat-ajukan': () => renderFormPengajuan(app),
        'masyarakat-status': () => renderStatusPengajuan(app),
        'masyarakat-detail': () => renderDetailPengajuanMasyarakat(app),
        'masyarakat-riwayat': () => renderRiwayatMasyarakat(app),
        'masyarakat-profil': () => renderProfilMasyarakat(app),
        'masyarakat-surat': () => renderSuratMasyarakat(app),
        // Petugas
        'petugas-dashboard': () => renderPetugasDashboard(app),
        'petugas-pengajuan': () => renderPetugasPengajuan(app),
        'petugas-detail': () => renderPetugasDetailPengajuan(app),
        'petugas-riwayat': () => renderPetugasRiwayat(app),
        // Kepala
        'kepala-dashboard': () => renderKepalaDashboard(app),
        'kepala-persetujuan': () => renderKepalaPersetujuan(app),
        'kepala-detail': () => renderKepalaDetail(app),
        'kepala-laporan': () => renderKepalaLaporan(app),
        // Admin
        'admin-dashboard': () => renderAdminDashboard(app),
        'admin-pengguna': () => renderAdminPengguna(app),
        'admin-pelayanan': () => renderAdminPelayanan(app),
        'admin-laporan': () => renderAdminLaporan(app),
        'admin-pengaturan': () => renderAdminPengaturan(app)
    };

    if (renderers[page]) renderers[page]();
    else navigate(getDashboardRoute(role));
}

// ============================================================
//  SIDEBAR GENERATOR
// ============================================================
function generateSidebar(role, activePage) {
    const unread = state.currentUser ? db.getUnreadCount(state.currentUser.id_user) : 0;

    const menus = {
        masyarakat: [
            { section: 'Menu Utama' },
            { icon: 'fa-house', label: 'Dashboard', page: 'masyarakat-dashboard' },
            { icon: 'fa-list', label: 'Pelayanan', page: 'masyarakat-pelayanan' },
            { icon: 'fa-file-circle-plus', label: 'Ajukan Pelayanan', page: 'masyarakat-ajukan' },
            { section: 'Pengajuan' },
            { icon: 'fa-clock-rotate-left', label: 'Status Pengajuan', page: 'masyarakat-status' },
            { icon: 'fa-folder-open', label: 'Riwayat', page: 'masyarakat-riwayat' },
            { section: 'Akun' },
            { icon: 'fa-user', label: 'Profil', page: 'masyarakat-profil' }
        ],
        petugas: [
            { section: 'Menu Utama' },
            { icon: 'fa-house', label: 'Dashboard', page: 'petugas-dashboard' },
            { icon: 'fa-inbox', label: 'Pengajuan Masuk', page: 'petugas-pengajuan', badgeCount: () => db.count('pengajuan', { status: 'DIAJUKAN' }) + db.count('pengajuan', { status: 'REVISI' }) },
            { section: 'Riwayat' },
            { icon: 'fa-folder-open', label: 'Riwayat Pelayanan', page: 'petugas-riwayat' }
        ],
        kepala: [
            { section: 'Menu Utama' },
            { icon: 'fa-house', label: 'Dashboard', page: 'kepala-dashboard' },
            { icon: 'fa-stamp', label: 'Persetujuan', page: 'kepala-persetujuan', badgeCount: () => db.count('pengajuan', { status: 'MENUNGGU_PERSETUJUAN' }) },
            { section: 'Laporan' },
            { icon: 'fa-chart-bar', label: 'Laporan', page: 'kepala-laporan' }
        ],
        admin: [
            { section: 'Menu Utama' },
            { icon: 'fa-house', label: 'Dashboard', page: 'admin-dashboard' },
            { icon: 'fa-users', label: 'Pengguna', page: 'admin-pengguna' },
            { icon: 'fa-clipboard-list', label: 'Pelayanan', page: 'admin-pelayanan' },
            { section: 'Laporan' },
            { icon: 'fa-chart-bar', label: 'Laporan', page: 'admin-laporan' },
            { section: 'Sistem' },
            { icon: 'fa-gear', label: 'Pengaturan', page: 'admin-pengaturan' }
        ]
    };

    const items = menus[role] || [];
    let navHtml = '';
    items.forEach(item => {
        if (item.section) {
            navHtml += `<div class="nav-section-title">${item.section}</div>`;
        } else {
            const isActive = activePage === item.page ? 'active' : '';
            const badge = item.badgeCount ? item.badgeCount() : 0;
            navHtml += `
                <div class="nav-item ${isActive}" onclick="navigate('${item.page}')">
                    <i class="fas ${item.icon}"></i>
                    <span>${item.label}</span>
                    ${badge > 0 ? `<span class="nav-badge">${badge}</span>` : ''}
                </div>
            `;
        }
    });

    return `
        <aside class="sidebar ${state.sidebarOpen ? 'open' : ''}" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo"><i class="fas fa-landmark"></i></div>
                <div class="sidebar-brand">
                    <h3>SIPADES</h3>
                    <span>${DESA_NAME}</span>
                </div>
            </div>
            <nav class="sidebar-nav">${navHtml}</nav>
            <div class="sidebar-footer">
                <div class="sidebar-user">
                    <div class="sidebar-avatar">${getInitials(state.currentUser.nama)}</div>
                    <div class="sidebar-user-info">
                        <div class="sidebar-user-name">${escapeHtml(state.currentUser.nama)}</div>
                        <div class="sidebar-user-role">${roleLabel(state.currentUser.role)}</div>
                    </div>
                </div>
            </div>
        </aside>
    `;
}

function generateTopBar(title, breadcrumbs = []) {
    const unread = db.getUnreadCount(state.currentUser.id_user);
    let bcHtml = breadcrumbs.map((b, i) => {
        if (i === breadcrumbs.length - 1) return `<span class="current">${b.label}</span>`;
        return `<span class="link" style="cursor:pointer" onclick="navigate('${b.page}')">${b.label}</span><span class="separator">/</span>`;
    }).join('');

    return `
        <header class="top-bar">
            <div class="top-bar-left">
                <button class="menu-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
                <div class="breadcrumb">${bcHtml}</div>
            </div>
            <div class="top-bar-right">
                <button class="notif-btn" onclick="showNotifikasi()" title="Notifikasi">
                    <i class="fas fa-bell"></i>
                    ${unread > 0 ? `<span class="notif-badge">${unread}</span>` : ''}
                </button>
                <div class="dropdown">
                    <button class="btn btn-outline btn-sm" onclick="toggleDropdown(this)">
                        <i class="fas fa-user"></i>
                        <span class="hide-mobile">${escapeHtml(state.currentUser.nama.split(' ')[0])}</span>
                        <i class="fas fa-chevron-down" style="font-size:0.625rem"></i>
                    </button>
                    <div class="dropdown-menu">
                        ${state.currentUser.role === 'masyarakat' ? `<button class="dropdown-item" onclick="navigate('masyarakat-profil');closeDropdowns()"><i class="fas fa-user"></i> Profil</button>` : ''}
                        <button class="dropdown-item" onclick="closeDropdowns();logout()"><i class="fas fa-arrow-right-from-bracket"></i> Keluar</button>
                    </div>
                </div>
            </div>
        </header>
    `;
}

function appShell(role, activePage, title, breadcrumbs, content) {
    return `
        ${generateSidebar(role, activePage)}
        <main class="main-content">
            ${generateTopBar(title, breadcrumbs)}
            <div class="page-content animate-fadeInUp">
                ${content}
            </div>
        </main>
    `;
}

function toggleSidebar() {
    state.sidebarOpen = !state.sidebarOpen;
    const sb = document.getElementById('sidebar');
    if (sb) sb.classList.toggle('open', state.sidebarOpen);
}

function toggleDropdown(btn) {
    const menu = btn.nextElementSibling;
    document.querySelectorAll('.dropdown-menu.show').forEach(m => { if (m !== menu) m.classList.remove('show'); });
    menu.classList.toggle('show');
}

function closeDropdowns() {
    document.querySelectorAll('.dropdown-menu.show').forEach(m => m.classList.remove('show'));
}

// Close dropdowns on outside click
document.addEventListener('click', (e) => {
    if (!e.target.closest('.dropdown')) closeDropdowns();
    if (!e.target.closest('.sidebar') && !e.target.closest('.menu-toggle') && state.sidebarOpen) {
        state.sidebarOpen = false;
        const sb = document.getElementById('sidebar');
        if (sb) sb.classList.remove('open');
    }
});

// ============================================================
//  NOTIFIKASI VIEW
// ============================================================
function showNotifikasi() {
    const notifs = db.getNotifikasiByUser(state.currentUser.id_user);
    let html = '';
    if (notifs.length === 0) {
        html = '<div class="empty-state"><i class="fas fa-bell-slash"></i><h3>Tidak ada notifikasi</h3></div>';
    } else {
        const unreadCount = notifs.filter(n => !n.status_baca).length;
        html = '<div style="max-height:400px;overflow-y:auto">';
        notifs.forEach(n => {
            html += `
                <div style="padding:0.75rem;border-bottom:1px solid var(--border-light);opacity:${n.status_baca ? '0.6' : '1'}">
                    <p style="font-size:var(--font-sm);color:var(--text-primary)">${escapeHtml(n.pesan)}</p>
                    <p style="font-size:var(--font-xs);color:var(--text-muted);margin-top:0.25rem">${formatDateTime(n.tanggal)}</p>
                </div>
            `;
            if (!n.status_baca) db.markNotifikasiRead(n.id_notifikasi);
        });
        html += '</div>';
    }
    const footerHtml = notifs.length > 0 ? `<button class="btn btn-outline btn-sm" onclick="markAllNotifRead();closeModal()"><i class="fas fa-check-double"></i> Tandai Semua Dibaca</button>` : '';
    showModal('Notifikasi', html, footerHtml);
}

function markAllNotifRead() {
    const notifs = db.getNotifikasiByUser(state.currentUser.id_user);
    notifs.forEach(n => { if (!n.status_baca) db.markNotifikasiRead(n.id_notifikasi); });
    render();
}

// ============================================================
//  PAGE: LANDING
// ============================================================
function renderLanding(app) {
    const pelayanan = db.getAll('jenis_pelayanan');
    let cardsHtml = pelayanan.map(p => {
        const reqs = p.persyaratan.split(',');
        return `
            <div class="service-card">
                <div class="service-card-icon"><i class="fas ${p.icon}"></i></div>
                <h3>${escapeHtml(p.nama_pelayanan)}</h3>
                <p>${escapeHtml(p.deskripsi)}</p>
                <div class="service-requirements">
                    ${reqs.map(r => `<span class="service-req-tag"><i class="fas fa-check-circle" style="color:var(--accent-primary);margin-right:0.25rem"></i> ${escapeHtml(r.trim())}</span>`).join('')}
                </div>
            </div>
        `;
    }).join('');

    app.innerHTML = `
        <div class="public-page">
            <div class="gov-topbar">
                <div class="gov-topbar-left">
                    <i class="fas fa-landmark"></i>
                    <span>REPUBLIK INDONESIA &bull; PEMERINTAH ${KABUPATEN.toUpperCase()} &bull; ${DESA_NAME.toUpperCase()}</span>
                </div>
                <div class="gov-topbar-right">
                    <span><i class="fas fa-clock"></i> Pelayanan Publik Digital 24/7</span>
                </div>
            </div>

            <nav class="public-navbar">
                <div class="public-navbar-container">
                    <div class="public-brand">
                        <div class="public-brand-logo"><i class="fas fa-landmark"></i></div>
                        <div class="public-brand-text">
                            <h2>SIPADES</h2>
                            <span>${DESA_NAME}, ${KECAMATAN}</span>
                        </div>
                    </div>
                    <div class="public-nav-links">
                        <a href="#layanan" class="public-nav-link"><i class="fas fa-list-check"></i> Layanan Surat</a>
                        <button class="btn btn-outline btn-sm" onclick="navigate('login')">
                            <i class="fas fa-arrow-right-to-bracket"></i> Masuk
                        </button>
                        <button class="btn btn-primary btn-sm" onclick="navigate('register')">
                            <i class="fas fa-user-plus"></i> Daftar Akun
                        </button>
                    </div>
                </div>
            </nav>

            <section class="hero-section">
                <div class="hero-badge"><i class="fas fa-shield-halved"></i> Portal Resmi Pelayanan Publik Desa Digital</div>
                <h1 class="hero-title">Sistem Informasi Pelayanan<br>Administrasi Desa/Kelurahan</h1>
                <p class="hero-desc">Layanan pembuatan surat keterangan desa secara terpadu, mandiri, dan transparan. Ajukan surat dari mana saja tanpa perlu mengantre di kantor desa.</p>
                <div class="hero-actions">
                    <button class="btn btn-primary btn-lg" onclick="navigate('login')">
                        <i class="fas fa-arrow-right-to-bracket"></i> Masuk Pengajuan Surat
                    </button>
                    <button class="btn btn-secondary btn-lg" onclick="navigate('register')">
                        <i class="fas fa-user-plus"></i> Registrasi Pemohon
                    </button>
                </div>
            </section>

            <section class="services-section" id="layanan">
                <h2 class="section-title">Katalog Layanan Surat Digital</h2>
                <p class="section-subtitle">Pilih jenis pelayanan administrasi desa yang Anda butuhkan di bawah ini</p>
                <div class="services-grid">${cardsHtml}</div>
            </section>

            <section style="background:#f1f5f9;padding:4rem 1.5rem;border-top:1px solid var(--border-color)">
                <div style="max-width:1240px;margin:0 auto">
                    <h2 class="section-title">Alur Pelayanan</h2>
                    <p class="section-subtitle">4 langkah mudah untuk mengajukan surat administrasi desa</p>
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:2rem;text-align:center">
                        <div style="padding:1.5rem">
                            <div style="width:64px;height:64px;background:var(--accent-gradient);border-radius:var(--radius-full);display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;color:#fff;font-size:1.5rem;box-shadow:0 4px 12px rgba(15,41,66,0.2)">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div style="font-size:var(--font-xs);font-weight:700;color:var(--accent-primary);margin-bottom:0.25rem">LANGKAH 1</div>
                            <h3 style="font-size:var(--font-lg);margin-bottom:0.5rem">Daftar Akun</h3>
                            <p style="font-size:var(--font-sm);color:var(--text-secondary)">Registrasi akun dengan data KTP. Cukup sekali daftar untuk semua layanan.</p>
                        </div>
                        <div style="padding:1.5rem">
                            <div style="width:64px;height:64px;background:var(--accent-gradient);border-radius:var(--radius-full);display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;color:#fff;font-size:1.5rem;box-shadow:0 4px 12px rgba(15,41,66,0.2)">
                                <i class="fas fa-file-circle-plus"></i>
                            </div>
                            <div style="font-size:var(--font-xs);font-weight:700;color:var(--accent-primary);margin-bottom:0.25rem">LANGKAH 2</div>
                            <h3 style="font-size:var(--font-lg);margin-bottom:0.5rem">Ajukan Surat</h3>
                            <p style="font-size:var(--font-sm);color:var(--text-secondary)">Isi formulir dan unggah dokumen persyaratan secara online.</p>
                        </div>
                        <div style="padding:1.5rem">
                            <div style="width:64px;height:64px;background:var(--accent-gradient);border-radius:var(--radius-full);display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;color:#fff;font-size:1.5rem;box-shadow:0 4px 12px rgba(15,41,66,0.2)">
                                <i class="fas fa-clipboard-check"></i>
                            </div>
                            <div style="font-size:var(--font-xs);font-weight:700;color:var(--accent-primary);margin-bottom:0.25rem">LANGKAH 3</div>
                            <h3 style="font-size:var(--font-lg);margin-bottom:0.5rem">Verifikasi</h3>
                            <p style="font-size:var(--font-sm);color:var(--text-secondary)">Petugas memeriksa berkas, Kepala Desa memberikan persetujuan.</p>
                        </div>
                        <div style="padding:1.5rem">
                            <div style="width:64px;height:64px;background:linear-gradient(135deg,#16a34a,#15803d);border-radius:var(--radius-full);display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;color:#fff;font-size:1.5rem;box-shadow:0 4px 12px rgba(22,163,74,0.3)">
                                <i class="fas fa-download"></i>
                            </div>
                            <div style="font-size:var(--font-xs);font-weight:700;color:#16a34a;margin-bottom:0.25rem">LANGKAH 4</div>
                            <h3 style="font-size:var(--font-lg);margin-bottom:0.5rem">Unduh Surat</h3>
                            <p style="font-size:var(--font-sm);color:var(--text-secondary)">Surat keterangan resmi siap diunduh dan dicetak.</p>
                        </div>
                    </div>
                </div>
            </section>

            <footer style="background:var(--header-navy);color:#ffffff;padding:3rem 1.5rem;border-top:3px solid var(--header-gold)">
                <div style="max-width:1240px;margin:0 auto;display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:2rem">
                    <div>
                        <h3 style="color:#ffffff;font-size:var(--font-lg);margin-bottom:1rem;display:flex;align-items:center;gap:0.5rem"><i class="fas fa-landmark" style="color:var(--header-gold-light)"></i> SIPADES</h3>
                        <p style="color:#94a3b8;font-size:var(--font-sm);line-height:1.6">Sistem Informasi Pelayanan Administrasi Desa/Kelurahan. Portal resmi pelayanan publik digital terpadu.</p>
                    </div>
                    <div>
                        <h4 style="color:#ffffff;margin-bottom:0.75rem;font-size:var(--font-sm)">Kontak Kantor Desa</h4>
                        <p style="color:#94a3b8;font-size:var(--font-xs);line-height:1.8">
                            <i class="fas fa-map-marker-alt" style="width:16px;color:var(--header-gold-light)"></i> ${DESA_NAME}, ${KECAMATAN}<br>
                            <i class="fas fa-map-marker-alt" style="width:16px;color:var(--header-gold-light)"></i> ${KABUPATEN}, ${PROVINSI}<br>
                            <i class="fas fa-phone" style="width:16px;color:var(--header-gold-light)"></i> (0761) 123-4567<br>
                            <i class="fas fa-envelope" style="width:16px;color:var(--header-gold-light)"></i> info@sipades.go.id
                        </p>
                    </div>
                    <div>
                        <h4 style="color:#ffffff;margin-bottom:0.75rem;font-size:var(--font-sm)">Jam Pelayanan</h4>
                        <p style="color:#94a3b8;font-size:var(--font-xs);line-height:1.8">
                            <i class="fas fa-clock" style="width:16px;color:var(--header-gold-light)"></i> Senin - Jumat: 08:00 - 16:00<br>
                            <i class="fas fa-clock" style="width:16px;color:var(--header-gold-light)"></i> Sabtu: 08:00 - 12:00<br>
                            <i class="fas fa-globe" style="width:16px;color:var(--header-gold-light)"></i> Online: 24 Jam
                        </p>
                    </div>
                </div>
                <div style="border-top:1px solid rgba(255,255,255,0.1);margin-top:2rem;padding-top:1.5rem;text-align:center">
                    <p style="color:#64748b;font-size:var(--font-xs)">&copy; 2026 Pemerintah ${DESA_NAME}. Hak Cipta Dilindungi Undang-Undang.</p>
                </div>
            </footer>
        </div>
    `;
}

// ============================================================
//  PAGE: LOGIN
// ============================================================
function renderLogin(app) {
    app.innerHTML = `
        <div class="auth-container">
            <div class="auth-card">
                <div class="logo-area">
                    <div class="logo-icon"><i class="fas fa-landmark"></i></div>
                    <h2>Masuk ke SIPADES</h2>
                    <p>Sistem Informasi Pelayanan Administrasi Desa</p>
                </div>
                <form id="loginForm" onsubmit="handleLogin(event)">
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" id="loginEmail" placeholder="Masukkan email" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" id="loginPassword" placeholder="Masukkan password" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block btn-lg mt-2">
                        <i class="fas fa-arrow-right-to-bracket"></i> Masuk
                    </button>
                </form>
                <div class="form-divider">atau</div>
                <button class="btn btn-secondary btn-block" onclick="navigate('register')">
                    <i class="fas fa-user-plus"></i> Daftar Akun Baru
                </button>
                <p style="text-align:center;margin-top:1rem">
                    <a href="javascript:void(0)" onclick="navigate('landing')" style="font-size:var(--font-sm)">
                        <i class="fas fa-arrow-left"></i> Kembali ke Beranda
                    </a>
                </p>
                <div style="margin-top:1.5rem;padding:1rem;background:rgba(99,102,241,0.08);border-radius:var(--radius-sm);font-size:var(--font-xs);color:var(--text-secondary)">
                    <strong style="color:var(--text-heading)">Demo Akun:</strong><br>
                    Admin: admin@sipades.go.id / admin123<br>
                    Petugas: petugas@sipades.go.id / petugas123<br>
                    Kepala: kepala@sipades.go.id / kepala123<br>
                    Masyarakat: budi@email.com / masyarakat123
                </div>
            </div>
        </div>
    `;
}

function handleLogin(e) {
    e.preventDefault();
    const email = document.getElementById('loginEmail').value.trim();
    const password = document.getElementById('loginPassword').value;
    const result = login(email, password);
    if (result.success) {
        showToast(`Selamat datang, ${result.user.nama}!`, 'success');
        navigate(getDashboardRoute(result.user.role));
    } else {
        showToast(result.message, 'error');
    }
}

// ============================================================
//  PAGE: REGISTER
// ============================================================
function renderRegister(app) {
    app.innerHTML = `
        <div class="auth-container">
            <div class="auth-card" style="max-width:520px">
                <div class="logo-area">
                    <div class="logo-icon"><i class="fas fa-user-plus"></i></div>
                    <h2>Daftar Akun</h2>
                    <p>Buat akun untuk menggunakan layanan SIPADES</p>
                </div>
                <form id="registerForm" onsubmit="handleRegister(event)">
                    <div class="form-group">
                        <label class="form-label">Nama Lengkap <span class="required">*</span></label>
                        <input type="text" class="form-control" id="regNama" placeholder="Masukkan nama lengkap" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">NIK <span class="required">*</span></label>
                        <input type="text" class="form-control" id="regNIK" placeholder="16 digit NIK" maxlength="16" pattern="[0-9]{16}" title="NIK harus 16 digit angka" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email <span class="required">*</span></label>
                        <input type="email" class="form-control" id="regEmail" placeholder="Masukkan email" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">No. HP <span class="required">*</span></label>
                        <input type="text" class="form-control" id="regHP" placeholder="Contoh: 081234567890" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Alamat <span class="required">*</span></label>
                        <textarea class="form-control" id="regAlamat" placeholder="Masukkan alamat lengkap" required></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Password <span class="required">*</span></label>
                        <input type="password" class="form-control" id="regPassword" placeholder="Minimal 6 karakter" minlength="6" required oninput="updatePasswordStrength(this.value)">
                        <div class="password-strength" id="pwdStrength" style="display:none">
                            <div class="password-strength-bar" id="pwdBar1"></div>
                            <div class="password-strength-bar" id="pwdBar2"></div>
                            <div class="password-strength-bar" id="pwdBar3"></div>
                            <span class="password-strength-label" id="pwdLabel"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Konfirmasi Password <span class="required">*</span></label>
                        <input type="password" class="form-control" id="regConfirm" placeholder="Ulangi password" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block btn-lg mt-2">
                        <i class="fas fa-user-plus"></i> Daftar
                    </button>
                </form>
                <p style="text-align:center;margin-top:1rem;font-size:var(--font-sm)">
                    Sudah punya akun? <a href="javascript:void(0)" onclick="navigate('login')">Masuk</a>
                </p>
            </div>
        </div>
    `;
}

function updatePasswordStrength(pwd) {
    const container = document.getElementById('pwdStrength');
    const bars = [document.getElementById('pwdBar1'), document.getElementById('pwdBar2'), document.getElementById('pwdBar3')];
    const label = document.getElementById('pwdLabel');
    if (!pwd) { container.style.display = 'none'; return; }
    container.style.display = 'flex';
    const strength = getPasswordStrength(pwd);
    bars.forEach((bar, i) => {
        bar.className = 'password-strength-bar';
        if (i < strength.level) bar.className += ` active ${strength.cls}`;
    });
    label.textContent = strength.label;
    label.style.color = strength.cls === 'weak' ? '#ef4444' : strength.cls === 'medium' ? '#f59e0b' : '#22c55e';
}

function handleRegister(e) {
    e.preventDefault();
    const nama = document.getElementById('regNama').value.trim();
    const nik = document.getElementById('regNIK').value.trim();
    const email = document.getElementById('regEmail').value.trim();
    const hp = document.getElementById('regHP').value.trim();
    const alamat = document.getElementById('regAlamat').value.trim();
    const pwd = document.getElementById('regPassword').value;
    const confirm = document.getElementById('regConfirm').value;

    if (!/^\d{16}$/.test(nik)) return showToast('NIK harus tepat 16 digit angka.', 'error');
    if (pwd.length < 6) return showToast('Password minimal 6 karakter.', 'error');
    if (pwd !== confirm) return showToast('Password tidak cocok.', 'error');
    if (db.findUserByEmail(email)) return showToast('Email sudah terdaftar.', 'error');
    
    // Check duplicate NIK
    const existingNIK = db.getAll('masyarakat').find(m => m.nik === nik);
    if (existingNIK) return showToast('NIK sudah terdaftar dalam sistem.', 'error');

    db.registerMasyarakat(nama, email, pwd, nik, alamat, hp);
    showToast('Registrasi berhasil! Silakan login.', 'success');
    navigate('login');
}

// ============================================================
//  MASYARAKAT PAGES
// ============================================================

function renderMasyarakatDashboard(app) {
    if (!requireAuth(['masyarakat'])) return;
    const masy = db.getMasyarakatByUserId(state.currentUser.id_user);
    const pengajuan = masy ? db.getPengajuanByMasyarakat(masy.id_masyarakat) : [];
    const total = pengajuan.length;
    const proses = pengajuan.filter(p => !['SELESAI', 'DITOLAK'].includes(p.status)).length;
    const selesai = pengajuan.filter(p => p.status === 'SELESAI').length;
    const ditolak = pengajuan.filter(p => p.status === 'DITOLAK').length;

    const recent = pengajuan.slice(0, 5);
    let recentHtml = '';
    if (recent.length === 0) {
        recentHtml = `<div class="empty-state"><i class="fas fa-inbox"></i><h3>Belum ada pengajuan</h3><p>Mulai ajukan pelayanan administrasi Anda</p>
            <button class="btn btn-primary" onclick="navigate('masyarakat-ajukan')"><i class="fas fa-plus"></i> Ajukan Pelayanan</button></div>`;
    } else {
        recentHtml = `<div class="overflow-auto"><table class="data-table">
            <thead><tr><th>Tanggal</th><th>Pelayanan</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>${recent.map(p => {
                const ply = db.getPelayananById(p.id_pelayanan);
                return `<tr>
                    <td>${formatDate(p.tanggal_pengajuan)}</td>
                    <td style="color:var(--text-primary)">${ply ? escapeHtml(ply.nama_pelayanan) : '-'}</td>
                    <td>${statusBadge(p.status)}</td>
                    <td><button class="btn btn-outline btn-sm" onclick="navigate('masyarakat-detail',{id:'${p.id_pengajuan}'})"><i class="fas fa-eye"></i> Detail</button></td>
                </tr>`;
            }).join('')}</tbody>
        </table></div>`;
    }

    app.innerHTML = appShell('masyarakat', 'masyarakat-dashboard', 'Dashboard',
        [{ label: 'Dashboard', page: 'masyarakat-dashboard' }],
        `
        <div class="page-header">
            <h1>Selamat Datang, ${escapeHtml(state.currentUser.nama.split(' ')[0])} 👋</h1>
            <p>Kelola pengajuan pelayanan administrasi Anda dari sini.</p>
        </div>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header"><div class="stat-icon blue"><i class="fas fa-file-lines"></i></div></div>
                <div class="stat-value">${total}</div>
                <div class="stat-label">Total Pengajuan</div>
            </div>
            <div class="stat-card">
                <div class="stat-header"><div class="stat-icon amber"><i class="fas fa-spinner"></i></div></div>
                <div class="stat-value">${proses}</div>
                <div class="stat-label">Sedang Diproses</div>
            </div>
            <div class="stat-card">
                <div class="stat-header"><div class="stat-icon green"><i class="fas fa-circle-check"></i></div></div>
                <div class="stat-value">${selesai}</div>
                <div class="stat-label">Selesai</div>
            </div>
            <div class="stat-card">
                <div class="stat-header"><div class="stat-icon red"><i class="fas fa-circle-xmark"></i></div></div>
                <div class="stat-value">${ditolak}</div>
                <div class="stat-label">Ditolak</div>
            </div>
        </div>
        <div class="table-container">
            <div class="table-header">
                <h3>Pengajuan Terbaru</h3>
                <button class="btn btn-primary btn-sm" onclick="navigate('masyarakat-ajukan')"><i class="fas fa-plus"></i> Ajukan Baru</button>
            </div>
            ${recentHtml}
        </div>
        `
    );
}

function renderPelayananList(app) {
    if (!requireAuth(['masyarakat'])) return;
    const pelayanan = db.getAll('jenis_pelayanan');
    const cardsHtml = pelayanan.map(p => {
        const reqs = p.persyaratan.split(',');
        return `
            <div class="service-card">
                <div class="service-card-icon"><i class="fas ${p.icon}"></i></div>
                <h3>${escapeHtml(p.nama_pelayanan)}</h3>
                <p>${escapeHtml(p.deskripsi)}</p>
                <div class="service-requirements mb-2">
                    ${reqs.map(r => `<span class="service-req-tag">${escapeHtml(r.trim())}</span>`).join('')}
                </div>
                <button class="btn btn-primary btn-sm" onclick="navigate('masyarakat-ajukan',{pelayanan:'${p.id_pelayanan}'})">
                    <i class="fas fa-file-circle-plus"></i> Ajukan
                </button>
            </div>
        `;
    }).join('');

    app.innerHTML = appShell('masyarakat', 'masyarakat-pelayanan', 'Pelayanan',
        [{ label: 'Dashboard', page: 'masyarakat-dashboard' }, { label: 'Pelayanan', page: 'masyarakat-pelayanan' }],
        `
        <div class="page-header">
            <h1>Daftar Pelayanan</h1>
            <p>Pilih jenis pelayanan administrasi yang Anda butuhkan</p>
        </div>
        <div class="services-grid">${cardsHtml}</div>
        `
    );
}

function renderFormPengajuan(app) {
    if (!requireAuth(['masyarakat'])) return;
    const pelayanan = db.getAll('jenis_pelayanan');
    const selectedId = state.pageParams?.pelayanan || '';

    // Generate service options
    const optionsHtml = pelayanan.map(p =>
        `<option value="${p.id_pelayanan}" ${p.id_pelayanan === selectedId ? 'selected' : ''}>${escapeHtml(p.nama_pelayanan)}</option>`
    ).join('');

    app.innerHTML = appShell('masyarakat', 'masyarakat-ajukan', 'Ajukan Pelayanan',
        [{ label: 'Dashboard', page: 'masyarakat-dashboard' }, { label: 'Ajukan Pelayanan', page: 'masyarakat-ajukan' }],
        `
        <div class="page-header">
            <h1>Ajukan Pelayanan</h1>
            <p>Isi formulir pengajuan dan unggah dokumen persyaratan</p>
        </div>
        <div class="card" style="max-width:800px">
            <div class="form-group">
                <label class="form-label">Jenis Pelayanan <span class="required">*</span></label>
                <select class="form-control" id="selectPelayanan" onchange="loadFormulir()">
                    <option value="">-- Pilih Pelayanan --</option>
                    ${optionsHtml}
                </select>
            </div>
            <div id="persyaratanInfo" style="display:none" class="mb-2">
                <div style="padding:1rem;background:rgba(59,130,246,0.08);border:1px solid rgba(59,130,246,0.15);border-radius:var(--radius-sm)">
                    <strong style="color:var(--accent-secondary);font-size:var(--font-sm)"><i class="fas fa-info-circle"></i> Persyaratan Dokumen:</strong>
                    <div id="persyaratanList" style="margin-top:0.5rem;font-size:var(--font-sm);color:var(--text-secondary)"></div>
                </div>
            </div>
            <div id="dynamicForm"></div>
            <div id="uploadSection" style="display:none" class="mt-2">
                <label class="form-label">Upload Dokumen Persyaratan</label>
                <div class="upload-area" id="uploadArea" onclick="document.getElementById('fileInput').click()" ondrop="handleDrop(event)" ondragover="event.preventDefault();this.classList.add('dragover')" ondragleave="this.classList.remove('dragover')">
                    <i class="fas fa-cloud-arrow-up"></i>
                    <p>Klik atau seret file ke sini</p>
                    <p class="upload-hint">Format: JPG, PNG, PDF. Maksimal 5MB per file.</p>
                </div>
                <input type="file" id="fileInput" multiple accept=".jpg,.jpeg,.png,.pdf" style="display:none" onchange="handleFileSelect(this.files)">
                <div id="uploadedFilesList" class="uploaded-files"></div>
            </div>
            <div id="submitSection" style="display:none" class="mt-3">
                <div class="form-group">
                    <label class="form-label">Catatan Tambahan (Opsional)</label>
                    <textarea class="form-control" id="catatanPengajuan" placeholder="Tambahkan catatan jika diperlukan"></textarea>
                </div>
                <button class="btn btn-primary btn-lg" onclick="submitPengajuan()">
                    <i class="fas fa-paper-plane"></i> Kirim Pengajuan
                </button>
            </div>
        </div>
        `
    );

    // Auto-load form if service selected
    if (selectedId) setTimeout(() => loadFormulir(), 100);
}

function loadFormulir() {
    const pelayananId = document.getElementById('selectPelayanan').value;
    const formContainer = document.getElementById('dynamicForm');
    const persyaratanInfo = document.getElementById('persyaratanInfo');
    const uploadSection = document.getElementById('uploadSection');
    const submitSection = document.getElementById('submitSection');

    if (!pelayananId) {
        formContainer.innerHTML = '';
        persyaratanInfo.style.display = 'none';
        uploadSection.style.display = 'none';
        submitSection.style.display = 'none';
        return;
    }

    const pelayanan = db.getPelayananById(pelayananId);
    if (!pelayanan) return;

    // Show requirements
    const reqs = pelayanan.persyaratan.split(',');
    document.getElementById('persyaratanList').innerHTML = reqs.map(r =>
        `<span style="display:inline-flex;align-items:center;gap:0.25rem;margin-right:0.5rem"><i class="fas fa-check-circle" style="color:#22c55e;font-size:0.75rem"></i> ${escapeHtml(r.trim())}</span>`
    ).join('');
    persyaratanInfo.style.display = 'block';

    // Generate form fields
    const fields = JSON.parse(pelayanan.formulir);
    let html = '<div style="border-top:1px solid var(--border-light);padding-top:1.25rem;margin-top:1.25rem"><h3 style="font-size:var(--font-lg);font-weight:600;color:var(--text-heading);margin-bottom:1rem">Data Pengajuan</h3>';
    html += '<div class="detail-grid">';
    fields.forEach(f => {
        html += `<div class="form-group">`;
        html += `<label class="form-label">${escapeHtml(f.label)} ${f.required ? '<span class="required">*</span>' : ''}</label>`;
        if (f.type === 'select') {
            html += `<select class="form-control" name="${f.name}" ${f.required ? 'required' : ''}>
                <option value="">-- Pilih --</option>
                ${f.options.map(o => `<option value="${o}">${o}</option>`).join('')}
            </select>`;
        } else if (f.type === 'textarea') {
            html += `<textarea class="form-control" name="${f.name}" ${f.required ? 'required' : ''} placeholder="Masukkan ${f.label.toLowerCase()}"></textarea>`;
        } else {
            html += `<input type="${f.type}" class="form-control" name="${f.name}" ${f.required ? 'required' : ''} placeholder="Masukkan ${f.label.toLowerCase()}">`;
        }
        html += `</div>`;
    });
    html += '</div></div>';
    formContainer.innerHTML = html;
    uploadSection.style.display = 'block';
    submitSection.style.display = 'block';
    state.uploadedFiles = [];
    document.getElementById('uploadedFilesList').innerHTML = '';

    // Pre-fill NIK and name if masyarakat data exists
    const masy = db.getMasyarakatByUserId(state.currentUser.id_user);
    if (masy) {
        const nikInput = formContainer.querySelector('[name="nik"]');
        if (nikInput) nikInput.value = masy.nik;
        const namaInput = formContainer.querySelector('[name="nama_lengkap"]');
        if (namaInput) namaInput.value = state.currentUser.nama;
        const alamatInput = formContainer.querySelector('[name="alamat"]') || formContainer.querySelector('[name="alamat_lengkap"]');
        if (alamatInput) alamatInput.value = masy.alamat;
    }
}

function handleFileSelect(files) {
    const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
    const maxSize = 5 * 1024 * 1024; // 5MB
    Array.from(files).forEach(file => {
        if (!allowedTypes.includes(file.type)) {
            return showToast(`File ${file.name} tidak didukung. Gunakan JPG, PNG, atau PDF.`, 'error');
        }
        if (file.size > maxSize) {
            return showToast(`File ${file.name} terlalu besar (maks 5MB).`, 'error');
        }
        const reader = new FileReader();
        reader.onload = (e) => {
            state.uploadedFiles.push({ name: file.name, data: e.target.result, size: file.size });
            renderUploadedFiles();
        };
        reader.readAsDataURL(file);
    });
}

function handleDrop(e) {
    e.preventDefault();
    e.currentTarget.classList.remove('dragover');
    handleFileSelect(e.dataTransfer.files);
}

function renderUploadedFiles() {
    const container = document.getElementById('uploadedFilesList');
    container.innerHTML = state.uploadedFiles.map((f, i) => `
        <div class="uploaded-file">
            <i class="fas fa-file"></i>
            <span class="file-name">${escapeHtml(f.name)}</span>
            <span style="color:var(--text-muted);font-size:var(--font-xs)">${(f.size / 1024).toFixed(1)} KB</span>
            <button class="file-remove" onclick="removeFile(${i})"><i class="fas fa-xmark"></i></button>
        </div>
    `).join('');
}

function removeFile(idx) {
    state.uploadedFiles.splice(idx, 1);
    renderUploadedFiles();
}

function submitPengajuan() {
    const pelayananId = document.getElementById('selectPelayanan').value;
    if (!pelayananId) return showToast('Pilih jenis pelayanan.', 'error');

    const pelayanan = db.getPelayananById(pelayananId);
    const fields = JSON.parse(pelayanan.formulir);
    const formData = {};
    let valid = true;

    fields.forEach(f => {
        const el = document.querySelector(`[name="${f.name}"]`);
        if (el) {
            formData[f.name] = el.value.trim();
            if (f.required && !formData[f.name]) valid = false;
        }
    });

    if (!valid) return showToast('Lengkapi semua data yang wajib diisi.', 'error');

    const masy = db.getMasyarakatByUserId(state.currentUser.id_user);
    if (!masy) return showToast('Data masyarakat tidak ditemukan.', 'error');

    const catatan = document.getElementById('catatanPengajuan').value.trim();
    const pengajuan = db.createPengajuan(masy.id_masyarakat, pelayananId, JSON.stringify(formData), catatan);

    // Prevent double submission
    const submitBtn = document.querySelector('#submitSection .btn-primary');
    if (submitBtn) { submitBtn.disabled = true; submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengirim...'; }

    // Save documents
    state.uploadedFiles.forEach(f => {
        db.addDokumen(pengajuan.id_pengajuan, f.name, f.data);
    });

    // Notification to user
    db.createNotifikasi(state.currentUser.id_user, `Pengajuan ${pelayanan.nama_pelayanan} berhasil dikirim. Nomor: ${pengajuan.id_pengajuan.substring(0, 12)}`);
    
    // Notify all petugas
    const petugasList = db.getAll('petugas');
    petugasList.forEach(ptg => {
        db.createNotifikasi(ptg.id_user, `Pengajuan baru: ${pelayanan.nama_pelayanan} dari ${state.currentUser.nama}`);
    });

    showToast('Pengajuan berhasil dikirim!', 'success');
    navigate('masyarakat-status');
}

function renderStatusPengajuan(app) {
    if (!requireAuth(['masyarakat'])) return;
    const masy = db.getMasyarakatByUserId(state.currentUser.id_user);
    const pengajuan = masy ? db.getPengajuanByMasyarakat(masy.id_masyarakat).filter(p => !['SELESAI', 'DITOLAK'].includes(p.status)) : [];

    let tableHtml = '';
    if (pengajuan.length === 0) {
        tableHtml = '<div class="empty-state"><i class="fas fa-clipboard-check"></i><h3>Tidak ada pengajuan aktif</h3><p>Semua pengajuan telah selesai atau tidak ada pengajuan.</p></div>';
    } else {
        tableHtml = `<div class="overflow-auto"><table class="data-table">
            <thead><tr><th>Tanggal</th><th>Pelayanan</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>${pengajuan.map(p => {
                const ply = db.getPelayananById(p.id_pelayanan);
                return `<tr>
                    <td>${formatDate(p.tanggal_pengajuan)}</td>
                    <td style="color:var(--text-primary)">${ply ? escapeHtml(ply.nama_pelayanan) : '-'}</td>
                    <td>${statusBadge(p.status)}</td>
                    <td><button class="btn btn-outline btn-sm" onclick="navigate('masyarakat-detail',{id:'${p.id_pengajuan}'})"><i class="fas fa-eye"></i> Detail</button></td>
                </tr>`;
            }).join('')}</tbody>
        </table></div>`;
    }

    app.innerHTML = appShell('masyarakat', 'masyarakat-status', 'Status Pengajuan',
        [{ label: 'Dashboard', page: 'masyarakat-dashboard' }, { label: 'Status Pengajuan', page: 'masyarakat-status' }],
        `<div class="page-header"><h1>Status Pengajuan</h1><p>Pantau perkembangan pengajuan Anda</p></div>
        <div class="table-container"><div class="table-header"><h3>Pengajuan Aktif</h3></div>${tableHtml}</div>`
    );
}

function renderDetailPengajuanMasyarakat(app) {
    if (!requireAuth(['masyarakat'])) return;
    const id = state.pageParams?.id;
    const pengajuan = db.getById('pengajuan', 'id_pengajuan', id);
    if (!pengajuan) return navigate('masyarakat-status');

    const ply = db.getPelayananById(pengajuan.id_pelayanan);
    const docs = db.getDokumenByPengajuan(id);
    const surat = db.getSuratByPengajuan(id);
    const formData = JSON.parse(pengajuan.data_formulir || '{}');

    // Status workflow
    const allStatuses = ['DIAJUKAN', 'DIPERIKSA', 'DIVERIFIKASI', 'MENUNGGU_PERSETUJUAN', 'DISETUJUI', 'DIPROSES', 'SELESAI'];
    const statusOrder = ['DIAJUKAN', 'DIPERIKSA', 'REVISI', 'DIVERIFIKASI', 'MENUNGGU_PERSETUJUAN', 'DISETUJUI', 'DITOLAK', 'DIPROSES', 'SELESAI'];
    const currentIdx = statusOrder.indexOf(pengajuan.status);

    let workflowHtml = allStatuses.map((s, i) => {
        const sIdx = statusOrder.indexOf(s);
        let cls = '';
        if (s === pengajuan.status) cls = 'active';
        else if (sIdx < currentIdx && pengajuan.status !== 'DITOLAK' && pengajuan.status !== 'REVISI') cls = 'done';
        return `
            <div class="workflow-step ${cls}">
                <div class="workflow-step-dot"></div>
                <span>${s.replace(/_/g, ' ')}</span>
            </div>
            ${i < allStatuses.length - 1 ? `<div class="workflow-line ${cls === 'done' ? 'done' : ''}"></div>` : ''}
        `;
    }).join('');

    // Form data display
    let formHtml = Object.entries(formData).map(([key, val]) =>
        `<div class="detail-item"><div class="detail-label">${key.replace(/_/g, ' ')}</div><div class="detail-value">${escapeHtml(val)}</div></div>`
    ).join('');

    // Documents
    let docsHtml = docs.map(d =>
        `<div class="uploaded-file"><i class="fas fa-file"></i><span class="file-name">${escapeHtml(d.nama_dokumen)}</span><span style="font-size:var(--font-xs);color:var(--text-muted)">${formatDate(d.tanggal_upload)}</span></div>`
    ).join('');

    // Revision note
    let revisionHtml = '';
    if (pengajuan.status === 'REVISI' && pengajuan.catatan_revisi) {
        revisionHtml = `
            <div class="card mb-2" style="border-color:rgba(239,68,68,0.3)">
                <div class="card-header"><h3 style="color:#ef4444"><i class="fas fa-exclamation-triangle"></i> Catatan Revisi</h3></div>
                <p style="font-size:var(--font-sm);color:var(--text-secondary)">${escapeHtml(pengajuan.catatan_revisi)}</p>
                <button class="btn btn-warning btn-sm mt-2" onclick="showRevisiModal('${id}')"><i class="fas fa-edit"></i> Perbaiki Pengajuan</button>
            </div>
        `;
    }

    // Download button
    let downloadHtml = '';
    if (pengajuan.status === 'SELESAI' && surat) {
        downloadHtml = `<button class="btn btn-success btn-lg mt-2" onclick="navigate('masyarakat-surat',{id:'${id}'})"><i class="fas fa-download"></i> Lihat & Download Surat</button>`;
    }

    app.innerHTML = appShell('masyarakat', 'masyarakat-status', 'Detail Pengajuan',
        [{ label: 'Dashboard', page: 'masyarakat-dashboard' }, { label: 'Status', page: 'masyarakat-status' }, { label: 'Detail', page: 'masyarakat-detail' }],
        `
        <div class="page-header">
            <h1>Detail Pengajuan</h1>
            <p>${ply ? escapeHtml(ply.nama_pelayanan) : ''}</p>
        </div>
        <div class="workflow-bar">${workflowHtml}</div>
        ${revisionHtml}
        <div class="detail-grid mb-2">
            <div class="card">
                <div class="card-header"><h3>Informasi Pengajuan</h3>${statusBadge(pengajuan.status)}</div>
                <div class="detail-item"><div class="detail-label">Tanggal Pengajuan</div><div class="detail-value">${formatDate(pengajuan.tanggal_pengajuan)}</div></div>
                <div class="detail-item"><div class="detail-label">Jenis Pelayanan</div><div class="detail-value">${ply ? escapeHtml(ply.nama_pelayanan) : '-'}</div></div>
                ${pengajuan.catatan ? `<div class="detail-item"><div class="detail-label">Catatan</div><div class="detail-value">${escapeHtml(pengajuan.catatan)}</div></div>` : ''}
            </div>
            <div class="card">
                <div class="card-header"><h3>Dokumen</h3></div>
                ${docsHtml || '<p style="color:var(--text-muted);font-size:var(--font-sm)">Tidak ada dokumen</p>'}
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h3>Data Formulir</h3></div>
            <div class="detail-grid">${formHtml}</div>
        </div>
        ${downloadHtml}
        <button class="btn btn-outline mt-2" onclick="navigate('masyarakat-status')"><i class="fas fa-arrow-left"></i> Kembali</button>
        `
    );
}

function showRevisiModal(id) {
    const pengajuan = db.getById('pengajuan', 'id_pengajuan', id);
    const ply = db.getPelayananById(pengajuan.id_pelayanan);
    const fields = JSON.parse(ply.formulir);
    const formData = JSON.parse(pengajuan.data_formulir || '{}');

    let formHtml = fields.map(f => {
        const val = formData[f.name] || '';
        let input = '';
        if (f.type === 'select') {
            input = `<select class="form-control" name="rev_${f.name}">
                ${f.options.map(o => `<option value="${o}" ${o === val ? 'selected' : ''}>${o}</option>`).join('')}
            </select>`;
        } else if (f.type === 'textarea') {
            input = `<textarea class="form-control" name="rev_${f.name}">${escapeHtml(val)}</textarea>`;
        } else {
            input = `<input type="${f.type}" class="form-control" name="rev_${f.name}" value="${escapeHtml(val)}">`;
        }
        return `<div class="form-group"><label class="form-label">${f.label}</label>${input}</div>`;
    }).join('');

    showModal('Perbaiki Pengajuan', formHtml,
        `<button class="btn btn-secondary" onclick="closeModal()">Batal</button>
         <button class="btn btn-primary" onclick="submitRevisi('${id}')"><i class="fas fa-paper-plane"></i> Kirim Ulang</button>`, true);
}

function submitRevisi(id) {
    const pengajuan = db.getById('pengajuan', 'id_pengajuan', id);
    const ply = db.getPelayananById(pengajuan.id_pelayanan);
    const fields = JSON.parse(ply.formulir);
    const formData = {};
    fields.forEach(f => {
        const el = document.querySelector(`[name="rev_${f.name}"]`);
        if (el) formData[f.name] = el.value.trim();
    });
    db.update('pengajuan', 'id_pengajuan', id, {
        data_formulir: JSON.stringify(formData),
        status: 'DIAJUKAN',
        catatan_revisi: '',
        updated_at: new Date().toISOString()
    });
    closeModal();
    showToast('Pengajuan telah diperbaiki dan dikirim ulang.', 'success');
    navigate('masyarakat-detail', { id });
}

function renderRiwayatMasyarakat(app) {
    if (!requireAuth(['masyarakat'])) return;
    const masy = db.getMasyarakatByUserId(state.currentUser.id_user);
    const pengajuan = masy ? db.getPengajuanByMasyarakat(masy.id_masyarakat) : [];

    let tableHtml = '';
    if (pengajuan.length === 0) {
        tableHtml = '<div class="empty-state"><i class="fas fa-folder-open"></i><h3>Belum ada riwayat</h3></div>';
    } else {
        tableHtml = `<div class="overflow-auto"><table class="data-table">
            <thead><tr><th>Tanggal</th><th>Pelayanan</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>${pengajuan.map(p => {
                const ply = db.getPelayananById(p.id_pelayanan);
                return `<tr>
                    <td>${formatDate(p.tanggal_pengajuan)}</td>
                    <td style="color:var(--text-primary)">${ply ? escapeHtml(ply.nama_pelayanan) : '-'}</td>
                    <td>${statusBadge(p.status)}</td>
                    <td>
                        <button class="btn btn-outline btn-sm" onclick="navigate('masyarakat-detail',{id:'${p.id_pengajuan}'})"><i class="fas fa-eye"></i></button>
                        ${p.status === 'SELESAI' ? `<button class="btn btn-success btn-sm" onclick="navigate('masyarakat-surat',{id:'${p.id_pengajuan}'})"><i class="fas fa-download"></i></button>` : ''}
                    </td>
                </tr>`;
            }).join('')}</tbody>
        </table></div>`;
    }

    app.innerHTML = appShell('masyarakat', 'masyarakat-riwayat', 'Riwayat',
        [{ label: 'Dashboard', page: 'masyarakat-dashboard' }, { label: 'Riwayat', page: 'masyarakat-riwayat' }],
        `<div class="page-header"><h1>Riwayat Pengajuan</h1><p>Seluruh riwayat pengajuan pelayanan Anda</p></div>
        <div class="table-container"><div class="table-header"><h3>Semua Pengajuan</h3></div>${tableHtml}</div>`
    );
}

function renderProfilMasyarakat(app) {
    if (!requireAuth(['masyarakat'])) return;
    const masy = db.getMasyarakatByUserId(state.currentUser.id_user);

    app.innerHTML = appShell('masyarakat', 'masyarakat-profil', 'Profil',
        [{ label: 'Dashboard', page: 'masyarakat-dashboard' }, { label: 'Profil', page: 'masyarakat-profil' }],
        `
        <div class="page-header"><h1>Profil Saya</h1><p>Kelola informasi akun Anda</p></div>
        <div class="card" style="max-width:600px">
            <div style="text-align:center;margin-bottom:1.5rem">
                <div class="sidebar-avatar" style="width:64px;height:64px;font-size:1.5rem;margin:0 auto 1rem">${getInitials(state.currentUser.nama)}</div>
                <h2 style="font-size:var(--font-xl)">${escapeHtml(state.currentUser.nama)}</h2>
                <p style="color:var(--text-secondary);font-size:var(--font-sm)">${roleLabel(state.currentUser.role)}</p>
            </div>
            <form onsubmit="handleUpdateProfile(event)">
                <div class="form-group">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" class="form-control" id="profNama" value="${escapeHtml(state.currentUser.nama)}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" value="${escapeHtml(state.currentUser.email)}" disabled>
                    <div class="form-hint">Email tidak dapat diubah</div>
                </div>
                <div class="form-group">
                    <label class="form-label">NIK</label>
                    <input type="text" class="form-control" id="profNIK" value="${masy ? escapeHtml(masy.nik) : ''}" maxlength="16">
                </div>
                <div class="form-group">
                    <label class="form-label">No. HP</label>
                    <input type="text" class="form-control" id="profHP" value="${masy ? escapeHtml(masy.no_hp) : ''}">
                </div>
                <div class="form-group">
                    <label class="form-label">Alamat</label>
                    <textarea class="form-control" id="profAlamat">${masy ? escapeHtml(masy.alamat) : ''}</textarea>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
            </form>
        </div>
        `
    );
}

function handleUpdateProfile(e) {
    e.preventDefault();
    const nama = document.getElementById('profNama').value.trim();
    db.update('users', 'id_user', state.currentUser.id_user, { nama });
    state.currentUser.nama = nama;
    sessionStorage.setItem('sipades_user', JSON.stringify(state.currentUser));

    const masy = db.getMasyarakatByUserId(state.currentUser.id_user);
    if (masy) {
        db.update('masyarakat', 'id_masyarakat', masy.id_masyarakat, {
            nik: document.getElementById('profNIK').value.trim(),
            no_hp: document.getElementById('profHP').value.trim(),
            alamat: document.getElementById('profAlamat').value.trim()
        });
    }
    showToast('Profil berhasil diperbarui.', 'success');
    navigate('masyarakat-profil');
}

function renderSuratMasyarakat(app) {
    if (!requireAuth(['masyarakat'])) return;
    const id = state.pageParams?.id;
    const pengajuan = db.getById('pengajuan', 'id_pengajuan', id);
    if (!pengajuan || pengajuan.status !== 'SELESAI') return navigate('masyarakat-riwayat');

    const surat = db.getSuratByPengajuan(id);
    const ply = db.getPelayananById(pengajuan.id_pelayanan);
    const formData = JSON.parse(pengajuan.data_formulir || '{}');

    const suratHtml = generateSuratHTML(ply, formData, surat);

    app.innerHTML = appShell('masyarakat', 'masyarakat-riwayat', 'Surat',
        [{ label: 'Dashboard', page: 'masyarakat-dashboard' }, { label: 'Riwayat', page: 'masyarakat-riwayat' }, { label: 'Surat', page: 'masyarakat-surat' }],
        `
        <div class="page-header d-flex justify-between align-center flex-wrap gap-2">
            <div><h1>Surat Pelayanan</h1><p>${ply ? escapeHtml(ply.nama_pelayanan) : ''}</p></div>
            <button class="btn btn-primary no-print" onclick="window.print()"><i class="fas fa-print"></i> Cetak Surat</button>
        </div>
        ${suratHtml}
        <button class="btn btn-outline mt-2 no-print" onclick="navigate('masyarakat-riwayat')"><i class="fas fa-arrow-left"></i> Kembali</button>
        `
    );
}

function generateSuratHTML(pelayanan, formData, surat) {
    const today = new Date();
    const tgl = formatDate(today.toISOString());
    const nomor = surat ? surat.nomor_surat : '-';

    let bodyContent = '';
    if (pelayanan.id_pelayanan === 'ply_domisili') {
        bodyContent = `
            <p>Yang bertanda tangan di bawah ini, Kepala ${DESA_NAME}, ${KECAMATAN}, ${KABUPATEN}, ${PROVINSI}, menerangkan bahwa:</p>
            <table>
                <tr><td>Nama</td><td>:</td><td><strong>${escapeHtml(formData.nama_lengkap || '-')}</strong></td></tr>
                <tr><td>NIK</td><td>:</td><td>${escapeHtml(formData.nik || '-')}</td></tr>
                <tr><td>Tempat/Tgl. Lahir</td><td>:</td><td>${escapeHtml(formData.tempat_lahir || '-')}, ${formatDate(formData.tanggal_lahir)}</td></tr>
                <tr><td>Jenis Kelamin</td><td>:</td><td>${escapeHtml(formData.jenis_kelamin || '-')}</td></tr>
                <tr><td>Agama</td><td>:</td><td>${escapeHtml(formData.agama || '-')}</td></tr>
                <tr><td>Pekerjaan</td><td>:</td><td>${escapeHtml(formData.pekerjaan || '-')}</td></tr>
                <tr><td>Alamat</td><td>:</td><td>${escapeHtml(formData.alamat_lengkap || '-')}</td></tr>
            </table>
            <p>Benar bahwa orang tersebut di atas berdomisili di alamat yang disebutkan dan surat ini dibuat untuk keperluan <strong>${escapeHtml(formData.keperluan || '-')}</strong>.</p>
            <p>Demikian surat keterangan ini dibuat untuk dipergunakan sebagaimana mestinya.</p>
        `;
    } else {
        // Generic template
        const entries = Object.entries(formData);
        bodyContent = `
            <p>Yang bertanda tangan di bawah ini, Kepala ${DESA_NAME}, ${KECAMATAN}, ${KABUPATEN}, ${PROVINSI}, menerangkan bahwa:</p>
            <table>
                ${entries.map(([k, v]) => `<tr><td>${k.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}</td><td>:</td><td>${escapeHtml(v)}</td></tr>`).join('')}
            </table>
            <p>Demikian surat keterangan ini dibuat dengan sebenarnya untuk dipergunakan sebagaimana mestinya.</p>
        `;
    }

    return `
        <div class="surat-preview" id="suratContent">
            <div class="kop-surat">
                <h2>Pemerintah ${KABUPATEN}</h2>
                <h3>${DESA_NAME}</h3>
                <p>${KECAMATAN}, ${PROVINSI}</p>
                <p>Kode Pos: 28000</p>
            </div>
            <div style="text-align:center;margin-bottom:1.5rem">
                <h3 style="text-decoration:underline;text-transform:uppercase;margin-bottom:0.25rem">${pelayanan ? pelayanan.nama_pelayanan : 'Surat Keterangan'}</h3>
                <p>Nomor: ${escapeHtml(nomor)}</p>
            </div>
            <div class="surat-body">
                ${bodyContent}
            </div>
            <div class="surat-ttd">
                <div class="ttd-area">
                    <p>${DESA_NAME}, ${tgl}</p>
                    <p>Kepala Desa</p>
                    <br><br><br>
                    <p style="text-decoration:underline"><strong>H. Muhammad Rizki, S.Sos</strong></p>
                </div>
            </div>
        </div>
    `;
}

// ============================================================
//  PETUGAS PAGES
// ============================================================

function renderPetugasDashboard(app) {
    if (!requireAuth(['petugas'])) return;
    const rekap = db.getRekapPengajuan();

    app.innerHTML = appShell('petugas', 'petugas-dashboard', 'Dashboard',
        [{ label: 'Dashboard', page: 'petugas-dashboard' }],
        `
        <div class="page-header">
            <h1>Dashboard Petugas</h1>
            <p>Kelola pengajuan pelayanan administrasi masyarakat</p>
        </div>
        <div class="stats-grid">
            <div class="stat-card"><div class="stat-header"><div class="stat-icon blue"><i class="fas fa-inbox"></i></div></div><div class="stat-value">${rekap.DIAJUKAN || 0}</div><div class="stat-label">Pengajuan Baru</div></div>
            <div class="stat-card"><div class="stat-header"><div class="stat-icon amber"><i class="fas fa-search"></i></div></div><div class="stat-value">${rekap.DIPERIKSA || 0}</div><div class="stat-label">Sedang Diperiksa</div></div>
            <div class="stat-card"><div class="stat-header"><div class="stat-icon red"><i class="fas fa-rotate-left"></i></div></div><div class="stat-value">${rekap.REVISI || 0}</div><div class="stat-label">Menunggu Revisi</div></div>
            <div class="stat-card"><div class="stat-header"><div class="stat-icon cyan"><i class="fas fa-check-double"></i></div></div><div class="stat-value">${rekap.DIVERIFIKASI || 0}</div><div class="stat-label">Terverifikasi</div></div>
            <div class="stat-card"><div class="stat-header"><div class="stat-icon green"><i class="fas fa-circle-check"></i></div></div><div class="stat-value">${rekap.SELESAI || 0}</div><div class="stat-label">Selesai</div></div>
            <div class="stat-card"><div class="stat-header"><div class="stat-icon purple"><i class="fas fa-file-lines"></i></div></div><div class="stat-value">${rekap.TOTAL || 0}</div><div class="stat-label">Total Pengajuan</div></div>
        </div>
        <div class="table-container">
            <div class="table-header"><h3>Pengajuan Perlu Tindakan</h3>
                <button class="btn btn-primary btn-sm" onclick="navigate('petugas-pengajuan')"><i class="fas fa-inbox"></i> Lihat Semua</button>
            </div>
            ${renderPengajuanTable(['DIAJUKAN', 'DIPERIKSA'], 'petugas-detail', 5)}
        </div>
        `
    );
}

function renderPengajuanTable(statuses, detailPage, limit = 0) {
    let data = [];
    statuses.forEach(s => { data = data.concat(db.getPengajuanByStatus(s)); });
    data.sort((a, b) => new Date(b.tanggal_pengajuan) - new Date(a.tanggal_pengajuan));
    if (limit > 0) data = data.slice(0, limit);

    if (data.length === 0) {
        return '<div class="empty-state"><i class="fas fa-inbox"></i><h3>Tidak ada data</h3></div>';
    }

    return `<div class="overflow-auto"><table class="data-table">
        <thead><tr><th>Tanggal</th><th>Pemohon</th><th>Pelayanan</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>${data.map(p => {
            const ply = db.getPelayananById(p.id_pelayanan);
            const masy = db.getById('masyarakat', 'id_masyarakat', p.id_masyarakat);
            const user = masy ? db.getById('users', 'id_user', masy.id_user) : null;
            return `<tr>
                <td>${formatDate(p.tanggal_pengajuan)}</td>
                <td style="color:var(--text-primary)">${user ? escapeHtml(user.nama) : '-'}</td>
                <td>${ply ? escapeHtml(ply.nama_pelayanan) : '-'}</td>
                <td>${statusBadge(p.status)}</td>
                <td><button class="btn btn-outline btn-sm" onclick="navigate('${detailPage}',{id:'${p.id_pengajuan}'})"><i class="fas fa-eye"></i> Periksa</button></td>
            </tr>`;
        }).join('')}</tbody>
    </table></div>`;
}

function renderPetugasPengajuan(app) {
    if (!requireAuth(['petugas'])) return;

    app.innerHTML = appShell('petugas', 'petugas-pengajuan', 'Pengajuan Masuk',
        [{ label: 'Dashboard', page: 'petugas-dashboard' }, { label: 'Pengajuan', page: 'petugas-pengajuan' }],
        `
        <div class="page-header"><h1>Pengajuan Masuk</h1><p>Periksa dan verifikasi pengajuan masyarakat</p></div>
        <div class="filter-bar" id="filterBar">
            <select id="filterStatus" onchange="filterPetugasPengajuan()">
                <option value="">Semua Status</option>
                <option value="DIAJUKAN" selected>Diajukan</option>
                <option value="DIPERIKSA">Diperiksa</option>
                <option value="REVISI">Revisi</option>
                <option value="DIVERIFIKASI">Diverifikasi</option>
                <option value="MENUNGGU_PERSETUJUAN">Menunggu Persetujuan</option>
                <option value="DISETUJUI">Disetujui</option>
                <option value="DIPROSES">Diproses</option>
                <option value="SELESAI">Selesai</option>
            </select>
        </div>
        <div class="table-container" id="pengajuanTableContainer">
            ${renderPengajuanTable(['DIAJUKAN'], 'petugas-detail')}
        </div>
        `
    );
}

function filterPetugasPengajuan() {
    const status = document.getElementById('filterStatus').value;
    const container = document.getElementById('pengajuanTableContainer');
    const statuses = status ? [status] : ['DIAJUKAN', 'DIPERIKSA', 'REVISI', 'DIVERIFIKASI', 'MENUNGGU_PERSETUJUAN', 'DISETUJUI', 'DITOLAK', 'DIPROSES', 'SELESAI'];
    container.innerHTML = renderPengajuanTable(statuses, 'petugas-detail');
}

function renderPetugasDetailPengajuan(app) {
    if (!requireAuth(['petugas'])) return;
    const id = state.pageParams?.id;
    const pengajuan = db.getById('pengajuan', 'id_pengajuan', id);
    if (!pengajuan) return navigate('petugas-pengajuan');

    const ply = db.getPelayananById(pengajuan.id_pelayanan);
    const masy = db.getById('masyarakat', 'id_masyarakat', pengajuan.id_masyarakat);
    const user = masy ? db.getById('users', 'id_user', masy.id_user) : null;
    const docs = db.getDokumenByPengajuan(id);
    const formData = JSON.parse(pengajuan.data_formulir || '{}');
    const verifikasi = db.findWhere('verifikasi', { id_pengajuan: id });

    let formHtml = Object.entries(formData).map(([key, val]) =>
        `<div class="detail-item"><div class="detail-label">${key.replace(/_/g, ' ')}</div><div class="detail-value">${escapeHtml(val)}</div></div>`
    ).join('');

    let docsHtml = docs.length > 0
        ? docs.map(d => `<div class="uploaded-file"><i class="fas fa-file"></i><span class="file-name">${escapeHtml(d.nama_dokumen)}</span></div>`).join('')
        : '<p style="color:var(--text-muted);font-size:var(--font-sm)">Tidak ada dokumen</p>';

    // Action buttons based on status
    let actionsHtml = '';
    if (pengajuan.status === 'DIAJUKAN') {
        actionsHtml = `
            <div class="d-flex gap-1 flex-wrap mt-2">
                <button class="btn btn-primary" onclick="petugasAction('${id}','periksa')"><i class="fas fa-search"></i> Mulai Pemeriksaan</button>
            </div>
        `;
    } else if (pengajuan.status === 'DIPERIKSA') {
        actionsHtml = `
            <div class="d-flex gap-1 flex-wrap mt-2">
                <button class="btn btn-success" onclick="petugasAction('${id}','verifikasi')"><i class="fas fa-check-double"></i> Verifikasi</button>
                <button class="btn btn-warning" onclick="petugasRevisiModal('${id}')"><i class="fas fa-rotate-left"></i> Minta Revisi</button>
            </div>
        `;
    } else if (pengajuan.status === 'DISETUJUI') {
        actionsHtml = `
            <div class="d-flex gap-1 flex-wrap mt-2">
                <button class="btn btn-primary" onclick="petugasProsesSurat('${id}')"><i class="fas fa-file-signature"></i> Proses Surat</button>
            </div>
        `;
    }

    app.innerHTML = appShell('petugas', 'petugas-pengajuan', 'Detail Pengajuan',
        [{ label: 'Dashboard', page: 'petugas-dashboard' }, { label: 'Pengajuan', page: 'petugas-pengajuan' }, { label: 'Detail', page: 'petugas-detail' }],
        `
        <div class="page-header d-flex justify-between align-center flex-wrap gap-2">
            <div><h1>Detail Pengajuan</h1><p>${ply ? escapeHtml(ply.nama_pelayanan) : ''}</p></div>
            ${statusBadge(pengajuan.status)}
        </div>
        <div class="detail-grid mb-2">
            <div class="card">
                <div class="card-header"><h3>Data Pemohon</h3></div>
                <div class="detail-item"><div class="detail-label">Nama</div><div class="detail-value">${user ? escapeHtml(user.nama) : '-'}</div></div>
                <div class="detail-item"><div class="detail-label">NIK</div><div class="detail-value">${masy ? escapeHtml(masy.nik) : '-'}</div></div>
                <div class="detail-item"><div class="detail-label">No. HP</div><div class="detail-value">${masy ? escapeHtml(masy.no_hp) : '-'}</div></div>
                <div class="detail-item"><div class="detail-label">Alamat</div><div class="detail-value">${masy ? escapeHtml(masy.alamat) : '-'}</div></div>
            </div>
            <div class="card">
                <div class="card-header"><h3>Dokumen Persyaratan</h3></div>
                ${docsHtml}
            </div>
        </div>
        <div class="card mb-2">
            <div class="card-header"><h3>Data Formulir</h3></div>
            <div class="detail-grid">${formHtml}</div>
        </div>
        ${verifikasi.length > 0 ? `
        <div class="card mb-2">
            <div class="card-header"><h3>Riwayat Verifikasi</h3></div>
            ${verifikasi.map(v => `
                <div style="padding:0.75rem 0;border-bottom:1px solid var(--border-light)">
                    <p style="font-size:var(--font-sm);color:var(--text-primary)">${escapeHtml(v.hasil)} — ${escapeHtml(v.catatan)}</p>
                    <p style="font-size:var(--font-xs);color:var(--text-muted)">${formatDateTime(v.tanggal_verifikasi)}</p>
                </div>
            `).join('')}
        </div>
        ` : ''}
        ${actionsHtml}
        <button class="btn btn-outline mt-2" onclick="navigate('petugas-pengajuan')"><i class="fas fa-arrow-left"></i> Kembali</button>
        `
    );
}

function petugasAction(id, action) {
    const petugas = db.getPetugasByUserId(state.currentUser.id_user);
    const pengajuan = db.getById('pengajuan', 'id_pengajuan', id);
    const masy = db.getById('masyarakat', 'id_masyarakat', pengajuan.id_masyarakat);

    if (action === 'periksa') {
        db.updateStatusPengajuan(id, 'DIPERIKSA');
        if (masy) db.createNotifikasi(masy.id_user, 'Pengajuan Anda sedang diperiksa oleh petugas.');
        showToast('Pengajuan sedang diperiksa.', 'info');
    } else if (action === 'verifikasi') {
        db.updateStatusPengajuan(id, 'MENUNGGU_PERSETUJUAN');
        db.createVerifikasi(id, petugas?.id_petugas || '', 'TERVERIFIKASI', 'Data dan dokumen telah diperiksa dan lengkap.');
        if (masy) db.createNotifikasi(masy.id_user, 'Pengajuan Anda telah diverifikasi dan menunggu persetujuan Kepala Desa.');
        showToast('Pengajuan berhasil diverifikasi.', 'success');
    }
    navigate('petugas-detail', { id });
}

function petugasRevisiModal(id) {
    showModal('Minta Revisi',
        `<div class="form-group">
            <label class="form-label">Catatan Revisi <span class="required">*</span></label>
            <textarea class="form-control" id="revisiCatatan" placeholder="Jelaskan apa yang perlu diperbaiki..." rows="4"></textarea>
        </div>`,
        `<button class="btn btn-secondary" onclick="closeModal()">Batal</button>
         <button class="btn btn-warning" onclick="submitMintaRevisi('${id}')"><i class="fas fa-rotate-left"></i> Minta Revisi</button>`
    );
}

function submitMintaRevisi(id) {
    const catatan = document.getElementById('revisiCatatan').value.trim();
    if (!catatan) return showToast('Catatan revisi harus diisi.', 'error');
    db.updateStatusPengajuan(id, 'REVISI', catatan);
    const pengajuan = db.getById('pengajuan', 'id_pengajuan', id);
    const masy = db.getById('masyarakat', 'id_masyarakat', pengajuan.id_masyarakat);
    if (masy) db.createNotifikasi(masy.id_user, `Pengajuan Anda perlu direvisi: ${catatan}`);
    const petugas = db.getPetugasByUserId(state.currentUser.id_user);
    db.createVerifikasi(id, petugas?.id_petugas || '', 'REVISI', catatan);
    closeModal();
    showToast('Permintaan revisi berhasil dikirim.', 'warning');
    navigate('petugas-detail', { id });
}

function petugasProsesSurat(id) {
    const pengajuan = db.getById('pengajuan', 'id_pengajuan', id);
    const ply = db.getPelayananById(pengajuan.id_pelayanan);
    const kode = ply.nama_pelayanan.split(' ').map(w => w[0]).join('').toUpperCase();
    const now = new Date();
    const nomor = `${String(db.getAll('surat').length + 1).padStart(3, '0')}/${kode}/DS/${now.toLocaleString('default', { month: 'short' }).toUpperCase()}/${now.getFullYear()}`;

    db.createSurat(id, nomor);
    db.updateStatusPengajuan(id, 'SELESAI');

    const masy = db.getById('masyarakat', 'id_masyarakat', pengajuan.id_masyarakat);
    if (masy) db.createNotifikasi(masy.id_user, `Surat ${ply.nama_pelayanan} Anda telah selesai dan siap diunduh.`);

    showToast('Surat berhasil diproses!', 'success');
    navigate('petugas-detail', { id });
}

function renderPetugasRiwayat(app) {
    if (!requireAuth(['petugas'])) return;

    app.innerHTML = appShell('petugas', 'petugas-riwayat', 'Riwayat',
        [{ label: 'Dashboard', page: 'petugas-dashboard' }, { label: 'Riwayat', page: 'petugas-riwayat' }],
        `
        <div class="page-header"><h1>Riwayat Pelayanan</h1><p>Seluruh riwayat pengajuan yang telah diproses</p></div>
        <div class="table-container">
            ${renderPengajuanTable(['SELESAI', 'DITOLAK', 'DIVERIFIKASI', 'MENUNGGU_PERSETUJUAN', 'DISETUJUI', 'DIPROSES'], 'petugas-detail')}
        </div>
        `
    );
}

// ============================================================
//  KEPALA DESA PAGES
// ============================================================

function renderKepalaDashboard(app) {
    if (!requireAuth(['kepala'])) return;
    const rekap = db.getRekapPengajuan();
    const menunggu = db.getPengajuanByStatus('MENUNGGU_PERSETUJUAN');

    let tableHtml = '';
    if (menunggu.length === 0) {
        tableHtml = '<div class="empty-state"><i class="fas fa-inbox"></i><h3>Tidak ada pengajuan menunggu persetujuan</h3></div>';
    } else {
        tableHtml = `<div class="overflow-auto"><table class="data-table">
            <thead><tr><th>Tanggal</th><th>Pemohon</th><th>Pelayanan</th><th>Aksi</th></tr></thead>
            <tbody>${menunggu.map(p => {
                const ply = db.getPelayananById(p.id_pelayanan);
                const masy = db.getById('masyarakat', 'id_masyarakat', p.id_masyarakat);
                const user = masy ? db.getById('users', 'id_user', masy.id_user) : null;
                return `<tr>
                    <td>${formatDate(p.tanggal_pengajuan)}</td>
                    <td style="color:var(--text-primary)">${user ? escapeHtml(user.nama) : '-'}</td>
                    <td>${ply ? escapeHtml(ply.nama_pelayanan) : '-'}</td>
                    <td><button class="btn btn-primary btn-sm" onclick="navigate('kepala-detail',{id:'${p.id_pengajuan}'})"><i class="fas fa-stamp"></i> Tinjau</button></td>
                </tr>`;
            }).join('')}</tbody>
        </table></div>`;
    }

    app.innerHTML = appShell('kepala', 'kepala-dashboard', 'Dashboard',
        [{ label: 'Dashboard', page: 'kepala-dashboard' }],
        `
        <div class="page-header"><h1>Dashboard Kepala Desa</h1><p>Pantau dan setujui pengajuan pelayanan</p></div>
        <div class="stats-grid">
            <div class="stat-card"><div class="stat-header"><div class="stat-icon purple"><i class="fas fa-hourglass-half"></i></div></div><div class="stat-value">${rekap.MENUNGGU_PERSETUJUAN || 0}</div><div class="stat-label">Menunggu Persetujuan</div></div>
            <div class="stat-card"><div class="stat-header"><div class="stat-icon green"><i class="fas fa-check"></i></div></div><div class="stat-value">${rekap.DISETUJUI || 0}</div><div class="stat-label">Disetujui</div></div>
            <div class="stat-card"><div class="stat-header"><div class="stat-icon red"><i class="fas fa-xmark"></i></div></div><div class="stat-value">${rekap.DITOLAK || 0}</div><div class="stat-label">Ditolak</div></div>
            <div class="stat-card"><div class="stat-header"><div class="stat-icon cyan"><i class="fas fa-chart-line"></i></div></div><div class="stat-value">${rekap.TOTAL || 0}</div><div class="stat-label">Total Pengajuan</div></div>
        </div>
        <div class="table-container">
            <div class="table-header"><h3>Pengajuan Menunggu Persetujuan</h3></div>
            ${tableHtml}
        </div>
        `
    );
}

function renderKepalaPersetujuan(app) {
    if (!requireAuth(['kepala'])) return;
    const menunggu = db.getPengajuanByStatus('MENUNGGU_PERSETUJUAN');

    let tableHtml = '';
    if (menunggu.length === 0) {
        tableHtml = '<div class="empty-state"><i class="fas fa-check-circle"></i><h3>Semua pengajuan telah ditinjau</h3></div>';
    } else {
        tableHtml = `<div class="overflow-auto"><table class="data-table">
            <thead><tr><th>Tanggal</th><th>Pemohon</th><th>Pelayanan</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>${menunggu.map(p => {
                const ply = db.getPelayananById(p.id_pelayanan);
                const masy = db.getById('masyarakat', 'id_masyarakat', p.id_masyarakat);
                const user = masy ? db.getById('users', 'id_user', masy.id_user) : null;
                return `<tr>
                    <td>${formatDate(p.tanggal_pengajuan)}</td>
                    <td style="color:var(--text-primary)">${user ? escapeHtml(user.nama) : '-'}</td>
                    <td>${ply ? escapeHtml(ply.nama_pelayanan) : '-'}</td>
                    <td>${statusBadge(p.status)}</td>
                    <td><button class="btn btn-primary btn-sm" onclick="navigate('kepala-detail',{id:'${p.id_pengajuan}'})"><i class="fas fa-eye"></i> Tinjau</button></td>
                </tr>`;
            }).join('')}</tbody>
        </table></div>`;
    }

    app.innerHTML = appShell('kepala', 'kepala-persetujuan', 'Persetujuan',
        [{ label: 'Dashboard', page: 'kepala-dashboard' }, { label: 'Persetujuan', page: 'kepala-persetujuan' }],
        `<div class="page-header"><h1>Persetujuan Pengajuan</h1><p>Tinjau dan setujui pengajuan yang telah diverifikasi</p></div>
        <div class="table-container"><div class="table-header"><h3>Daftar Pengajuan</h3></div>${tableHtml}</div>`
    );
}

function renderKepalaDetail(app) {
    if (!requireAuth(['kepala'])) return;
    const id = state.pageParams?.id;
    const pengajuan = db.getById('pengajuan', 'id_pengajuan', id);
    if (!pengajuan) return navigate('kepala-persetujuan');

    const ply = db.getPelayananById(pengajuan.id_pelayanan);
    const masy = db.getById('masyarakat', 'id_masyarakat', pengajuan.id_masyarakat);
    const user = masy ? db.getById('users', 'id_user', masy.id_user) : null;
    const formData = JSON.parse(pengajuan.data_formulir || '{}');
    const docs = db.getDokumenByPengajuan(id);

    let formHtml = Object.entries(formData).map(([key, val]) =>
        `<div class="detail-item"><div class="detail-label">${key.replace(/_/g, ' ')}</div><div class="detail-value">${escapeHtml(val)}</div></div>`
    ).join('');

    let actionsHtml = '';
    if (pengajuan.status === 'MENUNGGU_PERSETUJUAN') {
        actionsHtml = `
            <div class="d-flex gap-1 flex-wrap mt-2">
                <button class="btn btn-success btn-lg" onclick="kepalaSetujui('${id}')"><i class="fas fa-check"></i> Setujui</button>
                <button class="btn btn-danger btn-lg" onclick="kepalaTolakModal('${id}')"><i class="fas fa-xmark"></i> Tolak</button>
            </div>
        `;
    }

    app.innerHTML = appShell('kepala', 'kepala-persetujuan', 'Detail',
        [{ label: 'Dashboard', page: 'kepala-dashboard' }, { label: 'Persetujuan', page: 'kepala-persetujuan' }, { label: 'Detail', page: 'kepala-detail' }],
        `
        <div class="page-header d-flex justify-between align-center flex-wrap gap-2">
            <div><h1>Tinjauan Pengajuan</h1><p>${ply ? escapeHtml(ply.nama_pelayanan) : ''}</p></div>
            ${statusBadge(pengajuan.status)}
        </div>
        <div class="detail-grid mb-2">
            <div class="card">
                <div class="card-header"><h3>Data Pemohon</h3></div>
                <div class="detail-item"><div class="detail-label">Nama</div><div class="detail-value">${user ? escapeHtml(user.nama) : '-'}</div></div>
                <div class="detail-item"><div class="detail-label">NIK</div><div class="detail-value">${masy ? escapeHtml(masy.nik) : '-'}</div></div>
                <div class="detail-item"><div class="detail-label">Alamat</div><div class="detail-value">${masy ? escapeHtml(masy.alamat) : '-'}</div></div>
            </div>
            <div class="card">
                <div class="card-header"><h3>Dokumen</h3></div>
                ${docs.map(d => `<div class="uploaded-file"><i class="fas fa-file"></i><span class="file-name">${escapeHtml(d.nama_dokumen)}</span></div>`).join('') || '<p style="color:var(--text-muted)">Tidak ada</p>'}
            </div>
        </div>
        <div class="card mb-2">
            <div class="card-header"><h3>Data Formulir</h3></div>
            <div class="detail-grid">${formHtml}</div>
        </div>
        ${actionsHtml}
        <button class="btn btn-outline mt-2" onclick="navigate('kepala-persetujuan')"><i class="fas fa-arrow-left"></i> Kembali</button>
        `
    );
}

function kepalaSetujui(id) {
    db.updateStatusPengajuan(id, 'DISETUJUI');
    const pengajuan = db.getById('pengajuan', 'id_pengajuan', id);
    const masy = db.getById('masyarakat', 'id_masyarakat', pengajuan.id_masyarakat);
    if (masy) db.createNotifikasi(masy.id_user, 'Pengajuan Anda telah DISETUJUI oleh Kepala Desa.');
    showToast('Pengajuan berhasil disetujui!', 'success');
    navigate('kepala-persetujuan');
}

function kepalaTolakModal(id) {
    showModal('Tolak Pengajuan',
        `<div class="form-group">
            <label class="form-label">Alasan Penolakan <span class="required">*</span></label>
            <textarea class="form-control" id="alasanTolak" placeholder="Jelaskan alasan penolakan..." rows="4"></textarea>
        </div>`,
        `<button class="btn btn-secondary" onclick="closeModal()">Batal</button>
         <button class="btn btn-danger" onclick="kepalaTolak('${id}')"><i class="fas fa-xmark"></i> Tolak</button>`
    );
}

function kepalaTolak(id) {
    const alasan = document.getElementById('alasanTolak').value.trim();
    if (!alasan) return showToast('Alasan penolakan harus diisi.', 'error');
    db.updateStatusPengajuan(id, 'DITOLAK', alasan);
    const pengajuan = db.getById('pengajuan', 'id_pengajuan', id);
    const masy = db.getById('masyarakat', 'id_masyarakat', pengajuan.id_masyarakat);
    if (masy) db.createNotifikasi(masy.id_user, `Pengajuan Anda DITOLAK. Alasan: ${alasan}`);
    closeModal();
    showToast('Pengajuan ditolak.', 'warning');
    navigate('kepala-persetujuan');
}

function renderKepalaLaporan(app) {
    if (!requireAuth(['kepala'])) return;
    renderLaporanPage(app, 'kepala', 'kepala-laporan');
}

// ============================================================
//  ADMIN PAGES
// ============================================================

function renderAdminDashboard(app) {
    if (!requireAuth(['admin'])) return;
    const totalUsers = db.getAll('users').length;
    const totalMasy = db.getAll('masyarakat').length;
    const totalPetugas = db.getAll('petugas').length;
    const totalPelayanan = db.getAll('jenis_pelayanan').length;
    const rekap = db.getRekapPengajuan();
    const stats = db.getStatistik();
    const trend = stats.totalBulanIni - stats.totalBulanLalu;
    const trendIcon = trend >= 0 ? 'fa-arrow-up' : 'fa-arrow-down';
    const trendColor = trend >= 0 ? '#16a34a' : '#dc2626';
    const trendText = trend >= 0 ? `+${trend}` : `${trend}`;

    app.innerHTML = appShell('admin', 'admin-dashboard', 'Dashboard',
        [{ label: 'Dashboard', page: 'admin-dashboard' }],
        `
        <div class="page-header"><h1>Dashboard Admin</h1><p>Kelola sistem pelayanan administrasi desa</p></div>
        <div class="stats-grid">
            <div class="stat-card"><div class="stat-header"><div class="stat-icon blue"><i class="fas fa-users"></i></div></div><div class="stat-value">${totalUsers}</div><div class="stat-label">Total Pengguna</div></div>
            <div class="stat-card"><div class="stat-header"><div class="stat-icon green"><i class="fas fa-people-group"></i></div></div><div class="stat-value">${totalMasy}</div><div class="stat-label">Masyarakat</div></div>
            <div class="stat-card"><div class="stat-header"><div class="stat-icon cyan"><i class="fas fa-user-tie"></i></div></div><div class="stat-value">${totalPetugas}</div><div class="stat-label">Petugas</div></div>
            <div class="stat-card"><div class="stat-header"><div class="stat-icon purple"><i class="fas fa-clipboard-list"></i></div></div><div class="stat-value">${totalPelayanan}</div><div class="stat-label">Jenis Pelayanan</div></div>
            <div class="stat-card"><div class="stat-header"><div class="stat-icon amber"><i class="fas fa-file-lines"></i></div></div><div class="stat-value">${rekap.TOTAL || 0}</div><div class="stat-label">Total Pengajuan</div></div>
            <div class="stat-card"><div class="stat-header"><div class="stat-icon green"><i class="fas fa-circle-check"></i></div></div><div class="stat-value">${rekap.SELESAI || 0}</div><div class="stat-label">Selesai</div></div>
        </div>
        <div class="detail-grid mt-2">
            <div class="card">
                <div class="card-header"><h3><i class="fas fa-chart-line" style="margin-right:0.5rem;color:var(--accent-primary)"></i>Statistik Bulan Ini</h3></div>
                <div class="detail-item">
                    <div class="detail-label">Pengajuan Bulan Ini</div>
                    <div class="detail-value">
                        <strong>${stats.totalBulanIni}</strong>
                        <span style="font-size:var(--font-xs);margin-left:0.5rem;color:${trendColor}"><i class="fas ${trendIcon}"></i> ${trendText} dari bulan lalu</span>
                    </div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Selesai Bulan Ini</div>
                    <div class="detail-value"><strong>${stats.selesaiBulanIni}</strong></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Rata-rata Per Hari</div>
                    <div class="detail-value"><strong>${stats.rataRataPerHari}</strong> pengajuan</div>
                </div>
            </div>
            <div class="card">
                <div class="card-header"><h3><i class="fas fa-bolt" style="margin-right:0.5rem;color:var(--header-gold)"></i>Aksi Cepat</h3></div>
                <div style="display:flex;flex-direction:column;gap:0.75rem">
                    <button class="btn btn-primary btn-block" onclick="navigate('admin-pengguna')"><i class="fas fa-user-plus"></i> Kelola Pengguna</button>
                    <button class="btn btn-outline btn-block" onclick="navigate('admin-pelayanan')"><i class="fas fa-clipboard-list"></i> Kelola Pelayanan</button>
                    <button class="btn btn-outline btn-block" onclick="navigate('admin-laporan')"><i class="fas fa-chart-bar"></i> Lihat Laporan</button>
                    <button class="btn btn-outline btn-block" onclick="navigate('admin-pengaturan')"><i class="fas fa-gear"></i> Pengaturan Sistem</button>
                </div>
            </div>
        </div>
        `
    );
}

function renderAdminPengguna(app) {
    if (!requireAuth(['admin'])) return;
    const users = db.getAll('users');

    let tableHtml = `<div class="overflow-auto"><table class="data-table">
        <thead><tr><th>Nama</th><th>Email</th><th>Role</th><th>Terdaftar</th><th>Aksi</th></tr></thead>
        <tbody>${users.map(u => `<tr class="user-row" data-name="${escapeHtml(u.nama).toLowerCase()}" data-email="${escapeHtml(u.email).toLowerCase()}" data-role="${u.role}">
            <td style="color:var(--text-primary)">${escapeHtml(u.nama)}</td>
            <td>${escapeHtml(u.email)}</td>
            <td><span class="badge badge-diverifikasi">${roleLabel(u.role)}</span></td>
            <td>${formatDate(u.created_at)}</td>
            <td>
                ${u.role !== 'admin' ? `<button class="btn btn-danger btn-sm" onclick="adminDeleteUser('${u.id_user}','${escapeHtml(u.nama)}')"><i class="fas fa-trash"></i></button>` : ''}
            </td>
        </tr>`).join('')}</tbody>
    </table></div>`;

    app.innerHTML = appShell('admin', 'admin-pengguna', 'Pengguna',
        [{ label: 'Dashboard', page: 'admin-dashboard' }, { label: 'Pengguna', page: 'admin-pengguna' }],
        `
        <div class="page-header d-flex justify-between align-center flex-wrap gap-2">
            <div><h1>Manajemen Pengguna</h1><p>Kelola pengguna sistem</p></div>
            <button class="btn btn-primary" onclick="showAddPetugasModal()"><i class="fas fa-user-plus"></i> Tambah Petugas</button>
        </div>
        <div class="search-bar">
            <div class="search-input-wrapper">
                <i class="fas fa-search"></i>
                <input type="text" class="form-control" id="searchUser" placeholder="Cari nama atau email..." oninput="filterUserTable(this.value)">
            </div>
            <select class="form-control" style="max-width:180px" id="filterRole" onchange="filterUserTable(document.getElementById('searchUser').value)">
                <option value="">Semua Role</option>
                <option value="masyarakat">Masyarakat</option>
                <option value="petugas">Petugas</option>
                <option value="kepala">Kepala Desa</option>
                <option value="admin">Admin</option>
            </select>
        </div>
        <div class="table-container"><div class="table-header"><h3>Daftar Pengguna (${users.length})</h3></div>${tableHtml}</div>
        `
    );
}

function filterUserTable(query) {
    const search = (query || '').toLowerCase();
    const roleFilter = document.getElementById('filterRole')?.value || '';
    const rows = document.querySelectorAll('.user-row');
    let visible = 0;
    rows.forEach(row => {
        const name = row.dataset.name;
        const email = row.dataset.email;
        const role = row.dataset.role;
        const matchSearch = !search || name.includes(search) || email.includes(search);
        const matchRole = !roleFilter || role === roleFilter;
        const show = matchSearch && matchRole;
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });
}

function showAddPetugasModal() {
    showModal('Tambah Petugas',
        `<form id="addPetugasForm">
            <div class="form-group"><label class="form-label">Nama Lengkap <span class="required">*</span></label><input type="text" class="form-control" id="ptgNama" required></div>
            <div class="form-group"><label class="form-label">Email <span class="required">*</span></label><input type="email" class="form-control" id="ptgEmail" required></div>
            <div class="form-group"><label class="form-label">Jabatan</label><input type="text" class="form-control" id="ptgJabatan" value="Staf Pelayanan"></div>
            <div class="form-group"><label class="form-label">Password <span class="required">*</span></label><input type="password" class="form-control" id="ptgPassword" value="petugas123" required></div>
        </form>`,
        `<button class="btn btn-secondary" onclick="closeModal()">Batal</button>
         <button class="btn btn-primary" onclick="addPetugas()"><i class="fas fa-plus"></i> Tambah</button>`
    );
}

function addPetugas() {
    const nama = document.getElementById('ptgNama').value.trim();
    const email = document.getElementById('ptgEmail').value.trim();
    const jabatan = document.getElementById('ptgJabatan').value.trim();
    const pwd = document.getElementById('ptgPassword').value;
    if (!nama || !email || !pwd) return showToast('Lengkapi semua data.', 'error');
    if (db.findUserByEmail(email)) return showToast('Email sudah terdaftar.', 'error');

    const user = db.createUser(nama, email, pwd, 'petugas');
    db.insert('petugas', { id_petugas: db.generateId(), id_user: user.id_user, jabatan });
    closeModal();
    showToast('Petugas berhasil ditambahkan.', 'success');
    navigate('admin-pengguna');
}

function adminDeleteUser(id, nama) {
    showModal('Hapus Pengguna',
        `<p>Apakah Anda yakin ingin menghapus pengguna <strong>${nama}</strong>?</p>`,
        `<button class="btn btn-secondary" onclick="closeModal()">Batal</button>
         <button class="btn btn-danger" onclick="confirmDeleteUser('${id}')"><i class="fas fa-trash"></i> Hapus</button>`
    );
}

function confirmDeleteUser(id) {
    db.remove('users', 'id_user', id);
    db.remove('masyarakat', 'id_user', id);
    db.remove('petugas', 'id_user', id);
    closeModal();
    showToast('Pengguna berhasil dihapus.', 'success');
    navigate('admin-pengguna');
}

function renderAdminPelayanan(app) {
    if (!requireAuth(['admin'])) return;
    const pelayanan = db.getAll('jenis_pelayanan');
    const rekapPly = db.getRekapPerPelayanan();

    let tableHtml = `<div class="overflow-auto"><table class="data-table">
        <thead><tr><th>Pelayanan</th><th>Total Pengajuan</th><th>Selesai</th><th>Aksi</th></tr></thead>
        <tbody>${rekapPly.map(p => `<tr>
            <td style="color:var(--text-primary)"><i class="fas ${p.icon}" style="margin-right:0.5rem;color:var(--accent-primary)"></i>${escapeHtml(p.nama_pelayanan)}</td>
            <td>${p.total}</td>
            <td>${p.selesai}</td>
            <td><button class="btn btn-outline btn-sm" onclick="showEditPelayananModal('${p.id_pelayanan}')"><i class="fas fa-edit"></i></button></td>
        </tr>`).join('')}</tbody>
    </table></div>`;

    app.innerHTML = appShell('admin', 'admin-pelayanan', 'Pelayanan',
        [{ label: 'Dashboard', page: 'admin-dashboard' }, { label: 'Pelayanan', page: 'admin-pelayanan' }],
        `<div class="page-header"><h1>Manajemen Pelayanan</h1><p>Kelola jenis pelayanan administrasi</p></div>
        <div class="table-container"><div class="table-header"><h3>Jenis Pelayanan (${pelayanan.length})</h3></div>${tableHtml}</div>`
    );
}

function showEditPelayananModal(id) {
    const p = db.getPelayananById(id);
    if (!p) return;
    showModal('Edit Pelayanan',
        `<div class="form-group"><label class="form-label">Nama Pelayanan</label><input type="text" class="form-control" id="editPlyNama" value="${escapeHtml(p.nama_pelayanan)}"></div>
         <div class="form-group"><label class="form-label">Deskripsi</label><textarea class="form-control" id="editPlyDesc">${escapeHtml(p.deskripsi)}</textarea></div>
         <div class="form-group"><label class="form-label">Persyaratan (pisahkan dengan koma)</label><textarea class="form-control" id="editPlyReq">${escapeHtml(p.persyaratan)}</textarea></div>`,
        `<button class="btn btn-secondary" onclick="closeModal()">Batal</button>
         <button class="btn btn-primary" onclick="savePelayanan('${id}')"><i class="fas fa-save"></i> Simpan</button>`
    );
}

function savePelayanan(id) {
    db.update('jenis_pelayanan', 'id_pelayanan', id, {
        nama_pelayanan: document.getElementById('editPlyNama').value.trim(),
        deskripsi: document.getElementById('editPlyDesc').value.trim(),
        persyaratan: document.getElementById('editPlyReq').value.trim()
    });
    closeModal();
    showToast('Pelayanan berhasil diperbarui.', 'success');
    navigate('admin-pelayanan');
}

function renderAdminLaporan(app) {
    if (!requireAuth(['admin'])) return;
    renderLaporanPage(app, 'admin', 'admin-laporan');
}

// ============================================================
//  SHARED: LAPORAN PAGE
// ============================================================
function renderLaporanPage(app, role, activePage) {
    const rekap = db.getRekapPengajuan();
    const rekapPly = db.getRekapPerPelayanan();
    const statuses = ['DIAJUKAN', 'DIPERIKSA', 'REVISI', 'DIVERIFIKASI', 'MENUNGGU_PERSETUJUAN', 'DISETUJUI', 'DITOLAK', 'DIPROSES', 'SELESAI'];

    // Status rekap cards
    let statusCards = statuses.map(s => {
        const count = rekap[s] || 0;
        return `<div style="display:flex;justify-content:space-between;align-items:center;padding:0.75rem 0;border-bottom:1px solid var(--border-light)">
            <span style="font-size:var(--font-sm)">${statusBadge(s)}</span>
            <strong style="font-size:var(--font-lg);color:var(--text-heading)">${count}</strong>
        </div>`;
    }).join('');

    // Per-pelayanan chart (simple bar)
    const maxTotal = Math.max(...rekapPly.map(p => p.total), 1);
    let barChart = rekapPly.map(p => `
        <div style="margin-bottom:1rem">
            <div style="display:flex;justify-content:space-between;font-size:var(--font-sm);margin-bottom:0.25rem">
                <span style="color:var(--text-primary)">${escapeHtml(p.nama_pelayanan)}</span>
                <span style="color:var(--text-muted)">${p.total} pengajuan</span>
            </div>
            <div style="background:var(--bg-input);border-radius:var(--radius-full);height:8px;overflow:hidden">
                <div style="height:100%;width:${(p.total / maxTotal * 100)}%;background:var(--accent-gradient);border-radius:var(--radius-full);transition:width 0.5s"></div>
            </div>
        </div>
    `).join('');

    const dashboardPage = getDashboardRoute(role);

    app.innerHTML = appShell(role, activePage, 'Laporan',
        [{ label: 'Dashboard', page: dashboardPage }, { label: 'Laporan', page: activePage }],
        `
        <div class="page-header d-flex justify-between align-center flex-wrap gap-2">
            <div><h1>Laporan Pelayanan</h1><p>Rekap dan statistik pelayanan administrasi</p></div>
            <button class="btn btn-primary no-print" onclick="window.print()"><i class="fas fa-print"></i> Cetak</button>
        </div>
        <div class="stats-grid mb-2">
            <div class="stat-card"><div class="stat-header"><div class="stat-icon blue"><i class="fas fa-file-lines"></i></div></div><div class="stat-value">${rekap.TOTAL}</div><div class="stat-label">Total Pengajuan</div></div>
            <div class="stat-card"><div class="stat-header"><div class="stat-icon green"><i class="fas fa-circle-check"></i></div></div><div class="stat-value">${rekap.SELESAI || 0}</div><div class="stat-label">Selesai</div></div>
            <div class="stat-card"><div class="stat-header"><div class="stat-icon amber"><i class="fas fa-spinner"></i></div></div><div class="stat-value">${(rekap.TOTAL || 0) - (rekap.SELESAI || 0) - (rekap.DITOLAK || 0)}</div><div class="stat-label">Dalam Proses</div></div>
            <div class="stat-card"><div class="stat-header"><div class="stat-icon red"><i class="fas fa-xmark"></i></div></div><div class="stat-value">${rekap.DITOLAK || 0}</div><div class="stat-label">Ditolak</div></div>
        </div>
        <div class="detail-grid">
            <div class="card">
                <div class="card-header"><h3>Rekap Per Status</h3></div>
                ${statusCards}
                <div style="display:flex;justify-content:space-between;align-items:center;padding:0.75rem 0;font-weight:700">
                    <span style="font-size:var(--font-sm);color:var(--text-heading)">TOTAL</span>
                    <strong style="font-size:var(--font-xl);color:var(--accent-primary)">${rekap.TOTAL}</strong>
                </div>
            </div>
            <div class="card">
                <div class="card-header"><h3>Rekap Per Pelayanan</h3></div>
                ${barChart}
            </div>
        </div>
        `
    );
}

// ============================================================
//  ADMIN: PENGATURAN SISTEM
// ============================================================
function renderAdminPengaturan(app) {
    if (!requireAuth(['admin'])) return;
    const totalData = db.tables.reduce((sum, t) => sum + db.getAll(t).length, 0);
    const storageUsed = Object.keys(localStorage).filter(k => k.startsWith('sipades_')).reduce((sum, k) => sum + localStorage.getItem(k).length, 0);
    const storageKB = (storageUsed / 1024).toFixed(1);

    app.innerHTML = appShell('admin', 'admin-pengaturan', 'Pengaturan',
        [{ label: 'Dashboard', page: 'admin-dashboard' }, { label: 'Pengaturan', page: 'admin-pengaturan' }],
        `
        <div class="page-header"><h1>Pengaturan Sistem</h1><p>Konfigurasi dan pengelolaan data sistem</p></div>
        <div class="detail-grid">
            <div class="card">
                <div class="card-header"><h3><i class="fas fa-database" style="margin-right:0.5rem;color:var(--accent-primary)"></i>Informasi Database</h3></div>
                <div class="detail-item"><div class="detail-label">Total Record</div><div class="detail-value"><strong>${totalData}</strong> data</div></div>
                <div class="detail-item"><div class="detail-label">Penggunaan Storage</div><div class="detail-value"><strong>${storageKB} KB</strong></div></div>
                <div class="detail-item"><div class="detail-label">Jumlah Tabel</div><div class="detail-value"><strong>${db.tables.length}</strong> tabel</div></div>
                <div class="detail-item"><div class="detail-label">Engine</div><div class="detail-value">localStorage (Client-Side)</div></div>
            </div>
            <div class="card">
                <div class="card-header"><h3><i class="fas fa-info-circle" style="margin-right:0.5rem;color:var(--accent-secondary)"></i>Informasi Sistem</h3></div>
                <div class="detail-item"><div class="detail-label">Nama Sistem</div><div class="detail-value">SIPADES v1.0</div></div>
                <div class="detail-item"><div class="detail-label">Desa</div><div class="detail-value">${DESA_NAME}</div></div>
                <div class="detail-item"><div class="detail-label">Kecamatan</div><div class="detail-value">${KECAMATAN}</div></div>
                <div class="detail-item"><div class="detail-label">Kabupaten</div><div class="detail-value">${KABUPATEN}</div></div>
                <div class="detail-item"><div class="detail-label">Provinsi</div><div class="detail-value">${PROVINSI}</div></div>
            </div>
        </div>
        <div class="card mt-2">
            <div class="card-header"><h3><i class="fas fa-tools" style="margin-right:0.5rem;color:var(--header-gold)"></i>Aksi Database</h3></div>
            <div class="d-flex gap-1 flex-wrap" style="margin-top:1rem">
                <button class="btn btn-primary" onclick="exportDatabase()"><i class="fas fa-download"></i> Ekspor Data (JSON)</button>
                <button class="btn btn-outline" onclick="document.getElementById('importFile').click()"><i class="fas fa-upload"></i> Impor Data</button>
                <input type="file" id="importFile" accept=".json" style="display:none" onchange="importDatabase(this)">
                <button class="btn btn-warning" onclick="confirmResetDb()"><i class="fas fa-rotate-left"></i> Reset ke Data Awal</button>
            </div>
            <p style="margin-top:0.75rem;font-size:var(--font-xs);color:var(--text-muted)"><i class="fas fa-circle-info"></i> Reset akan menghapus semua data dan mengembalikan ke data demo awal.</p>
        </div>
        `
    );
}

function exportDatabase() {
    const data = db.exportAllData();
    const blob = new Blob([data], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `SIPADES_Backup_${new Date().toISOString().split('T')[0]}.json`;
    a.click();
    URL.revokeObjectURL(url);
    showToast('Data berhasil diekspor.', 'success');
}

function importDatabase(input) {
    const file = input.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = (e) => {
        if (db.importData(e.target.result)) {
            showToast('Data berhasil diimpor. Halaman akan dimuat ulang.', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast('Gagal mengimpor data. Format file tidak valid.', 'error');
        }
    };
    reader.readAsText(file);
    input.value = '';
}

function confirmResetDb() {
    showModal('Konfirmasi Reset Database',
        `<div style="text-align:center;padding:1rem">
            <i class="fas fa-triangle-exclamation" style="font-size:3rem;color:#d97706;margin-bottom:1rem"></i>
            <h3 style="margin-bottom:0.5rem">Anda yakin ingin mereset database?</h3>
            <p style="color:var(--text-secondary);font-size:var(--font-sm)">Semua data pengajuan, pengguna yang ditambahkan, dan notifikasi akan dihapus dan dikembalikan ke data demo awal. Tindakan ini <strong>tidak dapat dibatalkan</strong>.</p>
        </div>`,
        `<button class="btn btn-secondary" onclick="closeModal()">Batal</button>
         <button class="btn btn-danger" onclick="executeResetDb()"><i class="fas fa-trash"></i> Ya, Reset Sekarang</button>`
    );
}

function executeResetDb() {
    db.reset();
    closeModal();
    sessionStorage.removeItem('sipades_user');
    showToast('Database berhasil direset ke data awal.', 'success');
    setTimeout(() => location.reload(), 1000);
}

// ============================================================
//  KEYBOARD SHORTCUTS
// ============================================================
document.addEventListener('keydown', (e) => {
    // Escape key to close modals
    if (e.key === 'Escape') closeModal();
});

// ============================================================
//  INITIALIZATION
// ============================================================
(function init() {
    checkSession();
    if (state.currentUser) {
        navigate(getDashboardRoute(state.currentUser.role));
    } else {
        navigate('landing');
    }
})();
