@extends('layouts.app')

@section('content')
@php
    $ppnRatio = $sumKeluaran > 0 ? min(100, round(($sumMasukan / $sumKeluaran) * 100)) : 0;
    $statusKB = $selisih > 0;
    $statusLB = $selisih < 0;
    $statusNihil = $selisih == 0;
@endphp

{{-- Breadcrumb --}}
<nav class="flex items-center gap-1.5 text-xs text-slate-400 mb-4">
    <a href="{{ route('dashboard') }}" class="hover:text-slate-600 transition-colors">Dashboard</a>
    <svg class="h-3 w-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <a href="{{ route('tax.dashboard') }}" class="hover:text-slate-600 transition-colors">Perpajakan</a>
    <svg class="h-3 w-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-slate-700 font-semibold">PPN Masukan &amp; Keluaran</span>
</nav>

{{-- Professional Header --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5">
    <div class="flex items-center gap-3">
        <div class="flex-shrink-0 w-11 h-11 rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center shadow-md">
            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
            </svg>
        </div>
        <div>
            <h1 class="text-xl font-bold text-slate-800 leading-tight">PPN Masukan &amp; Keluaran</h1>
            <p class="text-sm text-slate-500">
                Buku Besar Pajak Pertambahan Nilai &mdash;
                Periode: <span class="font-semibold text-slate-700">{{ \App\Services\Tax\TaxCalculatorService::formatPeriode($periode) }}</span>
            </p>
        </div>
    </div>
    <div class="flex items-center gap-2 flex-wrap">
        <a href="{{ route('tax.ppn.masukan.create', ['periode' => $periode]) }}"
           class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-emerald-200 bg-emerald-50 text-sm font-semibold text-emerald-700 hover:bg-emerald-100 transition-all shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Faktur Masukan
        </a>
        <a href="{{ route('tax.ppn.keluaran.create', ['periode' => $periode]) }}"
           class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-indigo-600 text-sm font-semibold text-white hover:bg-indigo-700 transition-all shadow-sm hover:shadow-md">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Faktur Keluaran
        </a>
    </div>
</div>

{{-- Flash --}}

{{-- Rekap PPN Ledger --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5">

    {{-- PPN Keluaran --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 relative overflow-hidden group hover:shadow-md transition-all">
        <div class="absolute -top-5 -right-5 w-20 h-20 rounded-full bg-indigo-50 group-hover:scale-125 transition-transform duration-300"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-3">
                <div class="w-8 h-8 rounded-xl bg-indigo-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6L9 12.75l4.286-4.286a11.948 11.948 0 014.306 6.43l.776 2.898m0 0l3.182-5.511m-3.182 5.51l-5.511-3.181"/>
                    </svg>
                </div>
                <span class="text-xs font-semibold text-indigo-600 bg-indigo-50 border border-indigo-200 px-2 py-0.5 rounded-full">Pajak Keluaran</span>
            </div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">PPN Keluaran</p>
            <p class="text-2xl font-black text-indigo-700 tabular-nums leading-none">Rp {{ number_format($sumKeluaran, 0, ',', '.') }}</p>
            <p class="text-xs text-slate-400 mt-1.5">Dipungut dari pembeli (Tarif 12%)</p>
        </div>
        <div class="mt-3 pt-3 border-t border-slate-100">
            <p class="text-xs text-slate-400">KAP <span class="font-mono font-semibold text-slate-600">411211</span> &bull; KJS <span class="font-mono font-semibold text-slate-600">100</span></p>
        </div>
    </div>

    {{-- PPN Masukan --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 relative overflow-hidden group hover:shadow-md transition-all">
        <div class="absolute -top-5 -right-5 w-20 h-20 rounded-full bg-emerald-50 group-hover:scale-125 transition-transform duration-300"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-3">
                <div class="w-8 h-8 rounded-xl bg-emerald-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941"/>
                    </svg>
                </div>
                <span class="text-xs font-semibold text-emerald-600 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full">Kredit Pajak</span>
            </div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">PPN Masukan (Kredit)</p>
            <p class="text-2xl font-black text-emerald-700 tabular-nums leading-none">Rp {{ number_format($sumMasukan, 0, ',', '.') }}</p>
            <p class="text-xs text-slate-400 mt-1.5">Dapat dikreditkan dari pembelian</p>
        </div>
        <div class="mt-3 pt-3 border-t border-slate-100">
            <div class="flex items-center justify-between mb-1">
                <span class="text-xs text-slate-400">Rasio kredit</span>
                <span class="text-xs font-semibold text-slate-600">{{ $ppnRatio }}%</span>
            </div>
            <div class="h-1.5 rounded-full bg-slate-100 overflow-hidden">
                <div class="bg-emerald-500 h-full rounded-full transition-all" style="width: {{ $ppnRatio }}%"></div>
            </div>
        </div>
    </div>

    {{-- Selisih / Status --}}
    <div class="bg-white rounded-2xl border {{ $statusKB ? 'border-rose-200' : ($statusLB ? 'border-emerald-200' : 'border-slate-200') }} shadow-sm p-5 relative overflow-hidden group hover:shadow-md transition-all">
        <div class="absolute -top-5 -right-5 w-20 h-20 rounded-full {{ $statusKB ? 'bg-rose-50' : ($statusLB ? 'bg-emerald-50' : 'bg-slate-50') }} group-hover:scale-125 transition-transform duration-300"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-3">
                <div class="w-8 h-8 rounded-xl {{ $statusKB ? 'bg-rose-100' : ($statusLB ? 'bg-emerald-100' : 'bg-slate-100') }} flex items-center justify-center">
                    @if($statusKB)
                    <svg class="w-4 h-4 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                    @elseif($statusLB)
                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    @else
                    <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 12H6"/></svg>
                    @endif
                </div>
                <span class="text-xs font-bold px-2 py-0.5 rounded-full border
                    {{ $statusKB ? 'bg-rose-50 border-rose-200 text-rose-700' : ($statusLB ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-slate-100 border-slate-200 text-slate-600') }}">
                    {{ $statusKB ? 'Kurang Bayar' : ($statusLB ? 'Lebih Bayar' : 'Nihil') }}
                </span>
            </div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">
                {{ $statusKB ? 'KB — Harus Disetor' : ($statusLB ? 'LB — Dapat Dikompensasi' : 'Impas / Nihil') }}
            </p>
            <p class="text-2xl font-black {{ $statusKB ? 'text-rose-600' : ($statusLB ? 'text-emerald-700' : 'text-slate-500') }} tabular-nums leading-none">
                Rp {{ number_format(abs($selisih), 0, ',', '.') }}
            </p>
            <p class="text-xs text-slate-400 mt-1.5">
                @if($statusKB) Wajib disetor ke kas negara (jt. tgl 25)
                @elseif($statusLB) Dapat dikompensasi ke masa berikutnya
                @else Tidak ada kewajiban setoran PPN
                @endif
            </p>
        </div>
        @if($statusKB)
        <div class="mt-3 pt-3 border-t border-rose-100">
            <span class="inline-flex items-center gap-1 text-xs font-bold text-rose-600">
                <span class="w-1.5 h-1.5 rounded-full bg-rose-400 animate-pulse"></span>
                Jatuh tempo: tgl 25 bulan berikutnya
            </span>
        </div>
        @elseif($statusNihil)
        <div class="mt-3 pt-3 border-t border-slate-100">
            <span class="inline-flex items-center gap-1 text-xs font-semibold text-slate-500">
                <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                Tidak ada setoran
            </span>
        </div>
        @endif
    </div>
</div>

{{-- Filter + Tab dalam satu baris --}}
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 mb-5">
    <div class="flex flex-col sm:flex-row sm:items-center gap-4">

        {{-- Period Filter --}}
        <form method="GET" class="flex items-center gap-2 flex-shrink-0">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5"/>
                </svg>
                <input type="month" name="periode" value="{{ $periode }}"
                    class="pl-9 pr-3 py-2 rounded-xl border border-slate-200 bg-white text-sm text-slate-700 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-400 outline-none transition-all">
            </div>
            <button type="submit"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-800 text-white text-sm font-semibold hover:bg-slate-700 transition-all shadow-sm">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803 7.5 7.5 0 0015.803 15.803z"/></svg>
                Terapkan
            </button>
        </form>

        {{-- Tab Switcher --}}
        <div class="flex items-center gap-1 bg-slate-100 rounded-xl p-1 flex-1 sm:flex-none">
            <a href="{{ request()->fullUrlWithQuery(['tab' => 'masukan']) }}"
               class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg text-xs font-semibold transition-all
                      {{ $tab === 'masukan' ? 'bg-white shadow-sm text-emerald-700' : 'text-slate-500 hover:text-slate-700' }}">
                <span class="w-2 h-2 rounded-full {{ $tab === 'masukan' ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>
                Faktur Masukan
                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full text-[10px] font-black {{ $tab === 'masukan' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-500' }}">
                    {{ $masukan->total() }}
                </span>
            </a>
            <a href="{{ request()->fullUrlWithQuery(['tab' => 'keluaran']) }}"
               class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg text-xs font-semibold transition-all
                      {{ $tab === 'keluaran' ? 'bg-white shadow-sm text-indigo-700' : 'text-slate-500 hover:text-slate-700' }}">
                <span class="w-2 h-2 rounded-full {{ $tab === 'keluaran' ? 'bg-indigo-500' : 'bg-slate-300' }}"></span>
                Faktur Keluaran
                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full text-[10px] font-black {{ $tab === 'keluaran' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-200 text-slate-500' }}">
                    {{ $keluaran->total() }}
                </span>
            </a>
        </div>

        {{-- Period chips --}}
        @if($periodeList->count() > 1)
        <div class="hidden sm:flex items-center gap-1.5 flex-wrap">
            <span class="text-xs text-slate-400">Periode lain:</span>
            @foreach($periodeList->take(3) as $p)
            @if($p !== $periode)
            <a href="{{ request()->fullUrlWithQuery(['periode' => $p]) }}"
               class="text-xs px-2 py-0.5 rounded-lg bg-slate-100 text-slate-500 hover:bg-indigo-50 hover:text-indigo-700 transition-colors font-medium">
                {{ \App\Services\Tax\TaxCalculatorService::formatPeriode($p) }}
            </a>
            @endif
            @endforeach
        </div>
        @endif
    </div>
</div>

{{-- Banner: draft belum lengkap --}}
@if($tab === 'masukan' && $draftMasukanCount > 0)
<div class="flex items-start gap-3 p-4 rounded-2xl bg-amber-50 border border-amber-200 mb-4">
    <div class="flex-shrink-0 w-7 h-7 bg-amber-100 rounded-lg flex items-center justify-center">
        <svg class="h-3.5 w-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/>
        </svg>
    </div>
    <p class="text-sm text-amber-800">
        <strong>{{ $draftMasukanCount }} faktur masukan</strong> belum dilengkapi (nomor faktur kosong) di periode ini.
        Klik ikon <strong>pensil</strong> pada baris berwarna kuning untuk melengkapi.
    </p>
</div>
@endif

{{-- ═══════ TAB: MASUKAN ═══════ --}}
@if($tab === 'masukan')
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <div class="w-7 h-7 rounded-lg bg-emerald-100 flex items-center justify-center flex-shrink-0">
                <svg class="h-3.5 w-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12"/>
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-bold text-slate-800">Buku Pajak Masukan</h3>
                <p class="text-xs text-slate-400">Faktur pajak dari supplier &bull; Periode {{ \App\Services\Tax\TaxCalculatorService::formatPeriode($periode) }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2 flex-shrink-0">
            <span class="text-xs text-slate-500">Total PPN:</span>
            <span class="text-sm font-black text-emerald-700 tabular-nums bg-emerald-50 border border-emerald-200 px-2.5 py-1 rounded-lg">Rp {{ number_format($sumMasukan, 0, ',', '.') }}</span>
        </div>
    </div>

    @if($masukan->isEmpty())
    <div class="py-14 text-center">
        <div class="mx-auto w-14 h-14 rounded-2xl bg-emerald-50 flex items-center justify-center mb-4">
            <svg class="w-7 h-7 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
            </svg>
        </div>
        <p class="font-semibold text-slate-600 mb-1">Belum ada faktur pajak masukan</p>
        <p class="text-sm text-slate-400 mb-5">Periode {{ \App\Services\Tax\TaxCalculatorService::formatPeriode($periode) }}</p>
        <a href="{{ route('tax.ppn.masukan.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-emerald-200 bg-emerald-50 text-sm font-semibold text-emerald-700 hover:bg-emerald-100 transition-all">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Input Faktur Masukan
        </a>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200">
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">#</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">No. Faktur</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Tgl. Faktur</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Penjual</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">NPWP</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">DPP</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-emerald-600 uppercase tracking-wider">PPN (12%)</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Dikreditkan</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($masukan as $i => $row)
                @php $isDraft = !$row->no_faktur; @endphp
                <tr class="hover:bg-emerald-50/30 transition-colors {{ !$row->dapat_dikreditkan ? 'opacity-60' : '' }} {{ $isDraft ? 'bg-amber-50/50' : '' }}">
                    <td class="px-4 py-3.5">
                        <span class="text-[11px] font-bold text-slate-300 tabular-nums">{{ str_pad($masukan->firstItem() + $loop->index, 2, '0', STR_PAD_LEFT) }}</span>
                    </td>
                    <td class="px-4 py-3.5">
                        @if($isDraft)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-100 border border-amber-300 text-xs font-semibold text-amber-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>Belum diisi
                            </span>
                        @else
                            <span class="font-mono text-xs font-semibold text-slate-700">{{ $row->no_faktur }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3.5">
                        <span class="text-xs text-slate-600 tabular-nums font-medium">
                            {{ $row->tanggal_faktur?->format('d/m/Y') ?? '—' }}
                        </span>
                    </td>
                    <td class="px-4 py-3.5">
                        <p class="font-semibold text-slate-800 text-sm">{{ $row->nama_penjual }}</p>
                        @if($row->supplier)
                        <p class="text-xs text-slate-400 mt-0.5">{{ $row->supplier->name }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3.5">
                        <span class="font-mono text-xs text-slate-500">{{ $row->npwp_penjual ?: '—' }}</span>
                    </td>
                    <td class="px-4 py-3.5 text-right">
                        <span class="text-sm text-slate-700 tabular-nums font-semibold">Rp {{ number_format($row->dpp, 0, ',', '.') }}</span>
                    </td>
                    <td class="px-4 py-3.5 text-right">
                        <span class="text-sm font-black text-emerald-700 tabular-nums">Rp {{ number_format($row->ppn, 0, ',', '.') }}</span>
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        @if($row->status === 'normal')
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-xs font-semibold text-emerald-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>Normal
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-amber-50 border border-amber-200 text-xs font-semibold text-amber-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>Retur
                        </span>
                        @endif
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        @if($row->dapat_dikreditkan)
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-xs font-bold text-emerald-700">
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>Ya
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-slate-100 border border-slate-200 text-xs font-bold text-slate-500">
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>Tidak
                        </span>
                        @endif
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        <div class="flex items-center justify-center gap-1.5">
                            <a href="{{ route('tax.ppn.masukan.edit', $row) }}"
                               class="w-7 h-7 rounded-lg border {{ $isDraft ? 'border-amber-300 bg-amber-50 text-amber-600 hover:bg-amber-100' : 'border-slate-200 bg-white text-slate-400 hover:text-indigo-600 hover:border-indigo-300 hover:bg-indigo-50' }} flex items-center justify-center transition-all"
                               title="{{ $isDraft ? 'Lengkapi faktur' : 'Edit' }}">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/>
                                </svg>
                            </a>
                            <form method="POST" action="{{ route('tax.ppn.masukan.destroy', $row) }}"
                                  onsubmit="return confirm('Hapus faktur pajak masukan ini?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    class="w-7 h-7 rounded-lg border border-slate-200 bg-white flex items-center justify-center text-slate-400 hover:text-rose-600 hover:border-rose-300 hover:bg-rose-50 transition-all"
                                    title="Hapus">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-emerald-50 border-t-2 border-emerald-200">
                    <td colspan="5" class="px-4 py-3.5">
                        <span class="text-xs font-bold text-emerald-800 uppercase tracking-wide">Total Periode: {{ \App\Services\Tax\TaxCalculatorService::formatPeriode($periode) }}</span>
                    </td>
                    <td class="px-4 py-3.5 text-right">
                        <span class="text-sm font-bold text-slate-700 tabular-nums">Rp {{ number_format($masukan->sum('dpp'), 0, ',', '.') }}</span>
                    </td>
                    <td class="px-4 py-3.5 text-right">
                        <span class="text-base font-black text-emerald-700 tabular-nums">Rp {{ number_format($sumMasukan, 0, ',', '.') }}</span>
                    </td>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
        </table>
    </div>
    <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex items-center justify-between gap-2">
        <span class="text-xs text-slate-400">{{ $masukan->total() }} faktur &bull; Hanya faktur lengkap (no. faktur terisi) &amp; dapat dikreditkan yang dihitung</span>
        {{ $masukan->links() }}
    </div>
    @endif
</div>
@endif

{{-- ═══════ TAB: KELUARAN ═══════ --}}
@if($tab === 'keluaran')
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <div class="w-7 h-7 rounded-lg bg-indigo-100 flex items-center justify-center flex-shrink-0">
                <svg class="h-3.5 w-3.5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15"/>
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-bold text-slate-800">Buku Pajak Keluaran</h3>
                <p class="text-xs text-slate-400">Faktur pajak kepada pembeli (NSFP) &bull; Periode {{ \App\Services\Tax\TaxCalculatorService::formatPeriode($periode) }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2 flex-shrink-0">
            <span class="text-xs text-slate-500">Total PPN:</span>
            <span class="text-sm font-black text-indigo-700 tabular-nums bg-indigo-50 border border-indigo-200 px-2.5 py-1 rounded-lg">Rp {{ number_format($sumKeluaran, 0, ',', '.') }}</span>
        </div>
    </div>

    @if($keluaran->isEmpty())
    <div class="py-14 text-center">
        <div class="mx-auto w-14 h-14 rounded-2xl bg-indigo-50 flex items-center justify-center mb-4">
            <svg class="w-7 h-7 text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
            </svg>
        </div>
        <p class="font-semibold text-slate-600 mb-1">Belum ada faktur pajak keluaran</p>
        <p class="text-sm text-slate-400 mb-5">Periode {{ \App\Services\Tax\TaxCalculatorService::formatPeriode($periode) }}</p>
        <a href="{{ route('tax.ppn.keluaran.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 text-sm font-semibold text-white hover:bg-indigo-700 transition-all shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Input Faktur Keluaran
        </a>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200">
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">#</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">No. Faktur (NSFP)</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Tgl. Faktur</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Pembeli</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">NPWP</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">DPP</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-indigo-600 uppercase tracking-wider">PPN (12%)</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Invoice</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($keluaran as $row)
                <tr class="hover:bg-indigo-50/30 transition-colors">
                    <td class="px-4 py-3.5">
                        <span class="text-[11px] font-bold text-slate-300 tabular-nums">{{ str_pad($keluaran->firstItem() + $loop->index, 2, '0', STR_PAD_LEFT) }}</span>
                    </td>
                    <td class="px-4 py-3.5">
                        <span class="font-mono text-xs font-semibold text-slate-700">{{ $row->no_faktur }}</span>
                    </td>
                    <td class="px-4 py-3.5">
                        <span class="text-xs text-slate-600 tabular-nums font-medium">{{ $row->tanggal_faktur->format('d/m/Y') }}</span>
                    </td>
                    <td class="px-4 py-3.5">
                        <p class="font-semibold text-slate-800 text-sm">{{ $row->nama_pembeli }}</p>
                        @if($row->alamat_pembeli)
                        <p class="text-xs text-slate-400 truncate max-w-[140px] mt-0.5">{{ $row->alamat_pembeli }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3.5">
                        <span class="font-mono text-xs text-slate-500">{{ $row->npwp_pembeli ?: '—' }}</span>
                    </td>
                    <td class="px-4 py-3.5 text-right">
                        <span class="text-sm text-slate-700 tabular-nums font-semibold">Rp {{ number_format($row->dpp, 0, ',', '.') }}</span>
                    </td>
                    <td class="px-4 py-3.5 text-right">
                        <span class="text-sm font-black text-indigo-700 tabular-nums">Rp {{ number_format($row->ppn, 0, ',', '.') }}</span>
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        @if($row->status === 'normal')
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-xs font-semibold text-emerald-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>Normal
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-amber-50 border border-amber-200 text-xs font-semibold text-amber-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>Retur
                        </span>
                        @endif
                    </td>
                    <td class="px-4 py-3.5">
                        @if($row->invoice)
                        <a href="{{ route('invoices.show', $row->invoice) }}"
                           class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-800 hover:underline transition-colors">
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5"/></svg>
                            {{ $row->invoice->invoice_no ?? '#'.$row->invoice->id }}
                        </a>
                        @else
                        <span class="text-slate-300 text-xs">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        <form method="POST" action="{{ route('tax.ppn.keluaran.destroy', $row) }}"
                              onsubmit="return confirm('Hapus faktur pajak keluaran ini?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                class="w-7 h-7 rounded-lg border border-slate-200 bg-white flex items-center justify-center text-slate-400 hover:text-rose-600 hover:border-rose-300 hover:bg-rose-50 transition-all mx-auto"
                                title="Hapus">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                </svg>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-indigo-50 border-t-2 border-indigo-200">
                    <td colspan="5" class="px-4 py-3.5">
                        <span class="text-xs font-bold text-indigo-800 uppercase tracking-wide">Total Periode: {{ \App\Services\Tax\TaxCalculatorService::formatPeriode($periode) }}</span>
                    </td>
                    <td class="px-4 py-3.5 text-right">
                        <span class="text-sm font-bold text-slate-700 tabular-nums">Rp {{ number_format($keluaran->sum('dpp'), 0, ',', '.') }}</span>
                    </td>
                    <td class="px-4 py-3.5 text-right">
                        <span class="text-base font-black text-indigo-700 tabular-nums">Rp {{ number_format($sumKeluaran, 0, ',', '.') }}</span>
                    </td>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
        </table>
    </div>
    <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex items-center justify-between gap-2">
        <span class="text-xs text-slate-400">{{ $keluaran->total() }} faktur &bull; NSFP = Nomor Seri Faktur Pajak</span>
        {{ $keluaran->links() }}
    </div>
    @endif
</div>
@endif

@endsection
