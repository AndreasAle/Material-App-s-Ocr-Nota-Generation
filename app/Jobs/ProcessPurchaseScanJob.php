<?php

namespace App\Jobs;

use App\Models\PurchaseScanDraft;
use App\Services\Purchases\PurchaseScanDraftService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessPurchaseScanJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $draftId;

    public $timeout = 300;
    public $tries = 1;

    public function __construct(int $draftId)
    {
        $this->draftId = $draftId;
    }

    public function handle(PurchaseScanDraftService $purchaseScanDraftService): void
    {
        $draft = PurchaseScanDraft::find($this->draftId);

        if (! $draft) {
            return;
        }

        $started = hrtime(true);
        $waitMs = max(0, (int) round($draft->created_at->diffInMilliseconds(now())));
        try {
            $draft->update([
                'status' => 'processing',
                'progress' => 10,
                'progress_message' => 'Memvalidasi file nota...',
                'error_message' => null,
                'result_ready' => 0,
            ]);

            $draft->update([
                'progress' => 25,
                'progress_message' => 'Menyiapkan file untuk dibaca OCR...',
            ]);

            // WAJIB path relatif, bukan absolute path
            $purchaseScanDraftService->processExistingDraft($draft, $draft->file_path);

            $draft->refresh();

            $draft->update([
                'status' => 'processed',
                'progress' => 100,
                'progress_message' => 'Scan selesai. Mengalihkan ke halaman review...',
                'error_message' => null,
                'result_ready' => 1,
            ]);
        } catch (\Throwable $e) {
            $draft->update([
                'status' => 'failed',
                'progress' => 100,
                'progress_message' => 'Proses scan gagal.',
                'error_message' => 'Proses OCR gagal. Silakan coba lagi atau hubungi administrator.',
                'result_ready' => 0,
            ]);

            throw $e;
        } finally {
            \Illuminate\Support\Facades\Log::channel('receipt_scan')->info('Receipt scan completed', [
                'draft_id' => $draft->id,
                'status' => $draft->status,
                'wait_ms' => $waitMs,
                'processing_ms' => round((hrtime(true) - $started) / 1e6),
                'timings_ms' => data_get($draft->parsed_json, 'meta.timings_ms', []),
            ]);
        }
    }
}
