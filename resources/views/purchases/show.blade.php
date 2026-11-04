@extends('layouts.app')

@section('content')
@if(session('price_alerts'))
    @php $alertRows = collect(session('price_alerts')); @endphp
    <div class="mb-5 rounded-2xl border border-amber-200 bg-amber-50 p-4">
        <p class="text-sm font-bold text-amber-900">Perhatian harga barang</p>
        <form method="POST" action="{{ route('store.prices.apply-suggested') }}" class="mt-3 space-y-2">
            @csrf
            @foreach($alertRows as $a)
                <label class="flex flex-wrap items-center gap-x-3 gap-y-1 rounded-xl bg-white px-3 py-2 text-sm">
                    <input type="checkbox" name="material_ids[]" value="{{ $a['material_id'] }}" class="h-4 w-4 accent-amber-600" checked>
                    <span class="font-semibold text-slate-800">{{ $a['name'] }}</span>
                    <span class="text-xs text-slate-500">
                        @if($a['type'] === 'below_cost')
                            <span class="font-bold text-rose-600">Harga jual (Rp {{ number_format($a['sell_price'], 0, ',', '.') }}) di bawah harga beli!</span>
                        @elseif($a['type'] === 'price_up')
                            Harga beli naik Rp {{ number_format($a['old_cost'], 0, ',', '.') }} → Rp {{ number_format($a['new_cost'], 0, ',', '.') }} / {{ $a['unit'] }}
                        @else
                            Belum punya harga jual
                        @endif
                    </span>
                    <span class="ml-auto text-xs font-bold text-emerald-700">Saran jual: Rp {{ number_format($a['suggested'], 0, ',', '.') }} / {{ $a['unit'] }}</span>
                </label>
            @endforeach
            @if(auth()->user()->isAdmin())
                <button type="submit" class="btn-primary mt-1">Pakai saran harga jual untuk yang dicentang</button>
            @endif
        </form>
    </div>
@endif
@php
    $status = strtolower((string) ($purchaseTransaction->status ?? 'draft'));

    $statusConfig = match ($status) {
        'posted' => [
            'label' => 'Posted',
            'badge' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
            'soft' => 'bg-emerald-50',
            'dot' => 'bg-emerald-500',
        ],
        'submitted' => [
            'label' => 'Submitted',
            'badge' => 'bg-amber-50 text-amber-700 border border-amber-200',
            'soft' => 'bg-amber-50',
            'dot' => 'bg-amber-500',
        ],
        'draft' => [
            'label' => 'Draft',
            'badge' => 'bg-slate-100 text-slate-700 border border-slate-200',
            'soft' => 'bg-slate-100',
            'dot' => 'bg-slate-400',
        ],
        default => [
            'label' => ucfirst($purchaseTransaction->status ?? 'Unknown'),
            'badge' => 'bg-slate-100 text-slate-700 border border-slate-200',
            'soft' => 'bg-slate-100',
            'dot' => 'bg-slate-400',
        ],
    };

    $items = $purchaseTransaction->items ?? collect();
    $attachments = $purchaseTransaction->attachments ?? collect();

    $grandTotal = (int) ($purchaseTransaction->grand_total ?? 0);
    $subtotal = $items->sum(fn($item) => (int) ($item->subtotal ?? 0));
    $discount = max(0, $subtotal - $grandTotal);
    $qtyTotal = $items->sum(fn($item) => (float) ($item->qty ?? 0));

    $firstAttachment = $attachments->first();
@endphp

<div class="space-y-6">
    {{-- TOP ACTION --}}
    <section class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <div class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 shadow-sm">
                <span class="h-2 w-2 rounded-full {{ $statusConfig['dot'] }}"></span>
                Detail Pembelian
            </div>

            <h1 class="mt-3 text-3xl font-extrabold tracking-tight text-slate-900">
                Detail Transaksi Pembelian
            </h1>


        </div>

        <div class="flex shrink-0 flex-wrap items-center gap-3">
            <a href="{{ route('purchase-transactions.index') }}"
               class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                Kembali
            </a>

            <a href="{{ route('purchase-transactions.export-detail.pdf', $purchaseTransaction) }}"
               class="inline-flex items-center justify-center rounded-2xl border border-rose-200 bg-rose-50 px-5 py-3 text-sm font-semibold text-rose-700 transition hover:bg-rose-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-8.25a2.25 2.25 0 0 0-2.25-2.25h-8.25m0 0L4.5 8.25m4.5-4.5v4.5h4.5M6.75 12.75h10.5m-10.5 3h10.5m-10.5 3h6" />
                </svg>
                Export PDF
            </a>

            <a href="{{ route('purchase-transactions.export-detail.excel', $purchaseTransaction) }}"
               class="inline-flex items-center justify-center rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.5h16.5M3.75 9h16.5M9 4.5v15m6-15v15M5.25 19.5h13.5A1.5 1.5 0 0 0 20.25 18V6A1.5 1.5 0 0 0 18.75 4.5H5.25A1.5 1.5 0 0 0 3.75 6v12a1.5 1.5 0 0 0 1.5 1.5Z" />
                </svg>
                Export Excel
            </a>

            @if(! $purchaseTransaction->goodsReceipts()->exists())
            <a href="{{ route('purchase-transactions.edit', $purchaseTransaction) }}"
               class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-slate-900/10 transition hover:-translate-y-0.5 hover:bg-slate-800">
                <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                </svg>
                Edit Transaksi
            </a>
            @endif
        </div>
    </section>

    {{-- APPROVAL STATUS --}}
    @php
        $approvalStatus = $purchaseTransaction->approval_status ?? 'draft';
        $approvalConfig = match($approvalStatus) {
            'approved' => ['label' => 'Disetujui',          'bg' => 'bg-emerald-50', 'border' => 'border-emerald-200', 'text' => 'text-emerald-800', 'dot' => 'bg-emerald-500', 'icon_color' => 'text-emerald-600 bg-emerald-100'],
            'pending'  => ['label' => 'Menunggu Persetujuan','bg' => 'bg-amber-50',   'border' => 'border-amber-200',   'text' => 'text-amber-800',   'dot' => 'bg-amber-400',   'icon_color' => 'text-amber-600 bg-amber-100'],
            'rejected' => ['label' => 'Ditolak',            'bg' => 'bg-rose-50',    'border' => 'border-rose-200',    'text' => 'text-rose-800',    'dot' => 'bg-rose-500',    'icon_color' => 'text-rose-600 bg-rose-100'],
            default    => ['label' => 'Belum Diajukan',     'bg' => 'bg-slate-50',   'border' => 'border-slate-200',   'text' => 'text-slate-700',   'dot' => 'bg-slate-400',   'icon_color' => 'text-slate-500 bg-slate-100'],
        };
        $canSubmit  = in_array(auth()->user()->role, ['admin','staff'])
                      && in_array($approvalStatus, ['draft', 'rejected'], true);
        $canApprove = in_array(auth()->user()->role, ['admin','pimpinan'])
                      && $approvalStatus === 'pending';
        $canReject  = $canApprove;
    @endphp

    {{-- Mode Toko (pemilik perorangan): alur persetujuan tidak dipakai --}}
    @unless(config('store.mode'))
    <section x-data="{ showRejectModal: false }"
        class="rounded-[28px] border {{ $approvalConfig['border'] }} {{ $approvalConfig['bg'] }} p-5 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl {{ $approvalConfig['icon_color'] }}">
                    @if($approvalStatus === 'approved')
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                    @elseif($approvalStatus === 'pending')
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m5-2A9 9 0 1 1 3 12a9 9 0 0 1 18 0Z"/></svg>
                    @elseif($approvalStatus === 'rejected')
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15"/></svg>
                    @endif
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-bold {{ $approvalConfig['text'] }}">Approval: {{ $approvalConfig['label'] }}</span>
                    </div>
                    @if($approvalStatus === 'approved' && $purchaseTransaction->approvedBy)
                        <p class="mt-0.5 text-xs text-slate-500">
                            Disetujui oleh <strong>{{ $purchaseTransaction->approvedBy->name }}</strong>
                            pada {{ $purchaseTransaction->approved_at?->format('d M Y, H:i') }}
                        </p>
                    @elseif($approvalStatus === 'rejected')
                        <p class="mt-0.5 text-xs text-rose-600">
                            Alasan: {{ $purchaseTransaction->rejection_reason }}
                        </p>
                        @if($purchaseTransaction->approvedBy)
                        <p class="mt-0.5 text-xs text-slate-500">
                            Ditolak oleh <strong>{{ $purchaseTransaction->approvedBy->name }}</strong>
                            pada {{ $purchaseTransaction->approved_at?->format('d M Y, H:i') }}
                        </p>
                        @endif
                    @elseif($approvalStatus === 'pending')
                        <p class="mt-0.5 text-xs text-slate-500">Menunggu review dari admin atau pimpinan</p>
                    @else
                        <p class="mt-0.5 text-xs text-slate-500">Belum diajukan untuk persetujuan</p>
                    @endif
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @if($canSubmit)
                <form method="POST" action="{{ route('purchase-transactions.submit-approval', $purchaseTransaction) }}">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-2xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-md transition hover:-translate-y-0.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/></svg>
                        {{ $approvalStatus === 'rejected' ? 'Ajukan Ulang' : 'Ajukan Persetujuan' }}
                    </button>
                </form>
                @endif

                @if($canApprove)
                <form method="POST" action="{{ route('purchase-transactions.approve', $purchaseTransaction) }}">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-2xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md transition hover:-translate-y-0.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                        Setujui
                    </button>
                </form>
                @endif

                @if($canReject)
                <button type="button" @click="showRejectModal = true"
                    class="inline-flex items-center gap-2 rounded-2xl border border-rose-200 bg-white px-4 py-2.5 text-sm font-semibold text-rose-700 transition hover:bg-rose-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                    Tolak
                </button>
                @endif
            </div>
        </div>

        {{-- Modal Tolak --}}
        @if($canReject)
        <div x-show="showRejectModal" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             x-transition.opacity>
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showRejectModal = false"></div>
            <div class="relative w-full max-w-md rounded-[28px] bg-white p-6 shadow-2xl">
                <h3 class="text-lg font-bold text-slate-900">Tolak Transaksi</h3>
                <p class="mt-1 text-sm text-slate-500">Masukkan alasan penolakan. Alasan ini akan terlihat oleh pembuat transaksi.</p>

                <form method="POST" action="{{ route('purchase-transactions.reject', $purchaseTransaction) }}" class="mt-5">
                    @csrf
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600">Alasan Penolakan <span class="text-rose-500">*</span></label>
                        <textarea name="rejection_reason" rows="4" required maxlength="500"
                            placeholder="Contoh: Harga tidak sesuai, perlu revisi item..."
                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 placeholder-slate-400 focus:border-rose-300 focus:bg-white focus:outline-none focus:ring-4 focus:ring-rose-100 resize-none">{{ old('rejection_reason') }}</textarea>
                        @error('rejection_reason')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="mt-4 flex gap-3">
                        <button type="submit"
                            class="flex-1 rounded-2xl bg-rose-600 py-3 text-sm font-semibold text-white transition hover:bg-rose-700">
                            Konfirmasi Tolak
                        </button>
                        <button type="button" @click="showRejectModal = false"
                            class="flex-1 rounded-2xl border border-slate-200 bg-white py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif
    </section>
    @endunless

    {{-- BANNER: PPN Masukan otomatis (fitur pajak PKP, disembunyikan di Mode Toko) --}}
    @if($purchaseTransaction->ppnMasukan && ! config('store.mode'))
    @php $ppnDraft = !$purchaseTransaction->ppnMasukan->no_faktur; @endphp
    <div class="flex flex-wrap items-center gap-3 rounded-[22px] border
        {{ $ppnDraft ? 'border-amber-200 bg-amber-50' : 'border-emerald-200 bg-emerald-50' }} px-5 py-3.5 shadow-sm">
        <div class="flex-shrink-0 w-8 h-8 rounded-xl flex items-center justify-center
            {{ $ppnDraft ? 'bg-amber-100' : 'bg-emerald-100' }}">
            @if($ppnDraft)
            <svg class="h-4 w-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/>
            </svg>
            @else
            <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
            </svg>
            @endif
        </div>
        <div class="flex-1 min-w-0">
            @if($ppnDraft)
            <p class="text-sm font-semibold text-amber-800">
                Draft PPN Masukan dibuat otomatis — lengkapi nomor faktur pajak
            </p>
            <p class="text-xs text-amber-600 mt-0.5">
                DPP perkiraan: <strong>Rp {{ number_format($purchaseTransaction->ppnMasukan->dpp, 0, ',', '.') }}</strong>
                &bull; PPN: <strong>Rp {{ number_format($purchaseTransaction->ppnMasukan->ppn, 0, ',', '.') }}</strong>
            </p>
            @else
            <p class="text-sm font-semibold text-emerald-800">
                Faktur Pajak Masukan tercatat &bull; No. <span class="font-mono">{{ $purchaseTransaction->ppnMasukan->no_faktur }}</span>
            </p>
            <p class="text-xs text-emerald-600 mt-0.5">
                Tgl. {{ $purchaseTransaction->ppnMasukan->tanggal_faktur?->format('d/m/Y') }}
                &bull; PPN: <strong>Rp {{ number_format($purchaseTransaction->ppnMasukan->ppn, 0, ',', '.') }}</strong>
            </p>
            @endif
        </div>
        <a href="{{ route('tax.ppn.masukan.edit', $purchaseTransaction->ppnMasukan) }}"
           class="flex-shrink-0 inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold transition
               {{ $ppnDraft ? 'bg-amber-600 text-white hover:bg-amber-700' : 'border border-emerald-300 bg-white text-emerald-700 hover:bg-emerald-50' }}">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/>
            </svg>
            {{ $ppnDraft ? 'Lengkapi Sekarang' : 'Edit Faktur' }}
        </a>
    </div>
    @endif

    {{-- WORKSPACE --}}
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
        {{-- LEFT SIDE --}}
        <div class="space-y-6 xl:col-span-7">
            <section class="rounded-[30px] border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-6">
                    <div class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">
                        <span class="h-2 w-2 rounded-full bg-indigo-500"></span>
                        Invoice Details
                    </div>

                    <h2 class="mt-3 text-xl font-bold tracking-tight text-slate-900">
                        Informasi Transaksi
                    </h2>

                    <p class="mt-1 text-sm leading-6 text-slate-500">
                        Detail utama transaksi pembelian dalam pola layout yang lebih rapi seperti workspace preview.
                    </p>
                </div>

                <div class="space-y-5">
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-[0.16em] text-slate-500">
                            No. Transaksi
                        </label>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-sm font-semibold text-slate-900">
                            {{ $purchaseTransaction->transaction_no ?? '-' }}
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-[0.16em] text-slate-500">
                            Tanggal
                        </label>
                        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3.5 text-sm font-semibold text-slate-800">
                            {{ $purchaseTransaction->transaction_date?->format('d F Y') ?? '-' }}
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-[0.16em] text-slate-500">
                            Supplier
                        </label>
                        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3.5 text-sm font-semibold text-slate-800">
                            {{ $purchaseTransaction->supplier?->name ?? '-' }}
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-[0.16em] text-slate-500">
                            Proyek
                        </label>
                        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3.5 text-sm font-semibold text-slate-800">
                            {{ $purchaseTransaction->project?->name ?? '-' }}
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-[0.16em] text-slate-500">
                            Status
                        </label>
                        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3.5">
                            <span class="inline-flex items-center rounded-full px-3 py-1.5 text-xs font-semibold {{ $statusConfig['badge'] }}">
                                {{ $statusConfig['label'] }}
                            </span>
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-[0.16em] text-slate-500">
                            No. Nota
                        </label>
                        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3.5 text-sm font-semibold text-slate-800">
                            {{ $purchaseTransaction->note_number ?: '-' }}
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-[0.16em] text-slate-500">
                            Input Oleh
                        </label>
                        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3.5 text-sm font-semibold text-slate-800">
                            {{ $purchaseTransaction->user?->name ?? '-' }}
                        </div>
                    </div>

                    <div class="pt-1">
                        <div class="flex items-center gap-3">
                            <input type="checkbox" checked class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-400">
                            <label class="text-sm font-medium text-slate-700">
                                Tampilkan diskon transaksi
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-[0.16em] text-slate-500">
                            Catatan
                        </label>
                        <div class="min-h-[84px] rounded-2xl border border-slate-200 bg-white px-4 py-3.5 text-sm leading-6 text-slate-700">
                            {{ $purchaseTransaction->notes ?: '-' }}
                        </div>
                    </div>
                </div>
            </section>

            <section class="rounded-[30px] border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">
                            <span class="h-2 w-2 rounded-full bg-indigo-500"></span>
                            Product
                        </div>
                        <h2 class="mt-3 text-lg font-bold tracking-tight text-slate-900">
                            Item Pembelian
                        </h2>
                    </div>

                    <div class="text-xs font-medium text-slate-400">
                        {{ $items->count() }} item
                    </div>
                </div>

                <div class="space-y-3">
                    @forelse($items as $item)
                        <div class="rounded-2xl border border-slate-200 bg-slate-50/60 p-4">
                            <div class="grid grid-cols-1 gap-4 lg:grid-cols-12 lg:items-center">
                                <div class="lg:col-span-5">
                                    <div class="text-sm font-bold text-slate-900">
                                        {{ $item->material_name_snapshot }}
                                    </div>
                                    <div class="mt-1 text-xs text-slate-500">
                                        {{ $item->notes ?: 'Tanpa catatan item' }}
                                    </div>
                                </div>

                                <div class="lg:col-span-2">
                                    <div class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400">Qty</div>
                                    <div class="mt-1 text-sm font-semibold text-slate-800">
                                        {{ rtrim(rtrim(number_format($item->qty, 2, '.', ''), '0'), '.') }}
                                    </div>
                                </div>

                                <div class="lg:col-span-2">
                                    <div class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400">Satuan</div>
                                    <div class="mt-1 text-sm font-semibold text-slate-800">
                                        {{ $item->unit_name_snapshot }}
                                    </div>
                                </div>

                                <div class="lg:col-span-3 text-left lg:text-right">
                                    <div class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400">Subtotal</div>
                                    <div class="mt-1 text-sm font-bold text-slate-900">
                                        Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                    </div>
                                    <div class="mt-1 text-xs text-slate-500">
                                        Harga: Rp {{ number_format($item->price, 0, ',', '.') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center">
                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-white text-slate-400 shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75A2.25 2.25 0 0 1 6 4.5h12a2.25 2.25 0 0 1 2.25 2.25v10.5A2.25 2.25 0 0 1 18 19.5H6a2.25 2.25 0 0 1-2.25-2.25V6.75Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 9h9m-9 3h9m-9 3h6" />
                                </svg>
                            </div>
                            <h3 class="mt-4 text-base font-bold text-slate-900">Belum ada item pembelian</h3>
                            <p class="mt-2 text-sm text-slate-500">
                                Rincian item material belum tersedia pada transaksi ini.
                            </p>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>

        {{-- RIGHT PREVIEW --}}
        <div class="space-y-6 xl:col-span-5">
    <div class="xl:sticky xl:top-6">
        <section class="rounded-[32px] border border-slate-200 bg-slate-50/80 p-5 shadow-sm">
            <div class="mb-4 flex items-center justify-between gap-3 px-1">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-white text-slate-500 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-lg font-bold text-slate-900">Preview</div>
                        <div class="text-xs text-slate-400">Workspace detail transaksi</div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2 text-[11px] font-semibold">
                    <button type="button"
                            class="preview-tab-btn rounded-full border border-slate-200 bg-white px-3 py-1 text-slate-500 transition hover:bg-slate-100 active"
                            data-preview-target="pdf-panel">
                        PDF
                    </button>

                    <button type="button"
                            class="preview-tab-btn rounded-full border border-slate-200 bg-white px-3 py-1 text-slate-500 transition hover:bg-slate-100"
                            data-preview-target="email-panel">
                        Invoice
                    </button>

                    <!-- <button type="button"
                            class="preview-tab-btn rounded-full border border-slate-200 bg-white px-3 py-1 text-slate-500 transition hover:bg-slate-100"
                            data-preview-target="payment-panel">
                        Payment page
                    </button> -->
                </div>
            </div>

            {{-- PANEL PDF --}}
            <div id="pdf-panel" class="preview-panel">
                <div class="rounded-[28px] border border-slate-200 bg-white p-3 shadow-sm">
                    <iframe
                        src="{{ route('purchase-transactions.preview-detail.pdf', $purchaseTransaction) }}#toolbar=1&navpanes=0&scrollbar=1"
                        class="h-[900px] w-full rounded-[22px] border border-slate-200 bg-white"
                    ></iframe>
                </div>
            </div>

            {{-- PANEL EMAIL --}}
            <div id="email-panel" class="preview-panel hidden">
                <div class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="border-b border-slate-100 pb-5">
                        <div class="text-xl font-extrabold tracking-tight text-slate-900">
                            {{ $purchaseTransaction->transaction_no ?? 'TRX-0001' }}
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-5 border-b border-slate-100 py-5">
                        <div>
                            <div class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">Due date</div>
                            <div class="mt-1 text-sm font-semibold text-slate-900">
                                {{ $purchaseTransaction->transaction_date?->format('d F Y') ?? '-' }}
                            </div>
                        </div>

                        <div>
                            <div class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">Status</div>
                            <div class="mt-1">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusConfig['badge'] }}">
                                    {{ $statusConfig['label'] }}
                                </span>
                            </div>
                        </div>

                        <div>
                            <div class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">Billed to</div>
                            <div class="mt-1 text-sm font-semibold text-slate-900">
                                {{ $purchaseTransaction->supplier?->name ?? '-' }}
                            </div>
                        </div>

                        <div>
                            <div class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">Currency</div>
                            <div class="mt-1 text-sm font-semibold text-slate-900">
                                IDR - Indonesian Rupiah
                            </div>
                        </div>
                    </div>

                    <div class="py-5">
                        <div class="mb-3 grid grid-cols-12 gap-3 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">
                            <div class="col-span-6">Description</div>
                            <div class="col-span-2 text-center">Qty</div>
                            <div class="col-span-4 text-right">Amount</div>
                        </div>

                        <div class="space-y-3">
                            @forelse($items->take(4) as $item)
                                <div class="grid grid-cols-12 gap-3 rounded-2xl border border-slate-200 bg-slate-50/60 px-3 py-3">
                                    <div class="col-span-6 min-w-0">
                                        <div class="truncate text-sm font-bold text-slate-900">
                                            {{ $item->material_name_snapshot }}
                                        </div>
                                        <div class="mt-1 text-xs text-slate-500">
                                            Rp {{ number_format($item->price, 0, ',', '.') }}
                                        </div>
                                    </div>

                                    <div class="col-span-2 flex items-center justify-center text-sm font-semibold text-slate-700">
                                        {{ rtrim(rtrim(number_format($item->qty, 2, '.', ''), '0'), '.') }}
                                    </div>

                                    <div class="col-span-4 flex items-center justify-end text-sm font-bold text-slate-900">
                                        Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                    </div>
                                </div>
                            @empty
                                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">
                                    Belum ada item untuk ditampilkan.
                                </div>
                            @endforelse

                            @if($items->count() > 4)
                                <div class="text-center text-xs font-medium text-slate-400">
                                    +{{ $items->count() - 4 }} item lainnya
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="border-t border-slate-100 pt-5">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-sm text-slate-500">
                                <span>Subtotal</span>
                                <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                            </div>

                            <div class="flex items-center justify-between text-sm text-slate-500">
                                <span>Discount</span>
                                <span>{{ $discount > 0 ? 'Rp ' . number_format($discount, 0, ',', '.') : '-' }}</span>
                            </div>

                            <div class="flex items-center justify-between border-t border-dashed border-slate-200 pt-3 text-base font-bold text-slate-900">
                                <span>Total</span>
                                <span>Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
                            </div>

                            <div class="flex items-center justify-between text-sm font-semibold text-slate-800">
                                <span>Amount due</span>
                                <span>Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- PANEL PAYMENT --}}
            <div id="payment-panel" class="preview-panel hidden">
                <div class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm space-y-5">

                    @php
                        $paidAmount   = (float) ($purchaseTransaction->paid_amount ?? 0);
                        $creditAmount = app(\App\Services\Accounting\SupplierPaymentService::class)->creditedAmount($purchaseTransaction);
                        $remaining    = max(0, $grandTotal - $paidAmount - $creditAmount);
                        $paidPct      = $grandTotal > 0 ? min(100, round(($paidAmount / $grandTotal) * 100)) : 0;
                        $paymentStatus = $purchaseTransaction->payment_status ?? 'unpaid';
                        $supplierPayments = $purchaseTransaction->supplierPayments ?? collect();
                    @endphp

                    {{-- Payment Summary --}}
                    <div>
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-3">Ringkasan Pembayaran</p>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500">Grand Total</span>
                                <span class="font-bold text-slate-800">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500">Sudah Dibayar</span>
                                <span class="font-bold text-emerald-600">Rp {{ number_format($paidAmount, 0, ',', '.') }}</span>
                            </div>
                            @if($creditAmount > 0)
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500">Nota Kredit Retur</span>
                                <span class="font-bold text-indigo-600">Rp {{ number_format($creditAmount, 0, ',', '.') }}</span>
                            </div>
                            @endif
                            <div class="border-t border-dashed border-slate-200 pt-2 flex justify-between items-center">
                                <span class="font-semibold text-slate-700">Sisa Hutang</span>
                                <span class="font-extrabold text-lg {{ $remaining > 0 ? 'text-orange-600' : 'text-emerald-600' }}">
                                    Rp {{ number_format($remaining, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                        {{-- Progress --}}
                        <div class="mt-3">
                            <div class="w-full bg-slate-100 rounded-full h-2">
                                <div class="h-2 rounded-full {{ $paidPct >= 100 ? 'bg-emerald-500' : ($paidPct > 0 ? 'bg-indigo-500' : 'bg-slate-300') }}"
                                     style="width: {{ $paidPct }}%"></div>
                            </div>
                            <p class="text-xs text-slate-400 text-right mt-1">{{ $paidPct }}% terbayar</p>
                        </div>
                    </div>

                    {{-- Catat Pembayaran --}}
                    @if($remaining > 0)
                    <div class="border-t border-slate-200 pt-4">
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-3">Catat Pembayaran Baru</p>
                        <form method="POST" action="{{ route('supplier-payments.store', $purchaseTransaction) }}" class="space-y-3">
                            @csrf
                            <div>
                                <label class="text-xs font-semibold text-slate-600 mb-1 block">Tanggal Bayar</label>
                                <input type="date" name="payment_date" value="{{ now()->toDateString() }}"
                                       class="w-full rounded-xl border border-slate-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400" required>
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-slate-600 mb-1 block">Jumlah (Rp)</label>
                                <input type="number" name="amount" value="{{ $remaining }}" step="1000" min="1" max="{{ $remaining }}"
                                       class="w-full rounded-xl border border-slate-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400" required>
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-slate-600 mb-1 block">Metode Bayar</label>
                                <select name="payment_method" class="w-full rounded-xl border border-slate-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                    <option value="transfer">Transfer Bank</option>
                                    <option value="cash">Tunai</option>
                                    <option value="giro">Giro</option>
                                    <option value="check">Cek</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-slate-600 mb-1 block">No. Referensi (opsional)</label>
                                <input type="text" name="reference_no" placeholder="No. bukti transfer / kuitansi"
                                       class="w-full rounded-xl border border-slate-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            </div>
                            <button type="submit" class="w-full flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-indigo-700 transition-colors">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                                </svg>
                                Catat Pembayaran
                            </button>
                        </form>
                    </div>
                    @endif

                    {{-- Riwayat Pembayaran --}}
                    @if($supplierPayments->isNotEmpty())
                    <div class="border-t border-slate-200 pt-4">
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-3">Riwayat Pembayaran</p>
                        <div class="space-y-2">
                            @foreach($supplierPayments as $pay)
                            <div class="flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2.5">
                                <div>
                                    <p class="text-sm font-bold text-slate-800">Rp {{ number_format($pay->amount, 0, ',', '.') }}</p>
                                    <p class="text-xs text-slate-500">{{ \Carbon\Carbon::parse($pay->payment_date)->format('d M Y') }} · {{ ucfirst($pay->payment_method) }}</p>
                                    @if($pay->reference_no)
                                        <p class="text-xs text-slate-400">Ref: {{ $pay->reference_no }}</p>
                                    @endif
                                </div>
                                <form method="POST" action="{{ route('supplier-payments.destroy', [$purchaseTransaction, $pay]) }}"
                                      onsubmit="return confirm('Hapus catatan pembayaran ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs text-red-400 hover:text-red-600">Hapus</button>
                                </form>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                </div>
            </div>
        </section>
    </div>
</div>
    </div>

    {{-- PENERIMAAN BARANG --}}
    @php
        $goodsReceipts = $purchaseTransaction->goodsReceipts ?? collect();
    @endphp
    <section class="rounded-[30px] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">
                    <span class="h-2 w-2 rounded-full bg-indigo-500"></span>
                    Penerimaan Barang
                </div>
                <h2 class="mt-3 text-lg font-bold tracking-tight text-slate-900">Surat Jalan</h2>
                <p class="mt-1 text-sm text-slate-500">Riwayat penerimaan fisik barang dari transaksi ini.</p>
            </div>

            @if(auth()->user()->isAdmin() || (auth()->user()->isStaff() && $purchaseTransaction->user_id === auth()->id()))
            <a href="{{ route('purchase-transactions.goods-receipts.create', $purchaseTransaction) }}"
               class="btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Buat Surat Jalan
            </a>
            @endif
        </div>

        @if($goodsReceipts->count() > 0)
            <div class="space-y-3">
                @foreach($goodsReceipts as $receipt)
                    @php
                        $receiptStatusConfig = match($receipt->status) {
                            'complete' => ['badge' => 'badge-success', 'dot' => 'bg-emerald-500', 'label' => 'Lengkap'],
                            'partial'  => ['badge' => 'badge-warning', 'dot' => 'bg-amber-400',   'label' => 'Sebagian'],
                            default    => ['badge' => 'badge-neutral', 'dot' => 'bg-slate-400',   'label' => 'Belum Diterima'],
                        };
                    @endphp
                    <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-slate-50/60 p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl bg-white shadow-sm">
                                <span class="h-2.5 w-2.5 rounded-full {{ $receiptStatusConfig['dot'] }}"></span>
                            </span>
                            <div>
                                <p class="text-sm font-bold text-slate-900">{{ $receipt->receipt_no }}</p>
                                <p class="text-xs text-slate-500">
                                    {{ $receipt->receipt_date->translatedFormat('d F Y') }} &middot;
                                    Diterima oleh: <strong>{{ $receipt->received_by }}</strong>
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="{{ $receiptStatusConfig['badge'] }}">
                                {{ $receiptStatusConfig['label'] }}
                            </span>
                            <a href="{{ route('goods-receipts.show', $receipt) }}" class="btn-secondary">
                                Lihat Detail
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-white text-slate-400 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 1-.987-1.106v.958m12 0a1.5 1.5 0 0 0-1.5-1.5H6.75a1.5 1.5 0 0 0-1.5 1.5m12 0v.958c0 .568-.422 1.048-.987 1.106a48.554 48.554 0 0 1-10.026 0 1.106 1.106 0 0 0-.987 1.106v.958"/>
                    </svg>
                </div>
                <h3 class="mt-3 text-sm font-bold text-slate-900">Belum ada penerimaan barang</h3>
                <p class="mt-1 text-xs text-slate-500">Buat surat jalan untuk mencatat penerimaan barang dari transaksi ini.</p>
            </div>
        @endif
    </section>

    {{-- RETURN KE VENDOR --}}
    @php
        $purchaseReturns = $purchaseTransaction->returns ?? collect();
        $canCreateReturn = in_array(auth()->user()->role, ['admin','staff','purchasing'])
                          && $purchaseTransaction->goodsReceipts?->count() > 0;
    @endphp
    <section class="rounded-[30px] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full border border-rose-200 bg-rose-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-rose-600">
                    <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                    Return ke Vendor
                </div>
                <h2 class="mt-3 text-lg font-bold tracking-tight text-slate-900">Pengembalian Barang</h2>
                <p class="mt-1 text-sm text-slate-500">Riwayat pengembalian barang ke supplier dari transaksi ini.</p>
            </div>

            @if($canCreateReturn)
            <a href="{{ route('purchase-returns.create', $purchaseTransaction) }}"
               class="inline-flex items-center gap-2 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-sm font-semibold text-rose-700 transition hover:bg-rose-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3"/>
                </svg>
                Buat Return
            </a>
            @endif
        </div>

        @if($purchaseReturns->count() > 0)
            <div class="space-y-3">
                @foreach($purchaseReturns as $ret)
                    @php
                        $retStatusConfig = match($ret->status) {
                            'returned'  => ['badge' => 'badge-success', 'dot' => 'bg-emerald-500', 'label' => 'Dikembalikan'],
                            'credited'  => ['badge' => 'badge-info',    'dot' => 'bg-indigo-500',  'label' => 'Dikreditkan'],
                            'submitted' => ['badge' => 'badge-warning', 'dot' => 'bg-amber-400',   'label' => 'Menunggu Approval'],
                            'approved'  => ['badge' => 'badge-blue',    'dot' => 'bg-blue-500',    'label' => 'Disetujui'],
                            default     => ['badge' => 'badge-neutral', 'dot' => 'bg-slate-400',   'label' => 'Draft'],
                        };
                        $retReasonLabel = match($ret->reason) {
                            'wrong_item'      => 'Item Salah',
                            'damaged'         => 'Barang Rusak',
                            'quantity_excess' => 'Kelebihan Kuantitas',
                            'quality_reject'  => 'Tidak Lulus QC',
                            'spec_mismatch'   => 'Spesifikasi Tidak Sesuai',
                            default           => 'Lainnya',
                        };
                    @endphp
                    <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-slate-50/60 p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl bg-white shadow-sm">
                                <span class="h-2.5 w-2.5 rounded-full {{ $retStatusConfig['dot'] }}"></span>
                            </span>
                            <div>
                                <p class="text-sm font-bold text-slate-900">{{ $ret->return_no }}</p>
                                <p class="text-xs text-slate-500">
                                    <span class="font-medium text-rose-600">{{ $retReasonLabel }}</span>
                                    &middot;
                                    Total: <strong>Rp {{ number_format($ret->total_return_value ?? 0, 0, ',', '.') }}</strong>
                                    @if($ret->return_date)
                                    &middot; {{ \Carbon\Carbon::parse($ret->return_date)->format('d M Y') }}
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="{{ $retStatusConfig['badge'] }}">
                                {{ $retStatusConfig['label'] }}
                            </span>
                            <a href="{{ route('purchase-returns.show', $ret) }}" class="btn-secondary">
                                Lihat Detail
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-white text-slate-400 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3"/>
                    </svg>
                </div>
                <h3 class="mt-3 text-sm font-bold text-slate-900">Belum ada return</h3>
                <p class="mt-1 text-xs text-slate-500">Return barang ke supplier belum pernah dilakukan untuk transaksi ini.</p>
                @if($canCreateReturn)
                <a href="{{ route('purchase-returns.create', $purchaseTransaction) }}"
                   class="mt-4 inline-flex items-center gap-1.5 rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-rose-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                    </svg>
                    Buat Return Pertama
                </a>
                @endif
            </div>
        @endif
    </section>

    {{-- LAMPIRAN LIST --}}
    @if($attachments->count())
        <section class="rounded-[30px] border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-5">
                <div class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">
                    <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                    Lampiran Nota
                </div>

                <h2 class="mt-3 text-lg font-bold tracking-tight text-slate-900">
                    Dokumen Pendukung
                </h2>

                <p class="mt-1 text-sm leading-6 text-slate-500">
                    File nota dan dokumen pendukung transaksi lengkap dengan preview dan download.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach($attachments as $attachment)
                    @php
                        $fileUrl = $attachment->url;
                        $fileName = $attachment->file_name;
                        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                        $mime = strtolower($attachment->mime_type ?? '');

                        $isPdf = str_contains($mime, 'pdf') || $extension === 'pdf';
                        $isExcel = in_array($extension, ['xlsx', 'xls', 'csv']) ||
                                   str_contains($mime, 'spreadsheet') ||
                                   str_contains($mime, 'excel') ||
                                   str_contains($mime, 'csv');

                        $previewType = $isPdf ? 'pdf' : ($isExcel ? 'excel' : 'other');
                    @endphp

                    <div class="rounded-[24px] border border-slate-200 bg-slate-50 p-4 shadow-sm transition hover:-translate-y-0.5 hover:bg-white">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white text-slate-500 shadow-sm">
                                @if($isPdf)
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-8.25a2.25 2.25 0 0 0-2.25-2.25h-8.25m0 0L4.5 8.25m4.5-4.5v4.5h4.5M6.75 12.75h10.5m-10.5 3h10.5m-10.5 3h6" />
                                    </svg>
                                @elseif($isExcel)
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.5h16.5M3.75 9h16.5M9 4.5v15m6-15v15M5.25 19.5h13.5A1.5 1.5 0 0 0 20.25 18V6A1.5 1.5 0 0 0 18.75 4.5H5.25A1.5 1.5 0 0 0 3.75 6v12a1.5 1.5 0 0 0 1.5 1.5Z" />
                                    </svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-8.25a2.25 2.25 0 0 0-2.25-2.25h-8.25m0 0L4.5 8.25m4.5-4.5v4.5h4.5M6.75 12.75h10.5m-10.5 3h10.5m-10.5 3h6" />
                                    </svg>
                                @endif
                            </div>

                            <a href="{{ $fileUrl }}"
                               download="{{ $fileName }}"
                               class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white p-2 text-slate-600 transition hover:bg-slate-100 hover:text-slate-900"
                               title="Download">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0 4-4m-4 4-4-4M4.5 16.5v1.125A2.625 2.625 0 0 0 7.125 20.25h9.75A2.625 2.625 0 0 0 19.5 17.625V16.5" />
                                </svg>
                            </a>
                        </div>

                        <div class="mt-4 break-all font-bold text-slate-900">
                            {{ $fileName }}
                        </div>

                        <div class="mt-3 flex flex-wrap gap-2">
                            <span class="inline-flex rounded-full bg-white px-3 py-1 text-xs font-medium text-slate-500 ring-1 ring-slate-200">
                                {{ strtoupper($extension ?: 'FILE') }}
                            </span>
                            <span class="inline-flex rounded-full bg-white px-3 py-1 text-xs font-medium text-slate-500 ring-1 ring-slate-200">
                                {{ number_format(($attachment->file_size ?? 0) / 1024, 1) }} KB
                            </span>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-2">
                            @if($previewType !== 'other')
                                <button type="button"
                                        class="preview-trigger inline-flex items-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800"
                                        data-preview-type="{{ $previewType }}"
                                        data-file-url="{{ $fileUrl }}"
                                        data-file-name="{{ $fileName }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12S5.25 5.25 12 5.25 21.75 12 21.75 12 18.75 18.75 12 18.75 2.25 12 2.25 12Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15.75A3.75 3.75 0 1 0 12 8.25a3.75 3.75 0 0 0 0 7.5Z" />
                                    </svg>
                                    Preview
                                </button>
                            @else
                                <a href="{{ $fileUrl }}"
                                   target="_blank"
                                   class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">
                                    Buka File
                                </a>
                            @endif

                            <a href="{{ $fileUrl }}"
                               download="{{ $fileName }}"
                               class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">
                                Download
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</div>

{{-- PREVIEW MODAL --}}
<div id="filePreviewModal" class="fixed inset-0 z-[9999] hidden">
    <div class="absolute inset-0 bg-slate-900/70 backdrop-blur-sm"></div>

    <div class="relative mx-auto flex h-full w-full max-w-7xl items-center justify-center p-4 sm:p-6">
        <div class="flex h-[92vh] w-full flex-col overflow-hidden rounded-[28px] bg-white shadow-2xl">
            <div class="flex items-center justify-between gap-4 border-b border-slate-200 px-5 py-4">
                <div class="min-w-0">
                    <h3 id="previewTitle" class="truncate text-lg font-bold text-slate-900">Preview File</h3>
                    <p id="previewSubtitle" class="mt-1 text-sm text-slate-500">Menampilkan isi file lampiran.</p>
                </div>

                <div class="flex items-center gap-2">
                    <a id="previewDownloadBtn"
                       href="#"
                       download
                       class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">
                        Download
                    </a>

                    <button type="button"
                            id="closePreviewModal"
                            class="inline-flex items-center justify-center rounded-xl bg-slate-900 p-2 text-white transition hover:bg-slate-800">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <div id="previewLoading" class="hidden px-6 py-4 text-sm font-medium text-slate-500">
                Memuat preview file...
            </div>

            <div id="pdfPreviewWrap" class="hidden flex-1 bg-slate-100">
                <iframe id="pdfPreviewFrame" class="h-full w-full" src=""></iframe>
            </div>

            <div id="excelPreviewWrap" class="hidden flex-1 overflow-auto bg-white p-5">
                <div class="mb-4">
                    <h4 class="text-base font-bold text-slate-900">Live Preview Excel</h4>
                    <p class="text-sm text-slate-500">Menampilkan sheet pertama dari file Excel/CSV.</p>
                </div>

                <div id="excelPreviewContent" class="overflow-auto rounded-2xl border border-slate-200"></div>
            </div>

            <div id="otherPreviewWrap" class="hidden flex-1 items-center justify-center bg-slate-50 p-8">
                <div class="max-w-md text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-white text-slate-400 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-8.25a2.25 2.25 0 0 0-2.25-2.25h-8.25m0 0L4.5 8.25m4.5-4.5v4.5h4.5M6.75 12.75h10.5m-10.5 3h10.5m-10.5 3h6" />
                        </svg>
                    </div>
                    <h4 class="mt-4 text-lg font-bold text-slate-900">Preview tidak tersedia</h4>
                    <p class="mt-2 text-sm leading-6 text-slate-500">
                        Tipe file ini belum didukung untuk live preview.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const buttons = document.querySelectorAll('.preview-tab-btn');
    const panels = document.querySelectorAll('.preview-panel');

    function activatePreview(targetId) {
        panels.forEach(panel => {
            panel.classList.add('hidden');
        });

        buttons.forEach(button => {
            button.classList.remove('active', 'bg-slate-900', 'text-white', 'border-slate-900');
            button.classList.add('bg-white', 'text-slate-500', 'border-slate-200');
        });

        const activePanel = document.getElementById(targetId);
        if (activePanel) {
            activePanel.classList.remove('hidden');
        }

        const activeButton = document.querySelector(`[data-preview-target="${targetId}"]`);
        if (activeButton) {
            activeButton.classList.add('active', 'bg-slate-900', 'text-white', 'border-slate-900');
            activeButton.classList.remove('bg-white', 'text-slate-500', 'border-slate-200');
        }
    }

    buttons.forEach(button => {
        button.addEventListener('click', function () {
            activatePreview(this.dataset.previewTarget);
        });
    });

    activatePreview('pdf-panel');
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('filePreviewModal');
    const closeBtn = document.getElementById('closePreviewModal');
    const previewTitle = document.getElementById('previewTitle');
    const previewSubtitle = document.getElementById('previewSubtitle');
    const previewDownloadBtn = document.getElementById('previewDownloadBtn');
    const loadingEl = document.getElementById('previewLoading');

    const pdfWrap = document.getElementById('pdfPreviewWrap');
    const pdfFrame = document.getElementById('pdfPreviewFrame');

    const excelWrap = document.getElementById('excelPreviewWrap');
    const excelContent = document.getElementById('excelPreviewContent');

    const otherWrap = document.getElementById('otherPreviewWrap');

    const triggers = document.querySelectorAll('.preview-trigger');

    function resetPreview() {
        pdfWrap.classList.add('hidden');
        excelWrap.classList.add('hidden');
        otherWrap.classList.add('hidden');
        loadingEl.classList.add('hidden');
        pdfFrame.src = '';
        excelContent.innerHTML = '';
    }

    function openModal() {
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeModal() {
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        resetPreview();
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function renderExcelTable(rows) {
        if (!rows || !rows.length) {
            return `<div class="p-6 text-sm text-slate-500">Tidak ada data yang bisa ditampilkan dari file ini.</div>`;
        }

        let html = '<div class="overflow-auto">';
        html += '<table class="min-w-full text-sm">';
        html += '<thead class="bg-slate-50">';

        const headerRow = rows[0] || [];
        html += '<tr>';
        headerRow.forEach(cell => {
            html += `<th class="border-b border-slate-200 px-4 py-3 text-left font-bold text-slate-700 whitespace-nowrap">${escapeHtml(cell)}</th>`;
        });
        html += '</tr></thead><tbody>';

        rows.slice(1).forEach(row => {
            html += '<tr class="odd:bg-white even:bg-slate-50/50">';
            const normalizedRow = row || [];
            const maxCols = Math.max(headerRow.length, normalizedRow.length);

            for (let i = 0; i < maxCols; i++) {
                html += `<td class="border-b border-slate-100 px-4 py-3 align-top text-slate-600 whitespace-nowrap">${escapeHtml(normalizedRow[i] ?? '')}</td>`;
            }

            html += '</tr>';
        });

        html += '</tbody></table></div>';
        return html;
    }

    async function previewPdf(fileUrl, fileName) {
        resetPreview();
        previewTitle.textContent = fileName;
        previewSubtitle.textContent = 'Preview PDF langsung di browser.';
        previewDownloadBtn.href = fileUrl;
        previewDownloadBtn.setAttribute('download', fileName);

        pdfWrap.classList.remove('hidden');
        pdfFrame.src = fileUrl + '#toolbar=1&navpanes=1&scrollbar=1';
        openModal();
    }

    async function previewExcel(fileUrl, fileName) {
        resetPreview();
        previewTitle.textContent = fileName;
        previewSubtitle.textContent = 'Preview Excel/CSV dari sheet pertama.';
        previewDownloadBtn.href = fileUrl;
        previewDownloadBtn.setAttribute('download', fileName);

        openModal();
        excelWrap.classList.remove('hidden');
        loadingEl.classList.remove('hidden');

        try {
            const response = await fetch(fileUrl);
            if (!response.ok) throw new Error('Gagal mengambil file');

            const arrayBuffer = await response.arrayBuffer();
            const workbook = XLSX.read(arrayBuffer, { type: 'array' });
            const firstSheetName = workbook.SheetNames[0];
            const worksheet = workbook.Sheets[firstSheetName];

            const rows = XLSX.utils.sheet_to_json(worksheet, {
                header: 1,
                defval: ''
            });

            excelContent.innerHTML = `
                <div class="border-b border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700">
                    Sheet: ${escapeHtml(firstSheetName)}
                </div>
                ${renderExcelTable(rows)}
            `;
        } catch (error) {
            excelContent.innerHTML = `
                <div class="p-6">
                    <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">
                        Gagal memuat preview Excel. File tetap bisa didownload.
                    </div>
                </div>
            `;
        } finally {
            loadingEl.classList.add('hidden');
        }
    }

    triggers.forEach(button => {
        button.addEventListener('click', async function () {
            const type = this.dataset.previewType;
            const fileUrl = this.dataset.fileUrl;
            const fileName = this.dataset.fileName;

            if (type === 'pdf') {
                await previewPdf(fileUrl, fileName);
            } else if (type === 'excel') {
                await previewExcel(fileUrl, fileName);
            } else {
                resetPreview();
                previewTitle.textContent = fileName;
                previewSubtitle.textContent = 'Preview file.';
                previewDownloadBtn.href = fileUrl;
                previewDownloadBtn.setAttribute('download', fileName);
                otherWrap.classList.remove('hidden');
                openModal();
            }
        });
    });

    closeBtn.addEventListener('click', closeModal);

    modal.addEventListener('click', function (e) {
        if (e.target === modal || e.target.classList.contains('bg-slate-900/70')) {
            closeModal();
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });
});
</script>
@endsection