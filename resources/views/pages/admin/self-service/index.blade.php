@extends('layouts.enterprise')
@section('title', 'Verifikasi Pesanan Self Service — ' . (auth()->user()?->toko?->nama_toko ?? 'ERPlay AI'))

@section('content')
<!-- Breadcrumb Navigation -->
<nav class="erp-breadcrumb d-none d-md-block" aria-label="Breadcrumb">
    <a href="{{ url('/dashboard') }}" aria-label="Beranda Dashboard"><i class="bi bi-house-door"></i></a>
    <i class="bi bi-chevron-right mx-1" style="font-size:9px;" aria-hidden="true"></i>
    <span>POS Kasir</span>
    <i class="bi bi-chevron-right mx-1" style="font-size:9px;" aria-hidden="true"></i>
    <span aria-current="page">Pesanan Self-Service</span>
</nav>

<!-- Page Header & Action Bar -->
<div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
    <div>
        <h1 class="h4 mb-0 fw-bold" style="font-family:'Plus Jakarta Sans',sans-serif;color:var(--pb-text);letter-spacing:-0.02em;">
            <i class="bi bi-qr-code-scan me-2 text-primary" aria-hidden="true"></i>Verifikasi Pesanan Self-Service Pelanggan
        </h1>
        <p class="mb-0 mt-1" style="color:var(--text-secondary);font-size:13px;">
            Periksa pesanan belanja dari ponsel pelanggan, verifikasi metode pembayaran, dan terbitkan struk resmi.
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('self-service.index') }}" target="_blank" class="btn btn-sm btn-outline-primary fw-bold">
            <i class="bi bi-phone me-1"></i>Buka Kios Mobile (/self-service)
        </a>
        <a href="{{ url('/pos/standard') }}" class="btn btn-sm btn-pb fw-bold">
            <i class="bi bi-grid-3x3-gap-fill me-1"></i>POS Kasir
        </a>
    </div>
</div>

<!-- Sound Alert Toggle Indicator Bar -->
<div class="alert alert-info py-2 px-3 mb-3 d-flex align-items-center justify-content-between" style="font-size:12.5px;border-radius:10px;">
    <div>
        <i class="bi bi-bell-fill text-warning me-2"></i>
        <strong>Auto-Polling Real-Time:</strong> Notifikasi suara bel akan otomatis berbunyi ketika pelanggan mengirim pesanan baru.
    </div>
    <button type="button" id="btnTestSound" class="btn btn-sm btn-light py-0 px-2 fw-bold text-dark" style="font-size:11px;border-radius:6px;">
        <i class="bi bi-volume-up-fill me-1"></i>Tes Suara Bel
    </button>
</div>

<!-- Status Filter Tabs -->
<div class="card card-erp mb-3">
    <div class="card-header py-2 px-3 d-flex align-items-center justify-content-between">
        <ul class="nav nav-pills card-header-pills" style="font-size:12.5px;">
            <li class="nav-item">
                <a class="nav-link {{ $status === 'Pending' ? 'active fw-bold' : '' }}" href="{{ route('admin.self-service.index', ['status' => 'Pending']) }}">
                    <i class="bi bi-hourglass-split me-1"></i>Pending
                    @if($pendingCount > 0)
                        <span class="badge bg-danger ms-1" id="badgePendingCount">{{ $pendingCount }}</span>
                    @endif
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $status === 'Verified' ? 'active fw-bold' : '' }}" href="{{ route('admin.self-service.index', ['status' => 'Verified']) }}">
                    <i class="bi bi-check-circle-fill me-1"></i>Sudah Diverifikasi
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $status === 'Rejected' ? 'active fw-bold' : '' }}" href="{{ route('admin.self-service.index', ['status' => 'Rejected']) }}">
                    <i class="bi bi-x-circle-fill me-1"></i>Ditolak
                </a>
            </li>
        </ul>
    </div>
    <div class="table-responsive">
        <table class="table table-sm align-middle table-hover mb-0" style="font-size:13px;">
            <thead>
                <tr>
                    <th class="ps-3 py-2" style="width:140px;" scope="col">No. Pesanan</th>
                    <th class="py-2" style="width:140px;" scope="col">Waktu Masuk</th>
                    <th class="py-2" scope="col">Nama Pelanggan / Meja</th>
                    <th class="py-2 text-center" style="width:130px;" scope="col">Metode Bayar</th>
                    <th class="py-2 text-end" style="width:140px;" scope="col">Total Belanja</th>
                    <th class="py-2 text-center" style="width:110px;" scope="col">Status</th>
                    <th class="pe-3 py-2 text-center" style="width:160px;" scope="col">Aksi Kasir</th>
                </tr>
            </thead>
            <tbody id="ssOrderTableBody">
                @forelse($orders as $o)
                    <tr id="order-row-{{ $o->id }}">
                        <td class="ps-3 font-monospace fw-bold text-primary">{{ $o->nomor_pesanan }}</td>
                        <td class="text-secondary" style="font-size:12px;">{{ $o->created_at->format('d/m/Y H:i') }}</td>
                        <td class="fw-semibold" style="color:var(--pb-text);">{{ $o->nama_pelanggan }}</td>
                        <td class="text-center">
                            @if($o->metode_pembayaran === 'Tunai')
                                <span class="badge bg-success text-white">Tunai / Kasir</span>
                            @elseif($o->metode_pembayaran === 'Digital Payment')
                                <span class="badge bg-info text-dark">Digital QRIS</span>
                            @else
                                <span class="badge bg-warning text-dark">Kredit / Piutang</span>
                            @endif
                        </td>
                        <td class="text-end fw-bold fs-6" style="color:var(--pb-text);">Rp {{ number_format($o->total_bayar, 0, ',', '.') }}</td>
                        <td class="text-center">
                            @if($o->status === 'Pending')
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1" style="font-size:11px;font-weight:700;">
                                    <i class="bi bi-clock-history me-1"></i>PENDING
                                </span>
                            @elseif($o->status === 'Verified')
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size:11px;font-weight:700;">
                                    <i class="bi bi-check-circle-fill me-1"></i>VERIFIED
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary px-2 py-1" style="font-size:11px;font-weight:700;">REJECTED</span>
                            @endif
                        </td>
                        <td class="pe-3 text-center">
                            @if($o->status === 'Pending')
                                <button type="button" class="btn btn-primary btn-sm py-1 px-2 fw-bold" onclick="openVerifyModal({{ json_encode($o) }})">
                                    <i class="bi bi-shield-check me-1"></i>Periksa
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm py-1 px-2 ms-1" onclick="rejectOrder({{ $o->id }})" title="Tolak Pesanan">
                                    <i class="bi bi-trash"></i>
                                </button>
                            @else
                                <button type="button" class="btn btn-outline-secondary btn-sm py-1 px-2" onclick="openVerifyModal({{ json_encode($o) }})">
                                    <i class="bi bi-eye me-1"></i>Detail
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                            Tidak ada pesanan Self Service dalam kategori ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer py-2 px-3 border-top d-flex justify-content-end">
        {{ $orders->appends(['status' => $status])->links() }}
    </div>
</div>

<!-- Modal Inspect & Verify Order -->
<div class="modal fade" id="verifyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius:14px;">
            <div class="modal-header py-3 px-3 border-bottom">
                <h5 class="modal-title h6 fw-bold mb-0" style="color:var(--pb-text);">
                    <i class="bi bi-receipt me-2 text-primary"></i>Verifikasi Pesanan Self Service — <span id="vNomorPesanan" class="font-monospace text-primary"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <div class="row g-3 mb-3">
                    <div class="col-6 col-md-3">
                        <div class="p-2 rounded bg-light border">
                            <small class="text-muted d-block" style="font-size:11px;">NAMA PELANGGAN</small>
                            <strong id="vNamaPelanggan" style="font-size:13px;">-</strong>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-2 rounded bg-light border">
                            <small class="text-muted d-block" style="font-size:11px;">METODE BAYAR</small>
                            <strong id="vMetodeBayar" style="font-size:13px;" class="text-success">-</strong>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-2 rounded bg-light border">
                            <small class="text-muted d-block" style="font-size:11px;">STATUS PESANAN</small>
                            <strong id="vStatus" style="font-size:13px;">-</strong>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-2 rounded bg-light border">
                            <small class="text-muted d-block" style="font-size:11px;">WAKTU DIBUAT</small>
                            <strong id="vCreated" style="font-size:13px;">-</strong>
                        </div>
                    </div>
                </div>

                <div class="table-responsive border rounded mb-3">
                    <table class="table table-sm align-middle mb-0" style="font-size:12.5px;">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3 py-2" style="width:40px;">No</th>
                                <th class="py-2">Nama Produk</th>
                                <th class="py-2 text-end" style="width:120px;">Harga Satuan</th>
                                <th class="py-2 text-center" style="width:80px;">Qty</th>
                                <th class="pe-3 py-2 text-end" style="width:130px;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="vItemsBody"></tbody>
                    </table>
                </div>

                <div class="d-flex align-items-center justify-content-between p-3 rounded bg-light border">
                    <div>
                        <span class="text-muted d-block" style="font-size:11px;">TOTAL BELANJA DIBAYAR</span>
                        <strong class="fs-4 text-primary" id="vGrandTotal">Rp 0</strong>
                    </div>
                    <div id="vActionBtns">
                        <button type="button" class="btn btn-success px-4 py-2 fw-bold" id="btnConfirmVerify" style="border-radius:10px;">
                            <i class="bi bi-check-circle-fill me-1"></i>Verifikasi & Selesaikan Transaksi
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let lastKnownPendingCount = {{ $pendingCount }};
let activeOrderId = null;

function formatRupiah(num) {
    return 'Rp ' + Number(num).toLocaleString('id-ID');
}

// Web Audio API Doorbell Chime Sound Synthesizer
function playDoorbellChime() {
    try {
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        
        // Note 1: E5 (659.25 Hz)
        const osc1 = audioCtx.createOscillator();
        const gain1 = audioCtx.createGain();
        osc1.type = 'sine';
        osc1.frequency.setValueAtTime(659.25, audioCtx.currentTime);
        gain1.gain.setValueAtTime(0.3, audioCtx.currentTime);
        gain1.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.8);
        osc1.connect(gain1);
        gain1.connect(audioCtx.destination);
        osc1.start(audioCtx.currentTime);
        osc1.stop(audioCtx.currentTime + 0.8);

        // Note 2: C5 (523.25 Hz) after 0.3s
        setTimeout(() => {
            const osc2 = audioCtx.createOscillator();
            const gain2 = audioCtx.createGain();
            osc2.type = 'sine';
            osc2.frequency.setValueAtTime(523.25, audioCtx.currentTime);
            gain2.gain.setValueAtTime(0.35, audioCtx.currentTime);
            gain2.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 1.2);
            osc2.connect(gain2);
            gain2.connect(audioCtx.destination);
            osc2.start(audioCtx.currentTime);
            osc2.stop(audioCtx.currentTime + 1.2);
        }, 300);
    } catch (err) {
        console.error('Audio context error:', err);
    }
}

document.getElementById('btnTestSound').addEventListener('click', playDoorbellChime);

// Real-Time Polling for New Self-Service Orders
setInterval(async function() {
    try {
        const res = await fetch('{{ route("admin.self-service.pending-count") }}');
        const data = await res.json();
        if (data.success) {
            const count = data.pending_count;
            const badge = document.getElementById('badgePendingCount');
            if (badge) badge.textContent = count;

            if (count > lastKnownPendingCount) {
                lastKnownPendingCount = count;
                playDoorbellChime();
                // Refresh page if on Pending tab
                if (window.location.search.indexOf('status=Pending') !== -1 || window.location.search === '') {
                    setTimeout(() => window.location.reload(), 1500);
                }
            }
        }
    } catch (e) {
        console.error('Polling error:', e);
    }
}, 5000);

function openVerifyModal(order) {
    activeOrderId = order.id;
    document.getElementById('vNomorPesanan').textContent = order.nomor_pesanan;
    document.getElementById('vNamaPelanggan').textContent = order.nama_pelanggan || '-';
    document.getElementById('vMetodeBayar').textContent = order.metode_pembayaran || '-';
    document.getElementById('vCreated').textContent = new Date(order.created_at).toLocaleString('id-ID');
    document.getElementById('vGrandTotal').textContent = formatRupiah(order.total_bayar);

    const vStatus = document.getElementById('vStatus');
    vStatus.textContent = order.status;
    vStatus.className = order.status === 'Pending' ? 'text-danger fw-bold' : (order.status === 'Verified' ? 'text-success fw-bold' : 'text-secondary');

    let html = '';
    if (order.items && order.items.length > 0) {
        order.items.forEach((item, index) => {
            html += `
                <tr>
                    <td class="ps-3 py-2 text-center text-muted">${index + 1}</td>
                    <td class="py-2 fw-semibold">${item.nama_produk}</td>
                    <td class="py-2 text-end">${formatRupiah(item.harga_satuan)}</td>
                    <td class="py-2 text-center fw-bold">${item.qty}</td>
                    <td class="pe-3 py-2 text-end fw-bold text-primary">${formatRupiah(item.subtotal)}</td>
                </tr>`;
        });
    } else {
        html = `<tr><td colspan="5" class="text-center py-3 text-muted">Item tidak ditemukan.</td></tr>`;
    }
    document.getElementById('vItemsBody').innerHTML = html;

    const actionBox = document.getElementById('vActionBtns');
    if (order.status === 'Pending') {
        actionBox.innerHTML = `
            <button type="button" class="btn btn-success px-4 py-2 fw-bold" onclick="confirmVerifyOrder(${order.id})">
                <i class="bi bi-check-circle-fill me-1"></i>Verifikasi & Selesaikan Transaksi
            </button>`;
    } else if (order.status === 'Verified' && order.penjualan_id) {
        actionBox.innerHTML = `
            <a href="{{ url('/pos/print-struk') }}/${order.penjualan_id}" target="_blank" class="btn btn-outline-primary px-4 py-2 fw-bold">
                <i class="bi bi-printer-fill me-1"></i>Cetak Struk Thermal
            </a>`;
    } else {
        actionBox.innerHTML = `<span class="badge bg-secondary">Telah Diproses</span>`;
    }

    const modal = new bootstrap.Modal(document.getElementById('verifyModal'));
    modal.show();
}

async function confirmVerifyOrder(id) {
    if (!confirm('Apakah Anda yakin ingin memverifikasi pesanan Self-Service ini dan menerbitkan transaksi penjualan?')) return;

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const res = await fetch(`{{ url('/admin/self-service') }}/${id}/verify`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken || '',
                'Accept': 'application/json'
            }
        });
        const data = await res.json();
        if (res.ok && data.success) {
            alert(data.message);
            if (data.penjualan_id) {
                window.open(`{{ url('/pos/print-struk') }}/${data.penjualan_id}`, '_blank');
            }
            window.location.reload();
        } else {
            alert('Gagal verifikasi: ' + (data.message || 'Error server.'));
        }
    } catch (e) {
        alert('Kesalahan koneksi: ' + e.message);
    }
}

async function rejectOrder(id) {
    if (!confirm('Apakah Anda yakin ingin menolak pesanan Self Service ini?')) return;
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const res = await fetch(`{{ url('/admin/self-service') }}/${id}/reject`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken || '',
                'Accept': 'application/json'
            }
        });
        const data = await res.json();
        if (res.ok && data.success) {
            window.location.reload();
        }
    } catch (e) {
        alert('Kesalahan koneksi: ' + e.message);
    }
}
</script>
@endpush
