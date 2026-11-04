@extends('layouts.app')
@section('title', 'Stok: ' . $material->name)

@section('content')
@php
    $isEmpty = $currentStock <= 0;
    $isLow   = $material->minimum_stock !== null && $currentStock <= (float) $material->minimum_stock;
    $stockClass = $isEmpty ? 'text-rose-600' : ($isLow ? 'text-amber-600' : 'text-emerald-600');
@endphp

<x-page-header title="{{ $material->name }}" subtitle="{{ $material->category?->name }} · {{ $material->unit?->name }}">
    <x-slot name="actions">
        <a href="{{ route('stock.index') }}" class="btn-secondary">← Semua Stok</a>
        <a href="{{ route('stock.create', ['material_id' => $material->id]) }}" class="btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Catat Gerakan
        </a>
    </x-slot>
</x-page-header>

{{-- Alerts --}}
@if ($isEmpty)
    <div class="alert-danger mb-5">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
        </svg>
        <p><strong>Stok habis!</strong> Segera lakukan pengadaan untuk material ini.</p>
    </div>
@elseif ($isLow)
    <div class="alert-warning mb-5">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008z"/>
        </svg>
        <p><strong>Stok di bawah minimum!</strong> Batas minimum: {{ number_format($material->minimum_stock, 2, ',', '.') }} {{ $material->unit?->name }}</p>
    </div>
@endif

{{-- KPI Cards --}}
<div class="mb-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div class="rounded-2xl border {{ $isEmpty ? 'border-rose-200 bg-rose-50' : ($isLow ? 'border-amber-200 bg-amber-50' : 'border-emerald-200 bg-emerald-50') }} px-5 py-4 shadow-sm text-center">
        <p class="text-xs text-slate-500 mb-1">Stok Saat Ini</p>
        <p class="text-3xl font-bold {{ $stockClass }}">{{ number_format($currentStock, 2, ',', '.') }}</p>
        <p class="text-sm text-slate-400 mt-0.5">{{ $material->unit?->name }}</p>
    </div>
    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 shadow-sm text-center">
        <p class="text-xs text-slate-500 mb-1">Total Masuk</p>
        <p class="text-3xl font-bold text-emerald-600">{{ number_format($totalIn, 2, ',', '.') }}</p>
        <p class="text-sm text-slate-400 mt-0.5">{{ $material->unit?->name }}</p>
    </div>
    <div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 shadow-sm text-center">
        <p class="text-xs text-slate-500 mb-1">Total Keluar</p>
        <p class="text-3xl font-bold text-rose-500">{{ number_format($totalOut, 2, ',', '.') }}</p>
        <p class="text-sm text-slate-400 mt-0.5">{{ $material->unit?->name }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

    {{-- Chart --}}
    <x-data-card title="Gerakan Stok 6 Bulan Terakhir" class="lg:col-span-2">
        <div class="px-5 pb-5">
            @if (array_sum($chartIn) > 0 || array_sum($chartOut) > 0)
                <canvas id="stockChart" height="100"></canvas>
            @else
                <div class="flex flex-col items-center justify-center py-10 text-center">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941"/>
                        </svg>
                    </div>
                    <p class="mt-2 text-sm text-slate-400">Belum ada gerakan dalam 6 bulan terakhir</p>
                </div>
            @endif
        </div>
    </x-data-card>

    {{-- Right: Min + Info --}}
    <div class="space-y-5">
        <x-data-card title="Stok Minimum Alert">
            <form method="POST" action="{{ route('stock.set-minimum', $material) }}" class="px-5 py-4 space-y-3">
                @csrf
                <div>
                    <label class="form-label">Batas Minimum ({{ $material->unit?->name }})</label>
                    <input type="number" name="minimum_stock" step="0.01" min="0"
                           value="{{ $material->minimum_stock }}"
                           placeholder="Kosong = tidak ada alert"
                           class="form-input">
                </div>
                <button type="submit" class="btn-secondary w-full justify-center text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Simpan
                </button>
            </form>
        </x-data-card>

        <x-data-card title="Info Material">
            <div class="px-5 py-4 space-y-2 text-xs">
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Kode</span>
                    <span class="font-mono font-medium text-slate-700">{{ $material->code ?: '—' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Kategori</span>
                    <span class="font-medium text-slate-700">{{ $material->category?->name ?: '—' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Satuan</span>
                    <span class="font-medium text-slate-700">{{ $material->unit?->name ?: '—' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Harga Default</span>
                    <span class="font-semibold text-slate-800">Rp {{ number_format($material->default_price, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Min. Stok</span>
                    <span class="font-medium {{ $isLow ? 'text-amber-600' : 'text-slate-700' }}">
                        {{ $material->minimum_stock !== null ? number_format($material->minimum_stock, 2, ',', '.') . ' ' . $material->unit?->name : '—' }}
                    </span>
                </div>
            </div>
        </x-data-card>
    </div>
</div>

{{-- Filter --}}
<x-data-card class="mb-5">
    <div class="px-5 py-4">
        <form method="GET" action="{{ route('stock.show', $material) }}" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[150px]">
                <label class="form-label">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="form-input">
            </div>
            <div class="flex-1 min-w-[150px]">
                <label class="form-label">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="form-input">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary">Filter</button>
                <a href="{{ route('stock.show', $material) }}" class="btn-secondary">Reset</a>
            </div>
        </form>
    </div>
</x-data-card>

{{-- Riwayat Gerakan --}}
<x-data-card title="Riwayat Gerakan Stok" subtitle="{{ $movements->total() }} gerakan tercatat">
    @if ($movements->isEmpty())
        <div class="flex flex-col items-center justify-center py-12 text-center">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941"/>
                </svg>
            </div>
            <p class="mt-2 text-sm font-medium text-slate-700">Belum ada gerakan stok</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th class="th-center">Jenis</th>
                        <th class="th-right">Qty</th>
                        <th>Proyek</th>
                        <th>Sumber</th>
                        <th>Catatan</th>
                        <th>Dicatat Oleh</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($movements as $mv)
                        @php
                            $isIn = $mv->movement_type !== 'out';
                            $typeLabel = match($mv->movement_type) {
                                'in'  => 'Masuk',
                                'out' => 'Keluar',
                                default => 'Koreksi',
                            };
                            $typeBadge = match($mv->movement_type) {
                                'out'        => 'badge-danger',
                                'adjustment' => 'badge-neutral',
                                default      => 'badge-success',
                            };
                            $sourceLabel = match($mv->reference_type ?? '') {
                                'goods_receipt'        => 'Surat Jalan',
                                'purchase_transaction' => 'Transaksi',
                                'manual_in'            => 'Manual Masuk',
                                'manual_out'           => 'Manual Keluar',
                                default                => 'Koreksi',
                            };
                        @endphp
                        <tr>
                            <td class="text-slate-600">{{ $mv->movement_date->format('d M Y') }}</td>
                            <td class="text-center">
                                <span class="{{ $typeBadge }}">{{ $typeLabel }}</span>
                            </td>
                            <td class="text-right font-bold {{ $isIn ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $isIn ? '+' : '−' }}{{ number_format($mv->qty, 2, ',', '.') }}
                                <span class="text-xs text-slate-400 font-normal">{{ $mv->unit_name_snapshot }}</span>
                            </td>
                            <td class="text-slate-500 text-xs">{{ $mv->project?->name ?? '—' }}</td>
                            <td>
                                <span class="chip">{{ $sourceLabel }}</span>
                            </td>
                            <td class="text-slate-400 text-xs max-w-[180px] truncate">{{ $mv->notes ?: '—' }}</td>
                            <td class="text-slate-400 text-xs">{{ $mv->creator?->name ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-5 py-3">
            {{ $movements->links() }}
        </div>
    @endif
</x-data-card>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
@if (array_sum($chartIn) > 0 || array_sum($chartOut) > 0)
(function () {
    new Chart(document.getElementById('stockChart'), {
        type: 'bar',
        data: {
            labels: @json($chartLabels),
            datasets: [
                {
                    label: 'Masuk',
                    data: @json($chartIn),
                    backgroundColor: 'rgba(16, 185, 129, 0.2)',
                    borderColor: 'rgba(16, 185, 129, 0.8)',
                    borderWidth: 2,
                    borderRadius: 5,
                },
                {
                    label: 'Keluar',
                    data: @json($chartOut),
                    backgroundColor: 'rgba(244, 63, 94, 0.15)',
                    borderColor: 'rgba(244, 63, 94, 0.7)',
                    borderWidth: 2,
                    borderRadius: 5,
                },
            ],
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top', labels: { font: { size: 11 } } },
                tooltip: { callbacks: { label: ctx => ctx.dataset.label + ': ' + ctx.parsed.y.toLocaleString('id-ID') } },
            },
            scales: {
                y: { beginAtZero: true, ticks: { font: { size: 11 } }, grid: { color: 'rgba(0,0,0,0.04)' } },
                x: { ticks: { font: { size: 11 } }, grid: { display: false } },
            },
        },
    });
})();
@endif
</script>
@endpush
