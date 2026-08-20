@extends('layouts.enterprise')
@section('title', 'Pengaturan Katalog Publik — ' . $toko->nama_toko)

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-2">
    <div><h1 class="h4 mb-1 fw-bold"><i class="bi bi-globe2 me-2 text-primary"></i>Pengaturan Katalog Publik</h1><p class="mb-0 text-secondary" style="font-size:13px;">Atur tampilan, copywriting, dan akses katalog yang dibagikan ke pelanggan.</p></div>
    @if($toko->catalog_slug)<a href="{{ route('katalog.index', $toko->catalog_slug) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">Preview Katalog <i class="bi bi-box-arrow-up-right ms-1"></i></a>@endif
</div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="row g-4"><div class="col-12 col-lg-8"><div class="card card-erp shadow-sm"><div class="card-body p-4"><form method="POST" action="{{ route('pengaturan.katalog.update') }}">@csrf @method('PUT')
    <div class="form-check form-switch mb-4"><input class="form-check-input" type="checkbox" name="catalog_enabled" id="catalog_enabled" value="1" @checked(old('catalog_enabled', $toko->catalog_enabled ?? true))><label class="form-check-label fw-semibold" for="catalog_enabled">Aktifkan katalog publik</label><div class="form-text">Jika dinonaktifkan, link katalog tidak dapat dibuka pelanggan.</div></div>
    <div class="mb-3"><label class="form-label fw-semibold" for="catalog_slug">Slug URL</label><div class="input-group"><span class="input-group-text">/katalog/</span><input class="form-control" id="catalog_slug" name="catalog_slug" value="{{ old('catalog_slug', $toko->catalog_slug) }}" pattern="[A-Za-z0-9_-]+" required></div><div class="form-text">Gunakan huruf, angka, strip, atau underscore. Link: <code>{{ url('/katalog') }}/<span id="slugPreview">{{ $toko->catalog_slug }}</span></code></div></div>
    <hr class="my-4"><h2 class="h6 fw-bold mb-3">Copywriting Hero</h2>
    <div class="mb-3"><label class="form-label fw-semibold" for="catalog_hero_badge">Badge / Label Kecil</label><input class="form-control" id="catalog_hero_badge" name="catalog_hero_badge" maxlength="100" value="{{ old('catalog_hero_badge', $toko->catalog_hero_badge) }}" required></div>
    <div class="mb-3"><label class="form-label fw-semibold" for="catalog_hero_title">Judul Hero</label><input class="form-control" id="catalog_hero_title" name="catalog_hero_title" maxlength="180" value="{{ old('catalog_hero_title', $toko->catalog_hero_title) }}" required></div>
    <div class="mb-3"><label class="form-label fw-semibold" for="catalog_hero_description">Deskripsi Hero</label><textarea class="form-control" id="catalog_hero_description" name="catalog_hero_description" rows="3" maxlength="500" required>{{ old('catalog_hero_description', $toko->catalog_hero_description ?: 'Lihat produk, cek harga, dan tanyakan ketersediaan langsung ke asisten toko kami.') }}</textarea></div>
    <hr class="my-4"><h2 class="h6 fw-bold mb-3">Kontak Pelanggan</h2>
    <div class="mb-3"><label class="form-label fw-semibold" for="whatsapp_number">Nomor WhatsApp Katalog</label><input class="form-control" id="whatsapp_number" name="whatsapp_number" value="{{ old('whatsapp_number', $toko->whatsapp_number) }}" placeholder="6281234567890"></div>
    <div class="form-check form-switch mb-4"><input class="form-check-input" type="checkbox" name="whatsapp_enabled" id="whatsapp_enabled" value="1" @checked(old('whatsapp_enabled', $toko->whatsapp_enabled ?? false))><label class="form-check-label" for="whatsapp_enabled">Tampilkan kontak WhatsApp di katalog</label></div>
    <input type="hidden" name="catalog_theme" value="default"><button class="btn btn-primary px-4"><i class="bi bi-check-lg me-1"></i>Simpan Pengaturan Katalog</button>
</form></div></div></div><div class="col-12 col-lg-4"><div class="card card-erp shadow-sm"><div class="card-body p-4"><div class="fw-bold text-primary mb-2"><i class="bi bi-lightbulb me-1"></i>Tips copywriting</div><p class="text-secondary mb-0" style="font-size:13px;line-height:1.7;">Gunakan judul yang langsung menjelaskan manfaat. Contoh: <strong>Belanja kebutuhan harian tanpa perlu antre.</strong> Deskripsi sebaiknya singkat dan mengarahkan pelanggan untuk mencari produk atau bertanya ke AI.</p></div></div></div></div>
@endsection
@push('scripts')<script>document.getElementById('catalog_slug')?.addEventListener('input',e=>document.getElementById('slugPreview').textContent=e.target.value)</script>@endpush
