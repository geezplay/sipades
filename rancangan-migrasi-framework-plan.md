# Blueprint Rancangan Perbaikan dan Migrasi Framework — SIPADES

## Context
Codebase SIPADES saat ini berupa purwarupa frontend statis (Vanilla JS SPA, file CSS terpisah, dan database simulasi `localStorage` di browser klien tanpa backend). Arsitektur ini memiliki kelemahan kritis: data tidak tersinkronisasi antar-perangkat, rentan manipulasi otentikasi melalui DevTools, file diunggah dalam format Base64 yang membebani memori, tidak ada audit log legalitas surat, serta template cetak surat yang belum dinamis. Dokumen ini menyajikan audit komprehensif, 3 opsi framework siap pilih untuk klien, pemetaan skema database relasional berdasarkan ERD BAB VI, arsitektur alur kerja verifikasi & persetujuan, serta panduan implementasi langkah demi langkah (step-by-step).

---

## 1. Audit Masalah Codebase Saat Ini & Kebutuhan Klien

### 1.1 Kelemahan Kritis Sistem Berjalan
1. **Keamanan & Otentikasi Palsu (Client-Side Auth)**:
   - Sesi disimpan pada `sessionStorage.getItem('sipades_user')`. Pengguna dapat mengedit role menjadi `'admin'` atau `'kepala'` lewat console browser tanpa verifikasi server.
   - Hashing password di `js/database.js` hanya berupa manipulasi bit aritmatika 32-bit sederhana (`hash = ((hash << 5) - hash) + char`), bukan algoritma kriptografis (bcrypt/Argon2).
2. **Penyimpanan Data Terisolasi (localStorage)**:
   - Setiap pengguna hanya melihat data di browser masing-masing; pengajuan dari warga tidak benar-benar masuk ke dashboard Petugas atau Kepala Desa di perangkat berbeda.
   - Kuota `localStorage` terbatas (maksimal ~5–10 MB). File unggahan disimpan sebagai string Base64 (`file_dokumen`) yang cepat menyebabkan error `QuotaExceededError`.
3. **Integritas Relasi & Skema Data**:
   - Fungsi `confirmDeleteUser` menghapus akun tanpa cascading delete yang konsisten, meninggalkan record pengajuan, verifikasi, dan dokumen yatim (orphaned records).
   - Tabel `kepala_desa_lurah` tidak terhubung dinamis saat mencetak surat; nama Kepala Desa di-hardcode di file `js/app.js:1381` (`H. Muhammad Rizki, S.Sos`).
4. **UI & Output Dokumen**:
   - Ketidaksesuaian kelas CSS antara `app.js` (`.data-table`, `.detail-grid`, `.surat-preview`) dengan `css/style.css` (`.table`, `.surat-container`).
   - Tidak ada aturan `@media print` dan `.no-print`, sehingga mencetak surat menyertakan navigasi sidebar, topbar, dan tombol aksi.
   - Petugas dan Kepala Desa tidak memiliki tombol/fitur untuk membuka dan memeriksa berkas lampiran warga.

---

## 2. Opsi Framework untuk Klien

Berikut perbandingan 3 opsi framework terukur untuk diajukan kepada pemilik proyek (klien):

| Kriteria | Opsi A: Laravel (PHP) + Blade/Livewire | Opsi B: Next.js (TypeScript Fullstack) | Opsi C: Express.js (Node) + React/Vue SPA |
|:---|:---|:---|:---|
| **Profil Cocok Untuk** | **Paling Direkomendasikan untuk Instansi Desa/Kelurahan** | Sistem Modern, Startup, atau Tim Frontend Kuat | Arsitektur Decoupled / Siap Aplikasi Mobile |
| **Kelebihan Utama** | Ekosistem mature, built-in Auth, Migration, Eloquent ORM, siap export PDF kedinasan (DomPDF), ramah hosting murah (cPanel/Shared Hosting/VPS). | Satu bahasa (TypeScript end-to-end), performa SSR tinggi, UI responsif dengan Tailwind + Shadcn UI. | Fleksibel, RESTful API terstandarisasi, frontend dan backend dapat di-scale secara terpisah. |
| **Hosting & Biaya Server** | Sangat Hemat (mulai dari Shared Hosting cPanel Rp30.000/bln atau VPS Rp80.000/bln). | Sedang (Vercel / VPS Node.js dengan Docker + Managed PostgreSQL). | Sedang (VPS Linux dengan PM2/Docker + Nginx reverse proxy). |
| **Waktu Pengerjaan** | Cepat (2–3 minggu untuk MVP lengkap). | Menengah (3–4 minggu). | Menengah (3–4 minggu). |
| **Ekosistem PDF Kedinasan** | Sangat Kuat (`barryvdh/laravel-dompdf` dengan styling CSS cetak kertas A4/F4 presisi). | Cukup (`@react-pdf/renderer` atau Puppeteer, membutuhkan konfigurasi font sistem). | Cukup (`html-pdf-node` atau Puppeteer service). |

### Rekomendasi Utama ke Klien:
Pilihlah **Opsi A (Laravel 11 + MySQL + Blade/Livewire)** jika klien memprioritaskan biaya operasional rendah, kemudahan deployment ke server instansi pemerintah/desa, dan kecepatan integrasi template cetak PDF surat dinamis. Pilihlah **Opsi B** jika klien menghendaki arsitektur frontend modern berbasis React/TypeScript.

---

## 3. Skema Database Relasional (DDL MySQL/PostgreSQL Sesuai ERD BAB VI)

Skema database nyata untuk menggantikan `localStorage.tables`:

```sql
-- 1. Tabel Users (Akun Global)
CREATE TABLE users (
    id_user VARCHAR(36) PRIMARY KEY,
    nama VARCHAR(150) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('masyarakat', 'petugas', 'kepala', 'admin') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 2. Profil Masyarakat
CREATE TABLE masyarakat (
    id_masyarakat VARCHAR(36) PRIMARY KEY,
    id_user VARCHAR(36) NOT NULL UNIQUE,
    nik VARCHAR(16) UNIQUE NOT NULL,
    alamat TEXT NOT NULL,
    no_hp VARCHAR(20) NOT NULL,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE
);

-- 3. Profil Petugas
CREATE TABLE petugas (
    id_petugas VARCHAR(36) PRIMARY KEY,
    id_user VARCHAR(36) NOT NULL UNIQUE,
    jabatan VARCHAR(100) NOT NULL,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE
);

-- 4. Profil Kepala Desa / Lurah
CREATE TABLE kepala_desa_lurah (
    id_pimpinan VARCHAR(36) PRIMARY KEY,
    id_user VARCHAR(36) NOT NULL UNIQUE,
    jabatan VARCHAR(100) NOT NULL DEFAULT 'Kepala Desa',
    nip VARCHAR(30) NULL,
    tanda_tangan_digital VARCHAR(255) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE
);

-- 5. Master Jenis Pelayanan
CREATE TABLE jenis_pelayanan (
    id_pelayanan VARCHAR(50) PRIMARY KEY,
    nama_pelayanan VARCHAR(150) NOT NULL,
    deskripsi TEXT NOT NULL,
    persyaratan TEXT NOT NULL,
    icon VARCHAR(50) DEFAULT 'fa-file-lines',
    formulir_schema JSON NOT NULL,
    is_active BOOLEAN DEFAULT TRUE
);

-- 6. Transaksi Pengajuan
CREATE TABLE pengajuan (
    id_pengajuan VARCHAR(36) PRIMARY KEY,
    nomor_tiket VARCHAR(30) UNIQUE NOT NULL,
    id_masyarakat VARCHAR(36) NOT NULL,
    id_pelayanan VARCHAR(50) NOT NULL,
    tanggal_pengajuan TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('DIAJUKAN', 'DIPERIKSA', 'REVISI', 'DIVERIFIKASI', 'MENUNGGU_PERSETUJUAN', 'DISETUJUI', 'DITOLAK', 'DIPROSES', 'SELESAI') DEFAULT 'DIAJUKAN',
    data_formulir JSON NOT NULL,
    catatan TEXT NULL,
    catatan_revisi TEXT NULL,
    alasan_penolakan TEXT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_masyarakat) REFERENCES masyarakat(id_masyarakat) ON DELETE RESTRICT,
    FOREIGN KEY (id_pelayanan) REFERENCES jenis_pelayanan(id_pelayanan) ON DELETE RESTRICT
);

-- 7. Dokumen Lampiran Pengajuan
CREATE TABLE dokumen (
    id_dokumen VARCHAR(36) PRIMARY KEY,
    id_pengajuan VARCHAR(36) NOT NULL,
    nama_dokumen VARCHAR(150) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_size INT NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    tanggal_upload TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pengajuan) REFERENCES pengajuan(id_pengajuan) ON DELETE CASCADE
);

-- 8. Log Verifikasi Petugas
CREATE TABLE verifikasi (
    id_verifikasi VARCHAR(36) PRIMARY KEY,
    id_pengajuan VARCHAR(36) NOT NULL,
    id_petugas VARCHAR(36) NOT NULL,
    tanggal_verifikasi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    hasil ENUM('TERVERIFIKASI', 'REVISI', 'DITOLAK') NOT NULL,
    catatan TEXT NULL,
    FOREIGN KEY (id_pengajuan) REFERENCES pengajuan(id_pengajuan) ON DELETE CASCADE,
    FOREIGN KEY (id_petugas) REFERENCES petugas(id_petugas) ON DELETE RESTRICT
);

-- 9. Surat Resmi Keluar
CREATE TABLE surat (
    id_surat VARCHAR(36) PRIMARY KEY,
    id_pengajuan VARCHAR(36) NOT NULL UNIQUE,
    nomor_surat VARCHAR(100) UNIQUE NOT NULL,
    tanggal_surat DATE NOT NULL,
    id_pimpinan_penandatangan VARCHAR(36) NOT NULL,
    file_pdf_path VARCHAR(255) NULL,
    qr_token VARCHAR(64) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pengajuan) REFERENCES pengajuan(id_pengajuan) ON DELETE RESTRICT,
    FOREIGN KEY (id_pimpinan_penandatangan) REFERENCES kepala_desa_lurah(id_pimpinan) ON DELETE RESTRICT
);

-- 10. Notifikasi Pengguna
CREATE TABLE notifikasi (
    id_notifikasi VARCHAR(36) PRIMARY KEY,
    id_user VARCHAR(36) NOT NULL,
    judul VARCHAR(150) NOT NULL,
    pesan TEXT NOT NULL,
    link_url VARCHAR(255) NULL,
    status_baca BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE
);
```

---

## 4. Alur Kerja (Workflow Engine) & State Machine

Workflow pengajuan pelayanan administrasi desa memiliki aturan validasi ketat sebagai berikut:

```
[Masyarakat: Ajukan] ──> DIAJUKAN
                             │
                             ▼ (Petugas mengambil antrean)
                         DIPERIKSA
                             ├──[Berkas kurang]──> REVISI ──[Warga upload ulang]──> DIPERIKSA
                             ├──[Verifikasi valid]──> MENUNGGU_PERSETUJUAN
                             │                             ├──[Kades Tolak]──> DITOLAK (Final)
                             │                             └──[Kades Setujui]─> DISETUJUI
                             ▼                                                    │
                         DIPROSES (Petugas terbitkan No. Surat & Generate PDF) <──┘
                             │
                             ▼
                          SELESAI (Surat aktif, QR terverifikasi, unduh PDF)
```

### Aturan Transisi Status (State Rules):
1. **DIAJUKAN**: Pengajuan masuk, notifikasi otomatis terkirim ke seluruh user dengan role `petugas`.
2. **DIPERIKSA**: Hanya bisa diaktifkan oleh `petugas`.
3. **REVISI**: Petugas wajib mengisi `catatan_revisi`. Warga menerima notifikasi dan form input revisi dibuka. Setelah warga submit ulang, status kembali menjadi `DIPERIKSA` (atau `DIAJUKAN`) dan notifikasi dikirim ulang ke petugas.
4. **MENUNGGU_PERSETUJUAN**: Petugas menyimpan record verifikasi. Notifikasi dikirim ke user `kepala`.
5. **DISETUJUI / DITOLAK**: Hanya dapat diakses oleh user `kepala`. Penolakan wajib disertai `alasan_penolakan`.
6. **DIPROSES / SELESAI**: Petugas melakukan generate nomor surat otomatis, mengompilasi data ke format resmi, membuat QR code verifikasi keabsahan dokumen publik, dan menandai status `SELESAI`.

---

## 5. Fitur Penting Penunjang Profesionalitas

1. **Storage Berkas Aman (File Management)**:
   - Ganti Base64 dengan file upload multipart (`POST /api/pengajuan/upload`).
   - Validasi MIME Type di server: hanya menerima `application/pdf`, `image/jpeg`, `image/png` dengan ukuran maks. 2 MB per berkas.
   - Berkas disimpan di direktori privat (`storage/app/documents/...`), hanya dapat diakses melalui signed URL / authenticated route untuk menjaga privasi data KTP dan KK warga.
2. **Generator Surat Resmi & QR Code Keabsahan**:
   - Kop surat dinamis: logo lambang kabupaten, nama kabupaten, nama kecamatan, nama desa, alamat kantor, kode pos, dan kontak resmi.
   - Format penomoran otomatis tersistem (Contoh: `470/{NO_URUT}/KD-SM/X/2026`).
   - QR Code verifikasi disematkan di pojok bawah surat. Ketika discan oleh pihak luar (Bank/Kecamatan/Disnaker), mengarah ke halaman publik verifikasi: `/verifikasi-surat/{token}` yang memvalidasi bahwa surat tersebut sah terbit dari SIPADES.
3. **Audit Log & Keamanan Akun**:
   - Pencatatan log aktivitas (siapa yang memverifikasi, menyetujui, mencetak, atau membatalkan surat beserta timestamp dan IP address).
   - Rate limiting pada form login dan registrasi untuk mencegah serangan brute-force.
   - Session berbasis HTTP-only cookies dengan perlindungan CSRF.

---

## 6. Panduan Implementasi Bertahap (Roadmap Pengerjaan)

### Fase 1: Setup Proyek & Database (Hari 1–3)
- Inisialisasi framework terpilih (misal: Laravel 11 / Next.js).
- Konfigurasi koneksi database MySQL / PostgreSQL.
- Buat file migration dan seeder data awal (admin, 1 staf petugas, 1 kepala desa, 6 jenis pelayanan default, sample akun warga).
- Terapkan hashing kata sandi standar bcrypt/Argon2id.

### Fase 2: Otentikasi, Role & Layout Dashboard (Hari 4–7)
- Implementasi sistem login multi-role: Middleware Role Check (`masyarakat`, `petugas`, `kepala`, `admin`).
- Porting tema styling official government dari `css/style.css` ke sistem komponen modular (Blade components atau React components).
- Pembuatan layout responsif: public navbar, user sidebar navigasi, header status notifikasi.

### Fase 3: Modul Pengajuan & Berkas Warga (Hari 8–11)
- Form dinamis pengajuan pelayanan berdasarkan schema JSON jenis pelayanan.
- Integrasi dropzone upload berkas ke penyimpanan server dengan validasi server-side.
- Halaman pelacakan status pengajuan (timeline wizard) dan modul perbaikan revisi.

### Fase 4: Modul Petugas & Kepala Desa (Hari 12–16)
- Dashboard Petugas: tabel pengajuan masuk, filter status, preview berkas dokumen lampiran pemohon, modal catatan revisi, dan aksi verifikasi berkas.
- Dashboard Kepala Desa: daftar persetujuan tertunda, review profil dan berkas warga, aksi persetujuan satu-klik, serta form penolakan.
- Generator penomoran surat otomatis dan integrasi library PDF (DomPDF).

### Fase 5: Modul Admin, Laporan, & QR Verification (Hari 17–20)
- Manajemen pengguna (CRUD petugas, edit data kepala desa, aktivasi akun warga).
- Manajemen master jenis pelayanan dan syarat berkas.
- Laporan rekapitulasi pengajuan (filter rentang tanggal, export excel/PDF).
- Endpoint publik verifikasi keabsahan surat via QR code.

### Fase 6: Testing & Deployment (Hari 21–23)
- Uji alur end-to-end (pengajuan warga -> verifikasi petugas -> approval kades -> terbit PDF surat -> scan QR verifikasi).
- Pengujian hak akses (pastikan role masyarakat tidak dapat menembus endpoint petugas/admin).
- Deployment ke server staging/production klien.

---

## Verification & Cara Pembuktian
Dokumen blueprint ini siap dipresentasikan langsung ke klien untuk menentukan pilihan framework. Keberhasilan implementasi diverifikasi melalui kriteria berikut:
1. **Multi-Perangkat**: Data yang diajukan dari browser A langsung muncul secara realtime di browser Petugas B.
2. **Kerahasiaan Berkas**: Dokumen lampiran KTP/KK tidak lagi tersimpan di memory browser (`localStorage`), melainkan tersimpan aman di server dengan hak akses terbatas.
3. **Validitas Surat**: Cetak PDF surat menghasilkan layout standar kedinasan A4 lengkap dengan kop resmi dan QR code yang dapat diverifikasi keasliannya melalui ponsel.

---

## Critical files & anchors
1. `js/database.js` (line 7-60): Struktur tabel dan relasi objek data awal yang ditransformasikan ke skema tabel database relasional SQL (DDL).
2. `js/database.js` (line 370-482): Definisi 6 jenis pelayanan (`ply_domisili`, `ply_usaha`, dll.) dan JSON form schema yang dimigrasikan ke tabel `jenis_pelayanan`.
3. `js/app.js` (line 140-179): Logika otentikasi dan role routing yang ditransformasikan menjadi middleware server-side (RBAC).
4. `js/app.js` (line 1567-1625, 1775-1805): Logika transisi status verifikasi petugas, persetujuan kepala desa, dan penerbitan nomor surat yang diadopsi ke dalam controller/state machine backend.
5. `css/style.css` (line 10-90, 265-435): Variabel warna tema resmi pemerintahan (*Government Navy & Blue Accent*) dan komponen UI yang dipertahankan dalam styling framework baru.

---

## Assumptions & contingencies
- **Asumsi Pilihan Klien**: Jika klien memilih **Opsi A (Laravel 11)**, implementasi menggunakan arsitektur monolith MVC + Blade/Livewire dengan `barryvdh/laravel-dompdf`. Jika klien memilih **Opsi B (Next.js)**, implementasi menggunakan Next.js App Router dengan Prisma ORM + PostgreSQL. Jika klien memilih **Opsi C (Node/React)**, implementasi memisahkan backend REST API Express dan frontend SPA Vite React.
- **Database Engine**: Standar default MySQL 8.0+ atau MariaDB 10.6+ yang didukung oleh semua penyedia hosting di Indonesia.
- **Asset & Desain**: Semua diagram perancangan (ERD, DFD, UseCase) yang sudah ada di folder `diagrams/` dan palet warna di `css/style.css` dijadikan acuan utama antarmuka sistem baru.
