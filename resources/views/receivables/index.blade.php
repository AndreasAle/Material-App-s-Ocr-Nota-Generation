@extends('layouts.app')

@section('title', 'Piutang Usaha')

@section('content')

<x-page-header title="Piutang Usaha" subtitle="Aging report — status pembayaran piutang dari klien">
    <x-slot name="actions">
        <a href="{{ route('receivables.export-excel', request()->query()) }}"
           class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 transition-colors">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
            </svg>
            Export Excel
        </a>
    </x-slot>
</x-page-header>

{{-- Overdue Alert --}}
@if($summary['overdue_amount'] > 0)
<div class="mb-5 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700 font-medium">
    ⚠ <strong>{{ $summary['overdue_count'] }} invoice</strong> sudah melewati jatuh tempo dengan total tagihan
    <strong>Rp {{ number_format($summary['overdue_amount'], 0, ',', '.') }}</strong>. Segera follow-up ke klien.
</div>
@endif

{{-- Summary Cards --}}
<div class="grid grid-cols-2 gap-4 sm:grid-cols-4 mb-6">
    <x-data-card>
        <div class="p-5">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Total Outstanding</p>
            <p class="mt-1 text-xl font-extrabold text-slate-800">Rp {{ number_format($summary['total_outstanding'] / 1e6, 1, ',', '.') }}jt</p>
            <p class="text-xs text-slate-400 mt-1">Rp {{ number_format($summary['total_outstanding'], 0, ',', '.') }}</p>
        </div>
    </x-data-card>
    <x-data-card>
        <div class="p-5">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Belum Dibayar</p>
            <p class="mt-1 text-xl font-extrabold text-amber-600">Rp {{ number_format($summary['unpaid_total'] / 1e6, 1, ',', '.') }}jt</p>
            <p class="text-xs text-slate-400 mt-1">Invoice belum ada pembayaran</p>
        </div>
    </x-data-card>
    <x-data-card>
        <div class="p-5">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Bayar Sebagian</p>
            <p class="mt-1 text-xl font-extrabold text-indigo-600">Rp {{ number_format($summary['partial_total'] / 1e6, 1, ',', '.') }}jt</p>
            <p class="text-xs text-slate-400 mt-1">Sudah ada pembayaran sebagian</p>
        </div>
    </x-data-card>
    <x-data-card>
        <div class="p-5">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Sudah Jatuh Tempo</p>
            <p class="mt-1 text-xl font-extrabold text-red-600">{{ $summary['overdue_count'] }} invoice</p>
            <p class="text-xs text-red-400 mt-1">Rp {{ number_format($summary['overdue_amount'], 0, ',', '.') }}</p>
        </div>
    </x-data-card>
</div>

{{-- Filter --}}
<form method="GET" class="flex items-center gap-3 mb-5">
    <select name="project_id" class="rounded-lg border border-slate-200 text-sm px-3 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
        <option value="">Semua Proyek</option>
        @foreach($projects as $p)
            <option value="{{ $p->id }}" @selected($projectId == $p->id)>{{ $p->name }}</option>
        @endforeach
    </select>
    <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700">Filter</button>
    @if($projectId)
        <a href="{{ route('receivables.index') }}" class="px-3 py-2 rounded-lg border border-slate-300 text-sm text-slate-600 hover:bg-slate-50">Reset</a>
    @endif
</form>

{{-- Aging Buckets --}}
<div class="grid grid-cols-5 gap-3 mb-6">
    @foreach([
        'current' => ['Current', 'text-emerald-700', 'bg-emerald-50 border-emerald-200', 'Belum jatuh tempo'],
        '1_30'    => ['1-30 Hari', 'text-yellow-700', 'bg-yellow-50 border-yellow-200', 'Overdue 1-30 hari'],
        '31_60'   => ['31-60 Hari', 'text-orange-700', 'bg-orange-50 border-orange-200', 'Overdue 31-60 hari'],
        '61_90'   => ['61-90 Hari', 'text-red-600', 'bg-red-50 border-red-200', 'Overdue 61-90 hari'],
        'over_90' => ['>90 Hari', 'text-red-800', 'bg-red-100 border-red-300', 'Overdue >90 hari'],
    ] as $key => [$label, $textClass, $bgClass, $desc])
    <div class="rounded-xl border {{ $bgClass }} p-4 text-center">
        <p class="text-xs font-extrabold {{ $textClass }} uppercase tracking-wide">{{ $label }}</p>
        <p class="mt-2 text-lg font-extrabold {{ $textClass }}">
            Rp {{ number_format(($aging['totals'][$key] ?? 0) / 1e6, 1, ',', '.') }}jt
        </p>
        <p class="text-xs text-slate-500 mt-1">{{ count($aging['buckets'][$key] ?? []) }} invoice</p>
        <p class="text-xs {{ $textClass }} opacity-60 mt-0.5">{{ $desc }}</p>
    </div>
    @endforeach
</div>

{{-- Outstanding List --}}
<x-data-card title="Daftar Piutang Outstanding" subtitle="Invoice yang belum lunas">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50/60">
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 text-xs uppercase">No. Invoice</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 text-xs uppercase">Klien</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 text-xs uppercase">Proyek</th>
                    <th class="px-4 py-3 text-center font-semibold text-slate-600 text-xs uppercase">Tgl Invoice</th>
                    <th class="px-4 py-3 text-center font-semibold text-slate-600 text-xs uppercase">Jatuh Tempo</th>
                    <th class="px-4 py-3 text-right font-semibold text-slate-600 text-xs uppercase">Total</th>
                    <th class="px-4 py-3 text-right font-semibold text-slate-600 text-xs uppercase">Terbayar</th>
                    <th class="px-4 py-3 text-right font-semibold text-slate-600 text-xs uppercase">Outstanding</th>
                    <th class="px-4 py-3 text-center font-semibold text-slate-600 text-xs uppercase">Aging</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($list as $inv)
                <tr class="hover:bg-slate-50 {{ $inv->is_overdue ? 'bg-red-50/20' : '' }}">
                    <td class="px-4 py-3">
                        <a href="{{ route('invoices.show', $inv) }}" class="font-mono font-bold text-indigo-600 hover:underline">{{ $inv->invoice_no }}</a>
                    </td>
                    <td class="px-4 py-3 font-medium text-slate-800">{{ $inv->client_name }}</td>
                    <td class="px-4 py-3 text-slate-500 text-xs">{{ $inv->project?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-center text-xs text-slate-500">{{ $inv->invoice_date->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-center text-xs {{ $inv->is_overdue ? 'text-red-600 font-bold' : 'text-slate-500' }}">
                        {{ $inv->due_date ? $inv->due_date->format('d/m/Y') : '—' }}
                    </td>
                    <td class="px-4 py-3 text-right text-slate-700">Rp {{ number_format($inv->grand_total, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right text-emerald-600 font-medium">Rp {{ number_format($inv->paid_amount, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right font-bold text-slate-900">Rp {{ number_format($inv->outstanding, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($inv->is_overdue)
                            @php
                                $ageColor = $inv->days_overdue <= 30 ? 'bg-yellow-100 text-yellow-700'
                                    : ($inv->days_overdue <= 60 ? 'bg-orange-100 text-orange-700'
                                    : ($inv->days_overdue <= 90 ? 'bg-red-100 text-red-700'
                                    : 'bg-red-200 text-red-900'));
                            @endphp
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold {{ $ageColor }}">
                                {{ $inv->days_overdue }} hr
                            </span>
                        @else
                            <span class="text-emerald-600 text-xs font-medium">On Time</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-4 py-10 text-center text-slate-400">
                        Tidak ada piutang outstanding.
                        @if($projectId)
                            <a href="{{ route('receivables.index') }}" class="ml-2 text-indigo-500 hover:underline">Lihat semua proyek</a>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($list->isNotEmpty())
            <tfoot class="border-t-2 border-slate-300 bg-slate-50 font-bold">
                <tr>
                    <td colspan="7" class="px-4 py-3 text-right text-slate-700">TOTAL OUTSTANDING</td>
                    <td class="px-4 py-3 text-right text-indigo-800 text-base">Rp {{ number_format($list->sum('outstanding'), 0, ',', '.') }}</td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</x-data-card>

@endsection
