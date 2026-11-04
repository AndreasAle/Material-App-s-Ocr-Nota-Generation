@extends('layouts.app')

@section('title', 'Edit Akun ' . $coa->code)

@section('content')

<x-page-header title="Edit Akun: {{ $coa->code }} — {{ $coa->name }}" subtitle="Perbarui data akun dalam Chart of Accounts" :back="route('coa.index')"/>

<div class="max-w-2xl">
    <x-data-card title="Edit Akun COA">
        <form method="POST" action="{{ route('coa.update', $coa) }}" class="p-6 space-y-5">
            @csrf @method('PUT')

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-input-label for="code" value="Kode Akun *"/>
                    <x-text-input id="code" name="code" class="mt-1 w-full" value="{{ old('code', $coa->code) }}" required/>
                    <x-input-error :messages="$errors->get('code')" class="mt-1"/>
                </div>
                <div>
                    <x-input-label for="name" value="Nama Akun *"/>
                    <x-text-input id="name" name="name" class="mt-1 w-full" value="{{ old('name', $coa->name) }}" required/>
                    <x-input-error :messages="$errors->get('name')" class="mt-1"/>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-input-label for="account_type" value="Tipe Akun *"/>
                    <select id="account_type" name="account_type"
                            class="mt-1 w-full rounded-lg border border-slate-300 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                        <option value="asset"     @selected(old('account_type', $coa->account_type) === 'asset')>Aset (Asset)</option>
                        <option value="liability" @selected(old('account_type', $coa->account_type) === 'liability')>Kewajiban (Liability)</option>
                        <option value="equity"    @selected(old('account_type', $coa->account_type) === 'equity')>Ekuitas (Equity)</option>
                        <option value="revenue"   @selected(old('account_type', $coa->account_type) === 'revenue')>Pendapatan (Revenue)</option>
                        <option value="expense"   @selected(old('account_type', $coa->account_type) === 'expense')>Beban (Expense)</option>
                    </select>
                </div>
                <div>
                    <x-input-label for="normal_balance" value="Saldo Normal *"/>
                    <select id="normal_balance" name="normal_balance"
                            class="mt-1 w-full rounded-lg border border-slate-300 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                        <option value="debit"  @selected(old('normal_balance', $coa->normal_balance) === 'debit')>Debit</option>
                        <option value="credit" @selected(old('normal_balance', $coa->normal_balance) === 'credit')>Kredit</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-input-label for="account_subtype" value="Sub-tipe"/>
                    <x-text-input id="account_subtype" name="account_subtype" class="mt-1 w-full"
                                  value="{{ old('account_subtype', $coa->account_subtype) }}"/>
                </div>
                <div>
                    <x-input-label for="sort_order" value="Urutan Tampil"/>
                    <x-text-input id="sort_order" name="sort_order" type="number" class="mt-1 w-full"
                                  value="{{ old('sort_order', $coa->sort_order ?? 0) }}" min="0"/>
                </div>
            </div>

            <div class="flex items-center gap-6">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_header" value="1"
                           {{ old('is_header', $coa->is_header) ? 'checked' : '' }}
                           class="rounded border-slate-300 text-indigo-600">
                    <span class="text-sm text-slate-700">Akun Header</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1"
                           {{ old('is_active', $coa->is_active) ? 'checked' : '' }}
                           class="rounded border-slate-300 text-indigo-600">
                    <span class="text-sm text-slate-700">Aktif</span>
                </label>
            </div>

            <div>
                <x-input-label for="description" value="Keterangan"/>
                <textarea id="description" name="description" rows="2"
                          class="mt-1 w-full rounded-lg border border-slate-300 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('description', $coa->description) }}</textarea>
            </div>

            <div class="flex gap-3 pt-2 border-t border-slate-100">
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-indigo-600 text-sm font-semibold text-white hover:bg-indigo-700 transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Update Akun
                </button>
                <a href="{{ route('coa.index') }}" class="inline-flex items-center px-4 py-2 rounded-lg border border-slate-300 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">Batal</a>
            </div>
        </form>
    </x-data-card>

    {{-- Danger Zone --}}
    @if(!$coa->journalLines()->exists() && !$coa->children()->exists())
    <x-data-card class="mt-5 border-red-200">
        <div class="p-5">
            <p class="text-sm font-semibold text-red-700 mb-2">Zona Berbahaya</p>
            <form method="POST" action="{{ route('coa.destroy', $coa) }}"
                  onsubmit="return confirm('Hapus akun {{ $coa->code }} — {{ $coa->name }}? Tindakan ini tidak dapat dibatalkan.')">
                @csrf @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-red-300 text-sm font-semibold text-red-600 hover:bg-red-50 transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                    </svg>
                    Hapus Akun Ini
                </button>
            </form>
        </div>
    </x-data-card>
    @endif
</div>

@endsection
