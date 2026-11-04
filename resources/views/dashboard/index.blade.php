@extends('layouts.app')

@section('content')
@php
    $latestTransactionsCollection = collect($latestTransactions);
    $maxAmount = (float) ($latestTransactionsCollection->max('grand_total') ?: 1);
    $avgTransaction = $allTimeCount > 0 ? $allTimeTotal / $allTimeCount : 0;

    $statusCounts = $latestTransactionsCollection
        ->groupBy(fn ($item) => strtolower($item->status ?? 'unknown'))
        ->map->count();

    $approvedCount  = $statusCounts->get('approved', 0) + $statusCounts->get('posted', 0);
    $pendingCount   = $statusCounts->get('pending', 0);
    $draftCount     = $statusCounts->get('draft', 0);
    $rejectedCount  = $statusCounts->get('rejected', 0);

    $latestTotalNominal = (float) $latestTransactionsCollection->sum('grand_total');
    $estimatedTarget    = max((float) $monthlyTotal, 1) * 1.25;
    $achievement        = $estimatedTarget > 0 ? min(100, round(($monthlyTotal / $estimatedTarget) * 100)) : 0;
    $topTransaction     = $latestTransactionsCollection->sortByDesc('grand_total')->first();

    $latestChartData = $latestTransactionsCollection->take(7)->values();
    $statusTotal     = max($latestTransactionsCollection->count(), 1);

    $approvedPercent = round(($approvedCount / $statusTotal) * 100);
    $pendingPercent  = round(($pendingCount  / $statusTotal) * 100);
    $draftPercent    = round(($draftCount    / $statusTotal) * 100);
    $rejectedPercent = round(($rejectedCount / $statusTotal) * 100);

    $totalAlerts = $lowStockCount + $pendingApprovalCount + $pendingPoCount + $overdueInvoiceCount;
@endphp

<style>
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(14px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .du { animation: fadeUp .45s ease both; }
    .module-tile:hover .module-tile-icon { transform: scale(1.08); }
    .module-tile-icon { transition: transform .2s ease; }
</style>

<div class="space-y-5">

    {{-- ══════════════════════════════════════════
         ERP GREETING HEADER
    ══════════════════════════════════════════ --}}
    <div class="du [animation-delay:0ms] flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-1">
        <div>
            <div class="flex items-center gap-2.5 mb-1">
                <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-indigo-600 to-violet-700 flex items-center justify-center shadow-sm">
                    <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>
                    </svg>
                </div>
                <span class="text-xs font-bold text-indigo-600 uppercase tracking-widest">NOTAGO</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 leading-tight">
                Selamat datang, {{ auth()->user()->name ?? 'User' }}
            </h1>
            <p class="text-sm text-slate-400 mt-0.5">
                {{ now()->translatedFormat('l, d F Y') }}
                &bull; Berikut ringkasan operasional hari ini
            </p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            @if($totalAlerts > 0)
            <div class="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-rose-50 border border-rose-200">
                <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                <span class="text-xs font-bold text-rose-700">{{ $totalAlerts }} item perlu perhatian</span>
            </div>
            @endif
            <div class="px-3.5 py-2 rounded-xl bg-slate-100 border border-slate-200">
                <span class="text-xs font-semibold text-slate-600">{{ now()->format('H:i') }} WIB</span>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════
         ODOO-STYLE MODULE APP TILES
    ══════════════════════════════════════════ --}}
    <section class="du [animation-delay:40ms] bg-white rounded-2xl border border-slate-200 shadow-sm p-4">
        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3 px-1">Modul Aplikasi</p>
        <div class="grid grid-cols-4 sm:grid-cols-8 gap-2">
            @php
            $modules = [
                ['href' => route('purchase-transactions.index'), 'label' => 'Pembelian',   'color' => 'indigo',  'icon' => 'M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375'],
                ['href' => route('stock.index'),                 'label' => 'Inventori',   'color' => 'emerald', 'icon' => 'M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z'],
                ['href' => route('invoices.index'),             'label' => 'Invoice',     'color' => 'violet',  'icon' => 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z'],
                ['href' => route('purchase-orders.index'),      'label' => 'PO',          'color' => 'cyan',    'icon' => 'M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z'],
                ['href' => route('projects.index'),             'label' => 'Proyek',      'color' => 'amber',   'icon' => 'M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21'],
                ['href' => route('tax.dashboard'),              'label' => 'Perpajakan',  'color' => 'orange',  'icon' => 'M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0012 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 01-2.031.352'],
                ['href' => route('financial-reports.index'),    'label' => 'Laporan',     'color' => 'teal',    'icon' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zm9.75-9.75c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v16.5c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V3.375zM6.75 10.125c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125H7.875a1.125 1.125 0 01-1.125-1.125v-9.75z'],
                ['href' => route('warehouses.index'),           'label' => 'Gudang',      'color' => 'rose',    'icon' => 'M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016'],
            ];
            @endphp
            @foreach($modules as $mod)
            @php
                $colorMap = [
                    'indigo'  => ['bg'=>'bg-indigo-100',  'text'=>'text-indigo-600',  'hover'=>'hover:bg-indigo-50 hover:border-indigo-200'],
                    'emerald' => ['bg'=>'bg-emerald-100', 'text'=>'text-emerald-600', 'hover'=>'hover:bg-emerald-50 hover:border-emerald-200'],
                    'violet'  => ['bg'=>'bg-violet-100',  'text'=>'text-violet-600',  'hover'=>'hover:bg-violet-50 hover:border-violet-200'],
                    'cyan'    => ['bg'=>'bg-cyan-100',    'text'=>'text-cyan-600',    'hover'=>'hover:bg-cyan-50 hover:border-cyan-200'],
                    'amber'   => ['bg'=>'bg-amber-100',   'text'=>'text-amber-600',   'hover'=>'hover:bg-amber-50 hover:border-amber-200'],
                    'orange'  => ['bg'=>'bg-orange-100',  'text'=>'text-orange-600',  'hover'=>'hover:bg-orange-50 hover:border-orange-200'],
                    'teal'    => ['bg'=>'bg-teal-100',    'text'=>'text-teal-600',    'hover'=>'hover:bg-teal-50 hover:border-teal-200'],
                    'rose'    => ['bg'=>'bg-rose-100',    'text'=>'text-rose-600',    'hover'=>'hover:bg-rose-50 hover:border-rose-200'],
                ];
                $mc = $colorMap[$mod['color']] ?? $colorMap['indigo'];
            @endphp
            <a href="{{ $mod['href'] }}"
               class="module-tile flex flex-col items-center gap-2 p-3 rounded-xl border border-transparent {{ $mc['hover'] }} transition-all text-center group">
                <div class="module-tile-icon w-10 h-10 rounded-xl {{ $mc['bg'] }} flex items-center justify-center">
                    <svg class="h-5 w-5 {{ $mc['text'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $mod['icon'] }}"/>
                    </svg>
                </div>
                <span class="text-[11px] font-semibold text-slate-600 group-hover:{{ $mc['text'] }} transition-colors leading-tight">{{ $mod['label'] }}</span>
            </a>
            @endforeach
        </div>
    </section>

    {{-- ══════════════════════════════════════════
         ROW 1 — KPI METRIC CARDS
    ══════════════════════════════════════════ --}}
    <section class="grid grid-cols-2 gap-4 xl:grid-cols-4">

        {{-- Pengeluaran Bulan Ini --}}
        <div class="du [animation-delay:80ms] col-span-2 sm:col-span-1 relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-500 via-indigo-600 to-violet-700 p-5 shadow-lg shadow-indigo-200/50 text-white">
            <div class="pointer-events-none absolute -right-8 -top-8 h-32 w-32 rounded-full bg-white/10"></div>
            <div class="pointer-events-none absolute right-4 bottom-4 h-16 w-16 rounded-full bg-white/8"></div>
            <div class="relative z-10">
                <div class="flex items-start justify-between gap-2 mb-3">
                    <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center backdrop-blur-sm">
                        <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9"/>
                        </svg>
                    </div>
                    <span class="text-xs font-semibold text-indigo-200 bg-white/10 px-2 py-0.5 rounded-full">{{ now()->translatedFormat('M Y') }}</span>
                </div>
                <p class="text-[11px] font-bold uppercase tracking-widest text-indigo-200 mb-1">Pengeluaran Bulan Ini</p>
                <p class="text-3xl font-black leading-tight tracking-tight tabular-nums">
                    Rp {{ number_format($monthlyTotal / 1000000, 1, ',', '.') }}<span class="text-xl font-bold text-indigo-200">jt</span>
                </p>
                <div class="mt-2 flex items-center gap-1.5 text-xs text-indigo-200">
                    <svg class="h-3 w-3 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75 12 8.25l7.5 7.5"/></svg>
                    <span class="font-bold text-emerald-300">{{ $achievement }}%</span> dari estimasi target
                </div>
                <div class="mt-3 pt-3 border-t border-white/20 flex items-center justify-between text-xs">
                    <span class="text-indigo-200">{{ $monthlyCount }} transaksi</span>
                    <span class="text-indigo-200">All time: <span class="font-bold text-white">{{ $allTimeCount }}</span></span>
                </div>
            </div>
        </div>

        {{-- Aktivitas Hari Ini --}}
        <div class="du [animation-delay:120ms] relative rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-start justify-between gap-2 mb-3">
                <div class="w-9 h-9 rounded-xl bg-sky-100 flex items-center justify-center">
                    <svg class="h-4.5 w-4.5 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m5-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                @if($todayCount > 0)
                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>aktif
                </span>
                @endif
            </div>
            <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-1">Aktivitas Hari Ini</p>
            <p class="text-3xl font-black text-slate-900 tabular-nums leading-none">{{ $todayCount }}</p>
            <p class="text-xs text-slate-400 mt-1">transaksi</p>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-400">Total all time</span>
                <span class="font-bold text-slate-700 tabular-nums">{{ number_format($allTimeCount) }}</span>
            </div>
        </div>

        {{-- Supplier Aktif --}}
        <div class="du [animation-delay:160ms] relative rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-start justify-between gap-2 mb-3">
                <div class="w-9 h-9 rounded-xl bg-amber-100 flex items-center justify-center">
                    <svg class="h-4.5 w-4.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M5.25 21V8.25A2.25 2.25 0 017.5 6h9a2.25 2.25 0 012.25 2.25V21"/>
                    </svg>
                </div>
                <a href="{{ route('suppliers.index') }}" class="text-[11px] font-semibold text-indigo-500 hover:text-indigo-700 transition-colors" title="Lihat Supplier">
                    Lihat →
                </a>
            </div>
            <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-1">Supplier Aktif</p>
            <p class="text-3xl font-black text-slate-900 tabular-nums leading-none">{{ $supplierCount }}</p>
            <p class="text-xs text-slate-400 mt-1">vendor terdaftar</p>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-400">Rata-rata transaksi</span>
                <span class="font-bold text-slate-700 tabular-nums">Rp {{ number_format($avgTransaction / 1000000, 1, ',', '.') }}jt</span>
            </div>
        </div>

        {{-- Perlu Perhatian --}}
        <div class="du [animation-delay:200ms] relative rounded-2xl border {{ $totalAlerts > 0 ? 'border-rose-200 bg-rose-50/30' : 'border-slate-200 bg-white' }} p-5 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-start justify-between gap-2 mb-3">
                <div class="w-9 h-9 rounded-xl {{ $totalAlerts > 0 ? 'bg-rose-100' : 'bg-slate-100' }} flex items-center justify-center">
                    <svg class="h-4.5 w-4.5 {{ $totalAlerts > 0 ? 'text-rose-600' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z"/>
                    </svg>
                </div>
                @if($totalAlerts > 0)
                <span class="text-[11px] font-bold text-rose-500 animate-pulse">perlu aksi</span>
                @endif
            </div>
            <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-1">Perlu Perhatian</p>
            <p class="text-3xl font-black {{ $totalAlerts > 0 ? 'text-rose-600' : 'text-slate-900' }} tabular-nums leading-none">{{ $totalAlerts }}</p>
            <p class="text-xs text-slate-400 mt-1">item perlu tindakan</p>
            <div class="mt-3 pt-3 border-t {{ $totalAlerts > 0 ? 'border-rose-100' : 'border-slate-100' }} flex items-center gap-3 text-xs">
                <span class="{{ $lowStockCount > 0 ? 'text-amber-600 font-semibold' : 'text-slate-400' }}">Stok: {{ $lowStockCount }}</span>
                <span class="{{ $pendingApprovalCount > 0 ? 'text-rose-600 font-semibold' : 'text-slate-400' }}">Approval: {{ $pendingApprovalCount }}</span>
            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════════
         ALERT STRIP — 4 Module Alerts
    ══════════════════════════════════════════ --}}
    <section class="du [animation-delay:240ms] grid grid-cols-2 gap-3 xl:grid-cols-4">
        @php
        $alerts = [
            ['href' => route('stock.index', ['alert'=>'1']),                                 'count' => $lowStockCount,         'label' => 'Stok Menipis',   'sub' => 'Segera reorder',     'color' => 'amber', 'icon' => 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z'],
            ['href' => route('purchase-transactions.index', ['approval_status'=>'pending']),  'count' => $pendingApprovalCount, 'label' => 'Perlu Approval', 'sub' => 'Menunggu review',    'color' => 'orange','icon' => 'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z'],
            ['href' => route('purchase-orders.index', ['status'=>'submitted']),               'count' => $pendingPoCount,        'label' => 'PO Pending',     'sub' => 'Menunggu proses',    'color' => 'violet','icon' => 'M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z'],
            ['href' => route('invoices.index', ['status'=>'overdue']),                        'count' => $overdueInvoiceCount,   'label' => 'Invoice Overdue','sub' => 'Lewat jatuh tempo',  'color' => 'rose',  'icon' => 'M12 9v3.75m0 3.75h.007v.008H12v-.008zM21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        ];
        $colorMap2 = [
            'amber'  => ['border'=>'border-amber-200',  'bg'=>'bg-amber-50',  'icon_bg'=>'bg-amber-100',  'icon_text'=>'text-amber-600',  'num'=>'text-amber-700',  'sub'=>'text-amber-600'],
            'orange' => ['border'=>'border-orange-200', 'bg'=>'bg-orange-50', 'icon_bg'=>'bg-orange-100', 'icon_text'=>'text-orange-600', 'num'=>'text-orange-700', 'sub'=>'text-orange-600'],
            'violet' => ['border'=>'border-violet-200', 'bg'=>'bg-violet-50', 'icon_bg'=>'bg-violet-100', 'icon_text'=>'text-violet-600', 'num'=>'text-violet-700', 'sub'=>'text-violet-600'],
            'rose'   => ['border'=>'border-rose-200',   'bg'=>'bg-rose-50',   'icon_bg'=>'bg-rose-100',   'icon_text'=>'text-rose-600',   'num'=>'text-rose-700',   'sub'=>'text-rose-600'],
        ];
        @endphp
        @foreach($alerts as $a)
        @php
            $active = $a['count'] > 0;
            $ac = $colorMap2[$a['color']];
        @endphp
        <a href="{{ $a['href'] }}"
           class="group flex items-center gap-3.5 rounded-2xl border {{ $active ? $ac['border'].' '.$ac['bg'] : 'border-slate-100 bg-white' }} p-4 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md">
            <div class="flex-shrink-0 w-10 h-10 rounded-xl {{ $active ? $ac['icon_bg'] : 'bg-slate-100' }} flex items-center justify-center">
                <svg class="h-5 w-5 {{ $active ? $ac['icon_text'] : 'text-slate-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $a['icon'] }}"/>
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-2xl font-black {{ $active ? $ac['num'] : 'text-slate-300' }} tabular-nums leading-none">{{ $a['count'] }}</p>
                <p class="text-[11px] font-bold text-slate-600 mt-0.5 leading-none">{{ $a['label'] }}</p>
                <p class="text-[10px] {{ $active ? $ac['sub'] : 'text-slate-300' }} mt-0.5">{{ $a['sub'] }}</p>
            </div>
        </a>
        @endforeach
    </section>

    {{-- ══════════════════════════════════════════
         ROW 2 — PERFORMANCE BAR CHART + STATUS DONUT
    ══════════════════════════════════════════ --}}
    <section class="grid grid-cols-1 gap-5 xl:grid-cols-[1.6fr_1fr]">

        {{-- Bar Chart: Performance --}}
        <div class="du [animation-delay:300ms] rounded-2xl border border-slate-200 bg-white shadow-sm p-5 md:p-6">
            <div class="flex items-center justify-between gap-3 mb-5">
                <div>
                    <h3 class="text-[15px] font-bold text-slate-800">Performa Transaksi</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Nominal per transaksi terbaru</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-slate-500 bg-slate-100 border border-slate-200 px-2.5 py-1 rounded-lg">10 Terbaru</span>
                </div>
            </div>

            @if($latestChartData->count())
            <div class="relative">
                {{-- Y-axis guides --}}
                <div class="absolute inset-x-0 top-0 h-[180px] flex flex-col justify-between pointer-events-none">
                    @foreach([100, 75, 50, 25, 0] as $pct)
                    <div class="flex items-center gap-2">
                        <span class="w-6 text-right text-[10px] text-slate-300 shrink-0">{{ $pct }}%</span>
                        <div class="flex-1 border-t border-dashed border-slate-100"></div>
                    </div>
                    @endforeach
                </div>
                {{-- Bars --}}
                <div class="ml-8 flex items-end gap-2.5 h-[180px]">
                    @foreach($latestChartData as $idx => $trx)
                    @php
                        $pct = max(8, round(((float)$trx->grand_total / $maxAmount) * 100));
                        $st  = strtolower($trx->status ?? '');
                        $col = match($st) {
                            'approved','posted' => '#6366f1',
                            'pending'           => '#f59e0b',
                            'rejected'          => '#f43f5e',
                            'draft'             => '#cbd5e1',
                            default             => '#6366f1',
                        };
                    @endphp
                    <div class="group flex flex-1 flex-col items-center gap-1.5 relative">
                        <div class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 hidden group-hover:block z-10 pointer-events-none">
                            <div class="rounded-xl bg-slate-900 px-3 py-2 text-[11px] text-white shadow-xl whitespace-nowrap">
                                <div class="font-bold">{{ $trx->transaction_no }}</div>
                                <div class="text-slate-300 mt-0.5">Rp {{ number_format($trx->grand_total, 0, ',', '.') }}</div>
                                <div class="text-slate-400 capitalize">{{ $trx->status }}</div>
                            </div>
                        </div>
                        <div class="w-full rounded-t-lg transition-all duration-500 hover:opacity-100"
                             style="height:{{ $pct }}%; background-color:{{ $col }}; opacity:{{ $idx === 3 ? '1' : '0.45' }};">
                        </div>
                    </div>
                    @endforeach
                </div>
                {{-- X labels --}}
                <div class="ml-8 mt-2 flex items-center gap-2.5">
                    @foreach($latestChartData as $trx)
                    <div class="flex-1 text-center truncate text-[10px] text-slate-400">
                        {{ $trx->transaction_date?->format('d/m') ?? '-' }}
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-5 grid grid-cols-4 gap-2 border-t border-slate-100 pt-4">
                @foreach([
                    ['label'=>'Approved','val'=>$approvedCount,'color'=>'text-indigo-600','bg'=>'bg-indigo-100'],
                    ['label'=>'Pending', 'val'=>$pendingCount, 'color'=>'text-amber-600', 'bg'=>'bg-amber-100'],
                    ['label'=>'Draft',   'val'=>$draftCount,   'color'=>'text-slate-500', 'bg'=>'bg-slate-100'],
                    ['label'=>'Rejected','val'=>$rejectedCount,'color'=>'text-rose-600',  'bg'=>'bg-rose-100'],
                ] as $stat)
                <div class="text-center p-2 rounded-xl {{ $stat['bg'] }}">
                    <p class="text-xl font-black {{ $stat['color'] }} tabular-nums">{{ $stat['val'] }}</p>
                    <p class="text-[10px] font-semibold text-slate-500 mt-0.5">{{ $stat['label'] }}</p>
                </div>
                @endforeach
            </div>
            @else
            <div class="flex flex-col items-center justify-center h-[220px] text-slate-300">
                <svg class="h-12 w-12 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18M7 15l3.5-3.5 3 3L19 8"/>
                </svg>
                <p class="text-sm font-medium text-slate-400">Belum ada data transaksi</p>
            </div>
            @endif
        </div>

        {{-- Status Donut --}}
        <div class="du [animation-delay:360ms] rounded-2xl border border-slate-200 bg-white shadow-sm p-5 md:p-6">
            <div class="mb-5">
                <h3 class="text-[15px] font-bold text-slate-800">Status Transaksi</h3>
                <p class="text-xs text-slate-400 mt-0.5">Distribusi status 10 transaksi terbaru</p>
            </div>

            <div class="flex items-center justify-center my-2">
                <div class="relative">
                    <svg width="160" height="100" viewBox="0 0 160 100">
                        <path d="M 14 90 A 66 66 0 0 1 146 90" fill="none" stroke="#f1f5f9" stroke-width="14" stroke-linecap="round"/>
                        <path d="M 14 90 A 66 66 0 0 1 146 90" fill="none" stroke="#6366f1" stroke-width="14" stroke-linecap="round"
                              stroke-dasharray="{{ round($approvedPercent * 2.07) }} 207" stroke-dashoffset="0"/>
                        <path d="M 14 90 A 66 66 0 0 1 146 90" fill="none" stroke="#f59e0b" stroke-width="14" stroke-linecap="round"
                              stroke-dasharray="{{ round($pendingPercent * 2.07) }} 207" stroke-dashoffset="{{ -round($approvedPercent * 2.07) }}" opacity="0.85"/>
                        <path d="M 14 90 A 66 66 0 0 1 146 90" fill="none" stroke="#cbd5e1" stroke-width="14" stroke-linecap="round"
                              stroke-dasharray="{{ round($draftPercent * 2.07) }} 207" stroke-dashoffset="{{ -(round($approvedPercent * 2.07) + round($pendingPercent * 2.07)) }}" opacity="0.7"/>
                        <path d="M 14 90 A 66 66 0 0 1 146 90" fill="none" stroke="#f43f5e" stroke-width="14" stroke-linecap="round"
                              stroke-dasharray="{{ round($rejectedPercent * 2.07) }} 207" stroke-dashoffset="{{ -(round($approvedPercent * 2.07) + round($pendingPercent * 2.07) + round($draftPercent * 2.07)) }}" opacity="0.7"/>
                        <text x="80" y="80" text-anchor="middle" font-size="24" font-weight="900" fill="#1e293b" font-family="Inter,sans-serif">{{ $approvedPercent }}%</text>
                    </svg>
                    <p class="text-center text-[11px] font-semibold text-slate-400 -mt-1">Approval Rate</p>
                </div>
            </div>

            <div class="mt-4 space-y-2.5">
                @foreach([
                    ['label'=>'Approved','pct'=>$approvedPercent,'color'=>'bg-indigo-500'],
                    ['label'=>'Pending', 'pct'=>$pendingPercent, 'color'=>'bg-amber-400'],
                    ['label'=>'Draft',   'pct'=>$draftPercent,   'color'=>'bg-slate-300'],
                    ['label'=>'Rejected','pct'=>$rejectedPercent,'color'=>'bg-rose-400'],
                ] as $row)
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="h-2 w-2 flex-shrink-0 rounded-full {{ $row['color'] }}"></span>
                        <span class="text-xs font-medium text-slate-600">{{ $row['label'] }}</span>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <div class="h-1.5 w-20 rounded-full bg-slate-100 overflow-hidden">
                            <div class="h-full rounded-full {{ $row['color'] }} transition-all" style="width:{{ $row['pct'] }}%"></div>
                        </div>
                        <span class="text-xs font-black text-slate-700 w-8 text-right tabular-nums">{{ $row['pct'] }}%</span>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="mt-5 pt-4 border-t border-slate-100 grid grid-cols-2 gap-3">
                <div class="rounded-xl bg-slate-50 border border-slate-100 p-3">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Jumlah Data</p>
                    <div class="mt-1 flex items-center gap-1.5">
                        <p class="text-lg font-black text-slate-800">{{ $latestTransactionsCollection->count() }}</p>
                        <span class="text-[10px] font-bold text-indigo-600 bg-indigo-100 px-1.5 py-0.5 rounded-full">terbaru</span>
                    </div>
                </div>
                <div class="rounded-xl bg-slate-50 border border-slate-100 p-3">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Top Nominal</p>
                    <div class="mt-1 flex items-center gap-1.5">
                        <p class="text-sm font-black text-slate-800 tabular-nums">Rp {{ number_format((float)($topTransaction->grand_total ?? 0) / 1000000, 1, ',', '.') }}jt</p>
                        <span class="text-[10px] font-bold text-emerald-600 bg-emerald-100 px-1.5 py-0.5 rounded-full">max</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════════
         ROW 3 — TREND LINE CHART + ACTIVITY FEED
    ══════════════════════════════════════════ --}}
    <section class="du [animation-delay:420ms] grid grid-cols-1 gap-5 xl:grid-cols-[1.55fr_1fr]">

        {{-- Trend Chart --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-5 md:p-6">
            <div class="flex items-center justify-between gap-3 mb-5">
                <div>
                    <h3 class="text-[15px] font-bold text-slate-800">Tren 6 Bulan</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Pengeluaran vs Invoice per bulan</p>
                </div>
                <div class="flex items-center gap-3 text-xs flex-shrink-0">
                    <span class="flex items-center gap-1.5 text-slate-500"><span class="inline-block w-3 h-0.5 bg-indigo-500 rounded"></span>Pengeluaran</span>
                    <span class="flex items-center gap-1.5 text-slate-500"><span class="inline-block w-3 h-0.5 bg-emerald-500 rounded"></span>Invoice</span>
                    <a href="{{ route('cash-flow.index') }}" class="text-indigo-500 font-semibold hover:text-indigo-700 transition-colors ml-1">Detail →</a>
                </div>
            </div>
            <div style="height:220px;">
                <canvas id="proTrendChart"></canvas>
            </div>
        </div>

        {{-- Activity Feed --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-5 md:p-6">
            <div class="flex items-center justify-between gap-3 mb-4">
                <div>
                    <h3 class="text-[15px] font-bold text-slate-800">Aktivitas Terbaru</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Log operasional sistem</p>
                </div>
                <a href="{{ route('purchase-transactions.index') }}" class="text-xs text-indigo-500 font-semibold hover:text-indigo-700 transition-colors">
                    Semua →
                </a>
            </div>
            <div class="space-y-0.5 max-h-[232px] overflow-y-auto">
                @forelse($recentActivities as $act)
                @php
                    $typeConfig = match($act->activity_type) {
                        'transaction'      => ['icon_bg'=>'bg-indigo-100','icon_text'=>'text-indigo-600','icon'=>'M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75'],
                        'purchase_order'   => ['icon_bg'=>'bg-violet-100','icon_text'=>'text-violet-600','icon'=>'M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4'],
                        'material_request' => ['icon_bg'=>'bg-amber-100', 'icon_text'=>'text-amber-600', 'icon'=>'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108'],
                        default            => ['icon_bg'=>'bg-slate-100', 'icon_text'=>'text-slate-500', 'icon'=>'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5'],
                    };
                    $dot = match(strtolower($act->status ?? '')) {
                        'approved','converted' => 'bg-emerald-400',
                        'pending','submitted'  => 'bg-amber-400',
                        'rejected'             => 'bg-rose-400',
                        default                => 'bg-slate-300',
                    };
                @endphp
                <div class="flex items-center gap-3 rounded-xl p-2.5 hover:bg-slate-50 transition-colors">
                    <div class="flex-shrink-0 w-8 h-8 rounded-xl {{ $typeConfig['icon_bg'] }} flex items-center justify-center">
                        <svg class="h-3.5 w-3.5 {{ $typeConfig['icon_text'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $typeConfig['icon'] }}"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-slate-800 truncate">{{ $act->label }}</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">{{ $act->created_at?->diffForHumans() }}</p>
                    </div>
                    <div class="flex items-center gap-1.5 flex-shrink-0">
                        @if($act->amount)
                        <span class="text-[11px] font-bold text-slate-600 tabular-nums">Rp {{ number_format($act->amount / 1000000, 1, ',', '.') }}jt</span>
                        @endif
                        <span class="h-2 w-2 rounded-full {{ $dot }} flex-shrink-0"></span>
                    </div>
                </div>
                @empty
                <div class="flex flex-col items-center justify-center h-40 text-slate-300">
                    <svg class="h-10 w-10 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m5-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm font-medium">Belum ada aktivitas</p>
                </div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════════
         ROW 4 — TRANSAKSI TERBARU TABLE (ERP Style)
    ══════════════════════════════════════════ --}}
    <section class="du [animation-delay:480ms] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between gap-4 border-b border-slate-100 px-5 py-4 md:px-6">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-indigo-100 flex items-center justify-center">
                    <svg class="h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-[15px] font-bold text-slate-800">Transaksi Terbaru</h3>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $latestTransactionsCollection->count() }} transaksi dipantau</p>
                </div>
            </div>
            <a href="{{ route('purchase-transactions.index') }}"
               class="flex-shrink-0 inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-indigo-50 hover:border-indigo-200 hover:text-indigo-600 transition-all">
                Lihat Semua
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/60">
                        <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400 md:px-6">#</th>
                        <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400 md:px-6">No. Transaksi</th>
                        <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400 md:px-6">Tanggal</th>
                        <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400 md:px-6">Supplier</th>
                        <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400 md:px-6">Proyek</th>
                        <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400 md:px-6">Status</th>
                        <th class="px-5 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-400 md:px-6">Nominal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($latestTransactions as $i => $trx)
                    @php
                        $st = strtolower($trx->status ?? '');
                        $badge = match($st) {
                            'approved','posted' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            'pending'           => 'bg-amber-50 text-amber-700 border-amber-200',
                            'rejected'          => 'bg-rose-50 text-rose-700 border-rose-200',
                            'draft'             => 'bg-slate-100 text-slate-600 border-slate-200',
                            default             => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                        };
                        $dot = match($st) {
                            'approved','posted' => 'bg-emerald-400',
                            'pending'           => 'bg-amber-400',
                            'rejected'          => 'bg-rose-400',
                            default             => 'bg-slate-300',
                        };
                    @endphp
                    <tr class="group hover:bg-slate-50/80 transition-colors">
                        <td class="px-5 py-3.5 md:px-6">
                            <span class="text-[11px] font-bold text-slate-300 tabular-nums">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                        </td>
                        <td class="px-5 py-3.5 md:px-6">
                            <a href="{{ route('purchase-transactions.show', $trx) }}"
                               class="text-[13px] font-bold text-indigo-700 hover:text-indigo-900 hover:underline transition-colors">
                                {{ $trx->transaction_no }}
                            </a>
                        </td>
                        <td class="px-5 py-3.5 text-[13px] text-slate-500 tabular-nums md:px-6">
                            {{ $trx->transaction_date?->format('d/m/Y') ?? '—' }}
                        </td>
                        <td class="px-5 py-3.5 md:px-6">
                            <span class="text-[13px] font-semibold text-slate-700">{{ $trx->supplier?->name ?? '—' }}</span>
                        </td>
                        <td class="px-5 py-3.5 md:px-6">
                            @if($trx->project)
                            <span class="text-[11px] font-semibold text-indigo-600 bg-indigo-50 border border-indigo-200 px-2 py-0.5 rounded-full">
                                {{ Str::limit($trx->project->name, 20) }}
                            </span>
                            @else
                            <span class="text-[13px] text-slate-300">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 md:px-6">
                            <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[11px] font-semibold {{ $badge }}">
                                <span class="h-1.5 w-1.5 rounded-full {{ $dot }}"></span>
                                {{ ucfirst($trx->status) }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-right md:px-6">
                            <span class="text-[13px] font-black text-slate-900 tabular-nums">
                                Rp {{ number_format($trx->grand_total, 0, ',', '.') }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-14 text-center">
                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-300 mb-3">
                                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75A2.25 2.25 0 016 4.5h12A2.25 2.25 0 0120.25 6.75v10.5A2.25 2.25 0 0118 19.5H6a2.25 2.25 0 01-2.25-2.25V6.75z"/>
                                </svg>
                            </div>
                            <p class="text-sm font-semibold text-slate-400 mb-1">Belum ada transaksi</p>
                            <p class="text-xs text-slate-300">Mulai catat transaksi pembelian pertama Anda</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($latestTransactionsCollection->count() > 0)
        <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
            <span class="text-xs text-slate-400">
                Total nilai: <span class="font-bold text-slate-700 tabular-nums">Rp {{ number_format($latestTotalNominal, 0, ',', '.') }}</span>
            </span>
            <a href="{{ route('purchase-transactions.index') }}" class="text-xs text-indigo-500 font-semibold hover:text-indigo-700 transition-colors">
                Lihat semua transaksi →
            </a>
        </div>
        @endif
    </section>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
Chart.defaults.font.family = 'Inter, sans-serif';

new Chart(document.getElementById('proTrendChart'), {
    type: 'line',
    data: {
        labels: @json($chartMonths),
        datasets: [
            {
                label: 'Pengeluaran',
                data: @json($chartSpend),
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99,102,241,0.07)',
                borderWidth: 2,
                pointRadius: 3.5,
                pointBackgroundColor: '#6366f1',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                tension: 0.4,
                fill: true,
            },
            {
                label: 'Invoice',
                data: @json($chartInvoice),
                borderColor: '#10b981',
                backgroundColor: 'rgba(16,185,129,0.07)',
                borderWidth: 2,
                pointRadius: 3.5,
                pointBackgroundColor: '#10b981',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                tension: 0.4,
                fill: true,
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#0f172a',
                titleColor: '#94a3b8',
                bodyColor: '#f8fafc',
                padding: 12,
                cornerRadius: 10,
                borderColor: 'rgba(148,163,184,0.15)',
                borderWidth: 1,
                callbacks: { label: c => '  ' + c.dataset.label + ': Rp ' + c.parsed.y.toLocaleString('id-ID') }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: 'rgba(148,163,184,0.1)', borderDash: [4,4] },
                ticks: {
                    color: '#94a3b8',
                    font: { size: 10 },
                    callback: v => {
                        if (v >= 1000000) return 'Rp ' + (v/1000000).toFixed(0) + 'jt';
                        if (v >= 1000) return 'Rp ' + (v/1000).toFixed(0) + 'rb';
                        return 'Rp ' + v;
                    }
                }
            },
            x: {
                grid: { display: false },
                ticks: { color: '#94a3b8', font: { size: 10 } }
            }
        }
    }
});
</script>
@endpush
@endsection
