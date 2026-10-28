@extends('layouts.app')

@section('title', 'Dashboard Warga')
@section('breadcrumb', 'Dashboard Warga')

@section('content')
<div class="dashboard-warga">
    <!-- Welcome Hero Card -->
    <div class="card" style="margin-bottom: 2rem; background: linear-gradient(135deg, #0f2b48 0%, #16385c 60%, #1e6091 100%); color: #ffffff; border: none; overflow: hidden; position: relative;">
        <div style="position: absolute; right: -20px; bottom: -20px; font-size: 10rem; color: rgba(255,255,255,0.04); pointer-events: none;">
            <i class="fas fa-landmark"></i>
        </div>
        <div class="card-body" style="padding: 2.25rem; position: relative; z-index: 1;">
            <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 1.5rem;">
                <div>
                    <div style="display: inline-flex; align-items: center; gap: 0.5rem; background: rgba(255,255,255,0.15); padding: 0.35rem 0.85rem; border-radius: 9999px; font-size: 0.8rem; margin-bottom: 0.75rem; backdrop-filter: blur(4px);">
                        <i class="fas fa-shield-halved text-amber"></i> Portal Layanan Mandiri Masyarakat
                    </div>
                    <h2 style="font-size: 1.75rem; font-weight: 800; margin: 0 0 0.5rem 0; color: #ffffff;">
                        Selamat Datang, {{ Auth::user()->nama }}!
                    </h2>
                    <p style="color: #cbd5e1; margin: 0; font-size: 0.95rem; max-width: 600px;">
                        Ajukan surat permohonan administrasi kependudukan Anda secara online, pantau status verifikasi real-time, dan unduh surat resmi ber-QR Code tanpa perlu mengantre.
                    </p>
                </div>
                <div>
                    <a href="{{ Route::has('pengajuan.create') ? route('pengajuan.create') : (Route::has('warga.pengajuan.create') ? route('warga.pengajuan.create') : url('/warga/pengajuan/create')) }}" 
                       class="btn btn-warning btn-lg" 
                       style="background: #f59e0b; color: #0f172a; border: none; font-weight: 700; box-shadow: 0 4px 15px rgba(245, 158, 11, 0.4); display: inline-flex; align-items: center; gap: 0.6rem; padding: 0.85rem 1.5rem; text-decoration: none;">
                        <i class="fas fa-plus-circle"></i> Buat Pengajuan Baru
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Overview Grid -->
    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-bottom: 2rem;">
        <!-- Total Pengajuan -->
        <div class="stat-card stat-blue">
            <div class="stat-icon">
                <i class="fas fa-file-lines"></i>
            </div>
            <div class="stat-info">
                <h4>Total Pengajuan</h4>
                <div class="stat-value">{{ $stats['total'] ?? ($pengajuans->count() ?? 0) }}</div>
            </div>
        </div>

        <!-- Sedang Diproses -->
        <div class="stat-card stat-amber">
            <div class="stat-icon">
                <i class="fas fa-spinner"></i>
            </div>
            <div class="stat-info">
                <h4>Sedang Diproses</h4>
                <div class="stat-value">{{ $stats['diproses'] ?? ($pengajuans->whereIn('status', ['DIAJUKAN','DIPERIKSA','DIVERIFIKASI','MENUNGGU_PERSETUJUAN','DISETUJUI','DIPROSES'])->count() ?? 0) }}</div>
            </div>
        </div>

        <!-- Selesai -->
        <div class="stat-card stat-green">
            <div class="stat-icon">
                <i class="fas fa-circle-check"></i>
            </div>
            <div class="stat-info">
                <h4>Selesai Terbit</h4>
                <div class="stat-value">{{ $stats['selesai'] ?? ($pengajuans->where('status', 'SELESAI')->count() ?? 0) }}</div>
            </div>
        </div>

        <!-- Perlu Revisi -->
        <div class="stat-card stat-red">
            <div class="stat-icon">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <div class="stat-info">
                <h4>Perlu Perbaikan</h4>
                <div class="stat-value">{{ $stats['revisi'] ?? ($pengajuans->where('status', 'REVISI')->count() ?? 0) }}</div>
            </div>
        </div>
    </div>

    <!-- Pilihan Layanan Populer -->
    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-header" style="background: #ffffff; padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 0.6rem;">
                <i class="fas fa-grid-2 text-primary" style="color: var(--accent-primary); font-size: 1.15rem;"></i>
                <h3 style="margin: 0; font-size: 1.1rem; font-weight: 700; color: var(--header-navy);">Layanan Administrasi Tersedia</h3>
            </div>
            <span style="font-size: 0.85rem; color: var(--text-muted);">Pilih layanan untuk langsung mengisi formulir</span>
        </div>
        <div class="card-body" style="padding: 1.5rem;">
            @php
                $layananList = $jenisPelayananList ?? \App\Models\JenisPelayanan::where('is_active', true)->get();
            @endphp
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.25rem;">
                @forelse($layananList as $jp)
                    <div style="background: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 1.25rem; display: flex; flex-direction: column; justify-content: space-between; transition: all 0.2s;" onmouseover="this.style.borderColor='var(--accent-primary)'; this.style.boxShadow='var(--shadow-md)';" onmouseout="this.style.borderColor='var(--border-color)'; this.style.boxShadow='none';">
                        <div>
                            <div style="display: flex; align-items: center; gap: 0.85rem; margin-bottom: 0.85rem;">
                                <div style="width: 44px; height: 44px; border-radius: 10px; background: #eff6ff; color: var(--accent-primary); display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                                    <i class="fas {{ $jp->icon ?? 'fa-file-lines' }}"></i>
                                </div>
                                <h4 style="margin: 0; font-size: 1rem; font-weight: 700; color: var(--header-navy); line-height: 1.3;">
                                    {{ $jp->nama_pelayanan }}
                                </h4>
                            </div>
                            <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.5; margin-bottom: 1.25rem;">
                                {{ Str::limit($jp->deskripsi ?? 'Pengajuan surat keterangan resmi dari kantor kelurahan / desa.', 95) }}
                            </p>
                        </div>
                        <a href="{{ Route::has('pengajuan.create') ? route('pengajuan.create', ['id_pelayanan' => $jp->id_pelayanan]) : (Route::has('warga.pengajuan.create') ? route('warga.pengajuan.create', ['id_pelayanan' => $jp->id_pelayanan]) : url('/warga/pengajuan/create?id_pelayanan=' . $jp->id_pelayanan)) }}" 
                           class="btn btn-outline btn-sm" 
                           style="width: 100%; border-color: var(--border-color); color: var(--accent-primary); font-weight: 600; text-decoration: none;">
                            Ajukan Sekarang <i class="fas fa-arrow-right" style="margin-left: 0.35rem;"></i>
                        </a>
                    </div>
                @empty
                    <div style="grid-column: 1 / -1; text-align: center; padding: 2rem; color: var(--text-muted);">
                        Belum ada data master pelayanan yang aktif.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent Pengajuan Table -->
    <div class="card">
        <div class="card-header" style="background: #ffffff; padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 0.6rem;">
                <i class="fas fa-clock-rotate-left" style="color: var(--accent-primary);"></i>
                <h3 style="margin: 0; font-size: 1.1rem; font-weight: 700; color: var(--header-navy);">Pengajuan Terakhir Anda</h3>
            </div>
            <a href="{{ Route::has('pengajuan.index') ? route('pengajuan.index') : (Route::has('warga.pengajuan.index') ? route('warga.pengajuan.index') : url('/warga/pengajuan')) }}" 
               class="btn btn-outline btn-sm" style="font-size: 0.8rem; text-decoration: none;">
                Lihat Semua Riwayat <i class="fas fa-chevron-right" style="font-size: 0.7rem; margin-left: 0.25rem;"></i>
            </a>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 170px;">Nomor Tiket</th>
                        <th>Jenis Layanan</th>
                        <th style="width: 140px;">Tgl Pengajuan</th>
                        <th style="width: 180px;">Status</th>
                        <th style="width: 180px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $listPengajuan = isset($pengajuans) ? $pengajuans->take(5) : collect();
                    @endphp
                    @forelse($listPengajuan as $p)
                        <tr>
                            <td>
                                <strong style="font-family: monospace; font-size: 0.88rem; color: var(--header-navy);">
                                    {{ $p->nomor_tiket }}
                                </strong>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: var(--header-navy);">
                                    {{ $p->jenisPelayanan->nama_pelayanan ?? 'Layanan' }}
                                </div>
                                @if($p->catatan_revisi && $p->status === 'REVISI')
                                    <div style="font-size: 0.78rem; color: #dc2626; margin-top: 0.2rem;">
                                        <i class="fas fa-triangle-exclamation"></i> {{ Str::limit($p->catatan_revisi, 50) }}
                                    </div>
                                @endif
                            </td>
                            <td style="font-size: 0.85rem; color: var(--text-muted);">
                                {{ \Carbon\Carbon::parse($p->tanggal_pengajuan ?? $p->created_at)->translatedFormat('d M Y') }}
                            </td>
                            <td>
                                <x-status-badge :status="$p->status" />
                            </td>
                            <td style="text-align: center;">
                                <div style="display: inline-flex; gap: 0.4rem; justify-content: center;">
                                    <a href="{{ Route::has('pengajuan.show') ? route('pengajuan.show', $p->id_pengajuan) : (Route::has('warga.pengajuan.show') ? route('warga.pengajuan.show', $p->id_pengajuan) : url('/warga/pengajuan/' . $p->id_pengajuan)) }}" 
                                       class="btn btn-outline btn-sm" 
                                       title="Lihat Detail & Lacak" 
                                       style="text-decoration: none;">
                                        <i class="fas fa-eye"></i> Detail
                                    </a>

                                    @if($p->status === 'SELESAI' && $p->surat)
                                        <a href="{{ Route::has('surat.download') ? route('surat.download', $p->surat->id_surat) : url('/surat/' . $p->surat->id_surat . '/download') }}" 
                                           class="btn btn-success btn-sm" 
                                           target="_blank" 
                                           title="Unduh Surat Resmi PDF" 
                                           style="text-decoration: none;">
                                            <i class="fas fa-file-arrow-down"></i> Unduh
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 2.5rem 1rem; color: var(--text-muted);">
                                <i class="fas fa-inbox" style="font-size: 2.5rem; color: #cbd5e1; display: block; margin-bottom: 0.75rem;"></i>
                                <span style="font-size: 0.95rem;">Belum ada pengajuan surat yang dibuat.</span><br>
                                <a href="{{ Route::has('pengajuan.create') ? route('pengajuan.create') : (Route::has('warga.pengajuan.create') ? route('warga.pengajuan.create') : url('/warga/pengajuan/create')) }}" 
                                   class="btn btn-primary btn-sm" style="margin-top: 0.75rem; text-decoration: none;">
                                    Mulai Buat Pengajuan
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
