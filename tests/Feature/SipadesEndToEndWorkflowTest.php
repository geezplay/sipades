<?php

namespace Tests\Feature;

use App\Models\JenisPelayanan;
use App\Models\Masyarakat;
use App\Models\Pengajuan;
use App\Models\Surat;
use App\Models\User;
use App\Services\SuratService;
use App\Services\WorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SipadesEndToEndWorkflowTest extends TestCase
{
    public function test_complete_sipades_workflow_and_verification()
    {
        // 1. Ambil Akun Seeder
        $wargaUser = User::where('email', 'warga@sipades.go.id')->firstOrFail();
        $petugasUser = User::where('email', 'petugas@sipades.go.id')->firstOrFail();
        $kadesUser = User::where('email', 'kades@sipades.go.id')->firstOrFail();
        $adminUser = User::where('email', 'admin@sipades.go.id')->firstOrFail();

        $masyarakat = $wargaUser->masyarakat;
        $this->assertNotNull($masyarakat);

        $layanan = JenisPelayanan::where('id_pelayanan', 'ply_domisili')->firstOrFail();

        // 2. Warga Mengajukan Permohonan (DIAJUKAN)
        $fakePdf = UploadedFile::fake()->create('ktp_warga.pdf', 150, 'application/pdf');

        $workflowService = app(WorkflowService::class);
        $pengajuan = $workflowService->createPengajuan(
            $masyarakat->id_masyarakat,
            $layanan->id_pelayanan,
            [
                'keperluan' => 'Pengurusan Rekening Bank Kedinasan',
                'alamat_sekarang' => 'Kp. Sukamaju RT 02/03',
                'sejak_tahun' => '2018',
            ],
            [$fakePdf],
            'Mohon diproses segera untuk keperluan dinas.'
        );

        $this->assertEquals('DIAJUKAN', $pengajuan->status);
        $this->assertNotEmpty($pengajuan->nomor_tiket);
        $this->assertCount(1, $pengajuan->dokumen);

        // 3. Petugas Melakukan Pemeriksaan & Minta Revisi (REVISI)
        $petugas = $petugasUser->petugas;
        $pengajuan = $workflowService->mintaRevisi(
            $pengajuan->id_pengajuan,
            $petugas->id_petugas,
            'Mohon lampirkan scan Kartu Keluarga terbaru yang jelas terbaca.'
        );

        $this->assertEquals('REVISI', $pengajuan->status);
        $this->assertEquals('Mohon lampirkan scan Kartu Keluarga terbaru yang jelas terbaca.', $pengajuan->catatan_revisi);

        // 4. Warga Mengunggah Berkas Revisi (Kembali DIPERIKSA)
        $fakeKkPdf = UploadedFile::fake()->create('kartu_keluarga_revisi.pdf', 200, 'application/pdf');
        $pengajuan = $workflowService->submitRevisi(
            $pengajuan->id_pengajuan,
            [
                'keperluan' => 'Pengurusan Rekening Bank Kedinasan (Data Diperbarui)',
                'alamat_sekarang' => 'Kp. Sukamaju RT 02/03',
                'sejak_tahun' => '2018',
            ],
            [$fakeKkPdf],
            'Sudah saya lampirkan scan KK asli sesuai arahan.'
        );

        $this->assertEquals('DIPERIKSA', $pengajuan->status);
        $this->assertCount(2, $pengajuan->fresh()->dokumen);

        // 5. Petugas Memverifikasi Berkas (MENUNGGU_PERSETUJUAN)
        $pengajuan = $workflowService->verifikasiPetugas(
            $pengajuan->id_pengajuan,
            $petugas->id_petugas,
            'Berkas lengkap dan valid, diteruskan ke Kepala Desa.'
        );

        $this->assertEquals('MENUNGGU_PERSETUJUAN', $pengajuan->status);

        // 6. Kepala Desa Menyetujui (DISETUJUI)
        $kades = $kadesUser->kepalaDesa;
        $pengajuan = $workflowService->approveKepala(
            $pengajuan->id_pengajuan,
            $kades->id_pimpinan,
            'Disetujui. Silakan terbitkan surat dinas resmi.'
        );

        $this->assertEquals('DISETUJUI', $pengajuan->status);

        // 7. Petugas Menerbitkan Surat Resmi (SELESAI, Surat + QR Generated)
        $suratService = app(SuratService::class);
        $surat = $suratService->generateSurat(
            $pengajuan->id_pengajuan,
            $kades->id_pimpinan
        );

        $this->assertNotNull($surat);
        $this->assertNotEmpty($surat->nomor_surat);
        $this->assertNotEmpty($surat->qr_token);
        $this->assertTrue(Storage::disk('local')->exists($surat->file_pdf_path));

        $pengajuan->refresh();
        $this->assertEquals('SELESAI', $pengajuan->status);

        // 8. Pengujian Verifikasi Publik QR Code (Tanpa Autentikasi)
        $response = $this->get('/verifikasi-surat/' . $surat->qr_token);
        $response->assertStatus(200);
        $response->assertSee('DOKUMEN ASLI');
        $response->assertSee($surat->nomor_surat);
        $response->assertSee('H. Muhammad Rizki, S.Sos');

        // 9. Pengujian Otorisasi RBAC (Masyarakat dilarang akses area Petugas / Kepala / Admin)
        $this->actingAs($wargaUser);
        $forbiddenPetugas = $this->get('/petugas/pengajuan');
        // CheckRole middleware returns 403 or redirect with error
        $this->assertTrue(in_array($forbiddenPetugas->status(), [302, 403]));

        $forbiddenAdmin = $this->get('/admin/users');
        $this->assertTrue(in_array($forbiddenAdmin->status(), [302, 403]));

        // 10. Pengujian Akses Download Surat bagi Warga Pemilik
        $downloadResponse = $this->get('/surat/' . $surat->id_surat . '/download');
        $downloadResponse->assertStatus(200);

        // 11. Pengujian Proteksi Dokumen (Warga lain tidak boleh unduh jika bukan miliknya)
        $randomSuffix = \Illuminate\Support\Str::random(6);
        $wargaLain = User::create([
            'nama' => 'Warga Lain',
            'email' => "warga.lain.{$randomSuffix}@sipades.go.id",
            'password' => bcrypt('Password123!'),
            'role' => 'masyarakat',
        ]);
        $masyarakatLain = Masyarakat::create([
            'id_user' => $wargaLain->id_user,
            'nik' => '3201' . rand(100000000000, 999999999999),
            'alamat' => 'Desa Sebelah',
            'no_hp' => '08999999999',
        ]);

        $this->actingAs($wargaLain);
        $unauthorizedDownload = $this->get('/surat/' . $surat->id_surat . '/download');
        $unauthorizedDownload->assertStatus(403);
    }
}
