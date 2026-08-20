@extends('layouts.enterprise')
@section('title', 'Master Supplier — ' . (auth()->user()?->toko?->nama_toko ?? 'ERPlay AI'))

@push('styles')
<style>
    /* ── Compact Supplier Stat Cards ── */
    .supplier-stat-card {
        background: var(--bg-card);
        border: 1px solid var(--border-light);
        border-radius: 12px;
        padding: 14px 16px;
        display: flex;
        align-items: center;
        gap: 12px;
        transition: border-color 0.15s ease, box-shadow 0.15s ease, var(--theme-transition);
        height: 100%;
    }
    .supplier-stat-card:hover {
        border-color: var(--pb-mid);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
    }
    .supplier-stat-icon {
        width: 38px; height: 38px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }
    .supplier-stat-label {
        font-size: 11px;
        font-weight: 600;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .supplier-stat-val {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 18px;
        font-weight: 800;
        color: var(--pb-text);
        line-height: 1.1;
    }

    .badge-supplier-active {
        background: rgba(13, 78, 86, 0.1);
        color: var(--pb-dark);
        border: 1px solid rgba(13, 78, 86, 0.2);
        font-size: 11px;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 6px;
    }
    [data-theme="dark"] .badge-supplier-active {
        background: rgba(77, 184, 196, 0.15);
        color: #4DB8C4;
        border-color: rgba(77, 184, 196, 0.25);
    }
</style>
@endpush

@section('content')
<!-- Breadcrumb Navigation -->
<nav class="erp-breadcrumb d-none d-md-block" aria-label="Breadcrumb">
    <a href="{{ url('/dashboard') }}" aria-label="Beranda Dashboard"><i class="bi bi-house-door"></i></a>
    <i class="bi bi-chevron-right mx-1" style="font-size:9px;" aria-hidden="true"></i>
    <span aria-current="page">Master Supplier</span>
</nav>

<!-- Page Header & Action Bar -->
<div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
    <div>
        <h1 class="h4 mb-0 fw-bold" style="font-family:'Plus Jakarta Sans',sans-serif;color:var(--pb-text);letter-spacing:-0.02em;">
            <i class="bi bi-truck me-2" aria-hidden="true"></i>Master Data Supplier & Vendor
        </h1>
        <p class="mb-0 mt-1" style="color:var(--text-secondary);font-size:13px;">
            Kelola data distributor, sales representative (PIC), nomor kontak, dan histori transaksi pembelian.
        </p>
    </div>
    <button class="btn btn-sm btn-pb px-3 py-2" data-bs-toggle="modal" data-bs-target="#modalTambah">
        <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Tambah Supplier Baru
    </button>
</div>

<!-- Quick Supplier Stat Summary Row -->
<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">
        <div class="supplier-stat-card">
            <div class="supplier-stat-icon" style="background:rgba(13, 78, 86, 0.1);color:var(--pb-dark);" aria-hidden="true"><i class="bi bi-truck"></i></div>
            <div>
                <div class="supplier-stat-label">Total Supplier</div>
                <div class="supplier-stat-val">{{ number_format($suppliers->total(), 0, ',', '.') }} Mitra</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="supplier-stat-card">
            <div class="supplier-stat-icon" style="background:rgba(29, 78, 216, 0.1);color:#1D4ED8;" aria-hidden="true"><i class="bi bi-person-badge"></i></div>
            <div>
                <div class="supplier-stat-label">Kontak Sales PIC</div>
                <div class="supplier-stat-val">{{ number_format($suppliers->getCollection()->whereNotNull('nama_kontak')->count(), 0, ',', '.') }} Orang</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="supplier-stat-card">
            <div class="supplier-stat-icon" style="background:rgba(21, 128, 61, 0.1);color:#15803D;" aria-hidden="true"><i class="bi bi-cart-check"></i></div>
            <div>
                <div class="supplier-stat-label">Aktif Kulakan</div>
                <div class="supplier-stat-val">{{ number_format($suppliers->getCollection()->where('pembelians_count', '>', 0)->count(), 0, ',', '.') }} Vendor</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="supplier-stat-card">
            <div class="supplier-stat-icon" style="background:rgba(126, 34, 206, 0.1);color:#7E22CE;" aria-hidden="true"><i class="bi bi-receipt"></i></div>
            <div>
                <div class="supplier-stat-label">Total Faktur Masuk</div>
                <div class="supplier-stat-val">{{ number_format($suppliers->getCollection()->sum('pembelians_count'), 0, ',', '.') }} Faktur</div>
            </div>
        </div>
    </div>
</div>

<!-- Main Data Table Card -->
<div class="card card-erp mb-3">
    <div class="card-header py-2 px-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <h2 class="h6 card-title mb-0" style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;">
                <i class="bi bi-building me-2" aria-hidden="true"></i>Katalog Supplier & Vendor
            </h2>

            <!-- Search Form -->
            <form action="{{ route('master.supplier.index') }}" method="GET" class="d-flex align-items-center gap-1" style="width:260px;">
                <div class="input-group input-group-sm">
                    <input type="text" name="q" class="form-control" placeholder="Cari distributor / PIC / telp..." value="{{ $q }}" aria-label="Cari Supplier">
                    <button class="btn btn-outline-secondary" type="submit" aria-label="Cari"><i class="bi bi-search" aria-hidden="true"></i></button>
                    @if($q)
                        <a href="{{ route('master.supplier.index') }}" class="btn btn-outline-danger" title="Reset Pencarian"><i class="bi bi-x-lg" aria-hidden="true"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-sm align-middle table-hover mb-0">
            <thead>
                <tr>
                    <th class="text-center py-2 px-3" style="width: 40px;" scope="col">No</th>
                    <th class="py-2" scope="col">Nama Supplier / PT</th>
                    <th class="py-2" style="width: 160px;" scope="col">Kontak PIC / Sales</th>
                    <th style="width: 150px;" class="py-2" scope="col">No. Telepon / WA</th>
                    <th class="py-2" scope="col">Alamat Kantor / Gudang</th>
                    <th class="text-center py-2" style="width: 110px;" scope="col">Histori Faktur</th>
                    <th class="text-center py-2 px-3" style="width: 100px;" scope="col">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($suppliers as $index => $sup)
                    <tr>
                        <td class="text-center text-muted px-3" style="font-size:12px;">{{ $suppliers->firstItem() + $index }}</td>
                        <td class="fw-semibold" style="font-size:13px;color:var(--pb-text);">{{ $sup->nama_supplier }}</td>
                        <td style="font-size:12px;">
                            <i class="bi bi-person me-1 text-muted" aria-hidden="true"></i>{{ $sup->nama_kontak ?? '-' }}
                        </td>
                        <td style="font-size:12px;">
                            <i class="bi bi-telephone me-1 text-muted" aria-hidden="true"></i>{{ $sup->no_telepon }}
                        </td>
                        <td class="text-truncate" style="max-width:220px;font-size:12px;" title="{{ $sup->alamat }}">{{ $sup->alamat ?? '-' }}</td>
                        <td class="text-center">
                            <span class="badge-supplier-active">
                                <i class="bi bi-file-earmark-text me-1" aria-hidden="true"></i>{{ $sup->pembelians_count }} Faktur
                            </span>
                        </td>
                        <td class="text-center px-3">
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <button class="btn btn-sm btn-outline-primary py-1 px-2" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#modalEdit{{ $sup->id }}" 
                                        title="Edit Supplier">
                                    <i class="bi bi-pencil" aria-hidden="true"></i>
                                </button>
                                <form action="{{ route('master.supplier.destroy', $sup->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus supplier {{ addslashes($sup->nama_supplier) }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2" title="Hapus Supplier">
                                        <i class="bi bi-trash" aria-hidden="true"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="bi bi-truck d-block mb-2" style="font-size:36px;opacity:0.3;" aria-hidden="true"></i>
                            <div style="font-size:13px;font-weight:600;color:var(--text-secondary);">Belum Ada Data Supplier</div>
                            <div style="font-size:12px;color:var(--text-muted);margin-bottom:12px;">Tambahkan distributor atau vendor tempat Anda melakukan kulakan stok barang.</div>
                            <button class="btn btn-pb btn-sm px-3" data-bs-toggle="modal" data-bs-target="#modalTambah">
                                <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Tambah Supplier Baru
                            </button>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($suppliers->hasPages())
        <div class="card-footer py-2 px-3 bg-transparent border-top">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div style="font-size:12px;color:var(--text-secondary);">
                    Menampilkan {{ $suppliers->firstItem() }}–{{ $suppliers->lastItem() }} dari {{ $suppliers->total() }} supplier
                </div>
                <div>
                    {{ $suppliers->links('pagination::bootstrap-5', ['class' => 'pagination-sm mb-0']) }}
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Modal Edit Elements (Outside table loop for clean DOM) -->
@foreach ($suppliers as $sup)
    <div class="modal fade" id="modalEdit{{ $sup->id }}" tabindex="-1" aria-labelledby="titleModalEdit{{ $sup->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header py-3">
                    <h3 class="modal-title h6 fw-bold mb-0" id="titleModalEdit{{ $sup->id }}" style="color:var(--pb-text);">
                        <i class="bi bi-pencil-square me-2" aria-hidden="true"></i>Edit Data Supplier
                    </h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form action="{{ route('master.supplier.update', $sup->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body py-3">
                        <div class="mb-3">
                            <label for="nama_supplier_edit_{{ $sup->id }}" class="form-label fw-semibold" style="font-size:13px;">Nama Supplier / PT Distributor <span class="text-danger">*</span></label>
                            <input type="text" id="nama_supplier_edit_{{ $sup->id }}" name="nama_supplier" class="form-control form-control-sm" value="{{ $sup->nama_supplier }}" required maxlength="100" autocomplete="off">
                        </div>
                        <div class="mb-3">
                            <label for="nama_kontak_edit_{{ $sup->id }}" class="form-label fw-semibold" style="font-size:13px;">Nama Sales Representative / PIC</label>
                            <input type="text" id="nama_kontak_edit_{{ $sup->id }}" name="nama_kontak" class="form-control form-control-sm" value="{{ $sup->nama_kontak }}" placeholder="Contoh: Pak Herman (Sales Area)" maxlength="100" autocomplete="off">
                        </div>
                        <div class="mb-3">
                            <label for="no_telepon_edit_{{ $sup->id }}" class="form-label fw-semibold" style="font-size:13px;">No. Telepon / WhatsApp <span class="text-danger">*</span></label>
                            <input type="text" id="no_telepon_edit_{{ $sup->id }}" name="no_telepon" class="form-control form-control-sm" value="{{ $sup->no_telepon }}" required maxlength="20" autocomplete="off">
                        </div>
                        <div>
                            <label for="alamat_edit_{{ $sup->id }}" class="form-label fw-semibold" style="font-size:13px;">Alamat Kantor / Gudang Supplier</label>
                            <textarea id="alamat_edit_{{ $sup->id }}" name="alamat" class="form-control form-control-sm" rows="2" maxlength="500">{{ $sup->alamat }}</textarea>
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

<!-- Modal Tambah Supplier Baru -->
<div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="titleModalTambah" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-3">
                <h3 class="modal-title h6 fw-bold mb-0" id="titleModalTambah" style="color:var(--pb-text);">
                    <i class="bi bi-truck me-2" aria-hidden="true"></i>Tambah Supplier Baru
                </h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <form action="{{ route('master.supplier.store') }}" method="POST">
                @csrf
                <div class="modal-body py-3">
                    <div class="mb-3">
                        <label for="nama_supplier_tambah" class="form-label fw-semibold" style="font-size:13px;">Nama Supplier / PT Distributor <span class="text-danger">*</span></label>
                        <input type="text" id="nama_supplier_tambah" name="nama_supplier" class="form-control form-control-sm" placeholder="Contoh: PT Sinar Niaga Perkasa" required maxlength="100" autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <label for="nama_kontak_tambah" class="form-label fw-semibold" style="font-size:13px;">Nama Sales Representative / PIC</label>
                        <input type="text" id="nama_kontak_tambah" name="nama_kontak" class="form-control form-control-sm" placeholder="Contoh: Mas Agus (Sales Area)" maxlength="100" autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <label for="no_telepon_tambah" class="form-label fw-semibold" style="font-size:13px;">No. Telepon / WhatsApp <span class="text-danger">*</span></label>
                        <input type="text" id="no_telepon_tambah" name="no_telepon" class="form-control form-control-sm" placeholder="Contoh: 081298765432" required maxlength="20" autocomplete="off">
                    </div>
                    <div>
                        <label for="alamat_tambah" class="form-label fw-semibold" style="font-size:13px;">Alamat Kantor / Gudang Supplier</label>
                        <textarea id="alamat_tambah" name="alamat" class="form-control form-control-sm" rows="2" placeholder="Alamat komplek pergudangan, jalan, kota..." maxlength="500"></textarea>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-pb px-3"><i class="bi bi-check2-circle me-1" aria-hidden="true"></i>Simpan Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
