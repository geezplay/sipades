# 📋 Rekapitulasi Akun Login SIPADES (Sistem Pelayanan Administrasi Desa)

Dokumen ini memuat data akun resmi pengujian dan operasional sistem SIPADES, hak akses masing-masing peran, serta informasi alamat akses server.

---

## 🌐 Tautan Akses Sistem

| Lingkungan | Alamat URL | Keterangan |
|---|---|---|
| **Production (VPS)** | [https://sipades.geezplay.site](https://sipades.geezplay.site) | Server VPS Hostinger dengan SSL Let's Encrypt |
| **Production Alias** | [https://simpedes.geezplay.site](https://simpedes.geezplay.site) | Dialihkan otomatis (301) ke domain utama |
| **Lokal (Docker)** | [http://localhost:8000](http://localhost:8000) | Port forwarding Docker Compose development |

---

## 👥 Tabel Akun Pengguna Resmi

| Peran (Role) | Nama Lengkap | Alamat Email | Kata Sandi | NIK / NIP / Identitas | Wewenang & Hak Akses |
|---|---|---|---|---|---|
| **Administrator** | Administrator SIPADES | `admin@sipades.go.id` | `Admin123!` | ID: `adm-default` | Manajemen akun staf/petugas/kades, master jenis pelayanan desa, rekapitulasi data. |
| **Petugas Pelayanan** | Budi Santoso, S.AP | `petugas@sipades.go.id` | `Petugas123!` | Staf Pelayanan Umum | Verifikasi kelengkapan berkas warga, meminta revisi, meneruskan ke Kepala Desa, dan menerbitkan surat resmi ber-QR Code. |
| **Kepala Desa** | H. Muhammad Rizki, S.Sos | `kades@sipades.go.id` | `Kades123!` | NIP: `19750812 200212 1 003` | Meninjau berkas pemohon yang telah diverifikasi petugas, memberikan persetujuan (approval) atau penolakan dengan catatan. |
| **Masyarakat (Warga)** | Ahmad Subagyo | `warga@sipades.go.id` | `Warga123!` | NIK: `3201123456780001` | Mengajukan surat permohonan, mengunggah berkas persyaratan, melacak status pengajuan, memperbaiki revisi, dan mengunduh surat resmi. |

---

## 📝 Cara Membuat Akun Masyarakat Baru

Tersedia 2 (dua) metode untuk mendaftarkan akun masyarakat ke dalam sistem:

### Metode 1: Pendaftaran Mandiri oleh Warga (Online)
1. Buka halaman login di [https://sipades.geezplay.site/login](https://sipades.geezplay.site/login) (atau langsung ke [https://sipades.geezplay.site/register](https://sipades.geezplay.site/register)).
2. Klik tombol **"Buat Akun Masyarakat Baru"** di bawah tombol Masuk.
3. Lengkapi formulir pendaftaran:
   - **Nama Lengkap**: Sesuai KTP
   - **NIK**: 16 digit angka kependudukan valid
   - **No. WhatsApp / HP**: Kontak aktif pemohon
   - **Alamat Email**: Email aktif pemohon
   - **Alamat Tempat Tinggal / RT RW**
   - **Kata Sandi & Ulangi Sandi** (min. 8 karakter, dilengkapi tombol ikon mata untuk melihat sandi)
4. Klik **"Selesaikan Pendaftaran"**. Akun langsung aktif dan otomatis masuk ke Dashboard Warga.

### Metode 2: Pembuatan Akun oleh Administrator Desa
1. Masuk sebagai **Administrator** (`admin@sipades.go.id` / `Admin123!`).
2. Buka menu **Kelola Pengguna** (`/admin/users`).
3. Klik tombol **"+ Tambah Pengguna"**.
4. Pada pilihan **Peran (Role)**, pilih **"Masyarakat / Warga"**.
5. Formulir akan otomatis menampilkan field khusus masyarakat: **NIK (16 digit)**, **Nomor WhatsApp/HP**, dan **Alamat Tempat Tinggal**.
6. Masukkan data warga dan kata sandi awal, lalu klik **"Simpan Akun"**.

---

## 🔄 Alur Kerja Pengujian End-to-End

1. **Warga Mengajukan Permohonan**:
   - Masuk sebagai `warga@sipades.go.id` / `Warga123!`.
   - Pilih jenis layanan (misal: *Surat Keterangan Domisili*).
   - Isi formulir dan unggah berkas KTP/KK (Status: `DIAJUKAN`).

2. **Petugas Memeriksa & Meminta Revisi / Verifikasi**:
   - Masuk sebagai `petugas@sipades.go.id` / `Petugas123!`.
   - Buka menu **Antrean Berkas**, periksa dokumen pemohon.
   - Klik **Verifikasi Berkas** untuk meneruskan ke Kepala Desa (Status: `MENUNGGU_PERSETUJUAN`).

3. **Kepala Desa Menyetujui (Approval)**:
   - Masuk sebagai `kades@sipades.go.id` / `Kades123!`.
   - Buka menu **Persetujuan Surat**, periksa rincian pengajuan.
   - Klik **Setujui Pengajuan** (Status: `DISETUJUI`).

4. **Petugas Menerbitkan Surat Resmi**:
   - Kembali ke akun `petugas@sipades.go.id`.
   - Klik **Terbitkan Surat Resmi**. Nomor surat resmi otomatis dibuat (`470/xxx/KD-SM/x/2026`) beserta QR Code keabsahan (Status: `SELESAI`).

5. **Verifikasi Keabsahan Surat Secara Publik**:
   - Buka tautan verifikasi surat publik tanpa login:
     `https://sipades.geezplay.site/verifikasi-surat/{token}`
   - Sistem akan menampilkan status **DOKUMEN ASLI DAN TERDAFTAR DI SISTEM SIPADES** dengan data NIK tersensor demi privasi.

---

## 🔒 Catatan Keamanan
- Seluruh kata sandi di atas disimpan menggunakan algoritma enkripsi satu arah **bcrypt**.
- Berkas fisik dokumen tersimpan di direktori server terproteksi (`storage/app/private/`) dan hanya dapat diakses oleh pemilik akun atau staf berwenang.
