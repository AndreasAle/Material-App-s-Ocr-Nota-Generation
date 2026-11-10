<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('store.mode') ? config('store.name') : config('app.name') }}</title>

    {{-- PWA --}}
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#1f252f">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="{{ config('app.name') }}">
    <link rel="icon" type="image/svg+xml" href="/icons/icon.svg">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * { box-sizing: border-box; }
        [x-cloak] { display: none !important; }
        body { font-family: 'Inter', sans-serif; }

        /* ═══════════════════════════════════════
           SIDEBAR — arang gelap + aksen oranye (gaya dasbor proyek)
        ═══════════════════════════════════════ */
        :root { --sb-width: 260px; }

        .sidebar {
            width: var(--sb-width);
            max-width: 86vw;
            background: #1f252f;
            display: flex;
            flex-direction: column;
            box-shadow: 4px 0 24px rgba(0,0,0,.18);
        }
        /* HP: drawer tertutup sejak halaman dimuat (sebelum Alpine aktif) */
        @media (max-width: 1023px) {
            .sidebar:not(.translate-x-0) { transform: translateX(-100%); box-shadow: none; }
        }

        .sb-brand {
            height: 68px;
            display: flex; align-items: center; gap: 11px;
            padding: 0 16px;
            border-bottom: 1px solid rgba(255,255,255,.07);
            flex-shrink: 0;
        }
        .sb-logo {
            width: 40px; height: 40px; border-radius: 11px;
            background: #ef6c1e;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; overflow: hidden;
        }
        .sb-logo img { width: 100%; height: 100%; object-fit: contain; padding: 6px; filter: brightness(0) invert(1); }
        .sb-brand-name { font-size: 16px; font-weight: 800; color: #fff; letter-spacing: -.2px; line-height: 1.1; }
        .sb-brand-sub { font-size: 11px; font-weight: 500; color: #8592a5; margin-top: 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        .sb-scroll {
            flex: 1; overflow-y: auto;
            padding: 10px 10px 16px; display: flex; flex-direction: column; gap: 2px;
        }
        .sb-scroll::-webkit-scrollbar { width: 3px; }
        .sb-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,.12); border-radius: 3px; }

        .sb-label {
            font-size: 10px; font-weight: 700; letter-spacing: .14em; text-transform: uppercase;
            color: #66748a; padding: 0 10px; margin: 16px 0 4px;
        }

        .nav-item {
            display: flex; align-items: center; gap: 11px;
            min-height: 44px; padding: 9px 12px; border-radius: 10px;
            font-size: 14px; font-weight: 500; color: #c3cad5;
            transition: background .15s ease, color .15s ease;
            cursor: pointer; text-decoration: none; position: relative;
        }
        .nav-item:hover { background: rgba(255,255,255,.06); color: #fff; }
        .nav-item.active { background: #ef6c1e; color: #fff; font-weight: 700; box-shadow: 0 6px 16px rgba(239,108,30,.35); }
        .nav-icon { width: 19px; height: 19px; flex-shrink: 0; opacity: .75; }
        .nav-item.active .nav-icon, .nav-item:hover .nav-icon { opacity: 1; }
        .nav-caret { width: 14px; height: 14px; color: #66748a; transition: transform .2s; flex-shrink: 0; }
        .nav-item.active .nav-caret { color: #fff; }

        .submenu-wrap {
            margin-left: 21px; padding-left: 12px;
            border-left: 1.5px solid rgba(255,255,255,.1);
            display: flex; flex-direction: column; gap: 1px; margin-top: 2px; margin-bottom: 4px;
        }
        .submenu-item {
            display: flex; align-items: center; gap: 9px;
            min-height: 40px; padding: 8px 10px; border-radius: 8px;
            font-size: 13.5px; font-weight: 500; color: #a7b0bd;
            transition: all .15s ease; text-decoration: none;
        }
        .submenu-item:hover { background: rgba(255,255,255,.06); color: #fff; }
        .submenu-item.active { color: #fbb57c; font-weight: 700; }
        .submenu-dot { width: 6px; height: 6px; border-radius: 999px; background: #515c71; flex-shrink: 0; }
        .submenu-item.active .submenu-dot { background: #ef6c1e; }

        .sb-group { margin-top: 4px; }
        .sb-group-hd {
            display: flex; align-items: center; gap: 8px;
            min-height: 36px; padding: 6px 10px; border-radius: 8px;
            font-size: 11px; font-weight: 800; letter-spacing: .12em; text-transform: uppercase;
            color: #8592a5; cursor: pointer;
            background: none; border: none; width: 100%; text-align: left;
            margin-top: 10px;
        }
        .sb-group-hd:hover { color: #d5dae1; }
        .sb-group-hd.has-active { color: #fbb57c; }
        .sb-group-dot { width: 7px; height: 7px; border-radius: 2px; flex-shrink: 0; }
        .sb-group-chevron { width: 13px; height: 13px; margin-left: auto; flex-shrink: 0; color: #66748a; transition: transform .2s ease; }
        .sb-group-body { padding: 2px 0 4px 0; }

        .sb-bottom { border-top: 1px solid rgba(255,255,255,.07); padding: 12px; flex-shrink: 0; }
        .sb-user-card {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 12px; border-radius: 12px; background: rgba(255,255,255,.05);
        }
        .sb-user-avatar {
            width: 36px; height: 36px; border-radius: 10px; background: #ef6c1e;
            display: flex; align-items: center; justify-content: center;
            font-size: 14px; font-weight: 800; color: #fff; flex-shrink: 0;
        }
        .sb-user-name { font-size: 13.5px; font-weight: 700; color: #fff; line-height: 1.1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .sb-user-role { font-size: 11px; color: #8592a5; margin-top: 3px; font-weight: 600; }
        .sb-logout-btn {
            width: 36px; height: 36px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            background: rgba(244,63,94,.12); color: #fda4af; flex-shrink: 0; cursor: pointer;
        }
        .sb-logout-btn:hover { background: rgba(244,63,94,.22); }

        /* ═══════════════════════════════════════
           APP BAR
        ═══════════════════════════════════════ */
        .topbar {
            background: #ffffff;
            border-bottom: 1px solid #e3e6eb;
            box-shadow: 0 1px 10px rgba(31,37,47,.035);
        }

        .mobile-brand-mark {
            width: 40px; height: 40px; border-radius: 13px;
            display: inline-flex; align-items: center; justify-content: center;
            color: #e85f12; background: #fff5ed; border: 1px solid #fed7ba;
            flex-shrink: 0;
        }
        .mobile-brand-mark svg { width: 19px; height: 19px; }
        .mobile-store-name { font-size: 14px; line-height: 18px; font-weight: 800; letter-spacing: -.15px; color: #1f252f; }
        .mobile-store-meta { margin-top: 1px; font-size: 10.5px; line-height: 15px; font-weight: 500; color: #8491a3; }

        .main-bg { background: #f3f4f6; }

        /* Di HP konten memenuhi layar (tanpa kotak), di desktop dibungkus kartu */
        .content-shell { background: transparent; }
        @media (min-width: 1024px) {
            .content-shell {
                background: #fff;
                border: 1px solid #e3e6eb;
                box-shadow: 0 10px 30px rgba(31,37,47,.04);
            }
        }

        /* Ruang untuk navigasi bawah di HP */
        .has-bottom-nav main { padding-bottom: calc(var(--bottom-nav-h, 64px) + env(safe-area-inset-bottom) + 12px); }
        @media (min-width: 1024px) { .has-bottom-nav main { padding-bottom: 0; } }

        @keyframes slideDown { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }
        .flash { animation: slideDown .2s ease; }

        .user-avatar {
            width: 36px; height: 36px; border-radius: 10px; background: #ef6c1e;
            display: flex; align-items: center; justify-content: center;
            font-size: 14px; font-weight: 800; color: #fff; flex-shrink: 0;
        }

        @media (max-width: 1023px) {
            .user-avatar {
                width: 36px; height: 36px; border-radius: 999px;
                background: #ef6c1e; border: 3px solid #fff; outline: 1px solid #fed7ba;
                font-size: 12px; box-shadow: 0 2px 8px rgba(239,108,30,.16);
            }
        }

        .topbar-menu-trigger {
            position: relative; height: 40px; width: 40px; border-radius: 12px;
            display: inline-flex; align-items: center; justify-content: center;
            color: #424b5c; background: #f6f7f9; border: 1px solid #e3e6eb;
        }
        .topbar-menu-trigger:hover, .topbar-menu-trigger.active { background: #fff7ef; color: #d65a10; border-color: #fdd5b0; }
        .topbar-menu-trigger svg { width: 19px; height: 19px; }

        .quick-menu-dropdown {
            position: absolute; right: 0; top: calc(100% + 10px); width: 260px;
            background: #fff; border: 1px solid #e3e6eb; border-radius: 16px;
            box-shadow: 0 20px 50px rgba(31,37,47,.14); padding: 8px; z-index: 60;
        }
        .quick-menu-head { padding: 10px 11px 8px; border-bottom: 1px solid #eceef2; margin-bottom: 5px; }
        .quick-menu-head span { display: block; font-size: 13px; font-weight: 800; color: #1f252f; }
        .quick-menu-head small { display: block; margin-top: 2px; font-size: 11px; font-weight: 500; color: #8592a5; }
        .quick-menu-item {
            display: flex; align-items: center; gap: 10px; min-height: 48px; padding: 8px 9px;
            border-radius: 12px; color: #515c71; text-decoration: none;
        }
        .quick-menu-item:hover, .quick-menu-item.active { background: #fff7ef; color: #d65a10; }
        .quick-menu-icon {
            width: 36px; height: 36px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center;
            background: #f6f7f9; border: 1px solid #eceef2; flex-shrink: 0;
        }
        .quick-menu-icon svg { width: 18px; height: 18px; }
        .quick-menu-text { min-width: 0; display: flex; flex-direction: column; }
        .quick-menu-text b { font-size: 13px; font-weight: 700; color: currentColor; line-height: 1.15; }
        .quick-menu-text small { margin-top: 3px; font-size: 11px; color: #8592a5; line-height: 1.15; }
    </style>
</head>
<body class="h-full antialiased has-bottom-nav">

<div x-data="{ sidebarOpen: window.innerWidth >= 1024 }" class="flex h-screen overflow-hidden main-bg">

    {{-- Mobile overlay --}}
    <div x-show="sidebarOpen && window.innerWidth < 1024"
         x-cloak x-transition.opacity
         class="fixed inset-0 z-40 bg-charcoal-950/50 lg:hidden"
         @click="sidebarOpen = false"></div>

    {{-- ═══════════════════════ SIDEBAR ════════════════════════ --}}
    <aside class="sidebar fixed inset-y-0 left-0 z-50 transition-transform duration-300 ease-in-out"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">

        {{-- Brand --}}
        <div class="sb-brand">
            <div class="sb-logo">
                <img src="{{ asset('images/logo.png') }}" alt="Logo"
                     onerror="this.style.display='none'; this.parentElement.innerHTML='<svg width=\'20\' height=\'20\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'#ffffff\' stroke-width=\'2\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M3.75 9.75 12 3l8.25 6.75V19.5A1.5 1.5 0 0 1 18.75 21h-13.5a1.5 1.5 0 0 1-1.5-1.5V9.75Z\'/><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M9 21v-6.75h6V21\'/></svg>'">
            </div>
            <div class="min-w-0 flex-1">
                <div class="sb-brand-name">{{ config('store.mode') ? config('store.name') : 'NOTAGO' }}</div>
                <div class="sb-brand-sub">{{ config('store.mode') ? 'Aplikasi Toko Material' : 'Sistem RAB Material' }}</div>
            </div>
            <button @click="sidebarOpen = false"
                    class="lg:hidden shrink-0 h-10 w-10 rounded-xl flex items-center justify-center text-charcoal-300 hover:bg-white/10 transition-colors" aria-label="Tutup menu">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Navigation --}}
        <nav class="sb-scroll">

            {{-- Dashboard & Search — selalu tampil --}}
            @php $storeMode = (bool) config('store.mode'); @endphp
            @unless($storeMode)
            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9.75 12 3l8.25 6.75V19.5A1.5 1.5 0 0 1 18.75 21h-13.5a1.5 1.5 0 0 1-1.5-1.5V9.75Z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 21v-6.75h6V21"/>
                </svg>
                <span>Dashboard</span>
            </a>
            @endunless

            @unless(auth()->user()->isKasir())
            <a href="{{ route('search') }}" class="nav-item {{ request()->routeIs('search') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z"/>
                </svg>
                <span>Pencarian</span>
            </a>
            @endunless

            {{-- ══════════════════════════════════════════════
                 GROUP 0 — TOKO (kasir, penjualan, pelanggan, rekap)
            ══════════════════════════════════════════════ --}}
            @if(in_array(auth()->user()->role, ['admin','staff','kasir','pimpinan']))
            @php $grpToko = request()->routeIs('store.*'); @endphp
            <div x-data="{ open: {{ ($grpToko || $storeMode) ? 'true' : 'false' }} }" class="sb-group">
                <button type="button" @click="open = !open" class="sb-group-hd {{ $grpToko ? 'has-active' : '' }}">
                    <span class="sb-group-dot" style="background:#10b981"></span>
                    <span>Toko · Kerja Harian</span>
                    <svg class="sb-group-chevron" :class="open ? 'rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7"/></svg>
                </button>
                <div x-show="open" x-collapse class="sb-group-body">
                    @if(auth()->user()->canViewFinancials())
                    <a href="{{ route('store.dashboard') }}" class="nav-item {{ request()->routeIs('store.dashboard') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>
                        <span>Beranda</span>
                    </a>
                    @endif
                    @if(auth()->user()->role !== 'pimpinan')
                    <a href="{{ route('store.pos') }}" class="nav-item {{ request()->routeIs('store.pos') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
                        <span>Jual (Kasir)</span>
                    </a>
                    @if(auth()->user()->role !== 'kasir')
                    <a href="{{ route('purchase-scans.create') }}" class="nav-item {{ request()->routeIs('purchase-scans.*', 'purchase-scan-batches.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Z"/></svg>
                        <span>Barang Masuk (Foto Nota)</span>
                    </a>
                    <a href="{{ route('stock.index') }}" class="nav-item {{ request()->routeIs('stock.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/></svg>
                        <span>Stok Barang</span>
                    </a>
                    @endif
                    <a href="{{ route('store.sales.index') }}" class="nav-item {{ request()->routeIs('store.sales.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08M15.75 18H18a2.25 2.25 0 0 0 2.25-2.25V6.108M6.75 7.5h3v3h-3v-3Z"/></svg>
                        <span>Riwayat Penjualan</span>
                    </a>
                    <a href="{{ route('store.customers.index') }}" class="nav-item {{ request()->routeIs('store.customers.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Z"/></svg>
                        <span>Pelanggan &amp; Bon</span>
                    </a>
                    @endif
                    @if(auth()->user()->canViewFinancials())
                    <a href="{{ route('store.reports.recap') }}" class="nav-item {{ request()->routeIs('store.reports.recap*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941"/></svg>
                        <span>Laporan Toko</span>
                    </a>
                    <a href="{{ route('store.reports.top') }}" class="nav-item {{ request()->routeIs('store.reports.top') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z"/></svg>
                        <span>Barang Terlaris</span>
                    </a>
                    <a href="{{ route('store.reports.restock') }}" class="nav-item {{ request()->routeIs('store.reports.restock') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
                        <span>Saran Kulakan</span>
                    </a>
                    @endif
                    @if(auth()->user()->isAdmin())
                    <a href="{{ route('materials.index') }}" class="nav-item {{ request()->routeIs('materials.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z"/></svg>
                        <span>Data Barang &amp; Harga</span>
                    </a>
                    @endif
                </div>
            </div>
            @endif

            @unless(auth()->user()->isKasir())
            {{-- ══════════════════════════════════════════════
                 GROUP 1 — PURCHASING
            ══════════════════════════════════════════════ --}}
            @php
                $grpPurchase = request()->routeIs(
                    'purchase-transactions.*','purchase-scans.*','purchase-scan-batches.*',
                    'purchase-orders.*','material-requests.*','goods-receipts.*','rab-plans.*',
                    'rfq.*','purchase-returns.*'
                );
            @endphp
            <div x-data="{ open: {{ $grpPurchase ? 'true' : 'false' }} }" class="sb-group">
                <button type="button" @click="open = !open"
                        class="sb-group-hd {{ $grpPurchase ? 'has-active' : '' }}">
                    <span class="sb-group-dot" style="background:#f97316"></span>
                    <span>Purchasing</span>
                    <svg class="sb-group-chevron" :class="open ? 'rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7"/>
                    </svg>
                </button>
                <div x-show="open" x-collapse class="sb-group-body">

                    {{-- Transaksi --}}
                    @php $purchaseOpen = request()->routeIs('purchase-transactions.*','purchase-scans.*','purchase-scan-batches.*','purchase-reports.*','scanner-reports.*'); @endphp
                    <div x-data="{ open: {{ $purchaseOpen ? 'true' : 'false' }} }">
                        <button type="button" @click="open = !open" class="nav-item w-full {{ $purchaseOpen ? 'active' : '' }}">
                            <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75A2.25 2.25 0 0 1 6 4.5h12a2.25 2.25 0 0 1 2.25 2.25v10.5A2.25 2.25 0 0 1 18 19.5H6a2.25 2.25 0 0 1-2.25-2.25V6.75Z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 9h9m-9 3h6m-6 3h4.5"/>
                            </svg>
                            <span class="flex-1 text-left">Transaksi</span>
                            <svg class="nav-caret" :class="open ? 'rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7"/></svg>
                        </button>
                        <div x-show="open" x-collapse class="submenu-wrap">
                            <a href="{{ route('purchase-transactions.index') }}" class="submenu-item {{ request()->routeIs('purchase-transactions.index','purchase-transactions.show','purchase-transactions.edit') ? 'active' : '' }}"><span class="submenu-dot"></span>Daftar Transaksi</a>
                            <a href="{{ route('purchase-transactions.create') }}" class="submenu-item {{ request()->routeIs('purchase-transactions.create') ? 'active' : '' }}"><span class="submenu-dot"></span>Input Manual</a>
                            @if(!auth()->user()->isPurchasing())
                            <a href="{{ route('purchase-scans.create') }}" class="submenu-item {{ request()->routeIs('purchase-scans.*') ? 'active' : '' }}"><span class="submenu-dot"></span>Scan Nota</a>
                            @endif
                            <a href="{{ route('purchase-reports.index') }}" class="submenu-item {{ request()->routeIs('purchase-reports.*') ? 'active' : '' }}"><span class="submenu-dot"></span>Laporan Pembelian</a>
                            @if(!auth()->user()->isPurchasing())
                            <a href="{{ route('scanner-reports.index') }}" class="submenu-item {{ request()->routeIs('scanner-reports.*') ? 'active' : '' }}"><span class="submenu-dot"></span>Laporan Scanner</a>
                            @endif
                        </div>
                    </div>

                    {{-- Purchase Order --}}
                    @unless($storeMode)
                    <a href="{{ route('purchase-orders.index') }}" class="nav-item {{ request()->routeIs('purchase-orders.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/></svg>
                        <span>Purchase Order</span>
                    </a>
                    @endunless

                    {{-- RFQ / Penawaran --}}
                    @unless($storeMode)
                    @if(in_array(auth()->user()->role, ['admin','staff','purchasing']))
                    <a href="{{ route('rfq.index') }}" class="nav-item {{ request()->routeIs('rfq.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m5.231 13.481L15 17.25m-4.5-15H5.625c-.621 0-1.125.504-1.125 1.125v16.5c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9zm3.75 11.625a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                        <span>RFQ / Penawaran</span>
                    </a>
                    @endif
                    @endunless

                    {{-- Permintaan Material --}}
                    @unless($storeMode)
                    @if(in_array(auth()->user()->role, ['admin','staff','purchasing']))
                    <a href="{{ route('material-requests.index') }}" class="nav-item {{ request()->routeIs('material-requests.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                        <span>Permintaan Material</span>
                    </a>
                    @endif
                    @endunless

                    {{-- Surat Jalan --}}
                    @if(in_array(auth()->user()->role, ['admin','staff','purchasing']))
                    <a href="{{ route('goods-receipts.index') }}" class="nav-item {{ request()->routeIs('goods-receipts.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>
                        <span>Surat Jalan</span>
                    </a>
                    @endif

                    {{-- Return to Vendor --}}
                    @if(in_array(auth()->user()->role, ['admin','staff','purchasing']))
                    <a href="{{ route('purchase-returns.index') }}" class="nav-item {{ request()->routeIs('purchase-returns.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 0 1 0 12h-3"/></svg>
                        <span>Return ke Vendor</span>
                    </a>
                    @endif

                    {{-- RAB & Anggaran --}}
                    @if(auth()->user()->canViewFinancials())
                    @unless($storeMode)
                    @php $rabOpen = request()->routeIs('rab-plans.*'); @endphp
                    <div x-data="{ open: {{ $rabOpen ? 'true' : 'false' }} }">
                        <button type="button" @click="open = !open" class="nav-item w-full {{ $rabOpen ? 'active' : '' }}">
                            <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                            <span class="flex-1 text-left">RAB & Anggaran</span>
                            <svg class="nav-caret" :class="open ? 'rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7"/></svg>
                        </button>
                        <div x-show="open" x-collapse class="submenu-wrap">
                            <a href="{{ route('rab-plans.index') }}" class="submenu-item {{ request()->routeIs('rab-plans.index') ? 'active' : '' }}"><span class="submenu-dot"></span>Daftar RAB</a>
                            <a href="{{ route('rab-plans.create') }}" class="submenu-item {{ request()->routeIs('rab-plans.create') ? 'active' : '' }}"><span class="submenu-dot"></span>Buat RAB Baru</a>
                            <a href="{{ route('rab-plans.templates') }}" class="submenu-item {{ request()->routeIs('rab-plans.templates') ? 'active' : '' }}"><span class="submenu-dot"></span>Template RAB</a>
                        </div>
                    </div>
                    @endunless
                    @endif

                </div>
            </div>{{-- /GROUP PURCHASING --}}

            {{-- ══════════════════════════════════════════════
                 GROUP 2 — GUDANG & STOK  (non-purchasing)
            ══════════════════════════════════════════════ --}}
            @if(!auth()->user()->isPurchasing())
            @php
                $grpGudang = request()->routeIs('warehouses.*','stock.*','documents.*');
            @endphp
            <div x-data="{ open: {{ $grpGudang ? 'true' : 'false' }} }" class="sb-group">
                <button type="button" @click="open = !open"
                        class="sb-group-hd {{ $grpGudang ? 'has-active' : '' }}">
                    <span class="sb-group-dot" style="background:#22c55e"></span>
                    <span>Gudang & Stok</span>
                    <svg class="sb-group-chevron" :class="open ? 'rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7"/></svg>
                </button>
                <div x-show="open" x-collapse class="sb-group-body">

                    @if(in_array(auth()->user()->role, ['admin','staff']))
                    <a href="{{ route('warehouses.index') }}" class="nav-item {{ request()->routeIs('warehouses.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m0 6.205l3 1m1.5.5-1.5-.5M6.75 7.364V3h-3v18m3-13.636l10.5-3.819"/></svg>
                        <span>Gudang</span>
                    </a>
                    @endif

                    @php $stockOpen = request()->routeIs('stock.*'); @endphp
                    <div x-data="{ open: {{ $stockOpen ? 'true' : 'false' }} }">
                        <button type="button" @click="open = !open" class="nav-item w-full {{ $stockOpen ? 'active' : '' }}">
                            <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
                            <span class="flex-1 text-left">Stok Material</span>
                            <svg class="nav-caret" :class="open ? 'rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7"/></svg>
                        </button>
                        <div x-show="open" x-collapse class="submenu-wrap">
                            <a href="{{ route('stock.index') }}" class="submenu-item {{ request()->routeIs('stock.index') ? 'active' : '' }}"><span class="submenu-dot"></span>Ringkasan Stok</a>
                            <a href="{{ route('stock.create') }}" class="submenu-item {{ request()->routeIs('stock.create') ? 'active' : '' }}"><span class="submenu-dot"></span>Input Stok</a>
                            <a href="{{ route('stock.scan-qr') }}" class="submenu-item {{ request()->routeIs('stock.scan-qr') ? 'active' : '' }}"><span class="submenu-dot"></span>Scan QR</a>
                        </div>
                    </div>

                    @unless($storeMode)
                    <a href="{{ route('documents.index') }}" class="nav-item {{ request()->routeIs('documents.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z"/></svg>
                        <span>Dokumen Proyek</span>
                    </a>
                    @endunless

                </div>
            </div>{{-- /GROUP GUDANG --}}
            @endif

            {{-- ══════════════════════════════════════════════
                 GROUP 3 — INVOICE & TAGIHAN  (canViewFinancials)
            ══════════════════════════════════════════════ --}}
            @if(auth()->user()->canViewFinancials())
            @php
                $grpInvoice = request()->routeIs('invoices.*','subcontractors.*','receivables.*','payables.*')
                           || request()->is('subcontracts*');
            @endphp
            <div x-data="{ open: {{ $grpInvoice ? 'true' : 'false' }} }" class="sb-group">
                <button type="button" @click="open = !open"
                        class="sb-group-hd {{ $grpInvoice ? 'has-active' : '' }}">
                    <span class="sb-group-dot" style="background:#3b82f6"></span>
                    <span>Invoice & Tagihan</span>
                    <svg class="sb-group-chevron" :class="open ? 'rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7"/></svg>
                </button>
                <div x-show="open" x-collapse class="sb-group-body">

                    @unless($storeMode)
                    <a href="{{ route('invoices.index') }}" class="nav-item {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        <span>Invoice</span>
                    </a>
                    @endunless

                    @unless($storeMode)
                    @php $subOpen = request()->routeIs('subcontractors.*') || request()->is('subcontracts*'); @endphp
                    <div x-data="{ open: {{ $subOpen ? 'true' : 'false' }} }">
                        <button type="button" @click="open = !open" class="nav-item w-full {{ $subOpen ? 'active' : '' }}">
                            <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                            <span class="flex-1 text-left">Subkontraktor</span>
                            <svg class="nav-caret" :class="open ? 'rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7"/></svg>
                        </button>
                        <div x-show="open" x-collapse class="submenu-wrap">
                            <a href="{{ route('subcontractors.index') }}" class="submenu-item {{ request()->routeIs('subcontractors.index','subcontractors.show','subcontractors.create','subcontractors.edit') ? 'active' : '' }}"><span class="submenu-dot"></span>Master Subkon</a>
                            <a href="{{ route('subcontractors.spk.index') }}" class="submenu-item {{ request()->routeIs('subcontractors.spk.*') || request()->is('subcontracts*') ? 'active' : '' }}"><span class="submenu-dot"></span>Daftar SPK</a>
                        </div>
                    </div>
                    @endunless

                    @unless($storeMode)
                    <a href="{{ route('receivables.index') }}" class="nav-item {{ request()->routeIs('receivables.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75"/></svg>
                        <span>Piutang</span>
                    </a>
                    @endunless

                    <a href="{{ route('payables.index') }}" class="nav-item {{ request()->routeIs('payables.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        <span>Hutang</span>
                    </a>

                </div>
            </div>{{-- /GROUP INVOICE --}}
            @endif

            {{-- ══════════════════════════════════════════════
                 GROUP 4 — PAJAK & CORETAX  (canViewFinancials)
            ══════════════════════════════════════════════ --}}
            @if(auth()->user()->canViewFinancials() && ! $storeMode)
            @php $grpTax = request()->routeIs('tax.*'); @endphp
            <div x-data="{ open: {{ $grpTax ? 'true' : 'false' }} }" class="sb-group">
                <button type="button" @click="open = !open"
                        class="sb-group-hd {{ $grpTax ? 'has-active' : '' }}">
                    <span class="sb-group-dot" style="background:#a855f7"></span>
                    <span>Pajak & Coretax</span>
                    <svg class="sb-group-chevron" :class="open ? 'rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7"/></svg>
                </button>
                <div x-show="open" x-collapse class="sb-group-body">

                    <a href="{{ route('tax.dashboard') }}" class="nav-item {{ request()->routeIs('tax.dashboard') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0020.25 18V6A2.25 2.25 0 0018 3.75H6A2.25 2.25 0 003.75 6v12A2.25 2.25 0 006 20.25Z"/></svg>
                        <span>Dashboard Pajak</span>
                    </a>
                    <a href="{{ route('tax.ppn.index') }}" class="nav-item {{ request()->routeIs('tax.ppn.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/></svg>
                        <span>PPN Masukan/Keluaran</span>
                    </a>
                    <a href="{{ route('tax.pph-final.index') }}" class="nav-item {{ request()->routeIs('tax.pph-final.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0c1.1.128 1.907 1.077 1.907 2.185Z"/></svg>
                        <span>PPh Final Ps.4(2)</span>
                    </a>
                    <a href="{{ route('tax.pph23.index') }}" class="nav-item {{ request()->routeIs('tax.pph23.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75"/></svg>
                        <span>PPh Pasal 23</span>
                    </a>
                    <a href="{{ route('tax.setoran.index') }}" class="nav-item {{ request()->routeIs('tax.setoran.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/></svg>
                        <span>Setoran Pajak</span>
                    </a>

                    {{-- Coretax DJP sub-label --}}
                    <div class="flex items-center gap-2 px-3 pt-3 pb-1">
                        <div class="h-px flex-1 bg-slate-100"></div>
                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Coretax DJP</span>
                        <div class="h-px flex-1 bg-slate-100"></div>
                    </div>

                    <a href="{{ route('tax.bppu.index') }}" class="nav-item {{ request()->routeIs('tax.bppu.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                        <span>BPPU (PPh Unifikasi)</span>
                    </a>
                    <a href="{{ route('tax.dok-lain.index') }}" class="nav-item {{ request()->routeIs('tax.dok-lain.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 0 1-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0 1 12 18.375m9.75-12.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125m19.5 0v1.5c0 .621-.504 1.125-1.125 1.125M2.25 5.625v1.5c0 .621.504 1.125 1.125 1.125m0 0h17.25m-17.25 0h7.5c.621 0 1.125.504 1.125 1.125M3.375 8.25c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125"/></svg>
                        <span>Dokumen Lain PPN</span>
                    </a>
                    <a href="{{ route('tax.bp21.index') }}" class="nav-item {{ request()->routeIs('tax.bp21.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                        <span>BP21 (PPh Pasal 21)</span>
                    </a>
                    <a href="{{ route('tax.settings.edit') }}" class="nav-item {{ request()->routeIs('tax.settings.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 010 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 010-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Pengaturan Pajak</span>
                    </a>

                </div>
            </div>{{-- /GROUP PAJAK --}}
            @endif

            {{-- ══════════════════════════════════════════════
                 GROUP 5 — AKUNTANSI  (canViewFinancials)
            ══════════════════════════════════════════════ --}}
            @if(auth()->user()->canViewFinancials() && ! $storeMode)
            @php $grpAkun = request()->routeIs('accounting.*','coa.*','journal.*','trial-balance.*'); @endphp
            <div x-data="{ open: {{ $grpAkun ? 'true' : 'false' }} }" class="sb-group">
                <button type="button" @click="open = !open"
                        class="sb-group-hd {{ $grpAkun ? 'has-active' : '' }}">
                    <span class="sb-group-dot" style="background:#14b8a6"></span>
                    <span>Akuntansi</span>
                    <svg class="sb-group-chevron" :class="open ? 'rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7"/></svg>
                </button>
                <div x-show="open" x-collapse class="sb-group-body">

                    <a href="{{ route('accounting.dashboard') }}" class="nav-item {{ request()->routeIs('accounting.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0020.25 18V6A2.25 2.25 0 0018 3.75H6A2.25 2.25 0 003.75 6v12A2.25 2.25 0 006 20.25Z"/></svg>
                        <span>Dashboard Akuntansi</span>
                    </a>
                    <a href="{{ route('coa.index') }}" class="nav-item {{ request()->routeIs('coa.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg>
                        <span>Bagan Akun (COA)</span>
                    </a>
                    <a href="{{ route('journal.index') }}" class="nav-item {{ request()->routeIs('journal.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.75V16.5L12 14.25 7.5 16.5V3.75m9 0H18A2.25 2.25 0 0120.25 6v12A2.25 2.25 0 0118 20.25H6A2.25 2.25 0 013.75 18V6A2.25 2.25 0 016 3.75h1.5m9 0h-9"/></svg>
                        <span>Jurnal Umum</span>
                    </a>
                    <a href="{{ route('trial-balance.index') }}" class="nav-item {{ request()->routeIs('trial-balance.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0012 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52 2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 01-2.031.352 5.988 5.988 0 01-2.031-.352c-.483-.174-.711-.703-.589-1.202L18.75 4.97Zm-16.5.52c.99-.203 1.99-.377 3-.52m0 0 2.62 10.726c.122.499-.106 1.028-.589 1.202a5.989 5.989 0 01-2.031.352 5.989 5.989 0 01-2.031-.352c-.483-.174-.711-.703-.589-1.202L5.25 5.49Z"/></svg>
                        <span>Neraca Saldo</span>
                    </a>

                </div>
            </div>{{-- /GROUP AKUNTANSI --}}
            @endif

            {{-- ══════════════════════════════════════════════
                 GROUP 6 — LAPORAN  (canViewFinancials)
            ══════════════════════════════════════════════ --}}
            @if(auth()->user()->canViewFinancials())
            @php
                $grpLaporan = request()->routeIs(
                    'executive-summary.*','financial-reports.*','profit-loss.*',
                    'cash-flow.*','project-comparison.*','import.*',
                    'tax-report.*','inventory-valuation.*'
                );
            @endphp
            <div x-data="{ open: {{ $grpLaporan ? 'true' : 'false' }} }" class="sb-group">
                <button type="button" @click="open = !open"
                        class="sb-group-hd {{ $grpLaporan ? 'has-active' : '' }}">
                    <span class="sb-group-dot" style="background:#ef4444"></span>
                    <span>Laporan</span>
                    <svg class="sb-group-chevron" :class="open ? 'rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7"/></svg>
                </button>
                <div x-show="open" x-collapse class="sb-group-body">

                    @unless($storeMode)
                    <a href="{{ route('executive-summary.index') }}" class="nav-item {{ request()->routeIs('executive-summary.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                        <span>Executive Summary</span>
                    </a>
                    @endunless
                    @unless($storeMode)
                    <a href="{{ route('financial-reports.index') }}" class="nav-item {{ request()->routeIs('financial-reports.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75"/></svg>
                        <span>Lap. Keuangan</span>
                    </a>
                    @endunless
                    @unless($storeMode)
                    <a href="{{ route('profit-loss.index') }}" class="nav-item {{ request()->routeIs('profit-loss.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6L9 12.75l4.286-4.286a11.948 11.948 0 014.306 6.43l.776 2.898m0 0l3.182-5.511m-3.182 5.511l-5.511-3.182"/></svg>
                        <span>Laba / Rugi</span>
                    </a>
                    @endunless
                    @unless($storeMode)
                    <a href="{{ route('cash-flow.index') }}" class="nav-item {{ request()->routeIs('cash-flow.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>
                        <span>Arus Kas</span>
                    </a>
                    @endunless
                    @unless($storeMode)
                    <a href="{{ route('project-comparison.index') }}" class="nav-item {{ request()->routeIs('project-comparison.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125Z"/></svg>
                        <span>Perbandingan Proyek</span>
                    </a>
                    @endunless
                    <a href="{{ route('import.index') }}" class="nav-item {{ request()->routeIs('import.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                        <span>Import Excel</span>
                    </a>
                    @unless($storeMode)
                    <a href="{{ route('tax-report.index') }}" class="nav-item {{ request()->routeIs('tax-report.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0c1.1.128 1.907 1.077 1.907 2.185Z"/></svg>
                        <span>Rekap Pajak</span>
                    </a>
                    @endunless
                    <a href="{{ route('inventory-valuation.index') }}" class="nav-item {{ request()->routeIs('inventory-valuation.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/></svg>
                        <span>Nilai Stok</span>
                    </a>

                </div>
            </div>{{-- /GROUP LAPORAN --}}
            @endif

            {{-- ══════════════════════════════════════════════
                 GROUP 7 — MASTER DATA  (admin only)
            ══════════════════════════════════════════════ --}}
            @if(auth()->user()->isAdmin())
            @php $grpMaster = request()->routeIs('materials.*','suppliers.*','projects.*','material-categories.*','units.*'); @endphp
            <div x-data="{ open: {{ $grpMaster ? 'true' : 'false' }} }" class="sb-group">
                <button type="button" @click="open = !open"
                        class="sb-group-hd {{ $grpMaster ? 'has-active' : '' }}">
                    <span class="sb-group-dot" style="background:#64748b"></span>
                    <span>Master Data</span>
                    <svg class="sb-group-chevron" :class="open ? 'rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7"/></svg>
                </button>
                <div x-show="open" x-collapse class="sb-group-body">

                    <a href="{{ route('materials.index') }}" class="nav-item {{ request()->routeIs('materials.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 7.5 12 3l7.5 4.5v9L12 21l-7.5-4.5v-9Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 12 4.5 7.5M12 12l7.5-4.5M12 12v9"/></svg>
                        <span>Material</span>
                    </a>
                    <a href="{{ route('suppliers.index') }}" class="nav-item {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 19.5h16.5M5.25 19.5V8.25A2.25 2.25 0 0 1 7.5 6h9A2.25 2.25 0 0 1 18.75 8.25V19.5"/><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9.75h1.5m4.5 0h1.5M8.25 13.5h1.5m4.5 0h1.5"/></svg>
                        <span>Supplier</span>
                    </a>
                    @unless($storeMode)
                    <a href="{{ route('projects.index') }}" class="nav-item {{ request()->routeIs('projects.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 19.5h16.5M6.75 19.5V9.75L12 6l5.25 3.75V19.5M9.75 19.5v-4.5h4.5v4.5"/></svg>
                        <span>Proyek</span>
                    </a>
                    @endunless
                    <a href="{{ route('material-categories.index') }}" class="nav-item {{ request()->routeIs('material-categories.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 7.5 12 3l8.25 4.5M3.75 7.5V16.5L12 21m-8.25-13.5L12 12m8.25-4.5V16.5L12 21m0-9v9"/></svg>
                        <span>Kategori</span>
                    </a>
                    <a href="{{ route('units.index') }}" class="nav-item {{ request()->routeIs('units.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 4.5h9v15h-9z"/><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 7.5h3m-3 3h3m-3 3h3"/></svg>
                        <span>Satuan</span>
                    </a>

                </div>
            </div>{{-- /GROUP MASTER --}}
            @endif
            @endunless{{-- /non-kasir --}}

        </nav>

        {{-- User card --}}
        <div class="sb-bottom">
            <div class="sb-user-card">
                <div class="sb-user-avatar">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <div class="sb-user-name">{{ auth()->user()->name }}</div>
                    <div class="sb-user-role">{{ auth()->user()->roleLabel() }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="sb-logout-btn" title="Keluar">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- ═══════════════════════ MAIN AREA ════════════════════════ --}}
    <div class="flex flex-1 flex-col min-w-0 transition-all duration-300"
         :class="sidebarOpen ? 'lg:pl-[260px]' : 'pl-0'">

        {{-- ─── TOPBAR ─── --}}
        <header class="topbar sticky top-0 z-30 flex h-[64px] items-center gap-2 px-3 sm:gap-3 md:px-6 lg:h-[60px]">

            {{-- Hamburger --}}
            <button type="button" @click="sidebarOpen = !sidebarOpen" aria-label="Menu"
                    class="hidden lg:flex h-10 w-10 items-center justify-center rounded-xl text-charcoal-500 hover:bg-charcoal-100 hover:text-charcoal-800 transition-colors shrink-0">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5"/>
                </svg>
            </button>

            {{-- Identitas toko (HP: seperti judul aplikasi) --}}
            <a href="{{ route('dashboard') }}" class="flex min-w-0 flex-1 items-center gap-2.5">
                <span class="mobile-brand-mark lg:hidden">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z"/></svg>
                </span>
                <span class="min-w-0 flex-1">
                    <span class="mobile-store-name block truncate lg:text-[15px]">{{ config('store.mode') ? config('store.name') : config('app.name') }}</span>
                    <span class="mobile-store-meta block truncate lg:hidden">{{ auth()->user()->roleLabel() }} · {{ now()->translatedFormat('d M Y') }}</span>
                </span>
            </a>

            {{-- Search --}}
            @unless(auth()->user()->isKasir())
            <form method="GET" action="{{ route('search') }}" class="hidden md:flex">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z"/>
                    </svg>
                    <input type="text" name="q" value="{{ request('q') }}"
                           placeholder="Cari sesuatu..."
                           class="pl-8 pr-3 py-1.5 text-xs rounded-lg border border-slate-200 bg-slate-50 text-slate-700 placeholder:text-slate-400 focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100/80 outline-none transition-all w-44 focus:w-60">
                </div>
            </form>
            @endunless

            {{-- Role badge --}}
            <div class="hidden lg:flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-charcoal-100 text-xs font-semibold text-charcoal-700">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                {{ auth()->user()->roleLabel() }}
            </div>
{{-- Topbar menu dropdown --}}
<div x-data="{ openQuickMenu: false }" class="relative hidden lg:block">
<button type="button"
        @click="openQuickMenu = !openQuickMenu"
        class="topbar-menu-trigger topbar-menu-trigger-danger"
        :class="openQuickMenu ? 'active' : ''"
        title="Menu cepat">
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h10"/>
        <circle cx="18" cy="6" r="2"/>
        <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H10"/>
        <circle cx="6" cy="12" r="2"/>
        <path stroke-linecap="round" stroke-linejoin="round" d="M4 18h10"/>
        <circle cx="18" cy="18" r="2"/>
    </svg>
</button>

    <div x-show="openQuickMenu"
         x-cloak
         @click.away="openQuickMenu = false"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
         class="quick-menu-dropdown">

        <div class="quick-menu-head">
            <span>Menu Cepat</span>
            <small>Akses singkat</small>
        </div>

        <a href="{{ route('notifications.index') }}"
           class="quick-menu-item {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
            <span class="quick-menu-icon">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                </svg>
            </span>
            <span class="quick-menu-text">
                <b>Notifikasi</b>
                <small>Pusat pemberitahuan</small>
            </span>
        </a>

        @if(auth()->user()->isAdmin())
        <a href="{{ route('users.index') }}"
           class="quick-menu-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
            <span class="quick-menu-icon">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                </svg>
            </span>
            <span class="quick-menu-text">
                <b>Manajemen User</b>
                <small>Kelola pengguna sistem</small>
            </span>
        </a>
        @endif

        <a href="{{ route('settings.index') }}"
           class="quick-menu-item {{ request()->routeIs('settings.*') ? 'active' : '' }}">
            <span class="quick-menu-icon">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.094c.55 0 1.02.398 1.11.94l.094.564a1.125 1.125 0 001.668.778l.496-.282a1.125 1.125 0 011.37.173l.773.773c.39.39.457.994.173 1.37l-.282.496a1.125 1.125 0 00.778 1.668l.564.094c.542.09.94.56.94 1.11v1.094c0 .55-.398 1.02-.94 1.11l-.564.094a1.125 1.125 0 00-.778 1.668l.282.496c.284.376.217.98-.173 1.37l-.773.773a1.125 1.125 0 01-1.37.173l-.496-.282a1.125 1.125 0 00-1.668.778l-.094.564c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.02-.398-1.11-.94l-.094-.564a1.125 1.125 0 00-1.668-.778l-.496.282a1.125 1.125 0 01-1.37-.173l-.773-.773a1.125 1.125 0 01-.173-1.37l.282-.496a1.125 1.125 0 00-.778-1.668l-.564-.094A1.125 1.125 0 012.25 13.5v-1.094c0-.55.398-1.02.94-1.11l.564-.094a1.125 1.125 0 00.778-1.668l-.282-.496a1.125 1.125 0 01.173-1.37l.773-.773A1.125 1.125 0 015.567 6.9l.496.282a1.125 1.125 0 001.668-.778l.094-.564Z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </span>
            <span class="quick-menu-text">
                <b>Pengaturan</b>
                <small>Konfigurasi aplikasi</small>
            </span>
        </a>
    </div>
</div>
            {{-- Notification Bell --}}
            <div x-data="notifBell()" class="relative">

                <button type="button" @click="toggle()" aria-label="Notifikasi"
                        class="relative flex h-9 w-9 items-center justify-center rounded-full border border-charcoal-200 bg-charcoal-50 text-charcoal-600 transition-colors hover:border-brand-200 hover:bg-brand-50 hover:text-brand-600 lg:h-10 lg:w-10 lg:rounded-xl lg:border-0 lg:bg-transparent">
                    <svg class="h-[18px] w-[18px] lg:h-[22px] lg:w-[22px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                    </svg>
                    <span x-show="unreadCount > 0" x-text="unreadCount > 9 ? '9+' : unreadCount" x-cloak
                          class="absolute -top-0.5 -right-0.5 min-w-[15px] h-[15px] rounded-full bg-rose-500 text-white text-[9px] font-bold leading-none flex items-center justify-center px-0.5 ring-2 ring-white"></span>
                </button>

                {{-- Dropdown --}}
                <div x-show="open" @click.away="open = false" x-cloak
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     class="fixed inset-x-3 top-[64px] sm:absolute sm:inset-x-auto sm:right-0 sm:top-full mt-2 sm:w-80 bg-white rounded-2xl shadow-xl shadow-slate-900/10 border border-slate-100 z-50 overflow-hidden">
                    <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
                        <h4 class="text-sm font-bold text-slate-800">Notifikasi</h4>
                        <a href="{{ route('notifications.index') }}" class="text-xs text-indigo-600 hover:text-indigo-700 font-semibold">Lihat semua</a>
                    </div>
                    <div class="max-h-72 overflow-y-auto divide-y divide-slate-50">
                        <template x-if="loading">
                            <div class="p-6 text-center text-sm text-slate-400">Memuat...</div>
                        </template>
                        <template x-if="!loading && notifications.length === 0">
                            <div class="py-10 text-center text-sm text-slate-400">
                                <div class="text-3xl mb-2">🔔</div>
                                <p>Belum ada notifikasi</p>
                            </div>
                        </template>
                        <template x-for="n in notifications" :key="n.id">
                            <a :href="n.url || '#'"
                               :class="n.is_read ? 'bg-white' : 'bg-indigo-50/60'"
                               class="flex items-start gap-3 px-4 py-3 hover:bg-slate-50 transition-colors">
                                <div class="mt-0.5 text-base shrink-0" x-text="n.icon || '🔔'"></div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-semibold text-slate-800 leading-tight" x-text="n.title"></p>
                                    <p class="text-xs text-slate-500 mt-0.5 line-clamp-2" x-text="n.message"></p>
                                    <p class="text-[10px] text-slate-400 mt-1" x-text="n.created_at"></p>
                                </div>
                                <span x-show="!n.is_read" class="mt-1.5 h-1.5 w-1.5 rounded-full bg-indigo-500 shrink-0"></span>
                            </a>
                        </template>
                    </div>
                    <div class="px-4 py-2.5 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between">
                        <button @click="markAllRead()" class="text-xs text-slate-500 hover:text-slate-700 transition-colors">Tandai semua dibaca</button>
                        <a href="{{ route('notifications.index') }}" class="text-xs text-indigo-600 font-semibold hover:underline">Pusat Notif →</a>
                    </div>
                </div>
            </div>

            {{-- User area --}}
            <div class="flex items-center gap-2">
                <div class="hidden lg:block text-right">
                    <p class="text-[12px] font-semibold text-slate-800 leading-none">{{ auth()->user()->name }}</p>
                    <p class="text-[10px] text-slate-400 mt-0.5 leading-none">{{ auth()->user()->email }}</p>
                </div>
                <a href="{{ route('settings.index') }}" class="user-avatar text-sm" aria-label="Akun saya">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </a>
                <form method="POST" action="{{ route('logout') }}" class="hidden lg:block">
                    @csrf
                    <button type="submit"
                            class="h-7 flex items-center gap-1 rounded-lg bg-slate-100 px-2.5 text-[11px] font-semibold text-slate-600 hover:bg-rose-50 hover:text-rose-600 transition-colors">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75"/>
                        </svg>
                        Keluar
                    </button>
                </form>
            </div>
        </header>

        {{-- ─── CONTENT ─── --}}
        <main class="flex-1 overflow-y-auto">
<div class="px-3 py-4 sm:px-5 md:px-8 md:py-7 max-w-[1440px] mx-auto">
    <div class="content-shell lg:rounded-[24px] lg:p-6 lg:min-h-[calc(100vh-116px)]">

        @include('layouts.partials.flash')

        @yield('content')

    </div>
</div>
        </main>
    </div>

    @include('layouts.partials.bottom-nav')
</div>

@stack('scripts')

<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js', { scope: '/' }).catch(() => {});
    });
}

function notifBell() {
    return {
        open: false, unreadCount: 0, notifications: [], loading: false, _timer: null,
        init() {
            this.fetchData();
            this._timer = setInterval(() => { if (!this.open) this.fetchData(); }, 60000);
        },
        async fetchData() {
            try {
                const r = await fetch('{{ route('notifications.dropdown') }}', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? ''
                    }
                });
                if (r.ok) {
                    const d = await r.json();
                    this.unreadCount   = d.unread_count  ?? 0;
                    this.notifications = d.notifications ?? [];
                }
            } catch(_) {}
        },
        toggle() {
            this.open = !this.open;
            if (this.open) {
                this.loading = true;
                this.fetchData().finally(() => this.loading = false);
            }
        },
        async markAllRead() {
            try {
                await fetch('{{ route('notifications.mark-all-read') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                this.unreadCount = 0;
                this.notifications = this.notifications.map(n => ({...n, is_read: true}));
            } catch(_) {}
        }
    };
}
</script>
</body>
</html>
