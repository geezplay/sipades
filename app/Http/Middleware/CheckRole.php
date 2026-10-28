<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->guest(Route::has('login') ? route('login') : '/login');
        }

        $user = Auth::user();

        // Check if user's role matches any of the allowed roles
        if (!empty($roles) && !in_array($user->role, $roles, true)) {
            if ($request->expectsJson()) {
                abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk mengakses resource ini.');
            }

            // Redirect user to their designated dashboard
            $targetRoute = match ($user->role) {
                'admin' => 'admin.dashboard',
                'petugas' => 'petugas.dashboard',
                'kepala' => 'kepala.dashboard',
                'masyarakat' => 'warga.dashboard',
                default => null,
            };

            if ($targetRoute && Route::has($targetRoute)) {
                return redirect()->route($targetRoute)->with('error', 'Akses ditolak: Anda tidak memiliki wewenang untuk membuka halaman tersebut.');
            }

            abort(403, 'Akses ditolak: Anda tidak memiliki wewenang untuk membuka halaman ini.');
        }

        return $next($request);
    }
}
