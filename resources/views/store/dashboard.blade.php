@extends('layouts.app')
@section('content')
@php
    $rp = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');
    $qty = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, ',', '.'), '0'), ',');
    $hour = (int) now()->format('H');
    $greet = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 18 ? 'Selamat sore' : 'Selamat malam'));
    $firstName = \Illuminate\Support\Str::of(auth()->user()->name)->before(' ');
    $setupDone = collect($setupSteps)->where('done', true)->count();
    $setupTotal = count($setupSteps);
    $toneClass = [
        'brand'   => ['bg-brand-50 text-brand-600', 'bg-brand-600 text-white'],
        'danger'  => ['bg-rose-50 text-rose-600', 'bg-rose-600 text-white'],
        'warning' => ['bg-amber-50 text-amber-600', 'bg-amber-500 text-white'],
        'info'    => ['bg-sky-50 text-sky-600', 'bg-sky-600 text-white'],
    ];
@endphp

<div class="store-dashboard">

{{-- Sapaan --}}
<div class="mb-5 flex items-center justify-between gap-4 px-1 py-1">
    <div class="min-w-0">
        <p class="text-[11px] font-semibold uppercase tracking-[0.12em] text-charcoal-400">{{ now()->translatedFormat('l, d F Y') }}</p>
        <h1 class="mt-1 truncate text-lg font-extrabold tracking-tight text-charcoal-900 sm:text-xl">{{ $greet }}, {{ $firstName }}</h1>
        <p class="mt-0.5 text-xs text-charcoal-500">
            @if(count($tasks) > 0)
                {{ count($tasks) }} hal perlu diperiksa hari ini
            @else
                Semua aktivitas toko aman
            @endif
        </p>
    </div>
    <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-brand-50 text-brand-600 ring-1 ring-brand-100">
        <x-icon name="helmet" class="h-5 w-5" />
    </span>
</div>

{{-- Ringkasan utama --}}
<div class="mb-4 rounded-2xl bg-charcoal-900 px-4 py-4 text-white shadow-sm sm:px-5">
    <div class="flex items-end justify-between gap-3">
        <div class="min-w-0">
            <p class="text-[11px] font-semibold text-charcoal-300">Penjualan hari ini</p>
            <p class="mt-1 text-xl font-extrabold tracking-tight sm:text-2xl">{{ $rp($summary['omzet']) }}</p>
            <p class="mt-1 text-xs text-charcoal-300">{{ $summary['transactions'] }} transaksi</p>
        </div>
        <div class="text-right"><p class="text-[11px] text-charcoal-300">Untung kotor</p><p class="mt-1 text-sm font-bold text-emerald-300">{{ $rp($summary['gross_profit']) }}</p></div>
    </div>
</div>

{{-- Persiapan awal: hilang setelah semua langkah selesai --}}
@if($setupDone < $setupTotal && auth()->user()->isAdmin())
<div class="card mb-4 overflow-hidden" x-data="{ open: false }">
    <button type="button" @click="open = !open" class="flex w-full items-center gap-3 px-4 py-3 text-left">
        <span class="relative inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand-50 text-xs font-extrabold text-brand-700">
            {{ $setupDone }}/{{ $setupTotal }}
        </span>
        <span class="min-w-0 flex-1">
            <span class="block text-sm font-bold text-charcoal-900">Siapkan toko Anda</span>
            <span class="block text-xs text-charcoal-500">Selesaikan langkah ini sekali saja supaya semua fitur berjalan.</span>
        </span>
        <x-icon name="chevron" class="h-5 w-5 shrink-0 text-charcoal-400 transition-transform" ::class="open ? 'rotate-90' : ''" />
    </button>
    <div class="h-1.5 bg-charcoal-100"><div class="h-1.5 bg-brand-500" style="width: {{ round($setupDone / $setupTotal * 100) }}%"></div></div>
    <div x-show="open" x-collapse>
        @foreach($setupSteps as $i => $step)
            <div class="flex items-center gap-3 border-b border-charcoal-100 px-4 py-3 last:border-b-0">
                @if($step['done'])
                    <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-500 text-white"><x-icon name="check" class="h-5 w-5" stroke="2.2" /></span>
                @else
                    <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full border-2 border-charcoal-300 text-sm font-bold text-charcoal-500">{{ $i + 1 }}</span>
                @endif
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-bold {{ $step['done'] ? 'text-charcoal-400 line-through' : 'text-charcoal-900' }}">{{ $step['title'] }}</p>
                    @unless($step['done'])<p class="text-xs text-charcoal-500">{{ $step['desc'] }}</p>@endunless
                </div>
                @unless($step['done'])
                    <a href="{{ $step['url'] }}" class="btn-primary !min-h-[38px] shrink-0 !px-3 !py-1.5 !text-xs">{{ $step['action'] }}</a>
                @endunless
            </div>
        @endforeach
    </div>
</div>
@endif

{{-- Aksi utama --}}
<div class="mb-5">
    <div class="mb-2 flex items-center justify-between px-1"><h2 class="text-sm font-bold text-charcoal-900">Menu cepat</h2><span class="text-[11px] text-charcoal-400">Aktivitas utama</span></div>
    <div class="grid grid-cols-4 gap-2 sm:gap-3">
    <a href="{{ route('store.pos') }}" class="action-tile primary">
        <span class="action-tile-icon"><x-icon name="cart" /></span>
        <span><span class="action-tile-title block">Jual</span><span class="action-tile-desc block">Kasir</span></span>
    </a>
    <a href="{{ route('purchase-scans.create') }}" class="action-tile">
        <span class="action-tile-icon"><x-icon name="camera" /></span>
        <span><span class="action-tile-title block">Barang Masuk</span><span class="action-tile-desc block">Scan nota</span></span>
    </a>
    <a href="{{ route('store.customers.index', ['filter' => 'bon']) }}" class="action-tile">
        <span class="action-tile-icon"><x-icon name="wallet" /></span>
        <span><span class="action-tile-title block">Bayar Bon</span><span class="action-tile-desc block">Tagihan</span></span>
    </a>
    <a href="{{ route('stock.index') }}" class="action-tile">
        <span class="action-tile-icon"><x-icon name="box" /></span>
        <span><span class="action-tile-title block">Cek Stok</span><span class="action-tile-desc block">Gudang</span></span>
    </a>
    </div>
</div>

{{-- Perlu dikerjakan --}}
<div class="card mb-4 overflow-hidden">
    <div class="flex items-center justify-between border-b border-charcoal-100 px-4 py-3">
        <h2 class="text-sm font-bold text-charcoal-900">Perlu diperiksa</h2>
        <span class="text-[11px] text-charcoal-400">{{ count($tasks) }} tugas</span>
    </div>
    @forelse($tasks as $task)
        <a href="{{ $task['url'] }}" class="task-item">
            <span class="task-icon {{ $toneClass[$task['tone']][0] }}"><x-icon :name="$task['icon']" /></span>
            <span class="min-w-0 flex-1">
                <span class="task-title block">{{ $task['title'] }}</span>
                <span class="task-desc block">{{ $task['desc'] }}</span>
            </span>
            <span class="task-count {{ $toneClass[$task['tone']][1] }}">{{ $task['count'] }}</span>
            <x-icon name="chevron" class="h-4 w-4 shrink-0 text-charcoal-300" />
        </a>
    @empty
        <div class="flex items-center gap-3 px-4 py-6">
            <span class="task-icon bg-emerald-50 text-emerald-600"><x-icon name="check" /></span>
            <div>
                <p class="task-title">Tidak ada yang tertunda</p>
                <p class="task-desc">Nota, harga, stok, bon & hutang semuanya aman.</p>
            </div>
        </div>
    @endforelse
</div>

{{-- Angka hari ini --}}
<p class="section-label mb-2 mt-5">Detail hari ini</p>
<div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
    <div class="stat-tile">
        <p class="stat-tile-label"><span class="stat-tile-icon"><x-icon name="receipt" /></span>Penjualan</p>
        <p class="stat-tile-value">{{ $rp($summary['omzet']) }}</p>
        <p class="stat-tile-note">{{ $summary['transactions'] }} transaksi</p>
    </div>
    <div class="stat-tile">
        <p class="stat-tile-label"><span class="stat-tile-icon"><x-icon name="chart" /></span>Untung kotor</p>
        <p class="stat-tile-value text-emerald-700">{{ $rp($summary['gross_profit']) }}</p>
        <p class="stat-tile-note">Margin {{ $summary['margin_pct'] }}%</p>
    </div>
    <div class="stat-tile">
        <p class="stat-tile-label"><span class="stat-tile-icon"><x-icon name="in" /></span>Uang masuk</p>
        <p class="stat-tile-value">{{ $rp($summary['cash_in']) }}</p>
        <p class="stat-tile-note">Bon baru {{ $rp($summary['new_credit']) }}</p>
    </div>
    <div class="stat-tile">
        <p class="stat-tile-label"><span class="stat-tile-icon"><x-icon name="out" /></span>Uang keluar</p>
        <p class="stat-tile-value">{{ $rp($summary['cash_out']) }}</p>
        <p class="stat-tile-note">Bayar supplier &amp; refund</p>
    </div>
</div>

<p class="section-label mb-2 mt-6">Bulan ini</p>
<div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
    <div class="stat-tile"><p class="stat-tile-label">Penjualan</p><p class="stat-tile-value sm:!text-xl">{{ $rp($month['omzet']) }}</p></div>
    <div class="stat-tile"><p class="stat-tile-label">Untung kotor</p><p class="stat-tile-value sm:!text-xl text-emerald-700">{{ $rp($month['gross_profit']) }}</p></div>
    <a href="{{ route('store.customers.index', ['filter' => 'bon']) }}" class="stat-tile border-amber-200 bg-amber-50"><p class="stat-tile-label !text-amber-800">Bon pelanggan</p><p class="stat-tile-value sm:!text-xl !text-amber-900">{{ $rp($month['receivable_total']) }}</p><p class="stat-tile-note !text-amber-700">Uang yang belum ditagih →</p></a>
    <a href="{{ route('payables.index') }}" class="stat-tile border-rose-200 bg-rose-50"><p class="stat-tile-label !text-rose-800">Hutang ke supplier</p><p class="stat-tile-value sm:!text-xl !text-rose-900">{{ $rp($month['payable_total']) }}</p><p class="stat-tile-note !text-rose-700">Yang harus dibayar →</p></a>
</div>

<div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
    <div class="card overflow-hidden">
        <div class="flex items-center justify-between border-b border-charcoal-100 px-4 py-3">
            <h2 class="text-[15px] font-bold text-charcoal-900">Paling laku bulan ini</h2>
            <a href="{{ route('store.reports.top') }}" class="text-sm font-bold text-brand-600">Semua</a>
        </div>
        @forelse($top as $i => $t)
            <div class="flex items-center gap-3 border-b border-charcoal-100 px-4 py-3 last:border-b-0">
                <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $i === 0 ? 'bg-brand-500 text-white' : 'bg-charcoal-100 text-charcoal-600' }} text-sm font-extrabold">{{ $i + 1 }}</span>
                <span class="min-w-0 flex-1 truncate text-sm font-semibold text-charcoal-900">{{ $t['name'] }}</span>
                <span class="text-right text-sm"><b>{{ $qty($t['qty_base']) }}</b><span class="block text-[11px] text-emerald-700">untung {{ $rp($t['profit']) }}</span></span>
            </div>
        @empty
            <p class="px-4 py-6 text-center text-sm text-charcoal-500">Belum ada penjualan bulan ini.</p>
        @endforelse
    </div>

    <div class="card overflow-hidden">
        <div class="flex items-center justify-between border-b border-charcoal-100 px-4 py-3">
            <h2 class="text-[15px] font-bold text-charcoal-900">Penjualan terakhir</h2>
            <a href="{{ route('store.sales.index') }}" class="text-sm font-bold text-brand-600">Semua</a>
        </div>
        @forelse($recentSales as $s)
            <a href="{{ route('store.sales.show', $s) }}" class="flex items-center gap-3 border-b border-charcoal-100 px-4 py-3 last:border-b-0 hover:bg-charcoal-50">
                <span class="min-w-0 flex-1">
                    <span class="block truncate text-sm font-semibold text-charcoal-900">{{ $s->customer->name ?? 'Pembeli umum' }}</span>
                    <span class="block text-xs text-charcoal-500">{{ $s->sale_no }} · {{ $s->sale_date->format('H:i') }}</span>
                </span>
                <span class="text-right">
                    <span class="block text-sm font-bold">{{ $rp($s->netTotal()) }}</span>
                    <span class="badge {{ $s->payment_status === 'paid' ? 'badge-success' : 'badge-warning' }}">{{ $s->paymentStatusLabel() }}</span>
                </span>
            </a>
        @empty
            <p class="px-4 py-6 text-center text-sm text-charcoal-500">Belum ada penjualan.</p>
        @endforelse
    </div>
</div>

{{-- Alur kerja --}}
<div class="mt-6">
    <x-help title="Alur kerja toko dalam 3 langkah">
        <li><b>Barang datang dari supplier</b> → tekan <b>Barang Masuk</b>, foto notanya. Cek angka, simpan. Stok otomatis bertambah dan hutang supplier tercatat.</li>
        <li><b>Ada pembeli</b> → tekan <b>Jual</b>. Pilih barang, terima uang, cetak / kirim struk lewat WhatsApp. Pembeli belum bayar? pilih <b>Bon</b>.</li>
        <li><b>Sore/malam</b> → buka <b>Beranda</b> ini: lihat untung hari ini dan selesaikan daftar <b>Perlu dikerjakan</b>.</li>
    </x-help>
</div>
</div>

<style>
    .store-dashboard .action-tile {
        min-height: 102px;
        align-items: center;
        justify-content: flex-start;
        gap: .55rem;
        padding: .75rem .4rem;
        text-align: center;
        border-radius: 1rem;
        box-shadow: none;
    }
    .store-dashboard .action-tile.primary { border-color: rgb(254 215 170); background: white; color: inherit; }
    .store-dashboard .action-tile-icon,
    .store-dashboard .action-tile.primary .action-tile-icon { width: 2.5rem; height: 2.5rem; border-radius: .8rem; background: rgb(255 247 237); color: rgb(234 88 12); }
    .store-dashboard .action-tile-icon svg { width: 1.25rem; height: 1.25rem; }
    .store-dashboard .action-tile-title,
    .store-dashboard .action-tile.primary .action-tile-title { color: rgb(31 37 47); font-size: .75rem; line-height: 1rem; }
    .store-dashboard .action-tile-desc,
    .store-dashboard .action-tile.primary .action-tile-desc { color: rgb(132 145 162); font-size: .65rem; line-height: .9rem; }
    .store-dashboard .stat-tile { padding: .85rem; box-shadow: none; }
    .store-dashboard .stat-tile-value { font-size: 1rem; line-height: 1.4rem; }
    .store-dashboard .task-item { min-height: 58px; padding-top: .65rem; padding-bottom: .65rem; }
    .store-dashboard .task-icon { width: 2.25rem; height: 2.25rem; border-radius: .75rem; }
    @media (max-width: 380px) {
        .store-dashboard .action-tile-title { font-size: .68rem; }
        .store-dashboard .action-tile { padding-left: .2rem; padding-right: .2rem; }
    }
</style>
@endsection
