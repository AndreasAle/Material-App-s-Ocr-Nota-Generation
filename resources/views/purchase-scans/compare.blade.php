@extends('layouts.app')

@section('title', 'Cek Hasil Scan')

@section('content')
@php
    $noteNumber = $purchaseScanDraft->detected_note_number ?: null;
    $supplier = $parsed['supplier_name'] ?? null;
    $noteDate = $purchaseScanDraft->detected_transaction_date
        ? \Illuminate\Support\Carbon::parse($purchaseScanDraft->detected_transaction_date)->translatedFormat('d M Y')
        : null;
    $needCheck = collect($items)->filter(fn ($i) => !empty($i['review_reasons']) || ($i['confidence'] ?? 0) < 0.85)->count();
@endphp

<x-page-header title="Cek Hasil Scan" subtitle="Cocokkan dengan foto nota. Perbaiki yang salah, lalu lanjutkan." :back="route('purchase-scans.show', $purchaseScanDraft)">
    <x-slot name="actions">
        <button type="button" id="reset-all-btn" class="btn-secondary !min-h-[40px] !px-3">Kembalikan semua</button>
    </x-slot>
</x-page-header>

{{-- Langkah --}}
<ol class="mb-4 flex items-center gap-2 text-xs font-bold">
    <li class="inline-flex items-center gap-1.5 text-emerald-700"><span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-emerald-500 text-white">✓</span>Foto nota</li>
    <li class="h-px flex-1 bg-charcoal-300"></li>
    <li class="inline-flex items-center gap-1.5 text-brand-700"><span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-brand-600 text-white">2</span>Cek hasil</li>
    <li class="h-px flex-1 bg-charcoal-300"></li>
    <li class="inline-flex items-center gap-1.5 text-charcoal-400"><span class="inline-flex h-6 w-6 items-center justify-center rounded-full border-2 border-charcoal-300">3</span>Simpan</li>
</ol>

{{-- Ringkasan nota --}}
<div class="mb-4 grid grid-cols-2 gap-2 sm:grid-cols-4 sm:gap-3">
    <div class="stat-tile !p-3 col-span-2 sm:col-span-1">
        <p class="stat-tile-label">Supplier</p>
        <p class="mt-1 truncate text-[15px] font-extrabold {{ $supplier ? 'text-charcoal-900' : 'text-amber-600' }}">{{ $supplier ?: 'Belum terbaca' }}</p>
    </div>
    <div class="stat-tile !p-3">
        <p class="stat-tile-label">No. nota</p>
        <p class="mt-1 truncate text-[15px] font-extrabold {{ $noteNumber ? 'text-charcoal-900' : 'text-charcoal-400' }}">{{ $noteNumber ?: '—' }}</p>
    </div>
    <div class="stat-tile !p-3">
        <p class="stat-tile-label">Tanggal</p>
        <p class="mt-1 truncate text-[15px] font-extrabold {{ $noteDate ? 'text-charcoal-900' : 'text-charcoal-400' }}">{{ $noteDate ?: '—' }}</p>
    </div>
    <div class="stat-tile !p-3 col-span-2 sm:col-span-1">
        <p class="stat-tile-label">Jumlah subtotal item</p>
        <p class="mt-1 text-[15px] font-extrabold text-emerald-700" data-grand-total>Rp {{ number_format((int) $grandTotal, 0, ',', '.') }}</p>
    </div>
</div>

@if($needCheck > 0)
    <div class="alert-warning mb-4">
        <svg class="mt-0.5 h-5 w-5 shrink-0 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
        <p><b>{{ $needCheck }} barang</b> perlu dicek lebih teliti. Kartu bertanda kuning/merah kemungkinan salah baca (angka atau nama).</p>
    </div>
@endif

<x-help title="Cara mengecek" :open="false">
    <li>Buka <b>Foto nota</b> di bawah, lalu cocokkan <b>nama, jumlah, dan harga</b> tiap barang.</li>
    <li>Ketik langsung di kolom yang salah. Kolom yang Anda ubah berwarna kuning.</li>
    <li>Baris yang bukan barang (misalnya "Total" atau "Diskon")? Tekan ikon <b>tempat sampah</b>.</li>
    <li>Selesai? Tekan <b>Simpan &amp; Lanjut</b> untuk membuat catatan pembelian dan menambah stok.</li>
</x-help>

@php($detectedTotal = (int) ($parsed['detected_grand_total'] ?? 0))
@if($detectedTotal > 0)
    <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-950">
        <p>Total bawah nota yang dibaca AI: <b>Rp {{ number_format($detectedTotal, 0, ',', '.') }}</b>. Cocokkan juga dengan foto.</p>
        @if($detectedTotal !== (int) $grandTotal)
            <p>Jumlah subtotal saat halaman dibuka berbeda dari bacaan total nota. Periksa angka, diskon, dan biaya tambahan; jangan mengubah angka hanya agar total cocok.</p>
        @endif
    </div>
@endif

@if(!empty($parsed['notes']))
    <details class="mb-4 rounded-xl border border-charcoal-200 bg-white p-3 text-sm">
        <summary class="cursor-pointer font-semibold">Catatan pembacaan nota</summary>
        @foreach($parsed['notes'] as $note)<p class="mt-2">{{ $note }}</p>@endforeach
    </details>
@endif

<form method="POST" action="{{ route('purchase-scans.compare.update', $purchaseScanDraft) }}" id="compare-form">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-12 lg:items-start">

        {{-- Foto nota: di HP bisa dibuka-tutup, di layar lebar menempel di kanan --}}
        <div class="lg:order-last lg:col-span-5 lg:sticky lg:top-20" x-data="{ open: window.innerWidth >= 1024 }">
            <div class="card overflow-hidden">
                <button type="button" @click="open = !open" class="flex min-h-[56px] w-full items-center gap-3 px-4 py-3 text-left lg:cursor-default">
                    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600"><x-icon name="receipt" /></span>
                    <span class="min-w-0 flex-1">
                        <span class="block text-[15px] font-bold text-charcoal-900">Foto nota</span>
                        <span class="block truncate text-xs text-charcoal-500">{{ $purchaseScanDraft->file_name }}</span>
                    </span>
                    @if($previewUrl)
                        <a href="{{ $previewUrl }}" target="_blank" rel="noopener" @click.stop class="btn-secondary !min-h-[36px] !px-3 !py-1.5 !text-xs">Buka penuh</a>
                    @endif
                    <svg class="h-5 w-5 shrink-0 text-charcoal-400 transition-transform lg:hidden" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                </button>
                <div x-show="open" x-collapse class="border-t border-charcoal-100 bg-charcoal-100 p-2">
                    @if($previewUrl && $isImage)
                        <img src="{{ $previewUrl }}" alt="Foto nota" class="mx-auto max-h-[70vh] w-full rounded-lg bg-white object-contain lg:max-h-[calc(100vh-14rem)]">
                    @elseif($previewUrl && $isPdf)
                        <iframe src="{{ $previewUrl }}" class="h-[70vh] w-full rounded-lg bg-white lg:h-[calc(100vh-14rem)]"></iframe>
                    @else
                        <p class="px-4 py-10 text-center text-sm text-charcoal-500">Pratinjau nota tidak tersedia.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Daftar barang --}}
        <div class="min-w-0 space-y-3 lg:col-span-7">
            <div class="flex items-center justify-between">
                <h2 class="text-[15px] font-bold text-charcoal-900">Barang di nota (<span data-item-count>{{ count($items) }}</span>)</h2>
            </div>

            @forelse($items as $index => $item)
                @include('purchase-scans.partials.item-card', ['index' => $index, 'item' => $item])
            @empty
                <div class="card px-5 py-12 text-center">
                    <x-icon name="alert" class="mx-auto h-10 w-10 text-amber-500" />
                    <p class="mt-3 font-bold text-charcoal-800">Tidak ada barang yang terbaca</p>
                    <p class="mt-1 text-sm text-charcoal-500">Coba foto ulang dengan cahaya lebih terang dan nota yang rata.</p>
                    <a href="{{ route('purchase-scans.create') }}" class="btn-primary mt-4">Foto ulang nota</a>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Bar aksi: menempel di atas tab bawah --}}
    @if(count($items) > 0)
    <div class="sticky bottom-[calc(var(--bottom-nav-h)+env(safe-area-inset-bottom)+8px)] z-30 mt-4 lg:bottom-4">
        <div class="rounded-2xl bg-charcoal-900 p-3 text-white shadow-xl shadow-charcoal-900/30">
            <div class="mb-2 flex items-baseline justify-between px-1">
                <span class="text-xs text-charcoal-300"><span data-item-count>{{ count($items) }}</span> barang</span>
                <span class="text-lg font-extrabold" data-grand-total>Rp {{ number_format((int) $grandTotal, 0, ',', '.') }}</span>
            </div>
            <div class="grid grid-cols-[2fr_3fr] gap-2">
                <button type="submit" class="h-12 rounded-xl bg-white/10 text-sm font-bold text-white active:scale-[0.98]">Simpan saja</button>
                <button type="submit" id="btn-save-continue" name="then" value="import"
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
