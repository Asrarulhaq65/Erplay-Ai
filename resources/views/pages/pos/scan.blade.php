@extends('layouts.enterprise')
@section('title', 'Scan Gambar — POS')

@push('styles')
<style>
    .scan-dropzone {
        border: 2px dashed var(--border-medium);
        border-radius: 14px;
        padding: 40px 24px;
        text-align: center;
        background: var(--bg-card);
        cursor: pointer;
        transition: border-color 0.2s, background 0.2s;
    }
    .scan-dropzone:hover,
    .scan-dropzone.dragover {
        border-color: var(--pb-accent);
        background: var(--bg-card-hover);
    }
    .scan-dropzone i {
        font-size: 48px; color: var(--text-muted); display: block; margin-bottom: 12px;
    }
    .scan-preview {
        max-width: 100%; max-height: 320px;
        border-radius: 12px; border: 1px solid var(--border-light);
        display: block; margin: 0 auto;
    }
    .scan-result-row {
        border-bottom: 1px solid var(--border-light);
    }
    .scan-result-row:last-child { border-bottom: none; }
    .badge-status-tersedia {
        background: rgba(27,138,107,0.15); color: #1B8A6B;
        padding: 3px 10px; border-radius: 6px; font-size: 11px; font-weight: 600;
    }
    .badge-status-baru {
        background: rgba(212,132,42,0.15); color: #D4842A;
        padding: 3px 10px; border-radius: 6px; font-size: 11px; font-weight: 600;
    }
    .scan-loading {
        display: flex; align-items: center; justify-content: center;
        gap: 12px; padding: 40px 0; color: var(--text-muted);
    }
    .scan-loading .spinner-border { width: 20px; height: 20px; }
</style>
@endpush

@section('content')
<div class="erp-breadcrumb d-none d-md-block" aria-label="Breadcrumb">
    <a href="{{ url('/pos/standard') }}"><i class="bi bi-grid-3x3-gap"></i> POS Standar</a>
    <i class="bi bi-chevron-right mx-1" style="font-size:9px;"></i>
    <span>Scan Gambar</span>
</div>

<div class="d-flex align-items-center justify-content-between mb-3">
    <h4 class="mb-0 fw-bold" style="color:var(--pb-text);font-size:18px;">
        <i class="bi bi-camera me-2"></i>Scan Gambar ke Keranjang
    </h4>
    <a href="{{ url('/pos/standard') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Kembali ke POS
    </a>
</div>

@if(!$geminiReady)
<div class="alert alert-warning d-flex align-items-center justify-content-between flex-wrap gap-2" role="alert">
    <div>
        <i class="bi bi-exclamation-triangle me-1"></i>
        <strong>GEMINI_API_KEY belum dikonfigurasi.</strong>
        Silakan konfigurasi provider dan API key BYOK pada halaman Pengaturan AI.
    </div>
    <a href="{{ route('pengaturan.ai.index') }}" class="btn btn-sm btn-warning fw-bold">
        <i class="bi bi-gear me-1"></i>Buka Pengaturan AI
    </a>
</div>
@endif

<div class="row g-3">
    {{-- Panel Kiri: Upload --}}
    <div class="col-lg-5">
        <div class="card card-erp h-100">
            <div class="card-header">
                <h5 class="card-title"><i class="bi bi-cloud-upload me-2"></i>1. Upload Gambar</h5>
            </div>
            <div class="card-body p-3">
                <div id="dropzone" class="scan-dropzone" role="button" tabindex="0" aria-label="Pilih gambar">
                    <i class="bi bi-image"></i>
                    <div class="fw-semibold" style="font-size:13px;color:var(--text-primary);">Klik atau drag gambar ke sini</div>
                    <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">JPG / PNG / WEBP — maks. 5MB</div>
                    <input type="file" id="fileInput" accept="image/jpeg,image/png,image/webp" hidden>
                </div>

                <div id="previewWrap" class="mt-3" style="display:none;">
                    <img id="preview" class="scan-preview" alt="Preview gambar">
                    <div class="d-flex gap-2 mt-2">
                        <button id="processBtn" class="btn btn-pb w-100" {{ !$geminiReady ? 'disabled' : '' }}>
                            <i class="bi bi-stars me-1"></i>Proses dengan AI
                        </button>
                        <button id="resetBtn" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>

                <div id="loadingBox" class="scan-loading" style="display:none;">
                    <div class="spinner-border text-primary" role="status"></div>
                    <span>Memproses gambar dengan AI Vision...</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Panel Kanan: Review --}}
    <div class="col-lg-7">
        <div class="card card-erp h-100">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="bi bi-clipboard-check me-2"></i>2. Review Hasil
                </h5>
                <span id="resultCount" style="font-size:11px;color:var(--text-muted);display:none;"></span>
            </div>
            <div class="card-body p-0">
                <div id="emptyState" style="padding:60px 20px;text-align:center;color:var(--text-muted);">
                    <i class="bi bi-inbox d-block mb-2" style="font-size:36px;opacity:0.3;"></i>
                    <div style="font-size:13px;">Hasil scan akan muncul di sini</div>
                    <div style="font-size:11px;">Upload gambar dan klik "Proses dengan AI"</div>
                </div>

                <div id="resultsWrap" style="display:none;">
                    <div id="resultsList"></div>
                    <div class="p-3 border-top" style="border-color:var(--border-light) !important;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span style="font-size:12px;color:var(--text-secondary);">
                                <i class="bi bi-info-circle me-1"></i>
                                Item "Barang Baru" tidak dapat masuk keranjang — tambahkan ke master produk dahulu.
                            </span>
                        </div>
                        <button id="addToCartBtn" class="btn btn-pb w-100" disabled>
                            <i class="bi bi-cart-plus me-1"></i>Tambahkan ke Keranjang
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
(function(){
    const dropzone   = document.getElementById('dropzone');
    const fileInput  = document.getElementById('fileInput');
    const previewWrap = document.getElementById('previewWrap');
    const preview    = document.getElementById('preview');
    const processBtn = document.getElementById('processBtn');
    const resetBtn   = document.getElementById('resetBtn');
    const loadingBox = document.getElementById('loadingBox');
    const emptyState = document.getElementById('emptyState');
    const resultsWrap = document.getElementById('resultsWrap');
    const resultsList = document.getElementById('resultsList');
    const resultCount = document.getElementById('resultCount');
    const addToCartBtn = document.getElementById('addToCartBtn');

    let currentFile = null;
    let reviewItems = [];

    // Dropzone click
    dropzone.addEventListener('click', () => fileInput.click());
    dropzone.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); fileInput.click(); }
    });
    dropzone.addEventListener('dragover', (e) => { e.preventDefault(); dropzone.classList.add('dragover'); });
    dropzone.addEventListener('dragleave', () => dropzone.classList.remove('dragover'));
    dropzone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropzone.classList.remove('dragover');
        if (e.dataTransfer.files.length) handleFile(e.dataTransfer.files[0]);
    });
    fileInput.addEventListener('change', () => {
        if (fileInput.files.length) handleFile(fileInput.files[0]);
    });

    function handleFile(file) {
        if (!file.type.startsWith('image/')) {
            alert('File harus berupa gambar (JPG/PNG/WEBP).');
            return;
        }
        if (file.size > 5 * 1024 * 1024) {
            alert('Ukuran maksimal 5 MB.');
            return;
        }
        currentFile = file;
        const reader = new FileReader();
        reader.onload = (e) => { preview.src = e.target.result; previewWrap.style.display = 'block'; };
        reader.readAsDataURL(file);
    }

    resetBtn.addEventListener('click', () => {
        currentFile = null;
        fileInput.value = '';
        previewWrap.style.display = 'none';
        resultsWrap.style.display = 'none';
        emptyState.style.display = 'block';
        reviewItems = [];
    });

    // Process via AI
    processBtn.addEventListener('click', async () => {
        if (!currentFile) return;
        loadingBox.style.display = 'flex';
        processBtn.disabled = true;
        resultsWrap.style.display = 'none';
        emptyState.style.display = 'none';

        const fd = new FormData();
        fd.append('image', currentFile);
        fd.append('_token', document.querySelector('meta[name=csrf-token]').content);

        try {
            const resp = await fetch('{{ route("pos.scan.process") }}', {
                method: 'POST',
                body: fd,
                headers: { 'Accept': 'application/json' }
            });
            const data = await resp.json();

            loadingBox.style.display = 'none';
            processBtn.disabled = false;

            if (!data.ok) {
                alert('Error: ' + (data.error || 'Gagal memproses gambar.'));
                emptyState.style.display = 'block';
                return;
            }
            renderResults(data.items);
        } catch (err) {
            loadingBox.style.display = 'none';
            processBtn.disabled = false;
            alert('Kesalahan jaringan: ' + err.message);
            emptyState.style.display = 'block';
        }
    });

    function renderResults(items) {
        reviewItems = items;
        resultsList.innerHTML = '';
        if (!items.length) {
            resultsWrap.style.display = 'block';
            resultsList.innerHTML = '<div style="padding:40px;text-align:center;color:var(--text-muted);">Tidak ada produk terdeteksi.</div>';
            resultCount.style.display = 'none';
            return;
        }

        const matched = items.filter(i => i.status === 'tersedia').length;
        resultCount.textContent = `${items.length} item — ${matched} cocok, ${items.length - matched} baru`;
        resultCount.style.display = 'inline';

        items.forEach((item, idx) => {
            const row = document.createElement('div');
            row.className = 'scan-result-row p-3';

            const statusBadge = item.status === 'tersedia'
                ? '<span class="badge-status-tersedia"><i class="bi bi-check-circle me-1"></i>Tersedia</span>'
                : '<span class="badge-status-baru"><i class="bi bi-plus-circle me-1"></i>Barang Baru</span>';

            const candidates = item.candidates && item.candidates.length > 1
                ? '<select class="form-select form-select-sm mt-2" data-idx="' + idx + '" style="font-size:11px;">' +
                  item.candidates.map(c => `<option value="${c.id}" ${c.id === item.produk_id ? 'selected' : ''}>${c.nama} (${c.score}% — Rp${formatRp(c.harga)})</option>`).join('') +
                  '</select>'
                : '';

            const matchedInfo = item.status === 'tersedia'
                ? `<div style="font-size:11px;color:var(--text-muted);margin-top:4px;">
                     <i class="bi bi-upc-scan me-1"></i>${item.produk_barcode || ''} ·
                     Stok: ${item.produk_stok} ${item.produk_satuan || ''}
                   </div>`
                : '';

            const note = item.catatan
                ? `<div style="font-size:11px;color:var(--text-muted);margin-top:2px;font-style:italic;">${item.catatan}</div>`
                : '';

            row.innerHTML = `
                <div class="d-flex justify-content-between align-items-start gap-3">
                    <div class="flex-grow-1">
                        <div class="fw-semibold" style="font-size:13px;color:var(--text-primary);">${escapeHtml(item.ai_name)}</div>
                        ${note}
                        ${matchedInfo}
                        ${candidates}
                    </div>
                    <div class="text-end" style="min-width:120px;">
                        <div class="mb-2">${statusBadge}</div>
                        <div style="font-size:10px;color:var(--text-muted);">Qty</div>
                        <input type="number" min="1" value="${item.ai_qty}" class="form-control form-control-sm mb-1" data-qty-idx="${idx}" style="width:80px;margin-left:auto;">
                        <div style="font-size:10px;color:var(--text-muted);">Harga (Rp)</div>
                        <input type="number" min="0" value="${item.ai_harga || (item.produk_harga || 0)}" class="form-control form-control-sm" data-harga-idx="${idx}" style="width:120px;margin-left:auto;">
                    </div>
                </div>
            `;
            resultsList.appendChild(row);
        });

        // Wire candidate select changes
        resultsList.querySelectorAll('select[data-idx]').forEach(sel => {
            sel.addEventListener('change', (e) => {
                const idx = parseInt(e.target.dataset.idx);
                const newId = parseInt(e.target.value);
                const cand = reviewItems[idx].candidates.find(c => c.id === newId);
                if (cand) {
                    reviewItems[idx].produk_id = cand.id;
                    reviewItems[idx].produk_nama = cand.nama;
                    reviewItems[idx].produk_harga = cand.harga;
                    reviewItems[idx].status = 'tersedia';
                    reviewItems[idx].catatan = `Cocok ${cand.score}% (manual pilih)`;
                }
            });
        });

        // Wire qty/harga edits
        resultsList.querySelectorAll('[data-qty-idx]').forEach(inp => {
            inp.addEventListener('change', (e) => {
                reviewItems[parseInt(e.target.dataset.qtyIdx)].ai_qty = parseInt(e.target.value) || 1;
            });
        });
        resultsList.querySelectorAll('[data-harga-idx]').forEach(inp => {
            inp.addEventListener('change', (e) => {
                reviewItems[parseInt(e.target.dataset.hargaIdx)].ai_harga = parseInt(e.target.value) || 0;
            });
        });

        resultsWrap.style.display = 'block';
        updateAddToCart();
    }

    function updateAddToCart() {
        const matched = reviewItems.filter(i => i.status === 'tersedia' && i.produk_id);
        addToCartBtn.disabled = matched.length === 0;
        addToCartBtn.innerHTML = matched.length > 0
            ? `<i class="bi bi-cart-plus me-1"></i>Tambahkan ${matched.length} Item ke Keranjang`
            : '<i class="bi bi-cart-x me-1"></i>Belum ada item yang cocok';
    }

    // Add to cart → pass via session/localStorage to POS Standard
    addToCartBtn.addEventListener('click', () => {
        const matched = reviewItems.filter(i => i.status === 'tersedia' && i.produk_id);
        if (!matched.length) return;

        const payload = matched.map(i => ({
            produk_id: i.produk_id,
            nama:      i.produk_nama,
            qty:       i.ai_qty,
            harga:     i.ai_harga || i.produk_harga,
            barcode:   i.produk_barcode,
        }));

        // Simpan ke localStorage untuk dibaca POS Standard
        localStorage.setItem('pos_scan_items', JSON.stringify(payload));
        window.location.href = '{{ url("/pos/standard") }}?from_scan=1';
    });

    // Helpers
    function formatRp(n) { return new Intl.NumberFormat('id-ID').format(n || 0); }
    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }
})();
</script>
@endpush
