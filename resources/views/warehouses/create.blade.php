@extends('layouts.app')

@section('content')
<x-page-header title="Tambah Gudang Baru" subtitle="Daftarkan lokasi penyimpanan material baru">
    <x-slot name="actions">
        <a href="{{ route('warehouses.index') }}" class="btn-secondary">← Kembali</a>
    </x-slot>
</x-page-header>

<div class="max-w-2xl">
    <x-data-card title="Detail Gudang" subtitle="Isi informasi gudang yang akan didaftarkan">
        <div class="px-6 py-5">
            <form method="POST" action="{{ route('warehouses.store') }}" class="space-y-5">
                @csrf
                <div class="grid grid-cols-2 gap-5">
                    <div class="col-span-2">
                        <label class="form-label-required">Nama Gudang</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               class="form-input @error('name') border-rose-400 @enderror"
                               placeholder="Gudang Pusat, Gudang Lapangan A...">
                        @error('name')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">Kode Gudang</label>
                        <input type="text" name="code" value="{{ old('code') }}" maxlength="50"
                               class="form-input @error('code') border-rose-400 @enderror"
                               placeholder="GDG-01">
                        @error('code')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">Nama Penanggung Jawab</label>
                        <input type="text" name="manager_name" value="{{ old('manager_name') }}"
                               class="form-input @error('manager_name') border-rose-400 @enderror"
                               placeholder="Nama PJ gudang">
                        @error('manager_name')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="col-span-2">
                        <label class="form-label">Lokasi / Alamat</label>
                        <input type="text" name="location" value="{{ old('location') }}"
                               class="form-input @error('location') border-rose-400 @enderror"
                               placeholder="Jl. Contoh No.1, Kota...">
                        @error('location')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">No. Telepon</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" maxlength="20"
                               class="form-input @error('phone') border-rose-400 @enderror"
                               placeholder="08xx-xxxx-xxxx">
                        @error('phone')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex items-center gap-3 pt-6">
                        <input type="checkbox" name="is_active" id="is_active" value="1"
                               {{ old('is_active', '1') === '1' ? 'checked' : '' }}
                               class="w-5 h-5 rounded text-indigo-600 border-slate-300 focus:ring-indigo-500">
                        <label for="is_active" class="text-sm font-medium text-slate-700">Gudang Aktif</label>
                    </div>
                    <div class="col-span-2">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" rows="3"
                                  class="form-input resize-none"
                                  placeholder="Keterangan tambahan tentang gudang ini...">{{ old('description') }}</textarea>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex gap-3 justify-end">
                    <a href="{{ route('warehouses.index') }}" class="btn-secondary">Batal</a>
                    <button type="submit" class="btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z"/>
                        </svg>
                        Simpan Gudang
                    </button>
                </div>
            </form>
        </div>
    </x-data-card>
</div>
@endsection
