<!DOCTYPE html>
{{--
    POS MODE 1: Standard (Touch-Friendly Product Browser)
    ──────────────────────────────────────────────────────
    Standalone Blade page — no layout extension required.
    This file includes Bootstrap 5, Bootstrap Icons, and all
    vanilla JavaScript logic inline.

    Routes required (register in routes/web.php):
        Route::get('/pos/standard', ...)->name('pos.standard');

    Backend endpoints used:
        POST /penjualan/store  → PenjualanController@store
        GET  /pos/print-struk/{id} → PenjualanController@printStruk
--}}
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>POS Mode Standar — {{ config('app.name', 'Retail ERP') }}</title>
    <meta name="description" content="POS Standar dengan tampilan grid produk touchscreen-friendly untuk transaksi penjualan retail cepat dan akurat.">

    {{-- Bootstrap 5 --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    {{-- Google Font: Plus Jakarta Sans & Inter --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        /* ══════════════════════════════════════════════════════════════════
           CSS CUSTOM PROPERTIES & DARK MODE SYSTEM
        ══════════════════════════════════════════════════════════════════ */
        :root {
            --pb-lightest  : #EBF3F5;
            --pb-light     : #D6E9F0;
            --pb-mid       : #A0C4DF;
            --pb-accent    : #5B9EC9;
            --pb-dark      : #0D4E56;
            --pb-darker    : #09373D;
            --pb-text      : #0D4E56;
            --warn-bg      : #FFF0F0;
            --warn-border  : #FF8080;
            --warn-text    : #C0392B;
            --success-col  : #198754;
            --body-bg      : #F4F7F9;
            --bg-card      : #FFFFFF;
            --text-primary : #1E293B;
            --text-secondary: #64748B;
            --text-muted   : #94A3B8;
            --border-light : #E2E8F0;
            --card-shadow  : 0 2px 8px rgba(13,78,86,0.08);
            --sidebar-w    : 40%;
            --product-w    : 60%;
        }

        [data-theme="dark"] {
            --pb-lightest  : #162428;
            --pb-light     : #1E3238;
            --pb-mid       : #2A464E;
            --pb-accent    : #4DB8C4;
            --pb-dark      : #4DB8C4;
            --pb-darker    : #389BA6;
            --pb-text      : #E2F1F3;
            --warn-bg      : rgba(220, 38, 38, 0.15);
            --warn-border  : rgba(220, 38, 38, 0.3);
            --warn-text    : #F87171;
            --success-col  : #34D399;
            --body-bg      : #0F171A;
            --bg-card      : #152226;
            --text-primary : #F1F5F9;
            --text-secondary: #94A3B8;
            --text-muted   : #64748B;
            --border-light : #24363D;
            --card-shadow  : 0 2px 8px rgba(0,0,0,0.3);
        }

        [data-theme="dark"] .product-card {
            background-color: #162428 !important;
            border-color: #283F46 !important;
        }

        [data-theme="dark"] .product-name {
            color: #FFFFFF !important;
        }

        [data-theme="dark"] .product-barcode {
            color: #94A3B8 !important;
        }

        [data-theme="dark"] .product-price {
            color: #4DB8C4 !important;
        }

        [data-theme="dark"] .product-stock {
            color: #94A3B8 !important;
        }

        [data-theme="dark"] .cart-item-name {
            color: #FFFFFF !important;
        }

        [data-theme="dark"] .modal-content {
            background-color: #152226 !important;
            color: #F1F5F9 !important;
            border-color: #24363D !important;
        }

        [data-theme="dark"] .form-control,
        [data-theme="dark"] .form-select {
            background-color: #0F171A !important;
            color: #F1F5F9 !important;
            border-color: #24363D !important;
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
            background-color: #0D4E56 !important;
            color: #FFFFFF !important;
            border: 1px solid #0D4E56 !important;
            font-weight: 700 !important;
        }

        .btn-pb:hover {
            background-color: #09373D !important;
            color: #FFFFFF !important;
        }

        [data-theme="dark"] .btn-pb {
            background-color: #145C65 !important;
            color: #FFFFFF !important;
            border: 1px solid #4DB8C4 !important;
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

        /* ── Base Reset ───────────────────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; }

        html, body {
            height: 100%;
            margin: 0;
            font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, sans-serif;
            font-size: 13px;
            background-color: var(--body-bg);
            color: var(--text-primary);
            overflow: hidden;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        /* ── Main Wrapper ─────────────────────────────────────────────── */
        #pos-wrapper {
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
        }

        /* ══════════════════════════════════════════════════════════════════
           TOP BAR
        ══════════════════════════════════════════════════════════════════ */
        #pos-topbar {
            background: linear-gradient(135deg, var(--pb-lightest) 0%, var(--pb-light) 100%);
            border-bottom: 2px solid var(--pb-mid);
            padding: 8px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(91,158,201,0.12);
            z-index: 100;
        }

        #pos-topbar .pos-title {
            font-size: 15px;
            font-weight: 800;
            color: var(--pb-text);
            letter-spacing: 0.5px;
        }

        #pos-topbar .pos-meta {
            font-size: 11px;
            color: var(--text-muted);
        }

        /* ══════════════════════════════════════════════════════════════════
           MAIN SPLIT LAYOUT
        ══════════════════════════════════════════════════════════════════ */
        #pos-main {
            display: flex;
            flex: 1;
            overflow: hidden;
        }

        /* ── LEFT: Product Browser ────────────────────────────────────── */
        #product-browser {
            width: var(--product-w);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            background: var(--bg-card);
        }

        /* ── RIGHT: Sidebar Cart ──────────────────────────────────────── */
        #sidebar-cart {
            width: var(--sidebar-w);
            display: flex;
            flex-direction: column;
            background: rgba(var(--glass-bg), 0.4);
            border-left: 2px solid var(--pb-light);
            overflow: hidden;
        }

        /* ══════════════════════════════════════════════════════════════════
           PRODUCT BROWSER — Search Bar
        ══════════════════════════════════════════════════════════════════ */
        #search-bar {
            padding: 10px 14px;
            background: var(--pb-lightest);
            border-bottom: 1px solid var(--pb-light);
            flex-shrink: 0;
        }

        #searchInput {
            border: 1.5px solid var(--pb-mid);
            border-radius: 8px;
            font-size: 13px;
            padding: 8px 12px 8px 36px;
            transition: all 0.2s;
            background: var(--bg-card);
        }

        #searchInput:focus {
            border-color: var(--pb-accent);
            box-shadow: 0 0 0 3px rgba(123,184,212,0.18);
            outline: none;
        }

        .search-icon-wrap {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--pb-mid);
            font-size: 15px;
            pointer-events: none;
        }

        /* ══════════════════════════════════════════════════════════════════
           PRODUCT BROWSER — Category Filter Pills
        ══════════════════════════════════════════════════════════════════ */
        #category-bar {
            padding: 8px 14px;
            background: var(--bg-card);
            border-bottom: 1px solid #EEF3F7;
            flex-shrink: 0;
            overflow-x: auto;
            white-space: nowrap;
            scrollbar-width: thin;
        }

        #category-bar::-webkit-scrollbar { height: 4px; }
        #category-bar::-webkit-scrollbar-thumb { background: var(--pb-mid); border-radius: 4px; }

        .category-pill {
            display: inline-block;
            padding: 5px 14px;
            margin-right: 6px;
            margin-bottom: 4px;
            font-size: 11.5px;
            font-weight: 600;
            border-radius: 20px;
            border: 1.5px solid var(--pb-mid);
            background: var(--bg-card);
            color: var(--pb-text);
            cursor: pointer;
            transition: all 0.18s ease;
            white-space: nowrap;
            user-select: none;
        }

        .category-pill:hover {
            background: var(--pb-lightest);
            transform: translateY(-1px);
        }

        .category-pill.active {
            background: var(--pb-dark);
            border-color: var(--pb-dark);
            color: #fff;
            box-shadow: 0 2px 8px rgba(91,158,201,0.35);
        }

        .category-separator {
            display: inline-block;
            width: 1px;
            height: 22px;
            background: var(--pb-light);
            margin: 0 8px;
            vertical-align: middle;
        }

        .category-group-label {
            display: inline-block;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #8FA8B5;
            margin-right: 6px;
            vertical-align: middle;
        }

        /* ══════════════════════════════════════════════════════════════════
           PRODUCT BROWSER — Product Grid
        ══════════════════════════════════════════════════════════════════ */
        #product-grid-container {
            flex: 1;
            overflow-y: auto;
            padding: 12px 14px;
        }

        .product-card {
            border: 1.5px solid var(--border-light);
            border-radius: 10px;
            background: var(--bg-card);
            cursor: pointer;
            transition: all 0.2s ease;
            overflow: hidden;
            position: relative;
            height: 100%;
        }

        .product-card:hover {
            border-color: var(--pb-accent);
            box-shadow: 0 4px 16px rgba(91,158,201,0.18);
            transform: translateY(-2px);
        }

        .product-card:active {
            transform: scale(0.97);
            box-shadow: 0 1px 4px rgba(91,158,201,0.15);
        }

        .product-card.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }

        .product-card.disabled:hover {
            transform: none;
            box-shadow: none;
            border-color: var(--border-light);
        }

        /* Product Thumbnail Placeholder */
        .product-thumb {
            height: 140px; /* Diperbesar agar gambar lebih dominan */
            background: linear-gradient(145deg, var(--pb-lightest) 0%, var(--bg-card) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 1px solid var(--border-light);
            position: relative;
            overflow: hidden; /* Mencegah gambar overflow */
        }

        .product-thumb i {
            font-size: 32px;
            color: var(--pb-mid);
            opacity: 0.6;
        }

        .product-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Out of Stock Overlay */
        .stok-habis-badge {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(200, 60, 60, 0.85);
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 4px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            z-index: 5;
        }

        /* Product Card Body */
        .product-card-body {
            padding: 8px 10px 10px;
        }

        .product-name {
            font-size: 12px;
            font-weight: 700;
            color: var(--text-primary);
            line-height: 1.3;
            margin-bottom: 3px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-barcode {
            font-size: 10px;
            color: var(--text-muted);
            font-family: 'Courier New', monospace;
            margin-bottom: 4px;
        }

        .product-price {
            font-size: 13px;
            font-weight: 800;
            color: var(--pb-accent);
        }

        .product-stock {
            font-size: 10px;
            color: var(--text-secondary);
            margin-top: 2px;
        }

        .product-stock.low {
            color: #E67E22;
            font-weight: 600;
        }

        /* Cart flash animation on product card click */
        @keyframes card-pulse {
            0%   { box-shadow: 0 0 0 0 rgba(91,158,201,0.5); }
            70%  { box-shadow: 0 0 0 8px rgba(91,158,201,0); }
            100% { box-shadow: 0 0 0 0 rgba(91,158,201,0); }
        }

        .card-added-pulse {
            animation: card-pulse 0.4s ease-out;
        }

        /* ══════════════════════════════════════════════════════════════════
           SIDEBAR CART — Customer Section
        ══════════════════════════════════════════════════════════════════ */
        #cart-customer-section {
            padding: 10px 12px;
            background: linear-gradient(135deg, var(--pb-lightest) 0%, #fff 100%);
            border-bottom: 1px solid var(--pb-light);
            flex-shrink: 0;
        }

        #cart-customer-section .section-label {
            font-size: 10px;
            font-weight: 700;
            color: #7A97A7;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            margin-bottom: 6px;
        }

        /* ══════════════════════════════════════════════════════════════════
           SIDEBAR CART — Items List
        ══════════════════════════════════════════════════════════════════ */
        #cart-items-section {
            flex: 1;
            overflow-y: auto;
            padding: 8px 12px;
        }

        .cart-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 10px;
            background: var(--bg-card);
            border: 1px solid #EEF3F7;
            border-radius: 8px;
            margin-bottom: 6px;
            transition: all 0.15s;
        }

        .cart-item:hover {
            border-color: var(--pb-mid);
            box-shadow: 0 1px 4px rgba(91,158,201,0.1);
        }

        .cart-item-info {
            flex: 1;
            min-width: 0;
        }

        .cart-item-name {
            font-size: 12px;
            font-weight: 700;
            color: var(--text-primary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .cart-item-price {
            font-size: 10.5px;
            color: var(--text-secondary);
        }

        .cart-item-subtotal {
            font-size: 12px;
            font-weight: 700;
            color: var(--pb-text);
            white-space: nowrap;
            min-width: 70px;
            text-align: right;
        }

        /* Qty toggle group */
        .qty-toggle {
            display: flex;
            align-items: center;
            gap: 0;
            border: 1.5px solid #D6E9F0;
            border-radius: 6px;
            overflow: hidden;
            flex-shrink: 0;
        }

        .qty-toggle button {
            width: 28px;
            height: 28px;
            border: none;
            background: var(--pb-lightest);
            color: var(--pb-text);
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.12s;
            padding: 0;
        }

        .qty-toggle button:hover {
            background: var(--pb-light);
        }

        .qty-toggle button:active {
            background: var(--pb-mid);
            color: #fff;
        }

        .qty-toggle input {
            width: 36px;
            height: 28px;
            border: none;
            border-left: 1px solid var(--border-light);
            border-right: 1px solid var(--border-light);
            text-align: center;
            font-size: 12px;
            font-weight: 600;
            background: var(--bg-card);
            color: var(--text-primary);
            outline: none;
            -moz-appearance: textfield;
        }

        .qty-toggle input::-webkit-outer-spin-button,
        .qty-toggle input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        /* Remove button */
        .cart-remove-btn {
            width: 26px;
            height: 26px;
            border: none;
            background: transparent;
            color: #E08080;
            font-size: 14px;
            cursor: pointer;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s;
            flex-shrink: 0;
        }

        .cart-remove-btn:hover {
            background: #FFE8E8;
            color: #C0392B;
        }

        /* Empty cart state */
        .cart-empty {
            text-align: center;
            padding: 30px 16px;
            color: #B0C4D0;
        }

        .cart-empty i {
            font-size: 36px;
            margin-bottom: 8px;
            opacity: 0.5;
        }

        @keyframes cart-item-in {
            from { opacity: 0; transform: translateX(20px); }
            to   { opacity: 1; transform: translateX(0); }
        }

        .cart-item-animate {
            animation: cart-item-in 0.25s ease-out;
        }

        /* ══════════════════════════════════════════════════════════════════
           SIDEBAR CART — Bottom Totals & Checkout
        ══════════════════════════════════════════════════════════════════ */
        #cart-footer {
            flex-shrink: 0;
            background: var(--bg-card);
            border-top: 2px solid var(--pb-light);
            padding: 8px 12px 10px;
        }

        .grand-total-box {
            background: linear-gradient(135deg, var(--pb-dark) 0%, var(--pb-darker) 100%);
            border-radius: 10px;
            padding: 8px 14px;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 7px;
        }

        .grand-total-box .label-sm {
            font-size: 10px;
            opacity: 0.8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .grand-total-box .amount {
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .checkout-row {
            display: flex;
            gap: 8px;
            align-items: stretch;
        }

        #metodePembayaranSelect {
            font-size: 11.5px;
            border: 1.5px solid var(--pb-mid);
            border-radius: 7px;
            padding: 6px 8px;
            background: var(--bg-card);
            color: var(--pb-text);
            font-weight: 600;
            flex: 1;
        }

        #metodePembayaranSelect:focus {
            border-color: var(--pb-accent);
            box-shadow: 0 0 0 2px rgba(123,184,212,0.2);
            outline: none;
        }

        #checkoutBtn {
            background: linear-gradient(135deg, var(--pb-dark) 0%, var(--pb-darker) 100%);
            border: none;
            color: #fff;
            font-size: 12.5px;
            font-weight: 700;
            padding: 8px 16px;
            border-radius: 8px;
            letter-spacing: 0.3px;
            transition: all 0.2s;
            box-shadow: 0 3px 10px rgba(61,133,176,0.35);
            cursor: pointer;
            white-space: nowrap;
            flex: 1;
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

        /* ══════════════════════════════════════════════════════════════════
           TIER BADGES
        ══════════════════════════════════════════════════════════════════ */
        .badge-tier-umum    { background-color: #6c757d; color:#fff; }
        .badge-tier-member  { background-color: #198754; color:#fff; }
        .badge-tier-rekan   { background-color: #fd7e14; color:#fff; }
        .badge-tier-motoris { background-color: var(--pb-dark); color:#fff; }

        /* ══════════════════════════════════════════════════════════════════
           TOAST NOTIFICATIONS
        ══════════════════════════════════════════════════════════════════ */
        .toast-container {
            z-index: 1080;
        }

        #successToast, #warningToast { font-size: 12px; }

        /* ══════════════════════════════════════════════════════════════════
           FORM INPUTS
        ══════════════════════════════════════════════════════════════════ */
        .form-control-sm, .form-select-sm {
            font-size: 12px;
            border-color: #C8DDE8;
        }

        .form-control-sm:focus, .form-select-sm:focus {
            border-color: var(--pb-accent);
            box-shadow: 0 0 0 0.15rem rgba(123,184,212,0.25);
        }

        /* ══════════════════════════════════════════════════════════════════
           SCROLLBAR
        ══════════════════════════════════════════════════════════════════ */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: #F0F4F8; }
        ::-webkit-scrollbar-thumb { background: var(--pb-mid); border-radius: 4px; }

        /* ══════════════════════════════════════════════════════════════════
           NOMINAL UANG ROW
        ══════════════════════════════════════════════════════════════════ */
        #nominalUangRow {
            padding: 0 12px 0;
        }

        .nominal-input-group {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 8px;
        }

        .nominal-input-group label {
            font-size: 10.5px;
            font-weight: 600;
            color: #7A97A7;
            white-space: nowrap;
        }

        .kembalian-inline {
            font-size: 11px;
            text-align: right;
            margin-bottom: 8px;
            padding: 0 12px;
        }

        .kembalian-inline .kem-label {
            color: #7A97A7;
            font-weight: 600;
        }

        .kembalian-inline .kem-amount {
            font-weight: 700;
            color: var(--success-col);
        }

        /* ══════════════════════════════════════════════════════════════════
           RESPONSIVE ADJUSTMENTS
        ══════════════════════════════════════════════════════════════════ */
        @media (max-width: 992px) {
            :root {
                --sidebar-w: 40%;
                --product-w: 60%;
            }
        }

        @media (max-width: 768px) {
            #pos-main {
                flex-direction: column;
            }
            #product-browser, #sidebar-cart {
                width: 100%;
            }
            #product-browser {
                height: 55%;
            }
            #sidebar-cart {
                height: 45%;
                border-left: none;
                border-top: 2px solid var(--pb-light);
            }
        }
    </style>
</head>

<body>
<div id="pos-wrapper">

    {{-- ════════════════════════════════════════════════════════════════
         TOP BAR
    ════════════════════════════════════════════════════════════════ --}}
    <div id="pos-topbar">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-grid-3x3-gap-fill" style="color:var(--pb-dark); font-size:17px;"></i>
            <span class="pos-title">POS MODE STANDAR</span>
            <span class="badge badge-tier-umum" style="font-size:9px; padding:2px 8px; border-radius:10px;">Product Browser</span>
        </div>

        <div class="d-flex align-items-center gap-3">
            <span class="pos-meta">
                <i class="bi bi-calendar2 me-1"></i>
                <span id="posDate">—</span>
            </span>
            <span class="pos-meta">
                <i class="bi bi-clock me-1"></i>
                <span id="posClock">—</span>
            </span>
            <span class="pos-meta">
                <i class="bi bi-person-circle me-1"></i>
                {{ auth()->user()->nama_lengkap ?? 'Kasir' }}
            </span>
            <button type="button" id="posDarkModeToggle" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size:10.5px; border-radius:5px;" title="Ganti Tema Terang/Gelap" aria-label="Toggle Dark Mode">
                <i class="bi bi-moon-stars" id="posDarkModeIcon" aria-hidden="true"></i>
            </button>
            <a href="{{ url('/pos/custom') }}" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size:10.5px; border-radius:5px;">
                <i class="bi bi-lightning-charge me-1" aria-hidden="true"></i>Mode Kilat
            </a>
            <a href="{{ url('/dashboard') }}" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size:10.5px; border-radius:5px;">
                <i class="bi bi-house me-1" aria-hidden="true"></i>Dashboard
            </a>
            <a href="{{ route('laporan.rekap-penjualan') }}" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size:10.5px; border-radius:5px;" title="Lihat riwayat transaksi penjualan">
                <i class="bi bi-clock-history me-1" aria-hidden="true"></i>Riwayat
            </a>
            <a href="{{ url('/pos/scan') }}" class="btn btn-sm btn-pb py-0 px-2" style="font-size:10.5px; border-radius:5px;" title="Scan gambar struk/belanja dengan AI">
                <i class="bi bi-camera me-1"></i>Scan Gambar
            </a>
            <button type="button" class="btn btn-sm btn-warning py-0 px-2 fw-bold text-dark" style="font-size:10.5px; border-radius:5px;" data-bs-toggle="modal" data-bs-target="#voiceTransactionModal" title="Input Transaksi Suara AI">
                <i class="bi bi-mic-fill me-1"></i>Input Suara AI
            </button>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════
         MAIN SPLIT: LEFT (Product Browser) + RIGHT (Cart Sidebar)
    ════════════════════════════════════════════════════════════════ --}}
    <div id="pos-main">

        {{-- ─── LEFT: Product Browser ─────────────────────────────── --}}
        <div id="product-browser">

            {{-- Search Bar --}}
            <div id="search-bar">
                <div class="position-relative">
                    <span class="search-icon-wrap"><i class="bi bi-search"></i></span>
                    <input type="text"
                           id="searchInput"
                           class="form-control w-100"
                           placeholder="Cari produk berdasarkan nama atau barcode…"
                           autocomplete="off">
                </div>
            </div>

            {{-- Category Filter Pills --}}
            <div id="category-bar">
                <span class="category-pill active" data-filter="all" onclick="filterCategory('all', this)">
                    <i class="bi bi-grid-fill me-1"></i>Semua Produk
                </span>
                <span class="category-separator"></span>

                {{-- Rendered via JS from server data --}}
                <span id="categoryPillsContainer"></span>
            </div>

            {{-- Product Grid --}}
            <div id="product-grid-container">
                <div id="productGrid" class="row row-cols-2 row-cols-md-4 g-2">
                    {{-- Product cards rendered by JavaScript --}}
                </div>

                {{-- No results state --}}
                <div id="noResultsState" class="text-center py-5 d-none">
                    <i class="bi bi-search" style="font-size:40px; color:var(--pb-mid); opacity:0.4;"></i>
                    <p class="text-muted mt-2" style="font-size:12px;">Tidak ada produk ditemukan.</p>
                </div>

                {{-- Loading spinner --}}
                <div id="loadingState" class="text-center py-5">
                    <div class="spinner-border text-secondary" role="status" style="width:30px; height:30px;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted mt-2" style="font-size:12px;">Memuat produk…</p>
                </div>
            </div>
        </div>

        {{-- ─── RIGHT: Sidebar Cart ───────────────────────────────── --}}
        <div id="sidebar-cart">

            {{-- Customer Selection --}}
            <div id="cart-customer-section">
                <div class="section-label">
                    <i class="bi bi-person-fill me-1"></i>Pelanggan
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <select id="pelangganSelect" class="form-select form-select-sm" style="border-color:var(--pb-mid); font-size:12px; flex:1;">
                        <option value="" data-status="Umum">— Pelanggan Umum (Walk-In) —</option>
                        {{-- Options populated by JS --}}
                    </select>
                    <span id="tierBadge" class="badge badge-tier-umum" style="font-size:9px; padding:3px 8px; border-radius:10px; white-space:nowrap;">Umum</span>
                </div>
            </div>

            {{-- Cart Items List --}}
            <div id="cart-items-section">
                <div id="cartItemsContainer">
                    {{-- Rendered by JS --}}
                </div>
                <div id="cartEmpty" class="cart-empty">
                    <i class="bi bi-cart3 d-block"></i>
                    <span style="font-size:12px;">Ketuk produk untuk<br>menambah ke keranjang</span>
                </div>
            </div>

            {{-- Nominal Uang (for Tunai) --}}
            <div id="nominalUangRow">
                <div class="nominal-input-group">
                    <label for="nominalUang">Bayar Rp</label>
                    <input type="number"
                           id="nominalUang"
                           class="form-control form-control-sm text-end"
                           placeholder="0"
                           min="0"
                           style="border-color:var(--pb-mid); flex:1;">
                </div>
            </div>

            {{-- Kembalian display --}}
            <div id="kembalianRow" class="kembalian-inline">
                <span class="kem-label"><i class="bi bi-arrow-return-left me-1"></i>Kembalian:</span>
                <span class="kem-amount" id="kembalianDisplay">Rp 0</span>
            </div>

            {{-- Kredit / Piutang Info & DP Row --}}
            <div id="kreditRow" style="display:none; margin-top:6px; padding:8px 10px; background:rgba(217,119,6,0.08); border:1px solid rgba(217,119,6,0.25); border-radius:6px;">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span style="font-size:11px; font-weight:700; color:#B45309;">
                        <i class="bi bi-calendar-check me-1"></i>Transaksi Kredit (Piutang)
                    </span>
                    <span style="font-size:10.5px; color:#B45309;" id="statusKreditBadge">Belum Lunas</span>
                </div>
                <div class="d-flex gap-2 align-items-center mb-1">
                    <div style="flex:1;">
                        <label for="uangMukaKredit" style="font-size:10.5px; font-weight:600; color:var(--text-secondary); margin-bottom:2px; display:block;">Bayar Uang Muka / DP (Rp)</label>
                        <input type="number" id="uangMukaKredit" class="form-control form-control-sm text-end" placeholder="0" min="0" value="0" style="font-size:12px; border-color:var(--pb-mid);">
                    </div>
                    <div style="flex:1;">
                        <label for="jatuhTempoKredit" style="font-size:10.5px; font-weight:600; color:var(--text-secondary); margin-bottom:2px; display:block;">Jatuh Tempo (opsional)</label>
                        <input type="date" id="jatuhTempoKredit" class="form-control form-control-sm" style="font-size:11px; border-color:var(--pb-mid);">
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center pt-1" style="border-top: 1px dashed rgba(217,119,6,0.3); font-size:11.5px;">
                    <span style="color:var(--text-secondary);">Sisa Hutang / Piutang:</span>
                    <strong id="sisaPiutangDisplay" style="color:#DC2626; font-size:12.5px;">Rp 0</strong>
                </div>
            </div>

            {{-- Footer: Grand Total, Payment Method, Checkout --}}
            <div id="cart-footer">
                {{-- Grand Total --}}
                <div class="grand-total-box">
                    <div>
                        <div class="label-sm">Grand Total</div>
                        <div style="font-size:10px; opacity:0.7;">
                            <span id="cartCountDisplay">0</span> item
                        </div>
                    </div>
                    <div class="amount" id="grandTotalDisplay">Rp 0</div>
                </div>

                {{-- Payment Method + Checkout --}}
                <div class="checkout-row">
                    <select id="metodePembayaranSelect">
                        <option value="Tunai">💵 Tunai</option>
                        <option value="Kredit">📋 Kredit</option>
                        <option value="Digital Payment">📱 Digital</option>
                    </select>

                    <button id="checkoutBtn" disabled>
                        <span id="checkoutSpinner" class="spinner-border spinner-border-sm me-1 d-none" role="status"></span>
                        <i class="bi bi-check2-circle me-1" id="checkoutIcon"></i>
                        <span id="checkoutBtnText">Proses Transaksi</span>
                    </button>
                </div>

                {{-- Action Buttons (Cancel, Hold, Recall) --}}
                <div class="d-flex gap-2 mt-1">
                    <button type="button" class="btn btn-sm btn-danger flex-fill" style="background-color: #ffcccc; color: #cc0000; border-color: #ffcccc; font-weight: 600;" onclick="cancelTransaction()">
                        Batal Transaksi
                    </button>
                    <button type="button" class="btn btn-sm btn-info flex-fill" style="background-color: #d1ecf1; color: #0c5460; border-color: #d1ecf1; font-weight: 600;" onclick="holdTransaction()">
                        Tahan Transaksi
                    </button>
                </div>
                <button type="button" class="btn btn-sm btn-secondary w-100 mt-1" style="background-color: #e2e3e5; color: #383d41; border-color: #e2e3e5; font-weight: 600;" onclick="showHeldTransactions()">
                    💥 Transaksi Tertahan (<span id="hold-count">0</span>)
                </button>

            </div>

        </div>{{-- /#sidebar-cart --}}
    </div>{{-- /#pos-main --}}
</div>{{-- /#pos-wrapper --}}


{{-- ════════════════════════════════════════════════════════════════════
     TOAST NOTIFICATIONS
════════════════════════════════════════════════════════════════════ --}}
<div class="toast-container position-fixed bottom-0 end-0 p-3">
    {{-- Success Toast --}}
    <div id="successToast" class="toast align-items-center text-bg-success border-0" role="alert" data-bs-delay="5000">
        <div class="toast-body d-flex gap-2 align-items-start">
            <i class="bi bi-check-circle-fill mt-1" style="font-size:18px; flex-shrink:0;"></i>
            <div>
                <div class="fw-bold mb-1">Transaksi Berhasil!</div>
                <div><strong>Invoice:</strong> <span id="toastInvoice">—</span></div>
                <div><strong>Total:</strong> <span id="toastTotal">—</span></div>
                <div><strong>Kembalian:</strong> <span id="toastKembalian">—</span></div>
            </div>
            <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>

    {{-- Warning Toast (Stock limit) --}}
    <div id="warningToast" class="toast align-items-center text-bg-warning border-0" role="alert" data-bs-delay="3000">
        <div class="toast-body d-flex gap-2 align-items-center">
            <i class="bi bi-exclamation-triangle-fill" style="font-size:16px; flex-shrink:0;"></i>
            <span id="warningToastMsg">—</span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>

    {{-- Error Toast --}}
    <div id="errorToast" class="toast align-items-center text-bg-danger border-0" role="alert" data-bs-delay="6000">
        <div class="toast-body d-flex gap-2 align-items-start">
            <i class="bi bi-exclamation-circle-fill mt-1" style="font-size:16px; flex-shrink:0;"></i>
            <div>
                <div class="fw-bold" id="errorToastTitle">Error</div>
                <div id="errorToastMsg" style="font-size:11px;">—</div>
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
                        <thead style="background: #F7FAFC; position: sticky; top: 0; z-index: 1;">
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



{{-- ════════════════════════════════════════════════════════════════════
     BOOTSTRAP JS
════════════════════════════════════════════════════════════════════ --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


{{-- ════════════════════════════════════════════════════════════════════
     POS STANDARD MODE — JAVASCRIPT ENGINE
════════════════════════════════════════════════════════════════════ --}}
<script>
'use strict';

// ═══════════════════════════════════════════════════════════════════════════
// SECTION 1: CONSTANTS & CONFIGURATION
// ═══════════════════════════════════════════════════════════════════════════

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
// SECTION 2: SERVER DATA (injected from Blade)
// ═══════════════════════════════════════════════════════════════════════════

@php
    $allProductsData = \App\Models\Produk::with('kategori.kelompok')
        ->select([
            'id', 'kategori_id', 'barcode', 'nama_produk', 'satuan', 'gambar',
            'harga_jual_umum', 'harga_jual_member',
            'harga_jual_rekan', 'harga_jual_motoris',
            'stok', 'stok_minimum', 'toko_id'
        ])
        ->orderBy('nama_produk')
        ->get()
        ->map(function ($p) {
            return [
                'id'                  => $p->id,
                'kategori_id'         => $p->kategori_id,
                'kelompok_id'         => $p->kategori?->kelompok_id,
                'kelompok_nama'       => $p->kategori?->kelompok?->nama_kelompok ?? '-',
                'kategori_nama'       => $p->kategori?->nama_kategori ?? '-',
                'barcode'             => $p->barcode,
                'nama_produk'         => $p->nama_produk,
                'satuan'              => $p->satuan ?? 'Pcs',
                'gambar'              => $p->gambar,
                'harga_jual_umum'     => (float) $p->harga_jual_umum,
                'harga_jual_member'   => (float) $p->harga_jual_member,
                'harga_jual_rekan'    => (float) $p->harga_jual_rekan,
                'harga_jual_motoris'  => (float) $p->harga_jual_motoris,
                'stok'                => (int) $p->stok,
                'stok_minimum'        => (int) $p->stok_minimum,
            ];
        });

    $kelompokList = \App\Models\KelompokProduk::orderBy('nama_kelompok')
        ->get()
        ->map(fn ($k) => [
            'id'   => $k->id,
            'nama' => $k->nama_kelompok,
        ]);

    $kategoriList = \App\Models\KategoriProduk::orderBy('nama_kategori')
        ->get()
        ->map(fn ($k) => [
            'id'          => $k->id,
            'kelompok_id' => $k->kelompok_id,
            'nama'        => $k->nama_kategori,
        ]);

    $pelangganList = \App\Models\Pelanggan::orderBy('nama_pelanggan')
        ->get()
        ->map(fn ($p) => [
            'id'     => $p->id,
            'kode'   => $p->kode_pelanggan,
            'nama'   => $p->nama_pelanggan,
            'status' => $p->status_pelanggan,
        ]);
@endphp

/**
 * Product data — loaded once from the server at page load.
 * Each product includes all tier prices, stok, and category info.
 */
const ALL_PRODUCTS = @json($allProductsData);

/**
 * Kelompok Produk (Level 1 grouping) for category pills.
 */
const KELOMPOK_LIST = @json($kelompokList);

/**
 * Kategori Produk (Level 2) for sub-category pills.
 */
const KATEGORI_LIST = @json($kategoriList);

/**
 * Pelanggan list for customer dropdown.
 */
const PELANGGAN_LIST = @json($pelangganList);


// ═══════════════════════════════════════════════════════════════════════════
// SECTION 3: APPLICATION STATE
// ═══════════════════════════════════════════════════════════════════════════

const state = {
    // Customer
    customer           : { id: null, nama: 'Umum', status: 'Umum' },

    // Filtering
    activeFilter       : 'all',  // 'all' | 'kelompok_<id>' | 'kategori_<id>'
    searchQuery        : '',

    // Cart
    cart               : [],     // Array of { produk_id, barcode, nama_produk, satuan, tier, harga_satuan, qty, subtotal, stok }
    nextRowId          : 1,

    // Payment
    grandTotal         : 0,
    metodePembayaran   : 'Tunai',
};


// ═══════════════════════════════════════════════════════════════════════════
// SECTION 4: DOM REFERENCES
// ═══════════════════════════════════════════════════════════════════════════

const el = {
    // Top bar
    posDate              : document.getElementById('posDate'),
    posClock             : document.getElementById('posClock'),

    // Product browser
    searchInput          : document.getElementById('searchInput'),
    categoryPillsContainer: document.getElementById('categoryPillsContainer'),
    productGrid          : document.getElementById('productGrid'),
    noResultsState       : document.getElementById('noResultsState'),
    loadingState         : document.getElementById('loadingState'),

    // Sidebar cart
    pelangganSelect      : document.getElementById('pelangganSelect'),
    tierBadge            : document.getElementById('tierBadge'),
    cartItemsContainer   : document.getElementById('cartItemsContainer'),
    cartEmpty            : document.getElementById('cartEmpty'),
    cartCountDisplay     : document.getElementById('cartCountDisplay'),
    grandTotalDisplay    : document.getElementById('grandTotalDisplay'),
    metodePembayaranSelect: document.getElementById('metodePembayaranSelect'),
    nominalUangRow       : document.getElementById('nominalUangRow'),
    nominalUang          : document.getElementById('nominalUang'),
    kembalianRow         : document.getElementById('kembalianRow'),
    kembalianDisplay     : document.getElementById('kembalianDisplay'),
    kreditRow            : document.getElementById('kreditRow'),
    uangMukaKredit       : document.getElementById('uangMukaKredit'),
    jatuhTempoKredit     : document.getElementById('jatuhTempoKredit'),
    sisaPiutangDisplay   : document.getElementById('sisaPiutangDisplay'),
    statusKreditBadge    : document.getElementById('statusKreditBadge'),
    checkoutBtn          : document.getElementById('checkoutBtn'),
    checkoutSpinner      : document.getElementById('checkoutSpinner'),
    checkoutIcon         : document.getElementById('checkoutIcon'),
    checkoutBtnText      : document.getElementById('checkoutBtnText'),

    // Toasts
    successToast         : document.getElementById('successToast'),
    toastInvoice         : document.getElementById('toastInvoice'),
    toastTotal           : document.getElementById('toastTotal'),
    toastKembalian       : document.getElementById('toastKembalian'),
    warningToast         : document.getElementById('warningToast'),
    warningToastMsg      : document.getElementById('warningToastMsg'),
    errorToast           : document.getElementById('errorToast'),
    errorToastTitle      : document.getElementById('errorToastTitle'),
    errorToastMsg        : document.getElementById('errorToastMsg'),
};


// ═══════════════════════════════════════════════════════════════════════════
// SECTION 5: UTILITY FUNCTIONS
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
 * Safely escape HTML entities to prevent XSS.
 */
function escapeHtml(str) {
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(str ?? ''));
    return div.innerHTML;
}

/**
 * Debounce function.
 */
function debounce(fn, wait = 300) {
    let timer;
    return function (...args) {
        clearTimeout(timer);
        timer = setTimeout(() => fn.apply(this, args), wait);
    };
}

/**
 * Show a warning toast with a message.
 */
function showWarningToast(message) {
    el.warningToastMsg.textContent = message;
    const toast = new bootstrap.Toast(el.warningToast, { delay: 3000 });
    toast.show();
}

/**
 * Show an error toast.
 */
function showErrorToast(title, message) {
    el.errorToastTitle.textContent = title;
    el.errorToastMsg.textContent   = message;
    const toast = new bootstrap.Toast(el.errorToast, { delay: 6000 });
    toast.show();
}

/**
 * Show success toast with transaction details.
 */
function showSuccessToast(data) {
    el.toastInvoice.textContent   = data.nomor_invoice ?? '—';
    el.toastTotal.textContent     = formatRupiah(data.total_bayar ?? 0);
    el.toastKembalian.textContent = formatRupiah(data.kembalian ?? 0);
    const toast = new bootstrap.Toast(el.successToast, { delay: 5000 });
    toast.show();
}

/**
 * Get the current tier price column for the active customer.
 */
function getPriceColumn() {
    return PRICE_TIER_MAP[state.customer.status] ?? 'harga_jual_umum';
}

/**
 * Get price for a product based on the active customer tier.
 */
function getProductPrice(product) {
    const col = getPriceColumn();
    return parseFloat(product[col]) || 0;
}


// ═══════════════════════════════════════════════════════════════════════════
// SECTION 6: CUSTOMER MODULE
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Populate the customer dropdown from server data.
 */
function populatePelangganDropdown() {
    PELANGGAN_LIST.forEach(p => {
        const opt = document.createElement('option');
        opt.value            = p.id;
        opt.textContent      = `${p.nama} [${p.kode}] — ${p.status}`;
        opt.dataset.status   = p.status;
        el.pelangganSelect.appendChild(opt);
    });
}

/**
 * Handle customer selection change.
 * Updates the active tier and recalculates all prices in the cart.
 */
function onCustomerChange() {
    const select = el.pelangganSelect;
    const opt    = select.options[select.selectedIndex];

    if (select.value === '') {
        // Walk-in Umum customer
        state.customer = { id: null, nama: 'Umum', status: 'Umum' };
    } else {
        state.customer = {
            id    : parseInt(select.value),
            nama  : opt.textContent.split('[')[0].trim(),
            status: opt.dataset.status || 'Umum',
        };
    }

    // Update tier badge
    const status    = state.customer.status;
    const tierClass = TIER_BADGE_CLASS[status] ?? 'badge-tier-umum';
    el.tierBadge.className   = `badge ${tierClass}`;
    el.tierBadge.textContent = status;
    el.tierBadge.style.fontSize = '9px';
    el.tierBadge.style.padding = '3px 8px';
    el.tierBadge.style.borderRadius = '10px';
    el.tierBadge.style.whiteSpace = 'nowrap';

    // Recalculate all cart prices for the new tier
    recalculateCartPrices();

    // Re-render product grid to reflect new tier prices
    renderProductGrid();
}

/**
 * Recalculate all cart item prices when customer tier changes.
 */
function recalculateCartPrices() {
    const priceCol = getPriceColumn();

    state.cart.forEach(item => {
        // Find the original product data to get the new tier price
        const product = ALL_PRODUCTS.find(p => p.id === item.produk_id);
        if (product) {
            const newPrice   = parseFloat(product[priceCol]) || 0;
            item.tier        = state.customer.status;
            item.harga_satuan = newPrice;
            item.subtotal    = newPrice * item.qty;
        }
    });

    renderCartItems();
    recalculateTotals();
}


// ═══════════════════════════════════════════════════════════════════════════
// SECTION 7: CATEGORY FILTER MODULE
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Render category pills from Kelompok and Kategori data.
 */
function renderCategoryPills() {
    let html = '';

    KELOMPOK_LIST.forEach(kelompok => {
        // Kelompok pill (Level 1)
        html += `<span class="category-group-label">${escapeHtml(kelompok.nama)}:</span>`;

        // Sub-categories for this kelompok
        const subKategori = KATEGORI_LIST.filter(k => k.kelompok_id === kelompok.id);
        subKategori.forEach(kat => {
            html += `<span class="category-pill"
                           data-filter="kategori_${kat.id}"
                           onclick="filterCategory('kategori_${kat.id}', this)">
                        ${escapeHtml(kat.nama)}
                     </span>`;
        });

        html += '<span class="category-separator"></span>';
    });

    el.categoryPillsContainer.innerHTML = html;
}

/**
 * Handle category filter click.
 * Updates active pill and re-renders the product grid.
 */
function filterCategory(filter, pillEl) {
    state.activeFilter = filter;

    // Update active pill styling
    document.querySelectorAll('.category-pill').forEach(p => p.classList.remove('active'));
    if (pillEl) pillEl.classList.add('active');

    renderProductGrid();
}
// Expose to global for onclick
window.filterCategory = filterCategory;


// ═══════════════════════════════════════════════════════════════════════════
// SECTION 8: PRODUCT GRID MODULE
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Get filtered products based on active category filter and search query.
 */
function getFilteredProducts() {
    let products = [...ALL_PRODUCTS];

    // Apply category filter
    if (state.activeFilter !== 'all') {
        if (state.activeFilter.startsWith('kategori_')) {
            const katId = parseInt(state.activeFilter.replace('kategori_', ''));
            products = products.filter(p => p.kategori_id === katId);
        } else if (state.activeFilter.startsWith('kelompok_')) {
            const kelId = parseInt(state.activeFilter.replace('kelompok_', ''));
            products = products.filter(p => p.kelompok_id === kelId);
        }
    }

    // Apply search filter (name or barcode)
    if (state.searchQuery.length > 0) {
        const q = state.searchQuery.toLowerCase();
        products = products.filter(p =>
            p.nama_produk.toLowerCase().includes(q) ||
            (p.barcode && p.barcode.toLowerCase().includes(q))
        );
    }

    return products;
}

/**
 * Render the product grid cards.
 */
function renderProductGrid() {
    const products = getFilteredProducts();

    el.loadingState.classList.add('d-none');

    if (products.length === 0) {
        el.productGrid.innerHTML = '';
        el.noResultsState.classList.remove('d-none');
        return;
    }

    el.noResultsState.classList.add('d-none');

    const priceCol = getPriceColumn();

    el.productGrid.innerHTML = products.map(product => {
        const harga       = parseFloat(product[priceCol]) || 0;
        const isOutStock  = product.stok <= 0;
        const isLowStock  = !isOutStock && product.stok <= product.stok_minimum;
        const disabledCls = isOutStock ? 'disabled' : '';

        return `
            <div class="col">
                <div class="product-card ${disabledCls}"
                     data-product-id="${product.id}"
                     ${!isOutStock ? `onclick="addProductToCart(${product.id})"` : ''}>

                    <div class="product-thumb">
                        ${product.gambar 
                            ? `<img src="/storage/${product.gambar}" alt="${escapeHtml(product.nama_produk)}">` 
                            : '<i class="bi bi-box-seam"></i>'
                        }
                        ${isOutStock ? '<span class="stok-habis-badge">Stok Habis</span>' : ''}
                    </div>

                    <div class="product-card-body">
                        <div class="product-name" title="${escapeHtml(product.nama_produk)}">
                            ${escapeHtml(product.nama_produk)}
                        </div>
                        <div class="product-barcode">${escapeHtml(product.barcode || '-')}</div>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <div class="product-price mb-0">${formatRupiah(harga)}</div>
                            <div class="product-stock mb-0 ${isLowStock ? 'low' : ''}">
                                ${isOutStock
                                    ? '<span style="color:#C0392B;">Stok: 0</span>'
                                    : (isLowStock
                                        ? `<i class="bi bi-exclamation-triangle-fill me-1"></i>Sisa: ${product.stok}`
                                        : `Stok: ${product.stok}`
                                    )
                                }
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;
    }).join('');
}


// ═══════════════════════════════════════════════════════════════════════════
// SECTION 9: CART MANAGEMENT MODULE
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Add a product to the cart by clicking its card.
 * If the product already exists, increment qty by 1.
 * Enforces max stock limit.
 */
function addProductToCart(productId) {
    const product = ALL_PRODUCTS.find(p => p.id === productId);
    if (!product || product.stok <= 0) return;

    const existingIndex = state.cart.findIndex(item => item.produk_id === productId);

    if (existingIndex >= 0) {
        // Product already in cart — increment qty
        const existing = state.cart[existingIndex];
        if (existing.qty >= product.stok) {
            showWarningToast('Stok Maksimal Tercapai!');
            return;
        }
        existing.qty     += 1;
        existing.subtotal = existing.harga_satuan * existing.qty;
    } else {
        // New product — add to cart
        const priceCol = getPriceColumn();
        const harga    = parseFloat(product[priceCol]) || 0;

        state.cart.push({
            rowId        : state.nextRowId++,
            produk_id    : product.id,
            barcode      : product.barcode,
            nama_produk  : product.nama_produk,
            satuan       : product.satuan || 'Pcs',
            tier         : state.customer.status,
            harga_satuan : harga,
            qty          : 1,
            subtotal     : harga,
            stok         : product.stok,
        });
    }

    // Pulse animation on the clicked card
    const cardEl = document.querySelector(`.product-card[data-product-id="${productId}"]`);
    if (cardEl) {
        cardEl.classList.add('card-added-pulse');
        setTimeout(() => cardEl.classList.remove('card-added-pulse'), 400);
    }

    renderCartItems();
    recalculateTotals();
}
// Expose to global for onclick
window.addProductToCart = addProductToCart;

/**
 * Render the cart items list in the sidebar.
 */
function renderCartItems() {
    if (state.cart.length === 0) {
        el.cartItemsContainer.innerHTML = '';
        el.cartEmpty.classList.remove('d-none');
        el.cartCountDisplay.textContent = '0';
        return;
    }

    el.cartEmpty.classList.add('d-none');
    el.cartCountDisplay.textContent = state.cart.reduce((sum, item) => sum + item.qty, 0);

    el.cartItemsContainer.innerHTML = state.cart.map((item, index) => {
        return `
            <div class="cart-item cart-item-animate" id="cartItem-${item.rowId}">
                <div class="cart-item-info">
                    <div class="cart-item-name" title="${escapeHtml(item.nama_produk)}">${escapeHtml(item.nama_produk)}</div>
                    <div class="cart-item-price">${formatRupiah(item.harga_satuan)} / ${escapeHtml(item.satuan)}</div>
                </div>

                <div class="qty-toggle">
                    <button type="button" onclick="changeCartQty(${item.rowId}, -1)" aria-label="Kurangi qty">−</button>
                    <input type="number"
                           value="${item.qty}"
                           min="1"
                           max="${item.stok}"
                           onchange="setCartQty(${item.rowId}, this.value)"
                           aria-label="Quantity">
                    <button type="button" onclick="changeCartQty(${item.rowId}, 1)" aria-label="Tambah qty">+</button>
                </div>

                <div class="cart-item-subtotal">${formatRupiah(item.subtotal)}</div>

                <button type="button"
                        class="cart-remove-btn"
                        onclick="removeCartItem(${item.rowId})"
                        title="Hapus item"
                        aria-label="Hapus ${escapeHtml(item.nama_produk)}">
                    <i class="bi bi-trash3"></i>
                </button>
            </div>`;
    }).join('');
}

/**
 * Change qty of a cart item by delta (+1 or -1).
 */
function changeCartQty(rowId, delta) {
    const item = state.cart.find(i => i.rowId === rowId);
    if (!item) return;

    const newQty = item.qty + delta;

    if (newQty < 1) {
        removeCartItem(rowId);
        return;
    }

    if (newQty > item.stok) {
        showWarningToast('Stok Maksimal Tercapai!');
        return;
    }

    item.qty      = newQty;
    item.subtotal = item.harga_satuan * newQty;

    renderCartItems();
    recalculateTotals();
}
window.changeCartQty = changeCartQty;

/**
 * Set qty of a cart item directly from the input field.
 */
function setCartQty(rowId, value) {
    const item = state.cart.find(i => i.rowId === rowId);
    if (!item) return;

    let newQty = parseInt(value) || 1;
    if (newQty < 1) newQty = 1;

    if (newQty > item.stok) {
        newQty = item.stok;
        showWarningToast('Stok Maksimal Tercapai!');
    }

    item.qty      = newQty;
    item.subtotal = item.harga_satuan * newQty;

    renderCartItems();
    recalculateTotals();
}
window.setCartQty = setCartQty;

/**
 * Remove a cart item by rowId.
 */
function removeCartItem(rowId) {
    state.cart = state.cart.filter(i => i.rowId !== rowId);
    renderCartItems();
    recalculateTotals();
}
window.removeCartItem = removeCartItem;


// ═══════════════════════════════════════════════════════════════════════════
// SECTION 10: PAYMENT & TOTALS MODULE
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Recalculate grand total and update all payment displays.
 */
function recalculateTotals() {
    state.grandTotal = state.cart.reduce((sum, item) => sum + item.subtotal, 0);
    el.grandTotalDisplay.textContent = formatRupiah(state.grandTotal);

    calculateKembalian();
    validatePayment();
}

/**
 * Compute and display kembalian (change) for Tunai payments.
 */
function calculateKembalian() {
    if (state.metodePembayaran !== 'Tunai') {
        el.kembalianDisplay.textContent = 'Rp 0';
        el.kembalianDisplay.style.color = '';
        return;
    }

    const nominal   = parseFloat(el.nominalUang.value) || 0;
    const kembalian = nominal - state.grandTotal;

    el.kembalianDisplay.textContent = formatRupiah(Math.max(0, kembalian));
    el.kembalianDisplay.style.color = kembalian < 0 ? '#C0392B' : 'var(--success-col)';
}

/**
 * Calculate Sisa Piutang for Kredit.
 */
function calculateSisaPiutang() {
    if (!el.uangMukaKredit || !el.sisaPiutangDisplay) return;
    const dp = parseFloat(el.uangMukaKredit.value) || 0;
    const sisa = Math.max(0, state.grandTotal - dp);
    el.sisaPiutangDisplay.textContent = formatRupiah(sisa);
    if (el.statusKreditBadge) {
        if (sisa <= 0 && state.grandTotal > 0) {
            el.statusKreditBadge.textContent = 'Lunas (DP Penuh)';
            el.statusKreditBadge.style.color = 'var(--success-col)';
        } else {
            el.statusKreditBadge.textContent = 'Belum Lunas';
            el.statusKreditBadge.style.color = '#B45309';
        }
    }
}

/**
 * Handle payment method changes.
 */
function onPaymentMethodChange() {
    state.metodePembayaran = el.metodePembayaranSelect.value;

    const isTunai = state.metodePembayaran === 'Tunai';
    const isKredit = state.metodePembayaran === 'Kredit';

    el.nominalUangRow.style.display = isTunai ? '' : 'none';
    el.kembalianRow.style.display   = isTunai ? '' : 'none';
    if (el.kreditRow) {
        el.kreditRow.style.display  = isKredit ? '' : 'none';
    }

    // Reset nominal styling
    el.nominalUang.style.borderColor     = '';
    el.nominalUang.style.backgroundColor = '';

    calculateKembalian();
    calculateSisaPiutang();
    validatePayment();
}

/**
 * Validate payment readiness and enable/disable checkout button.
 */
function validatePayment() {
    const hasItems = state.cart.length > 0;

    if (!hasItems) {
        el.checkoutBtn.disabled = true;
        return;
    }

    if (state.metodePembayaran === 'Tunai') {
        const nominal = parseFloat(el.nominalUang.value) || 0;
        if (nominal < state.grandTotal) {
            el.checkoutBtn.disabled               = true;
            el.nominalUang.style.borderColor      = (nominal > 0) ? '#FF8080' : '';
            el.nominalUang.style.backgroundColor  = (nominal > 0) ? '#FFF0F0' : '';
        } else {
            el.checkoutBtn.disabled               = false;
            el.nominalUang.style.borderColor      = '';
            el.nominalUang.style.backgroundColor  = '';
        }
    } else {
        el.checkoutBtn.disabled = false;
    }
}


// ═══════════════════════════════════════════════════════════════════════════
// SECTION 11: CHECKOUT MODULE
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Process POS checkout transaction.
 * Sends cart to /penjualan/store via POST.
 * Backend derives prices from DB — we only send product_id and qty.
 */
async function processCheckout() {
    if (state.cart.length === 0) {
        showErrorToast('Keranjang Kosong', 'Silakan tambahkan produk sebelum checkout.');
        return;
    }

    if (el.checkoutBtn.disabled) return;

    const nominalUang = parseFloat(el.nominalUang.value) || 0;
    if (state.metodePembayaran === 'Tunai' && nominalUang < state.grandTotal) {
        return;
    }

    // Set loading state
    el.checkoutBtn.disabled = true;
    el.checkoutSpinner.classList.remove('d-none');
    el.checkoutIcon.classList.add('d-none');
    el.checkoutBtnText.textContent = 'Memproses…';

    // Construct payload
    const uangMukaVal = (state.metodePembayaran === 'Kredit' && el.uangMukaKredit)
        ? (parseFloat(el.uangMukaKredit.value) || 0)
        : nominalUang;
    const jatuhTempoVal = (state.metodePembayaran === 'Kredit' && el.jatuhTempoKredit)
        ? (el.jatuhTempoKredit.value || null)
        : null;

    const payload = {
        pelanggan_id        : state.customer.id,
        metode_pembayaran   : state.metodePembayaran,
        diskon              : 0,
        nominal_uang        : nominalUang,
        uang_muka           : uangMukaVal,
        tanggal_jatuh_tempo : jatuhTempoVal,
        items               : state.cart.map(item => ({
            product_id : item.produk_id,
            qty        : item.qty,
        })),
    };

    try {
        const response = await fetch("{{ route('penjualan.store') }}", {
            method  : 'POST',
            headers : {
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
            console.error('[POS Standard] Non-JSON checkout response:', rawText);
            showErrorToast('Error Server (' + response.status + ')', 'Respon server tidak valid. Silakan coba lagi.');
            return;
        }

        if (response.ok && data.success) {
            // Success!
            showSuccessToast(data.data);

            // Open thermal receipt in new tab
            if (data.data.penjualan_id) {
                window.open("{{ url('/pos/print-struk') }}/" + data.data.penjualan_id, '_blank');
            }

            // Update stok locally for just-sold items
            state.cart.forEach(cartItem => {
                const product = ALL_PRODUCTS.find(p => p.id === cartItem.produk_id);
                if (product) {
                    product.stok = Math.max(0, product.stok - cartItem.qty);
                }
            });

            // Full POS Reset
            resetPOS();

        } else {
            // Validation / business rule error
            let errorMsg = data.message || 'Transaksi gagal.';
            if (data.errors) {
                const errList = Array.isArray(data.errors)
                    ? data.errors
                    : Object.values(data.errors).flat();
                errorMsg += ' ' + errList.join(' ');
            }
            showErrorToast('Transaksi Gagal', errorMsg);
        }

    } catch (networkError) {
        console.error('[POS Standard] Checkout network error:', networkError);
        showErrorToast('Kesalahan Koneksi', 'Gagal terhubung ke server. Periksa koneksi internet dan coba lagi.');

    } finally {
        // Restore button state
        el.checkoutBtn.disabled = false;
        el.checkoutSpinner.classList.add('d-none');
        el.checkoutIcon.classList.remove('d-none');
        el.checkoutBtnText.textContent = 'Proses Transaksi';
        validatePayment();
    }
}


// ═══════════════════════════════════════════════════════════════════════════
// SECTION 12: POS RESET MODULE
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Full POS reset after a successful transaction.
 */
function resetPOS() {
    // Reset customer to Umum
    state.customer = { id: null, nama: 'Umum', status: 'Umum' };
    el.pelangganSelect.value = '';
    el.tierBadge.className   = 'badge badge-tier-umum';
    el.tierBadge.textContent = 'Umum';

    // Clear cart
    state.cart      = [];
    state.nextRowId = 1;
    state.grandTotal = 0;

    // Reset payment
    state.metodePembayaran = 'Tunai';
    el.metodePembayaranSelect.value = 'Tunai';
    el.nominalUang.value = '';
    el.nominalUang.style.borderColor     = '';
    el.nominalUang.style.backgroundColor = '';
    el.nominalUangRow.style.display = '';
    if (el.uangMukaKredit) el.uangMukaKredit.value = '0';
    if (el.jatuhTempoKredit) el.jatuhTempoKredit.value = '';
    if (el.kreditRow) el.kreditRow.style.display = 'none';
    if (el.sisaPiutangDisplay) el.sisaPiutangDisplay.textContent = 'Rp 0';
    el.kembalianRow.style.display   = '';

    // Re-render everything
    renderCartItems();
    recalculateTotals();
    renderProductGrid();

    // Focus search input
    el.searchInput.focus();
}


// ═══════════════════════════════════════════════════════════════════════════
// SECTION 13: EVENT LISTENERS
// ═══════════════════════════════════════════════════════════════════════════

// ─── Search Input ────────────────────────────────────────────────────────
const debouncedSearch = debounce(function () {
    state.searchQuery = el.searchInput.value.trim();
    renderProductGrid();
}, 200);

el.searchInput.addEventListener('input', debouncedSearch);

el.searchInput.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        this.value = '';
        state.searchQuery = '';
        renderProductGrid();
    }
});

// ─── Customer Dropdown ───────────────────────────────────────────────────
el.pelangganSelect.addEventListener('change', onCustomerChange);

// ─── Payment Method Dropdown ─────────────────────────────────────────────
el.metodePembayaranSelect.addEventListener('change', onPaymentMethodChange);

// ─── Nominal Uang Input ──────────────────────────────────────────────────
el.nominalUang.addEventListener('input', function () {
    calculateKembalian();
    validatePayment();
});

if (el.uangMukaKredit) {
    el.uangMukaKredit.addEventListener('input', function () {
        calculateSisaPiutang();
    });
}

el.nominalUang.addEventListener('keydown', function (e) {
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


// ═══════════════════════════════════════════════════════════════════════════
// SECTION 14: INITIALIZATION
// ═══════════════════════════════════════════════════════════════════════════

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
    const select = el.pelangganSelect;
    if (select) {
        select.value = '';
        state.customer = { id: null, nama: 'Umum', status: 'Umum' };
        
        // Update UI
        el.tierBadge.className = 'badge badge-tier-umum';
        el.tierBadge.textContent = 'Umum';
    }
    
    // Reset payment inputs
    if (el.nominalUang) el.nominalUang.value = '';
    
    // Default method
    if (el.metodePembayaranSelect) el.metodePembayaranSelect.value = 'Tunai';
    state.metodePembayaran = 'Tunai';
    
    renderCartItems();
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
        diskon: 0
    };
    
    let holds = JSON.parse(localStorage.getItem('pos_held_transactions')) || [];
    holds.push(holdData);
    localStorage.setItem('pos_held_transactions', JSON.stringify(holds));
    
    initHoldCount();
    
    // Implicit cancel to clear UI
    state.cart = [];
    state.nextRowId = 1;
    const select = el.pelangganSelect;
    if (select) {
        select.value = '';
        state.customer = { id: null, nama: 'Umum', status: 'Umum' };
        el.tierBadge.className = 'badge badge-tier-umum';
        el.tierBadge.textContent = 'Umum';
    }
    
    if (el.nominalUang) el.nominalUang.value = '';
    if (el.metodePembayaranSelect) el.metodePembayaranSelect.value = 'Tunai';
    state.metodePembayaran = 'Tunai';
    
    renderCartItems();
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
    
    // Update UI for customer
    const select = el.pelangganSelect;
    if (select) {
        select.value = state.customer.id || '';
        el.tierBadge.className = `badge ${TIER_BADGE_CLASS[state.customer.status] || 'badge-tier-umum'}`;
        el.tierBadge.textContent = state.customer.status;
    }
    
    renderCartItems();
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
    // Set date
    const today = new Date().toLocaleDateString('id-ID', {
        weekday: 'short',
        day    : '2-digit',
        month  : '2-digit',
        year   : 'numeric',
    });
    el.posDate.textContent = today;

    // Start clock
    function updateClock() {
        el.posClock.textContent = new Date().toLocaleTimeString('id-ID', {
            hour   : '2-digit',
            minute : '2-digit',
            second : '2-digit',
        });
    }
    updateClock();
    setInterval(updateClock, 1000);

    // Populate select
    populatePelangganDropdown();
    
    initHoldCount();

    // Render category pills
    renderCategoryPills();

    // Render product grid
    renderProductGrid();

    // Render empty cart
    renderCartItems();

    // Initialize totals
    recalculateTotals();

    // Set default payment method
    onPaymentMethodChange();

    // Focus search input
    el.searchInput.focus();

    // Load items from scan page (if redirected from /pos/scan)
    loadScanItems();
}

/**
 * Read pos_scan_items from localStorage (set by /pos/scan page)
 * and add to cart.
 */
function loadScanItems() {
    try {
        const raw = localStorage.getItem('pos_scan_items');
        if (!raw) return;
        const items = JSON.parse(raw);
        if (!Array.isArray(items) || !items.length) return;

        let added = 0, skipped = 0;
        items.forEach(item => {
            const product = ALL_PRODUCTS.find(p => p.id === item.produk_id);
            if (!product) { skipped++; return; }

            const existingIndex = state.cart.findIndex(i => i.produk_id === item.produk_id);
            if (existingIndex >= 0) {
                state.cart[existingIndex].qty += item.qty;
                state.cart[existingIndex].subtotal = state.cart[existingIndex].harga_satuan * state.cart[existingIndex].qty;
            } else {
                state.cart.push({
                    rowId:        state.nextRowId++,
                    produk_id:    product.id,
                    barcode:      product.barcode,
                    nama_produk:  product.nama_produk,
                    satuan:       product.satuan || 'Pcs',
                    tier:         state.customer.status,
                    harga_satuan: parseFloat(item.harga) || parseFloat(product.harga_jual_umum) || 0,
                    qty:          item.qty,
                    subtotal:     (parseFloat(item.harga) || parseFloat(product.harga_jual_umum) || 0) * item.qty,
                    stok:         product.stok,
                });
            }
            added++;
        });

        localStorage.removeItem('pos_scan_items');
        renderCartItems();
        recalculateTotals();

        if (added > 0) {
            showSuccessToast(`${added} item dari scan AI ditambahkan ke keranjang` + (skipped > 0 ? ` (${skipped} dilewati)` : ''));
        }
    } catch (e) {
        console.error('Failed loading scan items:', e);
    }
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

// ── Voice Input POS Handler ──
window.addEventListener('pos-voice-apply', function(e) {
    const data = e.detail;
    console.log('[POS Voice] Event received. Data:', JSON.stringify(data));
    if (!data) { console.warn('[POS Voice] No data in event detail.'); return; }

    if (data.items && data.items.length > 0) {
        console.log('[POS Voice] Processing', data.items.length, 'items...');
        data.items.forEach(function(item, idx) {
            console.log('[POS Voice] Item', idx, ':', JSON.stringify(item));
            if (item.produk_id) {
                const pId = parseInt(item.produk_id, 10);
                const qty = parseInt(item.qty, 10) || 1;

                // Find product in ALL_PRODUCTS with loose comparison
                const product = ALL_PRODUCTS.find(function(p) { return parseInt(p.id, 10) === pId; });
                if (!product) {
                    console.warn('[POS Voice] Product ID', pId, 'not found in ALL_PRODUCTS (' + ALL_PRODUCTS.length + ' products)');
                    return;
                }

                console.log('[POS Voice] Found product:', product.nama_produk, '| Adding qty:', qty);

                // Directly manipulate the cart state for reliability
                const priceCol = (typeof getPriceColumn === 'function') ? getPriceColumn() : 'harga_jual_umum';
                const harga = parseFloat(product[priceCol]) || 0;
                const existingIndex = state.cart.findIndex(function(c) { return parseInt(c.produk_id, 10) === pId; });

                if (existingIndex >= 0) {
                    state.cart[existingIndex].qty += qty;
                    state.cart[existingIndex].subtotal = state.cart[existingIndex].harga_satuan * state.cart[existingIndex].qty;
                    console.log('[POS Voice] Updated existing cart item. New qty:', state.cart[existingIndex].qty);
                } else {
                    state.cart.push({
                        rowId: state.nextRowId++,
                        produk_id: product.id,
                        barcode: product.barcode,
                        nama_produk: product.nama_produk,
                        satuan: product.satuan || 'Pcs',
                        tier: state.customer.status,
                        harga_satuan: harga,
                        qty: qty,
                        subtotal: harga * qty,
                        stok: product.stok,
                    });
                    console.log('[POS Voice] Added new cart item. Cart size:', state.cart.length);
                }
            }
        });

        // Re-render cart and recalculate totals
        if (typeof renderCartItems === 'function') {
            renderCartItems();
            console.log('[POS Voice] renderCartItems() called.');
        }
        if (typeof recalculateTotals === 'function') {
            recalculateTotals();
            console.log('[POS Voice] recalculateTotals() called.');
        }
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

    console.log('[POS Voice] Handler complete. Cart state:', JSON.stringify(state.cart));
});

document.addEventListener('DOMContentLoaded', function() {
    initPOSTheme();
    initPOS();
});
</script>

@include('partials.voice-transaction-modal')

</body>
</html>
