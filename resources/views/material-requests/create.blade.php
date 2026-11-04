@extends('layouts.app')

@section('content')
<x-page-header title="Buat Permintaan Material" subtitle="Ajukan kebutuhan material proyek untuk diproses">
    <x-slot name="actions">
        <a href="{{ route('material-requests.index') }}" class="btn-secondary">← Kembali</a>
    </x-slot>
</x-page-header>

<form method="POST" action="{{ route('material-requests.store') }}" x-data="mrForm()">
    @csrf

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left: Info & Submit --}}
        <div class="space-y-5">

            <x-data-card title="Info Permintaan" subtitle="Detail proyek, urgensi, dan jadwal">
                <div class="px-5 py-4 space-y-5">
                    <div>
                        <label class="form-label">Proyek</label>
                        <select name="project_id" class="form-select">
                            <option value="">— Tidak terkait proyek —</option>
                            @foreach($projects as $p)
                                <option value="{{ $p->id }}" {{ old('project_id') == $p->id ? 'selected' : '' }}>
                                    {{ $p->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="form-label-required">Urgensi</label>
                        <select name="urgency" class="form-select" required>
                            <option value="low"      {{ (old('urgency', 'normal')) === 'low'      ? 'selected' : '' }}>Rendah</option>
                            <option value="normal"   {{ (old('urgency', 'normal')) === 'normal'   ? 'selected' : '' }}>Normal</option>
                            <option value="high"     {{ (old('urgency', 'normal')) === 'high'     ? 'selected' : '' }}>Tinggi</option>
                            <option value="critical" {{ (old('urgency', 'normal')) === 'critical' ? 'selected' : '' }}>Kritis</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label">Dibutuhkan Sebelum</label>
                        <input type="date" name="needed_by" value="{{ old('needed_by') }}"
                               min="{{ date('Y-m-d') }}" class="form-input">
                    </div>

                    <div>
                        <label class="form-label">Catatan</label>
                        <textarea name="notes" rows="3" class="form-input resize-none"
                                  placeholder="Keterangan tambahan...">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </x-data-card>

            {{-- Total Summary --}}
            <x-data-card title="Estimasi Total">
                <div class="px-5 py-4">
                    <div class="rounded-xl bg-indigo-50 border border-indigo-100 px-4 py-4 text-center mb-4">
                        <p class="text-xs font-semibold uppercase tracking-wider text-indigo-500 mb-1">Estimasi Biaya</p>
                        <p class="text-2xl font-extrabold text-indigo-700" x-text="'Rp ' + fmt(grandTotal)">Rp 0</p>
                        <p class="text-xs text-slate-400 mt-1" x-text="items.length + ' item'">0 item</p>
                    </div>
                    <button type="submit" class="btn-primary w-full justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Simpan sebagai Draft
                    </button>
                </div>
            </x-data-card>

        </div>

        {{-- Right: Items --}}
        <div class="lg:col-span-2">
            <x-data-card title="Material yang Diminta" subtitle="Isi daftar kebutuhan material secara detail">
                <x-slot name="actions">
                    <button type="button" @click="addItem()" class="btn-secondary text-xs">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                        </svg>
                        Tambah Item
                    </button>
                </x-slot>

                <div class="px-5 py-4">
                    <div class="space-y-4">
                        <template x-for="(item, index) in items" :key="item.id">
                            <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-4">
                                <div class="flex items-center justify-between mb-4">
                                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500"
                                          x-text="'Item #' + (index + 1)"></span>
                                    <button type="button" @click="removeItem(index)" x-show="items.length > 1"
                                            class="inline-flex items-center gap-1 text-xs text-rose-500 hover:text-rose-700 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                        Hapus
                                    </button>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label text-xs">Pilih dari Master Material</label>
                                    <select class="form-select text-sm" @change="pickMaterial($event, index)">
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

                                <input type="hidden" :name="'items['+index+'][material_id]'" :value="item.material_id">

                                <div class="grid grid-cols-2 gap-3">
                                    <div class="col-span-2">
                                        <label class="form-label-required text-xs">Nama Material</label>
                                        <input type="text" :name="'items['+index+'][material_name]'"
                                               x-model="item.material_name" required
                                               class="form-input text-sm"
                                               placeholder="Nama material">
                                    </div>
                                    <div>
                                        <label class="form-label-required text-xs">Satuan</label>
                                        <input type="text" :name="'items['+index+'][unit_name]'"
                                               x-model="item.unit_name" required
                                               class="form-input text-sm"
                                               placeholder="pcs, kg, m2...">
                                    </div>
                                    <div>
                                        <label class="form-label-required text-xs">Qty Diminta</label>
                                        <input type="number" :name="'items['+index+'][qty_requested]'"
                                               x-model.number="item.qty_requested" @input="calcTotal()"
                                               min="0.01" step="0.01" required
                                               class="form-input text-sm">
                                    </div>
                                    <div>
                                        <label class="form-label text-xs">Harga Estimasi</label>
                                        <input type="number" :name="'items['+index+'][estimated_price]'"
                                               x-model.number="item.estimated_price" @input="calcTotal()"
                                               min="0" step="1"
                                               class="form-input text-sm"
                                               placeholder="Opsional">
                                    </div>
                                    <div>
                                        <label class="form-label text-xs">Subtotal Estimasi</label>
                                        <div class="form-input bg-slate-100 text-sm font-semibold text-indigo-700"
                                             x-text="item.estimated_price > 0 ? 'Rp ' + fmt(item.qty_requested * item.estimated_price) : '—'">—</div>
                                    </div>
                                    <div class="col-span-2">
                                        <label class="form-label text-xs">Catatan Item</label>
                                        <input type="text" :name="'items['+index+'][notes]'"
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
function mrForm() {
    return {
        items: [{ id: Date.now(), material_id: '', material_name: '', unit_name: '', qty_requested: 1, estimated_price: 0, notes: '' }],
        grandTotal: 0,
        addItem() {
            this.items.push({ id: Date.now(), material_id: '', material_name: '', unit_name: '', qty_requested: 1, estimated_price: 0, notes: '' });
        },
        removeItem(i) {
            if (this.items.length > 1) { this.items.splice(i, 1); this.calcTotal(); }
        },
        pickMaterial(e, i) {
            const o = e.target.selectedOptions[0];
            if (o.value) {
                this.items[i].material_id     = o.value;
                this.items[i].material_name   = o.dataset.name;
                this.items[i].unit_name       = o.dataset.unit;
                this.items[i].estimated_price = parseFloat(o.dataset.price) || 0;
                this.calcTotal();
            }
        },
        calcTotal() {
            this.grandTotal = this.items.reduce((s, i) => s + (parseFloat(i.qty_requested)||0)*(parseFloat(i.estimated_price)||0), 0);
        },
        fmt(v) { return new Intl.NumberFormat('id-ID').format(Math.round(v||0)); }
    };
}
</script>
@endpush
@endsection
