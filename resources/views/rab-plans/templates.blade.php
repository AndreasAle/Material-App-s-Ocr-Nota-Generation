@extends('layouts.app')
@section('content')

<x-page-header title="Template RAB" subtitle="RAB yang disimpan sebagai template reusable">
    <x-slot name="actions">
        <a href="{{ route('rab-plans.index') }}" class="btn-secondary">← Kembali ke RAB</a>
    </x-slot>
</x-page-header>

@if($templates->isEmpty())
<x-data-card>
    <div class="py-16 text-center text-slate-400">
        <div class="text-5xl mb-3">📋</div>
        <p class="font-semibold text-slate-600">Belum ada template RAB</p>
        <p class="text-sm mt-1">Buka halaman Detail RAB → klik "Simpan sebagai Template"</p>
    </div>
</x-data-card>
@else
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach($templates as $t)
    <div class="rounded-[22px] bg-white border border-slate-200 shadow-sm p-5 flex flex-col">
        <div class="flex items-start gap-3 mb-3">
            <div class="text-2xl">📋</div>
            <div>
                <p class="font-bold text-slate-800">{{ $t->template_name }}</p>
                <p class="text-xs text-slate-400">{{ $t->name }}</p>
            </div>
        </div>

        <div class="space-y-1 text-sm flex-1">
            <div class="flex justify-between">
                <span class="text-slate-500">Proyek Asal</span>
                <span class="font-medium text-slate-700">{{ $t->project->name ?? '—' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Jumlah Item</span>
                <span class="font-medium text-slate-700">{{ $t->items->count() }} item</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Total Budget</span>
                <span class="font-semibold text-indigo-700">Rp {{ number_format($t->total_budget, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Dibuat</span>
                <span class="text-slate-500 text-xs">{{ $t->created_at->format('d M Y') }}</span>
            </div>
        </div>

        <div class="mt-4 flex gap-2">
            <a href="{{ route('rab-plans.from-template', $t) }}"
               class="flex-1 text-center rounded-xl bg-indigo-600 text-white px-3 py-2 text-sm font-semibold hover:bg-indigo-700 transition-colors">
                Gunakan Template
            </a>
            <form method="POST" action="{{ route('rab-plans.destroy', $t) }}"
                  onsubmit="return confirm('Hapus template ini?')">
                @csrf @method('DELETE')
                <button type="submit" class="rounded-xl border border-rose-200 bg-rose-50 text-rose-600 px-3 py-2 text-sm hover:bg-rose-100 transition-colors">
                    🗑
                </button>
            </form>
        </div>
    </div>
    @endforeach
</div>
<div class="mt-4">{{ $templates->links() }}</div>
@endif
@endsection
