@extends('layouts.app')
@section('title', 'Purchase Order')

@section('content')
<x-page-header title="Purchase Order" subtitle="Kelola permintaan pembelian dan status persetujuan PO.">
    <x-slot name="actions">
        @if(in_array(auth()->user()->role, ['admin', 'staff']))
            <a href="{{ route('purchase-orders.create') }}" class="btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Buat PO Baru
            </a>
        @endif
    </x-slot>
</x-page-header>

{{-- Stats --}}
<div class="mb-5 grid grid-cols-2 gap-4 sm:grid-cols-5">
    <div class="rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm text-center">
        <p class="text-2xl font-bold text-slate-800">{{ $stats['total'] }}</p>
        <p class="text-xs text-slate-500 mt-0.5">Total PO</p>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 shadow-sm text-center">
        <p class="text-2xl font-bold text-slate-600">{{ $stats['draft'] }}</p>
        <p class="text-xs text-slate-500 mt-0.5">Draft</p>
    </div>
    <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 shadow-sm text-center">
        <p class="text-2xl font-bold text-amber-700">{{ $stats['pending'] }}</p>
        <p class="text-xs text-amber-600 mt-0.5">Menunggu</p>
    </div>
    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 shadow-sm text-center">
        <p class="text-2xl font-bold text-emerald-700">{{ $stats['approved'] }}</p>
        <p class="text-xs text-emerald-600 mt-0.5">Disetujui</p>
    </div>
    <div class="rounded-2xl border border-indigo-200 bg-indigo-50 px-5 py-4 shadow-sm text-center">
        <p class="text-2xl font-bold text-indigo-700">{{ $stats['converted'] }}</p>
        <p class="text-xs text-indigo-600 mt-0.5">Dikonversi</p>
    </div>
</div>

{{-- Filter --}}
<x-data-card class="mb-5">
    <div class="px-5 py-4">
        <form method="GET" action="{{ route('purchase-orders.index') }}" class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[180px]">
                <label class="form-label">Cari PO</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Nomor PO..." class="form-input">
            </div>
            <div class="w-44">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="draft"     @selected(request('status') === 'draft')>Draft</option>
                    <option value="submitted" @selected(request('status') === 'submitted')>Menunggu</option>
                    <option value="approved"  @selected(request('status') === 'approved')>Disetujui</option>
                    <option value="rejected"  @selected(request('status') === 'rejected')>Ditolak</option>
                    <option value="converted" @selected(request('status') === 'converted')>Dikonversi</option>
                </select>
            </div>
            <div class="w-44">
                <label class="form-label">Proyek</label>
                <select name="project_id" class="form-select">
                    <option value="">Semua Proyek</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" @selected(request('project_id') == $project->id)>{{ $project->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-44">
                <label class="form-label">Supplier</label>
                <select name="supplier_id" class="form-select">
                    <option value="">Semua Supplier</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @selected(request('supplier_id') == $supplier->id)>{{ $supplier->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary">Filter</button>
                <a href="{{ route('purchase-orders.index') }}" class="btn-secondary">Reset</a>
            </div>
        </form>
    </div>
</x-data-card>

{{-- Table --}}
<x-data-card title="Daftar Purchase Order" subtitle="{{ $purchaseOrders->total() }} PO ditemukan">
    @if($purchaseOrders->isEmpty())
        <div class="flex flex-col items-center justify-center py-14 text-center">
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
                </svg>
            </div>
            <p class="mt-3 font-semibold text-slate-700">Belum ada Purchase Order</p>
            <p class="mt-1 text-sm text-slate-400">Klik "Buat PO Baru" untuk memulai proses pembelian.</p>
            @if(in_array(auth()->user()->role, ['admin', 'staff']))
                <a href="{{ route('purchase-orders.create') }}" class="btn-primary mt-4">Buat PO Baru</a>
            @endif
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Nomor PO</th>
                        <th>Proyek</th>
                        <th>Supplier</th>
                        <th class="th-right">Total</th>
                        <th class="th-center">Status</th>
                        <th>Dibuat Oleh</th>
                        <th>Tanggal</th>
                        <th class="th-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchaseOrders as $po)
                    @php
                        $statusBadge = [
                            'draft'     => 'badge-neutral',
                            'submitted' => 'badge-warning',
                            'approved'  => 'badge-success',
                            'rejected'  => 'badge-danger',
                            'converted' => 'badge-purple',
                        ];
                        $statusLabel = [
                            'draft'     => 'Draft',
                            'submitted' => 'Menunggu',
                            'approved'  => 'Disetujui',
                            'rejected'  => 'Ditolak',
                            'converted' => 'Dikonversi',
                        ];
                    @endphp
                    <tr>
                        <td>
                            <a href="{{ route('purchase-orders.show', $po) }}"
                               class="font-mono font-semibold text-indigo-600 hover:underline">
                                {{ $po->po_no }}
                            </a>
                        </td>
                        <td class="text-slate-700">{{ $po->project?->name ?? '—' }}</td>
                        <td class="text-slate-700">{{ $po->supplier?->name ?? '—' }}</td>
                        <td class="text-right font-semibold text-slate-900">
                            Rp {{ number_format($po->grand_total, 0, ',', '.') }}
                        </td>
                        <td class="text-center">
                            <span class="{{ $statusBadge[$po->status] ?? 'badge-neutral' }}">
                                {{ $statusLabel[$po->status] ?? $po->status }}
                            </span>
                        </td>
                        <td class="text-slate-600 text-sm">{{ $po->creator?->name ?? '—' }}</td>
                        <td class="text-slate-500 text-xs">{{ $po->created_at->format('d M Y') }}</td>
                        <td>
                            <div class="flex items-center justify-end">
                                <a href="{{ route('purchase-orders.show', $po) }}"
                                   class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition-colors" title="Detail">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.641 0-8.573-3.007-9.964-7.178z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-5 py-3">
            {{ $purchaseOrders->links() }}
        </div>
    @endif
</x-data-card>
@endsection
