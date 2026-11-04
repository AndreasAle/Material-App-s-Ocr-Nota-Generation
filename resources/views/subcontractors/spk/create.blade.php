@extends('layouts.app')
@section('content')

<x-page-header title="Buat SPK Baru" subtitle="Surat Perintah Kerja">
    <x-slot name="actions">
        <a href="{{ route('subcontractors.spk.index') }}" class="btn-secondary">← Kembali</a>
    </x-slot>
</x-page-header>

<x-data-card class="max-w-2xl">
    <form method="POST" action="{{ route('subcontractors.spk.store') }}" class="p-5 space-y-4">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 mb-1">Subkontraktor <span class="text-rose-500">*</span></label>
                <select name="subcontractor_id" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-400 @error('subcontractor_id') border-rose-400 @enderror">
                    <option value="">-- Pilih Subkontraktor --</option>
                    @foreach($subcontractors as $s)
                    <option value="{{ $s->id }}" {{ old('subcontractor_id', $selectedSubId) == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
                @error('subcontractor_id')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 mb-1">Proyek <span class="text-rose-500">*</span></label>
                <select name="project_id" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-400 @error('project_id') border-rose-400 @enderror">
                    <option value="">-- Pilih Proyek --</option>
                    @foreach($projects as $p)
                    <option value="{{ $p->id }}" {{ old('project_id', $selectedProjId) == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Pekerjaan <span class="text-rose-500">*</span></label>
                <input type="text" name="work_name" value="{{ old('work_name') }}"
                       class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-400"
                       placeholder="Pekerjaan Struktur Bawah...">
                @error('work_name')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Nilai Kontrak (Rp) <span class="text-rose-500">*</span></label>
                <input type="number" name="contract_value" value="{{ old('contract_value') }}" min="0" step="1000"
                       class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-400">
                @error('contract_value')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Tanggal Mulai</label>
                <input type="date" name="start_date" value="{{ old('start_date') }}"
                       class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-400">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Tanggal Selesai</label>
                <input type="date" name="end_date" value="{{ old('end_date') }}"
                       class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-400">
            </div>

            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 mb-1">Lingkup Pekerjaan</label>
                <textarea name="scope_of_work" rows="3"
                          class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-400"
                          placeholder="Deskripsi detail pekerjaan...">{{ old('scope_of_work') }}</textarea>
            </div>

            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 mb-1">Catatan</label>
                <textarea name="notes" rows="2"
                          class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-400">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('subcontractors.spk.index') }}" class="btn-secondary">Batal</a>
            <button type="submit" class="btn-primary">Buat SPK</button>
        </div>
    </form>
</x-data-card>
@endsection
