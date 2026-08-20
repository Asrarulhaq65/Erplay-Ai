@extends('layouts.enterprise')
@section('title', 'Audit Log & Activity Trail — ' . (auth()->user()?->toko?->nama_toko ?? 'ERPlay AI'))

@section('content')
<!-- Breadcrumb Navigation -->
<nav class="erp-breadcrumb d-none d-md-block" aria-label="Breadcrumb">
    <a href="{{ url('/dashboard') }}" aria-label="Beranda Dashboard"><i class="bi bi-house-door"></i></a>
    <i class="bi bi-chevron-right mx-1" style="font-size:9px;" aria-hidden="true"></i>
    <span>Pengaturan</span>
    <i class="bi bi-chevron-right mx-1" style="font-size:9px;" aria-hidden="true"></i>
    <span aria-current="page">Audit Log System</span>
</nav>

<!-- Page Header & Action Bar -->
<div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
    <div>
        <h1 class="h4 mb-0 fw-bold" style="font-family:'Plus Jakarta Sans',sans-serif;color:var(--pb-text);letter-spacing:-0.02em;">
            <i class="bi bi-shield-check me-2 text-primary" aria-hidden="true"></i>Audit Log & System Activity Trail
        </h1>
        <p class="mb-0 mt-1" style="color:var(--text-secondary);font-size:13px;">
            Pencatatan riwayat aktivitas pengguna, transaksi, dan perubahan data penting dalam sistem toko.
        </p>
    </div>
</div>

<!-- Search & Module Filter -->
<div class="card card-erp mb-4">
    <div class="card-body py-2 px-3">
        <form action="{{ route('pengaturan.audit-log.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-12 col-md-5">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari aktivitas atau nama staf..." value="{{ $search }}">
            </div>
            <div class="col-12 col-md-4">
                <select name="modul" class="form-select form-select-sm">
                    <option value="">— Semua Modul Sistem —</option>
                    @foreach($modules as $mod)
                        <option value="{{ $mod }}" {{ ($modul == $mod) ? 'selected' : '' }}>Modul {{ $mod }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-3">
                <button type="submit" class="btn btn-sm btn-pb w-100 fw-bold">
                    <i class="bi bi-search me-1"></i>Filter Log
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card card-erp mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size:13px;">
                <thead>
                    <tr>
                        <th class="ps-3 py-3" style="width:160px;" scope="col">Waktu Audit</th>
                        <th class="py-3" style="width:180px;" scope="col">Pengguna / Staf</th>
                        <th class="py-3" style="width:110px;" scope="col">Modul</th>
                        <th class="py-3" scope="col">Deskripsi Aktivitas</th>
                        <th class="pe-3 py-3 text-end" style="width:140px;" scope="col">IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td class="ps-3 text-secondary font-monospace" style="font-size:12px;">
                            {{ $log->created_at->format('d/m/Y H:i:s') }}
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $log->user->nama_lengkap ?? 'System Automation' }}</div>
                            <span class="text-secondary" style="font-size:11px;">@ {{ $log->user->username ?? 'system' }}</span>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border px-2 py-1" style="font-size:11px;">{{ $log->modul }}</span>
                        </td>
                        <td class="fw-medium" style="color:var(--text-primary);">
                            {{ $log->aktivitas }}
                        </td>
                        <td class="pe-3 text-end font-monospace text-secondary" style="font-size:12px;">
                            {{ $log->ip_address ?? '127.0.0.1' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">Belum ada catatan aktivitas sistem.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($logs->hasPages())
    <div class="card-footer py-2">
        {{ $logs->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
