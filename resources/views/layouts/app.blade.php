<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0D4E56">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="ERPlay ERP">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.ico') }}">
    <title>@yield('title', 'Dashboard') — {{ config('app.name', 'ERPlay AI') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/erp-shared.css') }}">
    @stack('styles')
    <style>
        /* ── Navbar (app layout specific) ─────────────────────── */
        .erp-navbar {
            background: rgba(var(--glass-bg), 0.95);
            border-bottom: 1px solid var(--border-light);
            box-shadow: 0 2px 16px rgba(0,0,0,0.06);
            padding: 5px 16px;
            transition: var(--theme-transition);
        }
        .erp-navbar .navbar-brand {
            font-size: 13px; font-weight: 700; letter-spacing: 0.3px;
            color: var(--pb-text) !important;
        }
        .erp-navbar .nav-link {
            font-size: 12px; font-weight: 500; padding: 4px 10px !important;
            border-radius: 6px; transition: background 0.15s;
            color: var(--text-secondary) !important;
        }
        .erp-navbar .nav-link:hover,
        .erp-navbar .nav-link.active { background: var(--bg-card-hover); color: var(--pb-dark) !important; }
        .erp-navbar .dropdown-menu {
            font-size: 12px;
            padding: 4px 0; min-width: 200px;
        }
        .erp-navbar .dropdown-item {
            padding: 5px 14px; font-size: 12px; color: var(--text-primary);
        }
        .erp-navbar .dropdown-item:hover { background: var(--bg-card-hover); color: var(--pb-dark); }
        .erp-navbar .dropdown-header { font-size: 10px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
        .erp-navbar .dropdown-divider { border-color: var(--border-light); }
        .navbar-toggler { border-color: var(--border-light); }
        .navbar-toggler-icon { filter: none; }
        [data-theme="dark"] .navbar-toggler-icon { filter: invert(1); }

        /* ── Navbar Theme Toggle button ────────────────────────── */
        .nav-theme-toggle {
            background: transparent; border: 1px solid var(--border-light);
            border-radius: 8px; color: var(--text-secondary); cursor: pointer;
            width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;
            font-size: 15px; transition: all 0.2s; padding: 0;
        }
        .nav-theme-toggle:hover { background: var(--bg-card-hover); color: var(--pb-dark); }
    </style>
</head>
<body>
<a href="#main-content" class="skip-link">Lewati ke konten utama</a>

{{-- ═══ NAVBAR ════════════════════════════════════════════════════ --}}
<nav class="navbar navbar-expand-lg erp-navbar">
    <div class="container-fluid">

        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ url('/') }}">
            @if(auth()->user()?->toko?->logo)
                <img src="{{ asset('storage/' . auth()->user()->toko->logo) }}" alt="Logo" style="height: 24px; width: auto; object-fit: contain; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));">
            @else
                <i class="bi bi-shop-window" style="font-size:18px;"></i>
            @endif
            <span class="fw-bold" style="letter-spacing: 0.5px;">{{ auth()->user()?->toko?->nama_toko ?? config('app.name', 'ERPlay AI') }}</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#erpNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="erpNavbar">
            <ul class="navbar-nav me-auto gap-1 align-items-lg-center">

                @php
                    $role = auth()->user()?->role?->nama_role;
                    $isSuperAdmin = $role === 'Super Admin';
                    $isOwner      = $role === 'Owner';
                    $isAdminToko  = $role === 'Admin Toko';
                    $isKasir      = $role === 'Kasir';
                    $isGudang     = $role === 'Gudang';
                @endphp

                <li class="nav-item">
                    <a class="nav-link {{ request()->is('dashboard*') ? 'active' : '' }}" href="{{ url('/dashboard') }}">
                        <i class="bi bi-grid-1x2 me-1"></i>Dashboard
                    </a>
                </li>



                @if($isSuperAdmin || $isOwner || $isAdminToko || $isKasir)
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->is('pos*') ? 'active' : '' }}"
                       href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-lightning-charge me-1"></i>POS
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ url('/pos/custom') }}" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-keyboard me-2 text-primary"></i>Mode Kilat (Custom)
                        </a></li>
                        <li><a class="dropdown-item" href="{{ url('/pos/standard') }}" target="_blank" rel="noopener noreferrer">
                            <i class="bi bi-grid-3x3-gap me-2 text-success"></i>Mode Standard
                        </a></li>
                    </ul>
                </li>
                @endif

                @if($isSuperAdmin || $isOwner || $isAdminToko || $isKasir || $isGudang)
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->is('master*') ? 'active' : '' }}"
                       href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-database me-1"></i>Master Data
                    </a>
                    <ul class="dropdown-menu">
                        <li><h6 class="dropdown-header">Hierarki Produk</h6></li>
                        <li><a class="dropdown-item {{ request()->routeIs('master.kelompok*') ? 'fw-bold' : '' }}"
                               href="{{ route('master.kelompok-produk.index') }}">
                            <i class="bi bi-collection me-2" style="color:var(--pb-dark);"></i>Kelompok Produk
                        </a></li>
                        <li><a class="dropdown-item {{ request()->routeIs('master.kategori*') ? 'fw-bold' : '' }}"
                               href="{{ route('master.kategori-produk.index') }}">
                            <i class="bi bi-tag me-2" style="color:var(--pb-dark);"></i>Kategori Produk
                        </a></li>
                        <li><a class="dropdown-item {{ request()->routeIs('master.produk*') ? 'fw-bold' : '' }}"
                               href="{{ route('master.produk.index') }}">
                            <i class="bi bi-box-seam me-2" style="color:var(--pb-dark);"></i>Master Produk
                        </a></li>
                        
                        @if($isSuperAdmin || $isOwner || $isAdminToko)
                        <li><hr class="dropdown-divider"></li>
                        <li><h6 class="dropdown-header">Relasi Transaksi</h6></li>
                        <li><a class="dropdown-item {{ request()->routeIs('master.pelanggan*') ? 'fw-bold' : '' }}"
                               href="{{ route('master.pelanggan.index') }}">
                            <i class="bi bi-people me-2" style="color:var(--pb-dark);"></i>Pelanggan
                        </a></li>
                        <li><a class="dropdown-item" href="#">
                            <i class="bi bi-truck me-2" style="color:var(--pb-dark);"></i>Supplier
                        </a></li>
                        @endif
                    </ul>
                </li>
                @endif

                @if($isSuperAdmin || $isOwner || $isAdminToko || $isGudang)
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->is('pembelian*') ? 'active' : '' }}" 
                       href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-cart-plus me-1"></i>Pembelian
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item {{ request()->routeIs('pembelian.create') ? 'fw-bold' : '' }}" href="{{ route('pembelian.create') }}">
                            <i class="bi bi-plus-circle me-2 text-success"></i>Input Pembelian
                        </a></li>
                        <li><a class="dropdown-item {{ request()->routeIs('pembelian.index') || request()->routeIs('pembelian.show') ? 'fw-bold' : '' }}" href="{{ route('pembelian.index') }}">
                            <i class="bi bi-clock-history me-2 text-primary"></i>Riwayat Pembelian
                        </a></li>
                    </ul>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->is('inventory*') ? 'active' : '' }}"
                       href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-box-seam me-1"></i>Inventory
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item {{ request()->routeIs('inventory.opname*') ? 'fw-bold' : '' }}" href="{{ route('inventory.opname.index') }}"><i class="bi bi-pencil-square me-2" style="color:var(--pb-dark);"></i>Penyesuaian Stok (Opname)</a></li>
                    </ul>
                </li>
                @endif

                @if($isSuperAdmin || $isOwner || $isAdminToko || $isKasir)
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->is('laporan*') ? 'active' : '' }}"
                       href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-bar-chart me-1"></i>Laporan
                    </a>
                    <ul class="dropdown-menu">
                        @if($isSuperAdmin || $isOwner || $isAdminToko)
                        <li><a class="dropdown-item {{ request()->routeIs('laporan.analytics') ? 'fw-bold' : '' }}" href="{{ route('laporan.analytics') }}"><i class="bi bi-pie-chart me-2 text-warning"></i>Analytics Dashboard</a></li>
                        @endif
                        <li><a class="dropdown-item {{ request()->routeIs('laporan.rekap-penjualan') ? 'fw-bold' : '' }}" href="{{ route('laporan.rekap-penjualan') }}"><i class="bi bi-receipt me-2 text-success"></i>Rekap Penjualan</a></li>
                    </ul>
                </li>
                @endif

                @if($isSuperAdmin || $isOwner || $isAdminToko)
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->is('pengaturan*') ? 'active' : '' }}"
                       href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-gear me-1"></i>Pengaturan
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item {{ request()->routeIs('pengaturan.users*') ? 'fw-bold' : '' }}" href="{{ route('pengaturan.users.index') }}"><i class="bi bi-people me-2" style="color:var(--pb-dark);"></i>Manajemen User</a></li>
                        <li><a class="dropdown-item {{ request()->routeIs('pengaturan.toko*') ? 'fw-bold' : '' }}" href="{{ route('pengaturan.toko.edit') }}"><i class="bi bi-shop me-2" style="color:var(--pb-dark);"></i>Manajemen Toko</a></li>
                        <li><a class="dropdown-item {{ request()->routeIs('pengaturan.ai*') ? 'fw-bold' : '' }}" href="{{ route('pengaturan.ai.index') }}"><i class="bi bi-robot me-2 text-warning"></i>Integrasi AI & Vision</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item {{ request()->routeIs('langganan.status') ? 'fw-bold' : '' }}" href="{{ route('langganan.status') }}"><i class="bi bi-shield-check me-2 text-success"></i>Status Langganan</a></li>
                        @if($isSuperAdmin)
                        <li><a class="dropdown-item" href="{{ route('cms.toko.index') }}"><i class="bi bi-shield-lock-fill me-2 text-warning"></i>CMS SaaS Master</a></li>
                        @endif
                    </ul>
                </li>
                @endif

            </ul>

            <ul class="navbar-nav align-items-lg-center">
                <li class="nav-item me-1">
                    <button class="nav-theme-toggle" id="darkModeToggle" title="Tema Gelap / Terang" aria-label="Toggle Dark Mode">
                        <i class="bi bi-moon-stars" id="darkModeIcon" aria-hidden="true"></i>
                    </button>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center gap-1"
                       href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle" style="font-size:15px;"></i>
                        <span>{{ auth()->user()?->nama_lengkap ?? 'Kasir Utama' }}</span>
                        <span class="badge ms-1" style="background:var(--bg-card-hover);font-size:9px;color:var(--text-secondary);">
                            {{ auth()->user()?->role?->nama_role ?? 'Kasir' }}
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <span class="dropdown-item-text" style="font-size:11px;">
                                {{ auth()->user()?->toko?->nama_toko ?? 'Toko Kelontong Jaya' }}
                            </span>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

{{-- ═══ MAIN CONTENT ═══════════════════════════════════════════════ --}}
<main class="container-fluid py-3" style="max-width:1440px;" id="main-content">

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <i class="bi bi-check-circle-fill me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <i class="bi bi-exclamation-circle-fill me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show mb-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ session('warning') }}
            <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @yield('content')
</main>

@include('partials.confirm-modal')
@include('partials.ai-copilot-widget')
@include('partials.voice-transaction-modal')

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function(){
    const html = document.documentElement;
    const DM_KEY = 'erp_theme';
    const toggleBtn = document.getElementById('darkModeToggle');
    const toggleIcon = document.getElementById('darkModeIcon');

    function applyTheme(theme) {
        if (theme === 'dark') {
            html.setAttribute('data-theme', 'dark');
            toggleIcon.className = 'bi bi-sun';
        } else {
            html.removeAttribute('data-theme');
            toggleIcon.className = 'bi bi-moon-stars';
        }
    }

    function getPreferredTheme() {
        var stored = localStorage.getItem(DM_KEY);
        if (stored === 'dark' || stored === 'light') return stored;
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }

    function toggleTheme() {
        var current = html.hasAttribute('data-theme') ? 'dark' : 'light';
        var next = current === 'dark' ? 'light' : 'dark';
        localStorage.setItem(DM_KEY, next);
        applyTheme(next);
    }

    applyTheme(getPreferredTheme());
    toggleBtn.addEventListener('click', toggleTheme);

    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
        if (!localStorage.getItem(DM_KEY)) {
            applyTheme(e.matches ? 'dark' : 'light');
        }
    });

    /* ═══════════ ACCESSIBLE CONFIRM MODAL ═══════════ */
    (function() {
        var forms = document.querySelectorAll('form[onsubmit*="confirm"]');
        forms.forEach(function(form) {
            var match = form.getAttribute('onsubmit').match(/confirm\('([^']+)'\)/);
            if (!match) return;
            var message = match[1];
            form.removeAttribute('onsubmit');
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                var modalEl = document.getElementById('confirmDeleteModal');
                var modal = new bootstrap.Modal(modalEl);
                document.getElementById('confirmDeleteMessage').textContent = message;
                document.getElementById('confirmDeleteBtn').onclick = function() {
                    modal.hide();
                    form.submit();
                };
                modal.show();
            });
        });
    })();

    /* ═══════════ SUBMIT BUTTON SPINNER ═══════════ */
    document.querySelectorAll('form').forEach(function(form) {
        var submitBtn = form.querySelector('button[type="submit"]');
        if (!submitBtn) return;
        form.addEventListener('submit', function() {
            if (form.classList.contains('submitting')) return;
            form.classList.add('submitting');
            submitBtn.disabled = true;
            var original = submitBtn.innerHTML;
            submitBtn.setAttribute('data-original-text', original);
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Memproses...';
            form.addEventListener('ajax-error', function() {
                form.classList.remove('submitting');
                submitBtn.disabled = false;
                submitBtn.innerHTML = submitBtn.getAttribute('data-original-text');
            }, { once: true });
        });
    });
})();
</script>
@stack('scripts')
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => navigator.serviceWorker.register('{{ asset('service-worker.js') }}'));
    }
</script>
</body>
</html>
