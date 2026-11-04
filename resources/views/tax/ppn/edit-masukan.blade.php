@extends('layouts.app')

@section('content')
<x-page-header
    title="Lengkapi Faktur Pajak Masukan"
    subtitle="Isi nomor faktur dan tanggal faktur dari supplier"
>
    <x-slot name="actions">
        <a href="{{ route('tax.ppn.index', ['tab' => 'masukan', 'periode' => $masukan->periode]) }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
            </svg>
            Kembali
        </a>
    </x-slot>
</x-page-header>

{{-- Notice: draft otomatis --}}
@if(!$masukan->no_faktur)
<div class="flex items-start gap-3 p-4 rounded-2xl bg-amber-50 border border-amber-200 mb-6 max-w-3xl">
    <div class="flex-shrink-0 w-8 h-8 bg-amber-100 rounded-xl flex items-center justify-center mt-0.5">
        <svg class="h-4 w-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/>
        </svg>
    </div>
    <div class="flex-1">
        <p class="text-sm font-semibold text-amber-800">Draft Otomatis — Perlu Dilengkapi</p>
        <p class="text-xs text-amber-700 mt-0.5">
            Record ini dibuat otomatis saat transaksi pembelian disimpan.
            Lengkapi <strong>Nomor Faktur Pajak</strong> dan <strong>Tanggal Faktur</strong> dari supplier.
        </p>
    </div>
</div>
@endif

<form method="POST" action="{{ route('tax.ppn.masukan.update', $masukan) }}" x-data="ppnEditForm()" class="max-w-3xl">
    @csrf
    @method('PUT')

    <div class="space-y-6">

        {{-- Link ke Transaksi (opsional) --}}
        <x-data-card>
            <h3 class="text-sm font-semibold text-slate-700 mb-4">Sumber Data</h3>
            <div>
                <label class="form-label">Transaksi Pembelian Terkait</label>
                <select name="purchase_transaction_id" class="form-select" @change="prefillFromTx($event)">
                    <option value="">— Pilih transaksi (opsional) —</option>
                    @foreach($transactions as $tx)
                        <option value="{{ $tx->id }}"
                                data-supplier="{{ $tx->supplier->name ?? '' }}"
                                data-npwp="{{ $tx->supplier->npwp ?? '' }}"
                                data-total="{{ $tx->grand_total }}"
                                {{ old('purchase_transaction_id', $masukan->purchase_transaction_id) == $tx->id ? 'selected' : '' }}>
                            {{ $tx->transaction_no ?? '#'.$tx->id }} — {{ $tx->transaction_date?->format('d/m/Y') }}
                            @if($tx->supplier) ({{ $tx->supplier->name }}) @endif
                        </option>
                    @endforeach
                </select>
            </div>
        </x-data-card>

        {{-- Data Faktur --}}
        <x-data-card>
            <h3 class="text-sm font-semibold text-slate-700 mb-5">Data Faktur Pajak Masukan</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="form-label-required">Nomor Faktur Pajak</label>
                    <input type="text" name="no_faktur"
                           value="{{ old('no_faktur', $masukan->no_faktur) }}"
                           class="form-input font-mono" placeholder="Mis: 010.000-24.00000001" autofocus>
                    @error('no_faktur') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label-required">Tanggal Faktur</label>
                    <input type="date" name="tanggal_faktur"
                           value="{{ old('tanggal_faktur', $masukan->tanggal_faktur?->format('Y-m-d')) }}"
                           class="form-input" @change="updatePeriode($event)">
                    @error('tanggal_faktur') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label-required">Nama Penjual (PKP)</label>
                    <input type="text" name="nama_penjual" x-model="namaPenjual"
                           value="{{ old('nama_penjual', $masukan->nama_penjual) }}"
                           class="form-input" placeholder="Nama perusahaan penjual">
                    @error('nama_penjual') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label">NPWP Penjual</label>
                    <input type="text" name="npwp_penjual" x-model="npwpPenjual"
                           value="{{ old('npwp_penjual', $masukan->npwp_penjual) }}"
                           class="form-input font-mono" placeholder="XX.XXX.XXX.X-XXX.XXX">
                    @error('npwp_penjual') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label-required">Periode</label>
                    <input type="month" name="periode" x-model="periode"
                           value="{{ old('periode', $masukan->periode) }}"
                           class="form-input">
                    @error('periode') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label-required">Status Faktur</label>
                    <select name="status" class="form-select">
                        <option value="normal" {{ old('status', $masukan->status) === 'normal' ? 'selected' : '' }}>Normal</option>
                        <option value="retur"  {{ old('status', $masukan->status) === 'retur'  ? 'selected' : '' }}>Nota Retur</option>
                    </select>
                </div>

                {{-- DPP & PPN --}}
                <div>
                    <label class="form-label-required">DPP (Dasar Pengenaan Pajak)</label>
                    <input type="number" name="dpp" x-model="dpp" @input="hitungPpn"
                           value="{{ old('dpp', $masukan->dpp) }}" step="1" min="0"
                           class="form-input" placeholder="0">
                    @error('dpp') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label-required">PPN ({{ $tarifPpn }}%)</label>
                    <input type="number" name="ppn" x-model="ppn"
                           value="{{ old('ppn', $masukan->ppn) }}" step="1" min="0"
                           class="form-input bg-slate-50" placeholder="Otomatis terhitung">
                    @error('ppn') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    <p class="text-xs text-slate-400 mt-1">Dapat diedit manual jika berbeda</p>
                </div>

                <div class="sm:col-span-2 p-3 bg-indigo-50 border border-indigo-200 rounded-xl" x-show="dpp > 0">
                    <div class="flex justify-between text-sm">
                        <span class="text-indigo-600">Total Faktur (DPP + PPN)</span>
                        <span class="font-bold text-indigo-700" x-text="'Rp ' + (parseFloat(dpp||0) + parseFloat(ppn||0)).toLocaleString('id-ID')"></span>
                    </div>
                </div>

                <div class="sm:col-span-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="dapat_dikreditkan" value="0">
                        <input type="checkbox" name="dapat_dikreditkan" value="1"
                               {{ old('dapat_dikreditkan', $masukan->dapat_dikreditkan ? '1' : '0') == '1' ? 'checked' : '' }}
                               class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm text-slate-700">
                            Dapat dikreditkan sebagai pajak masukan
                            <span class="text-xs text-slate-400">(hapus centang jika tidak memenuhi syarat KUP)</span>
                        </span>
                    </label>
                </div>

                <div class="sm:col-span-2">
                    <label class="form-label">Keterangan</label>
                    <input type="text" name="keterangan"
                           value="{{ old('keterangan', $masukan->keterangan) }}"
                           class="form-input" placeholder="Keterangan opsional">
                </div>
            </div>
        </x-data-card>

        <div class="flex justify-end gap-3">
            <a href="{{ route('tax.ppn.index', ['tab' => 'masukan', 'periode' => $masukan->periode]) }}" class="btn-secondary">Batal</a>
            <button type="submit" class="btn-primary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                </svg>
                Simpan Perubahan
            </button>
        </div>
    </div>
</form>

@push('scripts')
<script>
function ppnEditForm() {
    return {
        dpp: '{{ old("dpp", $masukan->dpp) }}',
        ppn: '{{ old("ppn", $masukan->ppn) }}',
        periode: '{{ old("periode", $masukan->periode) }}',
        namaPenjual: '{{ old("nama_penjual", $masukan->nama_penjual) }}',
        npwpPenjual: '{{ old("npwp_penjual", $masukan->npwp_penjual) }}',
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

        prefillFromTx(e) {
            const opt = e.target.selectedOptions[0];
            if (!opt || !opt.value) return;
            this.namaPenjual = opt.dataset.supplier || '';
            this.npwpPenjual = opt.dataset.npwp    || '';
            const total = parseFloat(opt.dataset.total) || 0;
            this.dpp = Math.round(total / (1 + this.tarifPpn / 100));
            this.hitungPpn();
        },
    };
}
</script>
@endpush
@endsection
