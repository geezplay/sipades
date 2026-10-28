<?php

use App\Http\Controllers\SuratController;
use App\Http\Controllers\VerifikasiSuratController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes (Verification without authentication)
|--------------------------------------------------------------------------
*/
Route::get('/verifikasi-surat/{token}', [VerifikasiSuratController::class, 'verifikasi'])
    ->name('surat.verifikasi.publik');

/*
|--------------------------------------------------------------------------
| Protected Document Routes (Requires Authentication)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/surat/{id}/download', [SuratController::class, 'download'])
        ->name('surat.download');

    Route::get('/surat/{id}/preview', [SuratController::class, 'preview'])
        ->name('surat.preview');

    Route::post('/surat/{id_pengajuan}/generate', [SuratController::class, 'generate'])
        ->name('surat.generate');
});
