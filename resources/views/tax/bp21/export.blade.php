@extends('layouts.app')

@section('content')
<x-page-header
    title="Export BP21 ke Coretax"
    subtitle="Generate file Excel BP21 format DJP siap upload ke Coretax"
>
    <x-slot name="actions">
        <a href="{{ route('tax.bp21.index') }}" class="btn-secondary">← Kembali</a>
    </x-slot>
</x-page-header>

<div class="max-w-xl">
    <x-data-card>
        <form method="POST" action="{{ route('tax.bp21.export.download') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="form-label-required">Masa Pajak</label>
                    <select name="masa_pajak" class="form-select">
                        @forelse($masas as $m)
                        <option value="{{ $m['masa'] }}" {{ $masaFilter === $m['masa'] ? 'selected' : '' }}>
                            {{ $m['masa'] }} — {{ $m['count'] }} records
                        </option>
                        @empty
                        <option value="">Tidak ada data</option>
                        @endforelse
                    </select>
                </div>
                <div>
                    <label class="form-label-required">NPWP Pemotong</label>
                    <input type="text" name="npwp_pemotong" value="{{ $setting?->npwp ?? '' }}"
                           class="form-input font-mono" placeholder="NPWP perusahaan (16-digit)">
                </div>
                <div>
                    <label class="form-label-required">Nama Pemotong</label>
                    <input type="text" name="nama_pemotong" value="{{ $setting?->nama_wp ?? '' }}"
                           class="form-input" placeholder="Nama perusahaan">
                </div>
                <div class="p-3 bg-green-50 border border-green-200 rounded-xl text-xs text-green-700">
                    <strong>Output:</strong> File Excel format BP21 DJP v.4.
                    Siap diupload ke Coretax → e-Bupot 21.
                </div>
                @if(empty($masas))
                <div class="alert alert-warning text-sm">Belum ada data BP21 untuk ditampilkan.</div>
                @else
                <button type="submit" class="btn-primary w-full">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                    </svg>
                    Download Excel BP21 DJP
                </button>
                @endif
            </div>
        </form>
    </x-data-card>
</div>
@endsection
