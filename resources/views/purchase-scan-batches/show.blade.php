@extends('layouts.app')

@section('title', 'Hasil Scan Beberapa Nota')

@section('content')
@php
    $status = strtolower((string) $purchaseScanBatch->status);
    $rp = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');
    $qtyFmt = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, ',', '.'), '0'), ',');

    [$title, $desc, $tone, $icon] = match ($status) {
        'processed' => ['Semua nota berhasil dibaca', 'Cek dulu hasilnya, lalu simpan supaya stok bertambah.', 'emerald', 'check'],
        'reviewed'  => ['Koreksi sudah disimpan', 'Tinggal masukkan ke catatan pembelian.', 'brand', 'check'],
        'imported'  => ['Sudah masuk pembelian', 'Nota-nota ini sudah dipakai untuk membuat transaksi.', 'sky', 'check'],
        'supplier_mismatch' => ['Supplier nota berbeda', 'Beberapa nota hanya bisa digabung kalau dari supplier yang sama.', 'rose', 'alert'],
        'failed'    => ['Ada nota yang gagal dibaca', 'Foto ulang dengan cahaya lebih terang dan posisi nota rata.', 'rose', 'alert'],
        default     => ['Sedang diproses', 'Mohon tunggu sebentar.', 'charcoal', 'camera'],
    };
    $toneBg = ['emerald' => 'bg-emerald-500', 'brand' => 'bg-brand-500', 'sky' => 'bg-sky-500', 'rose' => 'bg-rose-500', 'charcoal' => 'bg-charcoal-500'][$tone];
    $canContinue = $totalItems > 0 && in_array($status, ['processed', 'reviewed'], true);
@endphp

<x-page-header title="Hasil Scan Beberapa Nota" subtitle="Ringkasan hasil pembacaan semua nota." :back="route('purchase-scan-batches.create')" />

<div class="card mb-4 flex items-center gap-4 p-4">
    <span class="inline-flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl {{ $toneBg }} text-white"><x-icon :name="$icon" class="h-7 w-7" stroke="2" /></span>
    <div class="min-w-0">
        <h2 class="text-lg font-extrabold leading-tight text-charcoal-900">{{ $title }}</h2>
        <p class="text-sm text-charcoal-500">{{ $desc }}</p>
    </div>
</div>

@if($purchaseScanBatch->error_message)
    <div class="alert-danger mb-4"><p>{{ $purchaseScanBatch->error_message }}</p></div>
@endif

<div class="mb-4 grid grid-cols-2 gap-2 sm:grid-cols-4 sm:gap-3">
    <div class="stat-tile !p-3 col-span-2 sm:col-span-1"><p class="stat-tile-label">Supplier</p><p class="mt-1 truncate text-[15px] font-extrabold">{{ $supplierName }}</p></div>
    <div class="stat-tile !p-3"><p class="stat-tile-label">Jumlah nota</p><p class="mt-1 text-[15px] font-extrabold">{{ count($filesForView) }}</p></div>
    <div class="stat-tile !p-3"><p class="stat-tile-label">Barang</p><p class="mt-1 text-[15px] font-extrabold">{{ $totalItems }}</p></div>
    <div class="stat-tile !p-3 col-span-2 sm:col-span-1"><p class="stat-tile-label">Total gabungan</p><p class="mt-1 text-[15px] font-extrabold text-emerald-700">{{ $rp($grandTotal) }}</p></div>
</div>

{{-- Per nota --}}
<h3 class="mb-2 text-[15px] font-bold text-charcoal-900">Nota yang di-scan</h3>
<div class="mb-4 grid grid-cols-1 gap-2 sm:grid-cols-2">
    @foreach($filesForView as $i => $f)
        <div class="card flex items-center gap-3 p-3">
            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-charcoal-900 text-sm font-extrabold text-white">{{ $i + 1 }}</span>
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-bold text-charcoal-900">{{ $f['note_number'] ? 'No. ' . $f['note_number'] : $f['file_name'] }}</p>
                <p class="truncate text-xs text-charcoal-500">{{ $f['supplier_name'] ?: 'Supplier belum terbaca' }} · {{ $f['transaction_date'] }}</p>
            </div>
            <div class="shrink-0 text-right">
                <p class="text-sm font-extrabold text-charcoal-900">{{ $rp($f['grand_total']) }}</p>
                @if($f['preview_url'])<a href="{{ $f['preview_url'] }}" target="_blank" rel="noopener" class="text-xs font-bold text-brand-600">Lihat foto</a>@endif
            </div>
        </div>
    @endforeach
</div>

{{-- Barang gabungan --}}
<div class="card overflow-hidden">
    <div class="border-b border-charcoal-100 px-4 py-3"><h3 class="text-[15px] font-bold text-charcoal-900">Barang gabungan ({{ $totalItems }})</h3></div>
    @forelse($items as $i => $item)
        @php $flag = ($item['confidence'] ?? 0) > 0 && ($item['confidence'] ?? 0) < 0.85; @endphp
        <div class="flex items-start gap-3 border-b border-charcoal-100 px-4 py-3 last:border-b-0">
            <span class="mt-0.5 inline-flex h-7 min-w-[28px] items-center justify-center rounded-lg bg-charcoal-100 text-xs font-extrabold text-charcoal-600">{{ $i + 1 }}</span>
            <div class="min-w-0 flex-1">
                <p class="text-[15px] font-bold leading-snug text-charcoal-900">{{ $item['nama_material'] ?? '-' }}</p>
                <p class="mt-0.5 text-sm text-charcoal-500">{{ $qtyFmt($item['qty'] ?? 0) }} {{ $item['satuan'] ?? '' }} × {{ $rp($item['harga'] ?? 0) }}</p>
                @if($flag)<span class="badge badge-warning mt-1.5">Mohon dicek</span>@endif
            </div>
            <p class="shrink-0 text-right text-[15px] font-extrabold text-charcoal-900">{{ $rp($item['subtotal'] ?? 0) }}</p>
        </div>
    @empty
        <p class="px-5 py-10 text-center text-sm text-charcoal-500">Belum ada barang yang terbaca.</p>
    @endforelse
    @if($totalItems > 0)
        <div class="flex items-center justify-between bg-charcoal-50 px-4 py-3">
            <span class="text-sm font-bold text-charcoal-600">Total</span>
            <span class="text-lg font-extrabold text-charcoal-900">{{ $rp($grandTotal) }}</span>
        </div>
    @endif
</div>

<div class="sticky bottom-[calc(var(--bottom-nav-h)+env(safe-area-inset-bottom)+8px)] z-30 mt-4 lg:bottom-4">
    <div class="rounded-2xl bg-charcoal-900 p-3 shadow-xl shadow-charcoal-900/30">
        @if($canContinue)
            <div class="grid grid-cols-[2fr_3fr] gap-2">
                <a href="{{ route('purchase-scan-batches.compare', $purchaseScanBatch) }}" class="inline-flex h-12 items-center justify-center rounded-xl bg-white/10 text-sm font-bold text-white">Cek &amp; Koreksi</a>
                <form method="POST" action="{{ route('purchase-scan-batches.import', $purchaseScanBatch) }}" >
                    @csrf
                    <button class="inline-flex h-12 w-full items-center justify-center gap-1.5 rounded-xl bg-brand-500 text-[15px] font-extrabold text-white">
                        Simpan ke Pembelian
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                    </button>
                </form>
            </div>
        @else
            <div class="grid grid-cols-2 gap-2">
                <a href="{{ route('purchase-scan-batches.create') }}" class="inline-flex h-12 items-center justify-center gap-2 rounded-xl bg-brand-500 text-[15px] font-extrabold text-white"><x-icon name="camera" class="h-5 w-5" />Foto nota lagi</a>
                <a href="{{ route('purchase-transactions.index') }}" class="inline-flex h-12 items-center justify-center rounded-xl bg-white/10 text-sm font-bold text-white">Daftar pembelian</a>
            </div>
        @endif
    </div>
</div>
@endsection
