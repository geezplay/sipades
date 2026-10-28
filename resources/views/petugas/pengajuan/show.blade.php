@extends('layouts.app')

@section('title', 'Periksa Berkas #' . ($pengajuan->nomor_tiket ?? ''))
@section('breadcrumb', 'Pemeriksaan Berkas')

@section('content')
<div class="petugas-review-page" style="max-width: 1050px; margin: 0 auto;">

    <!-- Top Action Bar -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <a href="{{ Route::has('petugas.pengajuan.index') ? route('petugas.pengajuan.index') : url('/petugas/pengajuan') }}" 
               class="btn btn-outline btn-sm" style="text-decoration: none; margin-bottom: 0.5rem; display: inline-flex; align-items: center; gap: 0.4rem;">
                <i class="fas fa-arrow-left"></i> Kembali ke Antrean
            </a>
            <h2 style="margin: 0; font-size: 1.5rem; font-weight: 800; color: var(--header-navy); display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                <span>Pemeriksaan Berkas #{{ $pengajuan->nomor_tiket }}</span>
                <x-status-badge :status="$pengajuan->status" />
            </h2>
        </div>

        @if($pengajuan->status === 'SELESAI' && $pengajuan->surat)
            <div>
                <a href="{{ Route::has('surat.download') ? route('surat.download', $pengajuan->surat->id_surat) : url('/surat/' . $pengajuan->surat->id_surat . '/download') }}" 
                   class="btn btn-success" 
                   target="_blank" 
                   style="text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; font-weight: 700;">
                    <i class="fas fa-file-pdf"></i> Unduh Surat Terbit
                </a>
            </div>
        @endif
    </div>

    <!-- Status Alert if Revisi or Ditolak -->
    @if($pengajuan->status === 'REVISI')
        <div class="alert-box alert-warning">
            <i class="fas fa-triangle-exclamation alert-icon"></i>
            <div class="alert-content">
                <strong>Status Pengajuan: REVISI</strong><br>
                Catatan revisi yang dikirimkan ke pemohon: {{ $pengajuan->catatan_revisi }}
            </div>
        </div>
    @elseif($pengajuan->status === 'DITOLAK')
        <div class="alert-box alert-danger">
            <i class="fas fa-circle-xmark alert-icon"></i>
            <div class="alert-content">
                <strong>Status Pengajuan: DITOLAK</strong><br>
                Alasan penolakan: {{ $pengajuan->alasan_penolakan }}
            </div>
        </div>
    @elseif($pengajuan->status === 'MENUNGGU_PERSETUJUAN')
        <div class="alert-box alert-info">
            <i class="fas fa-clock alert-icon"></i>
            <div class="alert-content">
                <strong>Telah Diverifikasi Petugas</strong><br>
                Berkas telah diteruskan ke Kepala Desa / Lurah untuk tahap persetujuan dan tanda tangan digital.
            </div>
        </div>
    @elseif($pengajuan->status === 'DISETUJUI')
        <div class="alert-box alert-success">
            <i class="fas fa-circle-check alert-icon"></i>
            <div class="alert-content">
                <strong>Telah Disetujui Kepala Desa!</strong><br>
                Silakan isi nomor surat resmi kedinasan dan terbitkan dokumen final dengan QR Code di panel bawah.
            </div>
        </div>
    @endif

    <!-- 2 Kolom: Data Pemohon & Data Formulir -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
        <!-- Profil Pemohon -->
        <div class="card">
            <div class="card-header" style="background: #ffffff; padding: 1.15rem 1.25rem; border-bottom: 1px solid var(--border-color);">
                <h4 style="margin: 0; font-size: 0.95rem; font-weight: 700; color: var(--header-navy);">
                    <i class="fas fa-id-card" style="color: var(--accent-primary); margin-right: 0.4rem;"></i>
                    Identitas Pemohon
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

    <!-- Berkas Lampiran Warga -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header" style="background: #ffffff; padding: 1.15rem 1.25rem; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between;">
            <h4 style="margin: 0; font-size: 0.95rem; font-weight: 700; color: var(--header-navy);">
                <i class="fas fa-paperclip" style="color: var(--accent-primary); margin-right: 0.4rem;"></i>
                Pemeriksaan Berkas Lampiran
            </h4>
            <span style="font-size: 0.8rem; color: var(--text-muted);">
                Buka dan periksa keabsahan berkas pemohon
            </span>
        </div>
        <div class="card-body" style="padding: 1.25rem;">
            @if($pengajuan->dokumen && $pengajuan->dokumen->count() > 0)
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1rem;">
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
                               class="btn btn-primary btn-sm" 
                               target="_blank" 
                               style="padding: 0.3rem 0.75rem; font-size: 0.78rem; text-decoration: none; flex-shrink: 0; font-weight: 600;">
                                <i class="fas fa-eye"></i> Periksa File
                            </a>
                        </div>
                    @endforeach
                </div>
            @else
                <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0;">Tidak ada dokumen lampiran terunggah.</p>
            @endif
        </div>
    </div>

    <!-- Tindakan Petugas: Verifikasi, Revisi, atau Tolak -->
    @if(in_array($pengajuan->status, ['DIAJUKAN', 'DIPERIKSA', 'REVISI']))
        <div class="card" style="margin-bottom: 1.5rem; border: 1px solid var(--accent-primary);">
            <div class="card-header" style="background: #eff6ff; padding: 1.15rem 1.25rem; border-bottom: 1px solid #bfdbfe;">
                <h4 style="margin: 0; font-size: 1rem; font-weight: 800; color: var(--header-navy);">
                    <i class="fas fa-gavel" style="color: var(--accent-primary); margin-right: 0.4rem;"></i>
                    Keputusan Verifikasi Berkas Petugas
                </h4>
            </div>
            <div class="card-body" style="padding: 1.5rem;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
                    
                    <!-- Opsi 1: Setujui & Teruskan ke Kepala Desa -->
                    <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: var(--radius-md); padding: 1.25rem; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem; color: #166534; font-weight: 700; font-size: 0.95rem;">
                                <i class="fas fa-circle-check"></i> Berkas Lengkap & Valid
                            </div>
                            <p style="font-size: 0.8rem; color: #15803d; line-height: 1.4; margin-bottom: 1rem;">
                                Berkas telah diverifikasi valid dan akan diteruskan ke antrean Kepala Desa untuk tanda tangan digital.
                            </p>
                        </div>
                        <form action="{{ Route::has('petugas.pengajuan.verifikasi') ? route('petugas.pengajuan.verifikasi', $pengajuan->id_pengajuan) : (Route::has('petugas.verifikasi') ? route('petugas.verifikasi', $pengajuan->id_pengajuan) : url('/petugas/pengajuan/' . $pengajuan->id_pengajuan . '/verifikasi')) }}" method="POST">
                            @csrf
                            <input type="hidden" name="hasil" value="TERVERIFIKASI">
                            <div class="form-group" style="margin-bottom: 0.75rem;">
                                <input type="text" name="catatan" class="form-control" placeholder="Catatan verifikasi (opsional)" style="font-size: 0.8rem; padding: 0.45rem 0.65rem;">
                            </div>
                            <button type="submit" class="btn btn-success btn-block" style="font-weight: 700;">
                                <i class="fas fa-check"></i> Verifikasi & Teruskan ke Kades
                            </button>
                        </form>
                    </div>

                    <!-- Opsi 2: Minta Revisi Berkas ke Pemohon -->
                    <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: var(--radius-md); padding: 1.25rem; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem; color: #92400e; font-weight: 700; font-size: 0.95rem;">
                                <i class="fas fa-triangle-exclamation"></i> Minta Perbaikan Berkas
                            </div>
                            <p style="font-size: 0.8rem; color: #b45309; line-height: 1.4; margin-bottom: 1rem;">
                                Beritahu pemohon berkas apa saja yang buram, kurang lengkap, atau perlu diunggah ulang.
                            </p>
                        </div>
                        <form action="{{ Route::has('petugas.pengajuan.revisi') ? route('petugas.pengajuan.revisi', $pengajuan->id_pengajuan) : (Route::has('petugas.revisi') ? route('petugas.revisi', $pengajuan->id_pengajuan) : url('/petugas/pengajuan/' . $pengajuan->id_pengajuan . '/revisi')) }}" method="POST">
                            @csrf
                            <input type="hidden" name="hasil" value="REVISI">
                            <div class="form-group" style="margin-bottom: 0.75rem;">
                                <textarea name="catatan_revisi" class="form-control" rows="2" placeholder="Tuliskan catatan revisi untuk pemohon..." required style="font-size: 0.8rem; padding: 0.45rem 0.65rem;"></textarea>
                            </div>
                            <button type="submit" class="btn btn-warning btn-block" style="font-weight: 700; color: #0f172a;">
                                <i class="fas fa-wrench"></i> Kembalikan untuk Revisi
                            </button>
                        </form>
                    </div>

                    <!-- Opsi 3: Tolak Pengajuan -->
                    <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: var(--radius-md); padding: 1.25rem; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem; color: #991b1b; font-weight: 700; font-size: 0.95rem;">
                                <i class="fas fa-ban"></i> Tolak Pengajuan
                            </div>
                            <p style="font-size: 0.8rem; color: #b91c1c; line-height: 1.4; margin-bottom: 1rem;">
                                Tolak jika pemohon bukan warga desa, data fiktif, atau permohonan tidak sesuai perundangan.
                            </p>
                        </div>
                        <form action="{{ Route::has('petugas.pengajuan.tolak') ? route('petugas.pengajuan.tolak', $pengajuan->id_pengajuan) : (Route::has('petugas.tolak') ? route('petugas.tolak', $pengajuan->id_pengajuan) : url('/petugas/pengajuan/' . $pengajuan->id_pengajuan . '/tolak')) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menolak pengajuan ini secara final?');">
                            @csrf
                            <input type="hidden" name="hasil" value="DITOLAK">
                            <div class="form-group" style="margin-bottom: 0.75rem;">
                                <textarea name="alasan_penolakan" class="form-control" rows="2" placeholder="Alasan penolakan resmi..." required style="font-size: 0.8rem; padding: 0.45rem 0.65rem;"></textarea>
                            </div>
                            <button type="submit" class="btn btn-danger btn-block" style="font-weight: 700;">
                                <i class="fas fa-circle-xmark"></i> Tolak Permohonan
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    @endif

    <!-- Panel Penerbitan Surat Kedinasan (Jika Status DISETUJUI atau DIPROSES) -->
    @if(in_array($pengajuan->status, ['DISETUJUI', 'DIPROSES']))
        <div class="card" style="margin-bottom: 1.5rem; border: 2px solid #16a34a;">
            <div class="card-header" style="background: #f0fdf4; padding: 1.25rem 1.5rem; border-bottom: 1px solid #bbf7d0;">
                <h4 style="margin: 0; font-size: 1.05rem; font-weight: 800; color: #166534;">
                    <i class="fas fa-print" style="margin-right: 0.4rem;"></i>
                    Penerbitan Surat Resmi Kedinasan & QR Code
                </h4>
            </div>
            <div class="card-body" style="padding: 1.5rem;">
                <p style="font-size: 0.88rem; color: #15803d; margin-bottom: 1.25rem;">
                    Pengajuan ini telah disetujui Kepala Desa. Lengkapi nomor surat kedinasan berikut untuk mengompilasi surat PDF dan menghasilkan QR Code keabsahan publik.
                </p>

                <form action="{{ Route::has('petugas.pengajuan.proses') ? route('petugas.pengajuan.proses', $pengajuan->id_pengajuan) : (Route::has('petugas.proses-surat') ? route('petugas.proses-surat', $pengajuan->id_pengajuan) : url('/petugas/pengajuan/' . $pengajuan->id_pengajuan . '/proses-surat')) }}" method="POST">
                    @csrf

                    @php
                        $romanMonths = [1=>'I', 2=>'II', 3=>'III', 4=>'IV', 5=>'V', 6=>'VI', 7=>'VII', 8=>'VIII', 9=>'IX', 10=>'X', 11=>'XI', 12=>'XII'];
                        $curMonth = $romanMonths[(int)date('n')] ?? 'X';
                        $curYear = date('Y');
                        $autoNomor = '470/' . rand(100, 999) . '/KD-SM/' . $curMonth . '/' . $curYear;
                    @endphp

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.25rem; margin-bottom: 1.25rem;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label style="display: block; font-weight: 700; font-size: 0.85rem; color: var(--header-navy); margin-bottom: 0.35rem;">
                                Nomor Surat Resmi Kedinasan <span style="color: #dc2626;">*</span>
                            </label>
                            <input type="text" name="nomor_surat" class="form-control" value="{{ old('nomor_surat', $autoNomor) }}" required style="font-family: monospace; font-weight: 600;">
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label style="display: block; font-weight: 700; font-size: 0.85rem; color: var(--header-navy); margin-bottom: 0.35rem;">
                                Tanggal Surat <span style="color: #dc2626;">*</span>
                            </label>
                            <input type="date" name="tanggal_surat" class="form-control" value="{{ old('tanggal_surat', date('Y-m-d')) }}" required>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label style="display: block; font-weight: 700; font-size: 0.85rem; color: var(--header-navy); margin-bottom: 0.35rem;">
                                Pejabat Penandatangan
                            </label>
                            @php
                                $kadesOptions = $kadesList ?? \App\Models\KepalaDesa::with('user')->where('is_active', true)->get();
                            @endphp
                            <select name="id_pimpinan" class="form-control" style="font-size: 0.85rem;">
                                @foreach($kadesOptions as $kd)
                                    <option value="{{ $kd->id_pimpinan }}">
                                        {{ $kd->user->nama ?? 'Kepala Desa' }} ({{ $kd->jabatan ?? 'Kepala Desa' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success btn-lg" style="font-weight: 700; padding: 0.75rem 2rem;">
                        <i class="fas fa-stamp" style="margin-right: 0.5rem;"></i> Kompilasi Surat PDF & Terbitkan (SELESAI)
                    </button>
                </form>
            </div>
        </div>
    @endif

    <!-- Riwayat Verifikasi Log -->
    @if($pengajuan->verifikasi && $pengajuan->verifikasi->count() > 0)
        <div class="card">
            <div class="card-header" style="background: #ffffff; padding: 1.15rem 1.25rem; border-bottom: 1px solid var(--border-color);">
                <h4 style="margin: 0; font-size: 0.95rem; font-weight: 700; color: var(--header-navy);">
                    <i class="fas fa-timeline" style="color: var(--accent-primary); margin-right: 0.4rem;"></i>
                    Riwayat Tindakan Petugas
                </h4>
            </div>
            <div class="card-body" style="padding: 1.25rem;">
                <ul style="list-style: none; padding: 0; margin: 0;">
                    @foreach($pengajuan->verifikasi as $verif)
                        <li style="padding-bottom: 1rem; margin-bottom: 1rem; border-bottom: 1px dashed var(--border-color); display: flex; justify-content: space-between; align-items: flex-start;">
                            <div>
                                <span class="badge {{ $verif->hasil === 'TERVERIFIKASI' ? 'badge-selesai' : ($verif->hasil === 'REVISI' ? 'badge-revisi' : 'badge-ditolak') }}">
                                    {{ $verif->hasil }}
                                </span>
                                <div style="font-size: 0.85rem; color: var(--header-navy); font-weight: 600; margin-top: 0.35rem;">
                                    Oleh: {{ $verif->petugas->user->nama ?? 'Petugas Pemeriksa' }}
                                </div>
                                @if($verif->catatan)
                                    <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.2rem;">
                                        Catatan: {{ $verif->catatan }}
                                    </div>
                                @endif
                            </div>
                            <span style="font-size: 0.78rem; color: var(--text-muted);">
                                {{ \Carbon\Carbon::parse($verif->tanggal_verifikasi ?? $verif->created_at)->translatedFormat('d M Y, H:i') }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

</div>
@endsection
