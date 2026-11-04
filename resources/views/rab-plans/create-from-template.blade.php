@extends('layouts.app')
@section('content')

<x-page-header title="Buat RAB dari Template" subtitle="{{ $template->template_name }}">
    <x-slot name="actions">
        <a href="{{ route('rab-plans.templates') }}" class="btn-secondary">← Kembali ke Template</a>
    </x-slot>
</x-page-header>

<div class="bg-indigo-50 border border-indigo-200 rounded-[22px] p-4 mb-5 text-sm text-indigo-800">
    📋 Menggunakan template: <strong>{{ $template->template_name }}</strong> ({{ $template->items->count() }} item · Rp {{ number_format($template->total_budget, 0, ',', '.') }})
</div>

<x-data-card>
    <form method="POST" action="{{ route('rab-plans.store') }}" x-data="rabForm()" class="p-5 space-y-5">
        @csrf
        <input type="hidden" name="template_id" value="{{ $template->id }}">

        {{-- Header RAB --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Proyek <span class="text-rose-500">*</span></label>
                <select name="project_id" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-400">
                    <option value="">-- Pilih Proyek --</option>
                    @foreach($projects as $p)
                    <option value="{{ $p->id }}" {{ old('project_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
                @error('project_id')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Nama RAB <span class="text-rose-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $template->name) }}"
                       class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-400">
                @error('name')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Periode Mulai <span class="text-rose-500">*</span></label>
                <input type="date" name="period_start" value="{{ old('period_start', today()->format('Y-m-d')) }}"
                       class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-400">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Periode Selesai</label>
                <input type="date" name="period_end" value="{{ old('period_end') }}"
                       class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-400">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 mb-1">Deskripsi</label>
                <textarea name="description" rows="2"
                          class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-400">{{ old('description') }}</textarea>
            </div>
        </div>

        {{-- Items dari Template --}}
        <div>
            <h4 class="text-sm font-bold text-slate-700 mb-3">Item RAB (dari template — bisa diubah)</h4>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm border border-slate-100 rounded-xl overflow-hidden">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-3 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Material</th>
                            <th class="px-3 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Satuan</th>
                            <th class="px-3 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500">Qty</th>
                            <th class="px-3 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500">Harga</th>
                            <th class="px-3 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500">Subtotal</th>
                            <th class="px-3 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100" id="items-body">
                        @foreach($template->items as $idx => $item)
                        <tr>
                            <td class="px-3 py-2">
                                <input type="hidden" name="items[{{ $idx }}][material_id]" value="{{ $item->material_id }}">
                                <input type="text" name="items[{{ $idx }}][material_name_snapshot]"
                                       value="{{ old("items.{$idx}.material_name_snapshot", $item->material_name_snapshot) }}"
                                       class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-sm outline-none focus:ring-2 focus:ring-indigo-300">
                            </td>
                            <td class="px-3 py-2">
                                <input type="text" name="items[{{ $idx }}][unit_name_snapshot]"
                                       value="{{ old("items.{$idx}.unit_name_snapshot", $item->unit_name_snapshot) }}"
                                       class="w-24 rounded-lg border border-slate-200 px-2 py-1.5 text-sm outline-none focus:ring-2 focus:ring-indigo-300">
                            </td>
                            <td class="px-3 py-2">
                                <input type="number" name="items[{{ $idx }}][qty_planned]"
                                       value="{{ old("items.{$idx}.qty_planned", $item->qty_planned) }}"
                                       step="0.01" min="0" class="w-24 rounded-lg border border-slate-200 px-2 py-1.5 text-sm text-right outline-none focus:ring-2 focus:ring-indigo-300">
                            </td>
                            <td class="px-3 py-2">
                                <input type="number" name="items[{{ $idx }}][price_planned]"
                                       value="{{ old("items.{$idx}.price_planned", $item->price_planned) }}"
                                       step="1" min="0" class="w-32 rounded-lg border border-slate-200 px-2 py-1.5 text-sm text-right outline-none focus:ring-2 focus:ring-indigo-300">
                            </td>
                            <td class="px-3 py-2 text-right font-semibold text-slate-700">
                                Rp {{ number_format($item->subtotal_planned, 0, ',', '.') }}
                            </td>
                            <td class="px-3 py-2 text-center">
                                <button type="button" onclick="this.closest('tr').remove()" class="text-rose-400 hover:text-rose-600 text-xs">✕</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('rab-plans.templates') }}" class="btn-secondary">Batal</a>
            <button type="submit" class="btn-primary">Buat RAB</button>
        </div>
    </form>
</x-data-card>
@endsection
