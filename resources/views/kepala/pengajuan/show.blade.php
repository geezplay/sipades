@extends('layouts.app')

@section('title', 'Persetujuan Surat #' . ($pengajuan->nomor_tiket ?? ''))
@section('breadcrumb', 'Persetujuan Surat')

@section('content')
<div class="kepala-review-page" style="max-width: 1050px; margin: 0 auto;">

    <!-- Top Action Bar -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <a href="{{ Route::has('kepala.pengajuan.index') ? route('kepala.pengajuan.index') : url('/kepala/pengajuan') }}" 
               class="btn btn-outline btn-sm" style="text-decoration: none; margin-bottom: 0.5rem; display: inline-flex; align-items: center; gap: 0.4rem;">
                <i class="fas fa-arrow-left"></i> Kembali ke Daftar Persetujuan
            </a>
            <h2 style="margin: 0; font-size: 1.5rem; font-weight: 800; color: var(--header-navy); display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                <span>Permohonan Surat #{{ $pengajuan->nomor_tiket }}</span>
                <x-status-badge :status="$pengajuan->status" />
            </h2>
        </div>

        @if($pengajuan->status === 'SELESAI' && $pengajuan->surat)
            <div>
                <a href="{{ Route::has('surat.download') ? route('surat.download', $pengajuan->surat->id_surat) : url('/surat/' . $pengajuan->surat->id_surat . '/download') }}" 
                   class="btn btn-success" 
                   target="_blank" 
                   style="text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; font-weight: 700;">
                    <i class="fas fa-file-pdf"></i> Unduh Surat PDF Terbit
                </a>
            </div>
        @endif
    </div>

    <!-- Alert Persetujuan / Penolakan Info -->
    @if($pengajuan->status === 'DISETUJUI')
        <div class="alert-box alert-success">
            <i class="fas fa-circle-check alert-icon"></i>
            <div class="alert-content">
                <strong>Pengajuan Telah Anda Setujui!</strong><br>
                Permohonan kini berada di meja petugas pelayanan untuk kompilasi nomor surat dan QR Code resmi.
            </div>
        </div>
    @elseif($pengajuan->status === 'DITOLAK')
        <div class="alert-box alert-danger">
            <i class="fas fa-circle-xmark alert-icon"></i>
            <div class="alert-content">
                <strong>Pengajuan Telah Ditolak</strong><br>
                Alasan penolakan: {{ $pengajuan->alasan_penolakan }}
            </div>
        </div>
    @elseif($pengajuan->status === 'MENUNGGU_PERSETUJUAN')
        <div class="alert-box alert-info">
            <i class="fas fa-stamp alert-icon"></i>
            <div class="alert-content">
                <strong>Menunggu Keputusan Persetujuan Anda</strong><br>
                Berkas telah diverifikasi lengkap dan sah oleh petugas pelayanan desa. Silakan berikan keputusan di panel bawah.
            </div>
        </div>
    @endif

    <!-- 2 Kolom: Data Pemohon & Data Formulir -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
        <!-- Profil Pemohon -->
        <div class="card">
            <div class="card-header" style="background: #ffffff; padding: 1.15rem 1.25rem; border-bottom: 1px solid var(--border-color);">
                <h4 style="margin: 0; font-size: 0.95rem; font-weight: 700; color: var(--header-navy);">
                    <i class="fas fa-user" style="color: var(--accent-primary); margin-right: 0.4rem;"></i>
                    Data Pemohon (Warga)
                </h4>
            </div>
            <div class="card-body" style="padding: 1.25rem;">
                <table style="width: 100%; font-size: 0.88rem; line-height: 2;">
                    <tr>
                        <td style="width: 110px; color: var(--text-muted);">Nama</td>
                        <td style="font-weight: 700; color: var(--header-navy);">: {{ $pengajuan->masyarakat->user->nama ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="color: var(--text-muted);">NIK</td>
                        <td style="font-family: monospace; font-weight: 600;">: {{ $pengajuan->masyarakat->nik ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="color: var(--text-muted);">No. HP / WA</td>
                        <td>: {{ $pengajuan->masyarakat->no_hp ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="color: var(--text-muted);">Email</td>
                        <td>: {{ $pengajuan->masyarakat->user->email ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="color: var(--text-muted); vertical-align: top;">Alamat</td>
                        <td style="vertical-align: top;">: {{ $pengajuan->masyarakat->alamat ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Detail Formulir Layanan -->
        <div class="card">
            <div class="card-header" style="background: #ffffff; padding: 1.15rem 1.25rem; border-bottom: 1px solid var(--border-color);">
                <h4 style="margin: 0; font-size: 0.95rem; font-weight: 700; color: var(--header-navy);">
                    <i class="fas fa-file-invoice" style="color: var(--accent-primary); margin-right: 0.4rem;"></i>
                    Informasi Formulir ({{ $pengajuan->jenisPelayanan->nama_pelayanan ?? 'Layanan' }})
                </h4>
            </div>
            <div class="card-body" style="padding: 1.25rem;">
                @php
                    $formData = is_array($pengajuan->data_formulir) 
                        ? $pengajuan->data_formulir 
                        : (json_decode($pengajuan->data_formulir, true) ?? []);
                @endphp
                @if(empty($formData))
                    <p style="color: var(--text-muted); font-size: 0.88rem; margin: 0;">Tidak ada formulir khusus.</p>
                @else
                    <table style="width: 100%; font-size: 0.88rem; line-height: 2;">
                        @foreach($formData as $key => $val)
                            @if(!is_array($val))
                                <tr>
                                    <td style="width: 130px; color: var(--text-muted); text-transform: capitalize;">
                                        {{ str_replace('_', ' ', $key) }}
                                    </td>
                                    <td style="font-weight: 600; color: var(--header-navy);">
                                        : {{ $val }}
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </table>
                @endif

                @if($pengajuan->catatan)
                    <div style="margin-top: 1rem; padding-top: 0.75rem; border-top: 1px solid var(--border-color); font-size: 0.85rem;">
                        <span style="color: var(--text-muted); font-weight: 600;">Catatan Tambahan Pemohon:</span>
                        <div style="color: var(--text-secondary); margin-top: 0.2rem;">{{ $pengajuan->catatan }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Berkas Dokumen Pendukung & Rekomendasi Verifikator -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header" style="background: #ffffff; padding: 1.15rem 1.25rem; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between;">
            <h4 style="margin: 0; font-size: 0.95rem; font-weight: 700; color: var(--header-navy);">
                <i class="fas fa-paperclip" style="color: var(--accent-primary); margin-right: 0.4rem;"></i>
                Dokumen Lampiran yang Telah Diverifikasi
            </h4>
            <span style="font-size: 0.8rem; color: var(--text-muted);">
                Silakan tinjau berkas digital bila diperlukan
            </span>
        </div>
        <div class="card-body" style="padding: 1.25rem;">
            @if($pengajuan->dokumen && $pengajuan->dokumen->count() > 0)
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1rem; margin-bottom: 1.25rem;">
                    @foreach($pengajuan->dokumen as $dok)
                        <div style="border: 1px solid var(--border-color); border-radius: 8px; padding: 0.85rem 1rem; display: flex; align-items: center; justify-content: space-between; background: #f8fafc;">
                            <div style="display: flex; align-items: center; gap: 0.75rem; overflow: hidden;">
                                <i class="fas fa-file-pdf" style="font-size: 1.5rem; color: #dc2626;"></i>
                                <div style="overflow: hidden;">
                                    <div style="font-weight: 700; font-size: 0.85rem; color: var(--header-navy); text-overflow: ellipsis; white-space: nowrap; overflow: hidden;">
                                        {{ $dok->nama_dokumen }}
                                    </div>
                                    <div style="font-size: 0.72rem; color: var(--text-muted);">
                                        {{ number_format(($dok->file_size ?? 0) / 1024, 0) }} KB &bull; {{ $dok->mime_type ?? 'dokumen' }}
                                    </div>
                                </div>
                            </div>
                            <a href="{{ Route::has('dokumen.preview') ? route('dokumen.preview', $dok->id_dokumen) : url('/dokumen/' . $dok->id_dokumen . '/view') }}" 
                               class="btn btn-outline btn-sm" 
                               target="_blank" 
                               style="padding: 0.3rem 0.75rem; font-size: 0.78rem; text-decoration: none; flex-shrink: 0;">
                                <i class="fas fa-eye"></i> Pratinjau
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Riwayat Verifikasi Petugas -->
            @php
                $lastVerif = $pengajuan->verifikasi ? $pengajuan->verifikasi->last() : null;
            @endphp
            @if($lastVerif)
                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 1rem; display: flex; gap: 0.75rem; align-items: flex-start;">
                    <i class="fas fa-clipboard-check text-green" style="font-size: 1.25rem; color: #16a34a; margin-top: 0.15rem;"></i>
                    <div>
                        <strong style="color: #166534; font-size: 0.9rem;">Rekomendasi Petugas Pemeriksa:</strong>
                        <div style="color: #15803d; font-size: 0.85rem; margin-top: 0.2rem;">
                            Diverifikasi oleh: <strong>{{ $lastVerif->petugas->user->nama ?? 'Petugas Desa' }}</strong> 
                            pada {{ \Carbon\Carbon::parse($lastVerif->tanggal_verifikasi ?? $lastVerif->created_at)->translatedFormat('d M Y, H:i') }}.
                            @if($lastVerif->catatan)
                                <br><em>"{{ $lastVerif->catatan }}"</em>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Panel Pengambilan Keputusan Kepala Desa (MENUNGGU_PERSETUJUAN) -->
    @if($pengajuan->status === 'MENUNGGU_PERSETUJUAN')
        <div class="card" style="margin-bottom: 2rem; border: 2px solid #7c3aed;">
            <div class="card-header" style="background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%); padding: 1.25rem 1.5rem; border-bottom: 1px solid #ddd6fe;">
                <h4 style="margin: 0; font-size: 1.1rem; font-weight: 800; color: #5b21b6;">
                    <i class="fas fa-stamp" style="margin-right: 0.4rem;"></i>
                    Keputusan Persetujuan Kepala Desa / Lurah
                </h4>
            </div>
            <div class="card-body" style="padding: 1.75rem;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
                    
                    <!-- Form Setujui (Approve) -->
                    <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: var(--radius-md); padding: 1.5rem; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem; color: #166534; font-weight: 800; font-size: 1.05rem;">
                                <i class="fas fa-circle-check"></i> SETUJUI PERMOHONAN
                            </div>
                            <p style="font-size: 0.85rem; color: #15803d; line-height: 1.5; margin-bottom: 1.25rem;">
                                Menyetujui permohonan surat ini. Status akan berubah menjadi <strong>DISETUJUI</strong> dan diteruskan ke staf untuk dicetak dengan QR Code tanda tangan digital Anda.
                            </p>
                        </div>
                        <form action="{{ Route::has('kepala.pengajuan.approve') ? route('kepala.pengajuan.approve', $pengajuan->id_pengajuan) : (Route::has('kepala.approve') ? route('kepala.approve', $pengajuan->id_pengajuan) : url('/kepala/pengajuan/' . $pengajuan->id_pengajuan . '/approve')) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success btn-lg btn-block" style="font-weight: 800; padding: 0.85rem; box-shadow: 0 4px 12px rgba(22, 163, 74, 0.25);">
                                <i class="fas fa-stamp" style="margin-right: 0.4rem;"></i> Setujui Surat Sekarang
                            </button>
                        </form>
                    </div>

                    <!-- Form Tolak (Reject) -->
                    <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: var(--radius-md); padding: 1.5rem; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem; color: #991b1b; font-weight: 800; font-size: 1.05rem;">
                                <i class="fas fa-circle-xmark"></i> TOLAK PERMOHONAN
                            </div>
                            <p style="font-size: 0.85rem; color: #b91c1c; line-height: 1.5; margin-bottom: 0.75rem;">
                                Tolak permohonan jika ditemukan ketidaksesuaian data substantif. Wajib menyertakan alasan penolakan kedinasan.
                            </p>
                        </div>
                        <form action="{{ Route::has('kepala.pengajuan.reject') ? route('kepala.pengajuan.reject', $pengajuan->id_pengajuan) : (Route::has('kepala.reject') ? route('kepala.reject', $pengajuan->id_pengajuan) : url('/kepala/pengajuan/' . $pengajuan->id_pengajuan . '/reject')) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menolak permohonan surat ini?');">
                            @csrf
                            <div class="form-group" style="margin-bottom: 0.85rem;">
                                <textarea name="alasan_penolakan" class="form-control" rows="2" placeholder="Tulis alasan penolakan secara resmi..." required style="font-size: 0.85rem; padding: 0.5rem 0.75rem;"></textarea>
                            </div>
                            <button type="submit" class="btn btn-danger btn-block" style="font-weight: 800; padding: 0.75rem;">
                                <i class="fas fa-ban" style="margin-right: 0.4rem;"></i> Tolak Pengajuan
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    @endif

</div>
@endsection
