@extends('layouts.app')
@section('title', 'RFQ ' . $rfq->rfq_no)

@section('content')
@php
    $badge = ['draft'=>'badge-neutral','sent'=>'badge-info','awarded'=>'badge-success','closed'=>'badge-neutral','cancelled'=>'badge-danger'];
    $label = ['draft'=>'Draft','sent'=>'Dikirim','awarded'=>'✅ Awarded','closed'=>'Ditutup','cancelled'=>'Dibatalkan'];
    $user = auth()->user();
@endphp

<x-page-header :title="'RFQ ' . $rfq->rfq_no" subtitle="{{ $rfq->project?->name ?? 'Tanpa proyek' }}">
    <x-slot name="actions">
        <span class="{{ $badge[$rfq->status] ?? 'badge-neutral' }}">{{ $label[$rfq->status] ?? $rfq->status }}</span>
        @if($rfq->status === 'sent' && $rfq->responses->where('status','submitted')->count() >= 2)
        <a href="{{ route('rfq.compare', $rfq) }}" class="btn-secondary">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125Z"/></svg>
            Bandingkan Penawaran
        </a>
        @endif
        <a href="{{ route('rfq.index') }}" class="btn-secondary">← Kembali</a>
    </x-slot>
</x-page-header>


<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Left Sidebar --}}
    <div class="space-y-5">

        {{-- Info --}}
        <x-data-card title="Detail RFQ">
            <div class="px-5 py-4 space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-500">No. RFQ</span>
                    <span class="font-mono font-bold text-indigo-600">{{ $rfq->rfq_no }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Proyek</span>
                    <span class="font-medium text-slate-800">{{ $rfq->project?->name ?? '—' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Deadline</span>
                    <span class="{{ $rfq->isExpired() && $rfq->status==='sent' ? 'text-rose-600 font-semibold' : 'text-slate-700' }}">
                        {{ $rfq->deadline?->format('d M Y') ?? '—' }}
                        @if($rfq->isExpired() && $rfq->status==='sent') <span class="text-xs">(Lewat)</span> @endif
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Penawaran Masuk</span>
                    <span class="font-semibold text-emerald-600">{{ $rfq->responses->where('status','submitted')->count() }} / {{ $rfq->responses->count() }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Dibuat oleh</span>
                    <span class="text-slate-700">{{ $rfq->creator?->name ?? '—' }}</span>
                </div>
                @if($rfq->awardedSupplier)
                <div class="border-t border-slate-100 pt-3">
                    <p class="text-xs font-semibold text-emerald-700 mb-1">Pemenang</p>
                    <p class="font-bold text-emerald-700">{{ $rfq->awardedSupplier->name }}</p>
                    @if($rfq->convertedPo)
                    <a href="{{ route('purchase-orders.show', $rfq->convertedPo) }}"
                       class="text-xs text-indigo-600 hover:underline font-mono mt-1 block">
                        → PO {{ $rfq->convertedPo->po_no }}
                    </a>
                    @endif
                </div>
                @endif
            </div>
        </x-data-card>

        {{-- Actions --}}
        @if($rfq->status === 'draft')
        <x-data-card title="Kirim ke Supplier">
            <form method="POST" action="{{ route('rfq.send', $rfq) }}" class="px-5 py-4">
                @csrf
                <p class="text-xs text-slate-500 mb-3">Pilih supplier yang akan menerima RFQ ini</p>
                <div class="space-y-1.5 max-h-52 overflow-y-auto mb-4">
                    @foreach($suppliers as $sup)
                    <label class="flex items-center gap-2.5 cursor-pointer rounded-lg px-2 py-1.5 hover:bg-slate-50 transition">
                        <input type="checkbox" name="supplier_ids[]" value="{{ $sup->id }}" class="rounded border-slate-300">
                        <span class="text-sm text-slate-700">{{ $sup->name }}</span>
                    </label>
                    @endforeach
                </div>
                @error('supplier_ids')<p class="form-error mb-2">{{ $message }}</p>@enderror
                <button type="submit" class="btn-primary w-full justify-center text-sm">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                    Kirim RFQ
                </button>
            </form>
        </x-data-card>
        @endif

        @if($rfq->status === 'sent')
        <x-data-card title="Award Pemenang">
            <form method="POST" action="{{ route('rfq.award', $rfq) }}" class="px-5 py-4">
                @csrf
                <p class="text-xs text-slate-500 mb-3">Pilih penawaran terbaik dan buat PO</p>
                @php $submitted = $rfq->responses->where('status','submitted'); @endphp
                @if($submitted->count() > 0)
                <select name="response_id" class="form-input text-sm mb-3" required>
                    <option value="">— Pilih penawaran —</option>
                    @foreach($submitted as $resp)
                    <option value="{{ $resp->id }}">
                        {{ $resp->supplier?->name }} — Rp {{ number_format($resp->total_price, 0, ',', '.') }}
                    </option>
                    @endforeach
                </select>
                <button type="submit" class="btn-success w-full justify-center text-sm"
                        onclick="return confirm('Pilih pemenang dan buat PO?')">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Award & Buat PO
                </button>
                @else
                <p class="text-xs text-slate-400 text-center py-3">Belum ada penawaran yang masuk</p>
                @endif
            </form>
        </x-data-card>
        @endif

        @if(in_array($rfq->status, ['draft','sent']))
        <form method="POST" action="{{ route('rfq.close', $rfq) }}">
            @csrf
            <input type="hidden" name="reason" value="Ditutup manual">
            <button type="submit" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-500 hover:bg-slate-50 transition"
                    onclick="return confirm('Tutup RFQ ini?')">
                Tutup RFQ
            </button>
        </form>
        @endif

    </div>

    {{-- Right: Items + Responses --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Items --}}
        <x-data-card title="Item yang Diminta" subtitle="{{ $rfq->items->count() }} item">
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Material</th>
                            <th>Satuan</th>
                            <th class="th-right">Qty Diperlukan</th>
                            <th class="th-right">Budget Harga</th>
                            <th>Spesifikasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rfq->items as $i => $item)
                        <tr>
                            <td class="text-slate-400 text-xs">{{ $i + 1 }}</td>
                            <td class="font-medium text-slate-800 text-sm">{{ $item->material_name_snapshot }}</td>
                            <td class="text-sm text-slate-600">{{ $item->unit_name_snapshot }}</td>
                            <td class="text-right font-mono text-sm">{{ number_format($item->qty_required, 2, ',', '.') }}</td>
                            <td class="text-right text-sm">
                                {{ $item->budget_price ? 'Rp ' . number_format($item->budget_price, 0, ',', '.') : '—' }}
                            </td>
                            <td class="text-xs text-slate-500">{{ $item->specification ?: '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-data-card>

        {{-- Responses --}}
        <x-data-card title="Penawaran Supplier" subtitle="{{ $rfq->responses->count() }} supplier diundang">
            @forelse($rfq->responses as $resp)
            @php
                $rBadge = ['pending'=>'badge-neutral','submitted'=>'badge-success','accepted'=>'badge-success','rejected'=>'badge-danger'];
                $rLabel = ['pending'=>'⏳ Menunggu','submitted'=>'✅ Masuk','accepted'=>'Dipilih','rejected'=>'Ditolak'];
            @endphp
            <div class="px-5 py-4 border-t border-slate-100 first:border-t-0">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="font-semibold text-slate-800">{{ $resp->supplier?->name ?? '—' }}</p>
                        @if($resp->status === 'submitted')
                        <p class="text-sm text-slate-600 mt-0.5">
                            Total: <strong class="text-emerald-700">Rp {{ number_format($resp->total_price, 0, ',', '.') }}</strong>
                            — Term: <span class="text-xs font-semibold">{{ $resp->payment_terms_label ?? ucfirst($resp->payment_terms ?? '—') }}</span>
                            @if($resp->delivery_days) — {{ $resp->delivery_days }} hari kirim @endif
                        </p>
                        @if($resp->notes)
                        <p class="text-xs text-slate-500 mt-1">{{ $resp->notes }}</p>
                        @endif
                        @endif
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <span class="{{ $rBadge[$resp->status] ?? 'badge-neutral' }} text-xs">{{ $rLabel[$resp->status] ?? $resp->status }}</span>
                        @if(in_array($rfq->status, ['sent']))
                        <a href="{{ route('rfq.edit-response', [$rfq, $resp]) }}"
                           class="text-xs text-indigo-600 hover:underline font-semibold">
                            {{ $resp->status === 'submitted' ? 'Edit' : 'Input Penawaran' }}
                        </a>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="px-5 py-10 text-center text-slate-400 text-sm">
                Belum ada supplier yang diundang. Kirim RFQ ke supplier dari panel kiri.
            </div>
            @endforelse
        </x-data-card>

    </div>
</div>
@endsection
