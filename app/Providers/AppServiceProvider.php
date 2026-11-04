<?php

namespace App\Providers;

use App\Services\Purchases\Ocr\OcrSpaceReceiptOcrService;
use App\Services\Purchases\Ocr\PaddleReceiptOcrService;
use App\Services\Purchases\Ocr\ReceiptOcrService;
use App\Services\Purchases\Ocr\TesseractReceiptOcrService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ReceiptOcrService::class, function ($app) {
            $driver = strtolower((string) env('OCR_DRIVER', 'tesseract'));

            return match ($driver) {
                'ocr_space', 'ocrspace' => $app->make(OcrSpaceReceiptOcrService::class),
                'paddle', 'paddleocr' => $app->make(PaddleReceiptOcrService::class),
                'tesseract' => $app->make(TesseractReceiptOcrService::class),
                default => $app->make(TesseractReceiptOcrService::class),
            };
        });
    }

    public function boot(): void
    {
        // Profil toko & pengaturan yang diubah pemilik dari aplikasi
        \App\Models\AppSetting::applyToConfig();

        // Fungsi MySQL untuk laporan saat berjalan di SQLite
        \App\Support\SqliteMysqlFunctions::register();
    }
}