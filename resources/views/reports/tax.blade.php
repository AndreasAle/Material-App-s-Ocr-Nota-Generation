@extends('layouts.app')

@section('title', 'Rekap Pajak')

@section('content')

<x-page-header title="Rekap Pajak" subtitle="Ringkasan PPN Masukan, PPN Keluaran, PPH Final & PPH 23 per periode">
    <x-slot name="actions">
        <a href="{{ route('tax-report.export-excel', request()->query()) }}"
           class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 transition-colors">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
            </svg>
            Export Excel
        </a>
    </x-slot>
</x-page-header>

{{-- Period Filter --}}
<form method="GET" class="flex items-center gap-3 flex-wrap mb-6">
    <select name="year" class="rounded-lg border border-slate-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        @for($y = now()->year; $y >= now()->year - 3; $y--)
            <option value="{{ $y }}" @selected($year == $y)>{{ $y }}</option>
        @endfor
    </select>
    <select name="month" class="rounded-lg border border-slate-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $i => $nm)
            <option value="{{ $i+1 }}" @selected($month == $i+1)>{{ $nm }}</option>
        @endforeach
    </select>
    <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700">Tampilkan</button>
</form>

{{-- Summary Cards --}}
<div class="grid grid-cols-2 gap-4 sm:grid-cols-5 mb-6">
    <x-data-card>
        <div class="p-4 text-center">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">PPN Masukan</p>
            <p class="mt-1 text-xl font-extrabold text-blue-700">Rp {{ number_format($report['ppn_masukan_total'], 0, ',', '.') }}</p>
            <p class="text-xs text-slate-400 mt-1">Pajak dari pembelian</p>
        </div>
    </x-data-card>
    <x-data-card>
        <div class="p-4 text-center">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">PPN Keluaran</p>
            <p class="mt-1 text-xl font-extrabold text-indigo-700">Rp {{ number_format($report['ppn_keluaran_total'], 0, ',', '.') }}</p>
            <p class="text-xs text-slate-400 mt-1">Pajak dari invoice</p>
        </div>
    </x-data-card>
    <x-data-card>
        @php $netPpn = $report['net_ppn']; @endphp
        <div class="p-4 text-center">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Net PPN</p>
            <p class="mt-1 text-xl font-extrabold {{ $netPpn > 0 ? 'text-red-600' : 'text-emerald-600' }}">
                Rp {{ number_format(abs($netPpn), 0, ',', '.') }}
            </p>
            <p class="text-xs {{ $netPpn > 0 ? 'text-red-400' : 'text-emerald-400' }} mt-1">
                {{ $netPpn > 0 ? '▲ Kurang Bayar' : '▼ Lebih Bayar' }}
            </p>
        </div>
    </x-data-card>
    <x-data-card>
        <div class="p-4 text-center">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">PPH Final</p>
            <p class="mt-1 text-xl font-extrabold text-orange-600">Rp {{ number_format($report['pph_final_total'], 0, ',', '.') }}</p>
            <p class="text-xs text-slate-400 mt-1">Jasa konstruksi</p>
        </div>
    </x-data-card>
    <x-data-card>
        <div class="p-4 text-center">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">PPH 23</p>
            <p class="mt-1 text-xl font-extrabold text-purple-600">Rp {{ number_format($report['pph23_total'], 0, ',', '.') }}</p>
            <p class="text-xs text-slate-400 mt-1">Jasa lainnya</p>
        </div>
    </x-data-card>
</div>

{{-- Net PPN Info --}}
@if($netPpn > 0)
<div class="mb-5 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700 font-medium">
    ⚠ PPN Kurang Bayar: PPN Keluaran (Rp {{ number_format($report['ppn_keluaran_total'], 0, ',', '.') }}) lebih besar dari PPN Masukan (Rp {{ number_format($report['ppn_masukan_total'], 0, ',', '.') }}). Selisih Rp {{ number_format(abs($netPpn), 0, ',', '.') }} harus disetor ke kas negara.
</div>
@elseif($netPpn < 0)
<div class="mb-5 rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-700 font-medium">
    ✓ PPN Lebih Bayar: PPN Masukan lebih besar Rp {{ number_format(abs($netPpn), 0, ',', '.') }}. Dapat dikreditkan ke periode berikutnya.
</div>
@endif

{{-- PPN Masukan --}}
<x-data-card title="PPN Masukan (Pajak Input dari Pembelian)" subtitle="Periode: {{ $dateFrom }} s/d {{ $dateTo }}" class="mb-5">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50/60">
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 text-xs uppercase">Tanggal</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 text-xs uppercase">No. Faktur Pajak</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 text-xs uppercase">Supplier</th>
                    <th class="px-4 py-3 text-right font-semibold text-slate-600 text-xs uppercase">DPP</th>
                    <th class="px-4 py-3 text-right font-semibold text-slate-600 text-xs uppercase">PPN (11%)</th>
                    <th class="px-4 py-3 text-center font-semibold text-slate-600 text-xs uppercase">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($report['ppn_masukan'] as $row)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-2.5 text-slate-500 text-xs whitespace-nowrap">{{ \Carbon\Carbon::parse($row->tanggal_faktur)->format('d/m/Y') }}</td>
                    <td class="px-4 py-2.5 font-mono font-medium text-slate-700">{{ $row->no_faktur ?? '—' }}</td>
                    <td class="px-4 py-2.5 text-slate-700">{{ $row->purchaseTransaction?->supplier?->name ?? '—' }}</td>
                    <td class="px-4 py-2.5 text-right text-slate-700">Rp {{ number_format($row->dpp ?? 0, 0, ',', '.') }}</td>
                    <td class="px-4 py-2.5 text-right font-bold text-blue-700">Rp {{ number_format($row->ppn ?? 0, 0, ',', '.') }}</td>
                    <td class="px-4 py-2.5 text-center">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $row->status === 'dikreditkan' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                            {{ $row->status ?? 'Belum dikreditkan' }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-slate-400">Tidak ada PPN Masukan periode ini.</td></tr>
                @endforelse
                @if($report['ppn_masukan']->isNotEmpty())
                <tr class="bg-blue-50 font-bold border-t-2 border-blue-200">
                    <td colspan="4" class="px-4 py-3 text-right text-slate-700 font-extrabold">Total PPN Masukan</td>
                    <td class="px-4 py-3 text-right text-blue-800 font-extrabold">Rp {{ number_format($report['ppn_masukan_total'], 0, ',', '.') }}</td>
                    <td></td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</x-data-card>

{{-- PPN Keluaran --}}
<x-data-card title="PPN Keluaran (Pajak Output dari Invoice)" subtitle="Periode: {{ $dateFrom }} s/d {{ $dateTo }}" class="mb-5">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50/60">
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 text-xs uppercase">Tanggal</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 text-xs uppercase">No. Faktur Pajak</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600 text-xs uppercase">Klien</th>
                    <th class="px-4 py-3 text-right font-semibold text-slate-600 text-xs uppercase">DPP</th>
                    <th class="px-4 py-3 text-right font-semibold text-slate-600 text-xs uppercase">PPN</th>
                    <th class="px-4 py-3 text-center font-semibold text-slate-600 text-xs uppercase">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($report['ppn_keluaran'] as $row)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-2.5 text-slate-500 text-xs whitespace-nowrap">{{ \Carbon\Carbon::parse($row->tanggal_faktur)->format('d/m/Y') }}</td>
                    <td class="px-4 py-2.5 font-mono font-medium text-slate-700">{{ $row->no_faktur ?? '—' }}</td>
                    <td class="px-4 py-2.5 text-slate-700">{{ $row->invoice?->client_name ?? '—' }}</td>
                    <td class="px-4 py-2.5 text-right text-slate-700">Rp {{ number_format($row->dpp ?? 0, 0, ',', '.') }}</td>
                    <td class="px-4 py-2.5 text-right font-bold text-indigo-700">Rp {{ number_format($row->ppn ?? 0, 0, ',', '.') }}</td>
                    <td class="px-4 py-2.5 text-center">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $row->status === 'dilaporkan' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                            {{ $row->status ?? 'Belum dilaporkan' }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-slate-400">Tidak ada PPN Keluaran periode ini.</td></tr>
                @endforelse
                @if($report['ppn_keluaran']->isNotEmpty())
                <tr class="bg-indigo-50 font-bold border-t-2 border-indigo-200">
                    <td colspan="4" class="px-4 py-3 text-right text-slate-700 font-extrabold">Total PPN Keluaran</td>
                    <td class="px-4 py-3 text-right text-indigo-800 font-extrabold">Rp {{ number_format($report['ppn_keluaran_total'], 0, ',', '.') }}</td>
                    <td></td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</x-data-card>

{{-- Tren Bulanan Chart --}}
<x-data-card title="Tren Pajak per Bulan — {{ $year }}">
    <div class="p-4">
        <canvas id="taxChart" style="max-height:240px;"></canvas>
    </div>
</x-data-card>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('taxChart'), {
    type: 'bar',
    data: {
        labels: ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'],
        datasets: [
            {
                label: 'PPN Masukan',
                data: @json(array_column($monthlySummary, 'ppn_masukan_total')),
                backgroundColor: 'rgba(59,130,246,0.7)',
                borderRadius: 4
            },
            {
                label: 'PPN Keluaran',
                data: @json(array_column($monthlySummary, 'ppn_keluaran_total')),
                backgroundColor: 'rgba(99,102,241,0.7)',
                borderRadius: 4
            },
            {
                label: 'PPH Final',
                data: @json(array_column($monthlySummary, 'pph_final_total')),
                backgroundColor: 'rgba(249,115,22,0.7)',
                borderRadius: 4
            },
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: {
            y: {
                ticks: {
                    callback: v => 'Rp ' + (v / 1e6).toFixed(1) + 'jt'
                }
            }
        }
    }
});
</script>
@endpush
