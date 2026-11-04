@extends('layouts.app')

@section('content')
<div class="space-y-6"
     x-data="goodsReceiptForm({{ $purchaseTransaction->items->map(fn($item) => [
         'purchase_transaction_item_id' => $item->id,
         'material_name_snapshot' => $item->material?->name ?? ($item->material_name_snapshot ?? ''),
         'unit_name_snapshot' => $item->material?->unit?->name ?? ($item->unit_name_snapshot ?? ''),
         'qty_ordered' => (float) $item->qty,
         'qty_received' => max(0, (float) $item->qty - ($receivedQty[$item->id] ?? 0)),
         'notes' => '',
     ])->values()->toJson() }})">

    <x-page-header
        title="Buat Surat Jalan"
        subtitle="Transaksi: {{ $purchaseTransaction->transaction_no }}"
    >
        <x-slot name="actions">
            <a href="{{ route('purchase-transactions.show', $purchaseTransaction) }}" class="btn-secondary">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali
            </a>
        </x-slot>
    </x-page-header>

    @if ($errors->any())
        <div class="alert-danger">
            <ul class="space-y-1 list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('purchase-transactions.goods-receipts.store', $purchaseTransaction) }}">
        @csrf

        {{-- Info Transaksi --}}
        <x-data-card title="Informasi Transaksi" subtitle="Detail transaksi yang terkait surat jalan ini">
            <div class="px-5 py-4 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div>
                    <span class="text-xs font-semibold text-slate-500">No. Transaksi</span>
                    <p class="font-semibold text-slate-800 mt-1">{{ $purchaseTransaction->transaction_no }}</p>
                </div>
                <div>
                    <span class="text-xs font-semibold text-slate-500">Supplier</span>
                    <p class="font-semibold text-slate-800 mt-1">{{ $purchaseTransaction->supplier?->name ?? '—' }}</p>
                </div>
                <div>
                    <span class="text-xs font-semibold text-slate-500">Proyek</span>
                    <p class="font-semibold text-slate-800 mt-1">{{ $purchaseTransaction->project?->name ?? '—' }}</p>
                </div>
            </div>
        </x-data-card>

        {{-- Detail Surat Jalan --}}
        <x-data-card title="Detail Surat Jalan" subtitle="Nomor, tanggal, dan penerima barang" class="mt-5">
            <div class="px-5 py-4 grid grid-cols-1 md:grid-cols-3 gap-5">
                <div>
                    <label class="form-label-required">Nomor Surat Jalan</label>
                    <input type="text"
                           name="receipt_no"
                           value="{{ old('receipt_no', $receiptNo) }}"
                           class="form-input" required>
                </div>
                <div>
                    <label class="form-label-required">Tanggal Penerimaan</label>
                    <input type="date"
                           name="receipt_date"
                           value="{{ old('receipt_date', now()->toDateString()) }}"
                           class="form-input" required>
                </div>
                <div>
                    <label class="form-label-required">Nama Penerima</label>
                    <input type="text"
                           name="received_by"
                           value="{{ old('received_by') }}"
                           placeholder="Nama orang yang menerima barang"
                           class="form-input" required>
                </div>
                <div class="md:col-span-3">
                    <label class="form-label">Catatan</label>
                    <textarea name="notes" rows="2"
                              placeholder="Catatan tambahan (opsional)"
                              class="form-input resize-none">{{ old('notes') }}</textarea>
                </div>
            </div>
        </x-data-card>

        {{-- Tabel Item --}}
        <x-data-card title="Daftar Barang" subtitle="Isi Qty Diterima sesuai kondisi aktual di lapangan" class="mt-5">
            <div class="overflow-x-auto">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>Material</th>
                            <th class="th-center">Satuan</th>
                            <th class="th-right">Qty Dipesan</th>
                            <th class="th-right w-36">Qty Diterima <span class="text-rose-400">*</span></th>
                            <th class="th-center w-24">Status</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(item, index) in rows" :key="index">
                            <tr>
                                <td>
                                    <input type="hidden" :name="`items[${index}][purchase_transaction_item_id]`" :value="item.purchase_transaction_item_id">
                                    <input type="hidden" :name="`items[${index}][material_name_snapshot]`" :value="item.material_name_snapshot">
                                    <input type="hidden" :name="`items[${index}][unit_name_snapshot]`" :value="item.unit_name_snapshot">
                                    <input type="hidden" :name="`items[${index}][qty_ordered]`" :value="item.qty_ordered">
                                    <span class="font-medium text-slate-800" x-text="item.material_name_snapshot"></span>
                                </td>
                                <td class="text-center text-slate-600" x-text="item.unit_name_snapshot"></td>
                                <td class="text-right font-mono text-slate-700" x-text="formatNum(item.qty_ordered)"></td>
                                <td class="text-right">
                                    <input type="number"
                                           :name="`items[${index}][qty_received]`"
                                           x-model.number="item.qty_received"
                                           min="0" step="0.01"
                                           class="form-input text-right text-sm"
                                           required>
                                </td>
                                <td class="text-center">
                                    <span x-show="item.qty_received >= item.qty_ordered" class="badge-success">Lengkap</span>
                                    <span x-show="item.qty_received > 0 && item.qty_received < item.qty_ordered" class="badge-warning">Sebagian</span>
                                    <span x-show="item.qty_received == 0" class="badge-neutral">Belum</span>
                                </td>
                                <td>
                                    <input type="text"
                                           :name="`items[${index}][notes]`"
                                           x-model="item.notes"
                                           placeholder="Keterangan..."
                                           class="form-input text-sm">
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            {{-- Status keseluruhan --}}
            <div class="px-5 py-4 border-t border-slate-100 flex flex-wrap items-center gap-3">
                <span class="text-sm text-slate-500">Status penerimaan:</span>
                <span x-show="overallStatus === 'complete'" class="badge-success">Lengkap — semua barang diterima</span>
                <span x-show="overallStatus === 'partial'" class="badge-warning">Sebagian — ada barang kurang</span>
                <span x-show="overallStatus === 'pending'" class="badge-neutral">Belum diterima</span>
            </div>
        </x-data-card>

        {{-- Tombol --}}
        <div class="mt-5 flex items-center justify-end gap-3">
            <a href="{{ route('purchase-transactions.show', $purchaseTransaction) }}" class="btn-secondary">Batal</a>
            <button type="submit" class="btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Simpan Surat Jalan
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function goodsReceiptForm(initialRows) {
    return {
        rows: initialRows,

        get overallStatus() {
            if (this.rows.length === 0) return 'pending';
            const allComplete = this.rows.every(r => Number(r.qty_received) >= Number(r.qty_ordered));
            if (allComplete) return 'complete';
            const anyReceived = this.rows.some(r => Number(r.qty_received) > 0);
            return anyReceived ? 'partial' : 'pending';
        },

        formatNum(val) {
            return Number(val).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
        },
    };
}
</script>
@endpush
@endsection
