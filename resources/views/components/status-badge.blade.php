@props(['status'])

@php
    $statusUpper = strtoupper($status ?? 'DIAJUKAN');
    $badgeClass = match($statusUpper) {
        'DIAJUKAN' => 'badge-diajukan',
        'DIPERIKSA' => 'badge-diperiksa',
        'REVISI' => 'badge-revisi',
        'DIVERIFIKASI' => 'badge-diverifikasi',
        'MENUNGGU_PERSETUJUAN' => 'badge-menunggu',
        'DISETUJUI' => 'badge-disetujui',
        'DITOLAK' => 'badge-ditolak',
        'DIPROSES' => 'badge-diproses',
        'SELESAI' => 'badge-selesai',
        default => 'badge-diajukan',
    };

    $iconClass = match($statusUpper) {
        'DIAJUKAN' => 'fa-paper-plane',
        'DIPERIKSA' => 'fa-magnifying-glass',
        'REVISI' => 'fa-triangle-exclamation',
        'DIVERIFIKASI' => 'fa-check',
        'MENUNGGU_PERSETUJUAN' => 'fa-clock',
        'DISETUJUI' => 'fa-circle-check',
        'DITOLAK' => 'fa-circle-xmark',
        'DIPROSES' => 'fa-gear fa-spin',
        'SELESAI' => 'fa-circle-check',
        default => 'fa-circle-info',
    };

    $label = match($statusUpper) {
        'DIAJUKAN' => 'Diajukan',
        'DIPERIKSA' => 'Sedang Diperiksa',
        'REVISI' => 'Perlu Perbaikan',
        'DIVERIFIKASI' => 'Terverifikasi Petugas',
        'MENUNGGU_PERSETUJUAN' => 'Menunggu Kades',
        'DISETUJUI' => 'Disetujui Kades',
        'DITOLAK' => 'Ditolak',
        'DIPROSES' => 'Sedang Diproses',
        'SELESAI' => 'Selesai Terbit',
        default => ucfirst(strtolower(str_replace('_', ' ', $statusUpper))),
    };
@endphp

<span class="badge {{ $badgeClass }}">
    <i class="fas {{ $iconClass }}"></i>
    <span>{{ $label }}</span>
</span>
