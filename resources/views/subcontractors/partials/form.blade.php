@php $isEdit = isset($subcontractor); @endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div class="sm:col-span-2">
        <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Subkontraktor <span class="text-rose-500">*</span></label>
        <input type="text" name="name" value="{{ old('name', $subcontractor->name ?? '') }}"
               class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 outline-none @error('name') border-rose-400 @enderror"
               placeholder="PT / CV / UD / Perorangan...">
        @error('name')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Kode</label>
        <input type="text" name="code" value="{{ old('code', $subcontractor->code ?? '') }}"
               class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 outline-none"
               placeholder="SKT-001 (opsional)">
        @error('code')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Bidang Keahlian</label>
        <input type="text" name="specialization" value="{{ old('specialization', $subcontractor->specialization ?? '') }}"
               class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 outline-none"
               placeholder="Sipil, Mekanikal, Listrik...">
    </div>

    <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Nama PIC / Kontak</label>
        <input type="text" name="contact_person" value="{{ old('contact_person', $subcontractor->contact_person ?? '') }}"
               class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 outline-none">
    </div>

    <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Nomor Telepon</label>
        <input type="text" name="phone" value="{{ old('phone', $subcontractor->phone ?? '') }}"
               class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 outline-none">
    </div>

    <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">Email</label>
        <input type="email" name="email" value="{{ old('email', $subcontractor->email ?? '') }}"
               class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 outline-none">
    </div>

    <div class="sm:col-span-2">
        <label class="block text-xs font-semibold text-slate-600 mb-1">Alamat</label>
        <textarea name="address" rows="2"
                  class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 outline-none">{{ old('address', $subcontractor->address ?? '') }}</textarea>
    </div>

    <div class="sm:col-span-2">
        <label class="block text-xs font-semibold text-slate-600 mb-1">Catatan</label>
        <textarea name="notes" rows="2"
                  class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 outline-none">{{ old('notes', $subcontractor->notes ?? '') }}</textarea>
    </div>

    <div>
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="is_active" value="1" class="rounded"
                   {{ old('is_active', ($isEdit ? $subcontractor->is_active : true)) ? 'checked' : '' }}>
            <span class="text-sm text-slate-700">Aktif</span>
        </label>
    </div>
</div>
