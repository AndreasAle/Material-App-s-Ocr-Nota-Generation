@extends('layouts.app')

@section('content')
<x-page-header
    title="Dokumen Lain PPN"
    subtitle="Kelola dokumen selain Faktur Pajak yang dapat dikreditkan/dilaporkan sebagai PPN"
>
    <x-slot name="actions">
        <a href="{{ route('tax.dok-lain.import.masukan') }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/>
            </svg>
            Import Masukan
        </a>
        <a href="{{ route('tax.dok-lain.import.keluaran') }}" class="btn-secondary">
            Import Keluaran
        </a>
    </x-slot>
</x-page-header>

{{-- Filter --}}
<form method="GET" class="flex flex-wrap gap-3 mb-6">
    <div>
        <label class="form-label">Masa Pajak</label>
        <input type="month" name="masa" value="{{ $masaFilter }}" class="form-input">
    </div>
    <div class="flex items-end">
        <button type="submit" class="btn-secondary">Filter</button>
    </div>
    <div class="flex items-end gap-2">
        <a href="{{ route('tax.dok-lain.export.masukan', ['masa_pajak' => $masaFilter, 'npwp' => '', 'nama' => '']) }}"
           class="btn-secondary text-xs">
            ↓ Export Masukan
        </a>
        <a href="{{ route('tax.dok-lain.export.keluaran', ['masa_pajak' => $masaFilter, 'npwp' => '', 'nama' => '']) }}"
           class="btn-secondary text-xs">
            ↓ Export Keluaran
        </a>
    </div>
</form>

{{-- Totals --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4">
        <p class="text-xs text-blue-600 font-medium">PPN Masukan (Dok Lain)</p>
        <p class="text-2xl font-bold text-blue-700 mt-1">Rp {{ number_format($totalMasukan, 0, ',', '.') }}</p>
        <p class="text-xs text-blue-400 mt-1">{{ $masukan->count() }} dokumen</p>
    </div>
    <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4">
        <p class="text-xs text-emerald-600 font-medium">PPN Keluaran (Dok Lain)</p>
        <p class="text-2xl font-bold text-emerald-700 mt-1">Rp {{ number_format($totalKeluaran, 0, ',', '.') }}</p>
        <p class="text-xs text-emerald-400 mt-1">{{ $keluaran->count() }} dokumen</p>
    </div>
    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4">
        <p class="text-xs text-slate-600 font-medium">Selisih (Kurang Bayar/Lebih Bayar)</p>
        @php $selisih = $totalKeluaran - $totalMasukan; @endphp
        <p class="text-2xl font-bold {{ $selisih >= 0 ? 'text-rose-700' : 'text-emerald-700' }} mt-1">
            Rp {{ number_format(abs($selisih), 0, ',', '.') }}
        </p>
        <p class="text-xs text-slate-400 mt-1">{{ $selisih >= 0 ? 'Kurang Bayar' : 'Lebih Bayar' }}</p>
    </div>
</div>

{{-- Tabs --}}
<div x-data="{ tab: '{{ $tab }}' }">
    <div class="flex gap-1 mb-4 bg-slate-100 p-1 rounded-xl w-fit">
        <button @click="tab='masukan'" :class="tab==='masukan' ? 'bg-white shadow text-indigo-700 font-semibold' : 'text-slate-500'"
                class="px-4 py-2 rounded-lg text-sm transition-all">
            PPN Masukan ({{ $masukan->count() }})
        </button>
        <button @click="tab='keluaran'" :class="tab==='keluaran' ? 'bg-white shadow text-indigo-700 font-semibold' : 'text-slate-500'"
                class="px-4 py-2 rounded-lg text-sm transition-all">
            PPN Keluaran ({{ $keluaran->count() }})
        </button>
    </div>

    {{-- Masukan Tab --}}
    <div x-show="tab === 'masukan'">
        <x-data-card>
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No Faktur/Dok</th>
                            <th>Tgl Faktur</th>
                            <th>Kode Transaksi</th>
                            <th>Nama Penjual</th>
                            <th class="text-right">DPP</th>
                            <th class="text-right">PPN</th>
                            <th class="text-right">PPnBM</th>
                            <th>Sumber</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($masukan as $r)
                        <tr>
                            <td class="font-mono text-xs">{{ $r->no_faktur ?? '—' }}</td>
                            <td>{{ $r->tanggal_faktur?->format('d/m/Y') }}</td>
                            <td>
                                @if($r->kode_transaksi)
                                    <span class="font-mono text-xs bg-slate-100 px-1.5 py-0.5 rounded">
                                        {{ $r->kode_transaksi }}
                                        @if(isset($kodeTransaksiMasukan[$r->kode_transaksi]))
                                            <span class="font-sans text-slate-500">— {{ Str::limit($kodeTransaksiMasukan[$r->kode_transaksi], 25) }}</span>
                                        @endif
                                    </span>
                                @else
                                    <span class="text-slate-400 text-xs">—</span>
                                @endif
                            </td>
                            <td>{{ $r->nama_penjual }}</td>
                            <td class="text-right font-mono">{{ number_format($r->dpp, 0, ',', '.') }}</td>
                            <td class="text-right font-mono font-semibold text-blue-700">{{ number_format($r->ppn, 0, ',', '.') }}</td>
                            <td class="text-right font-mono text-slate-500">{{ $r->ppnbm > 0 ? number_format($r->ppnbm, 0, ',', '.') : '—' }}</td>
                            <td>
                                @if($r->sumber === 'import_excel')
                                    <span class="badge badge-indigo">Import</span>
                                @else
                                    <span class="text-xs text-slate-400">Manual</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center py-6 text-slate-400">Tidak ada data masukan untuk periode ini</td></tr>
                        @endforelse
                    </tbody>
                    @if($masukan->count() > 0)
                    <tfoot>
                        <tr class="font-semibold bg-slate-50">
                            <td colspan="4" class="text-right text-sm">Total</td>
                            <td class="text-right font-mono">Rp {{ number_format($masukan->sum('dpp'), 0, ',', '.') }}</td>
                            <td class="text-right font-mono text-blue-700">Rp {{ number_format($totalMasukan, 0, ',', '.') }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </x-data-card>
    </div>

    {{-- Keluaran Tab --}}
    <div x-show="tab === 'keluaran'">
        <x-data-card>
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No Faktur/Dok</th>
                            <th>Tgl Faktur</th>
                            <th>Kode Transaksi</th>
                            <th>Nama Pembeli</th>
                            <th class="text-right">DPP</th>
                            <th class="text-right">PPN</th>
                            <th class="text-right">PPnBM</th>
                            <th>Sumber</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($keluaran as $r)
                        <tr>
                            <td class="font-mono text-xs">{{ $r->no_faktur ?? '—' }}</td>
                            <td>{{ $r->tanggal_faktur?->format('d/m/Y') }}</td>
                            <td>
                                @if($r->kode_transaksi)
                                    <span class="font-mono text-xs bg-slate-100 px-1.5 py-0.5 rounded">{{ $r->kode_transaksi }}</span>
                                @else
                                    <span class="text-slate-400 text-xs">—</span>
                                @endif
                            </td>
                            <td>{{ $r->nama_pembeli }}</td>
                            <td class="text-right font-mono">{{ number_format($r->dpp, 0, ',', '.') }}</td>
                            <td class="text-right font-mono font-semibold text-emerald-700">{{ number_format($r->ppn, 0, ',', '.') }}</td>
                            <td class="text-right font-mono text-slate-500">{{ $r->ppnbm > 0 ? number_format($r->ppnbm, 0, ',', '.') : '—' }}</td>
                            <td>
                                @if($r->sumber === 'import_excel')
                                    <span class="badge badge-indigo">Import</span>
                                @else
                                    <span class="text-xs text-slate-400">Manual</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center py-6 text-slate-400">Tidak ada data keluaran untuk periode ini</td></tr>
                        @endforelse
                    </tbody>
                    @if($keluaran->count() > 0)
                    <tfoot>
                        <tr class="font-semibold bg-slate-50">
                            <td colspan="4" class="text-right text-sm">Total</td>
                            <td class="text-right font-mono">Rp {{ number_format($keluaran->sum('dpp'), 0, ',', '.') }}</td>
                            <td class="text-right font-mono text-emerald-700">Rp {{ number_format($totalKeluaran, 0, ',', '.') }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </x-data-card>
    </div>
</div>

@endsection
