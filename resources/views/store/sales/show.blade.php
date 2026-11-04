@extends('layouts.app')
@section('content')
@php
    $rp = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');
    $qty = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, ',', '.'), '0'), ',');
    $returnable = $sale->items->filter(fn ($i) => $i->returnableQty() > 0);
@endphp

<x-page-header :title="'Penjualan ' . $sale->sale_no" :subtitle="$sale->sale_date->format('d M Y H:i') . ' · Kasir ' . ($sale->user->name ?? '-')">
    <x-slot name="actions">
        <a href="{{ route('store.sales.receipt', $sale) }}" class="btn-primary">Struk</a>
        <a href="{{ route('store.sales.index') }}" class="btn-secondary">← Riwayat</a>
    </x-slot>
</x-page-header>

<div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
    <div class="space-y-5 lg:col-span-2">
        <x-data-card title="Barang">
            <div class="overflow-x-auto">
                <table class="tbl">
                    <thead><tr><th>Barang</th><th class="th-right">Qty</th><th class="th-right">Harga</th><th class="th-right">Subtotal</th><th class="th-right">Diretur</th></tr></thead>
                    <tbody>
                    @foreach($sale->items as $item)
                        <tr>
                            <td>{{ $item->material_name_snapshot }}</td>
                            <td class="text-right">{{ $qty($item->qty) }} {{ $item->unit_name }}</td>
                            <td class="text-right">{{ $rp($item->price) }}</td>
                            <td class="text-right font-semibold">{{ $rp($item->subtotal) }}</td>
                            <td class="text-right text-rose-600">{{ (float) $item->qty_returned > 0 ? $qty($item->qty_returned) : '-' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="space-y-1 border-t border-slate-100 px-5 py-4 text-sm">
                <div class="flex justify-between"><span class="text-slate-500">Subtotal</span><span>{{ $rp($sale->subtotal) }}</span></div>
                @if((float) $sale->discount_amount > 0)
                    <div class="flex justify-between"><span class="text-slate-500">Diskon</span><span>-{{ $rp($sale->discount_amount) }}</span></div>
                @endif
                <div class="flex justify-between font-bold"><span>Total</span><span>{{ $rp($sale->grand_total) }}</span></div>
                @if((float) $sale->returned_total > 0)
                    <div class="flex justify-between text-rose-600"><span>Retur</span><span>-{{ $rp($sale->returned_total) }}</span></div>
                @endif
                @if(auth()->user()->canViewFinancials())
                    <div class="flex justify-between text-xs text-slate-500"><span>Laba kotor</span><span>{{ $rp($sale->netTotal() - ((float) $sale->cost_total - (float) $sale->returns->sum('cost_total'))) }}</span></div>
                @endif
            </div>
        </x-data-card>

        @if($returnable->isNotEmpty())
        <x-data-card title="Retur Penjualan" subtitle="Barang kembali ke stok. Retur mengurangi bon dulu, sisanya dikembalikan tunai.">
            <form method="POST" action="{{ route('store.sales.return', $sale) }}" class="space-y-3 px-5 py-4" onsubmit="return confirm('Simpan retur?')">
                @csrf
                @foreach($returnable as $item)
                    <div class="flex items-center gap-3">
                        <span class="flex-1 text-sm">{{ $item->material_name_snapshot }} <span class="text-xs text-slate-400">(maks {{ $qty($item->returnableQty()) }} {{ $item->unit_name }})</span></span>
                        <input type="number" name="qty[{{ $item->id }}]" min="0" max="{{ $item->returnableQty() }}" step="0.01" value="0" class="form-input w-28 text-right">
                    </div>
                @endforeach
                <input type="text" name="reason" placeholder="Alasan retur (opsional)" class="form-input">
                @error('items') <p class="form-error">{{ $message }}</p> @enderror
                <button class="btn-danger">Simpan Retur</button>
            </form>
        </x-data-card>
        @endif

        @if($sale->returns->isNotEmpty())
        <x-data-card title="Riwayat Retur">
            <div class="divide-y divide-slate-100">
                @foreach($sale->returns as $ret)
                    <div class="px-5 py-3 text-sm">
                        <div class="flex justify-between"><b>{{ $ret->return_no }}</b><span>{{ $ret->return_date->format('d/m/Y') }}</span></div>
                        <div class="text-xs text-slate-500">
                            @foreach($ret->items as $ri) {{ $ri->saleItem->material_name_snapshot ?? '-' }} ×{{ $qty($ri->qty) }}@if(! $loop->last), @endif @endforeach
                            · Nilai {{ $rp($ret->total) }} · Uang kembali {{ $rp($ret->refund_amount) }}
                        </div>
                    </div>
                @endforeach
            </div>
        </x-data-card>
        @endif
    </div>

    <div class="space-y-5">
        <x-data-card title="Pembayaran">
            <div class="space-y-2 px-5 py-4 text-sm">
                <div class="flex justify-between"><span class="text-slate-500">Pelanggan</span>
                    @if($sale->customer)<a class="font-semibold text-indigo-600" href="{{ route('store.customers.show', $sale->customer) }}">{{ $sale->customer->name }}</a>@else<span>Umum</span>@endif
                </div>
                <div class="flex justify-between"><span class="text-slate-500">Metode</span><span>{{ $sale->paymentMethodLabel() }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Dibayar</span><span class="text-emerald-700">{{ $rp($sale->paid_amount) }}</span></div>
                <div class="flex justify-between font-bold"><span>Sisa bon</span><span class="{{ (float) $sale->remaining_amount > 0 ? 'text-amber-700' : 'text-emerald-700' }}">{{ $rp($sale->remaining_amount) }}</span></div>
                @if($sale->due_date && (float) $sale->remaining_amount > 0)
                    <div class="flex justify-between"><span class="text-slate-500">Jatuh tempo</span><span class="{{ $sale->isOverdue() ? 'font-bold text-rose-600' : '' }}">{{ $sale->due_date->format('d/m/Y') }}</span></div>
                @endif
            </div>
            @if((float) $sale->remaining_amount > 0 && $sale->customer)
                <form method="POST" action="{{ route('store.sales.payment', $sale) }}" class="space-y-2 border-t border-slate-100 px-5 py-4">
                    @csrf
                    <input type="number" name="amount" min="1" max="{{ (float) $sale->remaining_amount }}" value="{{ (float) $sale->remaining_amount }}" class="form-input" required>
                    <select name="payment_method" class="form-select"><option value="cash">Tunai</option><option value="transfer">Transfer</option></select>
                    @error('amount') <p class="form-error">{{ $message }}</p> @enderror
                    <button class="btn-success w-full">Catat Pembayaran Bon</button>
                </form>
            @endif
            @if($sale->payments->isNotEmpty())
                <div class="divide-y divide-slate-100 border-t border-slate-100">
                    @foreach($sale->payments as $p)
                        <div class="flex justify-between px-5 py-2 text-xs"><span>{{ $p->payment_date->format('d/m/Y') }} · {{ $p->payment_method === 'transfer' ? 'Transfer' : 'Tunai' }}</span><span class="font-semibold">{{ $rp($p->amount) }}</span></div>
                    @endforeach
                </div>
            @endif
        </x-data-card>
    </div>
</div>
@endsection
