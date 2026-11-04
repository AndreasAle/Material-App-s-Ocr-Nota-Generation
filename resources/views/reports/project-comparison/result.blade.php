@extends('layouts.app')

@section('title', 'Hasil Perbandingan Proyek')

@section('content')

@php
    /* ------------------------------------------------------------------ */
    /* Color scheme per project                                              */
    /* ------------------------------------------------------------------ */
    $colorSchemes = [
        [
            'bg'       => 'bg-indigo-600',
            'bgHover'  => 'hover:bg-indigo-700',
            'light'    => 'bg-indigo-50',
            'lighter'  => 'bg-indigo-100',
            'text'     => 'text-indigo-700',
            'textDark' => 'text-indigo-900',
            'border'   => 'border-indigo-200',
            'ring'     => 'ring-indigo-400',
            'dot'      => 'bg-indigo-500',
            'bar'      => 'bg-indigo-500',
            'hex'      => '#4f46e5',
            'hexLight' => 'rgba(99,102,241,0.15)',
            'label'    => 'Indigo',
        ],
        [
            'bg'       => 'bg-emerald-600',
            'bgHover'  => 'hover:bg-emerald-700',
            'light'    => 'bg-emerald-50',
            'lighter'  => 'bg-emerald-100',
            'text'     => 'text-emerald-700',
            'textDark' => 'text-emerald-900',
            'border'   => 'border-emerald-200',
            'ring'     => 'ring-emerald-400',
            'dot'      => 'bg-emerald-500',
            'bar'      => 'bg-emerald-500',
            'hex'      => '#059669',
            'hexLight' => 'rgba(16,185,129,0.15)',
            'label'    => 'Emerald',
        ],
        [
            'bg'       => 'bg-amber-500',
            'bgHover'  => 'hover:bg-amber-600',
            'light'    => 'bg-amber-50',
            'lighter'  => 'bg-amber-100',
            'text'     => 'text-amber-700',
            'textDark' => 'text-amber-900',
            'border'   => 'border-amber-200',
            'ring'     => 'ring-amber-400',
            'dot'      => 'bg-amber-500',
            'bar'      => 'bg-amber-500',
            'hex'      => '#d97706',
            'hexLight' => 'rgba(245,158,11,0.15)',
            'label'    => 'Amber',
        ],
        [
            'bg'       => 'bg-rose-600',
            'bgHover'  => 'hover:bg-rose-700',
            'light'    => 'bg-rose-50',
            'lighter'  => 'bg-rose-100',
            'text'     => 'text-rose-700',
            'textDark' => 'text-rose-900',
            'border'   => 'border-rose-200',
            'ring'     => 'ring-rose-400',
            'dot'      => 'bg-rose-500',
            'bar'      => 'bg-rose-500',
            'hex'      => '#e11d48',
            'hexLight' => 'rgba(225,29,72,0.15)',
            'label'    => 'Rose',
        ],
    ];

    /* ------------------------------------------------------------------ */
    /* Pre-compute "winner" per metric                                       */
    /* ------------------------------------------------------------------ */
    $revenues  = collect($data)->pluck('revenue');
    $profits   = collect($data)->pluck('profit');
    $margins   = collect($data)->pluck('margin');
    $spendings = collect($data)->pluck('spending');
    $utils     = collect($data)->pluck('budget_util');

    $bestRevIdx    = $revenues->search($revenues->max());
    $bestProfIdx   = $profits->search($profits->max());
    $bestMargIdx   = $margins->search($margins->max());
    $bestSpendIdx  = $spendings->search($spendings->min());   // lowest spend = most efficient
    $bestUtilIdx   = $utils->search($utils->filter(fn($v) => $v > 0)->min() ?? null); // lowest util

    $projectNames  = collect($data)->pluck('project')->pluck('name');
@endphp

{{-- ====================================================================== --}}
{{-- PAGE HEADER                                                              --}}
{{-- ====================================================================== --}}
<div class="mb-6">
    <nav class="flex items-center gap-1.5 text-xs text-slate-400 mb-3">
        <a href="{{ route('project-comparison.index') }}" class="hover:text-indigo-600 transition-colors font-medium">
            Perbandingan Proyek
        </a>
        <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
        </svg>
        <span class="text-slate-600 font-semibold">Hasil Perbandingan</span>
    </nav>

    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div class="min-w-0">
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 leading-tight">
                Hasil Perbandingan Proyek
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Membandingkan
                <span class="font-semibold text-slate-700">{{ count($data) }} proyek</span>
                — {{ $projectNames->join(', ', ' & ') }}
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2 shrink-0">
            <button onclick="window.print()"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm font-medium text-slate-600 shadow-sm hover:bg-slate-50 transition-all print:hidden">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z"/>
                </svg>
                Cetak
            </button>
            <a href="{{ route('project-comparison.index') }}"
               class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm font-medium text-slate-600 shadow-sm hover:bg-slate-50 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/>
                </svg>
                Ubah Pilihan
            </a>
        </div>
    </div>
</div>

{{-- ====================================================================== --}}
{{-- PROJECT HEADER CARDS                                                     --}}
{{-- ====================================================================== --}}
<div class="grid grid-cols-{{ count($data) }} gap-4 mb-6">
    @foreach ($data as $i => $d)
    @php $c = $colorSchemes[$i]; @endphp
    <div class="rounded-2xl border {{ $c['border'] }} {{ $c['light'] }} p-5 relative overflow-hidden">
        {{-- Background decoration --}}
        <div class="absolute -right-4 -top-4 w-20 h-20 rounded-full {{ $c['lighter'] }} opacity-60"></div>

        <div class="relative">
            <div class="flex items-center gap-2 mb-3">
                <div class="w-8 h-8 rounded-xl {{ $c['bg'] }} flex items-center justify-center text-white text-xs font-extrabold shrink-0">
                    {{ strtoupper(mb_substr($d['project']->name, 0, 2)) }}
                </div>
                <span class="text-xs font-bold uppercase tracking-wider {{ $c['text'] }}">
                    Proyek {{ $i + 1 }}
                </span>
                @if ($i === $bestProfIdx && $d['profit'] > 0)
                <span class="ml-auto text-sm" title="Profit Tertinggi">🏆</span>
                @endif
            </div>
            <p class="font-extrabold text-slate-900 text-sm leading-snug mb-0.5">{{ $d['project']->name }}</p>
            @if ($d['project']->code)
            <p class="text-xs font-medium {{ $c['text'] }} opacity-70">{{ $d['project']->code }}</p>
            @endif

            {{-- Mini stats --}}
            <div class="mt-3 pt-3 border-t {{ $c['border'] }} grid grid-cols-2 gap-2">
                <div>
                    <p class="text-xs {{ $c['text'] }} opacity-70">Pengeluaran</p>
                    <p class="text-xs font-bold text-slate-800 truncate">
                        Rp {{ number_format($d['spending'] / 1_000_000, 1, ',', '.') }}jt
                    </p>
                </div>
                <div>
                    <p class="text-xs {{ $c['text'] }} opacity-70">Margin</p>
                    <p class="text-xs font-bold {{ $d['margin'] >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                        {{ $d['margin'] >= 0 ? '+' : '' }}{{ $d['margin'] }}%
                    </p>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- ====================================================================== --}}
{{-- COMPARISON TABLE                                                         --}}
{{-- ====================================================================== --}}
<div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden mb-6">
    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
        <div>
            <h3 class="text-sm font-bold text-slate-800">Tabel Perbandingan Detail</h3>
            <p class="text-xs text-slate-400 mt-0.5">Metrik finansial & operasional semua proyek</p>
        </div>
        <div class="flex items-center gap-3 text-xs text-slate-500">
            <span class="inline-flex items-center gap-1">
                <span class="text-base">🏆</span> Terbaik
            </span>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="bg-slate-50/80 border-b border-slate-200">
                    <th class="px-5 py-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wide w-52">Metrik</th>
                    @foreach ($data as $i => $d)
                    @php $c = $colorSchemes[$i]; @endphp
                    <th class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <span class="w-2.5 h-2.5 rounded-full {{ $c['dot'] }} shrink-0"></span>
                            <span class="text-xs font-bold {{ $c['text'] }} truncate max-w-[120px]">
                                {{ $d['project']->name }}
                            </span>
                        </div>
                    </th>
                    @endforeach
                </tr>
            </thead>

            <tbody>
                {{-- ======================================================= --}}
                {{-- SECTION: Anggaran & Pengeluaran                          --}}
                {{-- ======================================================= --}}
                <tr class="border-b border-slate-200 bg-slate-50">
                    <td colspan="{{ count($data) + 1 }}" class="px-5 py-2">
                        <span class="text-xs font-extrabold text-slate-500 uppercase tracking-widest">💰 Anggaran & Pengeluaran</span>
                    </td>
                </tr>

                {{-- Budget RAB --}}
                <tr class="border-b border-slate-100 hover:bg-slate-50/40 transition-colors">
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-2">
                            <div class="w-1 h-5 rounded-full bg-slate-300"></div>
                            <div>
                                <p class="text-sm font-semibold text-slate-700">Budget RAB</p>
                                <p class="text-xs text-slate-400">Total anggaran yang direncanakan</p>
                            </div>
                        </div>
                    </td>
                    @foreach ($data as $i => $d)
                    @php $c = $colorSchemes[$i]; @endphp
                    <td class="px-4 py-3.5 text-right">
                        @if ($d['rab_budget'] > 0)
                        <span class="font-bold text-slate-800 tabular-nums">
                            Rp {{ number_format($d['rab_budget'], 0, ',', '.') }}
                        </span>
                        @else
                        <span class="text-slate-300 text-xs font-medium">Tidak ada RAB</span>
                        @endif
                    </td>
                    @endforeach
                </tr>

                {{-- Total Pengeluaran --}}
                <tr class="border-b border-slate-100 hover:bg-slate-50/40 transition-colors">
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-2">
                            <div class="w-1 h-5 rounded-full bg-rose-400"></div>
                            <div>
                                <p class="text-sm font-semibold text-slate-700">Total Pengeluaran</p>
                                <p class="text-xs text-slate-400">Semua transaksi pembelian</p>
                            </div>
                        </div>
                    </td>
                    @foreach ($data as $i => $d)
                    <td class="px-4 py-3.5 text-right">
                        <div class="flex flex-col items-end gap-1">
                            <span class="font-bold text-rose-700 tabular-nums">
                                Rp {{ number_format($d['spending'], 0, ',', '.') }}
                            </span>
                            @if ($i === $bestSpendIdx)
                            <span class="text-xs bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full px-2 py-0.5 font-semibold">
                                🏆 Efisiensi Terbaik
                            </span>
                            @endif
                        </div>
                    </td>
                    @endforeach
                </tr>

                {{-- Utilisasi Budget --}}
                <tr class="border-b border-slate-100 hover:bg-slate-50/40 transition-colors">
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-2">
                            <div class="w-1 h-5 rounded-full bg-amber-400"></div>
                            <div>
                                <p class="text-sm font-semibold text-slate-700">Utilisasi Anggaran</p>
                                <p class="text-xs text-slate-400">Pengeluaran vs budget RAB</p>
                            </div>
                        </div>
                    </td>
                    @foreach ($data as $i => $d)
                    @php
                        $pct = $d['budget_util'];
                        $pctText = $pct > 90 ? 'text-rose-700' : ($pct > 70 ? 'text-amber-700' : 'text-emerald-700');
                        $pctBar  = $pct > 90 ? 'bg-rose-500'  : ($pct > 70 ? 'bg-amber-400'  : 'bg-emerald-500');
                    @endphp
                    <td class="px-4 py-3.5">
                        <div class="flex flex-col items-end gap-1.5">
                            <span class="font-extrabold {{ $pctText }} tabular-nums text-base">
                                @if ($d['rab_budget'] > 0)
                                    {{ $pct }}%
                                @else
                                    <span class="text-slate-300 text-sm font-medium">—</span>
                                @endif
                            </span>
                            @if ($d['rab_budget'] > 0)
                            <div class="w-full max-w-[100px] bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                <div class="{{ $pctBar }} h-1.5 rounded-full transition-all"
                                     style="width: {{ min($pct, 100) }}%"></div>
                            </div>
                            @endif
                            @if ($i === $bestUtilIdx && $d['rab_budget'] > 0)
                            <span class="text-xs bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full px-2 py-0.5 font-semibold">
                                🏆 Terefisiensi
                            </span>
                            @endif
                        </div>
                    </td>
                    @endforeach
                </tr>

                {{-- ======================================================= --}}
                {{-- SECTION: Revenue & Profit                                 --}}
                {{-- ======================================================= --}}
                <tr class="border-b border-slate-200 bg-slate-50">
                    <td colspan="{{ count($data) + 1 }}" class="px-5 py-2">
                        <span class="text-xs font-extrabold text-slate-500 uppercase tracking-widest">📈 Revenue & Profit</span>
                    </td>
                </tr>

                {{-- Revenue Diterima --}}
                <tr class="border-b border-slate-100 hover:bg-slate-50/40 transition-colors">
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-2">
                            <div class="w-1 h-5 rounded-full bg-emerald-500"></div>
                            <div>
                                <p class="text-sm font-semibold text-slate-700">Revenue Diterima</p>
                                <p class="text-xs text-slate-400">Pembayaran invoice yang sudah masuk</p>
                            </div>
                        </div>
                    </td>
                    @foreach ($data as $i => $d)
                    <td class="px-4 py-3.5 text-right">
                        <div class="flex flex-col items-end gap-1">
                            <span class="font-bold text-emerald-700 tabular-nums">
                                Rp {{ number_format($d['revenue'], 0, ',', '.') }}
                            </span>
                            @if ($i === $bestRevIdx && $d['revenue'] > 0)
                            <span class="text-xs bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full px-2 py-0.5 font-semibold">
                                🏆 Tertinggi
                            </span>
                            @endif
                        </div>
                    </td>
                    @endforeach
                </tr>

                {{-- Total Tagihan --}}
                <tr class="border-b border-slate-100 hover:bg-slate-50/40 transition-colors">
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-2">
                            <div class="w-1 h-5 rounded-full bg-indigo-400"></div>
                            <div>
                                <p class="text-sm font-semibold text-slate-700">Total Tagihan Invoice</p>
                                <p class="text-xs text-slate-400">Grand total dari semua invoice</p>
                            </div>
                        </div>
                    </td>
                    @foreach ($data as $d)
                    <td class="px-4 py-3.5 text-right">
                        <span class="font-bold text-indigo-700 tabular-nums">
                            Rp {{ number_format($d['billed'], 0, ',', '.') }}
                        </span>
                    </td>
                    @endforeach
                </tr>

                {{-- Laba / Rugi --}}
                <tr class="border-b border-slate-100 hover:bg-slate-50/40 transition-colors">
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-2">
                            <div class="w-1 h-5 rounded-full {{ collect($data)->max('profit') >= 0 ? 'bg-emerald-500' : 'bg-rose-500' }}"></div>
                            <div>
                                <p class="text-sm font-semibold text-slate-700">Laba / Rugi</p>
                                <p class="text-xs text-slate-400">Revenue dikurangi pengeluaran</p>
                            </div>
                        </div>
                    </td>
                    @foreach ($data as $i => $d)
                    <td class="px-4 py-3.5 text-right">
                        <div class="flex flex-col items-end gap-1">
                            <span class="font-extrabold text-base tabular-nums {{ $d['profit'] >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                                {{ $d['profit'] >= 0 ? '+' : '' }}Rp {{ number_format($d['profit'], 0, ',', '.') }}
                            </span>
                            @if ($i === $bestProfIdx && $d['profit'] > 0)
                            <span class="text-xs bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full px-2 py-0.5 font-semibold">
                                🏆 Profit Terbaik
                            </span>
                            @endif
                        </div>
                    </td>
                    @endforeach
                </tr>

                {{-- Margin --}}
                <tr class="border-b border-slate-100 hover:bg-slate-50/40 transition-colors">
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-2">
                            <div class="w-1 h-5 rounded-full bg-violet-400"></div>
                            <div>
                                <p class="text-sm font-semibold text-slate-700">Margin</p>
                                <p class="text-xs text-slate-400">Profit / Revenue × 100%</p>
                            </div>
                        </div>
                    </td>
                    @foreach ($data as $i => $d)
                    <td class="px-4 py-3.5 text-right">
                        <div class="flex flex-col items-end gap-1">
                            <span class="font-extrabold text-base tabular-nums {{ $d['margin'] >= 20 ? 'text-emerald-700' : ($d['margin'] >= 0 ? 'text-slate-700' : 'text-rose-700') }}">
                                {{ $d['margin'] >= 0 ? '+' : '' }}{{ $d['margin'] }}%
                            </span>
                            @if ($i === $bestMargIdx && $d['margin'] > 0)
                            <span class="text-xs bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full px-2 py-0.5 font-semibold">
                                🏆 Margin Terbaik
                            </span>
                            @endif
                        </div>
                    </td>
                    @endforeach
                </tr>

                {{-- ======================================================= --}}
                {{-- SECTION: Statistik Operasional                            --}}
                {{-- ======================================================= --}}
                <tr class="border-b border-slate-200 bg-slate-50">
                    <td colspan="{{ count($data) + 1 }}" class="px-5 py-2">
                        <span class="text-xs font-extrabold text-slate-500 uppercase tracking-widest">📋 Statistik Operasional</span>
                    </td>
                </tr>

                @php
                    $opMetrics = [
                        ['key' => 'tx_count',       'label' => 'Jumlah Transaksi',  'sub' => 'Purchase transactions',    'dot' => 'bg-indigo-400',  'winner' => false],
                        ['key' => 'material_count',  'label' => 'Material Unik',     'sub' => 'Jenis material berbeda',   'dot' => 'bg-teal-400',    'winner' => false],
                        ['key' => 'rab_count',       'label' => 'Jumlah RAB',        'sub' => 'Rencana Anggaran Biaya',   'dot' => 'bg-amber-400',   'winner' => false],
                        ['key' => 'po_count',        'label' => 'Purchase Order',    'sub' => 'PO yang dibuat',           'dot' => 'bg-violet-400',  'winner' => false],
                    ];
                @endphp

                @foreach ($opMetrics as $metric)
                <tr class="border-b border-slate-100 hover:bg-slate-50/40 transition-colors {{ !$loop->last ? '' : '' }}">
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-2">
                            <div class="w-1 h-5 rounded-full {{ $metric['dot'] }}"></div>
                            <div>
                                <p class="text-sm font-semibold text-slate-700">{{ $metric['label'] }}</p>
                                <p class="text-xs text-slate-400">{{ $metric['sub'] }}</p>
                            </div>
                        </div>
                    </td>
                    @php
                        $vals    = collect($data)->pluck($metric['key']);
                        $maxVal  = $vals->max();
                    @endphp
                    @foreach ($data as $i => $d)
                    @php $c = $colorSchemes[$i]; @endphp
                    <td class="px-4 py-3.5 text-right">
                        <div class="flex flex-col items-end gap-1.5">
                            <span class="font-extrabold text-xl text-slate-800 tabular-nums">
                                {{ number_format($d[$metric['key']], 0, ',', '.') }}
                            </span>
                            {{-- Mini bar relative to max --}}
                            @if ($maxVal > 0)
                            <div class="w-full max-w-[80px] bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                <div class="{{ $c['bar'] }} h-1.5 rounded-full transition-all"
                                     style="width: {{ $maxVal > 0 ? round(($d[$metric['key']] / $maxVal) * 100) : 0 }}%"></div>
                            </div>
                            @endif
                        </div>
                    </td>
                    @endforeach
                </tr>
                @endforeach

            </tbody>
        </table>
    </div>
</div>

{{-- ====================================================================== --}}
{{-- PERFORMANCE INSIGHTS                                                     --}}
{{-- ====================================================================== --}}
@php
    $insights = [];
    $bestProfProject = $data[$bestProfIdx] ?? null;
    $bestRevProject  = $data[$bestRevIdx] ?? null;
    $bestSpendProject= $data[$bestSpendIdx] ?? null;
    $bestMargProject = $data[$bestMargIdx] ?? null;

    if ($bestProfProject && $bestProfProject['profit'] > 0) {
        $insights[] = [
            'icon' => '🏆',
            'color' => 'bg-emerald-50 border-emerald-200 text-emerald-800',
            'text' => '<strong>' . e($bestProfProject['project']->name) . '</strong> mencatatkan profit tertinggi sebesar <strong>Rp ' . number_format($bestProfProject['profit'], 0, ',', '.') . '</strong> dengan margin <strong>' . $bestProfProject['margin'] . '%</strong>.',
        ];
    }
    if ($bestMargProject && $bestMargProject['margin'] > 0) {
        $insights[] = [
            'icon' => '📊',
            'color' => 'bg-indigo-50 border-indigo-200 text-indigo-800',
            'text' => '<strong>' . e($bestMargProject['project']->name) . '</strong> memiliki margin terbaik di antara semua proyek yang dibandingkan (<strong>' . $bestMargProject['margin'] . '%</strong>).',
        ];
    }
    if ($bestRevProject && $bestRevProject['revenue'] > 0) {
        $insights[] = [
            'icon' => '💵',
            'color' => 'bg-teal-50 border-teal-200 text-teal-800',
            'text' => 'Revenue tertinggi diraih oleh <strong>' . e($bestRevProject['project']->name) . '</strong> sebesar <strong>Rp ' . number_format($bestRevProject['revenue'], 0, ',', '.') . '</strong>.',
        ];
    }
    $overBudget = collect($data)->filter(fn($d) => $d['budget_util'] > 100 && $d['rab_budget'] > 0);
    if ($overBudget->isNotEmpty()) {
        $names = $overBudget->map(fn($d) => '<strong>' . e($d['project']->name) . '</strong>')->join(', ');
        $insights[] = [
            'icon' => '⚠️',
            'color' => 'bg-rose-50 border-rose-200 text-rose-800',
            'text' => $names . ' melebihi batas anggaran RAB. Tinjau kembali pengeluaran untuk proyek tersebut.',
        ];
    }
@endphp

@if (count($insights) > 0)
<div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden mb-6">
    <div class="border-b border-slate-100 px-5 py-4">
        <h3 class="text-sm font-bold text-slate-800">Insight Otomatis</h3>
        <p class="text-xs text-slate-400 mt-0.5">Rangkuman temuan dari perbandingan proyek ini</p>
    </div>
    <div class="p-5 space-y-3">
        @foreach ($insights as $ins)
        <div class="flex items-start gap-3 rounded-xl border {{ $ins['color'] }} p-3.5">
            <span class="text-lg shrink-0 mt-0.5">{{ $ins['icon'] }}</span>
            <p class="text-sm leading-relaxed">{!! $ins['text'] !!}</p>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ====================================================================== --}}
{{-- CHART SECTION                                                            --}}
{{-- ====================================================================== --}}
<div class="grid grid-cols-1 lg:grid-cols-5 gap-5 mb-6">

    {{-- Main Bar Chart (3/5) --}}
    <div class="lg:col-span-3 rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Perbandingan Finansial</h3>
                <p class="text-xs text-slate-400 mt-0.5">Budget RAB · Pengeluaran · Revenue</p>
            </div>
        </div>
        <div class="p-5">
            <div style="height: 260px;">
                <canvas id="compChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Margin & Utilization (2/5) --}}
    <div class="lg:col-span-2 rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Margin & Utilisasi</h3>
                <p class="text-xs text-slate-400 mt-0.5">Per proyek</p>
            </div>
        </div>
        <div class="p-5 space-y-4">
            @foreach ($data as $i => $d)
            @php $c = $colorSchemes[$i]; @endphp
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full {{ $c['dot'] }} shrink-0"></span>
                        <span class="text-xs font-semibold text-slate-700 truncate max-w-[120px]">{{ $d['project']->name }}</span>
                    </div>
                    <span class="text-xs font-extrabold {{ $d['margin'] >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                        {{ $d['margin'] >= 0 ? '+' : '' }}{{ $d['margin'] }}%
                    </span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden mb-0.5">
                    @php
                        $marginWidth = max(0, min(100, abs($d['margin'])));
                        $marginBar   = $d['margin'] >= 0 ? $c['bar'] : 'bg-rose-500';
                    @endphp
                    <div class="{{ $marginBar }} h-2.5 rounded-full transition-all"
                         style="width: {{ $marginWidth }}%"></div>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-slate-400">Margin</span>
                    @if ($d['rab_budget'] > 0)
                    <span class="text-xs text-slate-400">
                        Utilisasi: <span class="font-semibold {{ $d['budget_util'] > 90 ? 'text-rose-600' : ($d['budget_util'] > 70 ? 'text-amber-600' : 'text-emerald-600') }}">{{ $d['budget_util'] }}%</span>
                    </span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ====================================================================== --}}
{{-- OPERATIONAL STATS GRID                                                   --}}
{{-- ====================================================================== --}}
<div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
    <div class="border-b border-slate-100 px-5 py-4">
        <h3 class="text-sm font-bold text-slate-800">Ringkasan Operasional per Proyek</h3>
        <p class="text-xs text-slate-400 mt-0.5">Aktivitas dan volume kerja masing-masing proyek</p>
    </div>
    <div class="grid grid-cols-{{ count($data) }} divide-x divide-slate-100">
        @foreach ($data as $i => $d)
        @php $c = $colorSchemes[$i]; @endphp
        <div class="p-5">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-3 h-3 rounded-full {{ $c['dot'] }}"></div>
                <span class="text-xs font-bold {{ $c['text'] }} uppercase tracking-wide truncate">{{ $d['project']->name }}</span>
            </div>
            <div class="space-y-3">
                @foreach ([
                    [$d['tx_count'],       'Transaksi',    '📦'],
                    [$d['material_count'], 'Material',     '🏗️'],
                    [$d['rab_count'],      'RAB',          '📋'],
                    [$d['po_count'],       'PO',           '📄'],
                ] as [$val, $label, $icon])
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-1.5">
                        <span class="text-sm">{{ $icon }}</span>
                        <span class="text-xs text-slate-500">{{ $label }}</span>
                    </div>
                    <span class="text-sm font-extrabold text-slate-800 tabular-nums">{{ number_format($val, 0, ',', '.') }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    const labels   = @json(collect($data)->pluck('project')->pluck('name'));
    const spending = @json(collect($data)->pluck('spending'));
    const revenue  = @json(collect($data)->pluck('revenue'));
    const budget   = @json(collect($data)->pluck('rab_budget'));

    new Chart(document.getElementById('compChart'), {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    label: 'Budget RAB',
                    data: budget,
                    backgroundColor: 'rgba(99,102,241,0.2)',
                    borderColor: 'rgba(99,102,241,0.8)',
                    borderWidth: 2,
                    borderRadius: 8,
                    borderSkipped: false,
                },
                {
                    label: 'Pengeluaran',
                    data: spending,
                    backgroundColor: 'rgba(244,63,94,0.75)',
                    borderColor: 'rgba(244,63,94,0)',
                    borderWidth: 0,
                    borderRadius: 8,
                    borderSkipped: false,
                },
                {
                    label: 'Revenue',
                    data: revenue,
                    backgroundColor: 'rgba(16,185,129,0.75)',
                    borderColor: 'rgba(16,185,129,0)',
                    borderWidth: 0,
                    borderRadius: 8,
                    borderSkipped: false,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        pointStyle: 'circle',
                        padding: 16,
                        font: { size: 11 },
                        color: '#64748b',
                    },
                },
                tooltip: {
                    backgroundColor: '#0f172a',
                    titleColor: '#94a3b8',
                    bodyColor: '#f1f5f9',
                    borderColor: 'rgba(99,102,241,0.3)',
                    borderWidth: 1,
                    padding: 12,
                    cornerRadius: 10,
                    callbacks: {
                        label: c => '  ' + c.dataset.label + ': Rp ' + c.parsed.y.toLocaleString('id-ID'),
                    },
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: v => v >= 1_000_000
                            ? 'Rp ' + (v / 1_000_000).toFixed(0) + 'jt'
                            : 'Rp ' + (v / 1_000).toFixed(0) + 'rb',
                        font: { size: 11 },
                        color: '#94a3b8',
                        maxTicksLimit: 6,
                    },
                    grid: { color: 'rgba(0,0,0,0.04)' },
                    border: { dash: [4, 4] },
                },
                x: {
                    ticks: { font: { size: 11 }, color: '#94a3b8' },
                    grid: { display: false },
                },
            },
        },
    });
})();
</script>
@endpush

@endsection
