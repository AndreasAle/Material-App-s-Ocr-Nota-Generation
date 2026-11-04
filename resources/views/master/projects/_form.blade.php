@csrf
<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    <div>
        <label class="form-label-required">Nama Proyek</label>
        <input type="text" name="name" value="{{ old('name', $project->name ?? '') }}"
               placeholder="Nama proyek" class="form-input" required>
        @error('name')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="form-label-required">Kode Proyek</label>
        <input type="text" name="code" value="{{ old('code', $project->code ?? '') }}"
               placeholder="PRJ-001" class="form-input" required>
        @error('code')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="form-label">Lokasi</label>
        <input type="text" name="location" value="{{ old('location', $project->location ?? '') }}"
               placeholder="Kota atau alamat proyek" class="form-input">
    </div>

    <div>
        <label class="form-label">Tanggal Mulai</label>
        <input type="date" name="start_date"
               value="{{ old('start_date', isset($project->start_date) ? $project->start_date->format('Y-m-d') : '') }}"
               class="form-input">
    </div>
</div>

<div class="mt-5">
    <label class="form-label">Deskripsi</label>
    <textarea name="description" rows="4" placeholder="Deskripsi singkat tentang proyek ini..."
              class="form-input resize-none">{{ old('description', $project->description ?? '') }}</textarea>
</div>

<div class="mt-5">
    <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 hover:bg-white transition-colors">
        <input type="checkbox" name="is_active" value="1"
               {{ old('is_active', $project->is_active ?? true) ? 'checked' : '' }}
               class="h-4 w-4 rounded accent-indigo-600">
        <span class="text-sm font-medium text-slate-700">Proyek aktif</span>
    </label>
</div>

<div class="mt-6 flex justify-end gap-3">
    <a href="{{ route('projects.index') }}" class="btn-secondary">Batal</a>
    <button type="submit" class="btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        Simpan
    </button>
</div>
