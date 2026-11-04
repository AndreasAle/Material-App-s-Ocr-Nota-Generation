@extends('layouts.app')

@section('title', 'Dashboard Keuangan')

@section('content')

@php
    /* ------------------------------------------------------------------ */
    /* Computed values                                                       */
    /* ------------------------------------------------------------------ */
    $sisBudget      = max(0, $totalBudgetRab - $totalPengeluaran);
    $utilPct        = ($utilizationRate !== null) ? (float)$utilizationRate : null;
    $approvedVsAll  = $totalPengeluaranAll > 0
                        ? round(($totalPengeluaran / $totalPengeluaranAll) * 100, 1)
                        : 0;

    $healthConfig = match(true) {
        $utilPct === null  => [
            'label'    => 'Belum Ada RAB Aktif',
            'sublabel' => 'Buat RAB untuk memantau anggaran proyek Anda',
            'barColor' => 'bg-slate-300',
            'bgColor'  => 'bg-slate-50',
            'textColor'=> 'text-slate-600',
            'border'   => 'border-slate-200',
            'dot'      => 'bg-slate-400',
            'icon'     => 'info',
        ],
        $utilPct >= 90 => [
            'label'    => 'Status Anggaran: Kritis',
            'sublabel' => 'Utilisasi RAB sudah melewati 90% — anggaran hampir habis, perlu tindakan segera',
            'barColor' => 'bg-rose-500',
            'bgColor'  => 'bg-rose-50',
            'textColor'=> 'text-rose-700',
            'border'   => 'border-rose-200',
            'dot'      => 'bg-rose-500',
            'icon'     => 'critical',
        ],
        $utilPct >= 70 => [
            'label'    => 'Status Anggaran: Perlu Perhatian',
            'sublabel' => 'Utilisasi RAB sudah di atas 70% — pantau pengeluaran agar tidak melebihi anggaran',
            'barColor' => 'bg-amber-400',
            'bgColor'  => 'bg-amber-50',
            'textColor'=> 'text-amber-700',
            'border'   => 'border-amber-200',
            'dot'      => 'bg-amber-400',
            'icon'     => 'warning',
        ],
        default => [
            'label'    => 'Status Anggaran: Sehat',
            'sublabel' => 'Pengeluaran masih terkendali dan dalam batas anggaran yang sehat',
            'barColor' => 'bg-emerald-500',
            'bgColor'  => 'bg-emerald-50',
            'textColor'=> 'text-emerald-700',
            'border'   => 'border-emerald-200',
            'dot'      => 'bg-emerald-500',
            'icon'     => 'healthy',
        ],
    };

    $activeFilters = collect([
        $startDate  ? 'Dari: ' . \Carbon\Carbon::parse($startDate)->format('d M Y') : null,
        $endDate    ? 'S/d: '  . \Carbon\Carbon::parse($endDate)->format('d M Y')   : null,
        $projectId  ? 'Proyek: ' . ($projects->firstWhere('id', $projectId)?->name ?? '-') : null,
    ])->filter();
@endphp

{{-- ====================================================================== --}}
{{-- PAGE HEADER                                                              --}}
{{-- ====================================================================== --}}
<div class="mb-6">
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div class="min-w-0">
            <div class="flex items-center gap-2 mb-1.5">
                <div class="w-8 h-8 rounded-xl bg-indigo-600 flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/>
                    </svg>
                </div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Laporan Keuangan</span>
            </div>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 leading-tight">
                Dashboard Keuangan
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Overview pengeluaran, anggaran RAB, dan tren 12 bulan terakhir
                @if ($startDate || $endDate)
                    <span class="mx-1.5 text-slate-300">·</span>
                    <span class="font-semibold text-indigo-600">
                        {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d M Y') : '…' }}
                        —
                        {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d M Y') : '…' }}
                    </span>
                @endif
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2 shrink-0">
            <a href="{{ route('financial-reports.pengeluaran') }}"
               class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm font-medium text-slate-600 shadow-sm hover:bg-slate-50 hover:border-slate-300 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2z"/>
                </svg>
                Lap. Pengeluaran
            </a>
            <a href="{{ route('financial-reports.surat-jalan') }}"
               class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm font-medium text-slate-600 shadow-sm hover:bg-slate-50 hover:border-slate-300 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25"/>
                </svg>
                Surat Jalan
            </a>
            <a href="{{ route('financial-reports.rab-realisasi') }}"
               class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 active:scale-95 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2Zm0 0V9a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v10m-6 0a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2m0 0V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-2a2 2 0 0 1-2-2Z"/>
                </svg>
                RAB vs Realisasi
            </a>
        </div>
    </div>
</div>

{{-- ====================================================================== --}}
{{-- FILTER PANEL                                                             --}}
{{-- ====================================================================== --}}
<div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden mb-6">
    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3.5">
        <div class="flex items-center gap-2.5">
            <div class="w-7 h-7 rounded-lg bg-indigo-50 flex items-center justify-center">
                <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z"/>
                </svg>
            </div>
            <h3 class="text-sm font-bold text-slate-800">Filter Periode</h3>
            @if ($activeFilters->isNotEmpty())
                <span class="inline-flex items-center rounded-full bg-indigo-600 text-white text-xs font-bold px-2 py-0.5">
                    {{ $activeFilters->count() }}
                </span>
            @endif
        </div>
        @if ($activeFilters->isNotEmpty())
            <a href="{{ route('financial-reports.index') }}"
               class="inline-flex items-center gap-1 text-xs text-rose-500 hover:text-rose-700 font-semibold transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                </svg>
                Reset
            </a>
        @endif
    </div>

    <div class="p-5">
        {{-- Quick Presets --}}
        <div class="flex flex-wrap items-center gap-2 mb-4">
            <span class="text-xs font-semibold text-slate-400 mr-1">Pilih Cepat:</span>
            @foreach ([
                ['month',      'Bulan Ini'],
                ['lastmonth',  'Bulan Lalu'],
                ['quarter',    'Kuartal Ini'],
                ['year',       'Tahun Ini'],
                ['lastyear',   'Tahun Lalu'],
            ] as [$preset, $label])
            <button type="button" onclick="setDatePreset('{{ $preset }}')"
                    class="px-3 py-1 text-xs font-semibold rounded-lg border border-slate-200 bg-slate-50 text-slate-600 hover:bg-indigo-50 hover:border-indigo-200 hover:text-indigo-700 transition-all">
                {{ $label }}
            </button>
            @endforeach
        </div>

        <form method="GET" action="{{ route('financial-reports.index') }}" id="filterForm">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">Dari Tanggal</label>
                    <input type="date" id="start_date" name="start_date" value="{{ $startDate }}"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-300 focus:bg-white transition-all">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">Sampai Tanggal</label>
                    <input type="date" id="end_date" name="end_date" value="{{ $endDate }}"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-300 focus:bg-white transition-all">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">Filter Proyek</label>
                    <select name="project_id"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-300 focus:bg-white transition-all">
                        <option value="">Semua Proyek</option>
                        @foreach ($projects as $p)
                            <option value="{{ $p->id }}" @selected($projectId == $p->id)>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="flex items-center gap-2 mt-3.5">
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 active:scale-95 transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                    </svg>
                    Terapkan
                </button>
                <a href="{{ route('financial-reports.index') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-all">
                    Reset
                </a>
            </div>
        </form>

        @if ($activeFilters->isNotEmpty())
        <div class="flex flex-wrap gap-2 mt-4 pt-4 border-t border-dashed border-slate-200">
            <span class="text-xs text-slate-400 font-medium self-center">Filter aktif:</span>
            @foreach ($activeFilters as $chip)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-indigo-50 border border-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-400"></span>
                    {{ $chip }}
                </span>
            @endforeach
        </div>
        @endif
    </div>
</div>

{{-- ====================================================================== --}}
{{-- FINANCIAL HEALTH BANNER                                                  --}}
{{-- ====================================================================== --}}
<div class="rounded-2xl border {{ $healthConfig['border'] }} {{ $healthConfig['bgColor'] }} p-4 mb-6 flex items-center gap-4">
    <div class="flex-shrink-0">
        @if ($healthConfig['icon'] === 'healthy')
        <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
            </svg>
        </div>
        @elseif ($healthConfig['icon'] === 'warning')
        <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
            </svg>
        </div>
        @elseif ($healthConfig['icon'] === 'critical')
        <div class="w-10 h-10 rounded-full bg-rose-100 flex items-center justify-center animate-pulse">
            <svg class="w-5 h-5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/>
            </svg>
        </div>
        @else
        <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/>
            </svg>
        </div>
        @endif
    </div>
    <div class="flex-1 min-w-0">
        <p class="font-bold text-sm {{ $healthConfig['textColor'] }}">{{ $healthConfig['label'] }}</p>
        <p class="text-xs {{ $healthConfig['textColor'] }} opacity-75 mt-0.5">{{ $healthConfig['sublabel'] }}</p>
    </div>
    @if ($utilPct !== null)
    <div class="hidden sm:flex items-center gap-3 shrink-0">
        <div class="text-right">
            <p class="text-2xl font-extrabold {{ $healthConfig['textColor'] }}">{{ $utilPct }}%</p>
            <p class="text-xs {{ $healthConfig['textColor'] }} opacity-60">utilisasi RAB</p>
        </div>
        <div class="w-24">
            <div class="w-full bg-white/60 rounded-full h-2.5 overflow-hidden">
                <div class="{{ $healthConfig['barColor'] }} h-2.5 rounded-full transition-all duration-700"
                     style="width: {{ min(100, $utilPct) }}%"></div>
            </div>
        </div>
    </div>
    @endif
    @if ($txPendingApproval > 0)
    <a href="{{ route('financial-reports.pengeluaran', ['approval_status' => 'pending']) }}"
       class="hidden lg:inline-flex items-center gap-1.5 shrink-0 rounded-xl bg-amber-500 text-white text-xs font-bold px-3 py-2 hover:bg-amber-600 transition-all">
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
        </svg>
        {{ $txPendingApproval }} Pending
    </a>
    @endif
</div>

{{-- ====================================================================== --}}
{{-- KPI CARDS                                                                --}}
{{-- ====================================================================== --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">

    {{-- Card 1: Pengeluaran (kecuali ditolak) --}}
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-5 flex flex-col">
        <div class="flex items-start justify-between mb-4">
            <div class="w-11 h-11 rounded-2xl bg-indigo-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75"/>
                </svg>
            </div>
            <span class="text-xs font-semibold text-indigo-600 bg-indigo-50 border border-indigo-100 rounded-lg px-2 py-0.5">Approved</span>
        </div>
        <p class="text-xl font-extrabold text-slate-900 tracking-tight leading-tight">
            Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}
        </p>
        <p class="text-sm text-slate-500 mt-1">Pengeluaran (kecuali ditolak)</p>
        <div class="mt-auto pt-4 border-t border-slate-100">
            <div class="flex items-center justify-between mb-1.5">
                <p class="text-xs text-slate-400">vs semua status</p>
                <p class="text-xs font-bold text-indigo-600">{{ $approvedVsAll }}%</p>
            </div>
            <div class="w-full bg-slate-100 rounded-full h-1.5">
                <div class="bg-indigo-500 h-1.5 rounded-full" style="width: {{ $approvedVsAll }}%"></div>
            </div>
            <p class="text-xs text-slate-400 mt-1.5">
                Total semua: Rp {{ number_format($totalPengeluaranAll, 0, ',', '.') }}
            </p>
        </div>
    </div>

    {{-- Card 2: Total Anggaran RAB --}}
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-5 flex flex-col">
        <div class="flex items-start justify-between mb-4">
            <div class="w-11 h-11 rounded-2xl bg-emerald-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5M9 11.25v1.5M12 9v3.75m3-6v6"/>
                </svg>
            </div>
            <span class="text-xs font-semibold text-emerald-600 bg-emerald-50 border border-emerald-100 rounded-lg px-2 py-0.5">RAB Aktif</span>
        </div>
        @if ($totalBudgetRab > 0)
        <p class="text-xl font-extrabold text-slate-900 tracking-tight leading-tight">
            Rp {{ number_format($totalBudgetRab, 0, ',', '.') }}
        </p>
        <p class="text-sm text-slate-500 mt-1">Total Anggaran RAB</p>
        <div class="mt-auto pt-4 border-t border-slate-100">
            <p class="text-xs text-slate-400">Sisa anggaran</p>
            <p class="text-sm font-bold {{ $sisBudget > 0 ? 'text-emerald-600' : 'text-rose-600' }} mt-0.5">
                Rp {{ number_format($sisBudget, 0, ',', '.') }}
            </p>
        </div>
        @else
        <p class="text-base font-semibold text-slate-400 leading-tight">
            Belum Ada RAB Aktif
        </p>
        <p class="text-sm text-slate-400 mt-1">Buat RAB untuk memantau anggaran</p>
        <div class="mt-auto pt-4 border-t border-slate-100">
            <a href="{{ route('financial-reports.rab-realisasi') }}"
               class="text-xs text-indigo-600 font-semibold hover:underline">
                Lihat RAB →
            </a>
        </div>
        @endif
    </div>

    {{-- Card 3: Utilisasi RAB --}}
    @php
        $utilBg   = $utilPct === null ? 'bg-slate-50'    : ($utilPct >= 90 ? 'bg-rose-50'   : ($utilPct >= 70 ? 'bg-amber-50' : 'bg-indigo-50'));
        $utilIcon = $utilPct === null ? 'text-slate-400'  : ($utilPct >= 90 ? 'text-rose-600' : ($utilPct >= 70 ? 'text-amber-600' : 'text-indigo-600'));
        $utilVal  = $utilPct === null ? 'text-slate-400'  : ($utilPct >= 90 ? 'text-rose-700' : ($utilPct >= 70 ? 'text-amber-700' : 'text-slate-900'));
        $utilBar  = $utilPct === null ? 'bg-slate-300'    : ($utilPct >= 90 ? 'bg-rose-500'   : ($utilPct >= 70 ? 'bg-amber-400' : 'bg-indigo-500'));
        $utilBorder = $utilPct === null ? 'border-slate-200' : ($utilPct >= 90 ? 'border-rose-100' : ($utilPct >= 70 ? 'border-amber-100' : 'border-slate-200'));
        $statusLabel = $utilPct === null ? 'Tidak Ada RAB' : ($utilPct >= 90 ? 'Kritis' : ($utilPct >= 70 ? 'Perhatian' : 'Sehat'));
        $statusBadge = $utilPct === null ? 'text-slate-500 bg-slate-50 border-slate-200' : ($utilPct >= 90 ? 'text-rose-700 bg-rose-50 border-rose-200' : ($utilPct >= 70 ? 'text-amber-700 bg-amber-50 border-amber-200' : 'text-emerald-700 bg-emerald-50 border-emerald-200'));
    @endphp
    <div class="rounded-2xl border {{ $utilBorder }} bg-white shadow-sm p-5 flex flex-col">
        <div class="flex items-start justify-between mb-4">
            <div class="w-11 h-11 rounded-2xl {{ $utilBg }} flex items-center justify-center">
                <svg class="w-5 h-5 {{ $utilIcon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z"/>
                </svg>
            </div>
            <span class="text-xs font-semibold border rounded-lg px-2 py-0.5 {{ $statusBadge }}">{{ $statusLabel }}</span>
        </div>
        <p class="text-3xl font-extrabold {{ $utilVal }} tracking-tight leading-none">
            {{ $utilPct !== null ? $utilPct . '%' : '—' }}
        </p>
        <p class="text-sm text-slate-500 mt-1">Utilisasi RAB</p>
        <div class="mt-auto pt-4 border-t border-slate-100">
            <div class="w-full bg-slate-100 rounded-full h-2 mb-1.5 overflow-hidden">
                <div class="{{ $utilBar }} h-2 rounded-full transition-all duration-700"
                     style="width: {{ $utilPct !== null ? min(100, $utilPct) : 0 }}%"></div>
            </div>
            <p class="text-xs text-slate-400">
                {{ $utilPct !== null ? $utilPct . '% dari total anggaran RAB terpakai' : 'Tidak ada RAB aktif dalam periode ini' }}
            </p>
        </div>
    </div>

    {{-- Card 4: Menunggu Persetujuan --}}
    <div class="rounded-2xl border {{ $txPendingApproval > 0 ? 'border-amber-100' : 'border-slate-200' }} {{ $txPendingApproval > 0 ? 'bg-gradient-to-br from-amber-50/50 to-white' : 'bg-white' }} shadow-sm p-5 flex flex-col">
        <div class="flex items-start justify-between mb-4">
            <div class="w-11 h-11 rounded-2xl {{ $txPendingApproval > 0 ? 'bg-amber-100' : 'bg-slate-100' }} flex items-center justify-center">
                <svg class="w-5 h-5 {{ $txPendingApproval > 0 ? 'text-amber-600' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                </svg>
            </div>
            @if ($txPendingApproval > 0)
                <span class="text-xs font-bold text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-2 py-0.5 animate-pulse">
                    Butuh Aksi
                </span>
            @else
                <span class="text-xs font-semibold text-slate-400 bg-slate-50 border border-slate-200 rounded-lg px-2 py-0.5">
                    Bersih
                </span>
            @endif
        </div>
        <p class="text-3xl font-extrabold {{ $txPendingApproval > 0 ? 'text-amber-700' : 'text-slate-900' }} tracking-tight leading-none">
            {{ $txPendingApproval }}
        </p>
        <p class="text-sm text-slate-500 mt-1">Menunggu Persetujuan</p>
        <div class="mt-auto pt-4 border-t {{ $txPendingApproval > 0 ? 'border-amber-100' : 'border-slate-100' }}">
            @if ($txPendingApproval > 0)
                <a href="{{ route('financial-reports.pengeluaran', ['approval_status' => 'pending']) }}"
                   class="inline-flex items-center gap-1.5 text-xs font-bold text-amber-700 hover:text-amber-900 transition-colors">
                    Lihat transaksi pending
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
                    </svg>
                </a>
            @else
                <p class="text-xs text-slate-400">Semua transaksi sudah diproses</p>
            @endif
        </div>
    </div>

</div>

{{-- ====================================================================== --}}
{{-- CONTEXT QUICK STATS (mini stats row)                                     --}}
{{-- ====================================================================== --}}
<div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-6">
    <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 flex items-center gap-3">
        <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center shrink-0">
            <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
            </svg>
        </div>
        <div>
            <p class="text-xs text-slate-400 font-medium">Transaksi Bulan Ini</p>
            <p class="text-lg font-extrabold text-slate-800">{{ number_format($txThisMonth, 0, ',', '.') }}</p>
        </div>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 flex items-center gap-3">
        <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center shrink-0">
            <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5"/>
            </svg>
        </div>
        <div>
            <p class="text-xs text-slate-400 font-medium">Total Semua Status</p>
            <p class="text-base font-extrabold text-slate-800">Rp {{ number_format($totalPengeluaranAll / 1_000_000, 1, ',', '.') }}jt</p>
        </div>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 flex items-center gap-3 col-span-2 sm:col-span-1">
        <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center shrink-0">
            <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6 9 12.75l4.286-4.286a11.948 11.948 0 0 1 4.306 6.43l.776 2.898m0 0 3.182-5.511m-3.182 5.51-5.511-3.181"/>
            </svg>
        </div>
        <div>
            <p class="text-xs text-slate-400 font-medium">Porsi Tidak Ditolak</p>
            <p class="text-lg font-extrabold text-emerald-700">{{ $approvedVsAll }}%</p>
        </div>
    </div>
</div>

{{-- ====================================================================== --}}
{{-- CHART + PROJECT SPENDING GRID                                            --}}
{{-- ====================================================================== --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">

    {{-- ------------------------------------------------------------------ --}}
    {{-- Area Chart: Tren Pengeluaran                                         --}}
    {{-- ------------------------------------------------------------------ --}}
    <div class="lg:col-span-2 rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Tren Pengeluaran Bulanan</h3>
                <p class="text-xs text-slate-400 mt-0.5">12 bulan terakhir — transaksi disetujui</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-indigo-500 shrink-0"></span>
                <span class="text-xs text-slate-500 font-medium">Pengeluaran (Rp)</span>
            </div>
        </div>
        <div class="p-5">
            <canvas id="spendChart" height="85"></canvas>
        </div>
    </div>

    {{-- ------------------------------------------------------------------ --}}
    {{-- Per-Project Spending                                                 --}}
    {{-- ------------------------------------------------------------------ --}}
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Pengeluaran per Proyek</h3>
                <p class="text-xs text-slate-400 mt-0.5">Transaksi disetujui, top 8</p>
            </div>
            @if ($projectSpend->isNotEmpty())
            <span class="text-xs bg-slate-100 text-slate-500 border border-slate-200 rounded-lg px-2.5 py-1 font-semibold">
                {{ $projectSpend->count() }} proyek
            </span>
            @endif
        </div>
        <div class="p-5">
            @if ($projectSpend->isEmpty())
            <div class="flex flex-col items-center justify-center py-8 text-center">
                <div class="w-12 h-12 rounded-2xl bg-slate-50 border-2 border-dashed border-slate-200 flex items-center justify-center mb-3">
                    <svg class="w-6 h-6 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75Z"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-slate-400">Belum ada data proyek</p>
                <p class="text-xs text-slate-300 mt-1">Ubah rentang periode filter</p>
            </div>
            @else
            @php
                $maxSpend  = $projectSpend->max('total');
                $totalSpend= $projectSpend->sum('total');
                $barColors = [
                    ['bar' => 'bg-indigo-500',  'text' => 'text-indigo-700'],
                    ['bar' => 'bg-emerald-500', 'text' => 'text-emerald-700'],
                    ['bar' => 'bg-amber-500',   'text' => 'text-amber-700'],
                    ['bar' => 'bg-rose-500',    'text' => 'text-rose-700'],
                    ['bar' => 'bg-violet-500',  'text' => 'text-violet-700'],
                    ['bar' => 'bg-cyan-500',    'text' => 'text-cyan-700'],
                    ['bar' => 'bg-pink-500',    'text' => 'text-pink-700'],
                    ['bar' => 'bg-teal-500',    'text' => 'text-teal-700'],
                ];
            @endphp
            <div class="space-y-3.5">
                @foreach ($projectSpend as $row)
                @php
                    $cs     = $barColors[$loop->index % count($barColors)];
                    $barW   = $maxSpend > 0 ? round(($row->total / $maxSpend) * 100) : 0;
                    $pctAll = $totalSpend > 0 ? round(($row->total / $totalSpend) * 100, 1) : 0;
                @endphp
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="w-5 h-5 rounded-md {{ $cs['bar'] }} text-white text-xs font-extrabold flex items-center justify-center shrink-0">
                                {{ $loop->iteration }}
                            </span>
                            <span class="text-xs font-semibold text-slate-700 truncate max-w-[130px]"
                                  title="{{ $row->project?->name ?? 'Tanpa Proyek' }}">
                                {{ $row->project?->name ?? 'Tanpa Proyek' }}
                            </span>
                        </div>
                        <span class="text-xs font-bold {{ $cs['text'] }} shrink-0 ml-1">{{ $pctAll }}%</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                        <div class="{{ $cs['bar'] }} h-1.5 rounded-full transition-all duration-500"
                             style="width: {{ $barW }}%"></div>
                    </div>
                    <div class="flex items-center justify-between mt-0.5">
                        <span class="text-xs text-slate-400">{{ $row->tx_count }} transaksi</span>
                        <span class="text-xs font-semibold text-slate-600">
                            Rp {{ number_format($row->total / 1_000_000, 2, ',', '.') }}jt
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>

{{-- ====================================================================== --}}
{{-- RAB UTILIZATION TABLE                                                    --}}
{{-- ====================================================================== --}}
@if ($rabSummary->isNotEmpty())
<div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden mb-6">
    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
        <div>
            <h3 class="text-sm font-bold text-slate-800">Monitor Utilisasi RAB Aktif</h3>
            <p class="text-xs text-slate-400 mt-0.5">Anggaran vs realisasi pengeluaran per RAB</p>
        </div>
        <a href="{{ route('financial-reports.rab-realisasi') }}"
           class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition-colors">
            Lihat Detail Lengkap
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
            </svg>
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50/80 border-b border-slate-100">
                    <th class="text-left text-xs font-bold text-slate-400 uppercase tracking-wide px-5 py-3">RAB / Proyek</th>
                    <th class="text-right text-xs font-bold text-slate-400 uppercase tracking-wide px-4 py-3">Anggaran</th>
                    <th class="text-right text-xs font-bold text-slate-400 uppercase tracking-wide px-4 py-3">Realisasi</th>
                    <th class="text-right text-xs font-bold text-slate-400 uppercase tracking-wide px-4 py-3">Sisa</th>
                    <th class="text-left text-xs font-bold text-slate-400 uppercase tracking-wide px-4 py-3 min-w-[180px]">Utilisasi</th>
                    <th class="text-center text-xs font-bold text-slate-400 uppercase tracking-wide px-4 py-3">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rabSummary as $r)
                @php
                    $pct       = (float)$r['pct'];
                    $barColor  = $pct >= 90 ? 'bg-rose-500'   : ($pct >= 70 ? 'bg-amber-400' : 'bg-emerald-500');
                    $textColor = $pct >= 90 ? 'text-rose-700'  : ($pct >= 70 ? 'text-amber-700' : 'text-emerald-700');
                    $bgRow     = $pct >= 90 ? 'bg-rose-50/30'  : ($pct >= 70 ? 'bg-amber-50/20' : 'bg-white');
                    $statusLabel= $pct >= 90 ? 'Kritis'         : ($pct >= 70 ? 'Perhatian' : 'Sehat');
                    $statusBadge= $pct >= 90
                        ? 'bg-rose-50 text-rose-700 border-rose-200'
                        : ($pct >= 70 ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200');
                    $dotColor  = $pct >= 90 ? 'bg-rose-500' : ($pct >= 70 ? 'bg-amber-400' : 'bg-emerald-500');
                @endphp
                <tr class="{{ $bgRow }} hover:bg-indigo-50/20 transition-colors border-b border-slate-50">
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-2.5">
                            @if ($pct >= 90)
                            <div class="w-7 h-7 rounded-lg bg-rose-100 flex items-center justify-center shrink-0">
                                <svg class="w-3.5 h-3.5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/>
                                </svg>
                            </div>
                            @elseif ($pct >= 70)
                            <div class="w-7 h-7 rounded-lg bg-amber-100 flex items-center justify-center shrink-0">
                                <svg class="w-3.5 h-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                                </svg>
                            </div>
                            @else
                            <div class="w-7 h-7 rounded-lg bg-emerald-100 flex items-center justify-center shrink-0">
                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                </svg>
                            </div>
                            @endif
                            <div>
                                <p class="font-bold text-slate-800 truncate max-w-[180px]"
                                   title="{{ $r['plan']->name }}">{{ $r['plan']->name }}</p>
                                <p class="text-xs text-slate-400 truncate max-w-[180px]">{{ $r['plan']->project?->name ?? '—' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3.5 text-right text-slate-600 font-medium tabular-nums">
                        Rp {{ number_format($r['plan']->total_budget, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3.5 text-right text-slate-700 font-bold tabular-nums">
                        Rp {{ number_format($r['realisasi'], 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3.5 text-right font-bold {{ $textColor }} tabular-nums">
                        Rp {{ number_format($r['sisa'], 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3.5">
                        <div class="flex items-center gap-2.5">
                            <div class="flex-1 bg-slate-100 rounded-full h-2 overflow-hidden">
                                <div class="{{ $barColor }} h-2 rounded-full transition-all duration-500"
                                     style="width: {{ min(100, $pct) }}%"></div>
                            </div>
                            <span class="text-xs font-extrabold {{ $textColor }} w-10 text-right tabular-nums shrink-0">
                                {{ $pct }}%
                            </span>
                        </div>
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        <span class="inline-flex items-center gap-1.5 rounded-full border {{ $statusBadge }} px-2.5 py-1 text-xs font-bold">
                            <span class="w-1.5 h-1.5 rounded-full {{ $dotColor }}"></span>
                            {{ $statusLabel }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="px-5 py-3 bg-slate-50/50 border-t border-slate-100">
        <div class="flex flex-wrap items-center gap-4 text-xs text-slate-500">
            <div class="flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                <span>Sehat (&lt; 70%)</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                <span>Perhatian (70–90%)</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                <span>Kritis (&gt; 90%)</span>
            </div>
        </div>
    </div>
</div>
@endif

{{-- ====================================================================== --}}
{{-- SUB-REPORT QUICK LINKS                                                   --}}
{{-- ====================================================================== --}}
<div>
    <h2 class="text-sm font-bold text-slate-600 uppercase tracking-wide mb-3">Navigasi Cepat</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        {{-- RAB vs Realisasi --}}
        <a href="{{ route('financial-reports.rab-realisasi') }}"
           class="group relative block rounded-2xl border border-slate-200 bg-white p-5 hover:border-indigo-300 hover:shadow-md transition-all overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-indigo-50 rounded-bl-[3rem] opacity-0 group-hover:opacity-100 transition-all duration-300"></div>
            <div class="relative">
                <div class="w-11 h-11 rounded-2xl bg-indigo-50 flex items-center justify-center mb-4 group-hover:bg-indigo-100 transition-colors">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2Zm0 0V9a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v10m-6 0a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2m0 0V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-2a2 2 0 0 1-2-2Z"/>
                    </svg>
                </div>
                <div class="flex items-center justify-between">
                    <h4 class="font-bold text-slate-800 text-sm">RAB vs Realisasi</h4>
                    <svg class="w-4 h-4 text-slate-300 group-hover:text-indigo-500 group-hover:translate-x-0.5 transition-all" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
                    </svg>
                </div>
                <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">Bandingkan anggaran dengan pengeluaran aktual per item material dan proyek</p>
                @if ($rabSummary->isNotEmpty())
                <div class="mt-4 pt-3 border-t border-slate-100">
                    <p class="text-xs text-indigo-600 font-semibold">{{ $rabSummary->count() }} RAB aktif dipantau</p>
                </div>
                @endif
            </div>
        </a>

        {{-- Laporan Pengeluaran --}}
        <a href="{{ route('financial-reports.pengeluaran') }}"
           class="group relative block rounded-2xl border border-slate-200 bg-white p-5 hover:border-emerald-300 hover:shadow-md transition-all overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-emerald-50 rounded-bl-[3rem] opacity-0 group-hover:opacity-100 transition-all duration-300"></div>
            <div class="relative">
                <div class="w-11 h-11 rounded-2xl bg-emerald-50 flex items-center justify-center mb-4 group-hover:bg-emerald-100 transition-colors">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2z"/>
                    </svg>
                </div>
                <div class="flex items-center justify-between">
                    <h4 class="font-bold text-slate-800 text-sm">Laporan Pengeluaran</h4>
                    <svg class="w-4 h-4 text-slate-300 group-hover:text-emerald-500 group-hover:translate-x-0.5 transition-all" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
                    </svg>
                </div>
                <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">Detail transaksi pembelian dengan filter tanggal, proyek, supplier, dan status</p>
                @if ($txPendingApproval > 0)
                <div class="mt-4 pt-3 border-t border-slate-100">
                    <p class="text-xs text-amber-600 font-semibold">{{ $txPendingApproval }} transaksi menunggu approval</p>
                </div>
                @endif
            </div>
        </a>

        {{-- Rekap Surat Jalan --}}
        <a href="{{ route('financial-reports.surat-jalan') }}"
           class="group relative block rounded-2xl border border-slate-200 bg-white p-5 hover:border-violet-300 hover:shadow-md transition-all overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-violet-50 rounded-bl-[3rem] opacity-0 group-hover:opacity-100 transition-all duration-300"></div>
            <div class="relative">
                <div class="w-11 h-11 rounded-2xl bg-violet-50 flex items-center justify-center mb-4 group-hover:bg-violet-100 transition-colors">
                    <svg class="w-5 h-5 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/>
                    </svg>
                </div>
                <div class="flex items-center justify-between">
                    <h4 class="font-bold text-slate-800 text-sm">Rekap Surat Jalan</h4>
                    <svg class="w-4 h-4 text-slate-300 group-hover:text-violet-500 group-hover:translate-x-0.5 transition-all" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
                    </svg>
                </div>
                <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">Status penerimaan barang — pending, sebagian diterima, dan lengkap</p>
                <div class="mt-4 pt-3 border-t border-slate-100">
                    <p class="text-xs text-violet-600 font-semibold">Pantau status pengiriman barang</p>
                </div>
            </div>
        </a>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
/* ======================================================================== */
/* Trend Chart                                                                */
/* ======================================================================== */
(function () {
    const labels = @json($chartLabels);
    const data   = @json($chartData);

    const canvas = document.getElementById('spendChart');
    const ctx    = canvas.getContext('2d');

    /* Gradient fill */
    const gradient = ctx.createLinearGradient(0, 0, 0, canvas.offsetHeight || 280);
    gradient.addColorStop(0,   'rgba(99, 102, 241, 0.25)');
    gradient.addColorStop(0.7, 'rgba(99, 102, 241, 0.05)');
    gradient.addColorStop(1,   'rgba(99, 102, 241, 0)');

    const maxVal = Math.max(...data, 0);

    new Chart(canvas, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'Pengeluaran',
                data,
                backgroundColor: gradient,
                borderColor: 'rgba(99, 102, 241, 1)',
                borderWidth: 2.5,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#fff',
                pointBorderColor: 'rgba(99, 102, 241, 1)',
                pointBorderWidth: 2.5,
                pointRadius: data.map((v, i) => v === maxVal && maxVal > 0 ? 6 : 4),
                pointHoverRadius: 7,
                pointHoverBorderWidth: 3,
            }],
        },
        options: {
            responsive: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0f172a',
                    titleColor: '#94a3b8',
                    bodyColor: '#f1f5f9',
                    borderColor: 'rgba(99,102,241,0.3)',
                    borderWidth: 1,
                    padding: 12,
                    cornerRadius: 10,
                    callbacks: {
                        label: ctx => '  Rp ' + ctx.parsed.y.toLocaleString('id-ID'),
                        afterLabel: ctx => ctx.parsed.y > 0 && ctx.parsed.y === maxVal
                            ? '  ▲ Puncak tertinggi'
                            : '',
                    },
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: v => v >= 1_000_000
                            ? 'Rp ' + (v / 1_000_000).toFixed(1) + 'jt'
                            : 'Rp ' + (v / 1_000).toFixed(0) + 'rb',
                        font: { size: 11 },
                        color: '#94a3b8',
                        maxTicksLimit: 6,
                    },
                    grid: { color: 'rgba(0,0,0,0.04)' },
                    border: { dash: [4, 4] },
                },
                x: {
                    ticks: {
                        font: { size: 11 },
                        color: '#94a3b8',
                        maxRotation: 0,
                    },
                    grid: { display: false },
                },
            },
        },
    });
})();

/* ======================================================================== */
/* Date Preset Helper                                                          */
/* ======================================================================== */
function setDatePreset(preset) {
    const today = new Date();

    const fmt = (d) => {
        const y  = d.getFullYear();
        const mo = String(d.getMonth() + 1).padStart(2, '0');
        const da = String(d.getDate()).padStart(2, '0');
        return `${y}-${mo}-${da}`;
    };

    let start, end;

    switch (preset) {
        case 'month':
            start = fmt(new Date(today.getFullYear(), today.getMonth(), 1));
            end   = fmt(new Date(today.getFullYear(), today.getMonth() + 1, 0));
            break;
        case 'lastmonth':
            start = fmt(new Date(today.getFullYear(), today.getMonth() - 1, 1));
            end   = fmt(new Date(today.getFullYear(), today.getMonth(), 0));
            break;
        case 'quarter': {
            const q = Math.floor(today.getMonth() / 3);
            start = fmt(new Date(today.getFullYear(), q * 3, 1));
            end   = fmt(new Date(today.getFullYear(), q * 3 + 3, 0));
            break;
        }
        case 'year':
            start = fmt(new Date(today.getFullYear(), 0, 1));
            end   = fmt(new Date(today.getFullYear(), 11, 31));
            break;
        case 'lastyear':
            start = fmt(new Date(today.getFullYear() - 1, 0, 1));
            end   = fmt(new Date(today.getFullYear() - 1, 11, 31));
            break;
        default:
            return;
    }

    document.getElementById('start_date').value = start;
    document.getElementById('end_date').value   = end;
    document.getElementById('filterForm').submit();
}
</script>
@endpush
