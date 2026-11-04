@extends('layouts.app')

@section('content')
<x-page-header :title="'P&L — ' . $project->name">
    <x-slot name="actions">
        <a href="{{ route('profit-loss.index', ['year' => $year]) }}" class="btn-secondary">← Semua Proyek</a>
        <form method="GET" class="flex items-center gap-2">
            <select name="year" class="form-select" onchange="this.form.submit()">
                @foreach($availableYears as $y)
                    <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>Tahun {{ $y }}</option>
                @endforeach
            </select>
        </form>
        <a href="{{ route('profit-loss.export-pdf', [$project, 'year' => $year]) }}"
           class="btn-primary" target="_blank">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0 4-4m-4 4-4-4M4.5 16.5v1.125A2.625 2.625 0 0 0 7.125 20.25h9.75A2.625 2.625 0 0 0 19.5 17.625V16.5"/>
            </svg>
            Export PDF
        </a>
    </x-slot>
</x-page-header>

{{-- KPI Cards --}}
@php $isProfitable = $grossProfit >= 0; @endphp
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
    <div class="rounded-2xl border bg-emerald-50 border-emerald-200 p-4 text-center">
        <div class="text-xs text-emerald-600 font-semibold uppercase tracking-wide mb-1">Pendapatan Diterima</div>
        <div class="text-lg font-bold text-emerald-700">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
    </div>
    <div class="rounded-2xl border bg-indigo-50 border-indigo-200 p-4 text-center">
        <div class="text-xs text-indigo-500 font-semibold uppercase tracking-wide mb-1">Total Ditagihkan</div>
        <div class="text-lg font-bold text-indigo-700">Rp {{ number_format($totalBilled, 0, ',', '.') }}</div>
    </div>
    <div class="rounded-2xl border bg-amber-50 border-amber-200 p-4 text-center">
        <div class="text-xs text-amber-600 font-semibold uppercase tracking-wide mb-1">Total Pengeluaran</div>
        <div class="text-lg font-bold text-amber-700">Rp {{ number_format($totalCost, 0, ',', '.') }}</div>
    </div>
    <div class="rounded-2xl border bg-slate-50 border-slate-200 p-4 text-center">
        <div class="text-xs text-slate-500 font-semibold uppercase tracking-wide mb-1">Anggaran RAB</div>
        <div class="text-lg font-bold text-slate-700">
            {{ $totalBudget > 0 ? 'Rp ' . number_format($totalBudget, 0, ',', '.') : '—' }}
        </div>
    </div>
    <div class="rounded-2xl border {{ $isProfitable ? 'bg-emerald-50 border-emerald-200' : 'bg-rose-50 border-rose-200' }} p-4 text-center">
        <div class="text-xs {{ $isProfitable ? 'text-emerald-600' : 'text-rose-600' }} font-semibold uppercase tracking-wide mb-1">Laba Kotor</div>
        <div class="text-lg font-bold {{ $isProfitable ? 'text-emerald-700' : 'text-rose-700' }}">
            {{ $isProfitable ? '+' : '−' }}Rp {{ number_format(abs($grossProfit), 0, ',', '.') }}
        </div>
    </div>
    <div class="rounded-2xl border {{ $isProfitable ? 'bg-emerald-50 border-emerald-200' : 'bg-rose-50 border-rose-200' }} p-4 text-center">
        <div class="text-xs {{ $isProfitable ? 'text-emerald-600' : 'text-rose-600' }} font-semibold uppercase tracking-wide mb-1">Margin</div>
        <div class="text-lg font-bold {{ $isProfitable ? 'text-emerald-700' : 'text-rose-700' }}">
            {{ number_format($marginPct, 1) }}%
        </div>
    </div>
</div>

{{-- RAB Budget Usage --}}
@if($totalBudget > 0)
<x-data-card class="mb-6">
    <div class="px-5 py-4">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-semibold text-slate-700">Realisasi vs Anggaran RAB</span>
            <span class="text-sm font-bold {{ $budgetVsCost > 100 ? 'text-rose-600' : ($budgetVsCost > 80 ? 'text-amber-600' : 'text-emerald-600') }}">
                {{ number_format($budgetVsCost, 1) }}%
            </span>
        </div>
        <div class="progress-bar">
            <div class="progress-bar-fill {{ $budgetVsCost > 100 ? 'bg-rose-500' : ($budgetVsCost > 80 ? 'bg-amber-400' : 'bg-emerald-500') }}"
                 style="width: {{ min($budgetVsCost, 100) }}%"></div>
        </div>
        <div class="flex justify-between text-xs text-slate-500 mt-1">
            <span>Rp {{ number_format($totalCost, 0, ',', '.') }} terpakai</span>
            <span>Rp {{ number_format($totalBudget, 0, ',', '.') }} total anggaran</span>
        </div>
    </div>
</x-data-card>
@endif

{{-- Monthly Table --}}
<x-data-card class="mb-6">
    <div class="px-5 pt-4 pb-2 border-b border-slate-100">
        <h3 class="font-semibold text-slate-800">Rincian Bulanan {{ $year }}</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="tbl">
            <thead>
                <tr>
                    <th>Bulan</th>
                    <th class="th-right">Pendapatan</th>
                    <th class="th-right">Pengeluaran</th>
                    <th class="th-right">Laba</th>
                    <th class="th-center">Invoice</th>
                    <th class="th-center">Transaksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($monthly as $m => $row)
                @if($row['revenue'] > 0 || $row['cost'] > 0)
                <tr>
                    <td class="font-medium text-slate-700">{{ $row['month'] }}</td>
                    <td class="text-right text-indigo-700">
                        {{ $row['revenue'] > 0 ? 'Rp ' . number_format($row['revenue'], 0, ',', '.') : '—' }}
                    </td>
                    <td class="text-right text-amber-700">
                        {{ $row['cost'] > 0 ? 'Rp ' . number_format($row['cost'], 0, ',', '.') : '—' }}
                    </td>
                    <td class="text-right font-semibold {{ $row['profit'] >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                        {{ $row['profit'] >= 0 ? '+' : '−' }}Rp {{ number_format(abs($row['profit']), 0, ',', '.') }}
                    </td>
                    <td class="text-center text-slate-500">{{ $row['invoice_count'] ?: '—' }}</td>
                    <td class="text-center text-slate-500">{{ $row['tx_count'] ?: '—' }}</td>
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>
    </div>
</x-data-card>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- Invoices --}}
    <x-data-card>
        <div class="px-5 pt-4 pb-3 border-b border-slate-100">
            <h3 class="font-semibold text-slate-800">Invoice ({{ $invoices->count() }})</h3>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($invoices as $inv)
            <div class="px-5 py-3 flex items-center justify-between gap-3">
                <div>
                    <a href="{{ route('invoices.show', $inv) }}"
                       class="font-mono text-xs text-indigo-600 hover:underline font-semibold">{{ $inv->invoice_no }}</a>
                    <div class="text-xs text-slate-400 mt-0.5">
                        {{ $inv->invoice_date->format('d M Y') }} · {{ $inv->client_name }}
                    </div>
                </div>
                <div class="text-right shrink-0">
                    <div class="text-sm font-semibold text-indigo-700">
                        Rp {{ number_format($inv->paid_amount, 0, ',', '.') }}
                    </div>
                    <div class="text-xs text-slate-400">
                        dari Rp {{ number_format($inv->grand_total, 0, ',', '.') }}
                    </div>
                </div>
            </div>
            @empty
            <p class="px-5 py-8 text-sm text-slate-400 text-center">Belum ada invoice</p>
            @endforelse
        </div>
    </x-data-card>

    {{-- Purchase Transactions --}}
    <x-data-card>
        <div class="px-5 pt-4 pb-3 border-b border-slate-100">
            <h3 class="font-semibold text-slate-800">Transaksi Pembelian ({{ $transactions->count() }})</h3>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($transactions as $tx)
            <div class="px-5 py-3 flex items-center justify-between gap-3">
                <div>
                    <a href="{{ route('purchase-transactions.show', $tx) }}"
                       class="font-mono text-xs text-amber-700 hover:underline font-semibold">{{ $tx->transaction_no }}</a>
                    <div class="text-xs text-slate-400 mt-0.5">
                        {{ $tx->transaction_date->format('d M Y') }}
                        @if($tx->supplier) · {{ $tx->supplier->name }} @endif
                    </div>
                </div>
                <div class="text-sm font-semibold text-amber-700 shrink-0">
                    Rp {{ number_format($tx->grand_total, 0, ',', '.') }}
                </div>
            </div>
            @empty
            <p class="px-5 py-8 text-sm text-slate-400 text-center">Belum ada transaksi</p>
            @endforelse
        </div>
    </x-data-card>

</div>
@endsection
