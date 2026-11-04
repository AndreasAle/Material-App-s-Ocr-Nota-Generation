<?php

namespace App\Services\Purchases\Ocr;

use Illuminate\Support\Facades\Log;
use RuntimeException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class PaddleReceiptOcrService implements ReceiptOcrService
{
    public function extractText(string $absoluteFilePath, string $mimeType = ''): string
    {
        if (! is_file($absoluteFilePath)) {
            throw new RuntimeException('File OCR tidak ditemukan: ' . $absoluteFilePath);
        }

        $pythonPath = (string) config('services.paddle_ocr.python_path');
        $scriptPath = (string) config('services.paddle_ocr.script_path');
        $lang = (string) config('services.paddle_ocr.lang', 'id');
        $useGpu = (bool) config('services.paddle_ocr.use_gpu', false);
        $enableMkldnn = (bool) config('services.paddle_ocr.enable_mkldnn', true);
        $detLimitSideLen = (int) config('services.paddle_ocr.det_limit_side_len', 1216);
        $ocrVersion = (string) config('services.paddle_ocr.ocr_version', 'PP-OCRv4');

        if ($pythonPath === '') {
            throw new RuntimeException('Path Python PaddleOCR belum diatur di .env');
        }

        if (! is_file($pythonPath)) {
            throw new RuntimeException('Executable Python PaddleOCR tidak ditemukan di: ' . $pythonPath);
        }

        if ($scriptPath === '') {
            throw new RuntimeException('Path script PaddleOCR belum diatur di .env');
        }

        if (
            ! str_contains($scriptPath, ':') &&
            ! str_starts_with($scriptPath, '/') &&
            ! str_starts_with($scriptPath, '\\')
        ) {
            $scriptPath = base_path($scriptPath);
        }

        if (! is_file($scriptPath)) {
            throw new RuntimeException('Script PaddleOCR tidak ditemukan di: ' . $scriptPath);
        }

        $tempDir = storage_path('app/tmp/paddleocr');
        if (! is_dir($tempDir) && ! mkdir($tempDir, 0777, true) && ! is_dir($tempDir)) {
            throw new RuntimeException('Folder temporary PaddleOCR gagal dibuat: ' . $tempDir);
        }

        $env = $this->buildWindowsSafeEnv($tempDir);

        $this->runHealthCheck($pythonPath, base_path(), $env);

        $command = [
            $pythonPath,
            $scriptPath,
            '--file', $absoluteFilePath,
            '--lang', $lang,
            '--use-gpu', $useGpu ? '1' : '0',
            '--enable-mkldnn', $enableMkldnn ? '1' : '0',
            '--det-limit-side-len', (string) $detLimitSideLen,
            '--ocr-version', $ocrVersion,
            '--mime-type', $mimeType,
        ];

        $process = new Process($command, base_path(), $env);
        $process->setTimeout(300);
        $process->run();

        if (! $process->isSuccessful()) {
            Log::error('PaddleOCR process gagal', [
                'file' => $absoluteFilePath,
                'command' => $command,
                'cwd' => base_path(),
                'stderr' => $process->getErrorOutput(),
                'stdout' => $process->getOutput(),
                'env_debug' => $this->maskEnvForLog($env),
            ]);

            throw new ProcessFailedException($process);
        }

        $output = trim($process->getOutput());

        if ($output === '') {
            throw new RuntimeException('PaddleOCR selesai tetapi output kosong.');
        }

        $decoded = json_decode($output, true);

        if (! is_array($decoded)) {
            throw new RuntimeException('Output PaddleOCR tidak valid JSON. Output asli: ' . $output);
        }

        if (($decoded['success'] ?? false) !== true) {
            throw new RuntimeException((string) ($decoded['message'] ?? 'PaddleOCR gagal.'));
        }

        $text = $this->postProcessOcrText((string) ($decoded['text'] ?? ''));

        if (trim($text) === '') {
            throw new RuntimeException('PaddleOCR berhasil tetapi teks kosong setelah post-process.');
        }

        Log::info('PaddleOCR success', [
            'file' => $absoluteFilePath,
            'engine' => 'paddleocr',
            'lines' => (int) ($decoded['meta']['line_count'] ?? 0),
            'avg_confidence' => (float) ($decoded['meta']['avg_confidence'] ?? 0),
        ]);

        return trim($text);
    }

    protected function buildWindowsSafeEnv(string $tempDir): array
    {
        $systemRoot = getenv('SystemRoot') ?: getenv('SYSTEMROOT') ?: 'C:\\Windows';
        $windir = getenv('WINDIR') ?: $systemRoot;
        $userProfile = getenv('USERPROFILE') ?: '';
        $homeDrive = getenv('HOMEDRIVE') ?: '';
        $homePath = getenv('HOMEPATH') ?: '';
        $path = getenv('PATH') ?: getenv('Path') ?: '';

        $temp = getenv('TEMP') ?: $tempDir;
        $tmp = getenv('TMP') ?: $tempDir;

        return [
            'SystemRoot' => $systemRoot,
            'SYSTEMROOT' => $systemRoot,
            'WINDIR' => $windir,
            'USERPROFILE' => $userProfile,
            'HOMEDRIVE' => $homeDrive,
            'HOMEPATH' => $homePath,
            'TEMP' => $temp,
            'TMP' => $tmp,
            'PATH' => $path,
            'PYTHONIOENCODING' => 'utf-8',
        ];
    }

    protected function runHealthCheck(string $pythonPath, string $workingDir, array $env): void
    {
        $check = new Process(
            [$pythonPath, '-c', 'import os,sys; print("PY_OK"); print(sys.version); print(os.getenv("SystemRoot","")); print(os.getenv("TEMP",""))'],
            $workingDir,
            $env
        );

        $check->setTimeout(60);
        $check->run();

        Log::info('PaddleOCR python health check', [
            'successful' => $check->isSuccessful(),
            'stdout' => $check->getOutput(),
            'stderr' => $check->getErrorOutput(),
        ]);

        if (! $check->isSuccessful()) {
            throw new RuntimeException(
                "Python health check gagal.\nSTDOUT:\n" . $check->getOutput() . "\nSTDERR:\n" . $check->getErrorOutput()
            );
        }
    }

    protected function maskEnvForLog(array $env): array
    {
        return [
            'SystemRoot' => $env['SystemRoot'] ?? null,
            'WINDIR' => $env['WINDIR'] ?? null,
            'USERPROFILE' => $env['USERPROFILE'] ?? null,
            'TEMP' => $env['TEMP'] ?? null,
            'TMP' => $env['TMP'] ?? null,
            'PATH_exists' => ! empty($env['PATH']),
        ];
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