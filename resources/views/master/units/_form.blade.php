@csrf
<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <div>
        <label class="form-label-required">Nama Satuan</label>
        <input type="text" name="name" value="{{ old('name', $unit->name ?? '') }}"
               placeholder="Contoh: Kilogram, Meter, Buah" class="form-input" required>
        @error('name')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="form-label-required">Kode Satuan</label>
        <input type="text" name="code" value="{{ old('code', $unit->code ?? '') }}"
               placeholder="Contoh: kg, m, pcs" class="form-input" required>
        @error('code')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="mt-5">
    <label class="form-label">Deskripsi</label>
    <textarea name="description" rows="4" placeholder="Keterangan tambahan tentang satuan ini..."
              class="form-input resize-none">{{ old('description', $unit->description ?? '') }}</textarea>
    @error('description')
        <p class="form-error">{{ $message }}</p>
    @enderror
</div>

<div class="mt-6 flex justify-end gap-3">
    <a href="{{ route('units.index') }}" class="btn-secondary">Batal</a>
    <button type="submit" class="btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        Simpan
    </button>
</div>
