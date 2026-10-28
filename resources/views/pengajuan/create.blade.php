@extends('layouts.app')

@section('title', 'Buat Pengajuan Surat Baru')
@section('breadcrumb', 'Buat Pengajuan')

@section('content')
<div class="pengajuan-create-page" style="max-width: 900px; margin: 0 auto;">
    <!-- Step 1: Select Pelayanan if not selected -->
    @if(!isset($jenisPelayanan) && (!isset($selectedPelayanan) || !$selectedPelayanan))
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header" style="background: #ffffff; padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color);">
                <h3 style="margin: 0; font-size: 1.2rem; font-weight: 800; color: var(--header-navy);">
                    <i class="fas fa-list-check" style="color: var(--accent-primary); margin-right: 0.5rem;"></i>
                    Langkah 1: Pilih Jenis Pelayanan Administrasi
                </h3>
            </div>
            <div class="card-body" style="padding: 1.5rem;">
                <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1.5rem;">
                    Silakan tentukan jenis surat administrasi kependudukan yang ingin Anda ajukan:
                </p>
                @php
                    $listLayanan = $jenisPelayananList ?? \App\Models\JenisPelayanan::where('is_active', true)->get();
                @endphp
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 1rem;">
                    @foreach($listLayanan as $layanan)
                        <a href="{{ request()->fullUrlWithQuery(['id_pelayanan' => $layanan->id_pelayanan]) }}" 
                           class="btn btn-outline" 
                           style="text-align: left; padding: 1rem; border-radius: var(--radius-md); display: flex; align-items: center; gap: 0.85rem; text-decoration: none; border-color: var(--border-color); color: var(--header-navy);"
                           onmouseover="this.style.borderColor='var(--accent-primary)'; this.style.background='#f8fafc';"
                           onmouseout="this.style.borderColor='var(--border-color)'; this.style.background='transparent';">
                            <div style="width: 40px; height: 40px; border-radius: 8px; background: #eff6ff; color: var(--accent-primary); display: flex; align-items: center; justify-content: center; font-size: 1.15rem; flex-shrink: 0;">
                                <i class="fas {{ $layanan->icon ?? 'fa-file-lines' }}"></i>
                            </div>
                            <div style="flex: 1;">
                                <div style="font-weight: 700; font-size: 0.9rem; color: var(--header-navy);">{{ $layanan->nama_pelayanan }}</div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">Pilih layanan ini &rarr;</div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    @else
        @php
            $currentLayanan = $jenisPelayanan ?? $selectedPelayanan;
        @endphp

        <!-- Service Info Banner -->
        <div class="card" style="margin-bottom: 1.5rem; border-left: 4px solid var(--accent-primary);">
            <div class="card-body" style="padding: 1.25rem 1.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div style="width: 50px; height: 50px; border-radius: 12px; background: #eff6ff; color: var(--accent-primary); display: flex; align-items: center; justify-content: center; font-size: 1.4rem;">
                        <i class="fas {{ $currentLayanan->icon ?? 'fa-file-lines' }}"></i>
                    </div>
                    <div>
                        <span style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--accent-primary); letter-spacing: 0.5px;">Permohonan Surat</span>
                        <h3 style="margin: 0; font-size: 1.25rem; font-weight: 800; color: var(--header-navy);">
                            {{ $currentLayanan->nama_pelayanan }}
                        </h3>
                    </div>
                </div>
                <a href="{{ Route::has('pengajuan.create') ? route('pengajuan.create') : url('/warga/pengajuan/create') }}" 
                   class="btn btn-outline btn-sm" style="font-size: 0.8rem; text-decoration: none;">
                    <i class="fas fa-arrows-rotate"></i> Ganti Jenis Layanan
                </a>
            </div>
            @if($currentLayanan->deskripsi || $currentLayanan->persyaratan)
                <div style="background: #f8fafc; padding: 1rem 1.5rem; border-top: 1px solid var(--border-color); font-size: 0.85rem; color: var(--text-secondary);">
                    @if($currentLayanan->deskripsi)
                        <p style="margin: 0 0 0.5rem 0;">{{ $currentLayanan->deskripsi }}</p>
                    @endif
                    @if($currentLayanan->persyaratan)
                        <div style="font-weight: 600; color: var(--header-navy); margin-bottom: 0.25rem;">
                            <i class="fas fa-circle-info text-primary"></i> Persyaratan Dokumen:
                        </div>
                        <div style="color: var(--text-muted); line-height: 1.4;">
                            {{ $currentLayanan->persyaratan }}
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <!-- Form Submission -->
        <form action="{{ Route::has('pengajuan.store') ? route('pengajuan.store') : (Route::has('warga.pengajuan.store') ? route('warga.pengajuan.store') : url('/warga/pengajuan')) }}" 
              method="POST" 
              enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="id_pelayanan" value="{{ $currentLayanan->id_pelayanan }}">

            <!-- Formulir Data Khusus Layanan -->
            <div class="card" style="margin-bottom: 1.5rem;">
                <div class="card-header" style="background: #ffffff; padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color);">
                    <h4 style="margin: 0; font-size: 1.05rem; font-weight: 700; color: var(--header-navy);">
                        <i class="fas fa-pen-to-square" style="color: var(--accent-primary); margin-right: 0.4rem;"></i>
                        1. Informasi Formulir Permohonan
                    </h4>
                </div>
                <div class="card-body" style="padding: 1.5rem;">
                    @php
                        $schema = is_array($currentLayanan->formulir_schema) 
                            ? $currentLayanan->formulir_schema 
                            : (json_decode($currentLayanan->formulir_schema, true) ?? []);
                    @endphp

                    @if(empty($schema))
                        <!-- Default field if schema is empty -->
                        <div class="form-group" style="margin-bottom: 1.25rem;">
                            <label class="form-label" style="display: block; font-weight: 600; font-size: 0.88rem; color: var(--header-navy); margin-bottom: 0.4rem;">
                                Keperluan Permohonan <span style="color: #dc2626;">*</span>
                            </label>
                            <textarea name="keperluan" class="form-control" rows="3" placeholder="Jelaskan maksud dan keperluan permohonan surat ini..." required style="width: 100%; padding: 0.65rem 0.85rem; border: 1px solid var(--border-color); border-radius: 8px;"></textarea>
                        </div>
                    @else
                        @foreach($schema as $field)
                            @php
                                $fieldName = $field['name'] ?? 'field_' . $loop->index;
                                $fieldLabel = $field['label'] ?? ucfirst($fieldName);
                                $fieldType = $field['type'] ?? 'text';
                                $isRequired = !empty($field['required']);
                                $fieldPlaceholder = $field['placeholder'] ?? '';
                            @endphp

                            <div class="form-group" style="margin-bottom: 1.25rem;">
                                <label class="form-label" style="display: block; font-weight: 600; font-size: 0.88rem; color: var(--header-navy); margin-bottom: 0.4rem;">
                                    {{ $fieldLabel }}
                                    @if($isRequired)
                                        <span style="color: #dc2626;">*</span>
                                    @endif
                                </label>

                                @if($fieldType === 'textarea')
                                    <textarea name="{{ $fieldName }}" 
                                              class="form-control" 
                                              rows="3" 
                                              placeholder="{{ $fieldPlaceholder ?: 'Ketik di sini...' }}" 
                                              {{ $isRequired ? 'required' : '' }}
                                              style="width: 100%; padding: 0.65rem 0.85rem; border: 1px solid var(--border-color); border-radius: 8px;">{{ old($fieldName) }}</textarea>
                                @elseif($fieldType === 'select')
                                    <select name="{{ $fieldName }}" 
                                            class="form-control" 
                                            {{ $isRequired ? 'required' : '' }}
                                            style="width: 100%; padding: 0.65rem 0.85rem; border: 1px solid var(--border-color); border-radius: 8px;">
                                        <option value="">-- Pilih {{ $fieldLabel }} --</option>
                                        @foreach($field['options'] ?? [] as $opt)
                                            <option value="{{ $opt }}" {{ old($fieldName) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="{{ $fieldType }}" 
                                           name="{{ $fieldName }}" 
                                           class="form-control" 
                                           value="{{ old($fieldName) }}" 
                                           placeholder="{{ $fieldPlaceholder }}" 
                                           {{ $isRequired ? 'required' : '' }}
                                           style="width: 100%; padding: 0.65rem 0.85rem; border: 1px solid var(--border-color); border-radius: 8px;">
                                @endif
                            </div>
                        @endforeach
                    @endif

                    <!-- Catatan Tambahan (Opsional) -->
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="display: block; font-weight: 600; font-size: 0.88rem; color: var(--header-navy); margin-bottom: 0.4rem;">
                            Catatan Tambahan untuk Petugas <span style="font-weight: 400; color: var(--text-muted);">(Opsional)</span>
                        </label>
                        <textarea name="catatan" class="form-control" rows="2" placeholder="Tuliskan catatan atau pesan tambahan jika ada..." style="width: 100%; padding: 0.65rem 0.85rem; border: 1px solid var(--border-color); border-radius: 8px;">{{ old('catatan') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Upload Dokumen Persyaratan -->
            <div class="card" style="margin-bottom: 1.5rem;">
                <div class="card-header" style="background: #ffffff; padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color);">
                    <h4 style="margin: 0; font-size: 1.05rem; font-weight: 700; color: var(--header-navy);">
                        <i class="fas fa-file-arrow-up" style="color: var(--accent-primary); margin-right: 0.4rem;"></i>
                        2. Unggah Berkas Persyaratan (PDF, JPG, PNG — Maks. 2MB)
                    </h4>
                </div>
                <div class="card-body" style="padding: 1.5rem;">
                    <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 0.85rem 1rem; margin-bottom: 1.25rem; font-size: 0.85rem; color: #1e40af; display: flex; align-items: center; gap: 0.6rem;">
                        <i class="fas fa-shield-halved" style="font-size: 1.1rem;"></i>
                        <span>Berkas Anda disimpan terenkripsi di server privat desa dan hanya dapat ditinjau oleh petugas pemeriksa yang berwenang.</span>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.25rem;">
                        <!-- Scan KTP -->
                        <div style="background: #f8fafc; border: 1px dashed var(--border-accent); border-radius: var(--radius-md); padding: 1.25rem; text-align: center;">
                            <i class="fas fa-id-card" style="font-size: 2rem; color: var(--accent-primary); margin-bottom: 0.5rem; display: block;"></i>
                            <strong style="display: block; font-size: 0.9rem; color: var(--header-navy); margin-bottom: 0.25rem;">Scan / Foto KTP <span style="color: #dc2626;">*</span></strong>
                            <p style="font-size: 0.78rem; color: var(--text-muted); margin-bottom: 0.75rem;">Format PDF/JPG/PNG max 2MB</p>
                            <input type="file" name="dokumen[ktp]" accept=".pdf,.jpg,.jpeg,.png" required style="font-size: 0.8rem; width: 100%;">
                        </div>

                        <!-- Scan KK -->
                        <div style="background: #f8fafc; border: 1px dashed var(--border-accent); border-radius: var(--radius-md); padding: 1.25rem; text-align: center;">
                            <i class="fas fa-users-line" style="font-size: 2rem; color: var(--accent-primary); margin-bottom: 0.5rem; display: block;"></i>
                            <strong style="display: block; font-size: 0.9rem; color: var(--header-navy); margin-bottom: 0.25rem;">Kartu Keluarga (KK) <span style="color: #dc2626;">*</span></strong>
                            <p style="font-size: 0.78rem; color: var(--text-muted); margin-bottom: 0.75rem;">Format PDF/JPG/PNG max 2MB</p>
                            <input type="file" name="dokumen[kk]" accept=".pdf,.jpg,.jpeg,.png" required style="font-size: 0.8rem; width: 100%;">
                        </div>

                        <!-- Berkas Pendukung Tambahan -->
                        <div style="background: #f8fafc; border: 1px dashed var(--border-accent); border-radius: var(--radius-md); padding: 1.25rem; text-align: center;">
                            <i class="fas fa-file-invoice" style="font-size: 2rem; color: #64748b; margin-bottom: 0.5rem; display: block;"></i>
                            <strong style="display: block; font-size: 0.9rem; color: var(--header-navy); margin-bottom: 0.25rem;">Dokumen Pendukung <span style="font-weight: 400; color: var(--text-muted);">(Opsional)</span></strong>
                            <p style="font-size: 0.78rem; color: var(--text-muted); margin-bottom: 0.75rem;">Surat RT/RW atau bukti terkait</p>
                            <input type="file" name="dokumen[pendukung]" accept=".pdf,.jpg,.jpeg,.png" style="font-size: 0.8rem; width: 100%;">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; justify-content: flex-end; gap: 1rem; align-items: center;">
                <a href="{{ Route::has('pengajuan.index') ? route('pengajuan.index') : url('/warga/pengajuan') }}" 
                   class="btn btn-outline" style="text-decoration: none;">
                    Batal
                </a>
                <button type="submit" class="btn btn-primary btn-lg" style="padding: 0.75rem 2rem; font-weight: 700;">
                    <i class="fas fa-paper-plane" style="margin-right: 0.5rem;"></i> Kirim Pengajuan Surat
                </button>
            </div>
        </form>
    @endif
</div>
@endsection
