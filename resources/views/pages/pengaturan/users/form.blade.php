@extends('layouts.enterprise')
@section('title', (isset($user) ? 'Edit Data User' : 'Tambah User Baru') . ' — ' . (auth()->user()?->toko?->nama_toko ?? 'ERPlay AI'))

@section('content')
<!-- Breadcrumb Navigation -->
<nav class="erp-breadcrumb d-none d-md-block" aria-label="Breadcrumb">
    <a href="{{ url('/dashboard') }}" aria-label="Beranda Dashboard"><i class="bi bi-house-door"></i></a>
    <i class="bi bi-chevron-right mx-1" style="font-size:9px;" aria-hidden="true"></i>
    <a href="{{ route('pengaturan.users.index') }}">Manajemen User</a>
    <i class="bi bi-chevron-right mx-1" style="font-size:9px;" aria-hidden="true"></i>
    <span aria-current="page">{{ isset($user) ? 'Edit User' : 'Tambah User' }}</span>
</nav>

<!-- Page Header & Back Button -->
<div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-2">
    <div>
        <h1 class="h4 mb-0 fw-bold" style="font-family:'Plus Jakarta Sans',sans-serif;color:var(--pb-text);letter-spacing:-0.02em;">
            <i class="bi bi-person-gear me-2" aria-hidden="true"></i>{{ isset($user) ? 'Edit Data User' : 'Tambah User Baru' }}
        </h1>
        <p class="mb-0 mt-1" style="color:var(--text-secondary);font-size:13px;">
            {{ isset($user) ? 'Perbarui informasi akun, role hak akses, dan status keaktifan user.' : 'Daftarkan akun staf kasir atau admin baru untuk mengakses sistem toko.' }}
        </p>
    </div>
    <div>
        <a href="{{ route('pengaturan.users.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Kembali ke Daftar User
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Form Card Left -->
    <div class="col-12 col-md-8 col-lg-7">
        <div class="card card-erp">
            <div class="card-header py-3">
                <h2 class="h6 card-title mb-0" style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;">
                    <i class="bi bi-person-badge me-2" aria-hidden="true"></i>Form Kredensial & Profile User
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

                <form action="{{ isset($user) ? route('pengaturan.users.update', $user->id) : route('pengaturan.users.store') }}" method="POST">
                    @csrf
                    @if(isset($user)) @method('PUT') @endif

                    <div class="mb-3">
                        <label for="nama_lengkap" class="form-label fw-semibold" style="font-size:13px;">Nama Lengkap Staf <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="bi bi-person-badge text-secondary" aria-hidden="true"></i></span>
                            <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap', $user->nama_lengkap ?? '') }}" required placeholder="Contoh: Budi Santoso" autocomplete="off">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="username" class="form-label fw-semibold" style="font-size:13px;">Username Login <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="bi bi-person text-secondary" aria-hidden="true"></i></span>
                            <input type="text" class="form-control font-monospace" id="username" name="username" value="{{ old('username', $user->username ?? '') }}" required placeholder="Contoh: budi123" autocomplete="off">
                        </div>
                        @if(!isset($user))
                        <div class="form-text" style="font-size:11px;">Username digunakan untuk proses autentikasi masuk ke sistem.</div>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold" style="font-size:13px;">Password @if(!isset($user))<span class="text-danger">*</span>@endif</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="bi bi-lock text-secondary" aria-hidden="true"></i></span>
                            <input type="password" class="form-control" id="password" name="password" {{ isset($user) ? '' : 'required' }} placeholder="{{ isset($user) ? 'Kosongkan jika tidak ingin mengubah password' : 'Minimal 6 karakter' }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="role_id" class="form-label fw-semibold" style="font-size:13px;">Role Hak Akses (Jabatan) <span class="text-danger">*</span></label>
                        <select class="form-select form-select-sm" id="role_id" name="role_id" required aria-label="Pilih Role Akses">
                            <option value="">-- Pilih Role Akses --</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ old('role_id', $user->role_id ?? '') == $role->id ? 'selected' : '' }}>
                                    {{ $role->nama_role }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text" style="font-size:11px;">Menentukan menu dan fitur yang diizinkan untuk diakses user ini.</div>
                    </div>

                    <div class="mb-4">
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" {{ old('is_active', $user->is_active ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label ms-2" for="is_active">
                                <span class="fw-bold" style="color:var(--pb-text);font-size:13px;">Akun Aktif</span> <br>
                                <small style="color:var(--text-secondary);font-size:11px;">Jika non-aktif, user ini tidak akan bisa login ke dalam aplikasi.</small>
                            </label>
                        </div>
                    </div>

                    <hr class="mb-4">

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('pengaturan.users.index') }}" class="btn btn-sm btn-outline-secondary px-3">Batal</a>
                        <button type="submit" class="btn btn-sm btn-pb px-4 py-2">
                            <i class="bi bi-check2-circle me-1" aria-hidden="true"></i> {{ isset($user) ? 'Simpan Perubahan User' : 'Buat User Baru' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Security Info Helper Card Right -->
    <div class="col-12 col-md-4 col-lg-5 d-none d-md-block">
        <div class="card card-erp h-100">
            <div class="card-header py-3">
                <h2 class="h6 card-title mb-0" style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;">
                    <i class="bi bi-shield-check me-2" aria-hidden="true"></i>Keamanan & Hak Akses
                </h2>
            </div>
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div>
                    <div class="text-center mb-3">
                        <i class="bi bi-shield-lock" style="font-size: 3rem; color: var(--pb-dark);" aria-hidden="true"></i>
                    </div>
                    <p style="font-size: 13px; color: var(--text-secondary);">Pastikan Anda hanya memberikan akses kepada karyawan yang berwenang. Seluruh password disimpan menggunakan enkripsi hashing bertaraf industri.</p>
                    
                    <div class="w-100 mt-3" style="font-size:12px; color: var(--text-secondary);">
                        <div class="mb-2"><strong>Super Admin & Owner:</strong> Akses penuh ke seluruh sistem ERP, laporan, & master data.</div>
                        <div class="mb-2"><strong>Gudang:</strong> Mengelola stok inventory, barang masuk, dan pembelian supplier.</div>
                        <div class="mb-2"><strong>Kasir:</strong> Mengakses transaksi kasir POS, penjualan barang, dan cetak struk.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
