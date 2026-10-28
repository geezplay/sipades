<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — SIPADES (Sistem Pelayanan Administrasi Desa)</title>

    <!-- Google Fonts & Font Awesome -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- SIPADES Government Navy Stylesheet -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    <style>
        /* Government Navy Theme Overrides & Component Enhancements */
        :root {
            --header-navy: #0f2b48;
            --accent-primary: #1e6091;
            --accent-primary-hover: #154d76;
            --bg-primary: #f4f6f9;
        }

        body {
            background-color: var(--bg-primary);
            font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            color: var(--text-primary);
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(2px);
            z-index: 998;
        }
        .sidebar-overlay.active {
            display: block;
        }

        .alert-box {
            padding: 1rem 1.25rem;
            border-radius: var(--radius-md);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: 0.875rem;
            font-size: var(--font-sm);
            line-height: 1.5;
            position: relative;
            box-shadow: var(--shadow-sm);
        }
        .alert-box .alert-icon {
            font-size: 1.15rem;
            margin-top: 0.125rem;
            flex-shrink: 0;
        }
        .alert-box .alert-content {
            flex: 1;
        }
        .alert-box .alert-close {
            background: transparent;
            border: none;
            color: inherit;
            opacity: 0.6;
            cursor: pointer;
            font-size: 0.9rem;
            padding: 0 0.25rem;
        }
        .alert-box .alert-close:hover {
            opacity: 1;
        }

        .alert-success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
        }
        .alert-success .alert-icon { color: #16a34a; }

        .alert-error, .alert-danger {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }
        .alert-error .alert-icon, .alert-danger .alert-icon { color: #dc2626; }

        .alert-warning {
            background: #fffbe6;
            border: 1px solid #fef08a;
            color: #854d0e;
        }
        .alert-warning .alert-icon { color: #d97706; }

        .alert-info {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e40af;
        }
        .alert-info .alert-icon { color: #0284c7; }

        /* Role badge on topbar */
        .role-pill {
            display: inline-block;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 0.2rem 0.55rem;
            border-radius: var(--radius-full);
            background: #e0f2fe;
            color: #0369a1;
            margin-left: 0.5rem;
        }
        .role-pill.role-admin { background: #fee2e2; color: #b91c1c; }
        .role-pill.role-petugas { background: #fef3c7; color: #92400e; }
        .role-pill.role-kepala { background: #f3e8ff; color: #6b21a8; }
        .role-pill.role-masyarakat { background: #e0f2fe; color: #0369a1; }

        .nav-item.active {
            background: rgba(30, 96, 145, 0.12);
            color: var(--accent-primary);
            font-weight: 700;
            border-left: 3px solid var(--accent-primary);
        }

        .user-menu-btn {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            background: #ffffff;
            border: 1px solid var(--border-color);
            padding: 0.4rem 0.85rem;
            border-radius: var(--radius-full);
            cursor: pointer;
            font-weight: 600;
            font-size: var(--font-sm);
            color: var(--text-primary);
            transition: all var(--transition-fast);
        }
        .user-menu-btn:hover {
            background: #f8fafc;
            border-color: var(--accent-primary);
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            min-width: 220px;
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            padding: 0.5rem;
            z-index: 1000;
        }
        .dropdown-menu.show {
            display: block;
        }
        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            width: 100%;
            padding: 0.65rem 0.85rem;
            font-size: var(--font-sm);
            color: var(--text-secondary);
            border-radius: var(--radius-sm);
            text-decoration: none;
            background: transparent;
            border: none;
            cursor: pointer;
            text-align: left;
            transition: background var(--transition-fast);
        }
        .dropdown-item:hover {
            background: #f1f5f9;
            color: var(--header-navy);
        }
        .dropdown-item.text-danger:hover {
            background: #fef2f2;
            color: #dc2626;
        }
        .dropdown-divider {
            height: 1px;
            background: var(--border-color);
            margin: 0.35rem 0;
        }

        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                z-index: 999;
            }
            .sidebar.open {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0 !important;
                width: 100% !important;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Mobile Sidebar Backdrop Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    <!-- Sidebar Navigation -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header" style="background: linear-gradient(135deg, #0a192f 0%, #0f2b48 100%); color: #ffffff;">
            <div class="sidebar-logo" style="background: #1e6091; color: #ffffff; display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 8px;">
                <i class="fas fa-landmark" style="font-size: 1.2rem;"></i>
            </div>
            <div class="sidebar-brand" style="margin-left: 0.75rem;">
                <h3 style="margin: 0; font-size: 1.15rem; font-weight: 800; color: #ffffff; letter-spacing: 0.5px;">SIPADES</h3>
                <span style="font-size: 0.75rem; color: #94a3b8; font-weight: 500;">Pelayanan Administrasi Desa</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            @php
                $role = Auth::user()->role ?? 'masyarakat';
            @endphp

            @if($role === 'masyarakat')
                {{-- Menu Masyarakat / Warga --}}
                <div class="nav-section-title">Menu Warga</div>
                <a href="{{ Route::has('warga.dashboard') ? route('warga.dashboard') : url('/warga/dashboard') }}" 
                   class="nav-item {{ request()->routeIs('warga.dashboard') || request()->is('warga/dashboard') || request()->is('dashboard') ? 'active' : '' }}">
                    <i class="fas fa-house"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ Route::has('pengajuan.create') ? route('pengajuan.create') : (Route::has('warga.pengajuan.create') ? route('warga.pengajuan.create') : url('/warga/pengajuan/create')) }}" 
                   class="nav-item {{ request()->routeIs('*pengajuan.create') || request()->is('*/pengajuan/create') ? 'active' : '' }}">
                    <i class="fas fa-file-circle-plus"></i>
                    <span>Buat Pengajuan</span>
                </a>
                <a href="{{ Route::has('pengajuan.index') ? route('pengajuan.index') : (Route::has('warga.pengajuan.index') ? route('warga.pengajuan.index') : url('/warga/pengajuan')) }}" 
                   class="nav-item {{ request()->routeIs('*pengajuan.index') || request()->routeIs('*pengajuan.show') || (request()->is('*pengajuan*') && !request()->is('*/pengajuan/create')) ? 'active' : '' }}">
                    <i class="fas fa-clock-rotate-left"></i>
                    <span>Status & Riwayat</span>
                </a>

            @elseif($role === 'petugas')
                {{-- Menu Petugas Pelayanan --}}
                <div class="nav-section-title">Menu Petugas</div>
                <a href="{{ Route::has('petugas.dashboard') ? route('petugas.dashboard') : url('/petugas/dashboard') }}" 
                   class="nav-item {{ request()->routeIs('petugas.dashboard') || request()->is('petugas/dashboard') ? 'active' : '' }}">
                    <i class="fas fa-house"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ Route::has('petugas.pengajuan.index') ? route('petugas.pengajuan.index') : url('/petugas/pengajuan') }}" 
                   class="nav-item {{ request()->routeIs('petugas.pengajuan.*') || request()->is('petugas/pengajuan*') ? 'active' : '' }}">
                    <i class="fas fa-inbox"></i>
                    <span>Antrean Berkas</span>
                </a>

            @elseif($role === 'kepala')
                {{-- Menu Kepala Desa / Lurah --}}
                <div class="nav-section-title">Menu Kepala Desa</div>
                <a href="{{ Route::has('kepala.dashboard') ? route('kepala.dashboard') : url('/kepala/dashboard') }}" 
                   class="nav-item {{ request()->routeIs('kepala.dashboard') || request()->is('kepala/dashboard') ? 'active' : '' }}">
                    <i class="fas fa-house"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ Route::has('kepala.pengajuan.index') ? route('kepala.pengajuan.index') : url('/kepala/pengajuan') }}" 
                   class="nav-item {{ request()->routeIs('kepala.pengajuan.*') || request()->is('kepala/pengajuan*') ? 'active' : '' }}">
                    <i class="fas fa-stamp"></i>
                    <span>Persetujuan Surat</span>
                </a>

            @elseif($role === 'admin')
                {{-- Menu Administrator --}}
                <div class="nav-section-title">Menu Administrator</div>
                <a href="{{ Route::has('admin.dashboard') ? route('admin.dashboard') : url('/admin/dashboard') }}" 
                   class="nav-item {{ request()->routeIs('admin.dashboard') || request()->is('admin/dashboard') ? 'active' : '' }}">
                    <i class="fas fa-house"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ Route::has('admin.users') ? route('admin.users') : (Route::has('admin.users.index') ? route('admin.users.index') : url('/admin/users')) }}" 
                   class="nav-item {{ request()->routeIs('admin.users*') || request()->is('admin/users*') ? 'active' : '' }}">
                    <i class="fas fa-users-gear"></i>
                    <span>Kelola Pengguna</span>
                </a>
                <a href="{{ Route::has('admin.services') ? route('admin.services') : (Route::has('admin.services.index') ? route('admin.services.index') : url('/admin/services')) }}" 
                   class="nav-item {{ request()->routeIs('admin.services*') || request()->is('admin/services*') ? 'active' : '' }}">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Master Pelayanan</span>
                </a>
            @endif

            <div class="nav-section-title">Lainnya</div>
            <a href="{{ Route::has('notifikasi.index') ? route('notifikasi.index') : url('/notifikasi') }}" 
               class="nav-item {{ request()->routeIs('notifikasi.*') || request()->is('notifikasi*') ? 'active' : '' }}">
                <i class="fas fa-bell"></i>
                <span>Notifikasi</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="sidebar-avatar" style="background: #0f2b48; color: #ffffff; font-weight: 700;">
                    @php
                        $nameParts = explode(' ', Auth::user()->nama ?? 'User');
                        $initials = '';
                        foreach (array_slice($nameParts, 0, 2) as $p) {
                            $initials .= strtoupper(substr($p, 0, 1));
                        }
                    @endphp
                    {{ $initials ?: 'U' }}
                </div>
                <div class="sidebar-user-info">
                    <div class="sidebar-user-name" title="{{ Auth::user()->nama ?? 'User' }}">
                        {{ Auth::user()->nama ?? 'Pengguna' }}
                    </div>
                    <div class="sidebar-user-role">
                        {{ ucfirst(Auth::user()->role ?? 'masyarakat') }}
                    </div>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="main-content">
        <!-- Top Bar -->
        <header class="top-bar" style="background: #ffffff; border-bottom: 1px solid var(--border-color); padding: 0.85rem 1.5rem; display: flex; align-items: center; justify-content: space-between;">
            <div class="top-bar-left" style="display: flex; align-items: center; gap: 1rem;">
                <button class="menu-toggle btn btn-outline btn-sm" onclick="toggleSidebar()" aria-label="Toggle menu" style="padding: 0.4rem 0.65rem;">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="breadcrumb" style="display: flex; align-items: center; gap: 0.5rem; font-size: var(--font-sm); color: var(--text-muted);">
                    <a href="{{ url('/') }}" style="color: var(--text-muted); text-decoration: none;"><i class="fas fa-landmark"></i> SIPADES</a>
                    <span class="separator">/</span>
                    <span class="current" style="color: var(--header-navy); font-weight: 600;">@yield('breadcrumb', 'Dashboard')</span>
                </div>
            </div>

            <div class="top-bar-right" style="display: flex; align-items: center; gap: 1rem;">
                <!-- Notification Bell -->
                <a href="{{ Route::has('notifikasi.index') ? route('notifikasi.index') : url('/notifikasi') }}" 
                   class="btn btn-outline btn-sm" style="position: relative; border-radius: 50%; width: 38px; height: 38px; padding: 0; display: inline-flex; align-items: center; justify-content: center; text-decoration: none;" title="Notifikasi">
                    <i class="fas fa-bell"></i>
                    @php
                        $unreadCount = \App\Models\Notifikasi::where('id_user', Auth::id())
                            ->where('status_baca', false)
                            ->count() ?? 0;
                    @endphp
                    @if($unreadCount > 0)
                        <span style="position: absolute; top: -4px; right: -4px; background: #dc2626; color: #ffffff; font-size: 0.65rem; font-weight: 800; padding: 0.15rem 0.4rem; border-radius: 9999px; line-height: 1;">
                            {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                        </span>
                    @endif
                </a>

                <!-- User Dropdown -->
                <div class="dropdown" style="position: relative;" id="userDropdownContainer">
                    <button class="user-menu-btn" onclick="toggleUserDropdown(event)">
                        <i class="fas fa-circle-user" style="color: var(--accent-primary); font-size: 1.1rem;"></i>
                        <span style="max-width: 140px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            {{ Auth::user()->nama ?? 'Pengguna' }}
                        </span>
                        <span class="role-pill role-{{ Auth::user()->role ?? 'masyarakat' }}">
                            {{ Auth::user()->role ?? 'warga' }}
                        </span>
                        <i class="fas fa-chevron-down" style="font-size: 0.65rem; color: var(--text-muted); margin-left: 0.25rem;"></i>
                    </button>
                    <div class="dropdown-menu" id="userDropdownMenu">
                        <div style="padding: 0.65rem 0.85rem; border-bottom: 1px solid var(--border-color); margin-bottom: 0.35rem;">
                            <div style="font-weight: 700; color: var(--header-navy); font-size: var(--font-sm);">{{ Auth::user()->nama ?? '' }}</div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">{{ Auth::user()->email ?? '' }}</div>
                        </div>

                        <a href="{{ Route::has('notifikasi.index') ? route('notifikasi.index') : url('/notifikasi') }}" class="dropdown-item">
                            <i class="fas fa-bell"></i> Notifikasi
                        </a>

                        <div class="dropdown-divider"></div>

                        <form method="POST" action="{{ Route::has('logout') ? route('logout') : url('/logout') }}" style="margin: 0;">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger" style="color: #dc2626;">
                                <i class="fas fa-arrow-right-from-bracket"></i> Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Page Container -->
        <div class="page-content" style="padding: 1.75rem 2rem;">
            <!-- Flash Message Alerts -->
            @if(session('success'))
                <div class="alert-box alert-success" role="alert">
                    <i class="fas fa-circle-check alert-icon"></i>
                    <div class="alert-content">
                        <strong>Berhasil!</strong> {{ session('success') }}
                    </div>
                    <button type="button" class="alert-close" onclick="this.parentElement.remove()" aria-label="Close">&times;</button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert-box alert-error" role="alert">
                    <i class="fas fa-circle-exclamation alert-icon"></i>
                    <div class="alert-content">
                        <strong>Perhatian:</strong> {{ session('error') }}
                    </div>
                    <button type="button" class="alert-close" onclick="this.parentElement.remove()" aria-label="Close">&times;</button>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert-box alert-warning" role="alert">
                    <i class="fas fa-triangle-exclamation alert-icon"></i>
                    <div class="alert-content">
                        <strong>Peringatan:</strong> {{ session('warning') }}
                    </div>
                    <button type="button" class="alert-close" onclick="this.parentElement.remove()" aria-label="Close">&times;</button>
                </div>
            @endif

            @if(session('info'))
                <div class="alert-box alert-info" role="alert">
                    <i class="fas fa-circle-info alert-icon"></i>
                    <div class="alert-content">
                        <strong>Informasi:</strong> {{ session('info') }}
                    </div>
                    <button type="button" class="alert-close" onclick="this.parentElement.remove()" aria-label="Close">&times;</button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert-box alert-error" role="alert">
                    <i class="fas fa-circle-xmark alert-icon"></i>
                    <div class="alert-content">
                        <strong>Terdapat beberapa kesalahan pengisian:</strong>
                        <ul style="margin: 0.5rem 0 0 1.25rem; padding: 0;">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button type="button" class="alert-close" onclick="this.parentElement.remove()" aria-label="Close">&times;</button>
                </div>
            @endif

            <!-- Dynamic Page Content -->
            @yield('content')
        </div>
    </main>

    <!-- Global App Scripts -->
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            if (sidebar) sidebar.classList.toggle('open');
            if (overlay) overlay.classList.toggle('active');
        }

        function closeSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            if (sidebar) sidebar.classList.remove('open');
            if (overlay) overlay.classList.remove('active');
        }

        function toggleUserDropdown(e) {
            e.stopPropagation();
            const menu = document.getElementById('userDropdownMenu');
            if (menu) menu.classList.toggle('show');
        }

        document.addEventListener('click', function(e) {
            const dropdown = document.getElementById('userDropdownMenu');
            if (dropdown && !e.target.closest('#userDropdownContainer')) {
                dropdown.classList.remove('show');
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
