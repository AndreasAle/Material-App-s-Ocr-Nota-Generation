<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessPurchaseScanBatchJob;
use App\Models\PurchaseScanBatch;
use App\Services\Purchases\PurchaseScanBatchService;
use App\Services\Purchases\Ocr\ScanCorrectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PurchaseScanBatchController extends Controller
{
    public function __construct(
        protected PurchaseScanBatchService $purchaseScanBatchService,
        protected ScanCorrectionService $correctionService,
    ) {
    }

    public function create(): View
    {
        return view('purchase-scan-batches.create');
    }

    public function start(Request $request): JsonResponse
    {
        $request->validate([
            'receipts' => ['required', 'array', 'min:2', 'max:5'],
            'receipts.*' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
        ], [
            'receipts.required' => 'Silakan pilih minimal 2 file nota.',
            'receipts.array' => 'Format upload tidak valid.',
            'receipts.min' => 'Multiple Notes minimal 2 nota.',
            'receipts.max' => 'Multiple Notes maksimal 5 nota.',
            'receipts.*.mimes' => 'Format file hanya boleh JPG, JPEG, PNG, WEBP, atau PDF.',
            'receipts.*.max' => 'Ukuran maksimal tiap file adalah 10MB.',
        ]);

        try {
            $files = $request->file('receipts', []);
            $batch = $this->purchaseScanBatchService->createBatch($files, Auth::id());

            ProcessPurchaseScanBatchJob::dispatch($batch->id);

            return response()->json([
                'success' => true,
                'batch_id' => $batch->id,
                'progress_url' => route('purchase-scan-batches.progress', $batch),
                'redirect_url' => route('purchase-scan-batches.show', $batch),
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Purchase scan batch upload failed', [
                'user_id' => Auth::id(),
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Upload nota gagal diproses. Silakan coba lagi atau hubungi administrator.',
            ], 500);
        }
    }

    public function progress(PurchaseScanBatch $purchaseScanBatch): JsonResponse
    {
        $this->authorizeBatch($purchaseScanBatch);

        return response()->json([
            'id' => $purchaseScanBatch->id,
            'status' => $purchaseScanBatch->status,
            'progress' => (int) $purchaseScanBatch->progress,
            'progress_message' => $purchaseScanBatch->progress_message,
            'error_message' => $purchaseScanBatch->status === 'failed'
                ? 'Proses scan gagal. Silakan coba lagi atau hubungi administrator.'
                : null,
            'result_ready' => (int) $purchaseScanBatch->result_ready,
            'redirect_url' => $purchaseScanBatch->result_ready
                ? route('purchase-scan-batches.show', $purchaseScanBatch)
                : null,
        ]);
    }

    public function file(PurchaseScanBatch $purchaseScanBatch, \App\Models\PurchaseScanBatchFile $file)
    {
        $this->authorizeBatch($purchaseScanBatch);
        abort_unless((int) $file->purchase_scan_batch_id === (int) $purchaseScanBatch->id, 404);

        $disk = Storage::disk('private')->exists($file->file_path) ? 'private' : 'public';
        abort_unless(Storage::disk($disk)->exists($file->file_path), 404, 'File nota tidak ditemukan.');

        $mime = in_array($file->mime_type, ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'], true)
            ? $file->mime_type
            : 'application/octet-stream';

        return response()->file(Storage::disk($disk)->path($file->file_path), [
            'Content-Type' => $mime,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store, max-age=0',
        ]);
    }

    public function show(PurchaseScanBatch $purchaseScanBatch): View
    {
        $this->authorizeBatch($purchaseScanBatch);
        $purchaseScanBatch->load('files');

        $parsed = is_array($purchaseScanBatch->parsed_json)
            ? $purchaseScanBatch->parsed_json
            : [];

        $items = $parsed['items'] ?? [];
        if (!is_array($items)) {
            $items = [];
        }

        $fileSummaries = $parsed['file_summaries'] ?? [];
        if (!is_array($fileSummaries)) {
            $fileSummaries = [];
        }

        $displayDate = $purchaseScanBatch->detected_transaction_date
            ? Carbon::parse($purchaseScanBatch->detected_transaction_date)->format('d M Y')
            : '-';

        $filesForView = $purchaseScanBatch->files->map(function ($file) {
            $previewUrl = null;

            try {
                $previewUrl = route('purchase-scan-batches.file', [$file->batch, $file]);
            } catch (\Throwable $e) {
                $previewUrl = null;
            }

            $ext = strtolower(pathinfo((string) $file->file_name, PATHINFO_EXTENSION));

            return [
                'id' => $file->id,
                'file_name' => $file->file_name,
                'preview_url' => $previewUrl,
                'is_pdf' => $ext === 'pdf',
                'is_image' => in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']),
                'note_number' => $file->detected_note_number,
                'supplier_name' => $file->detected_supplier_name,
                'transaction_date' => $file->detected_transaction_date
                    ? Carbon::parse($file->detected_transaction_date)->format('d M Y')
                    : '-',
                'grand_total' => (int) $file->grand_total,
                'status' => $file->status,
            ];
        })->values()->all();

        return view('purchase-scan-batches.show', [
            'purchaseScanBatch' => $purchaseScanBatch,
            'parsed' => $parsed,
            'items' => $items,
            'fileSummaries' => $fileSummaries,
            'filesForView' => $filesForView,
            'grandTotal' => (int) $purchaseScanBatch->grand_total,
            'displayDate' => $displayDate,
            'supplierName' => $purchaseScanBatch->supplier_name ?: 'Belum terdeteksi',
            'totalItems' => count($items),
        ]);
    }

    public function import(PurchaseScanBatch $purchaseScanBatch): RedirectResponse
    {
        $this->authorizeBatch($purchaseScanBatch);

        if ($purchaseScanBatch->status === 'supplier_mismatch') {
            return back()->with('error', 'Batch tidak bisa diimport karena supplier pada nota berbeda.');
        }

        if (! in_array($purchaseScanBatch->status, ['processed', 'reviewed'], true)) {
            return back()->with('error', 'Hasil scan batch belum siap atau sudah pernah digunakan.');
        }

        $payload = $this->purchaseScanBatchService->buildImportPayload($purchaseScanBatch);

        if (empty($payload['items'])) {
            return back()->with('error', 'Tidak ada item valid dari Multiple Notes untuk diimport.');
        }

        session()->put('purchase_scan_import', $payload);

        $this->purchaseScanBatchService->markReviewed($purchaseScanBatch);

        return redirect()
            ->route('purchase-transactions.create')
            ->with('success', 'Multiple Notes berhasil dimasukkan ke form transaksi. Silakan cek kembali sebelum simpan.');
    }

    protected function authorizeBatch(PurchaseScanBatch $purchaseScanBatch): void
    {
        $user = auth()->user();

        if (! $user) {
            abort(403);
        }

        if (
            method_exists($user, 'isStaff') &&
            $user->isStaff() &&
            (int) $purchaseScanBatch->created_by !== (int) $user->id
        ) {
            abort(403);
        }
    }
    public function compare(PurchaseScanBatch $purchaseScanBatch): View
{
    $this->authorizeBatch($purchaseScanBatch);

    $purchaseScanBatch->load('files');

    $parsed = is_array($purchaseScanBatch->parsed_json)
        ? $purchaseScanBatch->parsed_json
        : [];

    $items = $parsed['items'] ?? [];
    if (!is_array($items)) {
        $items = [];
    }

    $filesForView = $purchaseScanBatch->files->map(function ($file) {
        $previewUrl = null;

        try {
            $previewUrl = route('purchase-scan-batches.file', [$file->batch, $file]);
        } catch (\Throwable $e) {
            $previewUrl = null;
        }

        $ext = strtolower(pathinfo((string) $file->file_name, PATHINFO_EXTENSION));

        return [
            'id' => $file->id,
            'file_name' => $file->file_name,
            'preview_url' => $previewUrl,
            'is_pdf' => $ext === 'pdf',
            'is_image' => in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']),
            'note_number' => $file->detected_note_number ?: 'Belum tersedia',
            'supplier_name' => $file->detected_supplier_name ?: 'Belum terdeteksi',
            'transaction_date' => $file->detected_transaction_date
                ? \Illuminate\Support\Carbon::parse($file->detected_transaction_date)->format('d M Y')
                : '-',
            'grand_total' => (int) $file->grand_total,
            'status' => $file->status,
        ];
    })->values()->all();

    $grandTotal = (int) collect($items)->sum(function ($item) {
        return (int) ($item['subtotal'] ?? 0);
    });

    return view('purchase-scan-batches.compare', [
        'purchaseScanBatch' => $purchaseScanBatch,
        'parsed' => $parsed,
        'items' => $items,
        'filesForView' => $filesForView,
        'grandTotal' => $grandTotal,
        'safeSupplier' => $purchaseScanBatch->supplier_name ?: 'Belum terdeteksi',
        'safeBatchCode' => $purchaseScanBatch->batch_code,
    ]);
}

public function updateCompare(Request $request, PurchaseScanBatch $purchaseScanBatch): RedirectResponse
{
    $this->authorizeBatch($purchaseScanBatch);

    abort_unless(in_array($purchaseScanBatch->status, ['processed', 'reviewed'], true), 409, 'Hasil scan belum siap atau sudah digunakan.');

    $parsed = is_array($purchaseScanBatch->parsed_json)
        ? $purchaseScanBatch->parsed_json
        : [];

    $oldItems = $parsed['items'] ?? [];
    if (!is_array($oldItems)) {
        $oldItems = [];
    }

    $submittedItems = $request->input('items', []);
    if (!is_array($submittedItems)) {
        $submittedItems = [];
    }

    $normalizedItems = [];
    $userId = (int) auth()->id();

    foreach ($submittedItems as $index => $item) {
        if (!is_array($item)) {
            continue;
        }

        // Skip deleted rows
        if (($item['_deleted'] ?? '0') === '1') {
            continue;
        }

        $namaMaterial = trim((string) ($item['nama_material'] ?? ''));
        if ($namaMaterial === '') {
            continue;
        }

        $old = $oldItems[$index] ?? [];

        $qty = $this->batchToFloat($item['qty'] ?? 0);
        $harga = $this->batchToInt($item['harga'] ?? 0);
        $subtotal = $this->batchToInt($item['subtotal'] ?? 0);

        if ($subtotal <= 0 && $qty > 0 && $harga > 0) {
            $subtotal = (int) round($qty * $harga);
        }

        // Record correction if user changed the material name
        $originalNama = trim((string) ($item['_original_nama'] ?? ''));
        if ($originalNama !== '' && $originalNama !== $namaMaterial) {
            $matchMaterialId = ($item['_match_material_id'] ?? '') !== ''
                ? (int) $item['_match_material_id']
                : null;

            $this->correctionService->record(
                sourceText: $originalNama,
                correctedName: $namaMaterial,
                materialId: $matchMaterialId,
                unitName: trim((string) ($item['satuan'] ?? '')) ?: null,
                userId: $userId,
            );
        }

        $normalizedItems[] = [
            'kode' => trim((string) ($item['kode'] ?? '')),
            'nama_material' => $namaMaterial,
            'satuan' => trim((string) ($item['satuan'] ?? '')),
            'qty' => $qty,
            'harga' => $harga,
            'subtotal' => $subtotal,
            'raw_line' => $old['raw_line'] ?? null,
            'confidence' => (float) ($old['confidence'] ?? 0),
            'match' => $old['match'] ?? null,
            'normalizer_notes' => $old['normalizer_notes'] ?? [],
            '_source_file_id' => $old['_source_file_id'] ?? null,
            '_source_file_name' => $old['_source_file_name'] ?? null,
        ];
    }

    $grandTotal = (int) collect($normalizedItems)->sum(function ($item) {
        return (int) ($item['subtotal'] ?? 0);
    });

    $parsed['items'] = array_values($normalizedItems);
    $parsed['grand_total'] = $grandTotal;

    $purchaseScanBatch->update([
        'parsed_json' => $parsed,
        'grand_total' => $grandTotal,
        'total_items' => count($normalizedItems),
        'status' => 'reviewed',
    ]);

    if ($request->input('then') === 'import') {
        return $this->import($purchaseScanBatch->fresh());
    }

    return redirect()
        ->route('purchase-scan-batches.show', $purchaseScanBatch)
        ->with('success', 'Koreksi disimpan.');
}

protected function batchToFloat(mixed $value): float
{
    if (is_int($value) || is_float($value)) {
        return (float) $value;
    }

    $value = trim((string) $value);
    $value = preg_replace('/[^0-9,.\-]/', '', $value) ?? '';

    if ($value === '' || $value === '.' || $value === ',') {
        return 0;
    }

    if (str_contains($value, '.') && str_contains($value, ',')) {
        $value = str_replace('.', '', $value);
        $value = str_replace(',', '.', $value);
        return (float) $value;
    }

    if (str_contains($value, '.')) {
        $parts = explode('.', $value);

        if (count($parts) > 1) {
            $allThreeDigits = true;

            for ($i = 1; $i < count($parts); $i++) {
                if (strlen($parts[$i]) !== 3) {
                    $allThreeDigits = false;
                    break;
                }
            }

            if ($allThreeDigits) {
                return (float) str_replace('.', '', $value);
            }
        }
    }

    if (str_contains($value, ',')) {
        $value = str_replace(',', '.', $value);
    }

    return (float) $value;
}

protected function batchToInt(mixed $value): int
{
    if (is_int($value) || is_float($value)) {
        return (int) round((float) $value);
    }

    $value = (string) $value;
    $value = str_replace(['Rp', 'rp', 'IDR', 'idr', ' '], '', $value);
    $value = preg_replace('/[^0-9,.\-]/', '', $value) ?? '';

    if ($value === '') {
        return 0;
    }

    if (str_contains($value, '.')) {
        $value = str_replace('.', '', $value);
    }

    if (str_contains($value, ',')) {
        $parts = explode(',', $value);
        $value = $parts[0];
    }

    return (int) round((float) $value);
}

}
