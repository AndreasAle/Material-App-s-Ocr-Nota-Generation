@extends('layouts.app')

@section('title', 'Buat Invoice')

@section('content')
<x-page-header title="Buat Invoice Baru" subtitle="Isi data klien, proyek, dan item tagihan">
    <x-slot name="actions">
        <a href="{{ route('invoices.index') }}" class="btn-secondary">← Kembali</a>
    </x-slot>
</x-page-header>

<form method="POST" action="{{ route('invoices.store') }}" x-data="invoiceForm()" class="space-y-6">
    @csrf

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left: Info & Items --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Informasi Invoice --}}
            <x-data-card title="Informasi Invoice" subtitle="Nomor, tanggal, dan proyek terkait">
                <div class="px-6 py-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="form-label-required">No. Invoice</label>
                            <input type="text" name="invoice_no" value="{{ old('invoice_no', $nextNo) }}"
                                   class="form-input @error('invoice_no') border-rose-400 @enderror">
                            @error('invoice_no')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="form-label">Proyek</label>
                            <select name="project_id" class="form-select">
                                <option value="">— Tanpa Proyek —</option>
                                @foreach ($projects as $p)
                                    <option value="{{ $p->id }}" @selected(old('project_id', $project?->id) == $p->id)>
                                        {{ $p->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label-required">Tanggal Invoice</label>
                            <input type="date" name="invoice_date"
                                   value="{{ old('invoice_date', today()->format('Y-m-d')) }}"
                                   class="form-input @error('invoice_date') border-rose-400 @enderror">
                            @error('invoice_date')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="form-label">Jatuh Tempo</label>
                            <input type="date" name="due_date" value="{{ old('due_date') }}"
                                   class="form-input">
                        </div>
                    </div>
                </div>
            </x-data-card>

            {{-- Data Klien --}}
            <x-data-card title="Data Klien" subtitle="Informasi klien atau perusahaan yang ditagih">
                <div class="px-6 py-5 space-y-5">
                    <div>
                        <label class="form-label-required">Nama Klien / Perusahaan</label>
                        <input type="text" name="client_name" value="{{ old('client_name') }}"
                               placeholder="Nama klien atau perusahaan"
                               class="form-input @error('client_name') border-rose-400 @enderror">
                        @error('client_name')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="form-label">No. Telepon</label>
                            <input type="text" name="client_phone" value="{{ old('client_phone') }}"
                                   placeholder="08xxxxxxxxxx" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Alamat</label>
                            <input type="text" name="client_address" value="{{ old('client_address') }}"
                                   placeholder="Alamat klien" class="form-input">
                        </div>
                    </div>
                </div>
            </x-data-card>

            {{-- Item Tagihan --}}
            <x-data-card title="Item Tagihan" subtitle="Rincian pekerjaan atau material yang ditagihkan">
                <x-slot name="actions">
                    <button type="button" @click="addItem()" class="btn-secondary text-xs">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                        </svg>
                        Tambah Baris
                    </button>
                </x-slot>

                <div class="px-6 py-5">
                    @error('items')
                        <p class="form-error mb-4">{{ $message }}</p>
                    @enderror

                    <div class="overflow-x-auto -mx-2">
                        <table class="tbl">
                            <thead>
                                <tr>
                                    <th>Deskripsi</th>
                                    <th class="w-24">Satuan</th>
                                    <th class="th-right w-24">Qty</th>
                                    <th class="th-right w-36">Harga Satuan</th>
                                    <th class="th-right w-36">Subtotal</th>
                                    <th class="w-10"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(item, idx) in items" :key="item.id">
                                    <tr>
                                        <td class="px-2 py-1.5">
                                            <input type="text" :name="`items[${idx}][description]`"
                                                   x-model="item.description"
                                                   placeholder="Nama pekerjaan / material"
                                                   class="form-input text-xs py-1.5">
                                        </td>
                                        <td class="px-2 py-1.5">
                                            <input type="text" :name="`items[${idx}][unit]`"
                                                   x-model="item.unit"
                                                   placeholder="unit"
                                                   class="form-input text-xs py-1.5">
                                        </td>
                                        <td class="px-2 py-1.5">
                                            <input type="number" :name="`items[${idx}][qty]`"
                                                   x-model.number="item.qty"
                                                   @input="calcSubtotal(item)"
                                                   step="0.01" min="0"
                                                   class="form-input text-xs py-1.5 text-right">
                                        </td>
                                        <td class="px-2 py-1.5">
                                            <input type="number" :name="`items[${idx}][unit_price]`"
                                                   x-model.number="item.unit_price"
                                                   @input="calcSubtotal(item)"
                                                   step="1" min="0"
                                                   class="form-input text-xs py-1.5 text-right">
                                        </td>
                                        <td class="px-3 py-1.5 text-right font-semibold text-slate-700 text-xs"
                                            x-text="'Rp ' + item.subtotal.toLocaleString('id-ID')">
                                        </td>
                                        <td class="px-2 py-1.5 text-center">
                                            <button type="button" @click="removeItem(idx)"
                                                    class="p-1.5 rounded-lg text-slate-300 hover:text-rose-500 hover:bg-rose-50 transition-colors"
                                                    :disabled="items.length <= 1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </x-data-card>

        </div>

        {{-- Right: Ringkasan & Catatan --}}
        <div class="space-y-5">

            <x-data-card title="Ringkasan" subtitle="Total tagihan dengan diskon & pajak">
                <div class="px-5 py-4 space-y-4">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Subtotal</span>
                        <span class="font-semibold text-slate-800" x-text="'Rp ' + subtotal.toLocaleString('id-ID')">Rp 0</span>
                    </div>

                    <div>
                        <label class="form-label">Diskon (Rp)</label>
                        <input type="number" name="discount_amount"
                               x-model.number="discount"
                               @input="calcGrandTotal()"
                               step="1000" min="0" value="{{ old('discount_amount', 0) }}"
                               class="form-input text-right">
                    </div>

                    <div>
                        <label class="form-label">Pajak PPN (%)</label>
                        <input type="number" name="tax_percent"
                               x-model.number="taxPercent"
                               @input="calcGrandTotal()"
                               step="0.1" min="0" max="100" value="{{ old('tax_percent', 11) }}"
                               class="form-input text-right">
                    </div>

                    <div class="flex justify-between text-xs text-slate-400">
                        <span>Pajak</span>
                        <span x-text="'Rp ' + tax.toLocaleString('id-ID')">Rp 0</span>
                    </div>

                    <div class="border-t border-slate-200 pt-4 flex justify-between items-center">
                        <span class="font-bold text-slate-700">Grand Total</span>
                        <span class="font-bold text-indigo-600 text-lg" x-text="'Rp ' + grandTotal.toLocaleString('id-ID')">Rp 0</span>
                    </div>
                </div>
            </x-data-card>

            <x-data-card title="Catatan">
                <div class="px-5 py-4">
                    <textarea name="notes" rows="4" placeholder="Catatan atau syarat pembayaran..."
                              class="form-input resize-none">{{ old('notes') }}</textarea>
                </div>
            </x-data-card>

            <div class="space-y-2">
                <button type="submit" class="btn-primary w-full justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Simpan Invoice
                </button>
                <a href="{{ route('invoices.index') }}" class="btn-secondary w-full justify-center block text-center">Batal</a>
            </div>

        </div>

    </div>
</form>
@endsection

@push('scripts')
<script>
function invoiceForm() {
    return {
        items: [{ id: 1, description: '', unit: '', qty: 1, unit_price: 0, subtotal: 0 }],
        nextId: 2,
        discount: {{ old('discount_amount', 0) }},
        taxPercent: {{ old('tax_percent', 11) }},
        subtotal: 0,
        tax: 0,
        grandTotal: 0,

        addItem() {
            this.items.push({ id: this.nextId++, description: '', unit: '', qty: 1, unit_price: 0, subtotal: 0 });
        },
        removeItem(idx) {
            if (this.items.length > 1) {
                this.items.splice(idx, 1);
                this.calcGrandTotal();
            }
        },
        calcSubtotal(item) {
            item.subtotal = Math.round((item.qty || 0) * (item.unit_price || 0));
            this.calcGrandTotal();
        },
        calcGrandTotal() {
            this.subtotal   = this.items.reduce((s, i) => s + (i.subtotal || 0), 0);
            const taxBase   = this.subtotal - (this.discount || 0);
            this.tax        = Math.round(taxBase * (this.taxPercent || 0) / 100);
            this.grandTotal = taxBase + this.tax;
        },
    };
}
</script>
@endpush
