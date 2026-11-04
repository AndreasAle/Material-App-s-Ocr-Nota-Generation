@extends('layouts.app')

@section('content')

{{-- Breadcrumb --}}
<nav class="flex items-center gap-1.5 text-xs text-slate-400 mb-4">
    <a href="{{ route('dashboard') }}" class="hover:text-slate-600 transition-colors">Dashboard</a>
    <svg class="h-3 w-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-slate-700 font-semibold">Dashboard Perpajakan</span>
</nav>

{{-- Professional Header --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5">
    <div class="flex items-center gap-3">
        <div class="flex-shrink-0 w-11 h-11 rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-700 flex items-center justify-center shadow-md">
            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0012 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 01-2.031.352 5.988 5.988 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L18.75 4.97zm-16.5.52c.99-.203 1.99-.377 3-.52m0 0l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.989 5.989 0 01-2.031.352 5.989 5.989 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L5.25 4.97z"/>
            </svg>
        </div>
        <div>
            <h1 class="text-xl font-bold text-slate-800 leading-tight">Dashboard Perpajakan</h1>
            <p class="text-sm text-slate-500">
                Periode: <span class="font-semibold text-slate-700">{{ \App\Services\Tax\TaxCalculatorService::formatPeriode($periode) }}</span>
                &bull; Tahun Fiskal <span class="font-semibold text-slate-700">{{ $tahun }}</span>
            </p>
        </div>
    </div>
    <div class="flex items-center gap-2 flex-wrap">
        <a href="{{ route('tax.settings.edit') }}"
           class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-slate-200 bg-white text-sm font-medium text-slate-600 hover:bg-slate-50 hover:border-slate-300 transition-all shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 010 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 010-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Pengaturan
        </a>
        <a href="{{ route('tax.setoran.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition-all shadow-sm hover:shadow-md">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Catat Setoran
        </a>
    </div>
</div>

{{-- Tax Module Navigation Tabs --}}
<div class="flex flex-wrap gap-1.5 mb-6 bg-slate-100 rounded-2xl p-1.5">
    <a href="{{ route('tax.dashboard') }}"
       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-indigo-600 text-white text-xs font-semibold shadow-sm">
        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zm9.75-9.75c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v16.5c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V3.375zM6.75 10.125c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125H7.875a1.125 1.125 0 01-1.125-1.125v-9.75z"/></svg>
        Ikhtisar
    </a>
    <a href="{{ route('tax.ppn.index') }}"
       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-slate-600 text-xs font-semibold hover:bg-white hover:text-indigo-700 transition-all">
        PPN
        @if($ppnKurang > 0)
        <span class="flex-shrink-0 w-1.5 h-1.5 rounded-full bg-rose-400"></span>
        @endif
    </a>
    <a href="{{ route('tax.pph-final.index') }}"
       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-slate-600 text-xs font-semibold hover:bg-white hover:text-amber-700 transition-all">
        PPh Final 4(2)
        @if($pphFinalMenungguBukpot > 0)
        <span class="flex-shrink-0 w-1.5 h-1.5 rounded-full bg-amber-400"></span>
        @endif
    </a>
    <a href="{{ route('tax.pph23.index') }}"
       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-slate-600 text-xs font-semibold hover:bg-white hover:text-emerald-700 transition-all">
        PPh Pasal 23
        @if($pph23BelumSetor > 0)
        <span class="flex-shrink-0 w-1.5 h-1.5 rounded-full bg-rose-400"></span>
        @endif
    </a>
    <a href="{{ route('tax.setoran.index') }}"
       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-slate-600 text-xs font-semibold hover:bg-white hover:text-violet-700 transition-all">
        Setoran Pajak
        @if($overdueSetoran->count() > 0)
        <span class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-rose-500 text-white text-xs font-black leading-none">{{ $overdueSetoran->count() }}</span>
        @endif
    </a>
    {{-- Divider --}}
    <span class="w-px h-6 bg-slate-300 self-center mx-1"></span>
    {{-- Coretax DJP shortcuts --}}
    <a href="{{ route('tax.bppu.index') }}"
       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-slate-600 text-xs font-semibold hover:bg-white hover:text-blue-700 transition-all">
        BPPU
    </a>
    <a href="{{ route('tax.dok-lain.index') }}"
       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-slate-600 text-xs font-semibold hover:bg-white hover:text-teal-700 transition-all">
        Dok Lain PPN
    </a>
    <a href="{{ route('tax.bp21.index') }}"
       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-slate-600 text-xs font-semibold hover:bg-white hover:text-purple-700 transition-all">
        BP21
        @if($bp21BelumSetor > 0)
        <span class="flex-shrink-0 w-1.5 h-1.5 rounded-full bg-purple-400"></span>
        @endif
    </a>
</div>

{{-- Alert: NPWP Belum Lengkap --}}
@if(!$setting->npwp)
<div class="flex items-start gap-3 p-4 rounded-2xl bg-amber-50 border border-amber-300 mb-5">
    <div class="flex-shrink-0 w-8 h-8 bg-amber-100 rounded-xl flex items-center justify-center">
        <svg class="w-4 h-4 text-amber-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
        </svg>
    </div>
    <div class="flex-1 min-w-0">
        <p class="text-sm font-bold text-amber-800">Data Pajak Perusahaan Belum Lengkap</p>
        <p class="text-xs text-amber-700 mt-0.5">NPWP dan profil pajak belum diisi — hasil kalkulasi mungkin tidak akurat.</p>
    </div>
    <a href="{{ route('tax.settings.edit') }}"
       class="flex-shrink-0 inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-amber-600 text-white text-xs font-semibold hover:bg-amber-700 transition-colors">
        Lengkapi Sekarang
    </a>
</div>
@endif

{{-- Alert: Overdue Setoran --}}
@if($overdueSetoran->count())
<div class="flex items-start gap-3 p-4 rounded-2xl bg-rose-50 border border-rose-300 mb-5">
    <div class="flex-shrink-0 w-8 h-8 bg-rose-100 rounded-xl flex items-center justify-center">
        <svg class="w-4 h-4 text-rose-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
        </svg>
    </div>
    <div class="flex-1 min-w-0">
        <p class="text-sm font-bold text-rose-800">{{ $overdueSetoran->count() }} Setoran Pajak Melewati Jatuh Tempo!</p>
        <p class="text-xs text-rose-700 mt-0.5">Segera setor untuk menghindari sanksi administrasi <strong>2% per bulan</strong> dari jumlah pajak terutang (Pasal 9 ayat 2a UU KUP).</p>
    </div>
    <a href="{{ route('tax.setoran.index', ['status' => 'belum_bayar']) }}"
       class="flex-shrink-0 inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-rose-600 text-white text-xs font-semibold hover:bg-rose-700 transition-colors">
        Lihat Daftar
    </a>
</div>
@endif

{{-- KPI Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">

    {{-- PPN Kurang Bayar --}}
    <div class="bg-white rounded-2xl border {{ $ppnKurang > 0 ? 'border-rose-200' : 'border-slate-200' }} shadow-sm p-5 relative overflow-hidden group hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
        <div class="absolute -top-5 -right-5 w-20 h-20 rounded-full {{ $ppnKurang > 0 ? 'bg-rose-50' : 'bg-slate-50' }} group-hover:scale-125 transition-transform duration-300"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-3">
                <div class="w-8 h-8 rounded-xl {{ $ppnKurang > 0 ? 'bg-rose-100' : 'bg-slate-100' }} flex items-center justify-center">
                    <svg class="w-4 h-4 {{ $ppnKurang > 0 ? 'text-rose-600' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6L9 12.75l4.286-4.286a11.948 11.948 0 014.306 6.43l.776 2.898m0 0l3.182-5.511m-3.182 5.51l-5.511-3.181"/>
                    </svg>
                </div>
                <a href="{{ route('tax.ppn.index') }}" class="text-xs {{ $ppnKurang > 0 ? 'text-rose-600 hover:text-rose-800' : 'text-indigo-600 hover:text-indigo-800' }} font-semibold transition-colors">Buku →</a>
            </div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">PPN Kurang Bayar</p>
            <p class="text-xl font-black {{ $ppnKurang > 0 ? 'text-rose-600' : 'text-slate-500' }} tabular-nums leading-none">
                Rp {{ number_format($ppnKurang, 0, ',', '.') }}
            </p>
            @if($ppnLebih > 0)
            <p class="text-xs text-emerald-600 font-semibold mt-1.5">LB: Rp {{ number_format($ppnLebih, 0, ',', '.') }}</p>
            @else
            <p class="text-xs text-slate-400 mt-1.5">{{ \App\Services\Tax\TaxCalculatorService::formatPeriode($periode) }}</p>
            @endif
        </div>
    </div>

    {{-- PPh Final --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 relative overflow-hidden group hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
        <div class="absolute -top-5 -right-5 w-20 h-20 rounded-full bg-amber-50 group-hover:scale-125 transition-transform duration-300"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-3">
                <div class="w-8 h-8 rounded-xl bg-amber-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0c1.1.128 1.907 1.077 1.907 2.185Z"/>
                    </svg>
                </div>
                <a href="{{ route('tax.pph-final.index') }}" class="text-xs text-amber-600 hover:text-amber-800 font-semibold transition-colors">Buku →</a>
            </div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">PPh Final 4(2)</p>
            <p class="text-xl font-black text-amber-700 tabular-nums leading-none">Rp {{ number_format($pphFinalPeriode, 0, ',', '.') }}</p>
            @if($pphFinalMenungguBukpot > 0)
            <p class="text-xs text-amber-600 font-semibold mt-1.5 flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse flex-shrink-0"></span>
                {{ $pphFinalMenungguBukpot }} menunggu bukpot
            </p>
            @else
            <p class="text-xs text-slate-400 mt-1.5">Dipotong klien</p>
            @endif
        </div>
    </div>

    {{-- PPh 23 --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 relative overflow-hidden group hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
        <div class="absolute -top-5 -right-5 w-20 h-20 rounded-full bg-indigo-50 group-hover:scale-125 transition-transform duration-300"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-3">
                <div class="w-8 h-8 rounded-xl bg-indigo-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75"/>
                    </svg>
                </div>
                <a href="{{ route('tax.pph23.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold transition-colors">Buku →</a>
            </div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">PPh Pasal 23</p>
            <p class="text-xl font-black text-indigo-700 tabular-nums leading-none">Rp {{ number_format($pph23Periode, 0, ',', '.') }}</p>
            @if($pph23BelumSetor > 0)
            <p class="text-xs text-rose-600 font-semibold mt-1.5 flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-rose-400 animate-pulse flex-shrink-0"></span>
                {{ $pph23BelumSetor }} belum disetor
            </p>
            @else
            <p class="text-xs text-slate-400 mt-1.5">Dipotong / disetor</p>
            @endif
        </div>
    </div>

    {{-- Total Setor Tahun Ini --}}
    <div class="bg-white rounded-2xl border border-emerald-200 shadow-sm p-5 relative overflow-hidden group hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
        <div class="absolute -top-5 -right-5 w-20 h-20 rounded-full bg-emerald-50 group-hover:scale-125 transition-transform duration-300"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-3">
                <div class="w-8 h-8 rounded-xl bg-emerald-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <a href="{{ route('tax.setoran.index') }}" class="text-xs text-emerald-600 hover:text-emerald-800 font-semibold transition-colors">Buku →</a>
            </div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Total Setor {{ $tahun }}</p>
            <p class="text-xl font-black text-emerald-700 tabular-nums leading-none">Rp {{ number_format($sudahBayar, 0, ',', '.') }}</p>
            <p class="text-xs text-slate-400 mt-1.5">Seluruh jenis pajak</p>
        </div>
    </div>
</div>

{{-- Coretax DJP Summary Row --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5">
    {{-- BP21 PPh 21 --}}
    <div class="bg-white rounded-2xl border border-purple-200 shadow-sm p-4 flex items-center gap-4 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
        <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">BP21 — PPh Pasal 21</p>
            <p class="text-lg font-black text-purple-700 tabular-nums">Rp {{ number_format($bp21Periode, 0, ',', '.') }}</p>
            @if($bp21BelumSetor > 0)
            <p class="text-xs text-rose-600 font-semibold flex items-center gap-1 mt-0.5">
                <span class="w-1.5 h-1.5 rounded-full bg-rose-400 animate-pulse flex-shrink-0"></span>
                {{ $bp21BelumSetor }} belum disetor
            </p>
            @else
            <p class="text-xs text-slate-400 mt-0.5">Periode ini</p>
            @endif
        </div>
        <a href="{{ route('tax.bp21.index', ['masa' => $periode]) }}" class="flex-shrink-0 text-xs text-purple-600 hover:text-purple-800 font-semibold">Buku →</a>
    </div>

    {{-- BPPU Summary --}}
    <div class="bg-white rounded-2xl border border-blue-200 shadow-sm p-4 flex items-center gap-4 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
        <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">BPPU — PPh Unifikasi</p>
            @php
                $bppuFinalPeriode = \App\Models\TaxPphFinalRecord::where('periode', $periode)->sum('pph_dipotong');
                $bppuPph23Periode = \App\Models\TaxPph23Record::where('periode', $periode)->sum('pph_dipotong');
                $bppuTotal = $bppuFinalPeriode + $bppuPph23Periode;
            @endphp
            <p class="text-lg font-black text-blue-700 tabular-nums">Rp {{ number_format($bppuTotal, 0, ',', '.') }}</p>
            <p class="text-xs text-slate-400 mt-0.5">Final + PPh 23 periode ini</p>
        </div>
        <a href="{{ route('tax.bppu.index', ['masa' => $periode]) }}" class="flex-shrink-0 text-xs text-blue-600 hover:text-blue-800 font-semibold">BPPU →</a>
    </div>

    {{-- Coretax CTA --}}
    <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-2xl shadow-sm p-4 flex flex-col justify-between hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
        <div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Coretax DJP</p>
            <p class="text-sm font-semibold text-white">Import / Export siap upload</p>
            <p class="text-xs text-slate-400 mt-1">Format DJP v.3 & v.4 — e-Bupot Unifikasi + e-Bupot 21</p>
        </div>
        <div class="flex gap-2 mt-3">
            <a href="{{ route('tax.bppu.export') }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-blue-500/20 text-blue-300 text-xs font-semibold hover:bg-blue-500/30 transition-colors">
                ↓ BPPU
            </a>
            <a href="{{ route('tax.bp21.export') }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-purple-500/20 text-purple-300 text-xs font-semibold hover:bg-purple-500/30 transition-colors">
                ↓ BP21
            </a>
            <a href="{{ route('tax.dok-lain.index') }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-teal-500/20 text-teal-300 text-xs font-semibold hover:bg-teal-500/30 transition-colors">
                Dok PPN
            </a>
        </div>
    </div>
</div>

{{-- Chart + Rekapitulasi --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5">

    {{-- Bar Chart --}}
    <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 mb-5">
            <div>
                <h3 class="font-bold text-slate-800 text-sm">Tren Pemotongan / Pemungutan Pajak</h3>
                <p class="text-xs text-slate-400 mt-0.5">6 bulan terakhir — per jenis pajak</p>
            </div>
            <div class="flex items-center gap-3 text-xs text-slate-500 flex-shrink-0 flex-wrap">
                <span class="flex items-center gap-1.5"><span class="inline-block w-3 h-2 rounded-sm bg-indigo-500"></span>PPN</span>
                <span class="flex items-center gap-1.5"><span class="inline-block w-3 h-2 rounded-sm bg-amber-500"></span>PPh Final</span>
                <span class="flex items-center gap-1.5"><span class="inline-block w-3 h-2 rounded-sm bg-emerald-500"></span>PPh 23</span>
                <span class="flex items-center gap-1.5"><span class="inline-block w-3 h-2 rounded-sm bg-purple-500"></span>BP21</span>
            </div>
        </div>
        <canvas id="taxChart" height="100"></canvas>
    </div>

    {{-- Rekapitulasi Ledger --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-200">
            <h3 class="font-bold text-slate-800 text-sm">Rekapitulasi Pajak {{ $tahun }}</h3>
            <p class="text-xs text-slate-400 mt-0.5">Akumulasi seluruh transaksi pajak tahun fiskal</p>
        </div>

        {{-- PPN --}}
        <div class="px-5 py-3.5 border-b border-slate-100">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2.5">Pajak Pertambahan Nilai (PPN)</p>
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-slate-500">PPN Keluaran</span>
                    <span class="text-xs font-semibold text-slate-700 tabular-nums">Rp {{ number_format($rekap['ppn_keluaran'], 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-slate-500">PPN Masukan (Kredit)</span>
                    <span class="text-xs font-semibold text-emerald-600 tabular-nums">(Rp {{ number_format($rekap['ppn_masukan'], 0, ',', '.') }})</span>
                </div>
                <div class="flex items-center justify-between pt-2 border-t border-dashed border-slate-200">
                    <span class="text-xs font-bold text-slate-600">Selisih PPN</span>
                    <span class="text-xs font-black tabular-nums {{ $rekap['ppn_kurang_lebih'] >= 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                        Rp {{ number_format(abs($rekap['ppn_kurang_lebih']), 0, ',', '.') }}
                        <span class="font-semibold text-xs">({{ $rekap['ppn_kurang_lebih'] >= 0 ? 'KB' : 'LB' }})</span>
                    </span>
                </div>
            </div>
        </div>

        {{-- PPh --}}
        <div class="px-5 py-3.5 border-b border-slate-100">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2.5">Pajak Penghasilan (PPh)</p>
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-slate-500">PPh Final Pasal 4(2)</span>
                    <span class="text-xs font-semibold text-amber-700 tabular-nums">Rp {{ number_format($rekap['pph_final'], 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-slate-500">PPh Pasal 23</span>
                    <span class="text-xs font-semibold text-indigo-700 tabular-nums">Rp {{ number_format($rekap['pph23'], 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-slate-500">PPh Pasal 21 (BP21)</span>
                    <span class="text-xs font-semibold text-purple-700 tabular-nums">Rp {{ number_format($rekap['bp21'], 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between pt-2 border-t border-dashed border-slate-200">
                    <span class="text-xs font-bold text-slate-600">Total PPh</span>
                    <span class="text-xs font-black text-slate-700 tabular-nums">Rp {{ number_format($rekap['pph_final'] + $rekap['pph23'] + $rekap['bp21'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        {{-- Profil Pajak Perusahaan --}}
        <div class="px-5 py-3.5 bg-slate-50/60">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2.5">Profil Pajak Perusahaan</p>
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-slate-400">Status PKP</span>
                    <span class="inline-flex items-center gap-1 text-xs font-semibold {{ $setting->is_pkp ? 'text-emerald-600' : 'text-slate-500' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $setting->is_pkp ? 'bg-emerald-400' : 'bg-slate-300' }}"></span>
                        {{ $setting->is_pkp ? 'PKP' : 'Non-PKP' }}
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-slate-400">Jenis Usaha</span>
                    <span class="text-xs font-medium text-slate-600">{{ $setting->getLabelJenisUsaha() }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-slate-400">Kualifikasi SBU</span>
                    <span class="text-xs font-medium text-slate-600">{{ $setting->getLabelKualifikasi() }}</span>
                </div>
                @if($setting->npwp)
                <div class="flex items-center justify-between">
                    <span class="text-xs text-slate-400">NPWP</span>
                    <span class="text-xs font-mono font-semibold text-slate-700">{{ $setting->npwp }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Jatuh Tempo + Modul --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

    {{-- Jadwal Jatuh Tempo --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-slate-800 text-sm">Jadwal Jatuh Tempo Setoran</h3>
                <p class="text-xs text-slate-400 mt-0.5">Overdue &amp; 14 hari ke depan</p>
            </div>
            <a href="{{ route('tax.setoran.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold transition-colors">
                Semua →
            </a>
        </div>

        @if($upcomingSetoran->isEmpty() && $overdueSetoran->isEmpty())
        <div class="py-12 text-center">
            <div class="mx-auto w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center mb-3">
                <svg class="w-6 h-6 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-sm font-semibold text-slate-600">Tidak ada jatuh tempo</p>
            <p class="text-xs text-slate-400 mt-1">Semua kewajiban setoran sudah terpenuhi.</p>
        </div>
        @else
        <div class="p-4 space-y-2.5">
            @foreach($overdueSetoran as $s)
            <div class="flex items-center gap-3 p-3.5 rounded-xl border border-rose-200 bg-rose-50">
                <div class="flex-shrink-0 w-9 h-9 rounded-xl bg-rose-100 flex items-center justify-center">
                    <svg class="h-4 w-4 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-bold text-rose-800">{{ $s->getLabelJenisPajak() }}</p>
                    <p class="text-xs text-rose-600 mt-0.5">
                        JT: {{ $s->jatuh_tempo->translatedFormat('d F Y') }}
                        &bull; <span class="font-bold uppercase">Terlambat</span>
                    </p>
                </div>
                <span class="flex-shrink-0 text-sm font-black text-rose-700 tabular-nums">Rp {{ number_format($s->jumlah, 0, ',', '.') }}</span>
            </div>
            @endforeach

            @foreach($upcomingSetoran as $s)
            @php $days = now()->diffInDays($s->jatuh_tempo, false); @endphp
            <div class="flex items-center gap-3 p-3.5 rounded-xl border {{ $days <= 3 ? 'border-rose-200 bg-rose-50/60' : 'border-amber-200 bg-amber-50' }}">
                <div class="flex-shrink-0 w-9 h-9 rounded-xl {{ $days <= 3 ? 'bg-rose-100' : 'bg-amber-100' }} flex items-center justify-center">
                    <svg class="h-4 w-4 {{ $days <= 3 ? 'text-rose-600' : 'text-amber-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-bold {{ $days <= 3 ? 'text-rose-800' : 'text-amber-800' }}">{{ $s->getLabelJenisPajak() }}</p>
                    <p class="text-xs {{ $days <= 3 ? 'text-rose-600' : 'text-amber-600' }} mt-0.5">
                        JT: {{ $s->jatuh_tempo->translatedFormat('d F Y') }}
                        &bull; <span class="font-bold">{{ $days }} hari lagi</span>
                    </p>
                </div>
                <span class="flex-shrink-0 text-sm font-black {{ $days <= 3 ? 'text-rose-700' : 'text-amber-700' }} tabular-nums">
                    Rp {{ number_format($s->jumlah, 0, ',', '.') }}
                </span>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Modul Perpajakan --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-200">
            <h3 class="font-bold text-slate-800 text-sm">Modul Perpajakan</h3>
            <p class="text-xs text-slate-400 mt-0.5">Akses cepat ke seluruh sub-modul pajak</p>
        </div>
        <div class="p-4 grid grid-cols-2 gap-2.5">

            <a href="{{ route('tax.ppn.masukan.create') }}"
               class="flex items-center gap-3 p-3.5 rounded-xl border border-slate-200 hover:border-indigo-300 hover:bg-indigo-50 transition-all group">
                <div class="flex-shrink-0 w-9 h-9 rounded-xl bg-indigo-100 group-hover:bg-indigo-200 flex items-center justify-center transition-colors">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-bold text-slate-700 group-hover:text-indigo-700 transition-colors leading-snug">Faktur Masukan</p>
                    <p class="text-xs text-slate-400">Input PPN Masukan</p>
                </div>
            </a>

            <a href="{{ route('tax.ppn.keluaran.create') }}"
               class="flex items-center gap-3 p-3.5 rounded-xl border border-slate-200 hover:border-indigo-300 hover:bg-indigo-50 transition-all group">
                <div class="flex-shrink-0 w-9 h-9 rounded-xl bg-indigo-100 group-hover:bg-indigo-200 flex items-center justify-center transition-colors">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-bold text-slate-700 group-hover:text-indigo-700 transition-colors leading-snug">Faktur Keluaran</p>
                    <p class="text-xs text-slate-400">Input PPN Keluaran</p>
                </div>
            </a>

            <a href="{{ route('tax.pph-final.create') }}"
               class="flex items-center gap-3 p-3.5 rounded-xl border border-slate-200 hover:border-amber-300 hover:bg-amber-50 transition-all group">
                <div class="flex-shrink-0 w-9 h-9 rounded-xl bg-amber-100 group-hover:bg-amber-200 flex items-center justify-center transition-colors">
                    <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0c1.1.128 1.907 1.077 1.907 2.185z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-bold text-slate-700 group-hover:text-amber-700 transition-colors leading-snug">Bukpot PPh Final</p>
                    <p class="text-xs text-slate-400">Pasal 4 Ayat (2)</p>
                </div>
            </a>

            <a href="{{ route('tax.pph23.create') }}"
               class="flex items-center gap-3 p-3.5 rounded-xl border border-slate-200 hover:border-emerald-300 hover:bg-emerald-50 transition-all group">
                <div class="flex-shrink-0 w-9 h-9 rounded-xl bg-emerald-100 group-hover:bg-emerald-200 flex items-center justify-center transition-colors">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-bold text-slate-700 group-hover:text-emerald-700 transition-colors leading-snug">Bukpot PPh 23</p>
                    <p class="text-xs text-slate-400">Pasal 23</p>
                </div>
            </a>

            <a href="{{ route('tax.setoran.create') }}"
               class="flex items-center gap-3 p-3.5 rounded-xl border border-slate-200 hover:border-violet-300 hover:bg-violet-50 transition-all group">
                <div class="flex-shrink-0 w-9 h-9 rounded-xl bg-violet-100 group-hover:bg-violet-200 flex items-center justify-center transition-colors">
                    <svg class="w-4 h-4 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-bold text-slate-700 group-hover:text-violet-700 transition-colors leading-snug">Catat Setoran</p>
                    <p class="text-xs text-slate-400">SSP / e-Billing</p>
                </div>
            </a>

            <a href="{{ route('tax.settings.edit') }}"
               class="flex items-center gap-3 p-3.5 rounded-xl border border-slate-200 hover:border-slate-300 hover:bg-slate-50 transition-all group">
                <div class="flex-shrink-0 w-9 h-9 rounded-xl bg-slate-100 group-hover:bg-slate-200 flex items-center justify-center transition-colors">
                    <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 010 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 010-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-bold text-slate-600 group-hover:text-slate-800 transition-colors leading-snug">Pengaturan Pajak</p>
                    <p class="text-xs text-slate-400">Profil &amp; tarif berlaku</p>
                </div>
            </a>

        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    const labels = @json($chartLabels);
    const ppn    = @json($chartPpn);
    const pphFin = @json($chartPphFin);
    const pph23  = @json($chartPph23);
    const bp21   = @json($chartBp21);

    new Chart(document.getElementById('taxChart'), {
        type: 'bar',
        data: {
            labels,
            datasets: [
                { label: 'PPN',       data: ppn,    backgroundColor: 'rgba(99,102,241,.75)',  hoverBackgroundColor: 'rgba(79,70,229,.9)',  borderRadius: 5, borderSkipped: false },
                { label: 'PPh Final', data: pphFin, backgroundColor: 'rgba(245,158,11,.75)', hoverBackgroundColor: 'rgba(217,119,6,.9)', borderRadius: 5, borderSkipped: false },
                { label: 'PPh 23',    data: pph23,  backgroundColor: 'rgba(16,185,129,.75)', hoverBackgroundColor: 'rgba(5,150,105,.9)',  borderRadius: 5, borderSkipped: false },
                { label: 'BP21',      data: bp21,   backgroundColor: 'rgba(168,85,247,.75)', hoverBackgroundColor: 'rgba(139,92,246,.9)', borderRadius: 5, borderSkipped: false },
            ],
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
                    padding: 12,
                    cornerRadius: 10,
                    borderColor: 'rgba(148,163,184,0.15)',
                    borderWidth: 1,
                    callbacks: {
                        label: ctx => '  ' + ctx.dataset.label + ': Rp ' + ctx.parsed.y.toLocaleString('id-ID'),
                    },
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(148,163,184,0.10)', borderDash: [4, 4] },
                    ticks: {
                        color: '#94a3b8',
                        font: { size: 10 },
                        callback: v => {
                            if (v >= 1000000) return 'Rp ' + (v / 1000000).toFixed(1) + 'jt';
                            if (v >= 1000)    return 'Rp ' + (v / 1000).toFixed(0) + 'rb';
                            return 'Rp ' + v;
                        },
                    },
                },
                x: {
                    grid: { display: false },
                    ticks: { color: '#94a3b8', font: { size: 10 } },
                },
            },
        },
    });
})();
</script>
@endpush
@endsection
