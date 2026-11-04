@extends('layouts.app')
@section('title', 'PO ' . $purchaseOrder->po_no)

@section('content')
@php
    $statusBadge = [
        'draft'              => 'badge-neutral',
        'submitted'          => 'badge-warning',
        'approved'           => 'badge-success',
        'rejected'           => 'badge-danger',
        'converted'          => 'badge-purple',
        'sent'               => 'badge-info',
        'supplier_confirmed' => 'badge-teal',
        'partially_received' => 'badge-warning',
        'fully_received'     => 'badge-success',
        'closed'             => 'badge-neutral',
    ];
    $statusLabel = [
        'draft'              => 'Draft',
        'submitted'          => 'Menunggu Persetujuan',
        'approved'           => 'Disetujui',
        'rejected'           => 'Ditolak',
        'converted'          => 'Dikonversi',
        'sent'               => 'Dikirim ke Supplier',
        'supplier_confirmed' => 'Dikonfirmasi Supplier',
        'partially_received' => 'Terima Sebagian',
        'fully_received'     => 'Terima Semua',
        'closed'             => 'Ditutup',
    ];
    $user = auth()->user();
    $canReceive = in_array($purchaseOrder->status, ['approved','sent','supplier_confirmed','converted','partially_received']);
@endphp

<x-page-header :title="'PO ' . $purchaseOrder->po_no"
               subtitle="{{ $purchaseOrder->project?->name ?? 'Tanpa proyek' }} — {{ $purchaseOrder->supplier?->name ?? 'Tanpa supplier' }}">
    <x-slot name="actions">
        <span class="{{ $statusBadge[$purchaseOrder->status] ?? 'badge-neutral' }}">
            {{ $statusLabel[$purchaseOrder->status] ?? $purchaseOrder->status }}
        </span>
        @if($purchaseOrder->delivery_status ?? false)
            @php $ds = $purchaseOrder->delivery_status; @endphp
            <span class="{{ $ds === 'complete' ? 'badge-success' : ($ds === 'partial' ? 'badge-warning' : 'badge-neutral') }}">
                {{ $ds === 'complete' ? '✅ Terima Lengkap' : ($ds === 'partial' ? '📦 Terima Sebagian' : '⏳ Belum Diterima') }}
            </span>
        @endif
        <a href="{{ route('purchase-orders.index') }}" class="btn-secondary">← Kembali</a>
    </x-slot>
</x-page-header>



{{-- Budget Warning --}}
@if(($purchaseOrder->budget_status ?? '') === 'over_budget')
<div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 flex items-start gap-3">
    <svg class="h-5 w-5 text-rose-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
    </svg>
    <div>
        <p class="font-semibold text-rose-800 text-sm">⚠️ Peringatan Anggaran</p>
        <p class="text-rose-700 text-sm mt-0.5">
            Nilai PO ini melebihi anggaran RAB yang tersedia.
            @if($purchaseOrder->budget_override_reason)
                Alasan override: <em>{{ $purchaseOrder->budget_override_reason }}</em>
            @endif
        </p>
    </div>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- ─── LEFT SIDEBAR ─── --}}
    <div class="space-y-5">

        {{-- PO Info --}}
        <x-data-card title="Detail PO">
            <div class="px-5 py-4 space-y-3 text-sm">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-slate-500">Nomor PO</span>
                    <span class="font-mono font-bold text-indigo-600">{{ $purchaseOrder->po_no }}</span>
                </div>
                <div class="flex items-center justify-between gap-2">
                    <span class="text-slate-500">Proyek</span>
                    <span class="font-medium text-slate-800 text-right">{{ $purchaseOrder->project?->name ?? '—' }}</span>
                </div>
                <div class="flex items-center justify-between gap-2">
                    <span class="text-slate-500">Supplier</span>
                    <span class="font-medium text-slate-800 text-right">{{ $purchaseOrder->supplier?->name ?? '—' }}</span>
                </div>
                <div class="flex items-center justify-between gap-2">
                    <span class="text-slate-500">Total Nilai</span>
                    <span class="font-bold text-slate-900">Rp {{ number_format($purchaseOrder->grand_total, 0, ',', '.') }}</span>
                </div>

                <div class="border-t border-slate-100 pt-3 space-y-2">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-slate-500">Term Bayar</span>
                        <span class="text-xs font-semibold text-indigo-600">{{ $purchaseOrder->payment_terms_label ?? ucfirst($purchaseOrder->payment_terms ?? '—') }}</span>
                    </div>
                    @if(in_array($purchaseOrder->payment_terms ?? '', ['dp50','dp30']) && ($purchaseOrder->dp_percent ?? 0) > 0)
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-slate-500">DP</span>
                        <span class="text-slate-700">{{ $purchaseOrder->dp_percent }}% = Rp {{ number_format($purchaseOrder->grand_total * $purchaseOrder->dp_percent / 100, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    @if($purchaseOrder->delivery_terms)
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-slate-500">Term Kirim</span>
                        <span class="text-xs text-slate-700">{{ $purchaseOrder->delivery_terms }}</span>
                    </div>
                    @endif
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-slate-500">Est. Tiba</span>
                        <span class="text-slate-700">{{ $purchaseOrder->expected_date?->format('d M Y') ?? '—' }}</span>
                    </div>
                </div>

                <div class="border-t border-slate-100 pt-3 space-y-2">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-slate-500">Progress</span>
                        <span class="font-bold {{ ($purchaseOrder->delivery_percent ?? 0) >= 100 ? 'text-emerald-600' : 'text-amber-600' }}">
                            {{ number_format($purchaseOrder->delivery_percent ?? 0, 1) }}%
                        </span>
                    </div>
                    <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                        @php $pct = min(100, $purchaseOrder->delivery_percent ?? 0); @endphp
                        <div class="h-full rounded-full transition-all {{ $pct >= 100 ? 'bg-emerald-500' : ($pct > 0 ? 'bg-amber-400' : 'bg-slate-200') }}"
                             style="width: {{ $pct }}%"></div>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-slate-500">Surat Jalan ke-</span>
                        <span class="font-semibold text-indigo-600">{{ $purchaseOrder->goodsReceipts->count() }}</span>
                    </div>
                </div>

                <div class="border-t border-slate-100 pt-3 space-y-2 text-xs text-slate-500">
                    <div class="flex justify-between">
                        <span>Dibuat oleh</span>
                        <span>{{ $purchaseOrder->creator?->name ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Tanggal</span>
                        <span>{{ $purchaseOrder->created_at->format('d M Y, H:i') }}</span>
                    </div>
                    @if($purchaseOrder->sent_at ?? false)
                    <div class="flex justify-between">
                        <span>Dikirim ke supplier</span>
                        <span>{{ $purchaseOrder->sent_at->format('d M Y') }}</span>
                    </div>
                    @endif
                    @if($purchaseOrder->supplier_ref_no ?? false)
                    <div class="flex justify-between">
                        <span>Ref. Supplier</span>
                        <span class="font-mono">{{ $purchaseOrder->supplier_ref_no }}</span>
                    </div>
                    @endif
                </div>

                @if($purchaseOrder->notes)
                    <div class="mt-1 pt-3 border-t border-slate-100">
                        <p class="text-xs font-semibold text-slate-500 mb-1">Catatan</p>
                        <p class="text-sm text-slate-700">{{ $purchaseOrder->notes }}</p>
                    </div>
                @endif
            </div>
        </x-data-card>

        {{-- Approval Info --}}
        @if(in_array($purchaseOrder->status, ['approved', 'rejected', 'converted', 'sent', 'supplier_confirmed', 'partially_received', 'fully_received']))
        <x-data-card title="Info Approval">
            <div class="px-5 py-4 space-y-3 text-sm">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-slate-500">Disetujui oleh</span>
                    <span class="font-medium text-slate-800">{{ $purchaseOrder->approver?->name ?? '—' }}</span>
                </div>
                <div class="flex items-center justify-between gap-2">
                    <span class="text-slate-500">Waktu</span>
                    <span class="text-slate-500 text-xs">{{ $purchaseOrder->approved_at?->format('d M Y, H:i') ?? '—' }}</span>
                </div>
                @if($approvalRule ?? false)
                <div class="flex items-center justify-between gap-2">
                    <span class="text-slate-500">Level Approval</span>
                    <span class="text-xs font-semibold text-indigo-600">{{ $approvalRule->approver_label ?? 'Standard' }}</span>
                </div>
                @endif
            </div>
            @if($purchaseOrder->rejection_reason)
                <div class="mx-5 mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3">
                    <p class="text-xs font-semibold text-rose-700 mb-1">Alasan Penolakan</p>
                    <p class="text-sm text-rose-800">{{ $purchaseOrder->rejection_reason }}</p>
                </div>
            @endif
        </x-data-card>
        @endif

        {{-- Closed Info --}}
        @if($purchaseOrder->status === 'closed')
        <x-data-card>
            <div class="px-5 py-4 space-y-2 text-sm">
                <p class="font-semibold text-slate-700">PO Ditutup</p>
                <div class="flex justify-between">
                    <span class="text-slate-500">Ditutup oleh</span>
                    <span>{{ $purchaseOrder->closedBy?->name ?? '—' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Waktu</span>
                    <span class="text-xs text-slate-500">{{ $purchaseOrder->closed_at?->format('d M Y, H:i') ?? '—' }}</span>
                </div>
                @if($purchaseOrder->close_reason)
                <div class="rounded-xl bg-slate-50 border border-slate-200 p-3 mt-2">
                    <p class="text-xs text-slate-600">{{ $purchaseOrder->close_reason }}</p>
                </div>
                @endif
            </div>
        </x-data-card>
        @endif

        {{-- Linked docs (MR / RFQ) --}}
        @if($purchaseOrder->materialRequest || $purchaseOrder->rfq)
        <x-data-card title="Dokumen Terkait">
            <div class="px-5 py-4 space-y-2">
                @if($purchaseOrder->materialRequest)
                <a href="{{ route('material-requests.show', $purchaseOrder->materialRequest) }}"
                   class="flex items-center gap-3 rounded-xl bg-blue-50 border border-blue-100 px-3 py-2.5 hover:bg-blue-100 transition-colors">
                    <svg class="h-4 w-4 text-blue-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                    </svg>
                    <div>
                        <p class="text-xs text-blue-500 font-medium">Material Request</p>
                        <p class="text-sm font-bold text-blue-700 font-mono">{{ $purchaseOrder->materialRequest->mr_no }}</p>
                    </div>
                </a>
                @endif
                @if($purchaseOrder->rfq)
                <a href="{{ route('rfq.show', $purchaseOrder->rfq) }}"
                   class="flex items-center gap-3 rounded-xl bg-violet-50 border border-violet-100 px-3 py-2.5 hover:bg-violet-100 transition-colors">
                    <svg class="h-4 w-4 text-violet-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m5.231 13.481L15 17.25m-4.5-15H5.625c-.621 0-1.125.504-1.125 1.125v16.5c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9zm3.75 11.625a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                    </svg>
                    <div>
                        <p class="text-xs text-violet-500 font-medium">RFQ</p>
                        <p class="text-sm font-bold text-violet-700 font-mono">{{ $purchaseOrder->rfq->rfq_no }}</p>
                    </div>
                </a>
                @endif
            </div>
        </x-data-card>
        @endif

        {{-- ─── TINDAKAN ─── --}}
        <x-data-card title="Tindakan">
            <div class="px-5 py-4 space-y-2">

                {{-- Submit --}}
                @if($purchaseOrder->isDraft() && in_array($user->role, ['admin', 'staff', 'purchasing']))
                    @if($user->role === 'admin' || $purchaseOrder->created_by === $user->id)
                    <form method="POST" action="{{ route('purchase-orders.submit', $purchaseOrder) }}">
                        @csrf
                        <button type="submit" class="btn-primary w-full justify-center text-sm"
                                onclick="return confirm('Ajukan PO ini untuk persetujuan?')">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                            Ajukan untuk Persetujuan
                        </button>
                    </form>
                    @endif
                @endif

                {{-- Approve --}}
                @if($purchaseOrder->isSubmitted() && in_array($user->role, ['admin', 'pimpinan']))
                <form method="POST" action="{{ route('purchase-orders.approve', $purchaseOrder) }}">
                    @csrf
                    <button type="submit" class="btn-success w-full justify-center text-sm"
                            onclick="return confirm('Setujui Purchase Order ini?')">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Setujui PO
                    </button>
                </form>
                @endif

                {{-- Reject --}}
                @if($purchaseOrder->isSubmitted() && in_array($user->role, ['admin', 'pimpinan']))
                <div x-data="{ open: false }">
                    <button @click="open = !open" class="btn-danger w-full justify-center text-sm">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        Tolak PO
                    </button>
                    <div x-show="open" x-collapse class="mt-3 space-y-2">
                        <form method="POST" action="{{ route('purchase-orders.reject', $purchaseOrder) }}">
                            @csrf
                            <textarea name="rejection_reason" required rows="3" class="form-input text-sm resize-none"
                                      placeholder="Alasan penolakan (wajib diisi)..."></textarea>
                            @error('rejection_reason')<p class="form-error">{{ $message }}</p>@enderror
                            <button type="submit" class="btn-danger w-full justify-center text-sm mt-2">Konfirmasi Tolak</button>
                        </form>
                    </div>
                </div>
                @endif

                {{-- Mark Sent --}}
                @if($purchaseOrder->isApproved() && in_array($user->role, ['admin', 'staff', 'purchasing']))
                <form method="POST" action="{{ route('purchase-orders.mark-sent', $purchaseOrder) }}">
                    @csrf
                    <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-sky-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-sky-700 transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                        Tandai Dikirim ke Supplier
                    </button>
                </form>
                @endif

                {{-- Confirm Supplier --}}
                @if(in_array($purchaseOrder->status, ['sent']) && in_array($user->role, ['admin', 'staff', 'purchasing']))
                <div x-data="{ open: false }">
                    <button @click="open = !open"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-teal-700 transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Konfirmasi Supplier
                    </button>
                    <div x-show="open" x-collapse class="mt-3">
                        <form method="POST" action="{{ route('purchase-orders.confirm-supplier', $purchaseOrder) }}">
                            @csrf
                            <input type="text" name="supplier_ref_no" class="form-input text-sm mb-2"
                                   placeholder="No. referensi supplier (opsional)">
                            <button type="submit" class="btn-success w-full justify-center text-sm">Simpan Konfirmasi</button>
                        </form>
                    </div>
                </div>
                @endif

                {{-- Buat Surat Jalan (GR from PO) --}}
                @if($canReceive && in_array($user->role, ['admin', 'staff', 'purchasing']))
                <a href="{{ route('purchase-orders.goods-receipts.create', $purchaseOrder) }}"
                   class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/>
                    </svg>
                    Buat Surat Jalan
                </a>
                @endif

                {{-- Convert to Transaction --}}
                @if($purchaseOrder->isApproved() && in_array($user->role, ['admin', 'staff', 'purchasing']))
                <form method="POST" action="{{ route('purchase-orders.convert', $purchaseOrder) }}">
                    @csrf
                    <button type="submit" class="btn-primary w-full justify-center text-sm"
                            onclick="return confirm('Konversi PO ini menjadi Transaksi Pembelian?')">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>
                        Buat Transaksi Pembelian
                    </button>
                </form>
                @endif

                {{-- Close PO (admin/pimpinan only) --}}
                @if(in_array($purchaseOrder->status, ['approved','sent','supplier_confirmed','converted','partially_received']) && in_array($user->role, ['admin','pimpinan']))
                <div x-data="{ open: false }">
                    <button @click="open = !open"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                        Tutup PO
                    </button>
                    <div x-show="open" x-collapse class="mt-3">
                        <form method="POST" action="{{ route('purchase-orders.close', $purchaseOrder) }}">
                            @csrf
                            <textarea name="close_reason" required rows="2" class="form-input text-sm resize-none mb-2"
                                      placeholder="Alasan penutupan PO..."></textarea>
                            <button type="submit" class="btn-danger w-full justify-center text-sm"
                                    onclick="return confirm('Tutup PO ini? Tindakan ini tidak bisa diubah.')">Konfirmasi Tutup PO</button>
                        </form>
                    </div>
                </div>
                @endif

                {{-- Delete --}}
                @if($purchaseOrder->isDraft() && $user->role === 'admin')
                <form method="POST" action="{{ route('purchase-orders.destroy', $purchaseOrder) }}"
                      onsubmit="return confirm('Hapus PO {{ $purchaseOrder->po_no }}? Tindakan ini tidak dapat dibatalkan.')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-rose-200 bg-white px-4 py-2.5 text-sm font-semibold text-rose-600 transition hover:bg-rose-50">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                        Hapus PO
                    </button>
                </form>
                @endif

            </div>
        </x-data-card>
    </div>

    {{-- ─── RIGHT MAIN AREA ─── --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Items Table --}}
        <x-data-card title="Daftar Item PO" subtitle="{{ $purchaseOrder->items->count() }} item">
            <div class="overflow-x-auto">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th class="w-8">#</th>
                            <th>Material</th>
                            <th class="th-center">Satuan</th>
                            <th class="th-right">Qty PO</th>
                            <th class="th-right">Sudah Terima</th>
                            <th class="th-right">Harga Satuan</th>
                            <th class="th-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchaseOrder->items as $i => $item)
                        @php
                            $rcvPct = $item->receive_percent ?? 0;
                        @endphp
                        <tr>
                            <td class="text-slate-400 text-xs">{{ $i + 1 }}</td>
                            <td>
                                <div class="font-medium text-slate-800">{{ $item->material_name_snapshot }}</div>
                                @if($item->notes)
                                    <div class="text-xs text-slate-400 mt-0.5">{{ $item->notes }}</div>
                                @endif
                                {{-- per-item delivery bar --}}
                                @if(($item->qty_received ?? 0) > 0)
                                <div class="mt-1.5">
                                    <div class="flex justify-between text-[10px] text-slate-400 mb-0.5">
                                        <span>Diterima {{ number_format($item->qty_received, 2, ',', '.') }}</span>
                                        <span>{{ number_format($rcvPct, 0) }}%</span>
                                    </div>
                                    <div class="h-1 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full rounded-full {{ $rcvPct >= 100 ? 'bg-emerald-500' : 'bg-amber-400' }}"
                                             style="width: {{ min(100, $rcvPct) }}%"></div>
                                    </div>
                                </div>
                                @endif
                            </td>
                            <td class="text-center text-slate-600 text-sm">{{ $item->unit_name_snapshot }}</td>
                            <td class="text-right font-mono text-slate-700 text-sm">{{ number_format($item->qty, 2, ',', '.') }}</td>
                            <td class="text-right font-mono text-sm {{ ($item->qty_received ?? 0) > 0 ? 'text-emerald-600 font-semibold' : 'text-slate-300' }}">
                                {{ number_format($item->qty_received ?? 0, 2, ',', '.') }}
                            </td>
                            <td class="text-right text-slate-700 text-sm">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                            <td class="text-right font-semibold text-slate-800">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-slate-400 text-sm">Tidak ada item</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="6" class="text-right font-bold text-slate-700">Total</td>
                            <td class="text-right font-bold text-slate-900 text-base">
                                Rp {{ number_format($purchaseOrder->grand_total, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </x-data-card>

        {{-- Surat Jalan (Goods Receipts) --}}
        @if($purchaseOrder->goodsReceipts->count() > 0)
        <x-data-card title="Surat Jalan (Penerimaan Barang)" subtitle="{{ $purchaseOrder->goodsReceipts->count() }} pengiriman">
            <div class="overflow-x-auto">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>No. Surat Jalan</th>
                            <th>Tanggal</th>
                            <th>Diterima Oleh</th>
                            <th class="th-center">Inspeksi</th>
                            <th class="th-center">Status</th>
                            <th class="th-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchaseOrder->goodsReceipts as $gr)
                        @php
                            $inspBadge = [
                                'pending'      => 'badge-neutral',
                                'passed'       => 'badge-success',
                                'partial_pass' => 'badge-warning',
                                'failed'       => 'badge-danger',
                            ];
                            $inspLabel = [
                                'pending'      => '⏳ Belum',
                                'passed'       => '✅ Lulus',
                                'partial_pass' => '⚠️ Sebagian',
                                'failed'       => '❌ Ditolak',
                            ];
                        @endphp
                        <tr>
                            <td class="font-mono font-semibold text-indigo-600 text-sm">{{ $gr->receipt_no }}</td>
                            <td class="text-sm text-slate-700">{{ $gr->receipt_date?->format('d M Y') }}</td>
                            <td class="text-sm text-slate-700">{{ $gr->received_by }}</td>
                            <td class="text-center">
                                <span class="{{ $inspBadge[$gr->inspection_status ?? 'pending'] ?? 'badge-neutral' }} text-xs">
                                    {{ $inspLabel[$gr->inspection_status ?? 'pending'] ?? '—' }}
                                </span>
                            </td>
                            <td class="text-center">
                                @php $grStatus = $gr->status ?? 'pending'; @endphp
                                <span class="{{ $grStatus === 'complete' ? 'badge-success' : ($grStatus === 'partial' ? 'badge-warning' : 'badge-neutral') }} text-xs">
                                    {{ $grStatus === 'complete' ? 'Lengkap' : ($grStatus === 'partial' ? 'Sebagian' : 'Pending') }}
                                </span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('goods-receipts.show', $gr) }}"
                                   class="text-xs text-indigo-600 hover:underline font-semibold">Lihat →</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-data-card>
        @endif

        {{-- Linked Transactions --}}
        @if($purchaseOrder->transactions->count() > 0)
        <x-data-card title="Transaksi Pembelian Terkait" subtitle="{{ $purchaseOrder->transactions->count() }} transaksi">
            <div class="overflow-x-auto">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>No. Transaksi</th>
                            <th>Tanggal</th>
                            <th class="th-right">Total</th>
                            <th class="th-center">Status</th>
                            <th class="th-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchaseOrder->transactions as $tx)
                        <tr>
                            <td class="font-mono font-semibold text-indigo-600 text-sm">{{ $tx->transaction_no }}</td>
                            <td class="text-sm text-slate-700">{{ $tx->transaction_date?->format('d M Y') }}</td>
                            <td class="text-right font-semibold text-sm">Rp {{ number_format($tx->grand_total, 0, ',', '.') }}</td>
                            <td class="text-center">
                                <span class="badge-neutral text-xs">{{ ucfirst($tx->status ?? '—') }}</span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('purchase-transactions.show', $tx) }}"
                                   class="text-xs text-indigo-600 hover:underline font-semibold">Lihat →</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-data-card>
        @endif

        {{-- No GR yet — call to action --}}
        @if($purchaseOrder->goodsReceipts->count() === 0 && $canReceive && in_array($user->role, ['admin','staff','purchasing']))
        <div class="rounded-2xl border-2 border-dashed border-emerald-200 bg-emerald-50 p-6 text-center">
            <svg class="h-10 w-10 text-emerald-400 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/>
            </svg>
            <p class="text-emerald-800 font-semibold mb-1">Belum ada penerimaan barang</p>
            <p class="text-emerald-600 text-sm mb-4">Buat surat jalan pertama ketika barang tiba dari supplier</p>
            <a href="{{ route('purchase-orders.goods-receipts.create', $purchaseOrder) }}"
               class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Buat Surat Jalan Pertama
            </a>
        </div>
        @endif

    </div>
</div>
@endsection
