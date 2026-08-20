@extends('layouts.enterprise')
@section('title', 'Laporan Laba Rugi — ' . (auth()->user()?->toko?->nama_toko ?? 'ERPlay AI'))

@section('content')
<!-- Breadcrumb Navigation -->
<nav class="erp-breadcrumb d-none d-md-block" aria-label="Breadcrumb">
    <a href="{{ url('/dashboard') }}" aria-label="Beranda Dashboard"><i class="bi bi-house-door"></i></a>
    <i class="bi bi-chevron-right mx-1" style="font-size:9px;" aria-hidden="true"></i>
    <span>Akuntansi</span>
    <i class="bi bi-chevron-right mx-1" style="font-size:9px;" aria-hidden="true"></i>
    <span aria-current="page">Laporan Laba Rugi</span>
</nav>

<!-- Page Header & Action Bar -->
<div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
    <div>
        <h1 class="h4 mb-0 fw-bold" style="font-family:'Plus Jakarta Sans',sans-serif;color:var(--pb-text);letter-spacing:-0.02em;">
            <i class="bi bi-pie-chart-fill me-2" aria-hidden="true"></i>Laporan Laba Rugi (Income Statement)
        </h1>
        <p class="mb-0 mt-1" style="color:var(--text-secondary);font-size:13px;">
            Ringkasan pendapatan, harga pokok penjualan (HPP), beban operasional, dan kalkulasi laba bersih toko.
        </p>
    </div>
</div>

<!-- Date Filter Form -->
<div class="card card-erp mb-4">
    <div class="card-body py-2 px-3">
        <form action="{{ route('akuntansi.laba-rugi') }}" method="GET" class="row g-2 align-items-center">
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
                <button type="submit" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-filter me-1"></i>Filter
                </button>
            </div>
        </form>
    </div>
</div>

<div class="row g-4">
    <!-- Main Statement Panel -->
    <div class="col-12 col-lg-8">
        <div class="card card-erp mb-4">
            <div class="card-header py-3">
                <h2 class="h6 card-title mb-0 fw-bold" style="font-family:'Plus Jakarta Sans',sans-serif;">
                    Laporan Laba Rugi Toko Periode {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} – {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
                </h2>
            </div>
            <div class="card-body p-0">
                <table class="table table-borderless align-middle mb-0" style="font-size:13px;">
                    <tbody>
                        <!-- PENDAPATAN -->
                        <tr class="table-light">
                            <td colspan="2" class="fw-bold text-success ps-3">I. PENDAPATAN OPERASIONAL</td>
                        </tr>
                        @foreach($pendapatanAccounts as $acc)
                        <tr>
                            <td class="ps-4">[{{ $acc->kode_akun }}] {{ $acc->nama_akun }}</td>
                            <td class="pe-3 text-end font-monospace">Rp {{ number_format($acc->total, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                        <tr class="border-top fw-bold">
                            <td class="ps-3 text-uppercase">Total Pendapatan (A)</td>
                            <td class="pe-3 text-end font-monospace text-success">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</td>
                        </tr>

                        <tr><td colspan="2" class="py-1"></td></tr>

                        <!-- BEBAN & HPP -->
                        <tr class="table-light">
                            <td colspan="2" class="fw-bold text-danger ps-3">II. HARGA POKOK & BEBAN OPERASIONAL</td>
                        </tr>
                        @foreach($bebanAccounts as $acc)
                        <tr>
                            <td class="ps-4">[{{ $acc->kode_akun }}] {{ $acc->nama_akun }}</td>
                            <td class="pe-3 text-end font-monospace text-danger">Rp {{ number_format($acc->total, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                        <tr class="border-top fw-bold">
                            <td class="ps-3 text-uppercase">Total Beban & HPP (B)</td>
                            <td class="pe-3 text-end font-monospace text-danger">Rp {{ number_format($totalBeban, 0, ',', '.') }}</td>
                        </tr>

                        <!-- LABA BERSIH -->
                        <tr class="table-dark fw-bold" style="font-size:14px;">
                            <td class="ps-3 py-3">LABA BERSIH PERIODE (A - B)</td>
                            <td class="pe-3 py-3 text-end font-monospace {{ $labaBersih >= 0 ? 'text-success' : 'text-danger' }}">
                                Rp {{ number_format($labaBersih, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right Summary Card -->
    <div class="col-12 col-lg-4">
        <div class="card card-erp p-4 text-center">
            <div class="text-secondary fw-semibold mb-1" style="font-size:12px;">ESTIMASI LABA BERSIH</div>
            <h3 class="fw-bold font-monospace {{ $labaBersih >= 0 ? 'text-success' : 'text-danger' }} mb-2" style="font-size:2rem;">
                Rp {{ number_format($labaBersih, 0, ',', '.') }}
            </h3>
            <p class="text-muted mb-0" style="font-size:12px;">
                Dihitung dari selisih total pendapatan bersih dikurangi HPP dan beban operasional toko selama periode yang dipilih.
            </p>
        </div>
    </div>
</div>
@endsection
