@extends('layouts.app')

@section('title', 'Manajemen User')

@section('content')
<x-page-header title="Manajemen User" subtitle="Kelola akun pengguna, role, dan status akses sistem.">
    <x-slot name="actions">
        <a href="{{ route('users.create') }}" class="btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM3 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 019.374 21c-2.331 0-4.512-.645-6.374-1.766z"/>
            </svg>
            Tambah User
        </a>
    </x-slot>
</x-page-header>

{{-- Stats --}}
<div class="mb-5 grid grid-cols-2 gap-4 sm:grid-cols-5">
    <div class="rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm text-center">
        <p class="text-2xl font-bold text-slate-800">{{ $stats['total'] }}</p>
        <p class="text-xs text-slate-500 mt-0.5">Total User</p>
    </div>
    <div class="rounded-2xl border border-indigo-200 bg-indigo-50 px-5 py-4 shadow-sm text-center">
        <p class="text-2xl font-bold text-indigo-700">{{ $stats['admin'] }}</p>
        <p class="text-xs text-indigo-500 mt-0.5">Admin</p>
    </div>
    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 shadow-sm text-center">
        <p class="text-2xl font-bold text-emerald-700">{{ $stats['staff'] }}</p>
        <p class="text-xs text-emerald-500 mt-0.5">Staff</p>
    </div>
    <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 shadow-sm text-center">
        <p class="text-2xl font-bold text-amber-700">{{ $stats['pimpinan'] }}</p>
        <p class="text-xs text-amber-500 mt-0.5">Pimpinan</p>
    </div>
    <div class="rounded-2xl border {{ $stats['inactive'] > 0 ? 'border-rose-200 bg-rose-50' : 'border-slate-200 bg-white' }} px-5 py-4 shadow-sm text-center">
        <p class="text-2xl font-bold {{ $stats['inactive'] > 0 ? 'text-rose-600' : 'text-slate-400' }}">{{ $stats['inactive'] }}</p>
        <p class="text-xs {{ $stats['inactive'] > 0 ? 'text-rose-500' : 'text-slate-400' }} mt-0.5">Tidak Aktif</p>
    </div>
</div>

{{-- Filter --}}
<x-data-card class="mb-5">
    <div class="px-5 py-4">
        <form method="GET" action="{{ route('users.index') }}" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="form-label">Cari User</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Nama atau email..."
                       class="form-input">
            </div>
            <div class="w-40">
                <label class="form-label">Role</label>
                <select name="role" class="form-select">
                    <option value="">Semua Role</option>
                    <option value="admin"    @selected($role === 'admin')>Admin</option>
                    <option value="staff"    @selected($role === 'staff')>Staff</option>
                    <option value="pimpinan" @selected($role === 'pimpinan')>Pimpinan</option>
                    <option value="purchasing" @selected($role === 'purchasing')>Purchasing</option>
                    <option value="kasir" @selected($role === 'kasir')>Kasir</option>
                </select>
            </div>
            <div class="w-40">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">Semua</option>
                    <option value="1" @selected($status === '1')>Aktif</option>
                    <option value="0" @selected($status === '0')>Tidak Aktif</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary">Cari</button>
                <a href="{{ route('users.index') }}" class="btn-secondary">Reset</a>
            </div>
        </form>
    </div>
</x-data-card>

{{-- Table --}}
<x-data-card title="Daftar User" subtitle="{{ $users->total() }} pengguna">
    @if ($users->isEmpty())
        <div class="flex flex-col items-center justify-center py-14 text-center">
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                </svg>
            </div>
            <p class="mt-3 font-semibold text-slate-700">Tidak ada user ditemukan</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th class="th-center">Status</th>
                        <th class="th-center">Transaksi</th>
                        <th>WhatsApp</th>
                        <th>Bergabung</th>
                        <th class="th-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $u)
                        @php
                            $roleBadge = match($u->role) {
                                'admin'    => 'badge-info',
                                'pimpinan' => 'badge-purple',
                                'kasir' => 'badge-green',
                                default    => 'badge-neutral',
                            };
                            $roleLabel = match($u->role) {
                                'admin'    => 'Admin',
                                'pimpinan' => 'Pimpinan',
                                'kasir' => 'Kasir',
                                'purchasing' => 'Purchasing',
                                default    => 'Staff',
                            };
                            $isSelf = $u->id === auth()->id();
                        @endphp
                        <tr class="{{ ! $u->is_active ? 'opacity-60' : '' }}">
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-indigo-700 font-bold text-sm">
                                        {{ strtoupper(substr($u->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <a href="{{ route('users.show', $u) }}" class="font-semibold text-slate-800 hover:text-indigo-600 transition-colors">
                                            {{ $u->name }}
                                            @if ($isSelf)
                                                <span class="text-xs text-indigo-400">(Anda)</span>
                                            @endif
                                        </a>
                                        <p class="text-xs text-slate-400">{{ $u->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="{{ $roleBadge }}">{{ $roleLabel }}</span>
                            </td>
                            <td class="text-center">
                                @if ($u->is_active)
                                    <span class="badge-success">Aktif</span>
                                @else
                                    <span class="badge-danger">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-center font-semibold text-slate-700">
                                {{ $u->purchase_transactions_count }}
                            </td>
                            <td class="text-slate-500 text-sm">
                                {{ $u->whatsapp_number ?: '—' }}
                            </td>
                            <td class="text-slate-400 text-xs">
                                {{ $u->created_at->format('d M Y') }}
                            </td>
                            <td>
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('users.show', $u) }}"
                                       class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition-colors"
                                       title="Lihat Detail">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.641 0-8.573-3.007-9.964-7.178z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                    </a>
                                    <a href="{{ route('users.edit', $u) }}"
                                       class="p-1.5 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition-colors"
                                       title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/>
                                        </svg>
                                    </a>
                                    @if (! $isSelf)
                                        <form method="POST" action="{{ route('users.toggle-active', $u) }}" class="inline">
                                            @csrf
                                            <button type="submit"
                                                    class="p-1.5 rounded-lg transition-colors {{ $u->is_active ? 'text-slate-400 hover:text-rose-600 hover:bg-rose-50' : 'text-slate-400 hover:text-emerald-600 hover:bg-emerald-50' }}"
                                                    title="{{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                @if ($u->is_active)
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                    </svg>
                                                @else
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                @endif
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-100 px-5 py-3">
            {{ $users->links() }}
        </div>
    @endif
</x-data-card>
@endsection
