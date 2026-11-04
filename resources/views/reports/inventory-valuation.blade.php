@extends('layouts.app')

@section('title', 'Valuasi Inventaris')

@section('content')

<x-page-header title="Valuasi Inventaris" subtitle="Nilai stok berdasarkan metode harga rata-rata tertimbang (WAC — Weighted Average Cost)">
    <x-slot name="actions">
        <a href="{{ route('inventory-valuation.export-excel', request()->query()) }}"
           class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 transition-colors">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
            </svg>
            Export Excel
        </a>
    </x-slot>
</x-page-header>

{{-- Filter --}}
<form method="GET" class="flex items-center gap-3 flex-wrap mb-6">
    <select name="warehouse_id" class="rounded-lg border border-slate-200 text-sm px-3 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
        <option value="">Semua Gudang</option>
        @foreach($warehouses as $wh)
            <option value="{{ $wh->id }}" @selected($warehouseId == $wh->id)>{{ $wh->name }}</option>
        @endforeach
    </select>
    <input type="date" name="as_of" value="{{ $asOfDate }}"
           class="rounded-lg border border-slate-200 text-sm px-3 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
    <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700">Tampilkan</button>
</form>

{{-- Low Stock Alert --}}
@if($lowStock->isNotEmpty())
<div class="mb-5 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700 font-medium">
    ⚠ <strong>{{ $lowStock->count() }} material</strong> memiliki stok di bawah batas minimum. Segera lakukan pembelian ulang.
</div>
@endif

{{-- Summary Cards --}}
<div class="grid grid-cols-2 gap-4 sm:grid-cols-4 mb-6">
    <x-data-card>
        <div class="p-5 flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-100 shrink-0">
                <svg class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-slate-500">Total Nilai Stok</p>
                <p class="text-lg font-extrabold text-indigo-700">Rp {{ number_format($summary['total_value'] / 1e6, 1, ',', '.') }}jt</p>
                <p class="text-xs text-slate-400">Rp {{ number_format($summary['total_value'], 0, ',', '.') }}</p>
            </div>
        </div>
    </x-data-card>
    <x-data-card>
        <div class="p-5 flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-teal-100 shrink-0">
                <svg class="h-5 w-5 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-slate-500">Jenis Material</p>
                <p class="text-2xl font-extrabold text-slate-800">{{ $summary['total_items'] }}</p>
            </div>
        </div>
    </x-data-card>
    <x-data-card>
        <div class="p-5 flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-red-100 shrink-0">
                <svg class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-slate-500">Stok Dibawah Minimum</p>
                <p class="text-2xl font-extrabold text-red-600">{{ $lowStock->count() }}</p>
            </div>
        </div>
    </x-data-card>
    <x-data-card>
        <div class="p-5 flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 shrink-0">
                <svg class="h-5 w-5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-slate-500">Per Tanggal</p>
                <p class="text-sm font-bold text-slate-700">{{ \Carbon\Carbon::parse($asOfDate)->translatedFormat('d M Y') }}</p>
                <p class="text-xs text-slate-400">{{ $warehouseId ? $warehouses->find($warehouseId)?->name : 'Semua Gudang' }}</p>
            </div>
        </div>
    </x-data-card>
</div>

{{-- Inventory Table --}}
<x-data-card title="Daftar Nilai Stok Material" subtitle="Harga rata-rata tertimbang (WAC)" class="mb-5">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50/60">
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 text-xs uppercase">Material</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 text-xs uppercase">Gudang</th>
                    <th class="px-4 py-3 text-center font-semibold text-slate-600 text-xs uppercase">Satuan</th>
                    <th class="px-4 py-3 text-right font-semibold text-slate-600 text-xs uppercase">Qty On Hand</th>
                    <th class="px-4 py-3 text-right font-semibold text-slate-600 text-xs uppercase">Harga Rata-rata</th>
                    <th class="px-4 py-3 text-right font-semibold text-indigo-600 text-xs uppercase">Total Nilai</th>
                    <th class="px-4 py-3 text-center font-semibold text-slate-600 text-xs uppercase">Terakhir Update</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @php $grandTotal = 0; @endphp
                @forelse($items as $row)
                @php $grandTotal += $row->total_value; @endphp
                <tr class="hover:bg-slate-50 {{ $row->qty_on_hand <= 0 ? 'bg-red-50/30' : '' }}">
                    <td class="px-4 py-2.5 font-medium text-slate-800">
                        {{ $row->material_name_snapshot }}
                        @if($row->qty_on_hand <= 0)
                            <span class="ml-1 inline-flex text-xs bg-red-100 text-red-600 px-1.5 py-0.5 rounded font-semibold">Habis</span>
                        @endif
                    </td>
                    <td class="px-4 py-2.5 text-slate-500 text-xs">
                        {{ $row->warehouse_id ? ($warehouses->find($row->warehouse_id)?->name ?? '—') : 'Semua' }}
                    </td>
                    <td class="px-4 py-2.5 text-center text-slate-500 text-xs">{{ $row->unit_name_snapshot }}</td>
                    <td class="px-4 py-2.5 text-right font-semibold {{ $row->qty_on_hand <= 0 ? 'text-red-600' : 'text-slate-800' }}">
                        {{ number_format($row->qty_on_hand, 2, ',', '.') }}
                    </td>
                    <td class="px-4 py-2.5 text-right text-slate-600">Rp {{ number_format($row->avg_cost, 0, ',', '.') }}</td>
                    <td class="px-4 py-2.5 text-right font-bold text-indigo-700">Rp {{ number_format($row->total_value, 0, ',', '.') }}</td>
                    <td class="px-4 py-2.5 text-center text-xs text-slate-400">
                        {{ $row->last_updated_at ? \Carbon\Carbon::parse($row->last_updated_at)->format('d/m/Y') : '—' }}
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-10 text-center text-slate-400">Tidak ada data stok inventaris.</td></tr>
                @endforelse
                @if($items->isNotEmpty())
                <tr class="bg-indigo-50 font-extrabold border-t-2 border-indigo-200">
                    <td colspan="5" class="px-4 py-3 text-right text-slate-700 uppercase tracking-wide text-sm">TOTAL NILAI INVENTARIS</td>
                    <td class="px-4 py-3 text-right text-indigo-800 text-base">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
                    <td></td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</x-data-card>

{{-- Low Stock Alert Table --}}
@if($lowStock->isNotEmpty())
<x-data-card title="⚠ Peringatan Stok Minimum" subtitle="{{ $lowStock->count() }} material perlu segera dipesan ulang">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-red-50">
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 text-xs uppercase">Material</th>
                    <th class="px-4 py-3 text-right font-semibold text-slate-600 text-xs uppercase">Stok Sekarang</th>
                    <th class="px-4 py-3 text-right font-semibold text-slate-600 text-xs uppercase">Stok Minimum</th>
                    <th class="px-4 py-3 text-right font-semibold text-slate-600 text-xs uppercase">Kekurangan</th>
                    <th class="px-4 py-3 text-center font-semibold text-slate-600 text-xs uppercase">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach($lowStock as $mat)
                <tr class="bg-red-50/60 hover:bg-red-50">
                    <td class="px-4 py-2.5 font-semibold text-slate-800">{{ $mat->name }}</td>
                    <td class="px-4 py-2.5 text-right font-bold text-red-600">{{ number_format($mat->qty_on_hand ?? 0, 2, ',', '.') }}</td>
                    <td class="px-4 py-2.5 text-right text-slate-600">{{ number_format($mat->minimum_stock, 2, ',', '.') }}</td>
                    <td class="px-4 py-2.5 text-right font-semibold text-red-700">
                        {{ number_format(max(0, $mat->minimum_stock - ($mat->qty_on_hand ?? 0)), 2, ',', '.') }}
                    </td>
                    <td class="px-4 py-2.5 text-center">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold {{ ($mat->qty_on_hand ?? 0) <= 0 ? 'bg-red-200 text-red-800' : 'bg-amber-100 text-amber-700' }}">
                            {{ ($mat->qty_on_hand ?? 0) <= 0 ? 'HABIS' : 'Kurang' }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-data-card>
@endif

@endsection
