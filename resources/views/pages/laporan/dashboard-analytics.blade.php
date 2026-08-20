@extends('layouts.enterprise')
@section('title', 'Executive Analytics — ' . (auth()->user()?->toko?->nama_toko ?? 'ERPlay AI'))

@push('styles')
<style>
    /* ── Analytics Metric Cards ── */
    .card-analytics {
        background: var(--bg-card);
        border: 1px solid var(--border-light);
        border-radius: 14px;
        padding: 24px 20px;
        text-align: center;
        transition: border-color 0.15s ease, box-shadow 0.15s ease, var(--theme-transition);
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        box-shadow: var(--shadow-sm);
    }
    .card-analytics:hover {
        border-color: var(--pb-mid);
        box-shadow: var(--shadow-md);
    }

    .analytics-icon-box {
        width: 52px; height: 52px;
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 24px;
        margin: 0 auto 16px;
    }

    .icon-box-teal { background: rgba(13, 78, 86, 0.1); color: var(--pb-dark); }
    .icon-box-green { background: rgba(21, 128, 61, 0.1); color: #15803D; }
    .icon-box-amber { background: rgba(217, 119, 6, 0.1); color: #B46B18; }

    [data-theme="dark"] .icon-box-teal { background: rgba(77, 184, 196, 0.15); color: #4DB8C4; }
    [data-theme="dark"] .icon-box-green { background: rgba(52, 211, 153, 0.15); color: #34D399; }
    [data-theme="dark"] .icon-box-amber { background: rgba(251, 191, 36, 0.15); color: #FBBF24; }

    .analytics-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--text-secondary);
        margin-bottom: 6px;
    }

    .analytics-val {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 26px;
        font-weight: 800;
        color: var(--pb-text);
        line-height: 1.15;
        letter-spacing: -0.03em;
    }
</style>
@endpush

@section('content')
<!-- Breadcrumb Navigation -->
<nav class="erp-breadcrumb d-none d-md-block" aria-label="Breadcrumb">
    <a href="{{ url('/dashboard') }}" aria-label="Beranda Dashboard"><i class="bi bi-house-door"></i></a>
    <i class="bi bi-chevron-right mx-1" style="font-size:9px;" aria-hidden="true"></i>
    <span aria-current="page">Executive Analytics</span>
</nav>

<!-- Page Header & Toolbar -->
<div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
    <div>
        <h1 class="h4 mb-0 fw-bold" style="font-family:'Plus Jakarta Sans',sans-serif;color:var(--pb-text);letter-spacing:-0.02em;">
            <i class="bi bi-pie-chart me-2" aria-hidden="true"></i>Executive Analytics Dashboard
        </h1>
        <p class="mb-0 mt-1" style="color:var(--text-secondary);font-size:13px;">
            Analisis kinerja keuangan, estimasi profitabilitas bersih, dan tren omzet penjualan harian.
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('laporan.analytics.export-csv', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-sm btn-outline-success">
            <i class="bi bi-file-earmark-spreadsheet me-1" aria-hidden="true"></i>Export CSV
        </a>
    </div>
</div>

<!-- Date Filter Form -->
<div class="card card-erp mb-4">
    <div class="card-body py-2 px-3">
        <form action="{{ route('laporan.analytics') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-auto">
                <label class="col-form-label col-form-label-sm fw-semibold">Rentang Tanggal:</label>
            </div>
            <div class="col-auto">
                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}">
            </div>
            <div class="col-auto">
                <span class="text-muted" style="font-size:12px;">s/d</span>
            </div>
            <div class="col-auto">
                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-pb fw-bold">
                    <i class="bi bi-filter me-1"></i>Filter Analisis
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 1. Executive Summary Metric Cards -->
<div class="row g-3 mb-4">
    <!-- Total Revenue -->
    <div class="col-12 col-md-4">
        <div class="card-analytics">
            <div class="analytics-icon-box icon-box-teal" aria-hidden="true">
                <i class="bi bi-wallet2"></i>
            </div>
            <div class="analytics-label">Total Pendapatan (Gross)</div>
            <div class="analytics-val">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
        </div>
    </div>
    
    <!-- Net Profit -->
    <div class="col-12 col-md-4">
        <div class="card-analytics">
            <div class="analytics-icon-box icon-box-green" aria-hidden="true">
                <i class="bi bi-graph-up-arrow"></i>
            </div>
            <div class="analytics-label">Estimasi Laba Bersih (Net Profit)</div>
            <div class="analytics-val" style="color:#15803D;">Rp {{ number_format($netProfit, 0, ',', '.') }}</div>
        </div>
    </div>

    <!-- Total Piutang -->
    <div class="col-12 col-md-4">
        <div class="card-analytics">
            <div class="analytics-icon-box icon-box-amber" aria-hidden="true">
                <i class="bi bi-cash-coin"></i>
            </div>
            <div class="analytics-label">Total Piutang Berjalan (Kredit)</div>
            <div class="analytics-val" style="color:#B46B18;">Rp {{ number_format($totalPiutang, 0, ',', '.') }}</div>
        </div>
    </div>
</div>

<!-- 2. Daily Sales Trend Chart -->
<div class="card card-erp mb-4">
    <div class="card-header py-3">
        <h2 class="h6 card-title mb-0" style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;">
            <i class="bi bi-bar-chart-line me-2 text-primary" aria-hidden="true"></i>Grafik Tren Penjualan Harian
        </h2>
    </div>
    <div class="card-body p-3">
        <canvas id="dailySalesChart" height="90"></canvas>
    </div>
</div>

<!-- 3. Department Performance Table -->
<div class="card card-erp mb-4">
    <div class="card-header py-3">
        <h2 class="h6 card-title mb-0" style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;">
            <i class="bi bi-diagram-3 me-2 text-primary" aria-hidden="true"></i>Performansi Departemen / Kelompok Produk
        </h2>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size:13px;">
                <thead>
                    <tr>
                        <th class="ps-3 py-3" scope="col">Nama Departemen</th>
                        <th class="py-3 text-center" style="width:140px;" scope="col">Total Terjual (Qty)</th>
                        <th class="py-3 text-end" style="width:180px;" scope="col">Total Omzet (Rp)</th>
                        <th class="py-3 text-end" style="width:180px;" scope="col">Estimasi Profit (Rp)</th>
                        <th class="pe-3 py-3 text-center" style="width:160px;" scope="col">Kontribusi Margin</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($performance as $row)
                    @php
                        $marginPct = ($totalRevenue > 0) ? round(($row->total_revenue / $totalRevenue) * 100, 1) : 0;
                    @endphp
                    <tr>
                        <td class="ps-3 fw-bold" style="color:var(--pb-text);">
                            <i class="bi bi-folder2-open me-2 text-secondary" aria-hidden="true"></i>{{ $row->nama_kelompok }}
                        </td>
                        <td class="text-center font-monospace">{{ number_format($row->total_qty) }} Pcs</td>
                        <td class="text-end font-monospace font-semibold">Rp {{ number_format($row->total_revenue, 0, ',', '.') }}</td>
                        <td class="text-end font-monospace text-success font-semibold">Rp {{ number_format($row->total_profit, 0, ',', '.') }}</td>
                        <td class="pe-3 text-center">
                            <div class="d-flex align-items-center justify-content-center gap-2">
                                <div class="progress flex-grow-1" style="height:6px;border-radius:3px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $marginPct }}%;" aria-valuenow="{{ $marginPct }}" aria-valuemin="0" aria-valuemax="100" aria-label="Kontribusi Margin {{ $marginPct }}%"></div>
                                </div>
                                <span class="font-monospace text-secondary" style="font-size:11px;">{{ $marginPct }}%</span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">Belum ada data penjualan pada rentang tanggal ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('dailySalesChart');
        if (!ctx) return;

        const labels = @json($dailySales['labels']);
        const omzet  = @json($dailySales['omzet']);

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Omzet Penjualan (Rp)',
                    data: omzet,
                    borderColor: '#0D4E56',
                    backgroundColor: 'rgba(13, 78, 86, 0.08)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    pointBackgroundColor: '#0D4E56',
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endpush
