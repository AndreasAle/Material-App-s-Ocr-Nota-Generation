@extends('layouts.app')

@section('content')
<x-page-header
    title="Preview Import BP21"
    subtitle="Verifikasi data sebelum disimpan"
>
    <x-slot name="actions">
        <a href="{{ route('tax.bp21.import') }}" class="btn-secondary">Upload Ulang</a>
    </x-slot>
</x-page-header>

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
        <div class="text-xs text-rose-600 mt-1">Error</div>
    </div>
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 text-center">
        <div class="text-2xl font-bold text-amber-700">{{ $batch->duplicate_rows }}</div>
        <div class="text-xs text-amber-600 mt-1">Duplikat</div>
    </div>
</div>

<x-data-card class="mb-4">
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
        <div><span class="text-slate-400">File:</span> {{ $batch->filename }}</div>
        <div><span class="text-slate-400">NPWP Pemotong:</span> <span class="font-mono">{{ $batch->npwp_pemotong ?? '—' }}</span></div>
        <div><span class="text-slate-400">Masa Pajak:</span> <span class="font-medium">{{ $batch->masa_pajak }}</span></div>
        <div><span class="text-slate-400">Tingkat sukses:</span> <span class="font-bold text-{{ $batch->success_rows > 0 ? 'emerald' : 'rose' }}-600">{{ $batch->success_rate }}%</span></div>
    </div>
</x-data-card>

@if(count($batch->errors ?? []) > 0)
<div class="alert alert-warning mb-4">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9.303 3.376c.866 1.5-.217 3.374-1.948 3.374H4.645c-1.73 0-2.813-1.874-1.948-3.374l5.942-9.748c.866-1.5 3.032-1.5 3.898 0zm.029 5.63h.008v.008h-.008v-.008Z"/>
    </svg>
    <div>
        <strong>{{ count($batch->errors) }} error ditemukan:</strong>
        <ul class="mt-1 text-xs list-disc ml-4">
            @foreach(array_slice($batch->errors, 0, 15) as $err)
            <li>Baris {{ $err['row'] }}: {{ $err['message'] }}</li>
            @endforeach
        </ul>
    </div>
</div>
@endif

<x-data-card class="mb-6">
    <div class="overflow-x-auto">
        <table class="data-table text-xs">
            <thead>
                <tr>
                    <th>Baris</th>
                    <th>Status</th>
                    <th>No Bukpot</th>
                    <th>Nama Penerima</th>
                    <th>KOP</th>
                    <th>Jenis Tarif</th>
                    <th class="text-right">Bruto</th>
                    <th class="text-right">Tarif</th>
                    <th class="text-right">PPh Dipotong</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $row)
                <tr class="{{ !$row['valid'] ? 'bg-rose-50' : ($row['duplicate'] ? 'bg-amber-50' : '') }}">
                    <td>{{ $row['row'] }}</td>
                    <td>
                        @if(!$row['valid']) <span class="badge badge-red">Error</span>
                        @elseif($row['duplicate']) <span class="badge badge-yellow">Duplikat</span>
                        @else <span class="badge badge-green">OK</span>
                        @endif
                    </td>
                    <td class="font-mono">{{ $row['data']['no_bukti_potong'] ?? '—' }}</td>
                    <td>{{ $row['data']['nama_penerima'] ?? '—' }}</td>
                    <td><span class="font-mono bg-slate-100 px-1 rounded">{{ $row['data']['kode_objek_pajak'] ?? '—' }}</span></td>
                    <td>{{ $row['data']['jenis_tarif'] ?? '—' }}</td>
                    <td class="text-right font-mono">{{ number_format($row['data']['penghasilan_bruto'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">{{ $row['data']['tarif'] ?? 0 }}%</td>
                    <td class="text-right font-mono font-semibold">{{ number_format($row['data']['pph_dipotong'] ?? 0, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-data-card>

@if($batch->success_rows > 0)
<div class="flex justify-end gap-3">
    <a href="{{ route('tax.bp21.index') }}" class="btn-secondary">Batalkan</a>
    <form method="POST" action="{{ route('tax.bp21.commit', $batch->id) }}">
        @csrf
        <button type="submit" class="btn-primary"
                onclick="return confirm('Simpan {{ $batch->success_rows }} bukti potong PPh 21? Proses ini tidak bisa dibatalkan.')">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
            </svg>
            Commit {{ $batch->success_rows }} Baris
        </button>
    </form>
</div>
@endif
@endsection
