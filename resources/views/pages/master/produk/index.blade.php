@extends('layouts.enterprise')
@section('title', 'Master Produk — ' . (auth()->user()?->toko?->nama_toko ?? 'ERPlay AI'))

@push('styles')
<style>
    /* ── Sticky Tier Container & Compact Price Cards ── */
    .sticky-tier-container {
        position: sticky;
        top: var(--topbar-h, 52px);
        z-index: 1020;
        background: var(--bg-body);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        padding: 8px 0;
        margin-bottom: 16px !important;
        border-bottom: 1px solid var(--border-light);
        transition: var(--theme-transition);
    }
    .market-price-card {
        background: var(--bg-card);
        border: 1px solid var(--border-light);
        border-radius: 10px;
        padding: 8px 12px;
        transition: border-color 0.15s ease, box-shadow 0.15s ease, var(--theme-transition);
        height: 100%;
    }
    .market-price-card:hover {
        border-color: var(--pb-mid);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }
    .price-card-label {
        font-size: 11px;
        font-weight: 600;
        color: var(--text-secondary);
        margin-bottom: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        transition: color 0.15s ease;
    }
    .price-card-val {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 15px;
        font-weight: 800;
        color: var(--pb-text);
        line-height: 1.15;
        letter-spacing: -0.01em;
        transition: color 0.15s ease;
    }

    /* WCAG AA High Contrast Color System */
    .color-umum { color: #15803D; }
    .color-member { color: #1D4ED8; }
    .color-rekan { color: #7E22CE; }
    .color-motoris { color: #B46B18; }

    [data-theme="dark"] .color-umum { color: #34D399; }
    [data-theme="dark"] .color-member { color: #60A5FA; }
    [data-theme="dark"] .color-rekan { color: #C084FC; }
    [data-theme="dark"] .color-motoris { color: #FBBF24; }

    .badge-tier {
        font-size: 10px;
        font-weight: 600;
        padding: 2px 6px;
        border-radius: 5px;
    }
    .badge-tier-umum { background: rgba(21, 128, 61, 0.1); color: #15803D; }
    .badge-tier-member { background: rgba(29, 78, 216, 0.1); color: #1D4ED8; }
    .badge-tier-rekan { background: rgba(126, 34, 206, 0.1); color: #7E22CE; }
    .badge-tier-motoris { background: rgba(180, 107, 24, 0.1); color: #B46B18; }

    [data-theme="dark"] .badge-tier-umum { background: rgba(52, 211, 153, 0.15); color: #34D399; }
    [data-theme="dark"] .badge-tier-member { background: rgba(96, 165, 250, 0.15); color: #60A5FA; }
    [data-theme="dark"] .badge-tier-rekan { background: rgba(192, 132, 252, 0.15); color: #C084FC; }
    [data-theme="dark"] .badge-tier-motoris { background: rgba(251, 191, 36, 0.15); color: #FBBF24; }

    .table-produk-hover tbody tr:hover {
        background-color: var(--bg-card-hover) !important;
    }
</style>
@endpush

@section('content')
<!-- Breadcrumb Navigation -->
<nav class="erp-breadcrumb d-none d-md-block" aria-label="Breadcrumb">
    <a href="{{ url('/dashboard') }}" aria-label="Beranda Dashboard"><i class="bi bi-house-door"></i></a>
    <i class="bi bi-chevron-right mx-1" style="font-size:9px;" aria-hidden="true"></i>
    <span aria-current="page">Master Produk</span>
</nav>

<!-- Page Title & Toolbar -->
<div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
    <div>
        <h1 class="h4 mb-0 fw-bold" style="font-family:'Plus Jakarta Sans',sans-serif;color:var(--pb-text);letter-spacing:-0.02em;">
            <i class="bi bi-box-seam me-2" aria-hidden="true"></i>Master Data Produk
        </h1>
        <p class="mb-0 mt-1" style="color:var(--text-secondary);font-size:13px;">
            Kelola katalog produk, varian barcode, stok minimum, dan skema harga bertingkat.
        </p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('master.produk.import') }}" class="btn btn-sm btn-outline-secondary" title="Import Data Produk via CSV">
            <i class="bi bi-cloud-upload me-1" aria-hidden="true"></i>Import CSV
        </a>
        <a id="btn-export-quick"
           href="{{ route('master.produk.export-csv', array_filter(['search' => $search, 'nama_kategori' => $namaKategoriFilter, 'nama_kelompok' => $namaKelompokFilter])) }}"
           class="btn btn-sm btn-outline-success" title="Export Data Produk ke CSV">
            <i class="bi bi-file-earmark-spreadsheet me-1" aria-hidden="true"></i>Export CSV
        </a>
        <a href="{{ route('master.produk.panduan-export') }}" class="btn btn-sm btn-outline-secondary" title="Panduan & Download Template">
            <i class="bi bi-book me-1" aria-hidden="true"></i>Panduan & Template
        </a>
        <a href="{{ route('master.produk.create') }}" class="btn btn-sm btn-pb">
            <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Tambah Produk
        </a>
    </div>
</div>

<!-- 1. Live Tier Price Average Compact Widgets (Sticky on Scroll) -->
<div class="sticky-tier-container">
    <div class="row g-2" id="counter-widget"
         data-avg-umum="{{ $avgUmum }}"
         data-avg-member="{{ $avgMember }}"
         data-avg-rekan="{{ $avgRekan }}"
         data-avg-motoris="{{ $avgMotoris }}">
        <div class="col-6 col-lg-3">
            <div class="market-price-card">
                <div class="d-flex align-items-center justify-content-between mb-1 gap-1">
                    <span class="price-card-label" id="label-umum">AVG Harga Umum</span>
                    <span class="badge-tier badge-tier-umum">Umum</span>
                </div>
                <div class="price-card-val color-umum" id="val-umum">Rp {{ number_format($avgUmum, 0, ',', '.') }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="market-price-card">
                <div class="d-flex align-items-center justify-content-between mb-1 gap-1">
                    <span class="price-card-label" id="label-member">AVG Harga Member</span>
                    <span class="badge-tier badge-tier-member">Member</span>
                </div>
                <div class="price-card-val color-member" id="val-member">Rp {{ number_format($avgMember, 0, ',', '.') }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="market-price-card">
                <div class="d-flex align-items-center justify-content-between mb-1 gap-1">
                    <span class="price-card-label" id="label-rekan">AVG Harga Rekan</span>
                    <span class="badge-tier badge-tier-rekan">Rekan</span>
                </div>
                <div class="price-card-val color-rekan" id="val-rekan">Rp {{ number_format($avgRekan, 0, ',', '.') }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="market-price-card">
                <div class="d-flex align-items-center justify-content-between mb-1 gap-1">
                    <span class="price-card-label" id="label-motoris">AVG Harga Motoris</span>
                    <span class="badge-tier badge-tier-motoris">Motoris</span>
                </div>
                <div class="price-card-val color-motoris" id="val-motoris">Rp {{ number_format($avgMotoris, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
</div>

<!-- 2. Main Product Catalog Table Card -->
<div class="card card-erp mb-4">
    <div class="card-header py-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <h2 class="h6 card-title mb-0" style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;">
                <i class="bi bi-list-columns-reverse me-2" aria-hidden="true"></i>Katalog Master Produk
            </h2>

            <!-- Single GET Filter & Search Form -->
            <form id="filterForm" method="GET" action="{{ route('master.produk.index') }}" class="d-flex flex-wrap align-items-center gap-2">
                <!-- Filter Kelompok -->
                <select id="filter-kelompok" name="nama_kelompok" class="form-select form-select-sm" style="width:160px;" onchange="this.form.submit()" aria-label="Filter Kelompok Produk">
                    <option value="">-- semua Kelompok --</option>
                    @foreach($kelompokList as $kel)
                        <option value="{{ $kel->nama_kelompok }}" {{ $namaKelompokFilter === $kel->nama_kelompok ? 'selected' : '' }}>
                            {{ $kel->nama_kelompok }}
                        </option>
                    @endforeach
                </select>

                <!-- Filter Kategori -->
                <select id="filter-kategori" name="nama_kategori" class="form-select form-select-sm" style="width:160px;" onchange="this.form.submit()" aria-label="Filter Kategori Produk">
                    <option value="">-- semua Kategori --</option>
                    @foreach($categories as $kat)
                        <option value="{{ $kat->nama_kategori }}" {{ $namaKategoriFilter === $kat->nama_kategori ? 'selected' : '' }}>
                            {{ $kat->nama_kategori }}
                        </option>
                    @endforeach
                </select>

                <!-- Search Input -->
                <div class="input-group input-group-sm" style="width:220px;">
                    <input type="text" id="search-input" name="search" class="form-control" placeholder="Cari nama / barcode..." value="{{ $search }}" aria-label="Cari Produk">
                    <button class="btn btn-outline-secondary" type="submit" aria-label="Cari">
                        <i class="bi bi-search" aria-hidden="true"></i>
                    </button>
                </div>

                <!-- Reset Filters Button -->
                @if(!empty($search) || !empty($namaKategoriFilter) || !empty($namaKelompokFilter))
                <a href="{{ route('master.produk.index') }}" class="btn btn-sm btn-outline-danger" title="Hapus semua filter">
                    <i class="bi bi-x-circle me-1" aria-hidden="true"></i>Reset
                </a>
                @endif
            </form>
        </div>
    </div>

    <!-- Product Table -->
    <div class="table-responsive">
        <table class="table table-sm align-middle table-produk-hover mb-0">
            <thead>
                <tr>
                    <th class="text-center py-2 px-3" style="width:50px;" scope="col">No</th>
                    <th class="py-2" style="width:130px;" scope="col">Barcode SKU</th>
                    <th class="py-2" scope="col">Nama Produk</th>
                    <th class="py-2" scope="col">Kategori & Kelompok</th>
                    <th class="py-2 text-end" scope="col">Harga Modal</th>
                    <th class="py-2 text-end" scope="col">Harga Umum</th>
                    <th class="py-2 text-center" style="width:90px;" scope="col">Stok</th>
                    <th class="py-2 text-center px-3" style="width:100px;" scope="col">Aksi</th>
                </tr>
            </thead>
            <tbody id="product-table-body">
                @forelse ($produk as $index => $prod)
                    @php
                        $isLowStock = $prod->stok <= $prod->stok_minimum;
                    @endphp
                    <tr class="hover-product" style="cursor:pointer;"
                        data-name="{{ $prod->nama_produk }}"
                        data-umum="{{ $prod->harga_jual_umum }}"
                        data-member="{{ $prod->harga_jual_member }}"
                        data-rekan="{{ $prod->harga_jual_rekan }}"
                        data-motoris="{{ $prod->harga_jual_motoris }}">
                        <td class="text-center text-muted px-3">{{ $produk->firstItem() + $index }}</td>
                        <td class="font-monospace" style="font-size:12px;font-weight:600;">{{ $prod->barcode }}</td>
                        <td class="fw-semibold">
                            {{ $prod->nama_produk }}
                            @if($isLowStock)
                                <span class="badge bg-danger ms-1" style="font-size:10px;" title="Stok Minimum: {{ $prod->stok_minimum }}">Stok Menipis</span>
                            @endif
                        </td>
                        <td>
                            <span style="font-size:12px;color:var(--text-primary);">
                                {{ $prod->kategori->nama_kategori ?? '-' }}
                                <span style="color:var(--text-muted);font-size:11px;">({{ $prod->kategori->kelompok->nama_kelompok ?? '-' }})</span>
                            </span>
                        </td>
                        <td class="text-end" style="font-size:12px;color:var(--text-secondary);font-weight:500;">
                            Rp {{ number_format($prod->harga_modal, 0, ',', '.') }}
                        </td>
                        <td class="text-end fw-bold" style="font-size:13px;color:var(--pb-text);">
                            Rp {{ number_format($prod->harga_jual_umum, 0, ',', '.') }}
                        </td>
                        <td class="text-center fw-bold {{ $isLowStock ? 'text-danger' : 'text-success' }}">
                            {{ $prod->stok }}
                            <span style="font-size:11px;font-weight:normal;color:var(--text-secondary);">{{ $prod->satuan }}</span>
                        </td>
                        <td class="text-center px-3">
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <a href="{{ route('master.produk.edit', $prod->id) }}" class="btn btn-sm btn-outline-primary py-1 px-2" title="Edit Produk">
                                    <i class="bi bi-pencil" aria-hidden="true"></i>
                                </a>
                                <form action="{{ route('master.produk.destroy', $prod->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus produk {{ addslashes($prod->nama_produk) }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2" title="Hapus Produk">
                                        <i class="bi bi-trash" aria-hidden="true"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            <i class="bi bi-box-seam d-block mb-3" style="font-size:42px;opacity:0.3;" aria-hidden="true"></i>
                            <div style="font-size:14px;font-weight:600;color:var(--text-secondary);">Belum Ada Data Produk</div>
                            <div style="font-size:12px;color:var(--text-muted);margin-bottom:16px;">Tidak ada produk yang sesuai dengan kriteria pencarian / filter Anda.</div>
                            <a href="{{ route('master.produk.create') }}" class="btn btn-pb btn-sm px-3">
                                <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Tambah Produk Baru
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination Footer -->
    <div class="card-footer py-2 px-3" id="paginationContainer">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            @if ($produk->hasPages())
                <div style="font-size:12px;color:var(--text-secondary);">
                    Menampilkan {{ $produk->firstItem() }}–{{ $produk->lastItem() }} dari {{ $produk->total() }} produk
                </div>
            @else
                <div style="font-size:12px;color:var(--text-secondary);">
                    Total: {{ $produk->total() }} produk
                </div>
            @endif

            <div class="d-flex align-items-center gap-1" style="font-size:12px;color:var(--text-secondary);">
                <span>Per halaman:</span>
                @foreach ([10, 25, 50, 100] as $opt)
                    @php $isActive = ((int) $perPage) === $opt; @endphp
                    <a href="{{ route('master.produk.index', array_merge(
                            request()->only(['search', 'nama_kategori', 'nama_kelompok']),
                            ['per_page' => $opt, 'page' => 1]
                        )) }}"
                       class="px-2 py-1 text-decoration-none rounded d-inline-flex align-items-center {{ $isActive ? 'fw-bold bg-primary text-white' : '' }}"
                       style="font-size:12px;min-height:24px;{{ $isActive ? '' : 'color:var(--text-muted);' }}">
                        {{ $opt }}
                    </a>
                @endforeach
            </div>

            <div class="ms-auto">
                {{ $produk->links('pagination::bootstrap-5', ['class' => 'pagination-sm mb-0']) }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
  function formatRp(angka) {
      if (!angka || isNaN(angka)) angka = 0;
      return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);
  }

  // Hover effect: update average pricing widget headers with specific product details
  document.getElementById('product-table-body').addEventListener('mouseover', function(e) {
      const tr = e.target.closest('.hover-product');
      if (!tr) return;
      document.getElementById('label-umum').innerText    = `Umum (${tr.dataset.name})`;
      document.getElementById('label-member').innerText  = `Member (${tr.dataset.name})`;
      document.getElementById('label-rekan').innerText   = `Rekan (${tr.dataset.name})`;
      document.getElementById('label-motoris').innerText = `Motoris (${tr.dataset.name})`;
      document.getElementById('val-umum').innerText      = formatRp(tr.dataset.umum);
      document.getElementById('val-member').innerText    = formatRp(tr.dataset.member);
      document.getElementById('val-rekan').innerText     = formatRp(tr.dataset.rekan);
      document.getElementById('val-motoris').innerText   = formatRp(tr.dataset.motoris);
  });

  document.getElementById('product-table-body').addEventListener('mouseout', function(e) {
      const tr = e.target.closest('.hover-product');
      if (!tr) return;
      const w = document.getElementById('counter-widget');
      document.getElementById('label-umum').innerText    = 'AVG Harga Umum';
      document.getElementById('val-umum').innerText      = formatRp(w.dataset.avgUmum);
      document.getElementById('label-member').innerText  = 'AVG Harga Member';
      document.getElementById('val-member').innerText    = formatRp(w.dataset.avgMember);
      document.getElementById('label-rekan').innerText   = 'AVG Harga Rekan';
      document.getElementById('val-rekan').innerText     = formatRp(w.dataset.avgRekan);
      document.getElementById('label-motoris').innerText = 'AVG Harga Motoris';
      document.getElementById('val-motoris').innerText   = formatRp(w.dataset.avgMotoris);
  });
</script>
@endpush
