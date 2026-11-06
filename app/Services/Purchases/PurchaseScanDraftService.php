<?php

namespace App\Services\Purchases;

use App\Models\PurchaseScanDraft;
use App\Services\Purchases\Ocr\ReceiptAiPipelineService;
use App\Services\Purchases\Ocr\ReceiptOcrService;
use App\Services\Purchases\Ocr\ReceiptParserService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class PurchaseScanDraftService
{
    public function __construct(
        protected ReceiptOcrService $ocrService,
        protected ReceiptParserService $parserService,
        protected ReceiptAiPipelineService $receiptAiPipelineService,
    ) {
    }

  public function storeAndProcess(UploadedFile $file, ?int $userId): PurchaseScanDraft
{
    return DB::transaction(function () use ($file, $userId) {
        $path = $file->store('purchase-scan-drafts', 'private');

        $draft = PurchaseScanDraft::create([
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => (int) ($file->getSize() ?: 0),
            'mime_type' => (string) ($file->getMimeType() ?: ''),
            'status' => 'uploaded',
            'progress' => 5,
            'progress_message' => 'File berhasil diupload. Menunggu proses scan...',
            'error_message' => null,
            'result_ready' => 0,
            'created_by' => $userId,
            'raw_text' => null,
            'parsed_json' => [],
            'detected_note_number' => null,
            'detected_transaction_date' => null,
        ]);

        return $this->processExistingDraft($draft, $path);
    });
}
    protected function processWithOpenAi(PurchaseScanDraft $draft, string $path): void
    {
        $result = $this->receiptAiPipelineService->processStoredFile($this->diskForPath($path), $path);

        $items = $result['items'] ?? [];
        if (!is_array($items)) {
            $items = [];
        }

        $draft->update([
            'raw_text' => $result['raw_response_text'] ?? null,
            'parsed_json' => [
                'note_number' => $result['invoice_number'] ?? null,
                'transaction_date' => $result['invoice_date'] ?? null,
                'supplier_name' => $result['supplier_name'] ?? null,
                'grand_total' => (int) ($result['grand_total'] ?? 0),
                'items' => array_values($items),
                'notes' => $result['notes'] ?? [],
                'meta' => [
                    'items_count' => count($items),
                    'processed_at' => now()->toDateTimeString(),
                    'ocr_engine' => 'openai',
                    'model' => $result['model'] ?? config('receipt_ai.model'),
                    'engine' => $result['engine'] ?? 'openai',
                    'timings_ms' => $result['timings_ms'] ?? [],
                ],
            ],
            'detected_note_number' => $result['invoice_number'] ?? null,
            'detected_transaction_date' => $this->normalizeDateForDatabase(
                $result['invoice_date'] ?? null
            ),
            'status' => 'processed',
        ]);
    }

    protected function processWithLegacyOcr(
        PurchaseScanDraft $draft,
        UploadedFile $file,
        string $path,
        string $ocrEngine
    ): void {
        $absolutePath = Storage::disk($this->diskForPath($path))->path($path);

        $rawText = $this->ocrService->extractText(
            $absolutePath,
            (string) $file->getMimeType()
        );

        $parsed = $this->parserService->parse($rawText);
        $parsed = is_array($parsed) ? $parsed : [];

        $items = $parsed['items'] ?? [];
        if (!is_array($items)) {
            $items = [];
        }

        $draft->update([
            'raw_text' => $rawText,
            'parsed_json' => [
                'note_number' => $parsed['note_number'] ?? null,
                'transaction_date' => $parsed['transaction_date'] ?? null,
                'supplier_name' => $parsed['supplier_name'] ?? null,
                'grand_total' => (int) ($parsed['grand_total'] ?? 0),
                'items' => array_values($items),
                'meta' => [
                    'items_count' => count($items),
                    'processed_at' => now()->toDateTimeString(),
                    'ocr_engine' => $ocrEngine,
                ],
            ],
            'detected_note_number' => $parsed['note_number'] ?? null,
            'detected_transaction_date' => $this->normalizeDateForDatabase(
                $parsed['transaction_date'] ?? null
            ),
            'status' => 'processed',
        ]);
    }

    protected function normalizeDateForDatabase(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($value)->toDateString();
        } catch (Throwable) {
            return null;
        }
    }

    public function markReviewed(PurchaseScanDraft $draft): void
    {
        $draft->update([
            'status' => 'reviewed',
        ]);
    }

    public function markImported(PurchaseScanDraft $draft): void
    {
        $draft->update([
            'status' => 'imported',
        ]);
    }

    public function processExistingDraft(PurchaseScanDraft $draft, string $path): PurchaseScanDraft
{
    $ocrEngine = strtolower((string) config('services.ocr.driver', 'tesseract'));

    $draft->update([
        'status' => 'processing',
        'progress' => 15,
        'progress_message' => 'Memvalidasi file nota...',
        'error_message' => null,
        'result_ready' => 0,
    ]);

    try {
        $draft->update([
            'progress' => 35,
            'progress_message' => 'Menyiapkan file untuk dibaca OCR...',
        ]);

        if ($ocrEngine === 'openai') {
            $draft->update([
                'progress' => 55,
                'progress_message' => 'Mengekstrak teks dari gambar nota...',
            ]);

            $this->processWithOpenAi($draft, $path);
        } else {
            $draft->update([
                'progress' => 55,
                'progress_message' => 'Mengekstrak teks dari gambar nota...',
            ]);

            $this->processWithLegacyOcrFromPath($draft, $path, $ocrEngine);
        }

        $draft->update([
            'status' => 'processed',
            'progress' => 90,
            'progress_message' => 'Menyusun item, qty, harga, dan total nota...',
            'error_message' => null,
        ]);

        $draft->update([
            'progress' => 100,
            'progress_message' => 'Scan selesai. Mengalihkan ke halaman review...',
            'result_ready' => 1,
        ]);
    } catch (Throwable $e) {
        Log::error('Purchase scan draft OCR failed', [
            'draft_id' => $draft->id,
            'file_name' => $draft->file_name,
            'message' => $e->getMessage(),
            'ocr_engine' => $ocrEngine,
        ]);

        $draft->update([
            'raw_text' => null,
            'parsed_json' => [
                'note_number' => null,
                'transaction_date' => null,
                'supplier_name' => null,
                'grand_total' => 0,
                'items' => [],
                'meta' => [
                    'items_count' => 0,
                    'processed_at' => now()->toDateTimeString(),
                    'ocr_engine' => $ocrEngine,
                    'error' => 'Proses OCR gagal.',
                ],
            ],
            'status' => 'failed',
            'progress' => 100,
            'progress_message' => 'Proses scan gagal.',
            'error_message' => 'Proses OCR gagal. Silakan coba lagi atau hubungi administrator.',
            'result_ready' => 0,
        ]);

        throw $e;
    }

    return $draft->fresh();
}

protected function processWithLegacyOcrFromPath(
    PurchaseScanDraft $draft,
    string $path,
    string $ocrEngine
): void {
    $absolutePath = Storage::disk($this->diskForPath($path))->path($path);

    $rawText = $this->ocrService->extractText(
        $absolutePath,
        (string) $draft->mime_type
    );

    $parsed = $this->parserService->parse($rawText);
    $parsed = is_array($parsed) ? $parsed : [];

    $items = $parsed['items'] ?? [];
    if (!is_array($items)) {
        $items = [];
    }

    $draft->update([
        'raw_text' => $rawText,
        'parsed_json' => [
            'note_number' => $parsed['note_number'] ?? null,
            'transaction_date' => $parsed['transaction_date'] ?? null,
            'supplier_name' => $parsed['supplier_name'] ?? null,
            'grand_total' => (int) ($parsed['grand_total'] ?? 0),
            'items' => array_values($items),
            'meta' => [
                'items_count' => count($items),
                'processed_at' => now()->toDateTimeString(),
                'ocr_engine' => $ocrEngine,
            ],
        ],
        'detected_note_number' => $parsed['note_number'] ?? null,
        'detected_transaction_date' => $this->normalizeDateForDatabase(
            $parsed['transaction_date'] ?? null
        ),
        'status' => 'processed',
    ]);
}

    protected function diskForPath(string $path): string
    {
        return Storage::disk('private')->exists($path) ? 'private' : 'public';
    }
}
