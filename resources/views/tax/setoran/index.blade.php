@extends('layouts.app')

@section('content')

{{-- Breadcrumb --}}
<nav class="flex items-center gap-1.5 text-xs text-slate-400 mb-4">
    <a href="{{ route('dashboard') }}" class="hover:text-slate-600 transition-colors">Dashboard</a>
    <svg class="h-3 w-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <a href="{{ route('tax.dashboard') }}" class="hover:text-slate-600 transition-colors">Perpajakan</a>
    <svg class="h-3 w-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-slate-700 font-semibold">Setoran Pajak</span>
</nav>

{{-- Header --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div class="flex items-center gap-3">
        <div class="flex-shrink-0 w-11 h-11 rounded-2xl bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center shadow-md">
            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/>
            </svg>
        </div>
        <div>
            <h1 class="text-xl font-bold text-slate-800 leading-tight">Setoran Pajak</h1>
            <p class="text-sm text-slate-500">
                Jurnal penyetoran ke kas negara &mdash; KAP / KJS resmi DJP
                &bull; Periode: <span class="font-semibold text-slate-700">{{ \App\Services\Tax\TaxCalculatorService::formatPeriode($periode) }}</span>
            </p>
        </div>
    </div>
    <a href="{{ route('tax.setoran.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-violet-600 text-white text-sm font-semibold hover:bg-violet-700 active:bg-violet-800 transition-all shadow-sm hover:shadow-md">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
        </svg>
        Catat Setoran
    </a>
</div>

{{-- Flash Messages --}}

{{-- Overdue Alert --}}
@if($overdue > 0)
<div class="flex items-start gap-3 p-4 rounded-2xl bg-rose-50 border border-rose-300 mb-5">
    <div class="flex-shrink-0 w-8 h-8 bg-rose-100 rounded-xl flex items-center justify-center">
        <svg class="w-4 h-4 text-rose-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
        </svg>
    </div>
    <div class="flex-1 min-w-0">
        <p class="text-sm font-bold text-rose-800">{{ $overdue }} Setoran Melewati Jatuh Tempo!</p>
        <p class="text-xs text-rose-700 mt-0.5">Setor segera untuk menghindari sanksi bunga <strong>2% per bulan</strong> dari jumlah pajak terutang (Pasal 9 ayat 2a UU KUP).</p>
    </div>
    <span class="flex-shrink-0 inline-flex items-center justify-center w-8 h-8 rounded-xl bg-rose-200 text-rose-800 font-black text-sm">
        {{ $overdue }}
    </span>
</div>
@endif

{{-- KPI Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">

    {{-- Jatuh Tempo Terlewat --}}
    <div class="bg-white rounded-2xl border {{ $overdue > 0 ? 'border-rose-200' : 'border-slate-200' }} shadow-sm p-5 relative overflow-hidden group hover:shadow-md transition-all">
        <div class="absolute -top-5 -right-5 w-20 h-20 rounded-full {{ $overdue > 0 ? 'bg-rose-50' : 'bg-slate-50' }} group-hover:scale-125 transition-transform duration-300"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-3">
                <div class="w-8 h-8 rounded-xl {{ $overdue > 0 ? 'bg-rose-100' : 'bg-slate-100' }} flex items-center justify-center">
                    <svg class="w-4 h-4 {{ $overdue > 0 ? 'text-rose-600' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                    </svg>
                </div>
                @if($overdue > 0)
                <span class="text-xs font-bold text-rose-600 animate-pulse">Perlu Aksi</span>
                @endif
            </div>
            <p class="text-xs font-semibold {{ $overdue > 0 ? 'text-rose-500' : 'text-slate-400' }} uppercase tracking-wider mb-1">Jatuh Tempo Terlewat</p>
            <p class="text-3xl font-black {{ $overdue > 0 ? 'text-rose-600' : 'text-slate-500' }} tabular-nums leading-none">{{ $overdue }}</p>
            <p class="text-xs text-slate-400 mt-1.5">Setoran belum dibayar, semua periode</p>
        </div>
    </div>

    {{-- Belum Dibayar --}}
    <div class="bg-white rounded-2xl border border-amber-200 shadow-sm p-5 relative overflow-hidden group hover:shadow-md transition-all">
        <div class="absolute -top-5 -right-5 w-20 h-20 rounded-full bg-amber-50 group-hover:scale-125 transition-transform duration-300"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-3">
                <div class="w-8 h-8 rounded-xl bg-amber-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="text-xs font-semibold text-amber-600 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-full">Pending</span>
            </div>
            <p class="text-xs font-semibold text-amber-500 uppercase tracking-wider mb-1">Belum Dibayar</p>
            <p class="text-xl font-black text-amber-700 tabular-nums leading-none">Rp {{ number_format($sumBelum, 0, ',', '.') }}</p>
            <p class="text-xs text-slate-400 mt-1.5">{{ \App\Services\Tax\TaxCalculatorService::formatPeriode($periode) }}</p>
        </div>
    </div>

    {{-- Sudah Dibayar --}}
    <div class="bg-white rounded-2xl border border-emerald-200 shadow-sm p-5 relative overflow-hidden group hover:shadow-md transition-all">
        <div class="absolute -top-5 -right-5 w-20 h-20 rounded-full bg-emerald-50 group-hover:scale-125 transition-transform duration-300"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-3">
                <div class="w-8 h-8 rounded-xl bg-emerald-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="text-xs font-semibold text-emerald-600 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full">Lunas</span>
            </div>
            <p class="text-xs font-semibold text-emerald-500 uppercase tracking-wider mb-1">Sudah Dibayar</p>
            <p class="text-xl font-black text-emerald-700 tabular-nums leading-none">Rp {{ number_format($sumSudah, 0, ',', '.') }}</p>
            <p class="text-xs text-slate-400 mt-1.5">{{ \App\Services\Tax\TaxCalculatorService::formatPeriode($periode) }}</p>
        </div>
    </div>
</div>

{{-- Filter Panel --}}
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 mb-5">
    <div class="flex items-center gap-2 mb-3">
        <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z"/>
        </svg>
        <span class="text-xs font-semibold text-slate-600 uppercase tracking-wider">Filter Jurnal Setoran</span>
    </div>
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1.5">Periode</label>
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5"/>
                </svg>
                <input type="month" name="periode" value="{{ $periode }}"
                    class="pl-9 pr-3 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-700 focus:ring-2 focus:ring-violet-500 focus:border-violet-400 outline-none transition-all">
            </div>
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1.5">Jenis Pajak</label>
            <div class="relative">
                <select name="jenis_pajak"
                    class="pl-3.5 pr-8 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-700 focus:ring-2 focus:ring-violet-500 focus:border-violet-400 outline-none transition-all appearance-none min-w-[180px]">
                    <option value="">Semua Jenis</option>
                    <option value="ppn"           {{ $jenisPajak === 'ppn'           ? 'selected' : '' }}>PPN Masa</option>
                    <option value="pph21"         {{ $jenisPajak === 'pph21'         ? 'selected' : '' }}>PPh Pasal 21</option>
                    <option value="pph23"         {{ $jenisPajak === 'pph23'         ? 'selected' : '' }}>PPh Pasal 23</option>
                    <option value="pph_final_4_2" {{ $jenisPajak === 'pph_final_4_2' ? 'selected' : '' }}>PPh Final 4(2)</option>
                </select>
                <svg class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
            </div>
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1.5">Status</label>
            <div class="relative">
                <select name="status"
                    class="pl-3.5 pr-8 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-700 focus:ring-2 focus:ring-violet-500 focus:border-violet-400 outline-none transition-all appearance-none min-w-[140px]">
                    <option value="">Semua Status</option>
                    <option value="belum_bayar" {{ $status === 'belum_bayar' ? 'selected' : '' }}>Belum Bayar</option>
                    <option value="sudah_bayar" {{ $status === 'sudah_bayar' ? 'selected' : '' }}>Sudah Bayar</option>
                </select>
                <svg class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
            </div>
        </div>
        <button type="submit"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-800 text-white text-sm font-semibold hover:bg-slate-700 transition-all shadow-sm">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803 7.5 7.5 0 0015.803 15.803z"/>
            </svg>
            Terapkan
        </button>
        @if($status || $jenisPajak || $periode !== now()->format('Y-m'))
        <a href="{{ route('tax.setoran.index') }}"
           class="inline-flex items-center gap-1.5 px-3 py-2.5 rounded-xl border border-slate-200 bg-white text-sm font-medium text-slate-500 hover:bg-slate-50 transition-all">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            Reset
        </a>
        @endif
    </form>

    {{-- Active filter chips --}}
    @if($status || $jenisPajak)
    <div class="mt-3 flex flex-wrap items-center gap-2">
        <span class="text-xs text-slate-400">Filter aktif:</span>
        @if($jenisPajak)
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-violet-50 border border-violet-200 text-violet-700 text-xs font-medium">
            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5"/></svg>
            {{ match($jenisPajak) { 'ppn'=>'PPN Masa','pph21'=>'PPh 21','pph23'=>'PPh 23','pph_final_4_2'=>'PPh Final 4(2)',default=>$jenisPajak } }}
        </span>
        @endif
        @if($status)
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full {{ $status === 'sudah_bayar' ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-amber-50 border-amber-200 text-amber-700' }} border text-xs font-medium">
            <span class="w-1.5 h-1.5 rounded-full {{ $status === 'sudah_bayar' ? 'bg-emerald-400' : 'bg-amber-400' }}"></span>
            {{ $status === 'sudah_bayar' ? 'Sudah Bayar' : 'Belum Bayar' }}
        </span>
        @endif
    </div>
    @endif
</div>

{{-- Main Ledger Table --}}
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

    {{-- Table Header Bar --}}
    <div class="px-5 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h3 class="font-bold text-slate-800 text-base">Jurnal Setoran Pajak</h3>
            <p class="text-xs text-slate-400 mt-0.5">
                Periode: {{ \App\Services\Tax\TaxCalculatorService::formatPeriode($periode) }}
                &bull; {{ $setoran->total() }} catatan
            </p>
        </div>
        <div class="flex items-center gap-3 text-xs text-slate-400 flex-shrink-0">
            <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-400"></span>Sudah Bayar</span>
            <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-amber-400"></span>Belum Bayar</span>
            <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-rose-400"></span>Terlambat</span>
        </div>
    </div>

    @if($setoran->isEmpty())
    <div class="py-14 text-center">
        <div class="mx-auto w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center mb-4">
            <svg class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/>
            </svg>
        </div>
        <p class="font-semibold text-slate-600 mb-1">Belum ada catatan setoran</p>
        <p class="text-sm text-slate-400 mb-5">Periode {{ \App\Services\Tax\TaxCalculatorService::formatPeriode($periode) }} belum memiliki data setoran pajak.</p>
        <a href="{{ route('tax.setoran.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-violet-600 text-white text-sm font-semibold hover:bg-violet-700 transition-all shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Catat Setoran Pertama
        </a>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Jenis Pajak</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Periode</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">KAP / KJS</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Uraian</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-violet-600 uppercase tracking-wider">Jumlah Setor</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Jatuh Tempo</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">NTPN / Tgl Setor</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($setoran as $s)
                @php
                    $isOverdue  = $s->isOverdue();
                    $isPaid     = $s->status === 'sudah_bayar';
                    $daysLeft   = $isPaid ? null : now()->diffInDays($s->jatuh_tempo, false);
                @endphp
                <tr x-data="{ open: false }"
                    class="{{ $isOverdue ? 'bg-rose-50/40' : ($isPaid ? '' : '') }} hover:bg-slate-50/60 transition-colors border-b border-slate-100">

                    {{-- Jenis Pajak --}}
                    <td class="px-5 py-3.5">
                        @php
                            $jpColor = match($s->jenis_pajak) {
                                'ppn'           => 'bg-indigo-100 text-indigo-700 border-indigo-200',
                                'pph21'         => 'bg-slate-100 text-slate-600 border-slate-200',
                                'pph23'         => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                'pph_final_4_2' => 'bg-amber-100 text-amber-700 border-amber-200',
                                default         => 'bg-slate-100 text-slate-600 border-slate-200',
                            };
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg border text-xs font-bold {{ $jpColor }}">
                            {{ $s->getLabelJenisPajak() }}
                        </span>
                    </td>

                    {{-- Periode --}}
                    <td class="px-4 py-3.5">
                        <span class="text-xs font-semibold text-slate-700 tabular-nums">
                            {{ \App\Services\Tax\TaxCalculatorService::formatPeriode($s->periode) }}
                        </span>
                    </td>

                    {{-- KAP / KJS --}}
                    <td class="px-4 py-3.5 text-center">
                        <div class="inline-flex flex-col items-center gap-0.5">
                            <span class="font-mono text-xs font-black text-slate-700 bg-slate-100 border border-slate-200 px-2 py-0.5 rounded-lg">{{ $s->kap }}</span>
                            <span class="font-mono text-xs text-slate-500">{{ $s->kjs }}</span>
                        </div>
                    </td>

                    {{-- Uraian --}}
                    <td class="px-4 py-3.5">
                        <span class="text-xs text-slate-600">{{ $s->uraian }}</span>
                        @if($s->keterangan)
                        <p class="text-xs text-slate-400 mt-0.5 italic">{{ Str::limit($s->keterangan, 50) }}</p>
                        @endif
                    </td>

                    {{-- Jumlah --}}
                    <td class="px-4 py-3.5 text-right">
                        <span class="text-sm font-black text-slate-800 tabular-nums">Rp {{ number_format($s->jumlah, 0, ',', '.') }}</span>
                    </td>

                    {{-- Jatuh Tempo --}}
                    <td class="px-4 py-3.5">
                        <p class="text-xs font-semibold {{ $isOverdue ? 'text-rose-700' : 'text-slate-700' }} tabular-nums">
                            {{ $s->jatuh_tempo->translatedFormat('d M Y') }}
                        </p>
                        @if($isOverdue)
                        <span class="inline-flex items-center gap-1 text-xs font-bold text-rose-600 mt-0.5">
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                            TERLAMBAT
                        </span>
                        @elseif(!$isPaid && $daysLeft !== null)
                        <p class="text-xs {{ $daysLeft <= 3 ? 'text-rose-500 font-semibold' : 'text-slate-400' }} mt-0.5">
                            {{ $daysLeft }} hari lagi
                        </p>
                        @elseif($isPaid)
                        <p class="text-xs text-emerald-600 font-medium mt-0.5">Lunas</p>
                        @endif
                    </td>

                    {{-- Status --}}
                    <td class="px-4 py-3.5 text-center">
                        @if($isPaid)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-xs font-bold text-emerald-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>Lunas
                        </span>
                        @elseif($isOverdue)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-rose-50 border border-rose-200 text-xs font-bold text-rose-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-400 animate-pulse"></span>Terlambat
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-amber-50 border border-amber-200 text-xs font-bold text-amber-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>Belum Bayar
                        </span>
                        @endif
                    </td>

                    {{-- NTPN / Tgl Setor --}}
                    <td class="px-4 py-3.5">
                        @if($isPaid)
                        <p class="font-mono text-xs font-semibold text-slate-700">{{ $s->ntpn ?: '—' }}</p>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $s->tanggal_setor?->format('d/m/Y') }}</p>
                        @else
                        <span class="text-slate-300 text-xs">—</span>
                        @endif
                    </td>

                    {{-- Aksi + Inline Konfirm Panel --}}
                    <td class="px-4 py-3.5">
                        <div class="flex items-center justify-center gap-1.5">
                            @if(!$isPaid)
                            <button type="button" @click="open = !open"
                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg {{ $isOverdue ? 'bg-rose-100 border border-rose-200 text-rose-700 hover:bg-rose-200' : 'bg-emerald-100 border border-emerald-200 text-emerald-700 hover:bg-emerald-200' }} text-xs font-semibold transition-all"
                                    title="Konfirmasi Sudah Bayar">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                                </svg>
                                Bayar
                            </button>
                            @endif
                            @if(!$isPaid)
                            <form method="POST" action="{{ route('tax.setoran.destroy', $s) }}"
                                  onsubmit="return confirm('Hapus catatan setoran ini?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    class="w-7 h-7 rounded-lg border border-slate-200 bg-white flex items-center justify-center text-slate-400 hover:text-rose-600 hover:border-rose-300 hover:bg-rose-50 transition-all"
                                    title="Hapus">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                    </svg>
                                </button>
                            </form>
                            @endif
                        </div>

                        {{-- Inline Konfirmasi Pembayaran --}}
                        @if(!$isPaid)
                        <div x-show="open" x-cloak x-transition class="mt-3">
                            <form method="POST" action="{{ route('tax.setoran.konfirm', $s) }}"
                                  class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl space-y-3">
                                @csrf
                                <div class="flex items-center gap-2 mb-1">
                                    <svg class="h-4 w-4 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <p class="text-xs font-bold text-emerald-800">Konfirmasi Pembayaran</p>
                                </div>
                                <div class="grid grid-cols-1 gap-2">
                                    <div>
                                        <label class="block text-xs font-semibold text-emerald-700 mb-1">Tanggal Setor <span class="text-rose-500">*</span></label>
                                        <input type="date" name="tanggal_setor" value="{{ today()->format('Y-m-d') }}" required
                                               class="w-full px-3 py-2 rounded-xl border border-emerald-300 bg-white text-xs text-slate-700 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-400 outline-none transition-all">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-emerald-700 mb-1">
                                            NTPN <span class="text-emerald-500 font-normal">(opsional)</span>
                                        </label>
                                        <input type="text" name="ntpn" placeholder="Nomor Transaksi Penerimaan Negara"
                                               class="w-full px-3 py-2 rounded-xl border border-emerald-300 bg-white text-xs text-slate-700 font-mono placeholder-slate-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-400 outline-none transition-all">
                                    </div>
                                </div>
                                <div class="flex gap-2 pt-1">
                                    <button type="button" @click="open = false"
                                            class="flex-1 px-3 py-2 text-xs font-semibold border border-emerald-300 bg-white text-emerald-700 rounded-xl hover:bg-emerald-50 transition-all">
                                        Batal
                                    </button>
                                    <button type="submit"
                                            class="flex-1 px-3 py-2 text-xs font-bold bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 transition-all">
                                        Konfirmasi Lunas
                                    </button>
                                </div>
                            </form>
                        </div>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
            {{-- Footer Total --}}
            <tfoot>
                <tr class="bg-slate-50 border-t-2 border-slate-200">
                    <td colspan="4" class="px-5 py-3.5">
                        <span class="text-xs font-bold text-slate-600 uppercase tracking-wide">
                            Total Periode: {{ \App\Services\Tax\TaxCalculatorService::formatPeriode($periode) }}
                        </span>
                    </td>
                    <td class="px-4 py-3.5 text-right">
                        <div>
                            <p class="text-xs text-slate-400 mb-0.5">Belum Bayar</p>
                            <span class="text-sm font-black text-amber-700 tabular-nums">Rp {{ number_format($sumBelum, 0, ',', '.') }}</span>
                        </div>
                    </td>
                    <td colspan="2" class="px-4 py-3.5">
                        <div>
                            <p class="text-xs text-slate-400 mb-0.5">Sudah Bayar</p>
                            <span class="text-sm font-black text-emerald-700 tabular-nums">Rp {{ number_format($sumSudah, 0, ',', '.') }}</span>
                        </div>
                    </td>
                    <td colspan="2" class="px-4 py-3.5">
                        <div>
                            <p class="text-xs text-slate-400 mb-0.5">Total</p>
                            <span class="text-sm font-black text-slate-800 tabular-nums">Rp {{ number_format($sumBelum + $sumSudah, 0, ',', '.') }}</span>
                        </div>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
    <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2">
        <span class="text-xs text-slate-400">
            SSP / e-Billing dapat dicetak melalui djponline.pajak.go.id
        </span>
        {{ $setoran->links() }}
    </div>
    @endif
</div>

@endsection
