@extends('layouts.app')
@section('title', 'Kategori: ' . $materialCategory->name)

@section('content')
<x-page-header title="{{ $materialCategory->name }}" subtitle="Kategori Material">
    <x-slot name="actions">
        <a href="{{ route('material-categories.edit', $materialCategory) }}" class="btn-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/>
            </svg>
            Edit
        </a>
        <a href="{{ route('material-categories.index') }}" class="btn-secondary">← Kembali</a>
    </x-slot>
</x-page-header>

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">

    <x-data-card title="Informasi Kategori">
        <div class="px-5 py-4 space-y-3 text-sm">
            <div class="flex items-start justify-between gap-3">
                <span class="text-slate-500 shrink-0">Nama</span>
                <span class="font-semibold text-slate-800 text-right">{{ $materialCategory->name }}</span>
            </div>
            <div class="flex items-center justify-between gap-3">
                <span class="text-slate-500">Slug</span>
                <span class="font-mono text-slate-700 text-xs bg-slate-100 px-2 py-0.5 rounded-lg">{{ $materialCategory->slug }}</span>
            </div>
            <div class="flex items-center justify-between gap-3">
                <span class="text-slate-500">Status</span>
                @if($materialCategory->is_active)
                    <span class="badge-success">Aktif</span>
                @else
                    <span class="badge-neutral">Nonaktif</span>
                @endif
            </div>
            @if($materialCategory->description)
                <div class="pt-2 border-t border-slate-100">
                    <p class="text-xs font-semibold text-slate-500 mb-1">Deskripsi</p>
                    <p class="text-slate-700 leading-relaxed">{{ $materialCategory->description }}</p>
                </div>
            @endif
        </div>
    </x-data-card>

    <x-data-card title="Statistik">
        <div class="px-5 py-4 space-y-3 text-sm">
            <div class="flex items-center justify-between gap-3">
                <span class="text-slate-500">Jumlah Material</span>
                <span class="font-bold text-slate-800">{{ number_format($materialCategory->materials_count ?? 0) }}</span>
            </div>
            <div class="flex items-center justify-between gap-3">
                <span class="text-slate-500">Dibuat</span>
                <span class="text-slate-400 text-xs">{{ $materialCategory->created_at->format('d M Y') }}</span>
            </div>
            <div class="flex items-center justify-between gap-3">
                <span class="text-slate-500">Diperbarui</span>
                <span class="text-slate-400 text-xs">{{ $materialCategory->updated_at->format('d M Y') }}</span>
            </div>
        </div>
        <div class="border-t border-slate-100 px-5 py-4">
            <a href="{{ route('materials.index', ['category_id' => $materialCategory->id]) }}"
               class="btn-secondary w-full justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
                </svg>
                Lihat Material Kategori Ini
            </a>
        </div>
    </x-data-card>

</div>
@endsection
