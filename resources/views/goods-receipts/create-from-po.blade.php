@extends('layouts.app')

@section('content')
<x-page-header
    title="Buat Surat Jalan dari PO"
    subtitle="{{ $purchaseOrder->po_no }} — {{ $purchaseOrder->supplier?->name ?? 'Tanpa supplier' }}"
>
    <x-slot name="actions">
        <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="btn-secondary">← Kembali ke PO</a>
    </x-slot>
</x-page-header>

{{-- Info PO --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4">
        <p class="text-xs text-blue-600 font-medium">Nomor PO</p>
        <p class="text-lg font-bold text-blue-700 font-mono">{{ $purchaseOrder->po_no }}</p>
    </div>
    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4">
        <p class="text-xs text-slate-600 font-medium">Supplier</p>
        <p class="text-lg font-bold text-slate-700">{{ $purchaseOrder->supplier?->name ?? '—' }}</p>
    </div>
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4">
        <p class="text-xs text-amber-600 font-medium">Progress Pengiriman</p>
        <p class="text-lg font-bold text-amber-700">{{ number_format($purchaseOrder->delivery_percent, 1) }}%</p>
    </div>
</div>

<form method="POST" action="{{ route('purchase-orders.goods-receipts.store', $purchaseOrder) }}"
      x-data="grFromPoForm()">
    @csrf

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left: Detail GR --}}
        <div class="lg:col-span-2 space-y-6">
            <x-data-card>
                <h3 class="text-sm font-semibold text-slate-700 mb-4">Info Penerimaan</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label-required">Nomor Surat Jalan (Internal)</label>
                        <input type="text" name="receipt_no" value="{{ $receiptNo }}"
                               class="form-input font-mono" required>
                    </div>
                    <div>
                        <label class="form-label-required">Tanggal Terima</label>
                        <input type="date" name="receipt_date" value="{{ today()->format('Y-m-d') }}"
                               class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">No. Surat Jalan Supplier (DO)</label>
                        <input type="text" name="supplier_do_no" class="form-input font-mono"
                               placeholder="Nomor dari supplier">
                    </div>
                    <div>
                        <label class="form-label">Tanggal DO Supplier</label>
                        <input type="date" name="supplier_do_date" class="form-input">
                    </div>
                    <div>
                        <label class="form-label-required">Diterima Oleh</label>
                        <input type="text" name="received_by" class="form-input"
                               placeholder="Nama penerima di lapangan" required>
                    </div>
                    <div>
                        <label class="form-label">Inspector / QC</label>
                        <input type="text" name="inspector_name" class="form-input"
                               placeholder="Nama inspector (opsional)">
                    </div>
                    <div>
                        <label class="form-label">Hasil Inspeksi</label>
                        <select name="inspection_status" class="form-input">
                            <option value="pending">⏳ Belum Diperiksa</option>
                            <option value="passed">✅ Lulus QC</option>
                            <option value="partial_pass">⚠️ Lulus Sebagian</option>
                            <option value="failed">❌ Ditolak</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Catatan Inspeksi</label>
                        <input type="text" name="inspection_notes" class="form-input"
                               placeholder="Catatan QC (opsional)">
                    </div>
                </div>
                <div class="mt-4">
                    <label class="form-label">Keterangan</label>
                    <textarea name="notes" rows="2" class="form-input" placeholder="Keterangan tambahan..."></textarea>
                </div>
            </x-data-card>

            {{-- Items --}}
            <x-data-card>
                <h3 class="text-sm font-semibold text-slate-700 mb-4">Item yang Diterima</h3>
                <p class="text-xs text-slate-500 mb-3">Isi qty yang diterima hari ini. Bisa kurang dari qty PO untuk pengiriman bertahap.</p>
                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Material</th>
                                <th class="text-right">Qty PO</th>
                                <th class="text-right">Sudah Terima</th>
                                <th class="text-right">Sisa</th>
                                <th class="text-right w-32">Qty Terima Sekarang</th>
                                <th>Kondisi</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchaseOrder->items as $i => $item)
                            @php
                                $remaining = max(0, (float)$item->qty - (float)$item->qty_received);
                            @endphp
                            <tr x-data="{ qty: {{ $remaining }}, qtyOrdered: {{ (float)$item->qty }}, qtyAlready: {{ (float)$item->qty_received }} }">
                                <td>
                                    <input type="hidden" name="items[{{ $i }}][purchase_order_item_id]" value="{{ $item->id }}">
                                    <input type="hidden" name="items[{{ $i }}][material_name_snapshot]" value="{{ $item->material_name_snapshot }}">
                                    <input type="hidden" name="items[{{ $i }}][unit_name_snapshot]" value="{{ $item->unit_name_snapshot }}">
                                    <input type="hidden" name="items[{{ $i }}][qty_ordered]" value="{{ $item->qty }}">
                                    <div class="font-medium text-slate-800 text-sm">{{ $item->material_name_snapshot }}</div>
                                    <div class="text-xs text-slate-400">{{ $item->unit_name_snapshot }}</div>
                                </td>
                                <td class="text-right font-mono text-sm">{{ number_format($item->qty, 2, ',', '.') }}</td>
                                <td class="text-right font-mono text-sm text-emerald-600">{{ number_format($item->qty_received, 2, ',', '.') }}</td>
                                <td class="text-right font-mono text-sm">
                                    <span :class="remaining <= 0 ? 'text-slate-400 line-through' : 'text-amber-600 font-semibold'"
                                          x-text="(qtyOrdered - qtyAlready).toFixed(2)"></span>
                                </td>
                                <td class="text-right">
                                    <input type="number" name="items[{{ $i }}][qty_received]"
                                           x-model="qty"
                                           min="0" max="{{ $remaining }}" step="0.01"
                                           class="form-input w-28 text-right font-mono"
                                           @if($remaining <= 0) readonly @endif>
                                </td>
                                <td>
                                    <select name="items[{{ $i }}][quality_status]" class="form-input text-xs">
                                        <option value="ok">✅ Baik</option>
                                        <option value="partial_reject">⚠️ Sebagian Tolak</option>
                                        <option value="full_reject">❌ Tolak Semua</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="items[{{ $i }}][notes]"
                                           class="form-input text-xs" placeholder="opsional...">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-data-card>
        </div>

        {{-- Right: PO Detail & Submit --}}
        <div class="space-y-4">
            <x-data-card>
                <h3 class="text-sm font-semibold text-slate-700 mb-3">Detail PO</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Status</dt>
                        <dd class="font-medium text-indigo-600">{{ ucfirst($purchaseOrder->status) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Proyek</dt>
                        <dd class="font-medium">{{ $purchaseOrder->project?->name ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Nilai PO</dt>
                        <dd class="font-bold">Rp {{ number_format($purchaseOrder->grand_total, 0, ',', '.') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Term Bayar</dt>
                        <dd class="text-xs">{{ $purchaseOrder->payment_terms_label }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Est. Tiba</dt>
                        <dd>{{ $purchaseOrder->expected_date?->format('d/m/Y') ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500">GR ke-</dt>
                        <dd class="font-bold text-indigo-600">{{ $purchaseOrder->goodsReceipts->count() + 1 }}</dd>
                    </div>
                </dl>
            </x-data-card>

            {{-- Delivery progress --}}
            <x-data-card>
                <h3 class="text-sm font-semibold text-slate-700 mb-3">Progress Pengiriman</h3>
                @foreach($purchaseOrder->items as $item)
                @php $pct = $item->receive_percent; @endphp
                <div class="mb-2">
                    <div class="flex justify-between text-xs mb-1">
                        <span class="text-slate-600 truncate max-w-[120px]">{{ $item->material_name_snapshot }}</span>
                        <span class="font-mono text-slate-500">{{ number_format($pct, 0) }}%</span>
                    </div>
                    <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full rounded-full {{ $pct >= 100 ? 'bg-green-500' : ($pct > 0 ? 'bg-amber-400' : 'bg-slate-200') }}"
                             style="width: {{ min(100, $pct) }}%"></div>
                    </div>
                </div>
                @endforeach
            </x-data-card>

            <button type="submit" class="btn-primary w-full">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
                </svg>
                Simpan Surat Jalan
            </button>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
function grFromPoForm() {
    return {};
}
</script>
@endpush
