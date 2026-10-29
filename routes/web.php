<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotifikasiController;
use App\Models\JenisPelayanan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Landing / Home Route
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    if (Auth::check()) {
        $role = Auth::user()->role;
        return match ($role) {
            'admin' => redirect()->route('admin.dashboard'),
            'petugas' => redirect()->route('petugas.dashboard'),
            'kepala' => redirect()->route('kepala.dashboard'),
            'masyarakat' => redirect()->route('warga.dashboard'),
            default => redirect()->route('login'),
        };
    }
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Sub-Route Modules
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';
require __DIR__.'/surat.php';
require __DIR__.'/workflow.php';

/*
|--------------------------------------------------------------------------
| Role-Protected Dashboards & Modules
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Notification Center
    Route::get('/notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi.index');
    Route::get('/notifikasi/{id}/baca', [NotifikasiController::class, 'markAsRead'])->name('notifikasi.baca');
    Route::post('/notifikasi/baca-semua', [NotifikasiController::class, 'markAllAsRead'])->name('notifikasi.baca_semua');

    // Warga Dashboard & Pelayanan Katalog
    Route::middleware('role:masyarakat,admin')->group(function () {
        Route::get('/warga/dashboard', [DashboardController::class, 'warga'])->name('warga.dashboard');
        Route::get('/warga/pelayanan', function () {
            $pelayanans = JenisPelayanan::where('is_active', true)->get();
            return view('warga.pelayanan', compact('pelayanans'));
        })->name('warga.pelayanan');
    });

    // Petugas Dashboard
    Route::middleware('role:petugas,admin')->group(function () {
        Route::get('/petugas/dashboard', [DashboardController::class, 'petugas'])->name('petugas.dashboard');
    });

    // Kepala Desa Dashboard
    Route::middleware('role:kepala,admin')->group(function () {
        Route::get('/kepala/dashboard', [DashboardController::class, 'kepala'])->name('kepala.dashboard');
    });

    // Admin Dashboard & Management
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
        Route::put('/users/{id}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{id}', [AdminController::class, 'destroyUser'])->name('users.destroy');
        Route::get('/services', [AdminController::class, 'services'])->name('services');
    });
});
