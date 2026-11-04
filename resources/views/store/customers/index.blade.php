@extends('layouts.app')
@section('content')
@php $rp = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.'); @endphp

<x-page-header title="Pelanggan & Bon" subtitle="Data pelanggan dan hutang (bon) yang belum dibayar">
    <x-slot name="actions">
        <a href="{{ route('store.customers.create') }}" class="btn-primary">+ Pelanggan</a>
    </x-slot>
</x-page-header>

<x-help title="Cara mencatat bon (hutang pembeli)">
    <li>Saat menjual di Kasir, pilih pembayaran <b>Bon</b> dan pilih nama pelanggan.</li>
    <li>Saat pelanggan membayar, buka namanya di sini lalu tekan <b>Simpan Pembayaran</b>. Pembayaran otomatis melunasi bon paling lama dulu.</li>
    <li>Bon yang lewat tempo bisa ditagih dengan tombol <b>Ingatkan via WhatsApp</b>.</li>
</x-help>

<div class="mb-4 rounded-2xl border border-amber-200 bg-amber-50 p-4">
    <p class="text-xs font-bold uppercase tracking-wider text-amber-800">Total bon belum dibayar</p>
    <p class="mt-1 text-2xl font-extrabold text-amber-900">{{ $rp($totalBon) }}</p>
</div>

<x-data-card class="mb-4">
    <form method="GET" class="flex flex-wrap items-center gap-3 px-4 py-4 sm:px-5">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama / no. HP" class="form-input min-w-[180px] flex-1">
        <label class="flex min-h-[44px] items-center gap-2 text-sm font-semibold"><input type="checkbox" name="filter" value="bon" class="h-5 w-5 rounded accent-brand-600" @checked(request('filter') === 'bon')> Hanya yang punya bon</label>
        <button class="btn-primary">Cari</button>
    </form>
</x-data-card>

<div class="space-y-2 md:hidden">
    @forelse($customers as $c)
        <a href="{{ route('store.customers.show', $c) }}" class="card flex items-center gap-3 p-3.5 active:bg-charcoal-50">
            <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-charcoal-100 text-base font-extrabold text-charcoal-700">{{ strtoupper(mb_substr($c->name, 0, 1)) }}</span>
            <span class="min-w-0 flex-1">
                <span class="block truncate text-[15px] font-bold text-charcoal-900">{{ $c->name }}</span>
                <span class="block text-xs text-charcoal-500">{{ $c->phone ?: 'Tanpa no. HP' }}</span>
            </span>
            <span class="shrink-0 text-right">
                @if((float) $c->outstanding > 0)
                    <span class="block text-xs text-amber-700">Bon</span>
                    <span class="block text-[15px] font-extrabold text-amber-700">{{ $rp($c->outstanding) }}</span>
                @else
                    <span class="badge-success">Lunas</span>
                @endif
            </span>
        </a>
    @empty
        <div class="card px-5 py-10 text-center text-sm text-charcoal-500">Belum ada pelanggan.</div>
    @endforelse
    <div class="pt-2">{{ $customers->links() }}</div>
</div>

<x-data-card class="hidden md:block">
    <div class="overflow-x-auto">
        <table class="tbl">
            <thead><tr><th>Nama</th><th>No. HP</th><th class="th-right">Bon</th><th class="th-right">Limit</th><th class="th-center">Status</th></tr></thead>
            <tbody>
            @forelse($customers as $c)
                <tr>
                    <td><a href="{{ route('store.customers.show', $c) }}" class="font-semibold text-indigo-600">{{ $c->name }}</a></td>
                    <td class="text-xs">{{ $c->phone ?: '-' }}</td>
                    <td class="text-right font-semibold {{ (float) $c->outstanding > 0 ? 'text-amber-700' : 'text-slate-400' }}">{{ $rp($c->outstanding) }}</td>
                    <td class="text-right text-xs">{{ $c->credit_limit ? $rp($c->credit_limit) : '-' }}</td>
                    <td class="text-center"><span class="badge {{ $c->is_active ? 'badge-success' : 'badge-neutral' }}">{{ $c->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                </tr>
            @empty
                <tr><td colspan="5" class="py-10 text-center text-slate-400">Belum ada pelanggan.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="border-t border-slate-100 px-5 py-4">{{ $customers->links() }}</div>
</x-data-card>
@endsection
