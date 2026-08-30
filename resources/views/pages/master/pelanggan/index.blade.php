@extends('layouts.enterprise')
@section('title', 'Master Pelanggan — ' . (auth()->user()?->toko?->nama_toko ?? 'ERPlay AI'))

@push('styles')
<style>
    /* ── Compact Customer Stat Cards ── */
    .pelanggan-stat-card {
        background: var(--bg-card);
        border: 1px solid var(--border-light);
        border-radius: 12px;
        padding: 14px 16px;
        display: flex;
        align-items: center;
        gap: 12px;
        transition: border-color 0.15s ease, box-shadow 0.15s ease, var(--theme-transition);
    }
    .pelanggan-stat-card:hover {
        border-color: var(--pb-mid);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
    }
    .pelanggan-stat-icon {
        width: 38px; height: 38px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }
    .pelanggan-stat-label {
        font-size: 11px;
        font-weight: 600;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .pelanggan-stat-val {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 18px;
        font-weight: 800;
        color: var(--pb-text);
        line-height: 1.1;
    }

    /* Customer Tier Badges - WCAG AA High Contrast */
    .badge-tier-umum { background: rgba(21, 128, 61, 0.1); color: #15803D; border: 1px solid rgba(21, 128, 61, 0.2); }
    .badge-tier-member { background: rgba(29, 78, 216, 0.1); color: #1D4ED8; border: 1px solid rgba(29, 78, 216, 0.2); }
    .badge-tier-rekan { background: rgba(126, 34, 206, 0.1); color: #7E22CE; border: 1px solid rgba(126, 34, 206, 0.2); }
    .badge-tier-motoris { background: rgba(180, 107, 24, 0.1); color: #B46B18; border: 1px solid rgba(180, 107, 24, 0.2); }

    [data-theme="dark"] .badge-tier-umum { background: rgba(52, 211, 153, 0.15); color: #34D399; border-color: rgba(52, 211, 153, 0.25); }
    [data-theme="dark"] .badge-tier-member { background: rgba(96, 165, 250, 0.15); color: #60A5FA; border-color: rgba(96, 165, 250, 0.25); }
    [data-theme="dark"] .badge-tier-rekan { background: rgba(192, 132, 252, 0.15); color: #C084FC; border-color: rgba(192, 132, 252, 0.25); }
    [data-theme="dark"] .badge-tier-motoris { background: rgba(251, 191, 36, 0.15); color: #FBBF24; border-color: rgba(251, 191, 36, 0.25); }

    .tier-inline-select {
        min-width: 92px;
        width: auto;
        margin: 0 auto;
        padding: 3px 24px 3px 8px;
        font-size: 11px;
        font-weight: 600;
        border-radius: 6px;
        cursor: pointer;
    }
    .tier-inline-select:focus { box-shadow: 0 0 0 3px rgba(91, 160, 173, 0.2); }
</style>
@endpush

@section('content')
@if(session('import_errors') && count(session('import_errors')) > 0)
    <div class="alert alert-warning py-2 px-3 mb-3" style="font-size:12px;">
        <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle me-1"></i>Beberapa baris tidak diimport:</div>
        <ul class="mb-0 ps-3">
            @foreach(session('import_errors') as $importError)
                <li>{{ $importError }}</li>
            @endforeach
        </ul>
    </div>
@endif

<!-- Breadcrumb Navigation -->
<nav class="erp-breadcrumb d-none d-md-block" aria-label="Breadcrumb">
    <a href="{{ url('/dashboard') }}" aria-label="Beranda Dashboard"><i class="bi bi-house-door"></i></a>
    <i class="bi bi-chevron-right mx-1" style="font-size:9px;" aria-hidden="true"></i>
    <span aria-current="page">Master Pelanggan</span>
</nav>

<!-- Page Header & Action Bar -->
<div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
    <div>
        <h1 class="h4 mb-0 fw-bold" style="font-family:'Plus Jakarta Sans',sans-serif;color:var(--pb-text);letter-spacing:-0.02em;">
            <i class="bi bi-people me-2" aria-hidden="true"></i>Master Data Pelanggan
        </h1>
        <p class="mb-0 mt-1" style="color:var(--text-secondary);font-size:13px;">
            Kelola data pembeli, nomor kontak, serta penentuan tingkat harga otomatis di kasir POS.
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('master.pelanggan.import') }}" class="btn btn-sm btn-outline-success px-3 py-2">
            <i class="bi bi-file-earmark-arrow-up me-1" aria-hidden="true"></i>Import Pelanggan
        </a>
        <button class="btn btn-sm btn-pb px-3 py-2" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Tambah Pelanggan Baru
        </button>
    </div>
</div>

<!-- Quick Customer Stat Summary Row -->
<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">
        <div class="pelanggan-stat-card">
            <div class="pelanggan-stat-icon" style="background:rgba(13, 78, 86, 0.1);color:var(--pb-dark);" aria-hidden="true"><i class="bi bi-people"></i></div>
            <div>
                <div class="pelanggan-stat-label">Total Pelanggan</div>
                <div class="pelanggan-stat-val">{{ number_format($pelanggans->total(), 0, ',', '.') }} Terdaftar</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="pelanggan-stat-card">
            <div class="pelanggan-stat-icon" style="background:rgba(29, 78, 216, 0.1);color:#1D4ED8;" aria-hidden="true"><i class="bi bi-award"></i></div>
            <div>
                <div class="pelanggan-stat-label">Pelanggan Member</div>
                <div class="pelanggan-stat-val">{{ number_format($pelanggans->getCollection()->where('status_pelanggan', 'Member')->count(), 0, ',', '.') }} Orang</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="pelanggan-stat-card">
            <div class="pelanggan-stat-icon" style="background:rgba(126, 34, 206, 0.1);color:#7E22CE;" aria-hidden="true"><i class="bi bi-briefcase"></i></div>
            <div>
                <div class="pelanggan-stat-label">Rekan & Motoris</div>
                <div class="pelanggan-stat-val">{{ number_format($pelanggans->getCollection()->whereIn('status_pelanggan', ['Rekan', 'Motoris'])->count(), 0, ',', '.') }} Mitra</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="pelanggan-stat-card">
            <div class="pelanggan-stat-icon" style="background:rgba(21, 128, 61, 0.1);color:#15803D;" aria-hidden="true"><i class="bi bi-person"></i></div>
            <div>
                <div class="pelanggan-stat-label">Pelanggan Umum</div>
                <div class="pelanggan-stat-val">{{ number_format($pelanggans->getCollection()->where('status_pelanggan', 'Umum')->count(), 0, ',', '.') }} Orang</div>
            </div>
        </div>
    </div>
</div>

<!-- Main Data Table Card -->
<div class="card card-erp mb-3">
    <div class="card-header py-2 px-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <h2 class="h6 card-title mb-0" style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;">
                <i class="bi bi-card-list me-2" aria-hidden="true"></i>Katalog Master Pelanggan
            </h2>

            <!-- Search Form -->
            <form action="{{ route('master.pelanggan.index') }}" method="GET" class="d-flex align-items-center gap-1" style="width:260px;">
                <div class="input-group input-group-sm">
                    <input type="text" name="q" class="form-control" placeholder="Cari nama / kode / HP..." value="{{ $q }}" aria-label="Cari Pelanggan">
                    <button class="btn btn-outline-secondary" type="submit" aria-label="Cari"><i class="bi bi-search" aria-hidden="true"></i></button>
                    @if($q)
                        <a href="{{ route('master.pelanggan.index') }}" class="btn btn-outline-danger" title="Reset Pencarian"><i class="bi bi-x-lg" aria-hidden="true"></i></a>
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
                    <th style="width: 110px;" class="py-2" scope="col">Kode PLG</th>
                    <th class="py-2" scope="col">Nama Pelanggan</th>
                    <th style="width: 150px;" class="py-2" scope="col">No. Telepon</th>
                    <th class="py-2" scope="col">Alamat Domisili</th>
                    <th class="text-center py-2" style="width: 110px;" scope="col">Tingkat Harga</th>
                    <th class="text-center py-2 px-3" style="width: 100px;" scope="col">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pelanggans as $index => $plg)
                    @php
                        $badgeClass = 'badge-tier-umum';
                        if($plg->status_pelanggan === 'Member') $badgeClass = 'badge-tier-member';
                        elseif($plg->status_pelanggan === 'Rekan') $badgeClass = 'badge-tier-rekan';
                        elseif($plg->status_pelanggan === 'Motoris') $badgeClass = 'badge-tier-motoris';
                    @endphp
                    <tr>
                        <td class="text-center text-muted px-3" style="font-size:12px;">{{ $pelanggans->firstItem() + $index }}</td>
                        <td class="font-monospace fw-semibold" style="font-size:12px;">{{ $plg->kode_pelanggan }}</td>
                        <td class="fw-semibold" style="font-size:13px;color:var(--pb-text);">{{ $plg->nama_pelanggan }}</td>
                        <td style="font-size:12px;"><i class="bi bi-telephone me-1 text-muted" aria-hidden="true"></i>{{ $plg->no_telepon }}</td>
                        <td class="text-truncate" style="max-width:220px;font-size:12px;" title="{{ $plg->alamat }}">{{ $plg->alamat ?? '-' }}</td>
                        <td class="text-center">
                            <form action="{{ route('master.pelanggan.tier.update', $plg->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <select name="status_pelanggan" class="form-select form-select-sm tier-inline-select {{ $badgeClass }}" onchange="this.form.submit()" aria-label="Ubah tingkat harga {{ $plg->nama_pelanggan }}">
                                    @foreach(['Umum', 'Member', 'Rekan', 'Motoris'] as $tier)
                                        <option value="{{ $tier }}" @selected($plg->status_pelanggan === $tier)>{{ $tier }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </td>
                        <td class="text-center px-3">
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <button class="btn btn-sm btn-outline-primary py-1 px-2" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#modalEdit{{ $plg->id }}" 
                                        title="Edit Pelanggan">
                                    <i class="bi bi-pencil" aria-hidden="true"></i>
                                </button>
                                <form action="{{ route('master.pelanggan.destroy', $plg->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus pelanggan {{ addslashes($plg->nama_pelanggan) }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2" title="Hapus Pelanggan">
                                        <i class="bi bi-trash" aria-hidden="true"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="bi bi-people d-block mb-2" style="font-size:36px;opacity:0.3;" aria-hidden="true"></i>
                            <div style="font-size:13px;font-weight:600;color:var(--text-secondary);">Belum Ada Data Pelanggan</div>
                            <div style="font-size:12px;color:var(--text-muted);margin-bottom:12px;">Tambahkan pelanggan pertama toko Anda untuk mencatat transaksi member.</div>
                            <button class="btn btn-pb btn-sm px-3" data-bs-toggle="modal" data-bs-target="#modalTambah">
                                <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Tambah Pelanggan Baru
                            </button>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($pelanggans->hasPages())
        <div class="card-footer py-2 px-3 bg-transparent border-top">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div style="font-size:12px;color:var(--text-secondary);">
                    Menampilkan {{ $pelanggans->firstItem() }}–{{ $pelanggans->lastItem() }} dari {{ $pelanggans->total() }} pelanggan
                </div>
                <div>
                    {{ $pelanggans->links('pagination::bootstrap-5', ['class' => 'pagination-sm mb-0']) }}
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Modal Edit Elements (Outside table loop for clean DOM) -->
@foreach ($pelanggans as $plg)
    <div class="modal fade" id="modalEdit{{ $plg->id }}" tabindex="-1" aria-labelledby="titleModalEdit{{ $plg->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header py-3">
                    <h3 class="modal-title h6 fw-bold mb-0" id="titleModalEdit{{ $plg->id }}" style="color:var(--pb-text);">
                        <i class="bi bi-pencil-square me-2" aria-hidden="true"></i>Edit Data Pelanggan
                    </h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form action="{{ route('master.pelanggan.update', $plg->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body py-3">
                        <div class="mb-3">
                            <label for="kode_pelanggan_edit_{{ $plg->id }}" class="form-label fw-semibold" style="font-size:13px;">Kode Pelanggan (Otomatis Sistem)</label>
                            <input type="text" id="kode_pelanggan_edit_{{ $plg->id }}" class="form-control form-control-sm font-monospace" value="{{ $plg->kode_pelanggan }}" disabled>
                        </div>
                        <div class="mb-3">
                            <label for="nama_pelanggan_edit_{{ $plg->id }}" class="form-label fw-semibold" style="font-size:13px;">Nama Lengkap Pelanggan <span class="text-danger">*</span></label>
                            <input type="text" id="nama_pelanggan_edit_{{ $plg->id }}" name="nama_pelanggan" class="form-control form-control-sm" value="{{ $plg->nama_pelanggan }}" required maxlength="100" autocomplete="off">
                        </div>
                        <div class="mb-3">
                            <label for="no_telepon_edit_{{ $plg->id }}" class="form-label fw-semibold" style="font-size:13px;">No. Telepon / WhatsApp <span class="text-danger">*</span></label>
                            <input type="text" id="no_telepon_edit_{{ $plg->id }}" name="no_telepon" class="form-control form-control-sm" value="{{ $plg->no_telepon }}" required maxlength="20">
                        </div>
                        <div class="mb-3">
                            <label for="status_pelanggan_edit_{{ $plg->id }}" class="form-label fw-semibold" style="font-size:13px;">Status / Skema Harga POS <span class="text-danger">*</span></label>
                            <select id="status_pelanggan_edit_{{ $plg->id }}" name="status_pelanggan" class="form-select form-select-sm" required>
                                <option value="Umum" {{ $plg->status_pelanggan === 'Umum' ? 'selected' : '' }}>Umum (Harga Standar)</option>
                                <option value="Member" {{ $plg->status_pelanggan === 'Member' ? 'selected' : '' }}>Member (Diskon Member)</option>
                                <option value="Rekan" {{ $plg->status_pelanggan === 'Rekan' ? 'selected' : '' }}>Rekan (Harga Khusus Toko)</option>
                                <option value="Motoris" {{ $plg->status_pelanggan === 'Motoris' ? 'selected' : '' }}>Motoris (Harga Grosir / Sales)</option>
                            </select>
                        </div>
                        <div>
                            <label for="alamat_edit_{{ $plg->id }}" class="form-label fw-semibold" style="font-size:13px;">Alamat Domisili Lengkap</label>
                            <textarea id="alamat_edit_{{ $plg->id }}" name="alamat" class="form-control form-control-sm" rows="2" maxlength="500">{{ $plg->alamat }}</textarea>
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

<!-- Modal Tambah Pelanggan Baru -->
<div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="titleModalTambah" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-3">
                <h3 class="modal-title h6 fw-bold mb-0" id="titleModalTambah" style="color:var(--pb-text);">
                    <i class="bi bi-person-plus me-2" aria-hidden="true"></i>Tambah Pelanggan Baru
                </h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <form action="{{ route('master.pelanggan.store') }}" method="POST">
                @csrf
                <div class="modal-body py-3">
                    <div class="mb-3">
                        <label for="nama_pelanggan_tambah" class="form-label fw-semibold" style="font-size:13px;">Nama Lengkap Pelanggan <span class="text-danger">*</span></label>
                        <input type="text" id="nama_pelanggan_tambah" name="nama_pelanggan" class="form-control form-control-sm" placeholder="Contoh: Rian Member / Toko Barokah" required maxlength="100" autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <label for="no_telepon_tambah" class="form-label fw-semibold" style="font-size:13px;">No. Telepon / WhatsApp <span class="text-danger">*</span></label>
                        <input type="text" id="no_telepon_tambah" name="no_telepon" class="form-control form-control-sm" placeholder="Contoh: 081234567890" required maxlength="20" autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <label for="status_pelanggan_tambah" class="form-label fw-semibold" style="font-size:13px;">Status / Skema Harga POS <span class="text-danger">*</span></label>
                        <select id="status_pelanggan_tambah" name="status_pelanggan" class="form-select form-select-sm" required>
                            <option value="Umum" selected>Umum (Harga Standar)</option>
                            <option value="Member">Member (Diskon Member)</option>
                            <option value="Rekan">Rekan (Harga Khusus Toko)</option>
                            <option value="Motoris">Motoris (Harga Grosir / Sales)</option>
                        </select>
                        <div class="form-text mt-1" style="font-size:11px;">Status pelanggan menentukan kolom harga yang otomatis terisi saat transaksi POS Kasir.</div>
                    </div>
                    <div>
                        <label for="alamat_tambah" class="form-label fw-semibold" style="font-size:13px;">Alamat Domisili Lengkap</label>
                        <textarea id="alamat_tambah" name="alamat" class="form-control form-control-sm" rows="2" placeholder="Alamat jalan, nomor, RT/RW..." maxlength="500"></textarea>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-pb px-3"><i class="bi bi-check2-circle me-1" aria-hidden="true"></i>Simpan Pelanggan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
