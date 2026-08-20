@extends('layouts.enterprise')
@section('title', 'Profil ERPlay AI Assistant')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-2">
    <div>
        <h1 class="h4 mb-1 fw-bold"><i class="bi bi-person-heart me-2 text-primary"></i>Profil ERPlay AI Assistant</h1>
        <p class="mb-0 text-secondary" style="font-size:13px;">Buat asisten terasa lebih dekat dengan gaya kerja toko Anda.</p>
    </div>
    <a href="{{ route('pengaturan.ai.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Kembali ke Pengaturan AI</a>
</div>

@if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif

<div class="row g-4">
    <div class="col-12 col-lg-7">
        <div class="card card-erp shadow-sm"><div class="card-body p-4">
            <form method="POST" action="{{ route('pengaturan.ai.assistant.update') }}">
                @csrf @method('PUT')
                <div class="mb-3"><label class="form-label fw-semibold" for="assistant_name">Nama Asisten</label><input class="form-control" id="assistant_name" name="assistant_name" maxlength="80" value="{{ old('assistant_name', $config?->assistant_name ?? 'ERPlay AI Assistant') }}" required><div class="form-text">Contoh: Budi Assistant atau Asisten Toko Berkah.</div></div>
                <div class="mb-3"><label class="form-label fw-semibold" for="personality">Gaya Komunikasi</label><select class="form-select" id="personality" name="personality"><option value="profesional" @selected(old('personality', $config?->personality ?? 'profesional') === 'profesional')>Profesional</option><option value="santai" @selected(old('personality', $config?->personality) === 'santai')>Santai</option><option value="formal" @selected(old('personality', $config?->personality) === 'formal')>Formal</option></select></div>
                <div class="mb-3"><label class="form-label fw-semibold" for="greeting_message">Pesan Sambutan</label><textarea class="form-control" id="greeting_message" name="greeting_message" rows="3" maxlength="250" placeholder="Selamat datang, {nama_user}!">{{ old('greeting_message', $config?->greeting_message) }}</textarea></div>
                <div class="form-check form-switch mb-4"><input class="form-check-input" type="checkbox" id="proactive_enabled" name="proactive_enabled" value="1" @checked(old('proactive_enabled', $config?->proactive_enabled ?? true))><label class="form-check-label fw-semibold" for="proactive_enabled">Aktifkan insight proaktif</label><div class="form-text">ERPlay AI dapat menampilkan saran stok dan ringkasan singkat sesuai kondisi toko.</div></div>
                <button class="btn btn-primary px-4"><i class="bi bi-check-lg me-1"></i>Simpan Profil Asisten</button>
            </form>
        </div></div>
    </div>
    <div class="col-12 col-lg-5"><div class="card card-erp shadow-sm"><div class="card-body p-4"><div class="text-primary fw-bold mb-2"><i class="bi bi-stars me-1"></i>Partner operasional toko</div><p class="text-secondary mb-0" style="font-size:13px;line-height:1.7;">Nama dan gaya komunikasi hanya mengubah pengalaman interaksi. Akses data tetap dibatasi pada toko yang sedang aktif, dan aksi yang berisiko memerlukan konfirmasi.</p></div></div></div>
</div>
@endsection
