@extends('layouts.app')
@section('title', 'Supplier')
@section('content')

<x-page-header title="Master Supplier" subtitle="Kelola data supplier dan toko pembelian material.">
    <x-slot:actions>
        <a href="{{ route('suppliers.create') }}" class="btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Tambah Supplier
        </a>
    </x-slot:actions>
</x-page-header>

{{-- Filter --}}
<x-data-card class="mb-5">
    <div class="px-5 py-4">
        <form method="GET" action="{{ route('suppliers.index') }}" class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[220px]">
                <label class="form-label">Cari Supplier</label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z"/>
                        </svg>
                    </span>
                    <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Nama supplier atau kota..."
                           class="form-input pl-10">
                </div>
            </div>
            <div class="w-36">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">Semua</option>
                    <option value="1" @selected(request('status') === '1')>Aktif</option>
                    <option value="0" @selected(request('status') === '0')>Nonaktif</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary">Filter</button>
                <a href="{{ route('suppliers.index') }}" class="btn-secondary">Reset</a>
            </div>
        </form>
    </div>
</x-data-card>

{{-- Table --}}
<x-data-card title="Daftar Supplier" subtitle="{{ $suppliers->total() }} supplier terdaftar">
    <div class="overflow-x-auto">
        <table class="tbl">
            <thead>
                <tr>
                    <th>Nama Supplier</th>
                    <th>Telepon</th>
                    <th>Email</th>
                    <th>Kota</th>
                    <th>Status</th>
                    <th class="th-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($suppliers as $item)
                <tr>
                    <td>
                        <a href="{{ route('suppliers.show', $item) }}" class="font-semibold text-slate-900 hover:text-indigo-600 transition-colors">
                            {{ $item->name }}
                        </a>
                        @if($item->address)
                            <p class="mt-0.5 text-xs text-slate-400 truncate max-w-[200px]">{{ $item->address }}</p>
                        @endif
                    </td>
                    <td class="text-slate-600 text-sm">{{ $item->phone ?: '—' }}</td>
                    <td class="text-slate-500 text-xs">{{ $item->email ?: '—' }}</td>
                    <td class="text-slate-600">{{ $item->city ?: '—' }}</td>
                    <td>
                        @if($item->is_active)
                            <span class="badge-success">Aktif</span>
                        @else
                            <span class="badge-neutral">Nonaktif</span>
                        @endif
                    </td>
                    <td>
                        <div class="flex items-center justify-end gap-1.5">
                            <a href="{{ route('suppliers.show', $item) }}"
                               class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition-colors" title="Detail">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.641 0-8.573-3.007-9.964-7.178z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </a>
                            <a href="{{ route('suppliers.price-history', $item) }}"
                               class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition-colors" title="Riwayat Harga">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941"/>
                                </svg>
                            </a>
                            <a href="{{ route('suppliers.edit', $item) }}"
                               class="p-1.5 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition-colors" title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/>
                                </svg>
                            </a>
                            <form method="POST" action="{{ route('suppliers.destroy', $item) }}"
                                  onsubmit="return confirm('Hapus supplier {{ addslashes($item->name) }}?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors" title="Hapus">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="flex flex-col items-center justify-center py-14 text-center">
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M5.25 21V8.25A2.25 2.25 0 0 1 7.5 6h9a2.25 2.25 0 0 1 2.25 2.25V21"/>
                                </svg>
                            </div>
                            <p class="mt-3 font-semibold text-slate-700">Belum ada supplier</p>
                            <p class="mt-1 text-sm text-slate-400">Tambah supplier pertama untuk memulai.</p>
                            <a href="{{ route('suppliers.create') }}" class="btn-primary mt-4">Tambah Supplier</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($suppliers->hasPages())
    <div class="border-t border-slate-100 px-5 py-3">
        {{ $suppliers->links() }}
    </div>
    @endif
</x-data-card>

@endsection
