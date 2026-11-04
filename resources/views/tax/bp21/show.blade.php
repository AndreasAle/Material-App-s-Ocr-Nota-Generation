@extends('layouts.app')

@section('content')
<x-page-header
    title="Detail BP21"
    subtitle="{{ $record->nama_penerima }} — {{ $record->masa_pajak }}"
>
    <x-slot name="actions">
        <a href="{{ route('tax.bp21.index', ['masa' => $record->masa_pajak]) }}" class="btn-secondary">← Kembali</a>
        @if($record->status === 'belum_disetor')
        <a href="{{ route('tax.bp21.edit', $record->id) }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/>
            </svg>
            Edit
        </a>
        <button x-data x-on:click="$dispatch('open-modal', 'konfirm-setor')" class="btn-primary">
            Konfirmasi Setor
        </button>
        @endif
    </x-slot>
</x-page-header>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 max-w-5xl">

    {{-- Main Data --}}
    <div class="lg:col-span-2 space-y-6">
        <x-data-card>
            <h3 class="text-sm font-semibold text-slate-700 mb-4">Data Penerima Penghasilan</h3>
            <dl class="grid grid-cols-2 gap-3 text-sm">
                <div>
                    <dt class="text-slate-400 text-xs">Nama Penerima</dt>
                    <dd class="font-medium mt-0.5">{{ $record->nama_penerima }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400 text-xs">NPWP Penerima</dt>
                    <dd class="font-mono mt-0.5">{{ $record->npwp_penerima ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400 text-xs">NIK</dt>
                    <dd class="font-mono mt-0.5">{{ $record->nik_penerima ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400 text-xs">Status PTKP</dt>
                    <dd class="font-mono mt-0.5">{{ $record->status_ptkp ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400 text-xs">Masa Pajak</dt>
                    <dd class="font-medium mt-0.5">{{ $record->masa_pajak }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400 text-xs">No Bukti Potong</dt>
                    <dd class="font-mono mt-0.5">{{ $record->no_bukti_potong ?? '—' }}</dd>
                </div>
            </dl>
        </x-data-card>

        <x-data-card>
            <h3 class="text-sm font-semibold text-slate-700 mb-4">Kalkulasi PPh 21</h3>
            <dl class="grid grid-cols-2 gap-3 text-sm">
                <div>
                    <dt class="text-slate-400 text-xs">Kode Objek Pajak</dt>
                    <dd class="font-mono mt-0.5">{{ $record->kode_objek_pajak }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400 text-xs">Nama Objek Pajak</dt>
                    <dd class="mt-0.5">{{ $record->nama_objek_pajak }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400 text-xs">Jenis Tarif</dt>
                    <dd class="mt-0.5"><span class="badge badge-blue">{{ $record->jenis_tarif }}</span></dd>
                </div>
                <div>
                    <dt class="text-slate-400 text-xs">Deemed %</dt>
                    <dd class="font-mono mt-0.5">{{ $record->deemed_percent ? $record->deemed_percent . '%' : '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400 text-xs">Penghasilan Bruto</dt>
                    <dd class="font-mono font-semibold mt-0.5">Rp {{ number_format($record->penghasilan_bruto, 0, ',', '.') }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400 text-xs">Tarif</dt>
                    <dd class="font-mono mt-0.5">{{ $record->tarif }}%</dd>
                </div>
            </dl>

            <div class="mt-4 p-3 bg-green-50 border border-green-200 rounded-xl">
                <div class="flex justify-between text-sm">
                    <span class="font-semibold text-green-700">PPh 21 Dipotong</span>
                    <span class="font-bold text-green-700 font-mono">Rp {{ number_format($record->pph_dipotong, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-sm mt-1">
                    <span class="text-slate-500">Diterima Bersih</span>
                    <span class="font-mono text-slate-600">Rp {{ number_format($record->getNilaiDibayar(), 0, ',', '.') }}</span>
                </div>

                @if(!$record->isValid())
                <div class="mt-2 text-xs text-rose-600 bg-rose-50 rounded p-2">
                    ⚠ PPh dipotong tidak sesuai kalkulasi bruto × tarif. Silakan verifikasi.
                </div>
                @endif
            </div>
        </x-data-card>

        {{-- Referensi Dokumen --}}
        @if($record->jenis_dok_referensi || $record->no_dok_referensi)
        <x-data-card>
            <h3 class="text-sm font-semibold text-slate-700 mb-4">Referensi Dokumen</h3>
            <dl class="grid grid-cols-3 gap-3 text-sm">
                <div>
                    <dt class="text-slate-400 text-xs">Jenis</dt>
                    <dd class="mt-0.5">{{ $record->jenis_dok_referensi ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400 text-xs">No Dokumen</dt>
                    <dd class="font-mono mt-0.5">{{ $record->no_dok_referensi ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400 text-xs">Tanggal</dt>
                    <dd class="mt-0.5">{{ $record->tanggal_dok_referensi?->format('d/m/Y') ?? '—' }}</dd>
                </div>
            </dl>
        </x-data-card>
        @endif
    </div>

    {{-- Sidebar --}}
    <div class="space-y-4">
        {{-- Status --}}
        <x-data-card>
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Status Setoran</h3>
            <span class="badge {{ $record->status === 'sudah_disetor' ? 'badge-green' : 'badge-yellow' }} text-sm">
                {{ $record->status === 'sudah_disetor' ? '✓ Sudah Disetor' : '⏳ Belum Disetor' }}
            </span>
            @if($record->tanggal_setor)
            <p class="text-xs text-slate-500 mt-2">Setor: {{ $record->tanggal_setor->format('d/m/Y') }}</p>
            @endif
            @if($record->ntpn)
            <p class="text-xs text-slate-500 mt-1">NTPN: <span class="font-mono">{{ $record->ntpn }}</span></p>
            @endif
        </x-data-card>

        {{-- Meta --}}
        <x-data-card>
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Informasi</h3>
            <dl class="space-y-2 text-xs">
                <div><dt class="text-slate-400">Sumber</dt>
                <dd>{{ $record->sumber === 'import_excel' ? 'Import Excel DJP' : 'Input Manual' }}</dd></div>
                <div><dt class="text-slate-400">Dibuat oleh</dt>
                <dd>{{ $record->creator?->name ?? '—' }}</dd></div>
                <div><dt class="text-slate-400">Dibuat pada</dt>
                <dd>{{ $record->created_at->format('d/m/Y H:i') }}</dd></div>
            </dl>
        </x-data-card>

        {{-- Delete --}}
        <form method="POST" action="{{ route('tax.bp21.destroy', $record->id) }}"
              onsubmit="return confirm('Hapus bukti potong ini?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn-danger w-full text-sm">Hapus Record</button>
        </form>
    </div>
</div>

{{-- Modal Konfirmasi Setor --}}
@if($record->status === 'belum_disetor')
<x-modal name="konfirm-setor" title="Konfirmasi Sudah Disetor">
    <form method="POST" action="{{ route('tax.bp21.konfirm', $record->id) }}">
        @csrf
        <div class="space-y-4 p-4">
            <div>
                <label class="form-label-required">Tanggal Setor</label>
                <input type="date" name="tanggal_setor" value="{{ today()->format('Y-m-d') }}" class="form-input" required>
            </div>
            <div>
                <label class="form-label">NTPN (Nomor Transaksi Penerimaan Negara)</label>
                <input type="text" name="ntpn" class="form-input font-mono" placeholder="Dari bukti setor Coretax">
                <p class="text-xs text-slate-400 mt-1">Opsional — bisa diisi nanti</p>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" x-on:click="$dispatch('close-modal', 'konfirm-setor')" class="btn-secondary">Batal</button>
                <button type="submit" class="btn-primary">Konfirmasi Setor</button>
            </div>
        </div>
    </form>
</x-modal>
@endif

@endsection
