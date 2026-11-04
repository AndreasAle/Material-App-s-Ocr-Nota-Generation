@extends('layouts.app')

@section('content')

<x-page-header title="Barang Masuk" subtitle="Foto nota dari supplier. Aplikasi membaca isinya, Anda cek, lalu stok bertambah otomatis." />

<x-help title="Langkah barang masuk (3 menit)" :open="true">
    <li><b>Foto nota</b> supplier dengan jelas (rata, tidak miring, tidak gelap).</li>
    <li>Tunggu aplikasi membaca nota, lalu <b>cek nama barang, jumlah & harga</b>. Perbaiki bila ada yang salah.</li>
    <li>Tekan <b>Masukkan ke Transaksi</b>, pastikan centang <b>“Barang sudah diterima”</b> aktif, lalu <b>Simpan</b>. Stok langsung bertambah.</li>
</x-help>

<div class="mb-4 flex justify-end">
{{-- Mode Switcher --}}
    <div class="flex items-center gap-1 bg-slate-100 rounded-2xl p-1 flex-shrink-0">
        <div class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold shadow-sm">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
            </svg>
            1 Nota
        </div>
        <a href="{{ route('purchase-scan-batches.create') }}"
           class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-slate-500 text-xs font-semibold hover:bg-white hover:text-slate-700 hover:shadow-sm transition-all">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6.878V6a2.25 2.25 0 012.25-2.25h7.5A2.25 2.25 0 0118 6v.878m-12 0c.235-.083.487-.128.75-.128h10.5c.263 0 .515.045.75.128m-12 0A2.25 2.25 0 004.5 9v.878m13.5-3A2.25 2.25 0 0119.5 9v.878m0 0a2.246 2.246 0 00-.75-.128H5.25c-.263 0-.515.045-.75.128m15 0A2.25 2.25 0 0121 12v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6c0-.98.626-1.813 1.5-2.122"/>
            </svg>
            Beberapa Nota
        </a>
    </div>
</div>

{{-- Step Indicator --}}
<div class="hidden sm:flex items-center gap-0 mb-6 bg-white rounded-2xl border border-slate-200 shadow-sm px-5 py-3.5 overflow-x-auto">
    <div class="flex items-center gap-3 flex-shrink-0">
        <div class="w-7 h-7 rounded-full bg-emerald-600 flex items-center justify-center text-white text-xs font-black">1</div>
        <div>
            <p class="text-xs font-bold text-emerald-700">Upload Nota</p>
            <p class="text-[11px] text-slate-400">Pilih atau drag file</p>
        </div>
    </div>
    <div class="flex-1 min-w-[40px] h-px bg-slate-200 mx-3"></div>
    <div class="flex items-center gap-3 flex-shrink-0">
        <div class="w-7 h-7 rounded-full bg-slate-200 flex items-center justify-center text-slate-500 text-xs font-black">2</div>
        <div>
            <p class="text-xs font-semibold text-slate-500">Preview & Cek</p>
            <p class="text-[11px] text-slate-400">Verifikasi file</p>
        </div>
    </div>
    <div class="flex-1 min-w-[40px] h-px bg-slate-200 mx-3"></div>
    <div class="flex items-center gap-3 flex-shrink-0">
        <div class="w-7 h-7 rounded-full bg-slate-200 flex items-center justify-center text-slate-500 text-xs font-black">3</div>
        <div>
            <p class="text-xs font-semibold text-slate-500">Proses OCR</p>
            <p class="text-[11px] text-slate-400">AI baca nota</p>
        </div>
    </div>
    <div class="flex-1 min-w-[40px] h-px bg-slate-200 mx-3"></div>
    <div class="flex items-center gap-3 flex-shrink-0">
        <div class="w-7 h-7 rounded-full bg-slate-200 flex items-center justify-center text-slate-500 text-xs font-black">4</div>
        <div>
            <p class="text-xs font-semibold text-slate-500">Draft Transaksi</p>
            <p class="text-[11px] text-slate-400">Review & simpan</p>
        </div>
    </div>
</div>

{{-- Main Form Card --}}
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <form
        id="scan-form"
        method="POST"
        action="{{ route('purchase-scans.store') }}"
        enctype="multipart/form-data"
    >
        @csrf

        <div class="grid grid-cols-1 xl:grid-cols-12">

            {{-- ===== LEFT: UPLOAD ZONE ===== --}}
            <div class="xl:col-span-7 p-4 sm:p-6 border-b border-slate-100 xl:border-b-0 xl:border-r">

                {{-- Upload Zone Header --}}
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-7 h-7 rounded-lg bg-emerald-100 flex items-center justify-center flex-shrink-0">
                        <svg class="h-3.5 w-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Upload File Nota</h3>
                        <p class="text-xs text-slate-400">JPG, PNG, WEBP, atau PDF &bull; Maksimal 10MB</p>
                    </div>
                </div>

                {{-- Foto langsung dari kamera (HP / webcam) atau pilih file --}}
                <x-camera-capture target="receipt" class="mb-3" />
                <p class="mb-3 hidden text-center text-xs font-semibold uppercase tracking-wider text-charcoal-400 sm:block">atau seret file nota ke sini</p>

                {{-- Dropzone --}}
                <div
                    id="dropzone"
                    class="group relative rounded-2xl border-2 border-dashed border-slate-300 bg-gradient-to-br from-slate-50 to-white transition-all duration-300 hover:border-emerald-400 hover:bg-emerald-50/30 hover:shadow-sm cursor-pointer"
                >
                    <input
                        id="receipt"
                        type="file"
                        name="receipt"
                        accept=".jpg,.jpeg,.png,.webp,.pdf"
                        class="absolute inset-0 z-10 h-full w-full cursor-pointer opacity-0"
                        required
                    >

                    <div class="pointer-events-none flex min-h-[220px] sm:min-h-[260px] flex-col items-center justify-center px-6 py-8 sm:py-10 text-center">
                        {{-- Scanner icon --}}
                        <div class="relative mb-5">
                            <div class="w-16 h-16 rounded-2xl bg-slate-900 flex items-center justify-center shadow-lg group-hover:bg-emerald-700 transition-colors duration-300">
                                <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75z"/>
                                </svg>
                            </div>
                            {{-- Scan line decoration --}}
                            <div class="absolute -inset-1 rounded-2xl border border-emerald-200 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        </div>

                        <h3 class="text-base font-bold text-slate-900 mb-1">
                            <span class="sm:hidden">Ketuk untuk pilih file nota</span><span class="hidden sm:inline">Drag &amp; Drop File Nota</span>
                        </h3>
                        <p class="text-sm text-slate-400 max-w-xs leading-relaxed mb-5">
                            <span class="sm:hidden">Foto yang sudah ada di galeri, atau PDF</span><span class="hidden sm:inline">atau klik area ini untuk pilih file dari komputer Anda</span>
                        </p>

                        {{-- Format badges --}}
                        <div class="flex flex-wrap items-center justify-center gap-2 mb-5">
                            @foreach(['JPG','PNG','WEBP','PDF'] as $fmt)
                            <span class="rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-bold text-slate-500 shadow-sm">{{ $fmt }}</span>
                            @endforeach
                        </div>

                        <div class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-sm group-hover:bg-emerald-700 transition-colors duration-300">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                            </svg>
                            Pilih File Nota
                        </div>
                    </div>
                </div>

                @error('receipt')
                <div class="mt-3 flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    <svg class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                    {{ $message }}
                </div>
                @enderror

                {{-- File Info Box --}}
                <div id="file-info-box" class="mt-4 hidden rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2.5 mb-3">
                                <div class="w-8 h-8 rounded-xl bg-emerald-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-emerald-800">File siap diproses</p>
                                    <p class="text-xs text-emerald-600">Periksa preview sebelum melanjutkan scan OCR</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs">
                                <div class="bg-white rounded-xl border border-emerald-200 px-3 py-2">
                                    <p class="text-emerald-600 font-semibold uppercase tracking-wide mb-0.5">Nama</p>
                                    <p id="file-name" class="font-bold text-slate-800 truncate">-</p>
                                </div>
                                <div class="bg-white rounded-xl border border-emerald-200 px-3 py-2">
                                    <p class="text-emerald-600 font-semibold uppercase tracking-wide mb-0.5">Ukuran</p>
                                    <p id="file-size" class="font-bold text-slate-800">-</p>
                                </div>
                                <div class="bg-white rounded-xl border border-emerald-200 px-3 py-2">
                                    <p class="text-emerald-600 font-semibold uppercase tracking-wide mb-0.5">Format</p>
                                    <p id="file-type" class="font-bold text-slate-800">-</p>
                                </div>
                            </div>
                        </div>
                        <button
                            type="button"
                            id="clear-file"
                            class="flex-shrink-0 inline-flex items-center gap-1.5 rounded-xl border border-emerald-300 bg-white px-3 py-2 text-xs font-semibold text-emerald-700 hover:bg-emerald-50 transition-all"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            Hapus File
                        </button>
                    </div>
                </div>

                {{-- Tips Panel --}}
                <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <div class="flex items-center gap-2 mb-3">
                        <svg class="h-4 w-4 text-amber-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.493 1.509 1.333 1.509 2.316V18"/>
                        </svg>
                        <h4 class="text-sm font-bold text-slate-700">Tips Foto Nota yang Bagus</h4>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="rounded-xl border border-emerald-200 bg-white p-3">
                            <div class="flex items-center gap-1.5 mb-2">
                                <span class="w-5 h-5 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="h-3 w-3 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                </span>
                                <p class="text-xs font-bold text-emerald-700">Yang Disarankan</p>
                            </div>
                            <ul class="space-y-1.5 text-xs text-slate-600">
                                <li class="flex items-start gap-1.5"><span class="w-1 h-1 rounded-full bg-emerald-400 flex-shrink-0 mt-1.5"></span>Foto di tempat terang dan tidak gelap</li>
                                <li class="flex items-start gap-1.5"><span class="w-1 h-1 rounded-full bg-emerald-400 flex-shrink-0 mt-1.5"></span>Nota terlihat penuh dari atas sampai bawah</li>
                                <li class="flex items-start gap-1.5"><span class="w-1 h-1 rounded-full bg-emerald-400 flex-shrink-0 mt-1.5"></span>Posisi kamera tegak dan tidak miring</li>
                                <li class="flex items-start gap-1.5"><span class="w-1 h-1 rounded-full bg-emerald-400 flex-shrink-0 mt-1.5"></span>Teks nominal dan item tidak blur</li>
                            </ul>
                        </div>
                        <div class="rounded-xl border border-rose-200 bg-white p-3">
                            <div class="flex items-center gap-1.5 mb-2">
                                <span class="w-5 h-5 rounded-full bg-rose-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="h-3 w-3 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                </span>
                                <p class="text-xs font-bold text-rose-700">Hindari Kondisi Ini</p>
                            </div>
                            <ul class="space-y-1.5 text-xs text-slate-600">
                                <li class="flex items-start gap-1.5"><span class="w-1 h-1 rounded-full bg-rose-400 flex-shrink-0 mt-1.5"></span>Bayangan menutupi isi nota</li>
                                <li class="flex items-start gap-1.5"><span class="w-1 h-1 rounded-full bg-rose-400 flex-shrink-0 mt-1.5"></span>Tepi nota terpotong</li>
                                <li class="flex items-start gap-1.5"><span class="w-1 h-1 rounded-full bg-rose-400 flex-shrink-0 mt-1.5"></span>Gambar pecah atau resolusi terlalu kecil</li>
                                <li class="flex items-start gap-1.5"><span class="w-1 h-1 rounded-full bg-rose-400 flex-shrink-0 mt-1.5"></span>Banyak lipatan di area nominal / item</li>
                            </ul>
                        </div>
                    </div>
                    <div class="mt-3 flex items-start gap-2 rounded-xl border border-indigo-200 bg-indigo-50 px-3 py-2.5">
                        <svg class="h-4 w-4 text-indigo-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
                        </svg>
                        <p class="text-xs text-indigo-800 leading-relaxed">
                            <span class="font-bold">Catatan:</span> Hasil scan OCR bersifat <span class="font-semibold">draft</span>. Admin tetap perlu memeriksa item, qty, harga, supplier, dan total sebelum diubah menjadi transaksi pembelian final.
                        </p>
                    </div>
                </div>
            </div>

            {{-- ===== RIGHT: PREVIEW PANEL ===== --}}
            <div class="xl:col-span-5 bg-slate-50/60 p-6">
                <div class="flex items-start justify-between gap-3 mb-4">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-slate-200 flex items-center justify-center flex-shrink-0">
                            <svg class="h-3.5 w-3.5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-700">Preview Nota</h3>
                            <p class="text-xs text-slate-400">Verifikasi sebelum scan</p>
                        </div>
                    </div>
                    <div id="preview-badge" class="hidden inline-flex items-center gap-1.5 rounded-full bg-emerald-600 px-3 py-1 text-xs font-bold text-white shadow-sm">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-300 animate-pulse"></span>
                        Siap Diproses
                    </div>
                </div>

                {{-- Empty State --}}
                <div id="empty-preview" class="flex min-h-[400px] items-center justify-center rounded-2xl border-2 border-dashed border-slate-300 bg-white px-6 py-10 text-center">
                    <div>
                        <div class="mx-auto mb-4 w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center">
                            <svg class="h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-slate-600 mb-1">Belum ada file dipilih</p>
                        <p class="text-xs text-slate-400 leading-relaxed max-w-[220px] mx-auto">
                            Preview gambar atau PDF nota akan muncul di sini setelah file dipilih
                        </p>
                    </div>
                </div>

                {{-- Preview Wrapper --}}
                <div id="preview-wrapper" class="hidden">
                    <div id="image-preview-container" class="hidden">
                        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                            <div class="border-b border-slate-200 bg-slate-50 px-4 py-2.5 flex items-center gap-2">
                                <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Preview Gambar Nota</span>
                            </div>
                            <div class="bg-slate-100 p-3">
                                <img
                                    id="image-preview"
                                    src=""
                                    alt="Preview Nota"
                                    class="mx-auto max-h-[500px] w-auto rounded-xl border border-slate-200 bg-white shadow-sm"
                                >
                            </div>
                        </div>
                    </div>

                    <div id="pdf-preview-container" class="hidden">
                        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                            <div class="border-b border-slate-200 bg-slate-50 px-4 py-2.5 flex items-center gap-2">
                                <svg class="h-3.5 w-3.5 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Preview PDF Nota</span>
                            </div>
                            <div class="bg-slate-100 p-3">
                                <iframe
                                    id="pdf-preview"
                                    src=""
                                    class="h-[500px] w-full rounded-xl border border-slate-200 bg-white"
                                ></iframe>
                            </div>
                        </div>
                    </div>

                    <div id="unsupported-preview" class="hidden rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
                        <div class="flex items-center gap-2 mb-1">
                            <svg class="h-4 w-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                            <span class="font-bold">Preview tidak tersedia</span>
                        </div>
                        <p class="text-xs">File ini tidak dapat dipreview, namun tetap bisa diproses oleh sistem OCR.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Action Footer --}}
        <div class="border-t border-slate-200 bg-white px-6 py-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-2 text-xs text-slate-500">
                    <svg class="h-4 w-4 text-slate-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
                    </svg>
                    <span>
                        Dengan menekan <span class="font-bold text-slate-700">Scan Sekarang</span>, file akan diproses oleh sistem OCR AI dan menghasilkan draft transaksi untuk direview.
                    </span>
                </div>
                <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end flex-shrink-0">
                    <a
                        href="{{ route('purchase-transactions.index') }}"
                        id="cancel-button"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 hover:border-slate-300 transition-all"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        Batal
                    </a>
                    <button
                        type="submit"
                        id="submit-button"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-emerald-700 active:bg-emerald-800 transition-all disabled:cursor-not-allowed disabled:opacity-70"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/>
                        </svg>
                        <span id="submit-button-text">Scan Sekarang</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- ═══════════════════════════════════════════════════
     LOADING OVERLAY — DO NOT MODIFY (JS dependent)
═══════════════════════════════════════════════════ --}}
<div
    id="scan-loading-overlay"
    class="pointer-events-none fixed inset-0 z-[9999] hidden items-center justify-center bg-slate-900/20 px-4 backdrop-blur-[6px]"
>
    <div class="w-full max-w-[430px] overflow-hidden rounded-[28px] border border-white/80 bg-white/95 text-slate-900 shadow-[0_30px_80px_rgba(15,23,42,0.18)]">
        <div class="relative p-6 sm:p-7">
            <div class="absolute inset-x-0 top-0 h-[3px] bg-gradient-to-r from-emerald-400 via-cyan-400 to-emerald-300"></div>

            <div class="flex flex-col items-center text-center">
                {{-- Logo --}}
                <div class="relative mb-4">
                    <div class="absolute inset-0 rounded-full bg-emerald-100 blur-lg"></div>
                    <div class="relative flex h-[84px] w-[84px] items-center justify-center rounded-full border border-emerald-100 bg-white shadow-[0_12px_30px_rgba(16,185,129,0.16)]">
                        <img
                            src="{{ asset('images/logo.png') }}"
                            alt="Notago Logo"
                            class="h-[62px] w-[62px] rounded-full object-cover"
                        >
                    </div>
                </div>

                <div class="mb-1 text-[10px] font-semibold uppercase tracking-[0.32em] text-emerald-600">
                    NOTAGO
                </div>

                <h3 class="text-[28px] font-bold leading-tight text-slate-900">
                    Gambar sedang diproses
                </h3>

                <p id="loading-status-text" class="mt-2 max-w-[320px] text-sm leading-6 text-slate-500">
                    Menyiapkan file untuk proses scan OCR...
                </p>

                {{-- Progress --}}
                <div class="mt-6 w-full">
                    <div class="mb-2 flex items-center justify-between">
                        <span class="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-400">Progress</span>
                        <span id="loading-progress-text" class="text-sm font-bold text-slate-800">0%</span>
                    </div>
                    <div class="h-2.5 w-full overflow-hidden rounded-full bg-slate-100">
                        <div
                            id="loading-progress-bar"
                            class="h-full w-0 rounded-full bg-gradient-to-r from-emerald-400 via-cyan-400 to-sky-400 transition-[width] duration-300 ease-out shadow-[0_4px_14px_rgba(34,197,94,0.22)]"
                        ></div>
                    </div>
                </div>

                {{-- Steps --}}
                <div class="mt-5 grid w-full grid-cols-3 gap-2.5">
                    <div id="step-1" class="scan-step-card rounded-[20px] px-3 py-3.5 text-left">
                        <div class="text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-400">Tahap 1</div>
                        <div class="mt-1.5 text-sm font-semibold text-slate-800">Validasi File</div>
                    </div>
                    <div id="step-2" class="scan-step-card rounded-[20px] px-3 py-3.5 text-left">
                        <div class="text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-400">Tahap 2</div>
                        <div class="mt-1.5 text-sm font-semibold text-slate-800">Ekstraksi OCR</div>
                    </div>
                    <div id="step-3" class="scan-step-card rounded-[20px] px-3 py-3.5 text-left">
                        <div class="text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-400">Tahap 3</div>
                        <div class="mt-1.5 text-sm font-semibold text-slate-800">Menyiapkan Draft</div>
                    </div>
                </div>

                {{-- Bottom note --}}
                <div class="mt-5 inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50 px-3.5 py-1.5 text-xs font-semibold text-emerald-700">
                    <span class="inline-flex h-2 w-2 animate-pulse rounded-full bg-emerald-500"></span>
                    Mohon tunggu, jangan tutup halaman ini
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes scan-soft-float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-1px); }
    }
    .scan-step-card {
        border: 1px solid rgba(226, 232, 240, 0.95);
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.04);
        transition: all .25s ease;
    }
    .scan-step-active {
        border-color: rgba(16, 185, 129, 0.35);
        background: linear-gradient(180deg, rgba(236, 253, 245, 1) 0%, rgba(240, 253, 250, 1) 100%);
        box-shadow: 0 12px 28px rgba(16, 185, 129, 0.10), inset 0 0 0 1px rgba(16, 185, 129, 0.06);
        animation: scan-soft-float 1.6s ease-in-out infinite;
    }
    .scan-step-done {
        border-color: rgba(167, 243, 208, 0.95);
        background: linear-gradient(180deg, rgba(240, 253, 244, 1) 0%, rgba(236, 253, 245, 1) 100%);
    }
    .scan-step-active .text-slate-400, .scan-step-done .text-slate-400 { color: #059669 !important; }
    @media (max-width: 640px) {
        #scan-loading-overlay .max-w-\[430px\] { max-width: 100%; }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('scan-form');
    const input = document.getElementById('receipt');
    const dropzone = document.getElementById('dropzone');

    const emptyPreview = document.getElementById('empty-preview');
    const previewWrapper = document.getElementById('preview-wrapper');
    const imagePreviewContainer = document.getElementById('image-preview-container');
    const pdfPreviewContainer = document.getElementById('pdf-preview-container');
    const unsupportedPreview = document.getElementById('unsupported-preview');
    const imagePreview = document.getElementById('image-preview');
    const pdfPreview = document.getElementById('pdf-preview');

    const fileInfoBox = document.getElementById('file-info-box');
    const fileName = document.getElementById('file-name');
    const fileSize = document.getElementById('file-size');
    const fileType = document.getElementById('file-type');
    const clearButton = document.getElementById('clear-file');
    const previewBadge = document.getElementById('preview-badge');

    const submitButton = document.getElementById('submit-button');
    const submitButtonText = document.getElementById('submit-button-text');
    const cancelButton = document.getElementById('cancel-button');

    const loadingOverlay = document.getElementById('scan-loading-overlay');
    const loadingProgressBar = document.getElementById('loading-progress-bar');
    const loadingProgressText = document.getElementById('loading-progress-text');
    const loadingStatusText = document.getElementById('loading-status-text');
    const step1 = document.getElementById('step-1');
    const step2 = document.getElementById('step-2');
    const step3 = document.getElementById('step-3');

    let pollingInterval = null;
    let isSubmitting = false;
    let currentPdfObjectUrl = null;

    function formatFileSize(bytes) {
        if (!bytes || bytes === 0) return '0 B';
        const units = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(1024));
        const size = bytes / Math.pow(1024, i);
        return size.toFixed(size >= 10 || i === 0 ? 0 : 1) + ' ' + units[i];
    }

    function revokePdfUrl() {
        if (currentPdfObjectUrl) {
            URL.revokeObjectURL(currentPdfObjectUrl);
            currentPdfObjectUrl = null;
        }
    }

    function resetPreview() {
        imagePreview.src = '';
        pdfPreview.src = '';
        revokePdfUrl();

        imagePreviewContainer.classList.add('hidden');
        pdfPreviewContainer.classList.add('hidden');
        unsupportedPreview.classList.add('hidden');
        previewWrapper.classList.add('hidden');
        fileInfoBox.classList.add('hidden');
        previewBadge.classList.add('hidden');
        emptyPreview.classList.remove('hidden');

        fileName.textContent = '-';
        fileSize.textContent = '-';
        fileType.textContent = '-';

        dropzone.classList.remove('border-emerald-400', 'bg-emerald-50/40', 'ring-4', 'ring-emerald-100');
    }

    function showPreview(file) {
        if (!file) { resetPreview(); return; }

        fileName.textContent = file.name || '-';
        fileSize.textContent = formatFileSize(file.size);
        fileType.textContent = file.type || 'Tidak diketahui';

        fileInfoBox.classList.remove('hidden');
        previewWrapper.classList.remove('hidden');
        previewBadge.classList.remove('hidden');
        emptyPreview.classList.add('hidden');

        dropzone.classList.add('border-emerald-400', 'bg-emerald-50/40', 'ring-4', 'ring-emerald-100');

        imagePreviewContainer.classList.add('hidden');
        pdfPreviewContainer.classList.add('hidden');
        unsupportedPreview.classList.add('hidden');

        const mimeType = (file.type || '').toLowerCase();
        const fileNameLower = (file.name || '').toLowerCase();

        if (mimeType.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function (event) {
                imagePreview.src = event.target.result;
                imagePreviewContainer.classList.remove('hidden');
            };
            reader.readAsDataURL(file);
            return;
        }

        if (mimeType === 'application/pdf' || fileNameLower.endsWith('.pdf')) {
            revokePdfUrl();
            currentPdfObjectUrl = URL.createObjectURL(file);
            pdfPreview.src = currentPdfObjectUrl;
            pdfPreviewContainer.classList.remove('hidden');
            return;
        }

        unsupportedPreview.classList.remove('hidden');
    }

    function resetSteps() {
        [step1, step2, step3].forEach(function (step) {
            step.classList.remove('scan-step-active', 'scan-step-done');
        });
    }

    function updateLoadingStep(progress) {
        resetSteps();
        if (progress <= 25) {
            step1.classList.add('scan-step-active');
        } else if (progress <= 70) {
            step1.classList.add('scan-step-done');
            step2.classList.add('scan-step-active');
        } else if (progress < 100) {
            step1.classList.add('scan-step-done');
            step2.classList.add('scan-step-done');
            step3.classList.add('scan-step-active');
        } else {
            step1.classList.add('scan-step-done');
            step2.classList.add('scan-step-done');
            step3.classList.add('scan-step-done');
        }
    }

    function setProgress(value, message = null) {
        const safeValue = Math.max(0, Math.min(100, parseInt(value || 0, 10)));
        loadingProgressBar.style.width = safeValue + '%';
        loadingProgressText.textContent = safeValue + '%';
        if (message) loadingStatusText.textContent = message;
        updateLoadingStep(safeValue);
    }

    function openLoadingOverlay() {
        loadingOverlay.classList.remove('hidden');
        loadingOverlay.classList.add('flex');
        document.body.classList.add('overflow-hidden');
        submitButton.disabled = true;
        submitButtonText.textContent = 'Memproses...';
        if (cancelButton) cancelButton.classList.add('pointer-events-none', 'opacity-60');
        setProgress(0, 'Mengupload file nota...');
    }

    function closeLoadingOverlay() {
        loadingOverlay.classList.add('hidden');
        loadingOverlay.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
        submitButton.disabled = false;
        submitButtonText.textContent = 'Scan Sekarang';
        if (cancelButton) cancelButton.classList.remove('pointer-events-none', 'opacity-60');
        if (pollingInterval) { clearTimeout(pollingInterval); pollingInterval = null; }
    }

    async function fetchProgressOnce(progressUrl) {
        const separator = progressUrl.includes('?') ? '&' : '?';
        const response = await fetch(`${progressUrl}${separator}_=${Date.now()}`, {
            method: 'GET',
            cache: 'no-store',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Cache-Control': 'no-cache, no-store, must-revalidate',
                'Pragma': 'no-cache',
            }
        });
        if (!response.ok) throw new Error('Gagal membaca progress scan.');
        return await response.json();
    }

    async function pollProgress(progressUrl) {
        async function loop() {
            try {
                const result = await fetchProgressOnce(progressUrl);
                console.log('SCAN PROGRESS:', result);
                setProgress(result.progress ?? 0, result.progress_message ?? 'Memproses scan...');

                if (String(result.status || '').toLowerCase() === 'failed') {
                    alert(result.error_message || 'Proses scan gagal.');
                    isSubmitting = false;
                    closeLoadingOverlay();
                    return;
                }

                if (parseInt(result.result_ready ?? 0, 10) === 1 || String(result.status || '').toLowerCase() === 'processed') {
                    window.location.href = result.redirect_url;
                    return;
                }

                pollingInterval = setTimeout(loop, 700);
            } catch (error) {
                alert(error.message || 'Terjadi kesalahan saat membaca progress.');
                isSubmitting = false;
                closeLoadingOverlay();
            }
        }

        if (pollingInterval) { clearTimeout(pollingInterval); pollingInterval = null; }
        await loop();
    }

    input.addEventListener('change', function (event) {
        const file = event.target.files && event.target.files[0] ? event.target.files[0] : null;
        showPreview(file);
    });

    clearButton.addEventListener('click', function () {
        input.value = '';
        resetPreview();
    });

    ['dragenter', 'dragover'].forEach(function (eventName) {
        dropzone.addEventListener(eventName, function () {
            dropzone.classList.add('border-slate-500', 'bg-slate-100');
        });
    });

    ['dragleave', 'drop'].forEach(function (eventName) {
        dropzone.addEventListener(eventName, function () {
            dropzone.classList.remove('border-slate-500', 'bg-slate-100');
        });
    });

    form.addEventListener('submit', async function (event) {
        event.preventDefault();
        if (isSubmitting) return;
        if (!input.files || !input.files.length) {
            alert('Silakan pilih file nota terlebih dahulu.');
            return;
        }

        isSubmitting = true;
        openLoadingOverlay();

        try {
            const formData = new FormData();
            formData.append('receipt', input.files[0]);

            const response = await fetch("{{ route('purchase-scans.start') }}", {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            if (!response.ok) {
                const errorResult = await response.json().catch(() => null);
                throw new Error(errorResult?.message || 'Gagal memulai proses scan.');
            }

            const result = await response.json();
            if (!result.success) throw new Error(result.message || 'Gagal memulai proses scan.');

            if (result.duplicate_warning) {
                const w = result.duplicate_warning;
                const msg = `Peringatan: File ini kemungkinan pernah di-scan sebelumnya (${w.created_at}, status: ${w.status}).\n\nProses scan tetap dilanjutkan. Anda bisa cek scan sebelumnya setelah selesai.`;
                alert(msg);
            }

            setProgress(5, 'File berhasil diupload. Menunggu proses scan...');
            await pollProgress(result.progress_url);
        } catch (error) {
            alert(error.message || 'Terjadi kesalahan.');
            isSubmitting = false;
            closeLoadingOverlay();
        }
    });

    window.addEventListener('pageshow', function () { isSubmitting = false; });
    resetPreview();
});
</script>
@endsection
