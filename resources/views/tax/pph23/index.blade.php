@extends('layouts.app')

@section('content')

{{-- Breadcrumb --}}
<nav class="flex items-center gap-1.5 text-xs text-slate-400 mb-4">
    <a href="{{ route('dashboard') }}" class="hover:text-slate-600 transition-colors">Dashboard</a>
    <svg class="h-3 w-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <a href="{{ route('tax.dashboard') }}" class="hover:text-slate-600 transition-colors">Perpajakan</a>
    <svg class="h-3 w-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-slate-700 font-semibold">PPh Pasal 23</span>
</nav>

{{-- Professional Header --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5">
    <div class="flex items-center gap-3">
        <div class="flex-shrink-0 w-11 h-11 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-md">
            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75"/>
            </svg>
        </div>
        <div>
            <h1 class="text-xl font-bold text-slate-800 leading-tight">PPh Pasal 23</h1>
            <p class="text-sm text-slate-500">
                Buku Pemotongan Pajak &mdash; Dasar: <span class="font-medium text-emerald-700">UU PPh Ps. 23 Ayat (1) Huruf c</span>
                &bull; Periode: <span class="font-semibold text-slate-700">{{ \App\Services\Tax\TaxCalculatorService::formatPeriode($periode) }}</span>
            </p>
        </div>
    </div>
    <a href="{{ route('tax.pph23.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-bold hover:bg-emerald-700 active:bg-emerald-800 transition-all shadow-sm hover:shadow-md">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
        </svg>
        Catat Bukti Potong
    </a>
</div>

{{-- Flash --}}

{{-- KPI Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5">

    {{-- PPh Dipotong --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 relative overflow-hidden group hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
        <div class="absolute -top-5 -right-5 w-20 h-20 rounded-full bg-emerald-50 group-hover:scale-125 transition-transform duration-300"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-3">
                <div class="w-8 h-8 rounded-xl bg-emerald-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0c1.1.128 1.907 1.077 1.907 2.185Z"/>
                    </svg>
                </div>
                <span class="text-xs font-semibold text-emerald-600 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full">PPh Dipotong</span>
            </div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Total PPh 23 Dipotong</p>
            <p class="text-2xl font-black text-emerald-700 tabular-nums leading-none">Rp {{ number_format($sumPph, 0, ',', '.') }}</p>
            <p class="text-xs text-slate-400 mt-1.5">Periode {{ \App\Services\Tax\TaxCalculatorService::formatPeriode($periode) }}</p>
        </div>
        <div class="mt-3 pt-3 border-t border-slate-100">
            <p class="text-xs text-slate-400">KAP <span class="font-mono font-semibold text-slate-600">411124</span> &bull; KJS <span class="font-mono font-semibold text-slate-600">104</span></p>
        </div>
    </div>

    {{-- Total Bruto --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 relative overflow-hidden group hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
        <div class="absolute -top-5 -right-5 w-20 h-20 rounded-full bg-indigo-50 group-hover:scale-125 transition-transform duration-300"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-3">
                <div class="w-8 h-8 rounded-xl bg-indigo-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="text-xs font-semibold text-indigo-600 bg-indigo-50 border border-indigo-200 px-2 py-0.5 rounded-full">DPP Bruto</span>
            </div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Total Bruto</p>
            <p class="text-2xl font-black text-slate-800 tabular-nums leading-none">Rp {{ number_format($sumBruto, 0, ',', '.') }}</p>
            <p class="text-xs text-slate-400 mt-1.5">Nilai sebelum dipotong PPh</p>
        </div>
        <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between">
            <span class="text-xs text-slate-400">Efektif rate</span>
            <span class="text-xs font-bold text-indigo-700">
                {{ $sumBruto > 0 ? number_format(($sumPph / $sumBruto) * 100, 2) : '0,00' }}%
            </span>
        </div>
    </div>

    {{-- Belum Disetor --}}
    <div class="bg-white rounded-2xl border {{ $belumSetor > 0 ? 'border-rose-200' : 'border-slate-200' }} shadow-sm p-5 relative overflow-hidden group hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
        <div class="absolute -top-5 -right-5 w-20 h-20 rounded-full {{ $belumSetor > 0 ? 'bg-rose-50' : 'bg-slate-50' }} group-hover:scale-125 transition-transform duration-300"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-3">
                <div class="w-8 h-8 rounded-xl {{ $belumSetor > 0 ? 'bg-rose-100' : 'bg-slate-100' }} flex items-center justify-center">
                    <svg class="w-4 h-4 {{ $belumSetor > 0 ? 'text-rose-600' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                    </svg>
                </div>
                @if($belumSetor > 0)
                <span class="text-xs font-bold text-rose-600 animate-pulse">Perlu Aksi</span>
                @endif
            </div>
            <p class="text-xs font-semibold {{ $belumSetor > 0 ? 'text-rose-500' : 'text-slate-400' }} uppercase tracking-wider mb-1">Belum Disetor</p>
            <p class="text-3xl font-black {{ $belumSetor > 0 ? 'text-rose-600' : 'text-slate-500' }} tabular-nums leading-none">{{ $belumSetor }}</p>
            <p class="text-xs text-slate-400 mt-1.5">Catatan semua periode</p>
        </div>
        @if($belumSetor > 0)
        <div class="mt-3 pt-3 border-t border-rose-100">
            <span class="inline-flex items-center gap-1 text-xs font-bold text-rose-600">
                <span class="w-1.5 h-1.5 rounded-full bg-rose-400 animate-pulse"></span>
                Jatuh tempo: tgl 10 bulan berikutnya
            </span>
        </div>
        @else
        <div class="mt-3 pt-3 border-t border-slate-100">
            <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                Semua sudah disetor
            </span>
        </div>
        @endif
    </div>
</div>

{{-- Referensi Tarif --}}
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm mb-5 overflow-hidden">
    <div class="px-5 py-3 bg-slate-50 border-b border-slate-200 flex items-center gap-2">
        <svg class="h-4 w-4 text-slate-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
        </svg>
        <h3 class="text-xs font-bold text-slate-600 uppercase tracking-wider">Referensi Tarif — UU PPh Pasal 23 Ayat (1) Huruf c</h3>
        <span class="ml-auto text-xs text-slate-400 bg-slate-100 border border-slate-200 px-2 py-0.5 rounded-full font-medium">Untuk Jasa Konstruksi & Sejenisnya</span>
    </div>
    <div class="p-4 flex flex-wrap gap-3">
        {{-- 2% NPWP --}}
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl border border-emerald-200 bg-emerald-50 flex-1 min-w-[200px]">
            <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center">
                <span class="text-xl font-black text-emerald-700">2%</span>
            </div>
            <div>
                <p class="text-sm font-bold text-emerald-800">Penerima Punya NPWP</p>
                <p class="text-xs text-emerald-600 mt-0.5">Tarif 2% × Nilai Bruto Jasa</p>
            </div>
        </div>
        {{-- 4% Tanpa NPWP --}}
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl border border-rose-200 bg-rose-50 flex-1 min-w-[200px]">
            <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-rose-100 flex items-center justify-center">
                <span class="text-xl font-black text-rose-700">4%</span>
            </div>
            <div>
                <p class="text-sm font-bold text-rose-800">Tidak Punya NPWP</p>
                <p class="text-xs text-rose-600 mt-0.5">Tarif 4% × Bruto (sanksi)</p>
            </div>
        </div>
        {{-- KAP/KJS --}}
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl border border-slate-200 bg-slate-50">
            <div class="space-y-1 text-xs text-slate-600">
                <div class="flex items-center gap-2">
                    <span class="text-slate-400 w-16">KAP</span>
                    <span class="font-mono font-black text-slate-800">411124</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-slate-400 w-16">KJS</span>
                    <span class="font-mono font-black text-slate-800">104</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-slate-400 w-16">Jatuh Tempo</span>
                    <span class="font-semibold text-slate-700">Tgl 10 bln berikutnya</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filter Panel --}}
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 mb-5">
    <div class="flex items-center gap-2 mb-3">
        <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z"/>
        </svg>
        <span class="text-xs font-semibold text-slate-600 uppercase tracking-wider">Filter Data</span>
    </div>
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1.5">Periode</label>
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25"/>
                </svg>
                <input type="month" name="periode" value="{{ $periode }}"
                    class="pl-9 pr-3 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-700 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-400 outline-none transition-all">
            </div>
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1.5">Status Setoran</label>
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <select name="status"
                    class="pl-9 pr-8 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-700 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-400 outline-none transition-all appearance-none min-w-[180px]">
                    <option value="">Semua Status</option>
                    <option value="belum_disetor" {{ $status === 'belum_disetor' ? 'selected' : '' }}>Belum Disetor</option>
                    <option value="sudah_disetor" {{ $status === 'sudah_disetor' ? 'selected' : '' }}>Sudah Disetor</option>
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
        @if($status || $periode !== now()->format('Y-m'))
        <a href="{{ route('tax.pph23.index') }}"
           class="inline-flex items-center gap-1.5 px-3 py-2.5 rounded-xl border border-slate-200 bg-white text-sm font-medium text-slate-500 hover:bg-slate-50 transition-all">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            Reset
        </a>
        @endif
    </form>
</div>

{{-- Main Ledger Table --}}
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

    {{-- Table Header Bar --}}
    <div class="px-5 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <div class="w-7 h-7 rounded-lg bg-emerald-100 flex items-center justify-center flex-shrink-0">
                <svg class="h-3.5 w-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0c1.1.128 1.907 1.077 1.907 2.185Z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-bold text-slate-800">Buku Pemotongan PPh Pasal 23</h3>
                <p class="text-xs text-slate-400 mt-0.5">
                    Periode: {{ \App\Services\Tax\TaxCalculatorService::formatPeriode($periode) }}
                    &bull; {{ $records->total() }} catatan
                </p>
            </div>
        </div>
        <div class="flex items-center gap-3 text-xs text-slate-400 flex-shrink-0">
            <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-400"></span>Sudah Disetor</span>
            <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>Belum Disetor</span>
        </div>
    </div>

    @if($records->isEmpty())
    <div class="py-14 text-center">
        <div class="mx-auto w-14 h-14 rounded-2xl bg-emerald-50 flex items-center justify-center mb-4">
            <svg class="w-7 h-7 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75"/>
            </svg>
        </div>
        <p class="font-semibold text-slate-600 mb-1">Belum ada catatan PPh Pasal 23</p>
        <p class="text-sm text-slate-400 mb-5">Periode {{ \App\Services\Tax\TaxCalculatorService::formatPeriode($periode) }}</p>
        <a href="{{ route('tax.pph23.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 text-sm font-bold text-white hover:bg-emerald-700 transition-all shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Catat Bukti Potong
        </a>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200">
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider w-10">#</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Penerima</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">NPWP</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Jenis Penghasilan</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Bruto</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Tarif</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-emerald-600 uppercase tracking-wider">PPh Dipotong</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Nilai Dibayar</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">No. Bukpot</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($records as $r)
                <tr x-data="{ open: false }"
                    class="{{ $r->status === 'belum_disetor' ? 'bg-amber-50/20' : '' }} hover:bg-slate-50/60 transition-colors border-b border-slate-100">

                    {{-- # --}}
                    <td class="px-4 py-3.5">
                        <span class="text-[11px] font-bold text-slate-300 tabular-nums">{{ str_pad($records->firstItem() + $loop->index, 2, '0', STR_PAD_LEFT) }}</span>
                    </td>

                    {{-- Penerima --}}
                    <td class="px-4 py-3.5">
                        <p class="font-semibold text-slate-800 text-sm">{{ $r->nama_penerima }}</p>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $r->tanggal_bukti_potong?->format('d/m/Y') ?? '—' }}</p>
                    </td>

                    {{-- NPWP --}}
                    <td class="px-4 py-3.5">
                        @if($r->isPunyaNpwp())
                        <div class="flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 flex-shrink-0"></span>
                            <span class="font-mono text-xs text-slate-600">{{ $r->npwp_penerima }}</span>
                        </div>
                        @else
                        <div class="flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-400 flex-shrink-0"></span>
                            <span class="text-xs font-semibold text-rose-600">Tidak ada NPWP</span>
                        </div>
                        @endif
                    </td>

                    {{-- Jenis Penghasilan --}}
                    <td class="px-4 py-3.5">
                        <span class="text-xs text-slate-600 font-medium">{{ $r->jenis_penghasilan }}</span>
                    </td>

                    {{-- Bruto --}}
                    <td class="px-4 py-3.5 text-right">
                        <span class="text-sm font-semibold text-slate-700 tabular-nums">Rp {{ number_format($r->bruto, 0, ',', '.') }}</span>
                    </td>

                    {{-- Tarif --}}
                    <td class="px-4 py-3.5 text-center">
                        <span class="inline-block px-2.5 py-1 rounded-lg text-xs font-black tabular-nums {{ $r->tarif == 2 ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                            {{ $r->tarif }}%
                        </span>
                    </td>

                    {{-- PPh Dipotong --}}
                    <td class="px-4 py-3.5 text-right">
                        <span class="text-sm font-black text-emerald-700 tabular-nums">Rp {{ number_format($r->pph_dipotong, 0, ',', '.') }}</span>
                    </td>

                    {{-- Nilai Dibayar --}}
                    <td class="px-4 py-3.5 text-right">
                        <span class="text-sm font-semibold text-slate-700 tabular-nums">Rp {{ number_format($r->getNilaiDibayar(), 0, ',', '.') }}</span>
                    </td>

                    {{-- No. Bukpot --}}
                    <td class="px-4 py-3.5">
                        <span class="font-mono text-xs text-slate-500">{{ $r->no_bukti_potong ?: '—' }}</span>
                    </td>

                    {{-- Status --}}
                    <td class="px-4 py-3.5 text-center">
                        @if($r->status === 'sudah_disetor')
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-xs font-bold text-emerald-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>Disetor
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-amber-50 border border-amber-200 text-xs font-bold text-amber-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>Belum Setor
                        </span>
                        @endif
                    </td>

                    {{-- Aksi + Inline Konfirm --}}
                    <td class="px-4 py-3.5">
                        <div class="flex items-center justify-center gap-1.5">
                            @if($r->status === 'belum_disetor')
                            <button type="button" @click="open = !open"
                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-emerald-100 border border-emerald-200 text-xs font-bold text-emerald-700 hover:bg-emerald-200 transition-all"
                                    title="Konfirmasi Sudah Disetor">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                                </svg>
                                Setor
                            </button>
                            @endif
                            <form method="POST" action="{{ route('tax.pph23.destroy', $r) }}"
                                  onsubmit="return confirm('Hapus catatan PPh 23 ini?')">
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

                        {{-- Inline Konfirmasi Setoran --}}
                        @if($r->status === 'belum_disetor')
                        <div x-show="open" x-cloak x-transition class="mt-3">
                            <form method="POST" action="{{ route('tax.pph23.konfirm', $r) }}"
                                  class="p-3.5 bg-emerald-50 border border-emerald-200 rounded-2xl space-y-2.5">
                                @csrf
                                <div class="flex items-center gap-1.5 mb-1">
                                    <svg class="h-3.5 w-3.5 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <p class="text-xs font-bold text-emerald-800">Konfirmasi Penyetoran</p>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-semibold text-emerald-700 mb-1">Tanggal Setor *</label>
                                    <input type="date" name="tanggal_setor" value="{{ today()->format('Y-m-d') }}" required
                                           class="w-full px-3 py-2 rounded-xl border border-emerald-300 bg-white text-xs text-slate-700 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-400 outline-none transition-all">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-semibold text-emerald-700 mb-1">NTPN <span class="font-normal text-emerald-500">(opsional)</span></label>
                                    <input type="text" name="ntpn" placeholder="Nomor Transaksi Penerimaan Negara"
                                           class="w-full px-3 py-2 rounded-xl border border-emerald-300 bg-white text-xs text-slate-700 font-mono placeholder-slate-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-400 outline-none transition-all">
                                </div>
                                <div class="flex gap-2 pt-0.5">
                                    <button type="button" @click="open = false"
                                            class="flex-1 px-3 py-2 text-xs font-semibold border border-emerald-300 bg-white text-emerald-700 rounded-xl hover:bg-emerald-50 transition-all">
                                        Batal
                                    </button>
                                    <button type="submit"
                                            class="flex-1 px-3 py-2 text-xs font-bold bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 transition-all">
                                        Konfirmasi Disetor
                                    </button>
                                </div>
                            </form>
                        </div>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-emerald-50 border-t-2 border-emerald-200">
                    <td colspan="3" class="px-4 py-3.5">
                        <span class="text-xs font-bold text-emerald-800 uppercase tracking-wide">Total Periode: {{ \App\Services\Tax\TaxCalculatorService::formatPeriode($periode) }}</span>
                    </td>
                    <td class="px-4 py-3.5 text-right">
                        <span class="text-xs text-slate-500 font-medium">Bruto</span>
                    </td>
                    <td class="px-4 py-3.5 text-right">
                        <span class="text-sm font-black text-slate-700 tabular-nums">Rp {{ number_format($sumBruto, 0, ',', '.') }}</span>
                    </td>
                    <td></td>
                    <td class="px-4 py-3.5 text-right">
                        <span class="text-base font-black text-emerald-700 tabular-nums">Rp {{ number_format($sumPph, 0, ',', '.') }}</span>
                    </td>
                    <td colspan="4"></td>
                </tr>
            </tfoot>
        </table>
    </div>
    <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2">
        <span class="text-xs text-slate-400">
            {{ $records->total() }} catatan &bull; Tarif 2% (ber-NPWP) / 4% (tidak ber-NPWP)
            &bull; Jatuh tempo setoran: tgl 10 bulan berikutnya
        </span>
        {{ $records->links() }}
    </div>
    @endif
</div>

@endsection
