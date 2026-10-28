<?php

namespace App\Http\Controllers;

use App\Models\JenisPelayanan;
use App\Models\KepalaDesa;
use App\Models\Pengajuan;
use App\Models\Petugas;
use App\Services\WorkflowService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PengajuanController extends Controller
{
    protected WorkflowService $workflowService;

    public function __construct(WorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    /**
     * Dispatch universal /pengajuan/{id} based on current user role.
     */
    public function showUniversal(string $id)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        return match ($user->role) {
            'petugas', 'admin' => redirect()->route('petugas.pengajuan.show', $id),
            'kepala' => redirect()->route('kepala.pengajuan.show', $id),
            default => redirect()->route('warga.pengajuan.show', $id),
        };
    }

    // =========================================================================
    // WARGA (MASYARAKAT) ACTIONS
    // =========================================================================

    /**
     * Display list of pengajuan owned by logged-in warga.
     */
    public function indexWarga(Request $request)
    {
        $user = Auth::user();
        $masyarakat = $user->masyarakat;

        if (!$masyarakat) {
            return redirect()->route('warga.dashboard')->with('error', 'Data profil masyarakat belum terdaftar.');
        }

        $query = Pengajuan::with(['jenisPelayanan', 'surat'])
            ->where('id_masyarakat', $masyarakat->id_masyarakat);

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        $pengajuans = $query->latest('tanggal_pengajuan')->paginate(10)->withQueryString();
        $jenisPelayananList = JenisPelayanan::where('is_active', true)->orderBy('nama_pelayanan')->get();

        $view = view()->exists('warga.pengajuan.index')
            ? 'warga.pengajuan.index'
            : (view()->exists('pengajuan.index') ? 'pengajuan.index' : 'warga.dashboard');

        return view($view, compact('pengajuans', 'jenisPelayananList'));
    }

    /**
     * Show form to create a new pengajuan for specific pelayanan.
     */
    public function createWarga(?string $idPelayanan = null)
    {
        if (!$idPelayanan) {
            return redirect()->route('warga.pelayanan')->with('info', 'Silakan pilih jenis layanan surat yang ingin diajukan.');
        }
        $jenisPelayanan = JenisPelayanan::where('is_active', true)->findOrFail($idPelayanan);
        $user = Auth::user();
        $masyarakat = $user->masyarakat;

        $view = view()->exists('warga.pengajuan.create')
            ? 'warga.pengajuan.create'
            : (view()->exists('pengajuan.create') ? 'pengajuan.create' : 'warga.dashboard');

        return view($view, compact('jenisPelayanan', 'user', 'masyarakat'));
    }

    /**
     * Store new pengajuan from Warga.
     */
    public function storeWarga(Request $request)
    {
        $user = Auth::user();
        $masyarakat = $user->masyarakat;

        if (!$masyarakat) {
            return back()->with('error', 'Profil masyarakat Anda tidak ditemukan.');
        }

        $request->validate([
            'id_pelayanan' => 'required|exists:jenis_pelayanan,id_pelayanan',
            'catatan' => 'nullable|string|max:1000',
            'berkas' => 'nullable|array',
            'berkas.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $idPelayanan = $request->input('id_pelayanan');
        $catatan = $request->input('catatan');

        // Extract form data
        $formulirData = $request->input('formulir', []);
        if (empty($formulirData)) {
            // Collect all inputs excluding framework/meta fields
            $formulirData = $request->except([
                '_token',
                '_method',
                'id_pelayanan',
                'catatan',
                'berkas',
            ]);
        }

        // Collect uploaded files
        $uploadedFiles = [];
        if ($request->hasFile('berkas')) {
            $berkasInputs = $request->file('berkas');
            if (is_array($berkasInputs)) {
                foreach ($berkasInputs as $name => $file) {
                    if ($file) {
                        $uploadedFiles[$name] = $file;
                    }
                }
            }
        }

        // Also check if any standalone files exist in request
        foreach ($request->allFiles() as $key => $file) {
            if ($key !== 'berkas' && !isset($uploadedFiles[$key])) {
                if (is_array($file)) {
                    foreach ($file as $subKey => $subFile) {
                        $uploadedFiles["{$key}_{$subKey}"] = $subFile;
                    }
                } else {
                    $uploadedFiles[$key] = $file;
                }
            }
        }

        try {
            $pengajuan = $this->workflowService->createPengajuan(
                $masyarakat->id_masyarakat,
                $idPelayanan,
                $formulirData,
                $uploadedFiles,
                $catatan
            );

            return redirect()->route('warga.pengajuan.show', $pengajuan->id_pengajuan)
                ->with('success', "Permohonan berhasil diajukan dengan nomor tiket: {$pengajuan->nomor_tiket}");
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal mengajukan surat: ' . $e->getMessage());
        }
    }

    /**
     * Show detail of pengajuan for Warga.
     */
    public function showWarga(string $id)
    {
        $user = Auth::user();
        $masyarakat = $user->masyarakat;

        $pengajuan = Pengajuan::with([
            'jenisPelayanan',
            'masyarakat.user',
            'dokumen',
            'verifikasi.petugas.user',
            'surat.kepalaDesa.user',
        ])->findOrFail($id);

        if (!$masyarakat || $pengajuan->id_masyarakat !== $masyarakat->id_masyarakat) {
            abort(403, 'Anda tidak berhak melihat data pengajuan ini.');
        }

        $view = view()->exists('warga.pengajuan.show')
            ? 'warga.pengajuan.show'
            : (view()->exists('pengajuan.show') ? 'pengajuan.show' : 'warga.dashboard');

        return view($view, compact('pengajuan'));
    }

    /**
     * Submit revision from Warga (re-upload files & update form data).
     */
    public function submitRevisiWarga(Request $request, string $id)
    {
        $user = Auth::user();
        $masyarakat = $user->masyarakat;

        $pengajuan = Pengajuan::with('masyarakat')->findOrFail($id);

        if (!$masyarakat || $pengajuan->id_masyarakat !== $masyarakat->id_masyarakat) {
            abort(403, 'Akses ditolak.');
        }

        if ($pengajuan->status !== WorkflowService::STATUS_REVISI) {
            return back()->with('error', 'Pengajuan ini tidak sedang dalam status menunggu revisi.');
        }

        $request->validate([
            'berkas' => 'nullable|array',
            'berkas.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $formulirData = $request->input('formulir', []);
        if (empty($formulirData)) {
            $formulirData = $request->except(['_token', '_method', 'berkas']);
        }

        $newFiles = [];
        if ($request->hasFile('berkas')) {
            $berkasInputs = $request->file('berkas');
            if (is_array($berkasInputs)) {
                foreach ($berkasInputs as $key => $file) {
                    if ($file) {
                        $newFiles[$key] = $file;
                    }
                }
            }
        }
        foreach ($request->allFiles() as $key => $file) {
            if ($key !== 'berkas' && !isset($newFiles[$key])) {
                if (is_array($file)) {
                    foreach ($file as $subKey => $subFile) {
                        $newFiles["{$key}_{$subKey}"] = $subFile;
                    }
                } else {
                    $newFiles[$key] = $file;
                }
            }
        }

        try {
            $this->workflowService->submitRevisi($pengajuan, $formulirData, $newFiles);

            return redirect()->route('warga.pengajuan.show', $pengajuan->id_pengajuan)
                ->with('success', 'Perbaikan berkas berhasil dikirimkan. Menunggu pemeriksaan kembali oleh petugas.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal mengirim revisi: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // PETUGAS ACTIONS
    // =========================================================================

    /**
     * List pengajuan queue for Petugas.
     */
    public function indexPetugas(Request $request)
    {
        $status = $request->query('status');

        $query = Pengajuan::with(['jenisPelayanan', 'masyarakat.user', 'surat']);

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        $pengajuans = $query->latest('tanggal_pengajuan')->paginate(15)->withQueryString();

        // Stat counts for quick badges
        $stats = [
            'total' => Pengajuan::count(),
            'diajukan' => Pengajuan::where('status', WorkflowService::STATUS_DIAJUKAN)->count(),
            'diperiksa' => Pengajuan::where('status', WorkflowService::STATUS_DIPERIKSA)->count(),
            'revisi' => Pengajuan::where('status', WorkflowService::STATUS_REVISI)->count(),
            'menunggu' => Pengajuan::where('status', WorkflowService::STATUS_MENUNGGU_PERSETUJUAN)->count(),
            'disetujui' => Pengajuan::where('status', WorkflowService::STATUS_DISETUJUI)->count(),
            'diproses' => Pengajuan::where('status', WorkflowService::STATUS_DIPROSES)->count(),
            'selesai' => Pengajuan::where('status', WorkflowService::STATUS_SELESAI)->count(),
            'ditolak' => Pengajuan::where('status', WorkflowService::STATUS_DITOLAK)->count(),
        ];

        $view = view()->exists('petugas.pengajuan.index')
            ? 'petugas.pengajuan.index'
            : (view()->exists('petugas.queue') ? 'petugas.queue' : 'petugas.dashboard');

        return view($view, compact('pengajuans', 'status', 'stats'));
    }

    /**
     * Detail review page for Petugas.
     */
    public function showPetugas(string $id)
    {
        $pengajuan = Pengajuan::with([
            'jenisPelayanan',
            'masyarakat.user',
            'dokumen',
            'verifikasi.petugas.user',
            'surat.kepalaDesa.user',
        ])->findOrFail($id);

        $petugas = Auth::user()->petugas;
        $petugasId = $petugas?->id_petugas;

        // Auto claim: if DIAJUKAN, transition to DIPERIKSA
        if ($pengajuan->status === WorkflowService::STATUS_DIAJUKAN) {
            $this->workflowService->periksaPengajuan($pengajuan, $petugasId);
            $pengajuan->refresh();
        }

        $kadesList = KepalaDesa::where('is_active', true)->with('user')->get();

        $view = view()->exists('petugas.pengajuan.show')
            ? 'petugas.pengajuan.show'
            : (view()->exists('petugas.review') ? 'petugas.review' : 'petugas.dashboard');

        return view($view, compact('pengajuan', 'kadesList'));
    }

    /**
     * Petugas requests revision.
     */
    public function mintaRevisiPetugas(Request $request, string $id)
    {
        $request->validate([
            'catatan_revisi' => 'required|string|min:5|max:1000',
        ]);

        $pengajuan = Pengajuan::findOrFail($id);
        $petugas = Auth::user()->petugas;
        $petugasId = $petugas ? $petugas->id_petugas : (string) Auth::id();

        try {
            $this->workflowService->mintaRevisi($pengajuan, $petugasId, $request->input('catatan_revisi'));

            return redirect()->route('petugas.pengajuan.show', $id)
                ->with('warning', 'Permintaan revisi berhasil dikirim ke pemohon.');
        } catch (Exception $e) {
            return back()->with('error', 'Gagal memproses revisi: ' . $e->getMessage());
        }
    }

    /**
     * Petugas marks pengajuan as verified and forwards to Kepala Desa.
     */
    public function verifikasiPetugas(Request $request, string $id)
    {
        $request->validate([
            'catatan' => 'nullable|string|max:1000',
        ]);

        $pengajuan = Pengajuan::findOrFail($id);
        $petugas = Auth::user()->petugas;
        $petugasId = $petugas ? $petugas->id_petugas : (string) Auth::id();

        try {
            $this->workflowService->verifikasiPetugas($pengajuan, $petugasId, $request->input('catatan'));

            return redirect()->route('petugas.pengajuan.index')
                ->with('success', "Pengajuan {$pengajuan->nomor_tiket} berhasil diverifikasi dan diteruskan ke Kepala Desa.");
        } catch (Exception $e) {
            return back()->with('error', 'Gagal memverifikasi: ' . $e->getMessage());
        }
    }

    /**
     * Petugas rejects pengajuan with reason.
     */
    public function tolakPetugas(Request $request, string $id)
    {
        $request->validate([
            'alasan_penolakan' => 'required|string|min:5|max:1000',
        ]);

        $pengajuan = Pengajuan::findOrFail($id);

        try {
            $this->workflowService->tolakPengajuan($pengajuan, (string) Auth::id(), $request->input('alasan_penolakan'));

            return redirect()->route('petugas.pengajuan.index')
                ->with('warning', "Pengajuan {$pengajuan->nomor_tiket} telah ditolak.");
        } catch (Exception $e) {
            return back()->with('error', 'Gagal menolak pengajuan: ' . $e->getMessage());
        }
    }

    /**
     * Petugas sets pengajuan to DIPROSES.
     */
    public function prosesPetugas(Request $request, string $id)
    {
        $pengajuan = Pengajuan::findOrFail($id);
        $petugas = Auth::user()->petugas;
        $petugasId = $petugas ? $petugas->id_petugas : (string) Auth::id();

        try {
            $this->workflowService->prosesPengajuan($pengajuan, $petugasId);

            return back()->with('success', "Status pengajuan diubah menjadi SEDANG DIPROSES.");
        } catch (Exception $e) {
            return back()->with('error', 'Gagal mengubah status: ' . $e->getMessage());
        }
    }

    /**
     * Petugas completes pengajuan and triggers letter generation.
     */
    public function selesaiPetugas(Request $request, string $id)
    {
        $pengajuan = Pengajuan::findOrFail($id);
        $idPimpinan = $request->input('id_pimpinan');

        try {
            $this->workflowService->selesaiPengajuan($pengajuan, $idPimpinan);

            return redirect()->route('petugas.pengajuan.show', $id)
                ->with('success', "Surat resmi berhasil diterbitkan dan status pengajuan menjadi SELESAI.");
        } catch (Exception $e) {
            return back()->with('error', 'Gagal menerbitkan surat: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // KEPALA DESA / LURAH ACTIONS
    // =========================================================================

    /**
     * List pengajuan awaiting approval for Kepala Desa.
     */
    public function indexKepala(Request $request)
    {
        $query = Pengajuan::with(['jenisPelayanan', 'masyarakat.user', 'surat']);

        $status = $request->query('status', WorkflowService::STATUS_MENUNGGU_PERSETUJUAN);
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        $pengajuans = $query->latest('tanggal_pengajuan')->paginate(15)->withQueryString();

        $view = view()->exists('kepala.pengajuan.index')
            ? 'kepala.pengajuan.index'
            : (view()->exists('kepala.approvals') ? 'kepala.approvals' : 'kepala.dashboard');

        return view($view, compact('pengajuans', 'status'));
    }

    /**
     * Show detail review for Kepala Desa.
     */
    public function showKepala(string $id)
    {
        $pengajuan = Pengajuan::with([
            'jenisPelayanan',
            'masyarakat.user',
            'dokumen',
            'verifikasi.petugas.user',
            'surat',
        ])->findOrFail($id);

        $view = view()->exists('kepala.pengajuan.show')
            ? 'kepala.pengajuan.show'
            : (view()->exists('kepala.review') ? 'kepala.review' : 'kepala.dashboard');

        return view($view, compact('pengajuan'));
    }

    /**
     * Kepala Desa approves pengajuan.
     */
    public function approveKepala(Request $request, string $id)
    {
        $request->validate([
            'catatan' => 'nullable|string|max:1000',
        ]);

        $pengajuan = Pengajuan::findOrFail($id);
        $kades = Auth::user()->kepalaDesa;
        $pimpinanId = $kades ? $kades->id_pimpinan : null;

        try {
            $this->workflowService->approveKepala($pengajuan, $pimpinanId, $request->input('catatan'));

            return redirect()->route('kepala.pengajuan.index')
                ->with('success', "Pengajuan tiket {$pengajuan->nomor_tiket} telah berhasil DISETUJUI.");
        } catch (Exception $e) {
            return back()->with('error', 'Gagal menyetujui pengajuan: ' . $e->getMessage());
        }
    }

    /**
     * Kepala Desa rejects pengajuan.
     */
    public function rejectKepala(Request $request, string $id)
    {
        $request->validate([
            'alasan_penolakan' => 'required|string|min:5|max:1000',
        ]);

        $pengajuan = Pengajuan::findOrFail($id);
        $kades = Auth::user()->kepalaDesa;
        $pimpinanId = $kades ? $kades->id_pimpinan : null;

        try {
            $this->workflowService->rejectKepala($pengajuan, $pimpinanId, $request->input('alasan_penolakan'));

            return redirect()->route('kepala.pengajuan.index')
                ->with('warning', "Pengajuan tiket {$pengajuan->nomor_tiket} telah DITOLAK.");
        } catch (Exception $e) {
            return back()->with('error', 'Gagal menolak pengajuan: ' . $e->getMessage());
        }
    }
}
