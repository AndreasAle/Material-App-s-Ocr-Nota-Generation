@extends('layouts.app')

@section('content')
<x-page-header
    title="Import BP21 dari Excel DJP"
    subtitle="Upload file 'BP21 Excel to XML v.4.xlsx' dari DJP untuk import data PPh 21"
>
    <x-slot name="actions">
        <a href="{{ route('tax.bp21.index') }}" class="btn-secondary">← Kembali</a>
    </x-slot>
</x-page-header>

<div class="alert alert-info mb-6">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/>
    </svg>
    <div>
        <strong>Format file yang diterima:</strong> BP21 Excel to XML v.4 dari DJP Coretax.
        <ul class="mt-1 text-xs list-disc ml-4">
            <li>Kolom: No Bukpot, NPWP, NIK, Nama, ID TKU, Status PTKP, KOP, Nama KOP, Bruto, Deemed%, Tarif%, PPh</li>
            <li>Header info di Row 1: NPWP Pemotong (B1), Nama (D1), Masa Pajak (F1), Tahun (H1)</li>
            <li>Data mulai baris 4</li>
            <li>Sistem auto-detect jenis tarif (TER/PS17/Harian/Pesangon/Pensiun) dari KOP</li>
        </ul>
    </div>
</div>

<div class="max-w-xl">
    <x-data-card>
        <form method="POST" action="{{ route('tax.bp21.import.process') }}" enctype="multipart/form-data">
            @csrf
            <div class="space-y-4">
                <div x-data="{ filename: '' }">
                    <label class="form-label-required">File Excel BP21</label>
                    <label class="flex flex-col items-center justify-center w-full h-40 border-2 border-dashed border-slate-300 rounded-xl cursor-pointer hover:border-green-400 hover:bg-green-50 transition-colors mt-1">
                        <input type="file" name="file" accept=".xlsx,.xls" class="hidden" @change="filename = $event.target.files[0]?.name || ''">
                        <svg class="w-10 h-10 text-slate-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/>
                        </svg>
                        <span x-text="filename || 'Klik untuk upload atau drag & drop'" class="text-sm text-slate-500"></span>
                        <span class="text-xs text-slate-400 mt-1">Format: .xlsx atau .xls • Maks. 10MB</span>
                    </label>
                    @error('file') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-700">
                    <strong>Proses import:</strong> Upload → Preview validasi → Konfirmasi → Simpan ke database.
                    Data belum tersimpan sampai Anda klik Commit.
                </div>
                <button type="submit" class="btn-primary w-full">Upload & Preview</button>
            </div>
        </form>
    </x-data-card>
</div>
@endsection
