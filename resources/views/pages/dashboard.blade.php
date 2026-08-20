@extends('layouts.enterprise')
@section('title', 'Dashboard — ' . (auth()->user()?->toko?->nama_toko ?? 'ERPlay AI'))

@push('styles')
<style>
    /* ── 1-Viewport Dashboard Layout (Desktop Viewport Fit) ── */
    @media (min-width: 992px) and (min-height: 550px) {
        .erp-content {
            padding-top: 16px !important;
            padding-bottom: 16px !important;
        }
        .dashboard-viewport-container {
            min-height: calc(100vh - var(--topbar-h) - 32px);
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            gap: 10px;
        }
        .dashboard-main-row {
            flex: 1;
            min-height: 0; /* Critical for inner flex scaling */
        }
        .dashboard-flex-card {
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .dashboard-chart-body {
            flex: 1;
            min-height: 0;
            position: relative;
        }
    }

    /* ── Stat Card System ── */
    .dashboard-stat-card {
        background: var(--bg-card);
        border-radius: 12px;
        padding: 10px 14px;
        border: 1px solid var(--border-light);
        transition: border-color 0.15s ease, box-shadow 0.15s ease, var(--theme-transition);
        height: 100%;
        display: flex;
        align-items: center;
    }
    .dashboard-stat-card:hover {
        border-color: var(--pb-mid);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
    }
    .dashboard-stat-card-link {
        text-decoration: none;
        display: block;
        height: 100%;
        color: inherit;
        border-radius: 12px;
    }
    .dashboard-stat-card-link:focus-visible .dashboard-stat-card {
        border-color: var(--pb-accent);
        box-shadow: 0 0 0 2px var(--pb-accent);
    }
    .stat-icon {
        width: 38px; height: 38px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .stat-icon i { font-size: 18px; }

    /* Stat Icon Color Palette */
    .stat-icon-teal { background: rgba(13, 78, 86, 0.1); color: var(--pb-dark); }
    .stat-icon-green { background: rgba(27, 138, 107, 0.1); color: #15803D; }
    .stat-icon-amber { background: rgba(217, 119, 6, 0.1); color: #B46B18; }
    .stat-icon-red { background: rgba(220, 38, 38, 0.1); color: #B91C1C; }
    .stat-icon-blue { background: rgba(37, 99, 235, 0.1); color: #1D4ED8; }

    [data-theme="dark"] .stat-icon-teal { background: rgba(77, 184, 196, 0.15); color: #4DB8C4; }
    [data-theme="dark"] .stat-icon-green { background: rgba(52, 211, 153, 0.15); color: #34D399; }
    [data-theme="dark"] .stat-icon-amber { background: rgba(251, 191, 36, 0.15); color: #FBBF24; }
    [data-theme="dark"] .stat-icon-red { background: rgba(248, 113, 113, 0.15); color: #F87171; }
    [data-theme="dark"] .stat-icon-blue { background: rgba(96, 165, 250, 0.15); color: #60A5FA; }

    .stat-label {
        font-size: 11px;
        font-weight: 600;
        color: var(--text-secondary);
        margin-bottom: 1px;
        white-space: nowrap;
    }
    .stat-value {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 18px;
        font-weight: 800;
        color: var(--pb-text);
        line-height: 1.15;
        letter-spacing: -0.02em;
    }
    .stat-val-warning { color: #B46B18; }
    .stat-val-danger { color: #B91C1C; }
    [data-theme="dark"] .stat-val-warning { color: #FBBF24; }
    [data-theme="dark"] .stat-val-danger { color: #F87171; }

    .stat-subtext {
        font-size: 11px;
        color: var(--text-muted);
        margin-top: 1px;
        white-space: nowrap;
    }

    /* Master Data Strip inside Chart Footer */
    .master-data-strip {
        background: rgba(var(--glass-bg), 0.3);
        border-top: 1px solid var(--border-light);
        padding: 8px 14px;
        border-radius: 0 0 12px 12px;
    }
    .master-data-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .master-data-val {
        font-size: 13px;
        font-weight: 800;
        color: var(--pb-text);
        line-height: 1.1;
    }
    .master-data-lbl {
        font-size: 10px;
        color: var(--text-secondary);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }
    .master-data-trend {
        font-size: 10px;
        font-weight: 700;
        color: var(--pb-accent);
        margin-left: 2px;
    }

    /* Quick Action Cards */
    .quick-action-card {
        background: var(--bg-card);
        border: 1px solid var(--border-light);
        border-radius: 10px;
        padding: 10px 12px;
        display: flex; align-items: center; gap: 10px;
        transition: border-color 0.15s ease, box-shadow 0.15s ease, var(--theme-transition);
        text-decoration: none;
        color: inherit;
        flex: 1;
    }
    .quick-action-card:hover {
        border-color: var(--pb-mid);
        box-shadow: 0 4px 12px rgba(0,0,0,0.04);
        color: inherit;
    }
    .quick-action-icon {
        width: 36px; height: 36px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .quick-action-title {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 13px; font-weight: 700;
        color: var(--pb-text);
        line-height: 1.2;
    }
    .quick-action-desc {
        font-size: 11px; color: var(--text-secondary); margin-top: 1px;
    }
</style>
@endpush

@section('content')
<div class="dashboard-viewport-container">

    <!-- Header & Welcome Banner (Condensed 1-Row Bar) -->
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 py-1">
        <div class="d-flex align-items-center gap-3">
            <div>
                <div class="d-flex align-items-center gap-2">
                    <h1 class="h5 mb-0 fw-bold" style="font-family:'Plus Jakarta Sans',sans-serif;color:var(--pb-text);letter-spacing:-0.02em;">
                        Selamat Datang, {{ auth()->user()?->toko?->nama_toko ?? 'ERPlay AI' }}
                    </h1>
                    <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size:10px;font-weight:600;">
                        <i class="bi bi-circle-fill me-1" style="font-size:6px;vertical-align:middle;"></i>Sistem Online
                    </span>
                </div>
                <p class="mb-0 text-muted" style="font-size:12px;">
                    {{ auth()->user()?->toko?->slogan_struk ?? 'Ringkasan operasional dan performa bisnis Anda hari ini.' }}
                </p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <nav class="erp-breadcrumb d-none d-lg-block mb-0 me-2" aria-label="Breadcrumb">
                <a href="{{ url('/dashboard') }}" aria-label="Beranda Dashboard"><i class="bi bi-house-door"></i></a>
                <i class="bi bi-chevron-right mx-1" style="font-size:9px;" aria-hidden="true"></i>
                <span aria-current="page">Dashboard</span>
            </nav>
            <div style="font-size:11px;font-weight:600;color:var(--text-secondary);background:var(--bg-card);padding:5px 10px;border-radius:8px;border:1px solid var(--border-light);">
                <i class="bi bi-calendar3 me-1 text-primary" aria-hidden="true"></i>{{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM Y') }}
            </div>
        </div>
    </div>

    <!-- Top KPI Cards Row (4 Operational & Sales Metrics) -->
    <div class="row g-2">
        <!-- Today's Sales -->
        <div class="col-6 col-lg-3">
            <div class="dashboard-stat-card">
                <div class="d-flex align-items-center gap-3 w-100">
                    <div class="stat-icon stat-icon-teal" aria-hidden="true">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                    <div class="overflow-hidden">
                        <div class="stat-label">Penjualan Hari Ini</div>
                        <div class="stat-value text-truncate">Rp {{ number_format($todaySummary['total'] ?? 0, 0, ',', '.') }}</div>
                        <div class="stat-subtext">{{ $todaySummary['transaksi'] ?? 0 }} transaksi selesai</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Average Transaction Value -->
        <div class="col-6 col-lg-3">
            <div class="dashboard-stat-card">
                <div class="d-flex align-items-center gap-3 w-100">
                    <div class="stat-icon stat-icon-blue" aria-hidden="true">
                        <i class="bi bi-receipt"></i>
                    </div>
                    <div class="overflow-hidden">
                        <div class="stat-label">Rata-rata Transaksi</div>
                        <div class="stat-value text-truncate">
                            Rp {{ number_format(($todaySummary['transaksi'] ?? 0) > 0 ? ($todaySummary['total'] / $todaySummary['transaksi']) : 0, 0, ',', '.') }}
                        </div>
                        <div class="stat-subtext">per transaksi hari ini</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Low Stock Alert -->
        <div class="col-6 col-lg-3">
            <a href="{{ route('master.produk.index', ['stok_rendah' => 1]) }}" class="dashboard-stat-card-link" aria-label="Lihat produk stok menipis: {{ $lowStockCount ?? 0 }} produk">
                <div class="dashboard-stat-card">
                    <div class="d-flex align-items-center gap-3 w-100">
                        <div class="stat-icon stat-icon-amber" aria-hidden="true">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                        <div class="overflow-hidden">
                            <div class="stat-label">Stok Menipis</div>
                            <div class="stat-value stat-val-warning">{{ $lowStockCount ?? 0 }} <span style="font-size:11px;font-weight:600;color:var(--text-secondary);">Produk</span></div>
                            <div class="stat-subtext">perlu restock segera</div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Out of Stock Alert -->
        <div class="col-6 col-lg-3">
            <a href="{{ route('master.produk.index', ['stok_habis' => 1]) }}" class="dashboard-stat-card-link" aria-label="Lihat produk stok habis: {{ $outOfStockCount ?? 0 }} produk">
                <div class="dashboard-stat-card">
                    <div class="d-flex align-items-center gap-3 w-100">
                        <div class="stat-icon stat-icon-red" aria-hidden="true">
                            <i class="bi bi-x-circle"></i>
                        </div>
                        <div class="overflow-hidden">
                            <div class="stat-label">Stok Habis</div>
                            <div class="stat-value stat-val-danger">{{ $outOfStockCount ?? 0 }} <span style="font-size:11px;font-weight:600;color:var(--text-secondary);">Produk</span></div>
                            <div class="stat-subtext">segera isi ulang produk</div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Main Section (Flex-1) : Sales Chart (8 Cols) + Quick Navigation & Summary (4 Cols) -->
    <div class="row g-2 dashboard-main-row">
        <!-- Sales Chart with Integrated Master Data Strip -->
        <div class="col-12 col-lg-8 h-100">
            <div class="card card-erp dashboard-flex-card shadow-sm">
                <div class="card-header d-flex align-items-center justify-content-between py-2 px-3">
                    <h2 class="h6 card-title mb-0" style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;">
                        <i class="bi bi-graph-up text-primary me-2" aria-hidden="true"></i>Grafik Tren Penjualan {{ now()->year }}
                    </h2>
                    <span style="font-size:11px;color:var(--text-muted);">
                        <i class="bi bi-info-circle me-1" aria-hidden="true"></i>Akumulasi Bulanan (Rp)
                    </span>
                </div>
                <div class="card-body p-3 dashboard-chart-body">
                    <canvas id="salesChart" role="img" aria-label="Grafik tren penjualan bulanan tahun {{ now()->year }}">
                        <p class="visually-hidden">Grafik tren penjualan bulanan toko tahun {{ now()->year }}.</p>
                    </canvas>
                </div>
                <!-- Master Data Summary Strip inside Card Footer -->
                <div class="master-data-strip">
                    <div class="row g-2 align-items-center">
                        <div class="col-6 col-md-3">
                            <div class="master-data-item">
                                <div class="stat-icon stat-icon-teal" style="width:28px;height:28px;border-radius:8px;">
                                    <i class="bi bi-box-seam" style="font-size:13px;"></i>
                                </div>
                                <div class="overflow-hidden">
                                    <div class="master-data-lbl">Total Produk</div>
                                    <div class="master-data-val">
                                        {{ number_format($counts['produk'] ?? 0, 0, ',', '.') }}
                                        @if(($trends['produk'] ?? 0) > 0)
                                        <span class="master-data-trend"><i class="bi bi-arrow-up-short"></i>+{{ $trends['produk'] }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <div class="master-data-item">
                                <div class="stat-icon stat-icon-green" style="width:28px;height:28px;border-radius:8px;">
                                    <i class="bi bi-people" style="font-size:13px;"></i>
                                </div>
                                <div class="overflow-hidden">
                                    <div class="master-data-lbl">Pelanggan</div>
                                    <div class="master-data-val">
                                        {{ number_format($counts['pelanggan'] ?? 0, 0, ',', '.') }}
                                        @if(($trends['pelanggan'] ?? 0) > 0)
                                        <span class="master-data-trend"><i class="bi bi-arrow-up-short"></i>+{{ $trends['pelanggan'] }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <div class="master-data-item">
                                <div class="stat-icon stat-icon-amber" style="width:28px;height:28px;border-radius:8px;">
                                    <i class="bi bi-collection" style="font-size:13px;"></i>
                                </div>
                                <div class="overflow-hidden">
                                    <div class="master-data-lbl">Kelompok</div>
                                    <div class="master-data-val">
                                        {{ number_format($counts['kelompok'] ?? 0, 0, ',', '.') }}
                                        @if(($trends['kelompok'] ?? 0) > 0)
                                        <span class="master-data-trend"><i class="bi bi-arrow-up-short"></i>+{{ $trends['kelompok'] }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <div class="master-data-item">
                                <div class="stat-icon stat-icon-red" style="width:28px;height:28px;border-radius:8px;">
                                    <i class="bi bi-tag" style="font-size:13px;"></i>
                                </div>
                                <div class="overflow-hidden">
                                    <div class="master-data-lbl">Kategori</div>
                                    <div class="master-data-val">
                                        {{ number_format($counts['kategori'] ?? 0, 0, ',', '.') }}
                                        @if(($trends['kategori'] ?? 0) > 0)
                                        <span class="master-data-trend"><i class="bi bi-arrow-up-short"></i>+{{ $trends['kategori'] }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Access Panel (4 Cols) -->
        <div class="col-12 col-lg-4 h-100">
            <div class="card card-erp dashboard-flex-card shadow-sm">
                <div class="card-header py-2 px-3">
                    <h2 class="h6 card-title mb-0" style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;">
                        <i class="bi bi-lightning-charge text-warning me-2" aria-hidden="true"></i>Akses Cepat Sistem
                    </h2>
                </div>
                <div class="card-body p-2 d-flex flex-column gap-2 overflow-hidden">
                    <a href="{{ route('master.produk.index') }}" class="quick-action-card">
                        <div class="quick-action-icon stat-icon-teal" aria-hidden="true">
                            <i class="bi bi-box-seam"></i>
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="quick-action-title">Data Master Produk</div>
                            <div class="quick-action-desc">Katalog, varian stok, dan harga</div>
                        </div>
                        <i class="bi bi-arrow-right-short" style="color:var(--pb-mid);font-size:20px;" aria-hidden="true"></i>
                    </a>

                    <a href="{{ url('/pos/standard') }}" class="quick-action-card" target="_blank" rel="noopener noreferrer">
                        <div class="quick-action-icon stat-icon-green" aria-hidden="true">
                            <i class="bi bi-grid-3x3-gap-fill"></i>
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="quick-action-title">Kasir POS Standar</div>
                            <div class="quick-action-desc">Galeri visual & pencarian produk</div>
                        </div>
                        <i class="bi bi-arrow-right-short" style="color:var(--pb-mid);font-size:20px;" aria-hidden="true"></i>
                    </a>

                    <a href="{{ url('/pos/custom') }}" class="quick-action-card" target="_blank" rel="noopener noreferrer">
                        <div class="quick-action-icon stat-icon-amber" aria-hidden="true">
                            <i class="bi bi-lightning-charge"></i>
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="quick-action-title">Kasir POS Kilat</div>
                            <div class="quick-action-desc">Scan barcode & checkout ultra-cepat</div>
                        </div>
                        <i class="bi bi-arrow-right-short" style="color:var(--pb-mid);font-size:20px;" aria-hidden="true"></i>
                    </a>

                    <a href="{{ route('laporan.rekap-penjualan') }}" class="quick-action-card">
                        <div class="quick-action-icon" style="background:rgba(139,92,246,.13);" aria-hidden="true">
                            <i class="bi bi-clock-history" style="color:#8b5cf6;"></i>
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="quick-action-title">Riwayat Penjualan</div>
                            <div class="quick-action-desc">Rekap & detail transaksi toko</div>
                        </div>
                        <i class="bi bi-arrow-right-short" style="color:var(--pb-mid);font-size:20px;" aria-hidden="true"></i>
                    </a>

                    <!-- Status Highlight Card -->
                    <div class="p-2.5 mt-auto rounded-3 border" style="background:var(--bg-input);border-color:var(--border-light) !important;">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span style="font-size:11px;font-weight:700;color:var(--pb-text);" class="text-uppercase tracking-wider">Status Inventori</span>
                            <span class="badge {{ ($outOfStockCount ?? 0) > 0 ? 'bg-danger-subtle text-danger border border-danger-subtle' : (($lowStockCount ?? 0) > 0 ? 'bg-warning-subtle text-warning-emphasis border border-warning-subtle' : 'bg-success-subtle text-success border border-success-subtle') }}" style="font-size:10px;">
                                {{ ($outOfStockCount ?? 0) > 0 ? 'Kritis' : (($lowStockCount ?? 0) > 0 ? 'Perhatian' : 'Optimal') }}
                            </span>
                        </div>
                        <div class="progress mb-1.5" style="height: 6px; background: rgba(0,0,0,0.06);">
                            @php
                                $totalProd = max($counts['produk'] ?? 1, 1);
                                $normalStockPct = max(0, min(100, 100 - ((($lowStockCount ?? 0) + ($outOfStockCount ?? 0)) / $totalProd * 100)));
                            @endphp
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $normalStockPct }}%" aria-valuenow="{{ $normalStockPct }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center" style="font-size:10px;color:var(--text-secondary);">
                            <span>Tersedia: {{ max(0, ($counts['produk'] ?? 0) - (($lowStockCount ?? 0) + ($outOfStockCount ?? 0))) }} item</span>
                            <span>Total: {{ $counts['produk'] ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js" defer></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var salesData = @json($salesChart);
    var canvasEl = document.getElementById('salesChart');
    if (!canvasEl) return;
    var ctx = canvasEl.getContext('2d');

    function getThemeColors() {
        var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        return {
            grid: isDark ? 'rgba(148,163,184,0.12)' : 'rgba(100,116,139,0.1)',
            text: isDark ? '#94A3B8' : '#64748B',
            line: isDark ? '#4DB8C4' : '#0D4E56',
            fill: isDark ? 'rgba(77,184,196,0.08)' : 'rgba(13,78,86,0.06)'
        };
    }

    var colors = getThemeColors();

    var chartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: salesData.labels,
            datasets: [{
                label: 'Total Penjualan (Rp)',
                data: salesData.values,
                borderColor: colors.line,
                backgroundColor: colors.fill,
                borderWidth: 2.5,
                fill: true,
                tension: 0.35,
                pointRadius: 3,
                pointHoverRadius: 6,
                pointBackgroundColor: colors.line,
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { intersect: false, mode: 'index' },
            plugins: {
                legend: { display: false },
                tooltip: {
                    padding: 10,
                    callbacks: {
                        label: function(ctx) {
                            return 'Penjualan: Rp ' + new Intl.NumberFormat('id-ID').format(ctx.parsed.y);
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { color: colors.grid, drawBorder: false },
                    ticks: { color: colors.text, font: { size: 10, family: 'Plus Jakarta Sans' } }
                },
                y: {
                    grid: { color: colors.grid, drawBorder: false },
                    ticks: {
                        color: colors.text,
                        font: { size: 10, family: 'Plus Jakarta Sans' },
                        callback: function(v) {
                            if (v >= 1000000) return (v / 1000000).toFixed(1) + 'M';
                            if (v >= 1000) return (v / 1000).toFixed(0) + 'K';
                            return v;
                        }
                    },
                    beginAtZero: true
                }
            }
        }
    });

    // Handle theme toggle dynamic chart update
    var observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.attributeName === 'data-theme') {
                var newColors = getThemeColors();
                chartInstance.data.datasets[0].borderColor = newColors.line;
                chartInstance.data.datasets[0].backgroundColor = newColors.fill;
                chartInstance.data.datasets[0].pointBackgroundColor = newColors.line;
                chartInstance.options.scales.x.grid.color = newColors.grid;
                chartInstance.options.scales.x.ticks.color = newColors.text;
                chartInstance.options.scales.y.grid.color = newColors.grid;
                chartInstance.options.scales.y.ticks.color = newColors.text;
                chartInstance.update();
            }
        });
    });
    observer.observe(document.documentElement, { attributes: true });
});
</script>
@endpush
