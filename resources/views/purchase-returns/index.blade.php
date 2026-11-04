@extends('layouts.app')
@section('title', 'Return ke Vendor')

@section('content')
<x-page-header title="Return ke Vendor" subtitle="Daftar pengembalian barang ke supplier">
    <x-slot name="actions">
        <a href="{{ route('purchase-transactions.index') }}" class="btn-secondary">
            Buat dari Transaksi
        </a>
    </x-slot>
</x-page-header>


{{-- Stats --}}
<div class="grid grid-cols-2 sm:grid-cols-5 gap-4 mb-6">
    @php
        $statData = [
            ['label'=>'Total','val'=>$stats['total'],'bg'=>'bg-slate-50','border'=>'border-slate-200','text'=>'text-slate-800','sub'=>'text-slate-500'],
            ['label'=>'Draft','val'=>$stats['draft'],'bg'=>'bg-amber-50','border'=>'border-amber-200','text'=>'text-amber-700','sub'=>'text-amber-600'],
            ['label'=>'Diajukan','val'=>$stats['submitted'],'bg'=>'bg-blue-50','border'=>'border-blue-200','text'=>'text-blue-700','sub'=>'text-blue-600'],
            ['label'=>'Diproses','val'=>$stats['returned'],'bg'=>'bg-orange-50','border'=>'border-orange-200','text'=>'text-orange-700','sub'=>'text-orange-600'],
            ['label'=>'Dikreditkan','val'=>$stats['credited'],'bg'=>'bg-emerald-50','border'=>'border-emerald-200','text'=>'text-emerald-700','sub'=>'text-emerald-600'],
        ];
    @endphp
    @foreach($statData as $s)
    <div class="{{ $s['bg'] }} border {{ $s['border'] }} rounded-2xl p-4">
        <p class="text-xs {{ $s['sub'] }} font-medium">{{ $s['label'] }}</p>
        <p class="text-2xl font-bold {{ $s['text'] }}">{{ $s['val'] }}</p>
    </div>
    @endforeach
</div>

{{-- Filter --}}
<form method="GET" class="mb-5 flex flex-wrap gap-3">
    <select name="status" onchange="this.form.submit()" class="form-input w-40 text-sm">
        <option value="">Semua Status</option>
        <option value="draft"     {{ request('status') === 'draft'     ? 'selected' : '' }}>Draft</option>
        <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Diajukan</option>
        <option value="approved"  {{ request('status') === 'approved'  ? 'selected' : '' }}>Disetujui</option>
        <option value="returned"  {{ request('status') === 'returned'  ? 'selected' : '' }}>Diproses</option>
        <option value="credited"  {{ request('status') === 'credited'  ? 'selected' : '' }}>Dikreditkan</option>
    </select>
</form>

<x-data-card>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>No. Return</th>
                    <th>Supplier</th>
                    <th>Alasan</th>
                    <th class="th-right">Total</th>
                    <th class="th-center">Status</th>
                    <th>Tanggal</th>
                    <th class="th-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($returns as $ret)
                @php
                    $badge = ['draft'=>'badge-neutral','submitted'=>'badge-warning','approved'=>'badge-info','returned'=>'badge-warning','credited'=>'badge-success'];
                    $label = ['draft'=>'Draft','submitted'=>'Diajukan','approved'=>'Disetujui','returned'=>'Diproses','credited'=>'✅ Dikreditkan'];
                    $reasonLabel = ['wrong_item'=>'Item Salah','damaged'=>'Rusak','quantity_excess'=>'Qty Lebih','quality_reject'=>'QC Reject','spec_mismatch'=>'Spek Beda','other'=>'Lainnya'];
                @endphp
                <tr>
                    <td>
                        <a href="{{ route('purchase-returns.show', $ret) }}"
                           class="font-mono font-bold text-indigo-600 hover:underline text-sm">
                            {{ $ret->return_no }}
                        </a>
                    </td>
                    <td class="text-sm text-slate-700">{{ $ret->supplier?->name ?? '—' }}</td>
                    <td>
                        <span class="text-xs font-semibold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-full">
                            {{ $reasonLabel[$ret->reason] ?? $ret->reason }}
                        </span>
                    </td>
                    <td class="text-right font-semibold text-sm">Rp {{ number_format($ret->total_return_value ?? 0, 0, ',', '.') }}</td>
                    <td class="text-center">
                        <span class="{{ $badge[$ret->status] ?? 'badge-neutral' }} text-xs">{{ $label[$ret->status] ?? $ret->status }}</span>
                    </td>
                    <td class="text-xs text-slate-500">{{ $ret->created_at->format('d M Y') }}</td>
                    <td class="text-right">
                        <a href="{{ route('purchase-returns.show', $ret) }}"
                           class="text-xs text-indigo-600 hover:underline font-semibold">Lihat →</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-12 text-center text-slate-400 text-sm">
                        Belum ada retur. Return dibuat dari halaman detail Transaksi Pembelian.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($returns->hasPages())
    <div class="px-5 py-4 border-t border-slate-100">{{ $returns->withQueryString()->links() }}</div>
    @endif
</x-data-card>
@endsection
