@extends('layouts.app')
@section('content')
@php
    $rp = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');
    $qty = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, ',', '.'), '0'), ',');
@endphp

<x-page-header title="Barang Terlaris" subtitle="Diurutkan dari qty terjual (setelah retur)" />

@include('store.reports._period')

<x-data-card>
    <div class="overflow-x-auto">
        <table class="tbl">
            <thead><tr><th>#</th><th>Barang</th><th class="th-right">Qty (satuan dasar)</th><th class="th-right">Omzet</th><th class="th-right">Laba kotor</th></tr></thead>
            <tbody>
            @forelse($top as $i => $t)
                <tr>
                    <td class="text-slate-400">{{ $i + 1 }}</td>
                    <td class="font-semibold">{{ $t['name'] }}</td>
                    <td class="text-right">{{ $qty($t['qty_base']) }}</td>
                    <td class="text-right">{{ $rp($t['omzet']) }}</td>
                    <td class="text-right {{ $t['profit'] >= 0 ? 'text-emerald-700' : 'text-rose-600' }}">{{ $rp($t['profit']) }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="py-10 text-center text-slate-400">Belum ada penjualan pada periode ini.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</x-data-card>
@endsection
