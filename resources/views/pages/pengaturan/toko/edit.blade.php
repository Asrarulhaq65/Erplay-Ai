@extends('layouts.enterprise')
@section('title', 'Pengaturan Profil Toko — ' . (auth()->user()?->toko?->nama_toko ?? 'ERPlay AI'))

@section('content')
<!-- Breadcrumb Navigation -->
<nav class="erp-breadcrumb d-none d-md-block" aria-label="Breadcrumb">
    <a href="{{ url('/dashboard') }}" aria-label="Beranda Dashboard"><i class="bi bi-house-door"></i></a>
    <i class="bi bi-chevron-right mx-1" style="font-size:9px;" aria-hidden="true"></i>
    <span aria-current="page">Pengaturan Profil Toko</span>
</nav>

<!-- Page Header -->
<div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-2">
    <div>
        <h1 class="h4 mb-0 fw-bold" style="font-family:'Plus Jakarta Sans',sans-serif;color:var(--pb-text);letter-spacing:-0.02em;">
            <i class="bi bi-shop me-2" aria-hidden="true"></i>Pengaturan Profil & Identitas Toko
        </h1>
        <p class="mb-0 mt-1" style="color:var(--text-secondary);font-size:13px;">
            Atur nama toko, slogan, nomor telepon, logo, dan alamat domisili untuk kop struk POS.
        </p>
    </div>
</div>

<div class="alert alert-primary d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4" role="status">
    <div><strong><i class="bi bi-globe2 me-1"></i>Katalog publik toko</strong><div style="font-size:12px;">Bagikan link ini agar pelanggan bisa melihat produk dan bertanya ke AI.</div><code>{{ route('katalog.index', $toko->catalog_slug) }}</code></div>
    <a href="{{ route('katalog.index', $toko->catalog_slug) }}" target="_blank" rel="noopener" class="btn btn-sm btn-primary">Buka Katalog <i class="bi bi-box-arrow-up-right ms-1"></i></a>
</div>

<div class="row g-4 mb-4">
    <!-- Form Card Left -->
    <div class="col-12 col-md-8 col-lg-7">
        <div class="card card-erp">
            <div class="card-header py-3">
                <h2 class="h6 card-title mb-0" style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;">
                    <i class="bi bi-sliders me-2" aria-hidden="true"></i>Form Parameter Toko
                </h2>
            </div>
            <div class="card-body p-4">
                
                @if($errors->any())
                    <div class="alert alert-danger mb-4" style="font-size:12px;" role="alert">
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('pengaturan.toko.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="mb-4 text-center">
                        <div class="d-inline-block position-relative">
                            @if($toko->logo)
                                <img src="{{ asset('storage/' . $toko->logo) }}" alt="Preview Logo {{ $toko->nama_toko }}" class="rounded shadow-sm" style="width: 120px; height: 120px; object-fit: contain; background: var(--bg-card); padding: 5px; border: 1px solid var(--border-light);">
                            @else
                                <div class="rounded shadow-sm d-flex align-items-center justify-content-center" style="width: 120px; height: 120px; background: var(--bg-card-hover); border: 2px dashed var(--border-light); color: var(--pb-dark);">
                                    <i class="bi bi-image" style="font-size: 2.8rem;" aria-hidden="true"></i>
                                </div>
                            @endif
                        </div>
                        <div class="mt-3">
                            <label for="logo" class="btn btn-sm btn-outline-primary px-3">
                                <i class="bi bi-upload me-1" aria-hidden="true"></i> Unggah Logo Baru
                            </label>
                            <input type="file" id="logo" name="logo" class="d-none" accept="image/jpeg,image/png,image/jpg,image/svg+xml" onchange="previewLogo(this)" aria-label="Unggah Foto Logo Toko">
                            <div class="form-text mt-1" style="font-size:11px;">Format: JPG, PNG, SVG (Maks. 2MB). Disarankan foto rasio 1:1.</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="nama_toko" class="form-label fw-semibold" style="font-size:13px;">Nama Toko / Usaha <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="bi bi-shop-window text-secondary" aria-hidden="true"></i></span>
                            <input type="text" class="form-control fw-bold" id="nama_toko" name="nama_toko" value="{{ old('nama_toko', $toko->nama_toko) }}" required placeholder="Contoh: Toko Kelontong Jaya" autocomplete="off">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="slogan_struk" class="form-label fw-semibold" style="font-size:13px;">Slogan Struk Belanja</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="bi bi-chat-quote text-secondary" aria-hidden="true"></i></span>
                            <input type="text" class="form-control" id="slogan_struk" name="slogan_struk" value="{{ old('slogan_struk', $toko->slogan_struk) }}" placeholder="Contoh: Belanja Hemat, Keluarga Senang" autocomplete="off">
                        </div>
                        <div class="form-text" style="font-size:11px;">Tercetak di bagian header struk belanja POS kasir & banner dashboard.</div>
                    </div>

                    <div class="mb-3">
                        <label for="no_telepon" class="form-label fw-semibold" style="font-size:13px;">Nomor Kontak WhatsApp / Telp <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="bi bi-telephone text-secondary" aria-hidden="true"></i></span>
                            <input type="text" class="form-control" id="no_telepon" name="no_telepon" value="{{ old('no_telepon', $toko->no_telepon) }}" required placeholder="Contoh: 081234567890" autocomplete="off">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="alamat" class="form-label fw-semibold" style="font-size:13px;">Alamat Domisili Lengkap <span class="text-danger">*</span></label>
                        <textarea class="form-control form-control-sm" id="alamat" name="alamat" rows="3" required placeholder="Jalan, Nomor, RT/RW, Kelurahan, Kecamatan, Kota">{{ old('alamat', $toko->alamat) }}</textarea>
                    </div>

                    <hr class="mb-4">

                    <div class="d-flex justify-content-end gap-2">
                        <button type="submit" class="btn btn-sm btn-pb px-4 py-2">
                            <i class="bi bi-check2-circle me-1" aria-hidden="true"></i> Simpan Pengaturan Profil
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Info Helper Card Right -->
    <div class="col-12 col-md-4 col-lg-5 d-none d-md-block">
        <div class="card card-erp h-100">
            <div class="card-header py-3">
                <h2 class="h6 card-title mb-0" style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;">
                    <i class="bi bi-info-circle me-2" aria-hidden="true"></i>Petunjuk Penggunaan
                </h2>
            </div>
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div>
                    <p style="font-size: 13px; color: var(--text-secondary);">Identitas toko yang Anda atur akan memunculkan informasi otomatis di:</p>
                    <ul style="font-size: 12px; color: var(--text-secondary);" class="ps-3 mb-4">
                        <li class="mb-2"><strong>Navigasi Navbar:</strong> Nama & logo toko di bagian atas sistem.</li>
                        <li class="mb-2"><strong>Header Dashboard:</strong> Slogan dan nama toko di banner utama.</li>
                        <li class="mb-2"><strong>Cetakan Struk POS:</strong> Nama toko, alamat, no telp, dan slogan pada struk thermal.</li>
                        <li class="mb-2"><strong>Laporan PDF / CSV:</strong> Kop resmi dokumen transaksi usaha Anda.</li>
                    </ul>
                </div>
                
                <div class="p-3 rounded border" style="background:var(--bg-card-hover);border-color:var(--border-light) !important;">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-lightbulb text-warning fs-4" aria-hidden="true"></i>
                        <span class="fw-bold" style="font-size:13px;color:var(--pb-text);">Tips Format Logo</span>
                    </div>
                    <span style="font-size: 12px; color: var(--text-secondary);">Gunakan logo dengan format PNG berlatar belakang transparan untuk hasil visual jernih di navbar & struk cetak.</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function previewLogo(input) {
    if (input.files && input.files[0]) {
        let label = input.previousElementSibling;
        label.innerHTML = '<i class="bi bi-check-circle me-1 text-success"></i> ' + input.files[0].name;
    }
}
</script>
@endpush
