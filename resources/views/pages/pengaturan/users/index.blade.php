@extends('layouts.enterprise')
@section('title', 'Manajemen User & Hak Akses — ' . (auth()->user()?->toko?->nama_toko ?? 'ERPlay AI'))

@push('styles')
<style>
    /* ── User Stat Summary Cards ── */
    .user-stat-card {
        background: var(--bg-card);
        border: 1px solid var(--border-light);
        border-radius: 12px;
        padding: 14px 16px;
        display: flex;
        align-items: center;
        gap: 12px;
        transition: border-color 0.15s ease, box-shadow 0.15s ease, var(--theme-transition);
        height: 100%;
    }
    .user-stat-card:hover {
        border-color: var(--pb-mid);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
    }
    .user-stat-icon {
        width: 38px; height: 38px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }
    .user-stat-label {
        font-size: 11px;
        font-weight: 600;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .user-stat-val {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 18px;
        font-weight: 800;
        color: var(--pb-text);
        line-height: 1.1;
    }

    /* Role Badges with WCAG AA High Contrast */
    .badge-role-super { background: rgba(220, 38, 38, 0.1); color: #B91C1C; border: 1px solid rgba(220, 38, 38, 0.2); }
    .badge-role-owner { background: rgba(29, 78, 216, 0.1); color: #1D4ED8; border: 1px solid rgba(29, 78, 216, 0.2); }
    .badge-role-kasir { background: rgba(21, 128, 61, 0.1); color: #15803D; border: 1px solid rgba(21, 128, 61, 0.2); }
    .badge-role-gudang { background: rgba(180, 107, 24, 0.1); color: #B46B18; border: 1px solid rgba(180, 107, 24, 0.2); }

    [data-theme="dark"] .badge-role-super { background: rgba(248, 113, 113, 0.15); color: #F87171; border-color: rgba(248, 113, 113, 0.25); }
    [data-theme="dark"] .badge-role-owner { background: rgba(96, 165, 250, 0.15); color: #60A5FA; border-color: rgba(96, 165, 250, 0.25); }
    [data-theme="dark"] .badge-role-kasir { background: rgba(52, 211, 153, 0.15); color: #34D399; border-color: rgba(52, 211, 153, 0.25); }
    [data-theme="dark"] .badge-role-gudang { background: rgba(251, 191, 36, 0.15); color: #FBBF24; border-color: rgba(251, 191, 36, 0.25); }
</style>
@endpush

@section('content')
<!-- Breadcrumb Navigation -->
<nav class="erp-breadcrumb d-none d-md-block" aria-label="Breadcrumb">
    <a href="{{ url('/dashboard') }}" aria-label="Beranda Dashboard"><i class="bi bi-house-door"></i></a>
    <i class="bi bi-chevron-right mx-1" style="font-size:9px;" aria-hidden="true"></i>
    <span aria-current="page">Manajemen User</span>
</nav>

<!-- Page Header & Action Bar -->
<div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
    <div>
        <h1 class="h4 mb-0 fw-bold" style="font-family:'Plus Jakarta Sans',sans-serif;color:var(--pb-text);letter-spacing:-0.02em;">
            <i class="bi bi-people me-2" aria-hidden="true"></i>Manajemen User & Hak Akses
        </h1>
        <p class="mb-0 mt-1" style="color:var(--text-secondary);font-size:13px;">
            Kelola pengguna sistem toko Anda, penugasan role (Super Admin, Owner, Kasir, Gudang), dan status keaktifan.
        </p>
    </div>
    <div>
        <a href="{{ route('pengaturan.users.create') }}" class="btn btn-sm btn-pb">
            <i class="bi bi-person-plus me-1" aria-hidden="true"></i>Tambah User Baru
        </a>
    </div>
</div>

<!-- User Stat Summary Row -->
<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">
        <div class="user-stat-card">
            <div class="user-stat-icon" style="background:rgba(13, 78, 86, 0.1);color:var(--pb-dark);" aria-hidden="true"><i class="bi bi-people"></i></div>
            <div>
                <div class="user-stat-label">Total Akun</div>
                <div class="user-stat-val">{{ number_format($users->total(), 0, ',', '.') }} User</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="user-stat-card">
            <div class="user-stat-icon" style="background:rgba(21, 128, 61, 0.1);color:#15803D;" aria-hidden="true"><i class="bi bi-check-circle"></i></div>
            <div>
                <div class="user-stat-label">Status Aktif</div>
                <div class="user-stat-val">{{ number_format($users->getCollection()->where('is_active', true)->count(), 0, ',', '.') }} Akun</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="user-stat-card">
            <div class="user-stat-icon" style="background:rgba(29, 78, 216, 0.1);color:#1D4ED8;" aria-hidden="true"><i class="bi bi-person-badge"></i></div>
            <div>
                <div class="user-stat-label">Petugas Kasir</div>
                <div class="user-stat-val">{{ number_format($users->getCollection()->filter(fn($u) => $u->role?->nama_role == 'Kasir')->count(), 0, ',', '.') }} Kasir</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="user-stat-card">
            <div class="user-stat-icon" style="background:rgba(180, 107, 24, 0.1);color:#B46B18;" aria-hidden="true"><i class="bi bi-shield-check"></i></div>
            <div>
                <div class="user-stat-label">Owner & Gudang</div>
                <div class="user-stat-val">{{ number_format($users->getCollection()->filter(fn($u) => in_array($u->role?->nama_role, ['Owner', 'Gudang', 'Admin Toko']))->count(), 0, ',', '.') }} Staf</div>
            </div>
        </div>
    </div>
</div>

<!-- Main User List Card -->
<div class="card card-erp mb-3">
    <div class="card-header py-2 px-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <h2 class="h6 card-title mb-0" style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;">
                <i class="bi bi-person-lines-fill me-2" aria-hidden="true"></i>Katalog Pengguna Toko
            </h2>

            <!-- Search Form -->
            <form action="{{ route('pengaturan.users.index') }}" method="GET" class="d-flex align-items-center gap-1" style="width:260px;">
                <div class="input-group input-group-sm">
                    <input type="text" name="search" class="form-control" placeholder="Cari nama / username..." value="{{ request('search') }}" aria-label="Cari User">
                    <button class="btn btn-outline-secondary" type="submit" aria-label="Cari"><i class="bi bi-search" aria-hidden="true"></i></button>
                    @if(request('search'))
                        <a href="{{ route('pengaturan.users.index') }}" class="btn btn-outline-danger" title="Reset Pencarian"><i class="bi bi-x-lg" aria-hidden="true"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-sm align-middle table-hover mb-0">
            <thead>
                <tr>
                    <th class="text-center py-2 px-3" style="width: 50px;" scope="col">No</th>
                    <th class="py-2" scope="col">Nama Lengkap</th>
                    <th class="py-2" style="width:160px;" scope="col">Username</th>
                    <th class="py-2 text-center" style="width:150px;" scope="col">Role Akses</th>
                    <th class="py-2 text-center" style="width:110px;" scope="col">Status</th>
                    <th class="py-2 text-center px-3" style="width: 100px;" scope="col">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $index => $user)
                    @php
                        $roleClass = 'badge-role-kasir';
                        if ($user->role?->nama_role == 'Super Admin') $roleClass = 'badge-role-super';
                        elseif ($user->role?->nama_role == 'Owner') $roleClass = 'badge-role-owner';
                        elseif ($user->role?->nama_role == 'Gudang') $roleClass = 'badge-role-gudang';
                    @endphp
                    <tr>
                        <td class="text-center text-muted px-3" style="font-size:12px;">{{ $users->firstItem() + $index }}</td>
                        <td class="fw-semibold" style="font-size:13px;color:var(--pb-text);">{{ $user->nama_lengkap }}</td>
                        <td>
                            <span class="font-monospace" style="font-size:12px;color:var(--text-secondary);">
                                <i class="bi bi-person me-1" aria-hidden="true"></i>{{ $user->username }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $roleClass }}" style="font-size:11px;font-weight:600;">
                                {{ $user->role?->nama_role ?? 'Tidak ada' }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($user->is_active)
                                <span class="badge bg-success text-white" style="font-size:11px;"><i class="bi bi-check-circle me-1" aria-hidden="true"></i>Aktif</span>
                            @else
                                <span class="badge bg-danger text-white" style="font-size:11px;"><i class="bi bi-x-circle me-1" aria-hidden="true"></i>Non-aktif</span>
                            @endif
                        </td>
                        <td class="text-center px-3">
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <a href="{{ route('pengaturan.users.edit', $user->id) }}" class="btn btn-sm btn-outline-primary py-1 px-2" title="Edit User">
                                    <i class="bi bi-pencil-square" aria-hidden="true"></i>
                                </a>
                                
                                @if($user->id !== auth()->id())
                                <form action="{{ route('pengaturan.users.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user {{ addslashes($user->nama_lengkap) }} secara permanen?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2" title="Hapus User">
                                        <i class="bi bi-trash" aria-hidden="true"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-people d-block mb-2" style="font-size:36px;opacity:0.3;" aria-hidden="true"></i>
                            <div style="font-size:13px;font-weight:600;color:var(--text-secondary);">Belum Ada Data User Ditemukan</div>
                            <div style="font-size:12px;color:var(--text-muted);margin-bottom:12px;">Tambahkan user pertama untuk kasir atau staf toko Anda.</div>
                            <a href="{{ route('pengaturan.users.create') }}" class="btn btn-pb btn-sm px-3">
                                <i class="bi bi-person-plus me-1" aria-hidden="true"></i>Tambah User Baru
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
        <div class="card-footer py-2 px-3 bg-transparent border-top">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div style="font-size:12px;color:var(--text-secondary);">
                    Menampilkan {{ $users->firstItem() }}–{{ $users->lastItem() }} dari {{ $users->total() }} user
                </div>
                <div>
                    {{ $users->links('pagination::bootstrap-5', ['class' => 'pagination-sm mb-0']) }}
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
