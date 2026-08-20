@extends('layouts.enterprise')
@section('title', 'Buku Besar — ' . (auth()->user()?->toko?->nama_toko ?? 'ERPlay AI'))

@section('content')
<!-- Breadcrumb Navigation -->
<nav class="erp-breadcrumb d-none d-md-block" aria-label="Breadcrumb">
    <a href="{{ url('/dashboard') }}" aria-label="Beranda Dashboard"><i class="bi bi-house-door"></i></a>
    <i class="bi bi-chevron-right mx-1" style="font-size:9px;" aria-hidden="true"></i>
    <span>Akuntansi</span>
    <i class="bi bi-chevron-right mx-1" style="font-size:9px;" aria-hidden="true"></i>
    <span aria-current="page">Buku Besar</span>
</nav>

<!-- Page Header & Action Bar -->
<div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
    <div>
        <h1 class="h4 mb-0 fw-bold" style="font-family:'Plus Jakarta Sans',sans-serif;color:var(--pb-text);letter-spacing:-0.02em;">
            <i class="bi bi-book-half me-2" aria-hidden="true"></i>Buku Besar (General Ledger)
        </h1>
        <p class="mb-0 mt-1" style="color:var(--text-secondary);font-size:13px;">
            Rincian mutasi debit, kredit, dan saldo akhir spesifik per akun perkiraan.
        </p>
    </div>
</div>

<!-- Filter Akun & Tanggal -->
<div class="card card-erp mb-4">
    <div class="card-body py-3 px-3">
        <form action="{{ route('akuntansi.buku-besar') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-12 col-md-5">
                <label for="akun_id" class="form-label fw-semibold mb-1" style="font-size:12px;">Pilih Akun Perkiraan:</label>
                <select name="akun_id" id="akun_id" class="form-select form-select-sm">
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}" {{ ($selectedAkun?->id == $acc->id) ? 'selected' : '' }}>
                            [{{ $acc->kode_akun }}] {{ $acc->nama_akun }} ({{ $acc->tipe_akun }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-5">
                <label class="form-label fw-semibold mb-1" style="font-size:12px;">Rentang Tanggal:</label>
                <div class="d-flex align-items-center gap-2">
                    <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}">
                    <span class="text-muted" style="font-size:12px;">s/d</span>
                    <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}">
                </div>
            </div>
            <div class="col-12 col-md-2">
                <button type="submit" class="btn btn-sm btn-pb w-100 fw-bold">
                    <i class="bi bi-search me-1"></i>Tampilkan
                </button>
            </div>
        </form>
    </div>
</div>

@if($selectedAkun)
<div class="card card-erp mb-4">
    <div class="card-header py-3 d-flex align-items-center justify-content-between">
        <h2 class="h6 card-title mb-0" style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;">
            [{{ $selectedAkun->kode_akun }}] {{ $selectedAkun->nama_akun }}
        </h2>
        <span class="badge bg-light text-dark border">Klasifikasi: {{ $selectedAkun->tipe_akun }} | Saldo Normal: {{ $selectedAkun->saldo_normal }}</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size:13px;">
                <thead>
                    <tr>
                        <th class="ps-3 py-3" style="width:120px;" scope="col">Tanggal</th>
                        <th class="py-3" style="width:140px;" scope="col">No. Jurnal</th>
                        <th class="py-3" scope="col">Keterangan / Memo</th>
                        <th class="py-3 text-end" style="width:140px;" scope="col">Debit (Rp)</th>
                        <th class="py-3 text-end" style="width:140px;" scope="col">Kredit (Rp)</th>
                        <th class="pe-3 py-3 text-end" style="width:160px;" scope="col">Kalkulasi Saldo (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $runningBalance = $selectedAkun->saldo_awal;
                    @endphp
                    <tr class="bg-light">
                        <td class="ps-3 fw-semibold text-muted" colspan="3">SALDO AWAL PERIODE</td>
                        <td class="text-end font-monospace">-</td>
                        <td class="text-end font-monospace">-</td>
                        <td class="pe-3 text-end font-monospace fw-bold">Rp {{ number_format($runningBalance, 0, ',', '.') }}</td>
                    </tr>

                    @forelse($mutasi as $row)
                        @php
                            if ($selectedAkun->saldo_normal === 'Debit') {
                                $runningBalance += ($row->debit - $row->kredit);
                            } else {
                                $runningBalance += ($row->kredit - $row->debit);
                            }
                        @endphp
                        <tr>
                            <td class="ps-3">{{ \Carbon\Carbon::parse($row->jurnal->tanggal)->format('d/m/Y') }}</td>
                            <td class="font-monospace fw-semibold" style="color:var(--pb-dark);">{{ $row->jurnal->nomor_jurnal }}</td>
                            <td>{{ $row->memo ?: $row->jurnal->keterangan }}</td>
                            <td class="text-end font-monospace text-success">{{ $row->debit > 0 ? number_format($row->debit, 0, ',', '.') : '-' }}</td>
                            <td class="text-end font-monospace text-danger">{{ $row->kredit > 0 ? number_format($row->kredit, 0, ',', '.') : '-' }}</td>
                            <td class="pe-3 text-end font-monospace fw-bold">Rp {{ number_format($runningBalance, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Tidak ada mutasi transaksi pada rentang tanggal ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection
