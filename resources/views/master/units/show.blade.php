@extends('layouts.app')
@section('title', 'Satuan: ' . $unit->name)

@section('content')
<x-page-header title="{{ $unit->name }}" subtitle="Satuan Material">
    <x-slot name="actions">
        <a href="{{ route('units.edit', $unit) }}" class="btn-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/>
            </svg>
            Edit
        </a>
        <a href="{{ route('units.index') }}" class="btn-secondary">← Kembali</a>
    </x-slot>
</x-page-header>

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">

    <x-data-card title="Informasi Satuan">
        <div class="px-5 py-4 space-y-3 text-sm">
            <div class="flex items-center justify-between gap-3">
                <span class="text-slate-500">Nama Satuan</span>
                <span class="font-semibold text-slate-800">{{ $unit->name }}</span>
            </div>
            <div class="flex items-center justify-between gap-3">
                <span class="text-slate-500">Kode</span>
                <span class="font-mono font-semibold text-slate-800 bg-slate-100 px-2 py-0.5 rounded-lg text-xs">{{ $unit->code }}</span>
            </div>
            @if($unit->description)
                <div class="pt-2 border-t border-slate-100">
                    <p class="text-xs font-semibold text-slate-500 mb-1">Deskripsi</p>
                    <p class="text-slate-700 leading-relaxed">{{ $unit->description }}</p>
                </div>
            @endif
        </div>
    </x-data-card>

    <x-data-card title="Penggunaan">
        <div class="px-5 py-4 space-y-3 text-sm">
            <div class="flex items-center justify-between gap-3">
                <span class="text-slate-500">Digunakan Material</span>
                <span class="font-bold text-slate-800">{{ number_format($unit->materials_count ?? 0) }}</span>
            </div>
            <div class="flex items-center justify-between gap-3">
                <span class="text-slate-500">Dibuat</span>
                <span class="text-slate-400 text-xs">{{ $unit->created_at->format('d M Y') }}</span>
            </div>
            <div class="flex items-center justify-between gap-3">
                <span class="text-slate-500">Diperbarui</span>
                <span class="text-slate-400 text-xs">{{ $unit->updated_at->format('d M Y') }}</span>
            </div>
        </div>
        <div class="border-t border-slate-100 px-5 py-4">
            <a href="{{ route('materials.index', ['unit_id' => $unit->id]) }}"
               class="btn-secondary w-full justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
                </svg>
                Lihat Material dengan Satuan Ini
            </a>
        </div>
    </x-data-card>

</div>
@endsection
