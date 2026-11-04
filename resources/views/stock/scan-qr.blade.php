@extends('layouts.app')

@section('content')
<x-page-header title="Scan QR Gudang" subtitle="Arahkan kamera ke QR label gudang untuk langsung input gerakan stok">
    <x-slot name="actions">
        <a href="{{ route('stock.index') }}" class="btn-secondary">← Kembali ke Stok</a>
        <a href="{{ route('stock.create') }}" class="btn-primary">✏️ Input Manual</a>
    </x-slot>
</x-page-header>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Scanner --}}
    <x-data-card>
        <h3 class="font-semibold text-slate-800 mb-1">Kamera QR Scanner</h3>
        <p class="text-sm text-slate-500 mb-4">Izinkan akses kamera, lalu arahkan ke QR label gudang.</p>

        <div id="scanner-container" class="relative rounded-2xl overflow-hidden bg-slate-900" style="min-height: 300px;">
            <div id="reader" class="w-full"></div>
            <div id="scanner-overlay" class="absolute inset-0 flex items-center justify-center">
                <div class="border-4 border-indigo-400 rounded-2xl opacity-70" style="width: 220px; height: 220px;"></div>
            </div>
        </div>

        <div id="scan-status" class="mt-3 text-sm text-slate-500 text-center">
            Memuat kamera...
        </div>

        <div class="mt-4 flex gap-2">
            <button id="btn-start" onclick="startScanner()" class="btn-primary flex-1 hidden">
                ▶ Mulai Scan
            </button>
            <button id="btn-stop" onclick="stopScanner()" class="btn-secondary flex-1 hidden">
                ⏹ Stop
            </button>
        </div>
    </x-data-card>

    {{-- Hasil Scan --}}
    <x-data-card x-data="{ result: null, loading: false }">
        <h3 class="font-semibold text-slate-800 mb-1">Hasil Scan</h3>
        <p class="text-sm text-slate-500 mb-4">Info material & gudang dari QR akan muncul di sini.</p>

        {{-- Placeholder --}}
        <div id="scan-placeholder" class="text-center py-16 text-slate-400">
            <div class="text-5xl mb-3">🔲</div>
            <p class="font-medium">Belum ada QR yang dipindai</p>
            <p class="text-xs mt-1">Arahkan kamera ke QR label gudang</p>
        </div>

        {{-- Result Card --}}
        <div id="scan-result" class="hidden">
            <div class="rounded-2xl bg-indigo-50 border border-indigo-200 p-5 mb-4">
                <div class="flex items-start gap-3">
                    <div class="text-3xl">📦</div>
                    <div>
                        <p class="font-bold text-slate-900 text-lg" id="res-material">—</p>
                        <p class="text-sm text-slate-500" id="res-unit">—</p>
                        <div class="mt-2 flex items-center gap-2">
                            <span class="text-xs bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full font-medium">
                                🏭 <span id="res-warehouse">—</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-2">
                <a id="btn-stock-out"
                   href="#"
                   class="flex items-center justify-between rounded-2xl bg-rose-50 border border-rose-200 px-4 py-3 text-sm font-semibold text-rose-700 hover:bg-rose-100 transition-colors">
                    <span>📤 Catat Pengeluaran (OUT)</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                    </svg>
                </a>
                <a id="btn-stock-in"
                   href="#"
                   class="flex items-center justify-between rounded-2xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm font-semibold text-emerald-700 hover:bg-emerald-100 transition-colors">
                    <span>📥 Catat Penerimaan (IN)</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                    </svg>
                </a>
                <button id="btn-scan-again"
                        onclick="resumeScan()"
                        class="w-full flex items-center justify-center gap-2 rounded-2xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">
                    🔄 Scan Ulang
                </button>
            </div>
        </div>

        {{-- Raw QR data (jika format tidak dikenal) --}}
        <div id="scan-raw" class="hidden mt-4">
            <div class="rounded-2xl bg-amber-50 border border-amber-200 p-4">
                <p class="text-xs font-bold uppercase tracking-wider text-amber-600 mb-2">QR Tidak Dikenal</p>
                <p class="text-sm font-mono text-slate-700 break-all" id="res-raw">—</p>
            </div>
            <button onclick="resumeScan()" class="mt-3 btn-secondary w-full">🔄 Scan Ulang</button>
        </div>
    </x-data-card>
</div>

{{-- Petunjuk --}}
<x-data-card class="mt-6">
    <h4 class="font-semibold text-slate-700 mb-3">📖 Cara Penggunaan</h4>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm text-slate-600">
        <div class="flex gap-3">
            <span class="text-2xl">1️⃣</span>
            <p>Buka halaman <strong>Gudang → QR Label</strong>, cetak label QR untuk kombinasi material + gudang</p>
        </div>
        <div class="flex gap-3">
            <span class="text-2xl">2️⃣</span>
            <p>Kembali ke halaman ini, arahkan kamera HP ke QR label yang sudah dicetak</p>
        </div>
        <div class="flex gap-3">
            <span class="text-2xl">3️⃣</span>
            <p>Klik <strong>Catat IN</strong> atau <strong>Catat OUT</strong> — form akan ter-isi otomatis dengan material & gudang</p>
        </div>
    </div>
</x-data-card>

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
let html5QrCode = null;
let scannerActive = false;

const baseUrl = '{{ route('stock.create') }}';

function onScanSuccess(decodedText) {
    try {
        const data = JSON.parse(decodedText);

        if (data.type === 'material_warehouse' && data.material_id) {
            // Valid NOTAGO QR
            document.getElementById('scan-placeholder').classList.add('hidden');
            document.getElementById('scan-raw').classList.add('hidden');

            document.getElementById('res-material').textContent   = data.material_name  || 'Material ID: ' + data.material_id;
            document.getElementById('res-unit').textContent        = data.unit            || '—';
            document.getElementById('res-warehouse').textContent   = data.warehouse_name  || 'Gudang ID: ' + data.warehouse_id;

            const params = new URLSearchParams({
                material_id:  data.material_id,
                warehouse_id: data.warehouse_id || '',
            });

            document.getElementById('btn-stock-out').href = baseUrl + '?movement_type=out&' + params;
            document.getElementById('btn-stock-in').href  = baseUrl + '?movement_type=in&'  + params;

            document.getElementById('scan-result').classList.remove('hidden');

            document.getElementById('scan-status').textContent = '✅ QR berhasil dibaca!';
            stopScanner();

        } else {
            // Unknown QR
            showRaw(decodedText);
        }
    } catch {
        showRaw(decodedText);
    }
}

function showRaw(text) {
    document.getElementById('scan-placeholder').classList.add('hidden');
    document.getElementById('scan-result').classList.add('hidden');
    document.getElementById('res-raw').textContent = text;
    document.getElementById('scan-raw').classList.remove('hidden');
    document.getElementById('scan-status').textContent = '⚠️ QR tidak dikenal';
    stopScanner();
}

function startScanner() {
    if (scannerActive) return;
    html5QrCode = new Html5Qrcode("reader");
    html5QrCode.start(
        { facingMode: "environment" },
        { fps: 10, qrbox: { width: 220, height: 220 } },
        onScanSuccess
    ).then(() => {
        scannerActive = true;
        document.getElementById('scan-status').textContent = '📷 Kamera aktif — arahkan ke QR...';
        document.getElementById('scanner-overlay').style.display = 'block';
        document.getElementById('btn-start').classList.add('hidden');
        document.getElementById('btn-stop').classList.remove('hidden');
    }).catch(err => {
        document.getElementById('scan-status').textContent = '❌ Tidak bisa akses kamera: ' + err;
        document.getElementById('btn-start').classList.remove('hidden');
    });
}

function stopScanner() {
    if (html5QrCode && scannerActive) {
        html5QrCode.stop().then(() => {
            scannerActive = false;
            document.getElementById('btn-stop').classList.add('hidden');
            document.getElementById('btn-start').classList.remove('hidden');
        });
    }
}

function resumeScan() {
    document.getElementById('scan-result').classList.add('hidden');
    document.getElementById('scan-raw').classList.add('hidden');
    document.getElementById('scan-placeholder').classList.remove('hidden');
    startScanner();
}

// Auto-start on page load
window.addEventListener('DOMContentLoaded', () => {
    document.getElementById('scan-status').textContent = 'Klik "Mulai Scan" untuk mengaktifkan kamera';
    document.getElementById('btn-start').classList.remove('hidden');
    // Try auto-start
    startScanner();
});
</script>
@endpush
@endsection
