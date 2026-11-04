@extends('layouts.app')

@section('content')
<x-page-header
    title="Import Dok Lain PPN Keluaran"
    subtitle="Upload file Excel DJP untuk import dokumen lain PPN Keluaran"
>
    <x-slot name="actions">
        <a href="{{ route('tax.dok-lain.index') }}" class="btn-secondary">← Kembali</a>
    </x-slot>
</x-page-header>

<div class="alert alert-info mb-6">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/>
    </svg>
    <div>
        <strong>Format file:</strong> "Create Dok Lain Keluaran_0.xlsx" dari DJP Coretax
        <ul class="mt-1 text-xs list-disc ml-4">
            <li>Kolom: No Faktur/Dok, Kode Transaksi, Kode Dok Lain, Tanggal, NPWP, Nama Pembeli, Alamat, DPP, PPN, PPnBM, Ket, Info Tambahan</li>
            <li>Data mulai baris 4</li>
        </ul>
    </div>
</div>

<div class="max-w-xl">
    <x-data-card>
        <form method="POST" action="{{ route('tax.dok-lain.import.keluaran.process') }}" enctype="multipart/form-data">
            @csrf
            <div class="space-y-4">
                <div x-data="{ filename: '' }">
                    <label class="form-label-required">File Excel Dok Lain Keluaran</label>
                    <label class="flex flex-col items-center justify-center w-full h-36 border-2 border-dashed border-slate-300 rounded-xl cursor-pointer hover:border-indigo-400 transition-colors mt-1">
                        <input type="file" name="file" accept=".xlsx,.xls" class="hidden" @change="filename = $event.target.files[0]?.name || ''">
                        <svg class="w-8 h-8 text-slate-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/>
                        </svg>
                        <span x-text="filename || 'Klik untuk upload (.xlsx, .xls)'" class="text-sm text-slate-500"></span>
                    </label>
                    @error('file') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="btn-primary w-full">Upload & Preview</button>
            </div>
        </form>
    </x-data-card>
</div>
@endsection
