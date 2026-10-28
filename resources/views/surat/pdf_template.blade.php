<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $pelayanan?->nama_pelayanan ?? 'Surat Keterangan' }} - {{ $nomorSurat }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 1.5cm 2cm 2cm 2cm;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.45;
            color: #111827;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
        }

        /* Kop Surat Kedinasan */
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2px;
        }

        .kop-logo-td {
            width: 85px;
            vertical-align: middle;
            text-align: center;
        }

        .kop-text-td {
            text-align: center;
            vertical-align: middle;
            padding-right: 40px;
        }

        .kop-instansi {
            font-size: 13pt;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin: 0;
            line-height: 1.2;
        }

        .kop-desa {
            font-size: 16pt;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin: 2px 0 3px 0;
            line-height: 1.2;
        }

        .kop-kontak {
            font-size: 8.5pt;
            font-style: italic;
            margin: 0;
            line-height: 1.3;
            color: #333333;
        }

        .kop-line-thick {
            border-top: 3px solid #000000;
            margin-top: 6px;
            margin-bottom: 2px;
        }

        .kop-line-thin {
            border-top: 1px solid #000000;
            margin-bottom: 18px;
        }

        /* Judul Surat */
        .surat-header {
            text-align: center;
            margin-bottom: 18px;
        }

        .surat-judul {
            font-size: 13pt;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
            margin: 0 0 4px 0;
            letter-spacing: 0.5px;
        }

        .surat-nomor {
            font-size: 11pt;
            margin: 0;
            font-weight: normal;
        }

        /* Paragraf Pembuka & Penutup */
        .surat-pembuka, .surat-penutup {
            text-align: justify;
            text-indent: 36px;
            margin-bottom: 12px;
            line-height: 1.5;
        }

        /* Tabel Biodata */
        .biodata-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0 14px 20px;
        }

        .biodata-table td {
            padding: 3px 0;
            vertical-align: top;
            font-size: 11.5pt;
        }

        .col-label {
            width: 175px;
        }

        .col-separator {
            width: 15px;
            text-align: center;
        }

        .col-value {
            font-weight: 500;
        }

        /* Bagian Konten Khusus / Pernyataan */
        .surat-keterangan {
            text-align: justify;
            text-indent: 36px;
            margin: 12px 0;
            line-height: 1.5;
        }

        .highlight-box {
            background-color: #f8fafc;
            border-left: 3px solid #0f2b48;
            padding: 8px 12px;
            margin: 10px 0 10px 36px;
            font-size: 11pt;
        }

        /* Tanda Tangan & QR Code */
        .ttd-container {
            width: 100%;
            margin-top: 25px;
            page-break-inside: avoid;
        }

        .ttd-table {
            width: 100%;
            border-collapse: collapse;
        }

        .ttd-left {
            width: 50%;
            vertical-align: top;
            padding-right: 15px;
        }

        .ttd-right {
            width: 50%;
            vertical-align: top;
            text-align: center;
        }

        /* Kotak Validasi QR */
        .qr-badge-box {
            border: 1px dashed #94a3b8;
            border-radius: 4px;
            padding: 8px;
            background-color: #f8fafc;
            display: inline-block;
            text-align: center;
        }

        .qr-badge-box img {
            width: 80px;
            height: 80px;
            display: block;
            margin: 0 auto;
        }

        .qr-caption {
            font-size: 7.5pt;
            color: #475569;
            margin-top: 4px;
            line-height: 1.2;
            max-width: 180px;
        }

        .ttd-pejabat-title {
            font-size: 11.5pt;
            margin: 0 0 10px 0;
        }

        .ttd-spasi {
            height: 60px;
        }

        .ttd-nama {
            font-size: 11.5pt;
            font-weight: bold;
            text-decoration: underline;
            margin: 0;
        }

        .ttd-nip {
            font-size: 10.5pt;
            margin: 2px 0 0 0;
        }

        /* Footer Kemanan Dokumen */
        .dokumen-footer {
            margin-top: 25px;
            padding-top: 6px;
            border-top: 0.5px solid #cbd5e1;
            font-size: 7.5pt;
            color: #64748b;
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>
<body>

    <!-- KOP SURAT RESMI KEDINASAN -->
    <table class="kop-table">
        <tr>
            <td class="kop-logo-td">
                <!-- Garuda / Lambang Daerah Kedinasan SVG -->
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="75" height="75">
                    <circle cx="50" cy="50" r="46" fill="#0f2b48" stroke="#1e6091" stroke-width="2"/>
                    <circle cx="50" cy="50" r="38" fill="none" stroke="#e2e8f0" stroke-width="1" stroke-dasharray="2,2"/>
                    <!-- Perisai Bintang -->
                    <polygon points="50,22 58,38 76,38 61,49 67,65 50,54 33,65 39,49 24,38 42,38" fill="#f59e0b"/>
                    <path d="M 30,72 Q 50,82 70,72 L 67,78 Q 50,86 33,78 Z" fill="#ffffff"/>
                    <text x="50" y="77" font-size="5" font-family="sans-serif" font-weight="bold" fill="#0f2b48" text-anchor="middle">SUKAMAKMUR</text>
                </svg>
            </td>
            <td class="kop-text-td">
                <div class="kop-instansi">PEMERINTAH KABUPATEN BOGOR</div>
                <div class="kop-instansi">KECAMATAN SUKAMAJU</div>
                <div class="kop-desa">DESA SUKAMAKMUR</div>
                <div class="kop-kontak">
                    Jl. Raya Sukamakmur No. 45, Kecamatan Sukamaju, Kabupaten Bogor - 16830<br>
                    Telepon: (021) 879-12345 &bull; Pos-el: info@sukamakmur.desa.id &bull; Laman: www.sukamakmur.desa.id
                </div>
            </td>
        </tr>
    </table>
    <div class="kop-line-thick"></div>
    <div class="kop-line-thin"></div>

    <!-- JUDUL SURAT & NOMOR REGISTER -->
    <div class="surat-header">
        <div class="surat-judul">{{ $pelayanan?->nama_pelayanan ?? 'SURAT KETERANGAN' }}</div>
        <div class="surat-nomor">Nomor : {{ $nomorSurat }}</div>
    </div>

    <!-- KALIMAT PEMBUKA -->
    <div class="surat-pembuka">
        Yang bertanda tangan di bawah ini Kepala Desa Sukamakmur, Kecamatan Sukamaju, Kabupaten Bogor, Provinsi Jawa Barat, menerangkan dengan sebenarnya bahwa:
    </div>

    <!-- TABEL BIODATA PEMOHON -->
    @php
        $namaLengkap = $formData['nama_lengkap'] ?? $formData['nama'] ?? $user?->nama ?? $masyarakat?->user?->nama ?? '-';
        $nik = $formData['nik'] ?? $masyarakat?->nik ?? '-';
        $tempatLahir = $formData['tempat_lahir'] ?? '-';
        $tanggalLahir = !empty($formData['tanggal_lahir']) ? \Carbon\Carbon::parse($formData['tanggal_lahir'])->translatedFormat('d F Y') : '-';
        $jenisKelamin = $formData['jenis_kelamin'] ?? '-';
        $agama = $formData['agama'] ?? '-';
        $pekerjaan = $formData['pekerjaan'] ?? '-';
        $alamat = $formData['alamat_lengkap'] ?? $formData['alamat'] ?? $masyarakat?->alamat ?? '-';
        $idPelayanan = $pelayanan?->id_pelayanan ?? '';
    @endphp

    <table class="biodata-table">
        <tr>
            <td class="col-label">Nama Lengkap</td>
            <td class="col-separator">:</td>
            <td class="col-value"><strong>{{ strtoupper($namaLengkap) }}</strong></td>
        </tr>
        <tr>
            <td class="col-label">NIK</td>
            <td class="col-separator">:</td>
            <td class="col-value">{{ $nik }}</td>
        </tr>
        <tr>
            <td class="col-label">Tempat / Tanggal Lahir</td>
            <td class="col-separator">:</td>
            <td class="col-value">{{ $tempatLahir }}, {{ $tanggalLahir }}</td>
        </tr>
        <tr>
            <td class="col-label">Jenis Kelamin</td>
            <td class="col-separator">:</td>
            <td class="col-value">{{ $jenisKelamin }}</td>
        </tr>
        <tr>
            <td class="col-label">Agama</td>
            <td class="col-separator">:</td>
            <td class="col-value">{{ $agama }}</td>
        </tr>
        <tr>
            <td class="col-label">Pekerjaan</td>
            <td class="col-separator">:</td>
            <td class="col-value">{{ $pekerjaan }}</td>
        </tr>
        <tr>
            <td class="col-label">Alamat / Tempat Tinggal</td>
            <td class="col-separator">:</td>
            <td class="col-value">{{ $alamat }}</td>
        </tr>
    </table>

    <!-- ISI KETERANGAN BERDASARKAN JENIS PELAYANAN -->
    @if ($idPelayanan === 'ply_domisili')
        <div class="surat-keterangan">
            Bahwa nama yang bersangkutan di atas adalah benar-benar penduduk yang berdomisili dan bertempat tinggal pada alamat tersebut di atas di wilayah RT/RW setempat, Desa Sukamakmur, Kecamatan Sukamaju, Kabupaten Bogor.
        </div>
        @if (!empty($formData['keperluan']))
            <div class="highlight-box">
                <strong>Tujuan / Keperluan:</strong> {{ $formData['keperluan'] }}
            </div>
        @endif
        <div class="surat-keterangan">
            Surat keterangan ini diberikan kepada yang bersangkutan untuk dipergunakan sebagai kelengkapan administrasi domisili sesuai peruntukannya.
        </div>

    @elseif ($idPelayanan === 'ply_skck')
        <div class="surat-keterangan">
            Bahwa orang tersebut di atas adalah benar-benar warga penduduk Desa Sukamakmur yang beradat istiadat serta berkelakuan baik dalam kehidupan bermasyarakat, serta tidak sedang tersangkut perkara kriminal/pidana ataupun pelanggaran hukum lainnya.
        </div>
        @if (!empty($formData['keperluan']))
            <div class="highlight-box">
                <strong>Keperluan Pengantar SKCK:</strong> {{ $formData['keperluan'] }}
            </div>
        @endif
        <div class="surat-keterangan">
            Surat pengantar ini dibuat sebagai kelengkapan permohonan penerbitan <em>Surat Keterangan Catatan Kepolisian (SKCK)</em> pada Kepolisian Sektor (Polsek) setempat.
        </div>

    @elseif ($idPelayanan === 'ply_usaha')
        <div class="surat-keterangan">
            Bahwa nama tersebut di atas adalah benar-benar penduduk Desa Sukamakmur yang memiliki dan mengelola kegiatan usaha mandiri dengan rincian sebagai berikut:
        </div>
        <table class="biodata-table" style="margin-left: 36px;">
            <tr>
                <td style="width: 150px;">Nama Usaha</td>
                <td style="width: 15px; text-align: center;">:</td>
                <td><strong>{{ $formData['nama_usaha'] ?? '-' }}</strong></td>
            </tr>
            <tr>
                <td>Bidang / Jenis Usaha</td>
                <td style="text-align: center;">:</td>
                <td>{{ $formData['bidang_usaha'] ?? '-' }}</td>
            </tr>
            <tr>
                <td>Alamat Lokasi Usaha</td>
                <td style="text-align: center;">:</td>
                <td>{{ $formData['alamat_usaha'] ?? $alamat }}</td>
            </tr>
            @if (!empty($formData['tahun_berdiri']))
            <tr>
                <td>Mulai Beroperasi Sejak</td>
                <td style="text-align: center;">:</td>
                <td>Tahun {{ $formData['tahun_berdiri'] }}</td>
            </tr>
            @endif
        </table>
        @if (!empty($formData['keperluan']))
            <div class="highlight-box">
                <strong>Keperluan Surat:</strong> {{ $formData['keperluan'] }}
            </div>
        @endif

    @elseif ($idPelayanan === 'ply_belum_menikah')
        <div class="surat-keterangan">
            Berdasarkan pencatatan administrasi kependudukan dan penelaahan kami di lapangan, nama tersebut di atas sampai dengan saat dikeluarkannya surat keterangan ini <strong>belum pernah menikah / berstatus Belum Kawin</strong> dengan siapapun.
        </div>
        @if (!empty($formData['keperluan']))
            <div class="highlight-box">
                <strong>Maksud / Keperluan:</strong> {{ $formData['keperluan'] }}
            </div>
        @endif

    @elseif ($idPelayanan === 'ply_kelahiran')
        <div class="surat-keterangan">
            Menerangkan bahwa di Desa Sukamakmur telah lahir seorang anak dengan data kelahiran sebagai berikut:
        </div>
        <table class="biodata-table" style="margin-left: 36px;">
            <tr>
                <td style="width: 150px;">Nama Anak</td>
                <td style="width: 15px; text-align: center;">:</td>
                <td><strong>{{ strtoupper($formData['nama_anak'] ?? '-') }}</strong></td>
            </tr>
            <tr>
                <td>Jenis Kelamin</td>
                <td style="text-align: center;">:</td>
                <td>{{ $formData['jenis_kelamin_anak'] ?? '-' }}</td>
            </tr>
            <tr>
                <td>Tempat / Tanggal Lahir</td>
                <td style="text-align: center;">:</td>
                <td>{{ $formData['tempat_lahir_anak'] ?? '-' }}, {{ !empty($formData['tanggal_lahir_anak']) ? \Carbon\Carbon::parse($formData['tanggal_lahir_anak'])->translatedFormat('d F Y') : '-' }}</td>
            </tr>
            <tr>
                <td>Nama Orang Tua (Ayah / Ibu)</td>
                <td style="text-align: center;">:</td>
                <td>{{ $formData['nama_ayah'] ?? '-' }} / {{ $formData['nama_ibu'] ?? '-' }}</td>
            </tr>
        </table>

    @elseif ($idPelayanan === 'ply_kematian')
        <div class="surat-keterangan">
            Menerangkan bahwa nama yang tersebut di bawah ini telah meninggal dunia dalam wilayah kewenangan Desa Sukamakmur:
        </div>
        <table class="biodata-table" style="margin-left: 36px;">
            <tr>
                <td style="width: 150px;">Nama Almarhum/ah</td>
                <td style="width: 15px; text-align: center;">:</td>
                <td><strong>{{ strtoupper($formData['nama_almarhum'] ?? $namaLengkap) }}</strong></td>
            </tr>
            <tr>
                <td>Hari / Tanggal Kematian</td>
                <td style="text-align: center;">:</td>
                <td>{{ $formData['hari_meninggal'] ?? '-' }}, {{ !empty($formData['tanggal_meninggal']) ? \Carbon\Carbon::parse($formData['tanggal_meninggal'])->translatedFormat('d F Y') : '-' }}</td>
            </tr>
            <tr>
                <td>Tempat Meninggal</td>
                <td style="text-align: center;">:</td>
                <td>{{ $formData['tempat_meninggal'] ?? '-' }}</td>
            </tr>
            <tr>
                <td>Penyebab Kematian</td>
                <td style="text-align: center;">:</td>
                <td>{{ $formData['penyebab_kematian'] ?? '-' }}</td>
            </tr>
        </table>

    @else
        <!-- Fallback Generic Pelayanan Template -->
        <div class="surat-keterangan">
            Bahwa nama yang bersangkutan di atas adalah benar-benar warga penduduk Desa Sukamakmur dan surat keterangan ini diterbitkan sehubungan dengan permohonan <strong>{{ $pelayanan?->nama_pelayanan ?? 'Administrasi Kependudukan' }}</strong>.
        </div>
        @if (!empty($formData['keperluan']))
            <div class="highlight-box">
                <strong>Keperluan:</strong> {{ $formData['keperluan'] }}
            </div>
        @endif
    @endif

    <!-- KALIMAT PENUTUP -->
    <div class="surat-penutup">
        Demikian surat keterangan ini dibuat dengan sebenarnya dan penuh rasa tanggung jawab agar dapat dipergunakan sebagaimana mestinya oleh yang bersangkutan.
    </div>

    <!-- TANDA TANGAN & QR CODE VERIFIKASI RESMI -->
    <div class="ttd-container">
        <table class="ttd-table">
            <tr>
                <!-- Sisi Kiri: Validasi QR Code Digital -->
                <td class="ttd-left">
                    <div class="qr-badge-box">
                        <img src="{{ $qrCode }}" alt="QR Keabsahan">
                        <div class="qr-caption">
                            <strong>SIPADES DIGITAL VERIFIED</strong><br>
                            Pindai QR Code untuk memeriksa keabsahan surat ini secara online.
                        </div>
                    </div>
                </td>

                <!-- Sisi Kanan: Tanda Tangan Kepala Desa -->
                <td class="ttd-right">
                    <div>Sukamakmur, {{ $tanggalSurat }}</div>
                    <div class="ttd-pejabat-title">
                        <strong>{{ strtoupper($kepalaDesa?->jabatan ?? 'Kepala Desa Sukamakmur') }}</strong>
                    </div>

                    <!-- Area Spasi / Cap Kedinasan -->
                    <div class="ttd-spasi">
                        <!-- Tanda Tangan Digital Resmi Stamp -->
                        <span style="font-size: 8pt; color: #1e6091; font-style: italic;">
                            [Ditandatangani secara elektronik]
                        </span>
                    </div>

                    <div class="ttd-nama">
                        {{ $kepalaDesa?->user?->nama ?? 'H. MUHAMMAD RIZKI, S.Sos' }}
                    </div>
                    <div class="ttd-nip">
                        NIP. {{ $kepalaDesa?->nip ?? '19750812 200212 1 003' }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- FOOTER KEAMANAN & TRACKING DOKUMEN -->
    <div class="dokumen-footer">
        <div>
            Tiket: {{ $pengajuan?->nomor_tiket ?? '-' }} &bull; Token Keamanan: {{ substr($qrToken ?? '', 0, 16) }}...
        </div>
        <div>
            Dicetak melalui Sistem Informasi Pelayanan Administrasi Desa (SIPADES)
        </div>
    </div>

</body>
</html>
