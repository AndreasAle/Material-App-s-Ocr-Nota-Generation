@extends('layouts.app')
@section('title', 'Surat Jalan ' . $goodsReceipt->receipt_no)

@section('content')
@php
    $supplier = $goodsReceipt->purchaseOrder?->supplier
             ?? $goodsReceipt->purchaseTransaction?->supplier;
    $project  = $goodsReceipt->purchaseOrder?->project
             ?? $goodsReceipt->purchaseTransaction?->project;
    $statusConfig = match($goodsReceipt->status) {
        'complete' => ['badge' => 'badge-success', 'label' => 'Lengkap',        'bg' => 'bg-emerald-50', 'border' => 'border-emerald-200'],
        'partial'  => ['badge' => 'badge-warning', 'label' => 'Sebagian',       'bg' => 'bg-amber-50',   'border' => 'border-amber-200'],
        default    => ['badge' => 'badge-neutral', 'label' => 'Pending',        'bg' => 'bg-slate-50',   'border' => 'border-slate-200'],
    };
    $inspLabel = ['pending'=>'⏳ Belum Diperiksa','passed'=>'✅ Lulus QC','partial_pass'=>'⚠️ Lulus Sebagian','failed'=>'❌ Ditolak'];
    $inspBadge = ['pending'=>'badge-neutral','passed'=>'badge-success','partial_pass'=>'badge-warning','failed'=>'badge-danger'];
@endphp

<div class="space-y-6">
<x-page-header title="Surat Jalan" subtitle="{{ $goodsReceipt->receipt_no }}">
    <x-slot name="actions">
        {{-- Back link -- prefer PO, then transaction --}}
        @if($goodsReceipt->purchaseOrder)
        <a href="{{ route('purchase-orders.show', $goodsReceipt->purchaseOrder) }}" class="btn-secondary">← Kembali ke PO</a>
        @elseif($goodsReceipt->purchaseTransaction)
        <a href="{{ route('purchase-transactions.show', $goodsReceipt->purchaseTransaction) }}" class="btn-secondary">← Kembali</a>
        @else
        <a href="{{ route('goods-receipts.index') }}" class="btn-secondary">← Daftar</a>
        @endif

        @if(Route::has('goods-receipts.preview.pdf'))
        <a href="{{ route('goods-receipts.preview.pdf', $goodsReceipt) }}" target="_blank" class="btn-secondary">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12S5.25 5.25 12 5.25 21.75 12 21.75 12 18.75 18.75 12 18.75 2.25 12 2.25 12Z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15.75A3.75 3.75 0 1 0 12 8.25a3.75 3.75 0 0 0 0 7.5Z"/>
            </svg>
            Preview PDF
        </a>
        <a href="{{ route('goods-receipts.export.pdf', $goodsReceipt) }}" class="btn-primary">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
            </svg>
            Unduh PDF
        </a>
        @endif
    </x-slot>
</x-page-header>


{{-- Status Banner --}}
<div class="rounded-2xl border {{ $statusConfig['border'] }} {{ $statusConfig['bg'] }} px-5 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div class="flex items-center gap-3 flex-wrap">
        <span class="{{ $statusConfig['badge'] }}">{{ $statusConfig['label'] }}</span>
        @if($goodsReceipt->inspection_status)
        <span class="{{ $inspBadge[$goodsReceipt->inspection_status] ?? 'badge-neutral' }}">
            {{ $inspLabel[$goodsReceipt->inspection_status] ?? $goodsReceipt->inspection_status }}
        </span>
        @endif
        <span class="text-sm text-slate-600">
            Dibuat oleh <strong>{{ $goodsReceipt->creator?->name ?? '—' }}</strong>
        </span>
    </div>
    <div class="text-xs text-slate-400">
        {{ $goodsReceipt->created_at->translatedFormat('d F Y, H:i') }}
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

    {{-- Info Surat Jalan --}}
    <x-data-card title="Informasi Surat Jalan">
        <div class="px-5 py-4 space-y-3 text-sm">
            <div class="flex justify-between">
                <span class="text-slate-500">No. Surat Jalan</span>
                <span class="font-semibold font-mono text-slate-800">{{ $goodsReceipt->receipt_no }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Tanggal Terima</span>
                <span class="text-slate-800">{{ $goodsReceipt->receipt_date?->translatedFormat('d F Y') ?? '—' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Diterima Oleh</span>
                <span class="font-semibold text-slate-800">{{ $goodsReceipt->received_by ?? '—' }}</span>
            </div>
            @if($goodsReceipt->supplier_do_no)
            <div class="flex justify-between border-t border-slate-100 pt-3">
                <span class="text-slate-500">No. DO Supplier</span>
                <span class="font-mono text-slate-700">{{ $goodsReceipt->supplier_do_no }}</span>
            </div>
            @endif
            @if($goodsReceipt->supplier_do_date)
            <div class="flex justify-between">
                <span class="text-slate-500">Tanggal DO</span>
                <span class="text-slate-700">{{ $goodsReceipt->supplier_do_date?->translatedFormat('d F Y') }}</span>
            </div>
            @endif
            @if($goodsReceipt->inspector_name)
            <div class="flex justify-between border-t border-slate-100 pt-3">
                <span class="text-slate-500">Inspector / QC</span>
                <span class="font-semibold text-slate-700">{{ $goodsReceipt->inspector_name }}</span>
            </div>
            @endif
            @if($goodsReceipt->inspection_notes)
            <div class="border-t border-slate-100 pt-3">
                <p class="text-xs text-slate-500 mb-1">Catatan Inspeksi</p>
                <p class="text-slate-700 text-sm">{{ $goodsReceipt->inspection_notes }}</p>
            </div>
            @endif
            @if($goodsReceipt->notes)
            <div class="border-t border-slate-100 pt-3">
                <p class="text-xs text-slate-500 mb-1">Catatan</p>
                <p class="text-slate-700">{{ $goodsReceipt->notes }}</p>
            </div>
            @endif
        </div>
    </x-data-card>

    {{-- Dokumen Terkait --}}
    <x-data-card title="Dokumen Terkait">
        <div class="px-5 py-4 space-y-3 text-sm">
            @if($goodsReceipt->purchaseOrder)
            <div class="flex justify-between">
                <span class="text-slate-500">Purchase Order</span>
                <a href="{{ route('purchase-orders.show', $goodsReceipt->purchaseOrder) }}"
                   class="font-mono font-bold text-indigo-600 hover:underline">
                    {{ $goodsReceipt->purchaseOrder->po_no }}
                </a>
            </div>
            @endif
            @if($goodsReceipt->purchaseTransaction)
            <div class="flex justify-between">
                <span class="text-slate-500">Transaksi</span>
                <a href="{{ route('purchase-transactions.show', $goodsReceipt->purchaseTransaction) }}"
                   class="font-mono font-bold text-indigo-600 hover:underline">
                    {{ $goodsReceipt->purchaseTransaction->transaction_no }}
                </a>
            </div>
            @endif
            @if($supplier)
            <div class="flex justify-between">
                <span class="text-slate-500">Supplier</span>
                <span class="font-medium text-slate-800">{{ $supplier->name }}</span>
            </div>
            @endif
            @if($project)
            <div class="flex justify-between">
                <span class="text-slate-500">Proyek</span>
                <span class="font-medium text-slate-800">{{ $project->name }}</span>
            </div>
            @endif
        </div>
    </x-data-card>

</div>

{{-- Confirm Inspection --}}
@if(in_array(auth()->user()->role, ['admin','staff','purchasing']) && ($goodsReceipt->inspection_status ?? 'pending') === 'pending')
<x-data-card>
    <form method="POST" action="{{ route('goods-receipts.confirm-inspection', $goodsReceipt) }}"
          class="px-5 py-4 flex flex-wrap items-end gap-4">
        @csrf
        <div>
            <label class="form-label-required">Hasil Inspeksi</label>
            <select name="inspection_status" class="form-input w-48" required>
                <option value="passed">✅ Lulus QC</option>
                <option value="partial_pass">⚠️ Lulus Sebagian</option>
                <option value="failed">❌ Ditolak</option>
            </select>
        </div>
        <div class="flex-1 min-w-48">
            <label class="form-label">Catatan Inspeksi</label>
            <input type="text" name="inspection_notes" class="form-input" placeholder="Keterangan hasil QC...">
        </div>
        <button type="submit" class="btn-success">Simpan Hasil Inspeksi</button>
    </form>
</x-data-card>
@endif

{{-- Items Table --}}
<x-data-card title="Rincian Barang Diterima" subtitle="Perbandingan qty dipesan vs qty diterima">
    <div class="overflow-x-auto">
        <table class="tbl">
            <thead>
                <tr>
                    <th>Material</th>
                    <th class="th-center">Satuan</th>
                    <th class="th-right">Qty Dipesan</th>
                    <th class="th-right">Qty Diterima</th>
                    <th class="th-right">Selisih</th>
                    <th class="th-center">Status</th>
                    @if($goodsReceipt->items->where('quality_status','!=',null)->count() > 0)
                    <th class="th-center">Kondisi</th>
                    @endif
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($goodsReceipt->items as $item)
                @php
                    $selisih = (float)$item->qty_received - (float)$item->qty_ordered;
                    $pct = $item->qty_ordered > 0
                        ? min(100, round(($item->qty_received / $item->qty_ordered) * 100)) : 0;
                @endphp
                <tr>
                    <td class="font-medium text-slate-800">{{ $item->material_name_snapshot }}</td>
                    <td class="text-center text-slate-600">{{ $item->unit_name_snapshot }}</td>
                    <td class="text-right font-mono text-slate-700">{{ number_format((float)$item->qty_ordered, 2, ',', '.') }}</td>
                    <td class="text-right font-semibold font-mono">
                        <span class="{{ $item->qty_received >= $item->qty_ordered ? 'text-emerald-600' : ($item->qty_received > 0 ? 'text-amber-600' : 'text-slate-400') }}">
                            {{ number_format((float)$item->qty_received, 2, ',', '.') }}
                        </span>
                    </td>
                    <td class="text-right font-mono text-sm">
                        @if($selisih >= 0)
                            <span class="text-emerald-600">+{{ number_format($selisih, 2, ',', '.') }}</span>
                        @else
                            <span class="text-rose-500">{{ number_format($selisih, 2, ',', '.') }}</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($item->qty_received >= $item->qty_ordered)
                            <span class="badge-success text-xs">Lengkap</span>
                        @elseif($item->qty_received > 0)
                            <span class="badge-warning text-xs">Sebagian {{ $pct }}%</span>
                        @else
                            <span class="badge-neutral text-xs">Belum</span>
                        @endif
                    </td>
                    @if($goodsReceipt->items->where('quality_status','!=',null)->count() > 0)
                    <td class="text-center text-xs">
                        @if($item->quality_status === 'ok') <span class="text-emerald-600">✅ Baik</span>
                        @elseif($item->quality_status === 'partial_reject') <span class="text-amber-600">⚠️ Sebagian</span>
                        @elseif($item->quality_status === 'full_reject') <span class="text-rose-600">❌ Tolak</span>
                        @else —
                        @endif
                    </td>
                    @endif
                    <td class="text-slate-500 text-xs">{{ $item->notes ?: '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @php
        $totalOrdered  = $goodsReceipt->items->sum(fn($i) => (float)$i->qty_ordered);
        $totalReceived = $goodsReceipt->items->sum(fn($i) => (float)$i->qty_received);
        $totalPct      = $totalOrdered > 0 ? min(100, round(($totalReceived / $totalOrdered) * 100)) : 0;
    @endphp
    <div class="border-t border-slate-100 px-5 py-4 bg-slate-50/50 rounded-b-2xl">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-sm">
            <div class="flex items-center gap-6">
                <div>
                    <span class="text-slate-500">Total Dipesan</span>
                    <span class="ml-2 font-semibold text-slate-700">{{ number_format($totalOrdered, 2, ',', '.') }}</span>
                </div>
                <div>
                    <span class="text-slate-500">Total Diterima</span>
                    <span class="ml-2 font-semibold {{ $totalPct >= 100 ? 'text-emerald-600' : 'text-amber-600' }}">
                        {{ number_format($totalReceived, 2, ',', '.') }}
                    </span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="h-2 w-32 bg-slate-200 rounded-full overflow-hidden">
                    <div class="h-full rounded-full {{ $totalPct >= 100 ? 'bg-emerald-500' : ($totalPct > 0 ? 'bg-amber-400' : 'bg-slate-300') }}"
                         style="width: {{ $totalPct }}%"></div>
                </div>
                <span class="text-slate-600 font-semibold">{{ $totalPct }}%</span>
            </div>
        </div>
    </div>
</x-data-card>

</div>
@endsection
