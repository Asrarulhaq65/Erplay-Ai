<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0D4E56">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="ERPlay ERP">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.ico') }}">
    <title>@yield('title', 'Dashboard') — {{ config('app.name', 'Retail ERP') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    @stack('styles')
    <style>
        :root {
            /* ── Glass Base ───────────────────────────── */
            --glass-bg        : 255,255,255;
            --glass-blur      : 14px;
            --glass-border    : rgba(255,255,255,0.35);
            --glass-shadow    : 0 4px 24px rgba(0,0,0,0.06);
            /* ── Surface ──────────────────────────────── */
            --bg-body         : #EEF2F7;
            --bg-card         : rgba(255,255,255,0.95);
            --bg-card-hover   : rgba(255,255,255,1);
            --bg-input        : rgba(248,251,253,0.95);
            /* ── Text ─────────────────────────────────── */
            --text-primary    : #1E293B;
            --text-secondary  : #64748B;
            --text-muted      : #94A3B8;
            /* ── Borders ──────────────────────────────── */
            --border-light    : rgba(180,195,210,0.35);
            --border-medium   : rgba(150,170,190,0.4);
            /* ── Brand (teal) ─────────────────────────── */
            --pb-lightest : #E3ECF1;
            --pb-light    : #D0DDE6;
            --pb-mid      : #8CADBA;
            --pb-accent   : #5BA0AD;
            --pb-dark     : #0D4E56;
            --pb-darker   : #0A3C42;
            --pb-text     : #0D3C3F;
            /* ── Sidebar ──────────────────────────────── */
            --sidebar-bg        : rgba(11,46,53,0.88);
            --sidebar-blur      : 18px;
            --sidebar-hover     : rgba(255,255,255,0.08);
            --sidebar-active    : rgba(255,255,255,0.12);
            --sidebar-accent    : #D4842A;
            --sidebar-text      : #B0C8D2;
            --sidebar-icon      : #6E929C;
            --sidebar-border    : rgba(255,255,255,0.06);
            /* ── Layout ───────────────────────────────── */
            --topbar-h          : 52px;
            --sidebar-w         : 240px;
            --sidebar-collapsed : 64px;
            /* ── Transition ───────────────────────────── */
            --theme-transition  : background 0.2s ease, color 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
        }

        /* ═══════════════ DARK MODE ═══════════════ */
        [data-theme="dark"] {
            --glass-bg        : 30,41,59;
            --glass-blur      : 18px;
            --glass-border    : rgba(255,255,255,0.06);
            --glass-shadow    : 0 4px 24px rgba(0,0,0,0.3);
            --bg-body         : #0F172A;
            --bg-card         : rgba(30,41,59,0.92);
            --bg-card-hover   : rgba(30,41,59,0.94);
            --bg-input        : rgba(15,23,42,0.5);
            --text-primary    : #E2E8F0;
            --text-secondary  : #94A3B8;
            --text-muted      : #8898AA;
            --border-light    : rgba(71,85,105,0.4);
            --border-medium   : rgba(71,85,105,0.5);
            --pb-lightest : #1E293B;
            --pb-light    : #2D3A4A;
            --pb-mid      : #475569;
            --pb-accent   : #4DB8C4;
            --pb-dark     : #38A0AD;
            --pb-darker   : #2D8D99;
            --pb-text     : #E2E8F0;
            --sidebar-bg        : rgba(8,22,30,0.94);
            --sidebar-blur      : 22px;
            --sidebar-hover     : rgba(255,255,255,0.06);
            --sidebar-active    : rgba(255,255,255,0.1);
            --sidebar-accent    : #F0A04B;
            --sidebar-text      : #94A3B8;
            --sidebar-icon      : #64748B;
            --sidebar-border    : rgba(255,255,255,0.04);
        }

        *,*::before,*::after{box-sizing:border-box}

        /* ── Accessibility: Focus ring ───────────────── */
        :focus-visible {
            outline: 2px solid var(--pb-accent);
            outline-offset: 2px;
        }
        button:focus-visible, a:focus-visible, input:focus-visible, select:focus-visible {
            outline: 2px solid var(--pb-accent);
            outline-offset: 2px;
            border-radius: 4px;
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }

        /* Skip-to-content link */
        .skip-link {
            position: absolute; top: -100px; left: 10px;
            background: var(--pb-dark); color: #fff; padding: 8px 16px;
            border-radius: 0 0 8px 8px; z-index: 9999; font-size: 13px;
            text-decoration: none; font-weight: 600;
        }
        .skip-link:focus { top: 0; }

        html { transition: color-scheme 0.35s; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 13px;
            background: var(--bg-body);
            color: var(--text-primary);
            min-height: 100dvh;
            margin: 0;
            padding-top: var(--topbar-h);
            padding-left: var(--sidebar-w);
            transition: padding-left 0.25s cubic-bezier(0.4,0,0.2,1), var(--theme-transition);
        }
        body.sidebar-collapsed {
            padding-left: var(--sidebar-collapsed);
        }

        /* ═══════════════ TOPBAR ═══════════════ */
        .erp-topbar {
            position: fixed; top: 0; left: 0; right: 0; z-index: 1040;
            height: var(--topbar-h);
            background: rgba(var(--glass-bg), 0.95);
            border-bottom: 1px solid var(--border-light);
            display: flex; align-items: center;
            padding: 0 20px; gap: 16px;
            box-shadow: 0 1px 8px rgba(0,0,0,0.04);
            transition: var(--theme-transition);
        }
        .topbar-toggle {
            width: 44px; height: 44px;
            border: 1px solid var(--border-light); border-radius: 10px;
            background: rgba(var(--glass-bg), 0.5);
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            color: var(--text-secondary); font-size: 20px;
            transition: all 0.2s; flex-shrink: 0;
        }
        .topbar-toggle:hover { background: var(--bg-card-hover); color: var(--pb-dark); border-color: var(--pb-mid); }
        .topbar-brand {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 700; font-size: 14px; color: var(--pb-text);
            white-space: nowrap; letter-spacing: -0.2px;
        }
        .topbar-brand i { color: var(--pb-dark); font-size: 18px; margin-right: 6px; }
        .topbar-search {
            flex: 1; max-width: 420px; margin: 0 auto;
            position: relative;
        }
        .topbar-search input {
            width: 100%; height: 34px; padding: 0 14px 0 36px;
            border: 1px solid var(--border-light); border-radius: 10px;
            background: var(--bg-input);
            font-size: 12px; color: var(--text-primary);
            outline: none; transition: all 0.2s;
        }
        .topbar-search input::placeholder { color: var(--text-muted); }
        .topbar-search input:focus { border-color: var(--pb-accent); box-shadow: 0 0 0 3px rgba(91,160,173,0.12); }
        .topbar-search i {
            position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
            color: var(--text-muted); font-size: 14px;
        }
        .topbar-actions {
            display: flex; align-items: center; gap: 6px; flex-shrink: 0;
        }
        .topbar-btn {
            width: 44px; height: 44px; border-radius: 10px;
            border: 1px solid transparent; background: transparent;
            cursor: pointer; display: flex; align-items: center; justify-content: center;
            color: var(--text-secondary); font-size: 18px; transition: all 0.2s;
        }
        .topbar-btn:hover { background: var(--bg-card-hover); color: var(--pb-dark); border-color: var(--border-light); }
        .topbar-user {
            display: flex; align-items: center; gap: 8px;
            padding: 4px 10px 4px 4px; border-radius: 10px;
            cursor: pointer; transition: all 0.2s; border: 1px solid transparent;
        }
        .topbar-user:hover { background: var(--bg-card-hover); border-color: var(--border-light); }
        .topbar-avatar {
            width: 32px; height: 32px; border-radius: 10px;
            background: var(--pb-dark); color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-size: 13px; font-weight: 700;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .topbar-user-info { line-height: 1.2; text-align: left; }
        .topbar-user-name { font-size: 12px; font-weight: 600; color: var(--text-primary); }
        .topbar-user-role { font-size: 10px; color: var(--text-muted); }

        /* ═══════════════ SIDEBAR ═══════════════ */
        .erp-sidebar {
            position: fixed; left: 0; top: var(--topbar-h); bottom: 0;
            width: var(--sidebar-w); z-index: 1030;
            background: var(--sidebar-bg);
            backdrop-filter: blur(var(--sidebar-blur));
            -webkit-backdrop-filter: blur(var(--sidebar-blur));
            border-right: 1px solid var(--sidebar-border);
            display: flex; flex-direction: column;
            overflow: hidden;
            transition: width 0.25s cubic-bezier(0.4,0,0.2,1), var(--theme-transition);
        }
        body.sidebar-collapsed .erp-sidebar { width: var(--sidebar-collapsed); }
        .sidebar-scroll {
            flex: 1; overflow-y: auto; overflow-x: hidden;
            padding: 12px 0;
        }
        .sidebar-section {
            padding: 0 12px; margin-bottom: 4px;
        }
        .sidebar-section-label {
            font-size: 10px; font-weight: 600; color: var(--sidebar-icon);
            padding: 12px 12px 6px 16px;
            white-space: nowrap; overflow: hidden;
        }
        body.sidebar-collapsed .sidebar-section-label { display: none; }

        .sidebar-item {
            display: flex; align-items: center; gap: 10px;
            padding: 8px 12px; margin: 1px 0;
            min-height: 44px;
            border-radius: 8px; cursor: pointer;
            color: var(--sidebar-text); font-size: 12px; font-weight: 500;
            text-decoration: none; white-space: nowrap;
            transition: all 0.12s; position: relative;
            border-left: 3px solid transparent;
        }
        .sidebar-item:hover {
            background: var(--sidebar-hover); color: #fff;
            text-decoration: none;
        }
        .sidebar-item.active {
            background: var(--sidebar-active); color: #fff;
            border-left-color: var(--sidebar-accent);
            border-radius: 0 8px 8px 0; padding-left: 15px;
        }
        .sidebar-item i.side-icon {
            font-size: 16px; width: 20px; text-align: center;
            flex-shrink: 0; color: var(--sidebar-icon);
        }
        .sidebar-item:hover i.side-icon,
        .sidebar-item.active i.side-icon { color: #fff; }
        .sidebar-item.active i.side-icon { color: var(--sidebar-accent); }
        .sidebar-item-text {
            flex: 1; overflow: hidden;
        }
        .sidebar-item .submenu-arrow {
            margin-left: auto; font-size: 11px; transition: transform 0.2s;
            flex-shrink: 0;
        }
        .sidebar-item[aria-expanded="true"] .submenu-arrow { transform: rotate(90deg); }

        .sidebar-submenu {
            overflow: hidden;
            padding-left: 32px;
            margin: 0;
        }
        .sidebar-submenu .sidebar-item {
            font-size: 11px; padding: 6px 10px;
            border-left: none;
        }
        .sidebar-submenu .sidebar-item.active {
            background: var(--sidebar-active);
            border-left: 3px solid var(--sidebar-accent);
            border-radius: 0 6px 6px 0; padding-left: 13px;
        }
        .sidebar-submenu .sidebar-item i.side-icon { font-size: 14px; width: 16px; }

        .sidebar-footer {
            border-top: 1px solid var(--sidebar-border);
            padding: 8px 12px;
        }
        .sidebar-footer .sidebar-item {
            color: var(--sidebar-icon); font-size: 11px;
        }
        .sidebar-footer .sidebar-item:hover { color: #fff; }

        /* Collapsed sidebar adjustments */
        body.sidebar-collapsed .sidebar-item {
            justify-content: center; padding: 8px 0;
            border-radius: 8px; margin: 2px 8px;
            border-left: none !important;
            padding-left: 0 !important;
        }
        body.sidebar-collapsed .sidebar-item-text,
        body.sidebar-collapsed .submenu-arrow,
        body.sidebar-collapsed .sidebar-submenu { display: none; }
        body.sidebar-collapsed .sidebar-section { padding: 0 4px; }
        body.sidebar-collapsed .sidebar-item i.side-icon { font-size: 18px; width: auto; }

        /* ═══════════════ MAIN CONTENT ═══════════════ */
        .erp-content {
            padding: 20px 24px 40px;
            min-height: calc(100dvh - var(--topbar-h));
            max-width: 1440px;
        }
        .erp-breadcrumb {
            font-size: 11px; color: var(--text-muted); margin-bottom: 16px;
        }
        .erp-breadcrumb a { color: var(--text-secondary); text-decoration: none; }
        .erp-breadcrumb a:hover { color: var(--pb-dark); }
        .erp-breadcrumb span { color: var(--text-primary); font-weight: 500; }

        /* ═══════════════ SHARED COMPONENTS ═══════════════ */
        .card-erp {
            border: 1px solid var(--border-light); border-radius: 12px;
            background: var(--bg-card);
            transition: var(--theme-transition);
        }
        .card-erp .card-header {
            background: rgba(var(--glass-bg), 0.4);
            border-bottom: 1px solid var(--border-light); padding: 8px 16px;
            border-radius: 12px 12px 0 0;
            display: flex; align-items: center; justify-content: space-between;
        }
        .card-erp .card-header .card-title {
            margin: 0; font-size: 13px; font-weight: 700; color: var(--pb-text);
        }
        .table thead th {
            background: rgba(var(--glass-bg), 0.4); color: var(--pb-text);
            font-size: 11px; font-weight: 600;
            border-bottom: 2px solid var(--border-medium);
            padding: 6px 10px; white-space: nowrap;
        }
        .table tbody td {
            font-size: 12px; vertical-align: middle; padding: 5px 10px;
            border-color: var(--border-light); color: var(--text-primary);
        }
        .table tbody tr { transition: background 0.15s; }
        .table tbody tr:hover { background: rgba(var(--glass-bg), 0.4); }
        .table { color: var(--text-primary); }
        .row-stok-rendah { background-color: rgba(200,60,60,0.08) !important; }
        .row-stok-rendah td { color: #C34A4A; }
        .row-stok-rendah:hover { background-color: rgba(200,60,60,0.14) !important; }
        .btn-pb {
            background: var(--pb-dark); border: none; color: #fff;
            font-size: 12px; font-weight: 600; padding: 4px 14px; border-radius: 8px;
            transition: all 0.2s;
        }
        .btn-pb:hover { background: var(--pb-darker); color: #fff; }
        .btn-pb:disabled { background: #94AAB0; }
        .form-label {
            font-size: 11px; font-weight: 600; color: var(--pb-text);
            margin-bottom: 3px; display: block;
        }
        .form-label .req { color: #dc3545; }
        .form-control-sm, .form-select-sm {
            font-size: 12px; border-color: var(--border-light);
            background: var(--bg-input); color: var(--text-primary);
            transition: var(--theme-transition);
        }
        .form-control-sm:focus, .form-select-sm:focus {
            border-color: var(--pb-accent);
            box-shadow: 0 0 0 0.15rem rgba(91,160,173,0.2);
        }
        .form-control, .form-select {
            background: var(--bg-input); color: var(--text-primary);
            border-color: var(--border-light);
        }
        .form-control:focus, .form-select:focus {
            background: var(--bg-card-hover); color: var(--text-primary);
            border-color: var(--pb-accent);
            box-shadow: 0 0 0 0.15rem rgba(91,160,173,0.2);
        }
        ::placeholder,
        .form-control::placeholder,
        .form-control-sm::placeholder {
            color: var(--text-secondary) !important;
            opacity: 0.85 !important;
        }
        .form-text { font-size: 10px; color: var(--text-muted); }
        .modal-content {
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--border-light);
            border-radius: 14px;
            box-shadow: 0 16px 48px rgba(0,0,0,0.15);
        }
        .modal-header {
            background: rgba(var(--glass-bg), 0.3);
            border-bottom: 1px solid var(--border-light); padding: 8px 16px;
            border-radius: 14px 14px 0 0;
        }
        .modal-title { font-size: 13px; font-weight: 700; color: var(--pb-text); }
        .modal-body { padding: 16px; font-size: 12px; color: var(--text-primary); }
        .modal-footer { padding: 8px 16px; border-top: 1px solid var(--border-light); gap: 6px; }
        .alert { font-size: 12px; padding: 6px 12px; border-radius: 10px; }
        .badge-tier-umum    { background: #6c757d; color: #fff; }
        .badge-tier-member  { background: #198754; color: #fff; }
        .badge-tier-rekan   { background: #D4842A; color: #fff; }
        .badge-tier-motoris { background: var(--pb-dark); color: #fff; }
        .pagination { font-size: 12px; margin: 0; }
        .page-link {
            padding: 3px 10px; color: var(--text-secondary);
            background: var(--bg-card); border-color: var(--border-light);
            transition: var(--theme-transition);
        }
        .page-link:hover { color: var(--pb-dark); background: var(--bg-card-hover); }
        .page-item.active .page-link { background: var(--pb-dark); border-color: var(--pb-dark); color: #fff; }
        .page-item.disabled .page-link { background: transparent; color: var(--text-muted); }
        .search-bar {
            padding: 8px 16px; border-bottom: 1px solid var(--border-light);
            background: var(--bg-input);
        }
        .dropdown-menu {
            background: var(--bg-card);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--border-light);
            box-shadow: 0 8px 32px rgba(0,0,0,0.12);
            border-radius: 10px;
        }
        .dropdown-item {
            color: var(--text-primary); font-size: 12px;
            transition: background 0.12s;
        }
        .dropdown-item:hover { background: rgba(var(--glass-bg), 0.5); color: var(--pb-dark); }
        .dropdown-divider { border-color: var(--border-light); }
        .text-muted { color: var(--text-muted) !important; }
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--pb-mid); border-radius: 4px; }

        /* ── Dark mode: fix Bootstrap default white backgrounds ── */
        [data-theme="dark"] .table {
            --bs-table-bg: transparent;
            --bs-table-color: var(--text-primary);
            --bs-table-border-color: var(--border-light);
            --bs-table-hover-bg: rgba(var(--glass-bg), 0.35);
            --bs-table-striped-bg: rgba(var(--glass-bg), 0.15);
            --bs-table-active-bg: rgba(var(--glass-bg), 0.25);
        }
        [data-theme="dark"] .card,
        [data-theme="dark"] .card-body,
        [data-theme="dark"] .list-group-item {
            background: var(--bg-card);
            color: var(--text-primary);
            border-color: var(--border-light);
        }
        [data-theme="dark"] .bg-white,
        [data-theme="dark"] .bg-light,
        [data-theme="dark"] .card-footer {
            background-color: var(--bg-card) !important;
        }
        [data-theme="dark"] .border-bottom {
            border-bottom-color: var(--border-light) !important;
        }
        [data-theme="dark"] .table-bordered,
        [data-theme="dark"] .table-bordered td,
        [data-theme="dark"] .table-bordered th {
            border-color: var(--border-light) !important;
        }
        [data-theme="dark"] .text-dark {
            color: var(--text-primary) !important;
        }
        [data-theme="dark"] .btn-close {
            filter: invert(1) grayscale(100%) brightness(200%);
        }
        [data-theme="dark"] .border {
            border-color: var(--border-light) !important;
        }
        [data-theme="dark"] .border-start,
        [data-theme="dark"] .border-end,
        [data-theme="dark"] .border-top,
        [data-theme="dark"] .border-bottom {
            --bs-border-color: var(--border-light);
        }
        /* ── Accordion System Fix for Dark & Light Mode ── */
        .accordion {
            --bs-accordion-bg: var(--bg-card);
            --bs-accordion-color: var(--text-primary);
            --bs-accordion-border-color: var(--border-light);
            --bs-accordion-btn-bg: var(--bg-card);
            --bs-accordion-btn-color: var(--pb-text);
            --bs-accordion-active-bg: var(--bg-card-hover);
            --bs-accordion-active-color: var(--pb-accent);
        }
        .accordion-item {
            background-color: var(--bg-card) !important;
            border-color: var(--border-light) !important;
            color: var(--text-primary) !important;
        }
        .accordion-button {
            background-color: var(--bg-card) !important;
            color: var(--pb-text) !important;
        }
        .accordion-button:not(.collapsed) {
            background-color: var(--bg-card-hover) !important;
            color: var(--pb-accent) !important;
            box-shadow: inset 0 -1px 0 var(--border-light) !important;
        }
        .accordion-body {
            background-color: var(--bg-card) !important;
            color: var(--text-primary) !important;
        }
        [data-theme="dark"] .accordion-button::after {
            filter: invert(1) grayscale(100%) brightness(200%);
        }
        [data-theme="dark"] .accordion-item,
        [data-theme="dark"] .accordion-body {
            background-color: var(--bg-card) !important;
            color: var(--text-primary) !important;
            border-color: var(--border-light) !important;
        }
        [data-theme="dark"] .accordion-button {
            background-color: var(--bg-card) !important;
            color: var(--text-primary) !important;
        }
        [data-theme="dark"] .accordion-button:not(.collapsed) {
            background-color: rgba(255,255,255,0.08) !important;
            color: var(--pb-accent) !important;
        }
        [data-theme="dark"] .table thead th {
            background: rgba(255,255,255,0.05);
            color: #F1F5F9;
            border-bottom-color: rgba(255,255,255,0.08);
        }

        /* ── Back-to-top FAB ──────────────────────────────── */
        #backToTop {
            position: fixed; bottom: 24px; right: 24px; z-index: 1000;
            width: 40px; height: 40px; border-radius: 50%;
            background: var(--pb-dark); color: #fff; border: none;
            cursor: pointer; display: flex; align-items: center; justify-content: center;
            font-size: 18px; opacity: 0; visibility: hidden;
            transform: translateY(10px);
            transition: opacity 0.25s, transform 0.25s, visibility 0.25s;
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        }
        #backToTop.visible { opacity: 1; visibility: visible; transform: translateY(0); }
        #backToTop:hover { background: var(--pb-darker); }
    </style>
</head>
<body>
<a href="#main-content" class="skip-link">Lewati ke konten utama</a>

{{-- ═══════════════ TOPBAR ═══════════════ --}}
<header class="erp-topbar">
    <button class="topbar-toggle" id="sidebarToggle" title="Toggle Sidebar" aria-label="Toggle Sidebar">
        <i class="bi bi-list" aria-hidden="true"></i>
    </button>
    <a href="{{ url('/dashboard') }}" class="topbar-brand text-decoration-none">
        @if(auth()->user()?->toko?->logo)
            <img src="{{ asset('storage/' . auth()->user()->toko->logo) }}" alt="Logo {{ auth()->user()->toko->nama_toko }}" style="height:22px;width:auto;object-fit:contain;margin-right:6px;" loading="lazy">
        @else
            <i class="bi bi-shop-window"></i>
        @endif
        {{ auth()->user()?->toko?->nama_toko ?? config('app.name', 'Retail ERP') }}
    </a>

    <div class="topbar-search">
        <i class="bi bi-search"></i>
        <input type="text" placeholder="Cari... (Ctrl+K)" aria-label="Pencarian global">
    </div>

    <div class="topbar-actions">
        <button class="topbar-btn" id="darkModeToggle" title="Tema Gelap / Terang" aria-label="Toggle Dark Mode">
            <i class="bi bi-moon-stars" id="darkModeIcon" aria-hidden="true"></i>
        </button>
        <a href="{{ route('admin.self-service.index') }}" class="topbar-btn position-relative" title="Pesanan Self-Service" aria-label="Pesanan Self-Service">
            <i class="bi bi-bell" aria-hidden="true"></i>
            <span id="globalSelfServiceBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display:none;font-size:9px;">0</span>
        </a>

        <div class="topbar-user dropdown">
            <div class="topbar-user" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="topbar-avatar">
                    {{ strtoupper(substr(auth()->user()?->nama_lengkap ?? 'U', 0, 2)) }}
                </div>
                <div class="topbar-user-info">
                    <div class="topbar-user-name">{{ auth()->user()?->nama_lengkap ?? 'User' }}</div>
                    <div class="topbar-user-role">{{ auth()->user()?->role?->nama_role ?? 'Staff' }}</div>
                </div>
                <i class="bi bi-chevron-down" style="font-size:10px;color:var(--text-muted);margin-left:4px;"></i>
            </div>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><span class="dropdown-item-text" style="font-size:11px;color:var(--text-muted);">{{ auth()->user()?->toko?->nama_toko ?? 'Toko' }}</span></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger" style="font-size:12px;">
                            <i class="bi bi-box-arrow-right me-2"></i>Keluar
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>

{{-- ═══════════════ SIDEBAR ═══════════════ --}}
<aside class="erp-sidebar" id="erpSidebar" role="navigation" aria-label="Sidebar Navigation">
    @php
        $role = auth()->user()?->role?->nama_role;
        $isSuperAdmin = $role === 'Super Admin';
        $isOwner      = $role === 'Owner';
        $isAdminToko  = $role === 'Admin Toko';
        $isKasir      = $role === 'Kasir';
        $isGudang     = $role === 'Gudang';
    @endphp

    <div class="sidebar-scroll">
        <div class="sidebar-section">
            <a href="{{ url('/dashboard') }}" class="sidebar-item {{ request()->is('dashboard*') ? 'active' : '' }}" aria-label="Dashboard">
                <i class="bi bi-grid-1x2 side-icon"></i>
                <span class="sidebar-item-text">Dashboard</span>
            </a>
            @if(auth()->user()?->toko?->catalog_slug)
            <a href="{{ route('katalog.index', auth()->user()->toko->catalog_slug) }}" class="sidebar-item" target="_blank" rel="noopener" aria-label="Buka Katalog Publik">
                <i class="bi bi-globe2 side-icon"></i>
                <span class="sidebar-item-text">Katalog Publik</span>
                <i class="bi bi-box-arrow-up-right ms-auto" style="font-size:11px;"></i>
            </a>
            <a href="{{ route('pengaturan.katalog.edit') }}" class="sidebar-item {{ request()->routeIs('pengaturan.katalog*') ? 'active' : '' }}" aria-label="Pengaturan Katalog Publik">
                <i class="bi bi-sliders side-icon"></i>
                <span class="sidebar-item-text">Setting Katalog</span>
            </a>
            @endif
        </div>

        @if($isSuperAdmin || $isOwner || $isAdminToko || $isKasir)
        <div class="sidebar-section">
            <a class="sidebar-item {{ request()->is('pos*') ? 'active' : '' }}"
               data-bs-toggle="collapse" href="#submenuPos" role="button" aria-expanded="{{ request()->is('pos*') ? 'true' : 'false' }}"
               aria-label="Point of Sale">
                <i class="bi bi-lightning-charge side-icon"></i>
                <span class="sidebar-item-text">Point of Sale</span>
                <i class="bi bi-chevron-right submenu-arrow"></i>
            </a>
            <div class="collapse sidebar-submenu {{ request()->is('pos*') || request()->is('admin/self-service*') ? 'show' : '' }}" id="submenuPos">
                <a href="{{ url('/pos/custom') }}" class="sidebar-item {{ request()->is('pos/custom*') ? 'active' : '' }}" aria-label="POS Mode Kilat" target="_blank" rel="noopener noreferrer">
                    <i class="bi bi-keyboard side-icon"></i>
                    <span class="sidebar-item-text">Mode Kilat</span>
                </a>
                <a href="{{ url('/pos/standard') }}" class="sidebar-item {{ request()->is('pos/standard*') ? 'active' : '' }}" aria-label="POS Mode Standar" target="_blank" rel="noopener noreferrer">
                    <i class="bi bi-grid-3x3-gap side-icon"></i>
                    <span class="sidebar-item-text">Mode Standar</span>
                </a>
                <a href="{{ route('admin.self-service.index') }}" class="sidebar-item {{ request()->is('admin/self-service*') ? 'active' : '' }}" aria-label="Verifikasi Self Service">
                    <i class="bi bi-qr-code-scan side-icon"></i>
                    <span class="sidebar-item-text">Pesanan Self-Service</span>
                </a>
            </div>

        </div>
        @endif

        @if($isSuperAdmin || $isOwner || $isAdminToko || $isKasir || $isGudang)
        <div class="sidebar-section">
            <div class="sidebar-section-label">Master Data</div>
            <a class="sidebar-item {{ request()->is('master*') && !request()->is('master/pelanggan*') && !request()->is('master/supplier*') ? 'active' : '' }}"
               data-bs-toggle="collapse" href="#submenuMaster" role="button" aria-expanded="{{ request()->is('master*') ? 'true' : 'false' }}"
               aria-label="Master Data Produk">
                <i class="bi bi-database side-icon"></i>
                <span class="sidebar-item-text">Produk</span>
                <i class="bi bi-chevron-right submenu-arrow"></i>
            </a>
            <div class="collapse sidebar-submenu {{ request()->is('master*') && !request()->is('master/pelanggan*') && !request()->is('master/supplier*') ? 'show' : '' }}" id="submenuMaster">
                <a href="{{ route('master.kelompok-produk.index') }}" class="sidebar-item {{ request()->routeIs('master.kelompok*') ? 'active' : '' }}" aria-label="Kelompok Produk">
                    <i class="bi bi-collection side-icon"></i>
                    <span class="sidebar-item-text">Kelompok</span>
                </a>
                <a href="{{ route('master.kategori-produk.index') }}" class="sidebar-item {{ request()->routeIs('master.kategori*') ? 'active' : '' }}" aria-label="Kategori Produk">
                    <i class="bi bi-tag side-icon"></i>
                    <span class="sidebar-item-text">Kategori</span>
                </a>
                <a href="{{ route('master.produk.index') }}" class="sidebar-item {{ request()->routeIs('master.produk*') ? 'active' : '' }}" aria-label="Master Produk">
                    <i class="bi bi-box-seam side-icon"></i>
                    <span class="sidebar-item-text">Master Produk</span>
                </a>
            </div>

            @if($isSuperAdmin || $isOwner || $isAdminToko)
            <a class="sidebar-item {{ request()->is('master/pelanggan*') || request()->is('master/supplier*') ? 'active' : '' }}"
               data-bs-toggle="collapse" href="#submenuRelasi" role="button" aria-expanded="{{ request()->is('master/pelanggan*') || request()->is('master/supplier*') ? 'true' : 'false' }}"
               aria-label="Relasi Pelanggan dan Supplier">
                <i class="bi bi-people side-icon"></i>
                <span class="sidebar-item-text">Relasi</span>
                <i class="bi bi-chevron-right submenu-arrow"></i>
            </a>
            <div class="collapse sidebar-submenu {{ request()->is('master/pelanggan*') || request()->is('master/supplier*') ? 'show' : '' }}" id="submenuRelasi">
                <a href="{{ route('master.pelanggan.index') }}" class="sidebar-item {{ request()->routeIs('master.pelanggan*') ? 'active' : '' }}" aria-label="Master Pelanggan">
                    <i class="bi bi-person-vcard side-icon"></i>
                    <span class="sidebar-item-text">Pelanggan</span>
                </a>
                <a href="{{ route('master.supplier.index') }}" class="sidebar-item {{ request()->routeIs('master.supplier*') ? 'active' : '' }}" aria-label="Master Supplier">
                    <i class="bi bi-truck side-icon"></i>
                    <span class="sidebar-item-text">Supplier</span>
                </a>
            </div>
            @endif
        </div>
        @endif

        @if($isSuperAdmin || $isOwner || $isAdminToko || $isGudang)
        <div class="sidebar-section">
            <a class="sidebar-item {{ request()->is('pembelian*') ? 'active' : '' }}"
               data-bs-toggle="collapse" href="#submenuPembelian" role="button" aria-expanded="{{ request()->is('pembelian*') ? 'true' : 'false' }}"
               aria-label="Pembelian">
                <i class="bi bi-cart-plus side-icon"></i>
                <span class="sidebar-item-text">Pembelian</span>
                <i class="bi bi-chevron-right submenu-arrow"></i>
            </a>
            <div class="collapse sidebar-submenu {{ request()->is('pembelian*') ? 'show' : '' }}" id="submenuPembelian">
                <a href="{{ route('pembelian.create') }}" class="sidebar-item {{ request()->routeIs('pembelian.create') ? 'active' : '' }}" aria-label="Input Pembelian">
                    <i class="bi bi-plus-circle side-icon"></i>
                    <span class="sidebar-item-text">Input Pembelian</span>
                </a>
                <a href="{{ route('pembelian.index') }}" class="sidebar-item {{ request()->routeIs('pembelian.index') || request()->routeIs('pembelian.show') ? 'active' : '' }}" aria-label="Riwayat Pembelian">
                    <i class="bi bi-clock-history side-icon"></i>
                    <span class="sidebar-item-text">Riwayat</span>
                </a>
            </div>

            <a class="sidebar-item {{ request()->is('inventory*') ? 'active' : '' }}"
               data-bs-toggle="collapse" href="#submenuInventory" role="button" aria-expanded="{{ request()->is('inventory*') ? 'true' : 'false' }}"
               aria-label="Inventory">
                <i class="bi bi-box-seam side-icon"></i>
                <span class="sidebar-item-text">Inventory</span>
                <i class="bi bi-chevron-right submenu-arrow"></i>
            </a>
            <div class="collapse sidebar-submenu {{ request()->is('inventory*') ? 'show' : '' }}" id="submenuInventory">
                <a href="{{ route('inventory.opname.index') }}" class="sidebar-item {{ request()->routeIs('inventory.opname*') ? 'active' : '' }}" aria-label="Stock Opname">
                    <i class="bi bi-pencil-square side-icon"></i>
                    <span class="sidebar-item-text">Stock Opname</span>
                </a>
            </div>
        </div>
        @endif

        @if($isSuperAdmin || $isOwner || $isAdminToko || $isKasir)
        <div class="sidebar-section">
            <a class="sidebar-item {{ request()->is('laporan*') ? 'active' : '' }}"
               data-bs-toggle="collapse" href="#submenuLaporan" role="button" aria-expanded="{{ request()->is('laporan*') ? 'true' : 'false' }}"
               aria-label="Laporan">
                <i class="bi bi-bar-chart side-icon"></i>
                <span class="sidebar-item-text">Laporan</span>
                <i class="bi bi-chevron-right submenu-arrow"></i>
            </a>
            <div class="collapse sidebar-submenu {{ request()->is('laporan*') ? 'show' : '' }}" id="submenuLaporan">
                @if($isSuperAdmin || $isOwner || $isAdminToko)
                <a href="{{ route('laporan.analytics') }}" class="sidebar-item {{ request()->routeIs('laporan.analytics') ? 'active' : '' }}" aria-label="Analytics Dashboard">
                    <i class="bi bi-pie-chart side-icon"></i>
                    <span class="sidebar-item-text">Analytics</span>
                </a>
                @endif
                <a href="{{ route('laporan.rekap-penjualan') }}" class="sidebar-item {{ request()->routeIs('laporan.rekap-penjualan') ? 'active' : '' }}" aria-label="Rekap Penjualan">
                    <i class="bi bi-receipt side-icon"></i>
                    <span class="sidebar-item-text">Rekap Penjualan</span>
                </a>
            </div>
        </div>
        @endif

        @if($isSuperAdmin || $isOwner || $isAdminToko)
        <div class="sidebar-section">
            <a class="sidebar-item {{ request()->is('akuntansi*') ? 'active' : '' }}"
               data-bs-toggle="collapse" href="#submenuAkuntansi" role="button" aria-expanded="{{ request()->is('akuntansi*') ? 'true' : 'false' }}"
               aria-label="Akuntansi">
                <i class="bi bi-journal-bookmark side-icon"></i>
                <span class="sidebar-item-text">Akuntansi</span>
                <i class="bi bi-chevron-right submenu-arrow"></i>
            </a>
            <div class="collapse sidebar-submenu {{ request()->is('akuntansi*') ? 'show' : '' }}" id="submenuAkuntansi">
                <a href="{{ route('akuntansi.accounts.index') }}" class="sidebar-item {{ request()->routeIs('akuntansi.accounts*') ? 'active' : '' }}" aria-label="Chart of Accounts">
                    <i class="bi bi-list-nested side-icon"></i>
                    <span class="sidebar-item-text">Daftar Akun (COA)</span>
                </a>
                <a href="{{ route('akuntansi.jurnal.index') }}" class="sidebar-item {{ request()->routeIs('akuntansi.jurnal*') ? 'active' : '' }}" aria-label="Jurnal Umum">
                    <i class="bi bi-journal-text side-icon"></i>
                    <span class="sidebar-item-text">Jurnal Umum</span>
                </a>
                <a href="{{ route('akuntansi.buku-besar') }}" class="sidebar-item {{ request()->routeIs('akuntansi.buku-besar') ? 'active' : '' }}" aria-label="Buku Besar">
                    <i class="bi bi-book-half side-icon"></i>
                    <span class="sidebar-item-text">Buku Besar</span>
                </a>
                <a href="{{ route('akuntansi.laba-rugi') }}" class="sidebar-item {{ request()->routeIs('akuntansi.laba-rugi') ? 'active' : '' }}" aria-label="Laporan Laba Rugi">
                    <i class="bi bi-pie-chart-fill side-icon"></i>
                    <span class="sidebar-item-text">Laba Rugi</span>
                </a>
            </div>
        </div>
        @endif

        @if($isSuperAdmin || $isOwner || $isAdminToko)
        <div class="sidebar-section">
            <a class="sidebar-item {{ request()->is('pengaturan*') || request()->is('langganan*') ? 'active' : '' }}"
               data-bs-toggle="collapse" href="#submenuPengaturan" role="button" aria-expanded="{{ request()->is('pengaturan*') || request()->is('langganan*') ? 'true' : 'false' }}"
               aria-label="Pengaturan">
                <i class="bi bi-gear side-icon"></i>
                <span class="sidebar-item-text">Pengaturan</span>
                <i class="bi bi-chevron-right submenu-arrow"></i>
            </a>
            <div class="collapse sidebar-submenu {{ request()->is('pengaturan*') || request()->is('langganan*') ? 'show' : '' }}" id="submenuPengaturan">
                <a href="{{ route('pengaturan.users.index') }}" class="sidebar-item {{ request()->routeIs('pengaturan.users*') ? 'active' : '' }}" aria-label="Manajemen User">
                    <i class="bi bi-people side-icon"></i>
                    <span class="sidebar-item-text">Manajemen User</span>
                </a>
                <a href="{{ route('pengaturan.toko.edit') }}" class="sidebar-item {{ request()->routeIs('pengaturan.toko*') ? 'active' : '' }}" aria-label="Manajemen Toko">
                    <i class="bi bi-shop side-icon"></i>
                    <span class="sidebar-item-text">Manajemen Toko</span>
                </a>
                <a href="{{ route('pengaturan.ai.index') }}" class="sidebar-item {{ request()->routeIs('pengaturan.ai*') ? 'active' : '' }}" aria-label="Integrasi AI & Vision">
                    <i class="bi bi-robot side-icon text-warning"></i>
                    <span class="sidebar-item-text">Integrasi AI & Vision</span>
                </a>
                <a href="{{ route('pengaturan.audit-log.index') }}" class="sidebar-item {{ request()->routeIs('pengaturan.audit-log*') ? 'active' : '' }}" aria-label="Audit Log System">
                    <i class="bi bi-shield-check side-icon"></i>
                    <span class="sidebar-item-text">Audit Log</span>
                </a>
                <a href="{{ route('langganan.status') }}" class="sidebar-item {{ request()->routeIs('langganan.status') ? 'active' : '' }}" aria-label="Status Langganan">
                    <i class="bi bi-shield-check side-icon"></i>
                    <span class="sidebar-item-text">Langganan</span>
                </a>
                @if($isSuperAdmin)
                <a href="{{ route('cms.toko.index') }}" class="sidebar-item" aria-label="CMS SaaS Master">
                    <i class="bi bi-shield-lock side-icon"></i>
                    <span class="sidebar-item-text">CMS SaaS</span>
                </a>
                @endif
            </div>
        </div>
        @endif
    </div>
</aside>

{{-- ═══════════════ MAIN CONTENT ═══════════════ --}}
<main class="erp-content" id="main-content">
    @if (trim($__env->yieldContent('breadcrumb')))
    <nav class="erp-breadcrumb" aria-label="Breadcrumb">
        @yield('breadcrumb')
    </nav>
    @endif
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <i class="bi bi-check-circle-fill me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <i class="bi bi-exclamation-circle-fill me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show mb-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ session('warning') }}
            <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @yield('content')
</main>

@include('partials.confirm-modal')
@include('partials.ai-copilot-widget')
@include('partials.voice-transaction-modal')

{{-- Back-to-top FAB --}}
<button id="backToTop" title="Kembali ke atas" aria-label="Kembali ke atas">
    <i class="bi bi-chevron-up"></i>
</button>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function(){
    const html = document.documentElement;
    const body = document.body;

    /* ═══════════ DARK MODE ═══════════ */
    const DM_KEY = 'erp_theme';
    const toggleBtn = document.getElementById('darkModeToggle');
    const toggleIcon = document.getElementById('darkModeIcon');

    function applyTheme(theme) {
        if (theme === 'dark') {
            html.setAttribute('data-theme', 'dark');
            toggleIcon.className = 'bi bi-sun';
        } else {
            html.removeAttribute('data-theme');
            toggleIcon.className = 'bi bi-moon-stars';
        }
    }

    function getPreferredTheme() {
        const stored = localStorage.getItem(DM_KEY);
        if (stored === 'dark' || stored === 'light') return stored;
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }

    function toggleTheme() {
        const current = html.hasAttribute('data-theme') ? 'dark' : 'light';
        const next = current === 'dark' ? 'light' : 'dark';
        localStorage.setItem(DM_KEY, next);
        applyTheme(next);
    }

    applyTheme(getPreferredTheme());
    toggleBtn.addEventListener('click', toggleTheme);

    // Listen for OS theme changes (if user hasn't manually set a preference)
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
        if (!localStorage.getItem(DM_KEY)) {
            applyTheme(e.matches ? 'dark' : 'light');
        }
    });

    /* ═══════════ SIDEBAR TOGGLE ═══════════ */
    const SB_KEY = 'erp_sidebar_collapsed';
    const toggle = document.getElementById('sidebarToggle');

    if (localStorage.getItem(SB_KEY) === '1') {
        body.classList.add('sidebar-collapsed');
    }

    function toggleSidebar() {
        if (window.innerWidth < 992) {
            body.classList.toggle('sidebar-mobile-open');
            return;
        }
        body.classList.toggle('sidebar-collapsed');
        localStorage.setItem(SB_KEY, body.classList.contains('sidebar-collapsed') ? '1' : '0');
    }

    if (toggle) {
        toggle.addEventListener('click', toggleSidebar);
    }

    document.querySelectorAll('.erp-sidebar a').forEach(function(link) {
        link.addEventListener('click', function() {
            if (window.innerWidth < 992) body.classList.remove('sidebar-mobile-open');
        });
    });
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 992) body.classList.remove('sidebar-mobile-open');
    });

    document.querySelectorAll('.sidebar-submenu .sidebar-item').forEach(function(item) {
        item.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });

    /* ═══════════ ACCESSIBLE CONFIRM MODAL ═══════════ */
    (function() {
        var forms = document.querySelectorAll('form[data-confirm], form[onsubmit*="confirm"]');
        forms.forEach(function(form) {
            var message = form.getAttribute('data-confirm');
            if (!message) {
                var raw = form.getAttribute('onsubmit') || '';
                var match = raw.match(/confirm\(\s*['\"](.*)['\"]\s*\)/);
                message = match ? match[1] : null;
            }
            if (!message) return;
            form.removeAttribute('onsubmit');
            form.removeAttribute('data-confirm');
            // Keep message for modal via dataset
            form.setAttribute('data-confirm-message', message);
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                var modalEl = document.getElementById('confirmDeleteModal');
                if (!modalEl) {
                    if (confirm(form.getAttribute('data-confirm-message') || message)) form.submit();
                    return;
                }
                var modal = new bootstrap.Modal(modalEl);
                var msgEl = document.getElementById('confirmDeleteMessage');
                if (msgEl) msgEl.textContent = form.getAttribute('data-confirm-message') || message;
                document.getElementById('confirmDeleteBtn').onclick = function() {
                    modal.hide();
                    // Use native submit to bypass this handler
                    HTMLFormElement.prototype.submit.call(form);
                };
                modal.show();
            });
        });
/* ═══════════ BACK-TO-TOP + KEYBOARD SHORTCUT ═══════════ */
    var backToTop = document.getElementById('backToTop');
    window.addEventListener('scroll', function() {
        if (window.scrollY > 400) {
            backToTop.classList.add('visible');
        } else {
            backToTop.classList.remove('visible');
        }
    }, { passive: true });
    backToTop.addEventListener('click', function() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            var searchInput = document.querySelector('.topbar-search input');
            if (searchInput) searchInput.focus();
        }
    });
})();

    /* ═══════════ SUBMIT BUTTON SPINNER ═══════════ */
    document.querySelectorAll('form').forEach(function(form) {
        var submitBtn = form.querySelector('button[type="submit"]');
        if (!submitBtn) return;
        form.addEventListener('submit', function() {
            if (form.classList.contains('submitting')) return;
            form.classList.add('submitting');
            submitBtn.disabled = true;
            var original = submitBtn.innerHTML;
            submitBtn.setAttribute('data-original-text', original);
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Memproses...';
            form.addEventListener('ajax-error', function() {
                form.classList.remove('submitting');
                submitBtn.disabled = false;
                submitBtn.innerHTML = submitBtn.getAttribute('data-original-text');
            }, { once: true });
        });
    });
})();
</script>
    @stack('scripts')
    <script>
        (() => {
            const badge = document.getElementById('globalSelfServiceBadge');
            if (!badge) return;
            const refreshSelfServiceBadge = async () => {
                try {
                    const response = await fetch('{{ route('admin.self-service.pending-count') }}', { headers: { Accept: 'application/json' } });
                    if (!response.ok) return;
                    const data = await response.json();
                    const count = Number(data.pending_count || 0);
                    badge.textContent = count > 99 ? '99+' : count;
                    badge.style.display = count > 0 ? 'inline-block' : 'none';
                } catch (_) {
                    // Notification polling is non-critical to the current page.
                }
            };
            refreshSelfServiceBadge();
            setInterval(refreshSelfServiceBadge, 10000);
        })();
    </script>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => navigator.serviceWorker.register('{{ asset('service-worker.js') }}'));
        }
    </script>
</body>
</html>
