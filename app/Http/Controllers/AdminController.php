<?php

namespace App\Http\Controllers;

use App\Models\JenisPelayanan;
use App\Models\KepalaDesa;
use App\Models\Masyarakat;
use App\Models\Pengajuan;
use App\Models\Petugas;
use App\Models\Surat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    /**
     * Dashboard Administrator
     */
    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'total_layanan' => JenisPelayanan::count(),
            'total_pengajuan' => Pengajuan::count(),
            'total_surat' => Surat::count(),
            'total_warga' => Masyarakat::count(),
            'total_petugas' => Petugas::count(),
        ];

        $recentUsers = User::orderBy('created_at', 'desc')->take(8)->get();
        $pelayanans = JenisPelayanan::all();

        return view('admin.dashboard', compact('stats', 'recentUsers', 'pelayanans'));
    }

    /**
     * Manajemen Pengguna
     */
    public function users(Request $request)
    {
        $role = $request->get('role');
        $search = $request->get('q');

        $query = User::with(['masyarakat', 'petugas', 'kepalaDesa']);

        if ($role && in_array($role, ['masyarakat', 'petugas', 'kepala', 'admin'])) {
            $query->where('role', $role);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.users', compact('users', 'role', 'search'));
    }

    /**
     * Simpan Pengguna Baru (Staf Petugas / Kepala Desa / Admin)
     */
    public function storeUser(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,petugas,kepala',
            'jabatan' => 'nullable|string|max:100',
            'nip' => 'nullable|string|max:30',
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'id_user' => (string) Str::uuid(),
                'nama' => $request->nama,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
            ]);

            if ($request->role === 'petugas') {
                Petugas::create([
                    'id_petugas' => (string) Str::uuid(),
                    'id_user' => $user->id_user,
                    'jabatan' => $request->jabatan ?? 'Staf Pelayanan',
                ]);
            } elseif ($request->role === 'kepala') {
                KepalaDesa::create([
                    'id_pimpinan' => (string) Str::uuid(),
                    'id_user' => $user->id_user,
                    'jabatan' => $request->jabatan ?? 'Kepala Desa',
                    'nip' => $request->nip,
                    'is_active' => true,
                ]);
            }
        });

        return redirect()->route('admin.users')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    /**
     * Master Jenis Pelayanan
     */
    public function services()
    {
        $pelayanans = JenisPelayanan::all();
        return view('admin.services', compact('pelayanans'));
    }
}
