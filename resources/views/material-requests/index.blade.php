@extends('layouts.app')
@section('title', 'Permintaan Material')

@section('content')
<x-page-header title="Permintaan Material" subtitle="Kelola permintaan material, urgensi, dan proses persetujuan.">
    <x-slot name="actions">
        <a href="{{ route('material-requests.create') }}" class="btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Buat MR Baru
        </a>
    </x-slot>
</x-page-header>

{{-- Stats --}}
<div class="mb-5 grid grid-cols-2 gap-4 sm:grid-cols-4">
    <div class="rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm text-center">
        <p class="text-2xl font-bold text-slate-800">{{ $stats['total'] }}</p>
        <p class="text-xs text-slate-500 mt-0.5">Total MR</p>
    </div>
    <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 shadow-sm text-center">
        <p class="text-2xl font-bold text-amber-700">{{ $stats['submitted'] }}</p>
        <p class="text-xs text-amber-600 mt-0.5">Menunggu</p>
    </div>
    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 shadow-sm text-center">
        <p class="text-2xl font-bold text-emerald-700">{{ $stats['approved'] }}</p>
        <p class="text-xs text-emerald-600 mt-0.5">Disetujui</p>
    </div>
    <div class="rounded-2xl border border-indigo-200 bg-indigo-50 px-5 py-4 shadow-sm text-center">
        <p class="text-2xl font-bold text-indigo-700">{{ $stats['converted'] }}</p>
        <p class="text-xs text-indigo-600 mt-0.5">Jadi PO</p>
    </div>
</div>

{{-- Filter --}}
<x-data-card class="mb-5">
    <div class="px-5 py-4">
        <form method="GET" action="{{ route('material-requests.index') }}" class="flex flex-wrap items-end gap-3">
            <div class="w-44">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="draft"     @selected(request('status') === 'draft')>Draft</option>
                    <option value="submitted" @selected(request('status') === 'submitted')>Menunggu</option>
                    <option value="approved"  @selected(request('status') === 'approved')>Disetujui</option>
                    <option value="rejected"  @selected(request('status') === 'rejected')>Ditolak</option>
                    <option value="converted" @selected(request('status') === 'converted')>Dikonversi</option>
                </select>
            </div>
            <div class="w-40">
                <label class="form-label">Urgensi</label>
                <select name="urgency" class="form-select">
                    <option value="">Semua Urgensi</option>
                    <option value="low"      @selected(request('urgency') === 'low')>Rendah</option>
                    <option value="normal"   @selected(request('urgency') === 'normal')>Normal</option>
                    <option value="high"     @selected(request('urgency') === 'high')>Tinggi</option>
                    <option value="critical" @selected(request('urgency') === 'critical')>Kritis</option>
                </select>
            </div>
            <div class="flex-1 min-w-[160px]">
                <label class="form-label">Proyek</label>
                <select name="project_id" class="form-select">
                    <option value="">Semua Proyek</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" @selected(request('project_id') == $p->id)>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary">Filter</button>
                <a href="{{ route('material-requests.index') }}" class="btn-secondary">Reset</a>
            </div>
        </form>
    </div>
</x-data-card>

{{-- Table --}}
<x-data-card title="Daftar Permintaan Material" subtitle="{{ $materialRequests->total() }} MR ditemukan">
    @if($materialRequests->isEmpty())
        <div class="flex flex-col items-center justify-center py-14 text-center">
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15"/>
                </svg>
            </div>
            <p class="mt-3 font-semibold text-slate-700">Belum ada permintaan material</p>
            <p class="mt-1 text-sm text-slate-400">Buat MR baru untuk memulai proses permintaan.</p>
            <a href="{{ route('material-requests.create') }}" class="btn-primary mt-4">Buat MR Baru</a>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Nomor MR</th>
                        <th>Proyek</th>
                        <th class="th-center">Urgensi</th>
                        <th class="th-center">Item</th>
                        <th class="th-center">Status</th>
                        <th>Pemohon</th>
                        <th>Dibutuhkan</th>
                        <th class="th-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($materialRequests as $mr)
                    @php
                        $statusBadge = [
                            'draft'     => 'badge-neutral',
                            'submitted' => 'badge-warning',
                            'approved'  => 'badge-success',
                            'rejected'  => 'badge-danger',
                            'converted' => 'badge-purple',
                        ];
                        $statusLabel = [
                            'draft'     => 'Draft',
                            'submitted' => 'Menunggu',
                            'approved'  => 'Disetujui',
                            'rejected'  => 'Ditolak',
                            'converted' => 'Jadi PO',
                        ];
                        $urgencyBadge = [
                            'low'      => 'badge-neutral',
                            'normal'   => 'badge-info',
                            'high'     => 'badge-warning',
                            'critical' => 'badge-danger',
                        ];
                    @endphp
                    <tr>
                        <td>
                            <a href="{{ route('material-requests.show', $mr) }}"
                               class="font-mono font-semibold text-indigo-600 hover:underline">
                                {{ $mr->request_no }}
                            </a>
                            @if($mr->is_auto_reorder)
                                <span class="badge-warning ml-1 text-[10px]">Auto</span>
                            @endif
                        </td>
                        <td class="text-slate-700">{{ $mr->project?->name ?? '—' }}</td>
                        <td class="text-center">
                            <span class="{{ $urgencyBadge[$mr->urgency] ?? 'badge-neutral' }}">
                                {{ $mr->urgency_label }}
                            </span>
                        </td>
                        <td class="text-center font-medium text-slate-700">
                            {{ $mr->items_count ?? $mr->items->count() }}
                        </td>
                        <td class="text-center">
                            <span class="{{ $statusBadge[$mr->status] ?? 'badge-neutral' }}">
                                {{ $statusLabel[$mr->status] ?? $mr->status }}
                            </span>
                        </td>
                        <td class="text-slate-600 text-sm">{{ $mr->requester?->name ?? 'Sistem' }}</td>
                        <td class="text-slate-500 text-xs">
                            {{ $mr->needed_by?->format('d M Y') ?? '—' }}
                        </td>
                        <td>
                            <div class="flex items-center justify-end">
                                <a href="{{ route('material-requests.show', $mr) }}"
                                   class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition-colors" title="Detail">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.641 0-8.573-3.007-9.964-7.178z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-5 py-3">
            {{ $materialRequests->links() }}
        </div>
    @endif
</x-data-card>
@endsection
