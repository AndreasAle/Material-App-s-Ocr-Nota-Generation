@extends('layouts.app')

@section('content')

{{-- Breadcrumb --}}
<nav class="flex items-center gap-1.5 text-xs text-slate-400 mb-4">
    <a href="{{ route('dashboard') }}" class="hover:text-slate-600 transition-colors">Dashboard</a>
    <svg class="h-3 w-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <a href="{{ route('tax.dashboard') }}" class="hover:text-slate-600 transition-colors">Perpajakan</a>
    <svg class="h-3 w-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <a href="{{ route('tax.pph23.index') }}" class="hover:text-slate-600 transition-colors">PPh Pasal 23</a>
    <svg class="h-3 w-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-slate-700 font-semibold">Catat Bukti Potong</span>
</nav>

{{-- Header --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div class="flex items-center gap-3">
        <div class="flex-shrink-0 w-11 h-11 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-md">
            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
        </div>
        <div>
            <h1 class="text-xl font-bold text-slate-800 leading-tight">Catat Bukti Potong PPh Pasal 23</h1>
            <p class="text-sm text-slate-500">Pajak atas jasa pihak ketiga &mdash; <span class="font-medium text-emerald-700">UU PPh Pasal 23 Ayat (1) Huruf c</span></p>
        </div>
    </div>
    <a href="{{ route('tax.pph23.index') }}"
       class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-slate-200 bg-white text-sm font-medium text-slate-600 hover:bg-slate-50 hover:border-slate-300 transition-all shadow-sm">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
        </svg>
        Kembali
    </a>
</div>

<form method="POST" action="{{ route('tax.pph23.store') }}" x-data="pph23Form()" class="max-w-3xl space-y-5">
    @csrf

    {{-- Validation Errors --}}
    @if($errors->any())
    <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200">
        <div class="flex items-start gap-3">
            <svg class="h-5 w-5 text-rose-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
            </svg>
            <div>
                <p class="text-sm font-bold text-rose-800 mb-1">Terdapat {{ $errors->count() }} kesalahan</p>
                <ul class="space-y-0.5">
                    @foreach($errors->all() as $err)
                    <li class="text-xs text-rose-600 flex items-center gap-1.5">
                        <span class="w-1 h-1 rounded-full bg-rose-400 flex-shrink-0"></span>{{ $err }}
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    {{-- SECTION 1: Transaksi Terkait --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-200 flex items-center gap-2.5">
            <span class="flex-shrink-0 w-6 h-6 rounded-full bg-slate-200 flex items-center justify-center text-xs font-black text-slate-600">1</span>
            <div>
                <h3 class="text-sm font-bold text-slate-700">Sumber Data</h3>
                <p class="text-xs text-slate-400 mt-0.5">Hubungkan dengan transaksi pembelian yang ada (opsional)</p>
            </div>
        </div>
        <div class="p-5">
            <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                Transaksi Pembelian <span class="text-slate-400 font-normal">(opsional)</span>
            </label>
            <div class="relative">
                <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25"/>
                </svg>
                <select name="purchase_transaction_id"
                    class="w-full pl-10 pr-8 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-700 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-400 outline-none transition-all appearance-none"
                    @change="prefillFromTx($event)">
                    <option value="">— Pilih transaksi untuk prefill data (opsional) —</option>
                    @foreach($transactions as $tx)
                    <option value="{{ $tx->id }}"
                            data-supplier="{{ $tx->supplier->name ?? '' }}"
                            data-npwp="{{ $tx->supplier->npwp ?? '' }}"
                            data-total="{{ $tx->grand_total }}"
                            {{ old('purchase_transaction_id') == $tx->id ? 'selected' : '' }}>
                        {{ $tx->transaction_no ?? '#'.$tx->id }} — {{ $tx->transaction_date?->format('d/m/Y') }}
                        @if($tx->supplier) ({{ $tx->supplier->name }}) @endif
                    </option>
                    @endforeach
                </select>
                <svg class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
            </div>
            <p class="text-xs text-slate-400 mt-1.5">Memilih transaksi akan mengisi otomatis nama penerima, NPWP, dan nilai bruto</p>
        </div>
    </div>

    {{-- SECTION 2: Data Penerima --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-200 flex items-center gap-2.5">
            <span class="flex-shrink-0 w-6 h-6 rounded-full bg-slate-200 flex items-center justify-center text-xs font-black text-slate-600">2</span>
            <div>
                <h3 class="text-sm font-bold text-slate-700">Data Penerima Penghasilan</h3>
                <p class="text-xs text-slate-400 mt-0.5">Identitas pihak yang menerima pembayaran jasa</p>
            </div>
        </div>
        <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">

            {{-- Nama Penerima --}}
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                    Nama Penerima <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                    </svg>
                    <input type="text" name="nama_penerima" x-model="namaPenerima" value="{{ old('nama_penerima') }}"
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl border {{ $errors->has('nama_penerima') ? 'border-rose-400 bg-rose-50' : 'border-slate-200 bg-white' }} text-sm text-slate-700 placeholder-slate-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-400 outline-none transition-all"
                           placeholder="Nama orang atau badan usaha penerima">
                </div>
                @error('nama_penerima') <p class="text-xs text-rose-600 mt-1.5 flex items-center gap-1"><svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>{{ $message }}</p> @enderror
            </div>

            {{-- NPWP Penerima --}}
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                    NPWP Penerima <span class="text-slate-400 font-normal">(kosongkan jika tidak punya — tarif 4%)</span>
                </label>
                <div class="relative">
                    <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/>
                    </svg>
                    <input type="text" name="npwp_penerima" x-model="npwpPenerima" value="{{ old('npwp_penerima') }}"
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl border {{ $errors->has('npwp_penerima') ? 'border-rose-400 bg-rose-50' : 'border-slate-200 bg-white' }} text-sm text-slate-700 font-mono placeholder-slate-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-400 outline-none transition-all"
                           placeholder="XX.XXX.XXX.X-XXX.XXX"
                           @change="updateTarif">
                </div>
                @error('npwp_penerima') <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p> @enderror
            </div>

            {{-- Jenis Penghasilan --}}
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                    Jenis Penghasilan <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z"/>
                    </svg>
                    <select name="jenis_penghasilan"
                        class="w-full pl-10 pr-8 py-2.5 rounded-xl border {{ $errors->has('jenis_penghasilan') ? 'border-rose-400 bg-rose-50' : 'border-slate-200 bg-white' }} text-sm text-slate-700 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-400 outline-none transition-all appearance-none">
                        <option value="">— Pilih jenis penghasilan —</option>
                        @foreach($jenisPenghasilan as $jp)
                        <option value="{{ $jp }}" {{ old('jenis_penghasilan') === $jp ? 'selected' : '' }}>{{ $jp }}</option>
                        @endforeach
                    </select>
                    <svg class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                </div>
                @error('jenis_penghasilan') <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p> @enderror
            </div>
        </div>
    </div>

    {{-- SECTION 3: Kalkulasi PPh 23 --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 bg-emerald-50 border-b border-emerald-200 flex items-center gap-2.5">
            <span class="flex-shrink-0 w-6 h-6 rounded-full bg-emerald-200 flex items-center justify-center text-xs font-black text-emerald-800">3</span>
            <div>
                <h3 class="text-sm font-bold text-emerald-800">Kalkulasi Pemotongan PPh Pasal 23</h3>
                <p class="text-xs text-emerald-600 mt-0.5">KAP 411124 &bull; KJS 104 &bull; Jatuh tempo: tgl 10 bulan berikutnya</p>
            </div>
        </div>
        <div class="p-5 space-y-4">

            {{-- Tarif Indicator (Alpine reactive) --}}
            <div class="flex items-center justify-between p-4 rounded-2xl border transition-colors"
                 :class="tarifBerlaku == 2 ? 'bg-emerald-50 border-emerald-200' : 'bg-rose-50 border-rose-200'">
                <div>
                    <p class="text-xs font-bold mb-0.5" :class="tarifBerlaku == 2 ? 'text-emerald-800' : 'text-rose-800'">
                        Tarif PPh 23 Berlaku
                    </p>
                    <p class="text-xs" :class="tarifBerlaku == 2 ? 'text-emerald-600' : 'text-rose-600'"
                       x-text="tarifBerlaku == 2 ? 'Penerima ber-NPWP (tarif normal 2%)' : 'Penerima tidak ber-NPWP (sanksi 4%)'">
                    </p>
                </div>
                <div class="text-right flex-shrink-0 ml-4">
                    <p class="text-xs mb-0.5" :class="tarifBerlaku == 2 ? 'text-emerald-600' : 'text-rose-600'">Tarif</p>
                    <div class="flex items-end gap-0.5">
                        <span class="text-5xl font-black tabular-nums leading-none" :class="tarifBerlaku == 2 ? 'text-emerald-700' : 'text-rose-700'" x-text="tarifBerlaku"></span>
                        <span class="text-2xl font-bold mb-1" :class="tarifBerlaku == 2 ? 'text-emerald-600' : 'text-rose-600'">%</span>
                    </div>
                </div>
            </div>

            {{-- Hidden tarif --}}
            <input type="hidden" name="tarif" x-model="tarifBerlaku">

            {{-- Bruto + PPh --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                        Nilai Bruto (Dasar Pemotongan) <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-400 pointer-events-none">Rp</span>
                        <input type="number" name="bruto" x-model="bruto" @input="hitungPph"
                               value="{{ old('bruto') }}" step="1" min="0"
                               class="w-full pl-10 pr-4 py-2.5 rounded-xl border {{ $errors->has('bruto') ? 'border-rose-400 bg-rose-50' : 'border-slate-200 bg-white' }} text-sm text-slate-700 tabular-nums focus:ring-2 focus:ring-emerald-500 focus:border-emerald-400 outline-none transition-all"
                               placeholder="0">
                    </div>
                    @error('bruto') <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                        PPh Dipotong <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-semibold text-emerald-500 pointer-events-none">Rp</span>
                        <input type="number" name="pph_dipotong" x-model="pphDipotong"
                               value="{{ old('pph_dipotong') }}" step="1" min="0"
                               class="w-full pl-10 pr-4 py-2.5 rounded-xl border {{ $errors->has('pph_dipotong') ? 'border-rose-400 bg-rose-50' : 'border-emerald-200 bg-emerald-50' }} text-sm text-emerald-800 font-semibold tabular-nums focus:ring-2 focus:ring-emerald-500 focus:border-emerald-400 outline-none transition-all"
                               placeholder="0">
                    </div>
                    @error('pph_dipotong') <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p> @enderror
                    <p class="text-xs text-slate-400 mt-1">Dihitung otomatis, dapat disesuaikan</p>
                </div>
            </div>

            {{-- Calculation Summary --}}
            <div x-show="bruto > 0" x-transition class="rounded-2xl border border-slate-200 overflow-hidden">
                <div class="px-4 py-2.5 bg-slate-50 border-b border-slate-200">
                    <p class="text-xs font-bold text-slate-600 uppercase tracking-wider">Ringkasan Kalkulasi Pemotongan</p>
                </div>
                <div class="divide-y divide-slate-100">
                    <div class="flex items-center justify-between px-4 py-3">
                        <span class="text-sm text-slate-500">Nilai Bruto / DPP</span>
                        <span class="text-sm font-semibold text-slate-700 tabular-nums" x-text="'Rp ' + parseFloat(bruto||0).toLocaleString('id-ID')"></span>
                    </div>
                    <div class="flex items-center justify-between px-4 py-3 bg-emerald-50/50">
                        <div>
                            <span class="text-sm text-slate-500">PPh Dipotong</span>
                            <span class="text-xs text-emerald-600 ml-1.5 font-medium" x-text="'(' + tarifBerlaku + '%)'"></span>
                        </div>
                        <span class="text-sm font-bold text-emerald-700 tabular-nums" x-text="'(Rp ' + parseFloat(pphDipotong||0).toLocaleString('id-ID') + ')'"></span>
                    </div>
                    <div class="flex items-center justify-between px-4 py-3.5 bg-indigo-50">
                        <span class="text-sm font-bold text-slate-700">Yang Dibayarkan ke Penerima</span>
                        <span class="text-base font-black text-indigo-700 tabular-nums" x-text="'Rp ' + (parseFloat(bruto||0) - parseFloat(pphDipotong||0)).toLocaleString('id-ID')"></span>
                    </div>
                </div>
            </div>

            {{-- Periode + Status --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                        Periode Pajak <span class="text-rose-500">*</span>
                    </label>
                    <input type="month" name="periode" value="{{ old('periode', now()->format('Y-m')) }}"
                           class="w-full px-3.5 py-2.5 rounded-xl border {{ $errors->has('periode') ? 'border-rose-400 bg-rose-50' : 'border-slate-200 bg-white' }} text-sm text-slate-700 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-400 outline-none transition-all">
                    @error('periode') <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                        Status Setoran <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <select name="status"
                            class="w-full pl-3.5 pr-8 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-700 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-400 outline-none transition-all appearance-none">
                            <option value="belum_disetor" {{ old('status', 'belum_disetor') === 'belum_disetor' ? 'selected' : '' }}>Belum Disetor</option>
                            <option value="sudah_disetor" {{ old('status') === 'sudah_disetor' ? 'selected' : '' }}>Sudah Disetor</option>
                        </select>
                        <svg class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                    </div>
                </div>
            </div>

            {{-- No. Bukpot + Tanggal Bukpot --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                        No. Bukti Potong <span class="text-slate-400 font-normal">(opsional)</span>
                    </label>
                    <input type="text" name="no_bukti_potong" value="{{ old('no_bukti_potong') }}"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-700 font-mono placeholder-slate-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-400 outline-none transition-all"
                           placeholder="Nomor bukti potong">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                        Tanggal Bukti Potong <span class="text-slate-400 font-normal">(opsional)</span>
                    </label>
                    <input type="date" name="tanggal_bukti_potong" value="{{ old('tanggal_bukti_potong', today()->format('Y-m-d')) }}"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-700 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-400 outline-none transition-all">
                </div>
            </div>

            {{-- Tanggal Setor + NTPN (jika sudah disetor) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                        Tanggal Setor <span class="text-slate-400 font-normal">(isi jika sudah disetor)</span>
                    </label>
                    <input type="date" name="tanggal_setor" value="{{ old('tanggal_setor') }}"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-700 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-400 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                        NTPN <span class="text-slate-400 font-normal">(isi jika sudah disetor)</span>
                    </label>
                    <input type="text" name="ntpn" value="{{ old('ntpn') }}"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-700 font-mono placeholder-slate-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-400 outline-none transition-all"
                           placeholder="Nomor Transaksi Penerimaan Negara">
                </div>
            </div>

            {{-- Keterangan --}}
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                    Keterangan <span class="text-slate-400 font-normal">(opsional)</span>
                </label>
                <input type="text" name="keterangan" value="{{ old('keterangan') }}"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-700 placeholder-slate-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-400 outline-none transition-all"
                       placeholder="Mis: PPh 23 atas jasa konsultansi bulan {{ now()->translatedFormat('F Y') }}">
            </div>
        </div>
    </div>

    {{-- Info Note --}}
    <div class="flex items-start gap-3 p-4 rounded-2xl bg-indigo-50 border border-indigo-200">
        <svg class="h-4 w-4 text-indigo-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
        </svg>
        <div>
            <p class="text-xs font-bold text-indigo-800 mb-1">Ketentuan Penyetoran PPh Pasal 23</p>
            <ul class="space-y-1 text-xs text-indigo-700 leading-relaxed">
                <li class="flex items-start gap-1.5"><span class="w-1 h-1 rounded-full bg-indigo-400 flex-shrink-0 mt-1.5"></span>KAP: <span class="font-mono font-semibold">411124</span> &bull; KJS: <span class="font-mono font-semibold">104</span></li>
                <li class="flex items-start gap-1.5"><span class="w-1 h-1 rounded-full bg-indigo-400 flex-shrink-0 mt-1.5"></span>Jatuh tempo setoran: <span class="font-semibold">tanggal 10 bulan berikutnya</span></li>
                <li class="flex items-start gap-1.5"><span class="w-1 h-1 rounded-full bg-indigo-400 flex-shrink-0 mt-1.5"></span>Bukpot <span class="font-semibold">wajib diberikan ke penerima</span> paling lambat pada tanggal pemotongan</li>
                <li class="flex items-start gap-1.5"><span class="w-1 h-1 rounded-full bg-indigo-400 flex-shrink-0 mt-1.5"></span>Penerima <span class="font-semibold">tidak ber-NPWP</span> dikenakan tarif 2× lebih tinggi (4%)</li>
            </ul>
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="flex items-center justify-between pt-1">
        <a href="{{ route('tax.pph23.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm font-medium text-slate-600 hover:bg-slate-50 hover:border-slate-300 transition-all">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            Batal
        </a>
        <button type="submit"
            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-bold hover:bg-emerald-700 active:bg-emerald-800 transition-all shadow-sm hover:shadow-md">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
            </svg>
            Simpan Bukti Potong
        </button>
    </div>
</form>

@push('scripts')
<script>
function pph23Form() {
    return {
        namaPenerima: '{{ old("nama_penerima") }}',
        npwpPenerima: '{{ old("npwp_penerima") }}',
        bruto: '{{ old("bruto") }}',
        pphDipotong: '{{ old("pph_dipotong") }}',
        tarifBerlaku: {{ old("tarif", 2) }},
        tarif2: {{ $tarif2 }},
        tarif4: {{ $tarif4 }},

        updateTarif() {
            this.tarifBerlaku = this.npwpPenerima.trim() !== '' ? this.tarif2 : this.tarif4;
            this.hitungPph();
        },

        hitungPph() {
            const b = parseFloat(this.bruto) || 0;
            this.pphDipotong = Math.round(b * this.tarifBerlaku / 100);
        },

        prefillFromTx(e) {
            const opt = e.target.selectedOptions[0];
            if (!opt || !opt.value) return;
            this.namaPenerima = opt.dataset.supplier || '';
            this.npwpPenerima = opt.dataset.npwp    || '';
            this.bruto        = parseFloat(opt.dataset.total) || 0;
            this.updateTarif();
        },
    };
}
</script>
@endpush
@endsection
