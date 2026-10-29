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
     * Simpan Pengguna Baru (Masyarakat / Staf Petugas / Kepala Desa / Admin)
     */
    public function storeUser(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,petugas,kepala,masyarakat',
            'jabatan' => 'nullable|string|max:100',
            'nip' => 'nullable|string|max:30',
            'nik' => 'nullable|required_if:role,masyarakat|digits:16|unique:masyarakat,nik',
            'no_hp' => 'nullable|required_if:role,masyarakat|string|max:20',
            'alamat' => 'nullable|required_if:role,masyarakat|string|max:1000',
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'id_user' => (string) Str::uuid(),
                'nama' => $request->nama,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
            ]);

            if ($request->role === 'masyarakat') {
                Masyarakat::create([
                    'id_masyarakat' => (string) Str::uuid(),
                    'id_user' => $user->id_user,
                    'nik' => $request->nik,
                    'alamat' => $request->alamat,
                    'no_hp' => $request->no_hp,
                ]);
            } elseif ($request->role === 'petugas') {
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
     * Perbarui Data Pengguna (Edit User)
     */
    public function updateUser(Request $request, string $id)
    {
        $user = User::with(['masyarakat', 'petugas', 'kepalaDesa'])->findOrFail($id);

        $request->validate([
            'nama' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email,' . $user->id_user . ',id_user',
            'password' => 'nullable|string|min:8',
            'role' => 'required|in:admin,petugas,kepala,masyarakat',
            'jabatan' => 'nullable|string|max:100',
            'nip' => 'nullable|string|max:30',
            'nik' => 'nullable|required_if:role,masyarakat|digits:16|unique:masyarakat,nik,' . ($user->masyarakat?->id_masyarakat ?? 'NULL') . ',id_masyarakat',
            'no_hp' => 'nullable|required_if:role,masyarakat|string|max:20',
            'alamat' => 'nullable|required_if:role,masyarakat|string|max:1000',
        ]);

        DB::transaction(function () use ($request, $user) {
            $oldRole = $user->role;
            $newRole = $request->role;

            $userData = [
                'nama' => $request->nama,
                'email' => $request->email,
                'role' => $newRole,
            ];

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            $user->update($userData);

            // Bersihkan profil peran lama jika peran berubah
            if ($oldRole !== $newRole) {
                if ($oldRole === 'masyarakat' && $user->masyarakat) {
                    $user->masyarakat()->delete();
                } elseif ($oldRole === 'petugas' && $user->petugas) {
                    $user->petugas()->delete();
                } elseif ($oldRole === 'kepala' && $user->kepalaDesa) {
                    $user->kepalaDesa()->delete();
                }
            }

            // Simpan / Perbarui profil peran baru
            if ($newRole === 'masyarakat') {
                Masyarakat::updateOrCreate(
                    ['id_user' => $user->id_user],
                    [
                        'id_masyarakat' => $user->masyarakat?->id_masyarakat ?? (string) Str::uuid(),
                        'nik' => $request->nik,
                        'alamat' => $request->alamat ?? '',
                        'no_hp' => $request->no_hp ?? '',
                    ]
                );
            } elseif ($newRole === 'petugas') {
                Petugas::updateOrCreate(
                    ['id_user' => $user->id_user],
                    [
                        'id_petugas' => $user->petugas?->id_petugas ?? (string) Str::uuid(),
                        'jabatan' => $request->jabatan ?? 'Staf Pelayanan',
                    ]
                );
            } elseif ($newRole === 'kepala') {
                KepalaDesa::updateOrCreate(
                    ['id_user' => $user->id_user],
                    [
                        'id_pimpinan' => $user->kepalaDesa?->id_pimpinan ?? (string) Str::uuid(),
                        'jabatan' => $request->jabatan ?? 'Kepala Desa',
                        'nip' => $request->nip,
                        'is_active' => true,
                    ]
                );
            }
        });

        return redirect()->route('admin.users')->with('success', "Data pengguna {$user->nama} berhasil diperbarui.");
    }

    /**
     * Hapus Pengguna (Delete User)
     */
    public function destroyUser(string $id)
    {
        $user = User::with(['masyarakat', 'petugas', 'kepalaDesa'])->findOrFail($id);

        // Cegah admin menghapus dirinya sendiri
        if ($user->id_user === auth()->id()) {
            return redirect()->route('admin.users')->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        // Cek keterikatan data sebelum menghapus
        if ($user->role === 'masyarakat' && $user->masyarakat) {
            $hasPengajuan = Pengajuan::where('id_masyarakat', $user->masyarakat->id_masyarakat)->exists();
            if ($hasPengajuan) {
                return redirect()->route('admin.users')->with('error', "Gagal menghapus: Pengguna {$user->nama} memiliki riwayat permohonan surat.");
            }
        }

        if ($user->role === 'petugas' && $user->petugas) {
            $hasVerifikasi = \App\Models\Verifikasi::where('id_petugas', $user->petugas->id_petugas)->exists();
            if ($hasVerifikasi) {
                return redirect()->route('admin.users')->with('error', "Gagal menghapus: Petugas {$user->nama} memiliki riwayat verifikasi berkas.");
            }
        }

        if ($user->role === 'kepala' && $user->kepalaDesa) {
            $hasSurat = Surat::where('id_pimpinan_penandatangan', $user->kepalaDesa->id_pimpinan)->exists();
            if ($hasSurat) {
                return redirect()->route('admin.users')->with('error', "Gagal menghapus: Kepala Desa {$user->nama} tercatat menandatangani surat resmi.");
            }
        }

        $nama = $user->nama;
        $user->delete();

        return redirect()->route('admin.users')->with('success', "Pengguna {$nama} berhasil dihapus dari sistem.");
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
