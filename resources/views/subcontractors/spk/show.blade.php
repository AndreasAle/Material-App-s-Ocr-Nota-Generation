@extends('layouts.app')
@section('content')

<x-page-header title="{{ $subcontract->spk_no }}" subtitle="{{ $subcontract->work_name }}">
    <x-slot name="actions">
        <a href="{{ route('subcontractors.spk.edit', $subcontract) }}" class="btn-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/>
            </svg>
            Edit SPK
        </a>
        <a href="{{ route('subcontractors.spk.index') }}" class="btn-secondary">← Kembali</a>
    </x-slot>
</x-page-header>

{{-- Summary Cards --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Nilai Kontrak</p>
        <p class="mt-2 text-xl font-extrabold text-slate-900">Rp {{ number_format($subcontract->contract_value, 0, ',', '.') }}</p>
    </div>
    <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wider text-emerald-500">Terbayar</p>
        <p class="mt-2 text-xl font-extrabold text-emerald-600">Rp {{ number_format($totalPaid, 0, ',', '.') }}</p>
        <p class="text-xs text-emerald-400 mt-0.5">{{ $subcontract->payment_pct }}%</p>
    </div>
    <div class="rounded-2xl border border-amber-100 bg-amber-50 p-4 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wider text-amber-500">Sisa Tagihan</p>
        <p class="mt-2 text-xl font-extrabold {{ $subcontract->remaining_value > 0 ? 'text-amber-600' : 'text-emerald-600' }}">
            Rp {{ number_format($subcontract->remaining_value, 0, ',', '.') }}
        </p>
    </div>
    <div class="rounded-2xl border border-indigo-100 bg-indigo-50 p-4 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wider text-indigo-500">Progress</p>
        <p class="mt-2 text-xl font-extrabold text-indigo-600">{{ $subcontract->progress_pct }}%</p>
        <div class="progress-bar mt-1.5">
            <div class="progress-bar-fill bg-indigo-500" style="width: {{ $subcontract->progress_pct }}%"></div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">

    {{-- Detail SPK --}}
    <x-data-card title="Detail SPK">
        <div class="px-5 py-4 space-y-3 text-sm">
            @foreach([
                ['Subkontraktor', $subcontract->subcontractor->name ?? '—'],
                ['Proyek', $subcontract->project->name ?? '—'],
                ['Tgl Mulai', $subcontract->start_date ? $subcontract->start_date->format('d M Y') : '—'],
                ['Tgl Selesai', $subcontract->end_date ? $subcontract->end_date->format('d M Y') : '—'],
                ['Status', $subcontract->status_label],
                ['Dibuat oleh', $subcontract->creator->name ?? '—'],
            ] as [$label, $val])
                <div class="flex items-center justify-between gap-3">
                    <span class="text-slate-500 shrink-0">{{ $label }}</span>
                    <span class="text-slate-800 font-medium text-right">{{ $val }}</span>
                </div>
            @endforeach
            @if($subcontract->scope_of_work)
                <div class="pt-2 border-t border-slate-100">
                    <p class="text-xs font-semibold text-slate-500 mb-1">Lingkup Pekerjaan</p>
                    <p class="text-slate-700 leading-relaxed">{{ $subcontract->scope_of_work }}</p>
                </div>
            @endif
        </div>
    </x-data-card>

    {{-- Form Pembayaran --}}
    <x-data-card title="Pembayaran" x-data="{ open: false }">
        <x-slot name="actions">
            <button @click="open = !open" class="btn-secondary text-xs">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Catat
            </button>
        </x-slot>

        {{-- Form catat pembayaran --}}
        <div x-show="open" x-collapse class="border-b border-slate-100 bg-slate-50/60">
            <div class="px-5 py-4">
                <form method="POST" action="{{ route('subcontractors.spk.payment-store', $subcontract) }}" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="form-label text-xs">Tanggal</label>
                            <input type="date" name="payment_date" value="{{ today()->format('Y-m-d') }}"
                                   class="form-input text-sm">
                        </div>
                        <div>
                            <label class="form-label-required text-xs">Jumlah (Rp)</label>
                            <input type="number" name="amount" min="1" step="1000"
                                   class="form-input text-sm">
                        </div>
                        <div>
                            <label class="form-label text-xs">Metode</label>
                            <select name="payment_method" class="form-select text-sm">
                                <option value="transfer">Transfer Bank</option>
                                <option value="tunai">Tunai</option>
                                <option value="cek">Cek / Giro</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label text-xs">No. Referensi</label>
                            <input type="text" name="reference_no"
                                   class="form-input text-sm"
                                   placeholder="No. kwitansi...">
                        </div>
                        <div class="col-span-2">
                            <label class="form-label text-xs">Catatan</label>
                            <input type="text" name="notes" class="form-input text-sm">
                        </div>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="open = false" class="btn-secondary text-xs">Batal</button>
                        <button type="submit" class="btn-primary text-xs">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Daftar Pembayaran --}}
        <div class="divide-y divide-slate-100 text-sm">
            @forelse($subcontract->payments as $pay)
                <div class="px-5 py-3 flex items-center gap-3">
                    <div class="h-8 w-8 shrink-0 flex items-center justify-center rounded-xl bg-emerald-50">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-semibold text-slate-800">Rp {{ number_format($pay->amount, 0, ',', '.') }}</div>
                        <div class="text-xs text-slate-400 truncate">
                            {{ $pay->payment_date->format('d M Y') }}
                            @if($pay->payment_method) · {{ $pay->payment_method }} @endif
                            @if($pay->reference_no) · {{ $pay->reference_no }} @endif
                        </div>
                    </div>
                    <form method="POST" action="{{ route('subcontractors.spk.payment-destroy', $pay) }}"
                          onsubmit="return confirm('Hapus pembayaran ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="p-1.5 rounded-lg text-rose-400 hover:text-rose-600 hover:bg-rose-50 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                            </svg>
                        </button>
                    </form>
                </div>
            @empty
                <div class="px-5 py-8 text-center text-slate-400 text-sm">
                    Belum ada pembayaran dicatat.
                </div>
            @endforelse
        </div>
    </x-data-card>

</div>
@endsection
