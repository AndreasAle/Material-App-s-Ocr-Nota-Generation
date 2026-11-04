@extends('layouts.app')

@section('content')

{{-- Breadcrumb --}}
<nav class="flex items-center gap-1.5 text-xs text-slate-400 mb-4">
    <a href="{{ route('dashboard') }}" class="hover:text-slate-600 transition-colors">Dashboard</a>
    <svg class="h-3 w-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <a href="{{ route('tax.dashboard') }}" class="hover:text-slate-600 transition-colors">Perpajakan</a>
    <svg class="h-3 w-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-slate-700 font-semibold">Pengaturan Pajak</span>
</nav>

{{-- Header --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div class="flex items-center gap-3">
        <div class="flex-shrink-0 w-11 h-11 rounded-2xl bg-gradient-to-br from-slate-600 to-slate-800 flex items-center justify-center shadow-md">
            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 010 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 010-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </div>
        <div>
            <h1 class="text-xl font-bold text-slate-800 leading-tight">Pengaturan Pajak Perusahaan</h1>
            <p class="text-sm text-slate-500">Profil wajib pajak — digunakan untuk kalkulasi otomatis seluruh modul pajak</p>
        </div>
    </div>
    <a href="{{ route('tax.dashboard') }}"
       class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-slate-200 bg-white text-sm font-medium text-slate-600 hover:bg-slate-50 hover:border-slate-300 transition-all shadow-sm">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
        </svg>
        Dashboard Pajak
    </a>
</div>

{{-- Flash --}}

{{-- Validation Errors --}}
@if($errors->any())
<div class="flex items-start gap-3 p-4 rounded-2xl bg-rose-50 border border-rose-200 mb-5">
    <div class="flex-shrink-0 w-8 h-8 bg-rose-100 rounded-xl flex items-center justify-center">
        <svg class="h-4 w-4 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
        </svg>
    </div>
    <div class="flex-1">
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
@endif

<form method="POST" action="{{ route('tax.settings.update') }}">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- ===== KOLOM UTAMA (2/3) ===== --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- SECTION 1: Identitas Wajib Pajak --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-200 flex items-center gap-2.5">
                    <span class="flex-shrink-0 w-6 h-6 rounded-full bg-indigo-100 flex items-center justify-center text-xs font-black text-indigo-700">1</span>
                    <div>
                        <h3 class="text-sm font-bold text-slate-700">Identitas Wajib Pajak</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Data sesuai yang terdaftar di Direktorat Jenderal Pajak (DJP)</p>
                    </div>
                </div>
                <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">

                    {{-- Nama WP --}}
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                            Nama Wajib Pajak (Perusahaan) <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/>
                            </svg>
                            <input type="text" name="nama_wp" value="{{ old('nama_wp', $setting->nama_wp) }}"
                                   class="w-full pl-10 pr-4 py-2.5 rounded-xl border {{ $errors->has('nama_wp') ? 'border-rose-400 bg-rose-50' : 'border-slate-200 bg-white' }} text-sm text-slate-700 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-400 outline-none transition-all"
                                   placeholder="Nama sesuai akte perusahaan / NPWP">
                        </div>
                        @error('nama_wp') <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p> @enderror
                    </div>

                    {{-- NPWP --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                            NPWP <span class="text-slate-400 font-normal">(opsional)</span>
                        </label>
                        <div class="relative">
                            <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-3.17.789 6.721 6.721 0 01-3.168-.789 3.376 3.376 0 016.338 0z"/>
                            </svg>
                            <input type="text" name="npwp" value="{{ old('npwp', $setting->npwp) }}"
                                   class="w-full pl-10 pr-4 py-2.5 rounded-xl border {{ $errors->has('npwp') ? 'border-rose-400 bg-rose-50' : 'border-slate-200 bg-white' }} text-sm text-slate-700 font-mono placeholder-slate-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-400 outline-none transition-all"
                                   placeholder="XX.XXX.XXX.X-XXX.XXX">
                        </div>
                        @error('npwp') <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p> @enderror
                        @if(!$setting->npwp)
                        <p class="text-xs text-amber-600 mt-1.5 flex items-center gap-1">
                            <svg class="h-3 w-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                            Belum diisi — diperlukan untuk pelaporan pajak
                        </p>
                        @endif
                    </div>

                    {{-- KLU --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                            KLU (Kode Lapangan Usaha) <span class="text-slate-400 font-normal">(opsional)</span>
                        </label>
                        <div class="relative">
                            <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 8.25h15m-16.5 7.5h15m-1.8-13.5l-3.9 19.5m-2.1-19.5l-3.9 19.5"/>
                            </svg>
                            <input type="text" name="klu" value="{{ old('klu', $setting->klu) }}"
                                   class="w-full pl-10 pr-4 py-2.5 rounded-xl border {{ $errors->has('klu') ? 'border-rose-400 bg-rose-50' : 'border-slate-200 bg-white' }} text-sm text-slate-700 font-mono placeholder-slate-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-400 outline-none transition-all"
                                   placeholder="Mis: 41010">
                        </div>
                        @error('klu') <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p> @enderror
                        <p class="text-xs text-slate-400 mt-1">41010 = Konstruksi Gedung Hunian</p>
                    </div>

                    {{-- KPP --}}
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                            KPP Terdaftar <span class="text-slate-400 font-normal">(opsional)</span>
                        </label>
                        <div class="relative">
                            <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75"/>
                            </svg>
                            <input type="text" name="kpp" value="{{ old('kpp', $setting->kpp) }}"
                                   class="w-full pl-10 pr-4 py-2.5 rounded-xl border {{ $errors->has('kpp') ? 'border-rose-400 bg-rose-50' : 'border-slate-200 bg-white' }} text-sm text-slate-700 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-400 outline-none transition-all"
                                   placeholder="Mis: KPP Pratama Jakarta Selatan Satu">
                        </div>
                        @error('kpp') <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p> @enderror
                    </div>

                    {{-- Alamat WP --}}
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                            Alamat Wajib Pajak <span class="text-slate-400 font-normal">(opsional)</span>
                        </label>
                        <div class="relative">
                            <svg class="absolute left-3.5 top-3 h-4 w-4 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 0115 0z"/>
                            </svg>
                            <textarea name="alamat_wp" rows="2"
                                      class="w-full pl-10 pr-4 py-2.5 rounded-xl border {{ $errors->has('alamat_wp') ? 'border-rose-400 bg-rose-50' : 'border-slate-200 bg-white' }} text-sm text-slate-700 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-400 outline-none transition-all resize-none"
                                      placeholder="Alamat sesuai yang terdaftar di DJP">{{ old('alamat_wp', $setting->alamat_wp) }}</textarea>
                        </div>
                        @error('alamat_wp') <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- SECTION 3: PPh Final Konstruksi --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-5 py-3.5 bg-amber-50 border-b border-amber-200 flex items-center gap-2.5">
                    <span class="flex-shrink-0 w-6 h-6 rounded-full bg-amber-200 flex items-center justify-center text-xs font-black text-amber-800">3</span>
                    <div>
                        <h3 class="text-sm font-bold text-amber-800">Profil PPh Final Jasa Konstruksi</h3>
                        <p class="text-xs text-amber-600 mt-0.5">Menentukan tarif PPh Final Pasal 4(2) — PP No. 9 Tahun 2022</p>
                    </div>
                </div>
                <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">

                    {{-- Jenis Usaha --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                            Jenis Usaha Konstruksi <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <select name="jenis_usaha"
                                class="w-full pl-3.5 pr-8 py-2.5 rounded-xl border {{ $errors->has('jenis_usaha') ? 'border-rose-400 bg-rose-50' : 'border-slate-200 bg-white' }} text-sm text-slate-700 focus:ring-2 focus:ring-amber-500 focus:border-amber-400 outline-none transition-all appearance-none">
                                <option value="pelaksana"   {{ old('jenis_usaha', $setting->jenis_usaha) === 'pelaksana'   ? 'selected' : '' }}>Pelaksana Konstruksi</option>
                                <option value="perencana"   {{ old('jenis_usaha', $setting->jenis_usaha) === 'perencana'   ? 'selected' : '' }}>Perencana Konstruksi</option>
                                <option value="pengawas"    {{ old('jenis_usaha', $setting->jenis_usaha) === 'pengawas'    ? 'selected' : '' }}>Pengawas Konstruksi</option>
                                <option value="konsultansi" {{ old('jenis_usaha', $setting->jenis_usaha) === 'konsultansi' ? 'selected' : '' }}>Konsultansi Teknis</option>
                            </select>
                            <svg class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                        </div>
                        @error('jenis_usaha') <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p> @enderror
                    </div>

                    {{-- Kualifikasi SBU --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                            Kualifikasi SBU <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <select name="kualifikasi_konstruksi" id="kualifikasi"
                                class="w-full pl-3.5 pr-8 py-2.5 rounded-xl border {{ $errors->has('kualifikasi_konstruksi') ? 'border-rose-400 bg-rose-50' : 'border-slate-200 bg-white' }} text-sm text-slate-700 focus:ring-2 focus:ring-amber-500 focus:border-amber-400 outline-none transition-all appearance-none">
                                <option value="kecil"               {{ old('kualifikasi_konstruksi', $setting->kualifikasi_konstruksi) === 'kecil'               ? 'selected' : '' }}>Kecil (SBU Kecil)</option>
                                <option value="menengah"            {{ old('kualifikasi_konstruksi', $setting->kualifikasi_konstruksi) === 'menengah'            ? 'selected' : '' }}>Menengah (SBU Menengah)</option>
                                <option value="besar"               {{ old('kualifikasi_konstruksi', $setting->kualifikasi_konstruksi) === 'besar'               ? 'selected' : '' }}>Besar (SBU Besar)</option>
                                <option value="tidak_bersertifikat" {{ old('kualifikasi_konstruksi', $setting->kualifikasi_konstruksi) === 'tidak_bersertifikat' ? 'selected' : '' }}>Tidak Bersertifikat</option>
                            </select>
                            <svg class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                        </div>
                        @error('kualifikasi_konstruksi') <p class="text-xs text-rose-600 mt-1.5">{{ $message }}</p> @enderror
                    </div>

                    {{-- Tarif Preview --}}
                    <div class="sm:col-span-2">
                        <div class="flex items-center justify-between p-4 bg-amber-50 border border-amber-200 rounded-2xl">
                            <div>
                                <p class="text-xs font-bold text-amber-800 mb-0.5">Tarif PPh Final Berlaku Saat Ini</p>
                                <p class="text-xs text-amber-600">Berdasarkan kombinasi Jenis Usaha &amp; Kualifikasi SBU di atas</p>
                                <p class="text-xs text-amber-500 mt-1">KAP: <span class="font-mono font-semibold">411128</span> &bull; KJS: <span class="font-mono font-semibold">409</span> &bull; Dasar: PP No. 9/2022</p>
                            </div>
                            <div class="text-right flex-shrink-0 ml-4">
                                <p class="text-xs text-amber-600 mb-0.5">Tarif</p>
                                <div class="flex items-end gap-0.5">
                                    <span class="text-5xl font-black text-amber-700 tabular-nums leading-none" id="tarifDisplay">
                                        {{ \App\Services\Tax\TaxCalculatorService::getTarifPphFinal($setting->jenis_usaha, $setting->kualifikasi_konstruksi) }}
                                    </span>
                                    <span class="text-2xl font-bold text-amber-600 mb-1">%</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tarif Reference Mini Table --}}
                    <div class="sm:col-span-2">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Tabel Tarif Referensi PP 9/2022</p>
                        <div class="overflow-x-auto rounded-xl border border-amber-200">
                            <table class="w-full text-xs">
                                <thead>
                                    <tr class="bg-amber-50 border-b border-amber-200">
                                        <th class="text-left px-3 py-2 font-semibold text-amber-700">Jenis Usaha</th>
                                        <th class="text-center px-3 py-2 font-semibold text-amber-700">Kecil</th>
                                        <th class="text-center px-3 py-2 font-semibold text-amber-700">Menengah/Besar</th>
                                        <th class="text-center px-3 py-2 font-semibold text-amber-700">Tidak Bersertifikat</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="border-b border-amber-100 hover:bg-amber-50/40">
                                        <td class="px-3 py-2 font-semibold text-slate-700">Pelaksana Konstruksi</td>
                                        <td class="px-3 py-2 text-center"><span class="font-black text-amber-700 tabular-nums">1,75%</span></td>
                                        <td class="px-3 py-2 text-center"><span class="font-black text-amber-700 tabular-nums">2,65%</span></td>
                                        <td class="px-3 py-2 text-center"><span class="font-black text-rose-600 tabular-nums">4,00%</span></td>
                                    </tr>
                                    <tr class="hover:bg-amber-50/40">
                                        <td class="px-3 py-2 font-semibold text-slate-700">Perencana / Pengawas / Konsultansi</td>
                                        <td class="px-3 py-2 text-center"><span class="font-black text-amber-700 tabular-nums">4,00%</span></td>
                                        <td class="px-3 py-2 text-center"><span class="font-black text-amber-700 tabular-nums">4,00%</span></td>
                                        <td class="px-3 py-2 text-center"><span class="font-black text-rose-600 tabular-nums">6,00%</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- ===== SIDEBAR (1/3) ===== --}}
        <div class="space-y-5">

            {{-- SECTION 2: Status PKP --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-5 py-3.5 bg-emerald-50 border-b border-emerald-200 flex items-center gap-2.5">
                    <span class="flex-shrink-0 w-6 h-6 rounded-full bg-emerald-200 flex items-center justify-center text-xs font-black text-emerald-800">2</span>
                    <div>
                        <h3 class="text-sm font-bold text-emerald-800">Status PKP</h3>
                        <p class="text-xs text-emerald-600 mt-0.5">Pengusaha Kena Pajak</p>
                    </div>
                </div>
                <div class="p-5">
                    {{-- PKP Toggle --}}
                    <input type="hidden" name="is_pkp" value="0">
                    <label class="flex items-start gap-3 cursor-pointer group">
                        <div class="flex-shrink-0 mt-0.5">
                            <input type="checkbox" name="is_pkp" value="1"
                                   {{ old('is_pkp', $setting->is_pkp) ? 'checked' : '' }}
                                   class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 transition-colors">
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-slate-700 group-hover:text-emerald-700 transition-colors">Dikukuhkan sebagai PKP</p>
                            <p class="text-xs text-slate-400 mt-0.5 leading-relaxed">Centang jika perusahaan sudah dikukuhkan sebagai Pengusaha Kena Pajak — wajib membuat Faktur Pajak dan lapor SPT Masa PPN</p>
                        </div>
                    </label>

                    {{-- Status Saat Ini --}}
                    <div class="mt-4 flex items-center justify-between p-3 rounded-xl {{ $setting->is_pkp ? 'bg-emerald-50 border border-emerald-200' : 'bg-slate-50 border border-slate-200' }}">
                        <span class="text-xs font-semibold {{ $setting->is_pkp ? 'text-emerald-700' : 'text-slate-500' }}">Status saat ini</span>
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold {{ $setting->is_pkp ? 'text-emerald-700' : 'text-slate-500' }}">
                            <span class="w-2 h-2 rounded-full {{ $setting->is_pkp ? 'bg-emerald-400' : 'bg-slate-300' }}"></span>
                            {{ $setting->is_pkp ? 'PKP Aktif' : 'Non-PKP' }}
                        </span>
                    </div>

                    {{-- Info Box --}}
                    <div class="mt-4 p-3.5 bg-blue-50 border border-blue-200 rounded-xl space-y-2">
                        <p class="text-xs font-bold text-blue-700 flex items-center gap-1.5">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
                            Ketentuan PKP
                        </p>
                        <ul class="space-y-1.5 text-xs text-blue-700">
                            <li class="flex items-start gap-1.5">
                                <span class="w-1 h-1 rounded-full bg-blue-400 flex-shrink-0 mt-1.5"></span>
                                Wajib jika omzet &gt; Rp 4,8 Miliar/tahun
                            </li>
                            <li class="flex items-start gap-1.5">
                                <span class="w-1 h-1 rounded-full bg-blue-400 flex-shrink-0 mt-1.5"></span>
                                PKP wajib lapor SPT Masa PPN setiap bulan
                            </li>
                            <li class="flex items-start gap-1.5">
                                <span class="w-1 h-1 rounded-full bg-blue-400 flex-shrink-0 mt-1.5"></span>
                                Dapat memungut &amp; mengkreditkan PPN Masukan
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Profil Ringkas --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-200">
                    <h3 class="text-sm font-bold text-slate-700">Profil Terkini</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Data yang tersimpan saat ini</p>
                </div>
                <div class="p-4 space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <span class="text-xs text-slate-400 flex-shrink-0 pt-0.5">Nama WP</span>
                        <span class="text-xs font-semibold text-slate-700 text-right">{{ $setting->nama_wp ?: '—' }}</span>
                    </div>
                    <div class="flex items-start justify-between gap-2">
                        <span class="text-xs text-slate-400 flex-shrink-0">NPWP</span>
                        <span class="text-xs font-mono font-semibold {{ $setting->npwp ? 'text-slate-700' : 'text-amber-600' }}">
                            {{ $setting->npwp ?: 'Belum diisi' }}
                        </span>
                    </div>
                    <div class="flex items-start justify-between gap-2">
                        <span class="text-xs text-slate-400 flex-shrink-0">Jenis Usaha</span>
                        <span class="text-xs font-semibold text-slate-700">{{ $setting->getLabelJenisUsaha() }}</span>
                    </div>
                    <div class="flex items-start justify-between gap-2">
                        <span class="text-xs text-slate-400 flex-shrink-0">Kualifikasi</span>
                        <span class="text-xs font-semibold text-slate-700">{{ $setting->getLabelKualifikasi() }}</span>
                    </div>
                    <div class="flex items-start justify-between gap-2">
                        <span class="text-xs text-slate-400 flex-shrink-0">Tarif PPh</span>
                        <span class="text-xs font-black text-amber-700 tabular-nums">
                            {{ \App\Services\Tax\TaxCalculatorService::getTarifPphFinal($setting->jenis_usaha, $setting->kualifikasi_konstruksi) }}%
                        </span>
                    </div>
                    <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                        <span class="text-xs text-slate-400">Status PKP</span>
                        <span class="inline-flex items-center gap-1 text-xs font-bold {{ $setting->is_pkp ? 'text-emerald-600' : 'text-slate-500' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $setting->is_pkp ? 'bg-emerald-400' : 'bg-slate-300' }}"></span>
                            {{ $setting->is_pkp ? 'PKP' : 'Non-PKP' }}
                        </span>
                    </div>
                </div>
            </div>

        </div>

        {{-- ===== REFERENSI KAP/KJS (full width) ===== --}}
        <div class="lg:col-span-3">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-6 h-6 rounded-lg bg-slate-200 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-700">Referensi KAP &amp; KJS Resmi DJP</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Kode yang digunakan pada SSP / e-Billing untuk penyetoran pajak</p>
                        </div>
                    </div>
                    <span class="text-xs text-slate-400 bg-slate-100 border border-slate-200 px-2.5 py-1 rounded-full font-medium flex-shrink-0">Hanya untuk referensi</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-xs">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="px-5 py-3 text-left font-semibold text-slate-500 uppercase tracking-wider">Jenis Pajak</th>
                                <th class="px-4 py-3 text-center font-semibold text-slate-500 uppercase tracking-wider">KAP</th>
                                <th class="px-4 py-3 text-center font-semibold text-slate-500 uppercase tracking-wider">KJS</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-500 uppercase tracking-wider">Uraian</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-500 uppercase tracking-wider">Jatuh Tempo</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-500 uppercase tracking-wider">Dasar Hukum</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr class="hover:bg-indigo-50/30 transition-colors">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-indigo-400 flex-shrink-0"></span>
                                        <span class="font-semibold text-slate-700">PPN Masa</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 text-center"><span class="font-mono font-black text-indigo-700 bg-indigo-50 border border-indigo-200 px-2 py-0.5 rounded-lg">411211</span></td>
                                <td class="px-4 py-3.5 text-center"><span class="font-mono font-bold text-slate-600">100</span></td>
                                <td class="px-4 py-3.5 text-slate-600">PPN Dalam Negeri</td>
                                <td class="px-4 py-3.5 text-slate-500">Tgl <strong>25</strong> bulan berikutnya</td>
                                <td class="px-4 py-3.5 text-slate-400">PMK No. 131/2024 — tarif 12% (berlaku 1 Jan 2025)</td>
                            </tr>
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-slate-400 flex-shrink-0"></span>
                                        <span class="font-semibold text-slate-700">PPh Pasal 21</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 text-center"><span class="font-mono font-bold text-slate-600 bg-slate-100 border border-slate-200 px-2 py-0.5 rounded-lg">411121</span></td>
                                <td class="px-4 py-3.5 text-center"><span class="font-mono font-bold text-slate-600">100</span></td>
                                <td class="px-4 py-3.5 text-slate-600">PPh Pasal 21 Masa</td>
                                <td class="px-4 py-3.5 text-slate-500">Tgl <strong>10</strong> bulan berikutnya</td>
                                <td class="px-4 py-3.5 text-slate-400">PMK-168/2023 (Skema TER)</td>
                            </tr>
                            <tr class="hover:bg-emerald-50/30 transition-colors">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-emerald-400 flex-shrink-0"></span>
                                        <span class="font-semibold text-slate-700">PPh Pasal 23</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 text-center"><span class="font-mono font-black text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-lg">411124</span></td>
                                <td class="px-4 py-3.5 text-center"><span class="font-mono font-bold text-slate-600">104</span></td>
                                <td class="px-4 py-3.5 text-slate-600">PPh Pasal 23 atas Jasa</td>
                                <td class="px-4 py-3.5 text-slate-500">Tgl <strong>10</strong> bulan berikutnya</td>
                                <td class="px-4 py-3.5 text-slate-400">UU PPh Ps. 23 — 2% / 4% (ber-NPWP)</td>
                            </tr>
                            <tr class="hover:bg-amber-50/30 transition-colors bg-amber-50/10">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-amber-400 flex-shrink-0"></span>
                                        <span class="font-semibold text-amber-800">PPh Final 4(2)</span>
                                        <span class="text-xs bg-amber-100 text-amber-700 border border-amber-200 px-1.5 py-0.5 rounded-full font-semibold">Berlaku</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 text-center"><span class="font-mono font-black text-amber-700 bg-amber-50 border border-amber-300 px-2 py-0.5 rounded-lg">411128</span></td>
                                <td class="px-4 py-3.5 text-center"><span class="font-mono font-black text-amber-700">409</span></td>
                                <td class="px-4 py-3.5 text-amber-800 font-medium">PPh Final Jasa Konstruksi</td>
                                <td class="px-4 py-3.5 text-slate-500">Tgl <strong>10</strong> bulan berikutnya</td>
                                <td class="px-4 py-3.5 text-slate-400">PP No. 9 Tahun 2022</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="px-5 py-2.5 bg-slate-50 border-t border-slate-100">
                    <p class="text-xs text-slate-400">Keterlambatan penyetoran dikenakan sanksi bunga <strong class="text-slate-500">2% per bulan</strong> dari jumlah pajak terutang (Pasal 9 ayat 2a UU KUP).</p>
                </div>
            </div>
        </div>

    </div>

    {{-- Action Buttons --}}
    <div class="flex items-center justify-between mt-6 pt-5 border-t border-slate-200">
        <a href="{{ route('tax.dashboard') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm font-medium text-slate-600 hover:bg-slate-50 hover:border-slate-300 transition-all">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            Batal
        </a>
        <button type="submit"
            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-slate-800 text-white text-sm font-bold hover:bg-slate-700 active:bg-slate-900 transition-all shadow-sm hover:shadow-md">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
            </svg>
            Simpan Pengaturan Pajak
        </button>
    </div>
</form>

@push('scripts')
<script>
(function () {
    const RATES = {
        pelaksana:   { kecil: 1.75, menengah: 2.65, besar: 2.65, tidak_bersertifikat: 4.00 },
        perencana:   { kecil: 4.00, menengah: 4.00, besar: 4.00, tidak_bersertifikat: 6.00 },
        pengawas:    { kecil: 4.00, menengah: 4.00, besar: 4.00, tidak_bersertifikat: 6.00 },
        konsultansi: { kecil: 4.00, menengah: 4.00, besar: 4.00, tidak_bersertifikat: 6.00 },
    };

    function updateTarif() {
        const jenis = document.querySelector('[name="jenis_usaha"]').value;
        const kual  = document.querySelector('[name="kualifikasi_konstruksi"]').value;
        const tarif = (RATES[jenis] || {})[kual] || 4.00;
        document.getElementById('tarifDisplay').textContent = tarif;
    }

    document.querySelector('[name="jenis_usaha"]').addEventListener('change', updateTarif);
    document.querySelector('[name="kualifikasi_konstruksi"]').addEventListener('change', updateTarif);
})();
</script>
@endpush
@endsection
