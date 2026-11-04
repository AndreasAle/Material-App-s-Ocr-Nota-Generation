@extends('layouts.app')
@section('content')
@php
    $rp = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');
    $phone = preg_replace('/\D/', '', (string) $customer->phone);
    if (str_starts_with($phone, '0')) { $phone = '62' . substr($phone, 1); }
    $waText = rawurlencode("Halo {$customer->name}, kami dari " . config('store.name') . ". Mengingatkan sisa bon Anda sebesar " . $rp($outstanding) . ". Terima kasih.");
@endphp

<x-page-header :title="$customer->name" :subtitle="trim(($customer->phone ?: '') . ($customer->address ? ' · ' . $customer->address : ''))">
    <x-slot name="actions">
        <a href="{{ route('store.customers.edit', $customer) }}" class="btn-secondary">Edit</a>
        @if(auth()->user()->isAdmin())
            <form method="POST" action="{{ route('store.customers.destroy', $customer) }}" onsubmit="return confirm('Hapus pelanggan ini?')">
                @csrf @method('DELETE')
                <button class="btn-danger">Hapus</button>
            </form>
        @endif
    </x-slot>
</x-page-header>

<div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
    <div class="space-y-5">
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5">
            <p class="text-xs text-amber-800">Sisa bon</p>
            <p class="text-3xl font-extrabold text-amber-900">{{ $rp($outstanding) }}</p>
            @if($customer->credit_limit)
                <p class="mt-1 text-xs text-amber-800">Limit {{ $rp($customer->credit_limit) }}</p>
            @endif
            @if($outstanding > 0 && $phone)
                <a href="https://wa.me/{{ $phone }}?text={{ $waText }}" target="_blank" rel="noopener" class="btn-success mt-3 w-full justify-center">Ingatkan via WhatsApp</a>
            @endif
        </div>

        @if($outstanding > 0)
        <x-data-card title="Terima Pembayaran Bon" subtitle="Dialokasikan otomatis ke bon paling lama dulu.">
            <form method="POST" action="{{ route('store.customers.pay', $customer) }}" class="space-y-3 px-5 py-4">
                @csrf
                <input type="number" name="amount" min="1" max="{{ $outstanding }}" step="1" value="{{ $outstanding }}" class="form-input" required>
                <select name="payment_method" class="form-select"><option value="cash">Tunai</option><option value="transfer">Transfer</option></select>
                <input type="text" name="notes" placeholder="Catatan (opsional)" class="form-input">
                @error('amount') <p class="form-error">{{ $message }}</p> @enderror
                <button class="btn-success w-full">Simpan Pembayaran</button>
            </form>
        </x-data-card>
        @endif

        <x-data-card title="Pembayaran Terakhir">
            <div class="divide-y divide-slate-100">
                @forelse($payments as $p)
                    <div class="flex justify-between px-5 py-2 text-xs"><span>{{ $p->payment_date->format('d/m/Y') }} · {{ $p->sale->sale_no ?? '' }}</span><span class="font-semibold">{{ $rp($p->amount) }}</span></div>
                @empty
                    <p class="px-5 py-4 text-xs text-slate-400">Belum ada pembayaran.</p>
                @endforelse
            </div>
        </x-data-card>
    </div>

    <div class="lg:col-span-2">
        <x-data-card title="Riwayat Belanja">
            <div class="overflow-x-auto">
                <table class="tbl">
                    <thead><tr><th>No.</th><th>Tanggal</th><th class="th-right">Total</th><th class="th-right">Sisa</th><th>Jatuh tempo</th></tr></thead>
                    <tbody>
                    @forelse($sales as $s)
                        <tr>
                            <td><a href="{{ route('store.sales.show', $s) }}" class="font-semibold text-indigo-600">{{ $s->sale_no }}</a></td>
                            <td class="text-xs">{{ $s->sale_date->format('d/m/Y') }}</td>
                            <td class="text-right">{{ $rp($s->netTotal()) }}</td>
                            <td class="text-right font-semibold {{ (float) $s->remaining_amount > 0 ? 'text-amber-700' : 'text-emerald-600' }}">{{ (float) $s->remaining_amount > 0 ? $rp($s->remaining_amount) : 'Lunas' }}</td>
                            <td class="text-xs {{ $s->isOverdue() ? 'font-bold text-rose-600' : '' }}">{{ (float) $s->remaining_amount > 0 ? $s->due_date?->format('d/m/Y') : '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-8 text-center text-slate-400">Belum ada belanja.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-5 py-4">{{ $sales->links() }}</div>
        </x-data-card>
    </div>
</div>
@endsection
