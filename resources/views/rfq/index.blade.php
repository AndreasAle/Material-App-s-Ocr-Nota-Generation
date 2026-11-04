@extends('layouts.app')
@section('title', 'Daftar RFQ')

@section('content')
<x-page-header title="Request for Quotation" subtitle="Permintaan penawaran harga ke supplier">
    <x-slot name="actions">
        <a href="{{ route('rfq.create') }}" class="btn-primary">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Buat RFQ Baru
        </a>
    </x-slot>
</x-page-header>


{{-- Stats --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4">
        <p class="text-xs text-slate-500">Total RFQ</p>
        <p class="text-2xl font-bold text-slate-800">{{ $stats['total'] }}</p>
    </div>
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4">
        <p class="text-xs text-amber-600">Draft</p>
        <p class="text-2xl font-bold text-amber-700">{{ $stats['draft'] }}</p>
    </div>
    <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4">
        <p class="text-xs text-blue-600">Dikirim</p>
        <p class="text-2xl font-bold text-blue-700">{{ $stats['sent'] }}</p>
    </div>
    <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4">
        <p class="text-xs text-emerald-600">Awarded</p>
        <p class="text-2xl font-bold text-emerald-700">{{ $stats['awarded'] }}</p>
    </div>
</div>

{{-- Filter --}}
<form method="GET" class="mb-5 flex flex-wrap gap-3">
    <select name="status" onchange="this.form.submit()" class="form-input w-40 text-sm">
        <option value="">Semua Status</option>
        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
        <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Dikirim</option>
        <option value="awarded" {{ request('status') === 'awarded' ? 'selected' : '' }}>Awarded</option>
        <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Ditutup</option>
        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
    </select>
    <select name="project_id" onchange="this.form.submit()" class="form-input w-44 text-sm">
        <option value="">Semua Proyek</option>
        @foreach($projects as $proj)
        <option value="{{ $proj->id }}" {{ request('project_id') == $proj->id ? 'selected' : '' }}>{{ $proj->name }}</option>
        @endforeach
    </select>
</form>

<x-data-card>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>No. RFQ</th>
                    <th>Proyek</th>
                    <th class="th-center">Item</th>
                    <th class="th-center">Penawaran</th>
                    <th>Deadline</th>
                    <th class="th-center">Status</th>
                    <th>Dibuat</th>
                    <th class="th-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rfqs as $rfq)
                @php
                    $badge = [
                        'draft'     => 'badge-neutral',
                        'sent'      => 'badge-info',
                        'awarded'   => 'badge-success',
                        'closed'    => 'badge-neutral',
                        'cancelled' => 'badge-danger',
                    ];
                    $label = [
                        'draft'     => 'Draft',
                        'sent'      => 'Dikirim',
                        'awarded'   => '✅ Awarded',
                        'closed'    => 'Ditutup',
                        'cancelled' => 'Dibatalkan',
                    ];
                @endphp
                <tr>
                    <td>
                        <a href="{{ route('rfq.show', $rfq) }}" class="font-mono font-bold text-indigo-600 hover:underline text-sm">
                            {{ $rfq->rfq_no }}
                        </a>
                    </td>
                    <td class="text-sm text-slate-700">{{ $rfq->project?->name ?? '—' }}</td>
                    <td class="text-center text-sm font-semibold">{{ $rfq->items->count() }}</td>
                    <td class="text-center text-sm">
                        @php $submitted = $rfq->responses->where('status','submitted')->count(); @endphp
                        <span class="font-semibold {{ $submitted > 0 ? 'text-emerald-600' : 'text-slate-400' }}">
                            {{ $submitted }} / {{ $rfq->responses->count() }}
                        </span>
                    </td>
                    <td class="text-sm {{ $rfq->deadline?->isPast() && $rfq->status === 'sent' ? 'text-rose-600 font-semibold' : 'text-slate-700' }}">
                        {{ $rfq->deadline?->format('d M Y') ?? '—' }}
                    </td>
                    <td class="text-center">
                        <span class="{{ $badge[$rfq->status] ?? 'badge-neutral' }} text-xs">
                            {{ $label[$rfq->status] ?? $rfq->status }}
                        </span>
                    </td>
                    <td class="text-xs text-slate-500">{{ $rfq->creator?->name ?? '—' }}</td>
                    <td class="text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('rfq.show', $rfq) }}"
                               class="text-xs text-indigo-600 hover:underline font-semibold">Lihat</a>
                            @if($rfq->status === 'sent' && $rfq->responses->where('status','submitted')->count() >= 2)
                            <a href="{{ route('rfq.compare', $rfq) }}"
                               class="text-xs text-violet-600 hover:underline font-semibold">Bandingkan</a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="py-12 text-center text-slate-400 text-sm">
                        Belum ada RFQ. <a href="{{ route('rfq.create') }}" class="text-indigo-600 hover:underline">Buat RFQ pertama</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($rfqs->hasPages())
    <div class="px-5 py-4 border-t border-slate-100">{{ $rfqs->withQueryString()->links() }}</div>
    @endif
</x-data-card>
@endsection
