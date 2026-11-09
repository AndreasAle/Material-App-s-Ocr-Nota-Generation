@extends('layouts.app')

@section('title', 'Stok Barang')

@section('content')
@php $qty = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, ',', '.'), '0'), ','); @endphp

<div class="stock-dashboard">
<div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div class="min-w-0">
        <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-brand-600">Inventori</p>
        <h1 class="mt-1 text-xl font-extrabold tracking-tight text-charcoal-900 sm:text-2xl">Stok Barang</h1>
        <p class="mt-1 text-xs leading-5 text-charcoal-500 sm:text-sm">Pantau ketersediaan barang dan tindak lanjuti stok yang mulai menipis.</p>
    </div>
    <a href="{{ route('stock.create') }}" class="btn-primary shrink-0">
        <x-icon name="plus" class="h-4 w-4" stroke="2.2" />
        Input / Koreksi Stok
    </a>
</div>

<x-help title="Kapan stok berubah?">
    <li><b>Bertambah otomatis</b> saat Anda menyimpan nota supplier dengan centang “Barang sudah diterima”.</li>
    <li><b>Berkurang otomatis</b> setiap ada penjualan di Kasir, dan kembali saat ada retur.</li>
    <li>Pakai <b>Koreksi / Input Stok</b> hanya untuk stok awal, barang rusak/hilang, atau hasil hitung ulang gudang.</li>
    <li>Isi <b>stok minimum</b> di halaman barang agar muncul peringatan “perlu dibeli lagi”.</li>
</x-help>

{{-- Ringkasan (bisa diketuk sebagai filter) --}}
<div class="mb-4 grid grid-cols-3 gap-2.5 sm:gap-3">
    <a href="{{ route('stock.index') }}" class="stock-summary {{ $alert !== '1' ? 'active' : '' }}">
        <span class="stock-summary-icon bg-brand-50 text-brand-600"><x-icon name="box" /></span>
        <span><span class="stock-summary-label">Total barang</span><strong class="stock-summary-value">{{ number_format($totalMaterials) }}</strong></span>
    </a>
    <a href="{{ route('stock.index', ['alert' => '1']) }}" class="stock-summary {{ $alert === '1' ? 'active-warning' : '' }}">
        <span class="stock-summary-icon bg-amber-50 text-amber-600"><x-icon name="alert" /></span>
        <span><span class="stock-summary-label">Menipis</span><strong class="stock-summary-value text-amber-700">{{ $lowStockCount }}</strong></span>
    </a>
    <div class="stock-summary">
        <span class="stock-summary-icon bg-rose-50 text-rose-600"><x-icon name="box" /></span>
        <span><span class="stock-summary-label">Stok habis</span><strong class="stock-summary-value text-rose-700">{{ $zeroStockCount }}</strong></span>
    </div>
</div>

{{-- Cari --}}
<div class="mb-5 rounded-2xl border border-charcoal-200/80 bg-white p-3 shadow-sm sm:p-4">
<form method="GET" action="{{ route('stock.index') }}" class="flex gap-2">
    <div class="relative flex-1">
        <x-icon name="cube" class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-charcoal-400" />
        <input type="search" name="search" value="{{ $search }}" placeholder="Cari nama atau kode barang" class="form-input pl-10">
    </div>
    @if($alert === '1')<input type="hidden" name="alert" value="1">@endif
    <button class="btn-primary shrink-0 !px-4">Cari</button>
</form>
@if($categories->isNotEmpty())
<div class="mt-3 flex gap-2 overflow-x-auto pb-1">
    <a href="{{ route('stock.index', array_filter(['search' => $search, 'alert' => $alert])) }}" class="chip shrink-0 {{ ! $categoryId ? '!bg-charcoal-900 !text-white' : '' }}">Semua kategori</a>
    @foreach ($categories as $cat)
        <a href="{{ route('stock.index', array_filter(['search' => $search, 'alert' => $alert, 'category_id' => $cat->id])) }}" class="chip shrink-0 {{ (string) $categoryId === (string) $cat->id ? '!bg-brand-600 !text-white' : '' }}">{{ $cat->name }}</a>
    @endforeach
</div>
@endif
</div>

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
            <a href="{{ route('stock.show', $m) }}" class="stock-mobile-card">
                <div class="flex items-start gap-3">
                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $isEmpty ? 'bg-rose-50 text-rose-600' : ($isLow ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-600') }}"><x-icon name="box" class="h-4 w-4" /></span>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2"><p class="truncate text-sm font-bold text-charcoal-900">{{ $m->name }}</p><span class="{{ $isEmpty ? 'badge-danger' : ($isLow ? 'badge-warning' : 'badge-success') }} !px-2 !py-0.5 !text-[10px]">{{ $isEmpty ? 'Habis' : ($isLow ? 'Menipis' : 'Aman') }}</span></div>
                        <p class="mt-0.5 text-[11px] text-charcoal-500">{{ $m->category?->name ?? 'Tanpa kategori' }}{{ $m->minimum_stock !== null ? ' · Minimum ' . $qty($m->minimum_stock) : '' }}</p>
                    </div>
                    <div class="shrink-0 text-right">
                        <p class="text-base font-extrabold leading-none {{ $isEmpty ? 'text-rose-600' : ($isLow ? 'text-amber-600' : 'text-charcoal-900') }}">{{ $qty($balance) }}</p>
                        <p class="mt-1 text-[11px] text-charcoal-500">{{ $m->unit?->name }}</p>
                    </div>
                </div>
                <div class="mt-2.5 h-1.5 rounded-full bg-charcoal-100"><div class="h-1.5 rounded-full {{ $bar }}" style="width: {{ $pct }}%"></div></div>
            </a>
        @endforeach
    </div>

    <x-data-card class="hidden md:block" title="Daftar Inventori" subtitle="{{ $paginated->total() }} barang tercatat">
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
</div>

<style>
    .stock-dashboard .stock-summary { display:flex; min-width:0; align-items:center; gap:.65rem; border:1px solid rgb(226 232 240); border-radius:1rem; background:#fff; padding:.8rem; box-shadow:0 1px 2px rgb(15 23 42 / .04); }
    .stock-dashboard .stock-summary.active { border-color:rgb(251 146 60); box-shadow:inset 0 0 0 1px rgb(251 146 60 / .35); }
    .stock-dashboard .stock-summary.active-warning { border-color:rgb(245 158 11); box-shadow:inset 0 0 0 1px rgb(245 158 11 / .3); }
    .stock-dashboard .stock-summary-icon { display:inline-flex; width:2.25rem; height:2.25rem; flex:none; align-items:center; justify-content:center; border-radius:.75rem; }
    .stock-dashboard .stock-summary-icon svg { width:1rem; height:1rem; }
    .stock-dashboard .stock-summary-label { display:block; white-space:nowrap; font-size:.65rem; line-height:.9rem; font-weight:600; color:rgb(100 116 139); }
    .stock-dashboard .stock-summary-value { display:block; margin-top:.1rem; font-size:1rem; line-height:1.25rem; font-weight:800; }
    .stock-dashboard .stock-mobile-card { display:block; border:1px solid rgb(226 232 240); border-radius:1rem; background:#fff; padding:.8rem; box-shadow:0 1px 2px rgb(15 23 42 / .04); }
    .stock-dashboard .tbl th { background:rgb(248 250 252); font-size:.68rem; letter-spacing:.04em; color:rgb(100 116 139); }
    .stock-dashboard .tbl td { font-size:.78rem; }
    @media (max-width: 420px) {
        .stock-dashboard .stock-summary { flex-direction:column; align-items:flex-start; gap:.4rem; padding:.7rem; }
        .stock-dashboard .stock-summary-icon { width:1.9rem; height:1.9rem; }
        .stock-dashboard .stock-summary-label { white-space:normal; }
    }
</style>
@endsection
