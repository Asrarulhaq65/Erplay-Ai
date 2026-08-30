@extends('layouts.enterprise')
@section('title', 'Rekap Laporan Penjualan — ' . (auth()->user()?->toko?->nama_toko ?? 'ERPlay AI'))

@push('styles')
<style>
    /* ── Compact Report Stat Cards (5 Cards Layout) ── */
    .rekap-stat-card {
        background: var(--bg-card);
        border: 1px solid var(--border-light);
        border-radius: 10px;
        padding: 9px 12px;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: border-color 0.15s ease, box-shadow 0.15s ease, var(--theme-transition);
        height: 100%;
    }
    .rekap-stat-card:hover {
        border-color: var(--pb-mid);
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.04);
    }
    .rekap-stat-icon {
        width: 34px; height: 34px;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: 15px;
        flex-shrink: 0;
    }
    .rekap-stat-label {
        font-size: 10px;
        font-weight: 600;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.03em;
        line-height: 1.2;
        margin-bottom: 2px;
    }
    .rekap-stat-val {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 14.5px;
        font-weight: 800;
        color: var(--pb-text);
        line-height: 1.15;
        letter-spacing: -0.01em;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Status Badges */
    .badge-lunas {
        background: rgba(13, 78, 86, 0.12);
        color: var(--pb-dark);
        border: 1px solid rgba(13, 78, 86, 0.2);
        font-size: 11px;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 6px;
    }
    [data-theme="dark"] .badge-lunas {
        background: rgba(77, 184, 196, 0.15);
        color: #4DB8C4;
        border-color: rgba(77, 184, 196, 0.25);
    }

    .badge-kredit {
        background: rgba(217, 119, 6, 0.12);
        color: #B46B18;
        border: 1px solid rgba(217, 119, 6, 0.2);
        font-size: 11px;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 6px;
    }
    [data-theme="dark"] .badge-kredit {
        background: rgba(251, 191, 36, 0.15);
        color: #FBBF24;
        border-color: rgba(251, 191, 36, 0.25);
    }

    /* Bootstrap's warning row inherits light table text in dark mode. */
    [data-theme="dark"] .table-warning,
    [data-theme="dark"] .table-warning > td,
    [data-theme="dark"] .table-warning > th {
        --bs-table-color: #3F2A00;
        --bs-table-bg: #FFF1C2;
        --bs-table-border-color: #E8C96A;
        color: #3F2A00 !important;
    }
    [data-theme="dark"] .table-warning a,
    [data-theme="dark"] .table-warning .text-secondary,
    [data-theme="dark"] .table-warning .text-muted {
        color: #5C4300 !important;
    }
    [data-theme="dark"] .table-warning .text-primary {
        color: #075985 !important;
    }
</style>
@endpush

@section('content')
<!-- Breadcrumb Navigation -->
<nav class="erp-breadcrumb d-none d-md-block" aria-label="Breadcrumb">
    <a href="{{ url('/dashboard') }}" aria-label="Beranda Dashboard"><i class="bi bi-house-door"></i></a>
    <i class="bi bi-chevron-right mx-1" style="font-size:9px;" aria-hidden="true"></i>
    <span aria-current="page">Rekap Laporan Penjualan</span>
</nav>

<!-- Page Header & Action Bar -->
<div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
    <div>
        <h1 class="h4 mb-0 fw-bold" style="font-family:'Plus Jakarta Sans',sans-serif;color:var(--pb-text);letter-spacing:-0.02em;">
            <i class="bi bi-journal-text me-2" aria-hidden="true"></i>Rekap Laporan Penjualan
        </h1>
        <p class="mb-0 mt-1" style="color:var(--text-secondary);font-size:13px;">
            Rekapitulasi rinci pendapatan transaksi toko, audit pembayaran, dan rincian struk invoice.
        </p>
    </div>
    <div>
        <a href="{{ url('/pos/standard') }}" class="btn btn-sm btn-pb">
            <i class="bi bi-grid-3x3-gap-fill me-1" aria-hidden="true"></i>POS Kasir
        </a>
    </div>
</div>

@php
    $totalOmzet = $penjualan->sum('total_bayar');
    $totalTrx = $penjualan->count();
    $avgTrx = $totalTrx > 0 ? ($totalOmzet / $totalTrx) : 0;
    $totalLunas = $penjualan->where('status_pembayaran', 'Lunas')->sum('total_bayar');
@endphp

<!-- Stat Summary Row (5 Cards in 1 Row on Desktop) -->
<div class="row g-2 mb-3 row-cols-2 row-cols-sm-3 row-cols-md-3 row-cols-xl-5">
    <div class="col">
        <div class="rekap-stat-card">
            <div class="rekap-stat-icon" style="background:rgba(13, 78, 86, 0.1);color:var(--pb-dark);" aria-hidden="true"><i class="bi bi-cash-stack"></i></div>
            <div class="overflow-hidden">
                <div class="rekap-stat-label">Total Omzet</div>
                <div class="rekap-stat-val" title="Rp {{ number_format($totalOmzet, 0, ',', '.') }}">Rp {{ number_format($totalOmzet, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="rekap-stat-card">
            <div class="rekap-stat-icon" style="background:rgba(29, 78, 216, 0.1);color:#1D4ED8;" aria-hidden="true"><i class="bi bi-receipt"></i></div>
            <div class="overflow-hidden">
                <div class="rekap-stat-label">Total Transaksi</div>
                <div class="rekap-stat-val">{{ number_format($totalTrx, 0, ',', '.') }} Trx</div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="rekap-stat-card">
            <div class="rekap-stat-icon" style="background:rgba(21, 128, 61, 0.1);color:#15803D;" aria-hidden="true"><i class="bi bi-graph-up-arrow"></i></div>
            <div class="overflow-hidden">
                <div class="rekap-stat-label">Rata-Rata / Trx</div>
                <div class="rekap-stat-val" title="Rp {{ number_format($avgTrx, 0, ',', '.') }}">Rp {{ number_format($avgTrx, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="rekap-stat-card">
            <div class="rekap-stat-icon" style="background:rgba(126, 34, 206, 0.1);color:#7E22CE;" aria-hidden="true"><i class="bi bi-check-circle"></i></div>
            <div class="overflow-hidden">
                <div class="rekap-stat-label">Omzet Lunas</div>
                <div class="rekap-stat-val" title="Rp {{ number_format($totalLunas, 0, ',', '.') }}">Rp {{ number_format($totalLunas, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="rekap-stat-card" style="border-color:rgba(217,119,6,.3);">
            <div class="rekap-stat-icon" style="background:rgba(217,119,6,.12);color:#B45309;" aria-hidden="true"><i class="bi bi-hourglass-split"></i></div>
            <div class="overflow-hidden">
                <div class="rekap-stat-label">Piutang Belum Lunas</div>
                <div class="rekap-stat-val" style="color:#B45309;" title="Rp {{ number_format($totalPiutang, 0, ',', '.') }}">Rp {{ number_format($totalPiutang, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Bar Card (Slim & Compact) -->
<div class="card card-erp mb-3 p-2 px-3 shadow-sm" style="border-radius: 10px;">
    <form method="GET" action="{{ route('laporan.rekap-penjualan') }}" class="row g-2 align-items-end">
        <div class="col-12 col-md-auto pe-md-2 border-md-end" style="border-color: var(--border-light) !important;">
            <button type="submit" class="btn btn-pb btn-sm px-3 d-flex align-items-center justify-content-center w-100" style="height:29px; font-size:11.5px; border-radius:6px; font-weight:600;">
                <i class="bi bi-funnel me-1" aria-hidden="true"></i> Filter
            </button>
        </div>
        <div class="col-6 col-md-2">
            <label for="start_date" class="form-label mb-1 fw-semibold text-secondary" style="font-size:10.5px; line-height:1.2;">Mulai</label>
            <input type="date" id="start_date" name="start_date" class="form-control form-control-sm py-0 px-2" style="font-size:11.5px; height:29px; border-radius:6px;" value="{{ $startDate }}" aria-label="Tanggal Mulai">
        </div>
        <div class="col-6 col-md-2">
            <label for="end_date" class="form-label mb-1 fw-semibold text-secondary" style="font-size:10.5px; line-height:1.2;">Sampai</label>
            <input type="date" id="end_date" name="end_date" class="form-control form-control-sm py-0 px-2" style="font-size:11.5px; height:29px; border-radius:6px;" value="{{ $endDate }}" aria-label="Tanggal Akhir">
        </div>
        <div class="col-12 col-md-3">
            <label for="pelanggan_id" class="form-label mb-1 fw-semibold text-secondary" style="font-size:10.5px; line-height:1.2;">Pelanggan</label>
            <select id="pelanggan_id" name="pelanggan_id" class="form-select form-select-sm py-0 px-2" style="font-size:11.5px; height:29px; border-radius:6px;" aria-label="Filter Pelanggan">
                <option value="">-- Semua Pelanggan --</option>
                <option value="umum" {{ $pelangganId === 'umum' ? 'selected' : '' }}>Pelanggan Umum (Walk-in)</option>
                @foreach($pelanggans as $plg)
                    <option value="{{ $plg->id }}" {{ $pelangganId == $plg->id ? 'selected' : '' }}>
                        {{ $plg->nama_pelanggan }} ({{ $plg->status_pelanggan }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label for="metode_pembayaran" class="form-label mb-1 fw-semibold text-secondary" style="font-size:10.5px; line-height:1.2;">Metode</label>
            <select id="metode_pembayaran" name="metode_pembayaran" class="form-select form-select-sm py-0 px-2" style="font-size:11.5px; height:29px; border-radius:6px;" aria-label="Filter Metode Pembayaran">
                <option value="">-- Semua Metode --</option>
                <option value="Tunai" {{ $metode == 'Tunai' ? 'selected' : '' }}>Tunai</option>
                <option value="Kredit" {{ $metode == 'Kredit' ? 'selected' : '' }}>Kredit</option>
                <option value="Digital Payment" {{ $metode == 'Digital Payment' ? 'selected' : '' }}>Digital</option>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label for="status_pembayaran" class="form-label mb-1 fw-semibold text-secondary" style="font-size:10.5px; line-height:1.2;">Status</label>
            <select id="status_pembayaran" name="status_pembayaran" class="form-select form-select-sm py-0 px-2" style="font-size:11.5px; height:29px; border-radius:6px;" aria-label="Filter Status Pembayaran">
                <option value="">-- Semua Status --</option>
                <option value="Lunas" {{ ($statusPembayaran ?? '') == 'Lunas' ? 'selected' : '' }}>✅ Lunas</option>
                <option value="Belum Lunas" {{ ($statusPembayaran ?? '') == 'Belum Lunas' ? 'selected' : '' }}>⏳ Belum Lunas</option>
            </select>
        </div>
    </form>
</div>

<!-- Main Sales Data Table -->
<div class="card card-erp mb-4">
    <div class="table-responsive">
        <table class="table table-sm align-middle table-hover mb-0" style="font-size:13px;">
            <thead>
                <tr>
                    <th class="ps-3 py-2" style="width:130px;" scope="col">No. Invoice</th>
                    <th class="py-2" style="width:120px;" scope="col">Waktu</th>
                    <th class="py-2" scope="col">Pelanggan</th>
                    <th class="py-2 text-center" style="width:90px;" scope="col">Metode</th>
                    <th class="py-2 text-center" style="width:100px;" scope="col">Status</th>
                    <th class="py-2 text-end" style="width:130px;" scope="col">Total</th>
                    <th class="py-2 text-end" style="width:130px;" scope="col">Sisa Piutang</th>
                    <th class="py-2" style="width:110px;" scope="col">Kasir</th>
                    <th class="pe-3 py-2 text-center" style="width:150px;" scope="col">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($penjualan as $trx)
                    <tr class="{{ $trx->status_pembayaran === 'Belum Lunas' ? 'table-warning' : '' }}">
                        <td class="ps-3 font-monospace fw-semibold" style="font-size:11.5px;">
                            {{ $trx->nomor_invoice }}
                            @if($trx->tanggal_jatuh_tempo)
                                <div class="text-muted" style="font-size:10px;"><i class="bi bi-calendar2-event me-1"></i>JT: {{ $trx->tanggal_jatuh_tempo->format('d/m/Y') }}</div>
                            @endif
                        </td>
                        <td class="text-secondary" style="font-size:11.5px;">{{ $trx->created_at->format('d/m/Y H:i') }}</td>
                        <td class="fw-semibold" style="color:var(--pb-text);font-size:12.5px;">{{ $trx->pelanggan->nama_pelanggan ?? 'Pelanggan Umum' }}</td>
                        <td class="text-center">
                            @if($trx->metode_pembayaran == 'Tunai')
                                <span class="badge bg-success text-white" style="font-size:10.5px;">Tunai</span>
                            @elseif($trx->metode_pembayaran == 'Kredit')
                                <span class="badge-kredit">Kredit</span>
                            @else
                                <span class="badge-lunas">Digital</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($trx->status_pembayaran == 'Lunas')
                                <span class="badge-lunas"><i class="bi bi-check-circle me-1"></i>Lunas</span>
                            @else
                                <span class="badge" style="background:rgba(220,38,38,.12);color:#DC2626;border:1px solid rgba(220,38,38,.25);font-size:10.5px;font-weight:600;padding:3px 7px;border-radius:6px;"><i class="bi bi-clock me-1"></i>Belum Lunas</span>
                            @endif
                        </td>
                        <td class="text-end fw-bold" style="font-size:13px;color:var(--pb-text);">Rp {{ number_format($trx->total_bayar, 0, ',', '.') }}
                            @if($trx->metode_pembayaran === 'Kredit' && (float)$trx->uang_muka > 0 && $trx->status_pembayaran !== 'Lunas')
                                <div style="font-size:10px;color:var(--text-secondary);">DP: Rp {{ number_format($trx->uang_muka, 0, ',', '.') }}</div>
                            @endif
                        </td>
                        <td class="text-end" style="font-size:13px;">
                            @if($trx->metode_pembayaran === 'Kredit' && $trx->status_pembayaran === 'Belum Lunas')
                                <span class="fw-bold" style="color:#DC2626;">Rp {{ number_format($trx->sisa_piutang, 0, ',', '.') }}</span>
                            @elseif($trx->metode_pembayaran === 'Kredit' && $trx->status_pembayaran === 'Lunas')
                                <span class="text-success fw-semibold" style="font-size:11px;"><i class="bi bi-check-all"></i> Lunas</span>
                            @else
                                <span class="text-muted" style="font-size:11px;">—</span>
                            @endif
                        </td>
                        <td class="text-secondary" style="font-size:11.5px;">{{ $trx->user->nama_lengkap ?? '-' }}</td>
                        <td class="pe-3 text-center">
                            <div class="d-flex gap-1 justify-content-center flex-wrap">
                                <button type="button" class="btn btn-outline-primary btn-sm py-0 px-2" style="font-size:11px;" title="Detail transaksi" onclick="showDetail({{ $trx->id }})">
                                    <i class="bi bi-eye"></i>
                                </button>
                                @if($trx->metode_pembayaran === 'Kredit')
                                    <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2" style="font-size:11px;" title="Riwayat cicilan" onclick="showRiwayat({{ $trx->id }}, '{{ $trx->nomor_invoice }}')">
                                        <i class="bi bi-clock-history"></i>
                                    </button>
                                    @if($trx->status_pembayaran === 'Belum Lunas')
                                        <button type="button" class="btn btn-warning btn-sm py-0 px-2" style="font-size:11px;" title="Catat pembayaran" onclick="openBayarModal({{ $trx->id }}, '{{ $trx->nomor_invoice }}', {{ $trx->sisa_piutang }})">
                                            <i class="bi bi-plus-circle me-1"></i>Bayar
                                        </button>
                                        <button type="button" class="btn btn-success btn-sm py-0 px-2" style="font-size:11px;" title="Tandai lunas sekarang" onclick="konfirmasiLunas({{ $trx->id }}, '{{ $trx->nomor_invoice }}', {{ $trx->sisa_piutang }})">
                                            <i class="bi bi-check2-all"></i>
                                        </button>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox d-block mb-2" style="font-size:36px;opacity:0.3;" aria-hidden="true"></i>
                            <div style="font-size:13px;font-weight:600;color:var(--text-secondary);">Tidak Ada Transaksi Pada Periode Ini</div>
                            <div style="font-size:12px;color:var(--text-muted);">Coba ubah filter rentang tanggal atau metode pembayaran di atas.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="titleModalDetail" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content" style="border-radius:12px;overflow:hidden;">
            <div class="modal-header py-3 px-3">
                <h3 class="modal-title h6 fw-bold mb-0" id="titleModalDetail" style="color:var(--pb-text);">
                    <i class="bi bi-receipt me-2"></i>Detail Transaksi - <span id="modalInvoice"></span>
                </h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle table-hover mb-0" style="font-size:12px;">
                        <thead>
                            <tr>
                                <th class="ps-3 py-2 text-center" style="width:40px;">No</th>
                                <th class="py-2">Nama Produk</th>
                                <th class="py-2 text-end" style="width:120px;">Harga</th>
                                <th class="py-2 text-center" style="width:60px;">Qty</th>
                                <th class="pe-3 py-2 text-end" style="width:130px;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="modalDetailBody"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer py-3 px-3 justify-content-between border-top">
                <div style="font-size:12px;color:var(--text-secondary);">
                    <div>Metode: <strong id="modalMetode" style="color:var(--pb-text);">-</strong></div>
                    <div>Kasir: <strong id="modalKasir" style="color:var(--pb-text);">-</strong></div>
                </div>
                <div class="text-end">
                    <div style="font-size:11px;color:var(--text-secondary);text-transform:uppercase;">Grand Total:</div>
                    <div class="fw-bold" style="font-size:18px;color:var(--pb-dark);" id="modalTotal">Rp 0</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Catat Pembayaran Kredit -->
<div class="modal fade" id="modalBayarKredit" tabindex="-1" aria-labelledby="titleBayarKredit" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content" style="border-radius:12px;">
            <div class="modal-header py-3 px-4" style="background:var(--pb-dark);">
                <h5 class="modal-title text-white fw-bold mb-0" id="titleBayarKredit" style="font-size:14px;">
                    <i class="bi bi-cash-coin me-2"></i>Catat Pembayaran Kredit
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3 p-3 rounded" style="background:rgba(217,119,6,.1);border:1px solid rgba(217,119,6,.2);">
                    <div style="font-size:11px;color:var(--text-secondary);">Invoice</div>
                    <div class="fw-bold" style="color:var(--pb-text);" id="bayarInvoiceLabel">—</div>
                    <div class="mt-1" style="font-size:11px;color:var(--text-secondary);">Sisa Piutang</div>
                    <div class="fw-bold" style="font-size:18px;color:#DC2626;" id="bayarSisaLabel">Rp 0</div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold" style="font-size:12px;">Jumlah Bayar (Rp)</label>
                    <input type="number" id="bayarJumlah" class="form-control" min="1" placeholder="Masukkan nominal" style="font-size:14px;">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold" style="font-size:12px;">Keterangan (opsional)</label>
                    <input type="text" id="bayarKeterangan" class="form-control form-control-sm" placeholder="cth: cicilan 1">
                </div>
                <div class="mb-1">
                    <label class="form-label fw-semibold" style="font-size:12px;">Tanggal Bayar</label>
                    <input type="date" id="bayarTanggal" class="form-control form-control-sm" value="{{ now()->toDateString() }}">
                </div>
            </div>
            <div class="modal-footer py-3 px-4 border-top">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-warning btn-sm fw-bold px-4" id="btnSimpanBayar">
                    <i class="bi bi-check-lg me-1"></i>Simpan Pembayaran
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Riwayat Cicilan -->
<div class="modal fade" id="modalRiwayatKredit" tabindex="-1" aria-labelledby="titleRiwayatKredit" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:12px;overflow:hidden;">
            <div class="modal-header py-3 px-4">
                <h5 class="modal-title fw-bold mb-0" id="titleRiwayatKredit" style="font-size:14px;color:var(--pb-text);">
                    <i class="bi bi-clock-history me-2"></i>Riwayat Pembayaran - <span id="riwayatInvoiceLabel"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body p-4" id="riwayatBody">
                <div class="text-center py-3"><div class="spinner-border spinner-border-sm text-secondary"></div></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const csrfToken = '{{ csrf_token() }}';
    let _activePenjualanId = null;

    function formatRupiah(amount) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);
    }

    // ── Detail Modal ──────────────────────────────────────────────────────
    async function showDetail(id) {
        const modal = new bootstrap.Modal(document.getElementById('detailModal'));
        document.getElementById('modalInvoice').textContent = 'Memuat...';
        document.getElementById('modalMetode').textContent = '-';
        document.getElementById('modalKasir').textContent = '-';
        document.getElementById('modalTotal').textContent = 'Rp 0';
        document.getElementById('modalDetailBody').innerHTML = '<tr><td colspan="5" class="text-center py-4"><div class="spinner-border spinner-border-sm text-secondary"></div></td></tr>';
        modal.show();
        try {
            const res = await fetch(`{{ url('/api/penjualan/detail') }}/${id}`, { headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            if (data.success) {
                const trx = data.data;
                document.getElementById('modalInvoice').textContent = trx.nomor_invoice || '-';
                document.getElementById('modalMetode').textContent = trx.metode_pembayaran || '-';
                document.getElementById('modalKasir').textContent = trx.user?.nama_lengkap || '-';
                document.getElementById('modalTotal').textContent = formatRupiah(trx.total_bayar);
                let html = trx.details?.length
                    ? trx.details.map((item, i) => `<tr>
                        <td class="ps-3 py-2 text-center text-muted">${i + 1}</td>
                        <td class="py-2 fw-semibold">${item.produk?.nama_produk ?? '-'}</td>
                        <td class="py-2 text-end">${formatRupiah(item.harga_satuan)}</td>
                        <td class="py-2 text-center fw-bold">${item.qty}</td>
                        <td class="pe-3 py-2 text-end fw-bold" style="color:var(--pb-text);">${formatRupiah(item.subtotal)}</td>
                      </tr>`).join('')
                    : '<tr><td colspan="5" class="text-center py-3 text-muted">Tidak ada item.</td></tr>';
                document.getElementById('modalDetailBody').innerHTML = html;
            }
        } catch (e) {
            document.getElementById('modalDetailBody').innerHTML = '<tr><td colspan="5" class="text-center text-danger py-3">Gagal memuat data.</td></tr>';
        }
    }

    // ── Bayar Kredit Modal ────────────────────────────────────────────────
    function openBayarModal(id, invoice, sisa) {
        _activePenjualanId = id;
        document.getElementById('bayarInvoiceLabel').textContent = invoice;
        document.getElementById('bayarSisaLabel').textContent = formatRupiah(sisa);
        document.getElementById('bayarJumlah').value = sisa;
        document.getElementById('bayarJumlah').max = sisa;
        document.getElementById('bayarKeterangan').value = '';
        document.getElementById('bayarTanggal').value = '{{ now()->toDateString() }}';
        new bootstrap.Modal(document.getElementById('modalBayarKredit')).show();
    }

    document.getElementById('btnSimpanBayar').addEventListener('click', async function () {
        const jumlah = parseFloat(document.getElementById('bayarJumlah').value);
        if (!jumlah || jumlah <= 0) { alert('Masukkan jumlah bayar yang valid.'); return; }
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...';
        try {
            const res = await fetch(`/penjualan/${_activePenjualanId}/bayar`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({
                    jumlah,
                    keterangan: document.getElementById('bayarKeterangan').value,
                    tanggal_bayar: document.getElementById('bayarTanggal').value,
                })
            });
            const data = await res.json();
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('modalBayarKredit'))?.hide();
                alert(data.message);
                location.reload();
            } else {
                alert('Gagal: ' + data.message);
            }
        } catch (e) { alert('Terjadi kesalahan jaringan.'); }
        finally { btn.disabled = false; btn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Simpan Pembayaran'; }
    });

    // ── Konfirmasi Lunas ──────────────────────────────────────────────────
    async function konfirmasiLunas(id, invoice, sisa) {
        if (!confirm(`Tandai invoice ${invoice} sebagai LUNAS?\nSisa Rp ${sisa.toLocaleString('id-ID')} akan dicatat sebagai pelunasan.`)) return;
        try {
            const res = await fetch(`/penjualan/${id}/lunas`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ keterangan: 'Pelunasan penuh' })
            });
            const data = await res.json();
            alert(data.message);
            if (data.success) location.reload();
        } catch (e) { alert('Gagal terhubung ke server.'); }
    }

    // ── Riwayat Cicilan ───────────────────────────────────────────────────
    async function showRiwayat(id, invoice) {
        document.getElementById('riwayatInvoiceLabel').textContent = invoice;
        document.getElementById('riwayatBody').innerHTML = '<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-secondary"></div></div>';
        new bootstrap.Modal(document.getElementById('modalRiwayatKredit')).show();
        try {
            const res = await fetch(`/penjualan/${id}/riwayat-bayar`, { headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            if (data.success) {
                const rows = data.riwayat.length
                    ? data.riwayat.map(r => `<tr>
                        <td style="font-size:12px;">${r.tanggal_bayar}</td>
                        <td class="fw-bold" style="color:var(--pb-dark);font-size:13px;">${formatRupiah(r.jumlah)}</td>
                        <td style="font-size:11.5px;color:var(--text-secondary);">${r.keterangan || '—'}</td>
                        <td style="font-size:11px;">${r.user}</td>
                      </tr>`).join('')
                    : '<tr><td colspan="4" class="text-center text-muted py-3">Belum ada pembayaran dicatat.</td></tr>';
                document.getElementById('riwayatBody').innerHTML = `
                    <div class="d-flex justify-content-between mb-3" style="font-size:12px;">
                        <span>Total: <strong>${formatRupiah(data.total_bayar)}</strong></span>
                        <span>DP: <strong>${formatRupiah(data.uang_muka)}</strong></span>
                        <span>Sisa: <strong style="color:${data.sisa_piutang > 0 ? '#DC2626' : '#15803D'}">${formatRupiah(data.sisa_piutang)}</strong></span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm mb-0" style="font-size:12px;">
                            <thead><tr><th>Tanggal</th><th>Jumlah</th><th>Keterangan</th><th>Petugas</th></tr></thead>
                            <tbody>${rows}</tbody>
                        </table>
                    </div>`;
            }
        } catch (e) {
            document.getElementById('riwayatBody').innerHTML = '<div class="text-center text-danger py-3">Gagal memuat riwayat.</div>';
        }
    }
</script>
@endpush
