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

        .demo-box {
            margin-top: 1.5rem;
            padding: 1rem;
            background: #f8fafc;
            border: 1px dashed var(--border-color);
            border-radius: var(--radius-md);
            font-size: 0.78rem;
            color: var(--text-secondary);
        }

        .demo-box strong {
            color: var(--header-navy);
            display: block;
            margin-bottom: 0.35rem;
        }

        .demo-pill {
            cursor: pointer;
            padding: 0.2rem 0.5rem;
            background: #e2e8f0;
            border-radius: 4px;
            margin: 0.15rem 0.15rem;
            display: inline-block;
            font-family: monospace;
            font-size: 0.75rem;
            transition: background 0.15s;
        }
        .demo-pill:hover {
            background: #cbd5e1;
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
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="form-control" 
                           placeholder="Masukkan kata sandi" 
                           required
                           style="width: 100%; padding: 0.7rem 0.9rem; border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.9rem;">
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

            <div style="text-align: center; margin: 1.25rem 0; position: relative;">
                <hr style="border: none; border-top: 1px solid var(--border-color); margin: 0;">
                <span style="position: absolute; top: -10px; left: 50%; transform: translateX(-50%); background: #ffffff; padding: 0 0.75rem; font-size: 0.75rem; color: var(--text-muted);">
                    ATAU
                </span>
            </div>

            <a href="{{ Route::has('register') ? route('register') : url('/register') }}" 
               class="btn btn-outline btn-block" 
               style="width: 100%; padding: 0.7rem; border: 1px solid var(--border-color); border-radius: 8px; color: var(--header-navy); font-weight: 600; font-size: 0.9rem; text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 0.5rem; background: #ffffff;">
                <i class="fas fa-user-plus"></i> Daftar Akun Warga Baru
            </a>

            <!-- Quick Demo Helper -->
            <div class="demo-box">
                <strong><i class="fas fa-key"></i> Akun Pengujian Resmi:</strong>
                <div>Admin: <span class="demo-pill" onclick="fillForm('admin@sipades.go.id', 'Admin123!')">admin@sipades.go.id</span></div>
                <div>Petugas: <span class="demo-pill" onclick="fillForm('petugas@sipades.go.id', 'Petugas123!')">petugas@sipades.go.id</span></div>
                <div>Kades: <span class="demo-pill" onclick="fillForm('kades@sipades.go.id', 'Kades123!')">kades@sipades.go.id</span></div>
                <div>Warga: <span class="demo-pill" onclick="fillForm('warga@sipades.go.id', 'Warga123!')">warga@sipades.go.id</span></div>
            </div>
        </div>
    </div>

    <script>
        function fillForm(email, pass) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = pass;
        }
    </script>
</body>
</html>
