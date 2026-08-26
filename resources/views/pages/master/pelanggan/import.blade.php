@extends('layouts.enterprise')
@section('title', 'Import Pelanggan — ' . (auth()->user()?->toko?->nama_toko ?? 'ERPlay AI'))

@section('content')
<nav class="erp-breadcrumb d-none d-md-block mb-3" aria-label="Breadcrumb">
    <a href="{{ route('master.pelanggan.index') }}"><i class="bi bi-people"></i></a>
    <i class="bi bi-chevron-right mx-1" style="font-size:9px;"></i>
    <span>Import Pelanggan</span>
</nav>

<div class="d-flex align-items-center justify-content-between mb-4 gap-2">
    <div>
        <h1 class="h4 mb-1 fw-bold" style="color:var(--pb-text);">Import Data Pelanggan</h1>
        <p class="mb-0 text-secondary" style="font-size:13px;">Upload Excel atau CSV, lalu tandai tier harga pelanggan.</p>
    </div>
    <a href="{{ route('master.pelanggan.index') }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-7">
        <div class="card card-erp shadow-sm">
            <div class="card-header py-3 px-4"><strong><i class="bi bi-cloud-upload me-2 text-success"></i>Upload File Pelanggan</strong></div>
            <div class="card-body p-4">
                <form action="{{ route('master.pelanggan.import.process') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="customer_file" class="form-label fw-bold">File Excel / CSV <span class="text-danger">*</span></label>
                        <input type="file" name="customer_file" id="customer_file" class="form-control" accept=".xlsx,.csv,.txt" required>
                        <div class="form-text">Mendukung Excel XLSX serta CSV/TXT dengan pemisah koma, titik koma, atau tab.</div>
                    </div>
                    <div class="mb-4">
                        <label for="default_tier" class="form-label fw-bold">Tandai Tier Default <span class="text-danger">*</span></label>
                        <select name="default_tier" id="default_tier" class="form-select" required>
                            @foreach($tiers as $tier)
                                <option value="{{ $tier }}" @selected($tier === 'Umum')>{{ $tier }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Aksi ini menandai setiap baris yang jenis/tier-nya kosong atau tidak dikenali.</div>
                    </div>
                    <button type="submit" class="btn btn-success px-4"><i class="bi bi-check2-circle me-1"></i>Proses Import</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-5">
        <div class="card card-erp shadow-sm">
            <div class="card-header py-3 px-4"><strong><i class="bi bi-table me-2 text-primary"></i>Kolom yang Didukung</strong></div>
            <div class="card-body p-4" style="font-size:12.5px;">
                <p class="text-secondary">Header tidak sensitif terhadap huruf besar/kecil.</p>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead><tr><th>Kolom</th><th>Wajib</th><th>Alias</th></tr></thead>
                        <tbody>
                            <tr><td><code>nama_pelanggan</code></td><td>Ya</td><td>nama</td></tr>
                            <tr><td><code>no_telepon</code></td><td>Ya</td><td>telepon, no_hp, hp</td></tr>
                            <tr><td><code>status_pelanggan</code></td><td>Tidak</td><td>jenis_pelanggan, tier</td></tr>
                            <tr><td><code>alamat</code></td><td>Tidak</td><td>-</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="alert alert-info py-2 mb-0" style="font-size:12px;">
                    <strong>Tier valid:</strong> Umum, Member, Rekan, Motoris. Nilai seperti Mitra/Reseller otomatis menjadi Rekan; Sales/Grosir menjadi Motoris.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
