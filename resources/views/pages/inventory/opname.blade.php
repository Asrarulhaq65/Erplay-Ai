@extends('layouts.enterprise')
@section('title', 'Penyesuaian Stok (Stock Opname) — ' . (auth()->user()?->toko?->nama_toko ?? 'ERPlay AI'))

@push('styles')
<style>
    .opname-tambah-badge {
        background: rgba(21, 128, 61, 0.12);
        color: #15803D;
        border: 1px solid rgba(21, 128, 61, 0.2);
        font-size: 11px;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 6px;
    }
    [data-theme="dark"] .opname-tambah-badge {
        background: rgba(52, 211, 153, 0.15);
        color: #34D399;
        border-color: rgba(52, 211, 153, 0.25);
    }

    .opname-kurang-badge {
        background: rgba(220, 38, 38, 0.12);
        color: #B91C1C;
        border: 1px solid rgba(220, 38, 38, 0.2);
        font-size: 11px;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 6px;
    }
    [data-theme="dark"] .opname-kurang-badge {
        background: rgba(248, 113, 113, 0.15);
        color: #F87171;
        border-color: rgba(248, 113, 113, 0.25);
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
    <span aria-current="page">Penyesuaian Stok (Stock Opname)</span>
</nav>

<!-- Page Header & Action Bar -->
<div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-2">
    <div>
        <h1 class="h4 mb-0 fw-bold" style="font-family:'Plus Jakarta Sans',sans-serif;color:var(--pb-text);letter-spacing:-0.02em;">
            <i class="bi bi-box-seam me-2" aria-hidden="true"></i>Penyesuaian Stok (Stock Opname)
        </h1>
        <p class="mb-0 mt-1" style="color:var(--text-secondary);font-size:13px;">
            Audit selisih stok phisik gudang, penyesuaian barang rusak, retur, atau penambahan persediaan.
        </p>
    </div>
    <div>
        <a href="{{ route('master.produk.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-box-seam me-1" aria-hidden="true"></i>Katalog Produk
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Input Form Section -->
    <div class="col-12 col-md-4">
        <div class="card card-erp h-100">
            <div class="card-header py-3">
                <h2 class="h6 card-title mb-0" style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;">
                    <i class="bi bi-pencil-square me-2" aria-hidden="true"></i>Form Audit Opname
                </h2>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('inventory.opname.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="produk_id" class="form-label fw-semibold" style="font-size:13px;">Pilih Produk <span class="text-danger">*</span></label>
                        <select name="produk_id" id="produk_id" class="form-select form-select-sm" required onchange="updateStokTersedia()" aria-label="Pilih Produk">
                            <option value="">-- Pilih Produk --</option>
                            @foreach($produks as $p)
                                <option value="{{ $p->id }}" data-stok="{{ $p->stok }}">{{ $p->barcode }} - {{ $p->nama_produk }}</option>
                            @endforeach
                        </select>
                        <div class="mt-2 d-flex align-items-center justify-content-between" style="font-size: 12px;">
                            <span class="text-secondary">Stok Sistem Saat Ini:</span>
                            <span id="stok_tersedia_badge" class="badge bg-secondary">0 Item</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:13px;">Tipe Penyesuaian <span class="text-danger">*</span></label>
                        <div class="d-flex gap-2">
                            <input type="radio" class="btn-check" name="tipe_penyesuaian" id="tipe_tambah" value="Tambah_Stok" autocomplete="off" required>
                            <label class="btn btn-outline-success btn-sm w-50 py-2 fw-semibold" for="tipe_tambah" style="font-size:12px;">
                                <i class="bi bi-plus-circle me-1" aria-hidden="true"></i> Tambah Stok
                            </label>

                            <input type="radio" class="btn-check" name="tipe_penyesuaian" id="tipe_kurang" value="Kurang_Stok" autocomplete="off" required>
                            <label class="btn btn-outline-danger btn-sm w-50 py-2 fw-semibold" for="tipe_kurang" style="font-size:12px;">
                                <i class="bi bi-dash-circle me-1" aria-hidden="true"></i> Kurang Stok
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="jumlah_perubahan" class="form-label fw-semibold" style="font-size:13px;">Jumlah Perubahan Qty <span class="text-danger">*</span></label>
                        <input type="number" id="jumlah_perubahan" name="jumlah_perubahan" class="form-control form-control-sm" min="1" required placeholder="Contoh: 5" autocomplete="off">
                    </div>

                    <div class="mb-4">
                        <label for="keterangan" class="form-label fw-semibold" style="font-size:13px;">Alasan / Keterangan Audit <span class="text-danger">*</span></label>
                        <textarea id="keterangan" name="keterangan" class="form-control form-control-sm" rows="3" required placeholder="Contoh: Barang rusak, retur ke pabrik, selisih fisik gudang..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-pb w-100 py-2 fw-bold">
                        <i class="bi bi-check2-circle me-1" aria-hidden="true"></i>Simpan Penyesuaian Stok
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- History Table Section -->
    <div class="col-12 col-md-8">
        <div class="card card-erp h-100">
            <div class="card-header py-3">
                <h2 class="h6 card-title mb-0" style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;">
                    <i class="bi bi-clock-history me-2" aria-hidden="true"></i>Riwayat Audit Penyesuaian Stok
                </h2>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 520px;">
                    <table class="table table-sm align-middle table-hover mb-0" style="font-size:13px;">
                        <thead style="position: sticky; top: 0; z-index: 1;">
                            <tr>
                                <th class="ps-3 py-2" style="width:130px;" scope="col">Waktu Audit</th>
                                <th class="py-2" scope="col">Produk</th>
                                <th class="py-2 text-center" style="width:110px;" scope="col">Tipe</th>
                                <th class="py-2 text-center" style="width:70px;" scope="col">Awal</th>
                                <th class="py-2 text-center" style="width:70px;" scope="col">+/-</th>
                                <th class="py-2 text-center" style="width:70px;" scope="col">Akhir</th>
                                <th class="pe-3 py-2" scope="col">Keterangan / Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($history as $log)
                                <tr>
                                    <td class="ps-3 text-secondary" style="font-size:12px;">
                                        {{ $log->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td>
                                        <div class="font-monospace text-muted" style="font-size:11px;">{{ $log->produk->barcode ?? '-' }}</div>
                                        <div class="fw-semibold" style="color:var(--pb-text);font-size:12px;">{{ $log->produk->nama_produk ?? 'Produk Dihapus' }}</div>
                                    </td>
                                    <td class="text-center">
                                        @if($log->jumlah > 0)
                                            <span class="opname-tambah-badge"><i class="bi bi-arrow-up me-1" aria-hidden="true"></i>Tambah</span>
                                        @else
                                            <span class="opname-kurang-badge"><i class="bi bi-arrow-down me-1" aria-hidden="true"></i>Kurang</span>
                                        @endif
                                    </td>
                                    <td class="text-center text-muted" style="font-size:12px;">{{ $log->stok_awal }}</td>
                                    <td class="text-center fw-bold" style="font-size:13px; color: {{ $log->jumlah > 0 ? '#15803D' : '#B91C1C' }}">
                                        {{ $log->jumlah > 0 ? '+' : '' }}{{ $log->jumlah }}
                                    </td>
                                    <td class="text-center fw-bold" style="font-size:13px;color:var(--pb-text);">{{ $log->stok_akhir }}</td>
                                    <td class="pe-3 text-secondary" style="font-size:12px;">{{ $log->keterangan }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-box-seam d-block mb-2" style="font-size:36px;opacity:0.3;" aria-hidden="true"></i>
                                        <div style="font-size:13px;font-weight:600;color:var(--text-secondary);">Belum Ada Riwayat Penyesuaian Stok</div>
                                        <div style="font-size:12px;color:var(--text-muted);">Gunakan form di sebelah kiri untuk melakukan audit persediaan fisik.</div>
                                    </td>
                                </tr>
                            @endforelse
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
    function updateStokTersedia() {
        const select = document.getElementById('produk_id');
        const badge = document.getElementById('stok_tersedia_badge');
        
        if (select.selectedIndex > 0) {
            const option = select.options[select.selectedIndex];
            const stok = option.getAttribute('data-stok');
            badge.textContent = stok + ' Item';
            badge.className = 'badge bg-success text-white fw-bold';
        } else {
            badge.textContent = '0 Item';
            badge.className = 'badge bg-secondary';
        }
    }
</script>
@endpush
