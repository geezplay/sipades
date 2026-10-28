<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isValid ? 'Verifikasi Dokumen Sah' : 'Verifikasi Gagal' }} - SIPADES</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --gov-navy: #0f2b48;
            --gov-navy-light: #163e66;
            --gov-accent: #1e6091;
            --gov-accent-hover: #174e77;
            --gov-gold: #d97706;
            --gov-gold-light: #fef3c7;
            --gov-success: #059669;
            --gov-success-light: #ecfdf5;
            --gov-danger: #dc2626;
            --gov-danger-light: #fef2f2;
            --bg-body: #f4f6f9;
            --card-bg: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --radius-md: 10px;
            --radius-lg: 16px;
            --shadow-sm: 0 1px 3px rgba(15, 41, 66, 0.08);
            --shadow-lg: 0 10px 25px -5px rgba(15, 41, 66, 0.12), 0 8px 10px -6px rgba(15, 41, 66, 0.08);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            line-height: 1.5;
        }

        /* Top Government Bar */
        .gov-topbar {
            background-color: var(--gov-navy);
            color: #ffffff;
            padding: 12px 20px;
            box-shadow: 0 2px 8px rgba(15, 41, 66, 0.15);
        }

        .gov-topbar-inner {
            max-width: 900px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
        }

        .gov-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: #ffffff;
        }

        .gov-logo-badge {
            width: 42px;
            height: 42px;
            background: rgba(255, 255, 255, 0.1);
            border: 1.5px solid rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .gov-logo-badge svg {
            width: 26px;
            height: 26px;
            fill: #ffffff;
        }

        .gov-title-wrap h1 {
            font-size: 1.05rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .gov-title-wrap p {
            font-size: 0.75rem;
            color: #cbd5e1;
        }

        .gov-system-tag {
            background-color: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.25);
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        /* Main Container */
        .main-container {
            max-width: 760px;
            width: 100%;
            margin: 2rem auto;
            padding: 0 16px;
            flex: 1;
        }

        /* Card */
        .verify-card {
            background: var(--card-bg);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
        }

        /* Status Header */
        .status-banner {
            padding: 28px 24px;
            text-align: center;
            border-bottom: 1px solid var(--border-color);
        }

        .status-banner.valid {
            background: linear-gradient(180deg, #ecfdf5 0%, #ffffff 100%);
            border-top: 5px solid var(--gov-success);
        }

        .status-banner.invalid {
            background: linear-gradient(180deg, #fef2f2 0%, #ffffff 100%);
            border-top: 5px solid var(--gov-danger);
        }

        .status-icon-wrap {
            width: 72px;
            height: 72px;
            margin: 0 auto 16px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .valid .status-icon-wrap {
            background-color: #d1fae5;
            color: var(--gov-success);
            box-shadow: 0 0 0 8px rgba(5, 150, 105, 0.12);
        }

        .invalid .status-icon-wrap {
            background-color: #fee2e2;
            color: var(--gov-danger);
            box-shadow: 0 0 0 8px rgba(220, 38, 38, 0.12);
        }

        .status-icon-wrap svg {
            width: 38px;
            height: 38px;
        }

        .status-title {
            font-size: 1.35rem;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .valid .status-title {
            color: #065f46;
        }

        .invalid .status-title {
            color: #991b1b;
        }

        .status-subtitle {
            font-size: 0.9rem;
            color: var(--text-muted);
            max-width: 520px;
            margin: 0 auto;
        }

        /* Certificate Details Section */
        .details-section {
            padding: 24px;
        }

        .section-headline {
            font-size: 0.8rem;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.8px;
            color: var(--gov-accent);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-headline::after {
            content: '';
            flex: 1;
            height: 1px;
            background-color: var(--border-color);
        }

        .data-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        @media (max-width: 600px) {
            .data-grid {
                grid-template-columns: 1fr;
            }
        }

        .data-item {
            background-color: #f8fafc;
            border: 1px solid #f1f5f9;
            border-radius: var(--radius-md);
            padding: 12px 16px;
        }

        .data-item.full-width {
            grid-column: 1 / -1;
        }

        .data-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
            margin-bottom: 4px;
            font-weight: 600;
        }

        .data-value {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-main);
            word-break: break-word;
        }

        .data-value.highlight {
            color: var(--gov-navy);
            font-size: 1.05rem;
        }

        /* Badge Status Sah */
        .badge-sah {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background-color: #d1fae5;
            color: #065f46;
            font-size: 0.8rem;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 9999px;
            border: 1px solid #a7f3d0;
        }

        .badge-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #059669;
        }

        /* Penandatangan Box */
        .signer-box {
            background: linear-gradient(135deg, #0f2b48 0%, #1e6091 100%);
            color: #ffffff;
            border-radius: var(--radius-md);
            padding: 18px 20px;
            margin-top: 10px;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .signer-avatar {
            width: 52px;
            height: 52px;
            background-color: rgba(255, 255, 255, 0.15);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .signer-avatar svg {
            width: 26px;
            height: 26px;
            fill: #ffffff;
        }

        .signer-meta {
            flex: 1;
        }

        .signer-role {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #cbd5e1;
        }

        .signer-name {
            font-size: 1.05rem;
            font-weight: 700;
            color: #ffffff;
            margin: 2px 0;
        }

        .signer-nip {
            font-size: 0.8rem;
            color: #93c5fd;
        }

        /* Hash Token Box */
        .token-box {
            background-color: #f1f5f9;
            border-radius: var(--radius-md);
            padding: 10px 14px;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.75rem;
            color: #475569;
            word-break: break-all;
            margin-top: 20px;
        }

        /* Security Assurance Note */
        .security-note {
            background-color: var(--gov-gold-light);
            border-left: 4px solid var(--gov-gold);
            padding: 12px 16px;
            border-radius: 4px;
            margin-top: 20px;
            font-size: 0.8rem;
            color: #92400e;
            line-height: 1.5;
        }

        /* Footer */
        .gov-footer {
            text-align: center;
            padding: 24px 16px;
            color: var(--text-muted);
            font-size: 0.8rem;
            margin-top: auto;
        }

        .gov-footer a {
            color: var(--gov-accent);
            text-decoration: none;
            font-weight: 500;
        }

        .gov-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <!-- TOP GOVERNMENT HEADER -->
    <header class="gov-topbar">
        <div class="gov-topbar-inner">
            <a href="/" class="gov-brand">
                <div class="gov-logo-badge">
                    <!-- Government Landmark Emblem -->
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2L2 7v2h20V7L12 2zm-8 8v9h3v-9H4zm6 0v9h4v-9h-4zm7 0v9h3v-9h-3zM2 20v2h20v-2H2z"/>
                    </svg>
                </div>
                <div class="gov-title-wrap">
                    <h1>SIPADES</h1>
                    <p>Pemerintah Desa Sukamakmur &bull; Kec. Sukamaju, Kab. Bogor</p>
                </div>
            </a>
            <div class="gov-system-tag">
                PORTAL VERIFIKASI KEABSAHAN
            </div>
        </div>
    </header>

    <!-- CONTENT WRAPPER -->
    <main class="main-container">
        <div class="verify-card">
            @if ($isValid && $surat)
                @php
                    $pengajuan = $surat->pengajuan;
                    $masyarakat = $pengajuan?->masyarakat;
                    $userPemohon = $masyarakat?->user;
                    $pelayanan = $pengajuan?->jenisPelayanan;
                    $kades = $surat->kepalaDesa;
                    $kadesUser = $kades?->user;

                    // Form Data Helper
                    $formData = is_array($pengajuan?->data_formulir)
                        ? $pengajuan->data_formulir
                        : json_decode($pengajuan?->data_formulir ?: '[]', true);

                    $namaPemohon = $formData['nama_lengkap'] ?? $formData['nama'] ?? $userPemohon?->nama ?? '-';
                    $rawNik = $formData['nik'] ?? $masyarakat?->nik ?? '';
                    $maskedNik = \App\Http\Controllers\VerifikasiSuratController::maskNik($rawNik);
                @endphp

                <!-- VALID STATUS BANNER -->
                <div class="status-banner valid">
                    <div class="status-icon-wrap">
                        <!-- Shield Check SVG -->
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            <polyline points="9 11 12 14 22 4"/>
                        </svg>
                    </div>
                    <h2 class="status-title">DOKUMEN ASLI DAN TERDAFTAR</h2>
                    <p class="status-subtitle">
                        Dokumen kedinasan ini telah diverifikasi sah dan tercatat secara resmi di dalam pangkalan data Sistem Informasi Pelayanan Administrasi Desa (SIPADES).
                    </p>
                </div>

                <!-- DETAILS SECTION -->
                <div class="details-section">
                    <div class="section-headline">
                        Informasi Dokumen Resmi
                    </div>

                    <div class="data-grid">
                        <div class="data-item">
                            <div class="data-label">Nomor Surat</div>
                            <div class="data-value highlight">{{ $surat->nomor_surat }}</div>
                        </div>

                        <div class="data-item">
                            <div class="data-label">Status Dokumen</div>
                            <div class="data-value">
                                <span class="badge-sah">
                                    <span class="badge-dot"></span>
                                    Aktif & Sah
                                </span>
                            </div>
                        </div>

                        <div class="data-item full-width">
                            <div class="data-label">Perihal / Jenis Layanan</div>
                            <div class="data-value">{{ $pelayanan?->nama_pelayanan ?? 'Surat Keterangan Administrasi Desa' }}</div>
                        </div>

                        <div class="data-item">
                            <div class="data-label">Tanggal Terbit Surat</div>
                            <div class="data-value">{{ \Carbon\Carbon::parse($surat->tanggal_surat)->translatedFormat('d F Y') }}</div>
                        </div>

                        <div class="data-item">
                            <div class="data-label">Nomor Tiket Pengajuan</div>
                            <div class="data-value">{{ $pengajuan?->nomor_tiket ?? '-' }}</div>
                        </div>

                        <div class="data-item">
                            <div class="data-label">Nama Pemohon</div>
                            <div class="data-value">{{ strtoupper($namaPemohon) }}</div>
                        </div>

                        <div class="data-item">
                            <div class="data-label">NIK Pemohon (Tersensor)</div>
                            <div class="data-value">{{ $maskedNik }}</div>
                        </div>
                    </div>

                    <!-- PENANDATANGAN DIGITAL RESMI -->
                    <div class="section-headline">
                        Pejabat Penandatangan
                    </div>

                    <div class="signer-box">
                        <div class="signer-avatar">
                            <!-- Official Pen / Stamp SVG -->
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
                            </svg>
                        </div>
                        <div class="signer-meta">
                            <div class="signer-role">{{ strtoupper($kades?->jabatan ?? 'Kepala Desa Sukamakmur') }}</div>
                            <div class="signer-name">{{ $kadesUser?->nama ?? 'H. Muhammad Rizki, S.Sos' }}</div>
                            <div class="signer-nip">NIP: {{ $kades?->nip ?? '19750812 200212 1 003' }}</div>
                        </div>
                    </div>

                    <!-- SECURITY TOKEN HASH -->
                    <div class="token-box">
                        <strong>Security Token (SHA-256):</strong><br>
                        {{ $surat->qr_token }}
                    </div>

                    <div class="security-note">
                        <strong>Peringatan Integritas:</strong> Halaman verifikasi publik ini dienkripsi dan dijamin keasliannya oleh Pemerintah Desa Sukamakmur. Data yang tertera di atas dicocokkan langsung dengan basis data pusat SIPADES. Apabila isi fisik dokumen berbeda dengan verifikasi ini, maka dokumen fisik dinyatakan tidak sah / palsu.
                    </div>
                </div>

            @else
                <!-- INVALID STATUS BANNER -->
                <div class="status-banner invalid">
                    <div class="status-icon-wrap">
                        <!-- Shield Alert SVG -->
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            <line x1="12" y1="8" x2="12" y2="12"/>
                            <line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                    </div>
                    <h2 class="status-title">DOKUMEN TIDAK VALID / TIDAK DITEMUKAN</h2>
                    <p class="status-subtitle">
                        Dokumen dengan token verifikasi tersebut tidak terdaftar di dalam sistem SIPADES. Pastikan Anda memindai kode QR resmi yang tercetak pada dokumen asli.
                    </p>
                </div>

                <div class="details-section">
                    <div class="data-item full-width" style="background-color: #fef2f2; border-color: #fecaca;">
                        <div class="data-label" style="color: #991b1b;">Token Diperiksa</div>
                        <div class="data-value" style="font-family: monospace; font-size: 0.85rem; color: #7f1d1d;">
                            {{ $token ?? '-' }}
                        </div>
                    </div>

                    <div class="security-note" style="background-color: #fef2f2; border-left-color: #dc2626; color: #991b1b; margin-top: 16px;">
                        <strong>Kemungkinan Penyebab:</strong>
                        <ul style="margin: 8px 0 0 20px; line-height: 1.6;">
                            <li>QR Code rusak, buram, atau tidak terpindai secara sempurna.</li>
                            <li>Tautan verifikasi telah diubah atau dipalsukan.</li>
                            <li>Dokumen belum atau tidak diterbitkan secara resmi oleh pihak Desa Sukamakmur.</li>
                        </ul>
                    </div>

                    <div style="text-align: center; margin-top: 24px;">
                        <a href="/" style="display: inline-block; background-color: var(--gov-navy); color: #ffffff; padding: 10px 20px; border-radius: var(--radius-md); text-decoration: none; font-weight: 600; font-size: 0.85rem;">
                            Kembali ke Beranda SIPADES
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </main>

    <!-- FOOTER -->
    <footer class="gov-footer">
        <p>&copy; {{ date('Y') }} Pemerintah Desa Sukamakmur &bull; Kecamatan Sukamaju, Kabupaten Bogor</p>
        <p style="margin-top: 4px;">Sistem Informasi Pelayanan Administrasi Desa (SIPADES) &bull; Pelayanan Publik Transparan & Mandiri</p>
    </footer>

</body>
</html>
