@extends('layouts.app')

@section('content')
<x-page-header :title="'Edit Gudang: ' . $warehouse->name" subtitle="Perbarui informasi gudang">
    <x-slot name="actions">
        <a href="{{ route('warehouses.show', $warehouse) }}" class="btn-secondary">← Kembali</a>
    </x-slot>
</x-page-header>

<div class="max-w-2xl">
    <x-data-card title="Detail Gudang" subtitle="Ubah informasi gudang yang terdaftar">
        <div class="px-6 py-5">
            <form method="POST" action="{{ route('warehouses.update', $warehouse) }}" class="space-y-5">
                @csrf @method('PUT')
                <div class="grid grid-cols-2 gap-5">
                    <div class="col-span-2">
                        <label class="form-label-required">Nama Gudang</label>
                        <input type="text" name="name" value="{{ old('name', $warehouse->name) }}" required
                               class="form-input @error('name') border-rose-400 @enderror">
                        @error('name')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">Kode Gudang</label>
                        <input type="text" name="code" value="{{ old('code', $warehouse->code) }}" maxlength="50"
                               class="form-input @error('code') border-rose-400 @enderror">
                        @error('code')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">Nama Penanggung Jawab</label>
                        <input type="text" name="manager_name" value="{{ old('manager_name', $warehouse->manager_name) }}"
                               class="form-input @error('manager_name') border-rose-400 @enderror">
                        @error('manager_name')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="col-span-2">
                        <label class="form-label">Lokasi / Alamat</label>
                        <input type="text" name="location" value="{{ old('location', $warehouse->location) }}"
                               class="form-input @error('location') border-rose-400 @enderror">
                        @error('location')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">No. Telepon</label>
                        <input type="text" name="phone" value="{{ old('phone', $warehouse->phone) }}" maxlength="20"
                               class="form-input @error('phone') border-rose-400 @enderror">
                        @error('phone')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex items-center gap-3 pt-6">
                        <input type="checkbox" name="is_active" id="is_active" value="1"
                               {{ old('is_active', $warehouse->is_active) ? 'checked' : '' }}
                               class="w-5 h-5 rounded text-indigo-600 border-slate-300 focus:ring-indigo-500">
                        <label for="is_active" class="text-sm font-medium text-slate-700">Gudang Aktif</label>
                    </div>
                    <div class="col-span-2">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" rows="3"
                                  class="form-input resize-none">{{ old('description', $warehouse->description) }}</textarea>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex gap-3 justify-end">
                    <a href="{{ route('warehouses.show', $warehouse) }}" class="btn-secondary">Batal</a>
                    <button type="submit" class="btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                        </svg>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </x-data-card>

    @if(! $warehouse->stockMovements()->exists())
    <div class="mt-4">
        <x-data-card>
            <div class="px-6 py-4 flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-slate-700">Hapus Gudang</p>
                    <p class="text-xs text-slate-400 mt-0.5">Gudang belum memiliki riwayat stok — dapat dihapus permanen</p>
                </div>
                <form method="POST" action="{{ route('warehouses.destroy', $warehouse) }}"
                      onsubmit="return confirm('Hapus gudang {{ $warehouse->name }}?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-danger">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                        </svg>
                        Hapus Gudang
                    </button>
                </form>
            </div>
        </x-data-card>
    </div>
    @endif
</div>
@endsection
