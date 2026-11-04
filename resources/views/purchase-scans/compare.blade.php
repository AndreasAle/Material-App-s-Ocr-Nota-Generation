@extends('layouts.app')

@section('content')
@php
    $safeNoteNumber = $purchaseScanDraft->detected_note_number ?: 'Belum tersedia';
    $safeSupplier = $parsed['supplier_name'] ?? 'Belum terdeteksi';
    $lowConfItems = collect($items)->filter(fn($i) => ($i['confidence'] ?? 0) > 0 && ($i['confidence'] ?? 0) < 0.70)->count();
@endphp

{{-- Breadcrumb --}}
<nav class="flex items-center gap-1.5 text-xs text-slate-400 mb-4">
    <a href="{{ route('dashboard') }}" class="hover:text-slate-600 transition-colors">Dashboard</a>
    <svg class="h-3 w-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <a href="{{ route('purchase-transactions.index') }}" class="hover:text-slate-600 transition-colors">Transaksi</a>
    <svg class="h-3 w-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <a href="{{ route('purchase-scans.show', $purchaseScanDraft) }}" class="hover:text-slate-600 transition-colors">Scan Draft</a>
    <svg class="h-3 w-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-slate-700 font-semibold">Compare Mode</span>
</nav>

<form method="POST" action="{{ route('purchase-scans.compare.update', $purchaseScanDraft) }}" id="compare-form">
    @csrf
    @method('PUT')

    {{-- ═══════════════════════════════════════
         COMMAND BAR — Compact Sticky (stays visible while scrolling)
         sticky top-16 = 64px below fixed app navbar
    ═══════════════════════════════════════ --}}
    <div id="compare-command-bar"
         class="sticky top-16 z-30 bg-white border border-indigo-200 rounded-2xl shadow-md mb-5 overflow-hidden">
        <div class="h-1 w-full bg-gradient-to-r from-indigo-500 via-violet-500 to-indigo-400"></div>
        <div class="px-4 py-2.5 flex flex-wrap items-center gap-3">

            {{-- Identity chip --}}
            <div class="flex items-center gap-2.5 flex-1 min-w-0">
                <div class="flex-shrink-0 w-8 h-8 rounded-xl bg-indigo-600 flex items-center justify-center">
                    <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/>
                    </svg>
                </div>
                <div class="flex items-center gap-2 flex-wrap min-w-0">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-indigo-100 border border-indigo-200 text-[11px] font-bold text-indigo-700 uppercase tracking-wide flex-shrink-0">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 animate-pulse"></span>Compare
                    </span>
                    <span class="text-sm font-bold text-slate-800 hidden sm:inline">Review Scan OCR</span>
                    <span class="text-xs text-slate-400 hidden md:inline font-mono">{{ $safeNoteNumber }}</span>
                    <span class="hidden md:inline text-slate-300">|</span>
                    <span class="text-xs text-slate-500 hidden md:inline">{{ $safeSupplier }}</span>
                    <span class="inline-flex items-center gap-1 bg-indigo-100 border border-indigo-200 rounded-lg px-2 py-0.5 text-xs flex-shrink-0">
                        <span class="text-indigo-600 font-medium">Item:</span>
                        <span class="font-black text-indigo-800" id="item-count-badge">{{ count($items) }}</span>
                    </span>
                    <span class="inline-flex items-center gap-1 bg-emerald-100 border border-emerald-200 rounded-lg px-2 py-0.5 text-xs flex-shrink-0">
                        <span class="text-emerald-600 font-medium">Total:</span>
                        <span class="font-black text-emerald-800 tabular-nums" id="compare-grand-total-top">Rp {{ number_format((int) $grandTotal, 0, ',', '.') }}</span>
                    </span>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center gap-1.5 flex-shrink-0">
                <a href="{{ route('purchase-scans.show', $purchaseScanDraft) }}"
                    class="inline-flex items-center gap-1 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition-all shadow-sm">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                    Kembali
                </a>
                <button type="button" id="reset-all-btn"
                    class="inline-flex items-center gap-1 rounded-xl border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700 hover:bg-amber-100 transition-all shadow-sm">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7"/></svg>
                    Reset
                </button>
                <button type="submit"
                    class="inline-flex items-center gap-1 rounded-xl bg-indigo-600 px-3 py-1.5 text-xs font-bold text-white shadow-sm hover:bg-indigo-700 transition-all">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                    Simpan
                </button>
                @if(count($items) > 0)
                <button type="submit"
                    formaction="{{ route('purchase-scans.import', $purchaseScanDraft) }}"
                    formmethod="POST"
                    class="inline-flex items-center gap-1 rounded-xl bg-emerald-600 px-3 py-1.5 text-xs font-bold text-white shadow-sm hover:bg-emerald-700 transition-all">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    Import Transaksi
                </button>
                @endif
            </div>
        </div>
    </div>

    {{-- Low Confidence Warning --}}
    @if($lowConfItems > 0)
    <div class="flex items-start gap-3 p-4 rounded-2xl bg-amber-50 border border-amber-300 mb-5">
        <div class="flex-shrink-0 w-8 h-8 bg-amber-100 rounded-xl flex items-center justify-center">
            <svg class="h-4 w-4 text-amber-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-bold text-amber-800">{{ $lowConfItems }} item dengan confidence rendah (&lt; 70%)</p>
            <p class="text-xs text-amber-700 mt-0.5 leading-relaxed">
                Item yang ditandai badge <span class="font-semibold bg-amber-200 px-1 rounded">Sedang</span> atau <span class="font-semibold bg-rose-200 px-1 rounded">Rendah</span> berpotensi salah baca OCR. Periksa dengan seksama sebelum menyimpan.
            </p>
        </div>
    </div>
    @endif

    {{-- ═══════════════════════════════════════
         MAIN CONTENT: Left (Form) + Right (Note Preview)
    ═══════════════════════════════════════ --}}
    <div class="grid grid-cols-1 gap-5 xl:grid-cols-12 xl:items-start">

        {{-- ===== KIRI: Hasil Scan Editable ===== --}}
        <div class="min-w-0 xl:col-span-7">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

                {{-- Table Header --}}
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between gap-4 bg-slate-50">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg bg-indigo-100 flex items-center justify-center flex-shrink-0">
                            <svg class="h-3.5 w-3.5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-800">Hasil Scan — Dapat Diedit</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Koreksi data yang tidak sesuai dengan nota asli</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <span class="text-xs text-slate-500">Total:</span>
                        <span class="text-sm font-black text-emerald-700 tabular-nums bg-emerald-50 border border-emerald-200 px-2.5 py-1 rounded-lg" id="compare-grand-total-side">Rp {{ number_format((int) $grandTotal, 0, ',', '.') }}</span>
                    </div>
                </div>

                {{-- Items --}}
                <div id="items-container" class="divide-y divide-slate-100">
                    @foreach($items as $index => $item)
                    @php
                        $conf = (float) ($item['confidence'] ?? 0);
                        $confBadgeClass = $conf >= 0.85
                            ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                            : ($conf >= 0.65
                                ? 'border-amber-200 bg-amber-50 text-amber-700'
                                : ($conf > 0
                                    ? 'border-rose-200 bg-rose-50 text-rose-700'
                                    : 'border-slate-200 bg-slate-50 text-slate-500'));
                        $confDot = $conf >= 0.85 ? 'bg-emerald-500' : ($conf >= 0.65 ? 'bg-amber-400' : ($conf > 0 ? 'bg-rose-400' : 'bg-slate-300'));
                        $confLabel = $conf >= 0.85 ? 'Tinggi' : ($conf >= 0.65 ? 'Sedang' : ($conf > 0 ? 'Rendah' : '-'));
                        $isLearnt = ($item['match_source'] ?? 'fuzzy') === 'learnt';
                    @endphp

                    <div class="compare-row p-5 transition-opacity" data-row="{{ $index }}">

                        {{-- Hidden fields --}}
                        <input type="hidden" name="items[{{ $index }}][_deleted]" value="0" class="delete-flag">
                        <input type="hidden" name="items[{{ $index }}][_original_nama]" value="{{ $item['nama_material'] }}">
                        <input type="hidden" name="items[{{ $index }}][_match_material_id]" value="{{ $item['match_material_id'] ?? '' }}">

                        {{-- Row Header --}}
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between mb-4">
                            <div class="flex items-start gap-2.5">
                                {{-- Row number --}}
                                <div class="flex-shrink-0 w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center text-xs font-black text-slate-600">
                                    {{ $index + 1 }}
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap mb-1">
                                        <span class="text-sm font-bold text-slate-800">Baris {{ $index + 1 }}</span>
                                        {{-- Confidence badge --}}
                                        @if($conf > 0)
                                        <span class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-[11px] font-semibold {{ $confBadgeClass }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $confDot }}"></span>
                                            Conf: {{ number_format($conf, 2) }} ({{ $confLabel }})
                                        </span>
                                        @else
                                        <span class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-[11px] font-semibold text-slate-400">
                                            Conf: —
                                        </span>
                                        @endif
                                        @if($isLearnt)
                                        <span class="inline-flex items-center gap-1 rounded-full border border-indigo-200 bg-indigo-50 px-2 py-0.5 text-[11px] font-semibold text-indigo-700">
                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5"/></svg>
                                            Dipelajari
                                        </span>
                                        @endif
                                    </div>
                                    {{-- Raw OCR text --}}
                                    @if(!empty($item['raw_line']))
                                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1.5 text-[11px] leading-5 text-slate-400 font-mono">
                                        {{ $item['raw_line'] }}
                                    </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Row Actions --}}
                            <div class="flex items-center gap-1.5 flex-shrink-0">
                                <button type="button"
                                    class="reset-row inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-[11px] font-semibold text-slate-600 hover:bg-slate-50 hover:border-slate-300 transition-all">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                                    </svg>
                                    Reset
                                </button>
                                <button type="button"
                                    class="delete-row inline-flex items-center gap-1 rounded-lg border border-rose-200 bg-rose-50 px-2.5 py-1.5 text-[11px] font-semibold text-rose-700 hover:bg-rose-100 transition-all">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Hapus
                                </button>
                            </div>
                        </div>

                        {{-- Form Fields --}}
                        <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-4 space-y-3">

                            {{-- Row 1: Kode + Nama Material --}}
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-6">
                                <div class="sm:col-span-2">
                                    <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-1.5">Kode Material</label>
                                    <input type="text" name="items[{{ $index }}][kode]"
                                        value="{{ $item['kode'] }}" data-original="{{ $item['kode'] }}"
                                        class="compare-input h-10 w-full rounded-xl border border-emerald-300 bg-emerald-50 px-3 text-sm font-semibold text-slate-700 outline-none transition focus:border-indigo-400 focus:bg-white focus:ring-4 focus:ring-indigo-100">
                                </div>
                                <div class="sm:col-span-4">
                                    <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-1.5">Nama Material</label>
                                    <input type="text" name="items[{{ $index }}][nama_material]"
                                        value="{{ $item['nama_material'] }}" data-original="{{ $item['nama_material'] }}"
                                        class="compare-input h-10 w-full rounded-xl border border-emerald-300 bg-emerald-50 px-3 text-sm font-semibold text-slate-700 outline-none transition focus:border-indigo-400 focus:bg-white focus:ring-4 focus:ring-indigo-100">
                                </div>
                            </div>

                            {{-- Row 2: Satuan + Qty + Harga + Subtotal --}}
                            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                                <div>
                                    <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-1.5">Satuan</label>
                                    <input type="text" name="items[{{ $index }}][satuan]"
                                        value="{{ $item['satuan'] }}" data-original="{{ $item['satuan'] }}"
                                        class="compare-input h-10 w-full rounded-xl border border-emerald-300 bg-emerald-50 px-3 text-sm font-semibold text-slate-700 outline-none transition focus:border-indigo-400 focus:bg-white focus:ring-4 focus:ring-indigo-100">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-1.5">Qty</label>
                                    <input type="text" name="items[{{ $index }}][qty]"
                                        value="{{ rtrim(rtrim(number_format((float) $item['qty'], 2, '.', ''), '0'), '.') }}"
                                        data-original="{{ rtrim(rtrim(number_format((float) $item['qty'], 2, '.', ''), '0'), '.') }}"
                                        class="compare-input compare-qty h-10 w-full rounded-xl border border-emerald-300 bg-emerald-50 px-3 text-right text-sm font-black text-slate-700 outline-none tabular-nums transition focus:border-indigo-400 focus:bg-white focus:ring-4 focus:ring-indigo-100">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-1.5">Harga</label>
                                    <input type="text" name="items[{{ $index }}][harga]"
                                        value="{{ (int) $item['harga'] }}" data-original="{{ (int) $item['harga'] }}"
                                        class="compare-input compare-harga h-10 w-full rounded-xl border border-emerald-300 bg-emerald-50 px-3 text-right text-sm font-black text-slate-700 outline-none tabular-nums transition focus:border-indigo-400 focus:bg-white focus:ring-4 focus:ring-indigo-100">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-1.5">Subtotal</label>
                                    <input type="text" name="items[{{ $index }}][subtotal]"
                                        value="{{ (int) $item['subtotal'] }}" data-original="{{ (int) $item['subtotal'] }}"
                                        class="compare-input compare-subtotal h-10 w-full rounded-xl border border-emerald-300 bg-emerald-50 px-3 text-right text-sm font-black text-emerald-700 outline-none tabular-nums transition focus:border-indigo-400 focus:bg-white focus:ring-4 focus:ring-indigo-100">
                                </div>
                            </div>

                            {{-- Row 3: Status + Legend --}}
                            <div class="flex items-center justify-between pt-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Status:</span>
                                    <span class="compare-status text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-1 rounded-full">Sinkron</span>
                                </div>
                                <p class="text-[11px] text-slate-400 leading-relaxed hidden sm:block">
                                    <span class="text-emerald-600 font-semibold">Hijau</span> = sama dengan draft &bull;
                                    <span class="text-rose-600 font-semibold">Merah</span> = ada perubahan &bull;
                                    Koreksi nama akan <span class="font-semibold text-slate-600">diingat sistem</span>
                                </p>
                            </div>
                        </div>

                        {{-- Deleted Overlay --}}
                        <div class="deleted-overlay hidden mt-3 flex items-center justify-between rounded-xl border border-rose-200 bg-rose-50 px-4 py-3">
                            <div class="flex items-center gap-2 text-sm font-semibold text-rose-700">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7"/></svg>
                                Baris ini akan dihapus saat disimpan
                            </div>
                            <button type="button" class="undo-delete text-xs font-bold text-rose-700 underline hover:text-rose-900 transition-colors">
                                Batalkan Hapus
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Footer --}}
                <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-xs text-slate-400">
                        <span class="font-semibold text-emerald-600">Hijau</span> = data asli &bull;
                        <span class="font-semibold text-rose-600">Merah</span> = sudah diubah
                    </span>
                    <span class="text-xs text-slate-400" id="item-count-badge-footer"></span>
                </div>
            </div>
        </div>

        {{-- ===== KANAN: Dokumen Nota Preview ===== --}}
        <div class="min-w-0 xl:col-span-5">
            <div id="note-fixed-placeholder" class="hidden xl:block"></div>
            <div id="note-fixed-box" class="compare-note-box xl:fixed xl:z-20">
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    {{-- Preview Header --}}
                    <div class="px-4 py-3.5 border-b border-slate-200 bg-slate-50">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg bg-slate-200 flex items-center justify-center flex-shrink-0">
                                    <svg class="h-3.5 w-3.5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-700">Dokumen Nota Asli</p>
                                    <p class="text-xs text-slate-400">Referensi untuk verifikasi</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" id="toggle-sticky-mode"
                                    class="inline-flex items-center gap-1 rounded-lg border border-indigo-200 bg-indigo-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-indigo-700 transition-all whitespace-nowrap">
                                    Nonaktifkan
                                </button>
                                @if($previewUrl)
                                <a href="{{ $previewUrl }}" target="_blank"
                                    class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition-all whitespace-nowrap">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                                    Buka
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Preview Body --}}
                    <div class="compare-note-body bg-slate-100 p-3">
                        @if($previewUrl && $isImage)
                        <img src="{{ $previewUrl }}" alt="Preview nota compare"
                            class="mx-auto block h-auto w-full rounded-xl border border-slate-200 bg-white shadow-sm">
                        @elseif($previewUrl && $isPdf)
                        <iframe src="{{ $previewUrl }}"
                            class="compare-note-frame w-full rounded-xl border border-slate-200 bg-white"></iframe>
                        @else
                        <div class="flex min-h-[360px] items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white px-6 py-10 text-center">
                            <div>
                                <div class="mx-auto mb-3 w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center">
                                    <svg class="h-6 w-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                </div>
                                <p class="text-sm font-semibold text-slate-600">Preview nota belum tersedia</p>
                                <p class="text-xs text-slate-400 mt-1">File preview tidak dapat dimuat</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const compareRows = document.querySelectorAll('.compare-row');
    const grandTop = document.getElementById('compare-grand-total-top');
    const grandSide = document.getElementById('compare-grand-total-side');
    const itemCountBadge = document.getElementById('item-count-badge');

    function toNumber(value) {
        if (value === null || value === undefined) return 0;
        let str = String(value).trim().replace(/[^0-9,.\-]/g, '');
        if (!str) return 0;

        const hasDot = str.includes('.');
        const hasComma = str.includes(',');

        if (hasDot && hasComma) { str = str.replace(/\./g, '').replace(',', '.'); return Number(str) || 0; }

        if (hasDot && !hasComma) {
            const parts = str.split('.');
            if (parts.length > 1) {
                let allThree = true;
                for (let i = 1; i < parts.length; i++) { if (parts[i].length !== 3) { allThree = false; break; } }
                if (allThree) return Number(parts.join('')) || 0;
            }
        }

        if (hasComma && !hasDot) { str = str.replace(',', '.'); return Number(str) || 0; }

        return Number(str) || 0;
    }

    function formatRupiah(n) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(Number(n || 0)); }
    function normalizeVal(v) { return String(v ?? '').trim(); }

    function paintField(input, changed) {
        input.classList.remove('border-emerald-300', 'bg-emerald-50', 'border-rose-300', 'bg-rose-50');
        input.classList.add(changed ? 'border-rose-300' : 'border-emerald-300', changed ? 'bg-rose-50' : 'bg-emerald-50');
    }

    function refreshRow(row) {
        if (row.dataset.deleted === '1') return;
        const fields = row.querySelectorAll('.compare-input');
        let hasChanged = false;
        fields.forEach(function (input) {
            const changed = normalizeVal(input.value) !== normalizeVal(input.dataset.original || '');
            paintField(input, changed);
            if (changed) hasChanged = true;
        });
        const statusEl = row.querySelector('.compare-status');
        if (statusEl) {
            statusEl.textContent = hasChanged ? 'Ada Perubahan' : 'Sinkron';
            statusEl.classList.remove('text-emerald-700', 'bg-emerald-50', 'border-emerald-200', 'text-rose-700', 'bg-rose-50', 'border-rose-200');
            if (hasChanged) {
                statusEl.classList.add('text-rose-700', 'bg-rose-50', 'border-rose-200');
            } else {
                statusEl.classList.add('text-emerald-700', 'bg-emerald-50', 'border-emerald-200');
            }
        }
    }

    function refreshGrandTotal() {
        let total = 0;
        let activeCount = 0;
        compareRows.forEach(function (row) {
            if (row.dataset.deleted === '1') return;
            activeCount++;
            const subtotalInput = row.querySelector('.compare-subtotal');
            const qtyInput = row.querySelector('.compare-qty');
            const hargaInput = row.querySelector('.compare-harga');
            let subtotal = toNumber(subtotalInput?.value || 0);
            if (subtotal <= 0) {
                const qty = toNumber(qtyInput?.value || 0);
                const harga = toNumber(hargaInput?.value || 0);
                subtotal = qty * harga;
            }
            total += subtotal;
        });
        const formatted = formatRupiah(total);
        if (grandTop) grandTop.textContent = formatted;
        if (grandSide) grandSide.textContent = formatted;
        if (itemCountBadge) itemCountBadge.textContent = activeCount;
    }

    compareRows.forEach(function (row) {
        row.querySelectorAll('.compare-input').forEach(function (input) {
            input.addEventListener('input', function () { refreshRow(row); refreshGrandTotal(); });
        });

        const deleteBtn = row.querySelector('.delete-row');
        const undoBtn = row.querySelector('.undo-delete');
        const overlay = row.querySelector('.deleted-overlay');
        const deleteFlag = row.querySelector('.delete-flag');

        if (deleteBtn) {
            deleteBtn.addEventListener('click', function () {
                row.dataset.deleted = '1';
                if (deleteFlag) deleteFlag.value = '1';
                row.querySelectorAll('.compare-input').forEach(i => i.disabled = true);
                row.style.opacity = '0.4';
                if (overlay) overlay.classList.remove('hidden');
                refreshGrandTotal();
            });
        }

        if (undoBtn) {
            undoBtn.addEventListener('click', function () {
                row.dataset.deleted = '0';
                if (deleteFlag) deleteFlag.value = '0';
                row.querySelectorAll('.compare-input').forEach(i => i.disabled = false);
                row.style.opacity = '1';
                if (overlay) overlay.classList.add('hidden');
                refreshRow(row);
                refreshGrandTotal();
            });
        }

        const resetBtn = row.querySelector('.reset-row');
        if (resetBtn) {
            resetBtn.addEventListener('click', function () {
                row.querySelectorAll('.compare-input').forEach(function (input) {
                    input.value = input.dataset.original || '';
                });
                refreshRow(row);
                refreshGrandTotal();
            });
        }

        refreshRow(row);
    });

    const resetAllBtn = document.getElementById('reset-all-btn');
    if (resetAllBtn) {
        resetAllBtn.addEventListener('click', function () {
            compareRows.forEach(function (row) {
                if (row.dataset.deleted === '1') return;
                row.querySelectorAll('.compare-input').forEach(function (input) {
                    input.value = input.dataset.original || '';
                });
                refreshRow(row);
            });
            refreshGrandTotal();
        });
    }

    refreshGrandTotal();
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const box = document.getElementById('note-fixed-box');
    const placeholder = document.getElementById('note-fixed-placeholder');
    const toggleBtn = document.getElementById('toggle-sticky-mode');
    if (!box || !placeholder || !toggleBtn) return;

    const desktopQuery = window.matchMedia('(min-width: 1280px)');
    // 64px (fixed app navbar) + ~64px (sticky command bar) + 7px gap = 135
    const topOffset = 135;
    const storageKey = 'compare_note_sticky_mode';
    let stickyEnabled = localStorage.getItem(storageKey) !== '0';

    function updateToggleButton() {
        if (stickyEnabled) {
            toggleBtn.textContent = 'Nonaktifkan Mode Sticky';
            toggleBtn.classList.remove('border-slate-300', 'bg-white', 'text-slate-700');
            toggleBtn.classList.add('border-indigo-200', 'bg-indigo-600', 'text-white');
        } else {
            toggleBtn.textContent = 'Aktifkan Mode Sticky';
            toggleBtn.classList.remove('border-indigo-200', 'bg-indigo-600', 'text-white');
            toggleBtn.classList.add('border-slate-300', 'bg-white', 'text-slate-700');
        }
    }

    function resetNoteBox() {
        placeholder.style.height = '';
        box.style.top = box.style.left = box.style.width = box.style.position = '';
    }

    function applySticky() {
        if (!desktopQuery.matches) { resetNoteBox(); box.classList.remove('is-sticky-active', 'is-sticky-disabled'); return; }
        const col = box.parentElement;
        if (!col) return;
        const rect = col.getBoundingClientRect();
        placeholder.style.height = box.offsetHeight + 'px';
        box.style.position = 'fixed';
        box.style.top = topOffset + 'px';
        box.style.left = rect.left + 'px';
        box.style.width = rect.width + 'px';
        box.classList.add('is-sticky-active');
        box.classList.remove('is-sticky-disabled');
    }

    function renderNoteMode() {
        if (stickyEnabled) { applySticky(); } else { resetNoteBox(); box.classList.remove('is-sticky-active'); box.classList.add('is-sticky-disabled'); }
        updateToggleButton();
    }

    let ticking = false;
    function requestTick() {
        if (ticking) return; ticking = true;
        requestAnimationFrame(() => { renderNoteMode(); ticking = false; });
    }

    toggleBtn.addEventListener('click', function () {
        stickyEnabled = !stickyEnabled;
        localStorage.setItem(storageKey, stickyEnabled ? '1' : '0');
        renderNoteMode();
    });

    window.addEventListener('resize', requestTick);
    window.addEventListener('scroll', requestTick, { passive: true });
    desktopQuery.addEventListener('change', requestTick);
    setTimeout(renderNoteMode, 50);
    setTimeout(renderNoteMode, 300);
});
</script>

<style>
    .compare-note-box { width: 100%; transition: all 0.2s ease; }
    .compare-note-box.is-sticky-disabled { position: static !important; top: auto !important; left: auto !important; width: 100% !important; }
    @media (min-width: 1280px) {
        .compare-note-body { max-height: calc(100vh - 140px); overflow: auto; }
        .compare-note-box.is-sticky-disabled .compare-note-body { max-height: none; overflow: visible; }
        .compare-note-frame { display: block; width: 100%; min-height: 75vh; height: 1200px; }
        .compare-note-box.is-sticky-disabled .compare-note-frame { min-height: 900px; height: 900px; }
    }
</style>

@endsection
