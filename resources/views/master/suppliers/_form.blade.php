@csrf
<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    <div>
        <label class="form-label-required">Nama Supplier</label>
        <input type="text" name="name" value="{{ old('name', $supplier->name ?? '') }}"
               placeholder="Nama supplier atau perusahaan" class="form-input" required>
        @error('name')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="form-label">No. Telepon</label>
        <input type="text" name="phone" value="{{ old('phone', $supplier->phone ?? '') }}"
               placeholder="08xxxxxxxxxx" class="form-input">
        @error('phone')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="form-label">Email</label>
        <input type="email" name="email" value="{{ old('email', $supplier->email ?? '') }}"
               placeholder="email@supplier.com" class="form-input">
        @error('email')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="form-label">Kota</label>
        <input type="text" name="city" value="{{ old('city', $supplier->city ?? '') }}"
               placeholder="Jakarta, Surabaya, ..." class="form-input">
        @error('city')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="mt-5">
    <label class="form-label">Alamat Lengkap</label>
    <textarea name="address" rows="3" placeholder="Jalan, nomor, kelurahan, kecamatan..."
              class="form-input resize-none">{{ old('address', $supplier->address ?? '') }}</textarea>
</div>

<div class="mt-5">
    <label class="form-label">Catatan</label>
    <textarea name="notes" rows="3" placeholder="Catatan tambahan tentang supplier ini..."
              class="form-input resize-none">{{ old('notes', $supplier->notes ?? '') }}</textarea>
</div>

<div class="mt-5">
    <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 hover:bg-white transition-colors">
        <input type="checkbox" name="is_active" value="1"
               {{ old('is_active', $supplier->is_active ?? true) ? 'checked' : '' }}
               class="h-4 w-4 rounded accent-indigo-600">
        <span class="text-sm font-medium text-slate-700">Supplier aktif</span>
    </label>
</div>

<div class="mt-6 flex justify-end gap-3">
    <a href="{{ route('suppliers.index') }}" class="btn-secondary">Batal</a>
    <button type="submit" class="btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        Simpan
    </button>
</div>
