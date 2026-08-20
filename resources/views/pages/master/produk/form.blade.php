@extends('layouts.enterprise')
@section('title', ($produk ? 'Edit Produk — ' . $produk->nama_produk : 'Tambah Produk Baru') . ' — ' . (auth()->user()?->toko?->nama_toko ?? 'ERPlay AI'))

@section('content')
<!-- Breadcrumb Navigation -->
<nav class="erp-breadcrumb d-none d-md-block" aria-label="Breadcrumb">
    <a href="{{ url('/dashboard') }}" aria-label="Beranda Dashboard"><i class="bi bi-house-door"></i></a>
    <i class="bi bi-chevron-right mx-1" style="font-size:9px;" aria-hidden="true"></i>
    <a href="{{ route('master.produk.index') }}">Master Produk</a>
    <i class="bi bi-chevron-right mx-1" style="font-size:9px;" aria-hidden="true"></i>
    <span aria-current="page">{{ $produk ? 'Edit Produk' : 'Tambah Produk' }}</span>
</nav>

<!-- Page Header & Action Bar -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h4 mb-0 fw-bold" style="font-family:'Plus Jakarta Sans',sans-serif;color:var(--pb-text);letter-spacing:-0.02em;">
            <i class="bi bi-box-seam me-2" aria-hidden="true"></i>{{ $produk ? 'Edit Data Produk' : 'Tambah Produk Baru' }}
        </h1>
        <p class="mb-0 mt-1" style="color:var(--text-secondary);font-size:13px;">
            Isi rincian informasi produk, kategorisasi, stok awal, serta penataan 4 tier harga jual.
        </p>
    </div>
    <a href="{{ route('master.produk.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Kembali ke Daftar
    </a>
</div>

<!-- Main Form Card -->
<div class="card card-erp mb-4">
    <div class="card-header py-3">
        <h2 class="h6 card-title mb-0" style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;">
            <i class="bi bi-pencil-square me-2" aria-hidden="true"></i>Formulir Parameter Produk
        </h2>
    </div>
    <div class="card-body p-4">
        <form action="{{ $produk ? route('master.produk.update', $produk->id) : route('master.produk.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if($produk) @method('PUT') @endif

            <div class="row g-4">
                <!-- Column Left: Category, Barcode, Basic Info & Stock -->
                <div class="col-12 col-md-6">
                    <h3 class="h6 fw-bold mb-3 pb-2 border-bottom" style="color:var(--pb-dark);font-size:14px;">
                        <i class="bi bi-info-circle me-1" aria-hidden="true"></i>Informasi Dasar & Stok
                    </h3>
                    
                    <div class="row mb-3 g-3">
                        <div class="col-12 col-sm-6">
                            <label for="kelompok_id" class="form-label fw-semibold" style="font-size:13px;">Kelompok Produk <span class="text-danger">*</span></label>
                            <select id="kelompok_id" class="form-select form-select-sm" required aria-label="Pilih Kelompok Produk">
                                <option value="">-- Pilih Kelompok --</option>
                                @foreach($kelompoks as $kel)
                                    <option value="{{ $kel->id }}" {{ (isset($selectedKelompokId) && $selectedKelompokId == $kel->id) ? 'selected' : '' }}>
                                        {{ $kel->nama_kelompok }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label for="kategori_id" class="form-label fw-semibold" style="font-size:13px;">Kategori Produk <span class="text-danger">*</span></label>
                            <select name="kategori_id" id="kategori_id" class="form-select form-select-sm" required {{ $kategoris->isEmpty() ? 'disabled' : '' }} aria-label="Pilih Kategori Produk">
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($kategoris as $kat)
                                    <option value="{{ $kat->id }}" {{ (old('kategori_id', $produk->kategori_id ?? '') == $kat->id) ? 'selected' : '' }}>
                                        {{ $kat->nama_kategori }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="input-barcode" class="form-label fw-semibold" style="font-size:13px;">Barcode / SKU Kode <span class="text-danger">*</span></label>
                        <input type="text" id="input-barcode" name="barcode" class="form-control form-control-sm @error('barcode') is-invalid @enderror" 
                               value="{{ old('barcode', $produk->barcode ?? '') }}" required maxlength="50" placeholder="Scan atau ketik kode barcode..."
                               autocomplete="off"
                               @error('barcode') aria-invalid="true" aria-describedby="error-barcode" @enderror>
                        @error('barcode')
                            <div class="invalid-feedback" style="font-size:11px;" role="alert" id="error-barcode">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="input-nama-produk" class="form-label fw-semibold" style="font-size:13px;">Nama Produk <span class="text-danger">*</span></label>
                        <input type="text" id="input-nama-produk" name="nama_produk" class="form-control form-control-sm" 
                               value="{{ old('nama_produk', $produk->nama_produk ?? '') }}" required maxlength="150" placeholder="Contoh: Indomie Goreng Spesial 85g" autocomplete="off">
                    </div>

                    <div class="row mb-3 g-3">
                        <div class="col-4">
                            <label for="input-satuan" class="form-label fw-semibold" style="font-size:13px;">Satuan <span class="text-danger">*</span></label>
                            <input type="text" id="input-satuan" name="satuan" class="form-control form-control-sm" placeholder="Pcs, Kg, Bks" 
                                   value="{{ old('satuan', $produk->satuan ?? 'Pcs') }}" required maxlength="20">
                        </div>
                        <div class="col-4">
                            <label for="input-stok" class="form-label fw-semibold" style="font-size:13px;">Stok Awal <span class="text-danger">*</span></label>
                            <input type="number" id="input-stok" name="stok" class="form-control form-control-sm" 
                                   value="{{ old('stok', $produk->stok ?? 0) }}" required min="0" {{ $produk ? 'readonly' : '' }}>
                            @if($produk)
                                <div class="form-text" style="font-size:11px;">Ubah via Penyesuaian/Opname</div>
                            @endif
                        </div>
                        <div class="col-4">
                            <label for="input-stok-min" class="form-label fw-semibold" style="font-size:13px;">Stok Min <span class="text-danger">*</span></label>
                            <input type="number" id="input-stok-min" name="stok_minimum" class="form-control form-control-sm" 
                                   value="{{ old('stok_minimum', $produk->stok_minimum ?? 5) }}" required min="0">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="input-gambar" class="form-label fw-semibold" style="font-size:13px;">Gambar Foto Produk (Opsional)</label>
                        @if($produk && $produk->gambar)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $produk->gambar) }}" alt="Preview foto {{ $produk->nama_produk }}" class="img-thumbnail" style="max-height: 110px; border-radius: 8px;">
                            </div>
                        @endif
                        <input type="file" id="input-gambar" name="gambar" class="form-control form-control-sm @error('gambar') is-invalid @enderror" accept="image/*">
                        @error('gambar')
                            <div class="invalid-feedback" style="font-size:11px;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Column Right: HPP Cost & Multi-Tier Selling Prices -->
                <div class="col-12 col-md-6">
                    <h3 class="h6 fw-bold mb-3 pb-2 border-bottom" style="color:var(--pb-dark);font-size:14px;">
                        <i class="bi bi-tags me-1" aria-hidden="true"></i>Skema Pengaturan Harga (Rp)
                    </h3>

                    <div class="mb-3">
                        <label for="input-harga-modal" class="form-label fw-semibold" style="font-size:13px;">Harga Modal / HPP <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">Rp</span>
                            <input type="number" id="input-harga-modal" name="harga_modal" class="form-control form-control-sm" 
                                   value="{{ old('harga_modal', $produk ? round($produk->harga_modal) : 0) }}" required min="0">
                        </div>
                    </div>

                    <div class="p-3 mb-3 rounded border" style="background:var(--bg-card-hover);border-color:var(--border-light) !important;">
                        <label class="form-label fw-bold mb-3" style="color:var(--pb-text);font-size:13px;">
                            <i class="bi bi-diagram-3 me-1" aria-hidden="true"></i>Harga Jual Bertingkat Pelanggan
                        </label>
                        
                        <div class="row g-3">
                            <div class="col-12 col-sm-6">
                                <label for="harga-umum" class="form-label mb-1 fw-semibold" style="font-size:12px;">Harga Umum <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" id="harga-umum" name="harga_jual_umum" class="form-control form-control-sm" 
                                           value="{{ old('harga_jual_umum', $produk ? round($produk->harga_jual_umum) : 0) }}" required min="0">
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <label for="harga-member" class="form-label mb-1 fw-semibold" style="font-size:12px;">Harga Member <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" id="harga-member" name="harga_jual_member" class="form-control form-control-sm" 
                                           value="{{ old('harga_jual_member', $produk ? round($produk->harga_jual_member) : 0) }}" required min="0">
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <label for="harga-rekan" class="form-label mb-1 fw-semibold" style="font-size:12px;">Harga Rekan <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" id="harga-rekan" name="harga_jual_rekan" class="form-control form-control-sm" 
                                           value="{{ old('harga_jual_rekan', $produk ? round($produk->harga_jual_rekan) : 0) }}" required min="0">
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <label for="harga-motoris" class="form-label mb-1 fw-semibold" style="font-size:12px;">Harga Motoris <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" id="harga-motoris" name="harga_jual_motoris" class="form-control form-control-sm" 
                                           value="{{ old('harga_jual_motoris', $produk ? round($produk->harga_jual_motoris) : 0) }}" required min="0">
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Footer Buttons -->
            <div class="mt-4 pt-3 text-end border-top">
                <a href="{{ route('master.produk.index') }}" class="btn btn-sm btn-outline-secondary me-2 px-3">Batal</a>
                <button type="submit" class="btn btn-sm btn-pb px-4">
                    <i class="bi bi-check2-circle me-1" aria-hidden="true"></i>Simpan Produk
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const kelompokSelect = document.getElementById('kelompok_id');
    const kategoriSelect = document.getElementById('kategori_id');
    const oldKategoriId = "{{ old('kategori_id', $produk->kategori_id ?? '') }}";

    kelompokSelect.addEventListener('change', function() {
        const kelompokId = this.value;
        kategoriSelect.innerHTML = '<option value="">-- Pilih Kategori --</option>';
        kategoriSelect.disabled = true;

        if (kelompokId) {
            fetch(`/api/master/kategori-by-kelompok?kelompok_id=${kelompokId}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(res => {
                if(res.success) {
                    res.data.forEach(kat => {
                        const isSelected = (kat.id == oldKategoriId) ? 'selected' : '';
                        kategoriSelect.innerHTML += `<option value="${kat.id}" ${isSelected}>${kat.nama_kategori}</option>`;
                    });
                    kategoriSelect.disabled = false;
                }
            })
            .catch(err => console.error('Error fetching kategori:', err));
        }
    });

    if (kelompokSelect.value && oldKategoriId && {{ $produk ? 'false' : 'true' }}) {
        kelompokSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endpush
