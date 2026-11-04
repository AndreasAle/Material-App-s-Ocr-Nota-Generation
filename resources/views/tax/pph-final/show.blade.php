@extends('layouts.app')

@section('content')

{{-- Breadcrumb --}}
<nav class="flex items-center gap-1.5 text-xs text-slate-400 mb-4">
    <a href="{{ route('dashboard') }}" class="hover:text-slate-600 transition-colors">Dashboard</a>
    <svg class="h-3 w-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <a href="{{ route('tax.dashboard') }}" class="hover:text-slate-600 transition-colors">Perpajakan</a>
    <svg class="h-3 w-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <a href="{{ route('tax.pph-final.index') }}" class="hover:text-slate-600 transition-colors">PPh Final Ps. 4(2)</a>
    <svg class="h-3 w-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-slate-700 font-semibold font-mono">{{ $record->no_bukti_potong }}</span>
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
            <div class="flex items-center gap-2 flex-wrap">
                <h1 class="text-xl font-bold text-slate-800 leading-tight">Detail Bukti Potong PPh Final</h1>
                @if($record->status === 'sudah_terima_bukpot')
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-xs font-bold text-emerald-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Sudah Diterima
                </span>
                @else
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-amber-50 border border-amber-200 text-xs font-bold text-amber-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span> Menunggu Bukpot
                </span>
                @endif
            </div>
            <p class="text-sm text-slate-500 mt-0.5">
                No. <span class="font-mono font-semibold text-slate-700">{{ $record->no_bukti_potong }}</span>
                &mdash; {{ $record->tanggal_bukti_potong->translatedFormat('d F Y') }}
            </p>
        </div>
    </div>
    <div class="flex items-center gap-2 flex-wrap">
        @if($record->status === 'belum_terima_bukpot')
        <form method="POST" action="{{ route('tax.pph-final.konfirm', $record) }}">
            @csrf
            <button type="submit"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700 transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                </svg>
                Konfirmasi Diterima
            </button>
        </form>
        @endif
        <a href="{{ route('tax.pph-final.index') }}"
           class="inline-flex items-center gap-2 px-3 py-2.5 rounded-xl border border-slate-200 bg-white text-sm font-medium text-slate-600 hover:bg-slate-50 hover:border-slate-300 transition-all shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
            </svg>
            Kembali
        </a>
    </div>
</div>

{{-- Flash --}}

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- === KOLOM UTAMA (2/3) === --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Dokumen Bukti Potong --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            {{-- Document Header --}}
            <div class="px-5 py-4 bg-gradient-to-r from-amber-50 to-orange-50 border-b border-amber-200">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <div>
                        <p class="text-xs font-bold text-amber-700 uppercase tracking-wider mb-0.5">Bukti Pemotongan PPh Pasal 4 Ayat (2)</p>
                        <p class="text-xs text-amber-600">Jasa Konstruksi — PP No. 9 Tahun 2022</p>
                    </div>
                    <div class="text-right">
                        <p class="font-mono text-xs text-amber-600 mb-0.5">No. Bukpot</p>
                        <p class="font-mono font-black text-amber-800 text-sm">{{ $record->no_bukti_potong }}</p>
                    </div>
                </div>
            </div>

            {{-- Data Grid --}}
            <div class="p-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-5">
                    <div class="space-y-1">
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Tanggal Bukti Potong</p>
                        <p class="text-sm font-bold text-slate-800">{{ $record->tanggal_bukti_potong->translatedFormat('d F Y') }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Periode Pajak</p>
                        <p class="text-sm font-bold text-slate-800">{{ \App\Services\Tax\TaxCalculatorService::formatPeriode($record->periode) }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Nama Pemotong (Pemberi Kerja)</p>
                        <p class="text-sm font-bold text-slate-800">{{ $record->nama_pemotong }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">NPWP Pemotong</p>
                        <p class="text-sm font-mono font-semibold text-slate-700">{{ $record->npwp_pemotong ?: '—' }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Jenis Usaha</p>
                        <p class="text-sm font-semibold text-slate-700">{{ $record->getLabelJenisUsaha() }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Kualifikasi SBU</p>
                        <p class="text-sm font-semibold text-slate-700">{{ $record->getLabelKualifikasi() }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Proyek Terkait</p>
                        <p class="text-sm font-semibold text-slate-700">{{ $record->project?->name ?? '—' }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Status</p>
                        @if($record->status === 'sudah_terima_bukpot')
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-xs font-bold text-emerald-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Sudah Diterima
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-amber-50 border border-amber-200 text-xs font-bold text-amber-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span> Menunggu Bukpot
                        </span>
                        @endif
                    </div>
                    @if($record->keterangan)
                    <div class="sm:col-span-2 space-y-1">
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Keterangan</p>
                        <p class="text-sm text-slate-600 bg-slate-50 rounded-xl px-3 py-2 border border-slate-100">{{ $record->keterangan }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Kalkulasi Pajak — Accounting Ledger Style --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-200">
                <h3 class="text-sm font-bold text-slate-700">Kalkulasi Pemotongan Pajak</h3>
                <p class="text-xs text-slate-400 mt-0.5">PPh Final bersifat final, tidak dapat dikreditkan dalam SPT Tahunan</p>
            </div>

            {{-- Ledger rows --}}
            <div class="divide-y divide-slate-100">
                <div class="flex items-center justify-between px-5 py-4">
                    <div>
                        <p class="text-sm text-slate-600 font-medium">Nilai Kontrak / Pembayaran (DPP)</p>
                        <p class="text-xs text-slate-400 mt-0.5">Dasar Pengenaan Pajak</p>
                    </div>
                    <span class="text-base font-bold text-slate-800 tabular-nums">Rp {{ number_format($record->nilai_kontrak, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between px-5 py-4 bg-amber-50/50">
                    <div>
                        <p class="text-sm text-amber-700 font-medium">PPh Final Dipotong</p>
                        <p class="text-xs text-amber-600 mt-0.5">Tarif {{ $record->tarif }}% × DPP (PP 9/2022)</p>
                    </div>
                    <span class="text-base font-bold text-amber-700 tabular-nums">(Rp {{ number_format($record->pph_dipotong, 0, ',', '.') }})</span>
                </div>
                <div class="flex items-center justify-between px-5 py-5 bg-emerald-50">
                    <div>
                        <p class="text-sm font-bold text-slate-700">Nilai Diterima (Bersih)</p>
                        <p class="text-xs text-emerald-600 mt-0.5">Setelah pemotongan PPh Final</p>
                    </div>
                    <span class="text-xl font-black text-emerald-700 tabular-nums">Rp {{ number_format($record->getNilaiDiterima(), 0, ',', '.') }}</span>
                </div>
            </div>

            {{-- Tax Summary Bar --}}
            <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex flex-wrap gap-4 text-xs text-slate-500">
                <span>Tarif: <span class="font-black text-amber-700">{{ $record->tarif }}%</span></span>
                <span>Efektif PPh: <span class="font-semibold">Rp {{ number_format($record->pph_dipotong, 0, ',', '.') }}</span></span>
                <span>Rasio Potongan: <span class="font-semibold">{{ number_format(($record->nilai_kontrak > 0 ? $record->pph_dipotong / $record->nilai_kontrak * 100 : 0), 2) }}%</span></span>
            </div>
        </div>

    </div>

    {{-- === KOLOM SIDEBAR (1/3) === --}}
    <div class="space-y-5">

        {{-- Data Setoran Pajak --}}
        <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-2xl border border-amber-200 shadow-sm overflow-hidden">
            <div class="px-5 py-3.5 bg-amber-100/60 border-b border-amber-200">
                <h3 class="text-sm font-bold text-amber-800">Data Setoran Pajak</h3>
                <p class="text-xs text-amber-600 mt-0.5">Informasi untuk pengisian SSP/e-Billing</p>
            </div>
            <div class="p-5 space-y-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs text-amber-600 font-semibold mb-0.5">KAP (Kode Akun Pajak)</p>
                        <p class="font-mono font-black text-amber-900 text-2xl tabular-nums">{{ $kapKjs['kap'] }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-amber-600 font-semibold mb-0.5">KJS</p>
                        <p class="font-mono font-black text-amber-900 text-2xl tabular-nums">{{ $kapKjs['kjs'] }}</p>
                    </div>
                </div>
                <div class="pt-1">
                    <p class="text-xs text-amber-600 font-semibold mb-1">Uraian Setoran</p>
                    <p class="text-sm font-semibold text-amber-800 bg-amber-100 rounded-xl px-3 py-2 border border-amber-200">{{ $kapKjs['uraian'] }}</p>
                </div>
                <div class="pt-1 border-t border-amber-200">
                    <p class="text-xs text-amber-600 font-semibold mb-1">Jatuh Tempo Setoran</p>
                    <div class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-amber-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
                        </svg>
                        <div>
                            <p class="font-bold text-amber-900">{{ $jatuhTempo->translatedFormat('d F Y') }}</p>
                            <p class="text-xs text-amber-600">Tanggal 10 bulan berikutnya</p>
                        </div>
                    </div>
                    @php $daysLeft = now()->diffInDays($jatuhTempo, false); @endphp
                    @if($daysLeft >= 0)
                    <div class="mt-2 px-3 py-1.5 rounded-xl {{ $daysLeft <= 5 ? 'bg-rose-100 border border-rose-200 text-rose-700' : 'bg-amber-100 border border-amber-200 text-amber-700' }} text-xs font-semibold">
                        {{ $daysLeft <= 5 ? '⚠ ' : '' }}{{ $daysLeft }} hari lagi jatuh tempo
                    </div>
                    @else
                    <div class="mt-2 px-3 py-1.5 rounded-xl bg-rose-100 border border-rose-200 text-rose-700 text-xs font-semibold">
                        Telah melewati jatuh tempo
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Dasar Hukum --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-200">
                <h3 class="text-sm font-bold text-slate-700">Dasar Hukum</h3>
            </div>
            <div class="p-5 space-y-3">
                <div class="flex items-start gap-2.5">
                    <span class="flex-shrink-0 w-5 h-5 rounded-full bg-indigo-100 flex items-center justify-center mt-0.5">
                        <svg class="h-3 w-3 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                    </span>
                    <div>
                        <p class="text-xs font-bold text-slate-700">PP No. 9 Tahun 2022</p>
                        <p class="text-xs text-slate-500 mt-0.5 leading-relaxed">PPh Final Jasa Konstruksi (menggantikan PP 51/2008 jo PP 40/2009)</p>
                    </div>
                </div>
                <div class="flex items-start gap-2.5">
                    <span class="flex-shrink-0 w-5 h-5 rounded-full bg-amber-100 flex items-center justify-center mt-0.5">
                        <svg class="h-3 w-3 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                    </span>
                    <div>
                        <p class="text-xs font-bold text-slate-700">Sifat FINAL</p>
                        <p class="text-xs text-slate-500 mt-0.5 leading-relaxed">Tidak dapat dikreditkan dalam SPT Tahunan PPh</p>
                    </div>
                </div>
                <div class="flex items-start gap-2.5">
                    <span class="flex-shrink-0 w-5 h-5 rounded-full bg-rose-100 flex items-center justify-center mt-0.5">
                        <svg class="h-3 w-3 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5"/></svg>
                    </span>
                    <div>
                        <p class="text-xs font-bold text-slate-700">Kewajiban Pemotong</p>
                        <p class="text-xs text-slate-500 mt-0.5 leading-relaxed">Wajib menyetor paling lambat tgl 10 bulan berikutnya</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Invoice Terkait --}}
        @if($record->invoice)
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-200">
                <h3 class="text-sm font-bold text-slate-700">Invoice Terkait</h3>
            </div>
            <div class="p-4">
                <a href="{{ route('invoices.show', $record->invoice) }}"
                   class="flex items-center gap-3 p-3.5 rounded-xl border border-indigo-100 bg-indigo-50/60 hover:border-indigo-300 hover:bg-indigo-50 transition-colors group">
                    <div class="w-9 h-9 rounded-xl bg-indigo-100 flex items-center justify-center flex-shrink-0 group-hover:bg-indigo-200 transition-colors">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-bold text-indigo-700 truncate">{{ $record->invoice->invoice_no ?? 'Invoice #'.$record->invoice->id }}</p>
                        <p class="text-xs text-slate-400">Lihat detail invoice</p>
                    </div>
                    <svg class="h-4 w-4 text-indigo-400 group-hover:text-indigo-600 flex-shrink-0 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
                    </svg>
                </a>
            </div>
        </div>
        @endif

    </div>
</div>

@endsection
