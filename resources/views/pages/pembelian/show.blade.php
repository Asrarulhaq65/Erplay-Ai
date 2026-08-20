@extends('layouts.enterprise')
@section('title', 'Detail Faktur Pembelian — ' . $pembelian->nomor_faktur . ' — ' . (auth()->user()?->toko?->nama_toko ?? 'ERPlay AI'))

@push('styles')
<style>
    .detail-info-table td {
        padding: 8px 12px;
        font-size: 13px;
    }
    .detail-info-label {
        color: var(--text-secondary);
        font-weight: 500;
        width: 40%;
    }
    .detail-info-val {
        color: var(--pb-text);
        font-weight: 700;
    }

    /* Print Optimization */
    @media print {
        .erp-topbar, .erp-sidebar, .erp-breadcrumb, .btn, .no-print {
            display: none !important;
        }
        body {
            background: #FFFFFF !important;
            color: #000000 !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        .card-erp {
            border: 1px solid #CCCCCC !important;
            box-shadow: none !important;
        }
    }
</style>
@endpush

@section('content')
<!-- Breadcrumb Navigation -->
<nav class="erp-breadcrumb d-none d-md-block" aria-label="Breadcrumb">
    <a href="{{ url('/dashboard') }}" aria-label="Beranda Dashboard"><i class="bi bi-house-door"></i></a>
    <i class="bi bi-chevron-right mx-1" style="font-size:9px;" aria-hidden="true"></i>
    <a href="{{ route('pembelian.index') }}">Riwayat Pembelian</a>
    <i class="bi bi-chevron-right mx-1" style="font-size:9px;" aria-hidden="true"></i>
    <span aria-current="page">Faktur {{ $pembelian->nomor_faktur }}</span>
</nav>

<!-- Page Header & Action Bar -->
<div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-2 no-print">
    <div>
        <h1 class="h4 mb-0 fw-bold" style="font-family:'Plus Jakarta Sans',sans-serif;color:var(--pb-text);letter-spacing:-0.02em;">
            <i class="bi bi-receipt me-2" aria-hidden="true"></i>Detail Faktur Pembelian: {{ $pembelian->nomor_faktur }}
        </h1>
        <p class="mb-0 mt-1" style="color:var(--text-secondary);font-size:13px;">
            Rincian nota/faktur barang masuk dari supplier {{ $pembelian->supplier?->nama_supplier ?? '-' }}.
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('pembelian.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Kembali ke Riwayat
        </a>
        <button class="btn btn-sm btn-pb" onclick="window.print()">
            <i class="bi bi-printer me-1" aria-hidden="true"></i>Cetak Rincian Faktur
        </button>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Meta Info Card -->
    <div class="col-12 col-md-4">
        <div class="card card-erp h-100">
            <div class="card-header py-3">
                <h2 class="h6 card-title mb-0" style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;">
                    <i class="bi bi-info-circle me-2" aria-hidden="true"></i>Informasi Transaksi
                </h2>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-borderless detail-info-table mb-0">
                    <tbody>
                        <tr class="border-bottom">
                            <td class="detail-info-label">No. Faktur</td>
                            <td class="detail-info-val font-monospace">{{ $pembelian->nomor_faktur }}</td>
                        </tr>
                        <tr class="border-bottom">
                            <td class="detail-info-label">Tanggal Beli</td>
                            <td class="detail-info-val">{{ \Carbon\Carbon::parse($pembelian->tanggal_beli)->format('d M Y') }}</td>
                        </tr>
                        <tr class="border-bottom">
                            <td class="detail-info-label">Supplier</td>
                            <td class="detail-info-val">{{ $pembelian->supplier?->nama_supplier ?? '-' }}</td>
                        </tr>
                        <tr class="border-bottom">
                            <td class="detail-info-label">Metode Bayar</td>
                            <td class="detail-info-val">
                                <span class="badge bg-secondary text-white" style="font-size:11px;">{{ $pembelian->metode_pembayaran }}</span>
                            </td>
                        </tr>
                        <tr class="border-bottom">
                            <td class="detail-info-label">Status Bayar</td>
                            <td class="detail-info-val">
                                @if($pembelian->isLunas())
                                    <span class="badge bg-success text-white" style="font-size:11px;"><i class="bi bi-check-circle me-1" aria-hidden="true"></i>Lunas</span>
                                @else
                                    <span class="badge bg-danger text-white" style="font-size:11px;"><i class="bi bi-x-circle me-1" aria-hidden="true"></i>Hutang</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="detail-info-label">Petugas Input</td>
                            <td class="detail-info-val"><i class="bi bi-person me-1 text-secondary" aria-hidden="true"></i>{{ $pembelian->user?->nama_lengkap ?? 'Sistem' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Items Table Card -->
    <div class="col-12 col-md-8">
        <div class="card card-erp h-100">
            <div class="card-header py-3">
                <h2 class="h6 card-title mb-0" style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;">
                    <i class="bi bi-box-seam me-2" aria-hidden="true"></i>Rincian Barang Masuk
                </h2>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle table-hover mb-0" style="font-size:13px;">
                        <thead>
                            <tr>
                                <th class="ps-3 py-2 text-center" style="width:40px;" scope="col">No</th>
                                <th class="py-2" style="width:130px;" scope="col">Barcode</th>
                                <th class="py-2" scope="col">Nama Produk</th>
                                <th class="py-2 text-end" style="width:140px;" scope="col">Harga HPP Satuan</th>
                                <th class="py-2 text-center" style="width:70px;" scope="col">Qty</th>
                                <th class="pe-3 py-2 text-end" style="width:150px;" scope="col">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pembelian->details as $index => $detail)
                                <tr>
                                    <td class="ps-3 text-center text-muted">{{ $index + 1 }}</td>
                                    <td class="font-monospace" style="font-size:12px;font-weight:600;">{{ $detail->produk?->barcode ?? '-' }}</td>
                                    <td class="fw-semibold" style="color:var(--pb-text);">{{ $detail->produk?->nama_produk ?? 'Produk Dihapus' }}</td>
                                    <td class="text-end">Rp {{ number_format($detail->harga_beli_satuan, 0, ',', '.') }}</td>
                                    <td class="text-center fw-bold">{{ $detail->qty }}</td>
                                    <td class="pe-3 text-end fw-bold" style="color:var(--pb-text);">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="border-top">
                            <tr>
                                <td colspan="5" class="text-end fw-bold py-3" style="color:var(--text-secondary);font-size:13px;text-transform:uppercase;letter-spacing:0.04em;">TOTAL PEMBELIAN FAKTUR :</td>
                                <td class="pe-3 text-end fw-bold py-3" style="color:var(--pb-dark);font-size:18px;">
                                    Rp {{ number_format($pembelian->total_pembelian, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
