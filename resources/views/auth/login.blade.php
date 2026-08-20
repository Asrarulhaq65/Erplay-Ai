<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk Akun — {{ config('app.name', 'ERPlay AI') }}</title>
    
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

        /* ── Main Login Container Card ───────────────────────────────── */
        .login-card {
            position: relative;
            z-index: 10;
            display: flex;
            width: 880px;
            max-width: 92vw;
            min-height: 520px;
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
        .login-brand-panel {
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

        /* Quick fill badges for testing */
        .quick-fill-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 8px;
        }
        .quick-fill-btn {
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #FFFFFF;
            font-size: 11px;
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .quick-fill-btn:hover {
            background: rgba(255, 255, 255, 0.25);
            color: #FFFFFF;
            transform: translateY(-1px);
        }

        /* ── Form Panel Right ────────────────────────────────────────── */
        .login-form-panel {
            flex: 1.3;
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
            margin-bottom: 1.25rem;
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
        .btn-login-submit {
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
        }

        .btn-login-submit:hover {
            background: #09373D;
            color: #FFFFFF;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(13, 78, 86, 0.35);
        }

        /* Responsive Layout */
        @media (max-width: 860px) {
            .login-card {
                flex-direction: column;
                max-width: 460px;
            }
            .login-brand-panel {
                padding: 2rem;
                border-radius: 20px 20px 0 0;
            }
            .login-form-panel {
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
    <div class="login-card">
        <!-- Brand Area Left -->
        <div class="login-brand-panel">
            <div>
                <div class="brand-logo-icon" aria-hidden="true">
                    <i class="bi bi-shop-window"></i>
                </div>
                <h1 class="brand-title-text">{{ config('app.name', 'ERPlay AI') }}</h1>
                <p class="brand-subtitle-text">
                    Sistem Kasir & Manajemen Retail Terintegrasi untuk efisiensi bisnis toko dan operasional usaha Anda.
                </p>
            </div>

            <!-- Quick Demo Credentials for Fast Testing -->
            <div class="mt-4">
                <div class="quick-fill-title"><i class="bi bi-lightning-charge me-1" aria-hidden="true"></i>Uji Coba Akun Demo:</div>
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="quick-fill-btn" onclick="fillAccount('superadmin', 'superadmin123')">
                        <i class="bi bi-shield-check" aria-hidden="true"></i> Super Admin
                    </button>
                    <button type="button" class="quick-fill-btn" onclick="fillAccount('kasir', 'password')">
                        <i class="bi bi-cart-check" aria-hidden="true"></i> Kasir Toko
                    </button>
                </div>
            </div>
        </div>

        <!-- Form Area Right -->
        <div class="login-form-panel">
            <div class="mb-3">
                <h2 class="form-header-title">Selamat Datang</h2>
                <p class="form-header-sub">Silakan masuk menggunakan kredensial akun Anda.</p>
            </div>

            @if($errors->any())
                <div class="alert alert-danger p-3 mb-3 border-0 rounded-3" style="font-size:0.9rem;" role="alert">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-circle-fill text-danger fs-5" aria-hidden="true"></i>
                        <div>
                            @foreach($errors->all() as $error)
                                {{ $error }}<br>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success p-3 mb-3 border-0 rounded-3 d-flex align-items-center justify-content-between" style="font-size:0.9rem;" role="alert">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill text-success fs-5" aria-hidden="true"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert" aria-label="Tutup"></button>
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST">
                @csrf

                <!-- Username Input -->
                <div class="mb-3">
                    <label for="username" class="form-label fw-semibold mb-1" style="font-size:13px;color:var(--text-secondary);">Username</label>
                    <div class="input-group-custom">
                        <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan username Anda" value="{{ old('username') }}" required autofocus autocomplete="username">
                        <i class="bi bi-person input-icon-right" aria-hidden="true"></i>
                    </div>
                </div>

                <!-- Password Input with Show/Hide Toggle -->
                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold mb-1" style="font-size:13px;color:var(--text-secondary);">Password</label>
                    <div class="input-group-custom">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password Anda" required autocomplete="current-password">
                        <i class="bi bi-eye-slash input-icon-right" id="togglePassword" title="Tampilkan Password" aria-label="Tampilkan Password"></i>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label fw-medium" for="remember" style="font-size:13px;color:var(--text-secondary);">
                            Ingat sesi saya
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn-login-submit">
                    Masuk Sekarang <i class="bi bi-arrow-right-circle" aria-hidden="true"></i>
                </button>

                <div class="text-center mt-4" style="font-size:13px;color:var(--text-secondary);">
                    Belum memiliki toko? <a href="{{ route('register') }}" style="color:var(--pb-accent);text-decoration:none;font-weight:700;">Daftar Toko Baru</a>
                </div>
            </form>
            
            <div class="text-center mt-4">
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
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function() {
                const isPassword = passwordInput.getAttribute('type') === 'password';
                passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                this.className = isPassword ? 'bi bi-eye input-icon-right' : 'bi bi-eye-slash input-icon-right';
            });
        }

        // ── Quick Fill Demo Account Helper ────────────────────────────────────
        function fillAccount(username, password) {
            const usernameInput = document.getElementById('username');
            const passInput = document.getElementById('password');
            if (usernameInput) usernameInput.value = username;
            if (passInput) passInput.value = password;
        }
    </script>
</body>
</html>
