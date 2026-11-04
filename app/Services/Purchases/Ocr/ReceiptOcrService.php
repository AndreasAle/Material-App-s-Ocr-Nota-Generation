<?php

namespace App\Services\Purchases\Ocr;

interface ReceiptOcrService
{
    public function extractText(string $absoluteFilePath, string $mimeType = ''): string;
}