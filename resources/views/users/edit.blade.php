@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
<x-page-header title="Edit User: {{ $user->name }}" subtitle="Perbarui data akun, role, dan status">
    <x-slot name="actions">
        <a href="{{ route('users.show', $user) }}" class="btn-secondary">← Detail User</a>
    </x-slot>
</x-page-header>

<div class="max-w-2xl space-y-6">

    {{-- Edit Data --}}
    <x-data-card title="Data Akun" subtitle="Informasi profil dan hak akses pengguna">
        <div class="px-6 py-5">
            <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="form-label-required">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}"
                           class="form-input @error('name') border-rose-400 @enderror">
                    @error('name')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="form-label-required">Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}"
                           class="form-input @error('email') border-rose-400 @enderror">
                    @error('email')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="form-label-required">Role / Jabatan</label>
                    @if ($user->id === auth()->id())
                        <input type="hidden" name="role" value="{{ $user->role }}">
                        <div class="form-input bg-slate-50 text-slate-500 cursor-not-allowed">
                            {{ ucfirst($user->role) }}
                            <span class="text-xs text-slate-400">(tidak bisa ubah role sendiri)</span>
                        </div>
                    @else
                        <select name="role" class="form-select @error('role') border-rose-400 @enderror">
                            <option value="staff"    @selected(old('role', $user->role) === 'staff')>Staff</option>
                            <option value="pimpinan" @selected(old('role', $user->role) === 'pimpinan')>Pimpinan</option>
                            <option value="purchasing" @selected(old('role', $user->role) === 'purchasing')>Purchasing</option>
                            <option value="kasir" @selected(old('role', $user->role) === 'kasir')>Kasir</option>
                            <option value="admin"    @selected(old('role', $user->role) === 'admin')>Admin</option>
                        </select>
                    @endif
                    @error('role')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="form-label">Nomor WhatsApp</label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                                <path d="M12 0C5.373 0 0 5.373 0 12c0 2.127.555 4.122 1.527 5.855L.057 23.547a.5.5 0 0 0 .396.453l5.938-1.508A11.954 11.954 0 0 0 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.882a9.87 9.87 0 0 1-5.032-1.38l-.36-.214-3.73.948.98-3.617-.235-.374A9.869 9.869 0 0 1 2.118 12C2.118 6.529 6.529 2.118 12 2.118S21.882 6.529 21.882 12 17.471 21.882 12 21.882z"/>
                            </svg>
                        </div>
                        <input type="text" name="whatsapp_number"
                               value="{{ old('whatsapp_number', $user->whatsapp_number) }}"
                               placeholder="08123456789"
                               class="form-input pl-10 @error('whatsapp_number') border-rose-400 @enderror">
                    </div>
                    @error('whatsapp_number')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Status Aktif --}}
                @if ($user->id !== auth()->id())
                    <div>
                        <input type="hidden" name="is_active" value="0">
                        <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 hover:bg-white transition-colors">
                            <input type="checkbox" name="is_active" value="1" id="is_active"
                                   {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                                   class="h-4 w-4 rounded accent-indigo-600">
                            <span class="text-sm font-medium text-slate-700">User aktif (dapat login)</span>
                        </label>
                    </div>
                @else
                    <input type="hidden" name="is_active" value="1">
                @endif

                <div class="flex gap-3 pt-1">
                    <button type="submit" class="btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Simpan Perubahan
                    </button>
                    <a href="{{ route('users.show', $user) }}" class="btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </x-data-card>

    {{-- Reset Password --}}
    <x-data-card title="Reset Password" subtitle="Atur ulang kata sandi pengguna ini">
        <div class="px-6 py-5">
            <form method="POST" action="{{ route('users.reset-password', $user) }}" class="space-y-5"
                  x-data="{ show: false }">
                @csrf

                <div>
                    <label class="form-label-required">Password Baru</label>
                    <input :type="show ? 'text' : 'password'" name="new_password"
                           placeholder="Minimal 8 karakter"
                           class="form-input @error('new_password') border-rose-400 @enderror">
                    @error('new_password')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="form-label-required">Konfirmasi Password Baru</label>
                    <input :type="show ? 'text' : 'password'" name="new_password_confirmation"
                           placeholder="Ulangi password baru" class="form-input">
                </div>

                <div>
                    <label class="flex cursor-pointer items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" @change="show = !show" class="h-3.5 w-3.5 rounded accent-indigo-600">
                        Tampilkan password
                    </label>
                </div>

                <button type="submit" class="btn-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                    </svg>
                    Reset Password
                </button>
            </form>
        </div>
    </x-data-card>

    {{-- Hapus User --}}
    @if ($user->id !== auth()->id())
        <x-data-card x-data="{ confirm: false }">
            <div class="px-6 py-5">
                <h3 class="text-sm font-semibold text-rose-600 mb-1.5">Zona Berbahaya</h3>
                <p class="text-sm text-slate-500 mb-4">
                    Hapus user secara permanen. Jika user memiliki riwayat transaksi, hapus tidak akan diizinkan — gunakan nonaktifkan saja.
                </p>
                <div x-show="!confirm">
                    <button @click="confirm = true" type="button" class="btn-danger">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                        </svg>
                        Hapus User Ini
                    </button>
                </div>
                <div x-show="confirm" class="flex flex-col sm:flex-row items-start sm:items-center gap-3 rounded-xl border border-rose-200 bg-rose-50 p-4">
                    <p class="text-sm text-rose-700 font-medium flex-1">Yakin hapus user <strong>{{ $user->name }}</strong>? Tindakan ini tidak dapat dibatalkan.</p>
                    <div class="flex gap-2">
                        <form method="POST" action="{{ route('users.destroy', $user) }}" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-danger text-sm">Ya, Hapus</button>
                        </form>
                        <button @click="confirm = false" type="button" class="btn-secondary text-sm">Batal</button>
                    </div>
                </div>
            </div>
        </x-data-card>
    @endif

</div>
@endsection
