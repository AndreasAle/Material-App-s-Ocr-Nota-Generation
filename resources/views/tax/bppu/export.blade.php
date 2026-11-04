@extends('layouts.app')

@section('content')
<x-page-header
    title="Export BPPU ke Coretax"
    subtitle="Generate file Excel DJP siap upload ke aplikasi Coretax"
>
    <x-slot name="actions">
        <a href="{{ route('tax.bppu.index') }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
            </svg>
            Kembali
        </a>
    </x-slot>
</x-page-header>

<div class="max-w-xl">
    <x-data-card>
        <h3 class="text-sm font-semibold text-slate-700 mb-5">Parameter Export BPPU</h3>

        <form method="POST" action="{{ route('tax.bppu.export.download') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="form-label-required">Masa Pajak</label>
                    <select name="masa_pajak" class="form-select">
                        @forelse($masas as $m)
                        <option value="{{ $m['masa'] }}">{{ $m['masa'] }} — PPh Final: {{ $m['final'] }}, PPh 23: {{ $m['pph23'] }}</option>
                        @empty
                        <option value="">Tidak ada data</option>
                        @endforelse
                    </select>
                    <p class="text-xs text-slate-400 mt-1">Hanya masa yang ada datanya ditampilkan</p>
                </div>

                <div>
                    <label class="form-label-required">Jenis Data</label>
                    <select name="jenis" class="form-select">
                        <option value="all">Semua (PPh Final 4(2) + PPh 23)</option>
                        <option value="final">PPh Final Pasal 4(2) saja</option>
                        <option value="pph23">PPh Pasal 23 saja</option>
                    </select>
                </div>

                <div>
                    <label class="form-label-required">NPWP Pemotong</label>
                    <input type="text" name="npwp_pemotong" value="{{ $setting?->npwp ?? '' }}"
                           class="form-input font-mono" placeholder="NPWP perusahaan kita (16-digit)">
                </div>

                <div>
                    <label class="form-label-required">Nama Pemotong</label>
                    <input type="text" name="nama_pemotong" value="{{ $setting?->nama_wp ?? '' }}"
                           class="form-input" placeholder="Nama perusahaan kita">
                </div>

                <div class="p-3 bg-blue-50 border border-blue-200 rounded-xl text-xs text-blue-700">
                    <strong>Output:</strong> File Excel format BPPU DJP (kolom sesuai template v.3).
                    Siap diupload langsung ke Coretax → Menu e-Bupot Unifikasi.
                </div>

                @if(empty($masas))
                <div class="alert alert-warning">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9.303 3.376c.866 1.5-.217 3.374-1.948 3.374H4.645c-1.73 0-2.813-1.874-1.948-3.374l5.942-9.748c.866-1.5 3.032-1.5 3.898 0zm.029 5.63h.008v.008h-.008v-.008Z"/>
                    </svg>
                    <span>Belum ada data BPPU untuk tahun {{ $tahun }}. Input atau import data dulu.</span>
                </div>
                @else
                <button type="submit" class="btn-primary w-full">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                    </svg>
                    Download Excel BPPU DJP
                </button>
                @endif
            </div>
        </form>
    </x-data-card>
</div>
@endsection
