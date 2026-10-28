@extends('layouts.app')

@section('title', 'Dashboard Kepala Desa / Lurah')
@section('breadcrumb', 'Dashboard Kepala Desa')

@section('content')
<div class="kepala-dashboard">
    <!-- Header Card -->
    <div class="card" style="margin-bottom: 2rem; background: linear-gradient(135deg, #0a192f 0%, #16385c 50%, #7c3aed 100%); color: #ffffff; border: none;">
        <div class="card-body" style="padding: 2rem 2.25rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.5rem;">
                <div>
                    <div style="display: inline-flex; align-items: center; gap: 0.5rem; background: rgba(255,255,255,0.15); padding: 0.3rem 0.8rem; border-radius: 9999px; font-size: 0.8rem; margin-bottom: 0.75rem;">
                        <i class="fas fa-stamp"></i> Otoritas Pengesahan & Tanda Tangan Dokumen
                    </div>
                    <h2 style="font-size: 1.75rem; font-weight: 800; margin: 0 0 0.5rem 0; color: #ffffff;">
                        Selamat Bertugas, {{ Auth::user()->nama }}
                    </h2>
                    <p style="color: #e2e8f0; margin: 0; font-size: 0.95rem;">
                        Tinjau keabsahan berkas yang telah diverifikasi oleh petugas pelayanan dan berikan persetujuan tanda tangan digital kedinasan.
                    </p>
                </div>
                <div>
                    @php
                        $menungguCount = \App\Models\Pengajuan::where('status', 'MENUNGGU_PERSETUJUAN')->count() ?? 0;
                    @endphp
                    @if($menungguCount > 0)
                        <span class="badge" style="background: #f59e0b; color: #0f172a; font-size: 0.95rem; font-weight: 800; padding: 0.65rem 1.25rem; border-radius: 9999px;">
                            <i class="fas fa-bell"></i> {{ $menungguCount }} Berkas Perlu Persetujuan
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-bottom: 2rem;">
        <!-- Menunggu Persetujuan -->
        <div class="stat-card stat-purple">
            <div class="stat-icon">
                <i class="fas fa-stamp"></i>
            </div>
            <div class="stat-info">
                <h4>Perlu Persetujuan</h4>
                <div class="stat-value">{{ $stats['menunggu'] ?? (\App\Models\Pengajuan::where('status', 'MENUNGGU_PERSETUJUAN')->count() ?? 0) }}</div>
            </div>
        </div>

        <!-- Telah Disetujui -->
        <div class="stat-card stat-green">
            <div class="stat-icon">
                <i class="fas fa-circle-check"></i>
            </div>
            <div class="stat-info">
                <h4>Telah Disetujui</h4>
                <div class="stat-value">{{ $stats['disetujui'] ?? (\App\Models\Pengajuan::whereIn('status', ['DISETUJUI', 'SELESAI'])->count() ?? 0) }}</div>
            </div>
        </div>

        <!-- Ditolak -->
        <div class="stat-card stat-red">
            <div class="stat-icon">
                <i class="fas fa-circle-xmark"></i>
            </div>
            <div class="stat-info">
                <h4>Ditolak</h4>
                <div class="stat-value">{{ $stats['ditolak'] ?? (\App\Models\Pengajuan::where('status', 'DITOLAK')->count() ?? 0) }}</div>
            </div>
        </div>

        <!-- Total Surat Selesai -->
        <div class="stat-card stat-blue">
            <div class="stat-icon">
                <i class="fas fa-file-certificate"></i>
            </div>
            <div class="stat-info">
                <h4>Total Surat Terbit</h4>
                <div class="stat-value">{{ $stats['surat'] ?? (\App\Models\Surat::count() ?? 0) }}</div>
            </div>
        </div>
    </div>

    <!-- Antrean Persetujuan Surat (MENUNGGU_PERSETUJUAN) -->
    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-header" style="background: #ffffff; padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 0.6rem;">
                <i class="fas fa-clipboard-check" style="color: #7c3aed; font-size: 1.2rem;"></i>
                <h3 style="margin: 0; font-size: 1.15rem; font-weight: 800; color: var(--header-navy);">
                    Berkas Menunggu Persetujuan Anda
                </h3>
            </div>
            <a href="{{ Route::has('kepala.pengajuan.index') ? route('kepala.pengajuan.index') : url('/kepala/pengajuan') }}" 
               class="btn btn-outline btn-sm" style="text-decoration: none;">
                Lihat Semua &rarr;
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
                        <th style="width: 170px;">Verifikator</th>
                        <th style="width: 200px; text-align: center;">Keputusan Approval</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $pendingList = isset($pengajuans) 
                            ? $pengajuans->where('status', 'MENUNGGU_PERSETUJUAN') 
                            : \App\Models\Pengajuan::with(['masyarakat.user', 'jenisPelayanan', 'verifikasi.petugas.user'])
                                ->where('status', 'MENUNGGU_PERSETUJUAN')
                                ->latest()
                                ->get();
                    @endphp
                    @forelse($pendingList as $p)
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
                                @php
                                    $lastVerif = $p->verifikasi ? $p->verifikasi->last() : null;
                                @endphp
                                <span style="font-size: 0.85rem; font-weight: 600; color: #0d9488;">
                                    <i class="fas fa-check-circle"></i> {{ $lastVerif->petugas->user->nama ?? 'Petugas Desa' }}
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <div style="display: inline-flex; gap: 0.4rem; justify-content: center;">
                                    <a href="{{ Route::has('kepala.pengajuan.show') ? route('kepala.pengajuan.show', $p->id_pengajuan) : (Route::has('kepala.review') ? route('kepala.review', $p->id_pengajuan) : url('/kepala/pengajuan/' . $p->id_pengajuan)) }}" 
                                       class="btn btn-primary btn-sm" 
                                       style="text-decoration: none; padding: 0.35rem 0.75rem;">
                                        <i class="fas fa-eye"></i> Tinjau & Putuskan
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 3rem 1rem; color: var(--text-muted);">
                                <i class="fas fa-circle-check" style="font-size: 2.5rem; color: #16a34a; display: block; margin-bottom: 0.75rem;"></i>
                                <span style="font-size: 0.95rem; font-weight: 600;">Tidak ada berkas yang menunggu persetujuan. Kerja bagus!</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
