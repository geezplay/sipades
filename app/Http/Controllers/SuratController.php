<?php

namespace App\Http\Controllers;

use App\Models\Pengajuan;
use App\Models\Surat;
use App\Services\SuratService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SuratController extends Controller
{
    protected SuratService $suratService;

    public function __construct(SuratService $suratService)
    {
        $this->suratService = $suratService;
    }

    /**
     * Generate an official letter for an approved Pengajuan.
     * Route: POST /surat/{id_pengajuan}/generate (Name: surat.generate)
     * Accessible by: petugas, admin.
     */
    public function generate(Request $request, string $id_pengajuan): RedirectResponse|JsonResponse
    {
        $user = Auth::user();
        if (!$user || !in_array($user->role, ['petugas', 'admin', 'kepala'])) {
            abort(403, 'Hanya petugas atau admin yang berhak menerbitkan surat.');
        }

        $pengajuan = Pengajuan::with(['masyarakat.user', 'jenisPelayanan', 'surat'])
            ->findOrFail($id_pengajuan);

        // Check if pengajuan status permits letter generation
        if (!in_array($pengajuan->status, ['DISETUJUI', 'DIPROSES', 'SELESAI'])) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Pengajuan belum disetujui oleh Kepala Desa (Status saat ini: ' . $pengajuan->status . ').',
                ], 422);
            }

            return back()->with('error', 'Pengajuan belum disetujui oleh Kepala Desa (Status saat ini: ' . $pengajuan->status . ').');
        }

        $idPimpinan = $request->input('id_pimpinan');
        $surat = $this->suratService->generateSurat($pengajuan, $idPimpinan);

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Surat dinas resmi berhasil diterbitkan.',
                'surat'   => [
                    'id_surat'      => $surat->id_surat,
                    'nomor_surat'   => $surat->nomor_surat,
                    'tanggal_surat' => $surat->tanggal_surat?->toDateString(),
                    'download_url'  => route('surat.download', ['id' => $surat->id_surat]),
                    'preview_url'   => route('surat.preview', ['id' => $surat->id_surat]),
                ],
            ]);
        }

        return back()->with('success', 'Surat dinas nomor ' . $surat->nomor_surat . ' berhasil diterbitkan.');
    }

    /**
     * Download letter PDF with security authorization.
     * Route: GET /surat/{id}/download (Name: surat.download)
     */
    public function download(Request $request, string $id): BinaryFileResponse|StreamedResponse
    {
        $surat = $this->resolveSuratWithAuth($id);
        $filePath = $this->ensurePdfExists($surat);

        $downloadFilename = str_replace('/', '_', $surat->nomor_surat) . '.pdf';

        if (Storage::disk('local')->exists($filePath)) {
            return Storage::disk('local')->download($filePath, $downloadFilename);
        }

        $fullPath = storage_path('app/private/' . $filePath);
        if (!file_exists($fullPath)) {
            $fullPath = storage_path('app/' . $filePath);
        }

        return response()->download($fullPath, $downloadFilename);
    }

    /**
     * Preview letter PDF inline in browser.
     * Route: GET /surat/{id}/preview (Name: surat.preview)
     */
    public function preview(Request $request, string $id): BinaryFileResponse|StreamedResponse
    {
        $surat = $this->resolveSuratWithAuth($id);
        $filePath = $this->ensurePdfExists($surat);

        if (Storage::disk('local')->exists($filePath)) {
            $fullPath = Storage::disk('local')->path($filePath);
        } else {
            $fullPath = storage_path('app/private/' . $filePath);
            if (!file_exists($fullPath)) {
                $fullPath = storage_path('app/' . $filePath);
            }
        }

        return response()->file($fullPath, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . basename($fullPath) . '"',
        ]);
    }

    /**
     * Resolve Surat by id_surat or id_pengajuan, and authorize the current user.
     */
    protected function resolveSuratWithAuth(string $id): Surat
    {
        $user = Auth::user();
        if (!$user) {
            abort(401, 'Silakan login terlebih dahulu untuk mengakses surat.');
        }

        // Search by id_surat or by id_pengajuan
        $surat = Surat::with(['pengajuan.masyarakat', 'kepalaDesa.user'])
            ->where('id_surat', $id)
            ->orWhere('id_pengajuan', $id)
            ->firstOrFail();

        // Staff, village head, and administrators have full view access
        if (in_array($user->role, ['petugas', 'kepala', 'admin'])) {
            return $surat;
        }

        // For citizen (masyarakat), verify ownership
        $pengajuan = $surat->pengajuan;
        if ($pengajuan && $pengajuan->masyarakat) {
            if ($pengajuan->masyarakat->id_user === $user->id_user) {
                return $surat;
            }
        }

        abort(403, 'Akses Ditolak: Anda tidak memiliki hak akses untuk mengunduh dokumen ini.');
    }

    /**
     * Verify PDF exists on disk or regenerate if missing.
     */
    protected function ensurePdfExists(Surat $surat): string
    {
        $filePath = $surat->file_pdf_path;

        if (empty($filePath) || !Storage::disk('local')->exists($filePath)) {
            $filePath = $this->suratService->regeneratePdf($surat);
        }

        return $filePath;
    }
}
