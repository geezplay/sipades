@extends('layouts.app')

@section('title', 'Daftar Layanan Administrasi')
@section('breadcrumb', 'Daftar Layanan')

@section('content')
<div class="layanan-page">
    <div class="card" style="margin-bottom: 2rem; background: linear-gradient(135deg, #0f2b48 0%, #16385c 60%, #1e6091 100%); color: #ffffff; border: none;">
        <div class="card-body" style="padding: 2rem 2.25rem;">
            <h2 style="font-size: 1.6rem; font-weight: 800; margin: 0 0 0.5rem 0; color: #ffffff;">
                Katalog Pelayanan Administrasi Desa
            </h2>
            <p style="color: #cbd5e1; margin: 0; font-size: 0.95rem; max-width: 650px;">
                Pilih jenis pelayanan administrasi kependudukan yang Anda butuhkan, pelajari persyaratan berkas yang diperlukan, dan ajukan permohonan secara online.
            </p>
        </div>
    </div>

    @php
        $layananList = $jenisPelayananList ?? \App\Models\JenisPelayanan::where('is_active', true)->get();
    @endphp

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem;">
        @forelse($layananList as $jp)
            <div class="card" style="display: flex; flex-direction: column; justify-content: space-between; border-radius: var(--radius-lg); transition: all 0.2s;" onmouseover="this.style.boxShadow='var(--shadow-lg)'; this.style.borderColor='var(--accent-primary)';" onmouseout="this.style.boxShadow='var(--shadow-sm)'; this.style.borderColor='var(--border-color)';">
                <div class="card-body" style="padding: 1.5rem;">
                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                        <div style="width: 48px; height: 48px; border-radius: 12px; background: #eff6ff; color: var(--accent-primary); display: flex; align-items: center; justify-content: center; font-size: 1.4rem; flex-shrink: 0;">
                            <i class="fas {{ $jp->icon ?? 'fa-file-lines' }}"></i>
                        </div>
                        <div>
                            <h3 style="margin: 0; font-size: 1.1rem; font-weight: 800; color: var(--header-navy);">
                                {{ $jp->nama_pelayanan }}
                            </h3>
                            <code style="font-size: 0.72rem; color: var(--text-muted);">{{ $jp->id_pelayanan }}</code>
                        </div>
                    </div>

                    <p style="font-size: 0.88rem; color: var(--text-secondary); line-height: 1.5; margin-bottom: 1.25rem;">
                        {{ $jp->deskripsi }}
                    </p>

                    @if($jp->persyaratan)
                        <div style="background: #f8fafc; border: 1px solid var(--border-light); border-radius: 8px; padding: 0.85rem 1rem; margin-bottom: 1.25rem; font-size: 0.82rem;">
                            <strong style="color: var(--header-navy); display: block; margin-bottom: 0.25rem;">
                                <i class="fas fa-list-check text-primary"></i> Persyaratan Berkas:
                            </strong>
                            <div style="color: var(--text-muted); line-height: 1.4;">
                                {{ $jp->persyaratan }}
                            </div>
                        </div>
                    @endif
                </div>

                <div style="padding: 1rem 1.5rem; background: #fafafa; border-top: 1px solid var(--border-color); border-radius: 0 0 var(--radius-lg) var(--radius-lg);">
                    <a href="{{ Route::has('pengajuan.create') ? route('pengajuan.create', ['id_pelayanan' => $jp->id_pelayanan]) : (Route::has('warga.pengajuan.create') ? route('warga.pengajuan.create', ['id_pelayanan' => $jp->id_pelayanan]) : url('/warga/pengajuan/create?id_pelayanan=' . $jp->id_pelayanan)) }}" 
                       class="btn btn-primary btn-block" 
                       style="font-weight: 700; text-decoration: none;">
                        <i class="fas fa-file-circle-plus"></i> Ajukan Permohonan
                    </a>
                </div>
            </div>
        @empty
            <div style="grid-column: 1 / -1; text-align: center; padding: 3rem; color: var(--text-muted);">
                Belum ada data master pelayanan yang tersedia.
            </div>
        @endforelse
    </div>
</div>
@endsection
