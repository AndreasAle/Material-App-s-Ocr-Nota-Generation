@extends('layouts.app')

@section('content')
<x-page-header title="Buat Purchase Order Baru" subtitle="Buat PO pembelian material untuk proyek">
    <x-slot name="actions">
        <a href="{{ route('purchase-orders.index') }}" class="btn-secondary">← Kembali</a>
    </x-slot>
</x-page-header>

<form method="POST" action="{{ route('purchase-orders.store') }}" x-data="poForm()">
    @csrf

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left: PO Info & Total --}}
        <div class="space-y-5">

            <x-data-card title="Informasi PO" subtitle="Proyek, supplier, dan detail pengiriman">
                <div class="px-5 py-4 space-y-5">
                    <div>
                        <label class="form-label">Proyek</label>
                        <select name="project_id" class="form-select">
                            <option value="">— Pilih Proyek —</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}" {{ old('project_id') == $project->id ? 'selected' : '' }}>
                                    {{ $project->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('project_id')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="form-label">Supplier</label>
                        <select name="supplier_id" class="form-select">
                            <option value="">— Pilih Supplier —</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('supplier_id')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="form-label">Tgl. Estimasi Kirim</label>
                        <input type="date" name="expected_date" value="{{ old('expected_date') }}"
                               min="{{ date('Y-m-d') }}" class="form-input">
                        @error('expected_date')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="form-label">Catatan</label>
                        <textarea name="notes" rows="3" class="form-input resize-none"
                                  placeholder="Catatan atau instruksi khusus...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </x-data-card>

            {{-- Total Summary --}}
            <x-data-card title="Total PO">
                <div class="px-5 py-4">
                    <div class="rounded-xl bg-indigo-50 border border-indigo-100 px-4 py-4 text-center mb-4">
                        <p class="text-xs font-semibold uppercase tracking-wider text-indigo-500 mb-1">Grand Total</p>
                        <p class="text-2xl font-extrabold text-indigo-700" x-text="'Rp ' + formatRp(grandTotal)">Rp 0</p>
                        <p class="text-xs text-slate-400 mt-1" x-text="items.length + ' item'">0 item</p>
                    </div>
                    <button type="submit" class="btn-primary w-full justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Simpan Purchase Order
                    </button>
                </div>
            </x-data-card>
        </div>

        {{-- Right: Items --}}
        <div class="lg:col-span-2">
            <x-data-card title="Item PO" subtitle="Daftar material yang akan dipesan">
                <x-slot name="actions">
                    <button type="button" @click="addItem()" class="btn-secondary text-xs">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                        </svg>
                        Tambah Item
                    </button>
                </x-slot>

                <div class="px-5 py-4">
                    @error('items')
                        <p class="form-error mb-4">{{ $message }}</p>
                    @enderror

                    <div class="space-y-4">
                        <template x-for="(item, index) in items" :key="item.id">
                            <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-4">
                                <div class="flex items-center justify-between mb-4">
                                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500"
                                          x-text="'Item #' + (index + 1)"></span>
                                    <button type="button" @click="removeItem(index)"
                                            x-show="items.length > 1"
                                            class="inline-flex items-center gap-1 text-xs text-rose-500 hover:text-rose-700 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                        Hapus
                                    </button>
                                </div>

                                {{-- Material select --}}
                                <div class="mb-4">
                                    <label class="form-label text-xs">Pilih dari Master Material</label>
                                    <select class="form-select text-sm"
                                            @change="selectMaterial($event, index)"
                                            :name="'items[' + index + '][material_id]'">
                                        <option value="">— Pilih atau isi manual —</option>
                                        @foreach($materials as $m)
                                        <option value="{{ $m->id }}"
                                                data-name="{{ $m->name }}"
                                                data-unit="{{ $m->unit?->name ?? '' }}"
                                                data-price="{{ $m->default_price ?? 0 }}">
                                            {{ $m->name }} ({{ $m->unit?->name ?? '-' }})
                                        </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div class="col-span-2">
                                        <label class="form-label-required text-xs">Nama Material</label>
                                        <input type="text" :name="'items[' + index + '][material_name]'"
                                               x-model="item.material_name" required
                                               class="form-input text-sm"
                                               placeholder="Nama material">
                                    </div>
                                    <div>
                                        <label class="form-label-required text-xs">Satuan</label>
                                        <input type="text" :name="'items[' + index + '][unit_name]'"
                                               x-model="item.unit_name" required
                                               class="form-input text-sm"
                                               placeholder="pcs, m2, kg...">
                                    </div>
                                    <div>
                                        <label class="form-label-required text-xs">Qty</label>
                                        <input type="number" :name="'items[' + index + '][qty]'"
                                               x-model.number="item.qty" @input="calcTotal()"
                                               min="0.01" step="0.01" required
                                               class="form-input text-sm">
                                    </div>
                                    <div>
                                        <label class="form-label-required text-xs">Harga Satuan</label>
                                        <input type="number" :name="'items[' + index + '][unit_price]'"
                                               x-model.number="item.unit_price" @input="calcTotal()"
                                               min="0" step="1" required
                                               class="form-input text-sm">
                                    </div>
                                    <div>
                                        <label class="form-label text-xs">Subtotal</label>
                                        <div class="form-input bg-slate-100 text-sm font-semibold text-indigo-700"
                                             x-text="'Rp ' + formatRp(item.qty * item.unit_price)">Rp 0</div>
                                    </div>
                                    <div class="col-span-2">
                                        <label class="form-label text-xs">Catatan Item</label>
                                        <input type="text" :name="'items[' + index + '][notes]'"
                                               x-model="item.notes"
                                               class="form-input text-sm"
                                               placeholder="Opsional">
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <button type="button" @click="addItem()"
                            class="mt-4 w-full border-2 border-dashed border-slate-200 rounded-xl py-3 text-sm font-medium text-slate-400 hover:border-indigo-300 hover:text-indigo-500 transition-colors">
                        + Tambah Item Lagi
                    </button>
                </div>
            </x-data-card>
        </div>

    </div>
</form>

@push('scripts')
<script>
function poForm() {
    return {
        items: [{ id: Date.now(), material_id: '', material_name: '', unit_name: '', qty: 1, unit_price: 0, notes: '' }],
        grandTotal: 0,

        addItem() {
            this.items.push({ id: Date.now(), material_id: '', material_name: '', unit_name: '', qty: 1, unit_price: 0, notes: '' });
        },

        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
                this.calcTotal();
            }
        },

        selectMaterial(event, index) {
            const opt = event.target.selectedOptions[0];
            if (opt.value) {
                this.items[index].material_id   = opt.value;
                this.items[index].material_name = opt.dataset.name || '';
                this.items[index].unit_name     = opt.dataset.unit || '';
                this.items[index].unit_price    = parseFloat(opt.dataset.price) || 0;
                this.calcTotal();
            }
        },

        calcTotal() {
            this.grandTotal = this.items.reduce((sum, i) => sum + (parseFloat(i.qty) || 0) * (parseFloat(i.unit_price) || 0), 0);
        },

        formatRp(val) {
            return new Intl.NumberFormat('id-ID').format(Math.round(val || 0));
        }
    };
}
</script>
@endpush
@endsection
