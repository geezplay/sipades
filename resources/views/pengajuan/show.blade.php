@extends('layouts.app')

@section('title', 'Detail Pengajuan #' . ($pengajuan->nomor_tiket ?? ''))
@section('breadcrumb', 'Detail Pengajuan')

@section('content')
<div class="pengajuan-detail-page" style="max-width: 1000px; margin: 0 auto;">

    <!-- Top Navigation & Action Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <a href="{{ url()->previous() != url()->current() ? url()->previous() : (Route::has('pengajuan.index') ? route('pengajuan.index') : url('/warga/pengajuan')) }}" 
               class="btn btn-outline btn-sm" style="text-decoration: none; margin-bottom: 0.5rem; display: inline-flex; align-items: center; gap: 0.4rem;">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <h2 style="margin: 0; font-size: 1.5rem; font-weight: 800; color: var(--header-navy); display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                <span>Pengajuan Surat #{{ $pengajuan->nomor_tiket }}</span>
                <x-status-badge :status="$pengajuan->status" />
            </h2>
        </div>

        @if($pengajuan->status === 'SELESAI' && $pengajuan->surat)
            <div>
                <a href="{{ Route::has('surat.download') ? route('surat.download', $pengajuan->surat->id_surat) : url('/surat/' . $pengajuan->surat->id_surat . '/download') }}" 
                   class="btn btn-success btn-lg" 
                   target="_blank" 
                   style="text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; font-weight: 700; box-shadow: 0 4px 14px rgba(22, 163, 74, 0.3);">
                    <i class="fas fa-file-pdf"></i> Unduh Surat Resmi PDF
                </a>
            </div>
        @endif
    </div>

    <!-- Workflow Progress Timeline -->
    @php
        $st = strtoupper($pengajuan->status);
        $step1 = true; // DIAJUKAN
        $step2 = in_array($st, ['DIPERIKSA', 'REVISI', 'DIVERIFIKASI', 'MENUNGGU_PERSETUJUAN', 'DISETUJUI', 'DIPROSES', 'SELESAI']);
        $step3 = in_array($st, ['MENUNGGU_PERSETUJUAN', 'DISETUJUI', 'DIPROSES', 'SELESAI']);
        $step4 = ($st === 'SELESAI');
        $isRejected = ($st === 'DITOLAK');
        $isRevisi = ($st === 'REVISI');
    @endphp

    <div class="card" style="margin-bottom: 1.5rem; padding: 1.5rem;">
        <h4 style="margin: 0 0 1.25rem 0; font-size: 0.95rem; font-weight: 700; color: var(--header-navy); text-transform: uppercase; letter-spacing: 0.5px;">
            <i class="fas fa-route" style="color: var(--accent-primary); margin-right: 0.4rem;"></i>
            Progres Verifikasi Dokumen
        </h4>

        <div class="workflow-steps" style="display: flex; justify-content: space-between; position: relative; margin: 1.5rem 0 1rem 0;">
            <!-- Step 1 -->
            <div class="workflow-step {{ $step1 ? 'completed' : '' }}" style="flex: 1; text-align: center; position: relative; z-index: 1;">
                <div class="workflow-step-icon" style="margin: 0 auto 0.5rem auto; width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; background: #16a34a; color: #ffffff;">
                    <i class="fas fa-check"></i>
                </div>
                <div class="workflow-step-label" style="font-size: 0.8rem; font-weight: 700; color: var(--header-navy);">1. Pengajuan Masuk</div>
                <div style="font-size: 0.72rem; color: var(--text-muted);">Terkirim ke sistem</div>
            </div>

            <!-- Step 2 -->
            <div class="workflow-step {{ $step2 ? ($isRevisi ? 'active' : 'completed') : '' }}" style="flex: 1; text-align: center; position: relative; z-index: 1;">
                <div class="workflow-step-icon" style="margin: 0 auto 0.5rem auto; width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; background: {{ $isRevisi ? '#f59e0b' : ($step2 ? '#16a34a' : '#e2e8f0') }}; color: {{ $step2 ? '#ffffff' : '#64748b' }};">
                    @if($isRevisi)
                        <i class="fas fa-triangle-exclamation"></i>
                    @elseif($step2)
                        <i class="fas fa-check"></i>
                    @else
                        2
                    @endif
                </div>
                <div class="workflow-step-label" style="font-size: 0.8rem; font-weight: 700; color: {{ $isRevisi ? '#d97706' : ($step2 ? 'var(--header-navy)' : 'var(--text-muted)') }};">
                    2. Verifikasi Petugas
                </div>
                <div style="font-size: 0.72rem; color: var(--text-muted);">
                    {{ $isRevisi ? 'Perlu Perbaikan' : ($step2 ? 'Berkas Valid' : 'Pemeriksaan Berkas') }}
                </div>
            </div>

            <!-- Step 3 -->
            <div class="workflow-step {{ $step3 ? 'completed' : ($isRejected ? 'active' : '') }}" style="flex: 1; text-align: center; position: relative; z-index: 1;">
                <div class="workflow-step-icon" style="margin: 0 auto 0.5rem auto; width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; background: {{ $isRejected ? '#dc2626' : ($step3 ? '#16a34a' : '#e2e8f0') }}; color: {{ $step3 || $isRejected ? '#ffffff' : '#64748b' }};">
                    @if($isRejected)
                        <i class="fas fa-xmark"></i>
                    @elseif($step3)
                        <i class="fas fa-check"></i>
                    @else
                        3
                    @endif
                </div>
                <div class="workflow-step-label" style="font-size: 0.8rem; font-weight: 700; color: {{ $isRejected ? '#dc2626' : ($step3 ? 'var(--header-navy)' : 'var(--text-muted)') }};">
                    3. Persetujuan Kades
                </div>
                <div style="font-size: 0.72rem; color: var(--text-muted);">
                    {{ $isRejected ? 'Pengajuan Ditolak' : ($step3 ? 'Disetujui Pejabat' : 'Menunggu TTD') }}
                </div>
            </div>

            <!-- Step 4 -->
            <div class="workflow-step {{ $step4 ? 'completed' : '' }}" style="flex: 1; text-align: center; position: relative; z-index: 1;">
                <div class="workflow-step-icon" style="margin: 0 auto 0.5rem auto; width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; background: {{ $step4 ? '#16a34a' : '#e2e8f0' }}; color: {{ $step4 ? '#ffffff' : '#64748b' }};">
                    @if($step4)
                        <i class="fas fa-check-double"></i>
                    @else
                        4
                    @endif
                </div>
                <div class="workflow-step-label" style="font-size: 0.8rem; font-weight: 700; color: {{ $step4 ? 'var(--header-navy)' : 'var(--text-muted)' }};">
                    4. Penerbitan Surat
                </div>
                <div style="font-size: 0.72rem; color: var(--text-muted);">
                    {{ $step4 ? 'Siap Diunduh' : 'Nomor & QR Code' }}
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Revisi Section -->
    @if($pengajuan->status === 'REVISI')
        <div class="card" id="revisiSection" style="margin-bottom: 1.5rem; border: 2px solid #f59e0b; background: #fffbeb;">
            <div class="card-body" style="padding: 1.5rem;">
                <div style="display: flex; gap: 1rem; align-items: flex-start;">
                    <div style="font-size: 1.75rem; color: #d97706;">
                        <i class="fas fa-triangle-exclamation"></i>
                    </div>
                    <div style="flex: 1;">
                        <h4 style="margin: 0 0 0.5rem 0; color: #92400e; font-weight: 800;">Pengajuan Perlu Dilengkapi / Diperbaiki</h4>
                        <p style="margin: 0 0 1rem 0; color: #b45309; font-size: 0.9rem;">
                            <strong>Catatan Petugas:</strong> {{ $pengajuan->catatan_revisi ?: 'Terdapat berkas yang kurang jelas atau tidak sesuai. Silakan unggah perbaikan berkas di bawah.' }}
                        </p>

                        <!-- Form Resubmit / Perbaikan Revisi -->
                        <form action="{{ Route::has('pengajuan.resubmit') ? route('pengajuan.resubmit', $pengajuan->id_pengajuan) : (Route::has('warga.pengajuan.resubmit') ? route('warga.pengajuan.resubmit', $pengajuan->id_pengajuan) : url('/warga/pengajuan/' . $pengajuan->id_pengajuan . '/revisi')) }}" 
                              method="POST" 
                              enctype="multipart/form-data">
                            @csrf
                            <div style="background: #ffffff; padding: 1.25rem; border-radius: 8px; border: 1px solid #fde68a;">
                                <label style="display: block; font-weight: 700; font-size: 0.88rem; color: var(--header-navy); margin-bottom: 0.4rem;">
                                    Unggah Berkas Perbaikan Baru (PDF, JPG, PNG):
                                </label>
                                <input type="file" name="dokumen_revisi" class="form-control" required style="margin-bottom: 0.75rem; font-size: 0.85rem;">

                                <label style="display: block; font-weight: 700; font-size: 0.88rem; color: var(--header-navy); margin-bottom: 0.4rem;">
                                    Pesan / Penjelasan Perbaikan untuk Petugas:
                                </label>
                                <textarea name="catatan_pemohon" class="form-control" rows="2" placeholder="Contoh: Berkas KTP telah saya pindai ulang dengan resolusi lebih tajam..." style="margin-bottom: 1rem; font-size: 0.85rem;"></textarea>

                                <button type="submit" class="btn btn-warning" style="font-weight: 700;">
                                    <i class="fas fa-paper-plane"></i> Kirim Ulang Berkas Revisi
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Alert Ditolak Section -->
    @if($pengajuan->status === 'DITOLAK')
        <div class="card" style="margin-bottom: 1.5rem; border: 2px solid #dc2626; background: #fef2f2;">
            <div class="card-body" style="padding: 1.5rem; display: flex; gap: 1rem; align-items: flex-start;">
                <div style="font-size: 1.75rem; color: #dc2626;">
                    <i class="fas fa-circle-xmark"></i>
                </div>
                <div>
                    <h4 style="margin: 0 0 0.4rem 0; color: #991b1b; font-weight: 800;">Permohonan Surat Ditolak</h4>
                    <p style="margin: 0; color: #b91c1c; font-size: 0.9rem;">
                        <strong>Alasan Penolakan:</strong> {{ $pengajuan->alasan_penolakan ?: 'Pengajuan tidak memenuhi ketentuan peraturan administrasi desa.' }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- 2 Column Details: Data Pemohon & Data Formulir -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
        <!-- Data Pemohon -->
        <div class="card">
            <div class="card-header" style="background: #ffffff; padding: 1.15rem 1.25rem; border-bottom: 1px solid var(--border-color);">
                <h4 style="margin: 0; font-size: 0.95rem; font-weight: 700; color: var(--header-navy);">
                    <i class="fas fa-user" style="color: var(--accent-primary); margin-right: 0.4rem;"></i>
                    Data Pemohon
                </h4>
            </div>
            <div class="card-body" style="padding: 1.25rem;">
                <table style="width: 100%; font-size: 0.88rem; line-height: 2;">
                    <tr>
                        <td style="width: 110px; color: var(--text-muted);">Nama Lengkap</td>
                        <td style="font-weight: 700; color: var(--header-navy);">: {{ $pengajuan->masyarakat->user->nama ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="color: var(--text-muted);">NIK</td>
                        <td style="font-family: monospace; font-weight: 600;">: {{ $pengajuan->masyarakat->nik ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="color: var(--text-muted);">Email</td>
                        <td>: {{ $pengajuan->masyarakat->user->email ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="color: var(--text-muted);">No. HP / WA</td>
                        <td>: {{ $pengajuan->masyarakat->no_hp ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="color: var(--text-muted); vertical-align: top;">Alamat</td>
                        <td style="vertical-align: top;">: {{ $pengajuan->masyarakat->alamat ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Data Formulir Layanan -->
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
                    <p style="color: var(--text-muted); font-size: 0.88rem; margin: 0;">Tidak ada data formulir khusus.</p>
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
                        <span style="color: var(--text-muted); font-weight: 600;">Catatan Pemohon:</span>
                        <div style="color: var(--text-secondary); margin-top: 0.2rem;">{{ $pengajuan->catatan }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Lampiran Dokumen Warga -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header" style="background: #ffffff; padding: 1.15rem 1.25rem; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between;">
            <h4 style="margin: 0; font-size: 0.95rem; font-weight: 700; color: var(--header-navy);">
                <i class="fas fa-paperclip" style="color: var(--accent-primary); margin-right: 0.4rem;"></i>
                Berkas Lampiran Persyaratan
            </h4>
            <span style="font-size: 0.8rem; color: var(--text-muted);">
                Tersimpan di secure private storage
            </span>
        </div>
        <div class="card-body" style="padding: 1.25rem;">
            @if($pengajuan->dokumen && $pengajuan->dokumen->count() > 0)
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 1rem;">
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
                               title="Lihat Pratinjau Dokumen" 
                               style="padding: 0.3rem 0.6rem; font-size: 0.75rem; text-decoration: none; flex-shrink: 0;">
                                <i class="fas fa-arrow-up-right-from-square"></i> Buka
                            </a>
                        </div>
                    @endforeach
                </div>
            @else
                <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0;">Tidak ada dokumen lampiran terunggah.</p>
            @endif
        </div>
    </div>

    <!-- Informasi Surat Resmi Terbit (Jika SELESAI) -->
    @if($pengajuan->status === 'SELESAI' && $pengajuan->surat)
        <div class="card" style="border: 2px solid #16a34a; background: #f0fdf4;">
            <div class="card-body" style="padding: 1.5rem; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1.5rem;">
                <div>
                    <div style="display: flex; align-items: center; gap: 0.5rem; color: #166534; font-weight: 800; font-size: 1.1rem; margin-bottom: 0.35rem;">
                        <i class="fas fa-certificate text-green"></i> Surat Keterangan Resmi Telah Diterbitkan
                    </div>
                    <div style="font-size: 0.9rem; color: #15803d;">
                        Nomor Surat: <strong style="font-family: monospace;">{{ $pengajuan->surat->nomor_surat }}</strong><br>
                        Tanggal Terbit: {{ \Carbon\Carbon::parse($pengajuan->surat->tanggal_surat)->translatedFormat('d F Y') }}
                    </div>
                    @if($pengajuan->surat->qr_token)
                        <div style="margin-top: 0.5rem; font-size: 0.8rem; color: #166534;">
                            <i class="fas fa-qrcode"></i> Token QR Verifikasi Keabsahan: 
                            <a href="{{ url('/verifikasi-surat/' . $pengajuan->surat->qr_token) }}" target="_blank" style="color: #15803d; font-weight: 700; text-decoration: underline;">
                                Cek Keabsahan Publik
                            </a>
                        </div>
                    @endif
                </div>

                <a href="{{ Route::has('surat.download') ? route('surat.download', $pengajuan->surat->id_surat) : url('/surat/' . $pengajuan->surat->id_surat . '/download') }}" 
                   class="btn btn-success btn-lg" 
                   target="_blank" 
                   style="font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-file-arrow-down"></i> Unduh File PDF
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
