@extends('layouts.app')

@section('content')
@php
    $firstPreview = $filesForView[0] ?? null;
    $lowConfItems = collect($items)->filter(fn($i) => ($i['confidence'] ?? 0) > 0 && ($i['confidence'] ?? 0) < 0.70)->count();
@endphp

<x-page-header
    title="Mode Compare Multiple Notes"
    subtitle="Bandingkan hasil scan gabungan dengan nota asli per file, koreksi data, lalu simpan review."
/>

<form method="POST" action="{{ route('purchase-scan-batches.compare.update', $purchaseScanBatch) }}">
    @csrf
    @method('PUT')

    <div class="space-y-6">
        <div class="rounded-[28px] border border-indigo-200 bg-indigo-50/60 shadow-sm">
            <div class="flex flex-col gap-5 px-6 py-6 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="inline-flex rounded-full border border-indigo-200 bg-white px-3 py-1 text-xs font-semibold uppercase tracking-wide text-indigo-700">
                        Compare Mode
                    </div>

                    <h2 class="mt-3 text-xl font-bold tracking-tight text-slate-900">
                        Review Perbandingan Multiple Notes
                    </h2>

                    <div class="mt-4 flex flex-wrap gap-2">
                        <div class="rounded-full border border-white/70 bg-white/80 px-3 py-1.5 text-xs font-medium text-slate-700">
                            Batch: <span class="font-semibold text-slate-900">{{ $safeBatchCode }}</span>
                        </div>
                        <div class="rounded-full border border-white/70 bg-white/80 px-3 py-1.5 text-xs font-medium text-slate-700">
                            Supplier: <span class="font-semibold text-slate-900">{{ $safeSupplier }}</span>
                        </div>
                        <div class="rounded-full border border-white/70 bg-white/80 px-3 py-1.5 text-xs font-medium text-slate-700">
                            Nota: <span class="font-semibold text-slate-900">{{ count($filesForView) }}</span>
                        </div>
                        <div class="rounded-full border border-white/70 bg-white/80 px-3 py-1.5 text-xs font-medium text-slate-700">
                            Item: <span class="font-semibold text-slate-900">{{ count($items) }}</span>
                        </div>
                        <div class="rounded-full border border-white/70 bg-white/80 px-3 py-1.5 text-xs font-medium text-slate-700">
                            Grand Total: <span class="font-semibold text-slate-900" id="compare-grand-total-top">Rp {{ number_format((int) $grandTotal, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('purchase-scan-batches.show', $purchaseScanBatch) }}"
                        class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        Kembali
                    </a>
                    <button type="button" id="reset-all-btn"
                        class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        Reset Semua
                    </button>
                    <button type="submit"
                        class="inline-flex items-center justify-center rounded-2xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700">
                        Simpan Compare
                    </button>
                </div>



            </div>
        </div>

        {{-- Low confidence warning --}}
        @if($lowConfItems > 0)
            <div class="rounded-[20px] border border-amber-200 bg-amber-50 px-5 py-4">
                <div class="flex items-start gap-3">
                    <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.29 3.86l-7.1 12.3A2 2 0 004.91 19h14.18a2 2 0 001.72-2.84l-7.09-12.3a2 2 0 00-3.43 0z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-amber-800">{{ $lowConfItems }} item dengan confidence rendah (&lt; 70%)</div>
                        <p class="mt-0.5 text-xs leading-5 text-amber-700">Item dengan badge <span class="font-semibold">Rendah</span> atau <span class="font-semibold">Sedang</span> perlu diperiksa manual.</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-12 xl:items-start">
            <div class="min-w-0 xl:col-span-7">
                <div class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 via-white to-slate-50 px-6 py-5">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900">Hasil Scan Editable Gabungan</h3>
                            </div>

                            <div class="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-sm font-semibold text-slate-700">
                                Total:
                                <span class="text-slate-900" id="compare-grand-total-side">Rp {{ number_format((int) $grandTotal, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="divide-y divide-slate-100" id="items-container">
                            @foreach($items as $index => $item)
                            @php
                                $conf = (float) ($item['confidence'] ?? 0);
                                $confBadgeClass = $conf >= 0.85 ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                                                : ($conf >= 0.65 ? 'border-amber-200 bg-amber-50 text-amber-700'
                                                : ($conf > 0    ? 'border-rose-200 bg-rose-50 text-rose-700'
                                                                 : 'border-slate-200 bg-slate-50 text-slate-500'));
                                $confLabel = $conf >= 0.85 ? 'Tinggi' : ($conf >= 0.65 ? 'Sedang' : ($conf > 0 ? 'Rendah' : '-'));
                                $matchArr = is_array($item['match'] ?? null) ? $item['match'] : [];
                                $isLearnt = ($matchArr['source'] ?? 'fuzzy') === 'learnt';
                            @endphp
                                <div class="p-5 compare-row" data-row="{{ $index }}" data-file-id="{{ $item['_source_file_id'] ?? '' }}">

                                    {{-- Hidden tracking fields --}}
                                    <input type="hidden" name="items[{{ $index }}][_deleted]" value="0" class="delete-flag">
                                    <input type="hidden" name="items[{{ $index }}][_original_nama]" value="{{ $item['nama_material'] ?? '' }}">
                                    <input type="hidden" name="items[{{ $index }}][_match_material_id]" value="{{ $matchArr['material_id'] ?? '' }}">

                                    <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <div class="text-sm font-bold text-slate-900">Baris {{ $index + 1 }}</div>
                                                @if($isLearnt)
                                                    <span class="rounded-full border border-indigo-200 bg-indigo-50 px-2 py-0.5 text-[10px] font-semibold text-indigo-700">Koreksi Dipelajari</span>
                                                @endif
                                            </div>
                                            @if(!empty($item['raw_line']))
                                                <div class="mt-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-[11px] leading-5 text-slate-500">
                                                    Teks sumber: {{ $item['raw_line'] }}
                                                </div>
                                            @endif
                                            <div class="mt-2 inline-flex rounded-full border border-indigo-200 bg-indigo-50 px-3 py-1 text-[11px] font-semibold text-indigo-700">
                                                Sumber Nota: {{ $item['_source_file_name'] ?? '-' }}
                                            </div>
                                        </div>

                                        <div class="flex flex-wrap items-center gap-2">
                                            <button type="button" class="jump-to-note inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                                                data-file-id="{{ $item['_source_file_id'] ?? '' }}">
                                                Lihat Nota Sumber
                                            </button>
                                            <div class="rounded-full border px-2.5 py-1 text-[11px] font-medium {{ $confBadgeClass }}">
                                                @if($conf > 0) Conf: {{ number_format($conf, 2) }} ({{ $confLabel }}) @else Conf: - @endif
                                            </div>
                                            <button type="button" class="reset-row inline-flex items-center gap-1 rounded-xl border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-600 hover:bg-slate-50 transition">
                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                Reset
                                            </button>
                                            <button type="button" class="delete-row inline-flex items-center gap-1 rounded-xl border border-rose-200 bg-rose-50 px-2.5 py-1 text-[11px] font-semibold text-rose-700 hover:bg-rose-100 transition">
                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                Hapus
                                            </button>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-6">
                                        <div class="xl:col-span-2">
                                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Kode</label>
                                            <input
                                                type="text"
                                                name="items[{{ $index }}][kode]"
                                                value="{{ $item['kode'] ?? '' }}"
                                                data-original="{{ $item['kode'] ?? '' }}"
                                                class="compare-input h-11 w-full rounded-xl border border-emerald-300 bg-emerald-50 px-3 text-sm font-medium text-slate-700 outline-none transition focus:border-indigo-300 focus:bg-white focus:ring-4 focus:ring-indigo-100"
                                            >
                                        </div>

                                        <div class="xl:col-span-4">
                                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Nama Material</label>
                                            <input
                                                type="text"
                                                name="items[{{ $index }}][nama_material]"
                                                value="{{ $item['nama_material'] ?? '' }}"
                                                data-original="{{ $item['nama_material'] ?? '' }}"
                                                class="compare-input h-11 w-full rounded-xl border border-emerald-300 bg-emerald-50 px-3 text-sm font-medium text-slate-700 outline-none transition focus:border-indigo-300 focus:bg-white focus:ring-4 focus:ring-indigo-100"
                                                required
                                            >
                                        </div>

                                        <div>
                                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Satuan</label>
                                            <input
                                                type="text"
                                                name="items[{{ $index }}][satuan]"
                                                value="{{ $item['satuan'] ?? '' }}"
                                                data-original="{{ $item['satuan'] ?? '' }}"
                                                class="compare-input h-11 w-full rounded-xl border border-emerald-300 bg-emerald-50 px-3 text-sm font-medium text-slate-700 outline-none transition focus:border-indigo-300 focus:bg-white focus:ring-4 focus:ring-indigo-100"
                                            >
                                        </div>

                                        <div>
                                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Qty</label>
                                            <input
                                                type="text"
                                                name="items[{{ $index }}][qty]"
                                                value="{{ rtrim(rtrim(number_format((float) ($item['qty'] ?? 0), 2, '.', ''), '0'), '.') }}"
                                                data-original="{{ rtrim(rtrim(number_format((float) ($item['qty'] ?? 0), 2, '.', ''), '0'), '.') }}"
                                                class="compare-input compare-qty h-11 w-full rounded-xl border border-emerald-300 bg-emerald-50 px-3 text-right text-sm font-semibold text-slate-700 outline-none transition focus:border-indigo-300 focus:bg-white focus:ring-4 focus:ring-indigo-100"
                                                required
                                            >
                                        </div>

                                        <div>
                                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Harga</label>
                                            <input
                                                type="text"
                                                name="items[{{ $index }}][harga]"
                                                value="{{ (int) ($item['harga'] ?? 0) }}"
                                                data-original="{{ (int) ($item['harga'] ?? 0) }}"
                                                class="compare-input compare-harga h-11 w-full rounded-xl border border-emerald-300 bg-emerald-50 px-3 text-right text-sm font-semibold text-slate-700 outline-none transition focus:border-indigo-300 focus:bg-white focus:ring-4 focus:ring-indigo-100"
                                                required
                                            >
                                        </div>

                                        <div>
                                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Subtotal</label>
                                            <input
                                                type="text"
                                                name="items[{{ $index }}][subtotal]"
                                                value="{{ (int) ($item['subtotal'] ?? 0) }}"
                                                data-original="{{ (int) ($item['subtotal'] ?? 0) }}"
                                                class="compare-input compare-subtotal h-11 w-full rounded-xl border border-emerald-300 bg-emerald-50 px-3 text-right text-sm font-semibold text-slate-700 outline-none transition focus:border-indigo-300 focus:bg-white focus:ring-4 focus:ring-indigo-100"
                                            >
                                        </div>

                                        <div class="md:col-span-2 xl:col-span-2">
                                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                                <div class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500">Status Compare</div>
                                                <div class="mt-2 compare-status text-sm font-semibold text-emerald-700">
                                                    Sinkron
                                                </div>
                                            </div>
                                        </div>

                                        <div class="md:col-span-2 xl:col-span-4">
                                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs leading-6 text-slate-500">
                                                Item ini dari file <span class="font-semibold text-slate-700">{{ $item['_source_file_name'] ?? '-' }}</span>. Koreksi nama material akan <span class="font-semibold text-slate-700">diingat sistem</span> untuk scan berikutnya.
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Deleted overlay --}}
                                    <div class="deleted-overlay hidden mt-3 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
                                        Baris ini akan dihapus saat disimpan.
                                        <button type="button" class="undo-delete ml-2 underline text-rose-700 hover:text-rose-900">Batalkan</button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="min-w-0 xl:col-span-5 xl:mt-[0px]">
                <div id="note-fixed-placeholder" class="hidden xl:block"></div>

                <div id="note-fixed-box" class="compare-note-box xl:fixed xl:z-20">
                    <div class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm">
                        <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 via-white to-slate-50 px-6 py-5">
                            <div class="flex flex-col gap-3">
                                <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                                    <div class="min-w-0">
                                        <h3 class="text-lg font-semibold text-slate-900">Dokumen Nota</h3>
                                    </div>

                                    <div class="flex items-center gap-3 xl:shrink-0">
                                        <button
                                            type="button"
                                            id="toggle-sticky-mode"
                                            class="inline-flex items-center justify-center rounded-xl border border-indigo-200 bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700 whitespace-nowrap"
                                        >
                                            Nonaktifkan
                                        </button>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                    @foreach($filesForView as $file)
                                        <button
                                            type="button"
                                            class="note-tab rounded-2xl border px-4 py-3 text-left transition {{ $loop->first ? 'border-indigo-300 bg-indigo-50' : 'border-slate-200 bg-white hover:bg-slate-50' }}"
                                            data-file-id="{{ $file['id'] }}"
                                            data-preview-url="{{ $file['preview_url'] }}"
                                            data-is-image="{{ $file['is_image'] ? '1' : '0' }}"
                                            data-is-pdf="{{ $file['is_pdf'] ? '1' : '0' }}"
                                            data-file-name="{{ $file['file_name'] }}"
                                            data-note-number="{{ $file['note_number'] }}"
                                            data-supplier-name="{{ $file['supplier_name'] }}"
                                            data-transaction-date="{{ $file['transaction_date'] }}"
                                            data-grand-total="{{ $file['grand_total'] }}"
                                        >
                                            <div class="truncate text-sm font-semibold text-slate-900">{{ $file['file_name'] }}</div>
                                            <div class="mt-1 text-xs text-slate-500">No Nota: {{ $file['note_number'] }}</div>
                                        </button>
                                    @endforeach
                                </div>

                                <div id="active-note-meta" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs leading-6 text-slate-600">
                                    @if($firstPreview)
                                        <div>File: <span class="font-semibold text-slate-900" id="active-file-name">{{ $firstPreview['file_name'] }}</span></div>
                                        <div>No Nota: <span class="font-semibold text-slate-900" id="active-note-number">{{ $firstPreview['note_number'] }}</span></div>
                                        <div>Tanggal: <span class="font-semibold text-slate-900" id="active-note-date">{{ $firstPreview['transaction_date'] }}</span></div>
                                        <div>Total: <span class="font-semibold text-slate-900" id="active-note-total">Rp {{ number_format((int) $firstPreview['grand_total'], 0, ',', '.') }}</span></div>
                                    @else
                                        <div>Belum ada nota.</div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="compare-note-body bg-slate-100 p-4">
                            <div id="image-preview-wrap" class="{{ $firstPreview && $firstPreview['is_image'] ? '' : 'hidden' }}">
                                <img
                                    id="active-image-preview"
                                    src="{{ $firstPreview['is_image'] ? $firstPreview['preview_url'] : '' }}"
                                    alt="Preview nota compare"
                                    class="mx-auto block h-auto w-full rounded-2xl border border-slate-200 bg-white shadow-sm"
                                >
                            </div>

                            <div id="pdf-preview-wrap" class="{{ $firstPreview && $firstPreview['is_pdf'] ? '' : 'hidden' }}">
                                <iframe
                                    id="active-pdf-preview"
                                    src="{{ $firstPreview['is_pdf'] ? $firstPreview['preview_url'] : '' }}"
                                    class="compare-note-frame w-full rounded-2xl border border-slate-200 bg-white"
                                ></iframe>
                            </div>

                            <div id="empty-preview-wrap" class="{{ $firstPreview ? 'hidden' : '' }}">
                                <div class="flex min-h-[420px] items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-10 text-center">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-700">Preview nota belum tersedia</p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <a
                                    id="active-open-file"
                                    href="{{ $firstPreview['preview_url'] ?? '#' }}"
                                    target="_blank"
                                    class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 {{ $firstPreview && $firstPreview['preview_url'] ? '' : 'hidden' }}"
                                >
                                    Buka File
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@if(count($items) > 0)
    <div class="mt-4 flex justify-end">
        <form method="POST" action="{{ route('purchase-scan-batches.import', $purchaseScanBatch) }}">
            @csrf
            <button
                type="submit"
                class="inline-flex items-center justify-center rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700"
            >
                Masukkan ke Transaksi
            </button>
        </form>
    </div>
@endif
<script>
document.addEventListener('DOMContentLoaded', function () {
    const compareRows = document.querySelectorAll('.compare-row');
    const grandTop = document.getElementById('compare-grand-total-top');
    const grandSide = document.getElementById('compare-grand-total-side');

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
            statusEl.textContent = hasChanged ? 'Perubahan belum disimpan' : 'Sinkron';
            statusEl.classList.remove('text-emerald-700', 'text-rose-700');
            statusEl.classList.add(hasChanged ? 'text-rose-700' : 'text-emerald-700');
        }
    }

    function refreshGrandTotal() {
        let total = 0;
        compareRows.forEach(function (row) {
            if (row.dataset.deleted === '1') return;
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
                row.querySelectorAll('.compare-input').forEach(input => { input.value = input.dataset.original || ''; });
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
                row.querySelectorAll('.compare-input').forEach(input => { input.value = input.dataset.original || ''; });
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
    const tabs = document.querySelectorAll('.note-tab');
    const jumpButtons = document.querySelectorAll('.jump-to-note');

    const imageWrap = document.getElementById('image-preview-wrap');
    const pdfWrap = document.getElementById('pdf-preview-wrap');
    const emptyWrap = document.getElementById('empty-preview-wrap');

    const imagePreview = document.getElementById('active-image-preview');
    const pdfPreview = document.getElementById('active-pdf-preview');
    const openFile = document.getElementById('active-open-file');

    const activeFileName = document.getElementById('active-file-name');
    const activeNoteNumber = document.getElementById('active-note-number');
    const activeNoteDate = document.getElementById('active-note-date');
    const activeNoteTotal = document.getElementById('active-note-total');

    function activateTab(tab) {
        tabs.forEach(function (item) {
            item.classList.remove('border-indigo-300', 'bg-indigo-50');
            item.classList.add('border-slate-200', 'bg-white');
        });

        tab.classList.remove('border-slate-200', 'bg-white');
        tab.classList.add('border-indigo-300', 'bg-indigo-50');

        const previewUrl = tab.dataset.previewUrl || '';
        const isImage = tab.dataset.isImage === '1';
        const isPdf = tab.dataset.isPdf === '1';

        imageWrap.classList.add('hidden');
        pdfWrap.classList.add('hidden');
        emptyWrap.classList.add('hidden');

        if (isImage && previewUrl) {
            imagePreview.src = previewUrl;
            imageWrap.classList.remove('hidden');
        } else if (isPdf && previewUrl) {
            pdfPreview.src = previewUrl;
            pdfWrap.classList.remove('hidden');
        } else {
            emptyWrap.classList.remove('hidden');
        }

        if (openFile) {
            if (previewUrl) {
                openFile.href = previewUrl;
                openFile.classList.remove('hidden');
            } else {
                openFile.classList.add('hidden');
            }
        }

        if (activeFileName) activeFileName.textContent = tab.dataset.fileName || '-';
        if (activeNoteNumber) activeNoteNumber.textContent = tab.dataset.noteNumber || '-';
        if (activeNoteDate) activeNoteDate.textContent = tab.dataset.transactionDate || '-';
        if (activeNoteTotal) activeNoteTotal.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(Number(tab.dataset.grandTotal || 0));
    }

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            activateTab(tab);
        });
    });

    jumpButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const fileId = button.dataset.fileId || '';
            const targetTab = document.querySelector('.note-tab[data-file-id="' + fileId + '"]');

            if (targetTab) {
                activateTab(targetTab);
                targetTab.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        });
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const box = document.getElementById('note-fixed-box');
    const placeholder = document.getElementById('note-fixed-placeholder');
    const toggleBtn = document.getElementById('toggle-sticky-mode');

    if (!box || !placeholder || !toggleBtn) return;

    const desktopQuery = window.matchMedia('(min-width: 1280px)');
    const topOffset = 110;
    const storageKey = 'compare_multi_note_sticky_mode';

    let stickyEnabled = localStorage.getItem(storageKey);
    stickyEnabled = stickyEnabled === null ? '1' : stickyEnabled;
    stickyEnabled = stickyEnabled === '1';

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
        box.style.top = '';
        box.style.left = '';
        box.style.width = '';
        box.style.position = '';
    }

    function applyNonSticky() {
        resetNoteBox();
        box.classList.remove('is-sticky-active');
        box.classList.add('is-sticky-disabled');
    }

    function applySticky() {
        if (!desktopQuery.matches) {
            resetNoteBox();
            box.classList.remove('is-sticky-active', 'is-sticky-disabled');
            return;
        }

        const col = box.parentElement;
        if (!col) return;

        const rect = col.getBoundingClientRect();
        const width = rect.width;
        const left = rect.left;

        placeholder.style.height = box.offsetHeight + 'px';
        box.style.position = 'fixed';
        box.style.top = topOffset + 'px';
        box.style.left = left + 'px';
        box.style.width = width + 'px';

        box.classList.add('is-sticky-active');
        box.classList.remove('is-sticky-disabled');
    }

    function renderNoteMode() {
        if (stickyEnabled) {
            applySticky();
        } else {
            applyNonSticky();
        }

        updateToggleButton();
    }

    let ticking = false;

    function requestTick() {
        if (ticking) return;
        ticking = true;

        requestAnimationFrame(() => {
            renderNoteMode();
            ticking = false;
        });
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
    .compare-note-box {
        width: 100%;
        transition: all 0.2s ease;
    }

    .compare-note-box.is-sticky-disabled {
        position: static !important;
        top: auto !important;
        left: auto !important;
        width: 100% !important;
    }

    @media (min-width: 1280px) {
        .compare-note-body {
            max-height: calc(100vh - 140px);
            overflow: auto;
        }

        .compare-note-box.is-sticky-disabled .compare-note-body {
            max-height: none;
            overflow: visible;
        }

        .compare-note-frame {
            display: block;
            width: 100%;
            min-height: 75vh;
            height: 1200px;
        }

        .compare-note-box.is-sticky-disabled .compare-note-frame {
            min-height: 900px;
            height: 900px;
        }
    }
</style>
@endsection