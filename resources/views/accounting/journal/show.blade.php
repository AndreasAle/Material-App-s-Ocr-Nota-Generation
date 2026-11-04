@extends('layouts.app')

@section('title', 'Jurnal ' . $journal->entry_no)

@section('content')

<x-page-header title="{{ $journal->entry_no }}" subtitle="{{ $journal->description }}" :back="route('journal.index')">
    <x-slot name="actions">
        @if($journal->isDraft())
        <form method="POST" action="{{ route('journal.post', $journal) }}" class="inline">
            @csrf
            <button type="submit"
                    onclick="return confirm('Posting jurnal {{ $journal->entry_no }}? Setelah diposting tidak bisa diedit.')"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 transition-colors">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Posting Jurnal
            </button>
        </form>
        @endif
        @if($journal->isPosted())
        <form method="POST" action="{{ route('journal.void', $journal) }}" class="inline">
            @csrf
            <button type="submit"
                    onclick="return confirm('Batalkan jurnal {{ $journal->entry_no }}?')"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-red-300 bg-white px-4 py-2 text-sm font-semibold text-red-600 hover:bg-red-50 transition-colors">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Batalkan (Void)
            </button>
        </form>
        @endif
    </x-slot>
</x-page-header>


<div class="max-w-4xl space-y-5">

    {{-- Header Info --}}
    <x-data-card title="Informasi Jurnal">
        <div class="p-6">
            <div class="grid grid-cols-2 gap-x-8 gap-y-4 sm:grid-cols-3 text-sm">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">No. Jurnal</p>
                    <p class="font-mono font-bold text-slate-900">{{ $journal->entry_no }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Tanggal</p>
                    <p class="font-semibold text-slate-800">{{ $journal->entry_date->translatedFormat('d F Y') }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Status</p>
                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold
                        {{ $journal->status === 'posted' ? 'bg-emerald-100 text-emerald-800' : ($journal->status === 'void' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                        {{ $journal->getStatusLabelAttribute() }}
                    </span>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Sumber Transaksi</p>
                    <p class="text-slate-700">{{ $journal->reference_type ?? 'Jurnal Manual' }}</p>
                    @if($journal->reference_id)
                        <p class="text-xs text-slate-400">ID: {{ $journal->reference_id }}</p>
                    @endif
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Mata Uang</p>
                    <p class="font-semibold text-slate-700">{{ $journal->currency }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Dibuat Oleh</p>
                    <p class="text-slate-700">{{ $journal->creator?->name ?? '—' }}</p>
                    <p class="text-xs text-slate-400">{{ $journal->created_at->format('d/m/Y H:i') }}</p>
                </div>
                @if($journal->poster)
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Diposting Oleh</p>
                    <p class="text-slate-700">{{ $journal->poster->name }}</p>
                    <p class="text-xs text-slate-400">{{ $journal->posted_at?->format('d/m/Y H:i') }}</p>
                </div>
                @endif
                <div class="sm:col-span-3">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Keterangan / Memo</p>
                    <p class="text-slate-800">{{ $journal->description }}</p>
                </div>
            </div>
        </div>
    </x-data-card>

    {{-- Lines --}}
    <x-data-card title="Baris Jurnal (Debit-Kredit)">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/60">
                        <th class="px-4 py-3 text-left font-semibold text-slate-600 text-xs uppercase tracking-wide w-8">#</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600 text-xs uppercase tracking-wide">Kode</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600 text-xs uppercase tracking-wide">Nama Akun</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600 text-xs uppercase tracking-wide">Proyek</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600 text-xs uppercase tracking-wide">Keterangan</th>
                        <th class="px-4 py-3 text-right font-semibold text-blue-600 text-xs uppercase tracking-wide">DEBIT</th>
                        <th class="px-4 py-3 text-right font-semibold text-orange-600 text-xs uppercase tracking-wide">KREDIT</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($journal->lines->sortBy('line_order') as $i => $line)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-2.5 text-xs text-slate-400 font-mono">{{ $line->line_order }}</td>
                        <td class="px-4 py-2.5 font-mono text-sm font-semibold text-slate-700">{{ $line->account_code }}</td>
                        <td class="px-4 py-2.5 font-medium text-slate-800">{{ $line->account_name }}</td>
                        <td class="px-4 py-2.5 text-xs text-slate-500">{{ $line->project?->name ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-xs text-slate-500">{{ $line->description ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-right">
                            @if($line->debit > 0)
                                <span class="font-bold text-blue-700">Rp {{ number_format($line->debit, 0, ',', '.') }}</span>
                            @else
                                <span class="text-slate-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-2.5 text-right">
                            @if($line->credit > 0)
                                <span class="font-bold text-orange-700">Rp {{ number_format($line->credit, 0, ',', '.') }}</span>
                            @else
                                <span class="text-slate-300">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-slate-300 bg-slate-50 font-bold">
                        <td colspan="5" class="px-4 py-3 text-right text-slate-700 font-extrabold text-sm uppercase tracking-wide">TOTAL</td>
                        <td class="px-4 py-3 text-right text-blue-800 text-base">Rp {{ number_format($journal->total_debit, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-orange-800 text-base">Rp {{ number_format($journal->total_credit, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td colspan="7" class="px-4 py-2 text-center text-xs font-semibold {{ $journal->isBalanced() ? 'text-emerald-600' : 'text-red-600' }}">
                            @if($journal->isBalanced())
                                ✓ Jurnal Seimbang — Total Debit = Total Kredit = Rp {{ number_format($journal->total_debit, 0, ',', '.') }}
                            @else
                                ⚠ PERHATIAN: Jurnal Tidak Seimbang! Selisih = Rp {{ number_format(abs($journal->total_debit - $journal->total_credit), 0, ',', '.') }}
                            @endif
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </x-data-card>

    {{-- Status History --}}
    @if($journal->status === 'void')
    <div class="rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
        <strong>Jurnal ini telah dibatalkan (VOID).</strong> Data jurnal tidak lagi mempengaruhi neraca saldo.
    </div>
    @endif

</div>

@endsection
