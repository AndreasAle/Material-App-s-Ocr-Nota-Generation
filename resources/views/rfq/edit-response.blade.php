@extends('layouts.app')
@section('title', 'Input Penawaran — ' . $rfq->rfq_no)

@section('content')
<x-page-header :title="'Input Penawaran — ' . $rfq->rfq_no"
               :subtitle="$response->supplier?->name ?? 'Supplier'">
    <x-slot name="actions">
        <a href="{{ route('rfq.show', $rfq) }}" class="btn-secondary">← Kembali ke RFQ</a>
    </x-slot>
</x-page-header>

<form method="POST" action="{{ route('rfq.submit-response', [$rfq, $response]) }}">
    @csrf @method('PUT')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left: Items --}}
        <div class="lg:col-span-2 space-y-6">
            <x-data-card>
                <h3 class="text-sm font-semibold text-slate-700 mb-4">Harga Penawaran per Item</h3>
                <div class="overflow-x-auto">
                    <table class="data-table" x-data="responseForm()">
                        <thead>
                            <tr>
                                <th>Material</th>
                                <th class="th-right">Qty</th>
                                <th class="th-right">Budget</th>
                                <th class="th-right w-40">Harga Satuan (Rp)</th>
                                <th class="th-right">Subtotal</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($response->items as $i => $ri)
                            @php $rfqItem = $ri->rfqItem; @endphp
                            <tr x-data="{ price: {{ old("items.{$i}.unit_price", $ri->unit_price ?? 0) }}, qty: {{ $rfqItem?->qty_required ?? 0 }} }">
                                <input type="hidden" name="items[{{ $i }}][rfq_item_id]" value="{{ $ri->rfq_item_id }}">
                                <td class="font-medium text-sm text-slate-800">{{ $rfqItem?->material_name_snapshot ?? '—' }}</td>
                                <td class="text-right font-mono text-sm">
                                    {{ number_format($rfqItem?->qty_required ?? 0, 2, ',', '.') }}
                                    {{ $rfqItem?->unit_name_snapshot ?? '' }}
                                </td>
                                <td class="text-right text-sm text-slate-500">
                                    {{ $rfqItem?->budget_price ? 'Rp ' . number_format($rfqItem->budget_price, 0, ',', '.') : '—' }}
                                </td>
                                <td>
                                    <input type="number" name="items[{{ $i }}][unit_price]"
                                           x-model="price" x-on:input="price = parseFloat($event.target.value) || 0"
                                           value="{{ old("items.{$i}.unit_price", $ri->unit_price ?? '') }}"
                                           class="form-input text-right font-mono text-sm" min="0" step="100" required>
                                    @error("items.{$i}.unit_price")<p class="form-error">{{ $message }}</p>@enderror
                                </td>
                                <td class="text-right font-semibold text-sm text-slate-800">
                                    Rp <span x-text="(price * qty).toLocaleString('id-ID')"></span>
                                </td>
                                <td>
                                    <input type="text" name="items[{{ $i }}][notes]"
                                           value="{{ old("items.{$i}.notes", $ri->notes ?? '') }}"
                                           class="form-input text-sm" placeholder="opsional">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-data-card>
        </div>

        {{-- Right: Terms + Submit --}}
        <div class="space-y-4">
            <x-data-card>
                <h3 class="text-sm font-semibold text-slate-700 mb-4">Syarat Penawaran</h3>
                <div class="space-y-4">
                    <div>
                        <label class="form-label-required">Term Pembayaran</label>
                        <select name="payment_terms" class="form-input" required>
                            <option value="cash" {{ old('payment_terms', $response->payment_terms) === 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="net7"  {{ old('payment_terms', $response->payment_terms) === 'net7'  ? 'selected' : '' }}>Net 7</option>
                            <option value="net14" {{ old('payment_terms', $response->payment_terms) === 'net14' ? 'selected' : '' }}>Net 14</option>
                            <option value="net30" {{ old('payment_terms', $response->payment_terms) === 'net30' ? 'selected' : '' }}>Net 30</option>
                            <option value="net45" {{ old('payment_terms', $response->payment_terms) === 'net45' ? 'selected' : '' }}>Net 45</option>
                            <option value="net60" {{ old('payment_terms', $response->payment_terms) === 'net60' ? 'selected' : '' }}>Net 60</option>
                            <option value="dp50"  {{ old('payment_terms', $response->payment_terms) === 'dp50'  ? 'selected' : '' }}>DP 50%</option>
                            <option value="dp30"  {{ old('payment_terms', $response->payment_terms) === 'dp30'  ? 'selected' : '' }}>DP 30%</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Estimasi Hari Kirim</label>
                        <input type="number" name="delivery_days"
                               value="{{ old('delivery_days', $response->delivery_days) }}"
                               min="1" class="form-input" placeholder="Contoh: 7">
                    </div>
                    <div>
                        <label class="form-label">Berlaku Hingga</label>
                        <input type="date" name="valid_until"
                               value="{{ old('valid_until', $response->valid_until?->format('Y-m-d')) }}"
                               class="form-input">
                    </div>
                    <div>
                        <label class="form-label">No. Ref. Supplier</label>
                        <input type="text" name="supplier_ref_no"
                               value="{{ old('supplier_ref_no', $response->supplier_ref_no) }}"
                               class="form-input font-mono" placeholder="No. referensi penawaran">
                    </div>
                    <div>
                        <label class="form-label">Catatan</label>
                        <textarea name="notes" rows="3" class="form-input resize-none"
                                  placeholder="Catatan tambahan...">{{ old('notes', $response->notes) }}</textarea>
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

            <button type="submit" class="btn-success w-full justify-center">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/></svg>
                Simpan Penawaran
            </button>
        </div>

    </div>
</form>
@endsection

@push('scripts')
<script>
function responseForm() {
    return {};
}
</script>
@endpush
