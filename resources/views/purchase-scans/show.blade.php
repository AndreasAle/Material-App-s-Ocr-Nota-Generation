@extends('layouts.app')

@section('content')
@php
    $status = strtolower((string) $purchaseScanDraft->status);

    $statusConfig = match ($status) {
        'processed' => [
            'label' => 'Scan Berhasil',
            'badge' => 'bg-emerald-100 text-emerald-700 border border-emerald-200',
            'panel' => 'border-emerald-200 bg-emerald-50/70',
            'iconWrap' => 'bg-emerald-100 text-emerald-700',
            'title' => 'Hasil scan berhasil diproses',
            'desc' => 'Sistem berhasil membaca nota dan menyiapkan draft data pembelian untuk direview.',
        ],
        'reviewed' => [
            'label' => 'Sudah Direview',
            'badge' => 'bg-indigo-100 text-indigo-700 border border-indigo-200',
            'panel' => 'border-indigo-200 bg-indigo-50/70',
            'iconWrap' => 'bg-indigo-100 text-indigo-700',
            'title' => 'Draft scan sudah direview',
            'desc' => 'Data hasil pembacaan nota sudah melalui tahap pemeriksaan sebelum diimport.',
        ],
        'imported' => [
            'label' => 'Sudah Diimport',
            'badge' => 'bg-indigo-100 text-indigo-700 border border-indigo-200',
            'panel' => 'border-indigo-200 bg-indigo-50/70',
            'iconWrap' => 'bg-indigo-100 text-indigo-700',
            'title' => 'Draft scan sudah diimport',
            'desc' => 'Data hasil scan sudah dimasukkan ke transaksi pembelian.',
        ],
        'failed' => [
            'label' => 'Scan Gagal',
            'badge' => 'bg-rose-100 text-rose-700 border border-rose-200',
            'panel' => 'border-rose-200 bg-rose-50/70',
            'iconWrap' => 'bg-rose-100 text-rose-700',
            'title' => 'Proses scan tidak berhasil',
            'desc' => 'Sistem belum bisa membaca nota dengan baik. Silakan cek file atau upload ulang dengan kualitas lebih jelas.',
        ],
        default => [
            'label' => ucfirst($purchaseScanDraft->status),
            'badge' => 'bg-slate-100 text-slate-700 border border-slate-200',
            'panel' => 'border-slate-200 bg-slate-50/70',
            'iconWrap' => 'bg-slate-100 text-slate-700',
            'title' => 'Status scan tersedia',
            'desc' => 'Silakan review hasil pembacaan nota sebelum melanjutkan proses.',
        ],
    };

    $totalItems = count($items);
    $manualPercentage = $totalItems > 0 ? round(($manualCount / $totalItems) * 100) : 0;
    $matchedPercentage = $totalItems > 0 ? round(($matchedCount / $totalItems) * 100) : 0;

    $previewUrl = null;
    if (!empty($purchaseScanDraft->file_path)) {
        try {
            $previewUrl = route('purchase-scans.file', $purchaseScanDraft);
        } catch (\Throwable $e) {
            $previewUrl = null;
        }
    }

    $fileExtension = strtolower(pathinfo((string) $purchaseScanDraft->file_name, PATHINFO_EXTENSION));
    $isPdf = $fileExtension === 'pdf';
    $isImage = in_array($fileExtension, ['jpg', 'jpeg', 'png', 'webp', 'gif']);

    $safeSupplier = $supplierName ?: 'Belum terdeteksi';
    $safeNoteNumber = $purchaseScanDraft->detected_note_number ?: 'Belum tersedia';
    $safeModel = $ocrModel ?: '-';
    $safeEngine = strtoupper((string) $ocrEngine);
@endphp

<x-page-header
    title="Review Hasil Scan Nota"
    subtitle="Periksa hasil pembacaan nota sebelum dimasukkan ke transaksi pembelian."
/>

<div class="space-y-6">
    <div class="overflow-hidden rounded-[28px] border {{ $statusConfig['panel'] }} shadow-sm">
        <div class="flex flex-col gap-5 px-6 py-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-start gap-4">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl {{ $statusConfig['iconWrap'] }}">
                    @if($status === 'failed')
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86l-7.1 12.3A2 2 0 004.91 19h14.18a2 2 0 001.72-2.84l-7.09-12.3a2 2 0 00-3.43 0z"/>
                        </svg>
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    @endif
                </div>

                <div>
                    <div class="inline-flex rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wide {{ $statusConfig['badge'] }}">
                        {{ $statusConfig['label'] }}
                    </div>

                    <h2 class="mt-3 text-xl font-bold tracking-tight text-slate-900">
                        {{ $statusConfig['title'] }}
                    </h2>

                    <p class="mt-1 max-w-3xl text-sm leading-6 text-slate-600">
                        {{ $statusConfig['desc'] }}
                    </p>

                    <div class="mt-4 flex flex-wrap gap-2">
                        <div class="rounded-full border border-white/70 bg-white/80 px-3 py-1.5 text-xs font-medium text-slate-700">
                            Supplier: <span class="font-semibold text-slate-900">{{ $safeSupplier }}</span>
                        </div>
                        <div class="rounded-full border border-white/70 bg-white/80 px-3 py-1.5 text-xs font-medium text-slate-700">
                            Tanggal: <span class="font-semibold text-slate-900">{{ $displayDate }}</span>
                        </div>
                        <div class="rounded-full border border-white/70 bg-white/80 px-3 py-1.5 text-xs font-medium text-slate-700">
                            Jumlah Item: <span class="font-semibold text-slate-900">{{ $totalItems }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row lg:justify-end">
                <a
                    href="{{ route('purchase-scans.create') }}"
                    class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                >
                    Scan Ulang
                </a>

                @if($totalItems > 0 && $purchaseScanDraft->status !== 'failed')
                    <a
                        href="{{ route('purchase-scans.compare', $purchaseScanDraft) }}"
                        class="inline-flex items-center justify-center rounded-2xl border border-indigo-200 bg-indigo-50 px-4 py-3 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-100"
                    >
                        Mode Compare
                    </a>
                @endif

                @if($totalItems > 0 && $purchaseScanDraft->status !== 'failed' && $purchaseScanDraft->status !== 'imported')
                    <form method="POST" action="{{ route('purchase-scans.import', $purchaseScanDraft) }}">
                        @csrf
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700"
                        >
                            Masukkan ke Transaksi
                        </button>
                    </form>
                @endif
            </div>



        </div>
    </div>

    {{-- Peringatan duplikat --}}
    @if(!empty($duplicateWarning))
        <div class="rounded-[20px] border border-amber-200 bg-amber-50 px-5 py-4">
            <div class="flex items-start gap-3">
                <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <div class="text-sm font-semibold text-amber-800">Kemungkinan nota duplikat terdeteksi</div>
                    <p class="mt-0.5 text-xs leading-5 text-amber-700">
                        File yang sama pernah di-scan pada {{ $duplicateWarning['created_at'] }} (status: {{ $duplicateWarning['status'] }}).
                        <a href="{{ route('purchase-scans.show', $duplicateWarning['id']) }}" class="font-semibold underline">Lihat scan sebelumnya</a>
                    </p>
                </div>
            </div>
        </div>
    @endif

    @if($purchaseScanDraft->status === 'failed')
        <div class="rounded-3xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm leading-6 text-rose-700 shadow-sm">
            Sistem belum berhasil mengenali isi nota. Pastikan foto tidak blur, tidak terpotong, pencahayaan cukup, dan posisi nota tegak lurus.
        </div>
    @elseif($totalItems === 0)
        <div class="rounded-3xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm leading-6 text-amber-700 shadow-sm">
            Sistem belum menemukan item material yang valid. Draft ini masih bisa direview manual atau dilakukan scan ulang dengan kualitas foto yang lebih baik.
        </div>
    @elseif(($lowConfCount ?? 0) > 0)
        <div class="rounded-[20px] border border-amber-200 bg-amber-50 px-5 py-4">
            <div class="flex items-start gap-3">
                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-700 mt-0.5">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.29 3.86l-7.1 12.3A2 2 0 004.91 19h14.18a2 2 0 001.72-2.84l-7.09-12.3a2 2 0 00-3.43 0z"/></svg>
                </div>
                <div class="text-sm text-amber-800">
                    <span class="font-semibold">{{ $lowConfCount }} item</span> memiliki confidence OCR rendah — periksa dengan Mode Compare sebelum import.
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
        <div class="space-y-6 xl:col-span-8">
            <x-data-card class="overflow-hidden border border-slate-200 p-0 shadow-sm">
                <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 via-white to-slate-50 px-6 py-5">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900">Informasi Hasil Scan</h3>
                            <p class="mt-1 text-sm text-slate-500">
                                Ringkasan data utama yang berhasil dibaca dari nota pembelian.
                            </p>
                        </div>

                        <div class="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600">
                            Draft File: <span class="font-semibold text-slate-900">{{ $purchaseScanDraft->file_name }}</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 p-6 md:grid-cols-2 2xl:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">No Nota</div>
                        <div class="mt-2 text-base font-bold text-slate-900">{{ $safeNoteNumber }}</div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Tanggal Nota</div>
                        <div class="mt-2 text-base font-bold text-slate-900">{{ $displayDate }}</div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Supplier</div>
                        <div class="mt-2 text-base font-bold text-slate-900">{{ $safeSupplier }}</div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Grand Total</div>
                        <div class="mt-2 text-base font-bold text-slate-900">
                            Rp {{ number_format((int) $grandTotal, 0, ',', '.') }}
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Engine OCR</div>
                        <div class="mt-2 text-base font-bold text-slate-900">{{ $safeEngine }}</div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Model</div>
                        <div class="mt-2 break-all text-sm font-bold text-slate-900">{{ $safeModel }}</div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 md:col-span-2 2xl:col-span-3">
                        <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Nama File</div>
                        <div class="mt-2 break-all text-sm font-bold text-slate-900">{{ $purchaseScanDraft->file_name }}</div>
                    </div>
                </div>
            </x-data-card>

            <x-data-card class="overflow-hidden border border-slate-200 p-0 shadow-sm">
                <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 via-white to-slate-50 px-6 py-5">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900">Preview Nota</h3>
                            <p class="mt-1 text-sm text-slate-500">
                                Tampilan file asli yang diupload agar user bisa membandingkan hasil scan dengan dokumen sumber.
                            </p>
                        </div>

                        @if($previewUrl)
                            <a
                                href="{{ $previewUrl }}"
                                target="_blank"
                                class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                            >
                                Buka File Asli
                            </a>
                        @endif
                    </div>
                </div>

                <div class="p-6">
                    @if($previewUrl && $isImage)
                        <div class="overflow-hidden rounded-[24px] border border-slate-200 bg-slate-100 p-3">
                            <img
                                src="{{ $previewUrl }}"
                                alt="Preview nota"
                                class="mx-auto max-h-[760px] w-auto rounded-2xl border border-slate-200 bg-white shadow-sm"
                            >
                        </div>
                    @elseif($previewUrl && $isPdf)
                        <div class="overflow-hidden rounded-[24px] border border-slate-200 bg-slate-100 p-3">
                            <iframe
                                src="{{ $previewUrl }}"
                                class="h-[760px] w-full rounded-2xl border border-slate-200 bg-white"
                            ></iframe>
                        </div>
                    @else
                        <div class="flex min-h-[260px] items-center justify-center rounded-[24px] border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center">
                            <div>
                                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 2v6h6"/>
                                    </svg>
                                </div>
                                <p class="text-sm font-semibold text-slate-700">Preview file belum tersedia</p>
                                <p class="mt-1 text-xs leading-5 text-slate-500">
                                    File tetap tersimpan, tetapi tidak bisa dipreview langsung pada halaman ini.
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            </x-data-card>

            <x-data-card class="overflow-hidden border border-slate-200 p-0 shadow-sm">
                <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 via-white to-slate-50 px-6 py-5">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900">Item Hasil Scan</h3>
                            <p class="mt-1 text-sm text-slate-500">
                                Daftar item material yang berhasil diidentifikasi dari nota.
                            </p>
                        </div>

                        <div class="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-sm font-semibold text-slate-700">
                            Grand Total:
                            <span class="text-slate-900">Rp {{ number_format((int) $grandTotal, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                @if($totalItems > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-50 text-slate-700">
                                <tr>
                                    <th class="px-5 py-4 text-left font-semibold">Nama Material</th>
                                    <th class="px-5 py-4 text-left font-semibold">Satuan</th>
                                    <th class="px-5 py-4 text-right font-semibold">Qty</th>
                                    <th class="px-5 py-4 text-right font-semibold">Harga</th>
                                    <th class="px-5 py-4 text-right font-semibold">Subtotal</th>
                                    <th class="px-5 py-4 text-center font-semibold">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @foreach($items as $item)
                                    <tr class="align-top transition hover:bg-slate-50/70">
                                        <td class="px-5 py-4">
                                            <div class="font-semibold text-slate-900">
                                                {{ $item['nama_material'] ?: '-' }}
                                            </div>

                                            @if(!empty($item['kode']))
                                                <div class="mt-1 text-xs text-slate-500">
                                                    Kode: {{ $item['kode'] }}
                                                </div>
                                            @endif

                                            @if(!empty($item['raw_line']))
                                                <div class="mt-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-[11px] leading-5 text-slate-500">
                                                    Teks hasil baca: {{ $item['raw_line'] }}
                                                </div>
                                            @endif
                                        </td>

                                        <td class="px-5 py-4 text-slate-700">
                                            {{ $item['satuan'] ?: '-' }}
                                        </td>

                                        <td class="px-5 py-4 text-right font-medium text-slate-700">
                                            {{ rtrim(rtrim(number_format((float) ($item['qty'] ?? 0), 2, '.', ''), '0'), '.') }}
                                        </td>

                                        <td class="px-5 py-4 text-right text-slate-700">
                                            Rp {{ number_format((int) ($item['harga'] ?? 0), 0, ',', '.') }}
                                        </td>

                                        <td class="px-5 py-4 text-right font-semibold text-slate-900">
                                            Rp {{ number_format((int) ($item['subtotal'] ?? 0), 0, ',', '.') }}
                                        </td>

                                        @php
                                            $conf = (float) ($item['confidence'] ?? 0);
                                            $confBadge = $conf >= 0.85 ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                                                       : ($conf >= 0.65 ? 'border-amber-200 bg-amber-50 text-amber-700'
                                                       : ($conf > 0    ? 'border-rose-200 bg-rose-50 text-rose-700'
                                                                        : 'border-slate-100 bg-slate-50 text-slate-500'));
                                            $confLbl = $conf >= 0.85 ? 'Tinggi' : ($conf >= 0.65 ? 'Sedang' : ($conf > 0 ? 'Rendah' : null));
                                            $isLearnt = ($item['match_status'] ?? '') === 'matched'
                                                && str_contains($item['match_label'] ?? '', 'Learnt');
                                        @endphp
                                        <td class="px-5 py-4 text-center">
                                            @if(($item['match_status'] ?? null) === 'matched')
                                                <div class="inline-flex flex-col items-center gap-1">
                                                    <span class="rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Cocok</span>
                                                    @if(!empty($item['match_label']))
                                                        <span class="max-w-[180px] break-words text-[11px] leading-4 text-emerald-700">{{ $item['match_label'] }}</span>
                                                    @endif
                                                    @if(($item['match_score'] ?? 0) > 0)
                                                        <span class="text-[11px] text-slate-400">Skor: {{ number_format((float) $item['match_score'], 2) }}</span>
                                                    @endif
                                                </div>
                                            @else
                                                <div class="inline-flex flex-col items-center gap-1">
                                                    <span class="rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">Perlu Cek</span>
                                                    @if(($item['match_score'] ?? 0) > 0)
                                                        <span class="text-[11px] text-slate-400">Skor: {{ number_format((float) $item['match_score'], 2) }}</span>
                                                    @endif
                                                </div>
                                            @endif
                                            @if($conf > 0)
                                                <div class="mt-1.5 inline-flex items-center rounded-full border px-2 py-0.5 text-[10px] font-medium {{ $confBadge }}">
                                                    {{ number_format($conf, 2) }}{{ $confLbl ? ' (' . $confLbl . ')' : '' }}
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>

                            <tfoot>
                                <tr class="border-t bg-slate-50">
                                    <th colspan="4" class="px-5 py-4 text-right text-sm font-semibold text-slate-700">
                                        Grand Total
                                    </th>
                                    <th class="px-5 py-4 text-right text-sm font-bold text-slate-900">
                                        Rp {{ number_format((int) $grandTotal, 0, ',', '.') }}
                                    </th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <div class="px-6 py-14 text-center">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6M9 8h6M7 3h10a2 2 0 012 2v14a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z"/>
                            </svg>
                        </div>
                        <h4 class="mt-4 text-base font-semibold text-slate-900">Belum ada item yang terdeteksi</h4>
                        <p class="mt-1 text-sm leading-6 text-slate-500">
                            Sistem belum dapat membaca item material dengan baik dari nota ini. Disarankan untuk scan ulang dengan file yang lebih jelas.
                        </p>
                    </div>
                @endif
            </x-data-card>
        </div>

        <div class="space-y-6 xl:col-span-4">
            <x-data-card class="overflow-hidden border border-slate-200 p-0 shadow-sm">
                <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 via-white to-slate-50 px-6 py-5">
                    <h3 class="text-lg font-semibold text-slate-900">Ringkasan Hasil</h3>
                    <p class="mt-1 text-sm text-slate-500">
                        Gambaran cepat kualitas hasil scan dan kebutuhan pengecekan.
                    </p>
                </div>

                <div class="space-y-4 p-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="text-xs font-medium uppercase tracking-wide text-slate-500">Jumlah Item</div>
                            <div class="mt-2 text-2xl font-bold text-slate-900">{{ $totalItems }}</div>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="text-xs font-medium uppercase tracking-wide text-slate-500">Grand Total</div>
                            <div class="mt-2 text-lg font-bold text-slate-900">
                                Rp {{ number_format((int) $grandTotal, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                        <div class="mb-2 flex items-center justify-between gap-3">
                            <span class="text-sm font-semibold text-emerald-800">Item yang cocok otomatis</span>
                            <span class="text-sm font-bold text-emerald-700">{{ $matchedCount }}</span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-emerald-100">
                            <div class="h-full rounded-full bg-emerald-500" style="width: {{ $matchedPercentage }}%"></div>
                        </div>
                        <div class="mt-2 text-xs text-emerald-700">
                            {{ $matchedPercentage }}% dari total item terdeteksi.
                        </div>
                    </div>

                    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4">
                        <div class="mb-2 flex items-center justify-between gap-3">
                            <span class="text-sm font-semibold text-amber-800">Item yang perlu dicek</span>
                            <span class="text-sm font-bold text-amber-700">{{ $manualCount }}</span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-amber-100">
                            <div class="h-full rounded-full bg-amber-500" style="width: {{ $manualPercentage }}%"></div>
                        </div>
                        <div class="mt-2 text-xs text-amber-700">
                            {{ $manualPercentage }}% item masih memerlukan pemeriksaan user.
                        </div>
                    </div>
                </div>
            </x-data-card>

            <x-data-card class="overflow-hidden border border-slate-200 p-0 shadow-sm">
                <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 via-white to-slate-50 px-6 py-5">
                    <h3 class="text-lg font-semibold text-slate-900">Panduan Review</h3>
                    <p class="mt-1 text-sm text-slate-500">
                        Hal yang sebaiknya diperiksa sebelum import transaksi.
                    </p>
                </div>

                <div class="space-y-3 p-6">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="text-sm font-semibold text-slate-800">Periksa nama supplier</div>
                        <p class="mt-1 text-xs leading-5 text-slate-500">
                            Pastikan nama toko atau supplier sudah sesuai dengan nota asli.
                        </p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="text-sm font-semibold text-slate-800">Cek qty dan harga</div>
                        <p class="mt-1 text-xs leading-5 text-slate-500">
                            Angka sering keliru jika foto blur, miring, atau bagian nota terpotong.
                        </p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="text-sm font-semibold text-slate-800">Review item “Perlu Cek”</div>
                        <p class="mt-1 text-xs leading-5 text-slate-500">
                            Item dengan status ini perlu dipastikan lagi sebelum masuk ke transaksi pembelian.
                        </p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="text-sm font-semibold text-slate-800">Pastikan grand total benar</div>
                        <p class="mt-1 text-xs leading-5 text-slate-500">
                            Cocokkan total hasil scan dengan nominal akhir pada nota.
                        </p>
                    </div>
                </div>
            </x-data-card>

            @if(!empty($parsed['notes']) && is_array($parsed['notes']))
                <x-data-card class="overflow-hidden border border-slate-200 p-0 shadow-sm">
                    <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 via-white to-slate-50 px-6 py-5">
                        <h3 class="text-lg font-semibold text-slate-900">Catatan Penting</h3>
                        <p class="mt-1 text-sm text-slate-500">
                            Informasi tambahan yang terbaca dari dokumen.
                        </p>
                    </div>

                    <div class="space-y-3 p-6">
                        @foreach($parsed['notes'] as $note)
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm leading-6 text-slate-700">
                                {{ $note }}
                            </div>
                        @endforeach
                    </div>
                </x-data-card>
            @endif
        </div>
    </div>
</div>
@endsection
