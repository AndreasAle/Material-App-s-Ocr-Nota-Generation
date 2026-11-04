@extends('layouts.app')

@section('content')
<x-page-header
    title="Input Faktur Pajak Keluaran"
    subtitle="Catat faktur pajak yang diterbitkan kepada pembeli (PPN 12% — PMK 131/2024) — Nomor NSFP dari e-Faktur DJP"
>
    <x-slot name="actions">
        <a href="{{ route('tax.ppn.index', ['tab' => 'keluaran']) }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
            </svg>
            Kembali
        </a>
    </x-slot>
</x-page-header>

<div class="alert alert-warning mb-6">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
    </svg>
    <span>
        <strong>Faktur Pajak Keluaran</strong> hanya bisa dibuat oleh PKP. Nomor faktur (NSFP) diperoleh dari aplikasi <strong>e-Faktur Coretax DJP</strong>.
        Catat di sini untuk dokumentasi internal dan rekonsiliasi SPT Masa PPN.
    </span>
</div>

<form method="POST" action="{{ route('tax.ppn.keluaran.store') }}" x-data="ppnKForm()" class="max-w-3xl">
    @csrf

    <div class="space-y-6">

        {{-- Link ke Invoice --}}
        <x-data-card>
            <h3 class="text-sm font-semibold text-slate-700 mb-4">Invoice Terkait (Opsional)</h3>
            <div>
                <label class="form-label">Invoice</label>
                <select name="invoice_id" class="form-select" @change="prefillFromInvoice($event)">
                    <option value="">— Pilih invoice (opsional) —</option>
                    @foreach($invoices as $inv)
                        <option value="{{ $inv->id }}"
                                data-total="{{ $inv->grand_total ?? $inv->total_amount ?? 0 }}"
                                data-no="{{ $inv->invoice_no ?? '' }}"
                                {{ old('invoice_id') == $inv->id ? 'selected' : '' }}>
                            {{ $inv->invoice_no ?? '#'.$inv->id }} — {{ $inv->invoice_date?->format('d/m/Y') }}
                            @if($inv->project) [{{ $inv->project->name }}] @endif
                        </option>
                    @endforeach
                </select>
            </div>
        </x-data-card>

        {{-- Data Faktur --}}
        <x-data-card>
            <h3 class="text-sm font-semibold text-slate-700 mb-5">Data Faktur Pajak Keluaran</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="form-label-required">Nomor Seri Faktur Pajak (NSFP)</label>
                    <input type="text" name="no_faktur" value="{{ old('no_faktur') }}"
                           class="form-input font-mono" placeholder="Mis: 010.000-24.00000001">
                    @error('no_faktur') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    <p class="text-xs text-slate-400 mt-1">Dari e-Faktur Coretax DJP</p>
                </div>

                <div>
                    <label class="form-label-required">Tanggal Faktur</label>
                    <input type="date" name="tanggal_faktur" value="{{ old('tanggal_faktur', today()->format('Y-m-d')) }}"
                           class="form-input" @change="updatePeriode($event)">
                    @error('tanggal_faktur') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label-required">Nama Pembeli (Lawan Transaksi)</label>
                    <input type="text" name="nama_pembeli" x-model="namaPembeli" value="{{ old('nama_pembeli') }}"
                           class="form-input" placeholder="Nama instansi/perusahaan pembeli">
                    @error('nama_pembeli') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label">NPWP Pembeli</label>
                    <input type="text" name="npwp_pembeli" x-model="npwpPembeli" value="{{ old('npwp_pembeli') }}"
                           class="form-input font-mono" placeholder="XX.XXX.XXX.X-XXX.XXX">
                    @error('npwp_pembeli') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="form-label">Alamat Pembeli</label>
                    <input type="text" name="alamat_pembeli" value="{{ old('alamat_pembeli') }}"
                           class="form-input" placeholder="Alamat pembeli sesuai faktur">
                    @error('alamat_pembeli') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label-required">Periode</label>
                    <input type="month" name="periode" x-model="periode" value="{{ old('periode', now()->format('Y-m')) }}"
                           class="form-input">
                    @error('periode') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label-required">Status</label>
                    <select name="status" class="form-select">
                        <option value="normal" {{ old('status', 'normal') === 'normal' ? 'selected' : '' }}>Normal</option>
                        <option value="retur"  {{ old('status') === 'retur'  ? 'selected' : '' }}>Nota Retur (FPR)</option>
                    </select>
                </div>

                {{-- DPP & PPN --}}
                <div>
                    <label class="form-label-required">DPP (Dasar Pengenaan Pajak)</label>
                    <input type="number" name="dpp" x-model="dpp" @input="hitungPpn"
                           value="{{ old('dpp') }}" step="1" min="0" class="form-input" placeholder="0">
                    @error('dpp') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label-required">PPN ({{ $tarifPpn }}%)</label>
                    <input type="number" name="ppn" x-model="ppn"
                           value="{{ old('ppn') }}" step="1" min="0" class="form-input bg-slate-50" placeholder="Otomatis">
                    @error('ppn') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2 p-3 bg-indigo-50 border border-indigo-200 rounded-xl" x-show="dpp > 0">
                    <div class="flex justify-between text-sm">
                        <span class="text-indigo-600">Total Faktur (DPP + PPN)</span>
                        <span class="font-bold text-indigo-700" x-text="'Rp ' + (parseFloat(dpp||0) + parseFloat(ppn||0)).toLocaleString('id-ID')"></span>
                    </div>
                </div>

                <div class="sm:col-span-2">
                    <label class="form-label">Keterangan</label>
                    <input type="text" name="keterangan" value="{{ old('keterangan') }}"
                           class="form-input" placeholder="Keterangan opsional">
                </div>
            </div>
        </x-data-card>

        <div class="flex justify-end gap-3">
            <a href="{{ route('tax.ppn.index', ['tab' => 'keluaran']) }}" class="btn-secondary">Batal</a>
            <button type="submit" class="btn-primary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                </svg>
                Simpan Faktur Keluaran
            </button>
        </div>
    </div>
</form>

@push('scripts')
<script>
function ppnKForm() {
    return {
        dpp: '{{ old("dpp") }}',
        ppn: '{{ old("ppn") }}',
        periode: '{{ old("periode", now()->format("Y-m")) }}',
        namaPembeli: '{{ old("nama_pembeli") }}',
        npwpPembeli: '{{ old("npwp_pembeli") }}',
        tarifPpn: {{ $tarifPpn }},

        hitungPpn() {
            const d = parseFloat(this.dpp) || 0;
            this.ppn = Math.round(d * this.tarifPpn / 100);
        },

        updatePeriode(e) {
            const d = new Date(e.target.value);
            if (!isNaN(d)) {
                this.periode = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0');
            }
        },

        prefillFromInvoice(e) {
            const opt = e.target.selectedOptions[0];
            if (!opt || !opt.value) return;
            const total = parseFloat(opt.dataset.total) || 0;
            this.dpp = Math.round(total / (1 + this.tarifPpn / 100));
            this.hitungPpn();
        },
    };
}
</script>
@endpush
@endsection
