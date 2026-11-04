@extends('layouts.app')

@section('title', 'Laporan Pengeluaran')

@section('content')

{{-- ====================================================================== --}}
{{-- PAGE HEADER                                                              --}}
{{-- ====================================================================== --}}
<div class="mb-6">
    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-1.5 text-xs text-slate-400 mb-3">
        <a href="{{ route('financial-reports.index') }}" class="hover:text-indigo-600 transition-colors font-medium">
            Laporan Keuangan
        </a>
        <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
        </svg>
        <span class="text-slate-600 font-semibold">Laporan Pengeluaran</span>
    </nav>

    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div class="min-w-0">
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 leading-tight">
                Laporan Pengeluaran
            </h1>
            <p class="mt-1 text-sm text-slate-500">Rincian seluruh transaksi pembelian — filter, analisis, dan export</p>
        </div>
        <div class="flex flex-wrap items-center gap-2 shrink-0">
            <a href="{{ route('financial-reports.index') }}"
               class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm font-medium text-slate-600 shadow-sm hover:bg-slate-50 hover:border-slate-300 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/>
                </svg>
                Ringkasan
            </a>
            <button onclick="window.print()"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm font-medium text-slate-600 shadow-sm hover:bg-slate-50 hover:border-slate-300 transition-all print:hidden">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z"/>
                </svg>
                Cetak
            </button>
            <a href="{{ route('financial-reports.pengeluaran.export.pdf', request()->query()) }}"
               target="_blank"
               class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 active:scale-95 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                </svg>
                Export PDF
            </a>
        </div>
    </div>
</div>

{{-- ====================================================================== --}}
{{-- FILTER PANEL                                                             --}}
{{-- ====================================================================== --}}
@php
    $activeFilters = collect([
        $startDate  ? 'Dari: '    . \Carbon\Carbon::parse($startDate)->format('d M Y')  : null,
        $endDate    ? 'S/d: '     . \Carbon\Carbon::parse($endDate)->format('d M Y')    : null,
        $projectId  ? 'Proyek: '  . ($projects->firstWhere('id', $projectId)?->name ?? '-') : null,
        $supplierId ? 'Supplier: '. ($suppliers->firstWhere('id', $supplierId)?->name ?? '-') : null,
        $approvalStatus ? 'Status: ' . match($approvalStatus) {
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'pending'  => 'Menunggu',
            default    => 'Draft',
        } : null,
    ])->filter();
@endphp

<div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden mb-6 print:hidden">
    {{-- Filter Header --}}
    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3.5">
        <div class="flex items-center gap-2.5">
            <div class="w-7 h-7 rounded-lg bg-indigo-50 flex items-center justify-center">
                <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-bold text-slate-800">Filter & Pencarian</h3>
            </div>
            @if ($activeFilters->isNotEmpty())
                <span class="inline-flex items-center rounded-full bg-indigo-600 text-white text-xs font-bold px-2 py-0.5 min-w-[20px] text-center">
                    {{ $activeFilters->count() }}
                </span>
            @endif
        </div>
        @if ($activeFilters->isNotEmpty())
            <a href="{{ route('financial-reports.pengeluaran') }}"
               class="inline-flex items-center gap-1 text-xs text-rose-500 hover:text-rose-700 font-semibold transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                </svg>
                Hapus Semua Filter
            </a>
        @endif
    </div>

    <div class="p-5">
        {{-- Quick Date Presets --}}
        <div class="flex flex-wrap items-center gap-2 mb-4">
            <span class="text-xs font-semibold text-slate-400 mr-1">Pilih Cepat:</span>
            @foreach ([
                ['today',     'Hari Ini'],
                ['week',      'Minggu Ini'],
                ['month',     'Bulan Ini'],
                ['lastmonth', 'Bulan Lalu'],
                ['year',      'Tahun Ini'],
            ] as [$preset, $label])
            <button type="button" onclick="setDatePreset('{{ $preset }}')"
                    class="px-3 py-1 text-xs font-semibold rounded-lg border border-slate-200 bg-slate-50 text-slate-600 hover:bg-indigo-50 hover:border-indigo-200 hover:text-indigo-700 transition-all">
                {{ $label }}
            </button>
            @endforeach
        </div>

        {{-- Filter Form --}}
        <form method="GET" action="{{ route('financial-reports.pengeluaran') }}" id="filterForm">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
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
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">Proyek</label>
                    <select name="project_id"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-300 focus:bg-white transition-all">
                        <option value="">Semua Proyek</option>
                        @foreach ($projects as $p)
                            <option value="{{ $p->id }}" @selected($projectId == $p->id)>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">Supplier</label>
                    <select name="supplier_id"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-300 focus:bg-white transition-all">
                        <option value="">Semua Supplier</option>
                        @foreach ($suppliers as $s)
                            <option value="{{ $s->id }}" @selected($supplierId == $s->id)>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">Status</label>
                    <select name="approval_status"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-300 focus:bg-white transition-all">
                        <option value="">Semua Status</option>
                        <option value="draft"    @selected($approvalStatus === 'draft')>Draft</option>
                        <option value="pending"  @selected($approvalStatus === 'pending')>Menunggu Persetujuan</option>
                        <option value="approved" @selected($approvalStatus === 'approved')>Disetujui</option>
                        <option value="rejected" @selected($approvalStatus === 'rejected')>Ditolak</option>
                    </select>
                </div>
            </div>
            <div class="flex items-center gap-2 mt-3.5">
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 active:scale-95 transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                    </svg>
                    Terapkan Filter
                </button>
                <a href="{{ route('financial-reports.pengeluaran') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/>
                    </svg>
                    Reset
                </a>
            </div>
        </form>

        {{-- Active Filter Chips --}}
        @if ($activeFilters->isNotEmpty())
        <div class="flex flex-wrap gap-2 mt-4 pt-4 border-t border-dashed border-slate-200">
            <span class="text-xs text-slate-400 font-medium self-center">Filter aktif:</span>
            @foreach ($activeFilters as $chip)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-indigo-50 border border-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700">
                    <svg class="w-3 h-3 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                    </svg>
                    {{ $chip }}
                </span>
            @endforeach
        </div>
        @endif
    </div>
</div>

{{-- ====================================================================== --}}
{{-- KPI SUMMARY CARDS                                                        --}}
{{-- ====================================================================== --}}
@php
    $avgPerTx      = $totalCount > 0 ? $totalAmount / $totalCount : 0;
    $approvedPct   = $totalAmount > 0 ? round(($totalApproved / $totalAmount) * 100, 1) : 0;
    $notApproved   = max(0, $totalAmount - $totalApproved);
    $notApprovedPct= $totalAmount > 0 ? round(($notApproved / $totalAmount) * 100, 1) : 0;
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">

    {{-- Card 1 — Total Transaksi --}}
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-5 flex flex-col">
        <div class="flex items-start justify-between mb-4">
            <div class="w-11 h-11 rounded-2xl bg-indigo-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z"/>
                </svg>
            </div>
            <span class="text-xs font-semibold text-slate-400 bg-slate-50 border border-slate-100 rounded-lg px-2 py-0.5">Semua</span>
        </div>
        <p class="text-3xl font-extrabold text-slate-900 tracking-tight leading-none">
            {{ number_format($totalCount, 0, ',', '.') }}
        </p>
        <p class="text-sm text-slate-500 mt-1">Total Transaksi</p>
        <div class="mt-auto pt-4 border-t border-slate-100">
            <p class="text-xs text-slate-400">Rata-rata per transaksi</p>
            <p class="text-sm font-bold text-slate-700 mt-0.5">Rp {{ number_format($avgPerTx, 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Card 2 — Total Nilai --}}
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-5 flex flex-col">
        <div class="flex items-start justify-between mb-4">
            <div class="w-11 h-11 rounded-2xl bg-slate-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75"/>
                </svg>
            </div>
            <span class="text-xs font-semibold text-slate-400 bg-slate-50 border border-slate-100 rounded-lg px-2 py-0.5">Semua</span>
        </div>
        <p class="text-xl font-extrabold text-slate-900 tracking-tight leading-tight">
            Rp {{ number_format($totalAmount, 0, ',', '.') }}
        </p>
        <p class="text-sm text-slate-500 mt-1">Total Nilai Transaksi</p>
        <div class="mt-auto pt-4 border-t border-slate-100">
            <div class="flex items-center justify-between mb-1.5">
                <p class="text-xs text-slate-400">Disetujui</p>
                <p class="text-xs font-bold text-emerald-600">{{ $approvedPct }}%</p>
            </div>
            <div class="w-full bg-slate-100 rounded-full h-2">
                <div class="bg-gradient-to-r from-emerald-400 to-emerald-500 h-2 rounded-full transition-all"
                     style="width: {{ $approvedPct }}%"></div>
            </div>
        </div>
    </div>

    {{-- Card 3 — Total Disetujui --}}
    <div class="rounded-2xl border border-emerald-100 bg-gradient-to-br from-emerald-50/70 to-white shadow-sm p-5 flex flex-col">
        <div class="flex items-start justify-between mb-4">
            <div class="w-11 h-11 rounded-2xl bg-emerald-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                </svg>
            </div>
            <span class="text-xs font-semibold text-emerald-600 bg-emerald-50 border border-emerald-200 rounded-lg px-2 py-0.5">Approved</span>
        </div>
        <p class="text-xl font-extrabold text-emerald-700 tracking-tight leading-tight">
            Rp {{ number_format($totalApproved, 0, ',', '.') }}
        </p>
        <p class="text-sm text-emerald-600/80 mt-1">Total Disetujui</p>
        <div class="mt-auto pt-4 border-t border-emerald-100">
            <p class="text-xs text-emerald-500">
                <span class="font-bold">{{ $approvedPct }}%</span> dari total nilai transaksi
            </p>
        </div>
    </div>

    {{-- Card 4 — Belum Disetujui --}}
    <div class="rounded-2xl border border-amber-100 bg-gradient-to-br from-amber-50/70 to-white shadow-sm p-5 flex flex-col">
        <div class="flex items-start justify-between mb-4">
            <div class="w-11 h-11 rounded-2xl bg-amber-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                </svg>
            </div>
            <span class="text-xs font-semibold text-amber-600 bg-amber-50 border border-amber-200 rounded-lg px-2 py-0.5">Proses</span>
        </div>
        <p class="text-xl font-extrabold text-amber-700 tracking-tight leading-tight">
            Rp {{ number_format($notApproved, 0, ',', '.') }}
        </p>
        <p class="text-sm text-amber-600/80 mt-1">Belum Disetujui</p>
        <div class="mt-auto pt-4 border-t border-amber-100">
            <p class="text-xs text-amber-500">
                <span class="font-bold">{{ $notApprovedPct }}%</span> dari total nilai transaksi
            </p>
        </div>
    </div>

</div>

{{-- ====================================================================== --}}
{{-- PER-PROJECT BREAKDOWN                                                    --}}
{{-- ====================================================================== --}}
@if ($perProject->isNotEmpty())
<div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden mb-6">
    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
        <div>
            <h3 class="text-sm font-bold text-slate-800">Distribusi per Proyek</h3>
            <p class="text-xs text-slate-400 mt-0.5">Perbandingan nilai transaksi antar proyek</p>
        </div>
        <span class="text-xs bg-slate-100 text-slate-500 border border-slate-200 rounded-lg px-3 py-1 font-semibold">
            {{ $perProject->count() }} proyek
        </span>
    </div>
    <div class="p-5">
        @php
            $maxProjectTotal = $perProject->max('total');
            $projectColorSets = [
                ['bar' => 'bg-indigo-500',  'bg' => 'bg-indigo-50',  'text' => 'text-indigo-700',  'border' => 'border-indigo-100'],
                ['bar' => 'bg-emerald-500', 'bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-100'],
                ['bar' => 'bg-amber-500',   'bg' => 'bg-amber-50',   'text' => 'text-amber-700',   'border' => 'border-amber-100'],
                ['bar' => 'bg-rose-500',    'bg' => 'bg-rose-50',    'text' => 'text-rose-700',    'border' => 'border-rose-100'],
                ['bar' => 'bg-violet-500',  'bg' => 'bg-violet-50',  'text' => 'text-violet-700',  'border' => 'border-violet-100'],
                ['bar' => 'bg-cyan-500',    'bg' => 'bg-cyan-50',    'text' => 'text-cyan-700',    'border' => 'border-cyan-100'],
                ['bar' => 'bg-pink-500',    'bg' => 'bg-pink-50',    'text' => 'text-pink-700',    'border' => 'border-pink-100'],
                ['bar' => 'bg-teal-500',    'bg' => 'bg-teal-50',    'text' => 'text-teal-700',    'border' => 'border-teal-100'],
            ];
        @endphp
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @foreach ($perProject as $row)
            @php
                $cs         = $projectColorSets[$loop->index % count($projectColorSets)];
                $widthPct   = $maxProjectTotal > 0 ? round(($row->total / $maxProjectTotal) * 100) : 0;
                $ofTotalPct = $totalAmount > 0 ? round(($row->total / $totalAmount) * 100, 1) : 0;
            @endphp
            <div class="flex items-center gap-3 p-3.5 rounded-xl {{ $cs['bg'] }} border {{ $cs['border'] }}">
                <div class="w-8 h-8 rounded-lg {{ $cs['bar'] }} flex items-center justify-center text-white text-xs font-extrabold shrink-0">
                    {{ $loop->iteration }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-xs font-bold {{ $cs['text'] }} truncate max-w-[65%]"
                              title="{{ $row->project?->name ?? 'Tanpa Proyek' }}">
                            {{ $row->project?->name ?? 'Tanpa Proyek' }}
                        </span>
                        <span class="text-xs font-extrabold text-slate-700 shrink-0">{{ $ofTotalPct }}%</span>
                    </div>
                    <div class="w-full bg-white/70 rounded-full h-1.5 overflow-hidden">
                        <div class="{{ $cs['bar'] }} h-1.5 rounded-full transition-all duration-500"
                             style="width: {{ $widthPct }}%"></div>
                    </div>
                    <div class="flex items-center justify-between mt-1.5">
                        <span class="text-xs text-slate-500">{{ $row->cnt }} transaksi</span>
                        <span class="text-xs font-bold text-slate-700">
                            Rp {{ number_format($row->total, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- ====================================================================== --}}
{{-- TRANSACTIONS TABLE                                                       --}}
{{-- ====================================================================== --}}
<div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">

    {{-- Table Header --}}
    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
        <div>
            <h3 class="text-sm font-bold text-slate-800">Daftar Transaksi</h3>
            @if (!$transactions->isEmpty())
            <p class="text-xs text-slate-400 mt-0.5">
                Menampilkan
                <span class="font-semibold text-slate-600">{{ $transactions->firstItem() }}–{{ $transactions->lastItem() }}</span>
                dari
                <span class="font-semibold text-slate-600">{{ number_format($transactions->total(), 0, ',', '.') }}</span>
                transaksi
            </p>
            @endif
        </div>
        <div class="flex items-center gap-2">
            @if (!$transactions->isEmpty())
            <span class="hidden sm:inline-flex text-xs font-semibold text-slate-500 bg-slate-50 border border-slate-200 rounded-lg px-3 py-1.5">
                {{ number_format($transactions->total(), 0, ',', '.') }} data
            </span>
            @endif
            <a href="{{ route('financial-reports.pengeluaran.export.pdf', request()->query()) }}"
               target="_blank"
               class="inline-flex items-center gap-1.5 rounded-xl bg-rose-50 border border-rose-100 px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-100 transition-all">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                </svg>
                Export PDF
            </a>
        </div>
    </div>

    @if ($transactions->isEmpty())
    {{-- ================================================================ --}}
    {{-- EMPTY STATE                                                        --}}
    {{-- ================================================================ --}}
    <div class="flex flex-col items-center justify-center py-20 px-6 text-center">
        <div class="w-20 h-20 rounded-3xl bg-slate-50 border-2 border-dashed border-slate-200 flex items-center justify-center mb-5">
            <svg class="w-9 h-9 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
            </svg>
        </div>
        <h3 class="text-base font-bold text-slate-600 mb-1.5">Tidak ada transaksi ditemukan</h3>
        <p class="text-sm text-slate-400 max-w-sm leading-relaxed">
            Data tidak sesuai dengan filter yang diterapkan. Coba ubah rentang tanggal atau hapus beberapa filter.
        </p>
        <a href="{{ route('financial-reports.pengeluaran') }}"
           class="mt-6 inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 transition-all shadow-sm shadow-indigo-200">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/>
            </svg>
            Tampilkan Semua
        </a>
    </div>

    @else
    {{-- ================================================================ --}}
    {{-- DATA TABLE                                                         --}}
    {{-- ================================================================ --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50/80 border-b border-slate-100">
                    <th class="text-left text-xs font-bold text-slate-400 uppercase tracking-wide px-4 py-3 w-10">#</th>
                    <th class="text-left text-xs font-bold text-slate-400 uppercase tracking-wide px-4 py-3">No. Transaksi</th>
                    <th class="text-left text-xs font-bold text-slate-400 uppercase tracking-wide px-4 py-3">Tanggal</th>
                    <th class="text-left text-xs font-bold text-slate-400 uppercase tracking-wide px-4 py-3">Supplier</th>
                    <th class="text-left text-xs font-bold text-slate-400 uppercase tracking-wide px-4 py-3">Proyek</th>
                    <th class="text-left text-xs font-bold text-slate-400 uppercase tracking-wide px-4 py-3">Diinput Oleh</th>
                    <th class="text-right text-xs font-bold text-slate-400 uppercase tracking-wide px-4 py-3">Nilai</th>
                    <th class="text-center text-xs font-bold text-slate-400 uppercase tracking-wide px-4 py-3">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transactions as $tx)
                @php
                    $rowNum = $transactions->firstItem() + $loop->index;
                    $isEven = $loop->even;
                    $statusConfig = match($tx->approval_status) {
                        'approved' => [
                            'bg'     => 'bg-emerald-50',
                            'text'   => 'text-emerald-700',
                            'border' => 'border-emerald-200',
                            'dot'    => 'bg-emerald-500',
                            'label'  => 'Disetujui',
                        ],
                        'rejected' => [
                            'bg'     => 'bg-rose-50',
                            'text'   => 'text-rose-700',
                            'border' => 'border-rose-200',
                            'dot'    => 'bg-rose-500',
                            'label'  => 'Ditolak',
                        ],
                        'pending' => [
                            'bg'     => 'bg-amber-50',
                            'text'   => 'text-amber-700',
                            'border' => 'border-amber-200',
                            'dot'    => 'bg-amber-400',
                            'label'  => 'Menunggu',
                        ],
                        default => [
                            'bg'     => 'bg-slate-50',
                            'text'   => 'text-slate-500',
                            'border' => 'border-slate-200',
                            'dot'    => 'bg-slate-400',
                            'label'  => 'Draft',
                        ],
                    };
                    $supplierInitial = strtoupper(mb_substr($tx->supplier?->name ?? '?', 0, 1));
                    $userInitial     = strtoupper(mb_substr($tx->user?->name    ?? '?', 0, 1));
                @endphp
                <tr class="{{ $isEven ? 'bg-slate-50/30' : 'bg-white' }} hover:bg-indigo-50/40 transition-colors duration-100 border-b border-slate-50 group">

                    {{-- # --}}
                    <td class="px-4 py-3 text-xs font-medium text-slate-400">
                        {{ $rowNum }}
                    </td>

                    {{-- No. Transaksi --}}
                    <td class="px-4 py-3">
                        <a href="{{ route('purchase-transactions.show', $tx) }}"
                           class="inline-flex items-center gap-1 font-bold text-indigo-600 hover:text-indigo-800 transition-colors">
                            {{ $tx->transaction_no }}
                            <svg class="w-3 h-3 opacity-0 group-hover:opacity-100 transition-opacity shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                            </svg>
                        </a>
                    </td>

                    {{-- Tanggal --}}
                    <td class="px-4 py-3 whitespace-nowrap">
                        <span class="text-sm font-semibold text-slate-700">
                            {{ $tx->transaction_date->format('d M Y') }}
                        </span>
                    </td>

                    {{-- Supplier --}}
                    <td class="px-4 py-3">
                        @if ($tx->supplier)
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-slate-200 flex items-center justify-center text-xs font-extrabold text-slate-500 shrink-0">
                                {{ $supplierInitial }}
                            </div>
                            <span class="text-slate-700 font-medium truncate max-w-[130px]"
                                  title="{{ $tx->supplier->name }}">{{ $tx->supplier->name }}</span>
                        </div>
                        @else
                        <span class="text-slate-300 text-lg font-bold">—</span>
                        @endif
                    </td>

                    {{-- Proyek --}}
                    <td class="px-4 py-3">
                        @if ($tx->project)
                        <span class="inline-block rounded-xl bg-indigo-50 border border-indigo-100 px-2.5 py-1 text-xs font-semibold text-indigo-700 truncate max-w-[150px]"
                              title="{{ $tx->project->name }}">
                            {{ $tx->project->name }}
                        </span>
                        @else
                        <span class="text-slate-300 text-lg font-bold">—</span>
                        @endif
                    </td>

                    {{-- Diinput Oleh --}}
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-full bg-gradient-to-br from-slate-300 to-slate-400 flex items-center justify-center text-white text-xs font-bold shrink-0">
                                {{ $userInitial }}
                            </div>
                            <span class="text-xs text-slate-500 truncate max-w-[100px]">{{ $tx->user?->name ?? '—' }}</span>
                        </div>
                    </td>

                    {{-- Nilai Transaksi --}}
                    <td class="px-4 py-3 text-right">
                        <span class="font-extrabold text-slate-800 tabular-nums">
                            Rp {{ number_format($tx->grand_total, 0, ',', '.') }}
                        </span>
                        @if ($tx->grand_total >= 1_000_000)
                        <br>
                        <span class="text-xs text-slate-400 tabular-nums">
                            {{ number_format($tx->grand_total / 1_000_000, 2, ',', '.') }} jt
                        </span>
                        @endif
                    </td>

                    {{-- Status --}}
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center gap-1.5 rounded-full {{ $statusConfig['bg'] }} border {{ $statusConfig['border'] }} px-2.5 py-1 text-xs font-bold {{ $statusConfig['text'] }} whitespace-nowrap">
                            <span class="w-1.5 h-1.5 rounded-full {{ $statusConfig['dot'] }} shrink-0"></span>
                            {{ $statusConfig['label'] }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>

            {{-- Table Footer --}}
            <tfoot>
                <tr class="bg-gradient-to-r from-indigo-50 to-slate-50 border-t-2 border-indigo-100">
                    <td colspan="6" class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <div class="w-5 h-5 rounded bg-indigo-100 flex items-center justify-center">
                                <svg class="w-3 h-3 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                                </svg>
                            </div>
                            <span class="text-xs font-bold text-slate-600">
                                Sub-total halaman ini
                                <span class="font-semibold text-slate-400 ml-1">({{ $transactions->count() }} transaksi)</span>
                            </span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <span class="text-base font-extrabold text-indigo-700 tabular-nums">
                            Rp {{ number_format($transactions->sum('grand_total'), 0, ',', '.') }}
                        </span>
                    </td>
                    <td class="px-4 py-3"></td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- ================================================================ --}}
    {{-- PAGINATION                                                         --}}
    {{-- ================================================================ --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-5 py-4 border-t border-slate-100 bg-slate-50/50">
        <p class="text-xs text-slate-500 leading-relaxed">
            Menampilkan
            <span class="font-bold text-slate-700">{{ $transactions->firstItem() }}</span>
            —
            <span class="font-bold text-slate-700">{{ $transactions->lastItem() }}</span>
            dari
            <span class="font-bold text-slate-700">{{ number_format($transactions->total(), 0, ',', '.') }}</span>
            transaksi
            @if ($transactions->lastPage() > 1)
                · Halaman
                <span class="font-bold text-slate-700">{{ $transactions->currentPage() }}</span>
                dari
                <span class="font-bold text-slate-700">{{ $transactions->lastPage() }}</span>
            @endif
        </p>
        <div>
            {{ $transactions->links() }}
        </div>
    </div>

    @endif
</div>

@endsection

@push('scripts')
<script>
/**
 * Set date filter with a quick preset and auto-submit the form.
 */
function setDatePreset(preset) {
    const today = new Date();
    let start, end;

    const fmt = (d) => {
        const y  = d.getFullYear();
        const mo = String(d.getMonth() + 1).padStart(2, '0');
        const da = String(d.getDate()).padStart(2, '0');
        return `${y}-${mo}-${da}`;
    };

    switch (preset) {
        case 'today':
            start = end = fmt(today);
            break;

        case 'week': {
            const dow  = today.getDay();
            const diff = (dow === 0 ? -6 : 1) - dow;
            const mon  = new Date(today);
            mon.setDate(today.getDate() + diff);
            const sun = new Date(mon);
            sun.setDate(mon.getDate() + 6);
            start = fmt(mon);
            end   = fmt(sun);
            break;
        }

        case 'month':
            start = fmt(new Date(today.getFullYear(), today.getMonth(), 1));
            end   = fmt(new Date(today.getFullYear(), today.getMonth() + 1, 0));
            break;

        case 'lastmonth':
            start = fmt(new Date(today.getFullYear(), today.getMonth() - 1, 1));
            end   = fmt(new Date(today.getFullYear(), today.getMonth(), 0));
            break;

        case 'year':
            start = fmt(new Date(today.getFullYear(), 0, 1));
            end   = fmt(new Date(today.getFullYear(), 11, 31));
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
