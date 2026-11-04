@extends('layouts.app')
@section('title', 'Scorecard — ' . $supplier->name)

@section('content')
<x-page-header :title="'Scorecard: ' . $supplier->name" subtitle="Performa vendor 12 bulan terakhir">
    <x-slot name="actions">
        <form method="POST" action="{{ route('suppliers.scorecard.recalculate', $supplier) }}">
            @csrf
            <button type="submit" class="btn-secondary text-sm">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                Hitung Ulang Skor
            </button>
        </form>
        <a href="{{ route('suppliers.leaderboard') }}" class="btn-secondary">Leaderboard</a>
        <a href="{{ route('suppliers.index') }}" class="btn-secondary">← Supplier</a>
    </x-slot>
</x-page-header>


{{-- Current Score --}}
@if($currentScore)
@php
    $gradeColor = $currentScore->grade_color ?? 'text-slate-600';
    $grade = $currentScore->grade ?? '—';
@endphp
<div class="grid grid-cols-2 sm:grid-cols-6 gap-4 mb-6">
    <div class="sm:col-span-2 rounded-2xl border-2 p-5 text-center
        {{ $grade === 'A' ? 'border-emerald-300 bg-emerald-50' :
           ($grade === 'B' ? 'border-blue-300 bg-blue-50' :
           ($grade === 'C' ? 'border-amber-300 bg-amber-50' :
           ($grade === 'D' ? 'border-orange-300 bg-orange-50' :
            'border-rose-300 bg-rose-50'))) }}">
        <p class="text-xs font-semibold text-slate-500 mb-1">Skor Bulan Ini</p>
        <p class="text-5xl font-black {{ $gradeColor }}">{{ $grade }}</p>
        <p class="text-lg font-bold text-slate-700 mt-1">{{ number_format($currentScore->overall_score, 1) }} / 100</p>
    </div>

    @foreach([
        ['label'=>'Ketepatan Pengiriman','val'=>$currentScore->delivery_score,'color'=>'text-blue-600','bg'=>'bg-blue-50','border'=>'border-blue-200','note'=>'Bobot 30%'],
        ['label'=>'Akurasi Kuantitas','val'=>$currentScore->quantity_score,'color'=>'text-violet-600','bg'=>'bg-violet-50','border'=>'border-violet-200','note'=>'Bobot 25%'],
        ['label'=>'Kualitas Barang','val'=>$currentScore->quality_score,'color'=>'text-emerald-600','bg'=>'bg-emerald-50','border'=>'border-emerald-200','note'=>'Bobot 30%'],
        ['label'=>'Kompetitifitas Harga','val'=>$currentScore->price_score,'color'=>'text-amber-600','bg'=>'bg-amber-50','border'=>'border-amber-200','note'=>'Bobot 15%'],
    ] as $s)
    <div class="{{ $s['bg'] }} border {{ $s['border'] }} rounded-2xl p-4 text-center">
        <p class="text-xs text-slate-500 font-medium">{{ $s['label'] }}</p>
        <p class="text-2xl font-bold {{ $s['color'] }}">{{ number_format($s['val'], 1) }}</p>
        <p class="text-[10px] text-slate-400 mt-0.5">{{ $s['note'] }}</p>
    </div>
    @endforeach
</div>
@else
<div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 mb-6 text-center">
    <p class="text-amber-800 font-semibold">Belum ada skor untuk bulan ini.</p>
    <p class="text-amber-600 text-sm mt-1">Klik "Hitung Ulang Skor" untuk menghitung performa vendor bulan ini.</p>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- Chart --}}
    <x-data-card title="Tren Skor 12 Bulan">
        <div class="px-5 py-4">
            <canvas id="scorecardChart" height="200"></canvas>
        </div>
    </x-data-card>

    {{-- Avg + Notes --}}
    <x-data-card title="Ringkasan Performa">
        <div class="px-5 py-4 space-y-4">
            <div class="flex justify-between items-center py-3 border-b border-slate-100">
                <span class="text-sm text-slate-600">Rata-rata Skor (12 bln)</span>
                <span class="text-xl font-bold text-slate-800">{{ number_format($avgScore ?? 0, 1) }}</span>
            </div>
            <div class="flex justify-between items-center py-3 border-b border-slate-100">
                <span class="text-sm text-slate-600">Total Pesanan Diterima</span>
                <span class="font-semibold text-slate-800">{{ $history->sum('total_orders') }}</span>
            </div>
            <div class="flex justify-between items-center py-3 border-b border-slate-100">
                <span class="text-sm text-slate-600">Pengiriman Tepat Waktu</span>
                <span class="font-semibold text-emerald-600">{{ $history->sum('on_time_deliveries') }}</span>
            </div>
            <div class="flex justify-between items-center py-3 border-b border-slate-100">
                <span class="text-sm text-slate-600">Pengiriman Terlambat</span>
                <span class="font-semibold text-rose-600">{{ $history->sum('late_deliveries') }}</span>
            </div>
            <div class="flex justify-between items-center py-3">
                <span class="text-sm text-slate-600">Qty Retur</span>
                <span class="font-semibold text-amber-600">{{ number_format($history->sum('qty_returned'), 2, ',', '.') }}</span>
            </div>
        </div>
    </x-data-card>

</div>

{{-- History Table --}}
@if($history->count() > 0)
<div class="mt-6">
<x-data-card title="Riwayat Skor Bulanan">
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Periode</th>
                    <th class="th-center">Pesanan</th>
                    <th class="th-center">Tepat Waktu</th>
                    <th class="th-right">Skor Kirim</th>
                    <th class="th-right">Skor Qty</th>
                    <th class="th-right">Skor QC</th>
                    <th class="th-right">Skor Harga</th>
                    <th class="th-right">Overall</th>
                    <th class="th-center">Grade</th>
                </tr>
            </thead>
            <tbody>
                @foreach($history->sortByDesc(fn($s) => $s->year * 100 + $s->month) as $score)
                @php
                    $g = $score->grade ?? '—';
                    $gColor = match($g) {
                        'A' => 'text-emerald-700 bg-emerald-100',
                        'B' => 'text-blue-700 bg-blue-100',
                        'C' => 'text-amber-700 bg-amber-100',
                        'D' => 'text-orange-700 bg-orange-100',
                        default => 'text-rose-700 bg-rose-100',
                    };
                @endphp
                <tr>
                    <td class="font-semibold text-sm">{{ str_pad($score->month, 2, '0', STR_PAD_LEFT) }}/{{ $score->year }}</td>
                    <td class="text-center text-sm">{{ $score->total_orders }}</td>
                    <td class="text-center text-sm">{{ $score->on_time_deliveries }}/{{ $score->total_orders }}</td>
                    <td class="text-right font-mono text-sm">{{ number_format($score->delivery_score, 1) }}</td>
                    <td class="text-right font-mono text-sm">{{ number_format($score->quantity_score, 1) }}</td>
                    <td class="text-right font-mono text-sm">{{ number_format($score->quality_score, 1) }}</td>
                    <td class="text-right font-mono text-sm">{{ number_format($score->price_score, 1) }}</td>
                    <td class="text-right font-bold text-sm">{{ number_format($score->overall_score, 1) }}</td>
                    <td class="text-center">
                        <span class="inline-block text-xs font-bold px-2 py-0.5 rounded-full {{ $gColor }}">{{ $g }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-data-card>
</div>
@endif

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
const ctx = document.getElementById('scorecardChart');
if (ctx) {
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($chartLabels),
            datasets: [
                {
                    label: 'Overall',
                    data: @json($chartOverall),
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99,102,241,.1)',
                    borderWidth: 2.5,
                    tension: 0.35,
                    fill: true,
                    pointBackgroundColor: '#6366f1',
                    pointRadius: 4,
                },
                {
                    label: 'Pengiriman',
                    data: @json($chartDelivery),
                    borderColor: '#3b82f6',
                    borderWidth: 1.5,
                    tension: 0.35,
                    fill: false,
                    pointRadius: 3,
                    borderDash: [4,3],
                },
                {
                    label: 'Kualitas',
                    data: @json($chartQuality),
                    borderColor: '#10b981',
                    borderWidth: 1.5,
                    tension: 0.35,
                    fill: false,
                    pointRadius: 3,
                    borderDash: [4,3],
                },
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: { min: 0, max: 100, ticks: { stepSize: 20 }, grid: { color: '#f1f5f9' } },
                x: { grid: { display: false } }
            },
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } }
            }
        }
    });
}
</script>
@endpush
