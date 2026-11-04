@extends('layouts.app')

@section('title', 'Dashboard Akuntansi')

@section('content')

{{-- Breadcrumb --}}
<nav class="flex items-center gap-1.5 text-xs text-slate-400 mb-4">
    <a href="{{ route('dashboard') }}" class="hover:text-slate-600 transition-colors">Dashboard</a>
    <svg class="h-3 w-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-slate-700 font-semibold">Akuntansi</span>
</nav>

{{-- Professional Header --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5">
    <div class="flex items-center gap-3">
        <div class="flex-shrink-0 w-11 h-11 rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-700 flex items-center justify-center shadow-md">
            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5M9 11.25v1.5M12 9v3.75m3-6v6"/>
            </svg>
        </div>
        <div>
            <h1 class="text-xl font-bold text-slate-800 leading-tight">Dashboard Akuntansi</h1>
            <p class="text-sm text-slate-500">Ringkasan keuangan &mdash; posisi kas, laba rugi, piutang &amp; hutang</p>
        </div>
    </div>
    {{-- Period Selector --}}
    <form method="GET" class="flex items-center gap-2 flex-wrap flex-shrink-0">
        <div class="flex items-center gap-2 bg-white border border-slate-200 rounded-xl px-3 py-1.5 shadow-sm">
            <svg class="h-4 w-4 text-slate-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5"/>
            </svg>
            <select name="month" class="text-sm text-slate-700 bg-transparent border-none outline-none focus:ring-0 pr-1">
                @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $i => $nm)
                <option value="{{ $i+1 }}" @selected($month == $i+1)>{{ $nm }}</option>
                @endforeach
            </select>
            <span class="text-slate-300">|</span>
            <select name="year" class="text-sm text-slate-700 bg-transparent border-none outline-none focus:ring-0">
                @for($y = now()->year; $y >= now()->year - 3; $y--)
                <option value="{{ $y }}" @selected($year == $y)>{{ $y }}</option>
                @endfor
            </select>
        </div>
        <button type="submit"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition-all shadow-sm">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803 7.5 7.5 0 0015.803 15.803z"/></svg>
            Tampilkan
        </button>
    </form>
</div>

{{-- Accounting Module Navigation --}}
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-1.5 mb-5">
    <div class="flex flex-wrap gap-1">
        @php
        $navLinks = [
            ['label' => 'Ikhtisar',      'route' => route('accounting.dashboard'), 'active' => true,  'color' => 'indigo'],
            ['label' => 'Jurnal Umum',   'route' => route('journal.index'),         'active' => false, 'color' => 'slate'],
            ['label' => 'Bagan Akun',    'route' => route('coa.index'),             'active' => false, 'color' => 'slate'],
            ['label' => 'Neraca Saldo',  'route' => route('trial-balance.index'),   'active' => false, 'color' => 'slate'],
            ['label' => 'Piutang',       'route' => route('receivables.index'),     'active' => false, 'color' => 'slate'],
            ['label' => 'Hutang',        'route' => route('payables.index'),        'active' => false, 'color' => 'slate'],
            ['label' => 'Arus Kas',      'route' => route('cash-flow.index'),       'active' => false, 'color' => 'slate'],
        ];
        @endphp
        @foreach($navLinks as $nav)
        <a href="{{ $nav['route'] }}"
           class="inline-flex items-center px-3.5 py-2 rounded-xl text-xs font-semibold transition-all
                  {{ $nav['active'] ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-700' }}">
            {{ $nav['label'] }}
        </a>
        @endforeach
        <a href="{{ route('journal.create') }}"
           class="ml-auto inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 transition-all shadow-sm">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Jurnal Baru
        </a>
    </div>
</div>


{{-- ═══════════════════════════════════════════
     ROW 1: 4 KPI CARDS
═══════════════════════════════════════════ --}}
<div class="grid grid-cols-2 gap-4 xl:grid-cols-4 mb-5">

    {{-- Cash In --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 relative overflow-hidden group hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
        <div class="absolute -top-5 -right-5 w-20 h-20 rounded-full bg-emerald-50 group-hover:scale-125 transition-transform duration-300"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-3">
                <div class="w-8 h-8 rounded-xl bg-emerald-100 flex items-center justify-center">
                    <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941"/>
                    </svg>
                </div>
                <span class="text-xs font-semibold text-emerald-600 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full">Cash In</span>
            </div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Penerimaan</p>
            <p class="text-2xl font-black text-emerald-700 tabular-nums leading-none">Rp {{ number_format($cashFlow['cash_in'] / 1e6, 1, ',', '.') }}jt</p>
            <p class="text-xs text-slate-400 mt-1.5">Rp {{ number_format($cashFlow['cash_in'], 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Cash Out --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 relative overflow-hidden group hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
        <div class="absolute -top-5 -right-5 w-20 h-20 rounded-full bg-rose-50 group-hover:scale-125 transition-transform duration-300"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-3">
                <div class="w-8 h-8 rounded-xl bg-rose-100 flex items-center justify-center">
                    <svg class="h-4 w-4 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6L9 12.75l4.286-4.286a11.948 11.948 0 014.306 6.43l.776 2.898m0 0l3.182-5.511m-3.182 5.51l-5.511-3.181"/>
                    </svg>
                </div>
                <span class="text-xs font-semibold text-rose-600 bg-rose-50 border border-rose-200 px-2 py-0.5 rounded-full">Cash Out</span>
            </div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Pengeluaran</p>
            <p class="text-2xl font-black text-rose-700 tabular-nums leading-none">Rp {{ number_format($cashFlow['cash_out'] / 1e6, 1, ',', '.') }}jt</p>
            <p class="text-xs text-slate-400 mt-1.5">Material + Subkontrak</p>
        </div>
    </div>

    {{-- Net Flow --}}
    @php $net = $cashFlow['net_flow']; @endphp
    <div class="bg-white rounded-2xl border {{ $net >= 0 ? 'border-indigo-200' : 'border-amber-200' }} shadow-sm p-5 relative overflow-hidden group hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
        <div class="absolute -top-5 -right-5 w-20 h-20 rounded-full {{ $net >= 0 ? 'bg-indigo-50' : 'bg-amber-50' }} group-hover:scale-125 transition-transform duration-300"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-3">
                <div class="w-8 h-8 rounded-xl {{ $net >= 0 ? 'bg-indigo-100' : 'bg-amber-100' }} flex items-center justify-center">
                    <svg class="h-4 w-4 {{ $net >= 0 ? 'text-indigo-600' : 'text-amber-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $net >= 0 ? 'text-indigo-600 bg-indigo-50 border border-indigo-200' : 'text-amber-600 bg-amber-50 border border-amber-200' }}">
                    {{ $net >= 0 ? 'Positif' : 'Negatif' }}
                </span>
            </div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Net Cash Flow</p>
            <p class="text-2xl font-black {{ $net >= 0 ? 'text-indigo-700' : 'text-amber-700' }} tabular-nums leading-none">
                {{ $net < 0 ? '−' : '' }}Rp {{ number_format(abs($net) / 1e6, 1, ',', '.') }}jt
            </p>
            <p class="text-xs {{ $net >= 0 ? 'text-emerald-600' : 'text-amber-600' }} font-semibold mt-1.5">
                {{ $net >= 0 ? '▲ Arus Kas Positif' : '▼ Arus Kas Negatif' }}
            </p>
        </div>
    </div>

    {{-- Gross Profit --}}
    <div class="bg-white rounded-2xl border {{ $pl['gross_profit'] >= 0 ? 'border-teal-200' : 'border-rose-200' }} shadow-sm p-5 relative overflow-hidden group hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
        <div class="absolute -top-5 -right-5 w-20 h-20 rounded-full {{ $pl['gross_profit'] >= 0 ? 'bg-teal-50' : 'bg-rose-50' }} group-hover:scale-125 transition-transform duration-300"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-3">
                <div class="w-8 h-8 rounded-xl {{ $pl['gross_profit'] >= 0 ? 'bg-teal-100' : 'bg-rose-100' }} flex items-center justify-center">
                    <svg class="h-4 w-4 {{ $pl['gross_profit'] >= 0 ? 'text-teal-600' : 'text-rose-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5M9 11.25v1.5M12 9v3.75m3-6v6"/>
                    </svg>
                </div>
                <span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $pl['gross_profit'] >= 0 ? 'text-teal-600 bg-teal-50 border border-teal-200' : 'text-rose-600 bg-rose-50 border border-rose-200' }}">
                    Gross Profit
                </span>
            </div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Laba Kotor</p>
            <p class="text-2xl font-black {{ $pl['gross_profit'] >= 0 ? 'text-teal-700' : 'text-rose-700' }} tabular-nums leading-none">
                Rp {{ number_format(abs($pl['gross_profit']) / 1e6, 1, ',', '.') }}jt
            </p>
            <p class="text-xs text-slate-400 mt-1.5">Margin <span class="font-bold {{ $pl['gross_margin_pct'] > 20 ? 'text-teal-600' : ($pl['gross_margin_pct'] > 0 ? 'text-amber-600' : 'text-rose-600') }}">{{ number_format($pl['gross_margin_pct'], 1) }}%</span></p>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════
     ROW 2: Alert Piutang + Hutang + Quick Links
═══════════════════════════════════════════ --}}
<div class="grid grid-cols-1 gap-4 sm:grid-cols-3 mb-5">

    {{-- Piutang Alert --}}
    <a href="{{ route('receivables.index') }}"
       class="group bg-white rounded-2xl border {{ $receivableSummary['overdue_amount'] > 0 ? 'border-rose-200' : 'border-blue-200' }} shadow-sm p-4 flex items-center gap-4 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
        <div class="flex-shrink-0 w-12 h-12 rounded-xl {{ $receivableSummary['overdue_amount'] > 0 ? 'bg-rose-100' : 'bg-blue-100' }} flex items-center justify-center">
            <svg class="h-6 w-6 {{ $receivableSummary['overdue_amount'] > 0 ? 'text-rose-600' : 'text-blue-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/>
            </svg>
        </div>
        <div class="min-w-0 flex-1">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-0.5">Total Piutang</p>
            <p class="text-xl font-black text-blue-700 tabular-nums leading-none">Rp {{ number_format($receivableSummary['total_outstanding'] / 1e6, 1, ',', '.') }}jt</p>
            @if($receivableSummary['overdue_amount'] > 0)
            <p class="text-xs text-rose-600 font-semibold mt-1 flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-rose-400 animate-pulse flex-shrink-0"></span>
                Overdue: Rp {{ number_format($receivableSummary['overdue_amount'] / 1e6, 1, ',', '.') }}jt
            </p>
            @else
            <p class="text-xs text-emerald-600 font-semibold mt-1 flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 flex-shrink-0"></span>
                Tidak ada yang overdue
            </p>
            @endif
        </div>
        <svg class="h-4 w-4 text-slate-300 group-hover:text-slate-500 flex-shrink-0 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
    </a>

    {{-- Hutang Alert --}}
    <a href="{{ route('payables.index') }}"
       class="group bg-white rounded-2xl border {{ $payableSummary['overdue_amount'] > 0 ? 'border-rose-200' : 'border-orange-200' }} shadow-sm p-4 flex items-center gap-4 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
        <div class="flex-shrink-0 w-12 h-12 rounded-xl {{ $payableSummary['overdue_amount'] > 0 ? 'bg-rose-100' : 'bg-orange-100' }} flex items-center justify-center">
            <svg class="h-6 w-6 {{ $payableSummary['overdue_amount'] > 0 ? 'text-rose-600' : 'text-orange-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75"/>
            </svg>
        </div>
        <div class="min-w-0 flex-1">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-0.5">Total Hutang</p>
            <p class="text-xl font-black text-orange-600 tabular-nums leading-none">Rp {{ number_format($payableSummary['total_outstanding'] / 1e6, 1, ',', '.') }}jt</p>
            @if($payableSummary['overdue_amount'] > 0)
            <p class="text-xs text-rose-600 font-semibold mt-1 flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-rose-400 animate-pulse flex-shrink-0"></span>
                Overdue: Rp {{ number_format($payableSummary['overdue_amount'] / 1e6, 1, ',', '.') }}jt
            </p>
            @else
            <p class="text-xs text-emerald-600 font-semibold mt-1 flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 flex-shrink-0"></span>
                Tidak ada yang overdue
            </p>
            @endif
        </div>
        <svg class="h-4 w-4 text-slate-300 group-hover:text-slate-500 flex-shrink-0 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
    </a>

    {{-- Quick Access --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4">
        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">Akses Cepat</p>
        <div class="grid grid-cols-2 gap-2">
            @php
            $quickLinks = [
                ['href' => route('coa.index'),          'label' => 'Bagan Akun',   'color' => 'indigo'],
                ['href' => route('trial-balance.index'), 'label' => 'Neraca Saldo', 'color' => 'violet'],
                ['href' => route('cash-flow.index'),     'label' => 'Arus Kas',     'color' => 'teal'],
                ['href' => route('tax-report.index'),    'label' => 'Rekap Pajak',  'color' => 'amber'],
            ];
            $colorMap = [
                'indigo' => 'bg-indigo-50 text-indigo-700 border-indigo-200 hover:bg-indigo-100',
                'violet' => 'bg-violet-50 text-violet-700 border-violet-200 hover:bg-violet-100',
                'teal'   => 'bg-teal-50 text-teal-700 border-teal-200 hover:bg-teal-100',
                'amber'  => 'bg-amber-50 text-amber-700 border-amber-200 hover:bg-amber-100',
            ];
            @endphp
            @foreach($quickLinks as $ql)
            <a href="{{ $ql['href'] }}"
               class="flex items-center justify-center text-center px-2 py-2 rounded-xl border text-xs font-semibold transition-all {{ $colorMap[$ql['color']] }}">
                {{ $ql['label'] }}
            </a>
            @endforeach
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════
     ROW 3: P&L Ledger + Cash Flow Ledger + AR/AP
═══════════════════════════════════════════ --}}
<div class="grid grid-cols-1 gap-5 lg:grid-cols-3 mb-5">

    {{-- Laba Rugi Ledger --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Laba Rugi</h3>
                <p class="text-xs text-slate-400 mt-0.5">Bulan ini &bull; {{ ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'][$month-1] }} {{ $year }}</p>
            </div>
            <a href="{{ route('profit-loss.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold transition-colors">Detail →</a>
        </div>
        <div class="p-5 space-y-0">
            <div class="flex items-center justify-between py-2.5 border-b border-slate-100">
                <span class="text-xs text-slate-500 font-medium">Pendapatan (Revenue)</span>
                <span class="text-sm font-bold text-emerald-700 tabular-nums">Rp {{ number_format($pl['revenue'], 0, ',', '.') }}</span>
            </div>
            <div class="flex items-center justify-between py-2.5 border-b border-slate-100">
                <span class="text-xs text-slate-500 font-medium">Biaya Material</span>
                <span class="text-sm font-semibold text-rose-600 tabular-nums">−Rp {{ number_format($pl['material_cost'], 0, ',', '.') }}</span>
            </div>
            <div class="flex items-center justify-between py-2.5 border-b border-dashed border-slate-200">
                <span class="text-xs text-slate-500 font-medium">Biaya Subkontrak</span>
                <span class="text-sm font-semibold text-rose-600 tabular-nums">−Rp {{ number_format($pl['subcontract_cost'], 0, ',', '.') }}</span>
            </div>
            <div class="flex items-center justify-between py-2.5 border-b border-slate-200">
                <span class="text-xs font-bold text-slate-600">Total HPP (COGS)</span>
                <span class="text-sm font-bold text-slate-700 tabular-nums">Rp {{ number_format($pl['total_cogs'], 0, ',', '.') }}</span>
            </div>
            <div class="flex items-center justify-between py-3.5 rounded-xl {{ $pl['gross_profit'] >= 0 ? 'bg-teal-50' : 'bg-rose-50' }} px-3 -mx-0 mt-1">
                <span class="text-sm font-black text-slate-800">Gross Profit</span>
                <span class="text-base font-black {{ $pl['gross_profit'] >= 0 ? 'text-teal-700' : 'text-rose-700' }} tabular-nums">
                    Rp {{ number_format(abs($pl['gross_profit']), 0, ',', '.') }}
                </span>
            </div>
            <div class="flex items-center justify-between pt-2.5 text-xs">
                <span class="text-slate-400">Gross Margin</span>
                <span class="font-black text-sm {{ $pl['gross_margin_pct'] > 20 ? 'text-teal-600' : ($pl['gross_margin_pct'] > 0 ? 'text-amber-600' : 'text-rose-600') }}">
                    {{ number_format($pl['gross_margin_pct'], 2) }}%
                </span>
            </div>
        </div>
    </div>

    {{-- Arus Kas Ledger --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Arus Kas</h3>
                <p class="text-xs text-slate-400 mt-0.5">
                    Bulan ini
                    @if($cashFlow['using_actual'])
                    &bull; <span class="text-emerald-600 font-semibold">Data aktual</span>
                    @else
                    &bull; <span class="text-amber-600 font-semibold">Estimasi</span>
                    @endif
                </p>
            </div>
            <a href="{{ route('cash-flow.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold transition-colors">Detail →</a>
        </div>
        <div class="p-5 space-y-0">
            <div class="flex items-center justify-between py-2.5 border-b border-slate-100">
                <span class="text-xs text-slate-500 font-medium">Cash In (Klien)</span>
                <span class="text-sm font-bold text-emerald-700 tabular-nums">+Rp {{ number_format($cashFlow['cash_in'], 0, ',', '.') }}</span>
            </div>
            <div class="flex items-center justify-between py-2.5 border-b border-slate-100">
                <span class="text-xs text-slate-500 font-medium">Cash Out — Material</span>
                <span class="text-sm font-semibold text-rose-600 tabular-nums">−Rp {{ number_format($cashFlow['cash_out_purchase'], 0, ',', '.') }}</span>
            </div>
            <div class="flex items-center justify-between py-2.5 border-b border-dashed border-slate-200">
                <span class="text-xs text-slate-500 font-medium">Cash Out — Subkontrak</span>
                <span class="text-sm font-semibold text-rose-600 tabular-nums">−Rp {{ number_format($cashFlow['cash_out_subcontract'], 0, ',', '.') }}</span>
            </div>
            @php $cfNet = $cashFlow['net_flow']; @endphp
            <div class="flex items-center justify-between py-3.5 rounded-xl {{ $cfNet >= 0 ? 'bg-indigo-50' : 'bg-amber-50' }} px-3 -mx-0 mt-1">
                <span class="text-sm font-black text-slate-800">Net Cash Flow</span>
                <span class="text-base font-black {{ $cfNet >= 0 ? 'text-indigo-700' : 'text-amber-700' }} tabular-nums">
                    {{ $cfNet < 0 ? '−' : '+' }}Rp {{ number_format(abs($cfNet), 0, ',', '.') }}
                </span>
            </div>
            <div class="flex items-center justify-between pt-2.5 text-xs">
                <span class="text-slate-400">Rasio Cash In/Out</span>
                @php $cfRatio = $cashFlow['cash_out'] > 0 ? round($cashFlow['cash_in'] / $cashFlow['cash_out'] * 100, 1) : 100; @endphp
                <span class="font-black text-sm {{ $cfRatio >= 100 ? 'text-emerald-600' : 'text-amber-600' }}">{{ $cfRatio }}%</span>
            </div>
        </div>
    </div>

    {{-- AR + AP Detail --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-200">
            <h3 class="text-sm font-bold text-slate-800">Piutang &amp; Hutang</h3>
            <p class="text-xs text-slate-400 mt-0.5">Outstanding A/R &amp; A/P saat ini</p>
        </div>
        <div class="p-5 space-y-5">
            {{-- A/R --}}
            <div>
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs font-bold text-slate-600 uppercase tracking-wider">Piutang Usaha (A/R)</p>
                    <a href="{{ route('receivables.index') }}" class="text-xs text-indigo-600 font-semibold hover:text-indigo-800">Lihat →</a>
                </div>
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-slate-500">Outstanding</span>
                        <span class="text-sm font-black text-blue-700 tabular-nums">Rp {{ number_format($receivableSummary['total_outstanding'], 0, ',', '.') }}</span>
                    </div>
                    @if(isset($receivableSummary['overdue_count']) && $receivableSummary['overdue_count'] > 0)
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-rose-500 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-400 animate-pulse flex-shrink-0"></span>
                            Overdue ({{ $receivableSummary['overdue_count'] }} invoice)
                        </span>
                        <span class="text-xs font-bold text-rose-600 tabular-nums">Rp {{ number_format($receivableSummary['overdue_amount'], 0, ',', '.') }}</span>
                    </div>
                    @else
                    <p class="text-xs text-emerald-600 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 flex-shrink-0"></span>
                        Tidak ada yang overdue
                    </p>
                    @endif
                </div>
            </div>

            <div class="border-t border-slate-100 pt-4">
                {{-- A/P --}}
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs font-bold text-slate-600 uppercase tracking-wider">Hutang Usaha (A/P)</p>
                    <a href="{{ route('payables.index') }}" class="text-xs text-indigo-600 font-semibold hover:text-indigo-800">Lihat →</a>
                </div>
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-slate-500">Outstanding</span>
                        <span class="text-sm font-black text-orange-600 tabular-nums">Rp {{ number_format($payableSummary['total_outstanding'], 0, ',', '.') }}</span>
                    </div>
                    @if(isset($payableSummary['overdue_amount']) && $payableSummary['overdue_amount'] > 0)
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-rose-500 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-400 animate-pulse flex-shrink-0"></span>
                            Overdue
                        </span>
                        <span class="text-xs font-bold text-rose-600 tabular-nums">Rp {{ number_format($payableSummary['overdue_amount'], 0, ',', '.') }}</span>
                    </div>
                    @else
                    <p class="text-xs text-emerald-600 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 flex-shrink-0"></span>
                        Tidak ada yang overdue
                    </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════
     ROW 4: GRAFIK 12 Bulan
═══════════════════════════════════════════ --}}
<div class="grid grid-cols-1 gap-5 lg:grid-cols-2 mb-5">

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Cash Flow Bulanan</h3>
                <p class="text-xs text-slate-400 mt-0.5">Jan – Des {{ $year }}</p>
            </div>
            <div class="flex items-center gap-3 text-xs text-slate-500">
                <span class="flex items-center gap-1.5"><span class="inline-block w-3 h-2 rounded-sm bg-emerald-500 opacity-80"></span>Cash In</span>
                <span class="flex items-center gap-1.5"><span class="inline-block w-3 h-2 rounded-sm bg-rose-500 opacity-80"></span>Cash Out</span>
            </div>
        </div>
        <div class="p-5">
            <canvas id="cashFlowChart" style="max-height:220px;"></canvas>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Laba Rugi Bulanan</h3>
                <p class="text-xs text-slate-400 mt-0.5">Jan – Des {{ $year }}</p>
            </div>
            <div class="flex items-center gap-3 text-xs text-slate-500">
                <span class="flex items-center gap-1.5"><span class="inline-block w-4 h-0.5 bg-emerald-500 rounded"></span>Pendapatan</span>
                <span class="flex items-center gap-1.5"><span class="inline-block w-4 h-0.5 bg-rose-500 rounded"></span>HPP</span>
            </div>
        </div>
        <div class="p-5">
            <canvas id="plChart" style="max-height:220px;"></canvas>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════
     ROW 5: Jurnal Terbaru
═══════════════════════════════════════════ --}}
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <div class="w-7 h-7 rounded-lg bg-indigo-100 flex items-center justify-center flex-shrink-0">
                <svg class="h-3.5 w-3.5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-bold text-slate-800">Jurnal Terbaru</h3>
                <p class="text-xs text-slate-400 mt-0.5">10 entri jurnal terakhir</p>
            </div>
        </div>
        <a href="{{ route('journal.index') }}"
           class="inline-flex items-center gap-1 text-xs text-indigo-600 hover:text-indigo-800 font-semibold transition-colors flex-shrink-0">
            Lihat Semua
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">No. Jurnal</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Tanggal</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Keterangan</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-blue-600 uppercase tracking-wider">Debit</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-orange-600 uppercase tracking-wider">Kredit</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($recentJournals as $je)
                <tr class="hover:bg-slate-50/60 transition-colors">
                    <td class="px-5 py-3.5">
                        <a href="{{ route('journal.show', $je) }}"
                           class="font-mono text-xs font-bold text-indigo-600 hover:text-indigo-800 hover:underline transition-colors">
                            {{ $je->entry_no }}
                        </a>
                    </td>
                    <td class="px-4 py-3.5">
                        <span class="text-xs text-slate-500 tabular-nums font-medium whitespace-nowrap">{{ $je->entry_date->format('d M Y') }}</span>
                    </td>
                    <td class="px-4 py-3.5">
                        <span class="text-sm text-slate-700 truncate block max-w-[280px]">{{ $je->description }}</span>
                    </td>
                    <td class="px-4 py-3.5 text-right">
                        <span class="text-sm font-semibold text-blue-700 tabular-nums">Rp {{ number_format($je->total_debit, 0, ',', '.') }}</span>
                    </td>
                    <td class="px-4 py-3.5 text-right">
                        <span class="text-sm font-semibold text-orange-700 tabular-nums">Rp {{ number_format($je->total_credit, 0, ',', '.') }}</span>
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold border
                            {{ $je->status === 'posted' ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : ($je->status === 'void' ? 'bg-rose-50 border-rose-200 text-rose-700' : 'bg-slate-100 border-slate-200 text-slate-600') }}">
                            <span class="w-1.5 h-1.5 rounded-full flex-shrink-0
                                {{ $je->status === 'posted' ? 'bg-emerald-400' : ($je->status === 'void' ? 'bg-rose-400' : 'bg-slate-300') }}">
                            </span>
                            {{ $je->getStatusLabelAttribute() }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-12 text-center">
                        <div class="mx-auto w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center mb-3">
                            <svg class="h-6 w-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z"/>
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-slate-500 mb-2">Belum ada entri jurnal</p>
                        <a href="{{ route('journal.create') }}"
                           class="inline-flex items-center gap-1.5 text-sm text-indigo-600 hover:text-indigo-800 font-semibold transition-colors">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                            Buat Jurnal Pertama
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($recentJournals->count() > 0)
    <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 text-right">
        <a href="{{ route('journal.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold transition-colors">
            Lihat semua jurnal →
        </a>
    </div>
    @endif
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function() {
    const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

    @php
        $cfLabels  = [];
        $cfIn      = [];
        $cfOut     = [];
        $plRevenue = [];
        $plCost    = [];
        for ($m = 1; $m <= 12; $m++) {
            $cfLabels[]  = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'][$m-1];
            $cfIn[]      = (float) ($cashFlowTrend[$m]['cash_in'] ?? 0);
            $cfOut[]     = (float) ($cashFlowTrend[$m]['cash_out'] ?? 0);
            $plRevenue[] = (float) ($plTrend[$m]['revenue'] ?? 0);
            $plCost[]    = (float) ($plTrend[$m]['total_cogs'] ?? 0);
        }
    @endphp

    const fmt = v => 'Rp ' + (v / 1e6).toFixed(1) + 'jt';

    new Chart(document.getElementById('cashFlowChart'), {
        type: 'bar',
        data: {
            labels: @json($cfLabels),
            datasets: [
                {
                    label: 'Cash In',
                    data: @json($cfIn),
                    backgroundColor: 'rgba(16,185,129,0.75)',
                    hoverBackgroundColor: 'rgba(5,150,105,0.9)',
                    borderRadius: 5,
                    borderSkipped: false,
                },
                {
                    label: 'Cash Out',
                    data: @json($cfOut),
                    backgroundColor: 'rgba(239,68,68,0.75)',
                    hoverBackgroundColor: 'rgba(220,38,38,0.9)',
                    borderRadius: 5,
                    borderSkipped: false,
                }
            ]
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1e293b',
                    titleColor: '#94a3b8',
                    bodyColor: '#f8fafc',
                    padding: 10,
                    cornerRadius: 8,
                    callbacks: { label: ctx => '  ' + ctx.dataset.label + ': ' + fmt(ctx.parsed.y) }
                }
            },
            scales: {
                y: {
                    ticks: { callback: fmt, font: { size: 10 }, color: '#94a3b8' },
                    grid: { color: 'rgba(148,163,184,0.1)', borderDash: [4, 4] }
                },
                x: { ticks: { font: { size: 10 }, color: '#94a3b8' }, grid: { display: false } }
            }
        }
    });

    new Chart(document.getElementById('plChart'), {
        type: 'line',
        data: {
            labels: @json($cfLabels),
            datasets: [
                {
                    label: 'Pendapatan',
                    data: @json($plRevenue),
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16,185,129,0.08)',
                    tension: 0.35,
                    fill: true,
                    pointRadius: 3.5,
                    pointBackgroundColor: '#10b981',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                },
                {
                    label: 'Total HPP',
                    data: @json($plCost),
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239,68,68,0.06)',
                    tension: 0.35,
                    fill: true,
                    pointRadius: 3.5,
                    pointBackgroundColor: '#ef4444',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                }
            ]
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1e293b',
                    titleColor: '#94a3b8',
                    bodyColor: '#f8fafc',
                    padding: 10,
                    cornerRadius: 8,
                    callbacks: { label: ctx => '  ' + ctx.dataset.label + ': ' + fmt(ctx.parsed.y) }
                }
            },
            scales: {
                y: {
                    ticks: { callback: fmt, font: { size: 10 }, color: '#94a3b8' },
                    grid: { color: 'rgba(148,163,184,0.1)', borderDash: [4, 4] }
                },
                x: { ticks: { font: { size: 10 }, color: '#94a3b8' }, grid: { display: false } }
            }
        }
    });
})();
</script>
@endpush
