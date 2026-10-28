<?php

namespace App\Http\Controllers;

use App\Models\Surat;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class VerifikasiSuratController extends Controller
{
    /**
     * Public verification endpoint for QR code scanned from physical/digital letters.
     * Route: GET /verifikasi-surat/{token} (Name: surat.verifikasi.publik)
     */
    public function verifikasi(Request $request, string $token): View|JsonResponse
    {
        // Clean token
        $token = trim($token);

        // Find letter with all required relational dependencies
        $surat = Surat::with([
            'pengajuan.masyarakat.user',
            'pengajuan.jenisPelayanan',
            'kepalaDesa.user',
        ])->where('qr_token', $token)->first();

        $isValid = ($surat !== null);

        // If JSON response requested (e.g. by mobile scanner API)
        if ($request->wantsJson()) {
            if (!$isValid) {
                return response()->json([
                    'status'  => 'error',
                    'valid'   => false,
                    'message' => 'Dokumen tidak ditemukan atau token tidak valid.',
                    'token'   => $token,
                ], 404);
            }

            $pengajuan = $surat->pengajuan;
            $masyarakat = $pengajuan?->masyarakat;
            $userPemohon = $masyarakat?->user;
            $formData = is_array($pengajuan?->data_formulir)
                ? $pengajuan->data_formulir
                : json_decode($pengajuan?->data_formulir ?: '[]', true);

            $namaPemohon = $formData['nama_lengkap'] ?? $formData['nama'] ?? $userPemohon?->nama ?? '-';
            $rawNik = $formData['nik'] ?? $masyarakat?->nik ?? '';

            return response()->json([
                'status' => 'success',
                'valid'  => true,
                'dokumen' => [
                    'nomor_surat'    => $surat->nomor_surat,
                    'jenis_layanan'  => $pengajuan?->jenisPelayanan?->nama_pelayanan,
                    'tanggal_surat'  => $surat->tanggal_surat?->toDateString(),
                    'nama_pemohon'   => $namaPemohon,
                    'nik_tersensor'  => self::maskNik($rawNik),
                    'penandatangan'  => $surat->kepalaDesa?->user?->nama ?? 'H. Muhammad Rizki, S.Sos',
                    'jabatan'        => $surat->kepalaDesa?->jabatan ?? 'Kepala Desa',
                    'nip'            => $surat->kepalaDesa?->nip,
                    'status'         => 'Aktif & Sah',
                    'token'          => $surat->qr_token,
                ],
            ]);
        }

        // Return public verification view
        return view('surat.verifikasi_publik', [
            'isValid' => $isValid,
            'token'   => $token,
            'surat'   => $surat,
        ]);
    }

    /**
     * Mask NIK for public display privacy (e.g. 320102******0001).
     */
    public static function maskNik(?string $nik): string
    {
        if (empty($nik)) {
            return '-';
        }

        $length = strlen($nik);
        if ($length < 8) {
            return substr($nik, 0, 2) . str_repeat('*', max(0, $length - 4)) . substr($nik, -2);
        }

        // Standard 16-digit NIK: first 6 digits + 6 asterisks + last 4 digits
        return substr($nik, 0, 6) . '******' . substr($nik, -4);
    }
}
