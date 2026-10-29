<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Akun Warga — SIPADES</title>

    <!-- Google Fonts & Font Awesome -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- SIPADES Government Navy Stylesheet -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    <style>
        :root {
            --header-navy: #0f2b48;
            --accent-primary: #1e6091;
            --accent-primary-hover: #154d76;
            --bg-primary: #f4f6f9;
        }

        body {
            background-color: var(--bg-primary);
            font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.5rem;
        }

        .auth-wrapper {
            width: 100%;
            max-width: 540px;
        }

        .auth-card {
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            padding: 2.25rem;
            box-shadow: 0 10px 25px -5px rgba(15, 43, 72, 0.08), 0 8px 10px -6px rgba(15, 43, 72, 0.04);
        }

        .auth-brand {
            text-align: center;
            margin-bottom: 1.75rem;
        }

        .auth-brand .logo-icon {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, #0f2b48 0%, #1e6091 100%);
            color: #ffffff;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 0.75rem;
            box-shadow: 0 4px 12px rgba(30, 96, 145, 0.25);
        }

        .auth-brand h2 {
            font-size: 1.45rem;
            font-weight: 800;
            color: var(--header-navy);
            margin: 0 0 0.35rem 0;
            letter-spacing: -0.5px;
        }

        .auth-brand p {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin: 0;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        @media (max-width: 576px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
        .password-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        .password-toggle-btn {
            position: absolute;
            right: 12px;
            background: none;
            border: none;
            cursor: pointer;
            color: #64748b;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.95rem;
            transition: color 0.15s ease;
        }
        .password-toggle-btn:hover {
            color: var(--accent-primary);
        }
    </style>
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-brand">
                <div class="logo-icon">
                    <i class="fas fa-id-card"></i>
                </div>
                <h2>Pendaftaran Akun Warga</h2>
                <p>Lengkapi data kependudukan untuk mengakses pelayanan online</p>
            </div>

            @if(session('error'))
                <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1.25rem; font-size: 0.85rem;">
                    <i class="fas fa-circle-exclamation"></i> {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1.25rem; font-size: 0.85rem;">
                    <ul style="margin: 0; padding-left: 1rem;">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ Route::has('register') ? route('register') : url('/register') }}">
                @csrf

                <!-- Nama Lengkap -->
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label" for="nama" style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--header-navy); margin-bottom: 0.35rem;">
                        Nama Lengkap (Sesuai KTP) <span style="color: #dc2626;">*</span>
                    </label>
                    <input type="text" 
                           id="nama" 
                           name="nama" 
                           class="form-control" 
                           value="{{ old('nama') }}" 
                           placeholder="Contoh: Budi Santoso" 
                           required 
                           autofocus
                           style="width: 100%; padding: 0.65rem 0.85rem; border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.9rem;">
                </div>

                <div class="form-row">
                    <!-- NIK -->
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label class="form-label" for="nik" style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--header-navy); margin-bottom: 0.35rem;">
                            NIK (16 Digit) <span style="color: #dc2626;">*</span>
                        </label>
                        <input type="text" 
                               id="nik" 
                               name="nik" 
                               class="form-control" 
                               value="{{ old('nik') }}" 
                               maxlength="16" 
                               pattern="[0-9]{16}" 
                               title="NIK harus berupa 16 digit angka" 
                               placeholder="3201xxxxxxxxxxxx" 
                               required
                               style="width: 100%; padding: 0.65rem 0.85rem; border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.9rem;">
                    </div>

                    <!-- No. HP -->
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label class="form-label" for="no_hp" style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--header-navy); margin-bottom: 0.35rem;">
                            No. WhatsApp / HP <span style="color: #dc2626;">*</span>
                        </label>
                        <input type="text" 
                               id="no_hp" 
                               name="no_hp" 
                               class="form-control" 
                               value="{{ old('no_hp') }}" 
                               placeholder="081234567890" 
                               required
                               style="width: 100%; padding: 0.65rem 0.85rem; border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.9rem;">
                    </div>
                </div>

                <!-- Email -->
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label" for="email" style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--header-navy); margin-bottom: 0.35rem;">
                        Alamat Email Aktif <span style="color: #dc2626;">*</span>
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           class="form-control" 
                           value="{{ old('email') }}" 
                           placeholder="nama@email.com" 
                           required
                           style="width: 100%; padding: 0.65rem 0.85rem; border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.9rem;">
                </div>

                <!-- Alamat -->
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label" for="alamat" style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--header-navy); margin-bottom: 0.35rem;">
                        Alamat Tempat Tinggal / RT RW <span style="color: #dc2626;">*</span>
                    </label>
                    <textarea id="alamat" 
                              name="alamat" 
                              class="form-control" 
                              rows="2" 
                              placeholder="Jl. Melati No. 5 RT 02 / RW 04" 
                              required
                              style="width: 100%; padding: 0.65rem 0.85rem; border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.9rem; min-height: 70px;">{{ old('alamat') }}</textarea>
                </div>

                <div class="form-row">
                    <!-- Password -->
                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label class="form-label" for="password" style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--header-navy); margin-bottom: 0.35rem;">
                            Kata Sandi <span style="color: #dc2626;">*</span>
                        </label>
                        <div class="password-wrapper">
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   class="form-control" 
                                   placeholder="Min. 8 karakter" 
                                   minlength="8" 
                                   required
                                   style="width: 100%; padding: 0.65rem 2.4rem 0.65rem 0.85rem; border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.9rem;">
                            <button type="button" 
                                    class="password-toggle-btn" 
                                    onclick="togglePasswordVisibility('password', 'togglePasswordIcon1')" 
                                    title="Tampilkan / Sembunyikan Sandi"
                                    aria-label="Tampilkan / Sembunyikan Sandi">
                                <i class="fas fa-eye" id="togglePasswordIcon1"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Konfirmasi Password -->
                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label class="form-label" for="password_confirmation" style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--header-navy); margin-bottom: 0.35rem;">
                            Ulangi Sandi <span style="color: #dc2626;">*</span>
                        </label>
                        <div class="password-wrapper">
                            <input type="password" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   class="form-control" 
                                   placeholder="Ulangi kata sandi" 
                                   minlength="8" 
                                   required
                                   style="width: 100%; padding: 0.65rem 2.4rem 0.65rem 0.85rem; border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.9rem;">
                            <button type="button" 
                                    class="password-toggle-btn" 
                                    onclick="togglePasswordVisibility('password_confirmation', 'togglePasswordIcon2')" 
                                    title="Tampilkan / Sembunyikan Sandi"
                                    aria-label="Tampilkan / Sembunyikan Sandi">
                                <i class="fas fa-eye" id="togglePasswordIcon2"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg" style="width: 100%; padding: 0.75rem; background: var(--accent-primary); border: none; border-radius: 8px; color: #ffffff; font-weight: 700; font-size: 0.95rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                    <i class="fas fa-user-plus"></i> Selesaikan Pendaftaran
                </button>
            </form>

            <div style="text-align: center; margin-top: 1.5rem; font-size: 0.85rem; color: var(--text-muted);">
                Sudah memiliki akun terdaftar? 
                <a href="{{ Route::has('login') ? route('login') : url('/login') }}" style="color: var(--accent-primary); font-weight: 700; text-decoration: none;">
                    Masuk di sini
                </a>
            </div>
        </div>
    </div>

    <script>
        function togglePasswordVisibility(fieldId, iconId) {
            const input = document.getElementById(fieldId);
            const icon = document.getElementById(iconId);
            if (!input || !icon) return;
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
