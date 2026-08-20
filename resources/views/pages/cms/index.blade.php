@extends('layouts.cms')

@section('title', 'CMS Manajemen Toko')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1" style="font-weight: 700; color: var(--pb-text);">
                <i class="bi bi-shield-lock me-2 text-primary"></i>SaaS Management (Super Admin)
            </h4>
            <p class="text-muted mb-0" style="font-size: 13px;">Kelola daftar langganan dan akses dari seluruh tenant/toko yang terdaftar.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card card-erp">
        <div class="card-header bg-white pb-0 border-0 pt-4 px-4">
            <h6 class="card-title mb-0">Daftar Toko / Tenant Aktif</h6>
        </div>
        <div class="card-body px-4 pt-3 pb-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Toko / Usaha</th>
                            <th>Kontak & Alamat</th>
                            <th>Jumlah Akun</th>
                            <th>Status Langganan</th>
                            <th>Berakhir Pada</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tokos as $toko)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($toko->logo)
                                            <img src="{{ asset('storage/' . $toko->logo) }}" alt="Logo" class="rounded me-3" style="width: 40px; height: 40px; object-fit: cover; border: 1px solid #e2e8f0;">
                                        @else
                                            <div class="rounded me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: #e2e8f0; color: #64748b;">
                                                <i class="bi bi-shop"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <span class="fw-bold text-dark">{{ $toko->nama_toko }}</span>
                                            <div class="text-muted" style="font-size: 11px;">Bergabung: {{ $toko->created_at->format('d M Y') }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div><i class="bi bi-telephone-fill text-muted me-1"></i> {{ $toko->no_telepon ?? '-' }}</div>
                                    <div class="text-muted text-truncate" style="font-size: 11px; max-width: 200px;" title="{{ $toko->alamat }}">
                                        {{ $toko->alamat ?? '-' }}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $toko->users_count }} Akun</span>
                                </td>
                                <td>
                                    @if($toko->status_langganan === 'Aktif')
                                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Aktif</span>
                                    @elseif($toko->status_langganan === 'Trial')
                                        <span class="badge bg-info text-dark"><i class="bi bi-clock-history me-1"></i>Trial</span>
                                    @elseif($toko->status_langganan === 'Kedaluwarsa')
                                        <span class="badge bg-danger"><i class="bi bi-exclamation-triangle me-1"></i>Kedaluwarsa</span>
                                    @else
                                        <span class="badge bg-dark">{{ $toko->status_langganan ?? 'Trial' }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($toko->berakhir_pada)
                                        <span class="{{ $toko->berakhir_pada->isPast() ? 'text-danger fw-bold' : '' }}">
                                            {{ $toko->berakhir_pada->format('d M Y') }}
                                        </span>
                                    @else
                                        <span class="text-muted fst-italic">Selamanya</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEditLangganan-{{ $toko->id }}">
                                        <i class="bi bi-pencil-square"></i> Kelola
                                    </button>
                                </td>
                            </tr>

                            <!-- Modal Edit Langganan -->
                            <div class="modal fade" id="modalEditLangganan-{{ $toko->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('cms.toko.update_subscription', $toko->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header">
                                                <h5 class="modal-title">Kelola Langganan: {{ $toko->nama_toko }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Status Langganan <span class="text-danger">*</span></label>
                                                    <select class="form-select" name="status_langganan" required>
                                                        <option value="Trial" {{ $toko->status_langganan == 'Trial' ? 'selected' : '' }}>Trial (Uji Coba)</option>
                                                        <option value="Aktif" {{ $toko->status_langganan == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                                                        <option value="Kedaluwarsa" {{ $toko->status_langganan == 'Kedaluwarsa' ? 'selected' : '' }}>Kedaluwarsa</option>
                                                        <option value="Nonaktif" {{ $toko->status_langganan == 'Nonaktif' ? 'selected' : '' }}>Nonaktif (Blokir)</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Berakhir Pada (Tanggal Kedaluwarsa)</label>
                                                    <input type="date" class="form-control" name="berakhir_pada" value="{{ $toko->berakhir_pada ? $toko->berakhir_pada->format('Y-m-d') : '' }}">
                                                    <div class="form-text">Kosongkan jika langganan aktif selamanya.</div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-save me-1"></i> Simpan Perubahan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    Belum ada toko yang mendaftar di sistem ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
