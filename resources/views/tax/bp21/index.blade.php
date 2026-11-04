@extends('layouts.app')

@section('content')
<x-page-header
    title="BP21 — Bukti Pemotongan PPh Pasal 21"
    subtitle="Kelola pemotongan PPh 21 karyawan & tenaga ahli — Tarif TER berlaku Jan 2024 (PMK 168/2023)"
>
    <x-slot name="actions">
        <a href="{{ route('tax.bp21.create') }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Input Manual
        </a>
        <a href="{{ route('tax.bp21.import') }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/>
            </svg>
            Import Excel DJP
        </a>
        <a href="{{ route('tax.bp21.export', ['masa' => $masaFilter]) }}" class="btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
            </svg>
            Export Coretax
        </a>
    </x-slot>
</x-page-header>

{{-- Tracker SPT --}}
<x-data-card class="mb-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-semibold text-slate-700">Tracker SPT PPh 21 — {{ $tahun }}</h3>
        <div class="flex gap-2 text-xs">
            <a href="{{ route('tax.bp21.index', ['masa' => ($tahun-1).'-12']) }}" class="text-slate-500">← {{ $tahun-1 }}</a>
            <span class="text-indigo-600 font-bold">{{ $tahun }}</span>
            <a href="{{ route('tax.bp21.index', ['masa' => ($tahun+1).'-01']) }}" class="text-slate-500">{{ $tahun+1 }} →</a>
        </div>
    </div>
    <div class="grid grid-cols-6 sm:grid-cols-12 gap-1.5">
        @php $bulan = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des']; @endphp
        @foreach($rekapMasa as $masa => $info)
            @php
                $m = (int) substr($masa, 5, 2);
                $isActive = $masaFilter === $masa;
                $color = $info['has_data'] ? 'bg-green-100 border-green-300 text-green-700' : 'bg-slate-100 border-slate-200 text-slate-400';
                if ($isActive) $color = 'bg-indigo-600 border-indigo-600 text-white';
            @endphp
            <a href="{{ route('tax.bp21.index', ['masa' => $masa]) }}"
               class="rounded-lg border p-2 text-center hover:border-indigo-400 transition-colors {{ $color }}">
                <div class="text-xs font-bold">{{ $bulan[$m] }}</div>
                <div class="text-xs mt-0.5">{{ $info['has_data'] ? $info['count'] . ' bpr' : '—' }}</div>
            </a>
        @endforeach
    </div>
</x-data-card>

{{-- Summary --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-green-50 border border-green-200 rounded-2xl p-4">
        <p class="text-xs text-green-600 font-medium">Total PPh 21 Dipotong</p>
        <p class="text-2xl font-bold text-green-700 mt-1">Rp {{ number_format($totalPph, 0, ',', '.') }}</p>
        <p class="text-xs text-green-500 mt-1">{{ $masaFilter }}</p>
    </div>
    <div class="bg-rose-50 border border-rose-200 rounded-2xl p-4">
        <p class="text-xs text-rose-600 font-medium">Belum Disetor</p>
        <p class="text-2xl font-bold text-rose-700 mt-1">Rp {{ number_format($belumSetor, 0, ',', '.') }}</p>
        <p class="text-xs text-rose-500 mt-1">Harus setor sebelum tgl 10 bulan berikutnya</p>
    </div>
    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4">
        <p class="text-xs text-slate-600 font-medium">Total Records</p>
        <p class="text-2xl font-bold text-slate-700 mt-1">{{ $records->total() }}</p>
        <p class="text-xs text-slate-500 mt-1">{{ $masaFilter }}</p>
    </div>
</div>

{{-- Filter --}}
<form method="GET" class="flex gap-3 mb-4">
    <div>
        <label class="form-label">Masa Pajak</label>
        <input type="month" name="masa" value="{{ $masaFilter }}" class="form-input">
    </div>
    <div class="flex items-end">
        <button type="submit" class="btn-secondary">Filter</button>
    </div>
</form>

{{-- Table --}}
<x-data-card>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>No Bukpot</th>
                    <th>Nama Penerima</th>
                    <th>KOP</th>
                    <th>Jenis Tarif</th>
                    <th>Status PTKP</th>
                    <th class="text-right">Penghasilan Bruto</th>
                    <th class="text-right">Tarif</th>
                    <th class="text-right">PPh Dipotong</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $r)
                <tr>
                    <td class="font-mono text-xs">{{ $r->no_bukti_potong ?? '—' }}</td>
                    <td>
                        {{ $r->nama_penerima }}
                        @if($r->npwp_penerima)
                            <span class="block text-xs font-mono text-slate-400">{{ $r->npwp_penerima }}</span>
                        @elseif($r->nik_penerima)
                            <span class="block text-xs font-mono text-slate-400">NIK: {{ $r->nik_penerima }}</span>
                        @endif
                    </td>
                    <td>
                        <span class="font-mono text-xs bg-green-50 border border-green-200 px-1.5 py-0.5 rounded">
                            {{ $r->kode_objek_pajak }}
                        </span>
                    </td>
                    <td>
                        <span class="badge {{ match($r->jenis_tarif) {
                            'TER'      => 'badge-blue',
                            'PS17'     => 'badge-indigo',
                            'HARIAN'   => 'badge-yellow',
                            'PESANGON' => 'badge-amber',
                            'PENSIUN'  => 'badge-slate',
                            default    => 'badge-slate',
                        } }}">
                            {{ $r->jenis_tarif }}
                        </span>
                    </td>
                    <td class="font-mono text-xs">{{ $r->status_ptkp ?? '—' }}</td>
                    <td class="text-right font-mono">Rp {{ number_format($r->penghasilan_bruto, 0, ',', '.') }}</td>
                    <td class="text-right">{{ $r->tarif }}%</td>
                    <td class="text-right font-mono font-semibold text-green-700">Rp {{ number_format($r->pph_dipotong, 0, ',', '.') }}</td>
                    <td>
                        <span class="badge {{ $r->status === 'sudah_disetor' ? 'badge-green' : 'badge-yellow' }}">
                            {{ $r->status === 'sudah_disetor' ? 'Sudah Setor' : 'Belum Setor' }}
                        </span>
                        @if($r->ntpn)
                            <span class="block text-xs font-mono text-slate-400">{{ $r->ntpn }}</span>
                        @endif
                    </td>
                    <td>
                        <div class="flex gap-2">
                            <a href="{{ route('tax.bp21.show', $r->id) }}" class="text-xs text-indigo-600 hover:text-indigo-800">Detail</a>
                            @if($r->status === 'belum_disetor')
                            <span class="text-slate-300">|</span>
                            <a href="{{ route('tax.bp21.edit', $r->id) }}" class="text-xs text-slate-500 hover:text-slate-700">Edit</a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center py-8 text-slate-400">
                        Tidak ada data BP21 untuk masa ini.
                        <a href="{{ route('tax.bp21.create') }}" class="text-indigo-600 ml-1">Input manual</a> atau
                        <a href="{{ route('tax.bp21.import') }}" class="text-indigo-600">import dari Excel DJP</a>.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $records->withQueryString()->links() }}
    </div>
</x-data-card>

@endsection
