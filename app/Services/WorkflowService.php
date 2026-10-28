<?php

namespace App\Services;

use App\Models\Dokumen;
use App\Models\KepalaDesa;
use App\Models\Masyarakat;
use App\Models\Notifikasi;
use App\Models\Pengajuan;
use App\Models\Petugas;
use App\Models\Surat;
use App\Models\User;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

class WorkflowService
{
    // Status Constants
    public const STATUS_DIAJUKAN = 'DIAJUKAN';
    public const STATUS_DIPERIKSA = 'DIPERIKSA';
    public const STATUS_REVISI = 'REVISI';
    public const STATUS_DIVERIFIKASI = 'DIVERIFIKASI';
    public const STATUS_MENUNGGU_PERSETUJUAN = 'MENUNGGU_PERSETUJUAN';
    public const STATUS_DISETUJUI = 'DISETUJUI';
    public const STATUS_DITOLAK = 'DITOLAK';
    public const STATUS_DIPROSES = 'DIPROSES';
    public const STATUS_SELESAI = 'SELESAI';

    /**
     * Map of allowed state transitions.
     * key: current status -> values: allowed next statuses
     */
    protected array $allowedTransitions = [
        self::STATUS_DIAJUKAN => [
            self::STATUS_DIPERIKSA,
            self::STATUS_REVISI,
            self::STATUS_DIVERIFIKASI,
            self::STATUS_MENUNGGU_PERSETUJUAN,
            self::STATUS_DITOLAK,
        ],
        self::STATUS_DIPERIKSA => [
            self::STATUS_REVISI,
            self::STATUS_DIVERIFIKASI,
            self::STATUS_MENUNGGU_PERSETUJUAN,
            self::STATUS_DITOLAK,
        ],
        self::STATUS_REVISI => [
            self::STATUS_DIPERIKSA,
            self::STATUS_DITOLAK,
        ],
        self::STATUS_DIVERIFIKASI => [
            self::STATUS_MENUNGGU_PERSETUJUAN,
            self::STATUS_DITOLAK,
        ],
        self::STATUS_MENUNGGU_PERSETUJUAN => [
            self::STATUS_DISETUJUI,
            self::STATUS_DITOLAK,
            self::STATUS_REVISI,
        ],
        self::STATUS_DISETUJUI => [
            self::STATUS_DIPROSES,
            self::STATUS_SELESAI,
        ],
        self::STATUS_DIPROSES => [
            self::STATUS_SELESAI,
        ],
        self::STATUS_SELESAI => [],
        self::STATUS_DITOLAK => [],
    ];

    /**
     * Generate unique ticket number format: TIKET-YYYYMMDD-XXXX
     */
    public function generateNomorTiket(): string
    {
        $datePrefix = date('Ymd');
        do {
            $randomCode = strtoupper(Str::random(6));
            $nomorTiket = "TIKET-{$datePrefix}-{$randomCode}";
        } while (Pengajuan::where('nomor_tiket', $nomorTiket)->exists());

        return $nomorTiket;
    }

    /**
     * Verify if a state transition is permitted.
     */
    public function canTransition(string $fromStatus, string $toStatus): bool
    {
        if ($fromStatus === $toStatus) {
            return true;
        }

        return in_array($toStatus, $this->allowedTransitions[$fromStatus] ?? [], true);
    }

    /**
     * Assert state transition validity or throw exception.
     */
    public function assertCanTransition(Pengajuan $pengajuan, string $targetStatus): void
    {
        if (!$this->canTransition($pengajuan->status, $targetStatus)) {
            throw new InvalidArgumentException(
                "Transisi status dari '{$pengajuan->status}' ke '{$targetStatus}' tidak diizinkan."
            );
        }
    }

    /**
     * Helper to resolve Pengajuan instance.
     */
    protected function resolvePengajuan(Pengajuan|string $pengajuan): Pengajuan
    {
        if ($pengajuan instanceof Pengajuan) {
            return $pengajuan;
        }

        return Pengajuan::with(['masyarakat.user', 'jenisPelayanan', 'dokumen'])->findOrFail($pengajuan);
    }

    /**
     * Create a new Pengajuan (Warga action).
     *
     * @param string $idMasyarakat
     * @param string $idPelayanan
     * @param array $dataFormulir
     * @param array $uploadedFiles Array of [nama_dokumen => UploadedFile] or list of UploadedFile
     * @param string|null $catatan
     * @return Pengajuan
     */
    public function createPengajuan(
        string $idMasyarakat,
        string $idPelayanan,
        array $dataFormulir,
        array $uploadedFiles = [],
        ?string $catatan = null
    ): Pengajuan {
        return DB::transaction(function () use ($idMasyarakat, $idPelayanan, $dataFormulir, $uploadedFiles, $catatan) {
            $nomorTiket = $this->generateNomorTiket();

            $pengajuan = Pengajuan::create([
                'id_pengajuan' => (string) Str::uuid(),
                'nomor_tiket' => $nomorTiket,
                'id_masyarakat' => $idMasyarakat,
                'id_pelayanan' => $idPelayanan,
                'tanggal_pengajuan' => now(),
                'status' => self::STATUS_DIAJUKAN,
                'data_formulir' => $dataFormulir,
                'catatan' => $catatan,
            ]);

            // Save uploaded documents
            foreach ($uploadedFiles as $key => $file) {
                if ($file instanceof UploadedFile) {
                    $namaDokumen = is_string($key) && !is_numeric($key) ? $key : null;
                    $this->storeDokumen($pengajuan, $file, $namaDokumen);
                }
            }

            // Load relations for notifications
            $pengajuan->load(['masyarakat.user', 'jenisPelayanan']);
            $namaPemohon = $pengajuan->masyarakat?->user?->nama ?? 'Warga';
            $namaPelayanan = $pengajuan->jenisPelayanan?->nama_pelayanan ?? 'Pelayanan Desa';

            // Send notification to all Petugas
            $linkUrl = Route::has('petugas.pengajuan.show')
                ? route('petugas.pengajuan.show', $pengajuan->id_pengajuan)
                : "/petugas/pengajuan/{$pengajuan->id_pengajuan}";

            $this->notifikasiPetugas(
                "Pengajuan Baru: {$nomorTiket}",
                "Permohonan {$namaPelayanan} baru diajukan oleh {$namaPemohon} dengan nomor tiket {$nomorTiket}.",
                $linkUrl
            );

            return $pengajuan;
        });
    }

    /**
     * Store an uploaded document into secure private storage and create Dokumen record.
     */
    public function storeDokumen(
        Pengajuan $pengajuan,
        UploadedFile $file,
        ?string $namaDokumen = null
    ): Dokumen {
        $allowedMimes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
        $mime = $file->getMimeType() ?: $file->getClientMimeType();

        if (!in_array($mime, $allowedMimes, true)) {
            throw new InvalidArgumentException("Format berkas {$file->getClientOriginalName()} tidak didukung. Gunakan PDF, JPG, atau PNG.");
        }

        // Limit 2MB (2048 KB)
        if ($file->getSize() > 2048 * 1024) {
            throw new InvalidArgumentException("Ukuran berkas {$file->getClientOriginalName()} melebihi batas maksimum 2MB.");
        }

        $extension = $file->getClientOriginalExtension() ?: ($mime === 'application/pdf' ? 'pdf' : 'jpg');
        $rawBaseName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $cleanBaseName = Str::slug($rawBaseName);
        $fileName = $cleanBaseName . '_' . time() . '_' . Str::random(6) . '.' . $extension;

        // Store into private storage: storage/app/private/documents/{id_pengajuan}/{filename}
        $relativeDir = "documents/{$pengajuan->id_pengajuan}";
        $storedPath = $file->storeAs($relativeDir, $fileName, 'local');

        $docName = $namaDokumen ?: ($file->getClientOriginalName() ?: 'Dokumen Pendukung');

        return Dokumen::create([
            'id_dokumen' => (string) Str::uuid(),
            'id_pengajuan' => $pengajuan->id_pengajuan,
            'nama_dokumen' => $docName,
            'file_path' => $storedPath,
            'file_size' => $file->getSize(),
            'mime_type' => $mime,
            'tanggal_upload' => now(),
        ]);
    }

    /**
     * Petugas claims / begins examining the pengajuan.
     */
    public function periksaPengajuan(Pengajuan|string $pengajuan, ?string $petugasId = null): Pengajuan
    {
        $item = $this->resolvePengajuan($pengajuan);

        if ($item->status === self::STATUS_DIAJUKAN || $item->status === self::STATUS_REVISI) {
            $item->status = self::STATUS_DIPERIKSA;
            $item->save();
        }

        return $item;
    }

    /**
     * Petugas requests revision with specific notes.
     */
    public function mintaRevisi(
        Pengajuan|string $pengajuan,
        string $petugasId,
        string $catatanRevisi
    ): Pengajuan {
        $item = $this->resolvePengajuan($pengajuan);
        $this->assertCanTransition($item, self::STATUS_REVISI);

        return DB::transaction(function () use ($item, $petugasId, $catatanRevisi) {
            $item->status = self::STATUS_REVISI;
            $item->catatan_revisi = $catatanRevisi;
            $item->save();

            // Log verifikasi
            $item->verifikasi()->create([
                'id_verifikasi' => (string) Str::uuid(),
                'id_petugas' => $petugasId,
                'tanggal_verifikasi' => now(),
                'hasil' => 'REVISI',
                'catatan' => $catatanRevisi,
            ]);

            // Notify pemohon (warga)
            $idUserWarga = $item->masyarakat?->id_user;
            if ($idUserWarga) {
                $linkUrl = Route::has('warga.pengajuan.show')
                    ? route('warga.pengajuan.show', $item->id_pengajuan)
                    : "/warga/pengajuan/{$item->id_pengajuan}";

                $this->notifikasiUser(
                    $idUserWarga,
                    "Permintaan Revisi Dokumen: {$item->nomor_tiket}",
                    "Petugas meminta perbaikan dokumen: {$catatanRevisi}. Silakan perbaiki data/unggah ulang berkas Anda.",
                    $linkUrl
                );
            }

            return $item;
        });
    }

    /**
     * Warga submits revision (updates form and uploads revised documents).
     */
    public function submitRevisi(
        Pengajuan|string $pengajuan,
        array $dataFormulir = [],
        array $newUploadedFiles = []
    ): Pengajuan {
        $item = $this->resolvePengajuan($pengajuan);
        $this->assertCanTransition($item, self::STATUS_DIPERIKSA);

        return DB::transaction(function () use ($item, $dataFormulir, $newUploadedFiles) {
            // Merge or update form data
            if (!empty($dataFormulir)) {
                $currentData = is_array($item->data_formulir) ? $item->data_formulir : [];
                $item->data_formulir = array_merge($currentData, $dataFormulir);
            }

            // Upload additional/revised files
            foreach ($newUploadedFiles as $key => $file) {
                if ($file instanceof UploadedFile) {
                    $namaDokumen = is_string($key) && !is_numeric($key) ? $key : null;
                    $this->storeDokumen($item, $file, $namaDokumen);
                }
            }

            $item->status = self::STATUS_DIPERIKSA;
            $item->save();

            // Notify Petugas
            $linkUrl = Route::has('petugas.pengajuan.show')
                ? route('petugas.pengajuan.show', $item->id_pengajuan)
                : "/petugas/pengajuan/{$item->id_pengajuan}";

            $this->notifikasiPetugas(
                "Perbaikan Berkas Masuk: {$item->nomor_tiket}",
                "Pemohon telah menyerahkan perbaikan berkas untuk tiket {$item->nomor_tiket}. Siap untuk diperiksa kembali.",
                $linkUrl
            );

            return $item;
        });
    }

    /**
     * Petugas verifies documents and forwards pengajuan for Kepala Desa approval.
     */
    public function verifikasiPetugas(
        Pengajuan|string $pengajuan,
        string $petugasId,
        ?string $catatan = null
    ): Pengajuan {
        $item = $this->resolvePengajuan($pengajuan);
        $this->assertCanTransition($item, self::STATUS_MENUNGGU_PERSETUJUAN);

        return DB::transaction(function () use ($item, $petugasId, $catatan) {
            $item->status = self::STATUS_MENUNGGU_PERSETUJUAN;
            if ($catatan) {
                $item->catatan = $catatan;
            }
            $item->save();

            // Log verifikasi
            $item->verifikasi()->create([
                'id_verifikasi' => (string) Str::uuid(),
                'id_petugas' => $petugasId,
                'tanggal_verifikasi' => now(),
                'hasil' => 'TERVERIFIKASI',
                'catatan' => $catatan ?: 'Berkas lengkap dan terverifikasi sah oleh petugas.',
            ]);

            // Notify Kepala Desa
            $linkUrl = Route::has('kepala.pengajuan.show')
                ? route('kepala.pengajuan.show', $item->id_pengajuan)
                : "/kepala/pengajuan/{$item->id_pengajuan}";

            $namaPelayanan = $item->jenisPelayanan?->nama_pelayanan ?? 'Surat Permohonan';

            $this->notifikasiKepala(
                "Permohonan Persetujuan: {$item->nomor_tiket}",
                "Pengajuan {$namaPelayanan} ({$item->nomor_tiket}) telah diverifikasi oleh petugas dan menunggu persetujuan/tanda tangan Anda.",
                $linkUrl
            );

            return $item;
        });
    }

    /**
     * Kepala Desa approves the pengajuan.
     */
    public function approveKepala(
        Pengajuan|string $pengajuan,
        ?string $pimpinanId = null,
        ?string $catatan = null
    ): Pengajuan {
        $item = $this->resolvePengajuan($pengajuan);
        $this->assertCanTransition($item, self::STATUS_DISETUJUI);

        return DB::transaction(function () use ($item, $catatan) {
            $item->status = self::STATUS_DISETUJUI;
            if ($catatan) {
                $item->catatan = $catatan;
            }
            $item->save();

            // Notify Petugas to process and generate official letter
            $linkUrl = Route::has('petugas.pengajuan.show')
                ? route('petugas.pengajuan.show', $item->id_pengajuan)
                : "/petugas/pengajuan/{$item->id_pengajuan}";

            $this->notifikasiPetugas(
                "Pengajuan Disetujui Kepala Desa: {$item->nomor_tiket}",
                "Pengajuan {$item->nomor_tiket} telah disetujui oleh Kepala Desa. Silakan terbitkan nomor surat dan selesaikan dokumen.",
                $linkUrl
            );

            return $item;
        });
    }

    /**
     * Kepala Desa rejects the pengajuan with reason.
     */
    public function rejectKepala(
        Pengajuan|string $pengajuan,
        ?string $pimpinanId = null,
        string $alasanPenolakan = ''
    ): Pengajuan {
        $item = $this->resolvePengajuan($pengajuan);
        $this->assertCanTransition($item, self::STATUS_DITOLAK);

        return DB::transaction(function () use ($item, $alasanPenolakan) {
            $item->status = self::STATUS_DITOLAK;
            $item->alasan_penolakan = $alasanPenolakan;
            $item->save();

            // Notify Pemohon
            $idUserWarga = $item->masyarakat?->id_user;
            if ($idUserWarga) {
                $linkUrl = Route::has('warga.pengajuan.show')
                    ? route('warga.pengajuan.show', $item->id_pengajuan)
                    : "/warga/pengajuan/{$item->id_pengajuan}";

                $this->notifikasiUser(
                    $idUserWarga,
                    "Pengajuan Ditolak: {$item->nomor_tiket}",
                    "Mohon maaf, pengajuan Anda tidak dapat disetujui. Alasan: {$alasanPenolakan}",
                    $linkUrl
                );
            }

            return $item;
        });
    }

    /**
     * Generic rejection handler (from Petugas or Kepala).
     */
    public function tolakPengajuan(
        Pengajuan|string $pengajuan,
        string $userId,
        string $alasanPenolakan
    ): Pengajuan {
        $item = $this->resolvePengajuan($pengajuan);
        $this->assertCanTransition($item, self::STATUS_DITOLAK);

        return DB::transaction(function () use ($item, $userId, $alasanPenolakan) {
            $user = User::find($userId);

            if ($user && $user->role === 'petugas') {
                $petugas = Petugas::where('id_user', $userId)->first();
                if ($petugas) {
                    $item->verifikasi()->create([
                        'id_verifikasi' => (string) Str::uuid(),
                        'id_petugas' => $petugas->id_petugas,
                        'tanggal_verifikasi' => now(),
                        'hasil' => 'DITOLAK',
                        'catatan' => $alasanPenolakan,
                    ]);
                }
            }

            $item->status = self::STATUS_DITOLAK;
            $item->alasan_penolakan = $alasanPenolakan;
            $item->save();

            // Notify Warga
            $idUserWarga = $item->masyarakat?->id_user;
            if ($idUserWarga) {
                $linkUrl = Route::has('warga.pengajuan.show')
                    ? route('warga.pengajuan.show', $item->id_pengajuan)
                    : "/warga/pengajuan/{$item->id_pengajuan}";

                $this->notifikasiUser(
                    $idUserWarga,
                    "Pengajuan Ditolak: {$item->nomor_tiket}",
                    "Mohon maaf, permohonan Anda dengan nomor tiket {$item->nomor_tiket} ditolak. Alasan: {$alasanPenolakan}",
                    $linkUrl
                );
            }

            return $item;
        });
    }

    /**
     * Petugas starts drafting / processing the approved pengajuan.
     */
    public function prosesPengajuan(Pengajuan|string $pengajuan, ?string $petugasId = null): Pengajuan
    {
        $item = $this->resolvePengajuan($pengajuan);
        $this->assertCanTransition($item, self::STATUS_DIPROSES);

        $item->status = self::STATUS_DIPROSES;
        $item->save();

        return $item;
    }

    /**
     * Complete the pengajuan by generating Surat (PDF & QR Code) via SuratService.
     */
    public function selesaiPengajuan(
        Pengajuan|string $pengajuan,
        ?string $idPimpinan = null
    ): Pengajuan {
        $item = $this->resolvePengajuan($pengajuan);
        $this->assertCanTransition($item, self::STATUS_SELESAI);

        return DB::transaction(function () use ($item, $idPimpinan) {
            // Generate Surat using SuratService if available
            if (class_exists(\App\Services\SuratService::class)) {
                $suratService = app(\App\Services\SuratService::class);
                if (method_exists($suratService, 'generateSurat')) {
                    $suratService->generateSurat($item, $idPimpinan);
                }
            }

            $item->status = self::STATUS_SELESAI;
            $item->save();

            // Notify Warga
            $idUserWarga = $item->masyarakat?->id_user;
            if ($idUserWarga) {
                $linkUrl = Route::has('warga.pengajuan.show')
                    ? route('warga.pengajuan.show', $item->id_pengajuan)
                    : "/warga/pengajuan/{$item->id_pengajuan}";

                $this->notifikasiUser(
                    $idUserWarga,
                    "Surat Selesai Diterbitkan: {$item->nomor_tiket}",
                    "Surat permohonan Anda telah selesai diterbitkan dan siap diunduh secara resmi.",
                    $linkUrl
                );
            }

            return $item;
        });
    }

    // ==========================================
    // NOTIFICATION HELPERS
    // ==========================================

    /**
     * Create a notification record for a specific user.
     */
    public function notifikasiUser(
        string $idUser,
        string $judul,
        string $pesan,
        ?string $linkUrl = null
    ): Notifikasi {
        return Notifikasi::create([
            'id_notifikasi' => (string) Str::uuid(),
            'id_user' => $idUser,
            'judul' => $judul,
            'pesan' => $pesan,
            'link_url' => $linkUrl,
            'status_baca' => false,
        ]);
    }

    /**
     * Send notification to all Petugas (and Admin).
     */
    public function notifikasiPetugas(string $judul, string $pesan, ?string $linkUrl = null): void
    {
        $officers = User::whereIn('role', ['petugas', 'admin'])->get();
        foreach ($officers as $officer) {
            $this->notifikasiUser($officer->id_user, $judul, $pesan, $linkUrl);
        }
    }

    /**
     * Send notification to all Kepala Desa / Lurah.
     */
    public function notifikasiKepala(string $judul, string $pesan, ?string $linkUrl = null): void
    {
        $leaders = User::where('role', 'kepala')->get();
        foreach ($leaders as $leader) {
            $this->notifikasiUser($leader->id_user, $judul, $pesan, $linkUrl);
        }
    }

    /**
     * Send notification to a Masyarakat record.
     */
    public function notifikasiMasyarakat(
        Masyarakat|string $masyarakat,
        string $judul,
        string $pesan,
        ?string $linkUrl = null
    ): ?Notifikasi {
        $masyarakatModel = is_string($masyarakat) ? Masyarakat::find($masyarakat) : $masyarakat;
        if ($masyarakatModel && $masyarakatModel->id_user) {
            return $this->notifikasiUser($masyarakatModel->id_user, $judul, $pesan, $linkUrl);
        }

        return null;
    }
}
