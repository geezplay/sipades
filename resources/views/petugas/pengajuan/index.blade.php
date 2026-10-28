@extends('layouts.app')

@section('title', 'Antrean Pengajuan Berkas — Meja Petugas')
@section('breadcrumb', 'Antrean Pengajuan')

@section('content')
<div class="petugas-queue-page">
    <div class="card">
        <div class="card-header" style="background: #ffffff; padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color); display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem;">
            <div>
                <h3 style="margin: 0 0 0.25rem 0; font-size: 1.25rem; font-weight: 800; color: var(--header-navy);">
                    <i class="fas fa-inbox" style="color: var(--accent-primary); margin-right: 0.5rem;"></i>
                    Antrean Permohonan Pelayanan Administrasi
                </h3>
                <p style="margin: 0; font-size: 0.85rem; color: var(--text-muted);">
                    Verifikasi kelengkapan berkas pemohon dan koordinasikan persetujuan surat resmi
                </p>
            </div>
        </div>

        <!-- Filter Status Bar -->
        <div style="padding: 1rem 1.5rem; background: #f8fafc; border-bottom: 1px solid var(--border-color); display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                <span style="font-size: 0.85rem; font-weight: 600; color: var(--header-navy); margin-right: 0.25rem;">Status:</span>
                <a href="{{ request()->fullUrlWithQuery(['status' => '']) }}" 
                   class="btn btn-sm {{ !request('status') ? 'btn-primary' : 'btn-outline' }}" 
                   style="text-decoration: none; border-radius: 9999px;">
                    Semua
                </a>
                <a href="{{ request()->fullUrlWithQuery(['status' => 'DIAJUKAN']) }}" 
                   class="btn btn-sm {{ request('status') === 'DIAJUKAN' ? 'btn-primary' : 'btn-outline' }}" 
                   style="text-decoration: none; border-radius: 9999px;">
                    Diajukan (Baru)
                </a>
                <a href="{{ request()->fullUrlWithQuery(['status' => 'DIPERIKSA']) }}" 
                   class="btn btn-sm {{ request('status') === 'DIPERIKSA' ? 'btn-primary' : 'btn-outline' }}" 
                   style="text-decoration: none; border-radius: 9999px;">
                    Diperiksa
                </a>
                <a href="{{ request()->fullUrlWithQuery(['status' => 'REVISI']) }}" 
                   class="btn btn-sm {{ request('status') === 'REVISI' ? 'btn-danger' : 'btn-outline' }}" 
                   style="text-decoration: none; border-radius: 9999px;">
                    Revisi
                </a>
                <a href="{{ request()->fullUrlWithQuery(['status' => 'MENUNGGU_PERSETUJUAN']) }}" 
                   class="btn btn-sm {{ request('status') === 'MENUNGGU_PERSETUJUAN' ? 'btn-primary' : 'btn-outline' }}" 
                   style="text-decoration: none; border-radius: 9999px;">
                    Menunggu Kades
                </a>
                <a href="{{ request()->fullUrlWithQuery(['status' => 'DISETUJUI']) }}" 
                   class="btn btn-sm {{ request('status') === 'DISETUJUI' ? 'btn-success' : 'btn-outline' }}" 
                   style="text-decoration: none; border-radius: 9999px;">
                    Siap Terbit Surat
                </a>
                <a href="{{ request()->fullUrlWithQuery(['status' => 'SELESAI']) }}" 
                   class="btn btn-sm {{ request('status') === 'SELESAI' ? 'btn-success' : 'btn-outline' }}" 
                   style="text-decoration: none; border-radius: 9999px;">
                    Selesai
                </a>
            </div>

            <!-- Search Field -->
            <div style="position: relative; min-width: 250px;">
                <input type="text" id="filterInput" placeholder="Cari tiket, nama, atau NIK..." 
                       onkeyup="applyFilter()" 
                       style="width: 100%; padding: 0.45rem 0.75rem 0.45rem 2rem; font-size: 0.85rem; border: 1px solid var(--border-color); border-radius: 8px;">
                <i class="fas fa-search" style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); font-size: 0.8rem; color: var(--text-muted);"></i>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table" id="queueTable">
                <thead>
                    <tr>
                        <th style="width: 160px;">Nomor Tiket</th>
                        <th>Pemohon (Warga)</th>
                        <th>Jenis Layanan</th>
                        <th style="width: 140px;">Tgl Pengajuan</th>
                        <th style="width: 180px;">Status</th>
                        <th style="width: 150px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pengajuans as $p)
                        <tr class="queue-row">
                            <td>
                                <strong style="font-family: monospace; font-size: 0.9rem; color: var(--header-navy);">
                                    {{ $p->nomor_tiket }}
                                </strong>
                            </td>
                            <td>
                                <div style="font-weight: 700; color: var(--header-navy); font-size: 0.92rem;">
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
                                {{ \Carbon\Carbon::parse($p->tanggal_pengajuan ?? $p->created_at)->translatedFormat('d M Y, H:i') }}
                            </td>
                            <td>
                                <x-status-badge :status="$p->status" />
                            </td>
                            <td style="text-align: center;">
                                <a href="{{ Route::has('petugas.pengajuan.show') ? route('petugas.pengajuan.show', $p->id_pengajuan) : (Route::has('petugas.review') ? route('petugas.review', $p->id_pengajuan) : url('/petugas/pengajuan/' . $p->id_pengajuan)) }}" 
                                   class="btn {{ in_array($p->status, ['DIAJUKAN', 'DIPERIKSA']) ? 'btn-primary' : ($p->status === 'DISETUJUI' ? 'btn-success' : 'btn-outline') }} btn-sm" 
                                   style="text-decoration: none; padding: 0.35rem 0.85rem;">
                                    @if(in_array($p->status, ['DIAJUKAN', 'DIPERIKSA']))
                                        <i class="fas fa-clipboard-check"></i> Periksa
                                    @elseif($p->status === 'DISETUJUI')
                                        <i class="fas fa-print"></i> Terbitkan
                                    @else
                                        <i class="fas fa-eye"></i> Detail
                                    @endif
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 3rem 1rem; color: var(--text-muted);">
                                <i class="fas fa-folder-open" style="font-size: 2.5rem; color: #cbd5e1; display: block; margin-bottom: 0.75rem;"></i>
                                <span style="font-size: 0.95rem;">Tidak ada data permohonan dalam antrean ini.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($pengajuans, 'links'))
            <div style="padding: 1rem 1.5rem; background: #ffffff; border-top: 1px solid var(--border-color);">
                {{ $pengajuans->links() }}
            </div>
        @endif
    </div>
</div>

<script>
    function applyFilter() {
        const query = document.getElementById('filterInput').value.toLowerCase();
        const rows = document.querySelectorAll('#queueTable tbody tr.queue-row');
        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(query) ? '' : 'none';
        });
    }
</script>
@endsection
