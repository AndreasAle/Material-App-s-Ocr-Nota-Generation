@extends('layouts.app')

@section('title', 'RAB vs Realisasi')

@section('content')

@php
    /* ------------------------------------------------------------------ */
    /* Aggregate stats across all rendered plans                            */
    /* ------------------------------------------------------------------ */
    $grandBudget = $plansData->sum('total_budget');
    $grandReal   = $plansData->sum('total_realisasi');
    $grandSisa   = $plansData->sum('total_sisa');
    $grandPct    = $grandBudget > 0 ? round(($grandReal / $grandBudget) * 100, 1) : 0;
    $planCount   = $plansData->count();

    $criticalPlans  = $plansData->filter(fn($p) => $p['total_pct'] >= 90)->count();
    $warningPlans   = $plansData->filter(fn($p) => $p['total_pct'] >= 70 && $p['total_pct'] < 90)->count();
    $healthyPlans   = $plansData->filter(fn($p) => $p['total_pct'] < 70)->count();

    $grandHealthConfig = match(true) {
        $grandPct >= 90 => ['barColor' => 'bg-rose-500',   'textColor' => 'text-rose-700',   'bgColor' => 'bg-rose-50',   'border' => 'border-rose-200'],
        $grandPct >= 70 => ['barColor' => 'bg-amber-400',  'textColor' => 'text-amber-700',  'bgColor' => 'bg-amber-50',  'border' => 'border-amber-200'],
        $grandPct > 0   => ['barColor' => 'bg-emerald-500','textColor' => 'text-emerald-700','bgColor' => 'bg-emerald-50','border' => 'border-emerald-200'],
        default         => ['barColor' => 'bg-slate-300',  'textColor' => 'text-slate-500',  'bgColor' => 'bg-slate-50',  'border' => 'border-slate-200'],
    };
@endphp

{{-- ====================================================================== --}}
{{-- PAGE HEADER                                                              --}}
{{-- ====================================================================== --}}
<div class="mb-6">
    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-1.5 text-xs text-slate-400 mb-3">
        <a href="{{ route('financial-reports.index') }}" class="hover:text-indigo-600 transition-colors font-medium">
            Dashboard Keuangan
        </a>
        <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
        </svg>
        <span class="text-slate-600 font-semibold">RAB vs Realisasi</span>
    </nav>

    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div class="min-w-0">
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 leading-tight">
                RAB vs Realisasi
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Perbandingan anggaran material dengan pengeluaran aktual per proyek
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2 shrink-0">
            <a href="{{ route('financial-reports.index') }}"
               class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm font-medium text-slate-600 shadow-sm hover:bg-slate-50 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/>
                </svg>
                Dashboard
            </a>
            <a href="{{ route('financial-reports.pengeluaran') }}"
               class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm font-medium text-slate-600 shadow-sm hover:bg-slate-50 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2z"/>
                </svg>
                Lap. Pengeluaran
            </a>
            <a href="{{ route('rab-plans.create') }}"
               class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 active:scale-95 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Buat RAB Baru
            </a>
        </div>
    </div>
</div>

{{-- ====================================================================== --}}
{{-- FILTER PANEL                                                             --}}
{{-- ====================================================================== --}}
<div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden mb-6">
    <div class="border-b border-slate-100 px-5 py-3.5">
        <div class="flex items-center gap-2.5">
            <div class="w-7 h-7 rounded-lg bg-indigo-50 flex items-center justify-center">
                <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z"/>
                </svg>
            </div>
            <h3 class="text-sm font-bold text-slate-800">Filter RAB</h3>
        </div>
    </div>
    <div class="p-5">
        <form method="GET" action="{{ route('financial-reports.rab-realisasi') }}" id="filterForm">

            {{-- Status Quick Tabs --}}
            <div class="mb-4">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-2">Status RAB</label>
                <div class="inline-flex items-center gap-1 bg-slate-100 rounded-xl p-1">
                    @foreach ([
                        ['active', 'Aktif',  'bg-emerald-500'],
                        ['draft',  'Draft',   'bg-amber-400'],
                        ['closed', 'Ditutup', 'bg-slate-400'],
                        ['all',    'Semua',   'bg-indigo-500'],
                    ] as [$val, $label, $dotColor])
                    <button type="submit" name="status" value="{{ $val }}"
                            onclick="document.getElementById('filterForm').submit()"
                            class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-lg text-xs font-semibold transition-all
                                   {{ $status === $val
                                       ? 'bg-white text-slate-800 shadow-sm ring-1 ring-slate-200/60'
                                       : 'text-slate-500 hover:text-slate-700 hover:bg-white/60' }}">
                        <span class="w-2 h-2 rounded-full {{ $dotColor }}"></span>
                        {{ $label }}
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- Project Filter + Hidden Status --}}
            <input type="hidden" name="status" value="{{ $status }}">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1.5">Filter Proyek</label>
                    <select name="project_id"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-300 focus:bg-white transition-all">
                        <option value="">Semua Proyek</option>
                        @foreach ($projects as $p)
                            <option value="{{ $p->id }}" @selected($projectId == $p->id)>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 active:scale-95 transition-all">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                        </svg>
                        Terapkan
                    </button>
                    <a href="{{ route('financial-reports.rab-realisasi') }}"
                       class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-all">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ====================================================================== --}}
{{-- EMPTY STATE                                                              --}}
{{-- ====================================================================== --}}
@if ($plansData->isEmpty())
<div class="rounded-2xl border-2 border-dashed border-slate-200 bg-white p-16 flex flex-col items-center text-center">
    <div class="w-20 h-20 rounded-3xl bg-slate-50 border-2 border-dashed border-slate-200 flex items-center justify-center mb-5">
        <svg class="w-9 h-9 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2Zm0 0V9a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v10m-6 0a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2m0 0V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-2a2 2 0 0 1-2-2Z"/>
        </svg>
    </div>
    <h3 class="text-base font-bold text-slate-600 mb-1.5">Tidak ada RAB ditemukan</h3>
    <p class="text-sm text-slate-400 max-w-sm leading-relaxed mb-6">
        Belum ada RAB yang sesuai dengan filter ini. Coba ubah status atau proyek, atau buat RAB baru.
    </p>
    <div class="flex items-center gap-3">
        <a href="{{ route('financial-reports.rab-realisasi') }}"
           class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-all">
            Reset Filter
        </a>
        <a href="{{ route('rab-plans.create') }}"
           class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 transition-all shadow-sm shadow-indigo-200">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Buat RAB Baru
        </a>
    </div>
</div>

@else

{{-- ====================================================================== --}}
{{-- AGGREGATE OVERVIEW (jika lebih dari 1 plan)                             --}}
{{-- ====================================================================== --}}
@if ($planCount > 1)
<div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden mb-6">
    <div class="border-b border-slate-100 px-5 py-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Ringkasan Keseluruhan</h3>
                <p class="text-xs text-slate-400 mt-0.5">Agregat dari {{ $planCount }} RAB yang ditampilkan</p>
            </div>
            <div class="flex items-center gap-2">
                @if ($criticalPlans > 0)
                <span class="inline-flex items-center gap-1 rounded-full bg-rose-50 border border-rose-200 px-2.5 py-1 text-xs font-bold text-rose-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                    {{ $criticalPlans }} Kritis
                </span>
                @endif
                @if ($warningPlans > 0)
                <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 border border-amber-200 px-2.5 py-1 text-xs font-bold text-amber-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                    {{ $warningPlans }} Perhatian
                </span>
                @endif
                @if ($healthyPlans > 0)
                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 border border-emerald-200 px-2.5 py-1 text-xs font-bold text-emerald-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    {{ $healthyPlans }} Sehat
                </span>
                @endif
            </div>
        </div>
    </div>
    <div class="p-5">
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-5">
            <div class="text-center p-3 rounded-xl bg-slate-50 border border-slate-100">
                <p class="text-2xl font-extrabold text-slate-800">{{ $planCount }}</p>
                <p class="text-xs text-slate-500 mt-0.5">RAB Aktif</p>
            </div>
            <div class="text-center p-3 rounded-xl bg-slate-50 border border-slate-100">
                <p class="text-lg font-extrabold text-slate-800 truncate">Rp {{ number_format($grandBudget / 1_000_000, 1, ',', '.') }}jt</p>
                <p class="text-xs text-slate-500 mt-0.5">Total Anggaran</p>
            </div>
            <div class="text-center p-3 rounded-xl bg-indigo-50 border border-indigo-100">
                <p class="text-lg font-extrabold text-indigo-700 truncate">Rp {{ number_format($grandReal / 1_000_000, 1, ',', '.') }}jt</p>
                <p class="text-xs text-indigo-500 mt-0.5">Total Realisasi</p>
            </div>
            <div class="text-center p-3 rounded-xl {{ $grandSisa > 0 ? 'bg-emerald-50 border-emerald-100' : 'bg-rose-50 border-rose-100' }} border">
                <p class="text-lg font-extrabold {{ $grandSisa > 0 ? 'text-emerald-700' : 'text-rose-700' }} truncate">
                    Rp {{ number_format($grandSisa / 1_000_000, 1, ',', '.') }}jt
                </p>
                <p class="text-xs {{ $grandSisa > 0 ? 'text-emerald-500' : 'text-rose-500' }} mt-0.5">Sisa Anggaran</p>
            </div>
        </div>
        <div>
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-semibold text-slate-600">Utilisasi Keseluruhan</p>
                <p class="text-sm font-extrabold {{ $grandHealthConfig['textColor'] }}">{{ $grandPct }}%</p>
            </div>
            <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden">
                <div class="{{ $grandHealthConfig['barColor'] }} h-3 rounded-full transition-all duration-700 relative"
                     style="width: {{ min(100, $grandPct) }}%">
                    @if ($grandPct > 10)
                    <div class="absolute inset-0 bg-white/20 rounded-full"
                         style="background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,0.2) 50%, transparent 100%)"></div>
                    @endif
                </div>
            </div>
            <div class="flex items-center justify-between mt-1.5">
                <span class="text-xs text-slate-400">0%</span>
                <span class="text-xs text-slate-400">Batas Anggaran 100%</span>
            </div>
        </div>
    </div>
</div>
@endif

{{-- ====================================================================== --}}
{{-- PLAN CARDS                                                               --}}
{{-- ====================================================================== --}}
@foreach ($plansData as $planIdx => $pd)
@php
    $plan = $pd['plan'];
    $pct  = (float)$pd['total_pct'];

    /* Health config per plan */
    $hBar    = $pct >= 90 ? 'bg-rose-500'    : ($pct >= 70 ? 'bg-amber-400'  : ($pct > 0 ? 'bg-emerald-500' : 'bg-slate-300'));
    $hText   = $pct >= 90 ? 'text-rose-700'   : ($pct >= 70 ? 'text-amber-700' : ($pct > 0 ? 'text-emerald-700' : 'text-slate-500'));
    $hBg     = $pct >= 90 ? 'bg-rose-50'      : ($pct >= 70 ? 'bg-amber-50'    : ($pct > 0 ? 'bg-emerald-50'    : 'bg-slate-50'));
    $hBorder = $pct >= 90 ? 'border-rose-100'  : ($pct >= 70 ? 'border-amber-100' : ($pct > 0 ? 'border-emerald-100' : 'border-slate-200'));

    /* Plan status badge */
    $statusCfg = match($plan->status) {
        'active' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'dot' => 'bg-emerald-500', 'label' => 'Aktif'],
        'closed' => ['bg' => 'bg-slate-100',  'text' => 'text-slate-600',   'border' => 'border-slate-300',   'dot' => 'bg-slate-400',   'label' => 'Ditutup'],
        default  => ['bg' => 'bg-amber-50',   'text' => 'text-amber-700',   'border' => 'border-amber-200',   'dot' => 'bg-amber-400',   'label' => 'Draft'],
    };

    /* Health label */
    $healthLabel = match(true) {
        $pct >= 100 => 'Melebihi Anggaran',
        $pct >= 90  => 'Kritis',
        $pct >= 70  => 'Perlu Perhatian',
        $pct > 0    => 'Sehat',
        default     => 'Belum Dimulai',
    };

    /* Items that are critical/warning */
    $criticalItems = $pd['items']->filter(fn($r) => $r['budget_pct'] >= 90)->count();
    $cardId = 'plan-' . $planIdx;
@endphp

<div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden mb-5" id="{{ $cardId }}">

    {{-- ================================================================ --}}
    {{-- PLAN HEADER                                                        --}}
    {{-- ================================================================ --}}
    <div class="border-b border-slate-100 px-5 py-4">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="flex items-start gap-3 min-w-0">
                {{-- Status circle --}}
                <div class="w-10 h-10 rounded-xl {{ $hBg }} border {{ $hBorder }} flex items-center justify-center shrink-0 mt-0.5">
                    @if ($pct >= 90)
                    <svg class="w-5 h-5 {{ $hText }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/>
                    </svg>
                    @elseif ($pct >= 70)
                    <svg class="w-5 h-5 {{ $hText }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                    </svg>
                    @elseif ($pct > 0)
                    <svg class="w-5 h-5 {{ $hText }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                    </svg>
                    @else
                    <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 7.5A2.25 2.25 0 0 1 7.5 5.25h9a2.25 2.25 0 0 1 2.25 2.25v9a2.25 2.25 0 0 1-2.25 2.25h-9a2.25 2.25 0 0 1-2.25-2.25v-9Z"/>
                    </svg>
                    @endif
                </div>

                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <h3 class="font-extrabold text-slate-900 text-base leading-tight">{{ $plan->name }}</h3>
                        {{-- Status Badge --}}
                        <span class="inline-flex items-center gap-1 rounded-full {{ $statusCfg['bg'] }} border {{ $statusCfg['border'] }} px-2.5 py-0.5 text-xs font-bold {{ $statusCfg['text'] }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $statusCfg['dot'] }}"></span>
                            {{ $statusCfg['label'] }}
                        </span>
                        {{-- Health Badge --}}
                        <span class="inline-flex items-center gap-1 rounded-full {{ $hBg }} border {{ $hBorder }} px-2.5 py-0.5 text-xs font-bold {{ $hText }}">
                            {{ $healthLabel }}
                        </span>
                        @if ($criticalItems > 0)
                        <span class="inline-flex items-center gap-1 rounded-full bg-rose-50 border border-rose-200 px-2.5 py-0.5 text-xs font-bold text-rose-700">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/>
                            </svg>
                            {{ $criticalItems }} item kritis
                        </span>
                        @endif
                    </div>
                    <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500">
                        @if ($plan->project)
                        <span class="inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12 11.204 3.045c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>
                            </svg>
                            <span class="font-semibold text-slate-700">{{ $plan->project->name }}</span>
                        </span>
                        @endif
                        <span class="inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
                            </svg>
                            {{ $plan->period_start->format('d M Y') }}
                            @if ($plan->period_end)
                                — {{ $plan->period_end->format('d M Y') }}
                            @else
                                — <em>Tidak ada batas</em>
                            @endif
                        </span>
                    </div>
                </div>
            </div>
            <a href="{{ route('rab-plans.show', $plan) }}"
               class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2 text-xs font-semibold text-slate-600 hover:bg-indigo-50 hover:border-indigo-200 hover:text-indigo-700 transition-all shrink-0">
                Lihat Detail RAB
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
                </svg>
            </a>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- KPI MINI-CARDS + PROGRESS                                          --}}
    {{-- ================================================================ --}}
    <div class="p-5 border-b border-slate-100">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
            {{-- Total Anggaran --}}
            <div class="rounded-xl border border-slate-100 bg-slate-50 p-3.5">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-6 h-6 rounded-lg bg-slate-200 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5"/>
                        </svg>
                    </div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Anggaran</p>
                </div>
                <p class="text-base font-extrabold text-slate-800 tabular-nums leading-tight">
                    Rp {{ number_format($pd['total_budget'], 0, ',', '.') }}
                </p>
            </div>

            {{-- Total Realisasi --}}
            <div class="rounded-xl border border-indigo-100 bg-indigo-50 p-3.5">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-6 h-6 rounded-lg bg-indigo-200 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5 text-indigo-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75"/>
                        </svg>
                    </div>
                    <p class="text-xs font-semibold text-indigo-500 uppercase tracking-wide">Realisasi</p>
                </div>
                <p class="text-base font-extrabold text-indigo-700 tabular-nums leading-tight">
                    Rp {{ number_format($pd['total_realisasi'], 0, ',', '.') }}
                </p>
            </div>

            {{-- Sisa Anggaran --}}
            <div class="rounded-xl border {{ $pd['total_sisa'] > 0 ? 'border-emerald-100 bg-emerald-50' : 'border-rose-100 bg-rose-50' }} p-3.5">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-6 h-6 rounded-lg {{ $pd['total_sisa'] > 0 ? 'bg-emerald-200' : 'bg-rose-200' }} flex items-center justify-center">
                        <svg class="w-3.5 h-3.5 {{ $pd['total_sisa'] > 0 ? 'text-emerald-700' : 'text-rose-700' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            @if ($pd['total_sisa'] > 0)
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                            @else
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                            @endif
                        </svg>
                    </div>
                    <p class="text-xs font-semibold {{ $pd['total_sisa'] > 0 ? 'text-emerald-600' : 'text-rose-600' }} uppercase tracking-wide">Sisa</p>
                </div>
                <p class="text-base font-extrabold {{ $pd['total_sisa'] > 0 ? 'text-emerald-700' : 'text-rose-700' }} tabular-nums leading-tight">
                    Rp {{ number_format($pd['total_sisa'], 0, ',', '.') }}
                </p>
            </div>

            {{-- Utilization % --}}
            <div class="rounded-xl border {{ $hBorder }} {{ $hBg }} p-3.5">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-6 h-6 rounded-lg {{ $pct >= 90 ? 'bg-rose-200' : ($pct >= 70 ? 'bg-amber-200' : 'bg-emerald-200') }} flex items-center justify-center">
                        <svg class="w-3.5 h-3.5 {{ $hText }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z"/>
                        </svg>
                    </div>
                    <p class="text-xs font-semibold {{ $hText }} uppercase tracking-wide">Terpakai</p>
                </div>
                <p class="text-2xl font-extrabold {{ $hText }} tabular-nums leading-none">{{ $pct }}%</p>
            </div>
        </div>

        {{-- Overall Progress Bar --}}
        <div>
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-bold text-slate-600">Progress Utilisasi Anggaran</p>
                <div class="flex items-center gap-2 text-xs">
                    <span class="text-slate-400">Rp {{ number_format($pd['total_realisasi'], 0, ',', '.') }}</span>
                    <span class="text-slate-300">/</span>
                    <span class="font-bold text-slate-600">Rp {{ number_format($pd['total_budget'], 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden relative">
                <div class="{{ $hBar }} h-3 rounded-full transition-all duration-700 relative"
                     style="width: {{ min(100, $pct) }}%">
                    @if ($pct > 10)
                    <div class="absolute inset-0 rounded-full" style="background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,0.25) 50%, transparent 100%)"></div>
                    @endif
                </div>
                {{-- 70% marker --}}
                <div class="absolute top-0 bottom-0 border-r-2 border-dashed border-amber-400/50"
                     style="left: 70%" title="Batas perhatian 70%"></div>
                {{-- 90% marker --}}
                <div class="absolute top-0 bottom-0 border-r-2 border-dashed border-rose-400/50"
                     style="left: 90%" title="Batas kritis 90%"></div>
            </div>
            <div class="flex items-center justify-between mt-1.5">
                <span class="text-xs text-slate-400">0%</span>
                <span class="text-xs text-amber-500 font-medium">70% ⚠</span>
                <span class="text-xs text-rose-500 font-medium">90% 🔴</span>
                <span class="text-xs text-slate-400">100%</span>
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- ITEMS TABLE                                                        --}}
    {{-- ================================================================ --}}
    @if ($pd['items']->isEmpty())
    <div class="px-5 py-10 text-center">
        <p class="text-sm font-medium text-slate-400">RAB ini belum memiliki item material</p>
        <a href="{{ route('rab-plans.show', $plan) }}" class="mt-2 inline-flex text-xs text-indigo-600 hover:underline font-semibold">
            Tambah item →
        </a>
    </div>

    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            {{-- Column Group Headers --}}
            <thead>
                <tr class="bg-slate-50/80">
                    <th class="text-left px-4 py-2 border-b border-slate-100" colspan="1" rowspan="2">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wide">#</span>
                    </th>
                    <th class="text-left px-4 py-2 border-b border-slate-100" colspan="1" rowspan="2">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wide">Material</span>
                    </th>
                    <th class="text-center px-3 py-2 border-r border-slate-200" colspan="3">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wide">Rencana (RAB)</span>
                    </th>
                    <th class="text-center px-3 py-2 border-r border-slate-200" colspan="2">
                        <span class="text-xs font-bold text-indigo-500 uppercase tracking-wide">Aktual</span>
                    </th>
                    <th class="text-center px-3 py-2" colspan="2">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wide">Selisih</span>
                    </th>
                </tr>
                <tr class="bg-slate-50/50 border-b border-slate-200">
                    <th class="text-right text-xs font-bold text-slate-400 px-4 py-2">Qty</th>
                    <th class="text-right text-xs font-bold text-slate-400 px-4 py-2">Harga Satuan</th>
                    <th class="text-right text-xs font-bold text-slate-400 px-4 py-2 border-r border-slate-200">Budget</th>
                    <th class="text-right text-xs font-bold text-indigo-400 px-4 py-2">Qty Aktual</th>
                    <th class="text-right text-xs font-bold text-indigo-400 px-4 py-2 border-r border-slate-200">Total Aktual</th>
                    <th class="text-right text-xs font-bold text-slate-400 px-4 py-2">Sisa Budget</th>
                    <th class="text-left text-xs font-bold text-slate-400 px-4 py-2 min-w-[140px]">% Budget</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pd['items'] as $row)
                @php
                    $bp      = (float)$row['budget_pct'];
                    $isOver  = $bp > 100;
                    $isCrit  = $bp >= 90;
                    $isWarn  = $bp >= 70 && $bp < 90;
                    $notStarted = $row['qty_actual'] == 0;

                    $rowBg    = $isOver  ? 'bg-rose-50/60'   : ($isCrit ? 'bg-rose-50/30' : ($isWarn ? 'bg-amber-50/30' : 'bg-white'));
                    $barColor = $isOver  ? 'bg-rose-600'      : ($isCrit ? 'bg-rose-500'   : ($isWarn ? 'bg-amber-400'   : 'bg-emerald-500'));
                    $pctText  = $isOver  ? 'text-rose-700'    : ($isCrit ? 'text-rose-600'  : ($isWarn ? 'text-amber-700'  : ($notStarted ? 'text-slate-400' : 'text-emerald-700')));
                    $sisaText = $row['sisa_budget'] >= 0 ? ($isCrit || $isOver ? 'text-rose-700 font-bold' : 'text-slate-700') : 'text-rose-700 font-bold';
                @endphp
                <tr class="{{ $rowBg }} hover:bg-indigo-50/20 transition-colors border-b border-slate-50 group">

                    {{-- # --}}
                    <td class="px-4 py-3 text-xs font-medium text-slate-400 w-8">
                        {{ $loop->iteration }}
                    </td>

                    {{-- Material --}}
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2.5">
                            @if ($isOver)
                            <div class="w-2 h-2 rounded-full bg-rose-500 shrink-0" title="Melebihi anggaran"></div>
                            @elseif ($isCrit)
                            <div class="w-2 h-2 rounded-full bg-rose-400 shrink-0" title="Kritis"></div>
                            @elseif ($isWarn)
                            <div class="w-2 h-2 rounded-full bg-amber-400 shrink-0" title="Perlu perhatian"></div>
                            @elseif ($notStarted)
                            <div class="w-2 h-2 rounded-full bg-slate-300 shrink-0" title="Belum dimulai"></div>
                            @else
                            <div class="w-2 h-2 rounded-full bg-emerald-400 shrink-0" title="Sehat"></div>
                            @endif
                            <div>
                                <p class="font-semibold text-slate-800 {{ $notStarted ? 'opacity-60' : '' }}">
                                    {{ $row['item']->material_name_snapshot }}
                                </p>
                                <p class="text-xs text-slate-400">{{ $row['item']->unit_name_snapshot }}</p>
                            </div>
                        </div>
                        @if ($isOver)
                        <span class="mt-1 inline-flex items-center rounded-full bg-rose-100 border border-rose-200 px-2 py-0.5 text-xs font-bold text-rose-700">
                            ⚠ Melebihi Anggaran
                        </span>
                        @endif
                    </td>

                    {{-- RAB columns --}}
                    <td class="px-4 py-3 text-right text-slate-600 tabular-nums {{ $notStarted ? 'opacity-60' : '' }}">
                        {{ number_format($row['item']->qty_planned, 2, ',', '.') }}
                    </td>
                    <td class="px-4 py-3 text-right text-slate-600 tabular-nums {{ $notStarted ? 'opacity-60' : '' }}">
                        Rp {{ number_format($row['item']->price_planned, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3 text-right font-bold text-slate-700 border-r border-slate-100 tabular-nums">
                        Rp {{ number_format($row['item']->subtotal_planned, 0, ',', '.') }}
                    </td>

                    {{-- Actual columns --}}
                    <td class="px-4 py-3 text-right tabular-nums {{ $notStarted ? 'text-slate-300' : 'text-indigo-600 font-medium' }}">
                        @if ($notStarted)
                            <span class="text-slate-300">—</span>
                        @else
                            {{ number_format($row['qty_actual'], 2, ',', '.') }}
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right border-r border-slate-100 tabular-nums {{ $notStarted ? 'text-slate-300' : 'text-indigo-700 font-bold' }}">
                        @if ($notStarted)
                            <span class="text-slate-300">—</span>
                        @else
                            Rp {{ number_format($row['total_actual'], 0, ',', '.') }}
                        @endif
                    </td>

                    {{-- Variance columns --}}
                    <td class="px-4 py-3 text-right {{ $sisaText }} tabular-nums">
                        Rp {{ number_format($row['sisa_budget'], 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3">
                        @if ($notStarted)
                            <div class="flex items-center gap-2">
                                <div class="flex-1 bg-slate-100 rounded-full h-2">
                                    <div class="bg-slate-300 h-2 rounded-full" style="width: 0%"></div>
                                </div>
                                <span class="text-xs font-semibold text-slate-300 w-10 text-right">0%</span>
                            </div>
                        @else
                            <div class="flex items-center gap-2">
                                <div class="flex-1 bg-slate-100 rounded-full h-2 overflow-hidden">
                                    <div class="{{ $barColor }} h-2 rounded-full transition-all"
                                         style="width: {{ min(100, $bp) }}%"></div>
                                </div>
                                <span class="text-xs font-extrabold {{ $pctText }} w-10 text-right tabular-nums">
                                    {{ $bp }}%
                                </span>
                            </div>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>

            {{-- Table Footer — Totals --}}
            <tfoot>
                <tr class="bg-gradient-to-r from-slate-100/80 to-slate-50/30 border-t-2 border-slate-200">
                    <td colspan="2" class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <div class="w-5 h-5 rounded bg-slate-200 flex items-center justify-center">
                                <svg class="w-3 h-3 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                                </svg>
                            </div>
                            <span class="text-xs font-extrabold text-slate-700 uppercase tracking-wide">Total</span>
                        </div>
                    </td>
                    <td colspan="2" class="px-4 py-3 text-right">
                    </td>
                    <td class="px-4 py-3 text-right font-extrabold text-slate-800 tabular-nums border-r border-slate-200">
                        Rp {{ number_format($pd['total_budget'], 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3 text-right"></td>
                    <td class="px-4 py-3 text-right font-extrabold text-indigo-700 tabular-nums border-r border-slate-200">
                        Rp {{ number_format($pd['total_realisasi'], 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3 text-right font-extrabold {{ $pd['total_sisa'] >= 0 ? 'text-emerald-700' : 'text-rose-700' }} tabular-nums">
                        Rp {{ number_format($pd['total_sisa'], 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <div class="flex-1 bg-slate-200 rounded-full h-2 overflow-hidden">
                                <div class="{{ $hBar }} h-2 rounded-full" style="width: {{ min(100, $pct) }}%"></div>
                            </div>
                            <span class="text-sm font-extrabold {{ $hText }} tabular-nums w-12 text-right">{{ $pct }}%</span>
                        </div>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- Item Legend --}}
    <div class="px-5 py-3 bg-slate-50/60 border-t border-slate-100">
        <div class="flex flex-wrap items-center gap-4 text-xs text-slate-500">
            <div class="flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-slate-300"></span>
                <span>Belum dimulai</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                <span>Sehat (&lt; 70%)</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                <span>Perhatian (70–90%)</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                <span>Kritis / Over (&gt; 90%)</span>
            </div>
            <span class="text-slate-300">·</span>
            <span class="text-slate-400">{{ $pd['items']->count() }} item material</span>
        </div>
    </div>
    @endif
</div>
@endforeach

@endif
@endsection
