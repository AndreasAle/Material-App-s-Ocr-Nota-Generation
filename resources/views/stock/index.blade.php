@extends('layouts.app')

@section('title', 'Stok Barang')

@section('content')
@php $qty = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, ',', '.'), '0'), ','); @endphp

<x-page-header title="Stok Barang" subtitle="Sisa barang di gudang. Bertambah saat barang masuk, berkurang saat terjual.">
    <x-slot name="actions">
        <a href="{{ route('stock.create') }}" class="btn-primary">
            <x-icon name="plus" class="h-5 w-5" stroke="2.2" />
            Koreksi / Input Stok
        </a>
    </x-slot>
</x-page-header>

<x-help title="Kapan stok berubah?">
    <li><b>Bertambah otomatis</b> saat Anda menyimpan nota supplier dengan centang “Barang sudah diterima”.</li>
    <li><b>Berkurang otomatis</b> setiap ada penjualan di Kasir, dan kembali saat ada retur.</li>
    <li>Pakai <b>Koreksi / Input Stok</b> hanya untuk stok awal, barang rusak/hilang, atau hasil hitung ulang gudang.</li>
    <li>Isi <b>stok minimum</b> di halaman barang agar muncul peringatan “perlu dibeli lagi”.</li>
</x-help>

{{-- Ringkasan (bisa diketuk sebagai filter) --}}
<div class="mb-4 grid grid-cols-3 gap-2 sm:gap-3">
    <a href="{{ route('stock.index') }}" class="stat-tile !p-3 {{ $alert !== '1' ? 'ring-2 ring-brand-500' : '' }}">
        <p class="text-[11px] font-bold uppercase tracking-wider text-charcoal-500">Semua</p>
        <p class="mt-1 text-xl font-extrabold text-charcoal-900">{{ number_format($totalMaterials) }}</p>
    </a>
    <a href="{{ route('stock.index', ['alert' => '1']) }}" class="stat-tile !p-3 {{ $lowStockCount > 0 ? 'border-amber-200 bg-amber-50' : '' }} {{ $alert === '1' ? 'ring-2 ring-amber-500' : '' }}">
        <p class="text-[11px] font-bold uppercase tracking-wider text-amber-700">Menipis</p>
        <p class="mt-1 text-xl font-extrabold text-amber-700">{{ $lowStockCount }}</p>
    </a>
    <div class="stat-tile !p-3 {{ $zeroStockCount > 0 ? 'border-rose-200 bg-rose-50' : '' }}">
        <p class="text-[11px] font-bold uppercase tracking-wider text-rose-700">Habis</p>
        <p class="mt-1 text-xl font-extrabold text-rose-700">{{ $zeroStockCount }}</p>
    </div>
</div>

{{-- Cari --}}
<form method="GET" action="{{ route('stock.index') }}" class="mb-3 flex gap-2">
    <div class="relative flex-1">
        <x-icon name="cube" class="pointer-events-none absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-charcoal-400" />
        <input type="search" name="search" value="{{ $search }}" placeholder="Cari barang…" class="form-input pl-11">
    </div>
    @if($alert === '1')<input type="hidden" name="alert" value="1">@endif
    <button class="btn-secondary shrink-0">Cari</button>
</form>
@if($categories->isNotEmpty())
<div class="-mx-3 mb-4 flex gap-2 overflow-x-auto px-3 pb-1 sm:mx-0 sm:px-0">
    <a href="{{ route('stock.index', array_filter(['search' => $search, 'alert' => $alert])) }}" class="chip shrink-0 {{ ! $categoryId ? '!bg-charcoal-900 !text-white' : '' }}">Semua kategori</a>
    @foreach ($categories as $cat)
        <a href="{{ route('stock.index', array_filter(['search' => $search, 'alert' => $alert, 'category_id' => $cat->id])) }}" class="chip shrink-0 {{ (string) $categoryId === (string) $cat->id ? '!bg-brand-600 !text-white' : '' }}">{{ $cat->name }}</a>
    @endforeach
</div>
@endif

@if ($paginated->isEmpty())
    <div class="card px-5 py-12 text-center">
        <x-icon name="box" class="mx-auto h-10 w-10 text-charcoal-300" />
        <p class="mt-3 font-bold text-charcoal-700">Tidak ada barang ditemukan</p>
        <p class="mt-1 text-sm text-charcoal-500">Coba kata kunci lain atau hapus filter.</p>
    </div>
@else
    {{-- Kartu (HP) / tabel (layar lebar) --}}
    <div class="space-y-2 md:hidden">
        @foreach ($paginated as $m)
            @php
                $balance = $m->current_stock;
                $isEmpty = $balance <= 0;
                $isLow = $m->is_low_stock;
                $pct = $m->minimum_stock > 0 ? min(100, max(4, $balance / ($m->minimum_stock * 2) * 100)) : ($isEmpty ? 4 : 100);
                $bar = $isEmpty ? 'bg-rose-500' : ($isLow ? 'bg-amber-500' : 'bg-emerald-500');
            @endphp
            <a href="{{ route('stock.show', $m) }}" class="card block p-3.5 active:bg-charcoal-50">
                <div class="flex items-start gap-3">
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-[15px] font-bold text-charcoal-900">{{ $m->name }}</p>
                        <p class="text-xs text-charcoal-500">{{ $m->category?->name ?? 'Tanpa kategori' }}{{ $m->minimum_stock !== null ? ' · min ' . $qty($m->minimum_stock) : '' }}</p>
                    </div>
                    <div class="shrink-0 text-right">
                        <p class="text-lg font-extrabold leading-none {{ $isEmpty ? 'text-rose-600' : ($isLow ? 'text-amber-600' : 'text-charcoal-900') }}">{{ $qty($balance) }}</p>
                        <p class="mt-1 text-xs text-charcoal-500">{{ $m->unit?->name }}</p>
                    </div>
                </div>
                <div class="mt-2.5 h-1.5 rounded-full bg-charcoal-100"><div class="h-1.5 rounded-full {{ $bar }}" style="width: {{ $pct }}%"></div></div>
            </a>
        @endforeach
    </div>

    <x-data-card class="hidden md:block" title="Daftar Stok" subtitle="{{ $paginated->total() }} barang">
        <div class="overflow-x-auto">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Barang</th>
                        <th>Kategori</th>
                        <th class="th-right">Stok Sekarang</th>
                        <th class="th-right">Stok Minimum</th>
                        <th class="th-center">Status</th>
                        <th class="th-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($paginated as $m)
                        @php
                            $balance = $m->current_stock;
                            $isEmpty = $balance <= 0;
                            $isLow = $m->is_low_stock;
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('stock.show', $m) }}" class="font-semibold text-charcoal-900 hover:text-brand-600">{{ $m->name }}</a>
                                @if ($m->code)<p class="font-mono text-xs text-charcoal-400">{{ $m->code }}</p>@endif
                            </td>
                            <td class="text-charcoal-500">{{ $m->category?->name ?? '—' }}</td>
                            <td class="text-right">
                                <span class="{{ $isEmpty ? 'font-bold text-rose-600' : ($isLow ? 'font-bold text-amber-600' : 'font-semibold text-charcoal-900') }}">{{ $qty($balance) }}</span>
                                <span class="ml-1 text-xs text-charcoal-400">{{ $m->unit?->name }}</span>
                            </td>
                            <td class="text-right text-charcoal-500">{{ $m->minimum_stock !== null ? $qty($m->minimum_stock) . ' ' . ($m->unit?->name ?? '') : '—' }}</td>
                            <td class="text-center">
                                <span class="{{ $isEmpty ? 'badge-danger' : ($isLow ? 'badge-warning' : 'badge-success') }}">{{ $isEmpty ? 'Habis' : ($isLow ? 'Menipis' : 'Aman') }}</span>
                            </td>
                            <td>
                                <div class="flex justify-end gap-1.5">
                                    <a href="{{ route('stock.show', $m) }}" class="btn-secondary !min-h-[36px] !px-3 !py-1.5">Riwayat</a>
                                    <a href="{{ route('stock.create', ['material_id' => $m->id]) }}" class="btn-ghost !min-h-[36px] !px-3 !py-1.5">Koreksi</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-data-card>

    <div class="mt-4">{{ $paginated->links() }}</div>
@endif
@endsection
