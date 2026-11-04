<?php

namespace App\Services\Purchases\Ocr;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class OcrSpaceReceiptOcrService implements ReceiptOcrService
{
    public function extractText(string $absoluteFilePath, string $mimeType = ''): string
    {
        if (! is_file($absoluteFilePath)) {
            throw new RuntimeException('File OCR tidak ditemukan: ' . $absoluteFilePath);
        }

        $apiKey = (string) config('services.ocr_space.api_key');
        $endpoint = (string) config('services.ocr_space.endpoint', 'https://api.ocr.space/parse/image');
        $language = (string) config('services.ocr_space.language', 'eng');
        $engine = (int) config('services.ocr_space.engine', 2);
        $overlay = (bool) config('services.ocr_space.overlay', false);

        if ($apiKey === '') {
            throw new RuntimeException('OCR_SPACE_API_KEY belum diatur di .env');
        }

        $filename = basename($absoluteFilePath);
        $fileHandle = fopen($absoluteFilePath, 'r');

        if ($fileHandle === false) {
            throw new RuntimeException('Gagal membuka file OCR: ' . $absoluteFilePath);
        }

        try {
            $response = Http::timeout(120)
                ->withHeaders([
                    'apikey' => $apiKey,
                ])
                ->attach('file', $fileHandle, $filename)
                ->post($endpoint, [
                    'language' => $language,
                    'isOverlayRequired' => $overlay ? 'true' : 'false',
                    'OCREngine' => $engine,
                    'scale' => 'true',
                    'isTable' => 'true',
                ]);
        } finally {
            fclose($fileHandle);
        }

        if (! $response->successful()) {
            Log::error('OCR.space HTTP request gagal', [
                'status' => $response->status(),
                'body' => $response->body(),
                'file' => $absoluteFilePath,
            ]);

            throw new RuntimeException('OCR.space request gagal dengan status HTTP ' . $response->status());
        }

        $json = $response->json();

        if (! is_array($json)) {
            throw new RuntimeException('Response OCR.space tidak valid JSON.');
        }

        if (($json['IsErroredOnProcessing'] ?? false) === true) {
            $message = 'OCR.space gagal memproses file.';
            $errors = $json['ErrorMessage'] ?? null;

            if (is_array($errors) && count($errors) > 0) {
                $message .= ' ' . implode(' | ', array_map('strval', $errors));
            } elseif (is_string($errors) && $errors !== '') {
                $message .= ' ' . $errors;
            }

            throw new RuntimeException(trim($message));
        }

        $parsedResults = $json['ParsedResults'] ?? [];
        if (! is_array($parsedResults) || empty($parsedResults)) {
            throw new RuntimeException('OCR.space tidak mengembalikan ParsedResults.');
        }

        $texts = [];
        foreach ($parsedResults as $result) {
            if (! is_array($result)) {
                continue;
            }

            $text = trim((string) ($result['ParsedText'] ?? ''));
            if ($text !== '') {
                $texts[] = $text;
            }
        }

        $rawText = trim(implode("\n", $texts));
        $rawText = $this->postProcessOcrText($rawText);

        if ($rawText === '') {
            throw new RuntimeException('OCR.space berhasil dipanggil tetapi teks kosong.');
        }

        Log::info('OCR.space success', [
            'file' => $absoluteFilePath,
            'engine' => 'ocr_space',
            'parsed_results_count' => count($parsedResults),
        ]);

        return $rawText;
    }

    protected function postProcessOcrText(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        $replace = [
            'NOSPP' => 'NO.SPP',
            'NO SPP' => 'NO.SPP',
            'HARGASAT' => 'HARGA/SAT',
            '1BR' => 'LBR',
            'IBR' => 'LBR',
            'KLG.' => 'KLG',
            'LBR.' => 'LBR',
            'M 3' => 'M3',
            'M-3' => 'M3',
            'RP.' => 'RP',
        ];

        $text = str_ireplace(array_keys($replace), array_values($replace), $text);
        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = preg_replace("/\n{2,}/", "\n", $text);

        return trim((string) $text);
    }
}