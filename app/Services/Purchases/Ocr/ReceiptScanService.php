<?php

namespace App\Services\Purchases\Ocr;

class ReceiptScanService
{
    public function __construct(
        protected ReceiptOcrService $ocrService,
        protected ReceiptParserService $parserService,
    ) {
    }

    public function scan(string $absoluteFilePath, string $mimeType = ''): array
    {
        $rawText = $this->ocrService->extractText($absoluteFilePath, $mimeType);
        $parsed = $this->parserService->parse($rawText);

        return [
            'raw_text' => $rawText,
            'note_number' => $parsed['note_number'] ?? null,
            'transaction_date' => $parsed['transaction_date'] ?? null,
            'items' => $parsed['items'] ?? [],
        ];
    }
}