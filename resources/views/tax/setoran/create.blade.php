@extends('layouts.app')

@section('content')
<x-page-header
    title="Catat Setoran Pajak"
    subtitle="Rekam pembayaran pajak ke kas negara — sistem otomatis isi KAP, KJS, dan jatuh tempo"
>
    <x-slot name="actions">
        <a href="{{ route('tax.setoran.index') }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
            </svg>
            Kembali
        </a>
    </x-slot>
</x-page-header>

<form method="POST" action="{{ route('tax.setoran.store') }}" x-data="setoranForm()" class="max-w-2xl">
    @csrf

    <x-data-card>
        <h3 class="text-sm font-semibold text-slate-700 mb-5">Data Setoran</h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

            <div>
                <label class="form-label-required">Periode</label>
                <input type="month" name="periode" x-model="periode"
                       value="{{ old('periode', now()->format('Y-m')) }}" class="form-input"
                       @change="updateInfo">
                @error('periode') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="form-label-required">Jenis Pajak</label>
                <select name="jenis_pajak" x-model="jenisPajak" class="form-select" @change="updateInfo">
                    <option value="">— Pilih jenis pajak —</option>
                    @foreach($jenisPajakList as $key => $label)
                        <option value="{{ $key }}" {{ old('jenis_pajak') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('jenis_pajak') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Auto-fill info (KAP/KJS/Jatuh Tempo) --}}
            <div class="sm:col-span-2" x-show="jenisPajak !== ''" x-cloak>
                <div class="p-4 rounded-xl bg-indigo-50 border border-indigo-200">
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div>
                            <p class="text-xs text-indigo-500 mb-1">KAP</p>
                            <p class="font-mono font-bold text-indigo-800 text-base" x-text="kap">—</p>
                        </div>
                        <div>
                            <p class="text-xs text-indigo-500 mb-1">KJS</p>
                            <p class="font-mono font-bold text-indigo-800 text-base" x-text="kjs">—</p>
                        </div>
                        <div>
                            <p class="text-xs text-indigo-500 mb-1">Jatuh Tempo</p>
                            <p class="font-bold text-indigo-800 text-sm" x-text="jatuhTempo">—</p>
                        </div>
                    </div>
                    <p class="text-xs text-indigo-600 text-center mt-2" x-text="uraian"></p>
                </div>
            </div>

            <div class="sm:col-span-2">
                <label class="form-label-required">Jumlah Setoran</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-medium">Rp</span>
                    <input type="number" name="jumlah" value="{{ old('jumlah') }}"
                           step="1" min="0" class="form-input pl-9">
                </div>
                @error('jumlah') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="sm:col-span-2">
                <label class="form-label">Keterangan</label>
                <input type="text" name="keterangan" value="{{ old('keterangan') }}"
                       class="form-input" placeholder="Keterangan opsional (mis: setoran PPN Mei 2026)">
            </div>
        </div>

        {{-- Referensi KAP/KJS --}}
        <div class="mt-5 p-3 bg-slate-50 border border-slate-200 rounded-xl">
            <p class="text-xs font-semibold text-slate-500 mb-2">Referensi KAP & KJS Resmi DJP:</p>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                <div class="p-2 bg-white rounded-lg border border-slate-200">
                    <p class="font-semibold text-slate-600">PPN Masa</p>
                    <p class="font-mono text-indigo-600">411211 / 100</p>
                </div>
                <div class="p-2 bg-white rounded-lg border border-slate-200">
                    <p class="font-semibold text-slate-600">PPh Pasal 21</p>
                    <p class="font-mono text-indigo-600">411121 / 100</p>
                </div>
                <div class="p-2 bg-white rounded-lg border border-slate-200">
                    <p class="font-semibold text-slate-600">PPh Pasal 23</p>
                    <p class="font-mono text-indigo-600">411124 / 104</p>
                </div>
                <div class="p-2 bg-white rounded-lg border border-slate-200">
                    <p class="font-semibold text-slate-600">PPh Final 4(2)</p>
                    <p class="font-mono text-indigo-600">411128 / 409</p>
                </div>
            </div>
        </div>
    </x-data-card>

    <div class="flex justify-end gap-3 mt-6">
        <a href="{{ route('tax.setoran.index') }}" class="btn-secondary">Batal</a>
        <button type="submit" class="btn-primary" :disabled="jenisPajak === ''">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
            </svg>
            Simpan Setoran
        </button>
    </div>
</form>

@push('scripts')
<script>
function setoranForm() {
    const KAP_KJS = {
        ppn:           { kap: '411211', kjs: '100', uraian: 'PPN Dalam Negeri', daysUntil: 25 },
        pph21:         { kap: '411121', kjs: '100', uraian: 'PPh Pasal 21 Masa', daysUntil: 10 },
        pph23:         { kap: '411124', kjs: '104', uraian: 'PPh Pasal 23 atas Jasa', daysUntil: 10 },
        pph_final_4_2: { kap: '411128', kjs: '409', uraian: 'PPh Final Jasa Konstruksi', daysUntil: 10 },
    };

    const BULAN = ['', 'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

    return {
        periode: '{{ old("periode", now()->format("Y-m")) }}',
        jenisPajak: '{{ old("jenis_pajak") }}',
        kap: '—', kjs: '—', uraian: '—', jatuhTempo: '—',

        updateInfo() {
            if (!this.jenisPajak || !this.periode) return;

            const cfg = KAP_KJS[this.jenisPajak];
            if (!cfg) return;

            this.kap   = cfg.kap;
            this.kjs   = cfg.kjs;
            this.uraian = cfg.uraian;

            const [y, m] = this.periode.split('-').map(Number);
            let nextM = m + 1, nextY = y;
            if (nextM > 12) { nextM = 1; nextY++; }
            const tgl = cfg.daysUntil;
            const jt  = new Date(nextY, nextM - 1, tgl);
            this.jatuhTempo = jt.getDate() + ' ' + BULAN[jt.getMonth() + 1] + ' ' + jt.getFullYear();
        },

        init() { this.updateInfo(); },
    };
}
</script>
@endpush
@endsection
