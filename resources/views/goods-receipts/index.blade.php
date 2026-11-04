@extends('layouts.app')
@section('title', 'Surat Jalan')

@section('content')
<x-page-header title="Surat Jalan" subtitle="Daftar penerimaan barang dari transaksi pembelian.">
</x-page-header>

{{-- Stats --}}
<div class="mb-5 grid grid-cols-2 gap-4 sm:grid-cols-4">
    <div class="rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm text-center">
        <p class="text-2xl font-bold text-slate-800">{{ $stats['total'] }}</p>
        <p class="text-xs text-slate-500 mt-0.5">Total Surat Jalan</p>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 shadow-sm text-center">
        <p class="text-2xl font-bold text-slate-600">{{ $stats['pending'] }}</p>
        <p class="text-xs text-slate-500 mt-0.5">Belum Diterima</p>
    </div>
    <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 shadow-sm text-center">
        <p class="text-2xl font-bold text-amber-700">{{ $stats['partial'] }}</p>
        <p class="text-xs text-amber-600 mt-0.5">Sebagian</p>
    </div>
    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 shadow-sm text-center">
        <p class="text-2xl font-bold text-emerald-700">{{ $stats['complete'] }}</p>
        <p class="text-xs text-emerald-600 mt-0.5">Lengkap</p>
    </div>
</div>

{{-- Filter --}}
<x-data-card class="mb-5">
    <div class="px-5 py-4">
        <form method="GET" action="{{ route('goods-receipts.index') }}" class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[180px]">
                <label class="form-label">Cari Surat Jalan</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Nomor surat jalan..." class="form-input">
            </div>
            <div class="w-44">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="pending"  @selected(request('status') === 'pending')>Belum Diterima</option>
                    <option value="partial"  @selected(request('status') === 'partial')>Sebagian</option>
                    <option value="complete" @selected(request('status') === 'complete')>Lengkap</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary">Filter</button>
                <a href="{{ route('goods-receipts.index') }}" class="btn-secondary">Reset</a>
            </div>
        </form>
    </div>
</x-data-card>

{{-- Table --}}
<x-data-card title="Daftar Surat Jalan" subtitle="{{ $receipts->total() }} surat jalan ditemukan">
    @if($receipts->isEmpty())
        <div class="flex flex-col items-center justify-center py-14 text-center">
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/>
                </svg>
            </div>
            <p class="mt-3 font-semibold text-slate-700">Belum ada surat jalan</p>
            <p class="mt-1 text-sm text-slate-400">Buat surat jalan dari halaman detail transaksi pembelian.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>No. Surat Jalan</th>
                        <th>Tanggal</th>
                        <th>PO / Transaksi</th>
                        <th>Supplier</th>
                        <th>Proyek</th>
                        <th>Diterima Oleh</th>
                        <th class="th-center">Status</th>
                        <th class="th-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($receipts as $receipt)
                        @php
                            $statusConfig = match($receipt->status) {
                                'complete' => ['class' => 'badge-success', 'label' => 'Lengkap'],
                                'partial'  => ['class' => 'badge-warning', 'label' => 'Sebagian'],
                                default    => ['class' => 'badge-neutral', 'label' => 'Belum Diterima'],
                            };
                        @endphp
                        <tr>
                            <td class="font-semibold text-slate-800">
                                <a href="{{ route('goods-receipts.show', $receipt) }}" class="text-indigo-600 hover:underline">
                                    {{ $receipt->receipt_no }}
                                </a>
                            </td>
                            <td class="text-slate-600 whitespace-nowrap">
                                {{ $receipt->receipt_date?->translatedFormat('d M Y') ?? '—' }}
                            </td>
                            <td>
                                @if($receipt->purchaseTransaction)
                                    <a href="{{ route('purchase-transactions.show', $receipt->purchaseTransaction) }}"
                                       class="text-indigo-600 hover:underline text-sm font-mono font-medium">
                                        {{ $receipt->purchaseTransaction->transaction_no }}
                                    </a>
                                @elseif($receipt->purchase_order_id)
                                    <a href="{{ route('purchase-orders.show', $receipt->purchase_order_id) }}"
                                       class="text-violet-600 hover:underline text-xs font-mono font-medium">
                                        PO → Lihat
                                    </a>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="text-slate-600 text-sm">
                                {{ $receipt->purchaseTransaction?->supplier?->name
                                ?? $receipt->purchaseOrder?->supplier?->name ?? '—' }}
                            </td>
                            <td class="text-slate-600 text-sm">
                                {{ $receipt->purchaseTransaction?->project?->name
                                ?? $receipt->purchaseOrder?->project?->name ?? '—' }}
                            </td>
                            <td class="text-slate-700 text-sm">
                                {{ $receipt->received_by ?? '—' }}
                            </td>
                            <td class="text-center">
                                <span class="{{ $statusConfig['class'] }}">{{ $statusConfig['label'] }}</span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('goods-receipts.show', $receipt) }}"
                                   class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-xs font-semibold text-indigo-600 hover:bg-indigo-50 transition-colors">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12S5.25 5.25 12 5.25 21.75 12 21.75 12 18.75 18.75 12 18.75 2.25 12 2.25 12Z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15.75A3.75 3.75 0 1 0 12 8.25a3.75 3.75 0 0 0 0 7.5Z"/>
                                    </svg>
                                    Lihat
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($receipts->hasPages())
            <div class="border-t border-slate-100 px-5 py-4">
                {{ $receipts->links() }}
            </div>
        @endif
    @endif
</x-data-card>

@endsection
