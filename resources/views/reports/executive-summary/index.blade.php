@extends('layouts.app')
@section('content')

<x-page-header title="Executive Summary" subtitle="Ringkasan manajemen bulanan">
    <x-slot name="actions">
        <a href="{{ route('executive-summary.export-pdf', ['year' => $year, 'month' => $month]) }}"
           class="btn-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0 4-4m-4 4-4-4M4.5 16.5v1.125A2.625 2.625 0 0 0 7.125 20.25h9.75A2.625 2.625 0 0 0 19.5 17.625V16.5"/>
            </svg>
            Export PDF
        </a>
    </x-slot>
</x-page-header>

{{-- Period Selector --}}
<x-data-card class="mb-6">
    <form method="GET" class="flex flex-wrap gap-3 items-end px-5 py-4">
        <div>
            <label class="form-label text-xs">Tahun</label>
            <select name="year" class="form-select">
                @foreach($years as $y)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label text-xs">Bulan</label>
            <select name="month" class="form-select">
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}
                    </option>
                @endfor
            </select>
        </div>
        <button type="submit" class="btn-primary">Tampilkan</button>
    </form>
</x-data-card>

@php
$periodLabel = \Carbon\Carbon::create($year, $month)->translatedFormat('F Y');
@endphp

{{-- KPI Cards --}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
    {{-- Pengeluaran --}}
    <div class="rounded-[22px] bg-rose-50 border border-rose-200 p-4 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wider text-rose-600 mb-2">Pengeluaran Bulan Ini</p>
        <p class="font-extrabold text-slate-900 text-sm">Rp {{ number_format($spendingMonth, 0, ',', '.') }}</p>
        <p class="text-xs text-slate-400 mt-0.5">YTD: Rp {{ number_format($spendingYtd, 0, ',', '.') }}</p>
    </div>
    {{-- Revenue --}}
    <div class="rounded-[22px] bg-emerald-50 border border-emerald-200 p-4 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wider text-emerald-600 mb-2">Revenue Bulan Ini</p>
        <p class="font-extrabold text-slate-900 text-sm">Rp {{ number_format($revenueMonth, 0, ',', '.') }}</p>
        <p class="text-xs text-slate-400 mt-0.5">YTD: Rp {{ number_format($revenueYtd, 0, ',', '.') }}</p>
    </div>
    {{-- Net --}}
    @php $netPos = $netMonth >= 0; @endphp
    <div class="rounded-[22px] {{ $netPos ? 'bg-indigo-50 border-indigo-200' : 'bg-rose-50 border-rose-200' }} border p-4 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wider {{ $netPos ? 'text-indigo-600' : 'text-rose-600' }} mb-2">Net Bulan Ini</p>
        <p class="font-extrabold text-sm {{ $netPos ? 'text-indigo-700' : 'text-rose-700' }}">
            {{ $netMonth >= 0 ? '+' : '' }}Rp {{ number_format($netMonth, 0, ',', '.') }}
        </p>
    </div>
    {{-- Stok Kritis --}}
    <div class="rounded-[22px] {{ $lowStock->count() > 0 ? 'bg-amber-50 border-amber-200' : 'bg-slate-50 border-slate-200' }} border p-4 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wider {{ $lowStock->count() > 0 ? 'text-amber-600' : 'text-slate-500' }} mb-2">Stok Kritis</p>
        <p class="font-extrabold text-sm {{ $lowStock->count() > 0 ? 'text-amber-700' : 'text-slate-700' }}">
            {{ $lowStock->count() }} material
        </p>
    </div>
    {{-- Invoice Jatuh Tempo --}}
    <div class="rounded-[22px] {{ $overdueInvoices->count() > 0 ? 'bg-rose-50 border-rose-200' : 'bg-slate-50 border-slate-200' }} border p-4 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wider {{ $overdueInvoices->count() > 0 ? 'text-rose-600' : 'text-slate-500' }} mb-2">Invoice Jatuh Tempo</p>
        <p class="font-extrabold text-sm {{ $overdueInvoices->count() > 0 ? 'text-rose-700' : 'text-slate-700' }}">
            {{ $overdueInvoices->count() }} invoice
        </p>
    </div>
</div>

{{-- 12-Month Chart --}}
<x-data-card class="mb-6">
    <h3 class="font-semibold text-slate-800 px-5 pt-4 pb-2">Tren 12 Bulan — Pengeluaran vs Revenue</h3>
    <div class="px-4 pb-4" style="height: 280px;">
        <canvas id="execChart"></canvas>
    </div>
</x-data-card>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6">
    {{-- RAB Utilization --}}
    <x-data-card>
        <div class="flex items-center gap-2 px-5 pt-4 pb-3 border-b border-slate-100">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z"/>
            </svg>
            <h3 class="font-semibold text-slate-800">RAB Aktif — Utilisasi</h3>
        </div>
        @if($rabSummary->isEmpty())
            <p class="px-5 py-8 text-sm text-center text-slate-400">Tidak ada RAB aktif.</p>
        @else
            <div class="divide-y divide-slate-100">
                @foreach($rabSummary as $r)
                <div class="px-5 py-3">
                    <div class="flex justify-between text-sm mb-1">
                        <span class="font-medium text-slate-800 truncate">{{ $r['name'] }}</span>
                        <span class="font-bold {{ $r['pct'] > 90 ? 'text-rose-600' : ($r['pct'] > 70 ? 'text-amber-600' : 'text-emerald-600') }}">{{ $r['pct'] }}%</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-bar-fill {{ $r['pct'] > 90 ? 'bg-rose-500' : ($r['pct'] > 70 ? 'bg-amber-400' : 'bg-emerald-500') }}"
                             style="width: {{ min($r['pct'], 100) }}%"></div>
                    </div>
                    <div class="flex justify-between text-xs text-slate-400 mt-1">
                        <span>{{ $r['project'] }}</span>
                        <span>Rp {{ number_format($r['actual'], 0, ',', '.') }} / Rp {{ number_format($r['budget'], 0, ',', '.') }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </x-data-card>

    {{-- Top Supplier --}}
    <x-data-card>
        <div class="flex items-center gap-2 px-5 pt-4 pb-3 border-b border-slate-100">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z"/>
            </svg>
            <h3 class="font-semibold text-slate-800">Top Supplier — {{ $periodLabel }}</h3>
        </div>
        @if($topSuppliers->isEmpty())
            <p class="px-5 py-8 text-sm text-center text-slate-400">Tidak ada transaksi bulan ini.</p>
        @else
            <div class="divide-y divide-slate-100 text-sm">
                @foreach($topSuppliers as $i => $s)
                <div class="px-5 py-3 flex items-center gap-3">
                    <span class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold flex items-center justify-center shrink-0">{{ $i + 1 }}</span>
                    <div class="flex-1 truncate">
                        <div class="font-medium text-slate-800 truncate">{{ $s->name }}</div>
                        <div class="text-xs text-slate-400">{{ $s->tx_count }} transaksi</div>
                    </div>
                    <div class="font-semibold text-slate-800 shrink-0">Rp {{ number_format($s->total, 0, ',', '.') }}</div>
                </div>
                @endforeach
            </div>
        @endif
    </x-data-card>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
    {{-- Stok Kritis --}}
    <x-data-card>
        <div class="flex items-center gap-2 px-5 pt-4 pb-3 border-b border-slate-100">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
            </svg>
            <h3 class="font-semibold text-slate-800">Stok Material Kritis</h3>
        </div>
        @if($lowStock->isEmpty())
            <p class="px-5 py-8 text-sm text-center text-emerald-600 font-medium">Semua stok dalam kondisi aman</p>
        @else
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Material</th>
                        <th class="th-right">Stok</th>
                        <th class="th-right">Minimum</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lowStock as $s)
                    <tr>
                        <td class="font-medium text-slate-800">{{ $s->name }}</td>
                        <td class="text-right font-bold text-rose-600">{{ $s->stock }}</td>
                        <td class="text-right text-slate-500">{{ $s->minimum_stock }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </x-data-card>

    {{-- SPK Aktif --}}
    <x-data-card>
        <div class="flex items-center gap-2 px-5 pt-4 pb-3 border-b border-slate-100">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437 1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008Z"/>
            </svg>
            <h3 class="font-semibold text-slate-800">SPK Aktif</h3>
        </div>
        @if($activeSpk->isEmpty())
            <p class="px-5 py-8 text-sm text-center text-slate-400">Tidak ada SPK aktif.</p>
        @else
            <div class="divide-y divide-slate-100 text-sm">
                @foreach($activeSpk as $spk)
                <div class="px-5 py-3">
                    <div class="flex justify-between mb-1">
                        <span class="font-medium text-slate-800">{{ $spk->work_name }}</span>
                        <span class="text-xs text-slate-500">{{ $spk->subcontractor->name ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between text-xs text-slate-400">
                        <span>{{ $spk->project->name ?? '—' }}</span>
                        <span>Selesai: {{ $spk->end_date ? $spk->end_date->format('d M Y') : '—' }}</span>
                    </div>
                    <div class="progress-bar mt-1.5">
                        <div class="progress-bar-fill bg-indigo-500" style="width: {{ $spk->progress_pct }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </x-data-card>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const months  = @json($chartMonths);
const spend   = @json($chartSpend);
const revenue = @json($chartRevenue);
const net     = spend.map((s, i) => revenue[i] - s);

new Chart(document.getElementById('execChart'), {
    type: 'bar',
    data: {
        labels: months,
        datasets: [
            { label: 'Pengeluaran', data: spend,   backgroundColor: 'rgba(244,63,94,0.65)', borderRadius: 6 },
            { label: 'Revenue',     data: revenue, backgroundColor: 'rgba(16,185,129,0.65)', borderRadius: 6 },
            { label: 'Net',         data: net,     type: 'line', borderColor: '#6366f1', backgroundColor: 'transparent', tension: 0.4, borderWidth: 2, pointRadius: 3 },
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: { position: 'top' },
            tooltip: { callbacks: { label: c => c.dataset.label + ': Rp ' + c.parsed.y.toLocaleString('id-ID') }}
        },
        scales: { y: { ticks: { callback: v => 'Rp ' + (v / 1000000).toFixed(0) + 'jt' }}}
    }
});
</script>
@endpush
@endsection
