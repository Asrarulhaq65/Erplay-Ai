<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Self Service Kios — {{ $toko->nama_toko ?? 'ERPlay AI' }}</title>

    <!-- Google Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --ss-primary: #0D4E56;
            --ss-accent: #4DB8C4;
            --ss-bg: #F4F7F9;
            --ss-card: #FFFFFF;
            --ss-text: #1E293B;
            --ss-muted: #64748B;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--ss-bg);
            color: var(--ss-text);
            margin: 0;
            padding-bottom: 90px;
            user-select: none;
            -webkit-tap-highlight-color: transparent;
        }

        /* Mobile Header */
        .ss-header {
            background: linear-gradient(135deg, #0D4E56 0%, #09373D 100%);
            color: #FFFFFF;
            padding: 16px;
            border-bottom-left-radius: 20px;
            border-bottom-right-radius: 20px;
            box-shadow: 0 4px 15px rgba(13, 78, 86, 0.2);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        /* Quick Action AI Bar */
        .ai-action-bar {
            display: flex;
            gap: 10px;
            margin-top: 12px;
        }

        .btn-ai-voice {
            flex: 1;
            background: #F59E0B;
            color: #0F172A;
            font-weight: 700;
            border: none;
            border-radius: 12px;
            padding: 10px;
            font-size: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            box-shadow: 0 4px 10px rgba(245, 158, 11, 0.3);
            transition: transform 0.15s;
        }

        .btn-ai-voice:active { transform: scale(0.96); }

        .btn-ai-scan {
            flex: 1;
            background: #4DB8C4;
            color: #0D4E56;
            font-weight: 700;
            border: none;
            border-radius: 12px;
            padding: 10px;
            font-size: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            box-shadow: 0 4px 10px rgba(77, 184, 196, 0.3);
            transition: transform 0.15s;
        }

        .btn-ai-scan:active { transform: scale(0.96); }

        /* Search Input */
        .search-container {
            padding: 14px 16px 0;
        }
        .ss-search-input {
            border-radius: 12px;
            border: 1.5px solid #E2E8F0;
            padding: 10px 14px 10px 38px;
            font-size: 13.5px;
            background: #FFFFFF;
        }

        /* Category Filter Pills */
        .category-scroll {
            display: flex;
            gap: 8px;
            overflow-x: auto;
            padding: 12px 16px;
            scrollbar-width: none;
        }
        .category-scroll::-webkit-scrollbar { display: none; }
        .cat-pill {
            background: #FFFFFF;
            border: 1px solid #CBD5E1;
            color: #475569;
            font-size: 12px;
            font-weight: 600;
            padding: 6px 14px;
            border-radius: 20px;
            white-space: nowrap;
            cursor: pointer;
        }
        .cat-pill.active {
            background: var(--ss-primary);
            color: #FFFFFF;
            border-color: var(--ss-primary);
        }

        /* Product Cards Mobile Grid */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            padding: 0 16px 16px;
        }
        @media (min-width: 576px) {
            .product-grid { grid-template-columns: repeat(3, 1fr); }
        }

        .ss-card {
            background: #FFFFFF;
            border-radius: 16px;
            border: 1px solid #E2E8F0;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
            transition: transform 0.15s ease;
        }
        .ss-card:active { transform: scale(0.97); }

        .ss-card-thumb {
            height: 110px;
            background: #F8FAFC;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        .ss-card-thumb img {
            max-height: 100%;
            max-width: 100%;
            object-fit: cover;
        }
        .ss-card-body {
            padding: 10px;
            display: flex;
            flex-direction: column;
            flex: 1;
            justify-content: space-between;
        }
        .ss-card-title {
            font-size: 12.5px;
            font-weight: 700;
            line-height: 1.3;
            margin-bottom: 4px;
            color: var(--ss-text);
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .ss-card-price {
            font-size: 13.5px;
            font-weight: 800;
            color: var(--ss-primary);
        }
        .btn-add-item {
            background: var(--ss-primary);
            color: #FFFFFF;
            border: none;
            border-radius: 10px;
            padding: 6px 0;
            font-size: 12px;
            font-weight: 700;
            width: 100%;
            margin-top: 6px;
        }

        /* Bottom Floating Cart Bar */
        .bottom-cart-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #FFFFFF;
            border-top: 1px solid #E2E8F0;
            padding: 12px 16px;
            box-shadow: 0 -4px 20px rgba(0,0,0,0.08);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .cart-badge {
            background: #EF4444;
            color: #FFFFFF;
            border-radius: 50%;
            width: 22px;
            height: 22px;
            font-size: 11px;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-left: 6px;
        }
        .btn-checkout-ss {
            background: linear-gradient(135deg, #0D4E56 0%, #145C65 100%);
            color: #FFFFFF;
            font-weight: 800;
            border: none;
            border-radius: 14px;
            padding: 12px 24px;
            font-size: 14px;
            box-shadow: 0 4px 15px rgba(13, 78, 86, 0.3);
        }
    </style>
</head>
<body>

    <!-- Header Mobile Kios -->
    <div class="ss-header">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-shop fs-4 text-warning"></i>
                <div>
                    <div class="fw-bold" style="font-size:15px;line-height:1.2;">{{ $toko->nama_toko ?? 'ERPlay AI' }}</div>
                    <div style="font-size:11px;opacity:0.85;">Self Service Ordering Kios</div>
                </div>
            </div>
            <span class="badge bg-warning text-dark px-2.5 py-1" style="font-size:10px;font-weight:700;">LIVE KIOS</span>
        </div>

        <!-- Dual AI Input Bar -->
        <div class="ai-action-bar">
            <button type="button" class="btn-ai-voice" id="btnVoiceModal">
                <i class="bi bi-mic-fill fs-6"></i>Pesan via Suara AI
            </button>
            <button type="button" class="btn-ai-scan" id="btnScanModal">
                <i class="bi bi-camera-fill fs-6"></i>Scan Foto Produk
            </button>
        </div>
    </div>

    <!-- Search Input -->
    <div class="search-container">
        <div class="position-relative">
            <i class="bi bi-search position-absolute text-muted" style="left:12px;top:50%;transform:translateY(-50%);font-size:14px;"></i>
            <input type="text" id="ssSearchInput" class="form-control ss-search-input" placeholder="Cari nama produk atau ketik barang…">
        </div>
    </div>

    <!-- Category Pills -->
    <div class="category-scroll">
        <span class="cat-pill active" data-cat="all">Semua Produk</span>
        @foreach($kategoris as $cat)
            <span class="cat-pill" data-cat="{{ $cat->id }}">{{ $cat->nama_kategori }}</span>
        @endforeach
    </div>

    <!-- Product Mobile Grid -->
    <div class="product-grid" id="productGrid">
        @foreach($produks as $p)
            <div class="ss-card product-item" data-id="{{ $p->id }}" data-cat="{{ $p->kategori_produk_id }}" data-name="{{ strtolower($p->nama_produk) }}">
                <div class="ss-card-thumb">
                    @if($p->foto_produk)
                        <img src="{{ asset('storage/' . $p->foto_produk) }}" alt="{{ $p->nama_produk }}">
                    @else
                        <i class="bi bi-box-seam fs-1 text-secondary opacity-50"></i>
                    @endif
                </div>
                <div class="ss-card-body">
                    <div class="ss-card-title">{{ $p->nama_produk }}</div>
                    <div class="ss-card-price">Rp {{ number_format($p->harga_jual_umum, 0, ',', '.') }}</div>
                    <button type="button" class="btn-add-item mt-2" onclick="addToCart({{ $p->id }}, '{{ addslashes($p->nama_produk) }}', {{ $p->harga_jual_umum }})">
                        <i class="bi bi-plus-lg me-1"></i>Tambah
                    </button>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Floating Bottom Cart Bar -->
    <div class="bottom-cart-bar">
        <div>
            <div class="text-secondary" style="font-size:11px;font-weight:600;">TOTAL KERANJANG</div>
            <div class="fw-bold fs-5 text-success" id="cartTotalDisplay">Rp 0</div>
        </div>
        <button type="button" class="btn-checkout-ss" id="btnOpenCartModal">
            Keranjang <span class="cart-badge" id="cartItemCount">0</span>
        </button>
    </div>

    <!-- Modal Review Keranjang & Checkout -->
    <div class="modal fade" id="cartModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
            <div class="modal-content" style="border-radius:20px;">
                <div class="modal-header border-bottom py-3">
                    <h5 class="modal-title fw-bold" style="font-size:16px;">
                        <i class="bi bi-cart-check me-2 text-primary"></i>Keranjang Belanja Self Service
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3" style="max-height:60vh;overflow-y:auto;">
                    <div id="cartItemsList">
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-cart-x fs-1 d-block mb-2 opacity-50"></i>
                            Keranjang Anda masih kosong.
                        </div>
                    </div>

                    <div class="mt-3 p-3 rounded bg-light border">
                        <label for="ssNamaPelanggan" class="form-label fw-bold" style="font-size:12px;">Nama Anda / No. Meja (Opsional)</label>
                        <input type="text" id="ssNamaPelanggan" class="form-control form-control-sm" placeholder="Contoh: Bpk Arul / Meja 05">

                        <label for="ssMetodeBayar" class="form-label fw-bold mt-2" style="font-size:12px;">Pilih Metode Pembayaran</label>
                        <select id="ssMetodeBayar" class="form-select form-select-sm">
                            <option value="Tunai">Tunai / Bayar di Kasir</option>
                            <option value="Digital Payment">Digital Payment (QRIS / Transfer)</option>
                            <option value="Kredit">Kredit / Piutang Toko</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top p-3 d-flex justify-content-between">
                    <div>
                        <span class="text-secondary d-block" style="font-size:11px;">TOTAL BAYAR</span>
                        <strong class="fs-5 text-primary" id="modalGrandTotal">Rp 0</strong>
                    </div>
                    <button type="button" class="btn btn-primary px-4 fw-bold" id="btnSubmitSelfService" style="border-radius:12px;">
                        Kirim Pesanan <i class="bi bi-send-fill ms-1"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Success Order -->
    <div class="modal fade" id="successOrderModal" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center p-4" style="border-radius:20px;">
                <div class="mb-3">
                    <i class="bi bi-check-circle-fill text-success" style="font-size:64px;"></i>
                </div>
                <h4 class="fw-bold mb-1">Pesanan Berhasil Dikirim!</h4>
                <p class="text-muted" style="font-size:13px;">Tunjukkan nomor pesanan di bawah ini kepada kasir untuk pembayaran & cetak struk.</p>

                <div class="p-3 bg-light rounded border my-2">
                    <div class="text-secondary" style="font-size:11px;font-weight:600;">NOMOR PESANAN SELF-SERVICE</div>
                    <div class="h3 fw-bold text-primary mb-0 font-monospace" id="successOrderNumber">SS-XXXXX</div>
                </div>

                <button type="button" class="btn btn-primary w-100 mt-3 py-2 fw-bold" style="border-radius:12px;" onclick="window.location.reload()">
                    Selesai & Belanja Lagi
                </button>
            </div>
        </div>
    </div>

    <!-- Include Voice Transaction Modal Component -->
    @include('partials.voice-transaction-modal')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let cart = [];

        function formatRupiah(num) {
            return 'Rp ' + Number(num).toLocaleString('id-ID');
        }

        function addToCart(pId, name, price) {
            const existing = cart.find(i => i.product_id === pId);
            if (existing) {
                existing.qty += 1;
            } else {
                cart.push({ product_id: pId, nama_produk: name, harga_satuan: price, qty: 1 });
            }
            renderCart();
        }

        function updateQty(pId, change) {
            const existing = cart.find(i => i.product_id === pId);
            if (existing) {
                existing.qty += change;
                if (existing.qty <= 0) {
                    cart = cart.filter(i => i.product_id !== pId);
                }
            }
            renderCart();
        }

        function renderCart() {
            const itemCountEl = document.getElementById('cartItemCount');
            const totalDisplayEl = document.getElementById('cartTotalDisplay');
            const modalTotalEl = document.getElementById('modalGrandTotal');
            const listEl = document.getElementById('cartItemsList');

            const totalCount = cart.reduce((sum, i) => sum + i.qty, 0);
            const grandTotal = cart.reduce((sum, i) => sum + (i.qty * i.harga_satuan), 0);

            itemCountEl.textContent = totalCount;
            totalDisplayEl.textContent = formatRupiah(grandTotal);
            modalTotalEl.textContent = formatRupiah(grandTotal);

            if (cart.length === 0) {
                listEl.innerHTML = `
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-cart-x fs-1 d-block mb-2 opacity-50"></i>
                        Keranjang Anda masih kosong.
                    </div>`;
                return;
            }

            let html = '<div class="list-group list-group-flush">';
            cart.forEach(item => {
                const sub = item.qty * item.harga_satuan;
                html += `
                    <div class="list-group-item d-flex align-items-center justify-content-between px-0 py-2">
                        <div>
                            <div class="fw-bold" style="font-size:13px;">${item.nama_produk}</div>
                            <div class="text-muted" style="font-size:11px;">${formatRupiah(item.harga_satuan)} x ${item.qty}</div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="updateQty(${item.product_id}, -1)">-</button>
                            <span class="fw-bold" style="font-size:13px;">${item.qty}</span>
                            <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="updateQty(${item.product_id}, 1)">+</button>
                            <span class="fw-bold text-success ms-2" style="font-size:13px;">${formatRupiah(sub)}</span>
                        </div>
                    </div>`;
            });
            html += '</div>';
            listEl.innerHTML = html;
        }

        document.getElementById('btnOpenCartModal').addEventListener('click', function() {
            const modal = new bootstrap.Modal(document.getElementById('cartModal'));
            modal.show();
        });

        // Listen for Voice AI POS Apply Event
        window.addEventListener('pos-voice-apply', function(e) {
            const data = e.detail;
            if (data && data.items && data.items.length > 0) {
                data.items.forEach(item => {
                    if (item.produk_id) {
                        addToCart(parseInt(item.produk_id, 10), item.nama_produk, parseFloat(item.harga_satuan) || 0);
                    }
                });
            }
        });

        // Trigger Voice Modal
        document.getElementById('btnVoiceModal').addEventListener('click', function() {
            const modalEl = document.getElementById('voiceTransactionModal');
            if (modalEl) {
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            }
        });

        // Category Filter
        document.querySelectorAll('.cat-pill').forEach(pill => {
            pill.addEventListener('click', function() {
                document.querySelectorAll('.cat-pill').forEach(p => p.classList.remove('active'));
                this.classList.add('active');
                const cat = this.getAttribute('data-cat');
                document.querySelectorAll('.product-item').forEach(item => {
                    if (cat === 'all' || item.getAttribute('data-cat') === cat) {
                        item.style.display = 'flex';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });

        // Search Filter
        document.getElementById('ssSearchInput').addEventListener('input', function() {
            const q = this.value.toLowerCase().trim();
            document.querySelectorAll('.product-item').forEach(item => {
                const name = item.getAttribute('data-name');
                if (name.includes(q)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });

        // Submit Order to Server
        document.getElementById('btnSubmitSelfService').addEventListener('click', async function() {
            if (cart.length === 0) {
                alert('Keranjang Anda masih kosong.');
                return;
            }

            const namaPelanggan = document.getElementById('ssNamaPelanggan').value.trim();
            const metodeBayar = document.getElementById('ssMetodeBayar').value;
            const submitBtn = this;

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="bi bi-arrow-repeat spin me-1"></i>Mengirim...';

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch('{{ route("self-service.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken || '',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        toko_id: {{ $toko->id ?? 1 }},
                        nama_pelanggan: namaPelanggan,
                        metode_pembayaran: metodeBayar,
                        items: cart.map(i => ({ product_id: i.product_id, qty: i.qty }))
                    })
                });

                const data = await res.json();
                if (res.ok && data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('cartModal'))?.hide();
                    document.getElementById('successOrderNumber').textContent = data.nomor_pesanan;
                    const successModal = new bootstrap.Modal(document.getElementById('successOrderModal'));
                    successModal.show();
                    cart = [];
                    renderCart();
                } else {
                    alert('Gagal mengirim pesanan: ' + (data.message || 'Terjadi kesalahan.'));
                }
            } catch (err) {
                alert('Kesalahan koneksi: ' + err.message);
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Kirim Pesanan <i class="bi bi-send-fill ms-1"></i>';
            }
        });
    </script>
</body>
</html>
