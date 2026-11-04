@extends('layouts.app')

@section('content')

<x-page-header
    title="Riwayat Harga — {{ $supplier->name }}"
    subtitle="Histori harga material dari supplier ini berdasarkan transaksi pembelian">
    <x-slot name="actions">
        <a href="{{ route('suppliers.show', $supplier) }}" class="btn-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
            </svg>
            Kembali ke Detail Supplier
        </a>
    </x-slot>
</x-page-header>

{{-- Info Supplier --}}
<div class="mb-5 grid grid-cols-2 gap-4 sm:grid-cols-4">
    <div class="rounded-[22px] border border-slate-200 bg-white p-4 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Supplier</p>
        <p class="mt-1.5 font-bold text-slate-900">{{ $supplier->name }}</p>
    </div>
    <div class="rounded-[22px] border border-slate-200 bg-white p-4 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Kota</p>
        <p class="mt-1.5 font-semibold text-slate-700">{{ $supplier->city ?: '-' }}</p>
    </div>
    <div class="rounded-[22px] border border-slate-200 bg-white p-4 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Telepon</p>
        <p class="mt-1.5 font-semibold text-slate-700">{{ $supplier->phone ?: '-' }}</p>
    </div>
    <div class="rounded-[22px] border border-slate-200 bg-white p-4 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Material Tercatat</p>
        <p class="mt-1.5 text-2xl font-extrabold text-indigo-600">{{ $summary->total() }}</p>
    </div>
</div>

{{-- Search --}}
<x-data-card class="mb-5">
    <form method="GET" action="{{ route('suppliers.price-history', $supplier) }}" class="flex items-center gap-3 px-5 py-4 flex-wrap">
        <div class="flex-1 min-w-[200px]">
            <label class="form-label text-xs">Cari Material</label>
            <input type="text" name="q" value="{{ request('q') }}"
                   placeholder="Cari nama material..."
                   class="form-input">
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z"/>
                </svg>
                Cari
            </button>
            @if(request('q'))
                <a href="{{ route('suppliers.price-history', $supplier) }}" class="btn-secondary">Reset</a>
            @endif
        </div>
    </form>
</x-data-card>

{{-- Tabel Riwayat Harga --}}
<x-data-card>
    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
        <h3 class="text-sm font-bold text-slate-800">Riwayat Harga per Material</h3>
        <span class="text-xs text-slate-400">{{ $summary->total() }} material</span>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="border-b border-slate-100 bg-slate-50/80">
                <tr>
                    <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-[0.18em] text-slate-500">Material</th>
                    <th class="px-5 py-4 text-right text-xs font-bold uppercase tracking-[0.18em] text-slate-500">Harga Terbaru</th>
                    <th class="px-5 py-4 text-right text-xs font-bold uppercase tracking-[0.18em] text-slate-500">Harga Terendah</th>
                    <th class="px-5 py-4 text-right text-xs font-bold uppercase tracking-[0.18em] text-slate-500">Harga Tertinggi</th>
                    <th class="px-5 py-4 text-center text-xs font-bold uppercase tracking-[0.18em] text-slate-500">Tren 6 Bulan</th>
                    <th class="px-5 py-4 text-center text-xs font-bold uppercase tracking-[0.18em] text-slate-500">Catatan</th>
                    <th class="px-5 py-4 text-center text-xs font-bold uppercase tracking-[0.18em] text-slate-500">Terakhir</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($summary as $row)
                @php
                    $latest       = $latestPrices->get($row->material_name_snapshot);
                    $latestPrice  = $latest ? (float) $latest->price : 0;
                    $trendPoints  = $trends->get($row->material_name_snapshot, collect());

                    // Hitung apakah harga naik/turun dari catatan sebelumnya
                    $priceChange  = null;
                    if ($trendPoints->count() >= 2) {
                        $first = (float) $trendPoints->first()->price;
                        $last  = (float) $trendPoints->last()->price;
                        $priceChange = $last > $first ? 'up' : ($last < $first ? 'down' : 'flat');
                    }

                    // Sparkline data (array harga)
                    $sparkData = $trendPoints->pluck('price')->map(fn($p) => (float)$p)->values()->toJson();
                @endphp
                <tr class="hover:bg-slate-50/80 transition-colors" x-data>
                    <td class="px-5 py-4">
                        <div class="font-semibold text-slate-900">{{ $row->material_name_snapshot }}</div>
                        <div class="mt-0.5 text-xs text-slate-400">{{ $row->unit_name_snapshot }}</div>
                    </td>

                    {{-- Harga Terbaru --}}
                    <td class="px-5 py-4 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            @if($priceChange === 'up')
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5 12 3m0 0 7.5 7.5M12 3v18"/>
                                </svg>
                            @elseif($priceChange === 'down')
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5 12 21m0 0-7.5-7.5M12 21V3"/>
                                </svg>
                            @endif
                            <span class="font-bold text-slate-900">
                                Rp {{ number_format($latestPrice, 0, ',', '.') }}
                            </span>
                        </div>
                    </td>

                    {{-- Min --}}
                    <td class="px-5 py-4 text-right text-emerald-600 font-medium">
                        Rp {{ number_format($row->price_min, 0, ',', '.') }}
                    </td>

                    {{-- Max --}}
                    <td class="px-5 py-4 text-right text-rose-600 font-medium">
                        Rp {{ number_format($row->price_max, 0, ',', '.') }}
                    </td>

                    {{-- Sparkline --}}
                    <td class="px-5 py-4">
                        <div class="flex items-center justify-center">
                            @if($trendPoints->count() > 1)
                                <canvas
                                    class="sparkline-chart"
                                    data-prices="{{ $sparkData }}"
                                    width="80" height="32"
                                    title="Tren harga 6 bulan terakhir">
                                </canvas>
                            @else
                                <span class="text-xs text-slate-300">—</span>
                            @endif
                        </div>
                    </td>

                    {{-- Jumlah Catatan --}}
                    <td class="px-5 py-4 text-center">
                        <span class="inline-flex items-center justify-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">
                            {{ $row->record_count }}x
                        </span>
                    </td>

                    {{-- Tanggal terakhir --}}
                    <td class="px-5 py-4 text-center text-xs text-slate-500">
                        {{ \Carbon\Carbon::parse($row->last_recorded_at)->format('d M Y') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-5 py-16 text-center text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto mb-3 h-10 w-10 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z"/>
                        </svg>
                        <p class="font-medium">Belum ada riwayat harga</p>
                        <p class="mt-1 text-xs">Data harga akan otomatis tercatat saat transaksi pembelian dari supplier ini dibuat</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($summary->hasPages())
    <div class="border-t border-slate-100 px-5 py-4">
        {{ $summary->links() }}
    </div>
    @endif
</x-data-card>

@endsection

@push('scripts')
<script>
// Sparkline mini chart menggunakan Canvas API
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.sparkline-chart').forEach(function (canvas) {
        const prices = JSON.parse(canvas.dataset.prices || '[]');
        if (prices.length < 2) return;

        const ctx   = canvas.getContext('2d');
        const w     = canvas.width;
        const h     = canvas.height;
        const pad   = 3;
        const min   = Math.min(...prices);
        const max   = Math.max(...prices);
        const range = max - min || 1;

        const toX = (i) => pad + (i / (prices.length - 1)) * (w - pad * 2);
        const toY = (v) => h - pad - ((v - min) / range) * (h - pad * 2);

        // Determine color: last > first = rose (naik), else emerald (turun/flat)
        const isUp = prices[prices.length - 1] > prices[0];
        const lineColor = isUp ? '#f43f5e' : '#10b981';
        const fillColor = isUp ? 'rgba(244,63,94,0.08)' : 'rgba(16,185,129,0.08)';

        // Fill area
        ctx.beginPath();
        ctx.moveTo(toX(0), h);
        prices.forEach((p, i) => ctx.lineTo(toX(i), toY(p)));
        ctx.lineTo(toX(prices.length - 1), h);
        ctx.closePath();
        ctx.fillStyle = fillColor;
        ctx.fill();

        // Line
        ctx.beginPath();
        prices.forEach((p, i) => {
            if (i === 0) ctx.moveTo(toX(i), toY(p));
            else ctx.lineTo(toX(i), toY(p));
        });
        ctx.strokeStyle = lineColor;
        ctx.lineWidth   = 1.8;
        ctx.lineJoin    = 'round';
        ctx.stroke();

        // Last point dot
        const lastX = toX(prices.length - 1);
        const lastY = toY(prices[prices.length - 1]);
        ctx.beginPath();
        ctx.arc(lastX, lastY, 2.5, 0, Math.PI * 2);
        ctx.fillStyle = lineColor;
        ctx.fill();
    });
});
</script>
@endpush
