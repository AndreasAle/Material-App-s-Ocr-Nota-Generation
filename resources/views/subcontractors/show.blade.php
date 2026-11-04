@extends('layouts.app')
@section('content')

<x-page-header title="{{ $subcontractor->name }}" subtitle="{{ $subcontractor->specialization ?? 'Subkontraktor' }}">
    <x-slot name="actions">
        <a href="{{ route('subcontractors.edit', $subcontractor) }}" class="btn-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/>
            </svg>
            Edit
        </a>
        <a href="{{ route('subcontractors.spk.create', ['subcontractor_id' => $subcontractor->id]) }}" class="btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Buat SPK
        </a>
    </x-slot>
</x-page-header>

{{-- Summary Cards --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <div class="rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm">
        <p class="text-xs text-slate-500">Total Kontrak</p>
        <p class="mt-1.5 text-lg font-bold text-slate-900">Rp {{ number_format($totalContract, 0, ',', '.') }}</p>
    </div>
    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 shadow-sm">
        <p class="text-xs text-emerald-600">Total Dibayar</p>
        <p class="mt-1.5 text-lg font-bold text-emerald-700">Rp {{ number_format($totalPaid, 0, ',', '.') }}</p>
    </div>
    <div class="rounded-2xl border {{ $totalSisa > 0 ? 'border-amber-200 bg-amber-50' : 'border-slate-200 bg-white' }} px-5 py-4 shadow-sm">
        <p class="text-xs {{ $totalSisa > 0 ? 'text-amber-600' : 'text-slate-500' }}">Sisa Tagihan</p>
        <p class="mt-1.5 text-lg font-bold {{ $totalSisa > 0 ? 'text-amber-700' : 'text-slate-400' }}">
            Rp {{ number_format($totalSisa, 0, ',', '.') }}
        </p>
    </div>
    <div class="rounded-2xl border border-indigo-200 bg-indigo-50 px-5 py-4 shadow-sm">
        <p class="text-xs text-indigo-600">Jumlah SPK</p>
        <p class="mt-1.5 text-lg font-bold text-indigo-700">{{ $subcontractor->subcontracts->count() }}</p>
    </div>
</div>

{{-- Info Card --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    <x-data-card>
        <h4 class="font-semibold text-slate-800 px-5 pt-4 pb-3 border-b border-slate-100">Informasi Kontak</h4>
        <dl class="divide-y divide-slate-50 text-sm">
            @foreach([['PIC / Kontak', $subcontractor->contact_person],['Telepon', $subcontractor->phone],['Email', $subcontractor->email],['Alamat', $subcontractor->address]] as [$label, $val])
            <div class="px-5 py-3 flex gap-4">
                <dt class="w-28 text-slate-400 shrink-0">{{ $label }}</dt>
                <dd class="text-slate-700">{{ $val ?? '—' }}</dd>
            </div>
            @endforeach
        </dl>
    </x-data-card>

    @if($subcontractor->notes)
    <x-data-card>
        <h4 class="font-semibold text-slate-800 px-5 pt-4 pb-3 border-b border-slate-100">Catatan</h4>
        <p class="px-5 py-4 text-sm text-slate-600">{{ $subcontractor->notes }}</p>
    </x-data-card>
    @endif
</div>

{{-- Daftar SPK --}}
<x-data-card title="Daftar SPK" subtitle="{{ $subcontractor->subcontracts->count() }} SPK">
    <x-slot name="actions">
        <a href="{{ route('subcontractors.spk.create', ['subcontractor_id' => $subcontractor->id]) }}"
           class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">+ Buat SPK Baru</a>
    </x-slot>
    <div class="overflow-x-auto">
        <table class="tbl">
            <thead>
                <tr>
                    <th>No. SPK</th>
                    <th>Pekerjaan</th>
                    <th>Proyek</th>
                    <th class="th-right">Nilai Kontrak</th>
                    <th class="th-right">Terbayar</th>
                    <th class="th-center min-w-[120px]">Progress</th>
                    <th class="th-center">Status</th>
                    <th class="th-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subcontractor->subcontracts as $spk)
                <tr>
                    <td class="font-mono font-semibold text-indigo-600">{{ $spk->spk_no }}</td>
                    <td class="font-medium text-slate-800">{{ $spk->work_name }}</td>
                    <td class="text-slate-600">{{ $spk->project->name ?? '—' }}</td>
                    <td class="text-right font-semibold text-slate-800">
                        Rp {{ number_format($spk->contract_value, 0, ',', '.') }}
                    </td>
                    <td class="text-right font-semibold text-emerald-700">
                        Rp {{ number_format($spk->paid_amount, 0, ',', '.') }}
                    </td>
                    <td>
                        <div class="flex items-center gap-2">
                            <div class="flex-1 h-2 rounded-full bg-slate-100 overflow-hidden">
                                <div class="h-full rounded-full {{ $spk->progress_pct >= 100 ? 'bg-emerald-500' : ($spk->progress_pct >= 50 ? 'bg-amber-400' : 'bg-indigo-500') }}"
                                     style="width: {{ $spk->progress_pct }}%"></div>
                            </div>
                            <span class="text-xs font-semibold text-slate-600 w-8 shrink-0">{{ $spk->progress_pct }}%</span>
                        </div>
                    </td>
                    <td class="text-center">
                        @php
                            $cls = match($spk->status) {
                                'active'    => 'badge-success',
                                'completed' => 'badge-neutral',
                                'cancelled' => 'badge-danger',
                                default     => 'badge-warning',
                            };
                        @endphp
                        <span class="{{ $cls }}">{{ $spk->status_label }}</span>
                    </td>
                    <td>
                        <div class="flex justify-end">
                            <a href="{{ route('subcontractors.spk.show', $spk) }}"
                               class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition-colors" title="Detail">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.641 0-8.573-3.007-9.964-7.178z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <div class="flex flex-col items-center justify-center py-12 text-center">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15"/>
                                </svg>
                            </div>
                            <p class="mt-2 text-sm font-medium text-slate-700">Belum ada SPK</p>
                            <p class="mt-1 text-xs text-slate-400">Buat SPK baru untuk subkontraktor ini.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-data-card>
@endsection
