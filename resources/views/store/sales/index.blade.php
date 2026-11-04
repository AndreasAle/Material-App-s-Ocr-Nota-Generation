@extends('layouts.app')
@section('content')
@php $rp = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.'); @endphp

<x-page-header title="Riwayat Penjualan" subtitle="Semua transaksi kasir">
    <x-slot name="actions">
        <a href="{{ route('store.pos') }}" class="btn-primary">+ Transaksi Kasir</a>
    </x-slot>
</x-page-header>

<x-data-card class="mb-4">
    <form method="GET" class="grid grid-cols-2 items-end gap-3 px-4 py-4 sm:flex sm:flex-wrap sm:px-5">
        <div><label class="form-label text-xs">Dari</label><input type="date" name="from" value="{{ $from }}" class="form-input"></div>
        <div><label class="form-label text-xs">Sampai</label><input type="date" name="to" value="{{ $to }}" class="form-input"></div>
        <div>
            <label class="form-label text-xs">Status</label>
            <select name="status" class="form-select">
                <option value="">Semua</option>
                <option value="paid" @selected(request('status') === 'paid')>Lunas</option>
                <option value="partial" @selected(request('status') === 'partial')>Bon Sebagian</option>
                <option value="unpaid" @selected(request('status') === 'unpaid')>Bon</option>
            </select>
        </div>
        <div class="col-span-2 min-w-[160px] flex-1"><label class="form-label text-xs">Cari</label><input type="text" name="q" value="{{ request('q') }}" placeholder="No. / pelanggan" class="form-input"></div>
        <button class="btn-primary col-span-2 sm:col-span-1">Tampilkan</button>
    </form>
</x-data-card>

<div class="mb-4 grid grid-cols-2 gap-2 sm:grid-cols-3 sm:gap-3">
    <div class="stat-tile !p-3"><p class="stat-tile-label">Transaksi</p><p class="stat-tile-value !mt-1">{{ $summary['count'] }}</p></div>
    <div class="stat-tile !p-3"><p class="stat-tile-label">Penjualan</p><p class="stat-tile-value !mt-1 text-emerald-700">{{ $rp($summary['total']) }}</p></div>
    <div class="stat-tile col-span-2 !p-3 sm:col-span-1"><p class="stat-tile-label">Sisa bon</p><p class="stat-tile-value !mt-1 text-amber-700">{{ $rp($summary['bon']) }}</p></div>
</div>

<div class="space-y-2 md:hidden">
    @forelse($sales as $sale)
        <a href="{{ route('store.sales.show', $sale) }}" class="card flex items-center gap-3 p-3.5 active:bg-charcoal-50">
            <span class="min-w-0 flex-1">
                <span class="block truncate text-[15px] font-bold text-charcoal-900">{{ $sale->customer->name ?? 'Pembeli umum' }}</span>
                <span class="block text-xs text-charcoal-500">{{ $sale->sale_no }} · {{ $sale->sale_date->format('d/m H:i') }}</span>
            </span>
            <span class="shrink-0 text-right">
                <span class="block text-[15px] font-extrabold text-charcoal-900">{{ $rp($sale->netTotal()) }}</span>
                <span class="badge {{ $sale->payment_status === 'paid' ? 'badge-success' : ($sale->isOverdue() ? 'badge-danger' : 'badge-warning') }}">{{ $sale->paymentStatusLabel() }}</span>
            </span>
        </a>
    @empty
        <div class="card px-5 py-10 text-center text-sm text-charcoal-500">Belum ada penjualan pada tanggal ini.</div>
    @endforelse
    <div class="pt-2">{{ $sales->links() }}</div>
</div>

<x-data-card class="hidden md:block">
    <div class="overflow-x-auto">
        <table class="tbl">
            <thead><tr><th>No.</th><th>Waktu</th><th>Pelanggan</th><th>Kasir</th><th class="th-right">Total</th><th class="th-center">Status</th></tr></thead>
            <tbody>
            @forelse($sales as $sale)
                <tr>
                    <td><a href="{{ route('store.sales.show', $sale) }}" class="font-semibold text-indigo-600">{{ $sale->sale_no }}</a></td>
                    <td class="text-xs text-slate-500">{{ $sale->sale_date->format('d/m/Y H:i') }}</td>
                    <td>{{ $sale->customer->name ?? 'Umum' }}</td>
                    <td class="text-xs">{{ $sale->user->name ?? '-' }}</td>
                    <td class="text-right font-semibold">{{ $rp($sale->netTotal()) }}</td>
                    <td class="text-center">
                        <span class="badge {{ $sale->payment_status === 'paid' ? 'badge-success' : ($sale->isOverdue() ? 'badge-danger' : 'badge-warning') }}">{{ $sale->paymentStatusLabel() }}</span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="py-10 text-center text-slate-400">Belum ada penjualan pada rentang ini.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="border-t border-slate-100 px-5 py-4">{{ $sales->links() }}</div>
</x-data-card>
@endsection
