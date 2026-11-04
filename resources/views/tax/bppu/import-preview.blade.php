@extends('layouts.app')

@section('content')
<x-page-header
    title="Preview Import BPPU"
    subtitle="Verifikasi data sebelum disimpan ke sistem"
>
    <x-slot name="actions">
        <a href="{{ route('tax.bppu.import') }}" class="btn-secondary">Upload Ulang</a>
    </x-slot>
</x-page-header>

{{-- Summary Card --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 text-center">
        <div class="text-2xl font-bold text-slate-700">{{ $batch->total_rows }}</div>
        <div class="text-xs text-slate-500 mt-1">Total Baris</div>
    </div>
    <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 text-center">
        <div class="text-2xl font-bold text-emerald-700">{{ $batch->success_rows }}</div>
        <div class="text-xs text-emerald-600 mt-1">Siap Disimpan</div>
    </div>
    <div class="bg-rose-50 border border-rose-200 rounded-2xl p-4 text-center">
        <div class="text-2xl font-bold text-rose-700">{{ $batch->error_rows }}</div>
        <div class="text-xs text-rose-600 mt-1">Baris Error</div>
    </div>
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 text-center">
        <div class="text-2xl font-bold text-amber-700">{{ $batch->duplicate_rows }}</div>
        <div class="text-xs text-amber-600 mt-1">Duplikat (Skip)</div>
    </div>
</div>

{{-- Info file --}}
<x-data-card class="mb-6">
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
        <div><span class="text-slate-400">File:</span> <span class="font-medium">{{ $batch->filename }}</span></div>
        <div><span class="text-slate-400">NPWP Pemotong:</span> <span class="font-mono">{{ $batch->npwp_pemotong ?? '—' }}</span></div>
        <div><span class="text-slate-400">Nama Pemotong:</span> <span class="font-medium">{{ $batch->nama_pemotong ?? '—' }}</span></div>
        <div><span class="text-slate-400">Masa Pajak:</span> <span class="font-medium">{{ $batch->masa_pajak ?? '—' }}</span></div>
    </div>
</x-data-card>

{{-- Error list --}}
@if(count($batch->errors ?? []) > 0)
<div class="alert alert-warning mb-6">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9.303 3.376c.866 1.5-.217 3.374-1.948 3.374H4.645c-1.73 0-2.813-1.874-1.948-3.374l5.942-9.748c.866-1.5 3.032-1.5 3.898 0zm.029 5.63h.008v.008h-.008v-.008Z"/>
    </svg>
    <div>
        <strong>Ditemukan {{ count($batch->errors) }} error (baris ini akan dilewati):</strong>
        <ul class="mt-2 text-xs list-disc ml-4 space-y-0.5">
            @foreach(array_slice($batch->errors, 0, 20) as $err)
            <li>Baris {{ $err['row'] }} — {{ $err['field'] }}: {{ $err['message'] }}</li>
            @endforeach
            @if(count($batch->errors) > 20)
            <li class="text-slate-400">... dan {{ count($batch->errors) - 20 }} error lainnya</li>
            @endif
        </ul>
    </div>
</div>
@endif

{{-- Data Preview Table --}}
<x-data-card class="mb-6">
    <h3 class="text-sm font-semibold text-slate-700 mb-4">Preview Data ({{ count($rows) }} baris)</h3>
    <div class="overflow-x-auto">
        <table class="data-table text-xs">
            <thead>
                <tr>
                    <th>Baris</th>
                    <th>Status</th>
                    <th>No Bukpot</th>
                    <th>Nama Penerima</th>
                    <th>KOP</th>
                    <th class="text-right">DPP/Bruto</th>
                    <th class="text-right">Tarif %</th>
                    <th class="text-right">PPh Dipotong</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $row)
                <tr class="{{ !$row['valid'] ? 'bg-rose-50' : ($row['duplicate'] ? 'bg-amber-50' : '') }}">
                    <td>{{ $row['row'] }}</td>
                    <td>
                        @if(!$row['valid'])
                            <span class="badge badge-red">Error</span>
                        @elseif($row['duplicate'])
                            <span class="badge badge-yellow">Duplikat</span>
                        @else
                            <span class="badge badge-green">OK</span>
                        @endif
                    </td>
                    <td class="font-mono">{{ $row['data']['no_bukti_potong'] ?? '—' }}</td>
                    <td>{{ $row['data']['nama_penerima'] ?? '—' }}</td>
                    <td>
                        <span class="font-mono bg-slate-100 px-1 rounded text-xs">{{ $row['data']['kode_objek_pajak'] ?? '—' }}</span>
                    </td>
                    <td class="text-right font-mono">{{ number_format($row['data']['nilai_kontrak'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">{{ $row['data']['tarif'] ?? 0 }}%</td>
                    <td class="text-right font-mono font-semibold">{{ number_format($row['data']['pph_dipotong'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-xs text-slate-400">
                        @if(!empty($row['errors']))
                            {{ collect($row['errors'])->pluck('message')->join(', ') }}
                        @elseif($row['duplicate'])
                            No Bukpot sudah ada
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-data-card>

{{-- Action Buttons --}}
@if($batch->success_rows > 0)
<div class="flex justify-end gap-3">
    <a href="{{ route('tax.bppu.index') }}" class="btn-secondary">Batalkan</a>
    <form method="POST" action="{{ route('tax.bppu.commit', $batch->id) }}">
        @csrf
        <button type="submit" class="btn-primary"
                onclick="return confirm('Simpan {{ $batch->success_rows }} bukti pemotongan ke database? Proses ini tidak bisa dibatalkan.')">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
            </svg>
            Commit {{ $batch->success_rows }} Baris
        </button>
    </form>
</div>
@else
<div class="alert alert-warning">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9.303 3.376c.866 1.5-.217 3.374-1.948 3.374H4.645c-1.73 0-2.813-1.874-1.948-3.374l5.942-9.748c.866-1.5 3.032-1.5 3.898 0zm.029 5.63h.008v.008h-.008v-.008Z"/>
    </svg>
    <span>Tidak ada baris yang valid untuk disimpan. Periksa file dan upload ulang.</span>
</div>
@endif

@endsection
