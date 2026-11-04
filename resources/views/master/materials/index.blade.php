@extends('layouts.app')
@section('title', 'Data Barang & Harga')
@section('content')
@php
    $rp = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');
    $qty = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, ',', '.'), '0'), ',');
    $filter = request('harga');
@endphp

<x-page-header title="Data Barang & Harga" subtitle="Daftar barang yang dijual: harga jual, harga beli terakhir, dan stok.">
    <x-slot:actions>
        <a href="{{ route('materials.create') }}" class="btn-primary">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Tambah Barang
        </a>
    </x-slot:actions>
</x-page-header>

<x-help title="Cara mengatur barang & harga">
    <li>Barang baru bisa ditambah manual di sini, atau otomatis saat Anda <b>foto nota supplier</b>.</li>
    <li>Isi <b>Harga Eceran</b> supaya barang bisa dijual di kasir. Harga grosir opsional.</li>
    <li>Barang dibeli per ikat/dus tapi dijual per batang/kaleng? Tambahkan di <b>Satuan Lain</b>.</li>
    <li>Tanda <b class="text-rose-700">merah</b> = harga jual sudah di bawah harga beli terakhir.</li>
</x-help>

{{-- Pencarian & filter cepat --}}
<form method="GET" action="{{ route('materials.index') }}" class="mb-3">
    <div class="relative">
        <svg class="pointer-events-none absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-charcoal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z"/></svg>
        <input type="search" name="q" value="{{ $q ?? '' }}" placeholder="Cari nama atau kode barang…" class="form-input pl-11">
        @if($filter)<input type="hidden" name="harga" value="{{ $filter }}">@endif
    </div>
</form>
<div class="-mx-3 mb-4 flex gap-2 overflow-x-auto px-3 pb-1 sm:mx-0 sm:px-0">
    <a href="{{ route('materials.index', array_filter(['q' => $q])) }}" class="chip shrink-0 {{ ! $filter ? '!bg-charcoal-900 !text-white' : '' }}">Semua</a>
    <a href="{{ route('materials.index', array_filter(['q' => $q, 'harga' => 'kosong'])) }}" class="chip shrink-0 {{ $filter === 'kosong' ? '!bg-amber-500 !text-white' : '' }}">Belum ada harga ({{ $counts['kosong'] }})</a>
    <a href="{{ route('materials.index', array_filter(['q' => $q, 'harga' => 'rugi'])) }}" class="chip shrink-0 {{ $filter === 'rugi' ? '!bg-rose-600 !text-white' : '' }}">Harga di bawah modal ({{ $counts['rugi'] }})</a>
    @foreach($categories as $cat)
        <a href="{{ route('materials.index', array_filter(['q' => $q, 'category_id' => $cat->id])) }}" class="chip shrink-0 {{ (string) request('category_id') === (string) $cat->id ? '!bg-brand-600 !text-white' : '' }}">{{ $cat->name }}</a>
    @endforeach
</div>

{{-- Daftar: kartu di HP, tabel di layar lebar --}}
<div class="space-y-2 md:hidden">
    @forelse($materials as $item)
        @php
            $stock = (float) ($stockMap[$item->id] ?? 0);
            $loss = (float) $item->sell_price > 0 && $item->last_purchase_price !== null && (float) $item->last_purchase_price >= (float) $item->sell_price;
        @endphp
        <a href="{{ route('materials.edit', $item) }}" class="card flex items-center gap-3 p-3.5 active:bg-charcoal-50">
            <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $loss ? 'bg-rose-50 text-rose-600' : 'bg-brand-50 text-brand-600' }}">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg>
            </span>
            <span class="min-w-0 flex-1">
                <span class="block truncate text-[15px] font-bold text-charcoal-900">{{ $item->name }}</span>
                <span class="block text-xs text-charcoal-500">{{ $item->code }} · Stok <b class="{{ $stock <= 0 ? 'text-rose-600' : 'text-charcoal-700' }}">{{ $qty($stock) }} {{ $item->unit?->name }}</b></span>
                @if($item->last_purchase_price)
                    <span class="block text-xs {{ $loss ? 'font-bold text-rose-600' : 'text-charcoal-400' }}">Beli terakhir {{ $rp($item->last_purchase_price) }}</span>
                @endif
            </span>
            <span class="shrink-0 text-right">
                @if((float) $item->sell_price > 0)
                    <span class="block text-[15px] font-extrabold {{ $loss ? 'text-rose-600' : 'text-charcoal-900' }}">{{ $rp($item->sell_price) }}</span>
                    <span class="block text-[11px] text-charcoal-400">/{{ $item->unit?->name }}</span>
                @else
                    <span class="badge-warning">Isi harga</span>
                @endif
            </span>
        </a>
    @empty
        <div class="card px-5 py-10 text-center text-sm text-charcoal-500">Belum ada barang. <a href="{{ route('materials.create') }}" class="font-bold text-brand-600">Tambah barang</a> atau foto nota supplier.</div>
    @endforelse
</div>

<x-data-card class="hidden md:block" title="Daftar Barang" subtitle="{{ $materials->total() }} barang">
    <div class="overflow-x-auto">
        <table class="tbl">
            <thead>
                <tr>
                    <th>Barang</th>
                    <th>Kategori</th>
                    <th class="th-right">Stok</th>
                    <th class="th-right">Harga Beli Terakhir</th>
                    <th class="th-right">Harga Jual</th>
                    <th class="th-center">Status</th>
                    <th class="th-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse($materials as $item)
                @php
                    $stock = (float) ($stockMap[$item->id] ?? 0);
                    $loss = (float) $item->sell_price > 0 && $item->last_purchase_price !== null && (float) $item->last_purchase_price >= (float) $item->sell_price;
                @endphp
                <tr>
                    <td>
                        <a href="{{ route('materials.show', $item) }}" class="font-semibold text-charcoal-900 hover:text-brand-600">{{ $item->name }}</a>
                        <p class="font-mono text-xs text-charcoal-400">{{ $item->code }}</p>
                    </td>
                    <td>@if($item->category)<span class="chip">{{ $item->category->name }}</span>@else — @endif</td>
                    <td class="text-right {{ $stock <= 0 ? 'font-bold text-rose-600' : '' }}">{{ $qty($stock) }} {{ $item->unit?->name }}</td>
                    <td class="text-right text-charcoal-600">{{ $item->last_purchase_price ? $rp($item->last_purchase_price) : '—' }}</td>
                    <td class="text-right font-bold {{ $loss ? 'text-rose-600' : 'text-charcoal-900' }}">
                        {{ (float) $item->sell_price > 0 ? $rp($item->sell_price) : '' }}
                        @if((float) $item->sell_price <= 0)<span class="badge-warning">Belum ada</span>@endif
                    </td>
                    <td class="text-center">
                        @if($item->is_active)<span class="badge-success">Aktif</span>@else<span class="badge-neutral">Nonaktif</span>@endif
                    </td>
                    <td>
                        <div class="flex items-center justify-end gap-1.5">
                            <a href="{{ route('materials.edit', $item) }}" class="btn-secondary !min-h-[36px] !px-3 !py-1.5">Ubah</a>
                            <form method="POST" action="{{ route('materials.destroy', $item) }}" onsubmit="return confirm('Hapus barang ini?')">
                                @csrf @method('DELETE')
                                <button class="btn-ghost !min-h-[36px] !px-3 !py-1.5 text-rose-600">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="py-12 text-center text-charcoal-500">Belum ada barang.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</x-data-card>

<div class="mt-4">{{ $materials->links() }}</div>
@endsection
