<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — SIPADES (Sistem Pelayanan Administrasi Desa)</title>

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
            padding: 1.5rem;
        }

        .auth-wrapper {
            width: 100%;
            max-width: 440px;
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
            margin-bottom: 2rem;
        }

        .auth-brand .logo-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #0f2b48 0%, #1e6091 100%);
            color: #ffffff;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            margin-bottom: 1rem;
            box-shadow: 0 4px 12px rgba(30, 96, 145, 0.25);
        }

        .auth-brand h2 {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--header-navy);
            margin: 0 0 0.35rem 0;
            letter-spacing: -0.5px;
        }

        .auth-brand p {
            font-size: 0.875rem;
            color: var(--text-muted);
            margin: 0;
        }

    </style>
    <style>
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
            color: var(--text-muted);
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
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
                    <i class="fas fa-landmark"></i>
                </div>
                <h2>Masuk ke SIPADES</h2>
                <p>Sistem Informasi Pelayanan Administrasi Desa</p>
            </div>

            <!-- Flash Error / Success Alerts -->
            @if(session('success'))
                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1.25rem; font-size: 0.85rem;">
                    <i class="fas fa-circle-check"></i> {{ session('success') }}
                </div>
            @endif

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

            <form method="POST" action="{{ Route::has('login') ? route('login') : url('/login') }}">
                @csrf

                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label class="form-label" for="email" style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--header-navy); margin-bottom: 0.4rem;">
                        Alamat Email <span style="color: #dc2626;">*</span>
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           class="form-control" 
                           value="{{ old('email') }}" 
                           placeholder="nama@email.com" 
                           required 
                           autofocus
                           style="width: 100%; padding: 0.7rem 0.9rem; border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.9rem;">
                </div>

                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                        <label class="form-label" for="password" style="font-size: 0.85rem; font-weight: 600; color: var(--header-navy); margin: 0;">
                            Kata Sandi <span style="color: #dc2626;">*</span>
                        </label>
                    </div>
                    <div class="password-wrapper">
                        <input type="password" 
                               id="password" 
                               name="password" 
                               class="form-control" 
                               placeholder="Masukkan kata sandi" 
                               required
                               style="width: 100%; padding: 0.7rem 2.5rem 0.7rem 0.9rem; border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.9rem;">
                        <button type="button" 
                                class="password-toggle-btn" 
                                id="togglePasswordBtn" 
                                onclick="togglePasswordVisibility()" 
                                title="Tampilkan / Sembunyikan Kata Sandi"
                                aria-label="Tampilkan / Sembunyikan Kata Sandi">
                            <i class="fas fa-eye" id="togglePasswordIcon"></i>
                        </button>
                    </div>
                </div>

                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; font-size: 0.85rem;">
                    <label style="display: flex; align-items: center; gap: 0.4rem; cursor: pointer; color: var(--text-secondary);">
                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        Ingat Saya
                    </label>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg" style="width: 100%; padding: 0.75rem; background: var(--accent-primary); border: none; border-radius: 8px; color: #ffffff; font-weight: 700; font-size: 0.95rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                    <i class="fas fa-arrow-right-to-bracket"></i> Masuk
                </button>
            </form>
        </div>
    </div>

    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('togglePasswordIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
