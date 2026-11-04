<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

class SecurePurchaseScanFiles extends Command
{
    protected $signature = 'scans:secure-files';

    protected $description = 'Move existing public receipt scans, purchase attachments and project documents into private storage';

    public function handle(): int
    {
        $moved = 0;
        $failed = 0;

        foreach (['purchase-scan-drafts', 'purchase-scan-batches', 'purchase-attachments', 'project-documents'] as $directory) {
            foreach (Storage::disk('public')->allFiles($directory) as $path) {
                try {
                    if (! Storage::disk('private')->exists($path)) {
                        Storage::disk('private')->put($path, Storage::disk('public')->get($path));
                    }

                    Storage::disk('public')->delete($path);
                    $moved++;
                } catch (Throwable $exception) {
                    report($exception);
                    $failed++;
                    $this->error('Could not secure file: ' . basename($path));
                }
            }
        }

        $this->info("Secured {$moved} file(s); {$failed} failure(s).");

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
