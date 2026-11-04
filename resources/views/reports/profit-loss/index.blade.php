@extends('layouts.app')

@section('content')
<x-page-header title="Laporan Laba Rugi" subtitle="Analisis pendapatan vs pengeluaran per proyek">
    <x-slot name="actions">
        <form method="GET" class="flex items-center gap-2">
            <select name="year" class="form-select text-sm" onchange="this.form.submit()">
                @foreach($availableYears as $y)
                    <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>Tahun {{ $y }}</option>
                @endforeach
            </select>
        </form>
    </x-slot>
</x-page-header>

{{-- Summary KPIs --}}
@php
    $isProfit   = $totalProfit >= 0;
    $profitText = $isProfit ? 'text-emerald-700' : 'text-rose-700';
    $profitBg   = $isProfit ? 'bg-emerald-50 border-emerald-200' : 'bg-rose-50 border-rose-200';
@endphp
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <div class="rounded-2xl border bg-indigo-50 border-indigo-200 p-5 text-center">
        <div class="text-xs font-semibold text-indigo-500 uppercase tracking-wide mb-1">Total Pendapatan</div>
        <div class="text-xl font-extrabold text-indigo-700">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
    </div>
    <div class="rounded-2xl border bg-amber-50 border-amber-200 p-5 text-center">
        <div class="text-xs font-semibold text-amber-500 uppercase tracking-wide mb-1">Total Pengeluaran</div>
        <div class="text-xl font-extrabold text-amber-700">Rp {{ number_format($totalCost, 0, ',', '.') }}</div>
    </div>
    <div class="rounded-2xl border {{ $profitBg }} p-5 text-center">
        <div class="text-xs font-semibold {{ $profitText }} uppercase tracking-wide mb-1 opacity-70">Laba Kotor</div>
        <div class="text-xl font-extrabold {{ $profitText }}">
            {{ $isProfit ? '+' : '−' }} Rp {{ number_format(abs($totalProfit), 0, ',', '.') }}
        </div>
    </div>
    <div class="rounded-2xl border {{ $profitBg }} p-5 text-center">
        <div class="text-xs font-semibold {{ $profitText }} uppercase tracking-wide mb-1 opacity-70">Margin</div>
        <div class="text-xl font-extrabold {{ $profitText }}">{{ number_format($avgMargin, 1) }}%</div>
    </div>
</div>

{{-- Monthly Chart --}}
<x-data-card title="Tren Bulanan {{ $year }}" class="mb-6">
    <div class="px-5 py-4" style="height:300px;">
        <canvas id="plChart"></canvas>
    </div>
</x-data-card>

{{-- Per-Project Summary --}}
<x-data-card title="Ringkasan per Proyek">
    @if($summaryRows->isEmpty())
        <div class="py-14 text-center text-slate-400">
            <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-slate-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0 0 20.25 18V6A2.25 2.25 0 0 0 18 3.75H6A2.25 2.25 0 0 0 3.75 6v12A2.25 2.25 0 0 0 6 20.25Z"/>
                </svg>
            </div>
            <p class="font-semibold text-slate-600">Belum ada data untuk tahun {{ $year }}</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Proyek</th>
                        <th class="th-right">Pendapatan</th>
                        <th class="th-right">Pengeluaran</th>
                        <th class="th-right">Anggaran RAB</th>
                        <th class="th-right">Laba Kotor</th>
                        <th class="th-center">Margin</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($summaryRows as $row)
                    @php
                        $rowProfit = $row['gross_profit'] >= 0;
                        $margin    = $row['margin_pct'];
                    @endphp
                    <tr>
                        <td>
                            <div class="font-semibold text-slate-800">{{ $row['project']->name }}</div>
                            <div class="text-xs text-slate-400">
                                {{ $row['invoice_count'] }} invoice · {{ $row['tx_count'] }} transaksi
                            </div>
                        </td>
                        <td class="text-right font-semibold text-indigo-700">
                            Rp {{ number_format($row['revenue'], 0, ',', '.') }}
                        </td>
                        <td class="text-right font-semibold text-amber-700">
                            Rp {{ number_format($row['cost'], 0, ',', '.') }}
                        </td>
                        <td class="text-right text-slate-500">
                            @if($row['budget'] > 0)
                                Rp {{ number_format($row['budget'], 0, ',', '.') }}
                            @else
                                <span class="text-slate-300">—</span>
                            @endif
                        </td>
                        <td class="text-right font-bold {{ $rowProfit ? 'text-emerald-700' : 'text-rose-700' }}">
                            {{ $rowProfit ? '+' : '−' }}Rp {{ number_format(abs($row['gross_profit']), 0, ',', '.') }}
                        </td>
                        <td class="text-center">
                            @if($margin !== null)
                                @if($margin >= 20)
                                    <span class="badge-success">{{ number_format($margin, 1) }}%</span>
                                @elseif($margin >= 0)
                                    <span class="badge-warning">{{ number_format($margin, 1) }}%</span>
                                @else
                                    <span class="badge-danger">{{ number_format($margin, 1) }}%</span>
                                @endif
                            @else
                                <span class="text-slate-300 text-xs">N/A</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <a href="{{ route('profit-loss.show', [$row['project'], 'year' => $year]) }}"
                               class="inline-flex items-center gap-1 text-xs font-medium text-indigo-600 hover:text-indigo-800 transition-colors">
                                Detail
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
                                </svg>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td class="font-bold text-slate-700">TOTAL</td>
                        <td class="text-right font-bold text-indigo-700">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</td>
                        <td class="text-right font-bold text-amber-700">Rp {{ number_format($totalCost, 0, ',', '.') }}</td>
                        <td></td>
                        <td class="text-right font-bold {{ $totalProfit >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                            {{ $totalProfit >= 0 ? '+' : '−' }}Rp {{ number_format(abs($totalProfit), 0, ',', '.') }}
                        </td>
                        <td class="text-center">
                            @if($avgMargin >= 20)
                                <span class="badge-success">{{ number_format($avgMargin, 1) }}%</span>
                            @elseif($avgMargin >= 0)
                                <span class="badge-warning">{{ number_format($avgMargin, 1) }}%</span>
                            @else
                                <span class="badge-danger">{{ number_format($avgMargin, 1) }}%</span>
                            @endif
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif
</x-data-card>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
const ctx = document.getElementById('plChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: @json($chartMonths),
        datasets: [
            {
                label: 'Pendapatan',
                data: @json($chartRevenue),
                backgroundColor: 'rgba(99,102,241,0.7)',
                borderRadius: 4,
            },
            {
                label: 'Pengeluaran',
                data: @json($chartCost),
                backgroundColor: 'rgba(245,158,11,0.7)',
                borderRadius: 4,
            },
            {
                label: 'Laba',
                data: @json($chartProfit),
                type: 'line',
                borderColor: '#10b981',
                backgroundColor: 'rgba(16,185,129,0.1)',
                pointBackgroundColor: '#10b981',
                tension: 0.4,
                fill: false,
                yAxisID: 'y',
            },
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'top' } },
        scales: {
            y: {
                ticks: { callback: v => 'Rp ' + new Intl.NumberFormat('id-ID').format(v) },
                grid: { color: '#f1f5f9' }
            }
        }
    }
});
</script>
@endpush
@endsection
