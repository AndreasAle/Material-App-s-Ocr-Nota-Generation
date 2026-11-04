@extends('layouts.app')
@section('content')

<x-page-header title="Hasil Pencarian" subtitle="{{ $q ? 'Menampilkan hasil untuk: ' . $q : 'Masukkan kata kunci untuk mulai mencari' }}">
</x-page-header>

@if(strlen($q) < 2 && $q !== '')
<x-data-card>
    <div class="py-12 text-center text-slate-400">
        <div class="text-5xl mb-3">🔍</div>
        <p class="font-semibold text-slate-600">Kata kunci terlalu pendek</p>
        <p class="text-sm mt-1">Masukkan minimal 2 karakter.</p>
    </div>
</x-data-card>
@elseif(empty($results))
<x-data-card>
    <div class="py-16 text-center text-slate-400">
        <div class="text-5xl mb-3">🔍</div>
        @if($q)
        <p class="font-semibold text-slate-600">Tidak ada hasil untuk "{{ $q }}"</p>
        <p class="text-sm mt-1">Coba kata kunci lain atau periksa ejaan.</p>
        @else
        <p class="font-semibold text-slate-600">Masukkan kata kunci di kotak pencarian di atas.</p>
        @endif
    </div>
</x-data-card>
@else
<div class="mb-4 text-sm text-slate-500">
    Ditemukan <span class="font-semibold text-slate-800">{{ $total }}</span> hasil untuk "<span class="font-semibold text-indigo-700">{{ $q }}</span>"
</div>

@php
$colorMap = [
    'indigo' => ['bg' => 'bg-indigo-100', 'text' => 'text-indigo-700', 'border' => 'border-indigo-200'],
    'emerald' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200'],
    'violet' => ['bg' => 'bg-indigo-100', 'text' => 'text-indigo-700', 'border' => 'border-indigo-200'],
    'amber' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-700', 'border' => 'border-amber-200'],
    'cyan' => ['bg' => 'bg-cyan-100', 'text' => 'text-cyan-700', 'border' => 'border-cyan-200'],
    'rose' => ['bg' => 'bg-rose-100', 'text' => 'text-rose-700', 'border' => 'border-rose-200'],
    'teal' => ['bg' => 'bg-indigo-100', 'text' => 'text-indigo-700', 'border' => 'border-indigo-200'],
    'orange' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-700', 'border' => 'border-amber-200'],
    'slate' => ['bg' => 'bg-slate-100', 'text' => 'text-slate-700', 'border' => 'border-slate-200'],
];
@endphp

<div class="space-y-3">
    @foreach($results as $item)
    @php $c = $colorMap[$item['color']] ?? $colorMap['slate']; @endphp
    <a href="{{ $item['url'] }}"
       class="flex items-center gap-4 rounded-[18px] border {{ $c['border'] }} bg-white px-5 py-4 shadow-sm hover:shadow-md transition-all hover:-translate-y-0.5 group">
        <div class="text-2xl w-10 shrink-0 text-center">{{ $item['icon'] }}</div>
        <div class="flex-1 min-w-0">
            <div class="font-semibold text-slate-900 truncate group-hover:text-indigo-700 transition-colors">{{ $item['title'] }}</div>
            <div class="text-xs text-slate-500 truncate">{{ $item['subtitle'] }}</div>
        </div>
        <div class="shrink-0">
            <span class="{{ $c['bg'] }} {{ $c['text'] }} text-xs font-semibold px-2.5 py-1 rounded-full">{{ $item['type'] }}</span>
        </div>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-300 group-hover:text-indigo-500 transition-colors shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
        </svg>
    </a>
    @endforeach
</div>
@endif
@endsection
