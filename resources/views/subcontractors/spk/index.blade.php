@extends('layouts.app')
@section('content')

<x-page-header title="Daftar SPK" subtitle="Surat Perintah Kerja semua subkontraktor">
    <x-slot name="actions">
        <a href="{{ route('subcontractors.index') }}" class="btn-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
            </svg>
            Subkontraktor
        </a>
        <a href="{{ route('subcontractors.spk.create') }}" class="btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Buat SPK
        </a>
    </x-slot>
</x-page-header>

{{-- Filter --}}
<x-data-card class="mb-5">
    <form method="GET" class="px-5 py-4 flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-[180px]">
            <label class="form-label text-xs">Cari</label>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="No. SPK, pekerjaan, subkon..."
                   class="form-input">
        </div>
        <div class="min-w-[160px]">
            <label class="form-label text-xs">Proyek</label>
            <select name="project_id" class="form-select">
                <option value="">Semua Proyek</option>
                @foreach($projects as $p)
                    <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>
                        {{ $p->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label text-xs">Status</label>
            <select name="status" class="form-select">
                <option value="">Semua</option>
                <option value="draft"     {{ request('status') == 'draft'     ? 'selected' : '' }}>Draft</option>
                <option value="active"    {{ request('status') == 'active'    ? 'selected' : '' }}>Aktif</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
            </select>
        </div>
        <button type="submit" class="btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z"/>
            </svg>
            Filter
        </button>
        @if(request()->hasAny(['search','project_id','status']))
            <a href="{{ route('subcontractors.spk.index') }}" class="btn-secondary">Reset</a>
        @endif
    </form>
</x-data-card>

<x-data-card>
    <div class="overflow-x-auto">
        <table class="tbl">
            <thead>
                <tr>
                    <th>No. SPK</th>
                    <th>Subkontraktor</th>
                    <th>Pekerjaan</th>
                    <th>Proyek</th>
                    <th class="th-right">Nilai Kontrak</th>
                    <th class="th-center">Progress</th>
                    <th class="th-center">Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($subcontracts as $spk)
                    <tr>
                        <td class="font-semibold text-indigo-700">{{ $spk->spk_no }}</td>
                        <td class="text-slate-800">{{ $spk->subcontractor->name ?? '—' }}</td>
                        <td class="text-slate-700">{{ $spk->work_name }}</td>
                        <td class="text-slate-600 text-xs">{{ $spk->project->name ?? '—' }}</td>
                        <td class="text-right font-semibold text-slate-800">
                            Rp {{ number_format($spk->contract_value, 0, ',', '.') }}
                        </td>
                        <td>
                            <div class="flex items-center gap-2 min-w-[90px] justify-center">
                                <div class="progress-bar flex-1">
                                    <div class="progress-bar-fill {{ $spk->progress_pct >= 100 ? 'bg-emerald-500' : 'bg-indigo-500' }}"
                                         style="width: {{ $spk->progress_pct }}%"></div>
                                </div>
                                <span class="text-xs font-semibold text-slate-600">{{ $spk->progress_pct }}%</span>
                            </div>
                        </td>
                        <td class="text-center">
                            @php
                                $cls = match($spk->status) {
                                    'active'    => 'badge-success',
                                    'completed' => 'badge-neutral',
                                    'cancelled' => 'badge-danger',
                                    default     => 'badge-warning'
                                };
                            @endphp
                            <span class="{{ $cls }}">{{ $spk->status_label }}</span>
                        </td>
                        <td class="text-right">
                            <a href="{{ route('subcontractors.spk.show', $spk) }}"
                               class="inline-flex items-center gap-1 text-xs font-medium text-indigo-600 hover:text-indigo-800 transition-colors">
                                Detail
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
                                </svg>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-5 py-14 text-center">
                            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400 mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                                </svg>
                            </div>
                            <p class="font-semibold text-slate-600">Belum ada SPK</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-4 border-t border-slate-100">{{ $subcontracts->links() }}</div>
</x-data-card>

@endsection
