@extends('layouts.app')

@section('content')
<x-page-header
    title="Preview Import {{ $batch->jenis === 'dok_lain_masukan' ? 'PPN Masukan' : 'PPN Keluaran' }}"
    subtitle="Verifikasi data sebelum disimpan"
>
    <x-slot name="actions">
        <a href="{{ route('tax.dok-lain.index') }}" class="btn-secondary">Batalkan</a>
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

@if(count($batch->errors ?? []) > 0)
<div class="alert alert-warning mb-6">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9.303 3.376c.866 1.5-.217 3.374-1.948 3.374H4.645c-1.73 0-2.813-1.874-1.948-3.374l5.942-9.748c.866-1.5 3.032-1.5 3.898 0zm.029 5.63h.008v.008h-.008v-.008Z"/>
    </svg>
    <div>
        <strong>Error (baris ini dilewati):</strong>
        <ul class="mt-2 text-xs list-disc ml-4">
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
                    <th>No Faktur</th>
                    <th>Tgl Faktur</th>
                    <th>Nama {{ $batch->jenis === 'dok_lain_masukan' ? 'Penjual' : 'Pembeli' }}</th>
                    <th class="text-right">DPP</th>
                    <th class="text-right">PPN</th>
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
                    <td class="font-mono">{{ $row['data']['no_faktur'] ?? '—' }}</td>
                    <td>{{ $row['data']['tanggal_faktur'] ?? '—' }}</td>
                    <td>{{ $row['data'][$batch->jenis === 'dok_lain_masukan' ? 'nama_penjual' : 'nama_pembeli'] ?? '—' }}</td>
                    <td class="text-right font-mono">{{ number_format($row['data']['dpp'] ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right font-mono font-semibold">{{ number_format($row['data']['ppn'] ?? 0, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-data-card>

@if($batch->success_rows > 0)
<div class="flex justify-end gap-3">
    <a href="{{ route('tax.dok-lain.index') }}" class="btn-secondary">Batalkan</a>
    <form method="POST" action="{{ route('tax.dok-lain.commit', $batch->id) }}">
        @csrf
        <button type="submit" class="btn-primary"
                onclick="return confirm('Simpan {{ $batch->success_rows }} dokumen ke database?')">
            Commit {{ $batch->success_rows }} Baris
        </button>
    </form>
</div>
@endif

@endsection
