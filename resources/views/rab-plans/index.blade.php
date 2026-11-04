@extends('layouts.app')
@section('title', 'RAB & Anggaran')

@section('content')
<x-page-header title="RAB & Anggaran" subtitle="Kelola rencana anggaran biaya per proyek dan pantau realisasinya.">
    <x-slot name="actions">
        <a href="{{ route('rab-plans.templates') }}" class="btn-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75"/>
            </svg>
            Template
        </a>
        @if(auth()->user()->isAdmin() || auth()->user()->role === 'staff')
        <a href="{{ route('rab-plans.create') }}" class="btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Buat RAB Baru
        </a>
        @endif
    </x-slot>
</x-page-header>

{{-- Stats --}}
<div class="mb-5 grid grid-cols-2 gap-4 sm:grid-cols-4">
    @php
        $totalBudget = $plans->sum('total_budget');
        $activeCount = $plans->where('status', 'active')->count();
        $draftCount = $plans->where('status', 'draft')->count();
        $closedCount = $plans->where('status', 'closed')->count();
    @endphp
    <x-data-card class="px-5 py-4">
        <p class="text-xs text-slate-500">Total Anggaran</p>
        <p class="mt-1 text-lg font-bold text-slate-900">Rp {{ number_format($totalBudget, 0, ',', '.') }}</p>
        <p class="mt-0.5 text-xs text-slate-400">{{ $plans->count() }} RAB</p>
    </x-data-card>
    <x-data-card class="px-5 py-4">
        <p class="text-xs text-slate-500">RAB Aktif</p>
        <p class="mt-1 text-2xl font-bold text-emerald-600">{{ $activeCount }}</p>
        <p class="mt-0.5 text-xs text-slate-400">sedang berjalan</p>
    </x-data-card>
    <x-data-card class="px-5 py-4">
        <p class="text-xs text-slate-500">Draft</p>
        <p class="mt-1 text-2xl font-bold text-amber-600">{{ $draftCount }}</p>
        <p class="mt-0.5 text-xs text-slate-400">belum aktif</p>
    </x-data-card>
    <x-data-card class="px-5 py-4">
        <p class="text-xs text-slate-500">Ditutup</p>
        <p class="mt-1 text-2xl font-bold text-slate-500">{{ $closedCount }}</p>
        <p class="mt-0.5 text-xs text-slate-400">selesai</p>
    </x-data-card>
</div>

{{-- Filter --}}
<x-data-card class="mb-5">
    <div class="px-5 py-4">
        <form method="GET" action="{{ route('rab-plans.index') }}" class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[180px]">
                <label class="form-label">Proyek</label>
                <select name="project_id" class="form-select">
                    <option value="">Semua Proyek</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" @selected(request('project_id') == $p->id)>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-40">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                    <option value="active" @selected(request('status') === 'active')>Aktif</option>
                    <option value="closed" @selected(request('status') === 'closed')>Ditutup</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary">Filter</button>
                @if(request()->hasAny(['project_id','status']))
                    <a href="{{ route('rab-plans.index') }}" class="btn-secondary">Reset</a>
                @endif
            </div>
        </form>
    </div>
</x-data-card>

{{-- Table --}}
<x-data-card title="Daftar RAB" subtitle="{{ $plans->count() }} RAB ditemukan">
    <div class="overflow-x-auto">
        <table class="tbl">
            <thead>
                <tr>
                    <th>Nama RAB</th>
                    <th>Proyek</th>
                    <th>Periode</th>
                    <th>Item</th>
                    <th class="th-right">Total Anggaran</th>
                    <th class="th-center">Status</th>
                    <th class="th-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($plans as $plan)
                <tr>
                    <td>
                        <a href="{{ route('rab-plans.show', $plan) }}" class="font-semibold text-slate-900 hover:text-indigo-600 transition-colors">
                            {{ $plan->name }}
                        </a>
                        @if($plan->description)
                            <p class="mt-0.5 text-xs text-slate-400 truncate max-w-[180px]">{{ $plan->description }}</p>
                        @endif
                    </td>
                    <td class="text-slate-700">
                        {{ $plan->project->name ?? '—' }}
                    </td>
                    <td class="text-slate-500 text-xs">
                        <div>{{ $plan->period_start->format('d M Y') }}</div>
                        @if($plan->period_end)
                            <div class="text-slate-400">s/d {{ $plan->period_end->format('d M Y') }}</div>
                        @endif
                    </td>
                    <td class="text-slate-600 text-sm">
                        {{ $plan->items_count ?? '—' }} item
                    </td>
                    <td class="text-right font-semibold text-slate-900">
                        Rp {{ number_format($plan->total_budget, 0, ',', '.') }}
                    </td>
                    <td class="text-center">
                        @if($plan->status === 'active')
                            <span class="badge-success">Aktif</span>
                        @elseif($plan->status === 'draft')
                            <span class="badge-warning">Draft</span>
                        @else
                            <span class="badge-neutral">Ditutup</span>
                        @endif
                    </td>
                    <td>
                        <div class="flex items-center justify-end gap-1.5">
                            <a href="{{ route('rab-plans.show', $plan) }}"
                               class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition-colors" title="Detail">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.641 0-8.573-3.007-9.964-7.178z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </a>
                            @if(! $plan->isClosed())
                            <a href="{{ route('rab-plans.edit', $plan) }}"
                               class="p-1.5 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition-colors" title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/>
                                </svg>
                            </a>
                            @endif
                            @if(auth()->user()->isAdmin())
                            <form method="POST" action="{{ route('rab-plans.destroy', $plan) }}"
                                  onsubmit="return confirm('Hapus RAB {{ addslashes($plan->name) }}?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors" title="Hapus">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                                    </svg>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="flex flex-col items-center justify-center py-14 text-center">
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15"/>
                                </svg>
                            </div>
                            <p class="mt-3 font-semibold text-slate-700">Belum ada RAB</p>
                            <p class="mt-1 text-sm text-slate-400">Buat RAB pertama untuk mulai menganggarkan proyek.</p>
                            <div class="mt-4 flex gap-3">
                                <a href="{{ route('rab-plans.templates') }}" class="btn-secondary">Dari Template</a>
                                <a href="{{ route('rab-plans.create') }}" class="btn-primary">Buat RAB Baru</a>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($plans->hasPages())
    <div class="border-t border-slate-100 px-5 py-3">
        {{ $plans->links() }}
    </div>
    @endif
</x-data-card>

@endsection
