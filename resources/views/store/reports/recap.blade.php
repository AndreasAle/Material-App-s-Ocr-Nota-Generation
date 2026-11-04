@extends('layouts.app')
@section('content')
@php
    $rp = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');
    $qty = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, ',', '.'), '0'), ',');
    $maxOmzet = max(1, $daily->max('omzet'));
@endphp

<x-page-header title="Rekap Toko" subtitle="Omzet, laba kotor, uang masuk & keluar">
    <x-slot name="actions">
        <a href="{{ route('store.reports.recap.pdf', request()->query()) }}" class="btn-secondary">Unduh PDF</a>
    </x-slot>
</x-page-header>

@include('store.reports._period')

<div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
    <div class="rounded-2xl bg-indigo-600 p-4 text-white"><p class="text-xs opacity-80">Omzet bersih</p><p class="text-xl font-extrabold">{{ $rp($summary['omzet']) }}</p><p class="text-[11px] opacity-80">{{ $summary['transactions'] }} transaksi · retur {{ $rp($summary['returns']) }}</p></div>
    <div class="rounded-2xl bg-emerald-600 p-4 text-white"><p class="text-xs opacity-80">Laba kotor</p><p class="text-xl font-extrabold">{{ $rp($summary['gross_profit']) }}</p><p class="text-[11px] opacity-80">HPP {{ $rp($summary['hpp']) }} · margin {{ $summary['margin_pct'] }}%</p></div>
    <div class="rounded-2xl border border-slate-200 bg-white p-4"><p class="text-xs text-slate-500">Uang masuk</p><p class="text-xl font-extrabold text-emerald-700">{{ $rp($summary['cash_in']) }}</p><p class="text-[11px] text-slate-400">Termasuk pelunasan bon</p></div>
    <div class="rounded-2xl border border-slate-200 bg-white p-4"><p class="text-xs text-slate-500">Uang keluar</p><p class="text-xl font-extrabold text-rose-700">{{ $rp($summary['cash_out']) }}</p><p class="text-[11px] text-slate-400">Supplier {{ $rp($summary['supplier_paid']) }} · refund {{ $rp($summary['refunds']) }}</p></div>
</div>
<div class="mt-3 grid grid-cols-2 gap-3 lg:grid-cols-4">
    <div class="rounded-2xl border border-slate-200 bg-white p-4"><p class="text-xs text-slate-500">Selisih kas</p><p class="text-lg font-extrabold {{ $summary['net_cash'] >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">{{ $rp($summary['net_cash']) }}</p></div>
    <div class="rounded-2xl border border-slate-200 bg-white p-4"><p class="text-xs text-slate-500">Kulakan (pembelian)</p><p class="text-lg font-extrabold">{{ $rp($summary['purchases']) }}</p></div>
    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4"><p class="text-xs text-amber-800">Total bon pelanggan</p><p class="text-lg font-extrabold text-amber-900">{{ $rp($summary['receivable_total']) }}</p></div>
    <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4"><p class="text-xs text-rose-800">Total hutang supplier</p><p class="text-lg font-extrabold text-rose-900">{{ $rp($summary['payable_total']) }}</p></div>
</div>

<div class="mt-5 grid grid-cols-1 gap-5 lg:grid-cols-3">
    <x-data-card title="Per hari" class="lg:col-span-2">
        <div class="overflow-x-auto">
            <table class="tbl">
                <thead><tr><th>Tanggal</th><th class="th-right">Trx</th><th>Omzet</th><th class="th-right">Laba</th><th class="th-right">Masuk</th><th class="th-right">Keluar</th></tr></thead>
                <tbody>
                @foreach($daily->reverse() as $d)
                    <tr>
                        <td class="whitespace-nowrap text-xs">{{ \Carbon\Carbon::parse($d['date'])->translatedFormat('D, d M') }}</td>
                        <td class="text-right">{{ $d['count'] }}</td>
                        <td class="min-w-[140px]">
                            <div class="text-xs font-semibold">{{ $rp($d['omzet']) }}</div>
                            <div class="mt-1 h-1.5 rounded bg-slate-100"><div class="h-1.5 rounded bg-indigo-500" style="width: {{ max(0, round($d['omzet'] / $maxOmzet * 100)) }}%"></div></div>
                        </td>
                        <td class="text-right text-emerald-700">{{ $rp($d['profit']) }}</td>
                        <td class="text-right">{{ $rp($d['cash_in']) }}</td>
                        <td class="text-right text-rose-600">{{ $rp($d['cash_out']) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </x-data-card>

    <x-data-card title="10 barang terlaris">
        <div class="divide-y divide-slate-100">
            @forelse($top as $i => $t)
                <div class="flex justify-between px-5 py-2 text-sm"><span><b class="mr-1 text-slate-400">{{ $i + 1 }}</b>{{ $t['name'] }}</span><span class="font-semibold">{{ $qty($t['qty_base']) }}</span></div>
            @empty
                <p class="px-5 py-6 text-center text-sm text-slate-400">Belum ada penjualan.</p>
            @endforelse
        </div>
    </x-data-card>
</div>
@endsection
