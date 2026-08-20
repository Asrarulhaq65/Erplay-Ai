@extends('layouts.enterprise')
@section('title', 'Pembelian & Kulakan Barang — ' . (auth()->user()?->toko?->nama_toko ?? 'ERPlay AI'))

@push('styles')
<style>
    /* ── Purchase Entry Form Styles ── */
    .quick-entry-card {
        background: var(--bg-card);
        border: 1px solid var(--border-light);
        border-radius: 12px;
        transition: var(--theme-transition);
    }
    .quick-entry-header {
        background: var(--bg-card-hover);
        border-bottom: 1px solid var(--border-light);
        padding: 12px 16px;
        border-radius: 12px 12px 0 0;
    }
    .step-number-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: var(--pb-dark);
        color: #FFFFFF;
        font-size: 11px;
        font-weight: 700;
        margin-right: 4px;
    }
    .grand-total-display {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 22px;
        font-weight: 800;
        color: var(--pb-dark);
        letter-spacing: -0.02em;
    }
    [data-theme="dark"] .grand-total-display {
        color: #4DB8C4;
    }
</style>
@endpush

@section('content')
<!-- Breadcrumb Navigation -->
<nav class="erp-breadcrumb d-none d-md-block" aria-label="Breadcrumb">
    <a href="{{ url('/dashboard') }}" aria-label="Beranda Dashboard"><i class="bi bi-house-door"></i></a>
    <i class="bi bi-chevron-right mx-1" style="font-size:9px;" aria-hidden="true"></i>
    <span aria-current="page">Pembelian / Kulakan Barang</span>
</nav>

<!-- Page Header -->
<div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-2">
    <div>
        <h1 class="h4 mb-0 fw-bold" style="font-family:'Plus Jakarta Sans',sans-serif;color:var(--pb-text);letter-spacing:-0.02em;">
            <i class="bi bi-box-seam me-2" aria-hidden="true"></i>Pembelian / Kulakan Barang
        </h1>
        <p class="mb-0 mt-1" style="color:var(--text-secondary);font-size:13px;">
            Pencatatan faktur barang masuk dari supplier untuk memperbarui stok & HPP produk toko.
        </p>
    </div>
    <div>
        <a href="{{ route('master.produk.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-box-seam me-1" aria-hidden="true"></i>Lihat Stok Produk
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <!-- Meta Data Pembelian Supplier & Faktur -->
    <div class="col-12">
        <div class="card card-erp">
            <div class="card-header py-3">
                <h2 class="h6 card-title mb-0" style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;">
                    <i class="bi bi-file-earmark-text me-2" aria-hidden="true"></i>Informasi Faktur & Supplier
                </h2>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label for="supplier_id" class="form-label fw-semibold" style="font-size:13px;">Pilih Supplier <span class="text-danger">*</span></label>
                        <select id="supplier_id" class="form-select form-select-sm" required aria-label="Pilih Supplier">
                            <option value="">-- Pilih Supplier --</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->nama_supplier }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="nomor_faktur" class="form-label fw-semibold" style="font-size:13px;">No. Faktur Supplier <span class="text-danger">*</span></label>
                        <input type="text" id="nomor_faktur" class="form-control form-control-sm" placeholder="Contoh: INV-99238 / FAK-8821" required autocomplete="off">
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="tanggal_beli" class="form-label fw-semibold" style="font-size:13px;">Tanggal Pembelian <span class="text-danger">*</span></label>
                        <input type="date" id="tanggal_beli" class="form-control form-control-sm" value="{{ now()->toDateString() }}" required aria-label="Tanggal Pembelian">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Entry Bar Section -->
    <div class="col-12">
        <div class="quick-entry-card">
            <div class="quick-entry-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h2 class="h6 mb-0 fw-bold" style="font-family:'Plus Jakarta Sans',sans-serif;color:var(--pb-text);">
                    <i class="bi bi-lightning-charge me-1" aria-hidden="true"></i>Quick-Entry Barang Masuk
                </h2>
                <span class="badge bg-secondary text-white fw-normal" style="font-size:11px;">
                    <i class="bi bi-keyboard me-1" aria-hidden="true"></i>Shortcut: Enter [ Produk ➔ HPP ➔ Qty ➔ Tambah ]
                </span>
            </div>
            <div class="card-body p-3">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-5">
                        <label for="productSearch" class="form-label mb-1 fw-semibold" style="font-size:12px;">
                            <span class="step-number-badge">1</span> Cari Produk / Barcode
                        </label>
                        <input type="text" id="productSearch" list="productList"
                               class="form-control form-control-sm"
                               placeholder="Ketik nama atau scan barcode barang..." autocomplete="off">
                        <datalist id="productList">
                            @foreach($products as $p)
                                <option data-id="{{ $p->id }}"
                                        data-barcode="{{ $p->barcode }}"
                                        data-harga="{{ (int)$p->harga_modal }}"
                                        value="{{ $p->barcode }} - {{ $p->nama_produk }}"></option>
                            @endforeach
                        </datalist>
                    </div>
                    <div class="col-6 col-md-2">
                        <label for="inputHarga" class="form-label mb-1 fw-semibold" style="font-size:12px;">
                            <span class="step-number-badge">2</span> HPP Modal (Rp)
                        </label>
                        <input type="number" id="inputHarga"
                               class="form-control form-control-sm text-end"
                               placeholder="0" min="0">
                    </div>
                    <div class="col-6 col-md-2">
                        <label for="inputQty" class="form-label mb-1 fw-semibold" style="font-size:12px;">
                            <span class="step-number-badge">3</span> Qty Masuk
                        </label>
                        <input type="number" id="inputQty"
                               class="form-control form-control-sm text-center"
                               placeholder="1" min="1">
                    </div>
                    <div class="col-12 col-md-3">
                        <button type="button" id="btnAddItem" class="btn btn-sm btn-pb w-100 py-2 fw-bold">
                            <i class="bi bi-plus-circle me-1" aria-hidden="true"></i> Tambah ke Keranjang
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Purchase Items Table Grid -->
    <div class="col-12">
        <div class="card card-erp">
            <div class="card-header py-3">
                <h2 class="h6 card-title mb-0" style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;">
                    <i class="bi bi-cart-check me-2" aria-hidden="true"></i>Rincian Item Barang Dikeranjangkan
                </h2>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="min-height: 220px;">
                    <table class="table table-sm align-middle table-hover mb-0" style="font-size:13px;">
                        <thead>
                            <tr>
                                <th class="ps-3 py-2 text-center" style="width:50px;" scope="col">No</th>
                                <th class="py-2" style="width:140px;" scope="col">Barcode</th>
                                <th class="py-2" scope="col">Nama Produk</th>
                                <th class="py-2 text-end" style="width:140px;" scope="col">Harga HPP</th>
                                <th class="py-2 text-center" style="width:100px;" scope="col">Qty</th>
                                <th class="py-2 text-end" style="width:160px;" scope="col">Subtotal</th>
                                <th class="pe-3 py-2 text-center" style="width:80px;" scope="col">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="cartTableBody">
                            <tr id="emptyRow">
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-cart-x d-block mb-2" style="font-size:32px;opacity:0.4;" aria-hidden="true"></i>
                                    Belum ada barang yang dimasukkan. Gunakan baris <strong>Quick-Entry</strong> di atas untuk menambah item.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Footer / Grand Total & Save Action Bar -->
            <div class="card-footer py-3 px-3 d-flex flex-wrap align-items-center justify-content-between gap-3 border-top">
                <div>
                    <span class="text-secondary fw-semibold" style="font-size:12px;text-transform:uppercase;letter-spacing:0.04em;">Grand Total Pembelian:</span>
                    <div class="grand-total-display" id="displayGrandTotal">Rp 0</div>
                </div>
                <div>
                    <button type="button" id="btnSimpan" class="btn btn-success btn-lg fw-bold px-4 fs-6">
                        <i class="bi bi-check2-circle me-2" aria-hidden="true"></i> Simpan Pembelian Barang
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Accessible Notification Toast Container -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:1055;">
    <div id="successToast" class="toast align-items-center text-white bg-dark border-0"
         role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body fw-semibold" id="toastMessage">Pembelian berhasil disimpan!</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Tutup"></button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let cart = [];

    // ── Element references ───────────────────────────────────────────────────
    const searchInput  = document.getElementById('productSearch');
    const hargaInput   = document.getElementById('inputHarga');
    const qtyInput     = document.getElementById('inputQty');
    const btnAdd       = document.getElementById('btnAddItem');
    const tbody        = document.getElementById('cartTableBody');
    const displayTotal = document.getElementById('displayGrandTotal');
    const btnSimpan    = document.getElementById('btnSimpan');
    const datalist     = document.getElementById('productList');

    let currentProduct = null;

    // ── Helper: Format Rupiah ─────────────────────────────────────────────────
    function formatRp(num) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency', currency: 'IDR', minimumFractionDigits: 0
        }).format(num);
    }

    // ── Helper: Find product from datalist ───────────────────────────────────
    function findProduct(query) {
        if (!query) return null;
        const q = query.toString().toLowerCase().trim();
        const opts = Array.from(datalist.options);
        for (const o of opts) {
            if ((o.value || '').toLowerCase() === q ||
                (o.getAttribute('data-barcode') || '').toLowerCase() === q) return o;
        }
        for (const o of opts) {
            if ((o.value || '').toLowerCase().includes(q)) return o;
        }
        return null;
    }

    function optionToProduct(opt) {
        return {
            id:          opt.getAttribute('data-id'),
            barcode:     opt.getAttribute('data-barcode'),
            nama:        opt.value.split(' - ').slice(1).join(' - ') || opt.value,
            harga_modal: opt.getAttribute('data-harga')
        };
    }

    // ── Auto-fill price when product selected ─────────────────────────────────
    searchInput.addEventListener('input', function () {
        const val = this.value.trim();
        if (!val) { currentProduct = null; return; }

        const opt = findProduct(val);
        if (opt && ((opt.value || '').toLowerCase() === val.toLowerCase() ||
                    (opt.getAttribute('data-barcode') || '').toLowerCase() === val.toLowerCase())) {
            currentProduct   = optionToProduct(opt);
            this.value       = opt.value;
            hargaInput.value = currentProduct.harga_modal;
            qtyInput.value   = '';
        } else {
            currentProduct = null;
        }
    });

    // ── Enter Keyboard Navigation Flow ───────────────────────────────────────
    searchInput.addEventListener('keydown', function (e) {
        if (e.key !== 'Enter') return;
        e.preventDefault();

        if (!currentProduct) {
            const opt = findProduct(this.value);
            if (!opt) {
                alert('Barang tidak ditemukan! Coba ketik ulang nama atau barcode.');
                this.select();
                return;
            }
            currentProduct   = optionToProduct(opt);
            this.value       = opt.value;
            hargaInput.value = currentProduct.harga_modal;
            qtyInput.value   = '';
        }

        hargaInput.focus();
        hargaInput.select();
    });

    hargaInput.addEventListener('keydown', function (e) {
        if (e.key !== 'Enter') return;
        e.preventDefault();

        if (this.value === '' || parseFloat(this.value) < 0) {
            this.select();
            return;
        }
        qtyInput.focus();
        qtyInput.select();
    });

    qtyInput.addEventListener('keydown', function (e) {
        if (e.key !== 'Enter') return;
        e.preventDefault();
        if (!this.value || parseInt(this.value) < 1) this.value = 1;
        addItemToCart();
    });

    btnAdd.addEventListener('click', addItemToCart);

    // ── Add Item to Cart ──────────────────────────────────────────────────────
    function addItemToCart() {
        if (!currentProduct) {
            const opt = findProduct(searchInput.value);
            if (!opt) {
                alert('Barang tidak ditemukan dalam daftar!');
                searchInput.focus();
                return;
            }
            currentProduct   = optionToProduct(opt);
            hargaInput.value = hargaInput.value || currentProduct.harga_modal;
            if (!qtyInput.value) qtyInput.value = 1;
        }

        const harga = parseFloat(hargaInput.value);
        const qty   = parseInt(qtyInput.value) || 1;

        if (isNaN(harga) || harga < 0) {
            alert('Harga modal HPP tidak valid!');
            hargaInput.focus(); hargaInput.select();
            return;
        }
        if (qty < 1) {
            alert('Qty barang tidak valid!');
            qtyInput.focus(); qtyInput.select();
            return;
        }

        const idx = cart.findIndex(i => i.id == currentProduct.id);
        if (idx > -1) {
            cart[idx].qty        += qty;
            cart[idx].harga_modal = harga;
        } else {
            cart.push({
                id:          currentProduct.id,
                barcode:     currentProduct.barcode,
                nama:        currentProduct.nama,
                harga_modal: harga,
                qty:         qty
            });
        }

        searchInput.value = '';
        hargaInput.value  = '';
        qtyInput.value    = '';
        currentProduct    = null;

        renderCart();
        searchInput.focus();
    }

    function removeCartItem(index) {
        cart.splice(index, 1);
        renderCart();
    }
    window.removeCartItem = removeCartItem;

    // ── Render Cart Data Table ───────────────────────────────────────────────
    function renderCart() {
        if (cart.length === 0) {
            tbody.innerHTML = `<tr id="emptyRow">
                <td colspan="7" class="text-center py-5 text-muted">
                    <i class="bi bi-cart-x d-block mb-2" style="font-size:32px;opacity:0.4;" aria-hidden="true"></i>
                    Belum ada barang yang dimasukkan. Gunakan baris <strong>Quick-Entry</strong> di atas untuk menambah item.
                </td>
            </tr>`;
            displayTotal.textContent = 'Rp 0';
            return;
        }

        let html = '', total = 0;
        cart.forEach((item, i) => {
            const sub = item.qty * item.harga_modal;
            total += sub;
            html += `
            <tr>
                <td class="ps-3 py-2 text-center text-muted">${i + 1}</td>
                <td class="py-2 font-monospace fw-semibold">${item.barcode}</td>
                <td class="py-2 fw-semibold">${item.nama}</td>
                <td class="py-2 text-end">${formatRp(item.harga_modal)}</td>
                <td class="py-2 text-center fw-bold">${item.qty}</td>
                <td class="py-2 text-end fw-bold" style="color:var(--pb-text);">${formatRp(sub)}</td>
                <td class="pe-3 py-2 text-center">
                    <button class="btn btn-sm btn-outline-danger py-1 px-2" onclick="removeCartItem(${i})" title="Hapus Item">
                        <i class="bi bi-trash" aria-hidden="true"></i>
                    </button>
                </td>
            </tr>`;
        });

        tbody.innerHTML = html;
        displayTotal.textContent = formatRp(total);
    }

    // ── Submit Purchase Entry ─────────────────────────────────────────────────
    btnSimpan.addEventListener('click', async function () {
        const supplier_id  = document.getElementById('supplier_id').value;
        const nomor_faktur = document.getElementById('nomor_faktur').value.trim();
        const tanggal_beli = document.getElementById('tanggal_beli').value;

        if (!supplier_id) {
            alert('Mohon pilih Supplier terlebih dahulu!');
            document.getElementById('supplier_id').focus();
            return;
        }
        if (!nomor_faktur) {
            alert('Mohon isi No. Faktur!');
            document.getElementById('nomor_faktur').focus();
            return;
        }
        if (!tanggal_beli) {
            alert('Mohon isi Tanggal Pembelian!');
            document.getElementById('tanggal_beli').focus();
            return;
        }
        if (cart.length === 0) {
            alert('Keranjang pembelian masih kosong! Tambahkan barang terlebih dahulu.');
            searchInput.focus();
            return;
        }

        const payload = {
            supplier_id:  parseInt(supplier_id),
            nomor_faktur: nomor_faktur,
            tanggal_beli: tanggal_beli,
            items: cart.map(i => ({
                product_id:  parseInt(i.id),
                qty:         i.qty,
                harga_modal: i.harga_modal
            }))
        };

        const originalHTML   = btnSimpan.innerHTML;
        btnSimpan.innerHTML  = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Menyimpan Pembelian...';
        btnSimpan.disabled   = true;

        try {
            const response = await fetch('{{ route("pembelian.store") }}', {
                method:  'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept':       'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                                    || '{{ csrf_token() }}'
                },
                body: JSON.stringify(payload)
            });

            let resData = {};
            try { resData = await response.json(); } catch (_) {}

            if (response.ok && resData.success) {
                document.getElementById('toastMessage').textContent =
                    resData.message || 'Pembelian barang berhasil disimpan!';
                new bootstrap.Toast(document.getElementById('successToast'), { delay: 3500 }).show();

                cart = [];
                renderCart();
                document.getElementById('supplier_id').value  = '';
                document.getElementById('nomor_faktur').value = '';
                searchInput.value = '';
                searchInput.focus();

            } else if (response.status === 422 && resData.errors) {
                const msgs = Object.values(resData.errors).flat().join('\n');
                alert('Data tidak valid:\n' + msgs);
            } else {
                alert('Gagal menyimpan: ' + (resData.message || `Status HTTP ${response.status}`));
            }

        } catch (err) {
            console.error('Submit error:', err);
            alert('Gagal terhubung ke server. Periksa koneksi internet dan coba lagi.');
        } finally {
            btnSimpan.innerHTML = originalHTML;
            btnSimpan.disabled  = false;
        }
    });
</script>
@endpush
