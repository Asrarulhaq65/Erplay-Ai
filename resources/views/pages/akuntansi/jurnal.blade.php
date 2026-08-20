@extends('layouts.enterprise')
@section('title', 'Jurnal Umum — ' . (auth()->user()?->toko?->nama_toko ?? 'ERPlay AI'))

@section('content')
<!-- Breadcrumb Navigation -->
<nav class="erp-breadcrumb d-none d-md-block" aria-label="Breadcrumb">
    <a href="{{ url('/dashboard') }}" aria-label="Beranda Dashboard"><i class="bi bi-house-door"></i></a>
    <i class="bi bi-chevron-right mx-1" style="font-size:9px;" aria-hidden="true"></i>
    <span>Akuntansi</span>
    <i class="bi bi-chevron-right mx-1" style="font-size:9px;" aria-hidden="true"></i>
    <span aria-current="page">Jurnal Umum</span>
</nav>

<!-- Page Header & Action Bar -->
<div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
    <div>
        <h1 class="h4 mb-0 fw-bold" style="font-family:'Plus Jakarta Sans',sans-serif;color:var(--pb-text);letter-spacing:-0.02em;">
            <i class="bi bi-journal-text me-2" aria-hidden="true"></i>Jurnal Umum (General Journal)
        </h1>
        <p class="mb-0 mt-1" style="color:var(--text-secondary);font-size:13px;">
            Catatan pencatatan mutasi transaksi keuangan debit dan kredit secara kronologis.
        </p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-sm btn-pb" data-bs-toggle="modal" data-bs-target="#modalTambahJurnal">
            <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Entri Jurnal Manual
        </button>
    </div>
</div>

{{-- Flash Messages --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
        <i class="bi bi-check-circle-fill me-1" aria-hidden="true"></i>{{ session('success') }}
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-1" aria-hidden="true"></i>{{ session('error') }}
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
@endif

<!-- Date Filter Form -->
<div class="card card-erp mb-3">
    <div class="card-body py-2 px-3">
        <form action="{{ route('akuntansi.jurnal.index') }}" method="GET" class="row g-2 align-items-center">
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

<div class="card card-erp mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0" style="font-size:13px;">
                <thead>
                    <tr>
                        <th class="ps-3 py-3" style="width:140px;" scope="col">No. Jurnal & Tanggal</th>
                        <th class="py-3" scope="col">Keterangan / Ref</th>
                        <th class="py-3" scope="col">Akun Perkiraan</th>
                        <th class="py-3 text-end" style="width:140px;" scope="col">Debit (Rp)</th>
                        <th class="pe-3 py-3 text-end" style="width:140px;" scope="col">Kredit (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jurnals as $jur)
                        @foreach($jur->details as $index => $det)
                        <tr class="{{ $index === 0 ? 'border-top' : '' }}">
                            @if($index === 0)
                            <td rowspan="{{ $jur->details->count() }}" class="ps-3 align-top py-2">
                                <div class="fw-bold font-monospace" style="color:var(--pb-dark);font-size:12px;">{{ $jur->nomor_jurnal }}</div>
                                <div class="text-secondary" style="font-size:11px;">{{ \Carbon\Carbon::parse($jur->tanggal)->format('d/m/Y') }}</div>
                                <span class="badge bg-light text-dark border mt-1" style="font-size:10px;">{{ $jur->ref_type }}</span>
                            </td>
                            <td rowspan="{{ $jur->details->count() }}" class="align-top py-2 fw-medium">
                                {{ $jur->keterangan }}
                            </td>
                            @endif

                            <td class="py-1">
                                <span class="{{ $det->kredit > 0 ? 'ms-3 text-secondary' : 'fw-semibold text-dark' }}">
                                    [{{ $det->akun->kode_akun ?? '-' }}] {{ $det->akun->nama_akun ?? 'Akun Tidak Ditemukan' }}
                                </span>
                            </td>
                            <td class="text-end font-monospace py-1">
                                {{ $det->debit > 0 ? number_format($det->debit, 0, ',', '.') : '-' }}
                            </td>
                            <td class="pe-3 text-end font-monospace py-1">
                                {{ $det->kredit > 0 ? number_format($det->kredit, 0, ',', '.') : '-' }}
                            </td>
                        </tr>
                        @endforeach
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">Belum ada transaksi jurnal umum pada periode ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($jurnals->hasPages())
    <div class="card-footer py-2">
        {{ $jurnals->withQueryString()->links() }}
    </div>
    @endif
</div>

<!-- Modal Tambah Jurnal Manual -->
<div class="modal fade" id="modalTambahJurnal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('akuntansi.jurnal.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" style="font-size:15px;">Entri Jurnal Umum Manual</h5>
                    <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2 mb-3">
                        <div class="col-4">
                            <label for="tanggal_jurnal" class="form-label fw-semibold" style="font-size:12px;">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal" id="tanggal_jurnal" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-8">
                            <label for="ket_jurnal" class="form-label fw-semibold" style="font-size:12px;">Keterangan Memoria <span class="text-danger">*</span></label>
                            <input type="text" name="keterangan" id="ket_jurnal" class="form-control form-control-sm" placeholder="Contoh: Pembayaran sewa toko bulan ini" required>
                        </div>
                    </div>

                    <div class="fw-bold mb-2" style="font-size:12px;">Detail Pos Debit & Kredit:</div>
                    <table class="table table-sm border mb-2" id="table-jurnal-lines">
                        <thead>
                            <tr class="bg-light">
                                <th>Akun Perkiraan</th>
                                <th style="width:160px;">Debit (Rp)</th>
                                <th style="width:160px;">Kredit (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <select name="details[0][akun_id]" class="form-select form-select-sm" required>
                                        <option value="">— Pilih Akun Debit —</option>
                                        @foreach($accounts as $acc)
                                            <option value="{{ $acc->id }}">{{ $acc->kode_akun }} - {{ $acc->nama_akun }} ({{ $acc->tipe_akun }})</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="number" name="details[0][debit]" class="form-control form-control-sm" value="0" min="0" required></td>
                                <td><input type="number" name="details[0][kredit]" class="form-control form-control-sm" value="0" min="0" required></td>
                            </tr>
                            <tr>
                                <td>
                                    <select name="details[1][akun_id]" class="form-select form-select-sm" required>
                                        <option value="">— Pilih Akun Kredit —</option>
                                        @foreach($accounts as $acc)
                                            <option value="{{ $acc->id }}">{{ $acc->kode_akun }} - {{ $acc->nama_akun }} ({{ $acc->tipe_akun }})</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="number" name="details[1][debit]" class="form-control form-control-sm" value="0" min="0" required></td>
                                <td><input type="number" name="details[1][kredit]" class="form-control form-control-sm" value="0" min="0" required></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-pb fw-bold">Simpan Jurnal</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
