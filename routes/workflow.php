<?php

use App\Http\Controllers\DokumenController;
use App\Http\Controllers\PengajuanController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Workflow & Document Storage Routes (Requires Authentication)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Universal show route: automatically routes user according to their role
    Route::get('/pengajuan/{id}', [PengajuanController::class, 'showUniversal'])->name('pengajuan.show');
    Route::get('/pengajuan/{id}/universal', [PengajuanController::class, 'showUniversal'])->name('pengajuan.show.universal');

    // Generic Warga route aliases
    Route::middleware('role:masyarakat,admin')->group(function () {
        Route::get('/pengajuan-list', [PengajuanController::class, 'indexWarga'])->name('pengajuan.index');
        Route::get('/pengajuan-baru/{id_pelayanan?}', [PengajuanController::class, 'createWarga'])->name('pengajuan.create');
        Route::post('/pengajuan-store', [PengajuanController::class, 'storeWarga'])->name('pengajuan.store');
        Route::post('/pengajuan-resubmit/{id}', [PengajuanController::class, 'submitRevisiWarga'])->name('pengajuan.resubmit');
    });
    // Secure Document Storage Routes
    Route::get('/dokumen/{id}/view', [DokumenController::class, 'preview'])->name('dokumen.view');
    Route::get('/dokumen/{id}/preview', [DokumenController::class, 'preview'])->name('dokumen.preview');
    Route::get('/dokumen/{id}/download', [DokumenController::class, 'download'])->name('dokumen.download');
    Route::post('/dokumen/{id_pengajuan}/upload', [DokumenController::class, 'upload'])->name('dokumen.upload');

    // Warga (Masyarakat) Routes
    Route::middleware('role:masyarakat,admin')->prefix('warga')->name('warga.')->group(function () {
        Route::get('/pengajuan', [PengajuanController::class, 'indexWarga'])->name('pengajuan.index');
        Route::get('/pengajuan/create/{id_pelayanan?}', [PengajuanController::class, 'createWarga'])->name('pengajuan.create');
        Route::post('/pengajuan', [PengajuanController::class, 'storeWarga'])->name('pengajuan.store');
        Route::get('/pengajuan/{id}', [PengajuanController::class, 'showWarga'])->name('pengajuan.show');
        Route::post('/pengajuan/{id}/revisi', [PengajuanController::class, 'submitRevisiWarga'])->name('pengajuan.revisi');
        Route::post('/pengajuan/{id}/resubmit', [PengajuanController::class, 'submitRevisiWarga'])->name('pengajuan.resubmit');
    });

    // Petugas Routes
    Route::middleware('role:petugas,admin')->prefix('petugas')->name('petugas.')->group(function () {
        Route::get('/pengajuan', [PengajuanController::class, 'indexPetugas'])->name('pengajuan.index');
        Route::get('/pengajuan/{id}', [PengajuanController::class, 'showPetugas'])->name('pengajuan.show');
        Route::post('/pengajuan/{id}/revisi', [PengajuanController::class, 'mintaRevisiPetugas'])->name('pengajuan.revisi');
        Route::post('/pengajuan/{id}/minta-revisi', [PengajuanController::class, 'mintaRevisiPetugas'])->name('pengajuan.minta_revisi');
        Route::post('/pengajuan/{id}/verifikasi', [PengajuanController::class, 'verifikasiPetugas'])->name('pengajuan.verifikasi');
        Route::post('/pengajuan/{id}/tolak', [PengajuanController::class, 'tolakPetugas'])->name('pengajuan.tolak');
        Route::post('/pengajuan/{id}/proses', [PengajuanController::class, 'prosesPetugas'])->name('pengajuan.proses');
        Route::post('/pengajuan/{id}/selesai', [PengajuanController::class, 'selesaiPetugas'])->name('pengajuan.selesai');
    });

    // Kepala Desa / Lurah Routes
    Route::middleware('role:kepala,admin')->prefix('kepala')->name('kepala.')->group(function () {
        Route::get('/pengajuan', [PengajuanController::class, 'indexKepala'])->name('pengajuan.index');
        Route::get('/pengajuan/{id}', [PengajuanController::class, 'showKepala'])->name('pengajuan.show');
        Route::post('/pengajuan/{id}/approve', [PengajuanController::class, 'approveKepala'])->name('pengajuan.approve');
        Route::post('/pengajuan/{id}/reject', [PengajuanController::class, 'rejectKepala'])->name('pengajuan.reject');
    });

});
