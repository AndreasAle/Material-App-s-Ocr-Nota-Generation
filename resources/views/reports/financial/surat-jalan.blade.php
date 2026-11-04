@extends('layouts.app')

@section('title', 'Rekap Surat Jalan')

@section('content')
<x-page-header title="Rekap Surat Jalan" subtitle="Status penerimaan barang dari seluruh transaksi pembelian">
    <x-slot name="actions">
        <a href="{{ route('financial-reports.index') }}" class="btn-secondary">
            ← Ringkasan
        </a>
    </x-slot>
</x-page-header>

{{-- Filter --}}
<x-data-card class="mb-6">
    <form method="GET" action="{{ route('financial-reports.surat-jalan') }}" class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-[150px]">
            <label class="block text-xs font-medium text-slate-600 mb-1">Dari Tanggal</label>
            <input type="date" name="start_date" value="{{ $startDate }}" class="form-input w-full text-sm">
        </div>
        <div class="flex-1 min-w-[150px]">
            <label class="block text-xs font-medium text-slate-600 mb-1">Sampai Tanggal</label>
            <input type="date" name="end_date" value="{{ $endDate }}" class="form-input w-full text-sm">
        </div>
        <div class="flex-1 min-w-[180px]">
            <label class="block text-xs font-medium text-slate-600 mb-1">Proyek</label>
            <select name="project_id" class="form-input w-full text-sm">
                <option value="">Semua Proyek</option>
                @foreach ($projects as $p)
                    <option value="{{ $p->id }}" @selected($projectId == $p->id)>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex-1 min-w-[150px]">
            <label class="block text-xs font-medium text-slate-600 mb-1">Status</label>
            <select name="status" class="form-input w-full text-sm">
                <option value="">Semua Status</option>
                <option value="pending"  @selected($status === 'pending')>Pending</option>
                <option value="partial"  @selected($status === 'partial')>Sebagian</option>
                <option value="complete" @selected($status === 'complete')>Lengkap</option>
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn-primary text-sm">Filter</button>
            <a href="{{ route('financial-reports.surat-jalan') }}" class="btn-secondary text-sm">Reset</a>
        </div>
    </form>
</x-data-card>

{{-- Stats Cards --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <x-data-card class="text-center">
        <p class="text-2xl font-bold text-slate-800">{{ $stats['total'] }}</p>
        <p class="text-xs text-slate-500 mt-0.5">Total Surat Jalan</p>
    </x-data-card>
    <x-data-card class="text-center">
        <p class="text-2xl font-bold text-amber-500">{{ $stats['pending'] }}</p>
        <p class="text-xs text-slate-500 mt-0.5">Pending</p>
    </x-data-card>
    <x-data-card class="text-center">
        <p class="text-2xl font-bold text-indigo-500">{{ $stats['partial'] }}</p>
        <p class="text-xs text-slate-500 mt-0.5">Sebagian Diterima</p>
    </x-data-card>
    <x-data-card class="text-center">
        <p class="text-2xl font-bold text-emerald-500">{{ $stats['complete'] }}</p>
        <p class="text-xs text-slate-500 mt-0.5">Lengkap</p>
    </x-data-card>
</div>

{{-- Receipts Table --}}
<x-data-card>
    @if ($receipts->isEmpty())
        <div class="text-center py-12">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/>
            </svg>
            <p class="text-slate-400 font-medium">Tidak ada surat jalan ditemukan</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50">
                        <th class="text-left text-xs font-semibold text-slate-500 px-3 py-2.5 rounded-l-lg">No. Surat Jalan</th>
                        <th class="text-left text-xs font-semibold text-slate-500 px-3 py-2.5">Tgl Terima</th>
                        <th class="text-left text-xs font-semibold text-slate-500 px-3 py-2.5">No. Transaksi</th>
                        <th class="text-left text-xs font-semibold text-slate-500 px-3 py-2.5">Supplier</th>
                        <th class="text-left text-xs font-semibold text-slate-500 px-3 py-2.5">Proyek</th>
                        <th class="text-left text-xs font-semibold text-slate-500 px-3 py-2.5">Diterima Oleh</th>
                        <th class="text-center text-xs font-semibold text-slate-500 px-3 py-2.5">Item</th>
                        <th class="text-center text-xs font-semibold text-slate-500 px-3 py-2.5 rounded-r-lg">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach ($receipts as $receipt)
                        @php
                            $statusBadge = match($receipt->status) {
                                'complete' => 'badge-success',
                                'partial'  => 'badge-info',
                                default    => 'badge-warning',
                            };
                            $statusLabel = match($receipt->status) {
                                'complete' => 'Lengkap',
                                'partial'  => 'Sebagian',
                                default    => 'Pending',
                            };
                            $tx = $receipt->purchaseTransaction;
                            // Qty summary
                            $qtyOrdered  = $receipt->items->sum('qty_ordered');
                            $qtyReceived = $receipt->items->sum('qty_received');
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-3 py-2.5">
                                <a href="{{ route('goods-receipts.show', $receipt) }}"
                                   class="font-medium text-indigo-600 hover:underline">
                                    {{ $receipt->receipt_no }}
                                </a>
                            </td>
                            <td class="px-3 py-2.5 text-slate-600">{{ $receipt->receipt_date->format('d M Y') }}</td>
                            <td class="px-3 py-2.5">
                                @if ($tx)
                                    <a href="{{ route('purchase-transactions.show', $tx) }}"
                                       class="text-indigo-500 hover:underline text-xs">
                                        {{ $tx->transaction_no }}
                                    </a>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-3 py-2.5 text-slate-600 max-w-[130px] truncate">{{ $tx?->supplier?->name ?? '—' }}</td>
                            <td class="px-3 py-2.5 text-slate-600 max-w-[130px] truncate">{{ $tx?->project?->name ?? '—' }}</td>
                            <td class="px-3 py-2.5 text-slate-600">{{ $receipt->received_by }}</td>
                            <td class="px-3 py-2.5 text-center">
                                <span class="text-slate-700 font-medium">{{ $receipt->items->count() }}</span>
                                <span class="text-slate-400 text-xs block">
                                    {{ number_format($qtyReceived, 1, ',', '.') }} / {{ number_format($qtyOrdered, 1, ',', '.') }}
                                </span>
                            </td>
                            <td class="px-3 py-2.5 text-center">
                                <span class="{{ $statusBadge }} text-xs">{{ $statusLabel }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $receipts->links() }}
        </div>
    @endif
</x-data-card>
@endsection
