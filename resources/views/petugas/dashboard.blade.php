@extends('layouts.app')

@section('title', 'Dashboard Petugas Pelayanan')
@section('breadcrumb', 'Dashboard Petugas')

@section('content')
<div class="petugas-dashboard">
    <!-- Header Welcome Card -->
    <div class="card" style="margin-bottom: 2rem; background: linear-gradient(135deg, #0a192f 0%, #0f2b48 50%, #1e6091 100%); color: #ffffff; border: none;">
        <div class="card-body" style="padding: 2rem 2.25rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.5rem;">
                <div>
                    <div style="display: inline-flex; align-items: center; gap: 0.5rem; background: rgba(255,255,255,0.15); padding: 0.3rem 0.8rem; border-radius: 9999px; font-size: 0.8rem; margin-bottom: 0.75rem;">
                        <i class="fas fa-clipboard-check"></i> Meja Pelayanan & Verifikasi Berkas Administrasi
                    </div>
                    <h2 style="font-size: 1.75rem; font-weight: 800; margin: 0 0 0.5rem 0; color: #ffffff;">
                        Halo, {{ Auth::user()->nama }}
                    </h2>
                    <p style="color: #cbd5e1; margin: 0; font-size: 0.95rem;">
                        Kelola antrean permohonan surat masyarakat, verifikasi kelengkapan berkas fisik & digital, dan terbitkan surat kedinasan ber-QR Code.
                    </p>
                </div>
                <div>
                    <a href="{{ Route::has('petugas.pengajuan.index') ? route('petugas.pengajuan.index') : url('/petugas/pengajuan') }}" 
                       class="btn btn-warning btn-lg" 
                       style="background: #f59e0b; color: #0f172a; border: none; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-inbox"></i> Buka Antrean Masuk
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-bottom: 2rem;">
        <!-- Antrean Baru -->
        <div class="stat-card stat-blue">
            <div class="stat-icon">
                <i class="fas fa-inbox"></i>
            </div>
            <div class="stat-info">
                <h4>Antrean Masuk (Diajukan)</h4>
                <div class="stat-value">{{ $stats['diajukan'] ?? (\App\Models\Pengajuan::where('status', 'DIAJUKAN')->count() ?? 0) }}</div>
            </div>
        </div>

        <!-- Sedang Diperiksa -->
        <div class="stat-card stat-amber">
            <div class="stat-icon">
                <i class="fas fa-magnifying-glass"></i>
            </div>
            <div class="stat-info">
                <h4>Sedang Diperiksa</h4>
                <div class="stat-value">{{ $stats['diperiksa'] ?? (\App\Models\Pengajuan::where('status', 'DIPERIKSA')->count() ?? 0) }}</div>
            </div>
        </div>

        <!-- Menunggu Kades -->
        <div class="stat-card stat-purple">
            <div class="stat-icon">
                <i class="fas fa-stamp"></i>
            </div>
            <div class="stat-info">
                <h4>Menunggu Kades</h4>
                <div class="stat-value">{{ $stats['menunggu'] ?? (\App\Models\Pengajuan::where('status', 'MENUNGGU_PERSETUJUAN')->count() ?? 0) }}</div>
            </div>
        </div>

        <!-- Siap Diterbitkan -->
        <div class="stat-card stat-green">
            <div class="stat-icon">
                <i class="fas fa-print"></i>
            </div>
            <div class="stat-info">
                <h4>Siap Terbit (Disetujui)</h4>
                <div class="stat-value">{{ $stats['disetujui'] ?? (\App\Models\Pengajuan::whereIn('status', ['DISETUJUI', 'DIPROSES'])->count() ?? 0) }}</div>
            </div>
        </div>
    </div>

    <!-- Antrean Pengajuan Aktif -->
    <div class="card">
        <div class="card-header" style="background: #ffffff; padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.6rem;">
                <i class="fas fa-list-check" style="color: var(--accent-primary); font-size: 1.15rem;"></i>
                <h3 style="margin: 0; font-size: 1.15rem; font-weight: 800; color: var(--header-navy);">Antrean Pengajuan Berkas Aktif</h3>
            </div>
            <a href="{{ Route::has('petugas.pengajuan.index') ? route('petugas.pengajuan.index') : url('/petugas/pengajuan') }}" 
               class="btn btn-outline btn-sm" style="text-decoration: none;">
                Kelola Semua Antrean &rarr;
            </a>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 170px;">Nomor Tiket</th>
                        <th>Pemohon (Warga)</th>
                        <th>Jenis Layanan</th>
                        <th style="width: 140px;">Tgl Masuk</th>
                        <th style="width: 170px;">Status</th>
                        <th style="width: 140px; text-align: center;">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $activeQueue = isset($pengajuans) 
                            ? $pengajuans->take(8) 
                            : \App\Models\Pengajuan::with(['masyarakat.user', 'jenisPelayanan'])
                                ->whereIn('status', ['DIAJUKAN', 'DIPERIKSA', 'REVISI', 'DISETUJUI', 'DIPROSES'])
                                ->latest()
                                ->take(8)
                                ->get();
                    @endphp
                    @forelse($activeQueue as $p)
                        <tr>
                            <td>
                                <strong style="font-family: monospace; font-size: 0.88rem; color: var(--header-navy);">
                                    {{ $p->nomor_tiket }}
                                </strong>
                            </td>
                            <td>
                                <div style="font-weight: 700; color: var(--header-navy);">
                                    {{ $p->masyarakat->user->nama ?? 'Warga' }}
                                </div>
                                <div style="font-size: 0.78rem; color: var(--text-muted); font-family: monospace;">
                                    NIK: {{ $p->masyarakat->nik ?? '-' }}
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: var(--text-secondary);">
                                    {{ $p->jenisPelayanan->nama_pelayanan ?? 'Layanan' }}
                                </div>
                            </td>
                            <td style="font-size: 0.85rem; color: var(--text-muted);">
                                {{ \Carbon\Carbon::parse($p->tanggal_pengajuan ?? $p->created_at)->translatedFormat('d M Y') }}
                            </td>
                            <td>
                                <x-status-badge :status="$p->status" />
                            </td>
                            <td style="text-align: center;">
                                <a href="{{ Route::has('petugas.pengajuan.show') ? route('petugas.pengajuan.show', $p->id_pengajuan) : (Route::has('petugas.review') ? route('petugas.review', $p->id_pengajuan) : url('/petugas/pengajuan/' . $p->id_pengajuan)) }}" 
                                   class="btn btn-primary btn-sm" 
                                   style="text-decoration: none; padding: 0.35rem 0.85rem;">
                                    <i class="fas fa-clipboard-check"></i> Periksa
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 3rem 1rem; color: var(--text-muted);">
                                <i class="fas fa-circle-check" style="font-size: 2.5rem; color: #16a34a; display: block; margin-bottom: 0.75rem;"></i>
                                <span style="font-size: 0.95rem; font-weight: 600;">Semua berkas pengajuan telah selesai diperiksa!</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
