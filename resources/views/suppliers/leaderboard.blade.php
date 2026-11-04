@extends('layouts.app')
@section('title', 'Leaderboard Vendor')

@section('content')
<x-page-header title="Leaderboard Vendor" subtitle="Peringkat performa supplier berdasarkan skor">
    <x-slot name="actions">
        <form method="POST" action="{{ route('suppliers.leaderboard.recalculate-all') }}">
            @csrf
            <button type="submit" class="btn-secondary text-sm"
                    onclick="return confirm('Hitung ulang skor semua vendor bulan ini?')">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                Hitung Ulang Semua
            </button>
        </form>
    </x-slot>
</x-page-header>


{{-- Period Filter --}}
<form method="GET" class="mb-5 flex flex-wrap gap-3 items-end">
    <div>
        <label class="form-label text-xs">Tahun</label>
        <select name="year" onchange="this.form.submit()" class="form-input w-28 text-sm">
            @for($y = now()->year; $y >= now()->year - 2; $y--)
            <option value="{{ $y }}" {{ request('year', now()->year) == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
        </select>
    </div>
    <div>
        <label class="form-label text-xs">Bulan</label>
        <select name="month" onchange="this.form.submit()" class="form-input w-32 text-sm">
            @for($m = 1; $m <= 12; $m++)
            <option value="{{ $m }}" {{ request('month', now()->month) == $m ? 'selected' : '' }}>
                {{ \Carbon\Carbon::create()->month($m)->locale('id')->monthName }}
            </option>
            @endfor
        </select>
    </div>
</form>

@if($scores->count() === 0)
<div class="rounded-2xl border-2 border-dashed border-slate-200 p-10 text-center">
    <p class="text-slate-500 font-semibold mb-2">Belum ada data skor untuk periode ini</p>
    <p class="text-slate-400 text-sm">Klik "Hitung Ulang Semua" untuk menghitung skor bulan ini</p>
</div>
@else
<x-data-card>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="th-center w-12">Rank</th>
                    <th>Supplier</th>
                    <th class="th-right">Skor Kirim</th>
                    <th class="th-right">Skor Qty</th>
                    <th class="th-right">Skor QC</th>
                    <th class="th-right">Skor Harga</th>
                    <th class="th-right">Overall</th>
                    <th class="th-center">Grade</th>
                    <th class="th-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($scores as $rank => $score)
                @php
                    $g = $score->grade ?? '—';
                    $gColor = match($g) {
                        'A' => 'text-emerald-700 bg-emerald-100',
                        'B' => 'text-blue-700 bg-blue-100',
                        'C' => 'text-amber-700 bg-amber-100',
                        'D' => 'text-orange-700 bg-orange-100',
                        default => 'text-rose-700 bg-rose-100',
                    };
                    $globalRank = ($scores->currentPage() - 1) * $scores->perPage() + $rank + 1;
                @endphp
                <tr class="{{ $globalRank <= 3 ? 'bg-amber-50/40' : '' }}">
                    <td class="text-center">
                        @if($globalRank === 1) <span class="text-xl">🥇</span>
                        @elseif($globalRank === 2) <span class="text-xl">🥈</span>
                        @elseif($globalRank === 3) <span class="text-xl">🥉</span>
                        @else <span class="text-slate-400 text-sm font-semibold">{{ $globalRank }}</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('suppliers.scorecard', $score->supplier) }}"
                           class="font-semibold text-slate-800 hover:text-indigo-600 transition">
                            {{ $score->supplier?->name ?? '—' }}
                        </a>
                    </td>
                    <td class="text-right font-mono text-sm">{{ number_format($score->delivery_score, 1) }}</td>
                    <td class="text-right font-mono text-sm">{{ number_format($score->quantity_score, 1) }}</td>
                    <td class="text-right font-mono text-sm">{{ number_format($score->quality_score, 1) }}</td>
                    <td class="text-right font-mono text-sm">{{ number_format($score->price_score, 1) }}</td>
                    <td class="text-right">
                        <div class="flex items-center justify-end gap-2">
                            <div class="flex-1 max-w-16 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full rounded-full {{ $score->overall_score >= 90 ? 'bg-emerald-500' : ($score->overall_score >= 70 ? 'bg-amber-400' : 'bg-rose-400') }}"
                                     style="width: {{ $score->overall_score }}%"></div>
                            </div>
                            <span class="font-bold text-sm text-slate-800">{{ number_format($score->overall_score, 1) }}</span>
                        </div>
                    </td>
                    <td class="text-center">
                        <span class="inline-block text-xs font-bold px-2 py-0.5 rounded-full {{ $gColor }}">{{ $g }}</span>
                    </td>
                    <td class="text-right">
                        <a href="{{ route('suppliers.scorecard', $score->supplier) }}"
                           class="text-xs text-indigo-600 hover:underline font-semibold">Detail →</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($scores->hasPages())
    <div class="px-5 py-4 border-t border-slate-100">{{ $scores->withQueryString()->links() }}</div>
    @endif
</x-data-card>
@endif
@endsection
