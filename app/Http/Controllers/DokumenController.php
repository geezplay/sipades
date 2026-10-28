<?php

namespace App\Http\Controllers;

use App\Models\Dokumen;
use App\Models\Pengajuan;
use App\Services\WorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DokumenController extends Controller
{
    protected WorkflowService $workflowService;

    public function __construct(WorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    /**
     * Authorize user access to the document.
     */
    protected function authorizeAccess(Dokumen $dokumen): void
    {
        if (!Auth::check()) {
            abort(401, 'Silakan login terlebih dahulu untuk mengakses berkas ini.');
        }

        $user = Auth::user();

        // Admin, Petugas, and Kepala Desa have full access
        if (in_array($user->role, ['petugas', 'kepala', 'admin'], true)) {
            return;
        }

        // Masyarakat may only access documents of their own pengajuan
        if ($user->role === 'masyarakat') {
            $masyarakat = $user->masyarakat;
            $pengajuan = $dokumen->pengajuan;

            if ($masyarakat && $pengajuan && $pengajuan->id_masyarakat === $masyarakat->id_masyarakat) {
                return;
            }
        }

        abort(403, 'Anda tidak memiliki hak akses untuk membuka dokumen ini.');
    }

    /**
     * Resolve the absolute filesystem path for the given document.
     */
    protected function resolveFilePath(Dokumen $dokumen): string
    {
        // Try Storage local disk path first
        $path = Storage::disk('local')->path($dokumen->file_path);
        if (file_exists($path)) {
            return $path;
        }

        // Fallback candidate paths
        $candidates = [
            storage_path('app/private/' . ltrim($dokumen->file_path, '/')),
            storage_path('app/' . ltrim($dokumen->file_path, '/')),
            storage_path($dokumen->file_path),
        ];

        foreach ($candidates as $cand) {
            if (file_exists($cand)) {
                return $cand;
            }
        }

        abort(404, 'Berkas fisik dokumen tidak ditemukan pada penyimpanan server.');
    }

    /**
     * Stream / preview document inline (PDF/images) in browser.
     *
     * @param string $id
     * @return BinaryFileResponse
     */
    public function preview(string $id): BinaryFileResponse
    {
        $dokumen = Dokumen::with('pengajuan.masyarakat')->findOrFail($id);
        $this->authorizeAccess($dokumen);

        $filePath = $this->resolveFilePath($dokumen);
        $safeName = basename($dokumen->nama_dokumen);

        return response()->file($filePath, [
            'Content-Type' => $dokumen->mime_type ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="' . addslashes($safeName) . '"',
            'Cache-Control' => 'private, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * Alias for preview (/dokumen/{id}/view)
     */
    public function view(string $id): BinaryFileResponse
    {
        return $this->preview($id);
    }

    /**
     * Download document file with attachment disposition.
     *
     * @param string $id
     * @return BinaryFileResponse
     */
    public function download(string $id): BinaryFileResponse
    {
        $dokumen = Dokumen::with('pengajuan.masyarakat')->findOrFail($id);
        $this->authorizeAccess($dokumen);

        $filePath = $this->resolveFilePath($dokumen);
        $safeName = basename($dokumen->nama_dokumen);

        return response()->download($filePath, $safeName, [
            'Content-Type' => $dokumen->mime_type ?: 'application/octet-stream',
            'Cache-Control' => 'private, no-cache, must-revalidate',
        ]);
    }

    /**
     * Upload an additional / replacement document to an existing Pengajuan.
     */
    public function upload(Request $request, string $idPengajuan)
    {
        $pengajuan = Pengajuan::with('masyarakat')->findOrFail($idPengajuan);
        $user = Auth::user();

        // Only pemohon or petugas/admin can upload
        if ($user->role === 'masyarakat') {
            $masyarakat = $user->masyarakat;
            if (!$masyarakat || $pengajuan->id_masyarakat !== $masyarakat->id_masyarakat) {
                abort(403, 'Akses ditolak.');
            }
        } elseif (!in_array($user->role, ['petugas', 'admin'], true)) {
            abort(403, 'Akses ditolak.');
        }

        $request->validate([
            'file_dokumen' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'nama_dokumen' => 'nullable|string|max:150',
        ]);

        $file = $request->file('file_dokumen');
        $namaDokumen = $request->input('nama_dokumen') ?: $file->getClientOriginalName();

        $dokumen = $this->workflowService->storeDokumen($pengajuan, $file, $namaDokumen);

        return back()->with('success', "Berkas '{$dokumen->nama_dokumen}' berhasil diunggah.");
    }
}
