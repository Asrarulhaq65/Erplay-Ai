@extends('layouts.enterprise')
@section('title', 'Master Kategori Produk — ' . (auth()->user()?->toko?->nama_toko ?? 'ERPlay AI'))

@push('styles')
<style>
    /* ── Compact Metric Header ── */
    .kategori-stat-card {
        background: var(--bg-card);
        border: 1px solid var(--border-light);
        border-radius: 12px;
        padding: 14px 16px;
        display: flex;
        align-items: center;
        gap: 12px;
        transition: border-color 0.15s ease, box-shadow 0.15s ease, var(--theme-transition);
    }
    .kategori-stat-card:hover {
        border-color: var(--pb-mid);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
    }
    .kategori-stat-icon {
        width: 38px; height: 38px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
        background: rgba(13, 78, 86, 0.1);
        color: var(--pb-dark);
    }
    [data-theme="dark"] .kategori-stat-icon {
        background: rgba(77, 184, 196, 0.15);
        color: #4DB8C4;
    }
    .kategori-stat-label {
        font-size: 11px;
        font-weight: 600;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .kategori-stat-val {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 18px;
        font-weight: 800;
        color: var(--pb-text);
        line-height: 1.1;
    }

    /* Badge & Micro Elements */
    .badge-kelompok {
        background: rgba(13, 78, 86, 0.08);
        color: var(--pb-dark);
        font-size: 11px;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 6px;
        border: 1px solid rgba(13, 78, 86, 0.12);
    }
    [data-theme="dark"] .badge-kelompok {
        background: rgba(77, 184, 196, 0.12);
        color: #4DB8C4;
        border-color: rgba(77, 184, 196, 0.2);
    }

    .badge-produk-count {
        background: var(--bg-body);
        color: var(--text-secondary);
        font-size: 11px;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 6px;
        border: 1px solid var(--border-light);
    }
</style>
@endpush

@section('content')
<!-- Breadcrumb Navigation -->
<nav class="erp-breadcrumb d-none d-md-block" aria-label="Breadcrumb">
    <a href="{{ url('/dashboard') }}" aria-label="Beranda Dashboard"><i class="bi bi-house-door"></i></a>
    <i class="bi bi-chevron-right mx-1" style="font-size:9px;" aria-hidden="true"></i>
    <span aria-current="page">Master Kategori Produk</span>
</nav>

<!-- Page Header & Action Bar -->
<div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
    <div>
        <h1 class="h4 mb-0 fw-bold" style="font-family:'Plus Jakarta Sans',sans-serif;color:var(--pb-text);letter-spacing:-0.02em;">
            <i class="bi bi-tag me-2" aria-hidden="true"></i>Master Kategori Produk
        </h1>
        <p class="mb-0 mt-1" style="color:var(--text-secondary);font-size:13px;">
            Pengelompokan hirarki tingkat 2 (Sub-Kategori) untuk katalog barang toko Anda.
        </p>
    </div>
    <button class="btn btn-sm btn-pb px-3 py-2" data-bs-toggle="modal" data-bs-target="#modalTambah">
        <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Tambah Kategori Baru
    </button>
</div>

<!-- Quick Stat Summary Row -->
<div class="row g-3 mb-3">
    <div class="col-12 col-md-4">
        <div class="kategori-stat-card">
            <div class="kategori-stat-icon" aria-hidden="true"><i class="bi bi-tags"></i></div>
            <div>
                <div class="kategori-stat-label">Total Kategori</div>
                <div class="kategori-stat-val">{{ number_format($kategoris->total(), 0, ',', '.') }} Kategori</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="kategori-stat-card">
            <div class="kategori-stat-icon" aria-hidden="true"><i class="bi bi-collection"></i></div>
            <div>
                <div class="kategori-stat-label">Kelompok Induk</div>
                <div class="kategori-stat-val">{{ number_format(count($kelompoks), 0, ',', '.') }} Kelompok</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="kategori-stat-card">
            <div class="kategori-stat-icon" aria-hidden="true"><i class="bi bi-box-seam"></i></div>
            <div>
                <div class="kategori-stat-label">Kategori Terisi</div>
                <div class="kategori-stat-val">{{ number_format($kategoris->getCollection()->where('produks_count', '>', 0)->count(), 0, ',', '.') }} Aktif Digunakan</div>
            </div>
        </div>
    </div>
</div>

<!-- Main Table Card -->
<div class="card card-erp mb-3">
    <div class="card-header py-2 px-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <h2 class="h6 card-title mb-0" style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;">
                <i class="bi bi-list-ul me-2" aria-hidden="true"></i>Daftar Kategori
            </h2>

            <!-- Search Form -->
            <form action="{{ route('master.kategori-produk.index') }}" method="GET" class="d-flex align-items-center gap-1" style="width:260px;">
                <div class="input-group input-group-sm">
                    <input type="text" name="q" class="form-control" placeholder="Cari nama kategori..." value="{{ $q }}" aria-label="Cari Kategori">
                    <button class="btn btn-outline-secondary" type="submit" aria-label="Cari"><i class="bi bi-search" aria-hidden="true"></i></button>
                    @if($q)
                        <a href="{{ route('master.kategori-produk.index') }}" class="btn btn-outline-danger" title="Reset Pencarian"><i class="bi bi-x-lg" aria-hidden="true"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-sm align-middle table-hover mb-0">
            <thead>
                <tr>
                    <th class="text-center py-2 px-3" style="width: 50px;" scope="col">No</th>
                    <th class="py-2" scope="col">Nama Kategori</th>
                    <th class="py-2" scope="col">Kelompok Induk</th>
                    <th class="py-2 text-center" style="width: 140px;" scope="col">Jumlah Produk</th>
                    <th class="py-2 text-center px-3" style="width: 100px;" scope="col">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($kategoris as $index => $kat)
                    <tr>
                        <td class="text-center text-muted px-3" style="font-size:12px;">{{ $kategoris->firstItem() + $index }}</td>
                        <td class="fw-semibold" style="font-size:13px;color:var(--pb-text);">{{ $kat->nama_kategori }}</td>
                        <td>
                            <span class="badge-kelompok">
                                <i class="bi bi-diagram-2 me-1" aria-hidden="true"></i>{{ $kat->kelompok->nama_kelompok ?? '-' }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge-produk-count">
                                <i class="bi bi-box-seam me-1" aria-hidden="true"></i>{{ number_format($kat->produks_count ?? 0, 0, ',', '.') }} Produk
                            </span>
                        </td>
                        <td class="text-center px-3">
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <button class="btn btn-sm btn-outline-primary py-1 px-2" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#modalEdit{{ $kat->id }}" 
                                        title="Edit Kategori">
                                    <i class="bi bi-pencil" aria-hidden="true"></i>
                                </button>
                                <form action="{{ route('master.kategori-produk.destroy', $kat->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus kategori {{ addslashes($kat->nama_kategori) }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2" title="Hapus Kategori">
                                        <i class="bi bi-trash" aria-hidden="true"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-5">
                            <i class="bi bi-inbox d-block mb-2" style="font-size:36px;opacity:0.3;" aria-hidden="true"></i>
                            <div style="font-size:13px;font-weight:600;color:var(--text-secondary);">Belum Ada Data Kategori Produk</div>
                            <div style="font-size:12px;color:var(--text-muted);margin-bottom:12px;">Tambahkan kategori produk pertama Anda untuk pengelompokan barang.</div>
                            <button class="btn btn-pb btn-sm px-3" data-bs-toggle="modal" data-bs-target="#modalTambah">
                                <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Tambah Kategori
                            </button>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($kategoris->hasPages())
        <div class="card-footer py-2 px-3 bg-transparent border-top">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div style="font-size:12px;color:var(--text-secondary);">
                    Menampilkan {{ $kategoris->firstItem() }}–{{ $kategoris->lastItem() }} dari {{ $kategoris->total() }} kategori
                </div>
                <div>
                    {{ $kategoris->links('pagination::bootstrap-5', ['class' => 'pagination-sm mb-0']) }}
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Modal Edit Elements (Outside table for clean DOM) -->
@foreach ($kategoris as $kat)
    <div class="modal fade" id="modalEdit{{ $kat->id }}" tabindex="-1" aria-labelledby="titleModalEdit{{ $kat->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header py-3">
                    <h3 class="modal-title h6 fw-bold mb-0" id="titleModalEdit{{ $kat->id }}" style="color:var(--pb-text);">
                        <i class="bi bi-pencil-square me-2" aria-hidden="true"></i>Edit Kategori Produk
                    </h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form action="{{ route('master.kategori-produk.update', $kat->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body py-3">
                        <div class="mb-3">
                            <label for="kelompok_edit_{{ $kat->id }}" class="form-label fw-semibold" style="font-size:13px;">Kelompok Produk <span class="text-danger">*</span></label>
                            <select id="kelompok_edit_{{ $kat->id }}" name="kelompok_id" class="form-select form-select-sm" required>
                                <option value="">-- Pilih Kelompok --</option>
                                @foreach($kelompoks as $kel)
                                    <option value="{{ $kel->id }}" {{ $kat->kelompok_id == $kel->id ? 'selected' : '' }}>
                                        {{ $kel->nama_kelompok }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="nama_kategori_edit_{{ $kat->id }}" class="form-label fw-semibold" style="font-size:13px;">Nama Kategori <span class="text-danger">*</span></label>
                            <input type="text" id="nama_kategori_edit_{{ $kat->id }}" name="nama_kategori" class="form-control form-control-sm" value="{{ $kat->nama_kategori }}" required maxlength="50" autocomplete="off">
                        </div>
                    </div>
                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sm btn-pb px-3"><i class="bi bi-check2-circle me-1" aria-hidden="true"></i>Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

<!-- Modal Tambah Kategori Baru -->
<div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="titleModalTambah" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-3">
                <h3 class="modal-title h6 fw-bold mb-0" id="titleModalTambah" style="color:var(--pb-text);">
                    <i class="bi bi-plus-circle me-2" aria-hidden="true"></i>Tambah Kategori Produk Baru
                </h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <form action="{{ route('master.kategori-produk.store') }}" method="POST">
                @csrf
                <div class="modal-body py-3">
                    <div class="mb-3">
                        <label for="kelompok_tambah" class="form-label fw-semibold" style="font-size:13px;">Kelompok Produk <span class="text-danger">*</span></label>
                        <select id="kelompok_tambah" name="kelompok_id" class="form-select form-select-sm" required>
                            <option value="">-- Pilih Kelompok --</option>
                            @foreach($kelompoks as $kel)
                                <option value="{{ $kel->id }}">{{ $kel->nama_kelompok }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="nama_kategori_tambah" class="form-label fw-semibold" style="font-size:13px;">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" id="nama_kategori_tambah" name="nama_kategori" class="form-control form-control-sm" placeholder="Contoh: Minuman Dingin, Buku Tulis" required maxlength="50" autocomplete="off">
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-pb px-3"><i class="bi bi-check2-circle me-1" aria-hidden="true"></i>Simpan Kategori</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
