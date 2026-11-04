@extends('layouts.app')

@section('title', 'Tambah Akun COA')

@section('content')

<x-page-header title="Tambah Akun COA" subtitle="Buat akun baru untuk Chart of Accounts" :back="route('coa.index')"/>

<div class="max-w-2xl">
    <x-data-card title="Form Akun Baru">
        <form method="POST" action="{{ route('coa.store') }}" class="p-6 space-y-5">
            @csrf

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-input-label for="code" value="Kode Akun *"/>
                    <x-text-input id="code" name="code" class="mt-1 w-full" value="{{ old('code') }}" placeholder="mis. 1101, 2101, 5102" required/>
                    <p class="text-xs text-slate-400 mt-1">1xxx=Aset, 2xxx=Kewajiban, 3xxx=Ekuitas, 4xxx=Pendapatan, 5xxx=HPP, 6xxx=Beban</p>
                    <x-input-error :messages="$errors->get('code')" class="mt-1"/>
                </div>
                <div>
                    <x-input-label for="name" value="Nama Akun *"/>
                    <x-text-input id="name" name="name" class="mt-1 w-full" value="{{ old('name') }}" placeholder="mis. Kas, Piutang Usaha" required/>
                    <x-input-error :messages="$errors->get('name')" class="mt-1"/>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-input-label for="account_type" value="Tipe Akun *"/>
                    <select id="account_type" name="account_type"
                            class="mt-1 w-full rounded-lg border border-slate-300 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                        <option value="">-- Pilih Tipe --</option>
                        <option value="asset"     @selected(old('account_type') === 'asset')>Aset (Asset)</option>
                        <option value="liability" @selected(old('account_type') === 'liability')>Kewajiban (Liability)</option>
                        <option value="equity"    @selected(old('account_type') === 'equity')>Ekuitas (Equity)</option>
                        <option value="revenue"   @selected(old('account_type') === 'revenue')>Pendapatan (Revenue)</option>
                        <option value="expense"   @selected(old('account_type') === 'expense')>Beban (Expense)</option>
                    </select>
                    <x-input-error :messages="$errors->get('account_type')" class="mt-1"/>
                </div>
                <div>
                    <x-input-label for="normal_balance" value="Saldo Normal *"/>
                    <select id="normal_balance" name="normal_balance"
                            class="mt-1 w-full rounded-lg border border-slate-300 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                        <option value="debit"  @selected(old('normal_balance', 'debit') === 'debit')>Debit — bertambah di sisi Debit</option>
                        <option value="credit" @selected(old('normal_balance') === 'credit')>Kredit — bertambah di sisi Kredit</option>
                    </select>
                    <p class="text-xs text-slate-400 mt-1">Aset/Beban = Debit | Kewajiban/Ekuitas/Pendapatan = Kredit</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-input-label for="parent_id" value="Induk Akun"/>
                    <select id="parent_id" name="parent_id"
                            class="mt-1 w-full rounded-lg border border-slate-300 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- Tidak ada induk --</option>
                        @foreach($parents as $p)
                            <option value="{{ $p->id }}" @selected(old('parent_id') == $p->id)>
                                {{ $p->code }} — {{ $p->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="account_subtype" value="Sub-tipe (opsional)"/>
                    <x-text-input id="account_subtype" name="account_subtype" class="mt-1 w-full"
                                  value="{{ old('account_subtype') }}" placeholder="mis. current_asset, fixed_asset"/>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-input-label for="sort_order" value="Urutan Tampil"/>
                    <x-text-input id="sort_order" name="sort_order" type="number" class="mt-1 w-full"
                                  value="{{ old('sort_order', 0) }}" min="0"/>
                </div>
                <div class="flex items-end pb-1.5">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_header" value="1" {{ old('is_header') ? 'checked' : '' }}
                               class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm text-slate-700">Akun Header (tidak bisa diposting langsung)</span>
                    </label>
                </div>
            </div>

            <div>
                <x-input-label for="description" value="Keterangan / Deskripsi"/>
                <textarea id="description" name="description" rows="2"
                          class="mt-1 w-full rounded-lg border border-slate-300 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                          placeholder="Penjelasan singkat tentang akun ini...">{{ old('description') }}</textarea>
            </div>

            <div class="flex gap-3 pt-2 border-t border-slate-100">
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-indigo-600 text-sm font-semibold text-white hover:bg-indigo-700 transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Simpan Akun
                </button>
                <a href="{{ route('coa.index') }}" class="inline-flex items-center px-4 py-2 rounded-lg border border-slate-300 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                    Batal
                </a>
            </div>
        </form>
    </x-data-card>
</div>

@endsection
