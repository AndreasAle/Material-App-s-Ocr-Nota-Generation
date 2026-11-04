@extends('layouts.app')
@section('title', 'MR ' . $materialRequest->request_no)

@section('content')
@php
    $user = auth()->user();
    $statusBadge = [
        'draft'     => 'badge-neutral',
        'submitted' => 'badge-warning',
        'approved'  => 'badge-success',
        'rejected'  => 'badge-danger',
        'converted' => 'badge-purple',
    ];
    $statusLabel = [
        'draft'     => 'Draft',
        'submitted' => 'Menunggu Persetujuan',
        'approved'  => 'Disetujui',
        'rejected'  => 'Ditolak',
        'converted' => 'Dikonversi ke PO',
    ];
    $urgencyBadge = [
        'low'      => 'badge-neutral',
        'normal'   => 'badge-info',
        'high'     => 'badge-warning',
        'critical' => 'badge-danger',
    ];
@endphp

<x-page-header :title="'MR ' . $materialRequest->request_no" subtitle="{{ $materialRequest->project?->name ?? 'Tanpa proyek' }}">
    <x-slot name="actions">
        <span class="{{ $statusBadge[$materialRequest->status] ?? 'badge-neutral' }}">
            {{ $statusLabel[$materialRequest->status] ?? $materialRequest->status }}
        </span>
        <a href="{{ route('material-requests.index') }}" class="btn-secondary">← Kembali</a>
    </x-slot>
</x-page-header>



<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Left: Info & Actions --}}
    <div class="space-y-5">

        {{-- MR Info --}}
        <x-data-card title="Detail MR">
            <div class="px-5 py-4 space-y-3 text-sm">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-slate-500">Nomor MR</span>
                    <span class="font-mono font-bold text-indigo-600">{{ $materialRequest->request_no }}</span>
                </div>
                <div class="flex items-center justify-between gap-2">
                    <span class="text-slate-500">Proyek</span>
                    <span class="font-medium text-slate-800 text-right">{{ $materialRequest->project?->name ?? '—' }}</span>
                </div>
                <div class="flex items-center justify-between gap-2">
                    <span class="text-slate-500">Urgensi</span>
                    <span class="{{ $urgencyBadge[$materialRequest->urgency] ?? 'badge-neutral' }}">{{ $materialRequest->urgency_label }}</span>
                </div>
                <div class="flex items-center justify-between gap-2">
                    <span class="text-slate-500">Dibutuhkan</span>
                    <span class="text-slate-700">{{ $materialRequest->needed_by?->format('d M Y') ?? '—' }}</span>
                </div>
                <div class="flex items-center justify-between gap-2">
                    <span class="text-slate-500">Pemohon</span>
                    <span class="font-medium text-slate-700">{{ $materialRequest->requester?->name ?? 'Sistem (Auto)' }}</span>
                </div>
                <div class="flex items-center justify-between gap-2">
                    <span class="text-slate-500">Dibuat</span>
                    <span class="text-slate-400 text-xs">{{ $materialRequest->created_at->format('d M Y, H:i') }}</span>
                </div>
                @if($materialRequest->is_auto_reorder)
                <div class="flex items-center justify-between gap-2">
                    <span class="text-slate-500">Tipe</span>
                    <span class="badge-warning">Auto-Reorder</span>
                </div>
                @endif
            </div>
            @if($materialRequest->notes)
                <div class="mx-5 mb-5 rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                    <p class="text-xs font-semibold text-slate-500 mb-1">Catatan</p>
                    {{ $materialRequest->notes }}
                </div>
            @endif
        </x-data-card>

        {{-- Approval Info --}}
        @if(in_array($materialRequest->status, ['approved', 'rejected', 'converted']))
        <x-data-card title="Info Approval">
            <div class="px-5 py-4 space-y-3 text-sm">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-slate-500">Diproses Oleh</span>
                    <span class="font-medium text-slate-800">{{ $materialRequest->approver?->name ?? '—' }}</span>
                </div>
                <div class="flex items-center justify-between gap-2">
                    <span class="text-slate-500">Waktu</span>
                    <span class="text-slate-400 text-xs">{{ $materialRequest->approved_at?->format('d M Y, H:i') ?? '—' }}</span>
                </div>
            </div>
            @if($materialRequest->rejection_reason)
                <div class="mx-5 mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm">
                    <p class="font-semibold text-rose-700 mb-1 text-xs">Alasan Penolakan</p>
                    <p class="text-rose-800">{{ $materialRequest->rejection_reason }}</p>
                </div>
            @endif
        </x-data-card>
        @endif

        {{-- Converted PO --}}
        @if($materialRequest->convertedPo)
        <x-data-card>
            <div class="flex items-center gap-4 px-5 py-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-slate-500">Dikonversi ke PO</p>
                    <a href="{{ route('purchase-orders.show', $materialRequest->convertedPo) }}"
                       class="font-mono font-bold text-indigo-600 hover:underline text-sm">
                        {{ $materialRequest->convertedPo->po_no }}
                    </a>
                </div>
            </div>
        </x-data-card>
        @endif

        {{-- Actions --}}
        <x-data-card title="Tindakan">
            <div class="px-5 py-4 space-y-2">

                {{-- Submit --}}
                @if($materialRequest->isDraft() && ($user->role !== 'staff' || $materialRequest->requested_by === $user->id))
                <form method="POST" action="{{ route('material-requests.submit', $materialRequest) }}">
                    @csrf
                    <button type="submit" class="btn-primary w-full justify-center"
                            onclick="return confirm('Ajukan MR ini untuk persetujuan?')">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/>
                        </svg>
                        Ajukan untuk Persetujuan
                    </button>
                </form>
                @endif

                {{-- Approve --}}
                @if($materialRequest->isSubmitted() && in_array($user->role, ['admin', 'pimpinan']))
                <form method="POST" action="{{ route('material-requests.approve', $materialRequest) }}">
                    @csrf
                    <button type="submit" class="btn-success w-full justify-center"
                            onclick="return confirm('Setujui permintaan material ini?')">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Setujui MR
                    </button>
                </form>
                @endif

                {{-- Reject --}}
                @if($materialRequest->isSubmitted() && in_array($user->role, ['admin', 'pimpinan']))
                <div x-data="{ open: false }">
                    <button @click="open = !open"
                            class="btn-danger w-full justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Tolak MR
                    </button>
                    <div x-show="open" x-collapse class="mt-3 space-y-2">
                        <form method="POST" action="{{ route('material-requests.reject', $materialRequest) }}">
                            @csrf
                            <textarea name="rejection_reason" required rows="2"
                                      class="form-input resize-none text-sm"
                                      placeholder="Alasan penolakan (wajib diisi)..."></textarea>
                            @error('rejection_reason')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                            <button type="submit" class="btn-danger w-full justify-center mt-2">
                                Konfirmasi Tolak
                            </button>
                        </form>
                    </div>
                </div>
                @endif

                {{-- Convert to PO --}}
                @if($materialRequest->isApproved() && in_array($user->role, ['admin', 'staff']))
                <form method="POST" action="{{ route('material-requests.convert-po', $materialRequest) }}">
                    @csrf
                    <button type="submit"
                            class="btn-primary w-full justify-center"
                            onclick="return confirm('Konversi MR ini menjadi Draft Purchase Order?')">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/>
                        </svg>
                        Konversi ke Purchase Order
                    </button>
                </form>
                @endif

                {{-- Delete Draft --}}
                @if($materialRequest->isDraft())
                <form method="POST" action="{{ route('material-requests.destroy', $materialRequest) }}"
                      onsubmit="return confirm('Hapus MR ini?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-rose-200 bg-white px-4 py-2.5 text-sm font-semibold text-rose-600 transition hover:bg-rose-50">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                        </svg>
                        Hapus MR
                    </button>
                </form>
                @endif
            </div>
        </x-data-card>
    </div>

    {{-- Right: Items --}}
    <div class="lg:col-span-2">
        @php $totalEst = $materialRequest->items->sum(fn($i) => $i->subtotal ?? 0); @endphp
        <x-data-card title="Daftar Material Diminta" subtitle="{{ $materialRequest->items->count() }} item">
            <div class="overflow-x-auto">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th class="w-8">#</th>
                            <th>Material</th>
                            <th class="th-center">Satuan</th>
                            <th class="th-right">Qty</th>
                            <th class="th-right">Harga Est.</th>
                            <th class="th-right">Subtotal Est.</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($materialRequest->items as $i => $item)
                        <tr>
                            <td class="text-slate-400 text-xs">{{ $i + 1 }}</td>
                            <td>
                                <div class="font-medium text-slate-800">{{ $item->material_name_snapshot }}</div>
                                @if($item->notes)
                                    <div class="text-xs text-slate-400 mt-0.5">{{ $item->notes }}</div>
                                @endif
                            </td>
                            <td class="text-center text-slate-600">{{ $item->unit_name_snapshot }}</td>
                            <td class="text-right font-mono text-slate-700">{{ number_format($item->qty_requested, 2, ',', '.') }}</td>
                            <td class="text-right text-slate-600">
                                {{ $item->estimated_price ? 'Rp ' . number_format($item->estimated_price, 0, ',', '.') : '—' }}
                            </td>
                            <td class="text-right font-semibold text-slate-800">
                                {{ $item->estimated_price ? 'Rp ' . number_format($item->subtotal ?? 0, 0, ',', '.') : '—' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-slate-400 text-sm">Tidak ada item</td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($totalEst > 0)
                    <tfoot>
                        <tr>
                            <td colspan="5" class="text-right font-bold text-slate-700">Total Estimasi</td>
                            <td class="text-right font-bold text-indigo-700">
                                Rp {{ number_format($totalEst, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </x-data-card>
    </div>

</div>
@endsection
