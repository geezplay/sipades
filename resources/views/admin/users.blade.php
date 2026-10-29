@extends('layouts.app')

@section('title', 'Manajemen Pengguna — Admin SIPADES')
@section('breadcrumb', 'Manajemen Pengguna')

@section('content')
<div class="admin-users-page">
    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-header" style="background: #ffffff; padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color); display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem;">
            <div>
                <h3 style="margin: 0 0 0.25rem 0; font-size: 1.25rem; font-weight: 800; color: var(--header-navy);">
                    <i class="fas fa-users-gear" style="color: var(--accent-primary); margin-right: 0.5rem;"></i>
                    Daftar Pengguna Sistem SIPADES
                </h3>
                <p style="margin: 0; font-size: 0.85rem; color: var(--text-muted);">
                    Kelola data otentikasi akun masyarakat, staf pelayanan, pimpinan desa, dan administrator
                </p>
            </div>
            <div>
                <button class="btn btn-primary" onclick="toggleAddUserModal()">
                    <i class="fas fa-user-plus"></i> Tambah Pengguna Baru
                </button>
            </div>
        </div>

        <!-- Filter Bar -->
        <div style="padding: 1rem 1.5rem; background: #f8fafc; border-bottom: 1px solid var(--border-color); display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                <span style="font-size: 0.85rem; font-weight: 600; color: var(--header-navy);">Peran:</span>
                <a href="{{ request()->fullUrlWithQuery(['role' => '']) }}" 
                   class="btn btn-sm {{ !request('role') ? 'btn-primary' : 'btn-outline' }}" 
                   style="border-radius: 9999px; text-decoration: none;">Semua</a>
                <a href="{{ request()->fullUrlWithQuery(['role' => 'masyarakat']) }}" 
                   class="btn btn-sm {{ request('role') === 'masyarakat' ? 'btn-primary' : 'btn-outline' }}" 
                   style="border-radius: 9999px; text-decoration: none;">Masyarakat</a>
                <a href="{{ request()->fullUrlWithQuery(['role' => 'petugas']) }}" 
                   class="btn btn-sm {{ request('role') === 'petugas' ? 'btn-primary' : 'btn-outline' }}" 
                   style="border-radius: 9999px; text-decoration: none;">Petugas</a>
                <a href="{{ request()->fullUrlWithQuery(['role' => 'kepala']) }}" 
                   class="btn btn-sm {{ request('role') === 'kepala' ? 'btn-primary' : 'btn-outline' }}" 
                   style="border-radius: 9999px; text-decoration: none;">Kepala Desa</a>
                <a href="{{ request()->fullUrlWithQuery(['role' => 'admin']) }}" 
                   class="btn btn-sm {{ request('role') === 'admin' ? 'btn-primary' : 'btn-outline' }}" 
                   style="border-radius: 9999px; text-decoration: none;">Administrator</a>
            </div>

            <!-- Search Field -->
            <div style="position: relative; min-width: 250px;">
                <input type="text" id="userFilterInput" placeholder="Cari nama atau email..." 
                       onkeyup="filterUsers()" 
                       style="width: 100%; padding: 0.45rem 0.75rem 0.45rem 2rem; font-size: 0.85rem; border: 1px solid var(--border-color); border-radius: 8px;">
                <i class="fas fa-search" style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); font-size: 0.8rem; color: var(--text-muted);"></i>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table" id="usersTable">
                <thead>
                    <tr>
                        <th>Nama Pengguna</th>
                        <th>Alamat Email</th>
                        <th style="width: 140px;">Peran (Role)</th>
                        <th>Informasi Tambahan</th>
                        <th style="width: 150px;">Tgl Registrasi</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $userQuery = \App\Models\User::with(['masyarakat', 'petugas', 'kepalaDesa']);
                        if (request('role')) {
                            $userQuery->where('role', request('role'));
                        }
                        $users = isset($userList) ? $userList : $userQuery->latest()->get();
                    @endphp
                    @forelse($users as $user)
                        <tr class="user-row">
                            <td>
                                <div style="font-weight: 700; color: var(--header-navy);">
                                    {{ $user->nama }}
                                </div>
                            </td>
                            <td>
                                <span style="font-family: monospace; font-size: 0.85rem; color: var(--text-secondary);">
                                    {{ $user->email }}
                                </span>
                            </td>
                            <td>
                                <span class="role-pill role-{{ $user->role }}">
                                    {{ $user->role }}
                                </span>
                            </td>
                            <td style="font-size: 0.85rem; color: var(--text-muted);">
                                @if($user->role === 'masyarakat' && $user->masyarakat)
                                    <div><strong>NIK:</strong> {{ $user->masyarakat->nik }}</div>
                                    <div><strong>No HP:</strong> {{ $user->masyarakat->no_hp ?? '-' }}</div>
                                @elseif($user->role === 'petugas' && $user->petugas)
                                    <div><strong>Jabatan:</strong> {{ $user->petugas->jabatan ?? 'Staf Pelayanan' }}</div>
                                @elseif($user->role === 'kepala' && $user->kepalaDesa)
                                    <div><strong>Jabatan:</strong> {{ $user->kepalaDesa->jabatan ?? 'Kepala Desa' }}</div>
                                    <div><strong>NIP:</strong> {{ $user->kepalaDesa->nip ?? '-' }}</div>
                                @else
                                    <span>Hak akses penuh sistem</span>
                                @endif
                            </td>
                            <td style="font-size: 0.82rem; color: var(--text-muted);">
                                {{ \Carbon\Carbon::parse($user->created_at)->translatedFormat('d M Y') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 2.5rem; color: var(--text-muted);">
                                Tidak ada data pengguna yang cocok.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Pengguna -->
<div class="modal-overlay" id="addUserModal" style="display: none; align-items: center; justify-content: center; z-index: 2000;">
    <div class="modal" style="width: 100%; max-width: 500px; max-height: 90vh; overflow-y: auto;">
        <div class="modal-header">
            <h3 style="margin: 0; font-size: 1.1rem; font-weight: 800; color: var(--header-navy);">
                <i class="fas fa-user-plus" style="color: var(--accent-primary); margin-right: 0.4rem;"></i>
                Tambah Pengguna Baru
            </h3>
            <button class="modal-close" onclick="toggleAddUserModal()">&times;</button>
        </div>
        <form method="POST" action="{{ Route::has('admin.users.store') ? route('admin.users.store') : url('/admin/users') }}">
            @csrf
            <div class="modal-body" style="padding: 1.25rem;">
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--header-navy); margin-bottom: 0.35rem;">
                        Nama Lengkap <span style="color: #dc2626;">*</span>
                    </label>
                    <input type="text" name="nama" class="form-control" required placeholder="Contoh: Ahmad Yani">
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--header-navy); margin-bottom: 0.35rem;">
                        Alamat Email <span style="color: #dc2626;">*</span>
                    </label>
                    <input type="email" name="email" class="form-control" required placeholder="nama@sipades.go.id">
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--header-navy); margin-bottom: 0.35rem;">
                        Peran (Role) <span style="color: #dc2626;">*</span>
                    </label>
                    <select name="role" class="form-control" required onchange="handleRoleChange(this.value)">
                        <option value="petugas">Petugas Pelayanan</option>
                        <option value="kepala">Kepala Desa / Lurah</option>
                        <option value="admin">Administrator</option>
                        <option value="masyarakat">Masyarakat / Warga</option>
                    </select>
                </div>

                <!-- Input Khusus Role Petugas / Kepala -->
                <div class="form-group" id="jabatanField" style="margin-bottom: 1rem;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--header-navy); margin-bottom: 0.35rem;">
                        Jabatan Kedinasan
                    </label>
                    <input type="text" name="jabatan" class="form-control" placeholder="Contoh: Kasi Pelayanan Umum">
                </div>

                <!-- Input Khusus Role Masyarakat -->
                <div id="masyarakatFields" style="display: none;">
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--header-navy); margin-bottom: 0.35rem;">
                            NIK (16 Digit) <span style="color: #dc2626;">*</span>
                        </label>
                        <input type="text" name="nik" id="nikInput" class="form-control" maxlength="16" placeholder="Contoh: 3201123456780001">
                    </div>
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--header-navy); margin-bottom: 0.35rem;">
                            Nomor WhatsApp / HP
                        </label>
                        <input type="text" name="no_hp" class="form-control" placeholder="Contoh: 081234567890">
                    </div>
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--header-navy); margin-bottom: 0.35rem;">
                            Alamat Tempat Tinggal
                        </label>
                        <textarea name="alamat" class="form-control" rows="2" placeholder="Contoh: Dusun Sukamaju RT 01/RW 02"></textarea>
                    </div>
                </div>
                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--header-navy); margin-bottom: 0.35rem;">
                        Kata Sandi Awal <span style="color: #dc2626;">*</span>
                    </label>
                    <input type="password" name="password" class="form-control" required placeholder="Min. 8 karakter" minlength="6">
                </div>
            </div>
            <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 0.5rem; padding: 1rem 1.25rem;">
                <button type="button" class="btn btn-outline btn-sm" onclick="toggleAddUserModal()">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm">Simpan Akun</button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleAddUserModal() {
        const modal = document.getElementById('addUserModal');
        modal.style.display = modal.style.display === 'flex' ? 'none' : 'flex';
    }

    function handleRoleChange(role) {
        const jField = document.getElementById('jabatanField');
        const mFields = document.getElementById('masyarakatFields');
        const nikInput = document.getElementById('nikInput');

        if (role === 'petugas' || role === 'kepala') {
            jField.style.display = 'block';
            mFields.style.display = 'none';
            nikInput.removeAttribute('required');
        } else if (role === 'masyarakat') {
            jField.style.display = 'none';
            mFields.style.display = 'block';
            nikInput.setAttribute('required', 'required');
        } else {
            jField.style.display = 'none';
            mFields.style.display = 'none';
            nikInput.removeAttribute('required');
        }
    }

    function filterUsers() {
        const query = document.getElementById('userFilterInput').value.toLowerCase();
        const rows = document.querySelectorAll('#usersTable tbody tr.user-row');
        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(query) ? '' : 'none';
        });
    }
</script>
@endsection
