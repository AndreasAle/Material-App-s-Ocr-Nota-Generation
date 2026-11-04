@extends('layouts.app')

@section('content')

{{-- Header --}}
<x-page-header title="{{ $rabPlan->name }}" subtitle="{{ $rabPlan->project->name ?? '-' }}">
    <x-slot name="actions">
        <div class="flex flex-wrap items-center gap-2">
            @if($rabPlan->isDraft())
            <form method="POST" action="{{ route('rab-plans.activate', $rabPlan) }}">
                @csrf
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-2xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md transition hover:-translate-y-0.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.636 5.636a9 9 0 1 0 12.728 0M12 3v9"/>
                    </svg>
                    Aktifkan RAB
                </button>
            </form>
            @endif

            @if($rabPlan->isActive())
            <form method="POST" action="{{ route('rab-plans.close', $rabPlan) }}"
                  onsubmit="return confirm('Tutup RAB ini? Status tidak dapat dikembalikan ke aktif.')">
                @csrf
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    Tutup RAB
                </button>
            </form>
            @endif

            @if(! $rabPlan->isClosed())
            <a href="{{ route('rab-plans.edit', $rabPlan) }}"
               class="inline-flex items-center gap-2 rounded-2xl border border-indigo-200 bg-indigo-50 px-4 py-2.5 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/>
                </svg>
                Edit
            </a>
            @endif

            {{-- Revisi RAB --}}
            @if($rabPlan->is_latest_version ?? true)
            <form method="POST" action="{{ route('rab-plans.revise', $rabPlan) }}"
                  onsubmit="return confirm('Buat revisi RAB? Versi baru (v{{ ($rabPlan->version ?? 1) + 1 }}) akan dibuat sebagai draft.')">
                @csrf
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-sm font-semibold text-amber-700 transition hover:bg-amber-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/>
                    </svg>
                    Buat Revisi
                </button>
            </form>
            @endif

            {{-- Simpan sebagai Template --}}
            <div x-data="{ showTemplate: false }">
                <button @click="showTemplate = true"
                    class="inline-flex items-center gap-2 rounded-2xl border border-indigo-200 bg-indigo-50 px-4 py-2.5 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-100">
                    📋 Simpan Template
                </button>
                <div x-show="showTemplate" x-cloak class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center" @click.self="showTemplate = false">
                    <div class="bg-white rounded-[22px] shadow-xl p-6 w-full max-w-sm mx-4">
                        <h4 class="font-bold text-slate-800 mb-3">Simpan sebagai Template</h4>
                        <form method="POST" action="{{ route('rab-plans.save-as-template', $rabPlan) }}">
                            @csrf
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Template <span class="text-rose-500">*</span></label>
                            <input type="text" name="template_name" required
                                   class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-400 mb-4"
                                   placeholder="Template Proyek Rumah Tinggal...">
                            <div class="flex justify-end gap-2">
                                <button type="button" @click="showTemplate = false" class="btn-secondary">Batal</button>
                                <button type="submit" class="btn-primary">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Version Badge --}}
            @if(isset($rabPlan->version) && $rabPlan->version > 1)
            <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-100 px-3 py-2 text-xs font-bold text-slate-600">
                📋 Versi {{ $rabPlan->version }}
                @if(! ($rabPlan->is_latest_version ?? true))<span class="text-amber-600">(Bukan Terbaru)</span>@endif
            </span>
            @endif
        </div>
    </x-slot>
</x-page-header>

{{-- Status + Meta Cards --}}
<div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">

    <div class="rounded-[22px] border border-slate-200 bg-white p-4 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Status</p>
        <div class="mt-2">
            @if($rabPlan->status === 'active')
                <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Aktif
                </span>
            @elseif($rabPlan->status === 'draft')
                <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-400"></span> Draft
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-500">
                    <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span> Ditutup
                </span>
            @endif
        </div>
    </div>

    <div class="rounded-[22px] border border-slate-200 bg-white p-4 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Anggaran</p>
        <p class="mt-2 text-lg font-extrabold text-slate-900">Rp {{ number_format($grandBudget, 0, ',', '.') }}</p>
    </div>

    <div class="rounded-[22px] border border-slate-200 bg-white p-4 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Realisasi</p>
        <p class="mt-2 text-lg font-extrabold
            {{ $grandPct > 90 ? 'text-rose-600' : ($grandPct > 70 ? 'text-amber-600' : 'text-emerald-600') }}">
            Rp {{ number_format($grandRealization, 0, ',', '.') }}
        </p>
        <p class="text-xs text-slate-400">{{ $grandPct }}% dari anggaran</p>
    </div>

    <div class="rounded-[22px] border border-slate-200 bg-white p-4 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Sisa Anggaran</p>
        <p class="mt-2 text-lg font-extrabold {{ $grandSisa < 0 ? 'text-rose-600' : 'text-slate-900' }}">
            Rp {{ number_format(abs($grandSisa), 0, ',', '.') }}
            @if($grandSisa < 0)<span class="text-xs font-normal">(melebihi)</span>@endif
        </p>
    </div>

</div>

{{-- Progress Bar Grand Total --}}
<x-data-card class="mb-6">
    <div class="px-5 py-4">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-semibold text-slate-700">Utilisasi Anggaran Keseluruhan</span>
            <span class="text-sm font-bold {{ $grandPct > 90 ? 'text-rose-600' : ($grandPct > 70 ? 'text-amber-600' : 'text-emerald-600') }}">
                {{ $grandPct }}%
            </span>
        </div>
        <div class="h-3 w-full overflow-hidden rounded-full bg-slate-100">
            <div class="h-full rounded-full transition-all
                {{ $grandPct > 90 ? 'bg-rose-500' : ($grandPct > 70 ? 'bg-amber-400' : 'bg-emerald-500') }}"
                style="width: {{ min($grandPct, 100) }}%">
            </div>
        </div>
        <div class="mt-2 flex justify-between text-xs text-slate-400">
            <span>Periode: {{ $rabPlan->period_start->format('d M Y') }}
                @if($rabPlan->period_end) &mdash; {{ $rabPlan->period_end->format('d M Y') }} @endif
            </span>
            <span>Dibuat oleh: {{ $rabPlan->creator->name ?? '-' }}</span>
        </div>
    </div>
</x-data-card>

{{-- Detail Items: Budget vs Aktual --}}
<x-data-card>
    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
        <h3 class="text-sm font-bold text-slate-800">Budget vs Realisasi per Item</h3>
        <span class="text-xs text-slate-400">{{ $summaryItems->count() }} item</span>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50/80 border-b border-slate-100">
                <tr>
                    <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-[0.18em] text-slate-500">Material</th>
                    <th class="px-5 py-4 text-right text-xs font-bold uppercase tracking-[0.18em] text-slate-500">Budget Qty</th>
                    <th class="px-5 py-4 text-right text-xs font-bold uppercase tracking-[0.18em] text-slate-500">Budget Total</th>
                    <th class="px-5 py-4 text-right text-xs font-bold uppercase tracking-[0.18em] text-slate-500">Realisasi Qty</th>
                    <th class="px-5 py-4 text-right text-xs font-bold uppercase tracking-[0.18em] text-slate-500">Realisasi Total</th>
                    <th class="px-5 py-4 text-right text-xs font-bold uppercase tracking-[0.18em] text-slate-500">Sisa</th>
                    <th class="px-5 py-4 text-center text-xs font-bold uppercase tracking-[0.18em] text-slate-500 min-w-[120px]">Utilisasi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($summaryItems as $row)
                @php
                    $item = $row['item'];
                    $pct  = $row['pct'];
                    $color = $pct > 90 ? 'rose' : ($pct > 70 ? 'amber' : 'emerald');
                    $barColor = $pct > 90 ? 'bg-rose-500' : ($pct > 70 ? 'bg-amber-400' : 'bg-emerald-500');
                    $textColor = $pct > 90 ? 'text-rose-600' : ($pct > 70 ? 'text-amber-600' : 'text-emerald-600');
                @endphp
                <tr class="hover:bg-slate-50/80 transition-colors">
                    <td class="px-5 py-4">
                        <div class="font-medium text-slate-900">{{ $item->material_name_snapshot }}</div>
                        <div class="text-xs text-slate-400">{{ $item->unit_name_snapshot }}</div>
                    </td>
                    <td class="px-5 py-4 text-right text-slate-700">
                        {{ number_format($item->qty_planned, 2, ',', '.') }}
                    </td>
                    <td class="px-5 py-4 text-right font-medium text-slate-800">
                        Rp {{ number_format($item->subtotal_planned, 0, ',', '.') }}
                    </td>
                    <td class="px-5 py-4 text-right text-slate-700">
                        @if($row['real_qty'] > 0)
                            {{ number_format($row['real_qty'], 2, ',', '.') }}
                        @else
                            <span class="text-slate-300">&mdash;</span>
                        @endif
                    </td>
                    <td class="px-5 py-4 text-right font-medium {{ $row['real_subtotal'] > 0 ? $textColor : 'text-slate-300' }}">
                        @if($row['real_subtotal'] > 0)
                            Rp {{ number_format($row['real_subtotal'], 0, ',', '.') }}
                        @else
                            &mdash;
                        @endif
                    </td>
                    <td class="px-5 py-4 text-right font-medium {{ $row['sisa'] < 0 ? 'text-rose-600' : 'text-slate-700' }}">
                        @if($row['sisa'] < 0)
                            <span class="text-xs">(+)</span> Rp {{ number_format(abs($row['sisa']), 0, ',', '.') }}
                        @else
                            Rp {{ number_format($row['sisa'], 0, ',', '.') }}
                        @endif
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-2">
                            <div class="flex-1 h-2 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full {{ $barColor }}"
                                     style="width: {{ min($pct, 100) }}%"></div>
                            </div>
                            <span class="w-10 shrink-0 text-right text-xs font-semibold {{ $textColor }}">
                                {{ $pct }}%
                            </span>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-slate-50 border-t-2 border-slate-200">
                <tr>
                    <td colspan="2" class="px-5 py-4 text-right text-sm font-bold text-slate-700">Total</td>
                    <td class="px-5 py-4 text-right text-sm font-bold text-slate-900">
                        Rp {{ number_format($grandBudget, 0, ',', '.') }}
                    </td>
                    <td class="px-5 py-4"></td>
                    <td class="px-5 py-4 text-right text-sm font-bold {{ $grandPct > 90 ? 'text-rose-600' : ($grandPct > 70 ? 'text-amber-600' : 'text-emerald-700') }}">
                        Rp {{ number_format($grandRealization, 0, ',', '.') }}
                    </td>
                    <td class="px-5 py-4 text-right text-sm font-bold {{ $grandSisa < 0 ? 'text-rose-600' : 'text-slate-900' }}">
                        Rp {{ number_format(abs($grandSisa), 0, ',', '.') }}
                        @if($grandSisa < 0) <span class="text-xs font-normal">(lebih)</span> @endif
                    </td>
                    <td class="px-5 py-4 text-center">
                        <span class="text-sm font-bold {{ $grandPct > 90 ? 'text-rose-600' : ($grandPct > 70 ? 'text-amber-600' : 'text-emerald-600') }}">
                            {{ $grandPct }}%
                        </span>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</x-data-card>

@endsection
