@extends('layouts.app')

@section('title', 'Catat Gerakan Stok')

@section('content')
<x-page-header title="Catat Gerakan Stok" subtitle="Tambah stok masuk, stok keluar, atau koreksi manual">
    <x-slot name="actions">
        <a href="{{ route('stock.index') }}" class="btn-secondary">← Kembali</a>
    </x-slot>
</x-page-header>

<div class="max-w-2xl">
    <x-data-card title="Form Gerakan Stok" subtitle="Isi detail pergerakan material di gudang">
        <div class="px-6 py-5" x-data="stockForm()">
            <form method="POST" action="{{ route('stock.store') }}" class="space-y-5">
                @csrf

                {{-- Jenis Gerakan --}}
                <div>
                    <label class="form-label-required">Jenis Gerakan</label>
                    <div class="grid grid-cols-3 gap-3 mt-1">
                        <label class="cursor-pointer">
                            <input type="radio" name="movement_type" value="in"
                                   x-model="movementType"
                                   {{ old('movement_type', 'in') === 'in' ? 'checked' : '' }}
                                   class="sr-only">
                            <div :class="movementType === 'in' ? 'border-emerald-500 bg-emerald-50 shadow-sm' : 'border-slate-200 bg-white'"
                                 class="border-2 rounded-xl p-3 text-center transition-all hover:border-emerald-300">
                                <div class="h-8 w-8 rounded-full bg-emerald-100 flex items-center justify-center mx-auto mb-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5L12 21m0 0l-7.5-7.5M12 21V3"/>
                                    </svg>
                                </div>
                                <p class="text-xs font-semibold text-emerald-700">Stok Masuk</p>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="movement_type" value="out"
                                   x-model="movementType"
                                   {{ old('movement_type') === 'out' ? 'checked' : '' }}
                                   class="sr-only">
                            <div :class="movementType === 'out' ? 'border-rose-500 bg-rose-50 shadow-sm' : 'border-slate-200 bg-white'"
                                 class="border-2 rounded-xl p-3 text-center transition-all hover:border-rose-300">
                                <div class="h-8 w-8 rounded-full bg-rose-100 flex items-center justify-center mx-auto mb-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5L12 3m0 0l7.5 7.5M12 3v18"/>
                                    </svg>
                                </div>
                                <p class="text-xs font-semibold text-rose-700">Stok Keluar</p>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="movement_type" value="adjustment"
                                   x-model="movementType"
                                   {{ old('movement_type') === 'adjustment' ? 'checked' : '' }}
                                   class="sr-only">
                            <div :class="movementType === 'adjustment' ? 'border-slate-500 bg-slate-100 shadow-sm' : 'border-slate-200 bg-white'"
                                 class="border-2 rounded-xl p-3 text-center transition-all hover:border-slate-400">
                                <div class="h-8 w-8 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75"/>
                                    </svg>
                                </div>
                                <p class="text-xs font-semibold text-slate-600">Koreksi</p>
                            </div>
                        </label>
                    </div>
                    @error('movement_type')
                        <p class="form-error mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Material --}}
                <div>
                    <label class="form-label-required">Material</label>
                    <select name="material_id"
                            @change="updateUnit($event)"
                            class="form-select @error('material_id') border-rose-400 @enderror">
                        <option value="">— Pilih Material —</option>
                        @foreach ($materials as $m)
                            <option value="{{ $m->id }}"
                                    data-unit="{{ $m->unit?->name }}"
                                    @selected(old('material_id', $material?->id) == $m->id)>
                                {{ $m->name }} @if ($m->code) [{{ $m->code }}] @endif
                            </option>
                        @endforeach
                    </select>
                    @error('material_id')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                    <p class="mt-1.5 text-xs text-slate-400" x-show="selectedUnit" x-text="'Satuan: ' + selectedUnit"></p>
                </div>

                {{-- Jumlah + Harga Satuan --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label-required">Jumlah</label>
                        <input type="number" name="qty" value="{{ old('qty') }}"
                               step="0.01" min="0.01"
                               placeholder="0.00"
                               class="form-input @error('qty') border-rose-400 @enderror">
                        @error('qty')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="form-label">Harga Satuan <span class="text-slate-400 font-normal">(opsional)</span></label>
                        <input type="number" name="unit_price" value="{{ old('unit_price') }}"
                               step="1" min="0" placeholder="0" class="form-input">
                    </div>
                </div>

                {{-- Tanggal --}}
                <div>
                    <label class="form-label-required">Tanggal</label>
                    <input type="date" name="movement_date"
                           value="{{ old('movement_date', today()->format('Y-m-d')) }}"
                           class="form-input @error('movement_date') border-rose-400 @enderror">
                    @error('movement_date')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Proyek (stok keluar) --}}
                <div x-show="movementType === 'out'" x-transition>
                    <label class="form-label">Proyek</label>
                    <select name="project_id" class="form-select">
                        <option value="">— Tanpa Proyek —</option>
                        @foreach ($projects as $p)
                            <option value="{{ $p->id }}" @selected(old('project_id') == $p->id)>
                                {{ $p->name }} @if ($p->code) ({{ $p->code }}) @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Gudang --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Gudang <span class="text-slate-400 font-normal">(opsional)</span></label>
                        <select name="warehouse_id" class="form-select">
                            <option value="">— Pilih Gudang —</option>
                            @foreach ($warehouses as $wh)
                                <option value="{{ $wh->id }}" @selected(old('warehouse_id') == $wh->id)>
                                    {{ $wh->name }}@if($wh->code) ({{ $wh->code }})@endif
                                </option>
                            @endforeach
                        </select>
                        @error('warehouse_id')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="form-label">Lokasi Manual <span class="text-slate-400 font-normal">(opsional)</span></label>
                        <input type="text" name="warehouse" value="{{ old('warehouse') }}"
                               placeholder="Misal: Site A, Lapangan..."
                               class="form-input">
                    </div>
                </div>

                {{-- Catatan --}}
                <div>
                    <label class="form-label">Catatan</label>
                    <textarea name="notes" rows="2" placeholder="Keterangan tambahan..."
                              class="form-input resize-none">{{ old('notes') }}</textarea>
                </div>

                <div class="flex gap-3 pt-1">
                    <button type="submit" class="btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Simpan Gerakan
                    </button>
                    <a href="{{ route('stock.index') }}" class="btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </x-data-card>
</div>
@endsection

@push('scripts')
<script>
function stockForm() {
    return {
        movementType: '{{ old('movement_type', 'in') }}',
        selectedUnit: '{{ $material?->unit?->name ?? '' }}',
        updateUnit(event) {
            const opt = event.target.selectedOptions[0];
            this.selectedUnit = opt?.dataset?.unit || '';
        },
    };
}
</script>
@endpush
