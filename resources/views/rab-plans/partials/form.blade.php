@php $isEdit = isset($rabPlan); @endphp

<div x-data="rabPlanForm()" x-init="init()">

{{-- Validation errors --}}
@if($errors->any())
<div class="alert-box mb-5 border-rose-200 bg-rose-50/90 text-rose-800">
    <div class="flex items-start gap-3">
        <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-rose-100 text-rose-700">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
            </svg>
        </div>
        <div class="min-w-0">
            <div class="font-semibold">Periksa kembali data yang dimasukkan</div>
            <ul class="mt-2 list-disc pl-4 text-sm space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endif

<div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

    {{-- Left column: form --}}
    <div class="xl:col-span-2 space-y-5">

        {{-- Info Umum --}}
        <x-data-card>
            <div class="px-5 py-4 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-800">Informasi RAB</h3>
            </div>
            <div class="grid grid-cols-1 gap-4 px-5 py-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-xs font-semibold text-slate-600">Nama RAB <span class="text-rose-500">*</span></label>
                    <input type="text" name="name"
                        value="{{ old('name', $rabPlan->name ?? '') }}"
                        placeholder="Contoh: Anggaran Pondasi Blok A"
                        class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-300 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-100">
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-slate-600">Proyek <span class="text-rose-500">*</span></label>
                    <select name="project_id"
                        class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 focus:border-indigo-300 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-100">
                        <option value="">-- Pilih Proyek --</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}" @selected(old('project_id', $rabPlan->project_id ?? '') == $p->id)>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-slate-600">Periode Mulai <span class="text-rose-500">*</span></label>
                    <input type="date" name="period_start"
                        value="{{ old('period_start', isset($rabPlan) ? $rabPlan->period_start->format('Y-m-d') : '') }}"
                        class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 focus:border-indigo-300 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-100">
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-slate-600">Periode Selesai</label>
                    <input type="date" name="period_end"
                        value="{{ old('period_end', isset($rabPlan) && $rabPlan->period_end ? $rabPlan->period_end->format('Y-m-d') : '') }}"
                        class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 focus:border-indigo-300 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-100">
                    <p class="mt-1 text-xs text-slate-400">Kosongkan jika tidak ada batas waktu</p>
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-xs font-semibold text-slate-600">Deskripsi</label>
                    <textarea name="description" rows="3"
                        placeholder="Keterangan tambahan tentang RAB ini..."
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-300 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-100 resize-none">{{ old('description', $rabPlan->description ?? '') }}</textarea>
                </div>
            </div>
        </x-data-card>

        {{-- Item RAB --}}
        <x-data-card>
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-800">Item Anggaran</h3>
                <button type="button" @click="addRow()"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-50 border border-indigo-200 px-3 py-1.5 text-xs font-semibold text-indigo-700 hover:bg-indigo-100 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                    </svg>
                    Tambah Item
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50/80 border-b border-slate-100">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500 w-[38%]">Nama Material</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500 w-[12%]">Satuan</th>
                            <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500 w-[15%]">Qty</th>
                            <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500 w-[18%]">Harga Satuan</th>
                            <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500 w-[14%]">Subtotal</th>
                            <th class="px-4 py-3 w-[3%]"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(row, idx) in rows" :key="row.id">
                            <tr class="border-b border-slate-100 hover:bg-slate-50/50">
                                <td class="px-3 py-2">
                                    <input type="hidden" :name="`items[${idx}][material_id]`" :value="row.material_id">
                                    <input type="text"
                                        :name="`items[${idx}][material_name_snapshot]`"
                                        x-model="row.material_name_snapshot"
                                        placeholder="Nama material..."
                                        list="material-list"
                                        @change="onMaterialChange(idx)"
                                        class="h-9 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                                </td>
                                <td class="px-3 py-2">
                                    <input type="text"
                                        :name="`items[${idx}][unit_name_snapshot]`"
                                        x-model="row.unit_name_snapshot"
                                        placeholder="Satuan"
                                        class="h-9 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-900 placeholder-slate-400 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                                </td>
                                <td class="px-3 py-2">
                                    <input type="number"
                                        :name="`items[${idx}][qty_planned]`"
                                        x-model="row.qty_planned"
                                        @input="recalc(idx)"
                                        min="0" step="0.01"
                                        placeholder="0"
                                        class="h-9 w-full rounded-xl border border-slate-200 bg-white px-3 text-right text-sm text-slate-900 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                                </td>
                                <td class="px-3 py-2">
                                    <input type="number"
                                        :name="`items[${idx}][price_planned]`"
                                        x-model="row.price_planned"
                                        @input="recalc(idx)"
                                        min="0" step="1"
                                        placeholder="0"
                                        class="h-9 w-full rounded-xl border border-slate-200 bg-white px-3 text-right text-sm text-slate-900 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                                </td>
                                <td class="px-3 py-2 text-right">
                                    <span class="text-sm font-semibold text-slate-800"
                                        x-text="'Rp ' + formatNum(row.subtotal)"></span>
                                </td>
                                <td class="px-3 py-2 text-center">
                                    <button type="button" @click="removeRow(idx)"
                                        class="text-rose-400 hover:text-rose-600 transition"
                                        title="Hapus baris">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="rows.length === 0">
                            <td colspan="6" class="py-8 text-center text-sm text-slate-400">
                                Belum ada item. Klik "Tambah Item" untuk mulai.
                            </td>
                        </tr>
                    </tbody>
                    <tfoot class="bg-slate-50 border-t-2 border-slate-200">
                        <tr>
                            <td colspan="4" class="px-4 py-3 text-right text-sm font-bold text-slate-700">Total Anggaran</td>
                            <td class="px-3 py-3 text-right font-bold text-slate-900"
                                x-text="'Rp ' + formatNum(grandTotal)"></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Datalist untuk material lookup --}}
            <datalist id="material-list">
                @foreach($materials as $m)
                    <option value="{{ $m->name }}" data-id="{{ $m->id }}" data-unit="{{ $m->unit->name ?? '' }}" data-price="{{ $m->default_price }}">
                @endforeach
            </datalist>
        </x-data-card>

    </div>{{-- end left --}}

    {{-- Right column: summary --}}
    <div class="xl:sticky xl:top-6 space-y-5">
        <x-data-card>
            <div class="px-5 py-4 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-800">Ringkasan</h3>
            </div>
            <div class="px-5 py-5 space-y-4">
                <div class="rounded-2xl bg-indigo-50 border border-indigo-100 p-4">
                    <p class="text-xs font-semibold text-indigo-600 uppercase tracking-wider">Total Anggaran</p>
                    <p class="mt-1 text-2xl font-extrabold text-indigo-900"
                       x-text="'Rp ' + formatNum(grandTotal)"></p>
                    <p class="mt-1 text-xs text-indigo-500"
                       x-text="rows.length + ' item anggaran'"></p>
                </div>

                <button type="submit"
                    class="w-full inline-flex items-center justify-center gap-2 rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-slate-900/10 transition hover:-translate-y-0.5 hover:shadow-xl">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                    </svg>
                    {{ $isEdit ? 'Simpan Perubahan' : 'Simpan RAB' }}
                </button>

                <a href="{{ route('rab-plans.index') }}"
                   class="w-full inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    Batal
                </a>
            </div>
        </x-data-card>
    </div>

</div>{{-- end grid --}}

</div>{{-- end x-data --}}

@push('scripts')
<script>
function rabPlanForm() {
    return {
        rows: [],
        nextId: 0,
        materialMap: {},

        get grandTotal() {
            return this.rows.reduce((s, r) => s + (parseFloat(r.subtotal) || 0), 0);
        },

        init() {
            // Build material map from datalist
            document.querySelectorAll('#material-list option').forEach(opt => {
                this.materialMap[opt.value.toLowerCase()] = {
                    id: opt.dataset.id,
                    unit: opt.dataset.unit,
                    price: parseFloat(opt.dataset.price) || 0,
                };
            });

            // Prefill rows on edit
            @php
                $existingItems = $isEdit ? $rabPlan->items->map(fn($i) => [
                    'material_id'            => $i->material_id,
                    'material_name_snapshot' => $i->material_name_snapshot,
                    'unit_name_snapshot'     => $i->unit_name_snapshot,
                    'qty_planned'            => $i->qty_planned,
                    'price_planned'          => $i->price_planned,
                    'subtotal_planned'       => $i->subtotal_planned,
                ])->values()->toArray() : [];
            @endphp
            const existing = @json($existingItems);

            if (existing.length > 0) {
                existing.forEach(item => {
                    this.rows.push({
                        id: this.nextId++,
                        material_id: item.material_id,
                        material_name_snapshot: item.material_name_snapshot,
                        unit_name_snapshot: item.unit_name_snapshot,
                        qty_planned: item.qty_planned,
                        price_planned: item.price_planned,
                        subtotal: parseFloat(item.subtotal_planned) || 0,
                    });
                });
            } else {
                this.addRow();
            }
        },

        addRow() {
            this.rows.push({
                id: this.nextId++,
                material_id: null,
                material_name_snapshot: '',
                unit_name_snapshot: '',
                qty_planned: '',
                price_planned: '',
                subtotal: 0,
            });
        },

        removeRow(idx) {
            this.rows.splice(idx, 1);
        },

        recalc(idx) {
            const r = this.rows[idx];
            const qty   = parseFloat(r.qty_planned) || 0;
            const price = parseFloat(r.price_planned) || 0;
            r.subtotal = qty * price;
        },

        onMaterialChange(idx) {
            const r = this.rows[idx];
            const key = r.material_name_snapshot.toLowerCase();
            const mat = this.materialMap[key];
            if (mat) {
                r.material_id = mat.id;
                if (!r.unit_name_snapshot) r.unit_name_snapshot = mat.unit;
                if (!r.price_planned) {
                    r.price_planned = mat.price;
                    this.recalc(idx);
                }
            }
        },

        formatNum(val) {
            return Number(val || 0).toLocaleString('id-ID');
        },
    };
}
</script>
@endpush
