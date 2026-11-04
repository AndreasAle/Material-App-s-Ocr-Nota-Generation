<?php

namespace App\Services\Purchases;

use App\Models\PurchaseScanBatch;
use App\Models\PurchaseScanBatchFile;
use App\Models\Supplier;
use App\Services\Purchases\Ocr\ReceiptAiPipelineService;
use App\Services\Purchases\Ocr\ReceiptOcrService;
use App\Services\Purchases\Ocr\ReceiptParserService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class PurchaseScanBatchService
{
    public function __construct(
        protected ReceiptOcrService $ocrService,
        protected ReceiptParserService $parserService,
        protected ReceiptAiPipelineService $receiptAiPipelineService,
    ) {
    }

    /**
     * @param UploadedFile[] $files
     */
    public function createBatch(array $files, ?int $userId): PurchaseScanBatch
    {
        return DB::transaction(function () use ($files, $userId) {
            $batch = PurchaseScanBatch::create([
                'batch_code' => 'PSB-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(5)),
                'scan_mode' => 'multiple',
                'status' => 'uploaded',
                'supplier_name' => null,
                'supplier_name_normalized' => null,
                'detected_transaction_date' => null,
                'total_files' => count($files),
                'total_items' => 0,
                'grand_total' => 0,
                'progress' => 5,
                'progress_message' => 'File berhasil diupload. Menunggu proses scan batch...',
                'error_message' => null,
                'result_ready' => false,
                'created_by' => $userId,
                'parsed_json' => [
                    'items' => [],
                    'notes' => [],
                    'supplier_names_detected' => [],
                    'file_summaries' => [],
                ],
            ]);

            foreach (array_values($files) as $index => $file) {
                $relativePath = $file->store('purchase-scan-batches', 'private');

                PurchaseScanBatchFile::create([
                    'purchase_scan_batch_id' => $batch->id,
                    'file_path' => $relativePath, // wajib relatif
                    'file_name' => $file->getClientOriginalName(),
                    'file_size' => (int) ($file->getSize() ?: 0),
                    'mime_type' => (string) ($file->getMimeType() ?: ''),
                    'sort_order' => $index + 1,
                    'status' => 'uploaded',
                    'raw_text' => null,
                    'parsed_json' => [],
                    'detected_note_number' => null,
                    'detected_transaction_date' => null,
                    'detected_supplier_name' => null,
                    'detected_supplier_name_normalized' => null,
                    'grand_total' => 0,
                    'error_message' => null,
                ]);
            }

            return $batch->fresh('files');
        });
    }

    public function processBatch(PurchaseScanBatch $batch): PurchaseScanBatch
    {
        $batch->load('files');

        $batch->update([
            'status' => 'processing',
            'progress' => 10,
            'progress_message' => 'Memvalidasi file nota batch...',
            'error_message' => null,
            'result_ready' => false,
        ]);

        $files = $batch->files;
        $totalFiles = max($files->count(), 1);
        $allItems = [];
        $allNotes = [];
        $supplierNamesDetected = [];
        $fileSummaries = [];
        $dateCandidates = [];
        $grandTotal = 0;

        try {
            foreach ($files as $index => $file) {
            $progressStart = 15 + (int) floor(($index / $totalFiles) * 65);
            $progressEnd = 15 + (int) floor((($index + 1) / $totalFiles) * 65);

            $currentNumber = $index + 1;

            $batch->update([
                'progress' => $progressStart,
                'progress_message' => "Memproses nota {$currentNumber} dari {$totalFiles}...",
            ]);

            $this->processSingleBatchFile($file);

            $parsed = is_array($file->parsed_json) ? $file->parsed_json : [];
            $items = $parsed['items'] ?? [];
            $notes = $parsed['notes'] ?? [];

            if (!is_array($items)) {
                $items = [];
            }

            if (!is_array($notes)) {
                $notes = [];
            }

            foreach ($items as $item) {
                if (!is_array($item)) {
                    continue;
                }

                $item['_source_file_id'] = $file->id;
                $item['_source_file_name'] = $file->file_name;
                $allItems[] = $item;
            }

            foreach ($notes as $note) {
                if (is_string($note) && trim($note) !== '') {
                    $allNotes[] = trim($note);
                }
            }

            $supplierName = trim((string) ($parsed['supplier_name'] ?? ''));
            if ($supplierName !== '') {
                $supplierNamesDetected[] = $supplierName;
            }

            $transactionDate = $file->detected_transaction_date
                ? Carbon::parse($file->detected_transaction_date)->toDateString()
                : null;

            if ($transactionDate) {
                $dateCandidates[] = $transactionDate;
            }

            $fileGrandTotal = (int) ($parsed['grand_total'] ?? 0);
            $grandTotal += $fileGrandTotal;

            $fileSummaries[] = [
                'file_id' => $file->id,
                'file_name' => $file->file_name,
                'note_number' => $file->detected_note_number,
                'supplier_name' => $supplierName,
                'transaction_date' => $transactionDate,
                'items_count' => count($items),
                'grand_total' => $fileGrandTotal,
                'status' => $file->status,
            ];

            $batch->update([
                'progress' => $progressEnd,
                'progress_message' => "Nota {$currentNumber} selesai diproses.",
            ]);
        }

            $normalizedSuppliers = collect($supplierNamesDetected)
                ->map(fn ($name) => $this->normalizeSupplierName($name))
                ->filter()
                ->unique()
                ->values();

            if ($normalizedSuppliers->count() > 1) {
                $batch->update([
                    'status' => 'supplier_mismatch',
                    'progress' => 100,
                    'progress_message' => 'Supplier pada beberapa nota berbeda. Batch perlu diperiksa.',
                    'error_message' => 'Multiple Notes hanya menerima nota dari supplier yang sama.',
                    'result_ready' => false,
                    'parsed_json' => [
                        'items' => $allItems,
                        'notes' => array_values(array_unique($allNotes)),
                        'supplier_names_detected' => array_values(array_unique($supplierNamesDetected)),
                        'file_summaries' => $fileSummaries,
                    ],
                ]);

                return $batch->fresh('files');
            }

            $finalSupplierName = collect($supplierNamesDetected)
                ->filter()
                ->countBy()
                ->sortDesc()
                ->keys()
                ->first();

            $dominantDate = collect($dateCandidates)
                ->filter()
                ->countBy()
                ->sortDesc()
                ->keys()
                ->first();

            $batch->update([
                'status' => 'processed',
                'supplier_name' => $finalSupplierName,
                'supplier_name_normalized' => $this->normalizeSupplierName($finalSupplierName),
                'detected_transaction_date' => $dominantDate,
                'total_items' => count($allItems),
                'grand_total' => $grandTotal,
                'progress' => 100,
                'progress_message' => 'Scan batch selesai. Mengalihkan ke halaman review...',
                'error_message' => null,
                'result_ready' => true,
                'processed_at' => now(),
                'parsed_json' => [
                    'items' => array_values($allItems),
                    'notes' => array_values(array_unique($allNotes)),
                    'supplier_name' => $finalSupplierName,
                    'transaction_date' => $dominantDate,
                    'grand_total' => $grandTotal,
                    'supplier_names_detected' => array_values(array_unique($supplierNamesDetected)),
                    'file_summaries' => $fileSummaries,
                    'meta' => [
                        'items_count' => count($allItems),
                        'files_count' => $totalFiles,
                        'processed_at' => now()->toDateTimeString(),
                        'ocr_engine' => strtolower((string) env('OCR_DRIVER', 'tesseract')),
                        'mode' => 'multiple',
                    ],
                ],
            ]);

            return $batch->fresh('files');
        } catch (Throwable $e) {
            Log::error('Purchase scan batch OCR failed', [
                'batch_id' => $batch->id,
                'message' => $e->getMessage(),
            ]);

            $batch->update([
                'status' => 'failed',
                'progress' => 100,
                'progress_message' => 'Proses scan batch gagal.',
                'error_message' => 'Proses scan batch gagal. Silakan coba lagi atau hubungi administrator.',
                'result_ready' => false,
            ]);

            throw $e;
        }
    }

    public function markReviewed(PurchaseScanBatch $batch): void
    {
        $batch->update([
            'status' => 'reviewed',
        ]);
    }

    public function markImported(PurchaseScanBatch $batch): void
    {
        $batch->update([
            'status' => 'imported',
        ]);
    }

    protected function processSingleBatchFile(PurchaseScanBatchFile $file): void
    {
        $file->update([
            'status' => 'processing',
            'error_message' => null,
        ]);

        $ocrEngine = strtolower((string) env('OCR_DRIVER', 'tesseract'));
        $relativePath = $file->file_path; // wajib relatif

        if ($ocrEngine === 'openai') {
            $result = $this->receiptAiPipelineService->processStoredFile($this->diskForPath($relativePath), $relativePath);

            $items = $result['items'] ?? [];
            if (!is_array($items)) {
                $items = [];
            }

            $supplierName = $result['supplier_name'] ?? null;
            $invoiceDate = $result['invoice_date'] ?? null;
            $invoiceNumber = $result['invoice_number'] ?? null;
            $grandTotal = (int) ($result['grand_total'] ?? 0);

            $file->update([
                'raw_text' => $result['raw_response_text'] ?? null,
                'parsed_json' => [
                    'note_number' => $invoiceNumber,
                    'transaction_date' => $invoiceDate,
                    'supplier_name' => $supplierName,
                    'grand_total' => $grandTotal,
                    'items' => array_values($items),
                    'notes' => $result['notes'] ?? [],
                    'meta' => [
                        'items_count' => count($items),
                        'processed_at' => now()->toDateTimeString(),
                        'ocr_engine' => 'openai',
                        'model' => $result['model'] ?? config('receipt_ai.model'),
                        'engine' => $result['engine'] ?? 'openai',
                    ],
                ],
                'detected_note_number' => $invoiceNumber,
                'detected_transaction_date' => $this->normalizeDateForDatabase($invoiceDate),
                'detected_supplier_name' => $supplierName,
                'detected_supplier_name_normalized' => $this->normalizeSupplierName($supplierName),
                'grand_total' => $grandTotal,
                'status' => 'processed',
            ]);

            return;
        }

        $absolutePath = Storage::disk($this->diskForPath($relativePath))->path($relativePath);
        $rawText = $this->ocrService->extractText($absolutePath, (string) $file->mime_type);
        $parsed = $this->parserService->parse($rawText);
        $parsed = is_array($parsed) ? $parsed : [];

        $items = $parsed['items'] ?? [];
        if (!is_array($items)) {
            $items = [];
        }

        $supplierName = $parsed['supplier_name'] ?? null;
        $transactionDate = $parsed['transaction_date'] ?? null;
        $noteNumber = $parsed['note_number'] ?? null;
        $grandTotal = (int) ($parsed['grand_total'] ?? 0);

        $file->update([
            'raw_text' => $rawText,
            'parsed_json' => [
                'note_number' => $noteNumber,
                'transaction_date' => $transactionDate,
                'supplier_name' => $supplierName,
                'grand_total' => $grandTotal,
                'items' => array_values($items),
                'meta' => [
                    'items_count' => count($items),
                    'processed_at' => now()->toDateTimeString(),
                    'ocr_engine' => $ocrEngine,
                ],
            ],
            'detected_note_number' => $noteNumber,
            'detected_transaction_date' => $this->normalizeDateForDatabase($transactionDate),
            'detected_supplier_name' => $supplierName,
            'detected_supplier_name_normalized' => $this->normalizeSupplierName($supplierName),
            'grand_total' => $grandTotal,
            'status' => 'processed',
        ]);
    }

    public function buildImportPayload(PurchaseScanBatch $batch): array
    {
        $parsed = is_array($batch->parsed_json) ? $batch->parsed_json : [];
        $items = $parsed['items'] ?? [];
        if (!is_array($items)) {
            $items = [];
        }

        $normalizedItems = [];
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

            if ($materialName === '') {
                continue;
            }

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

            $qty = $this->toFloat($item['qty'] ?? $item['quantity'] ?? 1);
            if ($qty <= 0) {
                $qty = 1;
            }

            $price = $this->toInt($item['harga'] ?? $item['price'] ?? 0);
            $subtotal = $this->toInt($item['subtotal'] ?? $item['total'] ?? 0);

            if ($subtotal <= 0 && $qty > 0 && $price > 0) {
                $subtotal = (int) round($qty * $price);
            }

            $sourceFileName = $item['_source_file_name'] ?? null;

            $normalizedItems[] = [
                'material_id' => $materialId,
                'material_code' => $materialCode !== '' ? $materialCode : null,
                'material_name' => $materialName,
                'unit_name' => $unitName !== '' ? $unitName : null,
                'qty' => $qty,
                'price' => $price,
                'subtotal' => $subtotal,
                'notes' => $sourceFileName
                    ? 'Import Multiple Notes dari file: ' . $sourceFileName
                    : 'Import Multiple Notes',
            ];
        }

        $supplierName = trim((string) ($batch->supplier_name ?? ''));
        $matchedSupplierId = null;

        if ($supplierName !== '') {
            $normalizedScannedSupplier = $this->normalizeSupplierName($supplierName);

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

        return [
            'scan_batch_id' => $batch->id,
            'input_source' => 'scanner',
            'scan_source_type' => 'multiple',
            'scan_reference_id' => $batch->id,
            'transaction_date' => $batch->detected_transaction_date
                ? Carbon::parse($batch->detected_transaction_date)->format('Y-m-d')
                : now()->format('Y-m-d'),
            'supplier_id' => $matchedSupplierId,
            'supplier_name' => $supplierName,
            'note_number' => $batch->batch_code,
            'notes' => 'Import dari Multiple Notes #' . $batch->batch_code,
            'items' => $normalizedItems,
        ];
    }

    protected function normalizeSupplierName(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        return Str::of($value)
            ->upper()
            ->replaceMatches('/[^A-Z0-9]+/', '')
            ->toString();
    }

    protected function normalizeDateForDatabase(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (Throwable) {
            return null;
        }
    }

    protected function toFloat(mixed $value): float
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

        $value = str_replace(',', '.', $value);

        return (float) $value;
    }

    protected function toInt(mixed $value): int
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

    protected function diskForPath(string $path): string
    {
        return Storage::disk('private')->exists($path) ? 'private' : 'public';
    }
}
