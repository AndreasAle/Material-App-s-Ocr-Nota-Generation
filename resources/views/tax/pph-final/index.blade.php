@extends('layouts.app')

@section('content')

{{-- Breadcrumb --}}
<nav class="flex items-center gap-1.5 text-xs text-slate-400 mb-4">
    <a href="{{ route('dashboard') }}" class="hover:text-slate-600 transition-colors">Dashboard</a>
    <svg class="h-3 w-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <a href="{{ route('tax.dashboard') }}" class="hover:text-slate-600 transition-colors">Perpajakan</a>
    <svg class="h-3 w-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-slate-700 font-semibold">PPh Final Ps. 4(2)</span>
</nav>

{{-- Header --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div class="flex items-center gap-3">
        <div class="flex-shrink-0 w-11 h-11 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-md">
            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0c1.1.128 1.907 1.077 1.907 2.185Z"/>
            </svg>
        </div>
        <div>
            <h1 class="text-xl font-bold text-slate-800 leading-tight">PPh Final Pasal 4 Ayat 2</h1>
            <p class="text-sm text-slate-500">Jasa Konstruksi &mdash; Dasar: <span class="font-medium text-amber-700">PP No. 9 Tahun 2022</span></p>
        </div>
    </div>
    <a href="{{ route('tax.pph-final.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber-600 text-white text-sm font-semibold hover:bg-amber-700 active:bg-amber-800 transition-all shadow-sm hover:shadow-md">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
        </svg>
        Catat Bukti Potong
    </a>
</div>

{{-- Flash --}}

{{-- KPI Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">

    {{-- Tarif Berlaku --}}
    <div class="bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl p-5 text-white shadow-md relative overflow-hidden">
        <div class="absolute top-0 right-0 w-24 h-24 rounded-full bg-white opacity-10 -translate-y-8 translate-x-8"></div>
        <div class="relative">
            <p class="text-xs font-semibold uppercase tracking-wider text-amber-100 mb-1">Tarif PPh Final Berlaku</p>
            <p class="text-5xl font-black tabular-nums leading-none">{{ $tarif }}<span class="text-2xl font-bold">%</span></p>
            <p class="text-xs text-amber-200 mt-2">{{ $setting->getLabelJenisUsaha() }}</p>
            <p class="text-xs text-amber-200">{{ $setting->getLabelKualifikasi() }}</p>
            <a href="{{ route('tax.settings.edit') }}"
               class="inline-flex items-center gap-1 mt-3 text-xs font-semibold text-white/80 hover:text-white transition-colors">
                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Ubah Pengaturan
            </a>
        </div>
    </div>

    {{-- Total PPh Dipotong --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 relative overflow-hidden group hover:shadow-md transition-shadow">
        <div class="absolute -top-5 -right-5 w-20 h-20 rounded-full bg-slate-50 group-hover:scale-110 transition-transform"></div>
        <div class="relative">
            <div class="flex items-center gap-2 mb-3">
                <div class="w-8 h-8 rounded-xl bg-amber-100 flex items-center justify-center">
                    <svg class="h-4 w-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total PPh Dipotong</span>
            </div>
            <p class="text-2xl font-black text-slate-800 tabular-nums">Rp {{ number_format($sumPph, 0, ',', '.') }}</p>
            <p class="text-xs text-slate-400 mt-1">Periode: <span class="font-semibold text-slate-600">{{ \App\Services\Tax\TaxCalculatorService::formatPeriode($periode) }}</span></p>
        </div>
        <div class="mt-3 pt-3 border-t border-slate-100">
            <p class="text-xs text-slate-400">KAP <span class="font-mono font-semibold text-slate-600">411128</span> &bull; KJS <span class="font-mono font-semibold text-slate-600">409</span></p>
        </div>
    </div>

    {{-- Menunggu Bukpot --}}
    <div class="bg-white rounded-2xl border {{ $countBelum > 0 ? 'border-rose-200' : 'border-slate-200' }} shadow-sm p-5 relative overflow-hidden group hover:shadow-md transition-shadow">
        <div class="absolute -top-5 -right-5 w-20 h-20 rounded-full {{ $countBelum > 0 ? 'bg-rose-50' : 'bg-slate-50' }} group-hover:scale-110 transition-transform"></div>
        <div class="relative">
            <div class="flex items-center gap-2 mb-3">
                <div class="w-8 h-8 rounded-xl {{ $countBelum > 0 ? 'bg-rose-100' : 'bg-slate-100' }} flex items-center justify-center">
                    <svg class="h-4 w-4 {{ $countBelum > 0 ? 'text-rose-600' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                    </svg>
                </div>
                <span class="text-xs font-semibold {{ $countBelum > 0 ? 'text-rose-500' : 'text-slate-400' }} uppercase tracking-wider">Menunggu Bukpot</span>
            </div>
            <p class="text-2xl font-black {{ $countBelum > 0 ? 'text-rose-600' : 'text-slate-800' }} tabular-nums">{{ $countBelum }}</p>
            <p class="text-xs text-slate-400 mt-1">Catatan seluruh periode</p>
            @if($countBelum > 0)
            <div class="mt-3 pt-3 border-t border-rose-100">
                <span class="inline-flex items-center gap-1 text-xs font-semibold text-rose-600 animate-pulse">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span> Perlu tindak lanjut
                </span>
            </div>
            @else
            <div class="mt-3 pt-3 border-t border-slate-100">
                <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Semua bukpot diterima
                </span>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Referensi Tarif PP 9/2022 --}}
<div class="bg-white rounded-2xl border border-amber-200 shadow-sm mb-6 overflow-hidden">
    <div class="px-5 py-3.5 bg-amber-50 border-b border-amber-200 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <svg class="h-4 w-4 text-amber-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
            </svg>
            <h3 class="text-xs font-bold text-amber-800 uppercase tracking-wider">Tabel Tarif Referensi — PP No. 9 Tahun 2022</h3>
        </div>
        <span class="text-xs text-amber-600 font-medium bg-amber-100 border border-amber-200 px-2 py-0.5 rounded-full">Berlaku sejak 21 Feb 2022</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-xs">
            <thead>
                <tr class="bg-amber-50/60">
                    <th class="text-left py-2.5 px-5 text-amber-700 font-semibold border-b border-amber-100">Jenis Usaha</th>
                    <th class="text-center py-2.5 px-4 text-amber-700 font-semibold border-b border-amber-100">Kecil (SBU)</th>
                    <th class="text-center py-2.5 px-4 text-amber-700 font-semibold border-b border-amber-100">Menengah / Besar (SBU)</th>
                    <th class="text-center py-2.5 px-4 text-amber-700 font-semibold border-b border-amber-100">Tidak Bersertifikat</th>
                </tr>
            </thead>
            <tbody>
                <tr class="border-b border-slate-100 hover:bg-amber-50/40 transition-colors">
                    <td class="py-3 px-5 font-semibold text-slate-700">Pelaksana Konstruksi</td>
                    <td class="py-3 px-4 text-center">
                        <span class="inline-block px-2.5 py-1 rounded-lg bg-amber-100 text-amber-800 font-black tabular-nums">1,75%</span>
                    </td>
                    <td class="py-3 px-4 text-center">
                        <span class="inline-block px-2.5 py-1 rounded-lg bg-amber-100 text-amber-800 font-black tabular-nums">2,65%</span>
                    </td>
                    <td class="py-3 px-4 text-center">
                        <span class="inline-block px-2.5 py-1 rounded-lg bg-rose-100 text-rose-700 font-black tabular-nums">4,00%</span>
                    </td>
                </tr>
                <tr class="hover:bg-amber-50/40 transition-colors">
                    <td class="py-3 px-5 font-semibold text-slate-700">Perencana / Pengawas / Konsultansi</td>
                    <td class="py-3 px-4 text-center">
                        <span class="inline-block px-2.5 py-1 rounded-lg bg-amber-100 text-amber-800 font-black tabular-nums">4,00%</span>
                    </td>
                    <td class="py-3 px-4 text-center">
                        <span class="inline-block px-2.5 py-1 rounded-lg bg-amber-100 text-amber-800 font-black tabular-nums">4,00%</span>
                    </td>
                    <td class="py-3 px-4 text-center">
                        <span class="inline-block px-2.5 py-1 rounded-lg bg-rose-100 text-rose-700 font-black tabular-nums">6,00%</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="px-5 py-2.5 bg-amber-50/50 border-t border-amber-100 flex flex-wrap gap-4 text-xs text-amber-700">
        <span>KAP: <span class="font-mono font-bold">411128</span></span>
        <span>KJS: <span class="font-mono font-bold">409</span></span>
        <span>Jatuh tempo setoran: <span class="font-semibold">tanggal 10 bulan berikutnya</span></span>
        <span>Sifat: <span class="font-semibold">FINAL (tidak dapat dikreditkan)</span></span>
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
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
                </svg>
                <input type="month" name="periode" value="{{ $periode }}"
                    class="pl-9 pr-3 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-700 focus:ring-2 focus:ring-amber-500 focus:border-amber-400 outline-none transition-all">
            </div>
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1.5">Status Bukpot</label>
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <select name="status"
                    class="pl-9 pr-8 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-700 focus:ring-2 focus:ring-amber-500 focus:border-amber-400 outline-none transition-all appearance-none min-w-[200px]">
                    <option value="">Semua Status</option>
                    <option value="belum_terima_bukpot" {{ $status === 'belum_terima_bukpot' ? 'selected' : '' }}>Belum Terima Bukpot</option>
                    <option value="sudah_terima_bukpot" {{ $status === 'sudah_terima_bukpot' ? 'selected' : '' }}>Sudah Terima Bukpot</option>
                </select>
                <svg class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
            </div>
        </div>
        <button type="submit"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-800 text-white text-sm font-semibold hover:bg-slate-700 transition-all shadow-sm">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803 7.5 7.5 0 0015.803 15.803z"/>
            </svg>
            Terapkan Filter
        </button>
        @if($status || $periode !== now()->format('Y-m'))
        <a href="{{ route('tax.pph-final.index') }}"
           class="inline-flex items-center gap-1.5 px-3 py-2.5 rounded-xl border border-slate-200 bg-white text-sm font-medium text-slate-500 hover:bg-slate-50 transition-all">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            Reset
        </a>
        @endif
    </form>
</div>

{{-- Main Ledger Table --}}
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

    {{-- Table Caption Bar --}}
    <div class="px-5 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h3 class="font-bold text-slate-800">Buku Besar PPh Final Pasal 4(2)</h3>
            <p class="text-xs text-slate-400 mt-0.5">Periode: {{ \App\Services\Tax\TaxCalculatorService::formatPeriode($periode) }} &bull; {{ $records->total() }} catatan</p>
        </div>
        <div class="flex items-center gap-3 text-xs text-slate-400 flex-shrink-0">
            <span class="flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span> Sudah Diterima
            </span>
            <span class="flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-amber-400"></span> Menunggu
            </span>
        </div>
    </div>

    @if($records->isEmpty())
    <div class="py-14 text-center">
        <div class="mx-auto w-16 h-16 rounded-2xl bg-amber-50 flex items-center justify-center mb-4">
            <svg class="w-8 h-8 text-amber-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0c1.1.128 1.907 1.077 1.907 2.185Z"/>
            </svg>
        </div>
        <p class="font-semibold text-slate-600 mb-1">Belum ada catatan PPh Final</p>
        <p class="text-sm text-slate-400 mb-5">Periode {{ \App\Services\Tax\TaxCalculatorService::formatPeriode($periode) }} belum memiliki bukti potong.</p>
        <a href="{{ route('tax.pph-final.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber-600 text-white text-sm font-semibold hover:bg-amber-700 transition-all shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Catat Bukti Potong
        </a>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">No. Bukpot</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Tgl. Bukpot</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Pemotong (Pemberi Kerja)</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Jenis / Kualifikasi</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Nilai Kontrak</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Tarif</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-amber-600 uppercase tracking-wider">PPh Dipotong</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Proyek</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($records as $r)
                <tr class="hover:bg-amber-50/30 transition-colors {{ $r->status === 'belum_terima_bukpot' ? 'bg-amber-50/20' : '' }}">
                    {{-- No. Bukpot --}}
                    <td class="px-5 py-3.5">
                        <a href="{{ route('tax.pph-final.show', $r) }}"
                           class="font-mono text-xs font-semibold text-amber-700 hover:text-amber-900 hover:underline transition-colors">
                            {{ $r->no_bukti_potong }}
                        </a>
                    </td>
                    {{-- Tanggal --}}
                    <td class="px-4 py-3.5">
                        <span class="text-xs text-slate-600 font-medium tabular-nums">{{ $r->tanggal_bukti_potong->format('d/m/Y') }}</span>
                    </td>
                    {{-- Pemotong --}}
                    <td class="px-4 py-3.5">
                        <p class="font-semibold text-slate-800 text-sm">{{ $r->nama_pemotong }}</p>
                        @if($r->npwp_pemotong)
                        <p class="font-mono text-xs text-slate-400 mt-0.5">{{ $r->npwp_pemotong }}</p>
                        @endif
                    </td>
                    {{-- Jenis / Kualifikasi --}}
                    <td class="px-4 py-3.5">
                        <p class="text-xs font-medium text-slate-700">{{ $r->getLabelJenisUsaha() }}</p>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $r->getLabelKualifikasi() }}</p>
                    </td>
                    {{-- Nilai Kontrak --}}
                    <td class="px-4 py-3.5 text-right">
                        <span class="text-sm font-semibold text-slate-700 tabular-nums">Rp {{ number_format($r->nilai_kontrak, 0, ',', '.') }}</span>
                    </td>
                    {{-- Tarif --}}
                    <td class="px-4 py-3.5 text-center">
                        <span class="inline-block px-2.5 py-1 text-xs font-black rounded-lg bg-amber-100 text-amber-800 tabular-nums">{{ $r->tarif }}%</span>
                    </td>
                    {{-- PPh Dipotong --}}
                    <td class="px-4 py-3.5 text-right">
                        <span class="text-sm font-black text-amber-700 tabular-nums">Rp {{ number_format($r->pph_dipotong, 0, ',', '.') }}</span>
                    </td>
                    {{-- Status --}}
                    <td class="px-4 py-3.5 text-center">
                        @if($r->status === 'sudah_terima_bukpot')
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-xs font-semibold text-emerald-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>Diterima
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-amber-50 border border-amber-200 text-xs font-semibold text-amber-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>Menunggu
                        </span>
                        @endif
                    </td>
                    {{-- Proyek --}}
                    <td class="px-4 py-3.5">
                        <span class="text-xs text-slate-500">{{ $r->project?->name ?? '—' }}</span>
                    </td>
                    {{-- Aksi --}}
                    <td class="px-4 py-3.5">
                        <div class="flex items-center justify-center gap-1.5">
                            <a href="{{ route('tax.pph-final.show', $r) }}"
                               class="w-7 h-7 rounded-lg border border-slate-200 bg-white flex items-center justify-center text-slate-500 hover:text-amber-600 hover:border-amber-300 hover:bg-amber-50 transition-all"
                               title="Lihat Detail">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </a>
                            <form method="POST" action="{{ route('tax.pph-final.destroy', $r) }}"
                                  onsubmit="return confirm('Hapus catatan bukti potong ini?')">
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
                <tr class="bg-amber-50 border-t-2 border-amber-200">
                    <td colspan="6" class="px-5 py-3.5">
                        <span class="text-xs font-bold text-amber-800 uppercase tracking-wide">Total Periode: {{ \App\Services\Tax\TaxCalculatorService::formatPeriode($periode) }}</span>
                    </td>
                    <td class="px-4 py-3.5 text-right">
                        <span class="text-base font-black text-amber-800 tabular-nums">Rp {{ number_format($sumPph, 0, ',', '.') }}</span>
                    </td>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
        </table>
    </div>
    <div class="px-5 py-3 bg-slate-50 border-t border-slate-100">
        {{ $records->links() }}
    </div>
    @endif
</div>

@endsection
