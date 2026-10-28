<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotifikasiController extends Controller
{
    /**
     * Tampilkan semua notifikasi user yang sedang login
     */
    public function index()
    {
        $user = Auth::user();
        $notifikasis = Notifikasi::where('id_user', $user->id_user)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('notifikasi.index', compact('notifikasis'));
    }

    /**
     * Tandai satu notifikasi sebagai telah dibaca
     */
    public function markAsRead(string $id)
    {
        $user = Auth::user();
        $notifikasi = Notifikasi::where('id_notifikasi', $id)
            ->where('id_user', $user->id_user)
            ->firstOrFail();

        $notifikasi->update(['status_baca' => true]);

        if ($notifikasi->link_url) {
            return redirect($notifikasi->link_url);
        }

        return back()->with('success', 'Notifikasi ditandai telah dibaca.');
    }

    /**
     * Tandai semua notifikasi pengguna sebagai telah dibaca
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        Notifikasi::where('id_user', $user->id_user)
            ->where('status_baca', false)
            ->update(['status_baca' => true]);

        return back()->with('success', 'Semua notifikasi telah ditandai dibaca.');
    }
}
