@extends('layouts.app')
@section('title', 'Perbandingan Penawaran ' . $rfq->rfq_no)

@section('content')
<x-page-header :title="'Perbandingan Penawaran — ' . $rfq->rfq_no"
               subtitle="Bandingkan harga dari setiap supplier">
    <x-slot name="actions">
        <a href="{{ route('rfq.show', $rfq) }}" class="btn-secondary">← Kembali ke RFQ</a>
    </x-slot>
</x-page-header>

@php
    $responses = $rfq->responses->where('status', 'submitted')->values();
    $items     = $rfq->items;
@endphp

@if($responses->count() < 2)
<div class="rounded-2xl border border-amber-200 bg-amber-50 p-6 text-center">
    <p class="text-amber-800 font-semibold">Minimal 2 penawaran diperlukan untuk perbandingan.</p>
    <a href="{{ route('rfq.show', $rfq) }}" class="mt-3 inline-block text-sm text-indigo-600 hover:underline">← Kembali ke RFQ</a>
</div>
@else

{{-- Summary Cards --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    @foreach($responses as $resp)
    @php
        $isLowest = $resp->total_price == $responses->min('total_price');
    @endphp
    <div class="rounded-2xl border p-4 {{ $isLowest ? 'border-emerald-300 bg-emerald-50' : 'border-slate-200 bg-slate-50' }}">
        <p class="text-xs {{ $isLowest ? 'text-emerald-600' : 'text-slate-500' }} font-medium mb-1">
            {{ $resp->supplier?->name ?? '—' }}
            @if($isLowest) 🏆 @endif
        </p>
        <p class="text-lg font-bold {{ $isLowest ? 'text-emerald-700' : 'text-slate-800' }}">
            Rp {{ number_format($resp->total_price, 0, ',', '.') }}
        </p>
        <p class="text-xs {{ $isLowest ? 'text-emerald-500' : 'text-slate-400' }} mt-1">
            {{ $resp->payment_terms_label ?? $resp->payment_terms ?? '—' }}
            @if($resp->delivery_days) · {{ $resp->delivery_days }}h @endif
        </p>
    </div>
    @endforeach
</div>

{{-- Comparison Table --}}
<x-data-card title="Detail Perbandingan per Item">
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Material</th>
                    <th class="th-right">Qty</th>
                    <th class="th-right">Budget</th>
                    @foreach($responses as $resp)
                    <th class="th-right text-center">{{ $resp->supplier?->name ?? '—' }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                @php
                    $prices = [];
                    foreach ($responses as $resp) {
                        $ri = $resp->items->firstWhere('rfq_item_id', $item->id);
                        $prices[$resp->id] = $ri?->unit_price ?? null;
                    }
                    $minPrice = collect($prices)->filter()->min();
@endphp
                <tr>
                    <td class="font-medium text-sm text-slate-800">
                        {{ $item->material_name_snapshot }}
                        @if($item->specification)
                        <div class="text-xs text-slate-400 mt-0.5">{{ $item->specification }}</div>
                        @endif
                    </td>
                    <td class="text-right font-mono text-sm">{{ number_format($item->qty_required, 2, ',', '.') }} {{ $item->unit_name_snapshot }}</td>
                    <td class="text-right text-sm text-slate-500">{{ $item->budget_price ? 'Rp ' . number_format($item->budget_price, 0, ',', '.') : '—' }}</td>
                    @foreach($responses as $resp)
                    @php $price = $prices[$resp->id] ?? null; $isMin = $price && $price == $minPrice; @endphp
                    <td class="text-right text-sm {{ $isMin ? 'text-emerald-600 font-bold bg-emerald-50' : 'text-slate-700' }}">
                        @if($price)
                            Rp {{ number_format($price, 0, ',', '.') }}
                            @if($isMin) <span class="text-xs">✓</span> @endif
                        @else
                            <span class="text-slate-300">—</span>
                        @endif
                    </td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-right font-bold text-slate-700">Total</td>
                    @foreach($responses as $resp)
                    @php $isLowest = $resp->total_price == $responses->min('total_price'); @endphp
                    <td class="text-right font-bold text-base {{ $isLowest ? 'text-emerald-600 bg-emerald-50' : 'text-slate-800' }}">
                        Rp {{ number_format($resp->total_price, 0, ',', '.') }}
                    </td>
                    @endforeach
                </tr>
            </tfoot>
        </table>
    </div>
</x-data-card>

{{-- Award Form --}}
@if(in_array(auth()->user()->role, ['admin','pimpinan']))
<div class="mt-6">
    <x-data-card>
        <div class="px-5 py-4">
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Pilih Pemenang & Buat PO</h3>
            <form method="POST" action="{{ route('rfq.award', $rfq) }}" class="flex items-end gap-3 flex-wrap">
                @csrf
                <div class="flex-1 min-w-48">
                    <label class="form-label">Pilih Penawaran</label>
                    <select name="response_id" class="form-input" required>
                        <option value="">— Pilih supplier pemenang —</option>
                        @foreach($responses as $resp)
                        <option value="{{ $resp->id }}">
                            {{ $resp->supplier?->name }} — Rp {{ number_format($resp->total_price, 0, ',', '.') }}
                            @if($resp->total_price == $responses->min('total_price')) (Termurah) @endif
                        </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn-success whitespace-nowrap"
                        onclick="return confirm('Award RFQ ke supplier ini dan buat PO?')">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Award & Buat PO
                </button>
            </form>
        </div>
    </x-data-card>
</div>
@endif

@endif
@endsection
