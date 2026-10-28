# 🏛️ SIPADES — Sistem Pelayanan Administrasi Desa

<p align="center">
  <img src="https://raw.githubusercontent.com/FortAwesome/Font-Awesome/6.x/svgs/solid/landmark.svg" width="90" height="90" alt="SIPADES Logo" />
</p>

<p align="center">
  <strong>Platform Digital Layanan Surat Kedinasan & Administrasi Kependudukan Tingkat Desa/Kelurahan Berbasis Alur Kerja Terverifikasi dan QR Code Keabsahan Elektronik.</strong>
</p>

<p align="center">
  <a href="https://sipades.geezplay.site"><img src="https://img.shields.io/badge/Live%20Demo-sipades.geezplay.site-0f2b48?style=for-the-badge&logo=googlechrome&logoColor=white" alt="Live Demo" /></a>
  <a href="https://laravel.com"><img src="https://img.shields.io/badge/Laravel-13.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel" /></a>
  <a href="https://www.php.net"><img src="https://img.shields.io/badge/PHP-8.3%20FPM-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.3" /></a>
  <a href="https://www.mysql.com"><img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL" /></a>
  <a href="https://www.docker.com"><img src="https://img.shields.io/badge/Docker-Compose-2496ED?style=for-the-badge&logo=docker&logoColor=white" alt="Docker" /></a>
  <a href="https://letsencrypt.org"><img src="https://img.shields.io/badge/SSL-Let's%20Encrypt-003A70?style=for-the-badge&logo=letsencrypt&logoColor=white" alt="SSL" /></a>
</p>

---

## 📑 Daftar Isi
- [Tentang SIPADES](#-tentang-sipades)
- [Fitur Utama](#-fitur-utama)
- [Akses Live Demo & Akun Uji Coba](#-akses-live-demo--akun-uji-coba)
- [Alur Kerja Sistem (State Machine)](#-alur-kerja-sistem-state-machine)
- [Katalog 6 Layanan Surat Mandiri](#-katalog-6-layanan-surat-mandiri)
- [Arsitektur & Tumpukan Teknologi](#-arsitektur--tumpukan-teknologi)
- [Panduan Instalasi Lokal (Docker)](#-panduan-instalasi-lokal-docker)
- [Panduan Deployment VPS](#-panduan-deployment-vps)
- [Pengujian Otomatis (Test Suite)](#-pengujian-otomatis-test-suite)
- [Struktur Repositori](#-struktur-repositori)
- [Lisensi](#-lisensi)

---

## 📖 Tentang SIPADES

**SIPADES** adalah sistem informasi pelayanan publik tingkat desa yang menggantikan birokrasi permohonan surat manual yang lambat dan rentan tercecer. Dibangun di atas arsitektur **Laravel + Docker Compose + MySQL 8.0**, sistem ini menjamin integritas data penduduk, penyimpanan berkas pribadi terenkripsi, penomoran surat resmi dinamis, serta keabsahan dokumen hukum melalui tanda tangan digital dan **QR Code Verifikasi Publik** tanpa perantara.

---

## ✨ Fitur Utama

- 🛡️ **Otentikasi Aman & RBAC 4-Level**: Pemisahan hak akses ketat antara `Masyarakat`, `Petugas Pelayanan`, `Kepala Desa`, dan `Administrator`.
- 📁 **Penyimpanan Dokumen Terproteksi (Private Storage)**: File sensitif KTP, KK, dan surat pemohon tidak dapat diakses langsung oleh publik (`HTTP 403`), melainkan melalui authorized streaming proxy.
- ⚙️ **Workflow State Machine Berjenjang**: Alur status terstruktur `DIAJUKAN` $\rightarrow$ `DIPERIKSA` $\leftrightarrow$ `REVISI` $\rightarrow$ `MENUNGGU_PERSETUJUAN` $\rightarrow$ `DISETUJUI` $\rightarrow$ `SELESAI`.
- 📄 **Official PDF Generator (DomPDF)**: Layout kedinasan standar A4 Indonesia dengan kop surat resmi instansi, garis ganda resmi, margin 2cm, dan format penomoran otomatis kedinasan `470/{NO}/KD-SM/{ROMAN}/{TAHUN}`.
- 🔍 **Verifikasi Keabsahan Surat Tanpa Login**: Halaman publik anti-pemalsuan `/verifikasi-surat/{token}` dengan token kriptografi hash SHA-256 dan penyensoran privasi NIK (`320102******0001`).
- 🎨 **Government Navy Aesthetic Theme**: Desain antarmuka berstandar pemerintahan modern berbasis CSS Grid, kontras tinggi, typography *Plus Jakarta Sans* & *Inter*, serta responsif di ponsel cerdas.
- 🔔 **Sistem Notifikasi Terintegrasi**: Peringatan status instan kepada pemohon ketika berkas butuh perbaikan atau telah disetujui.

---

## 🌐 Akses Live Demo & Akun Uji Coba

Sistem saat ini aktif pada lingkungan produksi VPS Hostinger ber-SSL:

| Portal | Tautan Akses |
|---|---|
| **Aplikasi Utama** | [https://sipades.geezplay.site](https://sipades.geezplay.site) |
| **Halaman Masuk** | [https://sipades.geezplay.site/login](https://sipades.geezplay.site/login) |
| **Domain Alias** | [https://simpedes.geezplay.site](https://simpedes.geezplay.site) *(Otomatis dialihkan via SSL 301)* |

### Kredensial Akun Pengujian

| Peran (Role) | Alamat Email | Kata Sandi | Identitas / NIP | Wewenang Sistem |
|---|---|---|---|---|
| **Administrator** | `admin@sipades.go.id` | `Admin123!` | ID: `adm-default` | Manajemen akun staf & pengaturan formulir |
| **Petugas** | `petugas@sipades.go.id` | `Petugas123!` | Staf Pelayanan Umum | Verifikasi berkas & penerbitan surat dinas |
| **Kepala Desa** | `kades@sipades.go.id` | `Kades123!` | NIP: `19750812 200212 1 003` | Penelaahan berkas & pengesahan tanda tangan |
| **Masyarakat** | `warga@sipades.go.id` | `Warga123!` | NIK: `3201123456780001` | Pengajuan surat & unduh sertifikat PDF |

> 💡 *Rincian panduan operasional akun selengkapnya tersedia pada berkas [AKUN_LOGIN.md](AKUN_LOGIN.md).*

---

## 🔄 Alur Kerja Sistem (State Machine)

```
[ Warga Mengajukan ]
        │
        ▼
   ( DIAJUKAN ) ──────────────┐
        │                     │
        ▼                     ▼
   ( DIPERIKSA ) ◄───────► ( REVISI )
   [Oleh Petugas]      [Perbaikan Berkas]
        │
        ▼
( MENUNGGU_PERSETUJUAN )
        │
        ▼
   ( DISETUJUI ) ─────────► ( DITOLAK )
   [Oleh Kepala Desa]
        │
        ▼
   ( SELESAI )
[Surat PDF & QR Terbit]
```

---

## 📋 Katalog 6 Layanan Surat Mandiri

1. **Surat Keterangan Domisili** (`ply_domisili`): Keterangan tempat tinggal warga pendatang atau penduduk tetap.
2. **Surat Pengantar SKCK** (`ply_skck`): Pengantar pembuatan Catatan Kepolisian ke Polsek/Polres.
3. **Surat Keterangan Usaha** (`ply_usaha`): Legalitas usaha mikro/kecil warga untuk pengajuan perbankan.
4. **Surat Keterangan Belum Menikah** (`ply_belum_menikah`): Pernyataan status perkawinan untuk urusan administrasi/pekerjaan.
5. **Surat Keterangan Kelahiran** (`ply_kelahiran`): Pengantar pencatatan kelahiran anak ke Dinas Kependudukan.
6. **Surat Keterangan Kematian** (`ply_kematian`): Pelaporan dan keterangan resmi peristiwa wafat warga desa.

---

## 💻 Arsitektur & Tumpukan Teknologi

- **Backend**: [Laravel 13](https://laravel.com) (PHP 8.3 FPM Alpine)
- **Database**: [MySQL 8.0](https://mysql.com) (InnoDB, Skema Relasional UUID VARCHAR(36) 10 Tabel)
- **Web Server**: [Nginx Alpine](https://nginx.org) (FastCGI Proxy, Static Cache & Security Headers)
- **Dokumentasi Dokumen**: [Barryvdh Laravel DomPDF](https://github.com/barryvdh/laravel-dompdf) (Renderer A4 Resmi)
- **Kriptografi & QR Code**: [Chillerlan PHP QR-Code](https://github.com/chillerlan/php-qrcode) + SHA-256 Token Hasher
- **Containerization**: [Docker & Docker Compose](https://docker.com)
- **Keamanan Jaringan**: Reverse Proxy Nginx Host + SSL Otomatis [Certbot Let's Encrypt](https://certbot.eff.org/)

---

## 🚀 Panduan Instalasi Lokal (Docker)

### 1. Klon Repositori
```bash
git clone https://github.com/geezplay/sipades.git
cd sipades
```

### 2. Salin Konfigurasi Environment
```bash
cp .env.example .env
```

### 3. Bangun dan Jalankan Kontainer Docker
```bash
docker compose up -d --build
```

### 4. Eksekusi Migrasi & Database Seeder
```bash
docker compose exec -T app php artisan key:generate
docker compose exec -T app php artisan migrate --seed --force
docker compose exec -T app php artisan storage:link
```

### 5. Akses Aplikasi
Buka peramban di: **`http://localhost:8000`**

---

## 🌐 Panduan Deployment VPS

Repositori ini telah dilengkapi dengan skrip deployment otomatis satu perintah untuk VPS Ubuntu/Debian:

```bash
# 1. Jalankan skrip deployment otomatis
chmod +x deploy.sh
./deploy.sh
```

Skrip ini akan secara otomatis:
1. Memvalidasi ketersediaan Docker & Docker Compose di VPS.
2. Memuat variabel konfigurasi produksi dari `.env.production`.
3. Membangun kontainer `sipades_prod_app`, `sipades_prod_db` (MySQL 8), dan `sipades_prod_web` (Nginx).
4. Melakukan migrasi basis data dan seeding data master pelayanan serta akun dinas.
5. Mengaktifkan optimasi cache Laravel (`config:cache`, `route:cache`, `view:cache`).

---

## 🧪 Pengujian Otomatis (Test Suite)

Untuk memvalidasi integritas seluruh fungsionalitas sistem dari awal hingga akhir:

```bash
docker compose exec -T app php artisan test
```

**Hasil Pengujian:**
```text
   PASS  Tests\Unit\ExampleTest
  ✓ that true is true

   PASS  Tests\Feature\SipadesEndToEndWorkflowTest
  ✓ complete sipades workflow and verification (24 assertions)

  Tests:    2 passed (24 assertions)
  Duration: 0.28s
```

---

## 📂 Struktur Repositori

```
sipades/
├── app/
│   ├── Http/Controllers/       # Auth, Pengajuan, Dokumen, Surat, Admin, Notifikasi
│   ├── Http/Middleware/        # CheckRole RBAC Middleware
│   ├── Models/                 # 10 Eloquent Models Relasional
│   └── Services/               # WorkflowService & SuratService (State Machine & DomPDF)
├── config/                     # Konfigurasi Laravel & Database
├── database/
│   ├── migrations/             # 10 Migrasi DDL Relasional MySQL ERD BAB VI
│   └── seeders/                # Seeder Akun Default & 6 Master Layanan
├── docker/
│   └── nginx/                  # Server block template Nginx Alpine
├── resources/
│   └── views/
│       ├── admin/              # Dashboard & Manajemen User
│       ├── auth/               # Tampilan Login & Register
│       ├── components/         # Status Badge & Reusable UI
│       ├── kepala/             # Dashboard & Approval Kades
│       ├── layouts/            # Master Layout Government Navy
│       ├── notifikasi/         # Pusat Pemberitahuan Warga/Staf
│       ├── pengajuan/          # Formulir Dinamis & Detail Berkas
│       ├── petugas/            # Antrean Verifikasi & Penerbitan Surat
│       ├── surat/              # Template Cetak PDF A4 & Halaman Verifikasi QR Publik
│       └── warga/              # Dashboard Warga & Pelayanan
├── routes/                     # web.php, auth.php, workflow.php, surat.php
├── storage/                    # Private Documents Storage & Generated Letters
├── AKUN_LOGIN.md               # Panduan kredensial akun bawaan sistem
├── deploy.sh                   # Skrip otomatisasi deployment VPS
├── docker-compose.yml          # Konfigurasi container lokal
├── docker-compose.prod.yml     # Konfigurasi container production VPS
├── Dockerfile                  # Base image PHP 8.3 FPM Alpine
└── README.md                   # Dokumentasi resmi proyek
```

---

## 📄 Lisensi

Proyek SIPADES ini dikembangkan di bawah lisensi terbuka [MIT License](LICENSE). Silakan gunakan dan kembangkan untuk kemajuan administrasi desa/kelurahan di seluruh Indonesia.

<p align="center">
  Dikelola dengan ❤️ oleh Tim Pengembang <strong>SIPADES Desa Sukamakmur</strong>.
</p>
