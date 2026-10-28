@extends('layouts.app')

@section('title', 'Master Pelayanan — Admin SIPADES')
@section('breadcrumb', 'Master Pelayanan')

@section('content')
<div class="admin-services-page">
    <div class="card">
        <div class="card-header" style="background: #ffffff; padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color); display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem;">
            <div>
                <h3 style="margin: 0 0 0.25rem 0; font-size: 1.25rem; font-weight: 800; color: var(--header-navy);">
                    <i class="fas fa-clipboard-list" style="color: var(--accent-primary); margin-right: 0.5rem;"></i>
                    Master Layanan Administrasi Desa
                </h3>
                <p style="margin: 0; font-size: 0.85rem; color: var(--text-muted);">
                    Daftar jenis surat administrasi kependudukan yang dapat diajukan oleh masyarakat secara online
                </p>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 250px;">Nama Pelayanan</th>
                        <th style="width: 140px;">ID Layanan</th>
                        <th>Deskripsi & Persyaratan</th>
                        <th style="width: 140px;">Formulir Schema</th>
                        <th style="width: 120px; text-align: center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $services = isset($jenisPelayananList) ? $jenisPelayananList : \App\Models\JenisPelayanan::all();
                    @endphp
                    @forelse($services as $svc)
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <div style="width: 38px; height: 38px; border-radius: 8px; background: #eff6ff; color: var(--accent-primary); display: flex; align-items: center; justify-content: center; font-size: 1.15rem; flex-shrink: 0;">
                                        <i class="fas {{ $svc->icon ?? 'fa-file-lines' }}"></i>
                                    </div>
                                    <div style="font-weight: 700; color: var(--header-navy); font-size: 0.95rem;">
                                        {{ $svc->nama_pelayanan }}
                                    </div>
                                </div>
                            </td>
                            <td>
                                <code style="font-size: 0.8rem; background: #f1f5f9; padding: 0.2rem 0.45rem; border-radius: 4px; color: var(--header-navy);">
                                    {{ $svc->id_pelayanan }}
                                </code>
                            </td>
                            <td>
                                <div style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 0.25rem;">
                                    {{ $svc->deskripsi ?: '-' }}
                                </div>
                                @if($svc->persyaratan)
                                    <div style="font-size: 0.78rem; color: var(--text-muted);">
                                        <strong>Syarat:</strong> {{ Str::limit($svc->persyaratan, 90) }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                @php
                                    $schema = is_array($svc->formulir_schema) 
                                        ? $svc->formulir_schema 
                                        : (json_decode($svc->formulir_schema, true) ?? []);
                                    $countFields = count($schema);
                                @endphp
                                <span class="badge badge-diajukan" style="font-size: 0.75rem;">
                                    <i class="fas fa-list-check"></i> {{ $countFields }} Kolom Input
                                </span>
                            </td>
                            <td style="text-align: center;">
                                @if($svc->is_active)
                                    <span class="badge badge-selesai" style="font-size: 0.78rem;">
                                        <i class="fas fa-check"></i> Aktif
                                    </span>
                                @else
                                    <span class="badge badge-ditolak" style="font-size: 0.78rem;">
                                        <i class="fas fa-ban"></i> Nonaktif
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 2.5rem; color: var(--text-muted);">
                                Belum ada master pelayanan terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
