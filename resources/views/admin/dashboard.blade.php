@extends('layouts.app')

@section('title', 'Dashboard Administrator')
@section('breadcrumb', 'Dashboard Administrator')

@section('content')
<div class="admin-dashboard">
    <!-- Header Banner -->
    <div class="card" style="margin-bottom: 2rem; background: linear-gradient(135deg, #0a192f 0%, #0f2b48 50%, #1e40af 100%); color: #ffffff; border: none;">
        <div class="card-body" style="padding: 2rem 2.25rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.5rem;">
                <div>
                    <div style="display: inline-flex; align-items: center; gap: 0.5rem; background: rgba(255,255,255,0.15); padding: 0.3rem 0.8rem; border-radius: 9999px; font-size: 0.8rem; margin-bottom: 0.75rem;">
                        <i class="fas fa-screwdriver-wrench"></i> Pusat Kendali Sistem Administrasi Desa
                    </div>
                    <h2 style="font-size: 1.75rem; font-weight: 800; margin: 0 0 0.5rem 0; color: #ffffff;">
                        Panel Administrator SIPADES
                    </h2>
                    <p style="color: #cbd5e1; margin: 0; font-size: 0.95rem;">
                        Kelola data pengguna, hak akses multi-peran, konfigurasi master formulir pelayanan, dan pantau statistik operasional desa.
                    </p>
                </div>
                <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                    <a href="{{ Route::has('admin.users') ? route('admin.users') : (Route::has('admin.users.index') ? route('admin.users.index') : url('/admin/users')) }}" 
                       class="btn btn-warning" 
                       style="font-weight: 700; color: #0f172a; text-decoration: none; display: inline-flex; align-items: center; gap: 0.4rem;">
                        <i class="fas fa-users-gear"></i> Kelola Pengguna
                    </a>
                    <a href="{{ Route::has('admin.services') ? route('admin.services') : (Route::has('admin.services.index') ? route('admin.services.index') : url('/admin/services')) }}" 
                       class="btn btn-outline" 
                       style="background: rgba(255,255,255,0.15); color: #ffffff; border-color: rgba(255,255,255,0.3); font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 0.4rem;">
                        <i class="fas fa-clipboard-list"></i> Master Layanan
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-bottom: 2rem;">
        <!-- Total Users -->
        <div class="stat-card stat-blue">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-info">
                <h4>Total Pengguna</h4>
                <div class="stat-value">{{ $stats['users'] ?? (\App\Models\User::count() ?? 0) }}</div>
            </div>
        </div>

        <!-- Master Layanan -->
        <div class="stat-card stat-purple">
            <div class="stat-icon">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <div class="stat-info">
                <h4>Master Layanan Aktif</h4>
                <div class="stat-value">{{ $stats['layanan'] ?? (\App\Models\JenisPelayanan::where('is_active', true)->count() ?? 0) }}</div>
            </div>
        </div>

        <!-- Total Pengajuan -->
        <div class="stat-card stat-amber">
            <div class="stat-icon">
                <i class="fas fa-file-lines"></i>
            </div>
            <div class="stat-info">
                <h4>Total Permohonan</h4>
                <div class="stat-value">{{ $stats['pengajuan'] ?? (\App\Models\Pengajuan::count() ?? 0) }}</div>
            </div>
        </div>

        <!-- Total Surat Terbit -->
        <div class="stat-card stat-green">
            <div class="stat-icon">
                <i class="fas fa-certificate"></i>
            </div>
            <div class="stat-info">
                <h4>Surat Resmi Terbit</h4>
                <div class="stat-value">{{ $stats['surat'] ?? (\App\Models\Surat::count() ?? 0) }}</div>
            </div>
        </div>
    </div>

    <!-- 2 Kolom: Pengguna Terbaru & Ringkasan Layanan -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 1.5rem;">
        <!-- Card Pengguna Terbaru -->
        <div class="card">
            <div class="card-header" style="background: #ffffff; padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 0.6rem;">
                    <i class="fas fa-user-clock" style="color: var(--accent-primary);"></i>
                    <h3 style="margin: 0; font-size: 1.05rem; font-weight: 700; color: var(--header-navy);">Pengguna Baru Terdaftar</h3>
                </div>
                <a href="{{ Route::has('admin.users') ? route('admin.users') : (Route::has('admin.users.index') ? route('admin.users.index') : url('/admin/users')) }}" 
                   class="btn btn-outline btn-sm" style="font-size: 0.8rem; text-decoration: none;">
                    Kelola Semua &rarr;
                </a>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Role</th>
                            <th>Tgl Terdaftar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $recentUsers = \App\Models\User::latest()->take(6)->get();
                        @endphp
                        @forelse($recentUsers as $u)
                            <tr>
                                <td>
                                    <div style="font-weight: 700; color: var(--header-navy);">{{ $u->nama }}</div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $u->email }}</div>
                                </td>
                                <td>
                                    <span class="role-pill role-{{ $u->role }}">
                                        {{ $u->role }}
                                    </span>
                                </td>
                                <td style="font-size: 0.8rem; color: var(--text-muted);">
                                    {{ \Carbon\Carbon::parse($u->created_at)->translatedFormat('d M Y') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" style="text-align: center; color: var(--text-muted); padding: 1.5rem;">
                                    Belum ada data pengguna.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Card Master Layanan -->
        <div class="card">
            <div class="card-header" style="background: #ffffff; padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 0.6rem;">
                    <i class="fas fa-list-check" style="color: var(--accent-primary);"></i>
                    <h3 style="margin: 0; font-size: 1.05rem; font-weight: 700; color: var(--header-navy);">Status Layanan Administrasi</h3>
                </div>
                <a href="{{ Route::has('admin.services') ? route('admin.services') : (Route::has('admin.services.index') ? route('admin.services.index') : url('/admin/services')) }}" 
                   class="btn btn-outline btn-sm" style="font-size: 0.8rem; text-decoration: none;">
                    Kelola Layanan &rarr;
                </a>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Jenis Pelayanan</th>
                            <th>ID Layanan</th>
                            <th style="text-align: center;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $services = \App\Models\JenisPelayanan::all();
                        @endphp
                        @forelse($services as $s)
                            <tr>
                                <td>
                                    <div style="font-weight: 700; color: var(--header-navy); display: flex; align-items: center; gap: 0.5rem;">
                                        <i class="fas {{ $s->icon ?? 'fa-file-lines' }}" style="color: var(--accent-primary);"></i>
                                        {{ $s->nama_pelayanan }}
                                    </div>
                                </td>
                                <td>
                                    <code style="font-size: 0.78rem; background: #f1f5f9; padding: 0.15rem 0.4rem; border-radius: 4px;">{{ $s->id_pelayanan }}</code>
                                </td>
                                <td style="text-align: center;">
                                    @if($s->is_active)
                                        <span class="badge badge-selesai" style="font-size: 0.75rem;">
                                            <i class="fas fa-check"></i> Aktif
                                        </span>
                                    @else
                                        <span class="badge badge-ditolak" style="font-size: 0.75rem;">
                                            <i class="fas fa-ban"></i> Nonaktif
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" style="text-align: center; color: var(--text-muted); padding: 1.5rem;">
                                    Belum ada master layanan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
