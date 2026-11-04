@extends('layouts.app')
@section('title', 'Pengaturan Akun')

@section('content')
<x-page-header title="Pengaturan Akun" subtitle="Kelola informasi profil dan keamanan akun Anda." />



<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- Left: Forms --}}
    <div class="xl:col-span-2 space-y-5">

        {{-- Profile Form --}}
        <x-data-card title="Informasi Profil" subtitle="Perbarui data utama akun Anda.">
            <form method="POST" action="{{ route('settings.profile.update') }}" class="px-6 py-5 space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="form-label-required">Nama Lengkap</label>
                    <input type="text" id="name" name="name"
                           value="{{ old('name', $user->name) }}"
                           placeholder="Masukkan nama lengkap"
                           class="form-input @error('name') border-rose-300 bg-rose-50 @enderror">
                    @error('name')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" value="{{ $user->email }}" disabled
                           class="form-input opacity-60 cursor-not-allowed bg-slate-100">
                    <p class="mt-1.5 text-xs text-slate-400">Email tidak dapat diubah untuk menjaga konsistensi data login.</p>
                </div>

                <div>
                    <label for="whatsapp_number" class="form-label">Nomor WhatsApp</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                                <path d="M12 0C5.373 0 0 5.373 0 12c0 2.127.558 4.126 1.534 5.856L0 24l6.29-1.509C8.005 23.447 9.96 24 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-1.846 0-3.576-.484-5.068-1.33l-.362-.215-3.734.896.934-3.637-.237-.375A9.946 9.946 0 0 1 2 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/>
                            </svg>
                        </span>
                        <input type="text" id="whatsapp_number" name="whatsapp_number"
                               value="{{ old('whatsapp_number', $user->whatsapp_number) }}"
                               placeholder="Contoh: 08123456789 atau +628123456789"
                               class="form-input pl-10 @error('whatsapp_number') border-rose-300 bg-rose-50 @enderror">
                    </div>
                    <p class="mt-1.5 text-xs text-slate-400">Digunakan untuk menerima notifikasi approval dan alert anggaran via WhatsApp.</p>
                    @error('whatsapp_number')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end pt-1">
                    <button type="submit" class="btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Simpan Profil
                    </button>
                </div>
            </form>
        </x-data-card>

        {{-- WhatsApp Config Card --}}
        @php
            $fontneConfigured = !empty(config('services.fonnte.token'));
            $waNumber = $user->whatsapp_number;
        @endphp
        <x-data-card>
            <div class="px-6 pt-5 pb-2 flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                        <path d="M12 0C5.373 0 0 5.373 0 12c0 2.127.558 4.126 1.534 5.856L0 24l6.29-1.509C8.005 23.447 9.96 24 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-1.846 0-3.576-.484-5.068-1.33l-.362-.215-3.734.896.934-3.637-.237-.375A9.946 9.946 0 0 1 2 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-slate-800">Notifikasi WhatsApp</p>
                    <p class="text-xs text-slate-400">Status koneksi Fonnte Gateway & uji kirim pesan</p>
                </div>
            </div>

            <div class="px-6 py-4 space-y-3">
                {{-- Status Token --}}
                <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50 px-4 py-3">
                    <div class="flex items-center gap-2.5">
                        <div class="h-2.5 w-2.5 rounded-full {{ $fontneConfigured ? 'bg-emerald-400 animate-pulse' : 'bg-rose-400' }}"></div>
                        <span class="text-sm font-medium text-slate-700">Token Fonnte</span>
                    </div>
                    @if($fontneConfigured)
                        <span class="badge-success text-xs">Terkonfigurasi</span>
                    @else
                        <span class="badge-danger text-xs">Belum Diisi</span>
                    @endif
                </div>

                {{-- Status WA Number --}}
                <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50 px-4 py-3">
                    <div class="flex items-center gap-2.5">
                        <div class="h-2.5 w-2.5 rounded-full {{ $waNumber ? 'bg-emerald-400' : 'bg-amber-400' }}"></div>
                        <span class="text-sm font-medium text-slate-700">Nomor WA Anda</span>
                    </div>
                    <span class="text-sm text-slate-600 font-mono">{{ $waNumber ?: '— belum diisi' }}</span>
                </div>

                @if(!$fontneConfigured)
                    {{-- Panduan Token --}}
                    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3.5 text-sm text-amber-800 space-y-1.5">
                        <p class="font-semibold flex items-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Cara mendapatkan token Fonnte:
                        </p>
                        <ol class="list-decimal list-inside space-y-1 pl-1 text-amber-700">
                            <li>Login ke <strong>dashboard.fonnte.com</strong></li>
                            <li>Di baris device Anda, klik tombol <strong>"Token"</strong></li>
                            <li>Copy token yang muncul</li>
                            <li>Buka file <code class="bg-amber-100 px-1 rounded">.env</code> di root project</li>
                            <li>Isi: <code class="bg-amber-100 px-1 rounded">FONNTE_TOKEN=paste_token_disini</code></li>
                            <li>Jalankan: <code class="bg-amber-100 px-1 rounded">php artisan config:clear</code></li>
                        </ol>
                    </div>
                @else
                    {{-- Tombol Test --}}
                    <form method="POST" action="{{ route('settings.test-whatsapp') }}" class="pt-1">
                        @csrf
                        <button type="submit"
                                @if(!$waNumber) disabled @endif
                                class="w-full flex items-center justify-center gap-2 rounded-xl border-2 border-emerald-300 bg-emerald-50 px-4 py-2.5 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100 disabled:opacity-40 disabled:cursor-not-allowed">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            {{ $waNumber ? 'Kirim Pesan Tes ke ' . $waNumber : 'Isi nomor WA dulu di form di atas' }}
                        </button>
                    </form>
                    @if(!$waNumber)
                        <p class="text-xs text-slate-400 text-center">Isi dan simpan nomor WhatsApp Anda di form Informasi Profil terlebih dahulu.</p>
                    @endif
                @endif
            </div>
        </x-data-card>

        {{-- Password Form --}}
        <x-data-card title="Keamanan Akun" subtitle="Ganti password untuk menjaga keamanan akun Anda.">
            <form method="POST" action="{{ route('settings.password.update') }}" class="px-6 py-5 space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="current_password" class="form-label-required">Password Saat Ini</label>
                    <input type="password" id="current_password" name="current_password"
                           placeholder="Masukkan password saat ini"
                           class="form-input @error('current_password') border-rose-300 bg-rose-50 @enderror">
                    @error('current_password')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="form-label-required">Password Baru</label>
                    <input type="password" id="password" name="password"
                           placeholder="Masukkan password baru (min. 8 karakter)"
                           class="form-input @error('password') border-rose-300 bg-rose-50 @enderror">
                    @error('password')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="form-label-required">Konfirmasi Password Baru</label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                           placeholder="Ulangi password baru"
                           class="form-input">
                </div>

                <div class="flex justify-end pt-1">
                    <button type="submit" class="btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                        </svg>
                        Ganti Password
                    </button>
                </div>
            </form>
        </x-data-card>
    </div>

    {{-- Right: Account Summary --}}
    <div class="space-y-5">

        {{-- User Card --}}
        <x-data-card>
            <div class="px-6 py-6 text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-indigo-100 text-indigo-700 font-bold text-2xl">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <p class="font-bold text-slate-900 text-base">{{ $user->name }}</p>
                <p class="mt-0.5 text-sm text-slate-400">{{ $user->email }}</p>
                <div class="mt-3 flex items-center justify-center gap-2">
                    @php
                        $roleBadge = match($user->role) {
                            'admin'    => 'badge-info',
                            'pimpinan' => 'badge-purple',
                            default    => 'badge-neutral',
                        };
                        $roleLabel = match($user->role) {
                            'admin'    => 'Admin',
                            'pimpinan' => 'Pimpinan',
                            default    => 'Staff',
                        };
                    @endphp
                    <span class="{{ $roleBadge }}">{{ $roleLabel }}</span>
                    @if($user->is_active)
                        <span class="badge-success">Aktif</span>
                    @else
                        <span class="badge-danger">Nonaktif</span>
                    @endif
                </div>
            </div>
            <div class="border-t border-slate-100 px-6 py-4 space-y-3">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-500">WhatsApp</span>
                    <span class="font-medium text-slate-700">{{ $user->whatsapp_number ?: '—' }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-500">Bergabung</span>
                    <span class="font-medium text-slate-700">{{ $user->created_at->format('d M Y') }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-500">ID User</span>
                    <span class="font-mono text-xs text-slate-400">#{{ $user->id }}</span>
                </div>
            </div>
        </x-data-card>

        {{-- Tips --}}
        <x-data-card title="Tips Keamanan">
            <ul class="px-6 py-5 space-y-3">
                <li class="flex items-start gap-2.5 text-sm text-slate-600">
                    <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-indigo-50 text-indigo-600 text-xs font-bold">1</span>
                    Pastikan nama akun sesuai dengan pengguna sistem.
                </li>
                <li class="flex items-start gap-2.5 text-sm text-slate-600">
                    <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-indigo-50 text-indigo-600 text-xs font-bold">2</span>
                    Gunakan password yang kuat, minimal 8 karakter dengan kombinasi huruf dan angka.
                </li>
                <li class="flex items-start gap-2.5 text-sm text-slate-600">
                    <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-indigo-50 text-indigo-600 text-xs font-bold">3</span>
                    Setelah password diganti, gunakan password baru saat login berikutnya.
                </li>
                <li class="flex items-start gap-2.5 text-sm text-slate-600">
                    <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-indigo-50 text-indigo-600 text-xs font-bold">4</span>
                    Tambahkan nomor WhatsApp untuk menerima notifikasi penting.
                </li>
            </ul>
        </x-data-card>

    </div>
</div>
@endsection
