@extends('layouts.app')

@section('content')
<x-page-header
    title="Detail Batch Import BPPU"
    subtitle="{{ $batch->filename }} — {{ $batch->masa_pajak }}"
>
    <x-slot name="actions">
        <a href="{{ route('tax.bppu.index') }}" class="btn-secondary">← Kembali</a>
    </x-slot>
</x-page-header>

<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4">
        <div class="text-xs text-slate-500">Status</div>
        <span class="badge {{ $batch->status === 'committed' ? 'badge-green' : 'badge-yellow' }} mt-1">
            {{ $batch->status === 'committed' ? 'Committed' : 'Preview' }}
        </span>
    </div>
    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4">
        <div class="text-xs text-slate-500">Total Baris</div>
        <div class="text-xl font-bold text-slate-700 mt-1">{{ $batch->total_rows }}</div>
    </div>
    <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4">
        <div class="text-xs text-emerald-600">Berhasil</div>
        <div class="text-xl font-bold text-emerald-700 mt-1">{{ $batch->success_rows }}</div>
    </div>
    <div class="bg-rose-50 border border-rose-200 rounded-2xl p-4">
        <div class="text-xs text-rose-600">Error</div>
        <div class="text-xl font-bold text-rose-700 mt-1">{{ $batch->error_rows }}</div>
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
    {{-- PPh Final Records --}}
    @if($batch->pphFinalRecords->count() > 0)
    <x-data-card>
        <h3 class="text-sm font-semibold text-slate-700 mb-3">PPh Final 4(2) — {{ $batch->pphFinalRecords->count() }} records</h3>
        <div class="space-y-2">
            @foreach($batch->pphFinalRecords as $r)
            <div class="flex justify-between text-sm p-2 bg-slate-50 rounded-lg">
                <div>
                    <span class="font-medium">{{ $r->nama_pemotong }}</span>
                    <span class="text-xs text-slate-400 block font-mono">{{ $r->kode_objek_pajak }}</span>
                </div>
                <span class="font-mono font-semibold text-blue-700">Rp {{ number_format($r->pph_dipotong, 0, ',', '.') }}</span>
            </div>
            @endforeach
        </div>
        <div class="border-t border-slate-200 mt-3 pt-3 flex justify-between font-semibold text-sm">
            <span>Total</span>
            <span class="font-mono text-blue-700">Rp {{ number_format($batch->pphFinalRecords->sum('pph_dipotong'), 0, ',', '.') }}</span>
        </div>
    </x-data-card>
    @endif

    {{-- PPh 23 Records --}}
    @if($batch->pph23Records->count() > 0)
    <x-data-card>
        <h3 class="text-sm font-semibold text-slate-700 mb-3">PPh Pasal 23 — {{ $batch->pph23Records->count() }} records</h3>
        <div class="space-y-2">
            @foreach($batch->pph23Records as $r)
            <div class="flex justify-between text-sm p-2 bg-slate-50 rounded-lg">
                <div>
                    <span class="font-medium">{{ $r->nama_penerima }}</span>
                    <span class="text-xs text-slate-400 block font-mono">{{ $r->kode_objek_pajak }}</span>
                </div>
                <span class="font-mono font-semibold text-amber-700">Rp {{ number_format($r->pph_dipotong, 0, ',', '.') }}</span>
            </div>
            @endforeach
        </div>
        <div class="border-t border-slate-200 mt-3 pt-3 flex justify-between font-semibold text-sm">
            <span>Total</span>
            <span class="font-mono text-amber-700">Rp {{ number_format($batch->pph23Records->sum('pph_dipotong'), 0, ',', '.') }}</span>
        </div>
    </x-data-card>
    @endif
</div>

<div class="mt-4 text-xs text-slate-400">
    Diupload oleh {{ $batch->creator?->name ?? 'Unknown' }} pada {{ $batch->created_at->format('d/m/Y H:i') }}
</div>

@endsection
