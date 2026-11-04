@extends('layouts.app')

@section('content')
<x-page-header
    title="Report Pembelian"
    subtitle="Insight pembelian berdasarkan transaksi, supplier, proyek, material, dan kelengkapan data."
/>

<div class="space-y-6">
    <x-data-card class="p-5">
        <form method="GET" action="{{ route('purchase-reports.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-3 xl:grid-cols-4">
            <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                    Tanggal Mulai
                </label>
                <input
                    type="date"
                    name="start_date"
                    value="{{ $startDate }}"
                    class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-medium text-slate-700 outline-none transition focus:border-indigo-300 focus:bg-white focus:ring-4 focus:ring-indigo-100"
                >
            </div>

            <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                    Tanggal Selesai
                </label>
                <input
                    type="date"
                    name="end_date"
                    value="{{ $endDate }}"
                    class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-medium text-slate-700 outline-none transition focus:border-indigo-300 focus:bg-white focus:ring-4 focus:ring-indigo-100"
                >
            </div>

            <div class="flex items-end gap-3">
                <button
                    type="submit"
                    class="inline-flex h-12 items-center justify-center rounded-2xl bg-slate-900 px-5 text-sm font-semibold text-white shadow-lg shadow-slate-900/10 transition hover:bg-slate-800"
                >
                    Terapkan Filter
                </button>

                <a
                    href="{{ route('purchase-reports.index') }}"
                    class="inline-flex h-12 items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                >
                    Reset
                </a>
            </div>
        </form>
    </x-data-card>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        <x-data-card class="p-5">
            <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Total Pembelian</div>
            <div class="mt-2 text-2xl font-extrabold text-slate-900">
                Rp {{ number_format($summary['total_grand_total'], 0, ',', '.') }}
            </div>
        </x-data-card>

        <x-data-card class="p-5">
            <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Total Transaksi</div>
            <div class="mt-2 text-2xl font-extrabold text-slate-900">
                {{ $summary['total_transactions'] }}
            </div>
        </x-data-card>

        <x-data-card class="p-5">
            <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Supplier Aktif</div>
            <div class="mt-2 text-2xl font-extrabold text-slate-900">
                {{ $summary['supplier_count'] }}
            </div>
        </x-data-card>

        <x-data-card class="p-5">
            <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Proyek Aktif</div>
            <div class="mt-2 text-2xl font-extrabold text-slate-900">
                {{ $summary['project_count'] }}
            </div>
        </x-data-card>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
        <x-data-card class="p-0 overflow-hidden xl:col-span-4">
            <div class="border-b border-slate-200 px-5 py-4">
                <h3 class="text-base font-bold text-slate-900">Status Transaksi</h3>
            </div>
            <div class="space-y-3 p-5">
                <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <span class="text-sm font-medium text-slate-700">Draft</span>
                    <span class="text-sm font-bold text-slate-900">{{ $summary['draft_count'] }}</span>
                </div>
                <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <span class="text-sm font-medium text-slate-700">Submitted</span>
                    <span class="text-sm font-bold text-slate-900">{{ $summary['submitted_count'] }}</span>
                </div>
                <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <span class="text-sm font-medium text-slate-700">Posted</span>
                    <span class="text-sm font-bold text-slate-900">{{ $summary['posted_count'] }}</span>
                </div>
            </div>
        </x-data-card>

        <x-data-card class="p-0 overflow-hidden xl:col-span-4">
            <div class="border-b border-slate-200 px-5 py-4">
                <h3 class="text-base font-bold text-slate-900">Kelengkapan Lampiran</h3>
            </div>
            <div class="space-y-3 p-5">
                <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <span class="text-sm font-medium text-slate-700">Dengan Lampiran</span>
                    <span class="text-sm font-bold text-slate-900">{{ $attachmentInsights['with_attachment'] }}</span>
                </div>
                <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <span class="text-sm font-medium text-slate-700">Tanpa Lampiran</span>
                    <span class="text-sm font-bold text-slate-900">{{ $attachmentInsights['without_attachment'] }}</span>
                </div>
            </div>
        </x-data-card>

        <x-data-card class="p-0 overflow-hidden xl:col-span-4">
            <div class="border-b border-slate-200 px-5 py-4">
                <h3 class="text-base font-bold text-slate-900">Master Data</h3>
            </div>
            <div class="space-y-3 p-5">
                <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <span class="text-sm font-medium text-slate-700">Material Aktif</span>
                    <span class="text-sm font-bold text-slate-900">{{ $summary['material_count'] }}</span>
                </div>
            </div>
        </x-data-card>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <x-data-card class="p-0 overflow-hidden">
            <div class="border-b border-slate-200 px-5 py-4">
                <h3 class="text-base font-bold text-slate-900">Top Supplier</h3>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($topSuppliers as $row)
                    <div class="flex items-center justify-between px-5 py-4">
                        <div>
                            <div class="text-sm font-bold text-slate-900">{{ $row->supplier?->name ?? 'Tanpa Supplier' }}</div>
                            <div class="text-xs text-slate-500">{{ $row->total_transactions }} transaksi</div>
                        </div>
                        <div class="text-sm font-bold text-slate-900">
                            Rp {{ number_format($row->total_amount, 0, ',', '.') }}
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-6 text-sm text-slate-500">Belum ada data supplier.</div>
                @endforelse
            </div>
        </x-data-card>

        <x-data-card class="p-0 overflow-hidden">
            <div class="border-b border-slate-200 px-5 py-4">
                <h3 class="text-base font-bold text-slate-900">Top Proyek</h3>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($topProjects as $row)
                    <div class="flex items-center justify-between px-5 py-4">
                        <div>
                            <div class="text-sm font-bold text-slate-900">{{ $row->project?->name ?? 'Tanpa Proyek' }}</div>
                            <div class="text-xs text-slate-500">{{ $row->total_transactions }} transaksi</div>
                        </div>
                        <div class="text-sm font-bold text-slate-900">
                            Rp {{ number_format($row->total_amount, 0, ',', '.') }}
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-6 text-sm text-slate-500">Belum ada data proyek.</div>
                @endforelse
            </div>
        </x-data-card>
    </div>

    <x-data-card class="p-0 overflow-hidden">
        <div class="border-b border-slate-200 px-5 py-4">
            <h3 class="text-base font-bold text-slate-900">Top Material</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-[0.16em] text-slate-500">Material</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-[0.16em] text-slate-500">Satuan</th>
                        <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-[0.16em] text-slate-500">Total Qty</th>
                        <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-[0.16em] text-slate-500">Total Nilai</th>
                        <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-[0.16em] text-slate-500">Baris</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($topMaterials as $row)
                        <tr>
                            <td class="px-4 py-3 text-sm font-semibold text-slate-900">{{ $row->material_name_snapshot }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $row->unit_name_snapshot }}</td>
                            <td class="px-4 py-3 text-right text-sm text-slate-700">{{ rtrim(rtrim(number_format($row->total_qty, 2, '.', ''), '0'), '.') }}</td>
                            <td class="px-4 py-3 text-right text-sm font-bold text-slate-900">Rp {{ number_format($row->total_amount, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right text-sm text-slate-700">{{ $row->total_rows }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-sm text-slate-500">
                                Belum ada data material.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-data-card>
</div>
@endsection