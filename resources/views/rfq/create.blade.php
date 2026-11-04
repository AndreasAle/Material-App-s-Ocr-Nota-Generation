@extends('layouts.app')
@section('title', 'Buat RFQ')

@section('content')
<x-page-header title="Buat Request for Quotation" subtitle="Minta penawaran harga ke supplier">
    <x-slot name="actions">
        <a href="{{ route('rfq.index') }}" class="btn-secondary">← Kembali</a>
    </x-slot>
</x-page-header>

<form method="POST" action="{{ route('rfq.store') }}" x-data="rfqCreateForm()">
    @csrf

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left: Form --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Info RFQ --}}
            <x-data-card>
                <h3 class="text-sm font-semibold text-slate-700 mb-4">Informasi RFQ</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label-required">Proyek</label>
                        <select name="project_id" class="form-input">
                            <option value="">— Pilih Proyek —</option>
                            @foreach($projects as $proj)
                            <option value="{{ $proj->id }}" {{ old('project_id', $materialRequest?->project_id) == $proj->id ? 'selected' : '' }}>
                                {{ $proj->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('project_id')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label-required">Deadline Penawaran</label>
                        <input type="date" name="deadline" value="{{ old('deadline') }}"
                               min="{{ today()->addDay()->format('Y-m-d') }}"
                               class="form-input" required>
                        @error('deadline')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    @if($materialRequest)
                    <input type="hidden" name="material_request_id" value="{{ $materialRequest->id }}">
                    <div class="sm:col-span-2">
                        <div class="rounded-xl bg-blue-50 border border-blue-200 px-4 py-3 text-sm text-blue-800 flex items-center gap-2">
                            <svg class="h-4 w-4 text-blue-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>
                            Dari Material Request <strong class="font-mono">{{ $materialRequest->mr_no }}</strong>. Item sudah ter-prefill di bawah.
                        </div>
                    </div>
                    @endif
                    <div class="sm:col-span-2">
                        <label class="form-label">Catatan / Syarat & Ketentuan</label>
                        <textarea name="notes" rows="2" class="form-input" placeholder="Catatan untuk supplier...">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </x-data-card>

            {{-- Items --}}
            <x-data-card>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-slate-700">Item yang Diminta</h3>
                    <button type="button" @click="addItem()" class="btn-secondary text-xs">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Tambah Item
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Material</th>
                                <th>Satuan</th>
                                <th class="th-right w-28">Qty</th>
                                <th class="th-right w-36">Budget Harga</th>
                                <th>Spesifikasi</th>
                                <th class="w-10"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(item, idx) in items" :key="idx">
                                <tr>
                                    <td>
                                        <input type="text" :name="`items[${idx}][material_name]`" x-model="item.material_name"
                                               class="form-input text-sm" placeholder="Nama material" required>
                                        <input type="hidden" :name="`items[${idx}][material_id]`" x-model="item.material_id">
                                    </td>
                                    <td>
                                        <input type="text" :name="`items[${idx}][unit_name]`" x-model="item.unit_name"
                                               class="form-input text-sm w-20" placeholder="Satuan" required>
                                    </td>
                                    <td>
                                        <input type="number" :name="`items[${idx}][qty_required]`" x-model="item.qty_required"
                                               class="form-input text-sm text-right" min="0.01" step="0.01" required>
                                    </td>
                                    <td>
                                        <input type="number" :name="`items[${idx}][budget_price]`" x-model="item.budget_price"
                                               class="form-input text-sm text-right" min="0" step="100" placeholder="0">
                                    </td>
                                    <td>
                                        <input type="text" :name="`items[${idx}][specification]`" x-model="item.specification"
                                               class="form-input text-sm" placeholder="Spek detail (opsional)">
                                    </td>
                                    <td>
                                        <button type="button" @click="removeItem(idx)"
                                                class="rounded-lg p-1.5 text-rose-400 hover:bg-rose-50 hover:text-rose-600 transition"
                                                x-show="items.length > 1">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </x-data-card>

        </div>

        {{-- Right: Suppliers + Submit --}}
        <div class="space-y-4">
            <x-data-card>
                <h3 class="text-sm font-semibold text-slate-700 mb-3">Cepat Kirim ke Supplier</h3>
                <p class="text-xs text-slate-500 mb-3">RFQ akan disimpan dulu sebagai Draft. Anda bisa kirim ke supplier dari halaman detail RFQ.</p>
                <div class="space-y-1.5 max-h-64 overflow-y-auto">
                    @foreach($suppliers as $sup)
                    <label class="flex items-center gap-2.5 cursor-pointer rounded-lg px-2 py-1.5 hover:bg-slate-50 transition">
                        <input type="checkbox" name="send_to_suppliers[]" value="{{ $sup->id }}"
                               class="rounded border-slate-300">
                        <span class="text-sm text-slate-700">{{ $sup->name }}</span>
                    </label>
                    @endforeach
                </div>
                <p class="text-xs text-slate-400 mt-2">Centang supplier untuk langsung kirim saat simpan, atau kosongkan untuk simpan sebagai Draft.</p>
            </x-data-card>

            @if($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4">
                <p class="text-sm font-semibold text-rose-700 mb-2">Ada kesalahan:</p>
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $err)
                    <li class="text-xs text-rose-600">{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <button type="submit" class="btn-primary w-full justify-center">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/></svg>
                Simpan RFQ
            </button>
        </div>

    </div>
</form>
@endsection

@php
    $prefilledItems = $materialRequest
        ? $materialRequest->items->map(fn($i) => [
            'material_name' => $i->material?->name ?? $i->material_name ?? '',
            'material_id'   => $i->material_id ?? null,
            'unit_name'     => $i->material?->unit?->name ?? '',
            'qty_required'  => $i->qty,
            'budget_price'  => null,
            'specification' => '',
          ])->values()
        : collect();
@endphp
@push('scripts')
<script>
function rfqCreateForm() {
    const prefilled = @json($prefilledItems);

    return {
        items: prefilled.length ? prefilled : [
            { material_name: '', material_id: null, unit_name: '', qty_required: 1, budget_price: null, specification: '' }
        ],
        addItem() {
            this.items.push({ material_name: '', material_id: null, unit_name: '', qty_required: 1, budget_price: null, specification: '' });
        },
        removeItem(idx) {
            if (this.items.length > 1) this.items.splice(idx, 1);
        },
    };
}
</script>
@endpush
