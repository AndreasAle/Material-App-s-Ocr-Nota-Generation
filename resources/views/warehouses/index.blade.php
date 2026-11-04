@extends('layouts.app')

@section('content')
@php
    $totalWarehouses = $warehouses->count();
    $activeCount     = $warehouses->where('is_active', true)->count();
    $inactiveCount   = $warehouses->where('is_active', false)->count();
    $totalMovements  = $stats['movements'];

    $colorSchemes = [
        ['icon_bg'=>'bg-indigo-100','icon_text'=>'text-indigo-600','accent'=>'bg-indigo-600','badge_bg'=>'bg-indigo-50','badge_text'=>'text-indigo-700','badge_border'=>'border-indigo-200'],
        ['icon_bg'=>'bg-emerald-100','icon_text'=>'text-emerald-600','accent'=>'bg-emerald-500','badge_bg'=>'bg-emerald-50','badge_text'=>'text-emerald-700','badge_border'=>'border-emerald-200'],
        ['icon_bg'=>'bg-violet-100','icon_text'=>'text-violet-600','accent'=>'bg-violet-600','badge_bg'=>'bg-violet-50','badge_text'=>'text-violet-700','badge_border'=>'border-violet-200'],
        ['icon_bg'=>'bg-amber-100','icon_text'=>'text-amber-600','accent'=>'bg-amber-500','badge_bg'=>'bg-amber-50','badge_text'=>'text-amber-700','badge_border'=>'border-amber-200'],
        ['icon_bg'=>'bg-cyan-100','icon_text'=>'text-cyan-600','accent'=>'bg-cyan-500','badge_bg'=>'bg-cyan-50','badge_text'=>'text-cyan-700','badge_border'=>'border-cyan-200'],
        ['icon_bg'=>'bg-rose-100','icon_text'=>'text-rose-600','accent'=>'bg-rose-500','badge_bg'=>'bg-rose-50','badge_text'=>'text-rose-700','badge_border'=>'border-rose-200'],
        ['icon_bg'=>'bg-teal-100','icon_text'=>'text-teal-600','accent'=>'bg-teal-500','badge_bg'=>'bg-teal-50','badge_text'=>'text-teal-700','badge_border'=>'border-teal-200'],
        ['icon_bg'=>'bg-orange-100','icon_text'=>'text-orange-600','accent'=>'bg-orange-500','badge_bg'=>'bg-orange-50','badge_text'=>'text-orange-700','badge_border'=>'border-orange-200'],
    ];
@endphp

{{-- Breadcrumb --}}
<nav class="flex items-center gap-1.5 text-xs text-slate-400 mb-4">
    <a href="{{ route('dashboard') }}" class="hover:text-slate-600 transition-colors">Dashboard</a>
    <svg class="h-3 w-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-slate-700 font-semibold">Multi-Gudang</span>
</nav>

{{-- Professional Header --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div class="flex items-center gap-3">
        <div class="flex-shrink-0 w-11 h-11 rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center shadow-md">
            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72l1.189-1.19A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72M6.75 18h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .414.336.75.75.75z"/>
            </svg>
        </div>
        <div>
            <h1 class="text-xl font-bold text-slate-800 leading-tight">Multi-Gudang</h1>
            <p class="text-sm text-slate-500">Kelola {{ $totalWarehouses }} gudang penyimpanan material</p>
        </div>
    </div>
    @if(auth()->user()->role === 'admin')
    <a href="{{ route('warehouses.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 active:bg-indigo-800 transition-all shadow-sm hover:shadow-md">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
        </svg>
        Tambah Gudang
    </a>
    @endif
</div>

{{-- Flash Messages --}}

{{-- Summary Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 flex items-center gap-3 hover:shadow-md transition-shadow">
        <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center flex-shrink-0">
            <svg class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-black text-slate-800 tabular-nums leading-none">{{ $totalWarehouses }}</p>
            <p class="text-xs text-slate-400 mt-0.5">Total Gudang</p>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 flex items-center gap-3 hover:shadow-md transition-shadow">
        <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center flex-shrink-0">
            <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-black text-emerald-700 tabular-nums leading-none">{{ $activeCount }}</p>
            <p class="text-xs text-slate-400 mt-0.5">Gudang Aktif</p>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 flex items-center gap-3 hover:shadow-md transition-shadow">
        <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center flex-shrink-0">
            <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-black text-slate-500 tabular-nums leading-none">{{ $inactiveCount }}</p>
            <p class="text-xs text-slate-400 mt-0.5">Nonaktif</p>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 flex items-center gap-3 hover:shadow-md transition-shadow">
        <div class="w-10 h-10 rounded-xl bg-violet-100 flex items-center justify-center flex-shrink-0">
            <svg class="h-5 w-5 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 3M21 7.5H7.5"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-black text-violet-700 tabular-nums leading-none">{{ number_format($totalMovements) }}</p>
            <p class="text-xs text-slate-400 mt-0.5">Pergerakan Stok</p>
        </div>
    </div>
</div>

@if($warehouses->isEmpty())

{{-- Empty State --}}
<div class="bg-white rounded-2xl border border-dashed border-slate-300 shadow-sm p-14 text-center">
    <div class="mx-auto mb-5 w-16 h-16 rounded-2xl bg-indigo-50 flex items-center justify-center">
        <svg class="h-8 w-8 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72l1.189-1.19A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72M6.75 18h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .414.336.75.75.75z"/>
        </svg>
    </div>
    <h3 class="text-lg font-bold text-slate-700 mb-2">Belum ada gudang terdaftar</h3>
    <p class="text-sm text-slate-400 mb-6 max-w-sm mx-auto leading-relaxed">
        Mulai kelola penyimpanan material dengan menambahkan gudang pertama Anda ke dalam sistem.
    </p>
    @if(auth()->user()->role === 'admin')
    <a href="{{ route('warehouses.create') }}"
       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition-all shadow-sm">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
        </svg>
        Tambah Gudang Pertama
    </a>
    @endif
</div>

@else

{{-- Search, Filter & View Toggle --}}
<div x-data="{
    search: '',
    statusFilter: 'all',
    viewMode: 'grid',
    warehouses: @js($warehouses->map(fn($w) => [
        'id'           => $w->id,
        'name'         => $w->name,
        'code'         => $w->code ?? '',
        'location'     => $w->location ?? '',
        'manager_name' => $w->manager_name ?? '',
        'is_active'    => $w->is_active,
    ])),
    get filtered() {
        const q = this.search.toLowerCase().trim();
        return this.warehouses.filter(w => {
            const matchSearch = !q
                || w.name.toLowerCase().includes(q)
                || w.code.toLowerCase().includes(q)
                || w.location.toLowerCase().includes(q)
                || w.manager_name.toLowerCase().includes(q);
            const matchStatus = this.statusFilter === 'all'
                || (this.statusFilter === 'active'   && w.is_active)
                || (this.statusFilter === 'inactive' && !w.is_active);
            return matchSearch && matchStatus;
        });
    },
    isVisible(id) { return this.filtered.some(f => f.id === id); }
}">

    {{-- Toolbar --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 mb-5">
        <div class="flex flex-col sm:flex-row gap-3">
            {{-- Search Input --}}
            <div class="flex-1 relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803 7.5 7.5 0 0015.803 15.803z"/>
                </svg>
                <input x-model="search" type="text" placeholder="Cari nama, kode, lokasi, atau pengelola..."
                    class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-700 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-400 focus:bg-white outline-none transition-all">
            </div>
            {{-- Status Segmented Control --}}
            <div class="flex items-center gap-1 bg-slate-100 rounded-xl p-1 flex-shrink-0">
                <button @click="statusFilter='all'"
                    :class="statusFilter==='all' ? 'bg-white shadow-sm text-slate-800 font-semibold' : 'text-slate-500 hover:text-slate-700'"
                    class="px-3 py-1.5 rounded-lg text-xs transition-all">Semua</button>
                <button @click="statusFilter='active'"
                    :class="statusFilter==='active' ? 'bg-white shadow-sm text-emerald-700 font-semibold' : 'text-slate-500 hover:text-slate-700'"
                    class="px-3 py-1.5 rounded-lg text-xs transition-all">Aktif</button>
                <button @click="statusFilter='inactive'"
                    :class="statusFilter==='inactive' ? 'bg-white shadow-sm text-slate-600 font-semibold' : 'text-slate-500 hover:text-slate-700'"
                    class="px-3 py-1.5 rounded-lg text-xs transition-all">Nonaktif</button>
            </div>
            {{-- View Mode --}}
            <div class="flex items-center gap-1 bg-slate-100 rounded-xl p-1 flex-shrink-0">
                <button @click="viewMode='grid'"
                    :class="viewMode==='grid' ? 'bg-white shadow-sm text-indigo-600' : 'text-slate-400 hover:text-slate-600'"
                    class="w-8 h-8 rounded-lg flex items-center justify-center transition-all" title="Tampilan Grid">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>
                    </svg>
                </button>
                <button @click="viewMode='list'"
                    :class="viewMode==='list' ? 'bg-white shadow-sm text-indigo-600' : 'text-slate-400 hover:text-slate-600'"
                    class="w-8 h-8 rounded-lg flex items-center justify-center transition-all" title="Tampilan Tabel">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                    </svg>
                </button>
            </div>
        </div>
        {{-- Result summary --}}
        <div class="mt-3 flex items-center gap-2 flex-wrap">
            <span class="text-xs text-slate-400">
                Menampilkan <span x-text="filtered.length" class="font-semibold text-slate-600"></span> dari {{ $totalWarehouses }} gudang
            </span>
            <template x-if="search.trim()">
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-indigo-50 border border-indigo-200 text-indigo-600 text-xs font-medium">
                    <svg class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803 7.5 7.5 0 0015.803 15.803z"/></svg>
                    <span x-text="'&quot;' + search + '&quot;'"></span>
                </span>
            </template>
        </div>
    </div>

    {{-- No results --}}
    <template x-if="filtered.length === 0">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-10 text-center">
            <div class="mx-auto w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center mb-3">
                <svg class="h-6 w-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803 7.5 7.5 0 0015.803 15.803z"/>
                </svg>
            </div>
            <p class="text-sm font-semibold text-slate-600">Gudang tidak ditemukan</p>
            <p class="text-xs text-slate-400 mt-1">Coba ubah kata kunci atau filter yang digunakan.</p>
            <button @click="search=''; statusFilter='all'"
                class="mt-3 text-xs text-indigo-600 hover:text-indigo-700 font-semibold transition-colors">
                Reset Pencarian
            </button>
        </div>
    </template>

    {{-- ======= GRID VIEW ======= --}}
    <div x-show="viewMode === 'grid' && filtered.length > 0"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">

        @foreach($warehouses as $idx => $wh)
        @php $cs = $colorSchemes[$idx % count($colorSchemes)]; @endphp
        <div x-show="isVisible({{ $wh->id }})"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-200 overflow-hidden group">

            {{-- Color accent bar --}}
            <div class="h-1.5 w-full {{ $cs['accent'] }}"></div>

            <div class="p-5">
                {{-- Top row: icon + name + status --}}
                <div class="flex items-start justify-between gap-3 mb-4">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="flex-shrink-0 w-11 h-11 rounded-2xl {{ $cs['icon_bg'] }} flex items-center justify-center group-hover:scale-105 transition-transform duration-200">
                            <svg class="h-5 w-5 {{ $cs['icon_text'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72l1.189-1.19A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72M6.75 18h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .414.336.75.75.75z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h3 class="font-bold text-slate-800 text-sm leading-snug truncate">{{ $wh->name }}</h3>
                            @if($wh->code)
                            <span class="inline-block px-1.5 py-0.5 rounded text-xs font-mono font-semibold {{ $cs['badge_bg'] }} {{ $cs['badge_text'] }} border {{ $cs['badge_border'] }} mt-0.5">
                                {{ $wh->code }}
                            </span>
                            @endif
                        </div>
                    </div>
                    @if($wh->is_active)
                    <span class="flex-shrink-0 inline-flex items-center gap-1 px-2 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-xs font-semibold text-emerald-700">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>Aktif
                    </span>
                    @else
                    <span class="flex-shrink-0 inline-flex items-center gap-1 px-2 py-1 rounded-full bg-slate-100 border border-slate-200 text-xs font-semibold text-slate-500">
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>Nonaktif
                    </span>
                    @endif
                </div>

                {{-- Detail info --}}
                <div class="space-y-2 mb-4 min-h-[56px]">
                    @if($wh->location)
                    <div class="flex items-start gap-2 text-sm text-slate-500">
                        <svg class="h-3.5 w-3.5 flex-shrink-0 mt-0.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                        </svg>
                        <span class="leading-snug">{{ $wh->location }}</span>
                    </div>
                    @endif
                    @if($wh->manager_name)
                    <div class="flex items-center gap-2 text-sm text-slate-500">
                        <div class="w-5 h-5 rounded-full bg-slate-200 flex items-center justify-center flex-shrink-0">
                            <span class="text-xs font-bold text-slate-600 leading-none">{{ strtoupper(substr($wh->manager_name, 0, 1)) }}</span>
                        </div>
                        <span class="truncate font-medium text-slate-600">{{ $wh->manager_name }}</span>
                        @if($wh->phone)
                        <span class="text-slate-300 flex-shrink-0">·</span>
                        <span class="text-xs text-slate-400 font-mono flex-shrink-0">{{ $wh->phone }}</span>
                        @endif
                    </div>
                    @endif
                    @if($wh->description)
                    <p class="text-xs text-slate-400 line-clamp-2 leading-relaxed">{{ $wh->description }}</p>
                    @endif
                    @if(!$wh->location && !$wh->manager_name && !$wh->description)
                    <p class="text-xs text-slate-300 italic">Belum ada detail lokasi atau pengelola.</p>
                    @endif
                </div>

                {{-- Stats mini cards --}}
                <div class="grid grid-cols-2 gap-2 mb-4">
                    <div class="bg-slate-50 rounded-xl p-2.5 text-center border border-slate-100">
                        <p class="text-xl font-black {{ $cs['icon_text'] }} tabular-nums leading-none">
                            {{ number_format($wh->stock_movements_count ?? 0) }}
                        </p>
                        <p class="text-xs text-slate-400 mt-0.5">Pergerakan Stok</p>
                    </div>
                    <div class="bg-slate-50 rounded-xl p-2.5 text-center border border-slate-100">
                        <p class="text-xl font-black text-slate-600 tabular-nums leading-none">
                            {{ $wh->created_at ? $wh->created_at->format('Y') : '—' }}
                        </p>
                        <p class="text-xs text-slate-400 mt-0.5">Tahun Dibuat</p>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center gap-2">
                    <a href="{{ route('warehouses.qr-label', $wh) }}"
                       class="flex-shrink-0 inline-flex items-center gap-1.5 px-2.5 py-2 rounded-xl border border-slate-200 bg-white text-xs font-medium text-slate-600 hover:bg-slate-50 hover:border-slate-300 transition-all"
                       title="Cetak QR Label">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75zM13.5 13.5h.75v.75h-.75v-.75zM13.5 19.5h.75v.75h-.75v-.75zM19.5 13.5h.75v.75h-.75v-.75zM19.5 19.5h.75v.75h-.75v-.75zM16.5 16.5h.75v.75h-.75v-.75z"/>
                        </svg>
                        QR
                    </a>
                    @if(auth()->user()->role === 'admin')
                    <a href="{{ route('warehouses.edit', $wh) }}"
                       class="flex-shrink-0 inline-flex items-center gap-1.5 px-2.5 py-2 rounded-xl border border-slate-200 bg-white text-xs font-medium text-slate-600 hover:bg-slate-50 hover:border-slate-300 transition-all">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                        </svg>
                        Edit
                    </a>
                    @endif
                    <a href="{{ route('warehouses.show', $wh) }}"
                       class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl {{ $cs['icon_bg'] }} border {{ $cs['badge_border'] }} text-xs font-semibold {{ $cs['icon_text'] }} hover:opacity-80 transition-all">
                        Lihat Detail
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ======= LIST VIEW ======= --}}
    <div x-show="viewMode === 'list' && filtered.length > 0"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider w-2"></th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Gudang</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Lokasi</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Pengelola</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Pergerakan</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($warehouses as $idx => $wh)
                    @php $cs = $colorSchemes[$idx % count($colorSchemes)]; @endphp
                    <tr x-show="isVisible({{ $wh->id }})"
                        class="hover:bg-slate-50/60 transition-colors">
                        {{-- Color strip --}}
                        <td class="pl-3 pr-0 py-3.5">
                            <div class="w-1 h-8 rounded-full {{ $cs['accent'] }}"></div>
                        </td>
                        {{-- Gudang --}}
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl {{ $cs['icon_bg'] }} flex items-center justify-center flex-shrink-0">
                                    <svg class="h-4 w-4 {{ $cs['icon_text'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-800">{{ $wh->name }}</p>
                                    @if($wh->code)
                                    <span class="text-xs font-mono {{ $cs['icon_text'] }} font-semibold">{{ $wh->code }}</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        {{-- Lokasi --}}
                        <td class="px-5 py-3.5">
                            @if($wh->location)
                            <div class="flex items-center gap-1.5 text-sm text-slate-500">
                                <svg class="h-3.5 w-3.5 text-slate-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                                </svg>
                                <span>{{ $wh->location }}</span>
                            </div>
                            @else
                            <span class="text-slate-300 text-xs italic">Belum diisi</span>
                            @endif
                        </td>
                        {{-- Pengelola --}}
                        <td class="px-5 py-3.5">
                            @if($wh->manager_name)
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-slate-200 flex items-center justify-center flex-shrink-0">
                                    <span class="text-xs font-bold text-slate-600 leading-none">{{ strtoupper(substr($wh->manager_name, 0, 1)) }}</span>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-slate-700">{{ $wh->manager_name }}</p>
                                    @if($wh->phone)
                                    <p class="text-xs text-slate-400 font-mono">{{ $wh->phone }}</p>
                                    @endif
                                </div>
                            </div>
                            @else
                            <span class="text-slate-300 text-xs italic">Belum diisi</span>
                            @endif
                        </td>
                        {{-- Status --}}
                        <td class="px-5 py-3.5 text-center">
                            @if($wh->is_active)
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-xs font-semibold text-emerald-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>Aktif
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-slate-100 border border-slate-200 text-xs font-semibold text-slate-500">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>Nonaktif
                            </span>
                            @endif
                        </td>
                        {{-- Pergerakan --}}
                        <td class="px-5 py-3.5 text-right">
                            <span class="font-black tabular-nums {{ $cs['icon_text'] }} text-base">
                                {{ number_format($wh->stock_movements_count ?? 0) }}
                            </span>
                            <p class="text-xs text-slate-400">pergerakan</p>
                        </td>
                        {{-- Aksi --}}
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('warehouses.qr-label', $wh) }}"
                                   class="w-7 h-7 rounded-lg border border-slate-200 bg-white flex items-center justify-center text-slate-500 hover:bg-slate-50 hover:border-slate-300 transition-all"
                                   title="QR Label">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5z"/>
                                    </svg>
                                </a>
                                @if(auth()->user()->role === 'admin')
                                <a href="{{ route('warehouses.edit', $wh) }}"
                                   class="w-7 h-7 rounded-lg border border-slate-200 bg-white flex items-center justify-center text-slate-500 hover:bg-slate-50 hover:border-slate-300 transition-all"
                                   title="Edit Gudang">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                                    </svg>
                                </a>
                                @endif
                                <a href="{{ route('warehouses.show', $wh) }}"
                                   class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg {{ $cs['icon_bg'] }} border {{ $cs['badge_border'] }} text-xs font-semibold {{ $cs['icon_text'] }} hover:opacity-80 transition-all">
                                    Detail
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2">
            <span class="text-xs text-slate-400">{{ $totalWarehouses }} gudang terdaftar &bull; {{ $activeCount }} aktif &bull; {{ $inactiveCount }} nonaktif</span>
            <span class="text-xs text-slate-400">{{ now()->translatedFormat('d F Y') }}</span>
        </div>
    </div>

</div>
@endif

@endsection
