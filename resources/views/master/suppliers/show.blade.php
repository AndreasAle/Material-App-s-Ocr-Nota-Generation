@extends('layouts.app')
@section('title', 'Supplier: ' . $supplier->name)

@section('content')
<x-page-header title="{{ $supplier->name }}" subtitle="{{ $supplier->city ?? 'Supplier' }}">
    <x-slot name="actions">
        <a href="{{ route('suppliers.price-history', $supplier) }}" class="btn-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75"/>
            </svg>
            Riwayat Harga
        </a>
        <a href="{{ route('suppliers.edit', $supplier) }}" class="btn-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/>
            </svg>
            Edit
        </a>
        <a href="{{ route('suppliers.index') }}" class="btn-secondary">← Kembali</a>
    </x-slot>
</x-page-header>

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">

    <x-data-card title="Informasi Supplier">
        <div class="px-5 py-4 space-y-3 text-sm">
            <div class="flex items-start justify-between gap-3">
                <span class="text-slate-500 shrink-0">Nama</span>
                <span class="font-semibold text-slate-800 text-right">{{ $supplier->name }}</span>
            </div>
            <div class="flex items-center justify-between gap-3">
                <span class="text-slate-500">Telepon</span>
                <span class="text-slate-700">{{ $supplier->phone ?: '—' }}</span>
            </div>
            <div class="flex items-center justify-between gap-3">
                <span class="text-slate-500">Email</span>
                <span class="text-slate-700">{{ $supplier->email ?: '—' }}</span>
            </div>
            <div class="flex items-center justify-between gap-3">
                <span class="text-slate-500">Kota</span>
                <span class="text-slate-700">{{ $supplier->city ?: '—' }}</span>
            </div>
            @if($supplier->address)
                <div class="pt-2 border-t border-slate-100">
                    <p class="text-xs font-semibold text-slate-500 mb-1">Alamat</p>
                    <p class="text-slate-700">{{ $supplier->address }}</p>
                </div>
            @endif
            @if($supplier->notes)
                <div class="pt-2 border-t border-slate-100">
                    <p class="text-xs font-semibold text-slate-500 mb-1">Catatan</p>
                    <p class="text-slate-700">{{ $supplier->notes }}</p>
                </div>
            @endif
        </div>
    </x-data-card>

    <x-data-card title="Statistik">
        <div class="px-5 py-4 space-y-3 text-sm">
            <div class="flex items-center justify-between gap-3">
                <span class="text-slate-500">Status</span>
                @if($supplier->is_active)
                    <span class="badge-success">Aktif</span>
                @else
                    <span class="badge-neutral">Nonaktif</span>
                @endif
            </div>
            <div class="flex items-center justify-between gap-3">
                <span class="text-slate-500">Total Transaksi</span>
                <span class="font-bold text-slate-800">{{ number_format($supplier->purchase_transactions_count ?? 0) }}</span>
            </div>
            <div class="flex items-center justify-between gap-3">
                <span class="text-slate-500">Terdaftar</span>
                <span class="text-slate-400 text-xs">{{ $supplier->created_at->format('d M Y') }}</span>
            </div>
        </div>
        <div class="border-t border-slate-100 px-5 py-4">
            <a href="{{ route('suppliers.price-history', $supplier) }}" class="btn-secondary w-full justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75"/>
                </svg>
                Lihat Riwayat Harga
            </a>
        </div>
    </x-data-card>

</div>
@endsection
