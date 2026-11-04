@extends('layouts.app')

@section('content')
<x-data-card class="overflow-hidden border border-slate-200/80 bg-white p-0 shadow-sm">
    <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 via-white to-slate-50 px-6 py-5">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Upload Multiple Notes</h2>
                <p class="mt-1 text-sm text-slate-500">
                    Upload 2 sampai 5 nota sekaligus. Semua nota harus berasal dari supplier / toko yang sama.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a
                    href="{{ route('purchase-scans.create') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                >
                    Single Notes
                </a>

                <span
                    class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-medium text-emerald-700"
                >
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    Multiple Notes
                </span>
            </div>
        </div>
    </div>

    <form id="multi-scan-form" class="space-y-0" enctype="multipart/form-data">
        @csrf

        <div class="grid grid-cols-1 gap-0 xl:grid-cols-12">
            <div class="xl:col-span-7">
                <div class="p-6">
                    <div class="mb-4">
                        <label for="receipts" class="mb-2 block text-sm font-semibold text-slate-800">
                            File Nota
                        </label>
                        <p class="text-xs leading-5 text-slate-500">
                            Minimal 2 file dan maksimal 5 file. Format: JPG, JPEG, PNG, WEBP, PDF.
                        </p>
                    </div>

                    {{-- Foto nota satu per satu langsung dari kamera; tiap foto ditambahkan ke daftar --}}
                    <x-camera-capture target="receipts" :append="true" :max="5" class="mb-3" />
                    <p class="mb-3 text-center text-xs text-charcoal-500">Tekan <b>Ambil Foto</b> berulang untuk tiap nota (2–5 nota).</p>

                    <div
                        id="dropzone"
                        class="group relative rounded-3xl border-2 border-dashed border-slate-300 bg-gradient-to-br from-slate-50 to-white p-5 transition-all duration-300 hover:border-slate-400 hover:shadow-sm"
                    >
                        <input
                            id="receipts"
                            type="file"
                            name="receipts[]"
                            accept=".jpg,.jpeg,.png,.webp,.pdf"
                            class="absolute inset-0 z-10 h-full w-full cursor-pointer opacity-0"
                            multiple
                            required
                        >

                        <div class="pointer-events-none flex min-h-[280px] flex-col items-center justify-center rounded-2xl border border-slate-200/70 bg-white/80 px-6 py-10 text-center">
                            <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-900 text-white shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m0 0l-4 4m4-4l4 4" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 15v2a3 3 0 003 3h10a3 3 0 003-3v-2" />
                                </svg>
                            </div>

                            <h3 class="text-base font-semibold text-slate-900">
                                Drag & drop multiple file nota ke area ini
                            </h3>

                            <p class="mt-2 max-w-xl text-sm leading-6 text-slate-500">
                                Sistem akan memproses semua file secara berurutan dan menggabungkan hasilnya menjadi 1 draft transaksi.
                            </p>

                            <div class="mt-5 flex flex-wrap items-center justify-center gap-2 text-xs">
                                <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 font-medium text-slate-600">Min 2 File</span>
                                <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 font-medium text-slate-600">Maks 5 File</span>
                                <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 font-medium text-slate-600">Supplier Sama</span>
                            </div>

                            <div class="mt-6 inline-flex items-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition group-hover:bg-slate-800">
                                Pilih Multiple Notes
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-800">
                        <div class="font-semibold">Penting:</div>
                        <div class="mt-1 leading-6">
                            Semua nota pada mode ini wajib dari supplier yang sama. Jika supplier terbaca berbeda, batch akan ditolak.
                        </div>
                    </div>

                    <div id="selected-files-box" class="mt-5 hidden rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">Daftar file terpilih</p>
                                <p class="text-xs text-slate-500">Klik item untuk melihat preview.</p>
                            </div>

                            <div id="files-count-badge" class="rounded-full bg-slate-900 px-3 py-1 text-xs font-medium text-white">
                                0 file
                            </div>
                        </div>

                        <div id="files-list" class="space-y-2"></div>
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-200 bg-slate-50/80 xl:col-span-5 xl:border-l xl:border-t-0">
                <div class="p-6">
                    <div class="mb-4 flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-semibold uppercase tracking-[0.16em] text-slate-500">
                                Preview File Aktif
                            </h3>
                            <p class="mt-1 text-sm text-slate-600">
                                Preview file yang sedang dipilih.
                            </p>
                        </div>
                    </div>

                    <div id="empty-preview" class="flex min-h-[420px] items-center justify-center rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-10 text-center">
                        <div>
                            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16.5V6a2 2 0 012-2h7.5L20 10.5V18a2 2 0 01-2 2H6a2 2 0 01-2-2v-1.5" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 4v6h6" />
                                </svg>
                            </div>
                            <p class="text-sm font-medium text-slate-700">Belum ada file dipilih</p>
                        </div>
                    </div>

                    <div id="preview-wrapper" class="hidden">
                        <div id="image-preview-container" class="hidden">
                            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                                <div class="border-b border-slate-200 bg-slate-50 px-4 py-3 text-xs font-medium uppercase tracking-wide text-slate-500">
                                    Preview Gambar Nota
                                </div>
                                <div class="bg-slate-100 p-3">
                                    <img
                                        id="image-preview"
                                        src=""
                                        alt="Preview Nota"
                                        class="mx-auto max-h-[520px] w-auto rounded-2xl border border-slate-200 bg-white shadow-sm"
                                    >
                                </div>
                            </div>
                        </div>

                        <div id="pdf-preview-container" class="hidden">
                            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                                <div class="border-b border-slate-200 bg-slate-50 px-4 py-3 text-xs font-medium uppercase tracking-wide text-slate-500">
                                    Preview PDF Nota
                                </div>
                                <div class="bg-slate-100 p-3">
                                    <iframe
                                        id="pdf-preview"
                                        src=""
                                        class="h-[520px] w-full rounded-2xl border border-slate-200 bg-white"
                                    ></iframe>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                        <h4 class="text-sm font-semibold text-slate-900">Aturan Multiple Notes</h4>
                        <ul class="mt-4 space-y-2 text-xs leading-5 text-slate-600">
                            <li>• Minimal 2 file.</li>
                            <li>• Maksimal 5 file.</li>
                            <li>• Semua nota harus dari supplier yang sama.</li>
                            <li>• Jika supplier berbeda, batch ditolak otomatis.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="border-t border-slate-200 bg-white px-6 py-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="text-xs leading-5 text-slate-500">
                    Dengan menekan <span class="font-semibold text-slate-700">Scan Multiple Notes</span>, semua file akan diproses menjadi 1 draft transaksi.
                </div>

                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <a
                        href="{{ route('purchase-transactions.index') }}"
                        id="cancel-button"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-2.5 text-center text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                    >
                        Batal
                    </a>

                    <button
                        type="submit"
                        id="submit-button"
                        class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-70"
                    >
                        <span id="submit-button-text">Scan Multiple Notes</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</x-data-card>

<div
    id="scan-loading-overlay"
    class="pointer-events-none fixed inset-0 z-[9999] hidden items-center justify-center bg-slate-900/20 px-4 backdrop-blur-[6px]"
>
    <div class="w-full max-w-[430px] overflow-hidden rounded-[28px] border border-white/80 bg-white/95 text-slate-900 shadow-[0_30px_80px_rgba(15,23,42,0.18)]">
        <div class="relative p-6 sm:p-7">
            <div class="absolute inset-x-0 top-0 h-[3px] bg-gradient-to-r from-emerald-400 via-cyan-400 to-emerald-300"></div>

            <div class="flex flex-col items-center text-center">
                <div class="mb-1 text-[10px] font-semibold uppercase tracking-[0.32em] text-emerald-600">
                    NOTAGO
                </div>

                <h3 class="text-[28px] font-bold leading-tight text-slate-900">
                    Multiple Notes sedang diproses
                </h3>

                <p id="loading-status-text" class="mt-2 max-w-[320px] text-sm leading-6 text-slate-500">
                    Menyiapkan batch nota untuk scan OCR...
                </p>

                <div class="mt-6 w-full">
                    <div class="mb-2 flex items-center justify-between">
                        <span class="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-400">
                            Progress
                        </span>
                        <span id="loading-progress-text" class="text-sm font-bold text-slate-800">0%</span>
                    </div>

                    <div class="h-2.5 w-full overflow-hidden rounded-full bg-slate-100">
                        <div
                            id="loading-progress-bar"
                            class="h-full w-0 rounded-full bg-gradient-to-r from-emerald-400 via-cyan-400 to-sky-400 transition-[width] duration-300 ease-out"
                        ></div>
                    </div>
                </div>

                <div class="mt-5 inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50 px-3.5 py-1.5 text-xs font-semibold text-emerald-700">
                    <span class="inline-flex h-2 w-2 animate-pulse rounded-full bg-emerald-500"></span>
                    Mohon tunggu, jangan tutup halaman ini
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('multi-scan-form');
    const input = document.getElementById('receipts');
    const filesList = document.getElementById('files-list');
    const selectedFilesBox = document.getElementById('selected-files-box');
    const filesCountBadge = document.getElementById('files-count-badge');

    const emptyPreview = document.getElementById('empty-preview');
    const previewWrapper = document.getElementById('preview-wrapper');
    const imagePreviewContainer = document.getElementById('image-preview-container');
    const pdfPreviewContainer = document.getElementById('pdf-preview-container');
    const imagePreview = document.getElementById('image-preview');
    const pdfPreview = document.getElementById('pdf-preview');

    const loadingOverlay = document.getElementById('scan-loading-overlay');
    const loadingProgressBar = document.getElementById('loading-progress-bar');
    const loadingProgressText = document.getElementById('loading-progress-text');
    const loadingStatusText = document.getElementById('loading-status-text');
    const submitButton = document.getElementById('submit-button');
    const submitButtonText = document.getElementById('submit-button-text');

    let pollingInterval = null;
    let currentPdfObjectUrl = null;
    let isSubmitting = false;
    let selectedIndex = 0;

    function revokePdfUrl() {
        if (currentPdfObjectUrl) {
            URL.revokeObjectURL(currentPdfObjectUrl);
            currentPdfObjectUrl = null;
        }
    }

    function renderPreview(file) {
        revokePdfUrl();
        imagePreviewContainer.classList.add('hidden');
        pdfPreviewContainer.classList.add('hidden');

        if (!file) {
            previewWrapper.classList.add('hidden');
            emptyPreview.classList.remove('hidden');
            return;
        }

        previewWrapper.classList.remove('hidden');
        emptyPreview.classList.add('hidden');

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
            currentPdfObjectUrl = URL.createObjectURL(file);
            pdfPreview.src = currentPdfObjectUrl;
            pdfPreviewContainer.classList.remove('hidden');
        }
    }

    function renderList() {
        const files = Array.from(input.files || []);
        filesList.innerHTML = '';

        if (!files.length) {
            selectedFilesBox.classList.add('hidden');
            renderPreview(null);
            return;
        }

        selectedFilesBox.classList.remove('hidden');
        filesCountBadge.textContent = `${files.length} file`;

        if (selectedIndex >= files.length) {
            selectedIndex = 0;
        }

        files.forEach((file, index) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = `w-full rounded-2xl border px-4 py-3 text-left transition ${
                index === selectedIndex
                    ? 'border-emerald-300 bg-emerald-50'
                    : 'border-slate-200 bg-white hover:bg-slate-50'
            }`;

            button.innerHTML = `
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <div class="truncate text-sm font-semibold text-slate-900">${file.name}</div>
                        <div class="mt-1 text-xs text-slate-500">${(file.type || 'unknown')} • ${Math.round((file.size || 0) / 1024)} KB</div>
                    </div>
                    <div class="rounded-full bg-slate-900 px-2.5 py-1 text-[11px] font-medium text-white">#${index + 1}</div>
                </div>
            `;

            button.addEventListener('click', function () {
                selectedIndex = index;
                renderList();
                renderPreview(files[selectedIndex]);
            });

            filesList.appendChild(button);
        });

        renderPreview(files[selectedIndex]);
    }

    function setProgress(value, message = null) {
        const safeValue = Math.max(0, Math.min(100, parseInt(value || 0, 10)));
        loadingProgressBar.style.width = safeValue + '%';
        loadingProgressText.textContent = safeValue + '%';

        if (message) {
            loadingStatusText.textContent = message;
        }
    }

    function openLoadingOverlay() {
        loadingOverlay.classList.remove('hidden');
        loadingOverlay.classList.add('flex');
        document.body.classList.add('overflow-hidden');
        submitButton.disabled = true;
        submitButtonText.textContent = 'Memproses...';
        setProgress(0, 'Mengupload batch nota...');
    }

    function closeLoadingOverlay() {
        loadingOverlay.classList.add('hidden');
        loadingOverlay.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
        submitButton.disabled = false;
        submitButtonText.textContent = 'Scan Multiple Notes';

        if (pollingInterval) {
            clearTimeout(pollingInterval);
            pollingInterval = null;
        }
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

        if (!response.ok) {
            throw new Error('Gagal membaca progress batch.');
        }

        return await response.json();
    }

    async function pollProgress(progressUrl) {
        async function loop() {
            try {
                const result = await fetchProgressOnce(progressUrl);
                setProgress(result.progress ?? 0, result.progress_message ?? 'Memproses batch...');

                if (String(result.status || '').toLowerCase() === 'failed') {
                    alert(result.error_message || 'Proses batch gagal.');
                    isSubmitting = false;
                    closeLoadingOverlay();
                    return;
                }

                if (parseInt(result.result_ready ?? 0, 10) === 1) {
                    window.location.href = result.redirect_url;
                    return;
                }

                pollingInterval = setTimeout(loop, 800);
            } catch (error) {
                alert(error.message || 'Terjadi kesalahan saat membaca progress batch.');
                isSubmitting = false;
                closeLoadingOverlay();
            }
        }

        if (pollingInterval) {
            clearTimeout(pollingInterval);
            pollingInterval = null;
        }

        await loop();
    }

    input.addEventListener('change', function () {
        const files = Array.from(input.files || []);

        if (files.length > 5) {
            alert('Multiple Notes maksimal 5 file.');
            input.value = '';
            selectedIndex = 0;
            renderList();
            return;
        }

        // Batas total sesuai post_max_size server, agar upload tidak gagal diam-diam
        const maxTotalBytes = {{ (int) \Illuminate\Support\Str::of(ini_get('post_max_size'))->replaceMatches('/\D/', '')->toString() ?: 40 }} * 1024 * 1024 * 0.95;
        const totalBytes = files.reduce((sum, f) => sum + f.size, 0);
        if (totalBytes > maxTotalBytes) {
            alert('Total ukuran file terlalu besar (' + (totalBytes / 1048576).toFixed(1) + ' MB). Batas server sekitar {{ ini_get('post_max_size') }}.');
            input.value = '';
            selectedIndex = 0;
            renderList();
            return;
        }

        renderList();
    });

    form.addEventListener('submit', async function (event) {
        event.preventDefault();

        if (isSubmitting) {
            return;
        }

        const files = Array.from(input.files || []);

        if (files.length < 2) {
            alert('Minimal pilih 2 file nota.');
            return;
        }

        if (files.length > 5) {
            alert('Maksimal 5 file nota.');
            return;
        }

        isSubmitting = true;
        openLoadingOverlay();

        try {
            const formData = new FormData();

            files.forEach((file) => {
                formData.append('receipts[]', file);
            });

            const response = await fetch("{{ route('purchase-scan-batches.start') }}", {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').getAttribute('content')
                }
            });

            if (!response.ok) {
                const errorResult = await response.json().catch(() => null);
                throw new Error(errorResult?.message || 'Gagal memulai proses Multiple Notes.');
            }

            const result = await response.json();

            if (!result.success) {
                throw new Error(result.message || 'Gagal memulai proses Multiple Notes.');
            }

            setProgress(5, 'File berhasil diupload. Menunggu proses batch...');
            await pollProgress(result.progress_url);
        } catch (error) {
            alert(error.message || 'Terjadi kesalahan.');
            isSubmitting = false;
            closeLoadingOverlay();
        }
    });

    renderList();
});
</script>
@endsection