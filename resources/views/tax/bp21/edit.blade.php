@extends('layouts.app')

@section('content')
<x-page-header
    title="Edit BP21 — {{ $record->nama_penerima }}"
    subtitle="Ubah data bukti pemotongan PPh Pasal 21"
>
    <x-slot name="actions">
        <a href="{{ route('tax.bp21.show', $record->id) }}" class="btn-secondary">← Kembali</a>
    </x-slot>
</x-page-header>

@if($record->status === 'sudah_disetor')
<div class="alert alert-warning mb-5">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
    </svg>
    <span><strong>Perhatian:</strong> Bukti potong ini sudah disetor. Perubahan data mungkin memerlukan pembetulan SPT.</span>
</div>
@endif

<form method="POST" action="{{ route('tax.bp21.update', $record->id) }}"
      x-data="bp21Form()"
      class="max-w-3xl space-y-6">
    @csrf
    @method('PUT')

    {{-- Penerima --}}
    <x-data-card>
        <h3 class="text-sm font-semibold text-slate-700 mb-4">Data Penerima Penghasilan</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label class="form-label-required">Nama Penerima</label>
                <input type="text" name="nama_penerima" value="{{ old('nama_penerima', $record->nama_penerima) }}"
                       class="form-input" placeholder="Nama lengkap karyawan / tenaga ahli">
                @error('nama_penerima') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">NPWP Penerima (16-digit)</label>
                <input type="text" name="npwp_penerima" value="{{ old('npwp_penerima', $record->npwp_penerima) }}"
                       class="form-input font-mono" placeholder="NPWP 16-digit format Coretax">
                @error('npwp_penerima') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">NIK (jika tidak punya NPWP)</label>
                <input type="text" name="nik_penerima" value="{{ old('nik_penerima', $record->nik_penerima) }}"
                       class="form-input font-mono" placeholder="NIK 16-digit">
                @error('nik_penerima') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Status PTKP</label>
                <select name="status_ptkp" class="form-select">
                    <option value="">— Pilih (untuk pegawai) —</option>
                    @foreach(['TK/0','TK/1','TK/2','TK/3','K/0','K/1','K/2','K/3','HB/0','HB/1','HB/2','HB/3'] as $ptkp)
                    <option value="{{ $ptkp }}" {{ old('status_ptkp', $record->status_ptkp) === $ptkp ? 'selected' : '' }}>{{ $ptkp }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-slate-400 mt-1">TK=Tidak Kawin, K=Kawin, HB=Kawin+Istri Kerja</p>
            </div>
            <div>
                <label class="form-label">Masa Pajak</label>
                <input type="month" name="masa_pajak" value="{{ old('masa_pajak', $record->masa_pajak) }}"
                       class="form-input">
                @error('masa_pajak') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>
    </x-data-card>

    {{-- Kode Objek Pajak --}}
    <x-data-card>
        <h3 class="text-sm font-semibold text-slate-700 mb-4">Kode Objek Pajak & Tarif</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label class="form-label-required">Kode Objek Pajak</label>
                <select name="kode_objek_pajak" class="form-select" @change="onKopChange($event)">
                    <option value="">— Pilih KOP —</option>
                    @foreach($kopOptions as $kop)
                    <option value="{{ $kop['kode'] }}"
                            data-jenis_tarif="{{ $kop['jenis_tarif'] }}"
                            {{ old('kode_objek_pajak', $record->kode_objek_pajak) === $kop['kode'] ? 'selected' : '' }}>
                        {{ $kop['label'] }}
                    </option>
                    @endforeach
                </select>
                @error('kode_objek_pajak') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="form-label-required">Penghasilan Bruto</label>
                <input type="number" name="penghasilan_bruto" x-model="bruto" @input="hitungPph"
                       value="{{ old('penghasilan_bruto', $record->penghasilan_bruto) }}" step="1" min="0" class="form-input"
                       placeholder="0">
                @error('penghasilan_bruto') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="form-label">Deemed % (Bukan Pegawai)</label>
                <input type="number" name="deemed_percent" x-model="deemed" @input="hitungPph"
                       value="{{ old('deemed_percent', $record->deemed_percent) }}" step="0.01" min="0" max="100"
                       class="form-input" placeholder="50 (untuk tenaga ahli)">
                <p class="text-xs text-slate-400 mt-1">Isi 50 untuk tenaga ahli (Deemed = 50% × Bruto)</p>
            </div>

            <div>
                <label class="form-label-required">Tarif (%)</label>
                <input type="number" name="tarif" x-model="tarif" @input="hitungPph"
                       value="{{ old('tarif', $record->tarif) }}" step="0.01" min="0" class="form-input"
                       placeholder="Tarif TER/PS17">
                @error('tarif') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="form-label-required">PPh Dipotong</label>
                <input type="number" name="pph_dipotong" x-model="pph"
                       value="{{ old('pph_dipotong', $record->pph_dipotong) }}" step="1" min="0" class="form-input bg-slate-50"
                       placeholder="Otomatis terhitung">
                @error('pph_dipotong') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="sm:col-span-2 p-3 bg-green-50 border border-green-200 rounded-xl" x-show="bruto > 0">
                <div class="grid grid-cols-3 gap-4 text-sm">
                    <div>
                        <span class="text-green-600 text-xs">DPP (Bruto)</span>
                        <p class="font-mono font-bold text-green-700" x-text="'Rp ' + parseFloat(bruto||0).toLocaleString('id-ID')"></p>
                    </div>
                    <div>
                        <span class="text-green-600 text-xs">PPh Dipotong</span>
                        <p class="font-mono font-bold text-green-700" x-text="'Rp ' + parseFloat(pph||0).toLocaleString('id-ID')"></p>
                    </div>
                    <div>
                        <span class="text-green-600 text-xs">Diterima Bersih</span>
                        <p class="font-mono font-bold text-green-700" x-text="'Rp ' + (parseFloat(bruto||0) - parseFloat(pph||0)).toLocaleString('id-ID')"></p>
                    </div>
                </div>
            </div>
        </div>
    </x-data-card>

    {{-- Referensi Dokumen --}}
    <x-data-card>
        <h3 class="text-sm font-semibold text-slate-700 mb-4">Referensi Dokumen</h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="form-label">Jenis Dok Referensi</label>
                <select name="jenis_dok_referensi" class="form-select">
                    <option value="">— Pilih —</option>
                    @foreach(['KONTRAK','INVOICE','SPK','SK','KUITANSI','LAINNYA'] as $jenis)
                    <option value="{{ $jenis }}" {{ old('jenis_dok_referensi', $record->jenis_dok_referensi) === $jenis ? 'selected' : '' }}>{{ $jenis }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">No Dok Referensi</label>
                <input type="text" name="no_dok_referensi" value="{{ old('no_dok_referensi', $record->no_dok_referensi) }}"
                       class="form-input" placeholder="No kontrak / invoice">
            </div>
            <div>
                <label class="form-label">Tanggal Dok Referensi</label>
                <input type="date" name="tanggal_dok_referensi"
                       value="{{ old('tanggal_dok_referensi', $record->tanggal_dok_referensi?->format('Y-m-d')) }}"
                       class="form-input">
            </div>
            <div>
                <label class="form-label">Tanggal Pemotongan</label>
                <input type="date" name="tanggal_pemotongan"
                       value="{{ old('tanggal_pemotongan', $record->tanggal_pemotongan?->format('Y-m-d')) }}"
                       class="form-input">
            </div>
            <div>
                <label class="form-label">Project Terkait</label>
                <select name="project_id" class="form-select">
                    <option value="">— Pilih project (opsional) —</option>
                    @foreach($projects as $proj)
                    <option value="{{ $proj->id }}" {{ old('project_id', $record->project_id) == $proj->id ? 'selected' : '' }}>{{ $proj->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-3">
                <label class="form-label">Keterangan</label>
                <input type="text" name="keterangan" value="{{ old('keterangan', $record->keterangan) }}"
                       class="form-input" placeholder="Keterangan tambahan (opsional)">
            </div>
        </div>
    </x-data-card>

    <div class="flex justify-end gap-3">
        <a href="{{ route('tax.bp21.show', $record->id) }}" class="btn-secondary">Batal</a>
        <button type="submit" class="btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
            </svg>
            Simpan Perubahan
        </button>
    </div>
</form>

@push('scripts')
<script>
function bp21Form() {
    return {
        bruto: '{{ old("penghasilan_bruto", $record->penghasilan_bruto) }}',
        deemed: '{{ old("deemed_percent", $record->deemed_percent) }}',
        tarif: '{{ old("tarif", $record->tarif) }}',
        pph: '{{ old("pph_dipotong", $record->pph_dipotong) }}',

        onKopChange(e) {
            const opt = e.target.selectedOptions[0];
            if (!opt || !opt.value) return;
            const jenisTarif = opt.dataset.jenis_tarif || '';
            if (jenisTarif === 'PS17' && (opt.value === '21-100-04' || opt.value === '21-100-07' || opt.value === '21-100-08')) {
                this.deemed = 50;
            }
            this.hitungPph();
        },

        hitungPph() {
            const b = parseFloat(this.bruto) || 0;
            const d = parseFloat(this.deemed) || 0;
            const t = parseFloat(this.tarif) || 0;
            const dpp = d > 0 ? b * d / 100 : b;
            this.pph = Math.round(dpp * t / 100);
        },
    };
}
</script>
@endpush
@endsection
