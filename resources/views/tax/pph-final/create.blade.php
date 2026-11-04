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
    <span class="text-slate-700 font-semibold">Catat Bukti Potong</span>
</nav>

{{-- Header --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div class="flex items-center gap-3">
        <div class="flex-shrink-0 w-11 h-11 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-md">
            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
        </div>
        <div>
            <h1 class="text-xl font-bold text-slate-800 leading-tight">Catat Bukti Potong PPh Final 4(2)</h1>
            <p class="text-sm text-slate-500">PPh Final Jasa Konstruksi &mdash; PP No. 9 Tahun 2022</p>
        </div>
    </div>
    <a href="{{ route('tax.pph-final.index') }}"
       class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-slate-200 bg-white text-sm font-medium text-slate-600 hover:bg-slate-50 hover:border-slate-300 transition-all shadow-sm">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
        </svg>
        Kembali
    </a>
</div>

<form method="POST" action="{{ route('tax.pph-final.store') }}" x-data="pphFinalForm()" class="max-w-3xl space-y-5">
    @csrf

    {{-- Validation Errors Summary --}}
    @if($errors->any())
    <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200">
        <div class="flex items-start gap-3">
            <svg class="h-5 w-5 text-rose-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
            </svg>
            <div>
                <p class="text-sm font-semibold text-rose-800 mb-1">Terdapat {{ $errors->count() }} kesalahan pada formulir</p>
                <ul class="space-y-0.5">
                    @foreach($errors->all() as $err)
                    <li class="text-xs text-rose-600 flex items-center gap-1">
                        <span class="w-1 h-1 rounded-full bg-rose-400 flex-shrink-0"></span>{{ $err }}
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    {{-- SECTION 1: Sumber Data --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-200 flex items-center gap-2.5">
            <span class="flex-shrink-0 w-6 h-6 rounded-full bg-slate-200 flex items-center justify-center text-xs font-black text-slate-600">1</span>
            <div>
                <h3 class="text-sm font-bold text-slate-700">Sumber Data</h3>
                <p class="text-xs text-slate-400">Hubungkan dengan invoice atau proyek yang ada (opsional)</p>
            </div>
        </div>
        <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Invoice Terkait <span class="text-slate-400 font-normal">(opsional)</span></label>
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                    </svg>
                    <select name="invoice_id" class="w-full pl-9 pr-8 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-700 focus:ring-2 focus:ring-amber-500 focus:border-amber-400 outline-none transition-all appearance-none"
                        @change="prefillInvoice($event)">
                        <option value="">— Pilih invoice (opsional) —</option>
                        @foreach($invoices as $inv)
                        <option value="{{ $inv->id }}"
                                data-total="{{ $inv->grand_total ?? $inv->total_amount ?? 0 }}"
                                data-project="{{ $inv->project_id ?? '' }}"
                                {{ old('invoice_id') == $inv->id ? 'selected' : '' }}>
                            {{ $inv->invoice_no ?? '#'.$inv->id }} — {{ $inv->invoice_date?->format('d/m/Y') }}
                            @if($inv->project) [{{ $inv->project->name }}] @endif
                        </option>
                        @endforeach
                    </select>
                    <svg class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Proyek <span class="text-slate-400 font-normal">(opsional)</span></label>
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21"/>
                    </svg>
                    <select name="project_id" x-model="projectId" class="w-full pl-9 pr-8 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-700 focus:ring-2 focus:ring-amber-500 focus:border-amber-400 outline-none transition-all appearance-none">
                        <option value="">— Pilih proyek —</option>
                        @foreach($projects as $p)
                        <option value="{{ $p->id }}" {{ old('project_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                    <svg class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                </div>
            </div>
        </div>
    </div>

    {{-- SECTION 2: Data Pemotong --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-200 flex items-center gap-2.5">
            <span class="flex-shrink-0 w-6 h-6 rounded-full bg-slate-200 flex items-center justify-center text-xs font-black text-slate-600">2</span>
            <div>
                <h3 class="text-sm font-bold text-slate-700">Data Pemotong Pajak</h3>
                <p class="text-xs text-slate-400">Identitas pemberi kerja yang melakukan pemotongan</p>
            </div>
        </div>
        <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                    Nama Pemotong <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="nama_pemotong" value="{{ old('nama_pemotong') }}"
                       class="w-full px-3.5 py-2.5 rounded-xl border {{ $errors->has('nama_pemotong') ? 'border-rose-400 bg-rose-50' : 'border-slate-200 bg-white' }} text-sm text-slate-700 placeholder-slate-400 focus:ring-2 focus:ring-amber-500 focus:border-amber-400 outline-none transition-all"
                       placeholder="Nama instansi / perusahaan pemberi kerja">
                @error('nama_pemotong') <p class="text-xs text-rose-600 mt-1.5 flex items-center gap-1"><svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                    NPWP Pemotong <span class="text-slate-400 font-normal">(opsional)</span>
                </label>
                <input type="text" name="npwp_pemotong" value="{{ old('npwp_pemotong') }}"
                       class="w-full px-3.5 py-2.5 rounded-xl border {{ $errors->has('npwp_pemotong') ? 'border-rose-400 bg-rose-50' : 'border-slate-200 bg-white' }} text-sm text-slate-700 font-mono placeholder-slate-400 focus:ring-2 focus:ring-amber-500 focus:border-amber-400 outline-none transition-all"
                       placeholder="XX.XXX.XXX.X-XXX.XXX">
                @error('npwp_pemotong') <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                    No. Bukti Potong <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="no_bukti_potong" value="{{ old('no_bukti_potong') }}"
                       class="w-full px-3.5 py-2.5 rounded-xl border {{ $errors->has('no_bukti_potong') ? 'border-rose-400 bg-rose-50' : 'border-slate-200 bg-white' }} text-sm text-slate-700 font-mono placeholder-slate-400 focus:ring-2 focus:ring-amber-500 focus:border-amber-400 outline-none transition-all"
                       placeholder="Nomor dari bukpot yang diterima">
                @error('no_bukti_potong') <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                    Tanggal Bukti Potong <span class="text-rose-500">*</span>
                </label>
                <input type="date" name="tanggal_bukti_potong"
                       value="{{ old('tanggal_bukti_potong', today()->format('Y-m-d')) }}"
                       class="w-full px-3.5 py-2.5 rounded-xl border {{ $errors->has('tanggal_bukti_potong') ? 'border-rose-400 bg-rose-50' : 'border-slate-200 bg-white' }} text-sm text-slate-700 focus:ring-2 focus:ring-amber-500 focus:border-amber-400 outline-none transition-all"
                       @change="updatePeriode($event)">
                @error('tanggal_bukti_potong') <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p> @enderror
            </div>
        </div>
    </div>

    {{-- SECTION 3: Kalkulasi PPh Final --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 bg-amber-50 border-b border-amber-200 flex items-center gap-2.5">
            <span class="flex-shrink-0 w-6 h-6 rounded-full bg-amber-200 flex items-center justify-center text-xs font-black text-amber-800">3</span>
            <div>
                <h3 class="text-sm font-bold text-amber-800">Kalkulasi PPh Final</h3>
                <p class="text-xs text-amber-600">PP No. 9 Tahun 2022 — KAP 411128 / KJS 409</p>
            </div>
        </div>
        <div class="p-5 space-y-4">

            {{-- Jenis & Kualifikasi --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                        Jenis Usaha <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <select name="jenis_usaha" x-model="jenisUsaha"
                            class="w-full pl-3.5 pr-8 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-700 focus:ring-2 focus:ring-amber-500 focus:border-amber-400 outline-none transition-all appearance-none"
                            @change="hitungTarif">
                            <option value="pelaksana"   {{ old('jenis_usaha', $setting->jenis_usaha) === 'pelaksana'   ? 'selected' : '' }}>Pelaksana Konstruksi</option>
                            <option value="perencana"   {{ old('jenis_usaha', $setting->jenis_usaha) === 'perencana'   ? 'selected' : '' }}>Perencana Konstruksi</option>
                            <option value="pengawas"    {{ old('jenis_usaha', $setting->jenis_usaha) === 'pengawas'    ? 'selected' : '' }}>Pengawas Konstruksi</option>
                            <option value="konsultansi" {{ old('jenis_usaha', $setting->jenis_usaha) === 'konsultansi' ? 'selected' : '' }}>Konsultansi Teknis</option>
                        </select>
                        <svg class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                        Kualifikasi SBU <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <select name="kualifikasi" x-model="kualifikasi"
                            class="w-full pl-3.5 pr-8 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-700 focus:ring-2 focus:ring-amber-500 focus:border-amber-400 outline-none transition-all appearance-none"
                            @change="hitungTarif">
                            <option value="kecil"               {{ old('kualifikasi', $setting->kualifikasi_konstruksi) === 'kecil'               ? 'selected' : '' }}>Kecil (SBU Kecil)</option>
                            <option value="menengah"            {{ old('kualifikasi', $setting->kualifikasi_konstruksi) === 'menengah'            ? 'selected' : '' }}>Menengah (SBU Menengah)</option>
                            <option value="besar"               {{ old('kualifikasi', $setting->kualifikasi_konstruksi) === 'besar'               ? 'selected' : '' }}>Besar (SBU Besar)</option>
                            <option value="tidak_bersertifikat" {{ old('kualifikasi', $setting->kualifikasi_konstruksi) === 'tidak_bersertifikat' ? 'selected' : '' }}>Tidak Bersertifikat</option>
                        </select>
                        <svg class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                    </div>
                </div>
            </div>

            {{-- Tarif Indicator --}}
            <div class="flex items-center justify-between p-4 bg-amber-50 border border-amber-200 rounded-2xl">
                <div>
                    <p class="text-xs font-semibold text-amber-700 mb-0.5">Tarif PPh Final Berlaku (PP 9/2022)</p>
                    <p class="text-xs text-amber-500">KAP: 411128 &bull; KJS: 409 &bull; Jatuh tempo: tgl 10 bulan berikutnya</p>
                </div>
                <div class="text-right">
                    <span class="text-4xl font-black text-amber-700 tabular-nums" x-text="tarif"></span>
                    <span class="text-xl font-bold text-amber-600">%</span>
                </div>
            </div>

            {{-- Nilai & PPh --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                        Nilai Kontrak / Pembayaran (DPP) <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-400 pointer-events-none">Rp</span>
                        <input type="number" name="nilai_kontrak" x-model="nilaiKontrak" @input="hitungPph"
                               value="{{ old('nilai_kontrak') }}" step="1" min="0"
                               class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border {{ $errors->has('nilai_kontrak') ? 'border-rose-400 bg-rose-50' : 'border-slate-200 bg-white' }} text-sm text-slate-700 tabular-nums focus:ring-2 focus:ring-amber-500 focus:border-amber-400 outline-none transition-all"
                               placeholder="0">
                    </div>
                    @error('nilai_kontrak') <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                        PPh Final Dipotong <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-semibold text-amber-500 pointer-events-none">Rp</span>
                        <input type="number" name="pph_dipotong" x-model="pphDipotong"
                               value="{{ old('pph_dipotong') }}" step="1" min="0"
                               class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border {{ $errors->has('pph_dipotong') ? 'border-rose-400 bg-rose-50' : 'border-amber-200 bg-amber-50' }} text-sm text-amber-800 font-semibold tabular-nums focus:ring-2 focus:ring-amber-500 focus:border-amber-400 outline-none transition-all"
                               placeholder="0">
                    </div>
                    @error('pph_dipotong') <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p> @enderror
                    <p class="text-xs text-slate-400 mt-1">Dihitung otomatis, dapat disesuaikan</p>
                </div>
            </div>

            {{-- Hidden tarif --}}
            <input type="hidden" name="tarif" x-model="tarif">

            {{-- Calculation Summary Card --}}
            <div x-show="nilaiKontrak > 0" x-transition class="rounded-2xl border border-slate-200 overflow-hidden">
                <div class="px-4 py-2.5 bg-slate-50 border-b border-slate-200">
                    <p class="text-xs font-bold text-slate-600 uppercase tracking-wider">Ringkasan Kalkulasi</p>
                </div>
                <div class="divide-y divide-slate-100">
                    <div class="flex items-center justify-between px-4 py-3">
                        <span class="text-sm text-slate-500">Nilai Kontrak / DPP</span>
                        <span class="text-sm font-semibold text-slate-700 tabular-nums" x-text="'Rp ' + parseFloat(nilaiKontrak||0).toLocaleString('id-ID')"></span>
                    </div>
                    <div class="flex items-center justify-between px-4 py-3 bg-amber-50/50">
                        <div>
                            <span class="text-sm text-slate-500">PPh Final Dipotong</span>
                            <span class="text-xs text-amber-600 ml-1.5 font-medium" x-text="'(' + tarif + '%)'"></span>
                        </div>
                        <span class="text-sm font-bold text-amber-700 tabular-nums" x-text="'(Rp ' + parseFloat(pphDipotong||0).toLocaleString('id-ID') + ')'"></span>
                    </div>
                    <div class="flex items-center justify-between px-4 py-3.5 bg-emerald-50">
                        <span class="text-sm font-bold text-slate-700">Nilai Diterima (Bersih)</span>
                        <span class="text-base font-black text-emerald-700 tabular-nums" x-text="'Rp ' + (parseFloat(nilaiKontrak||0) - parseFloat(pphDipotong||0)).toLocaleString('id-ID')"></span>
                    </div>
                </div>
            </div>

            {{-- Periode & Status --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                        Periode Pajak <span class="text-rose-500">*</span>
                    </label>
                    <input type="month" name="periode" x-model="periode"
                           value="{{ old('periode', now()->format('Y-m')) }}"
                           class="w-full px-3.5 py-2.5 rounded-xl border {{ $errors->has('periode') ? 'border-rose-400 bg-rose-50' : 'border-slate-200 bg-white' }} text-sm text-slate-700 focus:ring-2 focus:ring-amber-500 focus:border-amber-400 outline-none transition-all">
                    @error('periode') <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                        Status Bukpot <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <select name="status" class="w-full pl-3.5 pr-8 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-700 focus:ring-2 focus:ring-amber-500 focus:border-amber-400 outline-none transition-all appearance-none">
                            <option value="belum_terima_bukpot" {{ old('status', 'belum_terima_bukpot') === 'belum_terima_bukpot' ? 'selected' : '' }}>Belum Terima Bukpot</option>
                            <option value="sudah_terima_bukpot" {{ old('status') === 'sudah_terima_bukpot' ? 'selected' : '' }}>Sudah Terima Bukpot</option>
                        </select>
                        <svg class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                    </div>
                </div>
            </div>

            {{-- Keterangan --}}
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Keterangan <span class="text-slate-400 font-normal">(opsional)</span></label>
                <input type="text" name="keterangan" value="{{ old('keterangan') }}"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-sm text-slate-700 placeholder-slate-400 focus:ring-2 focus:ring-amber-500 focus:border-amber-400 outline-none transition-all"
                       placeholder="Mis: Bukpot atas Kontrak No. ... / Termin ke-...">
            </div>
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="flex items-center justify-between pt-1">
        <a href="{{ route('tax.pph-final.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm font-medium text-slate-600 hover:bg-slate-50 hover:border-slate-300 transition-all">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            Batal
        </a>
        <button type="submit"
            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-amber-600 text-white text-sm font-bold hover:bg-amber-700 active:bg-amber-800 transition-all shadow-sm hover:shadow-md">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
            </svg>
            Simpan Bukti Potong
        </button>
    </div>

</form>

@push('scripts')
<script>
function pphFinalForm() {
    const RATES = {
        pelaksana:   { kecil: 1.75, menengah: 2.65, besar: 2.65, tidak_bersertifikat: 4.00 },
        perencana:   { kecil: 4.00, menengah: 4.00, besar: 4.00, tidak_bersertifikat: 6.00 },
        pengawas:    { kecil: 4.00, menengah: 4.00, besar: 4.00, tidak_bersertifikat: 6.00 },
        konsultansi: { kecil: 4.00, menengah: 4.00, besar: 4.00, tidak_bersertifikat: 6.00 },
    };

    return {
        jenisUsaha:   '{{ old("jenis_usaha", $setting->jenis_usaha) }}',
        kualifikasi:  '{{ old("kualifikasi", $setting->kualifikasi_konstruksi) }}',
        tarif:        {{ $tarifDefault }},
        nilaiKontrak: '{{ old("nilai_kontrak") }}',
        pphDipotong:  '{{ old("pph_dipotong") }}',
        projectId:    '{{ old("project_id") }}',
        periode:      '{{ old("periode", now()->format("Y-m")) }}',

        hitungTarif() {
            this.tarif = (RATES[this.jenisUsaha] || {})[this.kualifikasi] || 4.00;
            this.hitungPph();
        },
        hitungPph() {
            const n = parseFloat(this.nilaiKontrak) || 0;
            this.pphDipotong = Math.round(n * this.tarif / 100);
        },
        updatePeriode(e) {
            const d = new Date(e.target.value);
            if (!isNaN(d)) {
                this.periode = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0');
            }
        },
        prefillInvoice(e) {
            const opt = e.target.selectedOptions[0];
            if (!opt || !opt.value) return;
            this.nilaiKontrak = parseFloat(opt.dataset.total) || 0;
            if (opt.dataset.project) this.projectId = opt.dataset.project;
            this.hitungPph();
        },
    };
}
</script>
@endpush
@endsection
