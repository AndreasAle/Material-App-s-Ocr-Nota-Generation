@extends('layouts.app')
@section('title', 'Tambah User')

@section('content')
<x-page-header title="Tambah User Baru" subtitle="Buat akun pengguna dan assign role akses.">
    <x-slot name="actions">
        <a href="{{ route('users.index') }}" class="btn-secondary">← Kembali</a>
    </x-slot>
</x-page-header>

<div class="max-w-2xl">
    <x-data-card title="Informasi Akun Baru">
        <form method="POST" action="{{ route('users.store') }}" class="px-6 py-5 space-y-5">
            @csrf

            <div>
                <label class="form-label-required">Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name') }}"
                       placeholder="Nama lengkap user"
                       class="form-input @error('name') border-rose-300 bg-rose-50 @enderror">
                @error('name')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="form-label-required">Email</label>
                <input type="email" name="email" value="{{ old('email') }}"
                       placeholder="contoh@email.com"
                       class="form-input @error('email') border-rose-300 bg-rose-50 @enderror">
                @error('email')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="form-label-required">Role / Jabatan</label>
                <select name="role" class="form-select @error('role') border-rose-300 bg-rose-50 @enderror">
                    <option value="">-- Pilih Role --</option>
                    <option value="staff"    @selected(old('role') === 'staff')>Staff — Input transaksi, scanner</option>
                    <option value="pimpinan" @selected(old('role') === 'pimpinan')>Pimpinan — Approve/reject transaksi</option>
                    <option value="purchasing" @selected(old('role') === 'purchasing')>Purchasing — PO, supplier, surat jalan</option>
                    <option value="kasir" @selected(old('role') === 'kasir')>Kasir — Hanya penjualan (toko)</option>
                    <option value="admin"    @selected(old('role') === 'admin')>Admin — Akses penuh</option>
                </select>
                @error('role')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="form-label">
                    Nomor WhatsApp
                    <span class="text-xs text-slate-400 font-normal ml-1">(untuk notifikasi)</span>
                </label>
                <input type="text" name="whatsapp_number" value="{{ old('whatsapp_number') }}"
                       placeholder="08123456789 atau +628..."
                       class="form-input @error('whatsapp_number') border-rose-300 bg-rose-50 @enderror">
                @error('whatsapp_number')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="form-label-required">Password</label>
                <input type="password" name="password"
                       placeholder="Minimal 8 karakter"
                       class="form-input @error('password') border-rose-300 bg-rose-50 @enderror">
                @error('password')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="form-label-required">Konfirmasi Password</label>
                <input type="password" name="password_confirmation"
                       placeholder="Ulangi password"
                       class="form-input">
            </div>

            <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" id="is_active"
                       {{ old('is_active', true) ? 'checked' : '' }}
                       class="h-4 w-4 rounded accent-indigo-600">
                <label for="is_active" class="text-sm font-medium text-slate-700 cursor-pointer">
                    User langsung aktif (dapat login segera)
                </label>
            </div>

            <div class="flex gap-3 pt-1">
                <button type="submit" class="btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM3 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 019.374 21c-2.331 0-4.512-.645-6.374-1.766z"/>
                    </svg>
                    Simpan User
                </button>
                <a href="{{ route('users.index') }}" class="btn-secondary">Batal</a>
            </div>
        </form>
    </x-data-card>
</div>
@endsection
