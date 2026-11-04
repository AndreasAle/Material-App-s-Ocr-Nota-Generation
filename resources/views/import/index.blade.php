@extends('layouts.app')

@section('content')
<x-page-header title="Import Data dari Excel" subtitle="Format: .xlsx, .xls, .csv — maks. 5MB">
</x-page-header>



@if(session('import_errors') && count(session('import_errors')) > 0)
<div class="alert alert-warning mb-5">
    <div class="font-semibold mb-2 text-sm">Beberapa baris dilewati:</div>
    <ul class="text-xs space-y-1 max-h-40 overflow-y-auto">
        @foreach(session('import_errors') as $err)
            <li>• {{ $err }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Import Material --}}
    <x-data-card>
        <div class="px-5 py-4 space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 2.625c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125m16.5 2.625v6"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800">Import Material</h3>
                    <p class="text-xs text-slate-500">Tambah banyak material sekaligus</p>
                </div>
            </div>

            <div class="rounded-xl bg-indigo-50 border border-indigo-100 p-3 text-xs text-indigo-700">
                <strong>Kolom yang dibutuhkan:</strong><br>
                nama_material*, satuan*, kategori, harga_default, deskripsi
            </div>

            <a href="{{ route('import.template', 'materials') }}"
               class="inline-flex items-center gap-1.5 text-xs text-indigo-600 hover:underline font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                </svg>
                Download Template CSV
            </a>

            <form method="POST" action="{{ route('import.materials') }}" enctype="multipart/form-data" class="space-y-3">
                @csrf
                <div>
                    <label class="form-label-required text-xs">Pilih File</label>
                    <input type="file" name="file" accept=".xlsx,.xls,.csv" required class="form-input text-sm">
                </div>
                <button type="submit" class="btn-primary w-full justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/>
                    </svg>
                    Upload & Import Material
                </button>
            </form>
        </div>
    </x-data-card>

    {{-- Import RAB Items --}}
    <x-data-card>
        <div class="px-5 py-4 space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800">Import Item RAB</h3>
                    <p class="text-xs text-slate-500">Tambah item anggaran ke RAB Plan</p>
                </div>
            </div>

            <div class="rounded-xl bg-emerald-50 border border-emerald-100 p-3 text-xs text-emerald-700">
                <strong>Kolom yang dibutuhkan:</strong><br>
                nama_material*, satuan*, qty_rencana*, harga_rencana*, catatan
            </div>

            <a href="{{ route('import.template', 'rab-items') }}"
               class="inline-flex items-center gap-1.5 text-xs text-emerald-600 hover:underline font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                </svg>
                Download Template CSV
            </a>

            <form method="POST" action="{{ route('import.rab-items') }}" enctype="multipart/form-data" class="space-y-3">
                @csrf
                <div>
                    <label class="form-label-required text-xs">Pilih RAB Plan</label>
                    <select name="rab_plan_id" required class="form-select">
                        <option value="">— Pilih RAB Plan —</option>
                        @foreach($rabPlans as $rab)
                            <option value="{{ $rab->id }}">
                                {{ $rab->name }}
                                @if($rab->project) ({{ $rab->project->name }}) @endif
                            </option>
                        @endforeach
                    </select>
                    @error('rab_plan_id')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label-required text-xs">Pilih File</label>
                    <input type="file" name="file" accept=".xlsx,.xls,.csv" required class="form-input text-sm">
                </div>
                <button type="submit" class="btn-primary w-full justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/>
                    </svg>
                    Upload & Import Item RAB
                </button>
            </form>
        </div>
    </x-data-card>

    {{-- Import Transaksi --}}
    <x-data-card>
        <div class="px-5 py-4 space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800">Import Transaksi</h3>
                    <p class="text-xs text-slate-500">Import histori transaksi pembelian</p>
                </div>
            </div>

            <div class="rounded-xl bg-indigo-50 border border-indigo-100 p-3 text-xs text-indigo-700">
                <strong>Kolom yang dibutuhkan:</strong><br>
                nomor_transaksi, tanggal*, supplier, proyek, nama_material*, satuan*, qty*, harga_satuan*, catatan<br>
                <span class="text-indigo-500 mt-1 block">Satu transaksi bisa banyak baris (sama nomor_transaksi)</span>
            </div>

            <a href="{{ route('import.template', 'transactions') }}"
               class="inline-flex items-center gap-1.5 text-xs text-indigo-600 hover:underline font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                </svg>
                Download Template CSV
            </a>

            <form method="POST" action="{{ route('import.transactions') }}" enctype="multipart/form-data" class="space-y-3">
                @csrf
                <div>
                    <label class="form-label-required text-xs">Pilih File</label>
                    <input type="file" name="file" accept=".xlsx,.xls,.csv" required class="form-input text-sm">
                </div>
                <button type="submit" class="btn-primary w-full justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/>
                    </svg>
                    Upload & Import Transaksi
                </button>
            </form>
        </div>
    </x-data-card>

</div>

{{-- Tips --}}
<x-data-card title="Panduan Import" class="mt-6">
    <div class="px-5 pb-5 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm text-slate-600">
        <div>
            <div class="font-semibold text-slate-700 mb-1.5">Format File</div>
            <ul class="space-y-1 text-xs text-slate-500">
                <li>• Gunakan format <strong class="text-slate-700">.xlsx</strong> atau <strong class="text-slate-700">.csv</strong></li>
                <li>• Baris pertama harus berisi <strong class="text-slate-700">nama kolom</strong> persis seperti template</li>
                <li>• Kolom bertanda <strong class="text-slate-700">*</strong> wajib diisi</li>
                <li>• Kolom tanggal: format <strong class="text-slate-700">YYYY-MM-DD</strong> (contoh: 2025-06-01)</li>
            </ul>
        </div>
        <div>
            <div class="font-semibold text-slate-700 mb-1.5">Aturan Duplikasi</div>
            <ul class="space-y-1 text-xs text-slate-500">
                <li>• Material dengan nama yang sama akan <strong class="text-slate-700">dilewati</strong></li>
                <li>• Satuan dan kategori akan dibuat otomatis jika belum ada</li>
                <li>• Transaksi dengan nomor yang sama akan <strong class="text-slate-700">dilewati</strong></li>
                <li>• Supplier dan proyek dicari berdasarkan nama; jika tidak ada, supplier baru dibuat</li>
            </ul>
        </div>
    </div>
</x-data-card>
@endsection
