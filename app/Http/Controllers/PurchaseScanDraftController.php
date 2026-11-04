<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchaseScanDraftRequest;
use App\Models\PurchaseScanDraft;
use App\Services\Purchases\PurchaseScanDraftService;
use App\Services\Purchases\Ocr\ScanCorrectionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Models\Supplier;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;

class PurchaseScanDraftController extends Controller
{
    public function __construct(
        protected PurchaseScanDraftService $purchaseScanDraftService,
        protected ScanCorrectionService $correctionService,
    ) {
    }

    public function create(): View
    {
        return view('purchase-scans.create');
    }

    public function store(StorePurchaseScanDraftRequest $request): RedirectResponse
    {
        try {
            $draft = $this->purchaseScanDraftService->storeAndProcess(
                file: $request->file('receipt'),
                userId: auth()->id(),
            );
        } catch (\Throwable $exception) {
            \Illuminate\Support\Facades\Log::error('Synchronous purchase scan failed', [
                'user_id' => auth()->id(),
                'exception' => $exception,
            ]);

            return redirect()->route('purchase-scans.create')
                ->with('error', 'Scan nota gagal diproses. Silakan coba lagi atau hubungi administrator.');
        }

        return redirect()
            ->route('purchase-scans.show', $draft)
            ->with('success', 'Nota berhasil di-scan. Silakan review hasilnya.');
    }

    public function show(PurchaseScanDraft $purchaseScanDraft): View
    {
        $this->authorizeDraft($purchaseScanDraft);

        $parsed = is_array($purchaseScanDraft->parsed_json)
            ? $purchaseScanDraft->parsed_json
            : [];

        $rawItems = $parsed['items'] ?? [];
        if (!is_array($rawItems)) {
            $rawItems = [];
        }

        $items = $this->formatDraftItemsForView($rawItems);

        $grandTotal = (int) collect($items)->sum('subtotal');

        $matchedCount = collect($items)->where('match_status', 'matched')->count();
        $manualCount  = collect($items)->where('match_status', '!=', 'matched')->count();
        $lowConfCount = collect($items)->filter(fn($i) => ($i['confidence'] ?? 0) > 0 && ($i['confidence'] ?? 0) < 0.70)->count();

        // Deteksi duplikat berdasarkan content_hash
        $duplicateWarning = null;
        if ($purchaseScanDraft->content_hash) {
            $duplicate = PurchaseScanDraft::where('content_hash', $purchaseScanDraft->content_hash)
                ->where('id', '!=', $purchaseScanDraft->id)
                ->where('created_by', $purchaseScanDraft->created_by)
                ->where('status', '!=', 'failed')
                ->orderByDesc('created_at')
                ->first();

            if ($duplicate) {
                $duplicateWarning = [
                    'id' => $duplicate->id,
                    'created_at' => $duplicate->created_at->translatedFormat('d M Y, H:i'),
                    'status' => $duplicate->status,
                ];
            }
        }

        return view('purchase-scans.show', [
            'purchaseScanDraft' => $purchaseScanDraft,
            'parsed' => $parsed,
            'items' => $items,
            'grandTotal' => $grandTotal,
            'matchedCount' => $matchedCount,
            'manualCount' => $manualCount,
            'lowConfCount' => $lowConfCount,
            'duplicateWarning' => $duplicateWarning,
            'displayDate' => $this->formatDisplayDate(
                $purchaseScanDraft->detected_transaction_date
                    ? (string) $purchaseScanDraft->detected_transaction_date
                    : Arr::get($parsed, 'transaction_date')
            ),
            'supplierName' => Arr::get($parsed, 'supplier_name'),
            'ocrEngine' => Arr::get($parsed, 'meta.ocr_engine', '-'),
            'ocrModel' => Arr::get($parsed, 'meta.model'),
        ]);
    }

    public function import(PurchaseScanDraft $purchaseScanDraft): RedirectResponse
{
    $this->authorizeDraft($purchaseScanDraft);

    if (! in_array($purchaseScanDraft->status, ['processed', 'reviewed'], true)) {
        return back()->with('error', 'Hasil scan belum siap atau sudah pernah digunakan.');
    }

    $parsed = is_array($purchaseScanDraft->parsed_json)
        ? $purchaseScanDraft->parsed_json
        : [];

    $items = $this->normalizeImportItems($parsed['items'] ?? []);

    if (empty($items)) {
        return back()->with('error', 'Tidak ada item hasil scan yang valid untuk diimport.');
    }

    $transactionDate = $purchaseScanDraft->detected_transaction_date
        ? Carbon::parse($purchaseScanDraft->detected_transaction_date)->format('Y-m-d')
        : now()->format('Y-m-d');

    $scannedSupplierName = trim((string) ($parsed['supplier_name'] ?? ''));
    $matchedSupplierId = null;

    if ($scannedSupplierName !== '') {
        $normalizedScannedSupplier = Str::of($scannedSupplierName)
            ->upper()
            ->replaceMatches('/[^A-Z0-9]+/', '')
            ->toString();

        $matchedSupplier = Supplier::query()
            ->where('is_active', true)
            ->get()
            ->first(function ($supplier) use ($normalizedScannedSupplier) {
                $normalizedSupplierName = Str::of($supplier->name)
                    ->upper()
                    ->replaceMatches('/[^A-Z0-9]+/', '')
                    ->toString();

                return $normalizedSupplierName === $normalizedScannedSupplier;
            });

        $matchedSupplierId = $matchedSupplier?->id;
    }

    session()->put('purchase_scan_import', [
        'scan_draft_id' => $purchaseScanDraft->id,
        'transaction_date' => $transactionDate,
        'supplier_id' => $matchedSupplierId,
        'supplier_name' => $scannedSupplierName,
        'input_source' => 'scanner',
        'scan_source_type' => 'single',
        'scan_reference_id' => $purchaseScanDraft->id,
        'note_number' => $purchaseScanDraft->detected_note_number,
        'notes' => 'Import dari hasil scan nota #' . $purchaseScanDraft->id,
        'items' => $items,
    ]);

    $this->purchaseScanDraftService->markReviewed($purchaseScanDraft);

    return redirect()
        ->route('purchase-transactions.create')
        ->with('success', 'Draft scan berhasil dimasukkan ke form transaksi. Silakan cek kembali sebelum simpan.');
}

    protected function authorizeDraft(PurchaseScanDraft $purchaseScanDraft): void
    {
        $user = auth()->user();

        if (!$user) {
            abort(403);
        }

        if (
            method_exists($user, 'isStaff') &&
            $user->isStaff() &&
            (int) $purchaseScanDraft->created_by !== (int) $user->id
        ) {
            abort(403);
        }
    }

    protected function formatDraftItemsForView(array $items): array
    {
        return collect($items)
            ->filter(fn ($item) => is_array($item))
            ->map(function (array $item) {
                $match = is_array($item['match'] ?? null) ? $item['match'] : [];

                $kode = $this->stringOrNull(
                    $item['kode']
                        ?? $item['material_code']
                        ?? null
                );

                $namaMaterial = $this->stringOrNull(
                    $item['nama_material']
                        ?? $item['material_name_snapshot']
                        ?? $item['material_name']
                        ?? $item['name']
                        ?? null
                );

                $satuan = $this->stringOrNull(
                    $item['satuan']
                        ?? $item['unit_name_snapshot']
                        ?? $item['unit']
                        ?? $item['unit_name']
                        ?? null
                );

                $qty = $this->toFloat(
                    $item['qty']
                        ?? $item['quantity']
                        ?? 0
                );

                $harga = $this->toInt(
                    $item['harga']
                        ?? $item['price']
                        ?? 0
                );

                $parsedSubtotal = $this->toInt(
                    $item['subtotal']
                        ?? $item['total']
                        ?? 0
                );

                // Prioritas utama: subtotal hasil OCR/normalizer.
                // Hanya fallback ke qty * harga kalau subtotal memang kosong.
                $subtotal = $parsedSubtotal > 0
                    ? $parsedSubtotal
                    : (($qty > 0 && $harga > 0)
                        ? (int) round($qty * $harga)
                        : 0);

                $matchStatus = $match['status'] ?? (!empty($item['matched']) ? 'matched' : 'manual_check');
                $matchScore = (float) ($match['score'] ?? 0);

                $matchedMaterialCode = $this->stringOrNull($match['material_code'] ?? null);
                $matchedMaterialName = $this->stringOrNull($match['material_name'] ?? null);

                $displayMatchLabel = $matchStatus === 'matched'
                    ? ($matchedMaterialName ?: ($matchedMaterialCode ?: 'Matched'))
                    : 'Manual Check';

                return [
                    'kode' => $kode,
                    'nama_material' => $namaMaterial,
                    'satuan' => $satuan,
                    'qty' => $qty,
                    'harga' => $harga,
                    'subtotal' => $subtotal,
                    'raw_line' => $this->stringOrNull(
                        $item['raw_line']
                            ?? $item['raw_text']
                            ?? null
                    ),
                    'confidence' => (float) ($item['confidence'] ?? 0),
                    'match_status' => $matchStatus,
                    'match_label' => $displayMatchLabel,
                    'match_score' => $matchScore,
                    'match_material_id' => $match['material_id'] ?? null,
                    'match_material_code' => $matchedMaterialCode,
                    'match_material_name' => $matchedMaterialName,
                    'matched' => $matchStatus === 'matched',
                ];
            })
            ->values()
            ->all();
    }

    protected function normalizeImportItems(array $items): array
    {
        $normalized = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $match = is_array($item['match'] ?? null) ? $item['match'] : [];

            $materialName = trim((string) (
                $item['nama_material']
                ?? $item['material_name_snapshot']
                ?? $item['material_name']
                ?? ''
            ));

            $unitName = trim((string) (
                $item['satuan']
                ?? $item['unit_name_snapshot']
                ?? $item['unit_name']
                ?? ''
            ));

            $materialCode = trim((string) (
                $match['material_code']
                ?? $item['kode']
                ?? $item['material_code']
                ?? ''
            ));

            $materialId = $match['material_id']
                ?? $item['material_id']
                ?? null;

            $qty = $this->toFloat(
                $item['qty']
                    ?? $item['quantity']
                    ?? 1
            );

            // Jangan buang item hanya karena qty OCR terbaca 0
            if ($qty <= 0) {
                $qty = 1;
            }

            $price = $this->toInt(
                $item['harga']
                    ?? $item['price']
                    ?? 0
            );

            $parsedSubtotal = $this->toInt(
                $item['subtotal']
                    ?? $item['total']
                    ?? 0
            );

            // Prioritas utama: subtotal hasil OCR/normalizer.
            // Jangan hitung ulang kalau invoice punya diskon per item.
            $subtotal = $parsedSubtotal > 0
                ? $parsedSubtotal
                : (($qty > 0 && $price > 0)
                    ? (int) round($qty * $price)
                    : 0);

            $notesParts = [];

            if (!empty($item['raw_line'])) {
                $notesParts[] = 'OCR: ' . trim((string) $item['raw_line']);
            }

            if (!empty($item['confidence'])) {
                $notesParts[] = 'Confidence: ' . number_format((float) $item['confidence'], 2);
            }

            if (($match['status'] ?? null) !== 'matched') {
                $notesParts[] = 'Perlu manual check';
            }

            if ($qty <= 0 || $price <= 0) {
                $notesParts[] = 'Perlu cek qty / harga';
            }

            $notes = !empty($notesParts)
                ? implode(' | ', $notesParts)
                : 'Hasil scan AI';

            // Hanya buang item kalau nama material kosong
            if ($materialName === '') {
                continue;
            }

            $normalized[] = [
                'material_code' => $materialCode,
                'material_id' => $materialId ?: '',
                'material_name_snapshot' => $materialName,
                'unit_name_snapshot' => $unitName,
                'qty' => $qty,
                'price' => $price,
                'subtotal' => $subtotal,
                'notes' => $notes,
            ];
        }

        return array_values($normalized);
    }

    protected function formatDisplayDate(?string $value): string
    {
        if (!$value) {
            return '-';
        }

        try {
            return Carbon::parse($value)->translatedFormat('d M Y');
        } catch (\Throwable) {
            return $value;
        }
    }

    protected function stringOrNull(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    protected function toFloat(mixed $value): float
    {
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        $value = trim((string) $value);

        if ($value === '') {
            return 0;
        }

        $value = preg_replace('/[^0-9,.\-]/', '', $value) ?? '';

        if ($value === '' || $value === '.' || $value === ',') {
            return 0;
        }

        $hasDot = str_contains($value, '.');
        $hasComma = str_contains($value, ',');

        // format Indonesia campuran: 31.925,00
        if ($hasDot && $hasComma) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);

            return (float) $value;
        }

        // hanya titik: 12.000 / 3.000 / 900
        if ($hasDot && ! $hasComma) {
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

        // hanya koma: 3,5
        if ($hasComma && ! $hasDot) {
            $value = str_replace(',', '.', $value);

            return (float) $value;
        }

        return (float) $value;
    }

    protected function toInt(mixed $value): int
    {
        if (is_int($value) || is_float($value)) {
            return (int) round((float) $value);
        }

        $value = (string) $value;
        $value = str_replace(['Rp', 'rp', ' '], '', $value);
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

    public function compare(PurchaseScanDraft $purchaseScanDraft): View
{
    $this->authorizeDraft($purchaseScanDraft);

    $parsed = is_array($purchaseScanDraft->parsed_json)
        ? $purchaseScanDraft->parsed_json
        : [];

    $rawItems = $parsed['items'] ?? [];
    if (!is_array($rawItems)) {
        $rawItems = [];
    }

    $items = $this->buildCompareItems($rawItems);

    $grandTotal = (int) collect($items)->sum('subtotal');

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

    return view('purchase-scans.compare', [
        'purchaseScanDraft' => $purchaseScanDraft,
        'parsed' => $parsed,
        'items' => $items,
        'grandTotal' => $grandTotal,
        'previewUrl' => $previewUrl,
        'isPdf' => $isPdf,
        'isImage' => $isImage,
    ]);
}

public function updateCompare(Request $request, PurchaseScanDraft $purchaseScanDraft): RedirectResponse
{
    $this->authorizeDraft($purchaseScanDraft);

    abort_unless(in_array($purchaseScanDraft->status, ['processed', 'reviewed'], true), 409, 'Hasil scan belum siap atau sudah digunakan.');

    $request->validate([
        'items' => ['required', 'array', 'min:1'],
        'items.*._deleted' => ['nullable', 'string'],
        'items.*._original_nama' => ['nullable', 'string'],
        'items.*._match_material_id' => ['nullable'],
        'items.*.kode' => ['nullable', 'string', 'max:255'],
        'items.*.nama_material' => ['nullable', 'string', 'max:255'],
        'items.*.satuan' => ['nullable', 'string', 'max:100'],
        'items.*.qty' => ['nullable'],
        'items.*.harga' => ['nullable'],
        'items.*.subtotal' => ['nullable'],
    ]);

    $parsed = is_array($purchaseScanDraft->parsed_json)
        ? $purchaseScanDraft->parsed_json
        : [];

    $existingItems = $parsed['items'] ?? [];
    if (!is_array($existingItems)) {
        $existingItems = [];
    }

    $rebuiltItems = [];
    $submittedItems = $request->input('items', []);
    $userId = (int) auth()->id();

    foreach ($submittedItems as $index => $input) {
        // Skip deleted rows
        if (($input['_deleted'] ?? '0') === '1') {
            continue;
        }

        $namaMaterial = trim((string) ($input['nama_material'] ?? ''));
        if ($namaMaterial === '') {
            continue;
        }

        $existing = is_array($existingItems[$index] ?? null) ? $existingItems[$index] : [];

        $qty = $this->toFloat($input['qty'] ?? 0);
        $harga = $this->toInt($input['harga'] ?? 0);
        $subtotalInput = $this->toInt($input['subtotal'] ?? 0);

        if ($qty <= 0) {
            $qty = 1;
        }

        $subtotal = $subtotalInput > 0
            ? $subtotalInput
            : (($qty > 0 && $harga > 0) ? (int) round($qty * $harga) : 0);

        // Record correction if user changed the material name
        $originalNama = trim((string) ($input['_original_nama'] ?? ''));
        if ($originalNama !== '' && $originalNama !== $namaMaterial) {
            $matchMaterialId = ($input['_match_material_id'] ?? '') !== ''
                ? (int) $input['_match_material_id']
                : null;

            $this->correctionService->record(
                sourceText: $originalNama,
                correctedName: $namaMaterial,
                materialId: $matchMaterialId,
                unitName: $this->stringOrNull($input['satuan'] ?? null),
                userId: $userId,
            );
        }

        $rebuiltItems[] = array_merge($existing, [
            'kode' => $this->stringOrNull($input['kode'] ?? null),
            'nama_material' => $namaMaterial,
            'satuan' => $this->stringOrNull($input['satuan'] ?? null),
            'qty' => $qty,
            'harga' => $harga,
            'subtotal' => $subtotal,
        ]);
    }

    $parsed['items'] = array_values($rebuiltItems);
    $parsed['grand_total'] = (int) collect($rebuiltItems)->sum(fn($i) => (int) ($i['subtotal'] ?? 0));

    $notes = is_array($parsed['notes'] ?? null) ? $parsed['notes'] : [];
    $notes[] = 'Compare mode updated by user on ' . now()->format('Y-m-d H:i:s');
    $parsed['notes'] = array_values(array_unique($notes));

    $purchaseScanDraft->update([
        'parsed_json' => $parsed,
        'status' => 'reviewed',
    ]);

    // "Simpan & Lanjut": koreksi yang baru diketik langsung dipakai untuk form pembelian
    if ($request->input('then') === 'import') {
        return $this->import($purchaseScanDraft->fresh());
    }

    return redirect()
        ->route('purchase-scans.show', $purchaseScanDraft)
        ->with('success', 'Koreksi disimpan. Nama barang yang Anda perbaiki akan diingat untuk scan berikutnya.');
}

protected function buildCompareItems(array $items): array
{
    return collect($items)
        ->filter(fn ($item) => is_array($item))
        ->map(function (array $item, int $index) {
            $kode = $this->stringOrNull(
                $item['kode']
                    ?? $item['material_code']
                    ?? null
            );

            $namaMaterial = $this->stringOrNull(
                $item['nama_material']
                    ?? $item['material_name_snapshot']
                    ?? $item['material_name']
                    ?? $item['name']
                    ?? null
            );

            $satuan = $this->stringOrNull(
                $item['satuan']
                    ?? $item['unit_name_snapshot']
                    ?? $item['unit']
                    ?? $item['unit_name']
                    ?? null
            );

            $qty = $this->toFloat(
                $item['qty']
                    ?? $item['quantity']
                    ?? 0
            );

            $harga = $this->toInt(
                $item['harga']
                    ?? $item['price']
                    ?? 0
            );

            $subtotal = $this->toInt(
                $item['subtotal']
                    ?? $item['total']
                    ?? 0
            );

            if ($subtotal <= 0 && $qty > 0 && $harga > 0) {
                $subtotal = (int) round($qty * $harga);
            }

            $match = is_array($item['match'] ?? null) ? $item['match'] : [];

            return [
                'row_index' => $index,
                'kode' => $kode,
                'nama_material' => $namaMaterial,
                'satuan' => $satuan,
                'qty' => $qty,
                'harga' => $harga,
                'subtotal' => $subtotal,
                'raw_line' => $this->stringOrNull(
                    $item['raw_line']
                        ?? $item['raw_text']
                        ?? null
                ),
                'confidence' => (float) ($item['confidence'] ?? 0),
                'match_material_id' => $match['material_id'] ?? null,
                'match_status' => $match['status'] ?? 'manual_check',
                'match_source' => $match['source'] ?? 'fuzzy',
            ];
        })
        ->values()
        ->all();
}



public function start(Request $request): JsonResponse
{
    $request->validate([
        'receipt' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
    ]);

    try {
        $file = $request->file('receipt');
        $contentHash = hash_file('sha256', $file->getRealPath());
        $storedPath = $file->store('purchase-scan-drafts', 'private');

        // Cek duplikat berdasarkan hash konten file
        $duplicateWarning = null;
        $existingDuplicate = PurchaseScanDraft::where('content_hash', $contentHash)
            ->where('created_by', Auth::id())
            ->where('status', '!=', 'failed')
            ->orderByDesc('created_at')
            ->first();

        if ($existingDuplicate) {
            $duplicateWarning = [
                'id' => $existingDuplicate->id,
                'created_at' => $existingDuplicate->created_at->format('d/m/Y H:i'),
                'status' => $existingDuplicate->status,
                'show_url' => route('purchase-scans.show', $existingDuplicate),
            ];
        }

        $draft = PurchaseScanDraft::create([
            'file_path' => $storedPath,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'content_hash' => $contentHash,
            'mime_type' => $file->getMimeType(),
            'raw_text' => null,
            'parsed_json' => [],
            'detected_note_number' => null,
            'detected_transaction_date' => null,
            'status' => 'uploaded',
            'progress' => 5,
            'progress_message' => 'File berhasil diupload. Menunggu proses scan...',
            'error_message' => null,
            'result_ready' => 0,
            'created_by' => Auth::id(),
        ]);

        \App\Jobs\ProcessPurchaseScanJob::dispatch($draft->id);

        return response()->json([
            'success' => true,
            'draft_id' => $draft->id,
            'progress_url' => route('purchase-scans.progress', $draft),
            'redirect_url' => route('purchase-scans.compare', $draft),
            'duplicate_warning' => $duplicateWarning,
        ]);
    } catch (\Throwable $e) {
        \Illuminate\Support\Facades\Log::error('Purchase scan upload failed', [
            'user_id' => Auth::id(),
            'exception' => $e,
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Upload nota gagal diproses. Silakan coba lagi atau hubungi administrator.',
        ], 500);
    }
}
    public function progress(PurchaseScanDraft $purchaseScanDraft): JsonResponse
    {
        $this->authorizeDraft($purchaseScanDraft);

        return response()->json([
            'id' => $purchaseScanDraft->id,
            'status' => $purchaseScanDraft->status,
            'progress' => (int) $purchaseScanDraft->progress,
            'progress_message' => $purchaseScanDraft->progress_message,
            'error_message' => $purchaseScanDraft->status === 'failed'
                ? 'Proses scan gagal. Silakan coba lagi atau hubungi administrator.'
                : null,
            'result_ready' => (int) $purchaseScanDraft->result_ready,
            'redirect_url' => $purchaseScanDraft->result_ready
                ? route('purchase-scans.compare', $purchaseScanDraft)
                : null,
        ]);
    }

    public function file(PurchaseScanDraft $purchaseScanDraft)
    {
        $this->authorizeDraft($purchaseScanDraft);

        return $this->privateFileResponse($purchaseScanDraft->file_path, $purchaseScanDraft->mime_type);
    }

    protected function privateFileResponse(string $path, ?string $mimeType)
    {
        $disk = Storage::disk('private')->exists($path) ? 'private' : 'public';
        abort_unless(Storage::disk($disk)->exists($path), 404, 'File nota tidak ditemukan.');

        $mime = in_array($mimeType, ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'], true)
            ? $mimeType
            : 'application/octet-stream';

        return response()->file(Storage::disk($disk)->path($path), [
            'Content-Type' => $mime,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store, max-age=0',
        ]);
    }
}
