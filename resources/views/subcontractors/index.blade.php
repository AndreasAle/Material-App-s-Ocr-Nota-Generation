@extends('layouts.app')
@section('title', 'Subkontraktor')

@section('content')
<x-page-header title="Subkontraktor" subtitle="Manajemen data subkontraktor, SPK, dan pembayaran.">
    <x-slot name="actions">
        <a href="{{ route('subcontractors.spk.index') }}" class="btn-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15"/>
            </svg>
            Semua SPK
        </a>
        <a href="{{ route('subcontractors.create') }}" class="btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Tambah Subkontraktor
        </a>
    </x-slot>
</x-page-header>

{{-- Filter --}}
<x-data-card class="mb-5">
    <div class="px-5 py-4">
        <form method="GET" action="{{ route('subcontractors.index') }}" class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[220px]">
                <label class="form-label">Cari Subkontraktor</label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z"/>
                        </svg>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama, kode, atau bidang..."
                           class="form-input pl-10">
                </div>
            </div>
            <div class="w-36">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">Semua</option>
                    <option value="1" @selected(request('status') === '1')>Aktif</option>
                    <option value="0" @selected(request('status') === '0')>Nonaktif</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary">Cari</button>
                @if(request('search') || request('status'))
                    <a href="{{ route('subcontractors.index') }}" class="btn-secondary">Reset</a>
                @endif
            </div>
        </form>
    </div>
</x-data-card>

{{-- Table --}}
<x-data-card title="Daftar Subkontraktor" subtitle="{{ $subcontractors->total() }} subkontraktor terdaftar">
    <div class="overflow-x-auto">
        <table class="tbl">
            <thead>
                <tr>
                    <th>Subkontraktor</th>
                    <th>Kontak</th>
                    <th>Bidang</th>
                    <th class="th-right">Jumlah SPK</th>
                    <th class="th-right">Total Kontrak</th>
                    <th class="th-center">Status</th>
                    <th class="th-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subcontractors as $sub)
                <tr>
                    <td>
                        <a href="{{ route('subcontractors.show', $sub) }}" class="font-semibold text-slate-900 hover:text-indigo-600 transition-colors">
                            {{ $sub->name }}
                        </a>
                        @if($sub->code)
                            <p class="mt-0.5 font-mono text-xs text-slate-400">{{ $sub->code }}</p>
                        @endif
                    </td>
                    <td>
                        <div class="text-slate-700 text-sm">{{ $sub->contact_person ?? '—' }}</div>
                        @if($sub->phone)
                            <div class="text-xs text-slate-400">{{ $sub->phone }}</div>
                        @endif
                    </td>
                    <td class="text-slate-600 text-sm">{{ $sub->specialization ?? '—' }}</td>
                    <td class="text-right font-semibold text-slate-800">{{ $sub->subcontracts_count }}</td>
                    <td class="text-right font-semibold text-slate-900">
                        Rp {{ number_format($sub->subcontracts_sum_contract_value ?? 0, 0, ',', '.') }}
                    </td>
                    <td class="text-center">
                        @if($sub->is_active)
                            <span class="badge-success">Aktif</span>
                        @else
                            <span class="badge-neutral">Nonaktif</span>
                        @endif
                    </td>
                    <td>
                        <div class="flex items-center justify-end gap-1.5">
                            <a href="{{ route('subcontractors.show', $sub) }}"
                               class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition-colors" title="Detail">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.641 0-8.573-3.007-9.964-7.178z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </a>
                            <a href="{{ route('subcontractors.edit', $sub) }}"
                               class="p-1.5 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition-colors" title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/>
                                </svg>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="flex flex-col items-center justify-center py-14 text-center">
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
                                </svg>
                            </div>
                            <p class="mt-3 font-semibold text-slate-700">Belum ada subkontraktor</p>
                            <p class="mt-1 text-sm text-slate-400">Tambah subkontraktor untuk mulai mengelola SPK.</p>
                            <a href="{{ route('subcontractors.create') }}" class="btn-primary mt-4">Tambah Subkontraktor</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($subcontractors->hasPages())
    <div class="border-t border-slate-100 px-5 py-3">
        {{ $subcontractors->links() }}
    </div>
    @endif
</x-data-card>
@endsection
