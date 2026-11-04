@extends('layouts.app')

@section('title', 'Invoice ' . $invoice->invoice_no)

@section('content')
@php
    $statusBadge = match($invoice->payment_status) {
        'paid'    => 'badge-success',
        'partial' => 'badge-info',
        default   => 'badge-warning',
    };
    $statusLabel = match($invoice->payment_status) {
        'paid'    => 'Lunas',
        'partial' => 'Sebagian',
        default   => 'Belum Bayar',
    };
    $remaining = $invoice->remaining_amount;
    $isOverdue = $invoice->isOverdue();
@endphp

<x-page-header title="Invoice {{ $invoice->invoice_no }}" subtitle="{{ $invoice->client_name }}">
    <x-slot name="actions">
        <a href="{{ route('invoices.index') }}" class="btn-secondary">← Daftar Invoice</a>
        <a href="{{ route('invoices.preview-pdf', $invoice) }}" target="_blank" class="btn-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.641 0-8.573-3.007-9.964-7.178z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Preview
        </a>
        <a href="{{ route('invoices.export-pdf', $invoice) }}" class="btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
            </svg>
            Download PDF
        </a>
    </x-slot>
</x-page-header>

{{-- Overdue alert --}}
@if ($isOverdue)
<div class="alert-danger mb-5">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
    </svg>
    <p><strong>Invoice ini melewati jatuh tempo</strong> ({{ $invoice->due_date->format('d M Y') }}). Segera konfirmasi pembayaran ke klien.</p>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Main Invoice Content --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Invoice Header --}}
        <x-data-card>
            <div class="flex flex-wrap items-start justify-between gap-4 p-5 sm:p-6">
                <div>
                    <div class="flex items-center gap-2 mb-3">
                        <h2 class="text-xl font-bold text-slate-800">{{ $invoice->invoice_no }}</h2>
                        <span class="{{ $statusBadge }}">{{ $statusLabel }}</span>
                    </div>
                    <div class="space-y-1 text-sm">
                        <p><span class="text-slate-500 w-28 inline-block">Klien</span><strong class="text-slate-800">{{ $invoice->client_name }}</strong></p>
                        @if ($invoice->client_address)
                            <p><span class="text-slate-500 w-28 inline-block">Alamat</span>{{ $invoice->client_address }}</p>
                        @endif
                        @if ($invoice->client_phone)
                            <p><span class="text-slate-500 w-28 inline-block">Telepon</span>{{ $invoice->client_phone }}</p>
                        @endif
                        <p><span class="text-slate-500 w-28 inline-block">Proyek</span>{{ $invoice->project?->name ?? '—' }}</p>
                    </div>
                </div>
                <div class="text-right text-sm space-y-1">
                    <p><span class="text-slate-500">Tanggal: </span><strong>{{ $invoice->invoice_date->format('d M Y') }}</strong></p>
                    <p><span class="text-slate-500">Jatuh Tempo: </span>
                        <strong class="{{ $isOverdue ? 'text-rose-600' : '' }}">
                            {{ $invoice->due_date?->format('d M Y') ?? '—' }}
                        </strong>
                    </p>
                    <p class="text-xs text-slate-400">Dibuat: {{ $invoice->creator?->name }} · {{ $invoice->created_at->format('d M Y') }}</p>
                </div>
            </div>
        </x-data-card>

        {{-- Items --}}
        <x-data-card title="Rincian Tagihan" subtitle="{{ $invoice->items->count() }} item">
            <div class="overflow-x-auto">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th class="w-8">#</th>
                            <th>Deskripsi</th>
                            <th class="th-center">Satuan</th>
                            <th class="th-right">Qty</th>
                            <th class="th-right">Harga Satuan</th>
                            <th class="th-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($invoice->items as $i => $item)
                            <tr>
                                <td class="text-slate-400 text-xs">{{ $i + 1 }}</td>
                                <td>
                                    <p class="font-medium text-slate-800">{{ $item->description }}</p>
                                    @if ($item->notes) <p class="text-xs text-slate-400 mt-0.5">{{ $item->notes }}</p> @endif
                                </td>
                                <td class="text-center text-slate-500">{{ $item->unit ?: '—' }}</td>
                                <td class="text-right text-slate-600">{{ number_format($item->qty, 2, ',', '.') }}</td>
                                <td class="text-right text-slate-600">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                <td class="text-right font-semibold text-slate-800">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="text-right text-xs text-slate-500 font-medium">Subtotal</td>
                            <td class="text-right text-slate-700 font-medium">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @if ($invoice->discount_amount > 0)
                        <tr>
                            <td colspan="5" class="text-right text-xs text-slate-500">Diskon</td>
                            <td class="text-right text-rose-500">− Rp {{ number_format($invoice->discount_amount, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        @if ($invoice->tax_percent > 0)
                        <tr>
                            <td colspan="5" class="text-right text-xs text-slate-500">PPN {{ $invoice->tax_percent }}%</td>
                            <td class="text-right text-slate-600">+ Rp {{ number_format($invoice->tax_amount, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td colspan="5" class="text-right font-bold text-indigo-700">Grand Total</td>
                            <td class="text-right font-bold text-indigo-700 text-base">Rp {{ number_format($invoice->grand_total, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @if ($invoice->notes)
                <div class="mx-5 mb-5 mt-1 rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                    <p class="text-xs font-semibold text-slate-500 mb-1">Catatan</p>
                    {{ $invoice->notes }}
                </div>
            @endif
        </x-data-card>

    </div>

    {{-- Right: Payment Status --}}
    <div class="space-y-5">
        {{-- Payment Summary --}}
        <x-data-card title="Status Pembayaran">
            <div class="px-5 py-4 space-y-2.5 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-500">Grand Total</span>
                    <span class="font-bold text-slate-800">Rp {{ number_format($invoice->grand_total, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Sudah Dibayar</span>
                    <span class="font-bold text-emerald-600">Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}</span>
                </div>
                <div class="border-t border-slate-100 pt-2.5 flex justify-between">
                    <span class="font-semibold text-slate-700">Sisa Tagihan</span>
                    <span class="font-bold {{ $remaining > 0 ? 'text-amber-600' : 'text-emerald-600' }}">
                        Rp {{ number_format($remaining, 0, ',', '.') }}
                    </span>
                </div>

                {{-- Progress bar --}}
                @php $paidPct = $invoice->grand_total > 0 ? min(100, round(($invoice->paid_amount / $invoice->grand_total) * 100)) : 0; @endphp
                <div class="pt-1">
                    <div class="w-full bg-slate-100 rounded-full h-2 mb-1">
                        <div class="h-2 rounded-full {{ $paidPct >= 100 ? 'bg-emerald-500' : 'bg-indigo-500' }}" style="width: {{ $paidPct }}%"></div>
                    </div>
                    <p class="text-xs text-slate-400 text-right">{{ $paidPct }}% terbayar</p>
                </div>
            </div>
        </x-data-card>

        {{-- Riwayat Pembayaran --}}
        @php $invoicePayments = $invoice->payments ?? collect(); @endphp
        @if($invoicePayments->isNotEmpty())
        <x-data-card title="Riwayat Pembayaran">
            <div class="px-5 py-3 space-y-2">
                @foreach($invoicePayments as $pay)
                <div class="flex items-center justify-between rounded-xl bg-emerald-50 border border-emerald-100 px-3 py-2.5">
                    <div>
                        <p class="text-sm font-bold text-emerald-800">+ Rp {{ number_format($pay->amount, 0, ',', '.') }}</p>
                        <p class="text-xs text-slate-500">{{ \Carbon\Carbon::parse($pay->payment_date)->format('d M Y') }} · {{ ucfirst($pay->payment_method) }}</p>
                        @if($pay->reference_no)
                            <p class="text-xs text-slate-400">Ref: {{ $pay->reference_no }}</p>
                        @endif
                    </div>
                    <form method="POST" action="{{ route('invoice-payments.destroy', [$invoice, $pay]) }}"
                          onsubmit="return confirm('Hapus catatan pembayaran ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-red-400 hover:text-red-600">Hapus</button>
                    </form>
                </div>
                @endforeach
            </div>
        </x-data-card>
        @endif

        {{-- Catat Pembayaran --}}
        @if (! $invoice->isPaid())
        <x-data-card title="Catat Pembayaran">
            <form method="POST" action="{{ route('invoice-payments.store', $invoice) }}" class="px-5 py-4 space-y-3">
                @csrf
                <div>
                    <label class="form-label text-xs font-semibold text-slate-600">Tanggal Pembayaran</label>
                    <input type="date" name="payment_date" value="{{ now()->toDateString() }}" class="form-input" required>
                </div>
                <div>
                    <label class="form-label text-xs font-semibold text-slate-600">Jumlah Pembayaran (Rp)</label>
                    <input type="number" name="amount"
                           value="{{ old('amount', $remaining) }}"
                           step="1000" min="1" max="{{ $remaining }}"
                           class="form-input">
                    @error('amount')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="form-label text-xs font-semibold text-slate-600">Metode Pembayaran</label>
                    <select name="payment_method" class="form-input">
                        <option value="transfer">Transfer Bank</option>
                        <option value="cash">Tunai</option>
                        <option value="giro">Giro</option>
                        <option value="cek">Cek</option>
                    </select>
                </div>
                <div>
                    <label class="form-label text-xs font-semibold text-slate-600">No. Referensi (opsional)</label>
                    <input type="text" name="reference_no" value="{{ old('reference_no') }}"
                           placeholder="No. bukti transfer / kuitansi" class="form-input">
                </div>
                <div>
                    <label class="form-label text-xs font-semibold text-slate-600">Catatan</label>
                    <textarea name="notes" rows="2" placeholder="opsional..." class="form-input">{{ old('notes') }}</textarea>
                </div>
                <button type="submit" class="btn-primary w-full justify-center">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                    </svg>
                    Catat Pembayaran
                </button>
            </form>
        </x-data-card>
        @endif

        {{-- Hapus --}}
        @if (! $invoice->isPaid())
        <x-data-card x-data="{ confirm: false }">
            <div class="px-5 py-4">
                <div x-show="!confirm">
                    <button @click="confirm = true" type="button"
                            class="text-sm text-rose-600 hover:underline">Hapus Invoice</button>
                </div>
                <div x-show="confirm" class="space-y-3">
                    <p class="text-sm text-rose-700 font-medium">Yakin ingin menghapus invoice ini?</p>
                    <div class="flex gap-2">
                        <form method="POST" action="{{ route('invoices.destroy', $invoice) }}">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-secondary text-xs border-rose-200 text-rose-600 hover:bg-rose-50">Ya, Hapus</button>
                        </form>
                        <button @click="confirm = false" class="btn-secondary text-xs">Batal</button>
                    </div>
                </div>
            </div>
        </x-data-card>
        @endif
    </div>
</div>
@endsection
