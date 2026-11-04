@extends('layouts.app')

@section('title', 'Jurnal Umum')

@section('content')

<x-page-header title="Jurnal Umum" subtitle="Daftar semua entri jurnal akuntansi double-entry">
    <x-slot name="actions">
        <a href="{{ route('journal.create') }}"
           class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 transition-colors">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Buat Jurnal
        </a>
    </x-slot>
</x-page-header>


{{-- Stats --}}
<div class="grid grid-cols-3 gap-4 mb-6">
    <x-data-card>
        <div class="p-5 flex items-center gap-4">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100">
                <svg class="h-5 w-5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.75V16.5L12 14.25 7.5 16.5V3.75m9 0H18A2.25 2.25 0 0120.25 6v12A2.25 2.25 0 0118 20.25H6A2.25 2.25 0 013.75 18V6A2.25 2.25 0 016 3.75h1.5m9 0h-9"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-slate-500">Total Jurnal</p>
                <p class="text-2xl font-extrabold text-slate-800">{{ $stats['total'] }}</p>
            </div>
        </div>
    </x-data-card>
    <x-data-card>
        <div class="p-5 flex items-center gap-4">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100">
                <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-slate-500">Sudah Diposting</p>
                <p class="text-2xl font-extrabold text-emerald-700">{{ $stats['posted'] }}</p>
            </div>
        </div>
    </x-data-card>
    <x-data-card>
        <div class="p-5 flex items-center gap-4">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100">
                <svg class="h-5 w-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-slate-500">Draft</p>
                <p class="text-2xl font-extrabold text-amber-600">{{ $stats['draft'] }}</p>
            </div>
        </div>
    </x-data-card>
</div>

{{-- Info --}}
<div class="mb-5 rounded-xl bg-blue-50 border border-blue-200 px-4 py-3 text-sm text-blue-700">
    <strong>Alur Jurnal:</strong> Buat jurnal (Draft) → Periksa keseimbangan Debit = Kredit → Posting untuk mengunci → Void jika perlu dibatalkan.
</div>

{{-- Filters --}}
<form method="GET" class="flex items-center gap-3 flex-wrap mb-5">
    <select name="status" class="rounded-lg border border-slate-200 text-sm px-3 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
        <option value="">Semua Status</option>
        <option value="draft"  @selected(request('status') === 'draft')>Draft</option>
        <option value="posted" @selected(request('status') === 'posted')>Diposting</option>
        <option value="void"   @selected(request('status') === 'void')>Dibatalkan</option>
    </select>
    <input type="date" name="date_from" value="{{ request('date_from') }}"
           class="rounded-lg border border-slate-200 text-sm px-3 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
    <input type="date" name="date_to" value="{{ request('date_to') }}"
           class="rounded-lg border border-slate-200 text-sm px-3 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
    <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700">Filter</button>
    @if(request()->hasAny(['status','date_from','date_to']))
        <a href="{{ route('journal.index') }}" class="px-3 py-2 rounded-lg border border-slate-300 text-sm text-slate-600 hover:bg-slate-50">Reset</a>
    @endif
</form>

<x-data-card>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50/60">
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 text-xs uppercase tracking-wide">No. Jurnal</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 text-xs uppercase tracking-wide">Tanggal</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 text-xs uppercase tracking-wide">Keterangan</th>
                    <th class="px-4 py-3 text-center font-semibold text-slate-600 text-xs uppercase tracking-wide">Sumber</th>
                    <th class="px-4 py-3 text-right font-semibold text-slate-600 text-xs uppercase tracking-wide">Debit</th>
                    <th class="px-4 py-3 text-right font-semibold text-slate-600 text-xs uppercase tracking-wide">Kredit</th>
                    <th class="px-4 py-3 text-center font-semibold text-slate-600 text-xs uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3 text-center font-semibold text-slate-600 text-xs uppercase tracking-wide">Dibuat</th>
                    <th class="px-4 py-3 text-center font-semibold text-slate-600 text-xs uppercase tracking-wide">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($entries as $je)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3">
                        <a href="{{ route('journal.show', $je) }}" class="font-mono font-bold text-indigo-600 hover:underline">{{ $je->entry_no }}</a>
                    </td>
                    <td class="px-4 py-3 text-slate-500 text-xs whitespace-nowrap">{{ $je->entry_date->format('d M Y') }}</td>
                    <td class="px-4 py-3 text-slate-700 max-w-xs">
                        <p class="truncate">{{ $je->description }}</p>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($je->reference_type)
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs bg-slate-100 text-slate-600">{{ $je->reference_type }}</span>
                        @else
                        <span class="text-xs text-slate-400">Manual</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right font-semibold text-blue-700 whitespace-nowrap">Rp {{ number_format($je->total_debit, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-orange-700 whitespace-nowrap">Rp {{ number_format($je->total_credit, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold
                            {{ $je->status === 'posted' ? 'bg-emerald-100 text-emerald-700' : ($je->status === 'void' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                            {{ $je->getStatusLabelAttribute() }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center text-xs text-slate-400 whitespace-nowrap">{{ $je->creator?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-center">
                        <a href="{{ route('journal.show', $je) }}" class="text-xs font-semibold text-indigo-600 hover:underline">Detail</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-4 py-12 text-center text-slate-400">
                        @if(request()->hasAny(['status','date_from','date_to']))
                            Tidak ada jurnal yang sesuai filter.
                            <a href="{{ route('journal.index') }}" class="ml-2 text-indigo-500 hover:underline">Reset filter</a>
                        @else
                            Belum ada entri jurnal.
                            <a href="{{ route('journal.create') }}" class="ml-2 text-indigo-500 hover:underline font-medium">Buat Jurnal Pertama →</a>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-4 py-3 border-t border-slate-100">
        {{ $entries->links() }}
    </div>
</x-data-card>

@endsection
