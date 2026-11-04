@extends('layouts.app')
@section('content')

<x-page-header title="Edit SPK" subtitle="{{ $subcontract->spk_no }}">
    <x-slot name="actions">
        <a href="{{ route('subcontractors.spk.show', $subcontract) }}" class="btn-secondary">← Kembali</a>
    </x-slot>
</x-page-header>

<x-data-card class="max-w-2xl">
    <form method="POST" action="{{ route('subcontractors.spk.update', $subcontract) }}" class="p-5 space-y-4">
        @csrf @method('PUT')

        <div class="bg-slate-50 rounded-xl px-4 py-3 text-sm text-slate-600 mb-2">
            <strong>SPK:</strong> {{ $subcontract->spk_no }} &nbsp;|&nbsp;
            <strong>Subkontraktor:</strong> {{ $subcontract->subcontractor->name }} &nbsp;|&nbsp;
            <strong>Proyek:</strong> {{ $subcontract->project->name }}
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Pekerjaan <span class="text-rose-500">*</span></label>
                <input type="text" name="work_name" value="{{ old('work_name', $subcontract->work_name) }}"
                       class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-400">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Nilai Kontrak (Rp) <span class="text-rose-500">*</span></label>
                <input type="number" name="contract_value" value="{{ old('contract_value', $subcontract->contract_value) }}"
                       min="0" step="1000" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-400">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Progress (%)</label>
                <input type="number" name="progress_pct" value="{{ old('progress_pct', $subcontract->progress_pct) }}"
                       min="0" max="100" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-400">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Tanggal Mulai</label>
                <input type="date" name="start_date" value="{{ old('start_date', optional($subcontract->start_date)->format('Y-m-d')) }}"
                       class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-400">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Tanggal Selesai</label>
                <input type="date" name="end_date" value="{{ old('end_date', optional($subcontract->end_date)->format('Y-m-d')) }}"
                       class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-400">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Status</label>
                <select name="status" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-400">
                    @foreach(['draft'=>'Draft','active'=>'Aktif','completed'=>'Selesai','cancelled'=>'Dibatalkan'] as $val => $label)
                    <option value="{{ $val }}" {{ old('status', $subcontract->status) == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 mb-1">Lingkup Pekerjaan</label>
                <textarea name="scope_of_work" rows="3"
                          class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-400">{{ old('scope_of_work', $subcontract->scope_of_work) }}</textarea>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 mb-1">Catatan</label>
                <textarea name="notes" rows="2"
                          class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-400">{{ old('notes', $subcontract->notes) }}</textarea>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('subcontractors.spk.show', $subcontract) }}" class="btn-secondary">Batal</a>
            <button type="submit" class="btn-primary">Simpan Perubahan</button>
        </div>
    </form>
</x-data-card>
@endsection
