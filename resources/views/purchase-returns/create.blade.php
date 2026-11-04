@extends('layouts.app')
@section('title', 'Buat Return ke Vendor')

@section('content')
<x-page-header title="Buat Return ke Vendor"
               subtitle="{{ $purchaseTransaction->transaction_no }} — {{ $purchaseTransaction->supplier?->name ?? 'Tanpa supplier' }}">
    <x-slot name="actions">
        <a href="{{ route('purchase-transactions.show', $purchaseTransaction) }}" class="btn-secondary">← Kembali ke Transaksi</a>
    </x-slot>
</x-page-header>

{{-- Info Transaksi --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4">
        <p class="text-xs text-blue-600 font-medium">No. Transaksi</p>
        <p class="text-lg font-bold text-blue-700 font-mono">{{ $purchaseTransaction->transaction_no }}</p>
    </div>
    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4">
        <p class="text-xs text-slate-600 font-medium">Supplier</p>
        <p class="text-lg font-bold text-slate-700">{{ $purchaseTransaction->supplier?->name ?? '—' }}</p>
    </div>
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4">
        <p class="text-xs text-amber-600 font-medium">Total Transaksi</p>
        <p class="text-lg font-bold text-amber-700">Rp {{ number_format($purchaseTransaction->grand_total, 0, ',', '.') }}</p>
    </div>
</div>

<form method="POST" action="{{ route('purchase-returns.store') }}" x-data="returnForm()">
    @csrf
    <input type="hidden" name="purchase_transaction_id" value="{{ $purchaseTransaction->id }}">

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left: Items --}}
        <div class="lg:col-span-2 space-y-6">
            <x-data-card>
                <h3 class="text-sm font-semibold text-slate-700 mb-4">Item yang Dikembalikan</h3>
                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Material</th>
                                <th class="th-right">Qty Beli</th>
                                <th class="th-right w-28">Qty Return</th>
                                <th class="th-right">Harga Satuan</th>
                                <th class="th-right">Subtotal</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchaseTransaction->items as $i => $txItem)
                            <tr x-data="{ qty: 0, price: {{ (float)$txItem->unit_price }}, max: {{ (float)$txItem->qty }} }">
                                <input type="hidden" name="items[{{ $i }}][purchase_transaction_item_id]" value="{{ $txItem->id }}">
                                <input type="hidden" name="items[{{ $i }}][material_name_snapshot]" value="{{ $txItem->material_name_snapshot ?? $txItem->material?->name }}">
                                <input type="hidden" name="items[{{ $i }}][unit_name_snapshot]" value="{{ $txItem->unit_name_snapshot ?? $txItem->material?->unit?->name ?? '' }}">
                                <input type="hidden" name="items[{{ $i }}][unit_price]" value="{{ $txItem->unit_price }}">
                                <td>
                                    <div class="font-medium text-sm text-slate-800">{{ $txItem->material_name_snapshot ?? $txItem->material?->name }}</div>
                                    <div class="text-xs text-slate-400">{{ $txItem->unit_name_snapshot ?? $txItem->material?->unit?->name }}</div>
                                </td>
                                <td class="text-right font-mono text-sm">{{ number_format($txItem->qty, 2, ',', '.') }}</td>
                                <td>
                                    <input type="number" name="items[{{ $i }}][qty_returned]"
                                           x-model="qty"
                                           min="0" :max="max" step="0.01"
                                           class="form-input text-right font-mono text-sm">
                                </td>
                                <td class="text-right text-sm">Rp {{ number_format($txItem->unit_price, 0, ',', '.') }}</td>
                                <td class="text-right font-semibold text-sm">
                                    Rp <span x-text="(qty * price).toLocaleString('id-ID')"></span>
                                </td>
                                <td>
                                    <input type="text" name="items[{{ $i }}][notes]" class="form-input text-sm" placeholder="opsional">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-data-card>
        </div>

        {{-- Right: Detail + Submit --}}
        <div class="space-y-4">
            <x-data-card>
                <h3 class="text-sm font-semibold text-slate-700 mb-4">Detail Return</h3>
                <div class="space-y-4">
                    <div>
                        <label class="form-label-required">Alasan Return</label>
                        <select name="reason" class="form-input" required>
                            <option value="">— Pilih alasan —</option>
                            <option value="wrong_item"      {{ old('reason') === 'wrong_item'      ? 'selected' : '' }}>Item Salah / Tidak Sesuai PO</option>
                            <option value="damaged"         {{ old('reason') === 'damaged'         ? 'selected' : '' }}>Barang Rusak</option>
                            <option value="quantity_excess" {{ old('reason') === 'quantity_excess' ? 'selected' : '' }}>Kelebihan Kuantitas</option>
                            <option value="quality_reject"  {{ old('reason') === 'quality_reject'  ? 'selected' : '' }}>Tidak Lulus QC</option>
                            <option value="spec_mismatch"   {{ old('reason') === 'spec_mismatch'   ? 'selected' : '' }}>Spesifikasi Tidak Sesuai</option>
                            <option value="other"           {{ old('reason') === 'other'           ? 'selected' : '' }}>Lainnya</option>
                        </select>
                        @error('reason')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label-required">Deskripsi</label>
                        <textarea name="description" rows="3" class="form-input resize-none"
                                  placeholder="Jelaskan detail alasan pengembalian..." required>{{ old('description') }}</textarea>
                        @error('description')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">No. Credit Note Supplier</label>
                        <input type="text" name="credit_note_no" value="{{ old('credit_note_no') }}"
                               class="form-input font-mono" placeholder="Jika sudah ada">
                    </div>
                </div>
            </x-data-card>

            @if($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $err)
                    <li class="text-xs text-rose-600">{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <button type="submit" class="btn-primary w-full justify-center">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/></svg>
                Buat Return
            </button>
        </div>

    </div>
</form>
@endsection

@push('scripts')
<script>
function returnForm() { return {}; }
</script>
@endpush
