@extends('layouts.app')

@section('title', 'Bagan Akun (COA)')

@section('content')

<x-page-header title="Bagan Akun (COA)" subtitle="Chart of Accounts — struktur akun untuk sistem double-entry bookkeeping">
    <x-slot name="actions">
        <a href="{{ route('coa.create') }}"
           class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 transition-colors">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Tambah Akun
        </a>
    </x-slot>
</x-page-header>


{{-- Stats --}}
<div class="grid grid-cols-3 gap-4 mb-6">
    <x-data-card>
        <div class="p-5 flex items-center gap-4">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-100">
                <svg class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-slate-500">Total Akun</p>
                <p class="text-2xl font-extrabold text-slate-800">{{ $stats['total'] }}</p>
            </div>
        </div>
    </x-data-card>
    <x-data-card>
        <div class="p-5 flex items-center gap-4">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100">
                <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-slate-500">Akun Aktif</p>
                <p class="text-2xl font-extrabold text-emerald-700">{{ $stats['active'] }}</p>
            </div>
        </div>
    </x-data-card>
    <x-data-card>
        <div class="p-5 flex items-center gap-4">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100">
                <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-slate-500">Akun Header</p>
                <p class="text-2xl font-extrabold text-blue-700">{{ $stats['headers'] }}</p>
            </div>
        </div>
    </x-data-card>
</div>

{{-- Info Box --}}
<div class="mb-6 rounded-xl bg-blue-50 border border-blue-200 px-4 py-3 text-sm text-blue-700">
    <strong>Petunjuk:</strong> Kode akun: <strong>1xxx</strong> = Aset, <strong>2xxx</strong> = Kewajiban, <strong>3xxx</strong> = Ekuitas, <strong>4xxx</strong> = Pendapatan, <strong>5xxx</strong> = HPP, <strong>6xxx</strong> = Beban. Saldo Normal Debit = bertambah di sisi Debit; Kredit = bertambah di sisi Kredit.
</div>

@php
    $typeLabels = [
        'asset'     => ['label' => 'Aset (Asset)',             'color' => 'bg-blue-50',    'badge' => 'bg-blue-100 text-blue-800',    'border' => 'border-blue-200'],
        'liability' => ['label' => 'Kewajiban (Liability)',    'color' => 'bg-red-50',     'badge' => 'bg-red-100 text-red-800',      'border' => 'border-red-200'],
        'equity'    => ['label' => 'Ekuitas (Equity)',         'color' => 'bg-purple-50',  'badge' => 'bg-purple-100 text-purple-800','border' => 'border-purple-200'],
        'revenue'   => ['label' => 'Pendapatan (Revenue)',     'color' => 'bg-emerald-50', 'badge' => 'bg-emerald-100 text-emerald-800','border' => 'border-emerald-200'],
        'expense'   => ['label' => 'Beban (Expense)',          'color' => 'bg-orange-50',  'badge' => 'bg-orange-100 text-orange-800', 'border' => 'border-orange-200'],
    ];
@endphp

@foreach($grouped as $type => $accounts)
@php $cfg = $typeLabels[$type] ?? ['label' => $type, 'color' => 'bg-slate-50', 'badge' => 'bg-slate-100 text-slate-700', 'border' => 'border-slate-200']; @endphp

<x-data-card class="mb-5">
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between {{ $cfg['color'] }}">
        <div class="flex items-center gap-3">
            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold {{ $cfg['badge'] }}">{{ $cfg['label'] }}</span>
            <span class="text-sm text-slate-500">{{ $accounts->count() }} akun</span>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50/60">
                    <th class="px-4 py-2.5 text-left font-semibold text-slate-600 text-xs uppercase tracking-wide">Kode</th>
                    <th class="px-4 py-2.5 text-left font-semibold text-slate-600 text-xs uppercase tracking-wide">Nama Akun</th>
                    <th class="px-4 py-2.5 text-center font-semibold text-slate-600 text-xs uppercase tracking-wide">Sub-tipe</th>
                    <th class="px-4 py-2.5 text-center font-semibold text-slate-600 text-xs uppercase tracking-wide">Saldo Normal</th>
                    <th class="px-4 py-2.5 text-center font-semibold text-slate-600 text-xs uppercase tracking-wide">Level</th>
                    <th class="px-4 py-2.5 text-center font-semibold text-slate-600 text-xs uppercase tracking-wide">Status</th>
                    <th class="px-4 py-2.5 text-center font-semibold text-slate-600 text-xs uppercase tracking-wide">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach($accounts as $acc)
                <tr class="hover:bg-slate-50 {{ $acc->is_header ? 'bg-slate-50/60 font-semibold' : '' }}">
                    <td class="px-4 py-2.5 font-mono text-slate-700 text-sm">{{ $acc->code }}</td>
                    <td class="px-4 py-2.5">
                        <div style="padding-left: {{ ($acc->level - 1) * 16 }}px" class="flex items-center gap-2">
                            @if($acc->is_header)
                                <span class="inline-flex text-xs bg-slate-200 text-slate-600 px-1.5 py-0.5 rounded font-medium shrink-0">Header</span>
                            @else
                                <span class="inline-block h-0.5 w-3 bg-slate-300 rounded shrink-0"></span>
                            @endif
                            <span class="{{ $acc->is_active ? 'text-slate-800' : 'text-slate-400 line-through' }}">{{ $acc->name }}</span>
                        </div>
                        @if($acc->description)
                            <p class="text-xs text-slate-400 mt-0.5 pl-{{ ($acc->level - 1) * 4 + 5 }}">{{ Str::limit($acc->description, 60) }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-2.5 text-center text-xs text-slate-500">{{ $acc->account_subtype ?? '—' }}</td>
                    <td class="px-4 py-2.5 text-center">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $acc->normal_balance === 'debit' ? 'bg-blue-100 text-blue-700' : 'bg-orange-100 text-orange-700' }}">
                            {{ ucfirst($acc->normal_balance) }}
                        </span>
                    </td>
                    <td class="px-4 py-2.5 text-center">
                        <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-600">{{ $acc->level }}</span>
                    </td>
                    <td class="px-4 py-2.5 text-center">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $acc->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                            {{ $acc->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td class="px-4 py-2.5 text-center">
                        <div class="flex items-center justify-center gap-3">
                            <a href="{{ route('coa.edit', $acc) }}" class="text-xs font-semibold text-indigo-600 hover:underline">Edit</a>
                            <form method="POST" action="{{ route('coa.destroy', $acc) }}"
                                  onsubmit="return confirm('Hapus akun {{ $acc->code }} {{ $acc->name }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs font-semibold text-red-500 hover:underline">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-data-card>
@endforeach

@endsection
