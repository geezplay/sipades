@extends('layouts.app')

@section('title', 'Daftar Persetujuan Surat — Kepala Desa')
@section('breadcrumb', 'Persetujuan Surat')

@section('content')
<div class="kepala-pengajuan-index">
    <div class="card">
        <div class="card-header" style="background: #ffffff; padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color); display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem;">
            <div>
                <h3 style="margin: 0 0 0.25rem 0; font-size: 1.25rem; font-weight: 800; color: var(--header-navy);">
                    <i class="fas fa-stamp" style="color: #7c3aed; margin-right: 0.5rem;"></i>
                    Daftar Pengajuan & Persetujuan Kepala Desa
                </h3>
                <p style="margin: 0; font-size: 0.85rem; color: var(--text-muted);">
                    Tinjau berkas yang telah diverifikasi petugas sebelum memberikan tanda tangan pengesahan
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
                <a href="{{ request()->fullUrlWithQuery(['status' => 'MENUNGGU_PERSETUJUAN']) }}" 
                   class="btn btn-sm {{ request('status') === 'MENUNGGU_PERSETUJUAN' ? 'btn-primary' : 'btn-outline' }}" 
                   style="text-decoration: none; border-radius: 9999px;">
                    Perlu Persetujuan
                </a>
                <a href="{{ request()->fullUrlWithQuery(['status' => 'DISETUJUI']) }}" 
                   class="btn btn-sm {{ request('status') === 'DISETUJUI' ? 'btn-success' : 'btn-outline' }}" 
                   style="text-decoration: none; border-radius: 9999px;">
                    Telah Disetujui
                </a>
                <a href="{{ request()->fullUrlWithQuery(['status' => 'SELESAI']) }}" 
                   class="btn btn-sm {{ request('status') === 'SELESAI' ? 'btn-success' : 'btn-outline' }}" 
                   style="text-decoration: none; border-radius: 9999px;">
                    Selesai Terbit
                </a>
                <a href="{{ request()->fullUrlWithQuery(['status' => 'DITOLAK']) }}" 
                   class="btn btn-sm {{ request('status') === 'DITOLAK' ? 'btn-danger' : 'btn-outline' }}" 
                   style="text-decoration: none; border-radius: 9999px;">
                    Ditolak
                </a>
            </div>

            <!-- Search Field -->
            <div style="position: relative; min-width: 250px;">
                <input type="text" id="kadesSearchInput" placeholder="Cari tiket, nama, atau NIK..." 
                       onkeyup="filterKadesTable()" 
                       style="width: 100%; padding: 0.45rem 0.75rem 0.45rem 2rem; font-size: 0.85rem; border: 1px solid var(--border-color); border-radius: 8px;">
                <i class="fas fa-search" style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); font-size: 0.8rem; color: var(--text-muted);"></i>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table" id="kadesTable">
                <thead>
                    <tr>
                        <th style="width: 160px;">Nomor Tiket</th>
                        <th>Pemohon (Warga)</th>
                        <th>Jenis Layanan</th>
                        <th style="width: 140px;">Tgl Pengajuan</th>
                        <th style="width: 180px;">Status</th>
                        <th style="width: 160px; text-align: center;">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pengajuans as $p)
                        <tr class="kades-row">
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
                                <a href="{{ Route::has('kepala.pengajuan.show') ? route('kepala.pengajuan.show', $p->id_pengajuan) : (Route::has('kepala.review') ? route('kepala.review', $p->id_pengajuan) : url('/kepala/pengajuan/' . $p->id_pengajuan)) }}" 
                                   class="btn {{ $p->status === 'MENUNGGU_PERSETUJUAN' ? 'btn-primary' : 'btn-outline' }} btn-sm" 
                                   style="text-decoration: none; padding: 0.35rem 0.85rem;">
                                    @if($p->status === 'MENUNGGU_PERSETUJUAN')
                                        <i class="fas fa-stamp"></i> Tinjau & Putuskan
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
                                <span style="font-size: 0.95rem;">Tidak ada permohonan dalam kategori ini.</span>
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
    function filterKadesTable() {
        const query = document.getElementById('kadesSearchInput').value.toLowerCase();
        const rows = document.querySelectorAll('#kadesTable tbody tr.kades-row');
        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(query) ? '' : 'none';
        });
    }
</script>
@endsection
