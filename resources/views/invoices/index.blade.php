@extends('layouts.app')

@section('title', 'Invoice')

@section('content')
<x-page-header title="Invoice" subtitle="Kelola tagihan ke klien, status pembayaran, dan piutang.">
    <x-slot name="actions">
        <a href="{{ route('invoices.create') }}" class="btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Buat Invoice
        </a>
    </x-slot>
</x-page-header>

{{-- Stats --}}
<div class="mb-5 grid grid-cols-2 gap-4 lg:grid-cols-4">
    <div class="rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-slate-500">Total Invoice</p>
                <p class="text-xl font-bold text-slate-800">{{ $stats['total'] }}</p>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 text-amber-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-amber-700">Belum Lunas</p>
                <p class="text-xl font-bold text-amber-700">{{ $stats['unpaid'] + $stats['partial'] }}</p>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border {{ $stats['overdue'] > 0 ? 'border-rose-200 bg-rose-50' : 'border-slate-200 bg-white' }} px-5 py-4 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl {{ $stats['overdue'] > 0 ? 'bg-rose-100 text-rose-600' : 'bg-slate-100 text-slate-400' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs {{ $stats['overdue'] > 0 ? 'text-rose-700' : 'text-slate-500' }}">Terlambat</p>
                <p class="text-xl font-bold {{ $stats['overdue'] > 0 ? 'text-rose-700' : 'text-slate-400' }}">{{ $stats['overdue'] }}</p>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-slate-500">Piutang Tersisa</p>
                <p class="text-sm font-bold text-slate-800 leading-tight mt-0.5">Rp {{ number_format($stats['total_outstanding'], 0, ',', '.') }}</p>
            </div>
        </div>
    </div>
</div>

{{-- Overdue alert --}}
@if ($stats['overdue'] > 0 && $overdue !== '1')
<div class="alert-danger mb-5">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
    </svg>
    <p><strong>{{ $stats['overdue'] }} invoice</strong> melewati jatuh tempo.
        <a href="{{ request()->fullUrlWithQuery(['overdue' => '1', 'payment_status' => '']) }}" class="underline font-semibold ml-1">Tampilkan saja</a>
    </p>
</div>
@endif

{{-- Filter --}}
<x-data-card class="mb-5">
    <div class="px-5 py-4">
        <form method="GET" action="{{ route('invoices.index') }}" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="form-label">Cari Invoice</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="No. Invoice atau nama klien..."
                       class="form-input">
            </div>
            <div class="w-44">
                <label class="form-label">Status</label>
                <select name="payment_status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="unpaid"  @selected($paymentStatus === 'unpaid')>Belum Bayar</option>
                    <option value="partial" @selected($paymentStatus === 'partial')>Sebagian</option>
                    <option value="paid"    @selected($paymentStatus === 'paid')>Lunas</option>
                </select>
            </div>
            <div class="w-44">
                <label class="form-label">Proyek</label>
                <select name="project_id" class="form-select">
                    <option value="">Semua Proyek</option>
                    @foreach ($projects as $p)
                        <option value="{{ $p->id }}" @selected($projectId == $p->id)>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-40">
                <label class="form-label">Dari</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="form-input">
            </div>
            <div class="w-40">
                <label class="form-label">Sampai</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="form-input">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary">Filter</button>
                <a href="{{ route('invoices.index') }}" class="btn-secondary">Reset</a>
            </div>
        </form>
    </div>
</x-data-card>

{{-- Table --}}
<x-data-card title="Daftar Invoice" subtitle="{{ $invoices->total() }} invoice ditemukan">
    @if ($invoices->isEmpty())
        <div class="flex flex-col items-center justify-center py-14 text-center">
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                </svg>
            </div>
            <p class="mt-3 font-semibold text-slate-700">Belum ada invoice</p>
            <p class="mt-1 text-sm text-slate-400">Buat invoice pertama untuk mulai mengelola tagihan.</p>
            <a href="{{ route('invoices.create') }}" class="btn-primary mt-4">Buat Invoice Pertama</a>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>No. Invoice</th>
                        <th>Klien</th>
                        <th>Proyek</th>
                        <th>Tgl Invoice</th>
                        <th>Jatuh Tempo</th>
                        <th class="th-right">Total</th>
                        <th class="th-right">Terbayar</th>
                        <th class="th-center">Status</th>
                        <th class="th-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoices as $inv)
                        @php
                            $isOverdue = $inv->isOverdue();
                            $statusBadge = match($inv->payment_status) {
                                'paid'    => 'badge-success',
                                'partial' => 'badge-info',
                                default   => 'badge-warning',
                            };
                            $statusLabel = match($inv->payment_status) {
                                'paid'    => 'Lunas',
                                'partial' => 'Sebagian',
                                default   => 'Belum Bayar',
                            };
                        @endphp
                        <tr class="{{ $isOverdue ? 'bg-rose-50/30' : '' }}">
                            <td>
                                <a href="{{ route('invoices.show', $inv) }}"
                                   class="font-semibold text-indigo-600 hover:underline">
                                    {{ $inv->invoice_no }}
                                </a>
                                @if ($isOverdue)
                                    <span class="badge-danger mt-0.5 block">Terlambat</span>
                                @endif
                            </td>
                            <td class="font-medium text-slate-700">{{ $inv->client_name }}</td>
                            <td class="text-slate-500 text-xs">{{ $inv->project?->name ?? '—' }}</td>
                            <td class="text-slate-600 text-sm">{{ $inv->invoice_date->format('d M Y') }}</td>
                            <td class="text-sm {{ $isOverdue ? 'font-semibold text-rose-600' : 'text-slate-500' }}">
                                {{ $inv->due_date?->format('d M Y') ?? '—' }}
                            </td>
                            <td class="text-right font-semibold text-slate-800">
                                Rp {{ number_format($inv->grand_total, 0, ',', '.') }}
                            </td>
                            <td class="text-right text-emerald-600 font-medium">
                                Rp {{ number_format($inv->paid_amount, 0, ',', '.') }}
                            </td>
                            <td class="text-center">
                                <span class="{{ $statusBadge }}">{{ $statusLabel }}</span>
                            </td>
                            <td>
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('invoices.show', $inv) }}"
                                       class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition-colors" title="Detail">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.641 0-8.573-3.007-9.964-7.178z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-100 px-5 py-3">
            {{ $invoices->links() }}
        </div>
    @endif
</x-data-card>
@endsection
