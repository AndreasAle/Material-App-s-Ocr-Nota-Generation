<?php

namespace App\Services\Purchases\Ocr;

use Illuminate\Support\Facades\Log;
use RuntimeException;

class TesseractReceiptOcrService implements ReceiptOcrService
{
    public function extractText(string $absoluteFilePath, string $mimeType = ''): string
    {
        if (! is_file($absoluteFilePath)) {
            throw new RuntimeException('File OCR tidak ditemukan: ' . $absoluteFilePath);
        }

        $tesseractPath = (string) config('services.tesseract.path');
        $lang = (string) config('services.tesseract.lang', 'ind+eng');
        $defaultPsm = (int) config('services.tesseract.psm', 6);
        $defaultOem = (int) config('services.tesseract.oem', 1);

        if ($tesseractPath === '') {
            throw new RuntimeException('Path Tesseract belum diatur di .env');
        }

        if (! is_file($tesseractPath)) {
            throw new RuntimeException('Executable Tesseract tidak ditemukan di: ' . $tesseractPath);
        }

        $tmpDir = storage_path('app/tmp/ocr');
        if (! is_dir($tmpDir) && ! mkdir($tmpDir, 0777, true) && ! is_dir($tmpDir)) {
            throw new RuntimeException('Gagal membuat folder temporary OCR.');
        }

        $sessionDir = $tmpDir . DIRECTORY_SEPARATOR . 'session_' . uniqid();
        if (! mkdir($sessionDir, 0777, true) && ! is_dir($sessionDir)) {
            throw new RuntimeException('Gagal membuat folder session OCR.');
        }

        try {
            $variants = $this->prepareImageVariants($absoluteFilePath, $sessionDir);

            $strategies = [
                ['psm' => $defaultPsm, 'oem' => $defaultOem],
                ['psm' => 6, 'oem' => 1],
                ['psm' => 4, 'oem' => 1],
                ['psm' => 11, 'oem' => 1],
            ];

            $bestText = '';
            $bestScore = -1;
            $debug = [];

            foreach ($variants as $variantPath) {
                foreach ($strategies as $strategy) {
                    $text = $this->runTesseract(
                        tesseractPath: $tesseractPath,
                        imagePath: $variantPath,
                        lang: $lang,
                        psm: $strategy['psm'],
                        oem: $strategy['oem'],
                        workDir: $sessionDir
                    );

                    $score = $this->scoreOcrText($text);

                    $debug[] = [
                        'variant' => basename($variantPath),
                        'psm' => $strategy['psm'],
                        'oem' => $strategy['oem'],
                        'score' => $score,
                        'preview' => mb_substr($text, 0, 200),
                    ];

                    if ($score > $bestScore) {
                        $bestScore = $score;
                        $bestText = $text;
                    }
                }
            }

            Log::info('OCR strategy results', $debug);

            $bestText = $this->postProcessOcrText($bestText);

            if (trim($bestText) === '') {
                throw new RuntimeException('OCR selesai tetapi teks kosong.');
            }

            return trim($bestText);
        } catch (\Throwable $e) {
            Log::error('Tesseract OCR gagal', [
                'file' => $absoluteFilePath,
                'message' => $e->getMessage(),
            ]);

            throw $e;
        } finally {
            $this->deleteDirectory($sessionDir);
        }
    }

    protected function prepareImageVariants(string $sourcePath, string $sessionDir): array
    {
        $variants = [];

        $originalCopy = $sessionDir . DIRECTORY_SEPARATOR . 'original' . $this->guessExt($sourcePath);
        copy($sourcePath, $originalCopy);
        $variants[] = $originalCopy;

        if (extension_loaded('imagick')) {
            try {
                $img = new \Imagick($sourcePath);
                $img->setImageColorspace(\Imagick::COLORSPACE_GRAY);
                $img->setImageType(\Imagick::IMGTYPE_GRAYSCALE);
                $img->setImageResolution(300, 300);
                $img->resampleImage(300, 300, \Imagick::FILTER_LANCZOS, 1);
                $img->deskewImage(40);
                $img->contrastStretchImage(0.1, 0.1);
                $img->sharpenImage(1, 0.8);

                $img1 = clone $img;
                $img1->resizeImage(
                    $img1->getImageWidth() * 2,
                    $img1->getImageHeight() * 2,
                    \Imagick::FILTER_LANCZOS,
                    1
                );
                $img1->thresholdImage(0.68 * \Imagick::getQuantum());
                $path1 = $sessionDir . DIRECTORY_SEPARATOR . 'variant_bw.png';
                $img1->writeImage($path1);
                $variants[] = $path1;

                $img2 = clone $img;
                $img2->resizeImage(
                    $img2->getImageWidth() * 2,
                    $img2->getImageHeight() * 2,
                    \Imagick::FILTER_LANCZOS,
                    1
                );
                $img2->normalizeImage();
                $img2->adaptiveSharpenImage(2, 1);
                $path2 = $sessionDir . DIRECTORY_SEPARATOR . 'variant_gray.png';
                $img2->writeImage($path2);
                $variants[] = $path2;

                $img3 = clone $img;
                $img3->resizeImage(
                    $img3->getImageWidth() * 3,
                    $img3->getImageHeight() * 3,
                    \Imagick::FILTER_LANCZOS,
                    1
                );
                $img3->thresholdImage(0.78 * \Imagick::getQuantum());
                $img3->morphology(
                    \Imagick::MORPHOLOGY_CLOSE,
                    1,
                    'rectangle:1x1'
                );
                $path3 = $sessionDir . DIRECTORY_SEPARATOR . 'variant_strong_bw.png';
                $img3->writeImage($path3);
                $variants[] = $path3;

                $img->clear();
                $img->destroy();
            } catch (\Throwable $e) {
                Log::warning('Imagick preprocess gagal, lanjut pakai original', [
                    'message' => $e->getMessage(),
                ]);
            }
        }

        return array_values(array_unique(array_filter($variants, fn ($v) => is_file($v))));
    }

    protected function runTesseract(
        string $tesseractPath,
        string $imagePath,
        string $lang,
        int $psm,
        int $oem,
        string $workDir
    ): string {
        $outputBase = $workDir . DIRECTORY_SEPARATOR . 'ocr_' . uniqid();
        $outputTxt = $outputBase . '.txt';

        $command = sprintf(
            '"%s" "%s" "%s" -l %s --oem %d --psm %d -c preserve_interword_spaces=1 -c tessedit_char_blacklist=§©®™',
            $tesseractPath,
            $imagePath,
            $outputBase,
            escapeshellarg($lang),
            $oem,
            $psm
        );

        exec($command . ' 2>&1', $output, $exitCode);

        if ($exitCode !== 0) {
            throw new RuntimeException('Tesseract gagal: ' . implode("\n", $output));
        }

        if (! file_exists($outputTxt)) {
            throw new RuntimeException('File output OCR tidak ditemukan.');
        }

        $text = file_get_contents($outputTxt);

        if ($text === false) {
            throw new RuntimeException('Gagal membaca hasil OCR.');
        }

        @unlink($outputTxt);

        return trim($text);
    }

    protected function scoreOcrText(string $text): int
    {
        $score = 0;
        $lower = mb_strtolower($text);

        $keywords = [
            'surat pesanan pembelian' => 40,
            'pesanan pembelian' => 30,
            'kuantitas' => 20,
            'harga/sat' => 20,
            'jumlah' => 20,
            'kayu' => 12,
            'amplas' => 12,
            'dempul' => 12,
            'cat' => 8,
            'minyak' => 8,
            'rp' => 3,
        ];

        foreach ($keywords as $word => $value) {
            if (str_contains($lower, $word)) {
                $score += $value;
            }
        }

        preg_match_all('/\b\d{1,3}(?:\.\d{3})+\b/', $text, $moneyMatches);
        $score += count($moneyMatches[0] ?? []) * 3;

        preg_match_all('/\b(?:m3|kg|klg|lbr|pcs|sak|zak|unit)\b/i', $text, $unitMatches);
        $score += count($unitMatches[0] ?? []) * 4;

        $lines = preg_split('/\r\n|\r|\n/', $text);
        $score += count(array_filter($lines, fn ($line) => trim($line) !== '')) * 1;

        return $score;
    }

    protected function postProcessOcrText(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        $replace = [
            'KUANTITAS' => 'KUANTITAS',
            'HARGA/SAT' => 'HARGA/SAT',
            'HARGASAT' => 'HARGA/SAT',
            'JUMLAH' => 'JUMLAH',
            'SATUAN' => 'SATUAN',
            'SPFAT' => 'SIFAT',
            'NOSPP' => 'NO.SPP',
            'NO SPP' => 'NO.SPP',
            'KAYU JALI' => 'KAYU JATI',
            '1HR' => 'LBR',
            'LBR.' => 'LBR',
            'KLG.' => 'KLG',
        ];

        $text = str_ireplace(array_keys($replace), array_values($replace), $text);

        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = preg_replace("/\n{2,}/", "\n", $text);

        return trim((string) $text);
    }

    protected function guessExt(string $path): string
    {
        $ext = pathinfo($path, PATHINFO_EXTENSION);

        return $ext ? '.' . $ext : '.jpg';
    }

    protected function deleteDirectory(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $files = scandir($dir);
        if ($files === false) {
            return;
        }

        foreach ($files as $file) {
            if (in_array($file, ['.', '..'], true)) {
                continue;
            }

            $path = $dir . DIRECTORY_SEPARATOR . $file;

            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                @unlink($path);
            }
        }

        @rmdir($dir);
    }
}