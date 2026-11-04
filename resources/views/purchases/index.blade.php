@extends('layouts.app')

@section('content')
@php
    $totalItems   = $transactions->total();
    $col          = $transactions->getCollection();

    $draftCount     = $col->where('status', 'draft')->count();
    $submittedCount = $col->where('status', 'submitted')->count();
    $postedCount    = $col->where('status', 'posted')->count();

    $pageTotal   = $col->sum('grand_total');
    $pageAverage = $col->count() > 0 ? $col->avg('grand_total') : 0;
    $topTrx      = $col->sortByDesc('grand_total')->first();
    $latestTrx   = $col->sortByDesc('transaction_date')->first();

    $q          = request('q');
    $stFilter   = request('status');
    $aprFilter  = request('approval_status');
    $startDate  = request('start_date');
    $endDate    = request('end_date');
    $hasFilter  = $q || $stFilter || $aprFilter || $startDate || $endDate;
@endphp

{{-- ══════════════════════════════════════════════════
     PAGE HEADER
══════════════════════════════════════════════════ --}}
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>
            </svg>
            Operasional
            <svg class="h-3 w-3 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7"/></svg>
            Transaksi Pembelian
        </div>
        <h1 class="text-xl font-bold text-slate-900">Transaksi Pembelian</h1>
        <p class="text-sm text-slate-500 mt-0.5">Kelola semua nota pembelian material proyek</p>
    </div>

    <div class="flex items-center gap-2 shrink-0">
        {{-- Export --}}
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" @click.away="open = false"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm font-semibold text-slate-600 shadow-sm hover:bg-slate-50 transition-colors">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V4.5m0 12 4.5-4.5M12 16.5 7.5 12m-3 6.75h15"/>
                </svg>
                Export
                <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7"/></svg>
            </button>
            <div x-show="open" x-cloak
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="absolute right-0 top-full mt-1.5 w-44 rounded-xl border border-slate-100 bg-white shadow-lg shadow-slate-900/10 z-20 overflow-hidden">
                <a href="{{ route('purchase-transactions.export-index.excel', request()->query()) }}"
                   class="flex items-center gap-2.5 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 transition-colors">
                    <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                    </svg>
                    Export Excel
                </a>
                <a href="{{ route('purchase-transactions.export-index.pdf', request()->query()) }}" target="_blank"
                   class="flex items-center gap-2.5 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-rose-50 hover:text-rose-700 transition-colors">
                    <svg class="h-4 w-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v2.625A2.625 2.625 0 0116.875 19.5H7.125A2.625 2.625 0 014.5 16.875V14.25m10.5-6.75L12 4.5m0 0L9 7.5m3-3v9"/>
                    </svg>
                    Export PDF
                </a>
            </div>
        </div>

        {{-- Scan --}}
        <a href="{{ route('purchase-scans.create') }}"
           class="inline-flex items-center gap-1.5 rounded-xl border border-indigo-200 bg-indigo-50 px-3.5 py-2.5 text-sm font-semibold text-indigo-700 hover:bg-indigo-100 transition-colors">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75zM13.5 13.5h.75v.75h-.75v-.75zM13.5 19.5h.75v.75h-.75v-.75zM19.5 13.5h.75v.75h-.75v-.75zM19.5 19.5h.75v.75h-.75v-.75zM16.5 16.5h.75v.75h-.75v-.75z"/>
            </svg>
            Scan Nota
        </a>

        {{-- Input Manual (primary) --}}
        <a href="{{ route('purchase-transactions.create') }}"
           class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-indigo-200 hover:bg-indigo-700 transition-colors">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Tambah
        </a>
    </div>
</div>

{{-- ══════════════════════════════════════════════════
     STAT CARDS — 4 in a row
══════════════════════════════════════════════════ --}}
<div class="mb-5 grid grid-cols-2 gap-3 xl:grid-cols-4">
    {{-- Total --}}
    <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between gap-2">
            <p class="text-xs font-semibold text-slate-400">Total Transaksi</p>
            <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-slate-100">
                <svg class="h-3.5 w-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm0 5.25h.007v.008H3.75V12zm0 5.25h.007v.008H3.75v-.008z"/>
                </svg>
            </div>
        </div>
        <p class="mt-2 text-2xl font-black text-slate-900">{{ $totalItems }}</p>
        <p class="mt-0.5 text-xs text-slate-400">data ditemukan</p>
    </div>

    {{-- Draft --}}
    <a href="{{ route('purchase-transactions.index', array_merge(request()->query(), ['status' => 'draft'])) }}"
       class="rounded-2xl border {{ $stFilter === 'draft' ? 'border-amber-300 bg-amber-50 ring-2 ring-amber-200' : 'border-slate-100 bg-white hover:border-amber-200 hover:bg-amber-50/50' }} p-4 shadow-sm transition-all cursor-pointer">
        <div class="flex items-center justify-between gap-2">
            <p class="text-xs font-semibold text-amber-600">Draft</p>
            <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-100">
                <svg class="h-3.5 w-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                </svg>
            </div>
        </div>
        <p class="mt-2 text-2xl font-black text-amber-700">{{ $draftCount }}</p>
        <p class="mt-0.5 text-xs text-amber-500">belum final</p>
    </a>

    {{-- Submitted --}}
    <a href="{{ route('purchase-transactions.index', array_merge(request()->query(), ['status' => 'submitted'])) }}"
       class="rounded-2xl border {{ $stFilter === 'submitted' ? 'border-indigo-300 bg-indigo-50 ring-2 ring-indigo-200' : 'border-slate-100 bg-white hover:border-indigo-200 hover:bg-indigo-50/50' }} p-4 shadow-sm transition-all cursor-pointer">
        <div class="flex items-center justify-between gap-2">
            <p class="text-xs font-semibold text-indigo-600">Submitted</p>
            <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-100">
                <svg class="h-3.5 w-3.5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <p class="mt-2 text-2xl font-black text-indigo-700">{{ $submittedCount }}</p>
        <p class="mt-0.5 text-xs text-indigo-400">menunggu proses</p>
    </a>

    {{-- Posted --}}
    <a href="{{ route('purchase-transactions.index', array_merge(request()->query(), ['status' => 'posted'])) }}"
       class="rounded-2xl border {{ $stFilter === 'posted' ? 'border-emerald-300 bg-emerald-50 ring-2 ring-emerald-200' : 'border-slate-100 bg-white hover:border-emerald-200 hover:bg-emerald-50/50' }} p-4 shadow-sm transition-all cursor-pointer">
        <div class="flex items-center justify-between gap-2">
            <p class="text-xs font-semibold text-emerald-600">Posted</p>
            <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-100">
                <svg class="h-3.5 w-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <p class="mt-2 text-2xl font-black text-emerald-700">{{ $postedCount }}</p>
        <p class="mt-0.5 text-xs text-emerald-500">sudah final</p>
    </a>
</div>

{{-- ══════════════════════════════════════════════════
     FILTER BAR
══════════════════════════════════════════════════ --}}
<div class="mb-5 rounded-2xl border border-slate-100 bg-white shadow-sm">
    <form method="GET" id="filterForm">
        <div class="flex flex-col gap-3 p-4 lg:flex-row lg:items-end">

            {{-- Search --}}
            <div class="flex-1 min-w-0">
                <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-widest text-slate-400">Cari Transaksi</label>
                <div class="relative">
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z"/>
                    </svg>
                    <input type="text" name="q" value="{{ $q }}"
                           placeholder="No. transaksi, supplier, proyek..."
                           class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 pl-10 pr-4 text-sm text-slate-700 placeholder:text-slate-400 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100 transition">
                </div>
            </div>

            {{-- Tanggal --}}
            <div class="w-full lg:w-36">
                <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-widest text-slate-400">Dari</label>
                <input type="date" name="start_date" value="{{ $startDate }}"
                       class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100 transition">
            </div>
            <div class="w-full lg:w-36">
                <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-widest text-slate-400">Sampai</label>
                <input type="date" name="end_date" value="{{ $endDate }}"
                       class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100 transition">
            </div>

            {{-- Status --}}
            <div class="w-full lg:w-40">
                <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-widest text-slate-400">Status</label>
                <select name="status"
                        class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100 transition">
                    <option value="">Semua Status</option>
                    <option value="draft"     @selected($stFilter === 'draft')>Draft</option>
                    <option value="submitted" @selected($stFilter === 'submitted')>Submitted</option>
                    <option value="posted"    @selected($stFilter === 'posted')>Posted</option>
                </select>
            </div>

            {{-- Approval --}}
            <div class="w-full lg:w-44">
                <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-widest text-slate-400">Approval</label>
                <select name="approval_status"
                        class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100 transition">
                    <option value="">Semua Approval</option>
                    <option value="draft"    @selected($aprFilter === 'draft')>Belum Diajukan</option>
                    <option value="pending"  @selected($aprFilter === 'pending')>Menunggu</option>
                    <option value="approved" @selected($aprFilter === 'approved')>Disetujui</option>
                    <option value="rejected" @selected($aprFilter === 'rejected')>Ditolak</option>
                </select>
            </div>

            {{-- Buttons --}}
            <div class="flex gap-2 shrink-0">
                <button type="submit"
                        class="h-10 rounded-xl bg-indigo-600 px-5 text-sm font-semibold text-white hover:bg-indigo-700 transition-colors shadow-sm">
                    Terapkan
                </button>
                @if($hasFilter)
                <a href="{{ route('purchase-transactions.index') }}"
                   class="h-10 rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-600 inline-flex items-center hover:bg-slate-50 transition-colors">
                    Reset
                </a>
                @endif
            </div>
        </div>

        {{-- Active filter tags --}}
        @if($hasFilter)
        <div class="flex flex-wrap items-center gap-2 border-t border-slate-100 px-4 py-2.5">
            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-widest">Filter aktif:</span>
            @if($q)
            <span class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-2.5 py-1 text-[11px] font-semibold text-indigo-700">
                "{{ $q }}"
                <a href="{{ route('purchase-transactions.index', array_merge(request()->except('q'), ['page' => null])) }}" class="ml-0.5 text-indigo-400 hover:text-indigo-700">×</a>
            </span>
            @endif
            @if($stFilter)
            <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-semibold text-amber-700">
                Status: {{ ucfirst($stFilter) }}
                <a href="{{ route('purchase-transactions.index', array_merge(request()->except('status'), ['page' => null])) }}" class="ml-0.5 text-amber-400 hover:text-amber-700">×</a>
            </span>
            @endif
            @if($aprFilter)
            <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-600">
                Approval: {{ ucfirst($aprFilter) }}
                <a href="{{ route('purchase-transactions.index', array_merge(request()->except('approval_status'), ['page' => null])) }}" class="ml-0.5 text-slate-400 hover:text-slate-700">×</a>
            </span>
            @endif
            @if($startDate)
            <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-600">
                Dari: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }}
                <a href="{{ route('purchase-transactions.index', array_merge(request()->except('start_date'), ['page' => null])) }}" class="ml-0.5 text-slate-400 hover:text-slate-700">×</a>
            </span>
            @endif
            @if($endDate)
            <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-600">
                Sampai: {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
                <a href="{{ route('purchase-transactions.index', array_merge(request()->except('end_date'), ['page' => null])) }}" class="ml-0.5 text-slate-400 hover:text-slate-700">×</a>
            </span>
            @endif
        </div>
        @endif
    </form>
</div>

{{-- ══════════════════════════════════════════════════
     TABLE
══════════════════════════════════════════════════ --}}
<div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">

    {{-- Table header bar --}}
    <div class="flex items-center justify-between gap-4 border-b border-slate-100 px-5 py-3.5">
        <div class="flex items-center gap-2">
            <h2 class="text-sm font-bold text-slate-800">Daftar Transaksi</h2>
            <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-bold text-slate-500">{{ $totalItems }}</span>
        </div>
        @if($latestTrx)
        <p class="hidden text-xs text-slate-400 sm:block">
            Terakhir diupdate: <span class="font-semibold text-slate-600">{{ $latestTrx->transaction_date?->format('d M Y') ?? '-' }}</span>
        </p>
        @endif
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50/70">
                    <th class="px-5 py-3 text-left text-[10.5px] font-bold uppercase tracking-widest text-slate-400">No. Transaksi</th>
                    <th class="px-4 py-3 text-left text-[10.5px] font-bold uppercase tracking-widest text-slate-400">Tanggal</th>
                    <th class="px-4 py-3 text-left text-[10.5px] font-bold uppercase tracking-widest text-slate-400">Supplier</th>
                    <th class="px-4 py-3 text-left text-[10.5px] font-bold uppercase tracking-widest text-slate-400">Proyek</th>
                    <th class="px-4 py-3 text-left text-[10.5px] font-bold uppercase tracking-widest text-slate-400">Input</th>
                    <th class="px-4 py-3 text-left text-[10.5px] font-bold uppercase tracking-widest text-slate-400">Status</th>
                    <th class="px-4 py-3 text-left text-[10.5px] font-bold uppercase tracking-widest text-slate-400">Approval</th>
                    <th class="px-4 py-3 text-left text-[10.5px] font-bold uppercase tracking-widest text-slate-400">Oleh</th>
                    <th class="px-4 py-3 text-right text-[10.5px] font-bold uppercase tracking-widest text-slate-400">Nominal</th>
                    <th class="px-4 py-3 text-center text-[10.5px] font-bold uppercase tracking-widest text-slate-400">Aksi</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-50">
                @forelse($transactions as $trx)
                @php
                    $inputType = filled($trx->purchase_scan_draft_id ?? null) ? 'Scanner' : 'Manual';

                    $stBadge = match($trx->status) {
                        'posted'    => ['cls' => 'bg-emerald-50 text-emerald-700 border border-emerald-200', 'dot' => 'bg-emerald-400'],
                        'submitted' => ['cls' => 'bg-indigo-50 text-indigo-700 border border-indigo-200',   'dot' => 'bg-indigo-400'],
                        default     => ['cls' => 'bg-amber-50 text-amber-700 border border-amber-200',     'dot' => 'bg-amber-400'],
                    };

                    $apr = $trx->approval_status ?? 'draft';
                    $aprBadge = match($apr) {
                        'approved' => ['cls' => 'bg-emerald-50 text-emerald-700 border border-emerald-200', 'dot' => 'bg-emerald-400', 'label' => 'Disetujui'],
                        'pending'  => ['cls' => 'bg-amber-50 text-amber-700 border border-amber-200',     'dot' => 'bg-amber-400',   'label' => 'Menunggu'],
                        'rejected' => ['cls' => 'bg-rose-50 text-rose-700 border border-rose-200',         'dot' => 'bg-rose-400',    'label' => 'Ditolak'],
                        default    => ['cls' => 'bg-slate-100 text-slate-500 border border-slate-200',     'dot' => 'bg-slate-300',   'label' => 'Belum'],
                    };
                @endphp
                <tr class="group transition-colors hover:bg-slate-50/80">
                    {{-- No Transaksi --}}
                    <td class="px-5 py-3.5">
                        <a href="{{ route('purchase-transactions.show', $trx) }}" class="font-bold text-slate-900 hover:text-indigo-600 transition-colors text-sm">
                            {{ $trx->transaction_no }}
                        </a>
                    </td>

                    {{-- Tanggal --}}
                    <td class="px-4 py-3.5 text-sm text-slate-500 whitespace-nowrap">
                        {{ $trx->transaction_date?->format('d/m/Y') ?? '-' }}
                    </td>

                    {{-- Supplier --}}
                    <td class="px-4 py-3.5">
                        <span class="text-sm font-medium text-slate-700 max-w-[140px] block truncate" title="{{ $trx->supplier?->name ?? '-' }}">
                            {{ $trx->supplier?->name ?? '-' }}
                        </span>
                    </td>

                    {{-- Proyek --}}
                    <td class="px-4 py-3.5">
                        <span class="text-sm text-slate-500 max-w-[120px] block truncate" title="{{ $trx->project?->name ?? '-' }}">
                            {{ $trx->project?->name ?? '-' }}
                        </span>
                    </td>

                    {{-- Tipe Input --}}
                    <td class="px-4 py-3.5">
                        @if($inputType === 'Scanner')
                        <span class="inline-flex items-center gap-1 rounded-lg bg-indigo-50 px-2 py-1 text-[11px] font-semibold text-indigo-600">
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5z"/><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/></svg>
                            Scan
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 rounded-lg bg-slate-100 px-2 py-1 text-[11px] font-semibold text-slate-500">
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                            Manual
                        </span>
                        @endif
                    </td>

                    {{-- Status --}}
                    <td class="px-4 py-3.5">
                        <span class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-[11px] font-semibold {{ $stBadge['cls'] }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ $stBadge['dot'] }}"></span>
                            {{ ucfirst($trx->status) }}
                        </span>
                    </td>

                    {{-- Approval --}}
                    <td class="px-4 py-3.5">
                        <span class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-[11px] font-semibold {{ $aprBadge['cls'] }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ $aprBadge['dot'] }}"></span>
                            {{ $aprBadge['label'] }}
                        </span>
                    </td>

                    {{-- User --}}
                    <td class="px-4 py-3.5 text-sm text-slate-500 max-w-[100px]">
                        <span class="block truncate" title="{{ $trx->user?->name ?? '-' }}">{{ $trx->user?->name ?? '-' }}</span>
                    </td>

                    {{-- Nominal --}}
                    <td class="px-4 py-3.5 text-right">
                        <span class="text-sm font-bold text-slate-900 whitespace-nowrap">
                            Rp {{ number_format($trx->grand_total, 0, ',', '.') }}
                        </span>
                    </td>

                    {{-- Aksi --}}
                    <td class="px-4 py-3.5">
                        <div class="flex items-center justify-center gap-1">
                            {{-- Detail --}}
                            <a href="{{ route('purchase-transactions.show', $trx) }}"
                               title="Lihat Detail"
                               class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-600 transition-colors">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </a>

                            {{-- Edit --}}
                            <a href="{{ route('purchase-transactions.edit', $trx) }}"
                               title="Edit"
                               class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 hover:border-amber-200 hover:bg-amber-50 hover:text-amber-600 transition-colors">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/>
                                </svg>
                            </a>

                            {{-- PDF --}}
                            @if(Route::has('purchase-transactions.preview-detail.pdf'))
                            <a href="{{ route('purchase-transactions.preview-detail.pdf', $trx) }}" target="_blank"
                               title="Export PDF"
                               class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600 transition-colors">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                                </svg>
                            </a>
                            @endif

                            {{-- Hapus (admin only) --}}
                            @if(auth()->user()->isAdmin())
                            <form method="POST" action="{{ route('purchase-transactions.destroy', $trx) }}"
                                  onsubmit="return confirm('Hapus transaksi {{ $trx->transaction_no }}?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        title="Hapus"
                                        class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 hover:border-rose-300 hover:bg-rose-50 hover:text-rose-600 transition-colors">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                    </svg>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="px-6 py-16 text-center">
                        <div class="mx-auto flex flex-col items-center max-w-sm">
                            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-300 mb-4">
                                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75A2.25 2.25 0 0 1 6 4.5h12a2.25 2.25 0 0 1 2.25 2.25v10.5A2.25 2.25 0 0 1 18 19.5H6a2.25 2.25 0 0 1-2.25-2.25V6.75Z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 9h9m-9 3h9m-9 3h6"/>
                                </svg>
                            </div>
                            <h3 class="text-base font-bold text-slate-800">Tidak ada transaksi ditemukan</h3>
                            <p class="mt-1.5 text-sm text-slate-400 text-center">
                                @if($hasFilter)
                                    Coba ubah atau hapus filter pencarian.
                                @else
                                    Belum ada transaksi. Mulai dengan input nota baru.
                                @endif
                            </p>
                            <div class="mt-5 flex flex-wrap justify-center gap-2">
                                @if($hasFilter)
                                <a href="{{ route('purchase-transactions.index') }}"
                                   class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
                                    Hapus Filter
                                </a>
                                @endif
                                <a href="{{ route('purchase-transactions.create') }}"
                                   class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 transition-colors">
                                    Input Manual
                                </a>
                                <a href="{{ route('purchase-scans.create') }}"
                                   class="rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-100 transition-colors">
                                    Scan Nota
                                </a>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Footer: summary + pagination --}}
    @if($transactions->count() > 0)
    <div class="flex flex-col gap-3 border-t border-slate-100 bg-slate-50/50 px-5 py-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-wrap items-center gap-4 text-xs text-slate-500">
            <span>Total halaman ini: <strong class="text-slate-800">Rp {{ number_format($pageTotal, 0, ',', '.') }}</strong></span>
            <span class="text-slate-300">|</span>
            <span>Rata-rata: <strong class="text-slate-800">Rp {{ number_format($pageAverage, 0, ',', '.') }}</strong></span>
            @if($topTrx)
            <span class="text-slate-300">|</span>
            <span>Tertinggi: <strong class="text-slate-800">{{ $topTrx->transaction_no }}</strong></span>
            @endif
        </div>
        <div class="shrink-0">
            {{ $transactions->links() }}
        </div>
    </div>
    @endif
</div>

@endsection
