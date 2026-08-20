@extends('layouts.enterprise')
@section('title', 'Daftar Akun (Chart of Accounts) — ' . (auth()->user()?->toko?->nama_toko ?? 'ERPlay AI'))

@section('content')
<!-- Breadcrumb Navigation -->
<nav class="erp-breadcrumb d-none d-md-block" aria-label="Breadcrumb">
    <a href="{{ url('/dashboard') }}" aria-label="Beranda Dashboard"><i class="bi bi-house-door"></i></a>
    <i class="bi bi-chevron-right mx-1" style="font-size:9px;" aria-hidden="true"></i>
    <span>Akuntansi</span>
    <i class="bi bi-chevron-right mx-1" style="font-size:9px;" aria-hidden="true"></i>
    <span aria-current="page">Chart of Accounts</span>
</nav>

<!-- Page Header & Toolbar -->
<div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
    <div>
        <h1 class="h4 mb-0 fw-bold" style="font-family:'Plus Jakarta Sans',sans-serif;color:var(--pb-text);letter-spacing:-0.02em;">
            <i class="bi bi-journal-bookmark-fill me-2" aria-hidden="true"></i>Chart of Accounts (COA)
        </h1>
        <p class="mb-0 mt-1" style="color:var(--text-secondary);font-size:13px;">
            Daftar perkiraan akun akuntansi untuk pengelompokan transaksi keuangan toko.
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('akuntansi.jurnal.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-journal-text me-1" aria-hidden="true"></i>Jurnal Umum
        </a>
        <button type="button" class="btn btn-sm btn-pb" data-bs-toggle="modal" data-bs-target="#modalTambahAkun">
            <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Tambah Akun Baru
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

<div class="card card-erp mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size:13px;">
                <thead>
                    <tr>
                        <th class="ps-3 py-3" style="width:120px;" scope="col">Kode Akun</th>
                        <th class="py-3" scope="col">Nama Perkiraan Akun</th>
                        <th class="py-3" style="width:140px;" scope="col">Klasifikasi Tipe</th>
                        <th class="py-3 text-center" style="width:120px;" scope="col">Saldo Normal</th>
                        <th class="pe-3 py-3 text-end" style="width:160px;" scope="col">Saldo Awal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($accounts as $acc)
                    <tr>
                        <td class="ps-3 font-monospace fw-bold" style="color:var(--pb-dark);">{{ $acc->kode_akun }}</td>
                        <td class="fw-semibold">{{ $acc->nama_akun }}</td>
                        <td>
                            @php
                                $badgeClass = match($acc->tipe_akun) {
                                    'Aset' => 'bg-success',
                                    'Kewajiban' => 'bg-danger',
                                    'Ekuitas' => 'bg-info text-dark',
                                    'Pendapatan' => 'bg-primary',
                                    'Beban' => 'bg-warning text-dark',
                                    default => 'bg-secondary',
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }} px-2 py-1" style="font-size:11px;">{{ $acc->tipe_akun }}</span>
                        </td>
                        <td class="text-center font-monospace" style="font-size:12px;">{{ $acc->saldo_normal }}</td>
                        <td class="pe-3 text-end font-monospace">Rp {{ number_format($acc->saldo_awal, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">Belum ada akun akuntansi terdaftar.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Akun -->
<div class="modal fade" id="modalTambahAkun" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('akuntansi.accounts.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" style="font-size:15px;">Tambah Akun Akuntansi Baru</h5>
                    <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="kode_akun" class="form-label fw-semibold" style="font-size:12px;">Kode Akun <span class="text-danger">*</span></label>
                        <input type="text" name="kode_akun" id="kode_akun" class="form-control form-control-sm" placeholder="Contoh: 1003 atau 6003" required>
                    </div>
                    <div class="mb-3">
                        <label for="nama_akun" class="form-label fw-semibold" style="font-size:12px;">Nama Akun Perkiraan <span class="text-danger">*</span></label>
                        <input type="text" name="nama_akun" id="nama_akun" class="form-control form-control-sm" placeholder="Contoh: Beban Listrik & Air" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="tipe_akun" class="form-label fw-semibold" style="font-size:12px;">Tipe Akun <span class="text-danger">*</span></label>
                            <select name="tipe_akun" id="tipe_akun" class="form-select form-select-sm" required>
                                <option value="Aset">Aset</option>
                                <option value="Kewajiban">Kewajiban</option>
                                <option value="Ekuitas">Ekuitas</option>
                                <option value="Pendapatan">Pendapatan</option>
                                <option value="Beban">Beban</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label for="saldo_normal" class="form-label fw-semibold" style="font-size:12px;">Saldo Normal <span class="text-danger">*</span></label>
                            <select name="saldo_normal" id="saldo_normal" class="form-select form-select-sm" required>
                                <option value="Debit">Debit</option>
                                <option value="Kredit">Kredit</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="saldo_awal" class="form-label fw-semibold" style="font-size:12px;">Saldo Awal (Rp)</label>
                        <input type="number" name="saldo_awal" id="saldo_awal" class="form-control form-control-sm" value="0" min="0">
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-pb fw-bold">Simpan Akun</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
