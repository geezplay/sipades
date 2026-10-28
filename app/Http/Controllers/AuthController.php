<?php

namespace App\Http\Controllers;

use App\Models\Masyarakat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Menampilkan halaman login.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('auth.login');
    }

    /**
     * Memproses otentikasi login dengan rate limiting (throttle).
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $throttleKey = Str::transliterate(Str::lower($credentials['email']) . '|' . $request->ip());

        // Maksimal 5 percobaan login gagal per menit
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors([
                    'email' => "Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik.",
                ]);
        }

        $remember = $request->boolean('remember');

        if (!Auth::attempt($credentials, $remember)) {
            RateLimiter::hit($throttleKey, 60);

            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors([
                    'email' => 'Email atau password yang dimasukkan tidak sesuai.',
                ]);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        return $this->redirectBasedOnRole(Auth::user());
    }

    /**
     * Menampilkan formulir pendaftaran masyarakat baru.
     */
    public function showRegister()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('auth.register');
    }

    /**
     * Memproses pendaftaran masyarakat baru secara atomik (DB Transaction).
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'nik' => ['required', 'numeric', 'digits:16', 'unique:masyarakat,nik'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email'],
            'no_hp' => ['required', 'string', 'max:20', 'regex:/^[0-9+\s\-()]+$/'],
            'alamat' => ['required', 'string', 'max:1000'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'nama.required' => 'Nama lengkap wajib diisi.',
            'nik.required' => 'Nomor Induk Kependudukan (NIK) wajib diisi.',
            'nik.numeric' => 'NIK harus berupa angka.',
            'nik.digits' => 'NIK harus tepat 16 digit.',
            'nik.unique' => 'NIK sudah terdaftar dalam sistem.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'no_hp.required' => 'Nomor HP/WhatsApp wajib diisi.',
            'no_hp.regex' => 'Format nomor HP tidak valid.',
            'alamat.required' => 'Alamat tempat tinggal wajib diisi.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal terdiri dari 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $user = DB::transaction(function () use ($validated) {
            $user = User::create([
                'id_user' => (string) Str::uuid(),
                'nama' => $validated['nama'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'masyarakat',
            ]);

            Masyarakat::create([
                'id_masyarakat' => (string) Str::uuid(),
                'id_user' => $user->id_user,
                'nik' => $validated['nik'],
                'alamat' => $validated['alamat'],
                'no_hp' => $validated['no_hp'],
            ]);

            return $user;
        });

        // Login otomatis setelah registrasi berhasil
        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/warga/dashboard')->with('success', 'Pendaftaran akun berhasil! Selamat datang di SIPADES.');
    }

    /**
     * Memproses logout pengguna.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Anda telah berhasil keluar dari sistem.');
    }

    /**
     * Mengarahkan pengguna ke dashboard sesuai perannya.
     */
    protected function redirectBasedOnRole($user)
    {
        $redirectUrl = match ($user->role) {
            'admin' => '/admin/dashboard',
            'petugas' => '/petugas/dashboard',
            'kepala' => '/kepala/dashboard',
            'masyarakat' => '/warga/dashboard',
            default => '/',
        };

        return redirect()->intended($redirectUrl);
    }
}
