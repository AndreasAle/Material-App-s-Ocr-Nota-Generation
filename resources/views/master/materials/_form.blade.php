@csrf
<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    <div>
        <label class="form-label-required">Kategori Material</label>
        <select name="material_category_id" class="form-select" required>
            <option value="">-- Pilih Kategori --</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" {{ (string) old('material_category_id', $material->material_category_id ?? '') === (string) $category->id ? 'selected' : '' }}>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
        @error('material_category_id')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="form-label-required">Satuan</label>
        <select name="unit_id" class="form-select" required>
            <option value="">-- Pilih Satuan --</option>
            @foreach($units as $unit)
                <option value="{{ $unit->id }}" {{ (string) old('unit_id', $material->unit_id ?? '') === (string) $unit->id ? 'selected' : '' }}>
                    {{ $unit->name }} ({{ $unit->code }})
                </option>
            @endforeach
        </select>
        @error('unit_id')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="form-label-required">Kode Material</label>
        <input type="text" name="code" value="{{ old('code', $material->code ?? '') }}"
               placeholder="Contoh: MAT-001" class="form-input" required>
        @error('code')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="form-label-required">Nama Material</label>
        <input type="text" name="name" value="{{ old('name', $material->name ?? '') }}"
               placeholder="Nama material" class="form-input" required>
        @error('name')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="form-label">Harga Default (Rp)</label>
        <input type="number" step="0.01" min="0" name="default_price"
               value="{{ old('default_price', $material->default_price ?? 0) }}"
               placeholder="0" class="form-input">
        @error('default_price')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="form-label">Barcode</label>
        <input type="text" name="barcode" value="{{ old('barcode', $material->barcode ?? '') }}"
               placeholder="Scan / ketik barcode (opsional)" class="form-input">
    </div>

    <div>
        <label class="form-label">Stok Minimum (satuan dasar)</label>
        <input type="number" step="0.01" min="0" name="minimum_stock"
               value="{{ old('minimum_stock', $material->minimum_stock ?? '') }}" class="form-input" placeholder="Peringatan bila stok di bawah ini">
    </div>
</div>

{{-- Harga jual --}}
<div class="mt-6 rounded-2xl border border-emerald-100 bg-emerald-50/50 p-4">
    <p class="text-sm font-bold text-emerald-900">Harga Jual (per satuan dasar)</p>
    @if(isset($material) && $material->last_purchase_price)
        <p class="mt-1 text-xs text-emerald-800">
            Harga beli terakhir: <b>Rp {{ number_format($material->last_purchase_price, 0, ',', '.') }}</b>
            · Saran jual ({{ config('store.default_margin') }}%): <b>Rp {{ number_format($material->suggestedSellPrice(), 0, ',', '.') }}</b>
            <button type="button" class="ml-1 font-semibold text-indigo-600 underline"
                    onclick="document.getElementById('sell-price-input').value = {{ $material->suggestedSellPrice() }}">pakai saran</button>
        </p>
    @endif
    <div class="mt-3 grid grid-cols-1 gap-4 md:grid-cols-3">
        <div>
            <label class="form-label">Harga Eceran (Rp)</label>
            <input type="number" step="1" min="0" name="sell_price" id="sell-price-input"
                   value="{{ old('sell_price', isset($material) ? (float) $material->sell_price : '') }}" class="form-input" placeholder="0">
            @error('sell_price') <p class="form-error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="form-label">Harga Grosir (Rp)</label>
            <input type="number" step="1" min="0" name="wholesale_price"
                   value="{{ old('wholesale_price', $material->wholesale_price ?? '') }}" class="form-input" placeholder="Opsional">
        </div>
        <div>
            <label class="form-label">Grosir mulai qty</label>
            <input type="number" step="0.01" min="0" name="wholesale_min_qty"
                   value="{{ old('wholesale_min_qty', $material->wholesale_min_qty ?? '') }}" class="form-input" placeholder="mis. 50">
            @error('wholesale_min_qty') <p class="form-error">{{ $message }}</p> @enderror
        </div>
    </div>
</div>

{{-- Satuan ganda --}}
@php
    $unitRows = old('units', isset($material) ? $material->units->map(fn ($u) => ['unit_name' => $u->unit_name, 'factor' => (float) $u->factor, 'sell_price' => $u->sell_price !== null ? (float) $u->sell_price : ''])->all() : []);
@endphp
<div class="mt-5 rounded-2xl border border-slate-200 p-4" x-data="{ rows: @js(array_values($unitRows)) }">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm font-bold text-slate-800">Satuan Lain &amp; Konversi</p>
            <p class="text-xs text-slate-500">Contoh: 1 <b>Ikat</b> = 10 (satuan dasar Batang). Dipakai saat beli per ikat &amp; jual per batang.</p>
        </div>
        <button type="button" class="btn-secondary" @click="rows.push({unit_name:'', factor:'', sell_price:''})">+ Satuan</button>
    </div>
    <template x-for="(row, i) in rows" :key="i">
        <div class="mt-3 grid grid-cols-12 items-end gap-2">
            <div class="col-span-4">
                <label class="form-label text-xs">Nama satuan</label>
                <input type="text" class="form-input" :name="`units[${i}][unit_name]`" x-model="row.unit_name" placeholder="Ikat / Dus / Palet">
            </div>
            <div class="col-span-3">
                <label class="form-label text-xs">Isi (satuan dasar)</label>
                <input type="number" step="0.0001" min="0" class="form-input" :name="`units[${i}][factor]`" x-model="row.factor" placeholder="10">
            </div>
            <div class="col-span-4">
                <label class="form-label text-xs">Harga jual / satuan ini</label>
                <input type="number" step="1" min="0" class="form-input" :name="`units[${i}][sell_price]`" x-model="row.sell_price" placeholder="Otomatis">
            </div>
            <div class="col-span-1">
                <button type="button" class="btn-secondary w-full" @click="rows.splice(i, 1)">✕</button>
            </div>
        </div>
    </template>
    @error('units.*.unit_name') <p class="form-error">{{ $message }}</p> @enderror
</div>

<div class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
    <div class="flex items-end pb-1">
        <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 hover:bg-white transition-colors">
            <input type="checkbox" name="is_active" value="1"
                   {{ old('is_active', $material->is_active ?? true) ? 'checked' : '' }}
                   class="h-4 w-4 rounded accent-indigo-600">
            <span class="text-sm font-medium text-slate-700">Material aktif</span>
        </label>
    </div>
</div>

<div class="mt-5">
    <label class="form-label">Spesifikasi / Keterangan</label>
    <textarea name="specification" rows="4" placeholder="Spesifikasi teknis atau keterangan tambahan..."
              class="form-input resize-none">{{ old('specification', $material->specification ?? '') }}</textarea>
</div>

<div class="mt-6 flex justify-end gap-3">
    <a href="{{ route('materials.index') }}" class="btn-secondary">Batal</a>
    <button type="submit" class="btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        Simpan
    </button>
</div>
