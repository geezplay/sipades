<?php

namespace Database\Seeders;

use App\Models\JenisPelayanan;
use App\Models\KepalaDesa;
use App\Models\Masyarakat;
use App\Models\Petugas;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Akun Admin
        User::updateOrCreate(
            ['email' => 'admin@sipades.go.id'],
            [
                'id_user' => (string) Str::uuid(),
                'nama' => 'Administrator SIPADES',
                'password' => Hash::make('Admin123!'),
                'role' => 'admin',
            ]
        );

        // 2. Akun Petugas
        $userPetugas = User::updateOrCreate(
            ['email' => 'petugas@sipades.go.id'],
            [
                'id_user' => (string) Str::uuid(),
                'nama' => 'Budi Santoso, S.AP',
                'password' => Hash::make('Petugas123!'),
                'role' => 'petugas',
            ]
        );

        Petugas::updateOrCreate(
            ['id_user' => $userPetugas->id_user],
            [
                'id_petugas' => (string) Str::uuid(),
                'jabatan' => 'Staf Pelayanan Umum',
            ]
        );

        // 3. Akun Kepala Desa
        $userKades = User::updateOrCreate(
            ['email' => 'kades@sipades.go.id'],
            [
                'id_user' => (string) Str::uuid(),
                'nama' => 'H. Muhammad Rizki, S.Sos',
                'password' => Hash::make('Kades123!'),
                'role' => 'kepala',
            ]
        );

        KepalaDesa::updateOrCreate(
            ['id_user' => $userKades->id_user],
            [
                'id_pimpinan' => (string) Str::uuid(),
                'jabatan' => 'Kepala Desa',
                'nip' => '19750812 200212 1 003',
                'tanda_tangan_digital' => 'kades_signature_default.png',
                'is_active' => true,
            ]
        );

        // 4. Akun Warga (Masyarakat)
        $userWarga = User::updateOrCreate(
            ['email' => 'warga@sipades.go.id'],
            [
                'id_user' => (string) Str::uuid(),
                'nama' => 'Ahmad Subagyo',
                'password' => Hash::make('Warga123!'),
                'role' => 'masyarakat',
            ]
        );

        Masyarakat::updateOrCreate(
            ['id_user' => $userWarga->id_user],
            [
                'id_masyarakat' => (string) Str::uuid(),
                'nik' => '3201123456780001',
                'alamat' => 'Jl. Merdeka No. 45 RT 02/03',
                'no_hp' => '081234567890',
            ]
        );

        // 5. 6 Master Jenis Pelayanan
        $pelayanan = [
            [
                'id_pelayanan' => 'ply_domisili',
                'nama_pelayanan' => 'Surat Keterangan Domisili',
                'deskripsi' => 'Surat yang menerangkan bahwa seseorang bertempat tinggal di wilayah desa/kelurahan tertentu.',
                'persyaratan' => 'KTP,Kartu Keluarga (KK),Surat Pengantar RT/RW',
                'icon' => 'fa-house',
                'formulir_schema' => [
                    ['name' => 'nama_lengkap', 'label' => 'Nama Lengkap', 'type' => 'text', 'required' => true],
                    ['name' => 'nik', 'label' => 'NIK', 'type' => 'text', 'required' => true],
                    ['name' => 'tempat_lahir', 'label' => 'Tempat Lahir', 'type' => 'text', 'required' => true],
                    ['name' => 'tanggal_lahir', 'label' => 'Tanggal Lahir', 'type' => 'date', 'required' => true],
                    ['name' => 'jenis_kelamin', 'label' => 'Jenis Kelamin', 'type' => 'select', 'options' => ['Laki-laki', 'Perempuan'], 'required' => true],
                    ['name' => 'agama', 'label' => 'Agama', 'type' => 'select', 'options' => ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'], 'required' => true],
                    ['name' => 'pekerjaan', 'label' => 'Pekerjaan', 'type' => 'text', 'required' => true],
                    ['name' => 'alamat_lengkap', 'label' => 'Alamat Lengkap', 'type' => 'textarea', 'required' => true],
                    ['name' => 'keperluan', 'label' => 'Keperluan', 'type' => 'textarea', 'required' => true],
                ],
                'is_active' => true,
            ],
            [
                'id_pelayanan' => 'ply_skck',
                'nama_pelayanan' => 'Surat Pengantar SKCK',
                'deskripsi' => 'Surat pengantar untuk permohonan Surat Keterangan Catatan Kepolisian (SKCK) dari pihak kepolisian.',
                'persyaratan' => 'KTP,Kartu Keluarga (KK),Surat Pengantar RT/RW,Pas Foto 4x6',
                'icon' => 'fa-shield-halved',
                'formulir_schema' => [
                    ['name' => 'nama_lengkap', 'label' => 'Nama Lengkap', 'type' => 'text', 'required' => true],
                    ['name' => 'nik', 'label' => 'NIK', 'type' => 'text', 'required' => true],
                    ['name' => 'tempat_lahir', 'label' => 'Tempat Lahir', 'type' => 'text', 'required' => true],
                    ['name' => 'tanggal_lahir', 'label' => 'Tanggal Lahir', 'type' => 'date', 'required' => true],
                    ['name' => 'jenis_kelamin', 'label' => 'Jenis Kelamin', 'type' => 'select', 'options' => ['Laki-laki', 'Perempuan'], 'required' => true],
                    ['name' => 'agama', 'label' => 'Agama', 'type' => 'select', 'options' => ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'], 'required' => true],
                    ['name' => 'pekerjaan', 'label' => 'Pekerjaan', 'type' => 'text', 'required' => true],
                    ['name' => 'alamat_lengkap', 'label' => 'Alamat Lengkap', 'type' => 'textarea', 'required' => true],
                    ['name' => 'keperluan', 'label' => 'Keperluan', 'type' => 'textarea', 'required' => true],
                ],
                'is_active' => true,
            ],
            [
                'id_pelayanan' => 'ply_usaha',
                'nama_pelayanan' => 'Surat Keterangan Usaha',
                'deskripsi' => 'Surat yang menerangkan bahwa seseorang memiliki usaha di wilayah desa/kelurahan.',
                'persyaratan' => 'KTP,Kartu Keluarga (KK),Foto Tempat Usaha,Surat Pengantar RT/RW',
                'icon' => 'fa-briefcase',
                'formulir_schema' => [
                    ['name' => 'nama_lengkap', 'label' => 'Nama Lengkap', 'type' => 'text', 'required' => true],
                    ['name' => 'nik', 'label' => 'NIK', 'type' => 'text', 'required' => true],
                    ['name' => 'alamat', 'label' => 'Alamat', 'type' => 'textarea', 'required' => true],
                    ['name' => 'nama_usaha', 'label' => 'Nama Usaha', 'type' => 'text', 'required' => true],
                    ['name' => 'jenis_usaha', 'label' => 'Jenis Usaha', 'type' => 'text', 'required' => true],
                    ['name' => 'alamat_usaha', 'label' => 'Alamat Usaha', 'type' => 'textarea', 'required' => true],
                    ['name' => 'tahun_berdiri', 'label' => 'Tahun Berdiri', 'type' => 'number', 'required' => true],
                    ['name' => 'keperluan', 'label' => 'Keperluan', 'type' => 'textarea', 'required' => true],
                ],
                'is_active' => true,
            ],
            [
                'id_pelayanan' => 'ply_belum_menikah',
                'nama_pelayanan' => 'Surat Keterangan Belum Menikah',
                'deskripsi' => 'Surat yang menerangkan bahwa seseorang belum pernah menikah/berstatus lajang.',
                'persyaratan' => 'KTP,Kartu Keluarga (KK),Surat Pernyataan Belum Menikah Bermaterai,Surat Pengantar RT/RW',
                'icon' => 'fa-ring',
                'formulir_schema' => [
                    ['name' => 'nama_lengkap', 'label' => 'Nama Lengkap', 'type' => 'text', 'required' => true],
                    ['name' => 'nik', 'label' => 'NIK', 'type' => 'text', 'required' => true],
                    ['name' => 'tempat_lahir', 'label' => 'Tempat Lahir', 'type' => 'text', 'required' => true],
                    ['name' => 'tanggal_lahir', 'label' => 'Tanggal Lahir', 'type' => 'date', 'required' => true],
                    ['name' => 'jenis_kelamin', 'label' => 'Jenis Kelamin', 'type' => 'select', 'options' => ['Laki-laki', 'Perempuan'], 'required' => true],
                    ['name' => 'agama', 'label' => 'Agama', 'type' => 'select', 'options' => ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'], 'required' => true],
                    ['name' => 'pekerjaan', 'label' => 'Pekerjaan', 'type' => 'text', 'required' => true],
                    ['name' => 'alamat_lengkap', 'label' => 'Alamat Lengkap', 'type' => 'textarea', 'required' => true],
                    ['name' => 'keperluan', 'label' => 'Keperluan', 'type' => 'textarea', 'required' => true],
                ],
                'is_active' => true,
            ],
            [
                'id_pelayanan' => 'ply_kelahiran',
                'nama_pelayanan' => 'Surat Keterangan Kelahiran',
                'deskripsi' => 'Surat yang menerangkan telah lahirnya seorang anak di wilayah desa/kelurahan.',
                'persyaratan' => 'KTP Orang Tua,Kartu Keluarga (KK),Surat Keterangan Lahir dari Bidan/RS,Buku Nikah/Akta Nikah',
                'icon' => 'fa-baby',
                'formulir_schema' => [
                    ['name' => 'nama_bayi', 'label' => 'Nama Bayi', 'type' => 'text', 'required' => true],
                    ['name' => 'jenis_kelamin', 'label' => 'Jenis Kelamin', 'type' => 'select', 'options' => ['Laki-laki', 'Perempuan'], 'required' => true],
                    ['name' => 'tempat_lahir', 'label' => 'Tempat Lahir', 'type' => 'text', 'required' => true],
                    ['name' => 'tanggal_lahir', 'label' => 'Tanggal Lahir', 'type' => 'date', 'required' => true],
                    ['name' => 'nama_ayah', 'label' => 'Nama Ayah', 'type' => 'text', 'required' => true],
                    ['name' => 'nik_ayah', 'label' => 'NIK Ayah', 'type' => 'text', 'required' => true],
                    ['name' => 'nama_ibu', 'label' => 'Nama Ibu', 'type' => 'text', 'required' => true],
                    ['name' => 'nik_ibu', 'label' => 'NIK Ibu', 'type' => 'text', 'required' => true],
                    ['name' => 'alamat', 'label' => 'Alamat', 'type' => 'textarea', 'required' => true],
                ],
                'is_active' => true,
            ],
            [
                'id_pelayanan' => 'ply_kematian',
                'nama_pelayanan' => 'Surat Keterangan Kematian',
                'deskripsi' => 'Surat yang menerangkan telah meninggalnya seseorang di wilayah desa/kelurahan.',
                'persyaratan' => 'KTP Almarhum/ah,Kartu Keluarga (KK),KTP Pelapor,Surat Keterangan dari RS/Dokter (jika ada),Surat Pengantar RT/RW',
                'icon' => 'fa-scroll',
                'formulir_schema' => [
                    ['name' => 'nama_almarhum', 'label' => 'Nama Almarhum/ah', 'type' => 'text', 'required' => true],
                    ['name' => 'nik', 'label' => 'NIK Almarhum/ah', 'type' => 'text', 'required' => true],
                    ['name' => 'tempat_lahir', 'label' => 'Tempat Lahir', 'type' => 'text', 'required' => true],
                    ['name' => 'tanggal_lahir', 'label' => 'Tanggal Lahir', 'type' => 'date', 'required' => true],
                    ['name' => 'jenis_kelamin', 'label' => 'Jenis Kelamin', 'type' => 'select', 'options' => ['Laki-laki', 'Perempuan'], 'required' => true],
                    ['name' => 'alamat', 'label' => 'Alamat Terakhir', 'type' => 'textarea', 'required' => true],
                    ['name' => 'tanggal_meninggal', 'label' => 'Tanggal Meninggal', 'type' => 'date', 'required' => true],
                    ['name' => 'tempat_meninggal', 'label' => 'Tempat Meninggal', 'type' => 'text', 'required' => true],
                    ['name' => 'sebab_kematian', 'label' => 'Sebab Kematian', 'type' => 'text', 'required' => true],
                    ['name' => 'nama_pelapor', 'label' => 'Nama Pelapor', 'type' => 'text', 'required' => true],
                    ['name' => 'hubungan_pelapor', 'label' => 'Hubungan dengan Almarhum/ah', 'type' => 'text', 'required' => true],
                ],
                'is_active' => true,
            ],
        ];

        foreach ($pelayanan as $item) {
            JenisPelayanan::updateOrCreate(
                ['id_pelayanan' => $item['id_pelayanan']],
                $item
            );
        }
    }
}
