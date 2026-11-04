@extends('layouts.app')

@section('title', 'Buat Jurnal Umum')

@section('content')

<div x-data="{
    lines: [
        {account_id:'', debit:'', credit:'', description:'', project_id:''},
        {account_id:'', debit:'', credit:'', description:'', project_id:''}
    ],
    get totalDebit()  { return this.lines.reduce((s,l) => s + (parseFloat(l.debit)||0), 0); },
    get totalCredit() { return this.lines.reduce((s,l) => s + (parseFloat(l.credit)||0), 0); },
    get isBalanced()  { return Math.abs(this.totalDebit - this.totalCredit) < 0.01 && this.totalDebit > 0; },
    get diff()        { return this.totalDebit - this.totalCredit; },
    addLine()         { this.lines.push({account_id:'',debit:'',credit:'',description:'',project_id:''}); },
    removeLine(i)     { if(this.lines.length > 2) this.lines.splice(i,1); },
    fmt(n)            { return 'Rp ' + Math.round(n).toLocaleString('id-ID'); }
}">

<x-page-header title="Buat Jurnal Umum" subtitle="Entri jurnal double-entry — Debit harus sama dengan Kredit" :back="route('journal.index')"/>


{{-- Panduan --}}
<div class="mb-5 rounded-xl bg-blue-50 border border-blue-200 px-4 py-3 text-sm text-blue-800">
    <strong>Aturan Double-Entry:</strong> Setiap transaksi harus memiliki minimal 2 baris.
    Total <strong>Debit = Total Kredit</strong>. Contoh: catat penerimaan kas → Debit Kas, Kredit Piutang Usaha.
</div>

<form method="POST" action="{{ route('journal.store') }}">
    @csrf

    <x-data-card title="Header Jurnal" class="mb-5">
        <div class="p-6 grid grid-cols-2 gap-4">
            <div>
                <x-input-label for="entry_date" value="Tanggal Jurnal *"/>
                <x-text-input id="entry_date" name="entry_date" type="date" class="mt-1 w-full"
                              value="{{ old('entry_date', now()->toDateString()) }}" required/>
                <x-input-error :messages="$errors->get('entry_date')" class="mt-1"/>
            </div>
            <div>
                <x-input-label for="description" value="Keterangan / Memo *"/>
                <x-text-input id="description" name="description" class="mt-1 w-full"
                              value="{{ old('description') }}"
                              placeholder="mis. Penerimaan pembayaran Invoice INV-2026-001" required/>
                <x-input-error :messages="$errors->get('description')" class="mt-1"/>
            </div>
        </div>
    </x-data-card>

    <x-data-card title="Baris Jurnal" subtitle="Minimal 2 baris — Total Debit harus = Total Kredit">
        <div class="p-4">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs text-slate-500">Gunakan tombol <strong>+ Tambah Baris</strong> untuk menambah akun</p>
                <button type="button" @click="addLine()"
                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-700 text-xs font-semibold hover:bg-indigo-100 transition-colors">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                    </svg>
                    Tambah Baris
                </button>
            </div>

            <div class="overflow-x-auto rounded-xl border border-slate-200">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="px-3 py-2.5 text-left font-semibold text-slate-600 text-xs uppercase tracking-wide w-10">#</th>
                            <th class="px-3 py-2.5 text-left font-semibold text-slate-600 text-xs uppercase tracking-wide">Akun *</th>
                            <th class="px-3 py-2.5 text-left font-semibold text-slate-600 text-xs uppercase tracking-wide w-36">Proyek</th>
                            <th class="px-3 py-2.5 text-left font-semibold text-slate-600 text-xs uppercase tracking-wide w-48">Keterangan Baris</th>
                            <th class="px-3 py-2.5 text-right font-semibold text-blue-600 text-xs uppercase tracking-wide w-36">DEBIT (Rp)</th>
                            <th class="px-3 py-2.5 text-right font-semibold text-orange-600 text-xs uppercase tracking-wide w-36">KREDIT (Rp)</th>
                            <th class="px-3 py-2.5 w-8"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(line, i) in lines" :key="i">
                            <tr class="border-b border-slate-100 hover:bg-slate-50/50">
                                <td class="px-3 py-2 text-center text-xs text-slate-400 font-mono" x-text="i+1"></td>
                                <td class="px-3 py-2">
                                    <select :name="'lines['+i+'][account_id]'" x-model="line.account_id"
                                        class="w-full rounded-lg border border-slate-200 text-xs px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent bg-white" required>
                                        <option value="">— Pilih Akun —</option>
                                        @foreach($accounts as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->code }} — {{ $acc->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-3 py-2">
                                    <select :name="'lines['+i+'][project_id]'" x-model="line.project_id"
                                        class="w-full rounded-lg border border-slate-200 text-xs px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                                        <option value="">—</option>
                                        @foreach($projects as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-3 py-2">
                                    <input :name="'lines['+i+'][description]'" x-model="line.description" type="text"
                                        class="w-full rounded-lg border border-slate-200 text-xs px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-400"
                                        placeholder="Opsional"/>
                                </td>
                                <td class="px-3 py-2">
                                    <input :name="'lines['+i+'][debit]'" x-model="line.debit" type="number"
                                           step="1" min="0"
                                        class="w-full rounded-lg border border-blue-200 text-xs px-2 py-1.5 text-right focus:outline-none focus:ring-2 focus:ring-blue-400 bg-blue-50/30"
                                        placeholder="0"/>
                                </td>
                                <td class="px-3 py-2">
                                    <input :name="'lines['+i+'][credit]'" x-model="line.credit" type="number"
                                           step="1" min="0"
                                        class="w-full rounded-lg border border-orange-200 text-xs px-2 py-1.5 text-right focus:outline-none focus:ring-2 focus:ring-orange-400 bg-orange-50/30"
                                        placeholder="0"/>
                                </td>
                                <td class="px-3 py-2 text-center">
                                    <button type="button" @click="removeLine(i)" x-show="lines.length > 2"
                                            class="text-red-400 hover:text-red-600 p-0.5 rounded transition-colors">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-slate-300 bg-slate-50">
                            <td colspan="4" class="px-3 py-3 text-right text-sm font-extrabold text-slate-700 uppercase tracking-wide">
                                TOTAL
                            </td>
                            <td class="px-3 py-3 text-right font-extrabold text-base" :class="isBalanced ? 'text-emerald-700' : 'text-red-600'">
                                <span x-text="fmt(totalDebit)"></span>
                            </td>
                            <td class="px-3 py-3 text-right font-extrabold text-base" :class="isBalanced ? 'text-emerald-700' : 'text-red-600'">
                                <span x-text="fmt(totalCredit)"></span>
                            </td>
                            <td class="px-3 py-3 text-center">
                                <span x-show="isBalanced" class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Seimbang
                                </span>
                                <span x-show="!isBalanced && totalDebit > 0" class="inline-flex items-center gap-1 text-xs font-semibold text-red-600">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                                    </svg>
                                    Selisih: <span x-text="fmt(Math.abs(diff))"></span>
                                </span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="px-6 pb-6 flex items-center gap-3">
            <button type="submit"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-semibold text-white transition-colors"
                    :class="isBalanced ? 'bg-indigo-600 hover:bg-indigo-700 cursor-pointer' : 'bg-slate-400 cursor-not-allowed'"
                    :disabled="!isBalanced">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                Simpan Jurnal
            </button>
            <a href="{{ route('journal.index') }}"
               class="inline-flex items-center px-4 py-2 rounded-lg border border-slate-300 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                Batal
            </a>
            <span x-show="!isBalanced && totalDebit > 0" class="text-xs text-red-500 font-medium">
                ⚠ Tombol Simpan aktif saat Debit = Kredit
            </span>
        </div>
    </x-data-card>

</form>
</div>

@endsection
