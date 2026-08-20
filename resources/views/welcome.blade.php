<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="ERPlay AI — Kelola toko, gak pusing, ada AI yang bantu. Sistem manajemen retail multi-tenant dengan POS, stok real-time, dan laporan keuangan.">
    <title>{{ config('app.name', 'ERPlay AI') }} — Kelola toko, gak pusing, ada AI yang bantu.</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 & Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --bg-body         : #EEF2F7;
            --bg-card         : #FFFFFF;
            --bg-card-hover   : #F8FAFC;
            --text-primary    : #0F172A;
            --text-secondary  : #475569;
            --text-muted      : #64748B;
            --border-light    : #CBD5E1;
            
            /* Brand Color System (Teal) */
            --pb-dark     : #0D4E56;
            --pb-darker   : #0A3C42;
            --pb-accent   : #0284C7;
            --pb-accent-bg: #E0F2FE;
            --pb-text     : #0D3C3F;
            
            /* Glassmorphism & UI Tokens */
            --nav-bg      : rgba(255, 255, 255, 0.85);
            --glass-border: rgba(203, 213, 225, 0.6);
            --shadow-sm   : 0 2px 8px rgba(0, 0, 0, 0.04);
            --shadow-md   : 0 10px 25px -5px rgba(13, 78, 86, 0.08);
            --theme-transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease;
        }

        [data-theme="dark"] {
            --bg-body         : #0F172A;
            --bg-card         : #1E293B;
            --bg-card-hover   : #334155;
            --text-primary    : #F8FAFC;
            --text-secondary  : #94A3B8;
            --text-muted      : #CBD5E1;
            --border-light    : #334155;

            --pb-dark     : #38A0AD;
            --pb-darker   : #2D8D99;
            --pb-accent   : #38BDF8;
            --pb-accent-bg: rgba(56, 189, 248, 0.12);
            --pb-text     : #F8FAFC;

            --nav-bg      : rgba(15, 23, 42, 0.88);
            --glass-border: rgba(51, 65, 85, 0.8);
            --shadow-sm   : 0 2px 8px rgba(0, 0, 0, 0.3);
            --shadow-md   : 0 10px 25px -5px rgba(0, 0, 0, 0.4);
        }

        *, *::before, *::after { box-sizing: border-box; }

        /* Focus rings for keyboard accessibility */
        :focus-visible {
            outline: 2px solid var(--pb-accent);
            outline-offset: 2px;
        }

        .skip-link {
            position: absolute; top: -100px; left: 10px;
            background: var(--pb-dark); color: #FFFFFF; padding: 10px 20px;
            border-radius: 0 0 8px 8px; z-index: 9999; font-size: 14px;
            text-decoration: none; font-weight: 600;
        }
        .skip-link:focus { top: 0; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
            transition: var(--theme-transition);
        }

        /* ── Ambient Background Lighting ── */
        .ambient-bg {
            position: absolute;
            top: 0; left: 0; right: 0; height: 600px;
            background: radial-gradient(circle at 50% -10%, rgba(13, 78, 86, 0.15), transparent 70%);
            z-index: -1;
            pointer-events: none;
        }
        [data-theme="dark"] .ambient-bg {
            background: radial-gradient(circle at 50% -10%, rgba(56, 160, 173, 0.15), transparent 70%);
        }

        /* ── Navigation ── */
        .navbar-landing {
            position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
            background: var(--nav-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--glass-border);
            padding: 14px 0;
            transition: var(--theme-transition);
        }

        .brand-logo {
            font-size: 20px;
            font-weight: 800;
            color: var(--pb-dark);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            letter-spacing: -0.02em;
        }
        .brand-logo:hover { color: var(--pb-dark); }
        .brand-icon {
            width: 36px; height: 36px;
            border-radius: 10px;
            background: var(--pb-dark);
            color: #FFFFFF;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
        }

        /* High-Contrast Buttons */
        .btn-brand-primary {
            background-color: var(--pb-dark);
            color: #FFFFFF !important;
            font-weight: 700;
            font-size: 14px;
            padding: 10px 22px;
            border-radius: 10px;
            text-decoration: none;
            border: 1px solid transparent;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(13, 78, 86, 0.2);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-brand-primary:hover {
            background-color: var(--pb-darker);
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(13, 78, 86, 0.3);
        }

        .btn-brand-outline {
            background-color: transparent;
            color: var(--text-primary) !important;
            font-weight: 700;
            font-size: 14px;
            padding: 10px 22px;
            border-radius: 10px;
            text-decoration: none;
            border: 1.5px solid var(--border-light);
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-brand-outline:hover {
            background-color: var(--bg-card-hover);
            border-color: var(--pb-dark);
            color: var(--pb-dark) !important;
        }

        .theme-toggle-btn {
            width: 40px; height: 40px;
            border-radius: 10px;
            border: 1px solid var(--border-light);
            background: var(--bg-card);
            color: var(--text-primary);
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            transition: var(--theme-transition);
        }
        .theme-toggle-btn:hover {
            border-color: var(--pb-dark);
        }

        /* ── Hero Section ── */
        .hero-section {
            padding: 140px 0 80px;
            text-align: center;
        }

        .badge-announcement {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--pb-accent-bg);
            color: var(--pb-accent);
            border: 1px solid rgba(2, 132, 199, 0.2);
            font-size: 13px;
            font-weight: 700;
            padding: 6px 16px;
            border-radius: 50px;
            margin-bottom: 24px;
        }

        .hero-title {
            font-size: clamp(32px, 5vw, 54px);
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: -0.03em;
            color: var(--text-primary);
            max-width: 860px;
            margin: 0 auto 20px;
        }
        .hero-title span {
            color: var(--pb-dark);
        }

        .hero-subtitle {
            font-size: clamp(15px, 2vw, 18px);
            color: var(--text-secondary);
            line-height: 1.6;
            max-width: 680px;
            margin: 0 auto 36px;
        }

        .hero-cta-group {
            display: flex;
            gap: 14px;
            justify-content: center;
            flex-wrap: wrap;
        }

        /* ── Key Feature Cards ── */
        .feature-section {
            padding: 40px 0 80px;
        }

        .section-header {
            text-align: center;
            max-width: 600px;
            margin: 0 auto 48px;
        }
        .section-tag {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--pb-dark);
            margin-bottom: 8px;
        }
        .section-title {
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: var(--text-primary);
        }

        .card-feature {
            background: var(--bg-card);
            border: 1px solid var(--border-light);
            border-radius: 16px;
            padding: 30px 24px;
            height: 100%;
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
            box-shadow: var(--shadow-sm);
        }
        .card-feature:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
            border-color: var(--pb-dark);
        }

        .feature-icon-wrapper {
            width: 48px; height: 48px;
            border-radius: 12px;
            background: var(--pb-accent-bg);
            color: var(--pb-dark);
            display: flex; align-items: center; justify-content: center;
            font-size: 22px;
            margin-bottom: 20px;
        }

        .feature-heading {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 10px;
        }

        .feature-desc {
            font-size: 14px;
            color: var(--text-secondary);
            line-height: 1.55;
            margin: 0;
        }

        /* ── Value Proposition Banner ── */
        .value-banner {
            background: var(--bg-card);
            border: 1px solid var(--border-light);
            border-radius: 20px;
            padding: 40px;
            box-shadow: var(--shadow-md);
            margin-bottom: 80px;
        }

        .stat-banner-number {
            font-size: 32px;
            font-weight: 800;
            color: var(--pb-dark);
            line-height: 1.1;
        }

        .stat-banner-label {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-secondary);
            margin-top: 4px;
        }

        /* ── Footer ── */
        .footer-landing {
            background: var(--bg-card);
            border-top: 1px solid var(--border-light);
            padding: 30px 0;
            margin-top: auto;
            font-size: 14px;
            color: var(--text-secondary);
            transition: var(--theme-transition);
        }
    </style>
</head>
<body>

    <!-- Skip-to-Content Link for Accessibility -->
    <a href="#main-content" class="skip-link">Langsung ke konten utama</a>

    <!-- Top Navigation Bar -->
    <nav class="navbar-landing" aria-label="Navigasi Utama">
        <div class="container d-flex align-items-center justify-content-between">
            <a href="/" class="brand-logo" aria-label="ERPlay AI Beranda">
                <div class="brand-icon" aria-hidden="true"><i class="bi bi-box-seam-fill"></i></div>
                <span>{{ config('app.name', 'ERPlay AI') }}</span>
            </a>

            <div class="d-flex align-items-center gap-3">
                <button type="button" id="themeToggle" class="theme-toggle-btn" aria-label="Ganti mode tampilan terang atau gelap">
                    <i class="bi bi-moon-stars" id="themeIcon" aria-hidden="true"></i>
                </button>

                @auth
                    <a href="{{ url('/dashboard') }}" class="btn-brand-primary">
                        <i class="bi bi-speedometer2" aria-hidden="true"></i> Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn-brand-outline">Login</a>
                    <a href="{{ route('register') }}" class="btn-brand-primary">
                        <i class="bi bi-shop" aria-hidden="true"></i> Daftar Toko
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Main Content Area -->
    <main id="main-content">
        <!-- Hero Section -->
        <section class="hero-section position-relative">
            <div class="ambient-bg"></div>
            <div class="container">
                <div class="badge-announcement">
                    <i class="bi bi-stars" aria-hidden="true"></i> Retail lebih rapi. Keputusan lebih cepat.
                </div>
                
                <h1 class="hero-title">
                    Kelola toko tanpa <span>kewalahan.</span>
                </h1>

                <p class="hero-subtitle">
                    <strong>Kelola toko, gak pusing, ada AI yang bantu.</strong><br>
                    ERPlay AI menyatukan kasir, stok, pembelian, laporan, dan bantuan AI dalam satu platform yang mudah dipakai. Supaya Anda bisa fokus melayani pelanggan, bukan mengejar data yang tercecer.
                </p>

                <div class="hero-cta-group">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="btn-brand-primary px-4 py-3 fs-6">
                            Masuk ke Dashboard Sistem <i class="bi bi-arrow-right" aria-hidden="true"></i>
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="btn-brand-primary px-4 py-3 fs-6">
                            Mulai Kelola Toko <i class="bi bi-arrow-right" aria-hidden="true"></i>
                        </a>
                        <a href="{{ route('login') }}" class="btn-brand-outline px-4 py-3 fs-6">
                            Sudah punya akun? Masuk <i class="bi bi-box-arrow-in-right" aria-hidden="true"></i>
                        </a>
                    @endauth
                </div>
            </div>
        </section>

        <!-- Main Features Section -->
        <section class="feature-section">
            <div class="container">
                <div class="section-header">
                    <div class="section-tag">Satu sistem untuk setiap aktivitas toko</div>
                    <h2 class="section-title">Dari transaksi pertama sampai laporan akhir hari, semuanya lebih terkendali.</h2>
                </div>

                <div class="row g-4 mb-5">
                    <div class="col-12 col-md-4">
                        <div class="card-feature">
                            <div class="feature-icon-wrapper" aria-hidden="true">
                                <i class="bi bi-lightning-charge-fill"></i>
                            </div>
                            <h3 class="feature-heading">Kasir cepat, antrean lebih singkat</h3>
                            <p class="feature-desc">
                                Proses penjualan dengan barcode, cetak struk, dan Mode Kilat untuk jam sibuk. Kasir bekerja lebih fokus, pelanggan tidak perlu menunggu lama.
                            </p>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="card-feature">
                            <div class="feature-icon-wrapper" aria-hidden="true">
                                <i class="bi bi-boxes"></i>
                            </div>
                            <h3 class="feature-heading">Stok akurat, belanja lebih terencana</h3>
                            <p class="feature-desc">
                                Pantau ketersediaan barang secara real-time, dapatkan peringatan stok menipis, dan atur harga berbeda untuk pelanggan umum, member, rekan, maupun motoris.
                            </p>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="card-feature">
                            <div class="feature-icon-wrapper" aria-hidden="true">
                                <i class="bi bi-graph-up-arrow"></i>
                            </div>
                            <h3 class="feature-heading">AI yang membantu Anda membaca bisnis</h3>
                            <p class="feature-desc">
                                Lihat omzet, tren penjualan, produk terlaris, dan kondisi stok dalam satu dashboard. Tanyakan data toko kepada ERPlay AI dan dapatkan insight yang siap ditindaklanjuti.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Value Proposition Summary Banner -->
                <div class="value-banner">
                    <div class="row text-center g-4">
                        <div class="col-6 col-md-3">
                            <div class="stat-banner-number">1 Platform</div>
                            <div class="stat-banner-label">Kasir sampai laporan</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-banner-number">Real-Time</div>
                            <div class="stat-banner-label">Stok dan performa toko</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-banner-number">AI Built-in</div>
                            <div class="stat-banner-label">Insight saat Anda butuhkan</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-banner-number">Multi-Tenant</div>
                            <div class="stat-banner-label">Aman untuk setiap toko</div>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer-landing">
        <div class="container d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                &copy; {{ date('Y') }} <strong>{{ config('app.name', 'ERPlay AI') }}</strong>. Kelola toko, gak pusing, ada AI yang bantu.
            </div>
            <div class="d-flex gap-3">
                <a href="{{ route('login') }}" class="text-decoration-none text-secondary">Masuk Kasir</a>
                <span>&bull;</span>
                <a href="{{ route('register') }}" class="text-decoration-none text-secondary">Registrasi Toko</a>
            </div>
        </div>
    </footer>

    <!-- Theme Switcher Script -->
    <script>
        (function() {
            var themeToggleBtn = document.getElementById('themeToggle');
            var themeIcon = document.getElementById('themeIcon');
            
            function setTheme(theme) {
                document.documentElement.setAttribute('data-theme', theme);
                localStorage.setItem('erp_theme', theme);
                if (theme === 'dark') {
                    themeIcon.className = 'bi bi-sun';
                } else {
                    themeIcon.className = 'bi bi-moon-stars';
                }
            }

            var savedTheme = localStorage.getItem('erp_theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            setTheme(savedTheme);

            themeToggleBtn.addEventListener('click', function() {
                var currentTheme = document.documentElement.getAttribute('data-theme');
                setTheme(currentTheme === 'dark' ? 'light' : 'dark');
            });
        })();
    </script>
</body>
</html>
