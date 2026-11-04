@extends('layouts.app')

@section('title', 'Detail User: ' . $user->name)

@section('content')
@php
    $roleBadge = match($user->role) {
        'admin'    => 'badge-info',
        'pimpinan' => 'badge-purple',
        'kasir' => 'badge-green',
        default    => 'badge-neutral',
    };
    $roleLabel = match($user->role) {
        'admin'    => 'Admin',
        'pimpinan' => 'Pimpinan',
        'kasir' => 'Kasir',
        'purchasing' => 'Purchasing',
        default    => 'Staff',
    };
@endphp

<x-page-header title="{{ $user->name }}" subtitle="{{ $user->email }}">
    <x-slot name="actions">
        <a href="{{ route('users.index') }}" class="btn-secondary">← Daftar User</a>
        <a href="{{ route('users.edit', $user) }}" class="btn-primary">Edit User</a>
    </x-slot>
</x-page-header>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Left: Profil + Stats --}}
    <div class="space-y-5">
        {{-- Profil Card --}}
        <x-data-card>
            <div class="text-center mb-5">
                <div class="w-16 h-16 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-2xl mx-auto mb-3">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <h3 class="font-bold text-slate-800 text-lg">{{ $user->name }}</h3>
                <p class="text-sm text-slate-400">{{ $user->email }}</p>
                <div class="flex items-center justify-center gap-2 mt-2">
                    <span class="{{ $roleBadge }}">{{ $roleLabel }}</span>
                    @if ($user->is_active)
                        <span class="badge-success text-xs">Aktif</span>
                    @else
                        <span class="badge-danger text-xs">Nonaktif</span>
                    @endif
                </div>
            </div>

            <div class="space-y-2.5 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-500">WhatsApp</span>
                    <span class="font-medium text-slate-700">{{ $user->whatsapp_number ?: '—' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Bergabung</span>
                    <span class="font-medium text-slate-700">{{ $user->created_at->format('d M Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">ID User</span>
                    <span class="font-medium text-slate-500">#{{ $user->id }}</span>
                </div>
            </div>
        </x-data-card>

        {{-- Stats --}}
        <x-data-card>
            <h4 class="font-semibold text-slate-700 mb-3 text-sm">Ringkasan Aktivitas</h4>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-500">Total Transaksi</span>
                    <span class="font-bold text-slate-800">{{ number_format($txCount) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-500">Transaksi Disetujui</span>
                    <span class="font-bold text-emerald-600">{{ number_format($txApproved) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-500">Total Nilai Pembelian</span>
                    <span class="font-bold text-indigo-600 text-xs">Rp {{ number_format($txTotal, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-500">Scan OCR</span>
                    <span class="font-bold text-slate-800">{{ number_format($scanCount) }}</span>
                </div>
            </div>
        </x-data-card>

        {{-- Quick Actions --}}
        @if (auth()->id() !== $user->id)
        <x-data-card>
            <h4 class="font-semibold text-slate-700 mb-3 text-sm">Aksi Cepat</h4>
            <div class="space-y-2">
                <form method="POST" action="{{ route('users.toggle-active', $user) }}">
                    @csrf
                    <button type="submit"
                            class="w-full text-left text-sm px-3 py-2 rounded-lg transition-colors flex items-center gap-2 {{ $user->is_active ? 'text-rose-600 hover:bg-rose-50' : 'text-emerald-600 hover:bg-emerald-50' }}">
                        @if ($user->is_active)
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                            </svg>
                            Nonaktifkan Akun
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Aktifkan Akun
                        @endif
                    </button>
                </form>
            </div>
        </x-data-card>
        @endif
    </div>

    {{-- Right: Chart + Recent Transactions --}}
    <div class="lg:col-span-2 space-y-5">
        {{-- Spending Chart --}}
        <x-data-card>
            <h4 class="font-semibold text-slate-700 mb-4">Pengeluaran 6 Bulan Terakhir</h4>
            @if (array_sum($chartData) > 0)
                <canvas id="userSpendChart" height="90"></canvas>
            @else
                <p class="text-slate-400 text-sm text-center py-6">Belum ada transaksi dalam 6 bulan terakhir</p>
            @endif
        </x-data-card>

        {{-- Recent Transactions --}}
        <x-data-card>
            <div class="flex items-center justify-between mb-4">
                <h4 class="font-semibold text-slate-700">Transaksi Terakhir</h4>
                <a href="{{ route('purchase-transactions.index', ['user_id' => $user->id]) }}"
                   class="text-xs text-indigo-600 hover:underline">Lihat semua →</a>
            </div>

            @if ($recentTx->isEmpty())
                <p class="text-slate-400 text-sm text-center py-4">Belum ada transaksi</p>
            @else
                <div class="space-y-2">
                    @foreach ($recentTx as $tx)
                        @php
                            $approvalBadge = match($tx->approval_status) {
                                'approved' => 'badge-success',
                                'rejected' => 'badge-danger',
                                'pending'  => 'badge-warning',
                                default    => 'badge-neutral',
                            };
                            $approvalLabel = match($tx->approval_status) {
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                                'pending'  => 'Menunggu',
                                default    => 'Draft',
                            };
                        @endphp
                        <div class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-slate-50 transition-colors">
                            <div class="flex-1 min-w-0">
                                <a href="{{ route('purchase-transactions.show', $tx) }}"
                                   class="font-medium text-slate-800 hover:text-indigo-600 text-sm transition-colors">
                                    {{ $tx->transaction_no }}
                                </a>
                                <p class="text-xs text-slate-400">
                                    {{ $tx->transaction_date->format('d M Y') }} · {{ $tx->project?->name ?? '—' }} · {{ $tx->supplier?->name ?? '—' }}
                                </p>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="font-semibold text-slate-700 text-sm">Rp {{ number_format($tx->grand_total, 0, ',', '.') }}</p>
                                <span class="{{ $approvalBadge }} text-xs">{{ $approvalLabel }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-data-card>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
@if (array_sum($chartData) > 0)
(function () {
    new Chart(document.getElementById('userSpendChart'), {
        type: 'bar',
        data: {
            labels: @json($chartLabels),
            datasets: [{
                label: 'Pengeluaran (Rp)',
                data: @json($chartData),
                backgroundColor: 'rgba(99, 102, 241, 0.15)',
                borderColor: 'rgba(99, 102, 241, 0.8)',
                borderWidth: 2,
                borderRadius: 6,
            }],
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: ctx => 'Rp ' + ctx.parsed.y.toLocaleString('id-ID') } },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: v => 'Rp ' + (v/1_000_000).toFixed(1) + 'jt', font: { size: 11 } },
                    grid: { color: 'rgba(0,0,0,0.04)' },
                },
                x: { ticks: { font: { size: 11 } }, grid: { display: false } },
            },
        },
    });
})();
@endif
</script>
@endpush
