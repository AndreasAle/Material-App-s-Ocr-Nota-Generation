@php
    /**
     * Kartu satu barang hasil scan (dipakai Cek Hasil Scan 1 nota & beberapa nota).
     * Nama field tidak boleh diubah: items[i][kode|nama_material|satuan|qty|harga|subtotal|_deleted|...].
     *
     * @var int $index
     * @var array $item
     * @var string|null $fileLabel  label asal nota (mode beberapa nota)
     */
    $conf = (float) ($item['confidence'] ?? 0);
    $level = $conf >= 0.85 ? 'ok' : ($conf >= 0.65 ? 'mid' : ($conf > 0 ? 'low' : 'none'));
    $accent = ['ok' => 'border-l-emerald-500', 'mid' => 'border-l-amber-400', 'low' => 'border-l-rose-500', 'none' => 'border-l-charcoal-300'][$level];
    $matchId = $item['match_material_id'] ?? ($item['match']['material_id'] ?? '');
    $qtyValue = rtrim(rtrim(number_format((float) ($item['qty'] ?? 0), 2, '.', ''), '0'), '.');
    $learnt = ($item['match_source'] ?? ($item['match']['source'] ?? '')) === 'learnt';
    $fileId = $item['_source_file_id'] ?? '';
@endphp

<div class="compare-row card overflow-hidden border-l-4 {{ $accent }} transition-opacity" data-row="{{ $index }}" data-file-id="{{ $fileId }}"
     @isset($filterable) x-show="matches('{{ $fileId }}')" @endisset>

    <input type="hidden" name="items[{{ $index }}][_deleted]" value="0" class="delete-flag">
    <input type="hidden" name="items[{{ $index }}][_original_nama]" value="{{ $item['nama_material'] ?? '' }}">
    <input type="hidden" name="items[{{ $index }}][_match_material_id]" value="{{ $matchId }}">

    {{-- Kepala kartu --}}
    <div class="flex items-center gap-2 bg-charcoal-50 px-3 py-2">
        <span class="inline-flex h-7 min-w-[28px] items-center justify-center rounded-lg bg-charcoal-900 px-1.5 text-xs font-extrabold text-white">{{ $index + 1 }}</span>

        @if($level === 'ok')
            <span class="badge badge-success">Terbaca jelas</span>
        @elseif($level === 'mid')
            <span class="badge badge-warning">Mohon dicek</span>
        @elseif($level === 'low')
            <span class="badge badge-danger">Kemungkinan salah baca</span>
        @endif
        @if($learnt)<span class="badge badge-info">Dikenali</span>@endif
        <span class="row-changed badge badge-warning hidden">Diubah</span>

        @if(!empty($fileLabel))
            <span class="hidden truncate text-xs text-charcoal-500 sm:inline">{{ $fileLabel }}</span>
        @endif

        <div class="ml-auto flex items-center gap-1">
            <button type="button" class="reset-row inline-flex h-10 w-10 items-center justify-center rounded-xl text-charcoal-500 hover:bg-charcoal-100" title="Kembalikan seperti hasil scan" aria-label="Kembalikan">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3"/></svg>
            </button>
            <button type="button" class="delete-row inline-flex h-10 w-10 items-center justify-center rounded-xl text-rose-500 hover:bg-rose-50" title="Hapus baris ini" aria-label="Hapus">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
            </button>
        </div>
    </div>

    {{-- Teks asli dari nota --}}
    @if(!empty($item['raw_line']))
        <p class="border-b border-charcoal-100 px-3 py-2 font-mono text-xs leading-5 text-charcoal-500">
            <span class="font-sans font-semibold text-charcoal-400">Di nota:</span> {{ $item['raw_line'] }}
        </p>
    @endif

    {{-- Isian --}}
    <div class="space-y-3 p-3">
        <div>
            <label class="form-label">Nama barang</label>
            <input type="text" name="items[{{ $index }}][nama_material]" value="{{ $item['nama_material'] ?? '' }}" data-original="{{ $item['nama_material'] ?? '' }}"
                   class="compare-input form-input font-semibold" autocomplete="off">
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="form-label">Jumlah</label>
                <input type="text" inputmode="decimal" name="items[{{ $index }}][qty]" value="{{ $qtyValue }}" data-original="{{ $qtyValue }}"
                       class="compare-input compare-qty form-input text-right font-bold tabular-nums">
            </div>
            <div>
                <label class="form-label">Satuan</label>
                <input type="text" name="items[{{ $index }}][satuan]" value="{{ $item['satuan'] ?? '' }}" data-original="{{ $item['satuan'] ?? '' }}"
                       class="compare-input form-input" placeholder="sak, batang, dus…">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="form-label">Harga satuan (Rp)</label>
                <input type="text" inputmode="numeric" name="items[{{ $index }}][harga]" value="{{ (int) ($item['harga'] ?? 0) }}" data-original="{{ (int) ($item['harga'] ?? 0) }}"
                       class="compare-input compare-harga form-input text-right font-bold tabular-nums">
            </div>
            <div>
                <label class="form-label">Subtotal (Rp)</label>
                <input type="text" inputmode="numeric" name="items[{{ $index }}][subtotal]" value="{{ (int) ($item['subtotal'] ?? 0) }}" data-original="{{ (int) ($item['subtotal'] ?? 0) }}"
                       class="compare-input compare-subtotal form-input text-right font-extrabold tabular-nums text-emerald-700">
            </div>
        </div>

        <details class="group">
            <summary class="flex min-h-[40px] cursor-pointer list-none items-center gap-1 text-xs font-bold text-charcoal-500">
                <svg class="h-4 w-4 transition-transform group-open:rotate-90" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                Kode barang (opsional)
            </summary>
            <input type="text" name="items[{{ $index }}][kode]" value="{{ $item['kode'] ?? '' }}" data-original="{{ $item['kode'] ?? '' }}"
                   class="compare-input form-input mt-1" placeholder="Kosongkan bila tidak ada">
        </details>
    </div>

    {{-- Baris dihapus --}}
    <div class="deleted-overlay hidden items-center justify-between gap-3 border-t border-rose-200 bg-rose-50 px-3 py-3">
        <span class="text-sm font-semibold text-rose-700">Baris ini tidak akan disimpan</span>
        <button type="button" class="undo-delete min-h-[40px] rounded-lg px-3 text-sm font-bold text-rose-700 underline">Batalkan</button>
    </div>
</div>
