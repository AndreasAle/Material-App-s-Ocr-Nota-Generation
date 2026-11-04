@extends('layouts.app')

@section('content')
@php
    $surplusMonths  = count(array_filter($chartNet, fn($v) => $v > 0));
    $deficitMonths  = count(array_filter($chartNet, fn($v) => $v < 0));
    $maxNet         = !empty($chartNet) ? max($chartNet) : 0;
    $minNet         = !empty($chartNet) ? min($chartNet) : 0;
    $avgNet         = !empty($chartNet) ? array_sum($chartNet) / count($chartNet) : 0;
    $bestMonthIdx   = !empty($chartNet) ? array_search($maxNet, $chartNet) : null;
    $worstMonthIdx  = !empty($chartNet) ? array_search($minNet, $chartNet) : null;
    $bestMonthLabel = ($bestMonthIdx !== null && isset($chartLabels[$bestMonthIdx])) ? $chartLabels[$bestMonthIdx] : '-';
    $worstMonthLabel= ($worstMonthIdx !== null && isset($chartLabels[$worstMonthIdx])) ? $chartLabels[$worstMonthIdx] : '-';
    $cashInOutRatio = $totalCashOut > 0 ? ($totalCashIn / $totalCashOut) * 100 : ($totalCashIn > 0 ? 200 : 100);
    $finalRunningBalance = !empty($tableRows) ? end($tableRows)['running_balance'] : 0;
    $totalMonths    = count($tableRows);

    $healthStatus = match(true) {
        $netFlow > 0 && $cashInOutRatio >= 120 => 'excellent',
        $netFlow > 0 && $cashInOutRatio >= 100  => 'healthy',
        $netFlow == 0                            => 'neutral',
        $netFlow < 0 && $cashInOutRatio >= 80   => 'warning',
        default                                  => 'critical',
    };
    $hc = match($healthStatus) {
        'excellent' => ['label'=>'Sangat Sehat','desc'=>'Arus kas sangat positif — cash in jauh melebihi cash out secara konsisten.','bg'=>'bg-emerald-50','border'=>'border-emerald-300','text'=>'text-emerald-700','icon_bg'=>'bg-emerald-100','badge'=>'bg-emerald-100 text-emerald-800 border-emerald-200','bar'=>'bg-emerald-500'],
        'healthy'   => ['label'=>'Sehat','desc'=>'Arus kas positif — pemasukan melebihi pengeluaran selama periode ini.','bg'=>'bg-teal-50','border'=>'border-teal-300','text'=>'text-teal-700','icon_bg'=>'bg-teal-100','badge'=>'bg-teal-100 text-teal-800 border-teal-200','bar'=>'bg-teal-500'],
        'neutral'   => ['label'=>'Impas','desc'=>'Arus kas seimbang — pemasukan setara dengan pengeluaran.','bg'=>'bg-slate-50','border'=>'border-slate-300','text'=>'text-slate-600','icon_bg'=>'bg-slate-100','badge'=>'bg-slate-100 text-slate-700 border-slate-200','bar'=>'bg-slate-400'],
        'warning'   => ['label'=>'Perhatian','desc'=>'Arus kas negatif — pengeluaran melebihi pemasukan. Perlu evaluasi.','bg'=>'bg-amber-50','border'=>'border-amber-300','text'=>'text-amber-700','icon_bg'=>'bg-amber-100','badge'=>'bg-amber-100 text-amber-800 border-amber-200','bar'=>'bg-amber-500'],
        default     => ['label'=>'Kritis','desc'=>'Arus kas sangat negatif — defisit signifikan selama 12 bulan terakhir.','bg'=>'bg-rose-50','border'=>'border-rose-300','text'=>'text-rose-700','icon_bg'=>'bg-rose-100','badge'=>'bg-rose-100 text-rose-800 border-rose-200','bar'=>'bg-rose-500'],
    };
@endphp

{{-- Breadcrumb --}}
<nav class="flex items-center gap-1.5 text-xs text-slate-400 mb-4">
    <a href="{{ route('dashboard') }}" class="hover:text-slate-600 transition-colors">Dashboard</a>
    <svg class="h-3 w-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-slate-500 font-medium">Laporan</span>
    <svg class="h-3 w-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-slate-700 font-semibold">Arus Kas</span>
</nav>

{{-- Professional Header --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div class="flex items-center gap-3">
        <div class="flex-shrink-0 w-11 h-11 rounded-2xl bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center shadow-md">
            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 16V4m0 0L3 8m4-4l4 4M17 8v12m0 0l4-4m-4 4l-4-4"/>
            </svg>
        </div>
        <div>
            <h1 class="text-xl font-bold text-slate-800 leading-tight">Laporan Arus Kas</h1>
            <p class="text-sm text-slate-500">
                Cash Flow 12 Bulan Terakhir
                @if($selectedProject)
                    &mdash; <span class="font-semibold text-cyan-600">{{ $selectedProject->name }}</span>
                @else
                    &mdash; Semua Proyek
                @endif
            </p>
        </div>
    </div>
    <div class="flex items-center gap-2 flex-wrap">
        <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-slate-200 bg-white text-sm font-medium text-slate-600 hover:bg-slate-50 hover:border-slate-300 transition-all shadow-sm">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.75 19.5m10.56-5.671c.24.03.48.062.72.096m-.72-.096L17.25 19.5M9 10.5h.008v.008H9V10.5zm3 0h.008v.008H12V10.5zm3 0h.008v.008H15V10.5zM6 18.75a.75.75 0 100-1.5.75.75 0 000 1.5z"/>
            </svg>
            Cetak
        </button>
        <a href="{{ route('cash-flow.export-pdf', request()->query()) }}"
           class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-rose-200 bg-rose-50 text-sm font-medium text-rose-600 hover:bg-rose-100 hover:border-rose-300 transition-all shadow-sm">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
            </svg>
            Export PDF
        </a>
    </div>
</div>

{{-- Financial Health Banner --}}
<div class="rounded-2xl border {{ $hc['border'] }} {{ $hc['bg'] }} p-4 mb-6 flex flex-col sm:flex-row sm:items-center gap-4">
    <div class="flex-shrink-0 w-10 h-10 rounded-xl {{ $hc['icon_bg'] }} flex items-center justify-center">
        @if(in_array($healthStatus, ['excellent', 'healthy']))
        <svg class="h-5 w-5 {{ $hc['text'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        @elseif($healthStatus === 'neutral')
        <svg class="h-5 w-5 {{ $hc['text'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
        </svg>
        @else
        <svg class="h-5 w-5 {{ $hc['text'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
        </svg>
        @endif
    </div>
    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2 flex-wrap mb-1">
            <span class="text-sm font-semibold {{ $hc['text'] }}">Kesehatan Arus Kas:</span>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $hc['badge'] }}">{{ $hc['label'] }}</span>
            <span class="text-xs {{ $hc['text'] }} opacity-75">• {{ $surplusMonths }} dari {{ $totalMonths }} bulan surplus</span>
        </div>
        <p class="text-xs {{ $hc['text'] }} opacity-70 mb-2">{{ $hc['desc'] }}</p>
        <div class="h-1.5 rounded-full bg-white bg-opacity-60 overflow-hidden">
            @php $barW = min(100, max(2, $cashInOutRatio)); @endphp
            <div class="{{ $hc['bar'] }} h-full rounded-full transition-all duration-700" style="width: {{ $barW }}%"></div>
        </div>
        <div class="flex justify-between mt-1">
            <span class="text-xs {{ $hc['text'] }} opacity-50">Rasio Cash In / Cash Out</span>
            <span class="text-xs font-bold {{ $hc['text'] }}">{{ number_format($cashInOutRatio, 1) }}%</span>
        </div>
    </div>
    @if($selectedProject)
    <div class="flex-shrink-0">
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border {{ $hc['badge'] }} text-xs font-semibold">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21"/>
            </svg>
            {{ $selectedProject->name }}
        </span>
    </div>
    @endif
</div>

{{-- Filter Panel --}}
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 mb-6">
    <div class="flex items-center gap-2 mb-3">
        <svg class="h-4 w-4 text-slate-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z"/>
        </svg>
        <span class="text-xs font-semibold text-slate-600 uppercase tracking-wider">Filter Laporan</span>
    </div>
    <form method="GET" class="flex flex-wrap items-end gap-3">
        <div class="flex-1 min-w-[220px]">
            <label class="block text-xs font-medium text-slate-500 mb-1.5">Pilih Proyek</label>
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21"/>
                </svg>
                <select name="project_id"
                    class="w-full pl-9 pr-8 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-700 focus:ring-2 focus:ring-cyan-500 focus:border-cyan-400 outline-none transition-all appearance-none"
                    onchange="this.form.submit()">
                    <option value="">— Semua Proyek ({{ $projects->count() }} aktif) —</option>
                    @foreach($projects as $p)
                    <option value="{{ $p->id }}" @selected(request('project_id') == $p->id)>
                        {{ $p->name }}@if($p->code) ({{ $p->code }})@endif
                    </option>
                    @endforeach
                </select>
                <svg class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                </svg>
            </div>
        </div>
        <div class="flex items-center gap-2">
            @if(request('project_id'))
            <a href="{{ route('cash-flow.index') }}"
               class="inline-flex items-center gap-1.5 px-3 py-2.5 rounded-xl border border-slate-200 bg-white text-sm font-medium text-slate-500 hover:bg-slate-50 transition-all">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Reset
            </a>
            @endif
            <div class="text-xs text-slate-400 bg-slate-50 px-3 py-2.5 rounded-xl border border-slate-100">
                <span class="font-semibold text-slate-600">12 bulan</span> &bull; Rolling window
            </div>
        </div>
    </form>
    @if(request('project_id') && $selectedProject)
    <div class="mt-3 flex flex-wrap items-center gap-2">
        <span class="text-xs text-slate-400">Filter aktif:</span>
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-cyan-50 text-cyan-700 border border-cyan-200 text-xs font-medium">
            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21"/>
            </svg>
            {{ $selectedProject->name }}
            @if($selectedProject->code)
            <span class="text-cyan-400">({{ $selectedProject->code }})</span>
            @endif
        </span>
    </div>
    @endif
</div>

{{-- KPI Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">

    {{-- Cash In --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 relative overflow-hidden group hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
        <div class="absolute -top-6 -right-6 w-24 h-24 rounded-full bg-emerald-50 group-hover:scale-125 transition-transform duration-300"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-3">
                <div class="w-9 h-9 rounded-xl bg-emerald-100 flex items-center justify-center">
                    <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941"/>
                    </svg>
                </div>
                <span class="text-xs font-semibold text-emerald-600 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full">Pemasukan</span>
            </div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Total Cash In</p>
            <p class="text-2xl font-black text-emerald-700 tabular-nums leading-none">
                Rp {{ $totalCashIn >= 1000000 ? number_format($totalCashIn/1000000, 1).'jt' : number_format($totalCashIn, 0, ',', '.') }}
            </p>
            <p class="text-xs text-slate-400 mt-1">Rp {{ number_format($totalCashIn, 0, ',', '.') }}</p>
        </div>
        <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between">
            <span class="text-xs text-slate-400">Invoice terbayar {{ $totalMonths }} bln</span>
            <span class="text-xs font-bold text-emerald-600">{{ $surplusMonths }}× surplus</span>
        </div>
    </div>

    {{-- Cash Out --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 relative overflow-hidden group hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
        <div class="absolute -top-6 -right-6 w-24 h-24 rounded-full bg-rose-50 group-hover:scale-125 transition-transform duration-300"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-3">
                <div class="w-9 h-9 rounded-xl bg-rose-100 flex items-center justify-center">
                    <svg class="h-4 w-4 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6L9 12.75l4.286-4.286a11.948 11.948 0 014.306 6.43l.776 2.898m0 0l3.182-5.511m-3.182 5.51l-5.511-3.181"/>
                    </svg>
                </div>
                <span class="text-xs font-semibold text-rose-600 bg-rose-50 border border-rose-200 px-2 py-0.5 rounded-full">Pengeluaran</span>
            </div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Total Cash Out</p>
            <p class="text-2xl font-black text-rose-700 tabular-nums leading-none">
                Rp {{ $totalCashOut >= 1000000 ? number_format($totalCashOut/1000000, 1).'jt' : number_format($totalCashOut, 0, ',', '.') }}
            </p>
            <p class="text-xs text-slate-400 mt-1">Rp {{ number_format($totalCashOut, 0, ',', '.') }}</p>
        </div>
        <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between">
            <span class="text-xs text-slate-400">Pembelian {{ $totalMonths }} bln</span>
            <span class="text-xs font-bold text-rose-600">{{ $deficitMonths }}× defisit</span>
        </div>
    </div>

    {{-- Net Flow --}}
    <div class="bg-white rounded-2xl border {{ $netFlow >= 0 ? 'border-indigo-200' : 'border-amber-200' }} shadow-sm p-5 relative overflow-hidden group hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
        <div class="absolute -top-6 -right-6 w-24 h-24 rounded-full {{ $netFlow >= 0 ? 'bg-indigo-50' : 'bg-amber-50' }} group-hover:scale-125 transition-transform duration-300"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-3">
                <div class="w-9 h-9 rounded-xl {{ $netFlow >= 0 ? 'bg-indigo-100' : 'bg-amber-100' }} flex items-center justify-center">
                    <svg class="h-4 w-4 {{ $netFlow >= 0 ? 'text-indigo-600' : 'text-amber-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="text-xs font-semibold px-2 py-0.5 rounded-full border {{ $netFlow >= 0 ? 'text-indigo-600 bg-indigo-50 border-indigo-200' : 'text-amber-600 bg-amber-50 border-amber-200' }}">
                    {{ $netFlow >= 0 ? 'Surplus' : 'Defisit' }}
                </span>
            </div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Net Cash Flow</p>
            <p class="text-2xl font-black {{ $netFlow >= 0 ? 'text-indigo-700' : 'text-amber-700' }} tabular-nums leading-none">
                {{ $netFlow >= 0 ? '+' : '-' }}Rp {{ abs($netFlow) >= 1000000 ? number_format(abs($netFlow)/1000000, 1).'jt' : number_format(abs($netFlow), 0, ',', '.') }}
            </p>
            <p class="text-xs text-slate-400 mt-1">{{ $netFlow >= 0 ? '+' : '' }}Rp {{ number_format($netFlow, 0, ',', '.') }}</p>
        </div>
        <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between">
            <span class="text-xs text-slate-400">Rata-rata/bulan</span>
            <span class="text-xs font-bold {{ $avgNet >= 0 ? 'text-indigo-600' : 'text-amber-600' }}">
                {{ $avgNet >= 0 ? '+' : '-' }}Rp {{ abs($avgNet) >= 1000000 ? number_format(abs($avgNet)/1000000, 2).'jt' : number_format(abs($avgNet), 0, ',', '.') }}
            </span>
        </div>
    </div>

    {{-- Peak Month --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 relative overflow-hidden group hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
        <div class="absolute -top-6 -right-6 w-24 h-24 rounded-full bg-violet-50 group-hover:scale-125 transition-transform duration-300"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-3">
                <div class="w-9 h-9 rounded-xl bg-violet-100 flex items-center justify-center">
                    <svg class="h-4 w-4 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.562.562 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/>
                    </svg>
                </div>
                <span class="text-xs font-semibold text-violet-600 bg-violet-50 border border-violet-200 px-2 py-0.5 rounded-full">Peak</span>
            </div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Bulan Terbaik</p>
            <p class="text-lg font-black text-violet-700 leading-tight">{{ $bestMonthLabel }}</p>
            <p class="text-sm font-bold text-emerald-600 tabular-nums mt-0.5">
                +Rp {{ $maxNet >= 1000000 ? number_format($maxNet/1000000, 1).'jt' : number_format($maxNet, 0, ',', '.') }}
            </p>
        </div>
        <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between">
            <span class="text-xs text-slate-400">Terburuk: {{ $worstMonthLabel }}</span>
            <span class="text-xs font-bold text-rose-500">
                {{ $minNet >= 1000000 ? number_format($minNet/1000000, 1).'jt' : number_format($minNet, 0, ',', '.') }}
            </span>
        </div>
    </div>
</div>

{{-- Chart Section --}}
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 mb-6">
    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 mb-5">
        <div>
            <h3 class="font-bold text-slate-800 text-base">Grafik Arus Kas Bulanan</h3>
            <p class="text-xs text-slate-400 mt-0.5">Perbandingan Cash In, Cash Out, dan Net Flow per bulan</p>
        </div>
        <div class="flex items-center gap-4 text-xs text-slate-500 flex-shrink-0">
            <span class="flex items-center gap-1.5">
                <span class="inline-block w-3 h-3 rounded-sm bg-emerald-400 opacity-80"></span> Cash In
            </span>
            <span class="flex items-center gap-1.5">
                <span class="inline-block w-3 h-3 rounded-sm bg-rose-400 opacity-80"></span> Cash Out
            </span>
            <span class="flex items-center gap-2">
                <span class="inline-block w-5 h-0.5 bg-indigo-500 rounded"></span> Net Flow
            </span>
        </div>
    </div>
    <div style="height:320px; position:relative;">
        <canvas id="cashFlowChart"></canvas>
    </div>
</div>

{{-- Quick Insights --}}
@php
    $insights = [];
    if ($surplusMonths > 0)
        $insights[] = ['type'=>'positive','text'=> $surplusMonths . ' dari ' . $totalMonths . ' bulan mencatat surplus kas.'];
    if ($deficitMonths > 0)
        $insights[] = ['type'=>'negative','text'=> $deficitMonths . ' bulan mengalami defisit — evaluasi pengeluaran pada periode tersebut.'];
    if ($maxNet > 0)
        $insights[] = ['type'=>'info','text'=> 'Performa terbaik di ' . $bestMonthLabel . ' dengan net flow +Rp ' . number_format($maxNet, 0, ',', '.') . '.'];
    if ($minNet < 0)
        $insights[] = ['type'=>'warning','text'=> 'Defisit terbesar terjadi di ' . $worstMonthLabel . ' sebesar Rp ' . number_format(abs($minNet), 0, ',', '.') . '.'];
    if ($totalCashOut > 0)
        $insights[] = ['type'=>'info','text'=> 'Rasio Cash In/Out ' . number_format($cashInOutRatio, 1) . '% — pemasukan ' . ($cashInOutRatio >= 100 ? 'melebihi' : 'belum mencapai') . ' pengeluaran secara total.'];
@endphp

@if(count($insights) > 0)
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 mb-6">
    <div class="flex items-center gap-2.5 mb-4">
        <div class="w-7 h-7 rounded-lg bg-amber-100 flex items-center justify-center flex-shrink-0">
            <svg class="h-3.5 w-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.493 1.509 1.333 1.509 2.316V18"/>
            </svg>
        </div>
        <div>
            <h3 class="font-bold text-slate-800 text-sm">Analisis & Insight Otomatis</h3>
            <p class="text-xs text-slate-400">Ringkasan kondisi arus kas berdasarkan data 12 bulan</p>
        </div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5">
        @foreach($insights as $ins)
        @php
            $ic = match($ins['type']) {
                'positive' => ['bg'=>'bg-emerald-50','border'=>'border-emerald-200','text'=>'text-emerald-700','dot'=>'bg-emerald-400'],
                'negative' => ['bg'=>'bg-rose-50','border'=>'border-rose-200','text'=>'text-rose-700','dot'=>'bg-rose-400'],
                'warning'  => ['bg'=>'bg-amber-50','border'=>'border-amber-200','text'=>'text-amber-700','dot'=>'bg-amber-400'],
                default    => ['bg'=>'bg-blue-50','border'=>'border-blue-200','text'=>'text-blue-700','dot'=>'bg-blue-400'],
            };
        @endphp
        <div class="flex items-start gap-2.5 p-3.5 rounded-xl {{ $ic['bg'] }} border {{ $ic['border'] }}">
            <span class="flex-shrink-0 w-1.5 h-1.5 rounded-full {{ $ic['dot'] }} mt-1.5"></span>
            <p class="text-xs {{ $ic['text'] }} leading-relaxed">{{ $ins['text'] }}</p>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Monthly Detail Table --}}
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    {{-- Table Header --}}
    <div class="px-5 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h3 class="font-bold text-slate-800 text-base">Rincian Per Bulan</h3>
            <p class="text-xs text-slate-400 mt-0.5">Data arus kas bulanan selama {{ $totalMonths }} bulan terakhir</p>
        </div>
        <div class="flex items-center gap-3 text-xs text-slate-400 flex-shrink-0">
            <span class="flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-400 inline-block"></span> Surplus
            </span>
            <span class="flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-rose-400 inline-block"></span> Defisit
            </span>
            <span class="flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-slate-300 inline-block"></span> Impas
            </span>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200">
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider w-10">#</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Periode</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-emerald-600 uppercase tracking-wider">Cash In</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-rose-600 uppercase tracking-wider">Cash Out</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-400 uppercase tracking-wider min-w-[120px]">Proporsi</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Net Flow</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-indigo-600 uppercase tracking-wider">Saldo Kumulatif</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @php
                    $allBalances = array_column($tableRows, 'running_balance');
                    $maxAbsBal   = max(1, ...array_map('abs', $allBalances ?: [0]));
                @endphp
                @foreach($tableRows as $i => $row)
                @php
                    $isPos   = $row['net'] > 0;
                    $isZero  = $row['net'] == 0;
                    $isBest  = ($i === $bestMonthIdx);
                    $isWorst = ($i === $worstMonthIdx) && $minNet < 0;
                    $rowMax  = max($row['cash_in'], $row['cash_out'], 1);
                    $inPct   = min(100, round(($row['cash_in']  / $rowMax) * 100));
                    $outPct  = min(100, round(($row['cash_out'] / $rowMax) * 100));
                    $balPct  = min(100, round((abs($row['running_balance']) / $maxAbsBal) * 100));
                @endphp
                <tr class="hover:bg-slate-50/60 transition-colors {{ $isBest ? 'bg-emerald-50/50' : ($isWorst ? 'bg-rose-50/50' : '') }}">
                    {{-- # --}}
                    <td class="px-4 py-3.5">
                        <span class="text-xs font-bold text-slate-300 tabular-nums">{{ str_pad($i+1, 2, '0', STR_PAD_LEFT) }}</span>
                    </td>

                    {{-- Periode --}}
                    <td class="px-4 py-3.5">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="w-2 h-2 flex-shrink-0 rounded-full {{ $isPos ? 'bg-emerald-400' : ($isZero ? 'bg-slate-300' : 'bg-rose-400') }}"></span>
                            <span class="font-semibold text-slate-800">{{ $row['period'] }}</span>
                            @if($isBest)
                            <span class="text-xs bg-emerald-100 text-emerald-700 border border-emerald-200 px-1.5 py-0.5 rounded-full font-medium leading-none">Terbaik</span>
                            @elseif($isWorst)
                            <span class="text-xs bg-rose-100 text-rose-700 border border-rose-200 px-1.5 py-0.5 rounded-full font-medium leading-none">Terburuk</span>
                            @endif
                        </div>
                    </td>

                    {{-- Cash In --}}
                    <td class="px-4 py-3.5 text-right">
                        <span class="font-semibold text-emerald-700 tabular-nums">Rp {{ number_format($row['cash_in'], 0, ',', '.') }}</span>
                    </td>

                    {{-- Cash Out --}}
                    <td class="px-4 py-3.5 text-right">
                        <span class="font-semibold text-rose-700 tabular-nums">Rp {{ number_format($row['cash_out'], 0, ',', '.') }}</span>
                    </td>

                    {{-- Proporsi mini bars --}}
                    <td class="px-4 py-3.5">
                        <div class="w-full max-w-[110px] mx-auto space-y-1.5">
                            <div class="flex items-center gap-1.5">
                                <span class="text-xs text-emerald-500 font-medium w-5 text-right flex-shrink-0">In</span>
                                <div class="flex-1 bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-emerald-400 h-full rounded-full transition-all" style="width:{{ $inPct }}%"></div>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="text-xs text-rose-400 font-medium w-5 text-right flex-shrink-0">Out</span>
                                <div class="flex-1 bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-rose-400 h-full rounded-full transition-all" style="width:{{ $outPct }}%"></div>
                                </div>
                            </div>
                        </div>
                    </td>

                    {{-- Net Flow --}}
                    <td class="px-4 py-3.5 text-right">
                        <div class="inline-flex items-center gap-1 font-bold tabular-nums {{ $isPos ? 'text-emerald-600' : ($isZero ? 'text-slate-400' : 'text-rose-600') }}">
                            @if($isPos)
                            <svg class="h-3 w-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5"/></svg>
                            @elseif(!$isZero)
                            <svg class="h-3 w-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                            @endif
                            {{ $row['net'] >= 0 ? '+' : '' }}Rp {{ number_format($row['net'], 0, ',', '.') }}
                        </div>
                    </td>

                    {{-- Running Balance --}}
                    <td class="px-4 py-3.5 text-right">
                        <div class="flex flex-col items-end gap-1">
                            <span class="font-bold tabular-nums {{ $row['running_balance'] >= 0 ? 'text-indigo-700' : 'text-amber-700' }}">
                                Rp {{ number_format($row['running_balance'], 0, ',', '.') }}
                            </span>
                            <div class="w-16 bg-slate-100 rounded-full h-1 overflow-hidden">
                                <div class="{{ $row['running_balance'] >= 0 ? 'bg-indigo-400' : 'bg-amber-400' }} h-full rounded-full transition-all"
                                     style="width:{{ max(4, $balPct) }}%"></div>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-gradient-to-r from-slate-100 to-slate-50 border-t-2 border-slate-300">
                    <td colspan="2" class="px-4 py-4">
                        <span class="font-black text-slate-700 text-sm uppercase tracking-wide">Total {{ $totalMonths }} Bulan</span>
                    </td>
                    <td class="px-4 py-4 text-right">
                        <span class="font-black text-emerald-700 tabular-nums text-sm">Rp {{ number_format($totalCashIn, 0, ',', '.') }}</span>
                    </td>
                    <td class="px-4 py-4 text-right">
                        <span class="font-black text-rose-700 tabular-nums text-sm">Rp {{ number_format($totalCashOut, 0, ',', '.') }}</span>
                    </td>
                    <td class="px-4 py-4 text-center">
                        <span class="text-xs text-slate-300">—</span>
                    </td>
                    <td class="px-4 py-4 text-right">
                        <span class="font-black tabular-nums text-sm {{ $netFlow >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                            {{ $netFlow >= 0 ? '+' : '' }}Rp {{ number_format($netFlow, 0, ',', '.') }}
                        </span>
                    </td>
                    <td class="px-4 py-4 text-right">
                        <p class="text-xs text-slate-400 mb-0.5">Saldo Akhir Periode</p>
                        <span class="font-black tabular-nums {{ $finalRunningBalance >= 0 ? 'text-indigo-700' : 'text-amber-700' }}">
                            Rp {{ number_format($finalRunningBalance, 0, ',', '.') }}
                        </span>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- Table Footer --}}
    <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2">
        <div class="flex items-center gap-4 text-xs text-slate-400">
            <span>Cash In = Invoice terbayar &bull; Cash Out = Transaksi pembelian</span>
        </div>
        <span class="text-xs text-slate-400">{{ now()->translatedFormat('d F Y, H:i') }} WIB</span>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    const ctx = document.getElementById('cashFlowChart').getContext('2d');

    const gradientNet = ctx.createLinearGradient(0, 0, 0, 300);
    gradientNet.addColorStop(0, 'rgba(99, 102, 241, 0.22)');
    gradientNet.addColorStop(0.6, 'rgba(99, 102, 241, 0.06)');
    gradientNet.addColorStop(1, 'rgba(99, 102, 241, 0.00)');

    const netData = @json($chartNet);

    new Chart(ctx, {
        data: {
            labels: @json($chartLabels),
            datasets: [
                {
                    type: 'bar',
                    label: 'Cash In',
                    data: @json($chartCashIn),
                    backgroundColor: 'rgba(52, 211, 153, 0.72)',
                    hoverBackgroundColor: 'rgba(16, 185, 129, 0.90)',
                    borderRadius: 6,
                    borderSkipped: false,
                    order: 2,
                },
                {
                    type: 'bar',
                    label: 'Cash Out',
                    data: @json($chartCashOut),
                    backgroundColor: 'rgba(251, 113, 133, 0.72)',
                    hoverBackgroundColor: 'rgba(244, 63, 94, 0.90)',
                    borderRadius: 6,
                    borderSkipped: false,
                    order: 2,
                },
                {
                    type: 'line',
                    label: 'Net Flow',
                    data: netData,
                    borderColor: '#6366f1',
                    backgroundColor: gradientNet,
                    borderWidth: 2.5,
                    pointBackgroundColor: netData.map(v => v >= 0 ? '#6366f1' : '#f59e0b'),
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    tension: 0.4,
                    fill: true,
                    order: 1,
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
                    backgroundColor: '#1e293b',
                    titleColor: '#94a3b8',
                    bodyColor: '#f8fafc',
                    padding: 12,
                    cornerRadius: 10,
                    borderColor: 'rgba(148,163,184,0.15)',
                    borderWidth: 1,
                    callbacks: {
                        label: function (item) {
                            const val = item.parsed.y;
                            const prefix = (item.datasetIndex === 2 && val >= 0) ? '+' : '';
                            return '  ' + item.dataset.label + ': ' + prefix + 'Rp ' + val.toLocaleString('id-ID');
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: '#94a3b8', font: { size: 11 } }
                },
                y: {
                    grid: { color: 'rgba(148,163,184,0.10)', borderDash: [4, 4] },
                    ticks: {
                        color: '#94a3b8',
                        font: { size: 11 },
                        callback: function (v) {
                            const abs = Math.abs(v);
                            const sign = v < 0 ? '-' : '';
                            if (abs >= 1000000) return sign + 'Rp ' + (abs / 1000000).toFixed(1) + 'jt';
                            if (abs >= 1000)    return sign + 'Rp ' + (abs / 1000).toFixed(0) + 'rb';
                            return sign + 'Rp ' + abs.toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    });
})();
</script>
@endpush
@endsection
