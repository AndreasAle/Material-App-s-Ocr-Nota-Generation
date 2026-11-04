<?php

namespace App\Services\Purchases\Ocr;

use Google\Cloud\Vision\V1\AnnotateImageRequest;
use Google\Cloud\Vision\V1\BatchAnnotateImagesRequest;
use Google\Cloud\Vision\V1\Client\ImageAnnotatorClient;
use Google\Cloud\Vision\V1\Feature;
use Google\Cloud\Vision\V1\Image;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class GoogleVisionReceiptOcrService implements ReceiptOcrService
{
    public function extractText(string $absoluteFilePath, string $mimeType = ''): string
    {
        if (! is_file($absoluteFilePath)) {
            throw new RuntimeException('File OCR tidak ditemukan: ' . $absoluteFilePath);
        }

        $mimeType = strtolower(trim($mimeType));

        if ($mimeType === 'application/pdf') {
            throw new RuntimeException('Versi awal ini fokus untuk file gambar. PDF nanti kita buat batchAnnotateFiles().');
        }

        $client = new ImageAnnotatorClient([
            'credentials' => config('services.google_vision.credentials'),
        ]);

        try {
            $imageContent = file_get_contents($absoluteFilePath);

            if ($imageContent === false) {
                throw new RuntimeException('Gagal membaca file OCR.');
            }

            $image = (new Image())
                ->setContent($imageContent);

            $feature = (new Feature())
                ->setType(Feature\Type::DOCUMENT_TEXT_DETECTION);

            $imageRequest = (new AnnotateImageRequest())
                ->setImage($image)
                ->setFeatures([$feature]);

            $request = (new BatchAnnotateImagesRequest())
                ->setRequests([$imageRequest]);

            $response = $client->batchAnnotateImages($request);
            $responses = $response->getResponses();

            if (count($responses) === 0) {
                throw new RuntimeException('Tidak ada response dari Google Vision.');
            }

            $first = $responses[0];

            $error = $first->getError();
            if ($error && $error->getMessage()) {
                throw new RuntimeException('Google Vision error: ' . $error->getMessage());
            }

            $fullText = $first->getFullTextAnnotation();
            $text = $fullText ? $fullText->getText() : '';

            if (trim($text) === '') {
                $annotations = $first->getTextAnnotations();
                if (count($annotations) > 0) {
                    $text = $annotations[0]->getDescription();
                }
            }

            return trim((string) $text);
        } catch (\Throwable $e) {
            Log::error('Google Vision OCR gagal', [
                'file' => $absoluteFilePath,
                'mime_type' => $mimeType,
                'message' => $e->getMessage(),
            ]);

            throw $e;
        } finally {
            $client->close();
        }
    }
}