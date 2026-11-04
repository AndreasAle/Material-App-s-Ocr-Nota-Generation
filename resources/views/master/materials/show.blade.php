@extends('layouts.app')
@section('title', 'Material: ' . $material->name)

@section('content')
<x-page-header title="{{ $material->name }}" subtitle="{{ $material->category?->name ?? 'Tanpa kategori' }}">
    <x-slot name="actions">
        <a href="{{ route('materials.index') }}" class="btn-secondary">← Kembali</a>
        <a href="{{ route('materials.edit', $material) }}" class="btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/>
            </svg>
            Edit Material
        </a>
    </x-slot>
</x-page-header>

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">

    <x-data-card title="Informasi Material">
        <div class="px-5 py-4 space-y-3 text-sm">
            <div class="flex items-center justify-between gap-3">
                <span class="text-slate-500">Kode</span>
                <span class="font-mono font-semibold text-slate-800">{{ $material->code ?: '—' }}</span>
            </div>
            <div class="flex items-center justify-between gap-3">
                <span class="text-slate-500">Nama</span>
                <span class="font-semibold text-slate-800">{{ $material->name }}</span>
            </div>
            <div class="flex items-center justify-between gap-3">
                <span class="text-slate-500">Kategori</span>
                <span class="text-slate-700">{{ $material->category?->name ?? '—' }}</span>
            </div>
            <div class="flex items-center justify-between gap-3">
                <span class="text-slate-500">Satuan</span>
                <span class="text-slate-700">{{ $material->unit?->name ?? '—' }}</span>
            </div>
            <div class="flex items-center justify-between gap-3">
                <span class="text-slate-500">Harga Default</span>
                <span class="font-semibold text-slate-800">Rp {{ number_format($material->default_price, 0, ',', '.') }}</span>
            </div>
            <div class="flex items-center justify-between gap-3">
                <span class="text-slate-500">Status</span>
                @if($material->is_active)
                    <span class="badge-success">Aktif</span>
                @else
                    <span class="badge-neutral">Nonaktif</span>
                @endif
            </div>
            @if($material->specification)
                <div class="pt-2 border-t border-slate-100">
                    <p class="text-xs font-semibold text-slate-500 mb-1.5">Spesifikasi</p>
                    <p class="text-slate-700 leading-relaxed">{{ $material->specification }}</p>
                </div>
            @endif
        </div>
    </x-data-card>

    <x-data-card title="Stok & Aktivitas">
        <div class="px-5 py-4 space-y-3 text-sm">
            <div class="flex items-center justify-between gap-3">
                <span class="text-slate-500">Stok Saat Ini</span>
                <a href="{{ route('stock.show', $material) }}" class="font-bold text-indigo-600 hover:underline">
                    {{ number_format($material->current_stock ?? 0, 2, ',', '.') }} {{ $material->unit?->name }}
                </a>
            </div>
            <div class="flex items-center justify-between gap-3">
                <span class="text-slate-500">Stok Minimum</span>
                <span class="text-slate-700">
                    {{ $material->minimum_stock !== null ? number_format($material->minimum_stock, 2, ',', '.') . ' ' . $material->unit?->name : '—' }}
                </span>
            </div>
            <div class="flex items-center justify-between gap-3">
                <span class="text-slate-500">Dibuat</span>
                <span class="text-slate-400 text-xs">{{ $material->created_at->format('d M Y') }}</span>
            </div>
            <div class="flex items-center justify-between gap-3">
                <span class="text-slate-500">Diperbarui</span>
                <span class="text-slate-400 text-xs">{{ $material->updated_at->format('d M Y') }}</span>
            </div>
        </div>
        <div class="border-t border-slate-100 px-5 py-4">
            <a href="{{ route('stock.show', $material) }}" class="btn-secondary w-full justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941"/>
                </svg>
                Lihat Riwayat Stok
            </a>
        </div>
    </x-data-card>

</div>
@endsection
