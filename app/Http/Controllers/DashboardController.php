<?php

namespace App\Http\Controllers;

use App\Models\JenisPelayanan;
use App\Models\Pengajuan;
use App\Models\Surat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Dashboard Warga (Masyarakat)
     */
    public function warga()
    {
        $user = Auth::user();
        $masyarakat = $user->masyarakat;

        $pengajuans = collect();
        $stats = [
            'total' => 0,
            'diproses' => 0,
            'selesai' => 0,
            'revisi' => 0,
        ];

        if ($masyarakat) {
            $pengajuans = Pengajuan::with(['jenisPelayanan', 'surat'])
                ->where('id_masyarakat', $masyarakat->id_masyarakat)
                ->orderBy('tanggal_pengajuan', 'desc')
                ->take(10)
                ->get();

            $allPengajuans = Pengajuan::where('id_masyarakat', $masyarakat->id_masyarakat)->get();
            $stats = [
                'total' => $allPengajuans->count(),
                'diproses' => $allPengajuans->whereIn('status', [
                    'DIAJUKAN', 'DIPERIKSA', 'DIVERIFIKASI', 'MENUNGGU_PERSETUJUAN', 'DISETUJUI', 'DIPROSES'
                ])->count(),
                'selesai' => $allPengajuans->where('status', 'SELESAI')->count(),
                'revisi' => $allPengajuans->where('status', 'REVISI')->count(),
            ];
        }

        $pelayanans = JenisPelayanan::where('is_active', true)->get();

        return view('warga.dashboard', compact('pengajuans', 'stats', 'pelayanans'));
    }

    /**
     * Dashboard Petugas Pelayanan
     */
    public function petugas()
    {
        $stats = [
            'diajukan' => Pengajuan::where('status', 'DIAJUKAN')->count(),
            'diperiksa' => Pengajuan::where('status', 'DIPERIKSA')->count(),
            'menunggu_kades' => Pengajuan::where('status', 'MENUNGGU_PERSETUJUAN')->count(),
            'siap_cetak' => Pengajuan::where('status', 'DISETUJUI')->count(),
            'revisi' => Pengajuan::where('status', 'REVISI')->count(),
            'selesai' => Pengajuan::where('status', 'SELESAI')->count(),
        ];

        $recentPengajuans = Pengajuan::with(['masyarakat.user', 'jenisPelayanan'])
            ->whereIn('status', ['DIAJUKAN', 'DIPERIKSA', 'REVISI', 'MENUNGGU_PERSETUJUAN', 'DISETUJUI', 'DIPROSES'])
            ->orderBy('tanggal_pengajuan', 'desc')
            ->take(10)
            ->get();

        return view('petugas.dashboard', compact('stats', 'recentPengajuans'));
    }

    /**
     * Dashboard Kepala Desa / Lurah
     */
    public function kepala()
    {
        $stats = [
            'menunggu' => Pengajuan::where('status', 'MENUNGGU_PERSETUJUAN')->count(),
            'disetujui' => Pengajuan::whereIn('status', ['DISETUJUI', 'SELESAI'])->count(),
            'ditolak' => Pengajuan::where('status', 'DITOLAK')->count(),
            'total_surat' => Surat::count(),
        ];

        $pendingPengajuans = Pengajuan::with(['masyarakat.user', 'jenisPelayanan', 'verifikasi.petugas.user'])
            ->where('status', 'MENUNGGU_PERSETUJUAN')
            ->orderBy('tanggal_pengajuan', 'asc')
            ->get();

        return view('kepala.dashboard', compact('stats', 'pendingPengajuans'));
    }
}
