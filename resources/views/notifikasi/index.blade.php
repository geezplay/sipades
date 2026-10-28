@extends('layouts.app')

@section('title', 'Notifikasi & Pemberitahuan')
@section('breadcrumb', 'Notifikasi')

@section('content')
<div class="notifikasi-page" style="max-width: 900px; margin: 0 auto;">
    <div class="card">
        <div class="card-header" style="background: #ffffff; padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color); display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.6rem;">
                <i class="fas fa-bell" style="color: var(--accent-primary); font-size: 1.25rem;"></i>
                <div>
                    <h3 style="margin: 0; font-size: 1.2rem; font-weight: 800; color: var(--header-navy);">
                        Kotak Notifikasi
                    </h3>
                    <p style="margin: 0; font-size: 0.8rem; color: var(--text-muted);">
                        Pemberitahuan perubahan status berkas permohonan dan pengumuman kedinasan
                    </p>
                </div>
            </div>
            @if(isset($notifikasis) && $notifikasis->where('status_baca', false)->count() > 0)
                <form action="{{ Route::has('notifikasi.read-all') ? route('notifikasi.read-all') : (Route::has('notifikasi.markAllRead') ? route('notifikasi.markAllRead') : url('/notifikasi/read-all')) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline btn-sm" style="font-size: 0.8rem;">
                        <i class="fas fa-check-double"></i> Tandai Semua Dibaca
                    </button>
                </form>
            @endif
        </div>

        <div class="card-body" style="padding: 0;">
            @php
                $notifList = isset($notifikasis) 
                    ? $notifikasis 
                    : \App\Models\Notifikasi::where('id_user', Auth::id())->latest()->get();
            @endphp

            @forelse($notifList as $notif)
                <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-light); background: {{ $notif->status_baca ? '#ffffff' : '#f0f9ff' }}; display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; transition: background 0.2s;">
                    <div style="display: flex; gap: 1rem; align-items: flex-start;">
                        <div style="width: 40px; height: 40px; border-radius: 50%; background: {{ $notif->status_baca ? '#f1f5f9' : '#e0f2fe' }}; color: {{ $notif->status_baca ? '#64748b' : 'var(--accent-primary)' }}; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0;">
                            <i class="fas {{ $notif->status_baca ? 'fa-envelope-open' : 'fa-envelope' }}"></i>
                        </div>
                        <div>
                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                                <h4 style="margin: 0; font-size: 0.95rem; font-weight: 700; color: var(--header-navy);">
                                    {{ $notif->judul }}
                                </h4>
                                @if(!$notif->status_baca)
                                    <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #0284c7;" title="Belum dibaca"></span>
                                @endif
                            </div>
                            <p style="margin: 0 0 0.5rem 0; font-size: 0.85rem; color: var(--text-secondary); line-height: 1.5;">
                                {{ $notif->pesan }}
                            </p>
                            <span style="font-size: 0.75rem; color: var(--text-muted);">
                                <i class="fas fa-clock" style="font-size: 0.7rem;"></i> 
                                {{ \Carbon\Carbon::parse($notif->created_at)->diffForHumans() }} ({{ \Carbon\Carbon::parse($notif->created_at)->translatedFormat('d M Y, H:i') }})
                            </span>
                        </div>
                    </div>

                    @if($notif->link_url)
                        <div style="flex-shrink: 0;">
                            <a href="{{ $notif->link_url }}" class="btn btn-outline btn-sm" style="font-size: 0.78rem; text-decoration: none;">
                                Buka Tautan <i class="fas fa-chevron-right" style="font-size: 0.65rem; margin-left: 0.25rem;"></i>
                            </a>
                        </div>
                    @endif
                </div>
            @empty
                <div style="text-align: center; padding: 4rem 1.5rem; color: var(--text-muted);">
                    <i class="fas fa-bell-slash" style="font-size: 2.75rem; color: #cbd5e1; display: block; margin-bottom: 1rem;"></i>
                    <h4 style="margin: 0 0 0.25rem 0; font-size: 1.05rem; font-weight: 700; color: var(--header-navy);">Tidak Ada Notifikasi</h4>
                    <p style="margin: 0; font-size: 0.85rem;">Anda belum memiliki pemberitahuan baru.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
