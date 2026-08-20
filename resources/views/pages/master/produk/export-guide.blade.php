@extends('layouts.enterprise')
@section('title', 'Export & Panduan Template Produk — ' . (auth()->user()?->toko?->nama_toko ?? 'ERPlay AI'))

@push('styles')
<style>
    /* Metric summary cards without side-stripe borders */
    .card-export-stat {
        background: var(--bg-card);
        border: 1px solid var(--border-light);
        border-radius: 12px;
        transition: var(--theme-transition);
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }

    .card-export-stat .stat-label {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-secondary);
    }

    .card-export-stat .stat-value {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-weight: 800;
        font-size: 1.75rem;
        letter-spacing: -0.03em;
        line-height: 1.2;
    }
</style>
@endpush

@section('content')
<!-- Breadcrumb Navigation -->
<nav class="erp-breadcrumb d-none d-md-block" aria-label="Breadcrumb">
    <a href="{{ url('/dashboard') }}" aria-label="Beranda Dashboard"><i class="bi bi-house-door"></i></a>
    <i class="bi bi-chevron-right mx-1" style="font-size:9px;" aria-hidden="true"></i>
    <a href="{{ route('master.produk.index') }}">Master Produk</a>
    <i class="bi bi-chevron-right mx-1" style="font-size:9px;" aria-hidden="true"></i>
    <span aria-current="page">Export & Panduan Template</span>
</nav>

<!-- Page Header & Action Bar -->
<div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
    <div>
        <h1 class="h4 mb-0 fw-bold" style="font-family:'Plus Jakarta Sans',sans-serif;color:var(--pb-text);letter-spacing:-0.02em;">
            <i class="bi bi-file-earmark-spreadsheet me-2" aria-hidden="true"></i>Export & Panduan Template Produk
        </h1>
        <p class="mb-0 mt-1" style="color:var(--text-secondary);font-size:13px;">
            Unduh seluruh katalog produk ke file spreadsheet CSV atau gunakan template standar untuk impor massal.
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('master.produk.import') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-cloud-upload me-1" aria-hidden="true"></i>Import Produk CSV
        </a>
        <a href="{{ route('master.produk.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Kembali ke Produk
        </a>
    </div>
</div>

<!-- 4 Top Stat Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card card-export-stat p-3 h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="stat-label">Total Produk Terdaftar</span>
                <span class="badge badge-subtle-success"><i class="bi bi-box-seam me-1" aria-hidden="true"></i>Siap Export</span>
            </div>
            <div class="stat-value text-success mb-1">{{ number_format($totalProduk) }}</div>
            <span class="text-secondary" style="font-size:12px;">Item produk di katalog toko</span>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card card-export-stat p-3 h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="stat-label">Stok Rendah Warning</span>
                <span class="badge badge-subtle-warning"><i class="bi bi-exclamation-triangle me-1" aria-hidden="true"></i>Perlu Restok</span>
            </div>
            <div class="stat-value text-warning mb-1">{{ number_format($stokRendah) }}</div>
            <span class="text-secondary" style="font-size:12px;">Stok di bawah batas minimum</span>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card card-export-stat p-3 h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="stat-label">Kategori Tersedia</span>
                <span class="badge badge-subtle-info"><i class="bi bi-tags me-1" aria-hidden="true"></i>Aktif</span>
            </div>
            <div class="stat-value text-info mb-1">{{ number_format($categories->count()) }}</div>
            <span class="text-secondary" style="font-size:12px;">Kelompok & sub-kategori produk</span>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Left Column: Export CSV Filter Form -->
    <div class="col-12 col-lg-7">
        <div class="card card-erp h-100">
            <div class="card-header py-3">
                <h2 class="h6 card-title mb-0" style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;">
                    <i class="bi bi-download me-2 text-success" aria-hidden="true"></i>Export Data Produk ke Format CSV
                </h2>
            </div>
            <div class="card-body p-4">
                <p class="text-secondary mb-3" style="font-size:13px;">
                    Unduh file <strong>CSV</strong> yang berisi informasi lengkap seluruh produk toko Anda. File dapat langsung dibuka di <strong>Microsoft Excel</strong>, <strong>Google Sheets</strong>, atau aplikasi analisis spreadsheet lainnya.
                </p>

                <!-- Filter Export Form -->
                <form id="form-export" action="{{ route('master.produk.export-csv') }}" method="GET">
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label for="nama_kategori" class="form-label fw-semibold" style="font-size:13px;">Filter Kategori <span class="text-muted fw-normal">(opsional)</span></label>
                            <select name="nama_kategori" id="nama_kategori" class="form-select form-select-sm">
                                <option value="">— Semua Kategori (Export Semua) —</option>
                                @foreach($categories as $kat)
                                    <option value="{{ $kat->nama_kategori }}">
                                        {{ $kat->nama_kategori }} ({{ $kat->kelompok->nama_kelompok ?? '-' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="search" class="form-label fw-semibold" style="font-size:13px;">Cari Nama / Barcode <span class="text-muted fw-normal">(opsional)</span></label>
                            <input type="text" name="search" id="search" class="form-control form-control-sm" placeholder="Contoh: Indomie atau 89930033">
                        </div>
                    </div>

                    <div class="alert alert-info py-2 px-3 mb-3" style="font-size:12px;">
                        <i class="bi bi-info-circle me-1" aria-hidden="true"></i>
                        <strong>Petunjuk:</strong> Jika filter dikosongkan, <strong>semua data produk</strong> akan diunduh.
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <button type="submit" id="btn-export" class="btn btn-success btn-sm px-4 py-2 fw-bold">
                            <i class="bi bi-file-earmark-spreadsheet me-1" aria-hidden="true"></i>Download CSV Sekarang
                        </button>
                        <span id="export-loading" class="text-secondary" style="font-size:12px;display:none;">
                            <i class="bi bi-arrow-repeat spin me-1" aria-hidden="true"></i>Menyiapkan file...
                        </span>
                    </div>
                </form>

                <hr class="my-4">

                <div class="fw-bold mb-3" style="font-size:13px;color:var(--pb-text);">
                    <i class="bi bi-list-check me-1 text-primary" aria-hidden="true"></i>Kolom yang Disertakan dalam Berkas Export:
                </div>
                <div class="row g-2">
                    @php
                        $cols = [
                            ['No','Nomor urut baris'],
                            ['Barcode','Kode unik barcode produk'],
                            ['Nama Produk','Nama lengkap barang'],
                            ['Kelompok','Kelompok/departemen barang'],
                            ['Kategori','Sub-kategori produk'],
                            ['Satuan','Pcs, Pack, Box, Dus, dll'],
                            ['Harga Modal','HPP / Harga beli'],
                            ['Harga Jual Umum','Harga konsumen Umum'],
                            ['Harga Jual Member','Harga tier Member'],
                            ['Harga Jual Rekan','Harga tier Rekan'],
                            ['Harga Jual Motoris','Harga tier Motoris'],
                            ['Stok','Jumlah stok fisik'],
                            ['Stok Minimum','Batas warning stok rendah'],
                            ['Status Stok','Normal / Stok Rendah'],
                        ];
                    @endphp
                    @foreach($cols as $col)
                        <div class="col-12 col-md-6">
                            <div class="p-2 rounded border" style="background:var(--bg-card-hover);font-size:12px;">
                                <i class="bi bi-check-circle-fill text-success me-1" aria-hidden="true"></i>
                                <span class="fw-semibold">{{ $col[0] }}</span>
                                <span class="text-secondary">— {{ $col[1] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Download Template & Interactive Guide -->
    <div class="col-12 col-lg-5">
        <div class="card card-erp mb-4">
            <div class="card-header py-3">
                <h2 class="h6 card-title mb-0" style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;">
                    <i class="bi bi-file-earmark-arrow-down me-2 text-primary" aria-hidden="true"></i>Download Template CSV Standards
                </h2>
            </div>
            <div class="card-body p-4">
                <p class="text-secondary mb-3" style="font-size:13px;">
                    Download template CSV yang disiapkan dengan format kolom baku dan baris contoh untuk mempermudah pendaftaran produk baru secara masal.
                </p>
                <a href="{{ route('master.produk.download-template') }}" class="btn btn-primary btn-sm px-4 py-2 fw-bold mb-3">
                    <i class="bi bi-file-earmark-arrow-down me-1" aria-hidden="true"></i>Download Template CSV
                </a>

                <div class="p-3 rounded border" style="background:var(--bg-card-hover);font-size:12px;">
                    <div class="fw-bold mb-1" style="color:var(--pb-text);">Header Kolom Dalam Template:</div>
                    <code style="font-size:11px;color:var(--pb-accent);word-break:break-all;">
                        barcode; nama_produk; nama_kategori; satuan; harga_modal; harga_jual_umum; harga_jual_member; harga_jual_rekan; harga_jual_motoris; stok; stok_minimum
                    </code>
                </div>
            </div>
        </div>

        <!-- Interactive FAQ Accordion Guide -->
        <div class="card card-erp">
            <div class="card-header py-3">
                <h2 class="h6 card-title mb-0" style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;">
                    <i class="bi bi-book me-2 text-warning" aria-hidden="true"></i>Panduan Langkah Demi Langkah
                </h2>
            </div>
            <div class="card-body p-0">
                <div class="accordion accordion-flush" id="accordionPanduan">

                    <div class="accordion-item border-bottom">
                        <h3 class="accordion-header">
                            <button class="accordion-button py-3 px-3 collapsed fw-semibold" style="font-size:13px;" type="button" data-bs-toggle="collapse" data-bs-target="#p1">
                                <i class="bi bi-cloud-download me-2 text-success" aria-hidden="true"></i>Cara Export Data Produk
                            </button>
                        </h3>
                        <div id="p1" class="accordion-collapse collapse" data-bs-parent="#accordionPanduan">
                            <div class="accordion-body py-3 px-3" style="font-size:13px;color:var(--text-primary);">
                                <ol class="mb-0 ps-3" style="line-height:1.6;">
                                    <li class="mb-1.5">Pilih filter Kategori atau cari nama barang jika ingin mengunduh subset produk tertentu.</li>
                                    <li class="mb-1.5">Klik tombol <strong>"Download CSV Sekarang"</strong>.</li>
                                    <li class="mb-1.5">Buka berkas di Excel: Klik kanan file ➔ <em>Open With ➔ Microsoft Excel</em>.</li>
                                    <li>Jika data muncul di 1 kolom: Pilih Kolom A ➔ Menu <em>Data ➔ Text to Columns ➔ Delimited ➔ Semicolon (;)</em>.</li>
                                </ol>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item border-bottom">
                        <h3 class="accordion-header">
                            <button class="accordion-button py-3 px-3 collapsed fw-semibold" style="font-size:13px;" type="button" data-bs-toggle="collapse" data-bs-target="#p2">
                                <i class="bi bi-file-earmark-excel me-2 text-primary" aria-hidden="true"></i>Membuka & Mengedit File di Excel
                            </button>
                        </h3>
                        <div id="p2" class="accordion-collapse collapse" data-bs-parent="#accordionPanduan">
                            <div class="accordion-body py-3 px-3" style="font-size:13px;color:var(--text-primary);">
                                <ul class="mb-0 ps-3" style="line-height:1.6;">
                                    <li class="mb-1.5">Pastikan baris judul (header) pada baris pertama tidak diubah.</li>
                                    <li class="mb-1.5">Pastikan format angka harga modal & harga jual hanya berisi angka tanpa tanda titik/koma (misal: <code style="background:var(--bg-input);color:#E11D48;padding:2px 6px;border-radius:4px;font-weight:700;border:1px solid var(--border-light);">15000</code>).</li>
                                    <li>Format file saat menyimpan kembali di Excel: Pilih <strong>CSV (Comma delimited) (*.csv)</strong> atau <strong>CSV UTF-8</strong>.</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
