@extends('layouts.app')
@section('content')
@php
    $rp = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');
    $qty = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, ',', '.'), '0'), ',');
    // Daftar belanja per supplier untuk dikirim via WhatsApp
    $bySupplier = $rows->groupBy(fn ($r) => $r['supplier'] ?? 'Belum ada supplier');
@endphp

<x-page-header title="Saran Kulakan" subtitle="Barang di bawah stok minimum atau habis tapi laku 30 hari terakhir" />

@if($rows->isEmpty())
    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-10 text-center text-emerald-800">Semua stok aman. Atur <b>Stok Minimum</b> di data barang agar peringatan lebih tepat.</div>
@else
    <div class="space-y-5">
        @foreach($bySupplier as $supplier => $items)
            @php
                $phone = preg_replace('/\D/', '', (string) ($items->first()['supplier_phone'] ?? ''));
                if (str_starts_with($phone, '0')) { $phone = '62' . substr($phone, 1); }
                $order = "Halo {$supplier}, dari " . config('store.name') . " mau pesan:\n" . $items->map(fn ($r) => '- ' . $r['material']->name . ' ± ' . $qty($r['suggested_qty']) . ' ' . $r['material']->baseUnitName())->implode("\n");
            @endphp
            <x-data-card :title="$supplier">
                <x-slot name="actions">
                    @if($supplier !== 'Belum ada supplier')
                        <a href="https://wa.me/{{ $phone }}?text={{ rawurlencode($order) }}" target="_blank" rel="noopener" class="btn-success">Pesan via WhatsApp</a>
                    @endif
                </x-slot>
                <div class="overflow-x-auto">
                    <table class="tbl">
                        <thead><tr><th>Barang</th><th class="th-right">Stok</th><th class="th-right">Minimum</th><th class="th-right">Terjual 30 hr</th><th class="th-right">Saran beli</th><th class="th-right">Harga termurah</th></tr></thead>
                        <tbody>
                        @foreach($items as $r)
                            <tr>
                                <td class="font-semibold">{{ $r['material']->name }}</td>
                                <td class="text-right {{ $r['stock'] <= 0 ? 'font-bold text-rose-600' : 'text-amber-600' }}">{{ $qty($r['stock']) }} {{ $r['material']->baseUnitName() }}</td>
                                <td class="text-right">{{ $r['minimum'] !== null ? $qty($r['minimum']) : '-' }}</td>
                                <td class="text-right">{{ $qty($r['sold_30d']) }}</td>
                                <td class="text-right font-bold">{{ $qty($r['suggested_qty']) }}</td>
                                <td class="text-right">{{ $r['price'] ? $rp($r['price']) . ' / ' . $r['price_unit'] : '-' }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </x-data-card>
        @endforeach
    </div>
@endif
@endsection
