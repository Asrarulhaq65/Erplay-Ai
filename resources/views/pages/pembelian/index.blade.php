@extends('layouts.enterprise')
@section('title', 'Riwayat Pembelian Barang — ' . (auth()->user()?->toko?->nama_toko ?? 'ERPlay AI'))

@section('content')
<!-- Breadcrumb Navigation -->
<nav class="erp-breadcrumb d-none d-md-block" aria-label="Breadcrumb">
    <a href="{{ url('/dashboard') }}" aria-label="Beranda Dashboard"><i class="bi bi-house-door"></i></a>
    <i class="bi bi-chevron-right mx-1" style="font-size:9px;" aria-hidden="true"></i>
    <span aria-current="page">Riwayat Pembelian</span>
</nav>

<!-- Page Header -->
<div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-2">
    <div>
        <h1 class="h4 mb-0 fw-bold" style="font-family:'Plus Jakarta Sans',sans-serif;color:var(--pb-text);letter-spacing:-0.02em;">
            <i class="bi bi-clock-history me-2" aria-hidden="true"></i>Riwayat Pembelian & Kulakan
        </h1>
        <p class="mb-0 mt-1" style="color:var(--text-secondary);font-size:13px;">
            Daftar riwayat transaksi barang masuk dari supplier beserta faktur pendukung.
        </p>
    </div>
    <div>
        <a href="{{ route('pembelian.create') }}" class="btn btn-sm btn-pb">
            <i class="bi bi-plus-circle me-1" aria-hidden="true"></i>Input Pembelian Baru
        </a>
    </div>
</div>

<!-- Filter Card -->
<div class="card card-erp mb-4">
    <div class="card-body p-3">
        <form action="{{ route('pembelian.index') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <label for="input-search" class="form-label mb-1 fw-semibold" style="font-size:12px;">Pencarian</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search" aria-hidden="true"></i></span>
                    <input type="text" id="input-search" name="search" class="form-control" placeholder="No. Faktur / Supplier..." value="{{ request('search') }}" aria-label="Cari Faktur / Supplier">
                </div>
            </div>
            <div class="col-6 col-md-3">
                <label for="start_date" class="form-label mb-1 fw-semibold" style="font-size:12px;">Dari Tanggal</label>
                <input type="date" id="start_date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}" aria-label="Dari Tanggal">
            </div>
            <div class="col-6 col-md-3">
                <label for="end_date" class="form-label mb-1 fw-semibold" style="font-size:12px;">Sampai Tanggal</label>
                <input type="date" id="end_date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}" aria-label="Sampai Tanggal">
            </div>
            <div class="col-12 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-outline-secondary w-100"><i class="bi bi-filter me-1" aria-hidden="true"></i>Filter</button>
                @if(request('search') || request('start_date') || request('end_date'))
                <a href="{{ route('pembelian.index') }}" class="btn btn-sm btn-outline-danger w-100"><i class="bi bi-x-circle me-1" aria-hidden="true"></i>Reset</a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- History Table Card -->
<div class="card card-erp mb-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:13px;">
            <thead>
                <tr>
                    <th style="width:50px;" class="text-center py-2 px-3" scope="col">No</th>
                    <th style="width:130px;" class="py-2" scope="col">Tanggal</th>
                    <th style="width:160px;" class="py-2" scope="col">No. Faktur</th>
                    <th class="py-2" scope="col">Supplier</th>
                    <th style="width:120px;" class="text-center py-2" scope="col">Total Item</th>
                    <th style="width:160px;" class="text-end py-2" scope="col">Total Pembelian</th>
                    <th style="width:100px;" class="text-center py-2 px-3" scope="col">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pembelians as $index => $trx)
                    <tr>
                        <td class="text-center text-muted px-3">{{ $pembelians->firstItem() + $index }}</td>
                        <td>{{ \Carbon\Carbon::parse($trx->tanggal_beli)->format('d M Y') }}</td>
                        <td>
                            <span class="font-monospace fw-semibold" style="font-size:12px;">
                                <i class="bi bi-receipt me-1" aria-hidden="true"></i>{{ $trx->nomor_faktur }}
                            </span>
                        </td>
                        <td class="fw-semibold">{{ $trx->supplier?->nama_supplier ?? 'Tidak Diketahui' }}</td>
                        <td class="text-center">
                            <span class="badge bg-secondary text-white" style="font-size:11px;">{{ $trx->details_count }} Item</span>
                        </td>
                        <td class="text-end fw-bold" style="color:var(--pb-text);font-size:14px;">
                            Rp {{ number_format($trx->total_pembelian, 0, ',', '.') }}
                        </td>
                        <td class="text-center px-3">
                            <a href="{{ route('pembelian.show', $trx->id) }}" class="btn btn-sm btn-outline-primary py-1 px-2" title="Lihat Rincian Faktur">
                                <i class="bi bi-eye me-1" aria-hidden="true"></i>Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox d-block mb-2" style="font-size:36px;opacity:0.3;" aria-hidden="true"></i>
                            <div style="font-size:13px;font-weight:600;color:var(--text-secondary);">Belum Ada Riwayat Pembelian</div>
                            <div style="font-size:12px;color:var(--text-muted);margin-bottom:12px;">Pencatatan pembelian barang masuk dari supplier akan muncul di sini.</div>
                            <a href="{{ route('pembelian.create') }}" class="btn btn-pb btn-sm px-3">
                                <i class="bi bi-plus-circle me-1" aria-hidden="true"></i>Input Pembelian Baru
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($pembelians->hasPages())
    <div class="card-footer py-2 px-3 bg-transparent border-top">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div style="font-size:12px;color:var(--text-secondary);">
                Menampilkan {{ $pembelians->firstItem() }}–{{ $pembelians->lastItem() }} dari {{ $pembelians->total() }} faktur
            </div>
            <div>
                {{ $pembelians->links('pagination::bootstrap-5', ['class' => 'pagination-sm mb-0']) }}
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
