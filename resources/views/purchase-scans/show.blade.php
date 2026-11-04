@extends('layouts.app')

@section('title', 'Hasil Scan Nota')

@section('content')
@php
    $status = strtolower((string) $purchaseScanDraft->status);
    $rp = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');
    $qtyFmt = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, ',', '.'), '0'), ',');
    $totalItems = count($items);

    // [judul, penjelasan, warna, ikon]
    [$title, $desc, $tone, $icon] = match ($status) {
        'processed' => ['Nota berhasil dibaca', 'Cek dulu hasilnya, lalu simpan supaya stok bertambah.', 'emerald', 'check'],
        'reviewed'  => ['Koreksi sudah disimpan', 'Tinggal masukkan ke catatan pembelian.', 'brand', 'check'],
        'imported'  => ['Sudah masuk pembelian', 'Nota ini sudah dipakai untuk membuat transaksi.', 'sky', 'check'],
        'failed'    => ['Nota gagal dibaca', 'Foto ulang dengan cahaya lebih terang dan posisi nota rata.', 'rose', 'alert'],
        default     => ['Sedang diproses', 'Mohon tunggu sebentar.', 'charcoal', 'camera'],
    };
    $toneBg = ['emerald' => 'bg-emerald-500', 'brand' => 'bg-brand-500', 'sky' => 'bg-sky-500', 'rose' => 'bg-rose-500', 'charcoal' => 'bg-charcoal-500'][$tone];

    $previewUrl = null;
    if (!empty($purchaseScanDraft->file_path)) {
        try { $previewUrl = route('purchase-scans.file', $purchaseScanDraft); } catch (\Throwable $e) {}
    }
    $ext = strtolower(pathinfo((string) $purchaseScanDraft->file_name, PATHINFO_EXTENSION));
    $isPdf = $ext === 'pdf';
    $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']);
    $canContinue = $totalItems > 0 && in_array($status, ['processed', 'reviewed'], true);
@endphp

<x-page-header title="Hasil Scan Nota" subtitle="Ringkasan hasil pembacaan nota." :back="route('purchase-scans.create')" />

{{-- Status --}}
<div class="card mb-4 flex items-center gap-4 p-4">
    <span class="inline-flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl {{ $toneBg }} text-white">
        <x-icon :name="$icon" class="h-7 w-7" stroke="2" />
    </span>
    <div class="min-w-0">
        <h2 class="text-lg font-extrabold leading-tight text-charcoal-900">{{ $title }}</h2>
        <p class="text-sm text-charcoal-500">{{ $desc }}</p>
    </div>
</div>

@if(!empty($duplicateWarning))
    <div class="alert-warning mb-4">
        <x-icon name="alert" class="mt-0.5 h-5 w-5 shrink-0 text-amber-600" />
        <p><b>Mungkin nota dobel.</b> File yang sama pernah di-scan pada {{ $duplicateWarning['created_at'] }}.
            <a href="{{ route('purchase-scans.show', $duplicateWarning['id']) }}" class="font-bold underline">Lihat scan sebelumnya</a>.</p>
    </div>
@endif

@if($status === 'failed' && $purchaseScanDraft->error_message)
    <div class="alert-danger mb-4"><p>{{ $purchaseScanDraft->error_message }}</p></div>
@elseif(($lowConfCount ?? 0) > 0)
    <div class="alert-warning mb-4">
        <x-icon name="alert" class="mt-0.5 h-5 w-5 shrink-0 text-amber-600" />
        <p><b>{{ $lowConfCount }} barang</b> mungkin salah baca. Buka <b>Cek &amp; Koreksi</b> sebelum disimpan.</p>
    </div>
@endif

{{-- Ringkasan angka --}}
<div class="mb-4 grid grid-cols-2 gap-2 sm:grid-cols-4 sm:gap-3">
    <div class="stat-tile !p-3 col-span-2 sm:col-span-1"><p class="stat-tile-label">Supplier</p><p class="mt-1 truncate text-[15px] font-extrabold {{ $supplierName ? 'text-charcoal-900' : 'text-amber-600' }}">{{ $supplierName ?: 'Belum terbaca' }}</p></div>
    <div class="stat-tile !p-3"><p class="stat-tile-label">No. nota</p><p class="mt-1 truncate text-[15px] font-extrabold {{ $purchaseScanDraft->detected_note_number ? '' : 'text-charcoal-400' }}">{{ $purchaseScanDraft->detected_note_number ?: '—' }}</p></div>
    <div class="stat-tile !p-3"><p class="stat-tile-label">Tanggal</p><p class="mt-1 truncate text-[15px] font-extrabold">{{ $displayDate ?: '—' }}</p></div>
    <div class="stat-tile !p-3 col-span-2 sm:col-span-1"><p class="stat-tile-label">Total nota</p><p class="mt-1 text-[15px] font-extrabold text-emerald-700">{{ $rp($grandTotal) }}</p></div>
</div>

<div class="grid grid-cols-1 gap-4 lg:grid-cols-12 lg:items-start">
    {{-- Foto nota --}}
    <div class="lg:order-last lg:col-span-5 lg:sticky lg:top-20" x-data="{ open: window.innerWidth >= 1024 }">
        <div class="card overflow-hidden">
            <button type="button" @click="open = !open" class="flex min-h-[56px] w-full items-center gap-3 px-4 py-3 text-left lg:cursor-default">
                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600"><x-icon name="receipt" /></span>
                <span class="min-w-0 flex-1">
                    <span class="block text-[15px] font-bold text-charcoal-900">Foto nota</span>
                    <span class="block truncate text-xs text-charcoal-500">{{ $purchaseScanDraft->file_name }}</span>
                </span>
                <svg class="h-5 w-5 shrink-0 text-charcoal-400 transition-transform lg:hidden" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
            </button>
            <div x-show="open" x-collapse class="border-t border-charcoal-100 bg-charcoal-100 p-2">
                @if($previewUrl && $isImage)
                    <img src="{{ $previewUrl }}" alt="Foto nota" class="mx-auto max-h-[60vh] w-full rounded-lg bg-white object-contain">
                @elseif($previewUrl && $isPdf)
                    <iframe src="{{ $previewUrl }}" class="h-[60vh] w-full rounded-lg bg-white"></iframe>
                @else
                    <p class="px-4 py-10 text-center text-sm text-charcoal-500">Pratinjau tidak tersedia.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Daftar barang --}}
    <div class="min-w-0 lg:col-span-7">
        <div class="card overflow-hidden">
            <div class="flex items-center justify-between border-b border-charcoal-100 px-4 py-3">
                <h3 class="text-[15px] font-bold text-charcoal-900">Barang di nota ({{ $totalItems }})</h3>
                @if($totalItems > 0)<span class="badge badge-success">{{ $matchedCount }} sudah dikenali</span>@endif
            </div>

            @forelse($items as $i => $item)
                @php
                    $conf = (float) ($item['confidence'] ?? 0);
                    $flag = $conf > 0 && $conf < 0.85;
                @endphp
                <div class="flex items-start gap-3 border-b border-charcoal-100 px-4 py-3 last:border-b-0">
                    <span class="mt-0.5 inline-flex h-7 min-w-[28px] items-center justify-center rounded-lg bg-charcoal-100 text-xs font-extrabold text-charcoal-600">{{ $i + 1 }}</span>
                    <div class="min-w-0 flex-1">
                        <p class="text-[15px] font-bold leading-snug text-charcoal-900">{{ $item['nama_material'] }}</p>
                        <p class="mt-0.5 text-sm text-charcoal-500">{{ $qtyFmt($item['qty']) }} {{ $item['satuan'] }} × {{ $rp($item['harga']) }}</p>
                        <div class="mt-1.5 flex flex-wrap gap-1.5">
                            @if(($item['match_status'] ?? null) === 'matched')
                                <span class="badge badge-success">Dikenali{{ !empty($item['match_label']) ? ': ' . $item['match_label'] : '' }}</span>
                            @else
                                <span class="badge badge-neutral">Barang baru</span>
                            @endif
                            @if($flag)<span class="badge badge-warning">Mohon dicek</span>@endif
                        </div>
                    </div>
                    <p class="shrink-0 text-right text-[15px] font-extrabold text-charcoal-900">{{ $rp($item['subtotal']) }}</p>
                </div>
            @empty
                <div class="px-5 py-12 text-center">
                    <x-icon name="alert" class="mx-auto h-10 w-10 text-amber-500" />
                    <p class="mt-3 font-bold text-charcoal-800">Belum ada barang yang terbaca</p>
                    <p class="mt-1 text-sm text-charcoal-500">Foto ulang nota dengan kualitas lebih jelas.</p>
                </div>
            @endforelse

            @if($totalItems > 0)
                <div class="flex items-center justify-between bg-charcoal-50 px-4 py-3">
                    <span class="text-sm font-bold text-charcoal-600">Total</span>
                    <span class="text-lg font-extrabold text-charcoal-900">{{ $rp($grandTotal) }}</span>
                </div>
            @endif
        </div>

        @if(!empty($parsed['notes']) && is_array($parsed['notes']))
            <details class="mt-3 rounded-xl border border-charcoal-200 bg-white px-4">
                <summary class="flex min-h-[48px] cursor-pointer items-center text-sm font-bold text-charcoal-600">Catatan sistem</summary>
                <ul class="list-disc space-y-1 pb-3 pl-5 text-sm text-charcoal-600">
                    @foreach($parsed['notes'] as $note)<li>{{ $note }}</li>@endforeach
                </ul>
            </details>
        @endif
    </div>
</div>

{{-- Aksi --}}
<div class="sticky bottom-[calc(var(--bottom-nav-h)+env(safe-area-inset-bottom)+8px)] z-30 mt-4 lg:bottom-4">
    <div class="rounded-2xl bg-charcoal-900 p-3 shadow-xl shadow-charcoal-900/30">
        @if($canContinue)
            <div class="grid grid-cols-[2fr_3fr] gap-2">
                <a href="{{ route('purchase-scans.compare', $purchaseScanDraft) }}" class="inline-flex h-12 items-center justify-center rounded-xl bg-white/10 text-sm font-bold text-white">Cek &amp; Koreksi</a>
                <form method="POST" action="{{ route('purchase-scans.import', $purchaseScanDraft) }}" >
                    @csrf
                    <button class="inline-flex h-12 w-full items-center justify-center gap-1.5 rounded-xl bg-brand-500 text-[15px] font-extrabold text-white">
                        Simpan ke Pembelian
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                    </button>
                </form>
            </div>
        @else
            <div class="grid grid-cols-2 gap-2">
                <a href="{{ route('purchase-scans.create') }}" class="inline-flex h-12 items-center justify-center gap-2 rounded-xl bg-brand-500 text-[15px] font-extrabold text-white"><x-icon name="camera" class="h-5 w-5" />Foto nota lagi</a>
                <a href="{{ route('purchase-transactions.index') }}" class="inline-flex h-12 items-center justify-center rounded-xl bg-white/10 text-sm font-bold text-white">Daftar pembelian</a>
            </div>
        @endif
    </div>
</div>
@endsection
