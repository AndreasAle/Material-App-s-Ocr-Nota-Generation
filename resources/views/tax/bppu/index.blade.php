@extends('layouts.app')

@section('content')
<x-page-header
    title="BPPU — Bukti Pemotongan PPh Unifikasi"
    subtitle="Kelola PPh Final 4(2) & PPh 23 yang dipotong pihak lain — siap upload ke Coretax DJP"
>
    <x-slot name="actions">
        <a href="{{ route('tax.bppu.import') }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/>
            </svg>
            Import Excel DJP
        </a>
        <a href="{{ route('tax.bppu.export') }}" class="btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
            </svg>
            Export ke Coretax
        </a>
    </x-slot>
</x-page-header>

{{-- ═══ Tracker SPT Masa ═══════════════════════════════════════════════════ --}}
<x-data-card class="mb-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-semibold text-slate-700">Tracker SPT Masa PPh Unifikasi {{ $tahun }}</h3>
        <div class="flex gap-2">
            <a href="{{ route('tax.bppu.index', ['tahun' => $tahun - 1]) }}" class="text-xs text-slate-500 hover:text-slate-700">← {{ $tahun - 1 }}</a>
            <span class="text-xs font-bold text-indigo-600">{{ $tahun }}</span>
            <a href="{{ route('tax.bppu.index', ['tahun' => $tahun + 1]) }}" class="text-xs text-slate-500 hover:text-slate-700">{{ $tahun + 1 }} →</a>
        </div>
    </div>
    <div class="grid grid-cols-6 sm:grid-cols-12 gap-1.5">
        @php $bulan = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des']; @endphp
        @foreach($rekapMasa as $masa => $info)
            @php
                $m = (int) substr($masa, 5, 2);
                $isActive = $masaFilter === $masa;
                $color = $info['has_data'] ? 'bg-emerald-100 border-emerald-300 text-emerald-700' : 'bg-slate-100 border-slate-200 text-slate-400';
                if ($isActive) $color = 'bg-indigo-600 border-indigo-600 text-white';
            @endphp
            <a href="{{ route('tax.bppu.index', ['tahun' => $tahun, 'masa' => $masa]) }}"
               class="rounded-lg border p-2 text-center hover:border-indigo-400 transition-colors {{ $color }}">
                <div class="text-xs font-bold">{{ $bulan[$m] }}</div>
                @if($info['has_data'])
                    <div class="text-xs mt-0.5">{{ $info['total_count'] }} dok</div>
                @else
                    <div class="text-xs mt-0.5 opacity-50">—</div>
                @endif
            </a>
        @endforeach
    </div>
</x-data-card>

{{-- ═══ Summary Totals ══════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4">
        <p class="text-xs text-blue-600 font-medium">PPh Final 4(2)</p>
        <p class="text-2xl font-bold text-blue-700 mt-1">Rp {{ number_format($totalFinal, 0, ',', '.') }}</p>
        <p class="text-xs text-blue-500 mt-1">{{ $masaFilter ?? 'Tahun ' . $tahun }}</p>
    </div>
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4">
        <p class="text-xs text-amber-600 font-medium">PPh Pasal 23</p>
        <p class="text-2xl font-bold text-amber-700 mt-1">Rp {{ number_format($totalPph23, 0, ',', '.') }}</p>
        <p class="text-xs text-amber-500 mt-1">{{ $masaFilter ?? 'Tahun ' . $tahun }}</p>
    </div>
    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4">
        <p class="text-xs text-slate-600 font-medium">Total PPh Dipotong</p>
        <p class="text-2xl font-bold text-slate-700 mt-1">Rp {{ number_format($totalFinal + $totalPph23, 0, ',', '.') }}</p>
        <p class="text-xs text-slate-500 mt-1">Klaim kredit pajak</p>
    </div>
</div>

{{-- ═══ Anomali Alert ═══════════════════════════════════════════════════════ --}}
@if(count($anomali) > 0)
<div class="alert alert-warning mb-6">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
    </svg>
    <span>
        <strong>{{ count($anomali) }} anomali ditemukan</strong> — PPh dipotong ≠ DPP × Tarif.
        Periksa sebelum upload ke Coretax.
        <ul class="mt-2 list-disc ml-4 text-xs">
            @foreach(array_slice($anomali, 0, 5) as $a)
            <li>{{ $a['type'] }} — {{ $a['nama'] }}: Rp {{ number_format($a['actual'], 0, ',', '.') }} (harusnya Rp {{ number_format($a['expected'], 0, ',', '.') }}, selisih {{ $a['selisih'] > 0 ? '+' : '' }}{{ number_format($a['selisih'], 0, ',', '.') }})</li>
            @endforeach
        </ul>
    </span>
</div>
@endif

{{-- ═══ Filter Bar ══════════════════════════════════════════════════════════ --}}
<form method="GET" class="flex flex-wrap gap-3 mb-4">
    <input type="hidden" name="tahun" value="{{ $tahun }}">
    <div>
        <label class="form-label">Masa Pajak</label>
        <input type="month" name="masa" value="{{ $masaFilter }}" class="form-input">
    </div>
    <div>
        <label class="form-label">Jenis</label>
        <select name="jenis" class="form-select">
            <option value="all"   {{ $jenis === 'all'   ? 'selected' : '' }}>Semua (PPh Final + PPh 23)</option>
            <option value="final" {{ $jenis === 'final' ? 'selected' : '' }}>PPh Final 4(2)</option>
            <option value="pph23" {{ $jenis === 'pph23' ? 'selected' : '' }}>PPh Pasal 23</option>
        </select>
    </div>
    <div class="flex items-end">
        <button type="submit" class="btn-secondary">Filter</button>
    </div>
</form>

{{-- ═══ Tabel Records ═══════════════════════════════════════════════════════ --}}
<x-data-card>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>No Bukpot</th>
                    <th>Nama Pemotong/Penerima</th>
                    <th>KOP</th>
                    <th>Jenis</th>
                    <th class="text-right">DPP/Bruto</th>
                    <th class="text-right">Tarif</th>
                    <th class="text-right">PPh Dipotong</th>
                    <th>Status</th>
                    <th>Sumber</th>
                </tr>
            </thead>
            <tbody>
                @forelse($query as $r)
                <tr>
                    <td class="font-mono text-xs">{{ $r->no_bukti_potong ?? '—' }}</td>
                    <td>
                        @if($r->_type === 'final')
                            {{ $r->nama_pemotong }}
                            @if($r->npwp_pemotong)
                                <span class="block text-xs text-slate-400 font-mono">{{ $r->npwp_pemotong }}</span>
                            @endif
                        @else
                            {{ $r->nama_penerima }}
                            @if($r->npwp_penerima)
                                <span class="block text-xs text-slate-400 font-mono">{{ $r->npwp_penerima }}</span>
                            @endif
                        @endif
                    </td>
                    <td>
                        @if($r->kode_objek_pajak)
                            <span class="font-mono text-xs bg-slate-100 px-1.5 py-0.5 rounded">{{ $r->kode_objek_pajak }}</span>
                        @else
                            <span class="text-slate-400">—</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $r->_type === 'final' ? 'badge-blue' : 'badge-amber' }}">
                            {{ $r->_tarif_label }}
                        </span>
                    </td>
                    <td class="text-right font-mono">Rp {{ number_format($r->_dpp, 0, ',', '.') }}</td>
                    <td class="text-right">{{ $r->tarif }}%</td>
                    <td class="text-right font-mono font-semibold text-indigo-700">Rp {{ number_format($r->pph_dipotong, 0, ',', '.') }}</td>
                    <td>
                        @if($r->_type === 'final')
                            <span class="badge {{ $r->status === 'sudah_terima_bukpot' ? 'badge-green' : 'badge-yellow' }}">
                                {{ $r->status === 'sudah_terima_bukpot' ? 'Terima Bukpot' : 'Menunggu' }}
                            </span>
                        @else
                            <span class="badge {{ $r->status === 'sudah_disetor' ? 'badge-green' : 'badge-yellow' }}">
                                {{ $r->status === 'sudah_disetor' ? 'Sudah Setor' : 'Belum Setor' }}
                            </span>
                        @endif
                    </td>
                    <td>
                        @if($r->sumber === 'import_excel')
                            <span class="badge badge-indigo">Import</span>
                        @else
                            <span class="text-xs text-slate-400">Manual</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-8 text-slate-400">
                        Tidak ada data untuk periode ini. Coba pilih masa pajak lain atau import dari Excel DJP.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-data-card>

{{-- ═══ Import Batches ══════════════════════════════════════════════════════ --}}
@if($batches->count() > 0)
<div class="mt-6">
    <h3 class="text-sm font-semibold text-slate-700 mb-3">Riwayat Import Terakhir</h3>
    <div class="space-y-2">
        @foreach($batches as $batch)
        <div class="flex items-center justify-between p-3 bg-white border border-slate-200 rounded-xl">
            <div>
                <span class="text-sm font-medium text-slate-700">{{ $batch->filename }}</span>
                <span class="ml-2 text-xs text-slate-400">{{ $batch->masa_pajak }} — {{ $batch->created_at->format('d/m/Y H:i') }}</span>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs text-slate-500">{{ $batch->success_rows }}/{{ $batch->total_rows }} baris</span>
                <span class="badge {{ $batch->status === 'committed' ? 'badge-green' : 'badge-yellow' }}">
                    {{ $batch->status === 'committed' ? 'Committed' : 'Preview' }}
                </span>
                @if($batch->status === 'preview')
                <a href="{{ route('tax.bppu.preview', $batch->id) }}" class="text-xs text-indigo-600 hover:text-indigo-800">Review →</a>
                @else
                <a href="{{ route('tax.bppu.batch', $batch->id) }}" class="text-xs text-slate-500 hover:text-slate-700">Detail</a>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

@endsection
