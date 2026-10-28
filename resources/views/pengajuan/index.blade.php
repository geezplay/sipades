@extends('layouts.app')

@section('title', 'Daftar Pengajuan Surat')
@section('breadcrumb', 'Daftar Pengajuan')

@section('content')
<div class="pengajuan-index-page">
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header" style="background: #ffffff; padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color); display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem;">
            <div>
                <h3 style="margin: 0 0 0.25rem 0; font-size: 1.25rem; font-weight: 800; color: var(--header-navy);">
                    <i class="fas fa-folder-open" style="color: var(--accent-primary); margin-right: 0.5rem;"></i>
                    Daftar & Riwayat Pengajuan Surat
                </h3>
                <p style="margin: 0; font-size: 0.85rem; color: var(--text-muted);">
                    Pantau status verifikasi dokumen administrasi Anda secara real-time
                </p>
            </div>
            <div>
                <a href="{{ Route::has('pengajuan.create') ? route('pengajuan.create') : (Route::has('warga.pengajuan.create') ? route('warga.pengajuan.create') : url('/warga/pengajuan/create')) }}" 
                   class="btn btn-primary" 
                   style="display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none;">
                    <i class="fas fa-plus"></i> Buat Pengajuan Baru
                </a>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div style="padding: 1rem 1.5rem; background: #f8fafc; border-bottom: 1px solid var(--border-color); display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                <label style="font-size: 0.85rem; font-weight: 600; color: var(--header-navy);">Filter Status:</label>
                <div style="display: flex; gap: 0.35rem; flex-wrap: wrap;">
                    <a href="{{ request()->fullUrlWithQuery(['status' => '']) }}" 
                       class="btn btn-sm {{ !request('status') ? 'btn-primary' : 'btn-outline' }}" 
                       style="text-decoration: none; border-radius: 9999px;">
                        Semua
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'DIAJUKAN']) }}" 
                       class="btn btn-sm {{ request('status') === 'DIAJUKAN' ? 'btn-primary' : 'btn-outline' }}" 
                       style="text-decoration: none; border-radius: 9999px;">
                        Diajukan
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'DIPERIKSA']) }}" 
                       class="btn btn-sm {{ request('status') === 'DIPERIKSA' ? 'btn-primary' : 'btn-outline' }}" 
                       style="text-decoration: none; border-radius: 9999px;">
                        Diperiksa
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'REVISI']) }}" 
                       class="btn btn-sm {{ request('status') === 'REVISI' ? 'btn-danger' : 'btn-outline' }}" 
                       style="text-decoration: none; border-radius: 9999px;">
                        Perlu Revisi
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'MENUNGGU_PERSETUJUAN']) }}" 
                       class="btn btn-sm {{ request('status') === 'MENUNGGU_PERSETUJUAN' ? 'btn-primary' : 'btn-outline' }}" 
                       style="text-decoration: none; border-radius: 9999px;">
                        Menunggu Kades
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'SELESAI']) }}" 
                       class="btn btn-sm {{ request('status') === 'SELESAI' ? 'btn-success' : 'btn-outline' }}" 
                       style="text-decoration: none; border-radius: 9999px;">
                        Selesai
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'DITOLAK']) }}" 
                       class="btn btn-sm {{ request('status') === 'DITOLAK' ? 'btn-danger' : 'btn-outline' }}" 
                       style="text-decoration: none; border-radius: 9999px;">
                        Ditolak
                    </a>
                </div>
            </div>

            <!-- Client Filter / Quick Search -->
            <div style="position: relative; min-width: 240px;">
                <input type="text" id="quickSearchInput" placeholder="Cari nomor tiket atau layanan..." 
                       onkeyup="filterTable()" 
                       style="width: 100%; padding: 0.45rem 0.75rem 0.45rem 2rem; font-size: 0.85rem; border: 1px solid var(--border-color); border-radius: 8px;">
                <i class="fas fa-search" style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); font-size: 0.8rem; color: var(--text-muted);"></i>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table" id="pengajuanTable">
                <thead>
                    <tr>
                        <th style="width: 180px;">Nomor Tiket</th>
                        <th>Jenis Layanan</th>
                        <th style="width: 150px;">Tanggal Pengajuan</th>
                        <th style="width: 190px;">Status</th>
                        <th style="width: 220px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pengajuans as $p)
                        <tr class="pengajuan-row">
                            <td class="col-tiket">
                                <strong style="font-family: monospace; font-size: 0.9rem; color: var(--header-navy);">
                                    {{ $p->nomor_tiket }}
                                </strong>
                            </td>
                            <td class="col-layanan">
                                <div style="font-weight: 600; color: var(--header-navy); font-size: 0.92rem;">
                                    {{ $p->jenisPelayanan->nama_pelayanan ?? 'Layanan' }}
                                </div>
                                @if($p->status === 'REVISI' && $p->catatan_revisi)
                                    <div style="margin-top: 0.35rem; padding: 0.35rem 0.65rem; background: #fef2f2; border: 1px solid #fecaca; border-radius: 6px; font-size: 0.8rem; color: #dc2626;">
                                        <strong><i class="fas fa-triangle-exclamation"></i> Catatan Revisi:</strong> {{ $p->catatan_revisi }}
                                    </div>
                                @elseif($p->status === 'DITOLAK' && $p->alasan_penolakan)
                                    <div style="margin-top: 0.35rem; padding: 0.35rem 0.65rem; background: #fef2f2; border: 1px solid #fecaca; border-radius: 6px; font-size: 0.8rem; color: #dc2626;">
                                        <strong><i class="fas fa-circle-xmark"></i> Alasan Penolakan:</strong> {{ $p->alasan_penolakan }}
                                    </div>
                                @endif
                            </td>
                            <td style="font-size: 0.85rem; color: var(--text-muted);">
                                {{ \Carbon\Carbon::parse($p->tanggal_pengajuan ?? $p->created_at)->translatedFormat('d M Y, H:i') }}
                            </td>
                            <td>
                                <x-status-badge :status="$p->status" />
                            </td>
                            <td style="text-align: center;">
                                <div style="display: inline-flex; gap: 0.4rem; justify-content: center; flex-wrap: wrap;">
                                    <a href="{{ Route::has('pengajuan.show') ? route('pengajuan.show', $p->id_pengajuan) : (Route::has('warga.pengajuan.show') ? route('warga.pengajuan.show', $p->id_pengajuan) : url('/warga/pengajuan/' . $p->id_pengajuan)) }}" 
                                       class="btn btn-outline btn-sm" 
                                       title="Lihat Detail & Lacak Status" 
                                       style="text-decoration: none;">
                                        <i class="fas fa-eye"></i> Detail
                                    </a>

                                    @if($p->status === 'REVISI')
                                        <a href="{{ Route::has('pengajuan.show') ? route('pengajuan.show', $p->id_pengajuan) : (Route::has('warga.pengajuan.show') ? route('warga.pengajuan.show', $p->id_pengajuan) : url('/warga/pengajuan/' . $p->id_pengajuan)) }}#revisiSection" 
                                           class="btn btn-danger btn-sm" 
                                           style="text-decoration: none;">
                                            <i class="fas fa-wrench"></i> Perbaiki
                                        </a>
                                    @endif

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
                            <td colspan="5" style="text-align: center; padding: 3rem 1rem; color: var(--text-muted);">
                                <i class="fas fa-folder-open" style="font-size: 2.5rem; color: #cbd5e1; display: block; margin-bottom: 0.75rem;"></i>
                                <span style="font-size: 0.95rem;">Tidak ada riwayat pengajuan surat ditemukan.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function filterTable() {
        const query = document.getElementById('quickSearchInput').value.toLowerCase();
        const rows = document.querySelectorAll('#pengajuanTable tbody tr.pengajuan-row');
        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(query) ? '' : 'none';
        });
    }
</script>
@endsection
