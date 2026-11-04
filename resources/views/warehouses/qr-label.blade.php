@extends('layouts.app')

@section('content')

{{-- ═══ SCREEN VIEW (no-print) ═══ --}}
<div class="no-print space-y-6">

    <x-page-header title="Cetak QR Label Gudang" subtitle="Pilih material, generate QR, cetak per lembar A4">
        <x-slot name="actions">
            <a href="{{ route('warehouses.show', $warehouse) }}" class="btn-secondary">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali
            </a>
            @if($selectedMaterials->isNotEmpty())
            <button onclick="window.print()" class="btn-primary">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.056 48.056 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z"/>
                </svg>
                Cetak {{ $selectedMaterials->count() }} Label
            </button>
            @endif
        </x-slot>
    </x-page-header>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- ─── PANEL KIRI: Pilih Material ─── --}}
        <div class="xl:col-span-1 space-y-4">
            <x-data-card title="Pilih Material" subtitle="Centang material untuk dicetak">
                <div class="px-5 pb-5">
                    {{-- Info gudang --}}
                    <div class="mb-4 rounded-xl bg-indigo-50 border border-indigo-100 px-4 py-3 flex items-center gap-3">
                        <div class="h-9 w-9 rounded-xl bg-indigo-600 flex items-center justify-center flex-shrink-0">
                            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m0 6.205l3 1m1.5.5-1.5-.5M6.75 7.364V3h-3v18m3-13.636l10.5-3.819"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-indigo-800">{{ $warehouse->name }}</p>
                            <p class="text-xs text-indigo-500">{{ $warehouse->code ?? 'Kode belum diset' }} · {{ $warehouse->location ?? '—' }}</p>
                        </div>
                    </div>

                    {{-- Form pilih material --}}
                    <form method="GET" action="{{ route('warehouses.qr-label', $warehouse) }}" id="selectForm">
                        <div class="space-y-1 max-h-96 overflow-y-auto pr-1">
                            @foreach($allMaterials as $mat)
                            <label class="flex items-center gap-3 px-3 py-2.5 rounded-xl cursor-pointer hover:bg-slate-50 transition-colors group {{ in_array($mat->id, $selectedIds) ? 'bg-indigo-50 border border-indigo-200' : 'border border-transparent' }}">
                                <input type="checkbox" name="material_ids[]" value="{{ $mat->id }}"
                                       {{ in_array($mat->id, $selectedIds) ? 'checked' : '' }}
                                       class="h-4 w-4 rounded text-indigo-600 border-slate-300 focus:ring-indigo-500"
                                       onchange="document.getElementById('selectForm').submit()">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-slate-700 truncate">{{ $mat->name }}</p>
                                    <p class="text-xs text-slate-400">{{ $mat->unit?->name ?? '—' }}</p>
                                </div>
                            </label>
                            @endforeach
                        </div>

                        @if($allMaterials->isEmpty())
                        <p class="text-center text-sm text-slate-400 py-4">Belum ada material aktif</p>
                        @endif
                    </form>
                </div>
            </x-data-card>

            {{-- Cara pakai --}}
            <x-data-card>
                <div class="px-5 py-4 space-y-2 text-sm text-slate-600">
                    <p class="font-semibold text-slate-700 mb-1">📋 Cara Pakai:</p>
                    <div class="flex gap-2"><span class="text-indigo-600 font-bold">1.</span><span>Centang material yang ingin dicetak</span></div>
                    <div class="flex gap-2"><span class="text-indigo-600 font-bold">2.</span><span>Preview label muncul di kanan</span></div>
                    <div class="flex gap-2"><span class="text-indigo-600 font-bold">3.</span><span>Klik "Cetak Label" → pilih printer/PDF</span></div>
                    <div class="flex gap-2"><span class="text-indigo-600 font-bold">4.</span><span>QR bisa di-scan HP untuk identifikasi material cepat di gudang</span></div>
                    <hr class="border-slate-100 my-2">
                    <p class="text-xs text-slate-400">📄 Layout: 2 label per baris, cetak di kertas A4</p>
                </div>
            </x-data-card>
        </div>

        {{-- ─── PANEL KANAN: Preview Label ─── --}}
        <div class="xl:col-span-2">
            <x-data-card title="Preview Label" subtitle="{{ $selectedMaterials->count() }} label siap cetak">
                <div class="px-5 pb-5">
                    @if($selectedMaterials->isEmpty())
                    <div class="flex flex-col items-center justify-center py-16 text-center">
                        <div class="h-16 w-16 rounded-2xl bg-slate-100 flex items-center justify-center mb-4">
                            <svg class="h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zm0 9.75c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zm9.75-9.75c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5z"/>
                            </svg>
                        </div>
                        <p class="font-semibold text-slate-600">Belum ada material dipilih</p>
                        <p class="text-sm text-slate-400 mt-1">Centang material di panel kiri untuk melihat preview label</p>
                    </div>
                    @else
                    <div class="grid grid-cols-2 gap-4">
                        @foreach($selectedMaterials as $mat)
                        @php $qrSrc = $qrImages[$mat->id] ?? ''; @endphp
                        <div class="label-card border-2 border-slate-200 rounded-2xl overflow-hidden bg-white shadow-sm">
                            {{-- Header strip --}}
                            <div class="bg-gradient-to-r from-indigo-700 to-indigo-600 px-4 py-2 flex items-center justify-between">
                                <span class="text-white text-xs font-bold tracking-widest uppercase">NOTAGO</span>
                                <span class="text-indigo-200 text-xs font-mono">{{ $warehouse->code ?? 'GDG-'.$warehouse->id }}</span>
                            </div>
                            {{-- Body --}}
                            <div class="p-4 flex gap-4 items-start">
                                {{-- QR --}}
                                <div class="flex-shrink-0">
                                    @if($qrSrc)
                                    <img src="{{ $qrSrc }}" alt="QR {{ $mat->name }}"
                                         class="w-24 h-24 rounded-lg border border-slate-100">
                                    @endif
                                </div>
                                {{-- Info --}}
                                <div class="flex-1 min-w-0">
                                    <p class="text-base font-bold text-slate-800 leading-tight">{{ $mat->name }}</p>
                                    <p class="text-xs text-slate-500 mt-0.5">Satuan: <strong>{{ $mat->unit?->name ?? '—' }}</strong></p>
                                    <div class="mt-2 pt-2 border-t border-slate-100">
                                        <p class="text-xs text-slate-400">Gudang</p>
                                        <p class="text-sm font-semibold text-slate-700">{{ $warehouse->name }}</p>
                                        @if($warehouse->location)
                                        <p class="text-xs text-slate-400">📍 {{ $warehouse->location }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            {{-- Footer --}}
                            <div class="bg-slate-50 border-t border-slate-100 px-4 py-2 flex items-center justify-between">
                                <span class="text-xs font-mono text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-full border border-indigo-100">
                                    {{ $warehouse->code ?? 'GDG-'.$warehouse->id }}
                                </span>
                                <span class="text-xs text-slate-400">ID: {{ $mat->id }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </x-data-card>
        </div>
    </div>
</div>

{{-- ═══ PRINT VIEW — hanya tampil saat cetak ═══ --}}
@if($selectedMaterials->isNotEmpty())
<div class="print-only">
    <div class="print-grid">
        @foreach($selectedMaterials as $mat)
        @php $qrSrc = $qrImages[$mat->id] ?? ''; @endphp
        <div class="print-label">
            {{-- Header --}}
            <div class="print-label-header">
                <span class="print-brand">NOTAGO</span>
                <span class="print-wcode">{{ $warehouse->code ?? 'GDG-'.$warehouse->id }}</span>
            </div>
            {{-- Body --}}
            <div class="print-label-body">
                {{-- QR code --}}
                <div class="print-qr-wrap">
                    @if($qrSrc)
                    <img src="{{ $qrSrc }}" alt="QR" class="print-qr-img">
                    @endif
                </div>
                {{-- Teks info --}}
                <div class="print-info">
                    <p class="print-mat-name">{{ $mat->name }}</p>
                    <p class="print-mat-unit">{{ $mat->unit?->name ?? '—' }}</p>
                    <div class="print-divider"></div>
                    <p class="print-label-key">Gudang</p>
                    <p class="print-wname">{{ $warehouse->name }}</p>
                    @if($warehouse->location)
                    <p class="print-wloc">{{ $warehouse->location }}</p>
                    @endif
                </div>
            </div>
            {{-- Footer --}}
            <div class="print-label-footer">
                <span class="print-code-badge">{{ $warehouse->code ?? 'GDG-'.$warehouse->id }}</span>
                <span class="print-mat-id">M-{{ str_pad($mat->id, 4, '0', STR_PAD_LEFT) }}</span>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

<style>
/* ─── SCREEN: sembunyikan print area ─── */
.print-only { display: none !important; }

/* ─── PRINT: sembunyikan screen area ─── */
@media print {
    .no-print,
    nav, aside, header,
    .topbar,
    .sidebar { display: none !important; }

    body, html {
        background: white !important;
        margin: 0;
        padding: 0;
    }

    .main-bg { background: white !important; }
    .content-shell {
        background: white !important;
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
        backdrop-filter: none !important;
    }

    .print-only { display: block !important; }

    .print-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10mm;
        padding: 10mm;
    }

    .print-label {
        border: 1.5px solid #c7d2fe;
        border-radius: 8px;
        overflow: hidden;
        page-break-inside: avoid;
        break-inside: avoid;
        background: white;
    }

    .print-label-header {
        background: #4338ca;
        color: white;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 5px 10px;
    }
    .print-brand {
        font-size: 9pt;
        font-weight: 800;
        letter-spacing: 2px;
        text-transform: uppercase;
    }
    .print-wcode {
        font-size: 8pt;
        font-family: monospace;
        color: #c7d2fe;
    }

    .print-label-body {
        display: flex;
        gap: 8px;
        padding: 8px 10px;
        align-items: flex-start;
    }

    .print-qr-wrap {
        flex-shrink: 0;
    }
    .print-qr-img {
        width: 22mm;
        height: 22mm;
        display: block;
        border: 1px solid #e2e8f0;
        border-radius: 4px;
    }

    .print-info {
        flex: 1;
        min-width: 0;
    }
    .print-mat-name {
        font-size: 11pt;
        font-weight: 700;
        color: #1e293b;
        margin: 0 0 1px;
        line-height: 1.2;
    }
    .print-mat-unit {
        font-size: 8pt;
        color: #64748b;
        margin: 0 0 4px;
    }
    .print-divider {
        border-top: 1px solid #e2e8f0;
        margin: 4px 0;
    }
    .print-label-key {
        font-size: 7pt;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: .05em;
        margin: 0;
    }
    .print-wname {
        font-size: 9pt;
        font-weight: 600;
        color: #334155;
        margin: 0;
    }
    .print-wloc {
        font-size: 7.5pt;
        color: #94a3b8;
        margin: 1px 0 0;
    }

    .print-label-footer {
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 4px 10px;
    }
    .print-code-badge {
        font-size: 8pt;
        font-family: monospace;
        color: #4338ca;
        background: #eef2ff;
        padding: 1px 6px;
        border-radius: 999px;
        border: 1px solid #c7d2fe;
    }
    .print-mat-id {
        font-size: 7.5pt;
        color: #94a3b8;
        font-family: monospace;
    }
}
</style>

@endsection
