<?php

namespace App\Services\Purchases\Ocr;

class DummyReceiptOcrService implements ReceiptOcrService
{
    public function extractText(string $absoluteFilePath, string $mimeType = ''): string
    {
        return <<<TEXT
NOTA: INV-00123
TANGGAL: 2026-04-10

SEMEN 2 ZAK 75000
PASIR 1 M3 250000
BATU BATA 100 PCS 850
TEXT;
    }
}