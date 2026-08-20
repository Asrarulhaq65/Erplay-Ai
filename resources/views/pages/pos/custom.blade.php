<!DOCTYPE html>
{{--
    POS MODE 2: Custom Quick-Entry (Keyboard-Driven)
    ─────────────────────────────────────────────────
    Standalone Blade page — no layout extension required.
    This file includes Bootstrap 5, Bootstrap Icons, and all
    vanilla JavaScript logic inline.

    Routes required (register in routes/web.php):
        Route::get('/pos/custom', [PosController::class, 'custom'])->name('pos.custom');

    Routes required (register in routes/api.php):
        Route::get('/pos/search-pelanggan', [PelangganSearchController::class, 'index'])->name('pos.search-pelanggan');
        Route::get('/pos/search-produk',    [ProductSearchController::class,  'index'])->name('pos.search-produk');
        Route::post('/pos/checkout',        [PenjualanController::class,       'store'])->name('pos.checkout');
--}}
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>POS Mode Kilat — {{ config('app.name', 'Retail ERP') }}</title>

    {{-- Bootstrap 5 --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    {{-- Google Font: Plus Jakarta Sans & Inter --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* ── CSS Custom Properties & Dark Mode ────────────────────────── */
        :root {
            --pb-lightest : #EBF3F5;
            --pb-light    : #D6E9F0;
            --pb-mid      : #A0C4DF;
            --pb-accent   : #5B9EC9;
            --pb-dark     : #0D4E56;
            --pb-darker   : #09373D;
            --pb-text     : #0D4E56;
            --warn-bg     : #FFF0F0;
            --warn-border : #FF8080;
            --warn-text   : #C0392B;
            --success-col : #198754;
            --body-bg     : #F4F7F9;
            --bg-card     : #FFFFFF;
            --text-primary: #1E293B;
            --text-secondary: #64748B;
            --text-muted  : #94A3B8;
            --border-light: #E2E8F0;
        }

        [data-theme="dark"] {
            --pb-lightest : #162428;
            --pb-light    : #1E3238;
            --pb-mid      : #2A464E;
            --pb-accent   : #4DB8C4;
            --pb-dark     : #4DB8C4;
            --pb-darker   : #389BA6;
            --pb-text     : #E2F1F3;
            --warn-bg     : rgba(220, 38, 38, 0.15);
            --warn-border : rgba(220, 38, 38, 0.3);
            --warn-text   : #F87171;
            --success-col : #34D399;
            --body-bg     : #0F171A;
            --bg-card     : #152226;
            --text-primary: #F1F5F9;
            --border-light: #24363D;
        }

        [data-theme="dark"] .form-control,
        [data-theme="dark"] .form-select {
            background-color: var(--body-bg) !important;
            color: var(--text-primary) !important;
            border-color: var(--border-light) !important;
        }

        /* ── High-Contrast Placeholder & Topbar Improvements ───────────── */
        ::placeholder,
        ::-webkit-input-placeholder,
        ::-moz-placeholder,
        :-ms-input-placeholder {
            color: #64748B !important;
            opacity: 1 !important;
        }

        [data-theme="dark"] ::placeholder,
        [data-theme="dark"] ::-webkit-input-placeholder,
        [data-theme="dark"] ::-moz-placeholder,
        [data-theme="dark"] :-ms-input-placeholder {
            color: #94A3B8 !important;
            opacity: 1 !important;
        }

        #pos-topbar .pos-meta {
            color: var(--text-primary) !important;
            font-weight: 600;
        }

        #pos-topbar .btn-outline-secondary {
            color: var(--text-primary) !important;
            border-color: var(--pb-mid) !important;
            background: var(--bg-card) !important;
            font-weight: 600;
        }

        #pos-topbar .btn-outline-secondary:hover {
            background: var(--pb-accent) !important;
            color: #FFFFFF !important;
            border-color: var(--pb-accent) !important;
        }

        .btn-pb {
            background-color: var(--pb-dark) !important;
            color: #FFFFFF !important;
            border: 1px solid var(--pb-dark) !important;
            font-weight: 700 !important;
        }

        .btn-pb:hover {
            background-color: var(--pb-darker) !important;
            color: #FFFFFF !important;
        }

        [data-theme="dark"] .btn-pb {
            background-color: var(--pb-darker) !important;
            color: #FFFFFF !important;
            border: 1px solid var(--pb-accent) !important;
        }

        [data-theme="dark"] #pos-topbar {
            background: linear-gradient(135deg, #111C1F 0%, #17272C 100%) !important;
            border-bottom-color: #24363D !important;
        }

        [data-theme="dark"] #pos-topbar .pos-title {
            color: #38BDF8 !important;
        }

        [data-theme="dark"] #pos-topbar .pos-meta {
            color: #E2E8F0 !important;
        }

        [data-theme="dark"] #pos-topbar .btn-outline-secondary {
            color: #F1F5F9 !important;
            border-color: #334155 !important;
            background: #1E293B !important;
        }

        /* ── Base ──────────────────────────────────────────────────────── */
        * { box-sizing: border-box; }

        html, body {
            height: 100%;
            font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
            font-size: 13px;
            background-color: var(--body-bg);
            color: var(--text-primary);
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        /* ── POS Wrapper ───────────────────────────────────────────────── */
        #pos-wrapper {
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
        }

        /* ── Top Bar ───────────────────────────────────────────────────── */
        #pos-topbar {
            background: linear-gradient(135deg, var(--pb-lightest) 0%, var(--pb-light) 100%);
            border-bottom: 2px solid var(--pb-mid);
            padding: 6px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-shrink: 0;
            box-shadow: 0 2px 6px rgba(91,158,201,0.12);
        }

        #pos-topbar .pos-title {
            font-size: 14px;
            font-weight: 700;
            color: var(--pb-text);
            letter-spacing: 0.3px;
        }

        #pos-topbar .pos-meta {
            font-size: 11px;
            color: var(--text-muted);
        }

        /* ── Customer Panel ────────────────────────────────────────────── */
        #pos-customer-panel {
            background: var(--bg-card);
            border-bottom: 1px solid #E2EDF2;
            padding: 6px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }

        #pos-customer-panel label {
            font-size: 11px;
            font-weight: 600;
            color: var(--pb-text);
            white-space: nowrap;
        }

        .customer-search-wrap {
            position: relative;
            width: 280px;
        }

        /* ── Quick Entry Bar ───────────────────────────────────────────── */
        #pos-entry-bar {
            background: var(--pb-lightest);
            border-bottom: 1px solid var(--pb-light);
            padding: 7px 16px;
            display: flex;
            align-items: flex-start;
            gap: 8px;
            flex-shrink: 0;
        }

        .product-search-wrap {
            position: relative;
            flex: 1;
            max-width: 520px;
        }

        .qty-wrap {
            width: 130px;
            flex-shrink: 0;
        }

        /* ── Floating Dropdown ─────────────────────────────────────────── */
        .pos-dropdown {
            position: absolute;
            top: calc(100% + 3px);
            left: 0;
            right: 0;
            background: var(--bg-card);
            border: 1px solid var(--pb-mid);
            border-radius: 6px;
            box-shadow: 0 6px 24px rgba(0,0,0,0.12);
            z-index: 1050;
            max-height: 260px;
            overflow-y: auto;
        }

        .dropdown-item-pos {
            display: flex;
            align-items: center;
            width: 100%;
            padding: 7px 12px;
            background: transparent;
            border: none;
            border-bottom: 1px solid #F0F4F7;
            cursor: pointer;
            text-align: left;
            font-size: 12px;
            transition: background 0.12s;
        }

        .dropdown-item-pos:last-child { border-bottom: none; }

        .dropdown-item-pos:hover,
        .dropdown-item-pos.active {
            background: var(--pb-lightest);
        }

        .dropdown-item-pos:disabled,
        .dropdown-item-pos[disabled] {
            opacity: 0.5;
            cursor: not-allowed;
            background: #fafafa;
        }

        /* ── Body Row (below topbar) — contains left-col + sidebar ───── */
        #pos-body {
            display: flex;
            flex: 1;
            overflow: hidden;
        }

        /* ── Left Column 60%: customer + entry + cart ──────────────────── */
        #pos-left-col {
            width: 60%;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            background: var(--bg-card);
        }
        #pos-main {
            flex: 1;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        /* ── Cart Area ─────────────────────────────────────────────────── */
        #pos-cart-area {
            flex: 1;
            overflow-y: auto;
            padding: 10px 16px;
            background: var(--bg-card);
        }
        .cart-table thead th {
            background: var(--pb-lightest);
            color: var(--pb-text);
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            border-bottom: 2px solid var(--pb-mid);
            padding: 6px 8px;
            white-space: nowrap;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .cart-table tbody td {
            font-size: 12px;
            padding: 5px 8px;
            vertical-align: middle;
            border-color: #EFF3F7;
        }

        .cart-table tbody tr:hover { background: var(--pb-lightest); }

        /* ── Payment Sidebar — 40% golden ratio column ──────────────── */
        #pos-payment-sidebar {
            width: 40%;
            flex-shrink: 0;
            background: rgba(var(--glass-bg), 0.4);
            border-left: 2px solid var(--pb-light);
            overflow-y: auto;
            padding: 10px 12px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .total-display-box {
            background: linear-gradient(135deg, var(--pb-dark) 0%, var(--pb-darker) 100%);
            border-radius: 10px;
            padding: 12px 16px;
            color: #fff;
            text-align: center;
        }

        .total-display-box .label-sm {
            font-size: 10px;
            opacity: 0.8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .total-display-box .amount {
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.5px;
            line-height: 1.1;
        }

        .payment-section-card {
            background: var(--bg-card);
            border: 1px solid #E2EDF2;
            border-radius: 8px;
            padding: 7px 10px;
        }

        .payment-section-card .section-label {
            font-size: 10px;
            font-weight: 600;
            color: #7A97A7;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 7px;
        }

        /* ── Payment Method Buttons ────────────────────────────────────── */
        .btn-payment-method {
            flex: 1;
            font-size: 11px;
            font-weight: 600;
            padding: 5px 4px;
            border: 1.5px solid var(--pb-mid);
            background: var(--bg-card);
            color: var(--pb-text);
            border-radius: 5px;
            transition: all 0.15s;
            cursor: pointer;
        }

        .btn-payment-method:hover {
            background: var(--pb-lightest);
        }

        .btn-payment-method.active {
            background: var(--pb-dark);
            border-color: var(--pb-dark);
            color: #fff;
            box-shadow: 0 2px 8px rgba(91,158,201,0.35);
        }

        /* ── Kembalian Display ─────────────────────────────────────────── */
        .kembalian-box {
            text-align: center;
            padding: 8px;
            background: #F0FAF4;
            border: 1px solid #B7DFCC;
            border-radius: 7px;
        }

        .kembalian-box .kem-label {
            font-size: 10px;
            color: #5A8070;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .kembalian-box .kem-amount {
            font-size: 16px;
            font-weight: 700;
            color: var(--success-col);
        }

        /* ── Checkout Button ───────────────────────────────────────────── */
        #checkoutBtn {
            background: linear-gradient(135deg, var(--pb-dark) 0%, var(--pb-darker) 100%);
            border: none;
            color: #fff;
            font-size: 13px;
            font-weight: 700;
            padding: 10px;
            border-radius: 8px;
            width: 100%;
            letter-spacing: 0.3px;
            transition: all 0.2s;
            box-shadow: 0 3px 10px rgba(61,133,176,0.4);
        }

        #checkoutBtn:disabled {
            background: #B0C8D8;
            box-shadow: none;
            cursor: not-allowed;
        }

        #checkoutBtn:not(:disabled):hover {
            background: linear-gradient(135deg, var(--pb-darker) 0%, #2E6E98 100%);
            box-shadow: 0 4px 14px rgba(61,133,176,0.5);
            transform: translateY(-1px);
        }

        /* ── Tier Badges ───────────────────────────────────────────────── */
        .badge-tier-umum    { background-color: #6c757d; color:#fff; }
        .badge-tier-member  { background-color: #198754; color:#fff; }
        .badge-tier-rekan   { background-color: #fd7e14; color:#fff; }
        .badge-tier-motoris { background-color: var(--pb-dark); color:#fff; }

        /* ── Price Badge ───────────────────────────────────────────────── */
        .price-mode-badge {
            font-size: 11px;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 20px;
            background: var(--pb-light);
            color: var(--pb-text);
            border: 1px solid var(--pb-mid);
            white-space: nowrap;
        }

        /* ── Stock Warning ─────────────────────────────────────────────── */
        #qtyWarning {
            font-size: 11px;
            color: var(--warn-text);
            margin-top: 3px;
            min-height: 16px;
        }

        /* ── Cart Item Count Badge ─────────────────────────────────────── */
        .cart-count-badge {
            font-size: 10px;
            padding: 1px 6px;
            background: var(--pb-dark);
            color: #fff;
            border-radius: 20px;
            font-weight: 700;
        }

        /* ── Shortcut Key Hints ────────────────────────────────────────── */
        .key-hint {
            font-size: 10px;
            color: #8FA8B5;
            background: #EDF2F6;
            border: 1px solid #D0DDE5;
            border-radius: 3px;
            padding: 1px 4px;
            font-weight: 600;
        }

        /* ── Form Inputs ───────────────────────────────────────────────── */
        .form-control-sm, .form-select-sm {
            font-size: 12px;
            border-color: #C8DDE8;
        }

        .form-control-sm:focus, .form-select-sm:focus {
            border-color: var(--pb-accent);
            box-shadow: 0 0 0 0.15rem rgba(123,184,212,0.25);
        }

        /* ── Scrollbar Styling ─────────────────────────────────────────── */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: #F0F4F8; }
        ::-webkit-scrollbar-thumb { background: var(--pb-mid); border-radius: 4px; }

        /* ── Error Alert ───────────────────────────────────────────────── */
        #errorAlertContainer {
            position: fixed;
            top: 10px;
            right: 16px;
            z-index: 1080;
            width: 340px;
        }

        /* ── Toast ─────────────────────────────────────────────────────── */
        #successToast {
            font-size: 12px;
        }

        /* ── Animations ────────────────────────────────────────────────── */
        @keyframes flash-added {
            0%   { background-color: #D4EDDA; }
            100% { background-color: transparent; }
        }

        .row-flash-added {
            animation: flash-added 0.7s ease-out;
        }

        /* ── Accessibility: Reduced Motion ─────────────────────────────── */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }

        /* ── Customer locked indicator ─────────────────────────────────── */
        .customer-locked-icon {
            color: var(--pb-dark);
            font-size: 13px;
        }

        .customer-unlocked-icon {
            color: #ADB5BD;
            font-size: 13px;
        }
    </style>
</head>

<body>
<div id="pos-wrapper" role="main" aria-label="POS Mode Kilat">

    {{-- ════════════════════════════════════════════════════════════
         TOP BAR
    ════════════════════════════════════════════════════════════ --}}
    <div id="pos-topbar">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-lightning-charge-fill" style="color:var(--pb-dark); font-size:16px;"></i>
            <span class="pos-title">POS MODE KILAT</span>
            <span class="badge badge-tier-umum" style="font-size:9px; padding:2px 6px;">Custom Quick-Entry</span>
        </div>

        <div class="d-flex align-items-center gap-3">
            {{-- Error Alert Container --}}
            <div id="errorAlertContainer"></div>

            <span class="pos-meta">
                <i class="bi bi-hash me-1"></i>
                <span id="invoiceDisplay" style="font-weight:600; color:var(--pb-text);">—</span>
            </span>
            <span class="pos-meta">
                <i class="bi bi-calendar2 me-1"></i>
                <span id="posDate">—</span>
            </span>
            <span class="pos-meta">
                <i class="bi bi-person-circle me-1"></i>
                {{ auth()->user()->nama_lengkap ?? 'Kasir' }}
            </span>
            <button type="button" id="posDarkModeToggle" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size:10.5px; border-radius:5px;" title="Ganti Tema Terang/Gelap" aria-label="Toggle Dark Mode">
                <i class="bi bi-moon-stars" id="posDarkModeIcon" aria-hidden="true"></i>
            </button>
            <a href="{{ url('/dashboard') }}" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size:10.5px; border-radius:5px;">
                <i class="bi bi-house me-1" aria-hidden="true"></i>Dashboard
            </a>
            <a href="{{ route('laporan.rekap-penjualan') }}" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size:10.5px; border-radius:5px;" title="Lihat riwayat transaksi penjualan">
                <i class="bi bi-clock-history me-1" aria-hidden="true"></i>Riwayat
            </a>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         BODY: LEFT COLUMN (60%) + PAYMENT SIDEBAR (40%)
    ════════════════════════════════════════════════════════════ --}}
    <div id="pos-body">
    <div id="pos-left-col">

    {{-- ─── CUSTOMER SELECTION PANEL ──────────────────────────── --}}
    <div id="pos-customer-panel" role="region" aria-label="Pemilihan Pelanggan">
        <i class="bi bi-person-fill" style="color:var(--pb-accent); font-size:15px;" aria-hidden="true"></i>
        <label for="customerInput">Pelanggan</label>

        <div class="customer-search-wrap">
            <div class="input-group input-group-sm">
                <input type="text"
                       id="customerInput"
                       class="form-control form-control-sm"
                       placeholder="Ketik nama / kode, atau Enter untuk Umum…"
                       aria-label="Cari pelanggan"
                       autocomplete="off"
                       style="border-color: var(--pb-mid);">
                <span class="input-group-text" style="background:var(--pb-lightest); border-color:var(--pb-mid);">
                    <i id="customerLockIcon" class="bi bi-unlock customer-unlocked-icon"></i>
                </span>
            </div>
            {{-- Customer search results dropdown --}}
            <div id="customerDropdown" class="pos-dropdown d-none"></div>
        </div>

        {{-- Active tier badge --}}
        <span id="customerTierBadge" class="badge badge-tier-umum price-mode-badge">Umum Mode</span>

        <div class="vr mx-1"></div>

        {{-- Quick shortcut hints --}}
        <span class="text-muted" style="font-size:10px;">
            <span class="key-hint">F8</span> Cari Produk &nbsp;
            <span class="key-hint">F9</span> Bayar
        </span>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         QUICK ENTRY BAR
    ════════════════════════════════════════════════════════════ --}}
    <div id="pos-entry-bar" role="region" aria-label="Entri Produk">
        <i class="bi bi-upc-scan" style="color:var(--pb-accent); font-size:18px; padding-top:4px; flex-shrink:0;" aria-hidden="true"></i>

        {{-- Product Search Input + Floating Dropdown --}}
        <div class="product-search-wrap position-relative">
            <input type="text"
                   id="productInput"
                   class="form-control form-control-sm"
                   placeholder="Ketik nama produk / barcode (min 2 karakter)…"
                   aria-label="Cari produk"
                   autocomplete="off"
                   style="border-color: var(--pb-mid);">
            {{-- Floating product dropdown --}}
            <ul id="productDropdown" class="list-group position-absolute w-100 shadow border d-none py-0" style="background-color: var(--bg-card); z-index: 1050; max-height: 260px; overflow-y: auto;"></ul>
        </div>

        {{-- Price badge (shown after product selected) --}}
        <span id="productPriceBadge" class="price-mode-badge d-none" style="padding-top:4px;"></span>

        {{-- Quantity Input --}}
        <div class="qty-wrap">
            <div class="input-group input-group-sm">
                 <span class="input-group-text" style="background:var(--pb-lightest); border-color:var(--pb-mid); font-size:11px; color: var(--text-muted);">Qty</span>
                <input type="number"
                       id="qtyInput"
                       class="form-control form-control-sm text-center"
                       value="1"
                       min="1"
                       max="9999"
                       aria-label="Jumlah produk"
                       style="border-color: var(--pb-mid);">
            </div>
            <div id="qtyWarning" class="d-none"></div>
        </div>

        {{-- Add Button --}}
        <button id="addBtn"
                class="btn btn-sm"
                style="background:var(--pb-dark); color:#fff; border:none; font-size:12px; font-weight:600; padding:4px 14px; border-radius:5px; flex-shrink:0; height:31px;"
                aria-label="Tambah produk ke keranjang">
            <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Tambah
        </button>
    </div>

    {{-- ─── CART TABLE ────────────────────────────────────────── --}}
    <div id="pos-main">
        <div id="pos-cart-area" role="region" aria-label="Keranjang Belanja">
            <div class="d-flex align-items-center mb-1 gap-2">
                <i class="bi bi-cart3" style="color:var(--pb-accent);"></i>
                <span style="font-size:12px; font-weight:600; color:var(--pb-text);">Keranjang Belanja</span>
                <span id="cartCount" class="cart-count-badge">0</span>
                <span class="ms-auto text-muted" style="font-size:10px;">
                    <span class="key-hint">Enter</span> pada Qty untuk tambah item
                </span>
            </div>

            <table class="table table-sm table-hover table-bordered cart-table mb-0">
                <thead>
                    <tr>
                        <th class="text-center" style="width:36px;" scope="col">No</th>
                        <th style="width:110px;" scope="col">Barcode</th>
                        <th scope="col">Nama Produk</th>
                        <th class="text-center" style="width:72px;" scope="col">Mode Harga</th>
                        <th class="text-end" style="width:100px;" scope="col">Harga/Satuan</th>
                        <th class="text-center" style="width:110px;" scope="col">Qty</th>
                        <th class="text-end" style="width:105px;" scope="col">Subtotal</th>
                        <th class="text-center" style="width:38px;" scope="col"><span class="visually-hidden">Aksi</span></th>
                    </tr>
                </thead>
                <tbody id="cartBody">
                    <tr id="cartEmptyRow">
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="bi bi-cart3 me-1" style="font-size:16px;"></i><br>
                            <span style="font-size:12px;">Keranjang kosong. Scan barcode atau ketik nama produk di atas.</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>{{-- /#pos-cart-area --}}
    </div>{{-- /#pos-main --}}
    </div>{{-- /#pos-left-col --}}

    {{-- ─── PAYMENT SIDEBAR (40%, full height below topbar) ───── --}}
    <div id="pos-payment-sidebar" role="region" aria-label="Pembayaran">

            {{-- Grand Total Box --}}
            <div class="total-display-box">
                <div class="label-sm">Total Belanja</div>
                <div class="amount" id="grandTotalDisplay" aria-live="polite">Rp 0</div>
            </div>

            {{-- Diskon & Total Bayar --}}
            <div class="payment-section-card">
                <div class="section-label">Potongan / Diskon</div>
                <div class="input-group input-group-sm">
                    <span class="input-group-text" style="font-size:11px;">Rp</span>
                    <input type="number"
                           id="diskonInput"
                           class="form-control form-control-sm text-end"
                           value="0"
                           min="0"
                           aria-label="Jumlah diskon">
                </div>
                <div class="d-flex justify-content-between align-items-center mt-1">
                    <span style="font-size:11px; color: var(--text-muted); font-weight:600;">Total Bayar</span>
                    <span id="totalBayarDisplay" style="font-size:14px; font-weight:700; color:var(--pb-text);">Rp 0</span>
                </div>
            </div>

            {{-- Metode Pembayaran --}}
            <div class="payment-section-card">
                <div class="section-label">Metode Pembayaran</div>
                <div class="d-flex gap-1">
                    <button class="btn-payment-method active" id="btnTunai" aria-label="Metode pembayaran Tunai" data-method="Tunai">
                        <i class="bi bi-cash-stack d-block" style="font-size:14px;" aria-hidden="true"></i>
                        Tunai
                    </button>
                    <button class="btn-payment-method" id="btnKredit" aria-label="Metode pembayaran Kredit" data-method="Kredit">
                        <i class="bi bi-calendar-check d-block" style="font-size:14px;" aria-hidden="true"></i>
                        Kredit
                    </button>
                    <button class="btn-payment-method" id="btnDigitalPayment" aria-label="Metode pembayaran Digital" data-method="Digital Payment">
                        <i class="bi bi-qr-code-scan d-block" style="font-size:14px;" aria-hidden="true"></i>
                        Digital
                    </button>
                </div>
            </div>

            {{-- Nominal Uang (Tunai only) --}}
            <div class="payment-section-card" id="nominalUangRow">
                <div class="section-label">Nominal Uang Bayar</div>
                <div class="input-group input-group-sm">
                    <span class="input-group-text" style="font-size:11px;">Rp</span>
                    <input type="number"
                           id="nominalUang"
                           class="form-control form-control-sm text-end"
                           aria-label="Nominal uang bayar"
                           placeholder="0"
                           min="0">
                </div>
            </div>

            {{-- Kembalian (Tunai only) --}}
            <div class="kembalian-box" id="kembalianRow">
                <div class="kem-label"><i class="bi bi-arrow-return-left me-1"></i>Kembalian</div>
                <div class="kem-amount" id="kembalianDisplay" aria-live="polite">Rp 0</div>
            </div>

            {{-- Referensi Digital (Digital Payment only) --}}
            <div class="payment-section-card d-none" id="refDigitalRow">
                <div class="section-label">Referensi / No. Transaksi Digital</div>
                <input type="text"
                       id="refDigitalInput"
                       class="form-control form-control-sm"
                       placeholder="QRIS / Transfer ref. (opsional)">
            </div>

            {{-- Kredit Info & Partial DP --}}
            <div class="payment-section-card d-none" id="kreditInfoRow" style="background:rgba(217,119,6,0.06); border-color:rgba(217,119,6,0.3);">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span style="font-size:11px; font-weight:700; color:#B45309;">
                        <i class="bi bi-calendar-check me-1"></i>Penjualan Kredit (Tempo)
                    </span>
                    <span id="kreditStatusBadge" class="badge" style="background:rgba(217,119,6,0.15); color:#B45309; font-size:10px;">Belum Lunas</span>
                </div>
                <div class="mb-2">
                    <label for="uangMukaInput" class="section-label mb-1" style="font-size:10.5px;">Bayar Uang Muka / DP (Rp)</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text" style="font-size:11px;">Rp</span>
                        <input type="number"
                               id="uangMukaInput"
                               class="form-control form-control-sm text-end"
                               placeholder="0"
                               min="0"
                               value="0"
                               aria-label="Uang Muka">
                    </div>
                </div>
                <div class="mb-2">
                    <label for="jatuhTempoInput" class="section-label mb-1" style="font-size:10.5px;">Jatuh Tempo (opsional)</label>
                    <input type="date"
                           id="jatuhTempoInput"
                           class="form-control form-control-sm"
                           style="font-size:11px;">
                </div>
                <div class="d-flex justify-content-between align-items-center pt-1" style="border-top:1px dashed rgba(217,119,6,0.3); font-size:11.5px;">
                    <span style="color:var(--text-muted); font-weight:600;">Sisa Piutang:</span>
                    <span id="sisaPiutangDisplay" style="font-size:13px; font-weight:700; color:#DC2626;">Rp 0</span>
                </div>
            </div>

            {{-- Checkout Button --}}
            <button id="checkoutBtn" disabled aria-label="Proses Transaksi (F9)">
                <span id="checkoutSpinner" class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
                <span id="checkoutBtnText">
                    <i class="bi bi-lightning-charge-fill me-1"></i>Proses Transaksi
                    <span class="key-hint ms-2" style="color:rgba(255,255,255,0.7); background:rgba(255,255,255,0.15); border-color:rgba(255,255,255,0.3);">F9</span>
                </span>
            </button>

            {{-- Action Buttons (Cancel, Hold, Recall) --}}
            <div class="d-flex gap-2 mt-1">
                <button type="button" id="btnCancelTransaction" class="btn btn-sm btn-danger flex-fill" style="background-color: #ffcccc; color: #cc0000; border-color: #ffcccc; font-weight: 600;" aria-label="Batal Transaksi">
                    Batal Transaksi
                </button>
                <button type="button" id="btnHoldTransaction" class="btn btn-sm btn-info flex-fill" style="background-color: #d1ecf1; color: #0c5460; border-color: #d1ecf1; font-weight: 600;" aria-label="Tahan Transaksi">
                    Tahan Transaksi
                </button>
            </div>
            <button type="button" id="btnShowHeld" class="btn btn-sm btn-secondary w-100 mt-1" style="background-color: #e2e3e5; color: #383d41; border-color: #e2e3e5; font-weight: 600;" aria-label="Lihat Transaksi Tertahan">
                💥 Transaksi Tertahan (<span id="hold-count">0</span>)
            </button>

    </div>{{-- /#pos-payment-sidebar --}}
    </div>{{-- /#pos-body --}}
</div>{{-- /#pos-wrapper --}}


{{-- ════════════════════════════════════════════════════════════════
     SUCCESS TOAST
════════════════════════════════════════════════════════════════ --}}
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:var(--z-toast, 600);">
    <div id="successToast" class="toast align-items-center text-bg-success border-0" role="alert" data-bs-delay="5000">
        <div class="toast-body d-flex gap-2 align-items-start">
            <i class="bi bi-check-circle-fill mt-1" style="font-size:18px; flex-shrink:0;"></i>
            <div>
                <div class="fw-bold mb-1">Transaksi Berhasil!</div>
                <div><strong>Invoice:</strong> <span id="toastInvoice">—</span></div>
                <div><strong>Total Bayar:</strong> <span id="toastTotal">—</span></div>
                <div><strong>Kembalian:</strong> <span id="toastKembalian">—</span></div>
                <div><strong>Item:</strong> <span id="toastItems">—</span> produk</div>
            </div>
            <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════════
     HELD TRANSACTIONS MODAL
════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="heldModal" tabindex="-1" aria-labelledby="heldModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header" style="background:var(--pb-lightest); border-bottom:2px solid var(--pb-mid);">
                <h6 class="modal-title fw-bold" id="heldModalLabel" style="color:var(--pb-text);">
                    <i class="bi bi-clock-history me-1"></i>Daftar Transaksi Tertahan
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                    <table class="table table-sm table-hover mb-0" style="font-size: 12px;">
                        <thead style="background: rgba(var(--glass-bg), 0.4); position: sticky; top: 0; z-index: var(--z-sticky, 10);">
                            <tr>
                                <th class="text-center" style="width: 50px;">No</th>
                                <th>Waktu / Catatan</th>
                                <th class="text-center">Pelanggan</th>
                                <th class="text-center">Total Item</th>
                                <th class="text-end">Total Nilai</th>
                                <th class="text-center" style="width: 150px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="heldTransactionsBody">
                            <tr>
                                <td colspan="6" class="text-center text-muted py-3">Tidak ada transaksi tertahan.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


{{-- ════════════════════════════════════════════════════════════════
     BOOTSTRAP JS
════════════════════════════════════════════════════════════════ --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


{{-- ════════════════════════════════════════════════════════════════
     POS QUICK-ENTRY JAVASCRIPT ENGINE
════════════════════════════════════════════════════════════════ --}}
<script>
'use strict';

// ── Bind onclick replacements for accessibility ─────────────────────────────
document.querySelectorAll('.btn-payment-method[data-method]').forEach(function(btn) {
    btn.addEventListener('click', function() {
        setMetodePembayaran(this.dataset.method);
    });
});
document.getElementById('btnCancelTransaction')?.addEventListener('click', cancelTransaction);
document.getElementById('btnHoldTransaction')?.addEventListener('click', holdTransaction);
document.getElementById('btnShowHeld')?.addEventListener('click', showHeldTransactions);

// ═══════════════════════════════════════════════════════════════════════════
// SECTION 1: CONSTANTS & CONFIGURATION
// ═══════════════════════════════════════════════════════════════════════════

const API = {
    searchPelanggan : '/api/pos/search-pelanggan',
    searchProduk    : '/api/pos/search-produk',
    checkout        : '/api/pos/checkout',
};

const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

/** Maps pelanggan.status_pelanggan → produk price column key */
const PRICE_TIER_MAP = {
    Umum    : 'harga_jual_umum',
    Member  : 'harga_jual_member',
    Rekan   : 'harga_jual_rekan',
    Motoris : 'harga_jual_motoris',
};

/** Maps status → badge CSS class */
const TIER_BADGE_CLASS = {
    Umum    : 'badge-tier-umum',
    Member  : 'badge-tier-member',
    Rekan   : 'badge-tier-rekan',
    Motoris : 'badge-tier-motoris',
};


// ═══════════════════════════════════════════════════════════════════════════
// SECTION 2: APPLICATION STATE
// ═══════════════════════════════════════════════════════════════════════════

const state = {
    // Customer
    customer           : { id: null, nama: 'Umum', status: 'Umum' },
    customerLocked     : false,
    pelangganResults   : [],
    pelangganIndex     : -1,

    // Product Search
    selectedProduct    : null,
    searchResults      : [],
    activeResultIndex  : -1,
    isStockValid       : true,

    // Cart
    cart               : [],     // Array of cart item objects
    nextRowId          : 1,      // Auto-increment row identifier

    // Payment
    grandTotal         : 0,
    diskon             : 0,
    totalBayar         : 0,
    metodePembayaran   : 'Tunai',
};


// ═══════════════════════════════════════════════════════════════════════════
// SECTION 3: DOM ELEMENT REFERENCES
// Short-hand: el.xxx maps to document.getElementById('xxx')
// ═══════════════════════════════════════════════════════════════════════════

const el = {
    // Top Bar
    invoiceDisplay      : document.getElementById('invoiceDisplay'),
    posDate             : document.getElementById('posDate'),

    // Customer
    customerInput       : document.getElementById('customerInput'),
    customerDropdown    : document.getElementById('customerDropdown'),
    customerTierBadge   : document.getElementById('customerTierBadge'),
    customerLockIcon    : document.getElementById('customerLockIcon'),

    // Quick Entry
    productInput        : document.getElementById('productInput'),
    productDropdown     : document.getElementById('productDropdown'),
    productPriceBadge   : document.getElementById('productPriceBadge'),
    qtyInput            : document.getElementById('qtyInput'),
    qtyWarning          : document.getElementById('qtyWarning'),
    addBtn              : document.getElementById('addBtn'),

    // Cart
    cartBody            : document.getElementById('cartBody'),
    cartCount           : document.getElementById('cartCount'),

    // Payment Sidebar
    grandTotalDisplay   : document.getElementById('grandTotalDisplay'),
    diskonInput         : document.getElementById('diskonInput'),
    totalBayarDisplay   : document.getElementById('totalBayarDisplay'),
    nominalUangRow      : document.getElementById('nominalUangRow'),
    nominalUang         : document.getElementById('nominalUang'),
    kembalianRow        : document.getElementById('kembalianRow'),
    kembalianDisplay    : document.getElementById('kembalianDisplay'),
    refDigitalRow       : document.getElementById('refDigitalRow'),
    refDigitalInput     : document.getElementById('refDigitalInput'),
    kreditInfoRow       : document.getElementById('kreditInfoRow'),
    uangMukaInput       : document.getElementById('uangMukaInput'),
    jatuhTempoInput     : document.getElementById('jatuhTempoInput'),
    sisaPiutangDisplay  : document.getElementById('sisaPiutangDisplay'),
    kreditStatusBadge   : document.getElementById('kreditStatusBadge'),
    checkoutBtn         : document.getElementById('checkoutBtn'),
    checkoutSpinner     : document.getElementById('checkoutSpinner'),
    checkoutBtnText     : document.getElementById('checkoutBtnText'),

    // Toast
    successToast        : document.getElementById('successToast'),
    toastInvoice        : document.getElementById('toastInvoice'),
    toastTotal          : document.getElementById('toastTotal'),
    toastKembalian      : document.getElementById('toastKembalian'),
    toastItems          : document.getElementById('toastItems'),

    // Error container
    errorAlertContainer : document.getElementById('errorAlertContainer'),
};


// ═══════════════════════════════════════════════════════════════════════════
// SECTION 4: UTILITY FUNCTIONS
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Format a number as Indonesian Rupiah currency string.
 * Example: 15000 → "Rp 15.000"
 */
function formatRupiah(amount) {
    return new Intl.NumberFormat('id-ID', {
        style                : 'currency',
        currency             : 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount || 0);
}

/**
 * Debounce: delays invoking fn until after wait ms have elapsed
 * since the last invocation. Used for search inputs.
 */
function debounce(fn, wait = 300) {
    let timer;
    return function (...args) {
        clearTimeout(timer);
        timer = setTimeout(() => fn.apply(this, args), wait);
    };
}

/**
 * Centralized fetch wrapper with JSON headers and CSRF token.
 * Throws an error for non-OK HTTP responses.
 */
async function apiFetch(url, opts = {}) {
    const response = await fetch(url, {
        headers: {
            'Content-Type' : 'application/json',
            'Accept'       : 'application/json',
            'X-CSRF-TOKEN' : CSRF,
        },
        ...opts,
    });
    return response.json();
}

/**
 * Safely escape HTML entities to prevent XSS when inserting
 * user/API data into innerHTML.
 */
function escapeHtml(str) {
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(str ?? ''));
    return div.innerHTML;
}

/**
 * Show a dismissible error alert in the top-right error container.
 */
function showErrorAlert(message, errors = null) {
    let html = `<strong>${escapeHtml(message)}</strong>`;

    if (errors) {
        const errList = Array.isArray(errors)
            ? errors
            : Object.values(errors).flat();

        if (errList.length > 0) {
            html += '<ul class="mb-0 mt-1 small">' +
                errList.map(e => `<li>${escapeHtml(String(e))}</li>`).join('') +
                '</ul>';
        }
    }

    el.errorAlertContainer.innerHTML = `
        <div class="alert alert-danger alert-dismissible py-2 px-3 mb-0 shadow-sm" role="alert" style="font-size:12px;">
            <i class="bi bi-exclamation-circle-fill me-1"></i>
            ${html}
            <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
        </div>`;

    // Auto-dismiss after 6 seconds
    setTimeout(() => {
        const alert = el.errorAlertContainer.querySelector('.alert');
        if (alert) alert.remove();
    }, 6000);
}

/**
 * Show the success Bootstrap Toast with transaction details.
 */
function showSuccessToast(data) {
    el.toastInvoice.textContent   = data.nomor_invoice ?? '—';
    el.toastTotal.textContent     = formatRupiah(data.total_bayar ?? 0);
    el.toastKembalian.textContent = formatRupiah(data.kembalian ?? 0);
    el.toastItems.textContent     = data.jumlah_item ?? 0;

    const bsToast = new bootstrap.Toast(el.successToast, { delay: 6000 });
    bsToast.show();
}


// ═══════════════════════════════════════════════════════════════════════════
// SECTION 5: CUSTOMER SEARCH MODULE
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Fetch customer search results from the API.
 * Results are stored in state.pelangganResults and rendered.
 */
async function fetchPelanggan(query) {
    try {
        const data = await apiFetch(`${API.searchPelanggan}?q=${encodeURIComponent(query)}`);
        if (data.success) {
            state.pelangganResults = data.data ?? [];
            state.pelangganIndex   = -1;
            renderPelangganDropdown();
        }
    } catch (e) {
        console.error('[POS] Customer search error:', e);
    }
}

/**
 * Render the customer search results into the dropdown.
 * Highlights the currently active row (via state.pelangganIndex).
 */
function renderPelangganDropdown() {
    const results = state.pelangganResults;

    if (results.length === 0) {
        el.customerDropdown.classList.add('d-none');
        return;
    }

    el.customerDropdown.innerHTML = results.map((p, i) => {
        const isActive   = i === state.pelangganIndex;
        const tierClass  = TIER_BADGE_CLASS[p.status_pelanggan] ?? 'badge-tier-umum';

        return `
            <button type="button"
                    class="dropdown-item-pos ${isActive ? 'active' : ''}"
                    data-idx="${i}"
                    onmousedown="selectPelanggan(${i})">
                <div class="d-flex w-100 align-items-center justify-content-between">
                    <div>
                        <span class="fw-semibold">${escapeHtml(p.nama_pelanggan)}</span>
                        <span class="text-muted ms-1" style="font-size:11px;">[${escapeHtml(p.kode_pelanggan)}]</span>
                    </div>
                    <span class="badge ${tierClass}" style="font-size:9px;">${escapeHtml(p.status_pelanggan)}</span>
                </div>
            </button>`;
    }).join('');

    el.customerDropdown.classList.remove('d-none');
}

/**
 * Select a customer from the dropdown results by index.
 * Updates state and locks the customer tier.
 */
function selectPelanggan(index) {
    const p = state.pelangganResults[index];
    if (!p) return;

    state.customer = {
        id    : p.id,
        nama  : p.nama_pelanggan,
        status: p.status_pelanggan,
    };

    lockCustomer();
}

/**
 * Lock the customer selection and update the UI accordingly.
 * - Updates the tier badge
 * - Shows the lock icon
 * - Moves focus to the product search input
 */
function lockCustomer() {
    state.customerLocked = true;

    el.customerInput.value = state.customer.nama;
    el.customerDropdown.classList.add('d-none');
    state.pelangganResults = [];
    state.pelangganIndex   = -1;

    // Update lock icon
    el.customerLockIcon.className = 'bi bi-lock-fill customer-locked-icon';

    // Update tier badge
    const status    = state.customer.status;
    const tierClass = TIER_BADGE_CLASS[status] ?? 'badge-tier-umum';
    el.customerTierBadge.className   = `badge ${tierClass} price-mode-badge`;
    el.customerTierBadge.textContent = `${status} Mode`;

    // Move focus to product search
    el.productInput.focus();
}

/**
 * Unlock the customer (called when the input is edited again).
 */
function unlockCustomer() {
    state.customerLocked = false;
    el.customerLockIcon.className = 'bi bi-unlock customer-unlocked-icon';
}

// Debounced customer search — 250ms delay
const debouncedPelangganSearch = debounce(fetchPelanggan, 250);


// ═══════════════════════════════════════════════════════════════════════════
// SECTION 6: PRODUCT SEARCH MODULE
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Fetch product search results from the API.
 * Requires minimum 2 characters to trigger (per PRD spec).
 * Results are stored in state.searchResults and rendered.
 */
async function fetchProduk(query) {
    if (query.length < 2) {
        closeProductDropdown();
        return;
    }

    try {
        const data = await apiFetch(`${API.searchProduk}?q=${encodeURIComponent(query)}`);
        if (data.success) {
            state.searchResults       = data.data ?? [];
            state.activeResultIndex   = -1;
            renderProductDropdown();
        }
    } catch (e) {
        console.error('[POS] Product search error:', e);
    }
}

/**
 * Render the product search dropdown.
 * Shows price for the current customer tier, stock badge, and out-of-stock warning.
 * Results are ordered by the API (exact barcode first).
 */
function renderProductDropdown() {
    const results   = state.searchResults;

    if (results.length === 0) {
        closeProductDropdown();
        return;
    }

    const status    = state.customer.status;
    const priceCol  = PRICE_TIER_MAP[status] ?? 'harga_jual_umum';

    el.productDropdown.innerHTML = results.map((p, i) => {
        const harga        = parseFloat(p[priceCol]) || 0;
        const isActive     = i === state.activeResultIndex;
        const stokZero     = p.stok <= 0;
        const stokRendah   = p.is_stok_rendah && !stokZero;
        const stokBadgeCls = stokZero ? 'bg-danger' : (stokRendah ? 'bg-warning text-dark' : 'bg-success');
        const stokLabel    = stokZero ? 'Habis' : `${p.stok}`;
        const hoverBg      = isActive ? 'background-color:#EBF3F5;' : '';

        return `
            <li class="list-group-item py-2 px-3"
                data-idx="${i}"
                style="background-color:#ffffff; ${hoverBg} ${stokZero ? 'opacity:0.5; cursor:not-allowed;' : 'cursor:pointer;'}"
                ${!stokZero ? `onmousedown="selectProduct(${i})"` : ''}
                onmouseover="this.style.backgroundColor='#EBF3F5'"
                onmouseout="this.style.backgroundColor='#ffffff'">
                <div class="d-flex w-100 align-items-center justify-content-between gap-2">
                    <div style="min-width:0;">
                        <span class="text-muted" style="font-size:10px;">${escapeHtml(p.barcode)}</span>
                        <span class="fw-semibold ms-1">${escapeHtml(p.nama_produk)}</span>
                        ${stokZero ? '<span class="badge bg-danger ms-1" style="font-size:9px;">Stok Habis</span>' : ''}
                        ${stokRendah ? '<span class="badge bg-warning text-dark ms-1" style="font-size:9px;">Stok Rendah</span>' : ''}
                    </div>
                    <div class="text-end flex-shrink-0">
                        <span class="fw-bold" style="color:var(--pb-darker); font-size:12px;">${formatRupiah(harga)}</span>
                        <span class="badge ${stokBadgeCls} ms-1" style="font-size:9px;">${stokLabel}</span>
                    </div>
                </div>
            </li>`;
    }).join('');

    el.productDropdown.classList.remove('d-none');
}

/**
 * Select a product from the search results by index.
 * - Updates the price badge with the correct tier price
 * - Moves focus to the Qty input and selects its content
 * - Resets any existing stock warnings
 */
function selectProduct(index) {
    // If called without argument (keyboard Enter), use active index or default to 0
    if (index === undefined || index === null) {
        index = state.activeResultIndex >= 0 ? state.activeResultIndex : 0;
    }

    const product = state.searchResults[index];
    if (!product) return;
    if (product.stok <= 0) return; // Cannot select out-of-stock products

    // Lock selected product into state
    state.selectedProduct     = product;
    state.activeResultIndex   = index;

    // Determine price based on current customer tier
    const status   = state.customer.status;
    const priceCol = PRICE_TIER_MAP[status] ?? 'harga_jual_umum';
    const harga    = parseFloat(product[priceCol]) || 0;

    // Update price badge
    el.productPriceBadge.textContent = `${status}: ${formatRupiah(harga)}`;
    el.productPriceBadge.classList.remove('d-none');

    // Set product name in input and close dropdown
    el.productInput.value = product.nama_produk;
    closeProductDropdown();

    // Reset qty and move focus — select() highlights the default "1"
    // so the kasir can immediately type the new quantity
    el.qtyInput.value = 1;
    el.qtyInput.focus();
    el.qtyInput.select();

    // Clear any lingering stock warnings from a previous selection
    resetStockWarning();
}

/**
 * Close and clear the product search dropdown.
 */
function closeProductDropdown() {
    el.productDropdown.classList.add('d-none');
    state.searchResults      = [];
    state.activeResultIndex  = -1;
}

// Debounced product search — 250ms delay
const debouncedProductSearch = debounce(fetchProduk, 250);


// ═══════════════════════════════════════════════════════════════════════════
// SECTION 7: STOCK VALIDATION MODULE
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Real-time stock validation triggered on every 'input' event on qtyInput.
 *
 * Visual rules (PRD spec — NO alert() or sound):
 *   - Over-stock: input background → pastel red, warning text shown, Enter disabled
 *   - Qty ≤ 0:    input background → pastel red, warning text shown, Enter disabled
 *   - Valid qty:  all warnings cleared instantly, Enter re-enabled
 */
function validateStock() {
    if (!state.selectedProduct) {
        resetStockWarning();
        return;
    }

    const qty       = parseInt(el.qtyInput.value) || 0;
    const available = state.selectedProduct.stok;
    const satuan    = state.selectedProduct.satuan || 'Pcs';

    if (qty > available) {
        state.isStockValid                = false;
        el.qtyInput.style.backgroundColor = '#FFE0E0';
        el.qtyInput.style.borderColor     = '#FF8080';
        el.qtyWarning.innerHTML           = `<i class="bi bi-exclamation-triangle-fill"></i> Stok tidak mencukupi! Sisa: <strong>${available} ${satuan}</strong>`;
        el.qtyWarning.classList.remove('d-none');
        el.addBtn.disabled                = true;
    } else if (qty <= 0) {
        state.isStockValid                = false;
        el.qtyInput.style.backgroundColor = '#FFE0E0';
        el.qtyInput.style.borderColor     = '#FF8080';
        el.qtyWarning.innerHTML           = `<i class="bi bi-exclamation-triangle-fill"></i> Jumlah harus minimal 1.`;
        el.qtyWarning.classList.remove('d-none');
        el.addBtn.disabled                = true;
    } else {
        // Valid qty — clear all warnings immediately
        resetStockWarning();
    }
}

/**
 * Reset all stock warning UI elements to their default state.
 */
function resetStockWarning() {
    state.isStockValid                = true;
    el.qtyInput.style.backgroundColor = '';
    el.qtyInput.style.borderColor     = '';
    el.qtyWarning.innerHTML           = '';
    el.qtyWarning.classList.add('d-none');
    el.addBtn.disabled                = false;
}


// ═══════════════════════════════════════════════════════════════════════════
// SECTION 8: CART MANAGEMENT MODULE
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Append the currently selected product + qty to the cart.
 *
 * If the same product (same produk_id) already exists in the cart,
 * its quantity is incremented rather than adding a duplicate row —
 * provided the combined qty doesn't exceed available stock.
 *
 * After appending:
 *   - Grand total is recalculated
 *   - Cart table is re-rendered
 *   - Product search input is cleared
 *   - Focus returns to product search (the "Append & Reset Loop")
 */
function appendToCart() {
    if (!state.selectedProduct || !state.isStockValid) return;

    const qty = parseInt(el.qtyInput.value) || 1;
    if (qty <= 0) return;

    const product  = state.selectedProduct;
    const status   = state.customer.status;
    const priceCol = PRICE_TIER_MAP[status] ?? 'harga_jual_umum';
    const harga    = parseFloat(product[priceCol]) || 0;

    // Check if this product is already in the cart
    const existingIndex = state.cart.findIndex(item => item.produk_id === product.id);

    if (existingIndex >= 0) {
        // ── Merge: update qty + subtotal of existing row ────────────────
        const existing  = state.cart[existingIndex];
        const mergedQty = existing.qty + qty;

        if (mergedQty > product.stok) {
            // Merged qty would exceed stock
            el.qtyWarning.innerHTML = `<i class="bi bi-exclamation-triangle-fill"></i> Total qty melebihi stok! Maks. tambah: <strong>${product.stok - existing.qty} ${product.satuan || 'Pcs'}</strong>`;
            el.qtyWarning.classList.remove('d-none');
            el.qtyInput.style.backgroundColor = '#FFE0E0';
            el.qtyInput.style.borderColor     = '#FF8080';
            return;
        }

        existing.qty      = mergedQty;
        existing.subtotal = harga * mergedQty;

    } else {
        // ── Insert: add a new cart row ──────────────────────────────────
        state.cart.push({
            rowId       : state.nextRowId++,
            produk_id   : product.id,
            barcode     : product.barcode,
            nama_produk : product.nama_produk,
            satuan      : product.satuan || 'Pcs',
            tier        : status,
            harga_satuan: harga,
            qty         : qty,
            subtotal    : harga * qty,
            stok        : product.stok,
        });
    }

    renderCart();
    recalculateTotals();
    resetAfterAppend();
}

/**
 * Render the full cart table body from state.cart.
 * Shows an empty-state row when the cart is empty.
 */
function renderCart() {
    if (state.cart.length === 0) {
        el.cartBody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center text-muted py-4">
                    <i class="bi bi-cart3 me-1" style="font-size:16px;"></i><br>
                    <span style="font-size:12px;">Keranjang kosong. Scan barcode atau ketik nama produk di atas.</span>
                </td>
            </tr>`;
        el.cartCount.textContent = '0';
        return;
    }

    el.cartBody.innerHTML = state.cart.map((item, i) => {
        const tierClass = TIER_BADGE_CLASS[item.tier] ?? 'badge-tier-umum';
        return `
            <tr id="cartRow-${item.rowId}">
                <td class="text-center text-muted">${i + 1}</td>
                <td class="text-muted font-monospace" style="font-size:11px;">${escapeHtml(item.barcode)}</td>
                <td class="fw-semibold">${escapeHtml(item.nama_produk)}</td>
                <td class="text-center">
                    <span class="badge ${tierClass}" style="font-size:9px;">${escapeHtml(item.tier)}</span>
                </td>
                <td class="text-end">${formatRupiah(item.harga_satuan)}</td>
                <td>
                    <div class="input-group input-group-sm" style="width:96px; margin:auto;">
                        <button class="btn btn-outline-secondary btn-sm"
                                style="padding:1px 6px; font-size:13px;"
                                onclick="changeQtyInCart(${item.rowId}, -1)">−</button>
                        <input type="number"
                               class="form-control form-control-sm text-center cart-qty-input"
                               value="${item.qty}"
                               min="1"
                               max="${item.stok}"
                               style="padding:2px 3px; font-size:12px;"
                               onchange="updateQtyInCart(${item.rowId}, this.value)"
                               oninput="validateCartQty(${item.rowId}, this)">
                        <button class="btn btn-outline-secondary btn-sm"
                                style="padding:1px 6px; font-size:13px;"
                                onclick="changeQtyInCart(${item.rowId}, 1)">+</button>
                    </div>
                </td>
                <td class="text-end fw-bold" style="color:var(--pb-text);">${formatRupiah(item.subtotal)}</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-outline-danger"
                            style="padding:1px 6px; font-size:11px; border-radius:4px;"
                            onclick="removeFromCart(${item.rowId})"
                            title="Hapus item ini">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </td>
            </tr>`;
    }).join('');

    el.cartCount.textContent = state.cart.length;
}

/**
 * Increment or decrement a cart item's qty by delta (+1 or -1).
 * Enforces min=1 and max=stok boundaries.
 */
function changeQtyInCart(rowId, delta) {
    const item = state.cart.find(i => i.rowId === rowId);
    if (!item) return;

    const newQty = item.qty + delta;
    if (newQty < 1 || newQty > item.stok) return;

    item.qty      = newQty;
    item.subtotal = item.harga_satuan * newQty;

    renderCart();
    recalculateTotals();
}

/**
 * Directly update a cart item's qty from the inline input field.
 * Clamps to the valid range [1, stok].
 */
function updateQtyInCart(rowId, value) {
    const item   = state.cart.find(i => i.rowId === rowId);
    if (!item) return;

    let newQty = parseInt(value) || 1;
    newQty     = Math.max(1, Math.min(newQty, item.stok));

    item.qty      = newQty;
    item.subtotal = item.harga_satuan * newQty;

    renderCart();
    recalculateTotals();
}

/**
 * Visual validation for inline qty input cells in the cart table.
 * Shows red border if qty exceeds stok.
 */
function validateCartQty(rowId, inputEl) {
    const item = state.cart.find(i => i.rowId === rowId);
    if (!item) return;

    const qty = parseInt(inputEl.value) || 0;
    if (qty > item.stok || qty < 1) {
        inputEl.style.borderColor     = '#FF8080';
        inputEl.style.backgroundColor = '#FFE0E0';
    } else {
        inputEl.style.borderColor     = '';
        inputEl.style.backgroundColor = '';
    }
}

/**
 * Remove a cart item by its rowId.
 */
function removeFromCart(rowId) {
    state.cart = state.cart.filter(i => i.rowId !== rowId);
    renderCart();
    recalculateTotals();
}

/**
 * Clear the product search and reset focus after appending an item.
 * This is the "Append & Reset Loop" per PRD spec.
 */
function resetAfterAppend() {
    el.productInput.value = '';
    el.productPriceBadge.classList.add('d-none');
    el.qtyInput.value = 1;

    state.selectedProduct = null;
    resetStockWarning();
    closeProductDropdown();

    // Force focus back to product search — kasir is immediately ready
    // to type/scan the next product without touching the mouse
    el.productInput.focus();
}


// ═══════════════════════════════════════════════════════════════════════════
// SECTION 9: PAYMENT & TOTALS MODULE
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Recalculate grandTotal, totalBayar, and update all payment displays.
 * Called after every cart mutation and discount change.
 */
function recalculateTotals() {
    state.grandTotal = state.cart.reduce((sum, item) => sum + item.subtotal, 0);
    state.diskon     = Math.max(0, parseFloat(el.diskonInput.value) || 0);
    state.totalBayar = Math.max(0, state.grandTotal - state.diskon);

    el.grandTotalDisplay.textContent = formatRupiah(state.grandTotal);
    el.totalBayarDisplay.textContent = formatRupiah(state.totalBayar);

    calculateKembalian();
    validatePayment();
}

/**
 * Compute and display the kembalian (change) for Tunai payments.
 */
function calculateKembalian() {
    if (state.metodePembayaran !== 'Tunai') {
        el.kembalianDisplay.textContent = formatRupiah(0);
        el.kembalianDisplay.style.color = '';
        return;
    }

    const nominal   = parseFloat(el.nominalUang.value) || 0;
    const kembalian = nominal - state.totalBayar;

    el.kembalianDisplay.textContent = formatRupiah(Math.max(0, kembalian));
    el.kembalianDisplay.style.color = kembalian < 0 ? '#C0392B' : 'var(--success-col)';
}

/**
 * Calculate Sisa Piutang for Kredit.
 */
function calculateSisaPiutang() {
    if (!el.uangMukaInput || !el.sisaPiutangDisplay) return;
    const dp = parseFloat(el.uangMukaInput.value) || 0;
    const sisa = Math.max(0, state.totalBayar - dp);
    el.sisaPiutangDisplay.textContent = formatRupiah(sisa);
    if (el.kreditStatusBadge) {
        if (sisa <= 0 && state.totalBayar > 0) {
            el.kreditStatusBadge.textContent = 'Lunas (DP Penuh)';
            el.kreditStatusBadge.style.background = 'rgba(21,128,61,0.15)';
            el.kreditStatusBadge.style.color = '#15803D';
        } else {
            el.kreditStatusBadge.textContent = 'Belum Lunas';
            el.kreditStatusBadge.style.background = 'rgba(217,119,6,0.15)';
            el.kreditStatusBadge.style.color = '#B45309';
        }
    }
}

/**
 * Switch the active payment method and update the UI accordingly.
 * Shows/hides relevant input panels (Nominal Uang, Referensi Digital, Kredit Info).
 *
 * @param {string} metode  'Tunai' | 'Kredit' | 'Digital Payment'
 */
function setMetodePembayaran(metode) {
    state.metodePembayaran = metode;

    // Update active button highlight
    ['Tunai', 'Kredit', 'Digital Payment'].forEach(m => {
        const btnId = 'btn' + m.replace(' ', '');
        const btn   = document.getElementById(btnId);
        if (btn) btn.classList.toggle('active', m === metode);
    });

    // Toggle context-specific panels
    el.nominalUangRow.classList.toggle('d-none', metode !== 'Tunai');
    el.kembalianRow.classList.toggle('d-none',   metode !== 'Tunai');
    el.refDigitalRow.classList.toggle('d-none',  metode !== 'Digital Payment');
    el.kreditInfoRow.classList.toggle('d-none',  metode !== 'Kredit');

    // Reset nominal uang styling on method change
    el.nominalUang.style.borderColor     = '';
    el.nominalUang.style.backgroundColor = '';

    calculateKembalian();
    calculateSisaPiutang();
    validatePayment();
}

/**
 * Validate payment readiness and enable/disable the checkout button.
 *
 * Rules:
 *   - Cart must have at least 1 item.
 *   - If Tunai: nominalUang must be >= totalBayar.
 *   - Kredit & Digital: no amount validation (credit is allowed; digital is administrative).
 */
function validatePayment() {
    const hasItems = state.cart.length > 0;

    if (!hasItems) {
        el.checkoutBtn.disabled = true;
        return;
    }

    if (state.metodePembayaran === 'Tunai') {
        const nominal = parseFloat(el.nominalUang.value) || 0;

        if (nominal < state.totalBayar) {
            // Insufficient cash: disable button + pastel red border on nominal input
            el.checkoutBtn.disabled               = true;
            el.nominalUang.style.borderColor      = '#FF8080';
            el.nominalUang.style.backgroundColor  = '#FFF0F0';
        } else {
            // Sufficient cash: enable button + reset nominal input styling
            el.checkoutBtn.disabled               = false;
            el.nominalUang.style.borderColor      = '';
            el.nominalUang.style.backgroundColor  = '';
        }
    } else {
        // Kredit or Digital Payment: always enable (no cash amount to validate)
        el.checkoutBtn.disabled = false;
    }
}


// ═══════════════════════════════════════════════════════════════════════════
// SECTION 10: CHECKOUT MODULE
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Process the POS checkout transaction.
 *
 * Sends the cart to PenjualanController@store via POST.
 * The backend derives prices from the DB — we only send product_id and qty.
 * Handles success (toast, reset) and all error cases (422 validation, 500 server).
 */
async function processCheckout() {
    // ── Client-Side Pre-Validation (Protective Guardrail) ────────────────
    if (state.cart.length === 0) {
        showErrorAlert('Keranjang belanja kosong!', {
            cart: ['Silakan tambahkan produk ke keranjang sebelum melakukan checkout.']
        });
        return;
    }

    if (el.checkoutBtn.disabled) return;

    const nominalUang = parseFloat(el.nominalUang.value) || 0;
    if (state.metodePembayaran === 'Tunai' && nominalUang < state.totalBayar) {
        // Strictly block execution if cash is insufficient
        return;
    }

    // ── Set loading state ────────────────────────────────────────────────
    el.checkoutBtn.disabled = true;
    el.checkoutSpinner.classList.remove('d-none');
    el.checkoutBtnText.innerHTML = 'Memproses…';

    // ── Payload Construction ─────────────────────────────────────────────
    // Only send product_id + qty. Prices are derived server-side from the
    // DB based on the customer's tier — anti-inspect-element protection.
    const uangMukaVal = (state.metodePembayaran === 'Kredit' && el.uangMukaInput)
        ? (parseFloat(el.uangMukaInput.value) || 0)
        : nominalUang;
    const jatuhTempoVal = (state.metodePembayaran === 'Kredit' && el.jatuhTempoInput)
        ? (el.jatuhTempoInput.value || null)
        : null;

    const payload = {
        pelanggan_id        : state.customer.id,       // null if 'Umum'
        metode_pembayaran   : state.metodePembayaran,
        diskon              : state.diskon,
        nominal_uang        : nominalUang,
        uang_muka           : uangMukaVal,
        tanggal_jatuh_tempo : jatuhTempoVal,
        referensi_digital   : el.refDigitalInput.value.trim() || null,
        items               : state.cart.map(item => ({
            product_id : item.produk_id,
            qty        : item.qty,
        })),
    };

    try {
        // ── AJAX Request via fetch ──────────────────────────────────────
        const response = await fetch("{{ route('penjualan.store') }}", {
            method : 'POST',
            headers: {
                'X-CSRF-TOKEN' : CSRF,
                'Content-Type' : 'application/json',
                'Accept'       : 'application/json',
            },
            body: JSON.stringify(payload),
        });

        const rawText = await response.text();
        let data = {};
        try {
            data = JSON.parse(rawText);
        } catch (jsonErr) {
            console.error('[POS Custom] Non-JSON checkout response:', rawText);
            showErrorAlert('Error Server (' + response.status + '): Respon server tidak valid.');
            return;
        }

        if (response.ok && data.success) {
            // ── Success Response Lifecycle ───────────────────────────────

            // Update invoice display in top bar
            el.invoiceDisplay.textContent = data.data.nomor_invoice ?? '—';

            // Show success toast with transaction summary
            showSuccessToast(data.data);

            // Thermal Receipt Integration — open print layout in new tab
            if (data.data.penjualan_id) {
                window.open("{{ url('/pos/print-struk') }}/" + data.data.penjualan_id, '_blank');
            }

            // ── Full POS Reset ───────────────────────────────────────────
            // Reset customer back to Umum walk-in
            state.customer = { id: null, nama: 'Umum', status: 'Umum' };
            state.customerLocked = false;
            el.customerInput.value = '';
            el.customerLockIcon.className = 'bi bi-unlock customer-unlocked-icon';
            el.customerTierBadge.className = 'badge badge-tier-umum price-mode-badge';
            el.customerTierBadge.textContent = 'Umum Mode';

            // Reset payment inputs
            el.nominalUang.value = '';
            el.refDigitalInput.value = '';
            el.diskonInput.value = 0;

            // Reset cart and totals using existing POS reset function
            resetPOS();

            // Return cursor focus to customer input for next transaction
            el.customerInput.focus();

        } else {
            // ── Validation / Business Rule Error ─────────────────────────
            showErrorAlert(data.message || 'Transaksi gagal.', data.errors || null);
        }

    } catch (networkError) {
        console.error('[POS Custom] Checkout network error:', networkError);
        showErrorAlert('Terjadi kesalahan koneksi. Periksa koneksi dan coba lagi.');

    } finally {
        // ── Restore button state ────────────────────────────────────────
        el.checkoutBtn.disabled = false;
        el.checkoutSpinner.classList.add('d-none');
        el.checkoutBtnText.innerHTML = `
            <i class="bi bi-lightning-charge-fill me-1"></i>Proses Transaksi
            <span class="key-hint ms-2" style="color:rgba(255,255,255,0.7); background:rgba(255,255,255,0.15); border-color:rgba(255,255,255,0.3);">F9</span>`;
        validatePayment();
    }
}


// ═══════════════════════════════════════════════════════════════════════════
// SECTION 11: POS RESET MODULE
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Full POS reset after a successful transaction.
 * Clears the cart, resets all inputs, and moves focus to product search
 * so the kasir is immediately ready for the next customer.
 * The customer state is preserved (same tier for quick back-to-back sales).
 */
function resetPOS() {
    // Clear cart data
    state.cart        = [];
    state.nextRowId   = 1;
    state.grandTotal  = 0;
    state.diskon      = 0;
    state.totalBayar  = 0;

    // Clear product search state
    state.selectedProduct   = null;
    state.searchResults     = [];
    state.activeResultIndex = -1;
    state.isStockValid      = true;

    // Reset Quick Entry Bar inputs
    el.productInput.value = '';
    el.productPriceBadge.classList.add('d-none');
    el.qtyInput.value = 1;

    // Reset payment inputs
    el.diskonInput.value   = 0;
    el.nominalUang.value   = '';
    el.refDigitalInput.value = '';
    el.nominalUang.style.borderColor     = '';
    el.nominalUang.style.backgroundColor = '';

    // Reset stock warning
    resetStockWarning();
    closeProductDropdown();

    // Re-render empty cart and update totals
    renderCart();
    recalculateTotals();

    // Focus product input — ready for next customer immediately
    el.productInput.focus();
}


// ═══════════════════════════════════════════════════════════════════════════
// SECTION 12: EVENT LISTENERS
// ═══════════════════════════════════════════════════════════════════════════

// ─── Customer Input ─────────────────────────────────────────────────────

el.customerInput.addEventListener('input', function () {
    unlockCustomer();
    debouncedPelangganSearch(this.value.trim());
});

el.customerInput.addEventListener('keydown', function (e) {
    const total = state.pelangganResults.length;

    switch (e.key) {
        case 'ArrowDown':
            e.preventDefault();
            state.pelangganIndex = Math.min(state.pelangganIndex + 1, total - 1);
            renderPelangganDropdown();
            break;

        case 'ArrowUp':
            e.preventDefault();
            state.pelangganIndex = Math.max(state.pelangganIndex - 1, -1);
            renderPelangganDropdown();
            break;

        case 'Enter':
            e.preventDefault();
            if (state.pelangganIndex >= 0 && total > 0) {
                // Select the highlighted dropdown item
                selectPelanggan(state.pelangganIndex);
            } else if (total > 0) {
                // Auto-select the first result if none highlighted
                selectPelanggan(0);
            } else {
                // No results or empty query → default to Umum walk-in
                state.customer = { id: null, nama: 'Umum', status: 'Umum' };
                lockCustomer();
            }
            break;

        case 'Escape':
            el.customerDropdown.classList.add('d-none');
            state.pelangganResults = [];
            state.pelangganIndex   = -1;
            break;
    }
});

// ─── Product Search Input ────────────────────────────────────────────────

el.productInput.addEventListener('input', function () {
    state.selectedProduct = null;
    el.productPriceBadge.classList.add('d-none');
    
    const query = this.value.trim();
    if (query.length < 2) {
        closeProductDropdown();
    } else {
        fetchProduk(query); // Immediate trigger
    }
});

el.productInput.addEventListener('keydown', function (e) {
    const total = state.searchResults.length;

    switch (e.key) {
        case 'ArrowDown':
            e.preventDefault();
            if (total > 0) {
                state.activeResultIndex = Math.min(state.activeResultIndex + 1, total - 1);
                renderProductDropdown();
            }
            break;

        case 'ArrowUp':
            e.preventDefault();
            state.activeResultIndex = Math.max(state.activeResultIndex - 1, 0);
            renderProductDropdown();
            break;

        case 'Enter':
            e.preventDefault();
            if (total > 0) {
                // Select highlighted item, or default to first result
                const idx = state.activeResultIndex >= 0 ? state.activeResultIndex : 0;
                selectProduct(idx);
            }
            break;

        case 'Escape':
            closeProductDropdown();
            break;
    }
});

// ─── Quantity Input ──────────────────────────────────────────────────────

el.qtyInput.addEventListener('input', validateStock);

el.qtyInput.addEventListener('keydown', function (e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        // Only append if stock is valid and a product is selected
        if (state.selectedProduct && state.isStockValid) {
            appendToCart();
        }
    }
});

// Select all content when qty input gains focus
el.qtyInput.addEventListener('focus', function () {
    this.select();
});

// ─── Add Button ──────────────────────────────────────────────────────────

el.addBtn.addEventListener('click', appendToCart);

// ─── Diskon Input ────────────────────────────────────────────────────────

el.diskonInput.addEventListener('input', recalculateTotals);

// ─── Nominal Uang & Kredit DP Input ─────────────────────────────────────

el.nominalUang.addEventListener('input', function () {
    calculateKembalian();
    validatePayment();
});

if (el.uangMukaInput) {
    el.uangMukaInput.addEventListener('input', function () {
        calculateSisaPiutang();
    });
}

el.nominalUang.addEventListener('keydown', function (e) {
    // Allow kasir to press Enter on nominal uang to process immediately
    if (e.key === 'Enter') {
        e.preventDefault();
        processCheckout();
    }
});

el.nominalUang.addEventListener('focus', function () {
    this.select();
});

// ─── Checkout Button ─────────────────────────────────────────────────────

el.checkoutBtn.addEventListener('click', processCheckout);

// ─── Click Outside: Close Dropdowns ─────────────────────────────────────

document.addEventListener('click', function (e) {
    // Close customer dropdown if click is outside customer area
    const inCustomerArea = el.customerInput.contains(e.target) ||
                           el.customerDropdown.contains(e.target);
    if (!inCustomerArea) {
        el.customerDropdown.classList.add('d-none');
    }

    // Close product dropdown if click is outside product search area
    const inProductArea = el.productInput.contains(e.target) ||
                          el.productDropdown.contains(e.target);
    if (!inProductArea) {
        closeProductDropdown();
    }
});


// ═══════════════════════════════════════════════════════════════════════════
// SECTION 13: GLOBAL KEYBOARD SHORTCUTS
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Global keyboard shortcut handler.
 *
 * F8  → Force focus to Product Search Input (from anywhere on the page)
 * F9  → Open payment panel / focus Nominal Uang or trigger checkout
 *
 * Prevents default browser behavior for these keys.
 */
document.addEventListener('keydown', function (e) {
    switch (e.key) {
        case 'F8':
            // ── F8: Jump to Product Search ─────────────────────────────
            e.preventDefault();
            el.productInput.focus();
            el.productInput.select();
            break;

        case 'F9':
            // ── F9: Open payment / focus Nominal Uang ──────────────────
            e.preventDefault();
            if (state.cart.length === 0) return;

            if (state.metodePembayaran === 'Tunai') {
                // For cash: focus nominal uang input for quick amount entry
                el.nominalUang.focus();
                el.nominalUang.select();
            } else {
                // For Kredit / Digital: trigger checkout directly
                if (!el.checkoutBtn.disabled) {
                    processCheckout();
                }
            }
            break;
    }
});


// ═══════════════════════════════════════════════════════════════════════════
// SECTION 14: INITIALIZATION
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Initialize the POS page on DOMContentLoaded.
 *
*   1. Set today's date in the top bar
 *   2. Render the initial empty cart
 *   3. Set default payment method to Tunai
 *   4. Autofocus the Customer Input field
 */
// ═══════════════════════════════════════════════════════════════════════════
// SECTION: HOLD, CANCEL, RECALL LOGIC
// ═══════════════════════════════════════════════════════════════════════════

function initHoldCount() {
    const holds = JSON.parse(localStorage.getItem('pos_held_transactions')) || [];
    const holdCountEl = document.getElementById('hold-count');
    if (holdCountEl) holdCountEl.textContent = holds.length;
}

function cancelTransaction() {
    if (state.cart.length === 0 && state.customer.nama === 'Umum') return;
    
    if (!confirm('Apakah Anda yakin ingin membatalkan transaksi ini?')) return;
    
    // Clear cart
    state.cart = [];
    state.nextRowId = 1;
    
    // Reset customer
    const select = el.customerInput;
    if (select) {
        select.value = '';
        state.customer = { id: null, nama: 'Umum', status: 'Umum' };
        
        // Update UI
        el.customerLockIcon.className = 'bi bi-unlock customer-unlocked-icon';
        el.customerTierBadge.className = 'badge badge-tier-umum price-mode-badge';
        el.customerTierBadge.textContent = 'Umum Mode';
        state.customerLocked = false;
        select.disabled = false;
    }
    
    // Reset payment inputs
    if (document.getElementById('diskonInput')) document.getElementById('diskonInput').value = '0';
    if (el.nominalUang) el.nominalUang.value = '';
    if (document.getElementById('refDigitalInput')) document.getElementById('refDigitalInput').value = '';
    
    // Default method
    setMetodePembayaran('Tunai');
    
    renderCart();
    recalculateTotals();
}

function holdTransaction() {
    if (state.cart.length === 0) {
        alert('Keranjang belanja masih kosong!');
        return;
    }
    
    const timestampId = 'HOLD-' + new Date().getTime().toString().slice(-6);
    let note = prompt('Masukkan catatan/nama untuk transaksi ini (Opsional):', '');
    if (note === null) return; // User cancelled prompt
    if (note.trim() === '') note = timestampId;
    
    const holdData = {
        hold_id: timestampId,
        note: note,
        date: new Date().toLocaleString('id-ID'),
        customer: state.customer,
        cart: state.cart,
        diskon: document.getElementById('diskonInput') ? document.getElementById('diskonInput').value : 0
    };
    
    let holds = JSON.parse(localStorage.getItem('pos_held_transactions')) || [];
    holds.push(holdData);
    localStorage.setItem('pos_held_transactions', JSON.stringify(holds));
    
    initHoldCount();
    
    // Implicit cancel to clear UI
    state.cart = [];
    state.nextRowId = 1;
    const select = el.customerInput;
    if (select) {
        select.value = '';
        state.customer = { id: null, nama: 'Umum', status: 'Umum' };
        el.customerLockIcon.className = 'bi bi-unlock customer-unlocked-icon';
        el.customerTierBadge.className = 'badge badge-tier-umum price-mode-badge';
        el.customerTierBadge.textContent = 'Umum Mode';
        state.customerLocked = false;
        select.disabled = false;
    }
    
    if (document.getElementById('diskonInput')) document.getElementById('diskonInput').value = '0';
    if (el.nominalUang) el.nominalUang.value = '';
    if (document.getElementById('refDigitalInput')) document.getElementById('refDigitalInput').value = '';
    setMetodePembayaran('Tunai');
    
    renderCart();
    recalculateTotals();
}

let heldModalInstance = null;

function showHeldTransactions() {
    renderHeldModal();
    if (!heldModalInstance) {
        heldModalInstance = new bootstrap.Modal(document.getElementById('heldModal'));
    }
    heldModalInstance.show();
}

function renderHeldModal() {
    const holds = JSON.parse(localStorage.getItem('pos_held_transactions')) || [];
    const tbody = document.getElementById('heldTransactionsBody');
    
    if (holds.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-3">Tidak ada transaksi tertahan.</td></tr>';
        return;
    }
    
    let html = '';
    holds.forEach((h, idx) => {
        const totalItems = h.cart.reduce((sum, item) => sum + item.qty, 0);
        const totalNilai = h.cart.reduce((sum, item) => sum + item.subtotal, 0) - (parseFloat(h.diskon) || 0);
        
        const badgeClass = TIER_BADGE_CLASS[h.customer.status] || 'bg-secondary';
        
        html += `
            <tr>
                <td class="text-center align-middle">${idx + 1}</td>
                <td class="align-middle">
                    <div class="fw-bold text-dark">${escapeHtml(h.note)}</div>
                    <div class="text-muted" style="font-size:10px;">${h.date}</div>
                </td>
                <td class="text-center align-middle">
                    <span class="badge ${badgeClass}">${escapeHtml(h.customer.nama)}</span>
                </td>
                <td class="text-center align-middle">${totalItems} Pcs</td>
                <td class="text-end align-middle fw-bold">${formatRupiah(totalNilai)}</td>
                <td class="text-center align-middle">
                    <button class="btn btn-sm btn-primary py-0 px-2 me-1" style="font-size:11px;" onclick="recallTransaction('${h.hold_id}')">Lanjutkan</button>
                    <button class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size:11px;" onclick="deleteHeldTransaction('${h.hold_id}')"><i class="bi bi-trash"></i></button>
                </td>
            </tr>
        `;
    });
    tbody.innerHTML = html;
}

function recallTransaction(holdId) {
    let holds = JSON.parse(localStorage.getItem('pos_held_transactions')) || [];
    const holdData = holds.find(h => h.hold_id === holdId);
    if (!holdData) return;
    
    // If current cart is not empty, warn user
    if (state.cart.length > 0) {
        if (!confirm('Keranjang saat ini tidak kosong. Mengambil transaksi ini akan menimpa keranjang saat ini. Lanjutkan?')) {
            return;
        }
    }
    
    // Restore state
    state.cart = holdData.cart;
    // Fix rowIds to prevent conflicts if needed
    let maxId = 0;
    state.cart.forEach(item => { if(item.rowId > maxId) maxId = item.rowId; });
    state.nextRowId = maxId + 1;
    
    state.customer = holdData.customer;
    if (document.getElementById('diskonInput')) document.getElementById('diskonInput').value = holdData.diskon || 0;
    
    // Update UI for customer
    const select = el.customerInput;
    if (select) {
        select.value = state.customer.id ? state.customer.nama : '';
        if (state.customer.id) {
            el.customerLockIcon.className = 'bi bi-lock-fill customer-locked-icon';
            state.customerLocked = true;
            select.disabled = true;
        } else {
            el.customerLockIcon.className = 'bi bi-unlock customer-unlocked-icon';
            state.customerLocked = false;
            select.disabled = false;
        }
        
        el.customerTierBadge.className = `badge ${TIER_BADGE_CLASS[state.customer.status] || 'badge-tier-umum'} price-mode-badge`;
        el.customerTierBadge.textContent = state.customer.status + ' Mode';
    }
    
    renderCart();
    recalculateTotals();
    
    // Remove from holds
    deleteHeldTransaction(holdId, true);
    
    if (heldModalInstance) {
        heldModalInstance.hide();
    }
}

function deleteHeldTransaction(holdId, skipConfirm = false) {
    if (!skipConfirm && !confirm('Hapus transaksi tertahan ini?')) return;
    
    let holds = JSON.parse(localStorage.getItem('pos_held_transactions')) || [];
    holds = holds.filter(h => h.hold_id !== holdId);
    localStorage.setItem('pos_held_transactions', JSON.stringify(holds));
    
    initHoldCount();
    renderHeldModal();
}

function initPOS() {
    // Populate dropdowns
    renderPelangganDropdown();
    
    // Initial UI state
    el.invoiceDisplay.textContent = 'NEW';
    el.posDate.textContent = new Date().toLocaleDateString('id-ID');

    initHoldCount();

    // Set today's date in Indonesian locale (DD/MM/YYYY)
    const today = new Date().toLocaleDateString('id-ID', {
        day   : '2-digit',
        month : '2-digit',
        year  : 'numeric',
    });
    el.posDate.textContent = today;

    // Set invoice display to pending state
    el.invoiceDisplay.textContent = 'AUTO';

    // Render initial empty cart table
    renderCart();

    // Initialize totals to zero
    recalculateTotals();

    // Set Tunai as the default payment method and show the right panels
    setMetodePembayaran('Tunai');

    // Autofocus customer input on page load (PRD: "Page Load Focus")
    el.customerInput.focus();
    el.customerInput.select();

    // PRD: Customer input should show "Umum" as default hint
    // The user can press Enter immediately to lock Umum tier and go to product search
    el.customerInput.value = '';
    el.customerInput.placeholder = 'Ketik nama / kode, atau tekan Enter untuk Pelanggan Umum…';
}

// ── Theme Handler ────────────────────────────────────────────────────────────
function initPOSTheme() {
    const toggleBtn = document.getElementById('posDarkModeToggle');
    const icon = document.getElementById('posDarkModeIcon');
    
    function applyTheme(theme) {
        if (theme === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
            if (icon) icon.className = 'bi bi-sun-fill text-warning';
        } else {
            document.documentElement.removeAttribute('data-theme');
            if (icon) icon.className = 'bi bi-moon-stars';
        }
    }

    const savedTheme = localStorage.getItem('erp_theme') || 
        (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    applyTheme(savedTheme);

    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            const current = document.documentElement.getAttribute('data-theme');
            const newTheme = current === 'dark' ? 'light' : 'dark';
            localStorage.setItem('erp_theme', newTheme);
            applyTheme(newTheme);
        });
    }
}

// ── Voice Input POS Handler for Custom Mode ──
window.addEventListener('pos-voice-apply', function(e) {
    const data = e.detail;
    console.log('[POS Custom Voice] Event received. Data:', JSON.stringify(data));
    if (!data) return;

    if (data.items && data.items.length > 0) {
        data.items.forEach(function(item) {
            if (item.produk_id) {
                const pId = parseInt(item.produk_id, 10);
                const qty = parseInt(item.qty, 10) || 1;
                const product = (typeof ALL_PRODUCTS !== 'undefined') ? ALL_PRODUCTS.find(function(p) { return parseInt(p.id, 10) === pId; }) : null;
                if (product) {
                    const priceCol = (typeof PRICE_TIER_MAP !== 'undefined' && state.customer) ? (PRICE_TIER_MAP[state.customer.status] ?? 'harga_jual_umum') : 'harga_jual_umum';
                    const harga = parseFloat(product[priceCol]) || 0;
                    const existingIndex = state.cart.findIndex(function(i) { return parseInt(i.produk_id, 10) === pId; });
                    if (existingIndex >= 0) {
                        state.cart[existingIndex].qty += qty;
                        state.cart[existingIndex].subtotal = state.cart[existingIndex].harga_satuan * state.cart[existingIndex].qty;
                    } else {
                        state.cart.push({
                            rowId: state.nextRowId++,
                            produk_id: product.id,
                            barcode: product.barcode,
                            nama_produk: product.nama_produk,
                            satuan: product.satuan || 'Pcs',
                            tier: state.customer ? state.customer.status : 'Umum',
                            harga_satuan: harga,
                            qty: qty,
                            subtotal: harga * qty,
                            stok: product.stok,
                        });
                    }
                } else {
                    console.warn('[POS Custom Voice] Product ID', pId, 'not found.');
                }
            }
        });
        if (typeof renderCart === 'function') renderCart();
        if (typeof recalculateTotals === 'function') recalculateTotals();
    }

    if (data.metode_pembayaran && el.metodePembayaranSelect) {
        el.metodePembayaranSelect.value = data.metode_pembayaran;
        el.metodePembayaranSelect.dispatchEvent(new Event('change'));
    }

    if (data.nominal_bayar && data.nominal_bayar > 0) {
        const inputUang = el.nominalUang || document.getElementById('nominalUang');
        if (inputUang) {
            inputUang.value = data.nominal_bayar;
            inputUang.dispatchEvent(new Event('input'));
        }
    }
});

document.addEventListener('DOMContentLoaded', function() {
    initPOSTheme();
    initPOS();
});
</script>

@include('partials.voice-transaction-modal')

</body>
</html>
