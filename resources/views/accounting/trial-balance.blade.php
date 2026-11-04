@extends('layouts.app')

@section('title', 'Neraca Saldo')

@section('content')

<x-page-header title="Neraca Saldo" subtitle="Trial Balance — ringkasan debit & kredit semua akun per periode">
    <x-slot name="actions">
        <a href="{{ route('trial-balance.export-excel', request()->query()) }}"
           class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 transition-colors">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
            </svg>
            Export Excel
        </a>
    </x-slot>
</x-page-header>

{{-- Filter --}}
<form method="GET" class="flex items-center gap-3 flex-wrap mb-5">
    <select name="month" class="rounded-lg border border-slate-200 text-sm px-3 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
        @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $i => $nm)
            <option value="{{ $i+1 }}" @selected($month == $i+1)>{{ $nm }}</option>
        @endforeach
    </select>
    <select name="year" class="rounded-lg border border-slate-200 text-sm px-3 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
        @for($y = now()->year; $y >= now()->year - 3; $y--)
            <option value="{{ $y }}" @selected($year == $y)>{{ $y }}</option>
        @endfor
    </select>
    <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition-colors">Tampilkan</button>
</form>

{{-- Balance Alert --}}
@if(!$isBalanced)
<div class="mb-5 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700 font-semibold">
    <div class="flex items-center gap-2">
        <svg class="h-5 w-5 shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
        </svg>
        <span>⚠ Neraca saldo tidak seimbang! Selisih = Rp {{ number_format(abs($totalDebit - $totalCredit), 0, ',', '.') }}. Periksa jurnal yang belum valid atau ada yang belum diposting.</span>
    </div>
</div>
@else
<div class="mb-5 rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-700 font-semibold">
    ✓ Neraca saldo seimbang — Total Debit = Total Kredit = Rp {{ number_format($totalDebit, 0, ',', '.') }}
</div>
@endif

{{-- Summary Cards --}}
<div class="grid grid-cols-2 gap-4 sm:grid-cols-4 mb-6">
    <x-data-card>
        <div class="p-4 text-center">
            <p class="text-xs text-slate-500">Total Debit</p>
            <p class="text-xl font-extrabold text-blue-700">Rp {{ number_format($totalDebit / 1e6, 1, ',', '.') }}jt</p>
        </div>
    </x-data-card>
    <x-data-card>
        <div class="p-4 text-center">
            <p class="text-xs text-slate-500">Total Kredit</p>
            <p class="text-xl font-extrabold text-orange-700">Rp {{ number_format($totalCredit / 1e6, 1, ',', '.') }}jt</p>
        </div>
    </x-data-card>
    <x-data-card>
        <div class="p-4 text-center">
            <p class="text-xs text-slate-500">Jumlah Akun Aktif</p>
            <p class="text-xl font-extrabold text-slate-800">{{ $rows->count() }}</p>
        </div>
    </x-data-card>
    <x-data-card>
        <div class="p-4 text-center">
            <p class="text-xs text-slate-500">Keseimbangan</p>
            <p class="text-xl font-extrabold {{ $isBalanced ? 'text-emerald-700' : 'text-red-600' }}">
                {{ $isBalanced ? '✓ Balance' : '✗ Unbalanced' }}
            </p>
        </div>
    </x-data-card>
</div>

<x-data-card title="Neraca Saldo — Periode {{ $dateFrom }} s/d {{ $dateTo }}">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50/60">
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 text-xs uppercase tracking-wide">Kode</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 text-xs uppercase tracking-wide">Nama Akun</th>
                    <th class="px-4 py-3 text-center font-semibold text-slate-600 text-xs uppercase tracking-wide">Tipe</th>
                    <th class="px-4 py-3 text-center font-semibold text-slate-600 text-xs uppercase tracking-wide">Saldo Normal</th>
                    <th class="px-4 py-3 text-right font-semibold text-blue-600 text-xs uppercase tracking-wide">Total Debit</th>
                    <th class="px-4 py-3 text-right font-semibold text-orange-600 text-xs uppercase tracking-wide">Total Kredit</th>
                    <th class="px-4 py-3 text-right font-semibold text-slate-600 text-xs uppercase tracking-wide">Saldo Akhir</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach($rows as $row)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-2.5 font-mono text-sm font-semibold text-slate-700">{{ $row->account->code }}</td>
                    <td class="px-4 py-2.5 font-medium text-slate-800">{{ $row->account->name }}</td>
                    <td class="px-4 py-2.5 text-center">
                        @php
                            $typeColor = match($row->account->account_type) {
                                'asset'     => 'bg-blue-100 text-blue-700',
                                'liability' => 'bg-red-100 text-red-700',
                                'equity'    => 'bg-purple-100 text-purple-700',
                                'revenue'   => 'bg-emerald-100 text-emerald-700',
                                'expense'   => 'bg-orange-100 text-orange-700',
                                default     => 'bg-slate-100 text-slate-600',
                            };
                        @endphp
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $typeColor }}">
                            {{ $row->account->getTypeLabel() }}
                        </span>
                    </td>
                    <td class="px-4 py-2.5 text-center">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $row->account->normal_balance === 'debit' ? 'bg-blue-50 text-blue-600' : 'bg-orange-50 text-orange-600' }}">
                            {{ ucfirst($row->account->normal_balance) }}
                        </span>
                    </td>
                    <td class="px-4 py-2.5 text-right">
                        @if($row->total_debit > 0)
                            <span class="font-semibold text-blue-700">Rp {{ number_format($row->total_debit, 0, ',', '.') }}</span>
                        @else
                            <span class="text-slate-300">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-2.5 text-right">
                        @if($row->total_credit > 0)
                            <span class="font-semibold text-orange-700">Rp {{ number_format($row->total_credit, 0, ',', '.') }}</span>
                        @else
                            <span class="text-slate-300">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-2.5 text-right">
                        <span class="font-bold {{ $row->balance < 0 ? 'text-red-600' : 'text-slate-800' }}">
                            @if($row->balance < 0)
                                (Rp {{ number_format(abs($row->balance), 0, ',', '.') }})
                            @else
                                Rp {{ number_format($row->balance, 0, ',', '.') }}
                            @endif
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="border-t-2 border-slate-300 bg-slate-50 font-extrabold">
                    <td colspan="4" class="px-4 py-3 text-right text-slate-700 text-sm uppercase tracking-wide">GRAND TOTAL</td>
                    <td class="px-4 py-3 text-right text-blue-800 text-base">Rp {{ number_format($totalDebit, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right text-orange-800 text-base">Rp {{ number_format($totalCredit, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-center text-sm font-bold {{ $isBalanced ? 'text-emerald-700' : 'text-red-700' }}">
                        {{ $isBalanced ? '✓ Seimbang' : '✗ Tidak Seimbang' }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</x-data-card>

@endsection
