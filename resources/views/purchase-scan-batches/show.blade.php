@extends('layouts.app')

@section('content')
@php
    $status = strtolower((string) $purchaseScanBatch->status);

    $statusConfig = match ($status) {
        'processed' => [
            'label' => 'Batch Berhasil',
            'badge' => 'bg-emerald-100 text-emerald-700 border border-emerald-200',
            'panel' => 'border-emerald-200 bg-emerald-50/70',
            'iconWrap' => 'bg-emerald-100 text-emerald-700',
            'title' => 'Multiple Notes berhasil diproses',
            'desc' => 'Sistem berhasil membaca beberapa nota dan menggabungkannya menjadi 1 draft transaksi.',
        ],
        'supplier_mismatch' => [
            'label' => 'Supplier Berbeda',
            'badge' => 'bg-amber-100 text-amber-700 border border-amber-200',
            'panel' => 'border-amber-200 bg-amber-50/70',
            'iconWrap' => 'bg-amber-100 text-amber-700',
            'title' => 'Batch ditolak karena supplier berbeda',
            'desc' => 'Mode Multiple Notes hanya menerima nota dari supplier yang sama.',
        ],
        'failed' => [
            'label' => 'Scan Gagal',
            'badge' => 'bg-rose-100 text-rose-700 border border-rose-200',
            'panel' => 'border-rose-200 bg-rose-50/70',
            'iconWrap' => 'bg-rose-100 text-rose-700',
            'title' => 'Proses batch tidak berhasil',
            'desc' => 'Sistem belum bisa membaca seluruh nota dengan baik.',
        ],
        default => [
            'label' => ucfirst($purchaseScanBatch->status),
            'badge' => 'bg-slate-100 text-slate-700 border border-slate-200',
            'panel' => 'border-slate-200 bg-slate-50/70',
            'iconWrap' => 'bg-slate-100 text-slate-700',
            'title' => 'Status batch tersedia',
            'desc' => 'Silakan review hasil pembacaan Multiple Notes.',
        ],
    };
@endphp

<x-page-header
    title="Review Multiple Notes"
    subtitle="Periksa hasil gabungan beberapa nota sebelum dimasukkan ke transaksi pembelian."
/>

<div class="space-y-6">
    <div class="overflow-hidden rounded-[28px] border {{ $statusConfig['panel'] }} shadow-sm">
        <div class="flex flex-col gap-5 px-6 py-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-start gap-4">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl {{ $statusConfig['iconWrap'] }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>

                <div>
                    <div class="inline-flex rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wide {{ $statusConfig['badge'] }}">
                        {{ $statusConfig['label'] }}
                    </div>

                    <h2 class="mt-3 text-xl font-bold tracking-tight text-slate-900">
                        {{ $statusConfig['title'] }}
                    </h2>

                    <p class="mt-1 max-w-3xl text-sm leading-6 text-slate-600">
                        {{ $statusConfig['desc'] }}
                    </p>

                    <div class="mt-4 flex flex-wrap gap-2">
                        <div class="rounded-full border border-white/70 bg-white/80 px-3 py-1.5 text-xs font-medium text-slate-700">
                            Batch: <span class="font-semibold text-slate-900">{{ $purchaseScanBatch->batch_code }}</span>
                        </div>
                        <div class="rounded-full border border-white/70 bg-white/80 px-3 py-1.5 text-xs font-medium text-slate-700">
                            Supplier: <span class="font-semibold text-slate-900">{{ $supplierName }}</span>
                        </div>
                        <div class="rounded-full border border-white/70 bg-white/80 px-3 py-1.5 text-xs font-medium text-slate-700">
                            Jumlah Nota: <span class="font-semibold text-slate-900">{{ $purchaseScanBatch->total_files }}</span>
                        </div>
                        <div class="rounded-full border border-white/70 bg-white/80 px-3 py-1.5 text-xs font-medium text-slate-700">
                            Jumlah Item: <span class="font-semibold text-slate-900">{{ $totalItems }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row lg:justify-end">
                <a
                    href="{{ route('purchase-scan-batches.create') }}"
                    class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                >
                    Scan Ulang
                </a>

                @if($purchaseScanBatch->status !== 'failed' && $purchaseScanBatch->status !== 'supplier_mismatch' && $totalItems > 0)
                    <a
                        href="{{ route('purchase-scan-batches.compare', $purchaseScanBatch) }}"
                        class="inline-flex items-center justify-center rounded-2xl border border-indigo-200 bg-indigo-50 px-4 py-3 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-100"
                    >
                        Mode Compare
                    </a>
                @endif

                @if(in_array($purchaseScanBatch->status, ['processed', 'reviewed'], true) && $totalItems > 0)
                    <form method="POST" action="{{ route('purchase-scan-batches.import', $purchaseScanBatch) }}">
                        @csrf
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700"
                        >
                            Masukkan ke Transaksi
                        </button>
                    </form>
                @endif

            </div>
        </div>
    </div>

    @if($purchaseScanBatch->status === 'supplier_mismatch')
        <div class="rounded-3xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm leading-6 text-amber-700 shadow-sm">
            Supplier pada nota yang diupload tidak sama, sehingga batch ini tidak bisa digabung menjadi 1 transaksi.
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
        <div class="space-y-6 xl:col-span-8">
            <x-data-card class="overflow-hidden border border-slate-200 p-0 shadow-sm">
                <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 via-white to-slate-50 px-6 py-5">
                    <h3 class="text-lg font-semibold text-slate-900">Ringkasan Batch</h3>
                </div>

                <div class="grid grid-cols-1 gap-4 p-6 md:grid-cols-2 2xl:grid-cols-4">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Batch Code</div>
                        <div class="mt-2 text-base font-bold text-slate-900">{{ $purchaseScanBatch->batch_code }}</div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Tanggal</div>
                        <div class="mt-2 text-base font-bold text-slate-900">{{ $displayDate }}</div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Supplier</div>
                        <div class="mt-2 text-base font-bold text-slate-900">{{ $supplierName }}</div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Grand Total</div>
                        <div class="mt-2 text-base font-bold text-slate-900">Rp {{ number_format((int) $grandTotal, 0, ',', '.') }}</div>
                    </div>
                </div>
            </x-data-card>

            <x-data-card class="overflow-hidden border border-slate-200 p-0 shadow-sm">
                <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 via-white to-slate-50 px-6 py-5">
                    <h3 class="text-lg font-semibold text-slate-900">Daftar Item Gabungan</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">#</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Kode</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Nama Material</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Satuan</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Qty</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Harga</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Subtotal</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Sumber</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse($items as $index => $item)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-slate-700">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-700">{{ $item['kode'] ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm font-medium text-slate-900">{{ $item['nama_material'] ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-700">{{ $item['satuan'] ?? '-' }}</td>
                                    <td class="px-4 py-3 text-right text-sm text-slate-700">{{ $item['qty'] ?? 0 }}</td>
                                    <td class="px-4 py-3 text-right text-sm text-slate-700">Rp {{ number_format((int) ($item['harga'] ?? 0), 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right text-sm font-semibold text-slate-900">Rp {{ number_format((int) ($item['subtotal'] ?? 0), 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-500">{{ $item['_source_file_name'] ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-6 text-center text-sm text-slate-500">
                                        Belum ada item gabungan yang terbaca.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-data-card>
        </div>

        <div class="space-y-6 xl:col-span-4">
            <x-data-card class="overflow-hidden border border-slate-200 p-0 shadow-sm">
                <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 via-white to-slate-50 px-6 py-5">
                    <h3 class="text-lg font-semibold text-slate-900">Daftar Nota</h3>
                </div>

                <div class="space-y-4 p-5">
                    @foreach($filesForView as $file)
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="mb-3 flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="truncate text-sm font-semibold text-slate-900">{{ $file['file_name'] }}</div>
                                    <div class="mt-1 text-xs text-slate-500">Nota #{{ $loop->iteration }}</div>
                                </div>

                                <div class="rounded-full bg-slate-900 px-2.5 py-1 text-[11px] font-medium text-white">
                                    {{ strtoupper($file['status']) }}
                                </div>
                            </div>

                            <div class="space-y-1 text-xs text-slate-600">
                                <div>No Nota: <span class="font-medium text-slate-900">{{ $file['note_number'] ?: '-' }}</span></div>
                                <div>Supplier: <span class="font-medium text-slate-900">{{ $file['supplier_name'] ?: '-' }}</span></div>
                                <div>Tanggal: <span class="font-medium text-slate-900">{{ $file['transaction_date'] }}</span></div>
                                <div>Total: <span class="font-medium text-slate-900">Rp {{ number_format((int) $file['grand_total'], 0, ',', '.') }}</span></div>
                            </div>

                            @if($file['preview_url'])
                                <div class="mt-4">
                                    @if($file['is_image'])
                                        <img src="{{ $file['preview_url'] }}" alt="{{ $file['file_name'] }}" class="w-full rounded-2xl border border-slate-200 bg-white shadow-sm">
                                    @elseif($file['is_pdf'])
                                        <a href="{{ $file['preview_url'] }}" target="_blank" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                                            Buka PDF
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </x-data-card>
        </div>
    </div>
</div>
@endsection