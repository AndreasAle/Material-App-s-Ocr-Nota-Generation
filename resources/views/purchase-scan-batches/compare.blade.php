@extends('layouts.app')

@section('title', 'Cek Hasil Beberapa Nota')

@section('content')
@php
    $rp = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');
    $needCheck = collect($items)->filter(fn ($i) => ($i['confidence'] ?? 0) > 0 && ($i['confidence'] ?? 0) < 0.85)->count();
    $blocked = $purchaseScanBatch->status === 'supplier_mismatch';
    $fileLabels = collect($filesForView)->mapWithKeys(fn ($f, $i) => [(string) $f['id'] => 'Nota ' . ($i + 1)]);
@endphp

<x-page-header title="Cek Hasil Beberapa Nota" subtitle="Semua nota digabung jadi satu pembelian. Cek tiap barang, lalu lanjutkan." :back="route('purchase-scan-batches.show', $purchaseScanBatch)">
    <x-slot name="actions">
        <button type="button" id="reset-all-btn" class="btn-secondary !min-h-[40px] !px-3">Kembalikan semua</button>
    </x-slot>
</x-page-header>

<div class="mb-4 grid grid-cols-2 gap-2 sm:grid-cols-4 sm:gap-3">
    <div class="stat-tile !p-3 col-span-2 sm:col-span-1"><p class="stat-tile-label">Supplier</p><p class="mt-1 truncate text-[15px] font-extrabold">{{ $safeSupplier }}</p></div>
    <div class="stat-tile !p-3"><p class="stat-tile-label">Jumlah nota</p><p class="mt-1 text-[15px] font-extrabold">{{ count($filesForView) }}</p></div>
    <div class="stat-tile !p-3"><p class="stat-tile-label">Barang</p><p class="mt-1 text-[15px] font-extrabold" data-item-count>{{ count($items) }}</p></div>
    <div class="stat-tile !p-3 col-span-2 sm:col-span-1"><p class="stat-tile-label">Total gabungan</p><p class="mt-1 text-[15px] font-extrabold text-emerald-700" data-grand-total>{{ $rp($grandTotal) }}</p></div>
</div>

@if($blocked)
    <div class="alert-danger mb-4"><p><b>Supplier di nota-nota ini berbeda.</b> Beberapa nota hanya bisa digabung kalau dari supplier yang sama. Scan ulang per nota.</p></div>
@elseif($needCheck > 0)
    <div class="alert-warning mb-4">
        <x-icon name="alert" class="mt-0.5 h-5 w-5 shrink-0 text-amber-600" />
        <p><b>{{ $needCheck }} barang</b> perlu dicek lebih teliti (kartu kuning/merah).</p>
    </div>
@endif

<x-help title="Cara mengecek" :open="false">
    <li>Pilih <b>Nota 1, Nota 2, …</b> untuk melihat fotonya dan barang dari nota itu saja.</li>
    <li>Ketik langsung di kolom yang salah (berwarna kuning bila diubah). Baris sampah? tekan ikon tempat sampah.</li>
    <li>Selesai? Tekan <b>Simpan &amp; Lanjut</b>.</li>
</x-help>

<form method="POST" action="{{ route('purchase-scan-batches.compare.update', $purchaseScanBatch) }}" id="compare-form"
      x-data="{
          file: 'all',
          files: @js($filesForView),
          matches(id) { return this.file === 'all' || String(id) === String(this.file); },
          get active() { return this.files.find(f => String(f.id) === String(this.file)) || this.files[0] || null; },
          open: window.innerWidth >= 1024,
      }">
    @csrf
    @method('PUT')

    {{-- Pilih nota --}}
    <div class="-mx-3 mb-3 flex gap-2 overflow-x-auto px-3 pb-1 sm:mx-0 sm:px-0">
        <button type="button" @click="file = 'all'" class="chip shrink-0" :class="file === 'all' ? '!bg-charcoal-900 !text-white' : ''">Semua nota</button>
        @foreach($filesForView as $i => $f)
            <button type="button" @click="file = '{{ $f['id'] }}'" class="chip shrink-0" :class="String(file) === '{{ $f['id'] }}' ? '!bg-brand-600 !text-white' : ''">Nota {{ $i + 1 }}</button>
        @endforeach
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-12 lg:items-start">
        {{-- Foto nota --}}
        <div class="lg:order-last lg:col-span-5 lg:sticky lg:top-20">
            <div class="card overflow-hidden">
                <button type="button" @click="open = !open" class="flex min-h-[56px] w-full items-center gap-3 px-4 py-3 text-left lg:cursor-default">
                    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600"><x-icon name="receipt" /></span>
                    <span class="min-w-0 flex-1">
                        <span class="block text-[15px] font-bold text-charcoal-900">Foto nota</span>
                        <span class="block truncate text-xs text-charcoal-500" x-text="active ? ((active.note_number && active.note_number !== 'Belum tersedia' ? 'No. ' + active.note_number + ' · ' : '') + active.transaction_date + ' · Rp ' + new Intl.NumberFormat('id-ID').format(active.grand_total)) : ''"></span>
                    </span>
                    <template x-if="active && active.preview_url">
                        <a :href="active.preview_url" target="_blank" rel="noopener" @click.stop class="btn-secondary !min-h-[36px] !px-3 !py-1.5 !text-xs">Buka penuh</a>
                    </template>
                    <svg class="h-5 w-5 shrink-0 text-charcoal-400 transition-transform lg:hidden" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                </button>
                <div x-show="open" x-collapse class="border-t border-charcoal-100 bg-charcoal-100 p-2">
                    <template x-if="active && active.is_image"><img :src="active.preview_url" alt="Foto nota" class="mx-auto max-h-[70vh] w-full rounded-lg bg-white object-contain lg:max-h-[calc(100vh-14rem)]"></template>
                    <template x-if="active && active.is_pdf"><iframe :src="active.preview_url" class="h-[70vh] w-full rounded-lg bg-white lg:h-[calc(100vh-14rem)]"></iframe></template>
                    <template x-if="!active || (!active.is_image && !active.is_pdf)"><p class="px-4 py-10 text-center text-sm text-charcoal-500">Pratinjau tidak tersedia.</p></template>
                </div>
            </div>
        </div>

        {{-- Barang --}}
        <div class="min-w-0 space-y-3 lg:col-span-7">
            @forelse($items as $index => $item)
                @include('purchase-scans.partials.item-card', [
                    'index' => $index,
                    'item' => $item,
                    'fileLabel' => $fileLabels[(string) ($item['_source_file_id'] ?? '')] ?? null,
                    'filterable' => true,
                ])
            @empty
                <div class="card px-5 py-12 text-center">
                    <x-icon name="alert" class="mx-auto h-10 w-10 text-amber-500" />
                    <p class="mt-3 font-bold text-charcoal-800">Tidak ada barang yang terbaca</p>
                    <a href="{{ route('purchase-scan-batches.create') }}" class="btn-primary mt-4">Foto ulang</a>
                </div>
            @endforelse
        </div>
    </div>

    @if(count($items) > 0)
    <div class="sticky bottom-[calc(var(--bottom-nav-h)+env(safe-area-inset-bottom)+8px)] z-30 mt-4 lg:bottom-4">
        <div class="rounded-2xl bg-charcoal-900 p-3 text-white shadow-xl shadow-charcoal-900/30">
            <div class="mb-2 flex items-baseline justify-between px-1">
                <span class="text-xs text-charcoal-300"><span data-item-count>{{ count($items) }}</span> barang</span>
                <span class="text-lg font-extrabold" data-grand-total>{{ $rp($grandTotal) }}</span>
            </div>
            <div class="grid grid-cols-[2fr_3fr] gap-2">
                <button type="submit" class="h-12 rounded-xl bg-white/10 text-sm font-bold text-white active:scale-[0.98]">Simpan saja</button>
                <button type="submit" id="btn-save-continue" name="then" value="import" @disabled($blocked)
                        class="inline-flex h-12 items-center justify-center gap-1.5 rounded-xl bg-brand-500 text-[15px] font-extrabold text-white active:scale-[0.98] disabled:opacity-40">
                    Simpan &amp; Lanjut
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                </button>
            </div>
        </div>
    </div>
    @endif
</form>

@include('purchase-scans.partials.editor-script')
@endsection
