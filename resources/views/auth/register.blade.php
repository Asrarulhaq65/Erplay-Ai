<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0D4E56">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="ERPlay ERP">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.ico') }}">
    <title>Daftar Toko Baru — {{ config('app.name', 'ERPlay AI') }}</title>
    
    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    {{-- Google Font: Plus Jakarta Sans --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        /* ── CSS Tokens & Theme Variables ────────────────────────────── */
        :root {
            --pb-dark     : #0D4E56;
            --pb-darker   : #09373D;
            --pb-accent   : #4DB8C4;
            --pb-lightest : #EBF3F5;
            --bg-body     : #F8FAFC;
            --bg-card     : #FFFFFF;
            --text-primary: #0F172A;
            --text-secondary: #475569;
            --text-muted  : #94A3B8;
            --border-color: #E2E8F0;
            --card-shadow : 0 20px 40px rgba(13, 78, 86, 0.08);
        }

        [data-theme="dark"] {
            --pb-dark     : #4DB8C4;
            --pb-darker   : #389BA6;
            --pb-accent   : #4DB8C4;
            --pb-lightest : #162428;
            --bg-body     : #0F171A;
            --bg-card     : #152226;
            --text-primary: #F8FAFC;
            --text-secondary: #CBD5E1;
            --text-muted  : #64748B;
            --border-color: #24363D;
            --card-shadow : 0 20px 50px rgba(0, 0, 0, 0.4);
        }

        *, *::before, *::after { box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-primary);
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
            padding: 20px 0;
            transition: background-color 0.25s ease, color 0.25s ease;
        }

        /* ── Modern Hardware-Accelerated Ambient Light ────────────────── */
        .ambient-light {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            pointer-events: none;
            overflow: hidden;
            z-index: 0;
        }

        .ambient-glow-1 {
            position: absolute;
            top: -15%; left: -10%;
            width: 50vw; height: 50vw;
            background: radial-gradient(circle, rgba(13, 78, 86, 0.12) 0%, rgba(13, 78, 86, 0) 70%);
            will-change: transform;
            animation: pulseGlow 12s infinite alternate ease-in-out;
        }

        .ambient-glow-2 {
            position: absolute;
            bottom: -20%; right: -10%;
            width: 55vw; height: 55vw;
            background: radial-gradient(circle, rgba(77, 184, 196, 0.14) 0%, rgba(77, 184, 196, 0) 70%);
            will-change: transform;
            animation: pulseGlow 15s infinite alternate-reverse ease-in-out;
        }

        @keyframes pulseGlow {
            0% { transform: scale(1) translate(0, 0); }
            100% { transform: scale(1.1) translate(3%, 4%); }
        }

        /* ── Floating Theme Toggle Button ────────────────────────────── */
        .theme-toggle-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 100;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            width: 42px; height: 42px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: all 0.2s ease;
        }
        .theme-toggle-btn:hover {
            transform: scale(1.05);
            border-color: var(--pb-accent);
        }

        /* ── Main Registration Container Card ───────────────────────── */
        .register-card {
            position: relative;
            z-index: 10;
            display: flex;
            width: 920px;
            max-width: 92vw;
            min-height: 560px;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            animation: cardAppear 0.6s cubic-bezier(0.16, 1, 0.3, 1);
            transition: background-color 0.25s ease, border-color 0.25s ease;
        }

        @keyframes cardAppear {
            from { opacity: 0; transform: translateY(24px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* ── Brand Panel Left ────────────────────────────────────────── */
        .register-brand-panel {
            flex: 1.1;
            background: linear-gradient(135deg, #09373D 0%, #0D4E56 100%);
            padding: 3rem 2.5rem;
            color: #FFFFFF;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .brand-logo-icon {
            width: 52px; height: 52px;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.2);
            display: flex; align-items: center; justify-content: center;
            font-size: 26px;
            color: #FFFFFF;
            backdrop-filter: blur(8px);
            margin-bottom: 2rem;
        }

        .brand-title-text {
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            line-height: 1.15;
            margin-bottom: 0.75rem;
        }

        .brand-subtitle-text {
            font-size: 0.95rem;
            color: rgba(255, 255, 255, 0.82);
            line-height: 1.6;
            font-weight: 400;
        }

        /* ── Form Panel Right ────────────────────────────────────────── */
        .register-form-panel {
            flex: 1.5;
            padding: 3rem 2.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-header-title {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--text-primary);
            letter-spacing: -0.03em;
            margin-bottom: 0.25rem;
        }

        .form-header-sub {
            font-size: 0.9rem;
            color: var(--text-secondary);
            margin-bottom: 1.5rem;
        }

        /* Form Inputs */
        .input-group-custom {
            position: relative;
            margin-bottom: 1rem;
        }

        .input-group-custom .form-control {
            border: 1px solid var(--border-color);
            background: var(--bg-body);
            color: var(--text-primary);
            border-radius: 10px;
            padding: 12px 42px 12px 16px;
            font-size: 0.95rem;
            font-weight: 500;
            transition: border-color 0.2s, box-shadow 0.2s;
            height: 48px;
        }

        .input-group-custom .form-control::placeholder {
            color: var(--text-secondary) !important;
            opacity: 0.85 !important;
        }

        .input-group-custom .form-control:focus {
            border-color: #0D4E56;
            box-shadow: 0 0 0 3px rgba(13, 78, 86, 0.15);
            outline: none;
        }

        .input-icon-right {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 1.1rem;
            cursor: pointer;
            z-index: 5;
            transition: color 0.2s;
        }

        .input-icon-right:hover {
            color: var(--text-primary);
        }

        /* Primary Button */
        .btn-register-submit {
            background: #0D4E56;
            color: #FFFFFF;
            border: none;
            padding: 12px 20px;
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 700;
            width: 100%;
            letter-spacing: -0.01em;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(13, 78, 86, 0.25);
            display: flex; align-items: center; justify-content: center;
            gap: 8px;
            height: 48px;
            margin-top: 0.5rem;
        }

        .btn-register-submit:hover {
            background: #09373D;
            color: #FFFFFF;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(13, 78, 86, 0.35);
        }

        /* Responsive Layout */
        @media (max-width: 860px) {
            .register-card {
                flex-direction: column;
                max-width: 480px;
            }
            .register-brand-panel {
                padding: 2rem;
                border-radius: 20px 20px 0 0;
            }
            .register-form-panel {
                padding: 2rem;
            }
        }
    </style>
</head>
<body>

    <!-- Ambient Glow background -->
    <div class="ambient-light">
        <div class="ambient-glow-1"></div>
        <div class="ambient-glow-2"></div>
    </div>

    <!-- Theme Toggle Floating Button -->
    <button class="theme-toggle-btn" id="themeToggleBtn" title="Tukar Mode Terang/Gelap" aria-label="Toggle Theme">
        <i class="bi bi-moon-stars" id="themeIcon" aria-hidden="true"></i>
    </button>

    <!-- Main Card -->
    <div class="register-card">
        <!-- Brand Area Left -->
        <div class="register-brand-panel">
            <div>
                <div class="brand-logo-icon" aria-hidden="true">
                    <i class="bi bi-shop"></i>
                </div>
                <h1 class="brand-title-text">{{ config('app.name', 'ERPlay AI') }}</h1>
                <p class="brand-subtitle-text">
                    Daftarkan toko atau bisnis retail Anda sekarang dan kelola transaksi, stok, hingga kas secara real-time dalam satu genggaman.
                </p>
            </div>

            <!-- Features list hint -->
            <div class="mt-4">
                <div style="font-size:12px;color:rgba(255,255,255,0.85);" class="d-flex flex-column gap-2">
                    <div><i class="bi bi-check-circle-fill me-2 text-info" aria-hidden="true"></i>Point of Sale (POS) Standar & Mode Kilat</div>
                    <div><i class="bi bi-check-circle-fill me-2 text-info" aria-hidden="true"></i>Manajemen Stok, HPP & Multi-Tier Harga</div>
                    <div><i class="bi bi-check-circle-fill me-2 text-info" aria-hidden="true"></i>Laporan Executive Analytics Real-Time</div>
                </div>
            </div>
        </div>

        <!-- Form Area Right -->
        <div class="register-form-panel">
            <div class="mb-3">
                <h2 class="form-header-title">Pendaftaran Toko Baru</h2>
                <p class="form-header-sub">Lengkapi data di bawah ini untuk mengaktifkan akun toko Anda.</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger p-3 mb-3 border-0 rounded-3" style="font-size:0.9rem;" role="alert">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-circle-fill text-danger fs-5" aria-hidden="true"></i>
                        <div>
                            @foreach ($errors->all() as $error)
                                {{ $error }}<br>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <form action="{{ route('register') }}" method="POST">
                @csrf

                <div class="row g-2">
                    <!-- Nama Toko -->
                    <div class="col-12 col-md-6 mb-3">
                        <label for="nama_toko" class="form-label fw-semibold mb-1" style="font-size:13px;color:var(--text-secondary);">Nama Toko / Usaha <span class="text-danger">*</span></label>
                        <div class="input-group-custom">
                            <input type="text" name="nama_toko" class="form-control" id="nama_toko" placeholder="Contoh: Toko Barokah Jaya" value="{{ old('nama_toko') }}" required autofocus autocomplete="organization">
                            <i class="bi bi-shop-window input-icon-right" aria-hidden="true"></i>
                        </div>
                    </div>

                    <!-- Nama Pemilik -->
                    <div class="col-12 col-md-6 mb-3">
                        <label for="nama_lengkap" class="form-label fw-semibold mb-1" style="font-size:13px;color:var(--text-secondary);">Nama Pemilik / Owner <span class="text-danger">*</span></label>
                        <div class="input-group-custom">
                            <input type="text" name="nama_lengkap" class="form-control" id="nama_lengkap" placeholder="Contoh: Ahmad Subagyo" value="{{ old('nama_lengkap') }}" required autocomplete="name">
                            <i class="bi bi-person-badge input-icon-right" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>

                <!-- Username Login -->
                <div class="mb-3">
                    <label for="username" class="form-label fw-semibold mb-1" style="font-size:13px;color:var(--text-secondary);">Username (untuk Login) <span class="text-danger">*</span></label>
                    <div class="input-group-custom">
                        <input type="text" name="username" class="form-control" id="username" placeholder="Contoh: ahmad_barokah" value="{{ old('username') }}" required autocomplete="username">
                        <i class="bi bi-person input-icon-right" aria-hidden="true"></i>
                    </div>
                </div>

                <div class="row g-2">
                    <!-- Password -->
                    <div class="col-12 col-md-6 mb-3">
                        <label for="password" class="form-label fw-semibold mb-1" style="font-size:13px;color:var(--text-secondary);">Password <span class="text-danger">*</span></label>
                        <div class="input-group-custom">
                            <input type="password" name="password" class="form-control" id="password" placeholder="Minimal 6 karakter" required autocomplete="new-password">
                            <i class="bi bi-eye-slash input-icon-right" id="togglePassword" title="Tampilkan Password" aria-label="Tampilkan Password"></i>
                        </div>
                    </div>

                    <!-- Konfirmasi Password -->
                    <div class="col-12 col-md-6 mb-3">
                        <label for="password_confirmation" class="form-label fw-semibold mb-1" style="font-size:13px;color:var(--text-secondary);">Konfirmasi Password <span class="text-danger">*</span></label>
                        <div class="input-group-custom">
                            <input type="password" name="password_confirmation" class="form-control" id="password_confirmation" placeholder="Ulangi password" required autocomplete="new-password">
                            <i class="bi bi-eye-slash input-icon-right" id="togglePasswordConfirm" title="Tampilkan Konfirmasi Password" aria-label="Tampilkan Konfirmasi Password"></i>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-register-submit">
                    Daftar Toko Sekarang <i class="bi bi-arrow-right-circle" aria-hidden="true"></i>
                </button>

                <div class="text-center mt-4" style="font-size:13px;color:var(--text-secondary);">
                    Sudah memiliki akun toko? <a href="{{ route('login') }}" style="color:var(--pb-accent);text-decoration:none;font-weight:700;">Masuk di sini</a>
                </div>
            </form>
            
            <div class="text-center mt-3">
                <small style="color:var(--text-muted);font-size:11px;">
                    &copy; {{ date('Y') }} {{ config('app.name', 'ERPlay AI') }}. Kelola toko, gak pusing, ada AI yang bantu.
                </small>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // ── Theme Handler ────────────────────────────────────────────────────
        const themeBtn = document.getElementById('themeToggleBtn');
        const themeIcon = document.getElementById('themeIcon');

        function applyTheme(theme) {
            if (theme === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
                if (themeIcon) themeIcon.className = 'bi bi-sun-fill text-warning';
            } else {
                document.documentElement.removeAttribute('data-theme');
                if (themeIcon) themeIcon.className = 'bi bi-moon-stars';
            }
        }

        const savedTheme = localStorage.getItem('erp_theme') || 
            (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        applyTheme(savedTheme);

        themeBtn.addEventListener('click', function() {
            const current = document.documentElement.getAttribute('data-theme');
            const nextTheme = current === 'dark' ? 'light' : 'dark';
            localStorage.setItem('erp_theme', nextTheme);
            applyTheme(nextTheme);
        });

        // ── Toggle Password Visibility ────────────────────────────────────────
        function setupTogglePassword(toggleId, inputId) {
            const toggleEl = document.getElementById(toggleId);
            const inputEl = document.getElementById(inputId);
            if (toggleEl && inputEl) {
                toggleEl.addEventListener('click', function() {
                    const isPassword = inputEl.getAttribute('type') === 'password';
                    inputEl.setAttribute('type', isPassword ? 'text' : 'password');
                    this.className = isPassword ? 'bi bi-eye input-icon-right' : 'bi bi-eye-slash input-icon-right';
                });
            }
        }

        setupTogglePassword('togglePassword', 'password');
        setupTogglePassword('togglePasswordConfirm', 'password_confirmation');
    </script>
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => navigator.serviceWorker.register('{{ asset('service-worker.js') }}'));
    }
</script>
</body>
</html>
