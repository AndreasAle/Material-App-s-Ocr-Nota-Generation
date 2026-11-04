@extends('layouts.app')
@section('title', 'Return ' . $purchaseReturn->return_no)

@section('content')
@php
    $badge = ['draft'=>'badge-neutral','submitted'=>'badge-warning','approved'=>'badge-info','returned'=>'badge-warning','credited'=>'badge-success'];
    $label = ['draft'=>'Draft','submitted'=>'Diajukan','approved'=>'Disetujui','returned'=>'Diproses','credited'=>'✅ Dikreditkan'];
    $reasonLabel = ['wrong_item'=>'Item Salah/Tidak Sesuai','damaged'=>'Barang Rusak','quantity_excess'=>'Kelebihan Kuantitas','quality_reject'=>'Tidak Lulus QC','spec_mismatch'=>'Spesifikasi Beda','other'=>'Lainnya'];
    $user = auth()->user();
@endphp

<x-page-header :title="'Return ' . $purchaseReturn->return_no"
               subtitle="{{ $purchaseReturn->supplier?->name ?? 'Tanpa supplier' }}">
    <x-slot name="actions">
        <span class="{{ $badge[$purchaseReturn->status] ?? 'badge-neutral' }}">
            {{ $label[$purchaseReturn->status] ?? $purchaseReturn->status }}
        </span>
        <a href="{{ route('purchase-returns.index') }}" class="btn-secondary">← Kembali</a>
    </x-slot>
</x-page-header>


<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Left Sidebar --}}
    <div class="space-y-5">

        <x-data-card title="Detail Return">
            <div class="px-5 py-4 space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-500">No. Return</span>
                    <span class="font-mono font-bold text-indigo-600">{{ $purchaseReturn->return_no }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Supplier</span>
                    <span class="font-medium text-slate-800">{{ $purchaseReturn->supplier?->name ?? '—' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Alasan</span>
                    <span class="text-xs font-semibold bg-amber-100 text-amber-800 px-2 py-0.5 rounded-full">
                        {{ $reasonLabel[$purchaseReturn->reason] ?? $purchaseReturn->reason }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Total Return</span>
                    <span class="font-bold text-rose-600">Rp {{ number_format($purchaseReturn->total_return_value ?? 0, 0, ',', '.') }}</span>
                </div>
                @if($purchaseReturn->purchaseTransaction)
                <div class="border-t border-slate-100 pt-3">
                    <p class="text-xs text-slate-400 mb-1">Dari Transaksi</p>
                    <a href="{{ route('purchase-transactions.show', $purchaseReturn->purchaseTransaction) }}"
                       class="font-mono text-sm font-bold text-indigo-600 hover:underline">
                        {{ $purchaseReturn->purchaseTransaction->transaction_no }}
                    </a>
                </div>
                @endif
                @if($purchaseReturn->reason_detail)
                <div class="border-t border-slate-100 pt-3">
                    <p class="text-xs text-slate-400 mb-1">Deskripsi</p>
                    <p class="text-sm text-slate-700">{{ $purchaseReturn->reason_detail }}</p>
                </div>
                @endif
                @if($purchaseReturn->credit_note_no)
                <div class="border-t border-slate-100 pt-3">
                    <p class="text-xs text-slate-400 mb-1">No. Credit Note</p>
                    <p class="font-mono text-sm font-semibold text-emerald-700">{{ $purchaseReturn->credit_note_no }}</p>
                    @if($purchaseReturn->credit_note_date)
                    <p class="text-xs text-slate-500">{{ $purchaseReturn->credit_note_date?->format('d M Y') }}</p>
                    @endif
                    @if($purchaseReturn->credit_amount)
                    <p class="text-sm font-semibold text-emerald-600 mt-1">Kredit: Rp {{ number_format($purchaseReturn->credit_amount, 0, ',', '.') }}</p>
                    @endif
                </div>
                @endif
                <div class="text-xs text-slate-400 border-t border-slate-100 pt-3">
                    Dibuat {{ $purchaseReturn->creator?->name ?? '—' }} · {{ $purchaseReturn->created_at->format('d M Y') }}
                </div>
            </div>
        </x-data-card>

        {{-- Actions --}}
        <x-data-card title="Tindakan">
            <div class="px-5 py-4 space-y-2">

                {{-- Submit --}}
                @if($purchaseReturn->status === 'draft' && in_array($user->role, ['admin','staff','purchasing']))
                <form method="POST" action="{{ route('purchase-returns.submit', $purchaseReturn) }}">
                    @csrf
                    <button type="submit" class="btn-primary w-full justify-center text-sm"
                            onclick="return confirm('Ajukan return ini?')">
                        Ajukan untuk Persetujuan
                    </button>
                </form>
                @endif

                {{-- Approve --}}
                @if($purchaseReturn->status === 'submitted' && in_array($user->role, ['admin','pimpinan']))
                <form method="POST" action="{{ route('purchase-returns.approve', $purchaseReturn) }}">
                    @csrf
                    <button type="submit" class="btn-success w-full justify-center text-sm"
                            onclick="return confirm('Setujui return ini?')">
                        Setujui Return
                    </button>
                </form>
                @endif

                {{-- Reject --}}
                @if($purchaseReturn->status === 'submitted' && in_array($user->role, ['admin','pimpinan']))
                <div x-data="{ open: false }">
                    <button @click="open = !open" class="btn-danger w-full justify-center text-sm">Tolak Return</button>
                    <div x-show="open" x-collapse class="mt-2">
                        <form method="POST" action="{{ route('purchase-returns.reject', $purchaseReturn) }}">
                            @csrf
                            <textarea name="rejection_reason" required rows="2" class="form-input text-sm resize-none mb-2"
                                      placeholder="Alasan penolakan..."></textarea>
                            <button type="submit" class="btn-danger w-full justify-center text-sm">Konfirmasi Tolak</button>
                        </form>
                    </div>
                </div>
                @endif

                {{-- Mark Returned --}}
                @if($purchaseReturn->status === 'approved' && in_array($user->role, ['admin','staff','purchasing']))
                <form method="POST" action="{{ route('purchase-returns.mark-returned', $purchaseReturn) }}">
                    @csrf
                    <button type="submit" class="w-full rounded-xl bg-orange-500 text-white px-4 py-2.5 text-sm font-semibold hover:bg-orange-600 transition justify-center flex items-center gap-2"
                            onclick="return confirm('Tandai barang sudah dikembalikan ke supplier?')">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3"/></svg>
                        Tandai Sudah Dikembalikan
                    </button>
                </form>
                @endif

                {{-- Mark Credited --}}
                @if($purchaseReturn->status === 'returned' && in_array($user->role, ['admin','pimpinan']))
                <div x-data="{ open: false }">
                    <button @click="open = !open" class="btn-success w-full justify-center text-sm">Tandai Sudah Dikreditkan</button>
                    <div x-show="open" x-collapse class="mt-2 space-y-2">
                        <form method="POST" action="{{ route('purchase-returns.mark-credited', $purchaseReturn) }}">
                            @csrf
                            <input type="text" name="credit_note_no" class="form-input text-sm mb-2 font-mono"
                                   placeholder="No. Credit Note dari supplier" required>
                            <input type="date" name="credit_note_date" class="form-input text-sm mb-2"
                                   value="{{ today()->format('Y-m-d') }}">
                            <input type="number" name="credit_amount" class="form-input text-sm mb-2 text-right"
                                   placeholder="Nilai kredit (Rp)" min="0" step="100"
                                   value="{{ $purchaseReturn->total_return_value ?? 0 }}">
                            <button type="submit" class="btn-success w-full justify-center text-sm">Simpan Kredit</button>
                        </form>
                    </div>
                </div>
                @endif

            </div>
        </x-data-card>

    </div>

    {{-- Items Table --}}
    <div class="lg:col-span-2">
        <x-data-card title="Item yang Dikembalikan" subtitle="{{ $purchaseReturn->items->count() }} item">
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Material</th>
                            <th class="th-right">Qty Return</th>
                            <th class="th-right">Harga Satuan</th>
                            <th class="th-right">Subtotal</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchaseReturn->items as $i => $item)
                        <tr>
                            <td class="text-slate-400 text-xs">{{ $i + 1 }}</td>
                            <td class="font-medium text-sm text-slate-800">{{ $item->material_name_snapshot }}</td>
                            <td class="text-right font-mono text-sm font-semibold text-rose-600">
                                {{ number_format($item->qty_returned, 2, ',', '.') }}
                            </td>
                            <td class="text-right text-sm">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                            <td class="text-right font-semibold text-sm">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            <td class="text-xs text-slate-500">{{ $item->notes ?: '—' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-slate-400 text-sm">Tidak ada item</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-right font-bold text-slate-700">Total Return</td>
                            <td class="text-right font-bold text-rose-600 text-base">
                                Rp {{ number_format($purchaseReturn->total_return_value ?? 0, 0, ',', '.') }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </x-data-card>
    </div>

</div>
@endsection
