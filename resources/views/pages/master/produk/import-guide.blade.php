@extends('layouts.enterprise')
@section('title', 'Import Produk via CSV — ' . (auth()->user()?->toko?->nama_toko ?? 'ERPlay AI'))

@push('styles')
<style>
    /* Step indicator pill */
    .step-badge {
        width: 28px; height: 28px;
        border-radius: 50%;
        background: var(--pb-dark);
        color: #FFFFFF;
        font-weight: 700;
        font-size: 12px;
        display: inline-flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }

    .step-card {
        background: var(--bg-card-hover);
        border: 1px solid var(--border-light);
        border-radius: 10px;
        padding: 14px 16px;
        margin-bottom: 14px;
        transition: var(--theme-transition);
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
    <span aria-current="page">Import Produk via CSV</span>
</nav>

<!-- Page Header & Action Bar -->
<div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
    <div>
        <h1 class="h4 mb-0 fw-bold" style="font-family:'Plus Jakarta Sans',sans-serif;color:var(--pb-text);letter-spacing:-0.02em;">
            <i class="bi bi-cloud-upload me-2" aria-hidden="true"></i>Import Master Produk via CSV
        </h1>
        <p class="mb-0 mt-1" style="color:var(--text-secondary);font-size:13px;">
            Unggah berkas spreadsheet CSV untuk memperbarui atau mengimpor ribuan katalog produk toko secara massal.
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('master.produk.panduan-export') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-file-earmark-spreadsheet me-1" aria-hidden="true"></i>Export & Template
        </a>
        <a href="{{ route('master.produk.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Kembali ke Produk
        </a>
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

@if($errors->any())
    <div class="alert alert-danger mb-3" role="alert">
        <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1" aria-hidden="true"></i>Validasi Gagal:</div>
        @foreach($errors->all() as $error)
            <p class="mb-0">{{ $error }}</p>
        @endforeach
    </div>
@endif

@if(session('import_info'))
    <div class="alert alert-info alert-dismissible fade show mb-3" style="max-height:220px;overflow-y:auto;" role="alert">
        <div class="fw-bold mb-1"><i class="bi bi-info-circle me-1" aria-hidden="true"></i>Info Baris yang Dilewati:</div>
        <ul class="mb-0 ps-3">
            @foreach(session('import_info') as $info)
                <li>{{ $info }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
@endif

@if(session('import_errors'))
    <div class="alert alert-warning alert-dismissible fade show mb-3" style="max-height:220px;overflow-y:auto;" role="alert">
        <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle me-1" aria-hidden="true"></i>Detail Catatan Per Baris:</div>
        <ul class="mb-0 ps-3">
            @foreach(session('import_errors') as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
@endif

<div class="row g-4 mb-4">
    <!-- Left Column: Upload Form & Step Process -->
    <div class="col-12 col-lg-7">
        <div class="card card-erp h-100">
            <div class="card-header py-3">
                <h2 class="h6 card-title mb-0" style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;">
                    <i class="bi bi-cloud-arrow-up me-2 text-primary" aria-hidden="true"></i>Upload File Spreadsheet CSV
                </h2>
            </div>
            <div class="card-body p-4">

                <!-- Visual Step Process -->
                <div class="d-flex align-items-center mb-4 gap-2 flex-wrap">
                    <div class="d-flex align-items-center gap-2">
                        <span class="step-badge">1</span>
                        <span class="fw-semibold" style="font-size:12px;color:var(--pb-text);">Download Template</span>
                    </div>
                    <i class="bi bi-chevron-right text-muted" style="font-size:10px;" aria-hidden="true"></i>
                    <div class="d-flex align-items-center gap-2">
                        <span class="step-badge">2</span>
                        <span class="fw-semibold" style="font-size:12px;color:var(--pb-text);">Isi Data Excel</span>
                    </div>
                    <i class="bi bi-chevron-right text-muted" style="font-size:10px;" aria-hidden="true"></i>
                    <div class="d-flex align-items-center gap-2">
                        <span class="step-badge">3</span>
                        <span class="fw-semibold" style="font-size:12px;color:var(--pb-text);">Upload File</span>
                    </div>
                </div>

                <!-- Step 1 Card -->
                <div class="step-card">
                    <div class="fw-bold mb-1" style="font-size:13px;color:var(--pb-text);">
                        <i class="bi bi-file-earmark-arrow-down me-1 text-primary" aria-hidden="true"></i>Langkah 1: Unduh Format Template CSV
                    </div>
                    <p class="text-secondary mb-2" style="font-size:12px;">
                        Gunakan file template CSV yang sudah dikonfigurasi dengan nama kolom yang sesuai.
                    </p>
                    <a href="{{ route('master.produk.download-template') }}" class="btn btn-sm btn-outline-primary fw-semibold">
                        <i class="bi bi-download me-1" aria-hidden="true"></i>Download Template CSV
                    </a>
                </div>

                <!-- Step 2 Card -->
                <div class="step-card">
                    <div class="fw-bold mb-1" style="font-size:13px;color:var(--pb-text);">
                        <i class="bi bi-pencil-square me-1 text-success" aria-hidden="true"></i>Langkah 2: Kelola Data di Spreadsheet
                    </div>
                    <p class="text-secondary mb-0" style="font-size:12px;">
                        Buka template di Excel/Google Sheets. Hapus baris petunjuk yang diawali tanda <code>#</code>, isi data produk Anda, lalu simpan sebagai file <code>.csv</code> (Pemisah titik koma <code>;</code>).
                        <br><strong>Kategori & Kelompok baru akan dibuat otomatis oleh sistem.</strong>
                    </p>
                </div>

                <!-- Step 3 Upload Card -->
                <div class="step-card">
                    <div class="fw-bold mb-2" style="font-size:13px;color:var(--pb-text);">
                        <i class="bi bi-cloud-upload me-1 text-warning" aria-hidden="true"></i>Langkah 3: Unggah Berkas CSV Produk
                    </div>

                    <form id="form-import" action="{{ route('master.produk.import.process') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label for="csv_file" class="form-label fw-semibold" style="font-size:13px;">Pilih File Berkas CSV <span class="text-danger">*</span></label>
                            <input type="file" name="csv_file" id="csv_file" class="form-control form-control-sm" accept=".csv,.txt" required>
                            <div class="form-text" style="font-size:11px;">Format berkas: <code>.csv</code> — Ukuran Maksimum: 5 MB</div>
                        </div>

                        <!-- Live File Preview -->
                        <div id="file-preview" class="mb-3" style="display:none;">
                            <div class="fw-semibold mb-1" style="font-size:12px;">
                                <i class="bi bi-eye me-1" aria-hidden="true"></i>Preview 5 Baris Pertama File:
                            </div>
                            <div class="table-responsive rounded border" style="max-height:180px;overflow-y:auto;">
                                <table class="table table-sm align-middle table-hover mb-0" style="font-size:11px;" id="preview-table">
                                    <thead id="preview-thead"></thead>
                                    <tbody id="preview-tbody"></tbody>
                                </table>
                            </div>
                            <div class="mt-1 text-muted" style="font-size:11px;" id="preview-info"></div>
                        </div>

                        <div class="alert alert-info py-2 px-3 mb-3" style="font-size:12px;">
                            <i class="bi bi-info-circle me-1" aria-hidden="true"></i>
                            <strong>Aturan Impor Sistem:</strong><br>
                            • Barcode sudah terdaftar ➔ Data produk <strong>diperbarui (update)</strong>.<br>
                            • Barcode baru ➔ Produk baru <strong>ditambahkan (insert)</strong>.<br>
                            • Barcode/Nama Kosong ➔ Baris dilewati secara otomatis.
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <button type="submit" id="btn-import" class="btn btn-pb btn-sm px-4 py-2 fw-bold" disabled>
                                <i class="bi bi-cloud-upload me-1" aria-hidden="true"></i>Proses Import CSV
                            </button>
                            <span id="import-loading" class="text-secondary" style="font-size:12px;display:none;">
                                <i class="bi bi-arrow-repeat spin me-1" aria-hidden="true"></i>Memproses file...
                            </span>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: CSV Column Format Specifications -->
    <div class="col-12 col-lg-5">
        <div class="card card-erp mb-4">
            <div class="card-header py-3">
                <h2 class="h6 card-title mb-0" style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;">
                    <i class="bi bi-table me-2 text-info" aria-hidden="true"></i>Format Kolom CSV
                </h2>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0" style="font-size:12px;">
                        <thead>
                            <tr>
                                <th class="ps-3 py-2" scope="col">Nama Kolom Header</th>
                                <th class="py-2 text-center" style="width:70px;" scope="col">Wajib</th>
                                <th class="pe-3 py-2" scope="col">Format Data / Contoh</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="ps-3 font-monospace fw-semibold">barcode</td>
                                <td class="text-center"><span class="badge bg-danger text-white" style="font-size:10px;">Ya</span></td>
                                <td class="pe-3 text-secondary" style="font-size:11px;">Angka unik EAN-13, min 3 digit (misal: <code>89930051</code>)</td>
                            </tr>
                            <tr>
                                <td class="ps-3 font-monospace fw-semibold">nama_produk</td>
                                <td class="text-center"><span class="badge bg-danger text-white" style="font-size:10px;">Ya</span></td>
                                <td class="pe-3 text-secondary" style="font-size:11px;">Nama barang (misal: <code>Indomie Goreng 85g</code>)</td>
                            </tr>
                            <tr>
                                <td class="ps-3 font-monospace fw-semibold">nama_kategori</td>
                                <td class="text-center"><span class="badge bg-secondary text-white" style="font-size:10px;">Opsional</span></td>
                                <td class="pe-3 text-secondary" style="font-size:11px;">Sub-kategori produk (misal: <code>Mie Instan</code>). Dibuat otomatis jika belum ada.</td>
                            </tr>
                            <tr>
                                <td class="ps-3 font-monospace fw-semibold">satuan</td>
                                <td class="text-center"><span class="badge bg-secondary text-white" style="font-size:10px;">Opsional</span></td>
                                <td class="pe-3 text-secondary" style="font-size:11px;">Pcs, Pack, Box, Dus, Botol (Default: <code>Pcs</code>)</td>
                            </tr>
                            <tr>
                                <td class="ps-3 font-monospace fw-semibold">harga_modal</td>
                                <td class="text-center"><span class="badge bg-secondary text-white" style="font-size:10px;">Opsional</span></td>
                                <td class="pe-3 text-secondary" style="font-size:11px;">HPP harga modal (Default: <code>0</code>)</td>
                            </tr>
                            <tr>
                                <td class="ps-3 font-monospace fw-semibold">harga_jual_umum</td>
                                <td class="text-center"><span class="badge bg-secondary text-white" style="font-size:10px;">Opsional</span></td>
                                <td class="pe-3 text-secondary" style="font-size:11px;">Harga jual konsumen umum (Default: <code>0</code>)</td>
                            </tr>
                            <tr>
                                <td class="ps-3 font-monospace fw-semibold">harga_jual_member</td>
                                <td class="text-center"><span class="badge bg-secondary text-white" style="font-size:10px;">Opsional</span></td>
                                <td class="pe-3 text-secondary" style="font-size:11px;">Harga khusus pelanggan Member</td>
                            </tr>
                            <tr>
                                <td class="ps-3 font-monospace fw-semibold">harga_jual_rekan</td>
                                <td class="text-center"><span class="badge bg-secondary text-white" style="font-size:10px;">Opsional</span></td>
                                <td class="pe-3 text-secondary" style="font-size:11px;">Harga khusus mitra Rekan toko</td>
                            </tr>
                            <tr>
                                <td class="ps-3 font-monospace fw-semibold">harga_jual_motoris</td>
                                <td class="text-center"><span class="badge bg-secondary text-white" style="font-size:10px;">Opsional</span></td>
                                <td class="pe-3 text-secondary" style="font-size:11px;">Harga khusus sales Motoris</td>
                            </tr>
                            <tr>
                                <td class="ps-3 font-monospace fw-semibold">stok</td>
                                <td class="text-center"><span class="badge bg-secondary text-white" style="font-size:10px;">Opsional</span></td>
                                <td class="pe-3 text-secondary" style="font-size:11px;">Jumlah unit stok (Default: <code>0</code>)</td>
                            </tr>
                            <tr>
                                <td class="ps-3 font-monospace fw-semibold">stok_minimum</td>
                                <td class="text-center"><span class="badge bg-secondary text-white" style="font-size:10px;">Opsional</span></td>
                                <td class="pe-3 text-secondary" style="font-size:11px;">Batas stok minimum warning (Default: <code>5</code>)</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const csvInput     = document.getElementById('csv_file');
    const btnImport    = document.getElementById('btn-import');
    const previewDiv   = document.getElementById('file-preview');
    const previewThead = document.getElementById('preview-thead');
    const previewTbody = document.getElementById('preview-tbody');
    const previewInfo  = document.getElementById('preview-info');

    csvInput.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) {
            btnImport.disabled = true;
            previewDiv.style.display = 'none';
            return;
        }

        btnImport.disabled = false;

        const reader = new FileReader();
        reader.onload = function (e) {
            const text = e.target.result;
            parseAndPreviewCsv(text, file.name);
        };
        reader.readAsText(file, 'UTF-8');
    });

    function parseAndPreviewCsv(text, filename) {
        const lines = text.split(/\r?\n/).map(l => l.trim()).filter(l => l.length > 0 && !l.startsWith('#'));
        if (lines.length === 0) {
            previewDiv.style.display = 'none';
            return;
        }

        const delimiter = lines[0].includes(';') ? ';' : ',';
        const headers   = lines[0].split(delimiter).map(h => h.trim().replace(/^["']|["']$/g, ''));
        const rows      = lines.slice(1, 6).map(line => line.split(delimiter).map(c => c.trim().replace(/^["']|["']$/g, '')));

        let theadHtml = '<tr>' + headers.map(h => `<th>${h}</th>`).join('') + '</tr>';
        previewThead.innerHTML = theadHtml;

        let tbodyHtml = '';
        rows.forEach(row => {
            tbodyHtml += '<tr>' + headers.map((_, i) => `<td>${row[i] !== undefined ? row[i] : ''}</td>`).join('') + '</tr>';
        });
        previewTbody.innerHTML = tbodyHtml;

        const totalDataRows = lines.length - 1;
        previewInfo.innerHTML = `<i class="bi bi-file-earmark-code me-1"></i><strong>${filename}</strong> — Total ${totalDataRows} baris data ditemukan. (Pemisah: <code>${delimiter === ';' ? 'Titik Koma (;)' : 'Koma (,)'}</code>)`;
        previewDiv.style.display = 'block';
    }
</script>
@endpush
